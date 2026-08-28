#!/usr/bin/env bash
#
# import-case-images.sh — attach the migrated clinic-case images to their
# clinic_case posts, with alt text, using WP-CLI.
#
# Run from the WordPress root (the folder holding wp-config.php):
#
#   ./import-case-images.sh            # DRY RUN: prints what it would do, writes nothing
#   ./import-case-images.sh --apply    # actually imports
#
# Safe to re-run: a case that already has a featured image is skipped, never
# replaced. Nothing is ever deleted. Exits non-zero if any case failed.
#
# Portability note: deliberately avoids process substitution and bash
# associative arrays — neither is available on some shared hosts.
#
set -uo pipefail

APPLY=0
[ "${1:-}" = "--apply" ] && APPLY=1

HERE="$(cd "$(dirname "$0")" && pwd)"
MANIFEST="$HERE/manifest.tsv"
IMGDIR="$HERE/full"

command -v wp  >/dev/null 2>&1 || { echo "FATAL: wp (WP-CLI) not found on PATH" >&2; exit 2; }
command -v awk >/dev/null 2>&1 || { echo "FATAL: awk not found on PATH" >&2; exit 2; }
[ -f wp-config.php ] || { echo "FATAL: run this from the WordPress root (no wp-config.php here)" >&2; exit 2; }
[ -f "$MANIFEST" ]   || { echo "FATAL: manifest not found: $MANIFEST" >&2; exit 2; }
[ -d "$IMGDIR" ]     || { echo "FATAL: image folder not found: $IMGDIR" >&2; exit 2; }

CSV="$(mktemp)" || { echo "FATAL: cannot create a temp file" >&2; exit 2; }
trap 'rm -f "$CSV"' EXIT

echo "WordPress : $(pwd)"
echo "Site      : $(wp option get siteurl 2>/dev/null)"
echo "Manifest  : $MANIFEST"
echo "Images    : $IMGDIR"
if [ "$APPLY" -eq 1 ]; then echo "Mode      : APPLY (will write)"; else echo "Mode      : DRY RUN (writes nothing)"; fi
echo

# ---- slug -> post ID lookup table, fetched once into a temp file -----------
if ! wp post list --post_type=clinic_case --post_status=any \
       --posts_per_page=-1 --fields=ID,post_name --format=csv > "$CSV"; then
  echo "FATAL: 'wp post list' failed (see the error above)" >&2
  exit 2
fi

found=$(( $(grep -c '' "$CSV") - 1 ))
echo "Found $found clinic_case post(s)."
if [ "$found" -lt 1 ]; then
  echo "FATAL: no clinic_case posts returned - wrong site directory?" >&2
  exit 2
fi
echo

ok=0; skipped=0; failed=0
printf '%-58s %-7s %s\n' "SLUG" "POST" "ACTION"
printf '%-58s %-7s %s\n' "----" "----" "------"

while IFS="$(printf '\t')" read -r slug file alt; do
  case "$slug" in ''|'#'*) continue ;; esac

  id="$(awk -F, -v s="$slug" 'NR>1 && $2==s { print $1; exit }' "$CSV")"
  path="$IMGDIR/$file"

  if [ -z "$id" ]; then
    printf '%-58s %-7s %s\n' "$slug" "-" "FAIL: no clinic_case with this slug"
    failed=$((failed+1)); continue
  fi
  if [ ! -f "$path" ]; then
    printf '%-58s %-7s %s\n' "$slug" "$id" "FAIL: image file missing"
    failed=$((failed+1)); continue
  fi

  existing="$(wp post meta get "$id" _thumbnail_id 2>/dev/null || true)"
  if [ -n "$existing" ]; then
    printf '%-58s %-7s %s\n' "$slug" "$id" "skip: already has featured image ($existing)"
    skipped=$((skipped+1)); continue
  fi

  if [ "$APPLY" -eq 0 ]; then
    printf '%-58s %-7s %s\n' "$slug" "$id" "would import $file + set alt"
    ok=$((ok+1)); continue
  fi

  att="$(wp media import "$path" --post_id="$id" --featured_image --porcelain 2>&1)"
  case "$att" in
    ''|*[!0-9]*)
      printf '%-58s %-7s %s\n' "$slug" "$id" "FAIL: import -> $(echo "$att" | tr '\n' ' ' | cut -c1-70)"
      failed=$((failed+1)); continue ;;
  esac

  if ! wp post meta update "$att" _wp_attachment_image_alt "$alt" >/dev/null 2>&1; then
    printf '%-58s %-7s %s\n' "$slug" "$id" "FAIL: attachment $att imported but alt not set"
    failed=$((failed+1)); continue
  fi

  printf '%-58s %-7s %s\n' "$slug" "$id" "imported att $att + alt set"
  ok=$((ok+1))
done < "$MANIFEST"

echo
echo "done: $ok ok, $skipped skipped, $failed failed"
[ "$APPLY" -eq 0 ] && echo "(dry run - re-run with --apply to write)"
[ "$failed" -gt 0 ] && exit 1
exit 0
