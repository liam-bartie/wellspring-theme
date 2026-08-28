#!/usr/bin/env python3
"""
audit_meta.py — dump every URL on a site with its title tag and meta description.

Standard library only. Read-only: GETs, nothing written to the site. Produces a
CSV you can open in Excel or Sheets, plus a console summary of what needs work.

Driven by /wp-sitemap.xml, and it reads each sub-sitemap separately so every URL
is labelled by what it actually is — page, clinic_case, case_focus and so on —
rather than guessed from the path.

Columns:
    kind, path, url, status, title, title_len, title_is_auto, description,
    desc_len, canonical, noindex, flags

title_is_auto is the useful one for a content worklist: it flags titles that
still look like the theme's automatic "Page name – Site name" pattern, i.e. the
pages nobody has written a real title for yet.

Usage:
    python3 audit_meta.py --site https://example.com
    python3 audit_meta.py --site https://example.com --out audit.csv --site-name "Wellspring Health"
    python3 audit_meta.py --site https://example.com --kind page      # pages only

Exit codes:
    0  every URL fetched
    1  one or more URLs failed, or returned no title
    2  the sitemap could not be read
"""

from __future__ import annotations

import argparse
import csv
import re
import sys
import time
import urllib.error
import urllib.parse
import urllib.request
from collections import Counter, defaultdict
from typing import Any

try:
    import capture_old_meta as cap
except ImportError:
    sys.stderr.write("FATAL: capture_old_meta.py must sit next to this script.\n")
    raise SystemExit(2)


ROBOTS_RE = re.compile(
    rb"""<meta\b[^>]*\bname\s*=\s*["']?robots["']?[^>]*>""", re.IGNORECASE
)
H1_RE = re.compile(rb"<h1[^>]*>(.*?)</h1\s*>", re.IGNORECASE | re.DOTALL)

# wp-sitemap-posts-page-1.xml -> page ; wp-sitemap-taxonomies-case_focus-1.xml -> case_focus
CHILD_RE = re.compile(r"wp-sitemap-(?:posts|taxonomies|users)-([A-Za-z0-9_]+)-\d+\.xml$")


def child_kind(url: str) -> str:
    m = CHILD_RE.search(urllib.parse.urlsplit(url).path)
    return m.group(1) if m else "unknown"


def sitemap_urls(site: str, opener) -> list[tuple[str, str]]:
    """Return [(kind, url)] from the sitemap index, labelled per sub-sitemap."""
    index = site.rstrip("/") + "/wp-sitemap.xml"
    status, body, headers, _ = cap.fetch(index, opener)
    if status != 200:
        raise RuntimeError(f"{index} returned HTTP {status}")

    children = [m.group(1).decode("utf-8", "replace").strip() for m in cap.LOC_RE.finditer(body)]
    if not cap.SITEMAPINDEX_RE.search(body):
        # A flat sitemap: no per-type labelling available.
        return [("unknown", u) for u in children]

    out: list[tuple[str, str]] = []
    for child in children:
        kind = child_kind(child)
        try:
            cstatus, cbody, _, _ = cap.fetch(child, opener)
            if cstatus != 200:
                print(f"  ! {child} returned HTTP {cstatus}", file=sys.stderr)
                continue
            for m in cap.LOC_RE.finditer(cbody):
                out.append((kind, m.group(1).decode("utf-8", "replace").strip()))
        except Exception as exc:  # noqa: BLE001
            print(f"  ! {child} failed: {exc}", file=sys.stderr)
    return out


def looks_auto(title: str, site_name: str) -> bool:
    """Does this look like the theme's automatic 'Page name – Site name' title?"""
    if not title or not site_name:
        return False
    for sep in (" – ", " - ", " — ", " | "):
        if title.endswith(sep + site_name):
            return True
    return False


def main() -> int:
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("--site", required=True)
    ap.add_argument("--out", default="audit.csv")
    ap.add_argument("--site-name", default="Wellspring Health",
                    help="Used to detect automatic titles")
    ap.add_argument("--kind", action="append", default=[],
                    help="Only audit these kinds (repeatable), e.g. --kind page")
    ap.add_argument("--delay", type=float, default=0.3)
    ap.add_argument("--insecure", action="store_true")
    args = ap.parse_args()

    opener = cap.build_opener(args.insecure)

    try:
        pairs = sitemap_urls(args.site, opener)
    except Exception as exc:  # noqa: BLE001
        print(f"FATAL: {exc}", file=sys.stderr)
        return 2

    if args.kind:
        wanted = set(args.kind)
        pairs = [(k, u) for k, u in pairs if k in wanted]

    # De-duplicate slash-insensitively, keeping the first form seen.
    seen: set[str] = set()
    ordered: list[tuple[str, str]] = []
    for kind, url in pairs:
        key = cap.url_key(url)
        if key in seen:
            continue
        seen.add(key)
        ordered.append((kind, url))

    print(f"Auditing {len(ordered)} URL(s) on {args.site}\n")

    rows: list[dict[str, Any]] = []
    failures = 0

    for i, (kind, url) in enumerate(ordered, 1):
        path = urllib.parse.urlsplit(url).path or "/"
        row: dict[str, Any] = {
            "kind": kind, "path": path, "url": url, "status": "",
            "title": "", "title_len": "", "title_is_auto": "",
            "description": "", "desc_len": "",
            "canonical": "", "noindex": "", "flags": "",
        }
        try:
            status, body, headers, _ = cap.fetch(url, opener)
            charset = cap.detect_charset(body, headers)
            row["status"] = status

            tm = cap.TITLE_RE.search(body)
            if tm:
                title = cap.collapse_ws(cap.html.unescape(tm.group(1).decode(charset, "replace")))
                row["title"] = title
                row["title_len"] = len(title)
                row["title_is_auto"] = "yes" if looks_auto(title, args.site_name) else "no"

            dm = cap.find_meta_description(body)
            if dm is not None:
                desc = cap.collapse_ws(cap.html.unescape(dm.decode(charset, "replace")))
                row["description"] = desc
                row["desc_len"] = len(desc)

            cm = cap.CANONICAL_RE.search(body)
            if cm:
                href = cap.attr_value(cm.group(0), cap.HREF_RE)
                row["canonical"] = href.decode(charset, "replace") if href else ""

            rm = ROBOTS_RE.search(body)
            if rm:
                content = cap.attr_value(rm.group(0), cap.CONTENT_RE)
                robots = content.decode(charset, "replace").lower() if content else ""
                row["noindex"] = "yes" if "noindex" in robots else "no"
            else:
                row["noindex"] = "no"

        except Exception as exc:  # noqa: BLE001
            row["status"] = "ERROR"
            row["flags"] = f"{type(exc).__name__}: {exc}"
            failures += 1

        rows.append(row)
        state = row["title"] or row["flags"] or "NO TITLE"
        print(f"  [{i}/{len(ordered)}] {kind:12} {path[:44]:44} {str(state)[:40]}")
        if args.delay:
            time.sleep(args.delay)

    # ------------------------------------------------------------------ flags
    title_counts = Counter(r["title"] for r in rows if r["title"])
    desc_counts = Counter(r["description"] for r in rows if r["description"])

    for r in rows:
        flags = [r["flags"]] if r["flags"] else []
        if r["status"] not in (200, "ERROR"):
            flags.append(f"http-{r['status']}")
        if not r["title"]:
            flags.append("no-title")
        if r["title_is_auto"] == "yes":
            flags.append("auto-title")
        if isinstance(r["title_len"], int) and r["title_len"] > 60:
            flags.append("title-over-60")
        if not r["description"]:
            flags.append("no-description")
        elif isinstance(r["desc_len"], int):
            if r["desc_len"] > 160:
                flags.append("desc-over-160")
            elif r["desc_len"] < 70:
                flags.append("desc-under-70")
        if r["title"] and title_counts[r["title"]] > 1:
            flags.append("duplicate-title")
        if r["description"] and desc_counts[r["description"]] > 1:
            flags.append("duplicate-description")
        if r["noindex"] == "yes":
            flags.append("noindex")
        r["flags"] = " ".join(flags)

    cols = ["kind", "path", "url", "status", "title", "title_len", "title_is_auto",
            "description", "desc_len", "canonical", "noindex", "flags"]
    with open(args.out, "w", encoding="utf-8-sig", newline="") as fh:
        w = csv.DictWriter(fh, fieldnames=cols)
        w.writeheader()
        for r in rows:
            w.writerow({c: r.get(c, "") for c in cols})

    # ---------------------------------------------------------------- summary
    print("\n" + "=" * 72)
    print("SUMMARY")
    print("=" * 72)

    by_kind: dict[str, int] = defaultdict(int)
    for r in rows:
        by_kind[r["kind"]] += 1
    for k, n in sorted(by_kind.items()):
        print(f"  {n:4}  {k}")

    tally = Counter()
    for r in rows:
        for f in r["flags"].split():
            tally[f] += 1

    print()
    if tally:
        print("  Needs attention:")
        for f, n in tally.most_common():
            print(f"    {n:4}  {f}")
    else:
        print("  Nothing flagged.")

    written = sum(1 for r in rows if r["title"] and r["title_is_auto"] == "no")
    print(f"\n  {written}/{len(rows)} URLs have a hand-written title.")
    print(f"  {sum(1 for r in rows if r['description'])}/{len(rows)} have a meta description.")
    print(f"\nWrote {args.out}")

    return 1 if failures or any(not r["title"] for r in rows) else 0


if __name__ == "__main__":
    sys.exit(main())
