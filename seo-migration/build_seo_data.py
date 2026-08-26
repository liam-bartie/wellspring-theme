#!/usr/bin/env python3
"""
build_seo_data.py — turn a capture into the import data file. Standard library only.

Reads dry_run_report.json (or old_meta.json) and emits seo_data.json: the exact
values to write, keyed by what they should be written to. Nothing is retyped —
every string comes from the capture, and every value carries the SHA-256 of the
captured source so the import can prove it wasn't altered in transit.

DEVIATIONS. The migration rule is byte-for-byte, so any departure has to be
deliberate, recorded and reviewable rather than quietly applied. Each one is
written into the file with its before, its after and its reason, and printed at
the end. Right now there is exactly one class: descriptions whose source is
double-encoded (`&amp;#39;` where an apostrophe belongs), which is a GoDaddy
encoding bug currently rendering a literal `&#39;` in the live SERP snippet.

TARGET TYPES matter. /clinic-cases is not a page — it is the clinic_case
post-type archive, so its values belong in the theme's Customizer settings, not
an ACF field on a post. Getting this wrong writes meta to nothing.

Usage:
    python3 build_seo_data.py --report dry_run_report.json --out seo_data.json
    python3 build_seo_data.py --report ... --no-fix-encoding   # strict byte-for-byte
"""

from __future__ import annotations

import argparse
import hashlib
import html
import json
import sys
from typing import Any
from urllib.parse import urlsplit

# old path -> (target_type, target_key)
#   front_page  : the page set as the static front page
#   page        : a WordPress page, by slug (nested slugs allowed)
#   cpt_archive : a post-type archive, by post type
MAPPING: dict[str, tuple[str, str]] = {
    "/":                  ("front_page", ""),
    "/about":             ("page", "about"),
    "/what-we-treat":     ("page", "what-we-treat"),
    "/contact":           ("page", "contact"),
    "/terms-of-service":  ("page", "terms-of-service"),
    "/privacy-policy":    ("page", "privacy-policy"),
    "/cookie-policy":     ("page", "cookie-policy"),
    # Decided 2026-08-26: build these as real pages rather than off-site redirects.
    "/book-appointments": ("page", "book-appointments"),
    "/events":            ("page", "events"),
    # NOT a page — the clinic_case post-type archive.
    "/clinic-cases":      ("cpt_archive", "clinic_case"),
}


def sha(text: str) -> str:
    return hashlib.sha256(text.encode("utf-8")).hexdigest()


def is_double_encoded(value: str) -> bool:
    """True if the value still contains HTML entities after one decode pass."""
    return html.unescape(value) != value


def main() -> int:
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("--report", default="dry_run_report.json",
                    help="dry_run_report.json or old_meta.json")
    ap.add_argument("--out", default="seo_data.json")
    ap.add_argument("--no-fix-encoding", action="store_true",
                    help="Strict byte-for-byte: carry double-encoded entities across unchanged")
    args = ap.parse_args()

    with open(args.report, encoding="utf-8") as fh:
        payload = json.load(fh)

    pages = payload.get("old") or payload.get("pages")
    if not pages:
        print("FATAL: no capture found (expected an 'old' or 'pages' key).", file=sys.stderr)
        return 2

    entries: list[dict[str, Any]] = []
    deviations: list[dict[str, Any]] = []
    unmapped: list[str] = []

    for url, rec in sorted(pages.items()):
        path = urlsplit(url).path or "/"
        path = "/" if path == "/" else "/" + path.strip("/")

        if path not in MAPPING:
            unmapped.append(path)
            continue

        target_type, target_key = MAPPING[path]
        title = rec.get("title")
        desc = rec.get("description")

        entry: dict[str, Any] = {
            "source_url": url,
            "source_path": path,
            "target_type": target_type,
            "target_key": target_key,
            "title": title,
            "title_sha": rec.get("title_sha"),
            "description": desc,
            "description_sha": rec.get("description_sha"),
            "notes": [],
        }

        if not title:
            entry["notes"].append("Old page served no <title> — nothing to port.")
        if not desc:
            entry["notes"].append("Old page served no meta description — nothing to port.")

        # --- the one approved deviation
        if desc and is_double_encoded(desc):
            fixed = html.unescape(desc)
            if args.no_fix_encoding:
                entry["notes"].append(
                    f"Source is double-encoded; carried across unchanged (would render "
                    f"literally). Fixed form would be: {fixed!r}"
                )
            else:
                entry["description"] = fixed
                entry["description_sha"] = sha(fixed)
                entry["description_source_sha"] = rec.get("description_sha")
                entry["deviation"] = "encoding_fix"
                deviations.append({
                    "path": path,
                    "field": "description",
                    "before": desc,
                    "after": fixed,
                    "reason": (
                        "Source was double-encoded (&amp;#39;), a GoDaddy artifact that "
                        "renders a literal &#39; in the SERP snippet. Approved as a "
                        "platform-artifact exception, same basis as a CMS-appended suffix."
                    ),
                })

        # Length reporting only — never a reason to shorten.
        if title:
            entry["title_len"] = len(title)
        if entry["description"]:
            entry["description_len"] = len(entry["description"])

        entries.append(entry)

    out = {
        "_meta": {
            "generated_from": args.report,
            "captured_utc": (payload.get("_meta") or {}).get("captured_utc")
                            or (payload.get("_meta") or {}).get("generated_utc"),
            "encoding_fix_applied": not args.no_fix_encoding,
            "rule": (
                "Values are byte-for-byte copies of the old site EXCEPT where a "
                "'deviation' key is present. Never hand-edit this file — regenerate it."
            ),
            "deviations": deviations,
            "unmapped_source_paths": unmapped,
        },
        "entries": entries,
    }

    with open(args.out, "w", encoding="utf-8") as fh:
        json.dump(out, fh, indent=2, ensure_ascii=False, sort_keys=True)

    # ------------------------------------------------------------------ report
    print(f"{'SOURCE':22} {'TARGET':34} {'T':>4} {'D':>4}  NOTES")
    print("-" * 100)
    for e in entries:
        target = (
            "front page" if e["target_type"] == "front_page"
            else f"archive:{e['target_key']}" if e["target_type"] == "cpt_archive"
            else f"page:{e['target_key']}"
        )
        flag = " *DEVIATION*" if e.get("deviation") else ""
        notes = "; ".join(e["notes"])[:34]
        print(f"{e['source_path']:22} {target:34} {e.get('title_len', 0):>4} "
              f"{e.get('description_len', 0):>4}  {notes}{flag}")

    if unmapped:
        print(f"\n! {len(unmapped)} captured path(s) not in MAPPING (nothing will be written):")
        for p in unmapped:
            print(f"    {p}")

    print(f"\nDeviations from byte-for-byte: {len(deviations)}")
    for d in deviations:
        print(f"\n  {d['path']} ({d['field']})")
        print(f"    before: {d['before']!r}")
        print(f"    after : {d['after']!r}")

    print(f"\nWrote {args.out} — {len(entries)} entries")
    return 0


if __name__ == "__main__":
    sys.exit(main())
