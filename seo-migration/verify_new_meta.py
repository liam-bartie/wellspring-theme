#!/usr/bin/env python3
"""
verify_new_meta.py — prove the NEW site renders exactly what was ported.

Standard library only. Read-only: every request is a GET, nothing is written to
the site. Exits non-zero if anything does not match, so it can gate a deploy.

Checks the rendered HTML, not the database. An import that "reported success"
proves nothing; this proves what a crawler actually sees.

THE FALLBACK RULE. The theme falls back to "Page name – Site name" when no SEO
title is set. That fallback is a feature, and it is also the most dangerous
possible verification result: a page whose ported value never landed still looks
perfectly reasonable. So where a ported value is expected, a fallback counts as
a FAILURE, never a match — reported as FALLBACK rather than a generic mismatch,
because the fix is different (re-run the import, not re-edit the value).

Also verified, because these are silent when wrong:
  - exactly one <title> and at most one meta description (duplicates mean two
    things are emitting, e.g. the plugin guard failed)
  - the URL stays on this site (an off-site redirect returns a legitimate 200
    from somebody else's server)
  - a canonical is present on the archive views core does not cover

Usage:
    python3 verify_new_meta.py --new https://newsite.example
    python3 verify_new_meta.py --new ... --data seo_data.json
    python3 verify_new_meta.py --new ... --json report.json
"""

from __future__ import annotations

import argparse
import json
import sys
import time
import urllib.error
import urllib.parse
import urllib.request
from typing import Any

try:
    import capture_old_meta as cap
except ImportError:
    sys.stderr.write("FATAL: capture_old_meta.py must sit next to this script.\n")
    raise SystemExit(2)


GREEN, RED, YELLOW, DIM, RESET = "\033[32m", "\033[31m", "\033[33m", "\033[2m", "\033[0m"


def plain(stream_is_tty: bool):
    if not stream_is_tty:
        return ("", "", "", "", "")
    return (GREEN, RED, YELLOW, DIM, RESET)


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None


def build_opener(insecure: bool) -> urllib.request.OpenerDirector:
    handlers: list[Any] = [NoRedirect]
    if insecure:
        ctx = cap.ssl.create_default_context()
        ctx.check_hostname = False
        ctx.verify_mode = cap.ssl.CERT_NONE
        handlers.append(urllib.request.HTTPSHandler(context=ctx))
    return urllib.request.build_opener(*handlers)


def get(url: str, opener, host: str, hops: int = 5) -> dict[str, Any]:
    """GET, following only SAME-SITE redirects. Leaving the site is a failure."""
    chain: list[str] = []
    current = url
    for _ in range(hops):
        chain.append(current)
        try:
            req = urllib.request.Request(
                current,
                headers={"User-Agent": cap.USER_AGENT, "Accept": "text/html,*/*;q=0.8"},
            )
            with opener.open(req, timeout=30) as resp:
                return {
                    "status": resp.status,
                    "body": resp.read(),
                    "headers": {k.lower(): v for k, v in resp.headers.items()},
                    "final": current,
                    "chain": chain,
                }
        except urllib.error.HTTPError as exc:
            loc = exc.headers.get("Location") if exc.headers else None
            if exc.code in (301, 302, 307, 308) and loc:
                nxt = urllib.parse.urljoin(current, loc)
                if urllib.parse.urlsplit(nxt).netloc.lower() != host:
                    return {"status": exc.code, "body": b"", "headers": {}, "final": nxt,
                            "chain": chain + [nxt], "left_site": True}
                current = nxt
                continue
            return {"status": exc.code, "body": b"", "headers": {}, "final": current, "chain": chain}
        except Exception as exc:  # noqa: BLE001
            return {"status": None, "body": b"", "headers": {}, "final": current,
                    "chain": chain, "error": f"{type(exc).__name__}: {exc}"}
    return {"status": None, "body": b"", "headers": {}, "final": current, "chain": chain,
            "error": "too many redirects"}


def extract(body: bytes, headers: dict[str, str]) -> dict[str, Any]:
    charset = cap.detect_charset(body, headers)
    titles = cap.TITLE_RE.findall(body)
    descs = [
        m for m in cap.re.finditer(
            rb"<meta\b[^>]*\bname\s*=\s*[\"']?description[\"']?", body, cap.re.IGNORECASE
        )
    ]
    dm = cap.find_meta_description(body)
    cm = cap.CANONICAL_RE.search(body)
    canonical = None
    if cm:
        href = cap.attr_value(cm.group(0), cap.HREF_RE)
        canonical = href.decode(charset, "replace") if href else None
    return {
        "title": cap.html.unescape(titles[0].decode(charset, "replace")) if titles else None,
        "title_count": len(titles),
        "description": cap.html.unescape(dm.decode(charset, "replace")) if dm is not None else None,
        "description_count": len(descs),
        "canonical": canonical,
        "canonical_count": len(cap.CANONICAL_RE.findall(body)),
    }


def target_path(entry: dict[str, Any]) -> str | None:
    t, k = entry.get("target_type"), entry.get("target_key")
    if t == "front_page":
        return "/"
    if t == "page":
        return "/" + str(k).strip("/")
    if t == "cpt_archive" and k == "clinic_case":
        return "/clinic-cases"
    return None


def looks_like_fallback(rendered: str, expected: str, site_name: str) -> bool:
    """A core-built 'Page – Site' title where a ported value was expected."""
    if not rendered or rendered == expected:
        return False
    for sep in (" – ", " - ", " | ", " — "):
        if rendered.endswith(sep + site_name):
            return True
    return False


def main() -> int:
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("--new", required=True, help="New site base URL")
    ap.add_argument("--data", default="seo_data.json")
    ap.add_argument("--site-name", default="Wellspring Health",
                    help="Used to recognise a core-built fallback title")
    ap.add_argument("--json", help="Also write a machine-readable report here")
    ap.add_argument("--delay", type=float, default=0.3)
    ap.add_argument("--insecure", action="store_true")
    args = ap.parse_args()

    g, r, y, d, x = plain(sys.stdout.isatty())

    with open(args.data, encoding="utf-8") as fh:
        data = json.load(fh)
    entries = data.get("entries") or []
    if not entries:
        print("FATAL: no entries in the data file.", file=sys.stderr)
        return 2

    base = args.new.rstrip("/")
    host = urllib.parse.urlsplit(base).netloc.lower()
    opener = build_opener(args.insecure)

    rows: list[dict[str, Any]] = []
    failures = 0

    print(f"Verifying {len(entries)} target(s) against {base}\n")
    print(f"{'TARGET':30} {'TITLE':>8} {'DESC':>8}  DETAIL")
    print("-" * 96)

    for entry in entries:
        path = target_path(entry)
        if path is None:
            print(f"{str(entry.get('source_path')):30} {'—':>8} {'—':>8}  unmapped target type")
            continue

        url = base + ("/" if path == "/" else path)
        res = get(url, opener, host)
        row: dict[str, Any] = {"source": entry.get("source_path"), "url": url,
                               "status": res.get("status")}

        # --- hard failures before any comparison
        if res.get("left_site"):
            print(f"{path:30} {r}{'OFF':>8}{x} {r}{'OFF':>8}{x}  leaves the site -> {res['final']}")
            row["result"] = "left_site"
            rows.append(row)
            failures += 1
            continue
        if res.get("status") != 200:
            detail = res.get("error") or f"HTTP {res.get('status')}"
            print(f"{path:30} {r}{'FAIL':>8}{x} {r}{'FAIL':>8}{x}  {detail}")
            row["result"] = "unreachable"
            rows.append(row)
            failures += 1
            continue

        got = extract(res["body"], res["headers"])
        row["rendered"] = got

        # --- duplicate tags mean two things are emitting
        dup_notes: list[str] = []
        if got["title_count"] != 1:
            dup_notes.append(f"{got['title_count']} <title> tags")
            failures += 1
        if got["description_count"] > 1:
            dup_notes.append(f"{got['description_count']} description tags")
            failures += 1

        verdicts = {}
        for field in ("title", "description"):
            expected = entry.get(field)
            rendered = got[field]

            if not expected:
                verdicts[field] = "n/a"
                continue
            if rendered == expected:
                verdicts[field] = "ok"
                continue
            if field == "title" and looks_like_fallback(rendered or "", expected, args.site_name):
                verdicts[field] = "fallback"
                failures += 1
                continue
            if rendered is None:
                verdicts[field] = "absent"
                failures += 1
                continue
            verdicts[field] = "differs"
            failures += 1

        row["verdicts"] = verdicts

        def mark(v, width=8):
            text = {"ok": "ok", "n/a": "n/a", "fallback": "FALLBK"}.get(v, v.upper())
            colour = g if v == "ok" else (d if v == "n/a" else r)
            # Pad the PLAIN text, then colour, so escape codes do not count
            # toward the column width.
            return colour + text.rjust(width) + x

        detail = ""
        for field in ("title", "description"):
            if verdicts[field] in ("differs", "absent", "fallback"):
                exp = entry.get(field) or ""
                ren = got[field]
                detail = f"{field}: expected {exp!r}"
                if verdicts[field] == "fallback":
                    detail += " but the theme FALLBACK rendered — the ported value did not land"
                else:
                    detail += f" got {ren!r}"
                break

        print(f"{path:30} {mark(verdicts['title'])} {mark(verdicts['description'])}  {detail}")
        for note in dup_notes:
            print(f"{'':30} {'':8} {'':8}  {r}{note}{x}")

        # --- canonicals: core skips archives, the theme must fill them
        if entry.get("target_type") == "cpt_archive" and not got["canonical"]:
            print(f"{'':30} {'':>8} {'':>8}  {y}no canonical on this archive — core omits it, the theme should add it{x}")
            failures += 1
        if got["canonical_count"] > 1:
            print(f"{'':30} {'':>8} {'':>8}  {r}{got['canonical_count']} canonical tags{x}")
            failures += 1

        rows.append(row)
        if args.delay:
            time.sleep(args.delay)

    print("-" * 96)
    if failures:
        print(f"\n{r}{failures} problem(s).{x} Nothing was changed — fix and re-run.")
    else:
        print(f"\n{g}All targets render exactly what was ported.{x}")

    if args.json:
        with open(args.json, "w", encoding="utf-8") as fh:
            json.dump(
                {
                    "_meta": {
                        "verified_utc": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
                        "new": base,
                        "data": args.data,
                        "failures": failures,
                    },
                    "rows": rows,
                },
                fh,
                indent=2,
                ensure_ascii=False,
            )
        print(f"Wrote {args.json}")

    return 1 if failures else 0


if __name__ == "__main__":
    sys.exit(main())
