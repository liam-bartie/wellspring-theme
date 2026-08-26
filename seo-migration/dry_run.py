#!/usr/bin/env python3
"""
dry_run.py — read-only pre-flight for the title/description migration.

Touches nothing. Every request is a GET, nothing is written to either site and
nothing is written to WordPress. The only files produced are its own report.

What it establishes, in order:

  1. OLD SITE   Capture every title and meta description byte-faithfully
                (reuses capture_old_meta.py, so the extraction logic and any
                bug in it are shared with the real capture rather than
                reimplemented).
  2. URL SHAPE  Probe the new site empirically for trailing-slash behaviour
                instead of inferring it from markup.
  3. MAPPING    Pair each old URL with its new counterpart, reporting old URLs
                with no target (need redirects) and new URLs with no source
                (will keep default WordPress titles).
  4. BASELINE   Record what the new site currently emits, so there is a before
                to compare against after the import.
  5. ANOMALIES  CMS-appended title suffixes, over-length values, invisible
                characters, duplicate values across pages, redirect chains,
                non-200s, and robots.txt on both sides.

Usage:
    python3 dry_run.py --old https://oldsite.example --new https://newsite.example
    python3 dry_run.py --old ... --new ... --map /book-appointments=/book/
    python3 dry_run.py --old ... --new ... --old-json old_meta.json   # reuse a capture

Exit codes:
    0  dry run completed, nothing blocking found
    1  dry run completed, but findings need a decision before importing
    2  could not complete (sitemap unreadable, site unreachable)
"""

from __future__ import annotations

import argparse
import json
import re
import sys
import time
import unicodedata
import urllib.error
import urllib.parse
from collections import Counter, defaultdict
from typing import Any

try:
    import capture_old_meta as cap
except ImportError:
    sys.stderr.write(
        "FATAL: capture_old_meta.py must sit next to this script "
        "(the extraction logic is shared, deliberately).\n"
    )
    raise SystemExit(2)


# --------------------------------------------------------------------------- io

def path_of(url: str) -> str:
    p = urllib.parse.urlsplit(url).path or "/"
    return p


def norm_key(url: str) -> str:
    """Slash-insensitive path key, for pairing old and new URLs."""
    p = path_of(url)
    return "/" if p == "/" else "/" + p.strip("/")


class _NoRedirect(urllib.request.HTTPRedirectHandler):
    """Surface redirects as HTTPError instead of following them silently."""

    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None


def build_probe_opener(insecure: bool) -> urllib.request.OpenerDirector:
    """
    A dedicated opener that does NOT follow redirects.

    Built from scratch rather than by splicing _NoRedirect into an existing
    opener's handler list: build_opener() de-duplicates by class, so mixing a
    HTTPRedirectHandler subclass with an already-instantiated
    HTTPRedirectHandler makes which one wins ambiguous — and if the default
    wins, every redirect is followed invisibly and the chain reporting is a lie.
    """
    handlers: list[Any] = [_NoRedirect]
    if insecure:
        ctx = cap.ssl.create_default_context()
        ctx.check_hostname = False
        ctx.verify_mode = cap.ssl.CERT_NONE
        handlers.append(urllib.request.HTTPSHandler(context=ctx))
    return urllib.request.build_opener(*handlers)


def probe(url: str, opener, follow: bool = True) -> dict[str, Any]:
    """GET a URL, recording the redirect chain rather than hiding it."""
    chain: list[dict[str, Any]] = []
    current = url
    for _ in range(6):
        try:
            req = urllib.request.Request(
                current,
                headers={"User-Agent": cap.USER_AGENT, "Accept": "text/html,*/*;q=0.8"},
                method="GET",
            )
            with opener.open(req, timeout=30) as resp:
                status = resp.status
                body = resp.read()
                headers = {k.lower(): v for k, v in resp.headers.items()}
            chain.append({"url": current, "status": status})
            return {"chain": chain, "status": status, "body": body, "headers": headers, "final": current}
        except urllib.error.HTTPError as exc:
            status = exc.code
            loc = exc.headers.get("Location") if exc.headers else None
            chain.append({"url": current, "status": status, "location": loc})
            if follow and status in (301, 302, 307, 308) and loc:
                current = urllib.parse.urljoin(current, loc)
                continue
            body = b""
            try:
                body = exc.read()
            except Exception:  # noqa: BLE001
                pass
            return {"chain": chain, "status": status, "body": body, "headers": {}, "final": current}
        except Exception as exc:  # noqa: BLE001
            chain.append({"url": current, "error": f"{type(exc).__name__}: {exc}"})
            return {"chain": chain, "status": None, "body": b"", "headers": {}, "final": current,
                    "error": f"{type(exc).__name__}: {exc}"}
    return {"chain": chain, "status": None, "body": b"", "headers": {}, "final": current,
            "error": "too many redirects"}


def extract(body: bytes, headers: dict[str, str]) -> dict[str, Any]:
    """Title/description/canonical, using the shared capture logic."""
    if not body:
        return {"title": None, "description": None, "canonical": None}
    charset = cap.detect_charset(body, headers)
    out: dict[str, Any] = {"charset": charset}

    tm = cap.TITLE_RE.search(body)
    if tm:
        raw = tm.group(1).decode(charset, "replace")
        dec = cap.html.unescape(raw)
        out["title"] = dec
        out["title_collapsed"] = cap.collapse_ws(dec)
        out["title_len"] = len(cap.collapse_ws(dec))
        out["title_invisibles"] = cap.describe_invisibles(dec)
    else:
        out["title"] = None

    dm = cap.find_meta_description(body)
    if dm is not None:
        raw = dm.decode(charset, "replace")
        dec = cap.html.unescape(raw)
        out["description"] = dec
        out["description_collapsed"] = cap.collapse_ws(dec)
        out["description_len"] = len(cap.collapse_ws(dec))
        out["description_invisibles"] = cap.describe_invisibles(dec)
    else:
        out["description"] = None

    cm = cap.CANONICAL_RE.search(body)
    if cm:
        href = cap.attr_value(cm.group(0), cap.HREF_RE)
        out["canonical"] = href.decode(charset, "replace") if href else None

    # Duplicate-tag detection: two title tags means something emits twice.
    out["title_tag_count"] = len(cap.TITLE_RE.findall(body))
    out["description_tag_count"] = len(
        [m for m in re.finditer(rb"<meta\b[^>]*\bname\s*=\s*[\"']?description[\"']?", body, re.IGNORECASE)]
    )
    return out


# ----------------------------------------------------------------- url shape

def detect_slash_behaviour(new_base: str, sample_path: str, prober) -> dict[str, Any]:
    """Ask the new site directly which URL shape is canonical."""
    result: dict[str, Any] = {"sample": sample_path}
    bare = new_base.rstrip("/") + "/" + sample_path.strip("/")
    slashed = bare + "/"
    for label, url in (("without_slash", bare), ("with_slash", slashed)):
        r = probe(url, prober, follow=False)
        entry = {"url": url, "status": r["status"]}
        if r["chain"] and r["chain"][-1].get("location"):
            entry["redirects_to"] = r["chain"][-1]["location"]
        result[label] = entry

    ws, wos = result["with_slash"], result["without_slash"]
    if ws.get("status") == 200 and wos.get("status") in (301, 302, 307, 308):
        result["canonical_shape"] = "trailing slash (/%postname%/)"
    elif wos.get("status") == 200 and ws.get("status") in (301, 302, 307, 308):
        result["canonical_shape"] = "no trailing slash (/%postname%)"
    elif ws.get("status") == 200 and wos.get("status") == 200:
        result["canonical_shape"] = "BOTH serve 200 — duplicate content risk"
    else:
        result["canonical_shape"] = "inconclusive"
    return result


# ------------------------------------------------------------------- anomalies

def detect_title_suffix(titles: list[str]) -> dict[str, Any]:
    """Look for a CMS-appended suffix common to most titles."""
    usable = [t for t in titles if t and t.strip()]
    if len(usable) < 3:
        return {"detected": False, "reason": "too few titles to judge"}

    candidates: Counter[str] = Counter()
    for sep in (" | ", " - ", " – ", " — ", " :: ", " · "):
        for t in usable:
            if sep in t:
                candidates[sep + t.rsplit(sep, 1)[1]] += 1

    if not candidates:
        return {"detected": False, "reason": "no common separator+suffix found"}

    suffix, count = candidates.most_common(1)[0]
    share = count / len(usable)
    return {
        "detected": share >= 0.6,
        "suffix": suffix,
        "appears_on": count,
        "of": len(usable),
        "share": round(share, 2),
        "note": (
            "Present on most titles — likely a platform artifact. FLAG, do not strip "
            "without a decision." if share >= 0.6 else
            "Present on a minority — probably editorial, not appended."
        ),
    }


def find_duplicates(values: dict[str, str | None]) -> dict[str, list[str]]:
    buckets: dict[str, list[str]] = defaultdict(list)
    for url, val in values.items():
        if val and val.strip():
            buckets[cap.collapse_ws(val)].append(url)
    return {v: urls for v, urls in buckets.items() if len(urls) > 1}


# ------------------------------------------------------------------------ main

def main() -> int:
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("--old", required=True, help="Old site base URL")
    ap.add_argument("--new", required=True, help="New site base URL")
    ap.add_argument("--old-json", help="Reuse an existing capture instead of re-crawling the old site")
    ap.add_argument("--map", action="append", default=[], metavar="OLD=NEW",
                    help="Explicit path mapping, repeatable (e.g. /book-appointments=/book/)")
    ap.add_argument("--exclude", action="append", default=[r"/m/"],
                    help="Regex of old paths to ignore (repeatable)")
    ap.add_argument("--slash-sample", default="about", help="Path used to probe URL shape")
    ap.add_argument("--out", default="dry_run_report.json")
    ap.add_argument("--delay", type=float, default=0.3)
    ap.add_argument("--insecure", action="store_true", help="Skip TLS verification (macOS CA workaround)")
    args = ap.parse_args()

    # Two openers on purpose: the capture path follows redirects (we want the
    # final page), the probe path does not (we want the chain).
    opener = cap.build_opener(args.insecure)
    prober = build_probe_opener(args.insecure)
    overrides = {}
    for pair in args.map:
        if "=" not in pair:
            print(f"FATAL: --map needs OLD=NEW, got {pair!r}", file=sys.stderr)
            return 2
        o, n = pair.split("=", 1)
        overrides[norm_key(o)] = n

    report: dict[str, Any] = {
        "_meta": {
            "generated_utc": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
            "old": args.old,
            "new": args.new,
            "read_only": True,
            "note": "Nothing was written to either site or to WordPress.",
        }
    }
    findings: list[str] = []

    # ---------------------------------------------------------------- 1. OLD
    print("=" * 72)
    print("1. OLD SITE — capturing titles and descriptions")
    print("=" * 72)

    if args.old_json:
        with open(args.old_json, encoding="utf-8") as fh:
            old_payload = json.load(fh)
        old_pages = old_payload["pages"]
        print(f"  reusing {args.old_json} ({len(old_pages)} URLs)")
    else:
        try:
            old_urls = cap.read_sitemap(args.old, opener)
        except Exception as exc:  # noqa: BLE001
            print(f"FATAL: old sitemap unreadable: {exc}", file=sys.stderr)
            return 2
        pats = [re.compile(p) for p in args.exclude]
        # Slash-insensitive: a sitemap index often lists "/" in one child and
        # "" (bare host) in another. Raw-string de-dup fetches it twice and then
        # reports the page as a duplicate of itself.
        seen: dict[str, str] = {}
        collapsed: list[tuple[str, str]] = []
        kept = []
        skipped = []
        for u in old_urls:
            key = cap.url_key(u)
            if key in seen:
                if u != seen[key]:
                    collapsed.append((u, seen[key]))
                continue
            seen[key] = u
            (skipped if any(p.search(u) for p in pats) else kept).append(u)
        print(f"  sitemap: {len(seen)} unique, {len(kept)} kept, {len(skipped)} excluded")
        if collapsed:
            print(f"  collapsed {len(collapsed)} entr(y/ies) differing only by trailing slash:")
            for dupe, keep in collapsed:
                print(f"    dropped {dupe}  (same page as {keep})")
            report.setdefault("_meta", {})["collapsed_duplicate_urls"] = [
                {"dropped": dd, "kept": kk} for dd, kk in collapsed
            ]
        for u in skipped:
            print(f"    excluded: {u}")
        old_pages = cap.capture(kept, opener, args.delay)

    report["old"] = old_pages

    old_titles = {u: r.get("title") for u, r in old_pages.items()}
    old_descs = {u: r.get("description") for u, r in old_pages.items()}

    # ------------------------------------------------------------ 2. URL SHAPE
    print()
    print("=" * 72)
    print("2. URL SHAPE — probing the new site directly")
    print("=" * 72)
    shape = detect_slash_behaviour(args.new, args.slash_sample, prober)
    report["url_shape"] = shape
    print(f"  /{args.slash_sample}   -> {shape['without_slash']}")
    print(f"  /{args.slash_sample}/  -> {shape['with_slash']}")
    print(f"  => new site canonical shape: {shape['canonical_shape']}")

    old_shapes = {("slash" if path_of(u).endswith("/") and path_of(u) != "/" else "bare")
                  for u in old_pages}
    old_shape = "trailing slash" if old_shapes == {"slash"} else (
        "no trailing slash" if old_shapes == {"bare"} else "mixed")
    print(f"  => old site shape: {old_shape}")
    if "no trailing slash" in old_shape and "trailing slash (/%postname%/)" == shape["canonical_shape"]:
        findings.append(
            "URL SHAPE MISMATCH: old URLs have no trailing slash, new site canonicalises "
            "to trailing slash. Either set permalinks to /%postname% (and fix hardcoded "
            "theme links) or accept a 301 on every old URL."
        )
    if "BOTH serve 200" in shape["canonical_shape"]:
        findings.append(
            "Both /path and /path/ return 200 on the new site — duplicate content. "
            "One shape must 301 to the other."
        )

    # -------------------------------------------------------------- 3. MAPPING
    print()
    print("=" * 72)
    print("3. MAPPING + 4. BASELINE — what the new site serves today")
    print("=" * 72)

    slash = "trailing slash" in shape["canonical_shape"]

    def new_url_for(old_url: str) -> str:
        key = norm_key(old_url)
        if key in overrides:
            target = overrides[key]
            return args.new.rstrip("/") + "/" + target.strip("/") + ("/" if slash and target != "/" else "")
        if key == "/":
            return args.new.rstrip("/") + "/"
        return args.new.rstrip("/") + "/" + key.strip("/") + ("/" if slash else "")

    pairs: dict[str, Any] = {}
    for old_url in old_pages:
        target = new_url_for(old_url)
        r = probe(target, prober)
        info = extract(r["body"], r["headers"])
        # A 200 reached by following a redirect OFF the site is not a pass: the
        # content belongs to someone else and there is nowhere on this domain to
        # hold the ported meta. Compare hosts, not just status codes.
        new_host = urllib.parse.urlsplit(args.new).netloc.lower()
        final_host = urllib.parse.urlsplit(r.get("final") or target).netloc.lower()
        left_site = bool(final_host) and final_host != new_host

        entry = {
            "old_url": old_url,
            "new_url": target,
            "new_status": r["status"],
            "final_url": r.get("final"),
            "left_site": left_site,
            "redirect_chain": r["chain"] if len(r["chain"]) > 1 else None,
            "old_title": old_titles.get(old_url),
            "old_description": old_descs.get(old_url),
            "new_title_now": info.get("title"),
            "new_description_now": info.get("description"),
            "new_title_tag_count": info.get("title_tag_count"),
            "new_description_tag_count": info.get("description_tag_count"),
        }
        if r.get("error"):
            entry["error"] = r["error"]
        pairs[old_url] = entry

        status = r["status"]
        if left_site:
            mark = "OFF!"
        elif status == 200:
            mark = "ok "
        else:
            mark = "MISS"
        print(f"  [{mark}] {path_of(old_url):28} -> {path_of(target):28} HTTP {status}")
        if left_site:
            print(f"         LEAVES THE SITE -> {r.get('final')}")
            findings.append(
                f"{path_of(old_url)} redirects OFF the new site to {final_host} — the 200 "
                "comes from a third party, so this URL has no page of yours and nowhere "
                "to hold its ported title/description."
            )
        if entry["redirect_chain"]:
            hops = " -> ".join(f"{h['status']}" for h in r["chain"])
            print(f"         redirect chain: {hops}")
        if info.get("title_tag_count", 0) > 1:
            findings.append(f"{target} emits {info['title_tag_count']} <title> tags.")
        if info.get("description_tag_count", 0) > 1:
            findings.append(f"{target} emits {info['description_tag_count']} description tags.")
        if args.delay:
            time.sleep(args.delay)

    report["pairs"] = pairs

    no_target = [u for u, e in pairs.items()
                 if e["new_status"] != 200 or e.get("left_site")]
    if no_target:
        findings.append(
            f"{len(no_target)} old URL(s) have no 200 on the new site — they need a "
            "redirect target decided: " + ", ".join(path_of(u) for u in no_target)
        )

    # --------------------------------------- new URLs with nothing to port
    print()
    print("=" * 72)
    print("5. NEW PAGES WITH NOTHING TO PORT")
    print("=" * 72)
    orphans: list[str] = []
    try:
        new_urls = cap.read_sitemap(args.new.rstrip("/") + "/wp-sitemap.xml", opener)
        mapped = {norm_key(e["new_url"]) for e in pairs.values()}
        for u in new_urls:
            if norm_key(u) not in mapped:
                orphans.append(u)
        print(f"  new sitemap: {len(new_urls)} URLs, {len(orphans)} with no old counterpart")
        for u in sorted(orphans):
            print(f"    {path_of(u)}")
        if orphans:
            findings.append(
                f"{len(orphans)} new URL(s) have no old counterpart and will keep default "
                "WordPress titles with no ported description."
            )
    except Exception as exc:  # noqa: BLE001
        print(f"  ! could not read the new sitemap: {exc}")
        findings.append(f"New-site sitemap unreadable ({exc}) — orphan list is incomplete.")
    report["new_orphans"] = sorted(orphans)

    # ------------------------------------------------------------ 6. ANOMALIES
    print()
    print("=" * 72)
    print("6. ANOMALIES")
    print("=" * 72)

    suffix = detect_title_suffix(list(old_titles.values()))
    report["title_suffix"] = suffix
    if suffix.get("detected"):
        print(f"  CMS suffix candidate: {suffix['suffix']!r} on {suffix['appears_on']}/{suffix['of']}")
        print(f"    {suffix['note']}")
        findings.append(
            f"Possible CMS-appended title suffix {suffix['suffix']!r} on "
            f"{suffix['appears_on']}/{suffix['of']} old titles — DECIDE before importing."
        )
    else:
        print(f"  no CMS title suffix detected ({suffix.get('reason', suffix.get('note'))})")

    long_titles = {u: r["title_len"] for u, r in old_pages.items() if (r.get("title_len") or 0) > 60}
    long_descs = {u: r["description_len"] for u, r in old_pages.items() if (r.get("description_len") or 0) > 160}
    report["over_length"] = {"titles": long_titles, "descriptions": long_descs}
    print(f"  titles over 60 chars: {len(long_titles)} (ported as-is by design)")
    for u, n in long_titles.items():
        print(f"    {n:4} {path_of(u)}")
    print(f"  descriptions over 160 chars: {len(long_descs)} (ported as-is by design)")
    for u, n in long_descs.items():
        print(f"    {n:4} {path_of(u)}")

    invis = {u: r["title_invisibles"] + r.get("description_invisibles", [])
             for u, r in old_pages.items()
             if r.get("title_invisibles") or r.get("description_invisibles")}
    report["invisibles"] = invis
    if invis:
        print(f"  ! invisible characters found on {len(invis)} page(s) — these MUST survive:")
        for u, chars in invis.items():
            print(f"    {path_of(u)}: {', '.join(chars)}")
        findings.append(
            f"{len(invis)} page(s) contain invisible characters (no-break spaces etc). "
            "Any 'improvement' pass will destroy these silently."
        )
    else:
        print("  no invisible characters found")

    missing_t = [u for u, t in old_titles.items() if not t]
    missing_d = [u for u, d in old_descs.items() if not d]
    report["missing"] = {"titles": missing_t, "descriptions": missing_d}
    if missing_t:
        findings.append(
            f"{len(missing_t)} old URL(s) served no <title> — may be JS-rendered; read via "
            "a browser rather than treating as absent: " + ", ".join(path_of(u) for u in missing_t)
        )
    if missing_d:
        print(f"  {len(missing_d)} old page(s) have no meta description: "
              + ", ".join(path_of(u) for u in missing_d))

    dup_t = find_duplicates(old_titles)
    dup_d = find_duplicates(old_descs)
    report["duplicates"] = {"titles": dup_t, "descriptions": dup_d}
    for label, dups in (("title", dup_t), ("description", dup_d)):
        for value, urls in dups.items():
            print(f"  duplicate {label} on {len(urls)} pages: {value[:60]!r}")
            findings.append(f"Duplicate {label} across {', '.join(path_of(u) for u in urls)}.")

    # -------------------------------------------------------------- robots.txt
    print()
    print("=" * 72)
    print("7. ROBOTS.TXT")
    print("=" * 72)
    for label, base in (("old", args.old), ("new", args.new)):
        r = probe(base.rstrip("/") + "/robots.txt", prober)
        text = r["body"].decode("utf-8", "replace") if r["body"] else ""
        report.setdefault("robots", {})[label] = {"status": r["status"], "body": text}
        print(f"  [{label}] HTTP {r['status']}")
        for line in text.splitlines():
            print(f"      {line}")
        blocked = re.search(r"User-agent:\s*Googlebot\s*\n\s*Disallow:\s*/\s*$",
                            text, re.IGNORECASE | re.MULTILINE)
        if blocked and label == "new":
            findings.append(
                "NEW SITE robots.txt blocks Googlebot entirely. Correct for staging, "
                "catastrophic if it survives cutover. Must be removed at launch."
            )

    # ----------------------------------------------------------------- summary
    print()
    print("=" * 72)
    print("FINDINGS — decide these before importing")
    print("=" * 72)
    if findings:
        for i, f in enumerate(findings, 1):
            print(f"  {i}. {f}")
    else:
        print("  none")

    report["findings"] = findings
    with open(args.out, "w", encoding="utf-8") as fh:
        json.dump(report, fh, indent=2, ensure_ascii=False, sort_keys=True)
    print(f"\nWrote {args.out} (nothing else was written anywhere)")

    return 1 if findings else 0


if __name__ == "__main__":
    sys.exit(main())
