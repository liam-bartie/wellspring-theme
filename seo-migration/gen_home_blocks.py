#!/usr/bin/env python3
"""
Generate inc/home-blocks.php from the assignments already in front-page.php.

The point is that the long default strings are never retyped: the ws_field()
expressions are lifted verbatim out of front-page.php, so the migration writes
exactly what front-page.php would have rendered.

Run from the theme root.
"""
import re
import sys

FP = "front-page.php"
OUT = "inc/home-blocks.php"

src = open(FP, encoding="utf-8").read()

# variable name -> (block, sub-field key)
MAP = [
    ("intro_eyebrow",      "intro",        "eyebrow"),
    ("intro_title",        "intro",        "title"),
    ("intro_body",         "intro",        "body"),
    ("wwt_eyebrow",        "wwt",          "eyebrow"),
    ("wwt_title",          "wwt",          "title"),
    ("wwt_lede",           "wwt",          "lede"),
    ("pract_eyebrow",      "practitioner", "eyebrow"),
    ("pract_name",         "practitioner", "name"),
    ("pract_credentials",  "practitioner", "credentials"),
    ("pract_bio",          "practitioner", "bio"),
    ("pract_link_label",   "practitioner", "link_label"),
    ("pract_link_url",     "practitioner", "link_url"),
    ("pract_portrait",     "practitioner", "portrait"),
    ("mod_eyebrow",        "modalities",   "eyebrow"),
    ("mod_title",          "modalities",   "title"),
    ("tcm_title",          "modalities",   "tcm_title"),
    ("tcm_body",           "modalities",   "tcm_body"),
    ("tcm_image",          "modalities",   "tcm_image"),
    ("acu_title",          "modalities",   "acu_title"),
    ("acu_body",           "modalities",   "acu_body"),
    ("acu_image",          "modalities",   "acu_image"),
    ("cases_eyebrow",      "cases",        "eyebrow"),
    ("cases_title",        "cases",        "title"),
    ("cases_lede",         "cases",        "lede"),
]

# Pull the right-hand side of each assignment, verbatim, up to the line-ending ";".
exprs = {}
for var, _, _ in MAP:
    m = re.search(
        r"^\$" + re.escape(var) + r"\s*=\s*(.+?);\s*$",
        src,
        re.M | re.S,
    )
    if not m:
        sys.exit(f"FATAL: could not find the assignment for ${var} in {FP}")
    rhs = m.group(1).strip()
    if "\n" in rhs:
        sys.exit(f"FATAL: ${var} spans multiple lines; refusing to guess")

    # Bind every read to the front page explicitly.
    #
    # front-page.php could rely on "the current post", because it only ever runs
    # on the home page. The migration runs on a Tools screen where there is no
    # current post, so an unbound read returns null and the default is written
    # instead of the saved value. That is a silent, content-shaped failure, so
    # the post id is made explicit here rather than left to context.
    if rhs.startswith("ws_field("):
        assert rhs.endswith(")"), f"unexpected ws_field shape for ${var}"
        rhs = rhs[:-1].rstrip() + ", $home_id )"
    rhs = re.sub(r"get_field\( '([a-z_]+)' \)", r"get_field( '\1', $home_id )", rhs)

    exprs[var] = rhs

order = ["intro", "wwt", "practitioner", "modalities", "cases"]
blocks = {b: [] for b in order}
for var, block, key in MAP:
    blocks[block].append((key, exprs[var]))

width = max(len(k) for b in blocks.values() for k, _ in b)

body = ""
for b in order:
    body += f"\t\t'{b}' => array(\n"
    for key, expr in blocks[b]:
        body += f"\t\t\t'{key}'{' ' * (width - len(key))} => {expr},\n"
    body += "\t\t),\n"

php = f'''<?php
/**
 * Resolved values for the home page's content blocks.
 *
 * ONE source of truth, used by two callers:
 *
 *   - front-page.php, to render the blocks from the page's top-level fields
 *   - inc/home-sections-import.php, to write those same values into
 *     "Page sections" rows
 *
 * That matters because of ws_field(): where a field has never been saved it
 * falls back to a hardcoded default, and the live page renders the default. A
 * migration that read the raw meta instead would write empty rows and blocks
 * would vanish. Reading through the same function means the migration writes
 * precisely what the page was already showing.
 *
 * GENERATED from front-page.php by seo-migration/gen_home_blocks.py — the
 * expressions below were lifted verbatim rather than retyped, so the default
 * strings cannot drift. If you change a default, change it here.
 *
 * @package Wellspring
 */

/**
 * @return array Block key => array of sub-field key => resolved value.
 */
function wellspring_home_block_values() {{
	static $cache = null;
	if ( null !== $cache ) {{
		return $cache;
	}}

	// Bound to the front page rather than "the current post", so this returns
	// the same values whether it is called while rendering the home page or
	// from the migration screen in wp-admin.
	$home_id = (int) get_option( 'page_on_front' );

	$cache = array(
{body}	);

	return $cache;
}}

/**
 * The featured clinic cases shown on the home page, as WP_Post objects.
 *
 * Kept separate from the text values above because it is a query, not a field
 * read, and front-page.php resolves it before the blocks are rendered.
 *
 * @return array WP_Post objects.
 */
function wellspring_home_featured_case_ids() {{
	$home_id = (int) get_option( 'page_on_front' );
	$ids     = function_exists( 'get_field' ) ? get_field( 'cases_featured', $home_id ) : array();

	if ( empty( $ids ) || ! is_array( $ids ) ) {{
		return array();
	}}

	// The relationship field can return objects or IDs depending on config.
	return array_values(
		array_filter(
			array_map(
				function ( $item ) {{
					if ( is_object( $item ) && isset( $item->ID ) ) {{
						return (int) $item->ID;
					}}
					return (int) $item;
				}},
				$ids
			)
		)
	);
}}
'''

open(OUT, "w", encoding="utf-8").write(php)
print(f"wrote {OUT}")
for b in order:
    print(f"  {b}: {len(blocks[b])} values")
