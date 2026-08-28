#!/usr/bin/env python3
"""
fetch_case_images.py — download, rename, resize and optimise the clinic-case
images from the old GoDaddy site, ready to upload to WordPress.

Run this on your own machine: the sandbox this was written in cannot reach
wellspringhealth.ca or img1.wsimg.com.

    pip install Pillow
    python3 fetch_case_images.py --sheet sheet.csv --out ./case-images

WHY A SHEET AND NOT A CRAWL
    The old /clinic-cases page injects its images with JavaScript and lazy-loads
    them, so a plain HTTP fetch of that page returns only 4 of the ~28 image
    URLs. Parsing the HTML is therefore not an option. Two ways round it:

      --sheet sheet.csv     Read the URLs from a column in your sheet (preferred).
      --render              Drive a real browser to force the lazy-load, then
                            scrape the URLs. Needs `pip install playwright`
                            and `playwright install chromium`.

FULL-SIZE ORIGINALS
    The page serves a 600x300 crop. Stripping the transform suffix from a
    wsimg.com URL — everything from "/:/" onward — returns the untouched
    original, which for the ones checked is 1580x1008. Do NOT ask the CDN for
    a bigger size (e.g. /:/rs=w:1920): it happily UPSCALES and hands back a
    1920x1225 file with no extra real detail, just a bigger download.

SIZES THIS THEME ACTUALLY USES
    wellspring-hero    1920x800  hard crop   single case page hero
    medium_large        768 wide             the case cards
    Upload one image per case at the largest genuine size and let WordPress
    generate the rest. A source under 1920 wide means WordPress will not
    generate wellspring-hero at all and the hero will use the full-size file
    instead — slightly soft, not broken. The report flags every such image.

OUTPUT
    <out>/full/<slug>.jpg      largest genuine size, optimised  <- upload this
    <out>/full/<slug>.webp     same, WebP, for reference
    <out>/report.csv           filename, dimensions, bytes, alt text, warnings
"""

from __future__ import annotations

import argparse
import csv
import io
import os
import re
import sys
import urllib.parse
import urllib.request

try:
    from PIL import Image
except ImportError:
    sys.stderr.write("FATAL: Pillow is required.  pip install Pillow\n")
    raise SystemExit(2)

UA = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 " \
     "(KHTML, like Gecko) Chrome/120.0 Safari/537.36"

# Alt text, written per case rather than copied from the old site.
#
# The old alt attributes are not reusable. Several say a treatment "cures" or
# "treats" a named condition — "Acupuncture cures flu and cold", "Acupuncture
# and Chinese medicine cure seasonal allergy". For a regulated health practice
# that is a claims problem before it is an SEO one, and two of them are also
# misspelled ("asthema", "Acupunccture"). These describe the picture and the
# clinical context without asserting an outcome.
ALT_TEXT: dict[str, str] = {
    "tmj-jaw-pain-and-night-time-teeth-grinding-eased":
        "Acupuncture needles placed around the jaw to ease TMJ pain and night-time teeth grinding",
    "extremely-heavy-periods-brought-back-to-normal":
        "Acupuncture and Chinese herbal medicine used in the care of heavy menstrual bleeding",
    "frequent-and-urgent-urination-resolved":
        "Acupuncture treatment for frequent and urgent urination at a Calgary TCM clinic",
    "swelling-and-pain-after-knee-surgery-fully-recovered":
        "Acupuncture applied to a knee to reduce swelling and pain after surgery",
    "waking-through-the-night-to-urinate-restored-deep-sleep":
        "Acupuncture and Chinese herbs used for night-time waking and disturbed sleep",
    "severe-insomnia-from-barely-sleeping-to-full-nights":
        "Acupuncture and ear seeds used in the care of severe insomnia",
    "twenty-years-of-asthma-off-the-inhaler":
        "Acupuncture and Chinese herbal medicine used for long-standing asthma and breathing difficulty",
    "heel-pain-from-a-bone-spur-back-on-his-feet":
        "Acupuncture and scalp acupuncture for heel pain caused by a bone spur",
    "whole-body-tightness-and-pain-from-scoliosis-relieved":
        "Acupuncture and scalp acupuncture for whole-body tightness and pain from scoliosis",
    "knee-pain-from-a-bone-spur-under-the-kneecap-resolved":
        "Acupuncture needles at the knee for pain from a bone spur under the kneecap",
    "frozen-shoulder-pain-from-aging-resolved":
        "Acupuncture treatment for frozen shoulder pain and restricted movement",
    "a-blocked-nose-and-lost-sense-of-smell-and-taste-restored":
        "Acupuncture and Chinese herbs used for a blocked nose and loss of smell and taste",
    "migraines-and-chinook-headaches-stopped-at-the-needle":
        "Acupuncture for migraines and Calgary Chinook headaches",
    "neck-shoulder-and-back-pain-after-a-car-accident-resolved":
        "Acupuncture for neck, shoulder and back pain following a car accident",
    "sleep-apnea-and-severe-snoring-machine-free-nights":
        "Acupuncture and Chinese herbal medicine used for sleep apnea and heavy snoring",
    "quitting-smoking-after-decades-with-tcm-support":
        "Ear seeds and acupuncture used to support quitting smoking",
    "seasonal-allergies-and-morning-sneezing-cleared":
        "Acupuncture and Chinese herbs used for seasonal allergies and morning sneezing",
    "a-fast-recovery-from-the-flu-without-the-usual-long-illness":
        "Acupuncture used during recovery from influenza at a Calgary TCM clinic",
    "high-blood-pressure-that-medication-couldnt-control-brought-down":
        "Acupuncture, ear seeds and Chinese herbs used in the care of high blood pressure",
    "dangerously-high-blood-pressure-without-medication-returned-to-normal":
        "Acupuncture and Chinese herbal medicine used for very high blood pressure",
    "lifelong-anxiety-and-insomnia-eased-with-herbs":
        "Chinese herbal medicine and acupuncture used for long-standing anxiety and insomnia",
    "twenty-years-of-depression-resolved-and-off-medication":
        "Acupuncture and Chinese herbs used in the care of long-term depression and fatigue",
    "raynauds-syndrome-cold-blue-hands-and-feet-warmed":
        "Acupuncture and Chinese herbs used for Raynaud's syndrome and poor circulation in the hands",
    "cosmetic-acupuncture-age-spots-gone-and-wrinkles-softened":
        "Cosmetic acupuncture applied to the face for age spots and fine lines",
    "sharp-pain-under-the-shoulder-blade-and-flank-relieved":
        "Acupuncture for sharp pain under the shoulder blade and along the flank",
    "shingles-blisters-and-severe-pain-cleared":
        "Acupuncture and Chinese herbal medicine used for shingles blisters and nerve pain",
    # Two cases on the live site were added after the seed file and are untagged.
    "heart-palpitations-and-severe-sweating-returned-to-a-normal-rhythm":
        "Acupuncture used in the care of heart palpitations and excessive sweating",
    "stress-driven-neck-and-shoulder-pain-resolved-in-one-treatment-course":
        "Acupuncture for stress-related neck and shoulder tension",
}


# Image Order in the sheet -> the clinic-case slug on the new site.
#
# Verified rather than assumed: the sheet's Image Order 1-28 matches the order
# the images appear in on the old /clinic-cases page, checked against the alt
# text of all 28. That is what lets us match by position instead of trying to
# fuzzy-match "TMJ Disorders" against a slug.
ORDER_TO_SLUG: dict[int, str] = {
    1: "tmj-jaw-pain-and-night-time-teeth-grinding-eased",
    2: "extremely-heavy-periods-brought-back-to-normal",
    3: "stress-driven-neck-and-shoulder-pain-resolved-in-one-treatment-course",
    4: "heart-palpitations-and-severe-sweating-returned-to-a-normal-rhythm",
    5: "frequent-and-urgent-urination-resolved",
    6: "swelling-and-pain-after-knee-surgery-fully-recovered",
    7: "waking-through-the-night-to-urinate-restored-deep-sleep",
    8: "severe-insomnia-from-barely-sleeping-to-full-nights",
    9: "twenty-years-of-asthma-off-the-inhaler",
    10: "heel-pain-from-a-bone-spur-back-on-his-feet",
    11: "whole-body-tightness-and-pain-from-scoliosis-relieved",
    12: "knee-pain-from-a-bone-spur-under-the-kneecap-resolved",
    13: "frozen-shoulder-pain-from-aging-resolved",
    14: "a-blocked-nose-and-lost-sense-of-smell-and-taste-restored",
    15: "migraines-and-chinook-headaches-stopped-at-the-needle",
    16: "neck-shoulder-and-back-pain-after-a-car-accident-resolved",
    17: "sleep-apnea-and-severe-snoring-machine-free-nights",
    18: "quitting-smoking-after-decades-with-tcm-support",
    19: "seasonal-allergies-and-morning-sneezing-cleared",
    20: "a-fast-recovery-from-the-flu-without-the-usual-long-illness",
    21: "high-blood-pressure-that-medication-couldnt-control-brought-down",
    22: "dangerously-high-blood-pressure-without-medication-returned-to-normal",
    23: "lifelong-anxiety-and-insomnia-eased-with-herbs",
    24: "twenty-years-of-depression-resolved-and-off-medication",
    25: "raynauds-syndrome-cold-blue-hands-and-feet-warmed",
    26: "cosmetic-acupuncture-age-spots-gone-and-wrinkles-softened",
    27: "sharp-pain-under-the-shoulder-blade-and-flank-relieved",
    28: "shingles-blisters-and-severe-pain-cleared",
}

def original_url(url: str) -> str:
    """Strip a wsimg.com transform suffix to get the untouched original."""
    url = url.strip()
    if url.startswith("//"):
        url = "https:" + url
    cut = url.find("/:/")
    return url[:cut] if cut > -1 else url


def slugify(text: str) -> str:
    text = re.sub(r"[^\w\s-]", "", (text or "").lower()).strip()
    text = re.sub(r"[\s_]+", "-", text)
    return re.sub(r"-{2,}", "-", text).strip("-")


def fetch(url: str, timeout: int = 60) -> bytes:
    req = urllib.request.Request(url, headers={"User-Agent": UA, "Accept": "image/*,*/*"})
    with urllib.request.urlopen(req, timeout=timeout) as resp:
        return resp.read()


def read_sheet(path: str) -> list[dict[str, object]]:
    """
    Read the tracking sheet and keep only the approved "Use Existing" rows.

    This sheet's columns are: Approved, Image Order, Clinic Case, Option 1..3.
    Approved TRUE and Option 1 "Use Existing" agree on every row — the FALSE
    rows all carry an Adobe Stock URL as a proposed replacement instead, which
    is licensed stock and deliberately NOT downloaded here.
    """
    with open(path, newline="", encoding="utf-8-sig") as fh:
        rows = list(csv.DictReader(fh))
    if not rows:
        raise SystemExit("Sheet has no rows.")

    kept, skipped, mismatched = [], [], []
    for r in rows:
        approved = (r.get("Approved") or "").strip().upper() == "TRUE"
        opt1 = (r.get("Option 1") or "").strip()
        use_existing = "use existing" in opt1.lower()

        if approved != use_existing:
            mismatched.append(r)

        if not (approved and use_existing):
            skipped.append(r)
            continue

        try:
            order = int(str(r.get("Image Order") or "").strip())
        except ValueError:
            mismatched.append(r)
            continue

        slug = ORDER_TO_SLUG.get(order)
        if not slug:
            mismatched.append(r)
            continue

        kept.append({
            "order": order,
            "slug": slug,
            "case_name": (r.get("Clinic Case") or "").strip(),
        })

    print(f"  {len(kept)} approved 'Use Existing'")
    print(f"  {len(skipped)} skipped (not approved, or a stock replacement proposed)")
    if mismatched:
        print(f"  ! {len(mismatched)} row(s) where Approved and Option 1 disagree, or the")
        print("    Image Order is missing/unknown — check these by hand:")
        for r in mismatched:
            print(f"      {r.get('Image Order')!r} {r.get('Clinic Case')!r} "
                  f"Approved={r.get('Approved')!r} Option1={(r.get('Option 1') or '')[:40]!r}")

    return sorted(kept, key=lambda k: k["order"])


def render_page_images(page_url: str, headed: bool = False) -> list[str]:
    """
    Return the case-image originals in the order they appear on the page.

    The old page injects its images with JavaScript and lazy-loads them, so a
    plain HTTP fetch returns only 4 of the 28. A real browser is the only way.
    Each image is scrolled into view to trip the IntersectionObserver — and the
    browser must be VISIBLE, because a hidden or backgrounded tab never fires it.
    """
    try:
        from playwright.sync_api import sync_playwright
    except ImportError:
        raise SystemExit(
            "Rendering needs Playwright:\n"
            "  pip install playwright && playwright install chromium"
        )

    urls: list[str] = []
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=not headed)
        page = browser.new_page(viewport={"width": 1440, "height": 1000})

        # NOT wait_until="networkidle": this page runs analytics and a service
        # worker that keep the network busy forever, so networkidle never fires
        # and the script hangs with no output. Wait for the DOM, then settle.
        print("  loading page ...")
        page.goto(page_url, wait_until="domcontentloaded", timeout=60_000)
        page.wait_for_timeout(4000)

        n = page.evaluate("document.querySelectorAll('img').length")
        print(f"  {n} img elements; scrolling each into view to trip the lazy-load")
        for i in range(n):
            page.evaluate(
                "i => { const el = document.querySelectorAll('img')[i];"
                " if (el) el.scrollIntoView({block:'center'}); }", i
            )
            page.wait_for_timeout(220)
            if (i + 1) % 5 == 0:
                loaded = page.evaluate(
                    """() => Array.from(document.querySelectorAll('img'))
                        .filter(x => (x.currentSrc||'').indexOf('img1.wsimg.com') > -1
                                  && (x.currentSrc||'').indexOf('transparent_placeholder') === -1).length"""
                )
                print(f"    {i + 1}/{n} scrolled, {loaded} real images so far")
        print("  settling ...")
        page.wait_for_timeout(4000)

        raw = page.evaluate("""() => Array.from(document.querySelectorAll('img'))
            .map(im => ({ src: im.currentSrc || im.src || '', alt: im.alt || '' }))""")
        browser.close()

    placeholders = 0
    for r in raw:
        src = r["src"]
        if "img1.wsimg.com" not in src:
            continue
        if "transparent_placeholder" in src:
            placeholders += 1
            urls.append("")          # keep the position so indexing stays honest
            continue
        urls.append(original_url(src))

    # The first two are the site logo, not case images.
    while urls and urls[0] and "Screenshot%202025-11-16" in urls[0]:
        urls.pop(0)

    if placeholders:
        print(f"  ! {placeholders} image(s) never loaded — re-run with --headed, "
              "and keep the browser window in the foreground")
    print(f"  {len([u for u in urls if u])} image URL(s) resolved, in page order")
    return urls


def main() -> int:
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("--sheet", required=True, help="CSV export of the tracking sheet")
    ap.add_argument("--page", default="https://wellspringhealth.ca/clinic-cases",
                    help="Old page holding the images")
    ap.add_argument("--local-dir", metavar="DIR",
                    help="Skip the browser entirely. Point this at a folder of images you "
                         "saved by hand, each named by its Image Order from the sheet "
                         "(1.png, 3.jpg, 13.png ...). Extension does not matter.")
    ap.add_argument("--out", default="./case-images")
    ap.add_argument("--headed", action="store_true",
                    help="Show the browser. Use this if images come back unloaded — a "
                         "hidden tab never fires the lazy-load observer.")
    ap.add_argument("--jpeg-quality", type=int, default=82)
    ap.add_argument("--webp-quality", type=int, default=80)
    ap.add_argument("--min-width", type=int, default=1920,
                    help="Warn below this; the theme hero wants 1920 wide")
    ap.add_argument("--dry-run", action="store_true",
                    help="Show the plan and the resolved URLs, download nothing")
    args = ap.parse_args()

    print(f"Reading {args.sheet}")
    wanted = read_sheet(args.sheet)
    if not wanted:
        print("Nothing approved to download.")
        return 0

    local_files: dict[int, str] = {}
    page_urls: list[str] = []

    if args.local_dir:
        # Files named by Image Order — no browser, no Playwright.
        for name in os.listdir(args.local_dir):
            stem = os.path.splitext(name)[0].strip()
            if stem.isdigit():
                local_files[int(stem)] = os.path.join(args.local_dir, name)
        print(f"\nUsing local files from {args.local_dir}: "
              f"{len(local_files)} named by Image Order")
        missing = [int(w["order"]) for w in wanted if int(w["order"]) not in local_files]
        if missing:
            print(f"  ! no file for Image Order: {missing}")
    else:
        print(f"\nRendering {args.page} to force the lazy-load ...")
        page_urls = render_page_images(args.page, headed=args.headed)

    os.makedirs(os.path.join(args.out, "full"), exist_ok=True)
    full_dir = os.path.join(args.out, "full")

    report: list[dict[str, object]] = []
    failures = 0

    print()
    for item in wanted:
        order = int(item["order"])
        slug = str(item["slug"])
        idx = order - 1          # Image Order is 1-based, page order is 0-based
        local = local_files.get(order, "")
        url = local or (page_urls[idx] if 0 <= idx < len(page_urls) else "")

        row: dict[str, object] = {
            "order": order, "case_name": item["case_name"], "slug": slug,
            "filename": "", "width": "", "height": "", "kb": "",
            "alt_text": ALT_TEXT.get(slug, ""), "source_url": url, "warnings": "",
        }
        warn: list[str] = []
        if not ALT_TEXT.get(slug):
            warn.append("no-alt-text-written-for-this-slug")

        if not url:
            warn.append("image-not-resolved-on-page")
            row["warnings"] = " ".join(warn)
            report.append(row)
            failures += 1
            print(f"  [{order:>2}] {slug[:48]:48} NOT RESOLVED")
            continue

        if args.dry_run:
            print(f"  [{order:>2}] {slug[:48]:48} would fetch {url[-46:]}")
            report.append(row)
            continue

        try:
            if local:
                with open(local, "rb") as fh:
                    raw = fh.read()
            else:
                raw = fetch(url)
            im = Image.open(io.BytesIO(raw))
            im.load()
            w, h = im.size
            if w < args.min_width:
                warn.append(f"under-{args.min_width}px-wide-case-hero-will-be-soft")

            if im.mode in ("RGBA", "LA", "P"):
                flat = Image.new("RGB", im.size, (255, 255, 255))
                rgba = im.convert("RGBA")
                flat.paste(rgba, mask=rgba.split()[-1])
                im = flat
            else:
                im = im.convert("RGB")

            jpg = os.path.join(full_dir, f"{slug}.jpg")
            im.save(jpg, "JPEG", quality=args.jpeg_quality, optimize=True, progressive=True)
            im.save(os.path.join(full_dir, f"{slug}.webp"), "WEBP",
                    quality=args.webp_quality, method=6)

            kb = round(os.path.getsize(jpg) / 1024)
            row.update({"filename": f"{slug}.jpg", "width": w, "height": h, "kb": kb})
            print(f"  [{order:>2}] {slug[:48]:48} {w}x{h}  {kb} KB"
                  + ("  ! " + " ".join(warn) if warn else ""))
        except Exception as exc:  # noqa: BLE001
            warn.append(f"{type(exc).__name__}: {exc}")
            failures += 1
            print(f"  [{order:>2}] {slug[:48]:48} FAILED  {exc}")

        row["warnings"] = " ".join(warn)
        report.append(row)

    cols = ["order", "case_name", "slug", "filename", "width", "height", "kb",
            "alt_text", "source_url", "warnings"]
    rp = os.path.join(args.out, "report.csv")
    with open(rp, "w", encoding="utf-8-sig", newline="") as fh:
        wr = csv.DictWriter(fh, fieldnames=cols)
        wr.writeheader()
        for r in report:
            wr.writerow({c: r.get(c, "") for c in cols})

    soft = [r for r in report if "under-" in str(r["warnings"])]
    print("\n" + "=" * 70)
    print(f"  {len(report) - failures}/{len(report)} downloaded")
    if soft:
        print(f"  {len(soft)} under {args.min_width}px wide — the case-detail hero will be soft")
    print(f"\n  Files : {full_dir}")
    print(f"  Report: {rp}")
    print("\n  Upload each .jpg as the Featured Image on its clinic case.")
    print("  WordPress generates the card and hero sizes itself.")
    print("  Copy alt_text from report.csv into the Media library Alt Text field.")
    return 1 if failures else 0


if __name__ == "__main__":
    sys.exit(main())
