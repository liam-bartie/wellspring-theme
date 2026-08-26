#!/usr/bin/env python3
"""
capture_old_meta.py — byte-faithful capture of <title> and meta description
from the OLD site, driven by its XML sitemap.

Standard library only. No dependencies, no network libraries beyond urllib.

Why raw-HTML capture rather than any summarising fetch: the migration rule is
byte-for-byte fidelity. Entity encoding, non-breaking spaces, curly quotes and
double spaces all have to survive, so we read the bytes and record BOTH the raw
substring and its entity-decoded form, plus a SHA-256 of each.

Usage:
    python3 capture_old_meta.py --base https://example.com --out old_meta.json
    python3 capture_old_meta.py --base https://example.com --passes 2   # stability check

Exit codes:
    0  every sitemap URL fetched and yielded a title
    1  one or more URLs failed to fetch, or returned HTML with no <title>
    2  the sitemap itself could not be read
"""

from __future__ import annotations

import argparse
import gzip
import hashlib
import html
import json
import re
import ssl
import sys
import time
import unicodedata
import urllib.error
import urllib.parse
import urllib.request
import zlib
from typing import Any

USER_AGENT = "CanRank-migration-audit/1.0 (+meta capture; contact liam@canrank.ca)"

# Deliberately permissive: we want whatever is between the tags, verbatim.
TITLE_RE = re.compile(rb"<title[^>]*>(.*?)</title\s*>", re.IGNORECASE | re.DOTALL)
CANONICAL_RE = re.compile(
    rb"""<link\b[^>]*\brel\s*=\s*["']?canonical["']?[^>]*>""", re.IGNORECASE
)
GENERATOR_RE = re.compile(
    rb"""<meta\b[^>]*\bname\s*=\s*["']?generator["']?[^>]*>""", re.IGNORECASE
)
HREF_RE = re.compile(rb"""\bhref\s*=\s*(?:"([^"]*)"|'([^']*)'|([^\s>]+))""", re.IGNORECASE)
CONTENT_RE = re.compile(
    rb"""\bcontent\s*=\s*(?:"([^"]*)"|'([^']*)'|([^\s>]+))""", re.IGNORECASE
)
CHARSET_RE = re.compile(rb"""charset\s*=\s*["']?\s*([a-zA-Z0-9_\-]+)""", re.IGNORECASE)
LOC_RE = re.compile(rb"<loc>\s*(.*?)\s*</loc>", re.IGNORECASE | re.DOTALL)
SITEMAPINDEX_RE = re.compile(rb"<sitemapindex", re.IGNORECASE)

# Characters that look like ordinary spaces/quotes but are not, and that silently
# break a byte-for-byte comparison later. We report them, we do not strip them.
INVISIBLES = {
    " ": "NO-BREAK SPACE",
    " ": "THIN SPACE",
    " ": "HAIR SPACE",
    "​": "ZERO WIDTH SPACE",
    "‎": "LEFT-TO-RIGHT MARK",
    "‏": "RIGHT-TO-LEFT MARK",
    " ": "LINE SEPARATOR",
    " ": "PARAGRAPH SEPARATOR",
    "﻿": "ZERO WIDTH NO-BREAK SPACE (BOM)",
    "­": "SOFT HYPHEN",
}


def url_key(url: str) -> str:
    """Slash-insensitive identity for a URL, so /x and /x/ are one page."""
    parts = urllib.parse.urlsplit(url)
    path = parts.path or "/"
    path = "/" if path == "/" else "/" + path.strip("/")
    return urllib.parse.urlunsplit((parts.scheme, parts.netloc, path, parts.query, ""))


def sha(text: str) -> str:
    return hashlib.sha256(text.encode("utf-8")).hexdigest()


def build_opener(insecure: bool) -> urllib.request.OpenerDirector:
    if insecure:
        ctx = ssl.create_default_context()
        ctx.check_hostname = False
        ctx.verify_mode = ssl.CERT_NONE
        return urllib.request.build_opener(urllib.request.HTTPSHandler(context=ctx))
    return urllib.request.build_opener()


def fetch(url: str, opener, timeout: int = 30) -> tuple[int, bytes, dict[str, str], str]:
    """Return (status, body_bytes, headers, final_url). Raises on transport error."""
    req = urllib.request.Request(
        url,
        headers={
            "User-Agent": USER_AGENT,
            "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Encoding": "gzip, deflate",
            "Accept-Language": "en-CA,en;q=0.9",
        },
    )
    with opener.open(req, timeout=timeout) as resp:
        raw = resp.read()
        enc = (resp.headers.get("Content-Encoding") or "").lower()
        if "gzip" in enc:
            raw = gzip.decompress(raw)
        elif "deflate" in enc:
            try:
                raw = zlib.decompress(raw)
            except zlib.error:
                raw = zlib.decompress(raw, -zlib.MAX_WBITS)
        headers = {k.lower(): v for k, v in resp.headers.items()}
        return resp.status, raw, headers, resp.geturl()


def detect_charset(body: bytes, headers: dict[str, str]) -> str:
    ctype = headers.get("content-type", "")
    m = CHARSET_RE.search(ctype.encode("ascii", "ignore"))
    if m:
        return m.group(1).decode("ascii", "ignore")
    # Only look at the head; a charset declaration must appear early.
    m = CHARSET_RE.search(body[:4096])
    if m:
        return m.group(1).decode("ascii", "ignore")
    return "utf-8"


def attr_value(tag_bytes: bytes, pattern: re.Pattern[bytes]) -> bytes | None:
    m = pattern.search(tag_bytes)
    if not m:
        return None
    for g in m.groups():
        if g is not None:
            return g
    return None


def find_meta_description(body: bytes) -> bytes | None:
    """Find meta[name=description] without assuming attribute order."""
    for m in re.finditer(rb"<meta\b[^>]*>", body, re.IGNORECASE):
        tag = m.group(0)
        name = attr_value(tag, re.compile(rb"""\bname\s*=\s*(?:"([^"]*)"|'([^']*)'|([^\s>]+))""", re.IGNORECASE))
        if name is None:
            continue
        if name.strip().lower() != b"description":
            continue
        return attr_value(tag, CONTENT_RE)
    return None


def describe_invisibles(text: str) -> list[str]:
    found = []
    for ch, label in INVISIBLES.items():
        n = text.count(ch)
        if n:
            found.append(f"{label} x{n}")
    # Any other non-printing or format character.
    for ch in set(text):
        if ch in INVISIBLES:
            continue
        if unicodedata.category(ch) in {"Cc", "Cf"} and ch not in "\t\n\r":
            found.append(f"U+{ord(ch):04X} ({unicodedata.category(ch)})")
    return sorted(set(found))


def collapse_ws(text: str) -> str:
    """HTML collapses runs of whitespace when rendering a title. Kept alongside
    the exact value so we can tell a real difference from a formatting one."""
    return re.sub(r"\s+", " ", text).strip()


def read_sitemap(base: str, opener, seen: set[str] | None = None) -> list[str]:
    """Follow a sitemap or sitemap index and return every content <loc>."""
    if seen is None:
        seen = set()
    url = base if base.endswith(".xml") else base.rstrip("/") + "/sitemap.xml"
    if url in seen:
        return []
    seen.add(url)

    status, body, headers, _ = fetch(url, opener)
    if status != 200:
        raise RuntimeError(f"sitemap {url} returned HTTP {status}")
    if body[:2] == b"\x1f\x8b":
        body = gzip.decompress(body)

    locs = [m.group(1).decode("utf-8", "replace").strip() for m in LOC_RE.finditer(body)]
    if SITEMAPINDEX_RE.search(body):
        out: list[str] = []
        for child in locs:
            try:
                out.extend(read_sitemap(child, opener, seen))
            except Exception as exc:  # noqa: BLE001 - report and continue
                print(f"  ! child sitemap {child} failed: {exc}", file=sys.stderr)
        return out
    return locs


def capture(urls: list[str], opener, delay: float) -> dict[str, dict[str, Any]]:
    out: dict[str, dict[str, Any]] = {}
    for i, url in enumerate(urls, 1):
        rec: dict[str, Any] = {"url": url}
        try:
            status, body, headers, final = fetch(url, opener)
            charset = detect_charset(body, headers)
            rec["http_status"] = status
            rec["final_url"] = final
            rec["redirected"] = (final.rstrip("/") != url.rstrip("/"))
            rec["charset"] = charset
            rec["bytes"] = len(body)

            tm = TITLE_RE.search(body)
            if tm:
                raw = tm.group(1).decode(charset, "replace")
                dec = html.unescape(raw)
                rec["title_raw"] = raw
                rec["title"] = dec
                rec["title_collapsed"] = collapse_ws(dec)
                rec["title_len"] = len(collapse_ws(dec))
                rec["title_sha"] = sha(dec)
                rec["title_invisibles"] = describe_invisibles(dec)
            else:
                rec["title"] = None
                rec["title_missing_reason"] = (
                    "no <title> in served HTML — may be JS-rendered; read via browser"
                )

            dm = find_meta_description(body)
            if dm is not None:
                raw = dm.decode(charset, "replace")
                dec = html.unescape(raw)
                rec["description_raw"] = raw
                rec["description"] = dec
                rec["description_collapsed"] = collapse_ws(dec)
                rec["description_len"] = len(collapse_ws(dec))
                rec["description_sha"] = sha(dec)
                rec["description_invisibles"] = describe_invisibles(dec)
            else:
                rec["description"] = None

            cm = CANONICAL_RE.search(body)
            if cm:
                href = attr_value(cm.group(0), HREF_RE)
                rec["canonical"] = href.decode(charset, "replace") if href else None

            gm = GENERATOR_RE.search(body)
            if gm:
                content = attr_value(gm.group(0), CONTENT_RE)
                rec["generator"] = content.decode(charset, "replace") if content else None

        except urllib.error.HTTPError as exc:
            rec["error"] = f"HTTP {exc.code}"
            rec["http_status"] = exc.code
        except ssl.SSLCertVerificationError as exc:
            rec["error"] = f"SSL: {exc}"
            rec["hint"] = (
                "Python from python.org on macOS ships without CA certificates. "
                "Run '/Applications/Python 3.x/Install Certificates.command', or "
                "re-run this script with --insecure. This is a local trust-store "
                "problem, NOT a problem with the site."
            )
        except Exception as exc:  # noqa: BLE001
            rec["error"] = f"{type(exc).__name__}: {exc}"

        out[url] = rec
        state = rec.get("error") or (rec.get("title") or "NO TITLE")
        print(f"  [{i}/{len(urls)}] {url} -> {str(state)[:70]}")
        if delay:
            time.sleep(delay)
    return out


def main() -> int:
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("--base", required=True, help="Old site base URL, or a full sitemap URL")
    ap.add_argument("--out", default="old_meta.json")
    ap.add_argument("--passes", type=int, default=1, help="Capture N times and compare hashes")
    ap.add_argument("--delay", type=float, default=0.4, help="Seconds between requests")
    ap.add_argument("--exclude", action="append", default=[], help="Regex of paths to exclude (repeatable)")
    ap.add_argument("--insecure", action="store_true", help="Skip TLS verification (macOS CA workaround)")
    args = ap.parse_args()

    opener = build_opener(args.insecure)
    if args.insecure:
        print("! TLS verification DISABLED (--insecure)", file=sys.stderr)

    print(f"Reading sitemap from {args.base} ...")
    try:
        urls = read_sitemap(args.base, opener)
    except Exception as exc:  # noqa: BLE001
        print(f"FATAL: could not read sitemap: {exc}", file=sys.stderr)
        print("Enumerate URLs another way (crawl, or a hand-supplied list) and re-run.", file=sys.stderr)
        return 2

    """
    De-duplicate slash-insensitively, keeping the first form seen.

    A sitemap index frequently lists the same page in two shapes across its
    children — e.g. "https://site/" in one and "https://site" in the other.
    Matching raw strings lets both through, which fetches the page twice and
    then reports it as a duplicate of itself.
    """
    seen: dict[str, str] = {}
    ordered: list[str] = []
    collapsed: list[tuple[str, str]] = []
    for u in urls:
        key = url_key(u)
        if key in seen:
            if u != seen[key]:
                collapsed.append((u, seen[key]))
            continue
        seen[key] = u
        ordered.append(u)

    if collapsed:
        print(f"Collapsed {len(collapsed)} duplicate sitemap entr(y/ies) differing only by trailing slash:")
        for dupe, kept in collapsed:
            print(f"  {dupe}  ==  {kept}  (kept the latter)")

    excluded: list[str] = []
    kept: list[str] = []
    patterns = [re.compile(p) for p in args.exclude]
    for u in ordered:
        if any(p.search(u) for p in patterns):
            excluded.append(u)
        else:
            kept.append(u)

    print(f"Sitemap yielded {len(ordered)} unique URL(s); {len(kept)} kept, {len(excluded)} excluded.")
    for u in excluded:
        print(f"  excluded: {u}")

    runs = []
    for p in range(args.passes):
        print(f"\nPass {p + 1} of {args.passes}:")
        runs.append(capture(kept, opener, args.delay))

    result = runs[0]

    stability: dict[str, Any] = {"passes": args.passes, "unstable": []}
    if args.passes > 1:
        for u in kept:
            sigs = {
                (r[u].get("title_sha"), r[u].get("description_sha")) for r in runs
            }
            if len(sigs) > 1:
                stability["unstable"].append(u)
        print(
            f"\nStability: {len(kept) - len(stability['unstable'])}/{len(kept)} URLs "
            f"identical across {args.passes} passes."
        )
        for u in stability["unstable"]:
            print(f"  ! UNSTABLE (value changed between passes): {u}")

    payload = {
        "_meta": {
            "base": args.base,
            "captured_utc": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
            "generator": "capture_old_meta.py",
            "note": (
                "Values are entity-DECODED text as a browser renders them. "
                "*_raw preserves the exact source substring. Re-escape on output; "
                "never hand-edit this file."
            ),
            "excluded_urls": excluded,
            "collapsed_duplicate_urls": [{"dropped": d, "kept": k} for d, k in collapsed],
            "stability": stability,
        },
        "pages": result,
    }

    with open(args.out, "w", encoding="utf-8") as fh:
        json.dump(payload, fh, indent=2, ensure_ascii=False, sort_keys=True)
    print(f"\nWrote {args.out}")

    failures = [u for u, r in result.items() if r.get("error") or not r.get("title")]
    if failures:
        print(f"\n{len(failures)} URL(s) failed or had no title:", file=sys.stderr)
        for u in failures:
            print(f"  {u}: {result[u].get('error') or 'no <title>'}", file=sys.stderr)
        return 1
    return 0


if __name__ == "__main__":
    sys.exit(main())
