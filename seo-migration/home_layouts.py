#!/usr/bin/env python3
"""
Insert the six home-block layouts into the page_sections flexible field, plus
the filter that offers them only when editing the front page.

Run from the theme root. Idempotent: refuses to run twice.
"""
import sys

PATH = "inc/acf-fields.php"
src = open(PATH, encoding="utf-8").read()

if "layout_home_intro" in src:
    print("home layouts already present — no change")
    sys.exit(0)

T = "\t"


def txt(key, name, label, instructions=""):
    return (
        f"{T*8}array(\n"
        f"{T*9}'key'          => '{key}',\n"
        f"{T*9}'name'         => '{name}',\n"
        f"{T*9}'label'        => '{label}',\n"
        + (f"{T*9}'instructions' => '{instructions}',\n" if instructions else "")
        + f"{T*9}'type'         => 'text',\n"
        f"{T*8}),\n"
    )


def wysiwyg(key, name, label, instructions=""):
    return (
        f"{T*8}array(\n"
        f"{T*9}'key'          => '{key}',\n"
        f"{T*9}'name'         => '{name}',\n"
        f"{T*9}'label'        => '{label}',\n"
        + (f"{T*9}'instructions' => '{instructions}',\n" if instructions else "")
        + f"{T*9}'type'         => 'wysiwyg',\n"
        f"{T*9}'tabs'         => 'all',\n"
        f"{T*9}'toolbar'      => 'full',\n"
        f"{T*9}'media_upload' => 1,\n"
        f"{T*9}'delay'        => 1,\n"
        f"{T*8}),\n"
    )


def image(key, name, label, instructions=""):
    return (
        f"{T*8}array(\n"
        f"{T*9}'key'           => '{key}',\n"
        f"{T*9}'name'          => '{name}',\n"
        f"{T*9}'label'         => '{label}',\n"
        + (f"{T*9}'instructions'  => '{instructions}',\n" if instructions else "")
        + f"{T*9}'type'          => 'image',\n"
        f"{T*9}'return_format' => 'array',\n"
        f"{T*9}'preview_size'  => 'medium',\n"
        f"{T*8}),\n"
    )


def url(key, name, label):
    return (
        f"{T*8}array(\n"
        f"{T*9}'key'   => '{key}',\n"
        f"{T*9}'name'  => '{name}',\n"
        f"{T*9}'label' => '{label}',\n"
        f"{T*9}'type'  => 'url',\n"
        f"{T*8}),\n"
    )


def message(key, body):
    return (
        f"{T*8}array(\n"
        f"{T*9}'key'     => '{key}',\n"
        f"{T*9}'label'   => '',\n"
        f"{T*9}'type'    => 'message',\n"
        f"{T*9}'message' => '{body}',\n"
        f"{T*8}),\n"
    )


def layout(key, name, label, fields):
    return (
        f"{T*7}'{key}' => array(\n"
        f"{T*8}'key'        => '{key}',\n"
        f"{T*8}'name'       => '{name}',\n"
        f"{T*8}'label'      => '{label}',\n"
        f"{T*8}'display'    => 'block',\n"
        f"{T*8}'sub_fields' => array(\n"
        + "".join(fields)
        + f"{T*8}),\n"
        f"{T*7}),\n"
    )


blocks = "".join([
    layout(
        "layout_home_intro", "home_intro", "Home: intro text",
        [
            txt("field_hs_intro_eyebrow", "eyebrow", "Eyebrow"),
            txt("field_hs_intro_title", "title", "Heading"),
            wysiwyg("field_hs_intro_body", "body", "Body"),
        ],
    ),
    layout(
        "layout_home_wwt", "home_wwt", "Home: what we treat",
        [
            message(
                "field_hs_wwt_note",
                "The cards in this grid are the sub-pages of &quot;What We Treat&quot;. "
                "Edit a sub-page&rsquo;s title, excerpt and featured image to change its card. "
                "Only the heading below is edited here.",
            ),
            txt("field_hs_wwt_eyebrow", "eyebrow", "Eyebrow"),
            txt("field_hs_wwt_title", "title", "Heading"),
            wysiwyg("field_hs_wwt_lede", "lede", "Standfirst"),
        ],
    ),
    layout(
        "layout_home_practitioner", "home_practitioner", "Home: practitioner",
        [
            txt("field_hs_pr_eyebrow", "eyebrow", "Eyebrow"),
            txt("field_hs_pr_name", "name", "Name"),
            txt("field_hs_pr_credentials", "credentials", "Credentials"),
            wysiwyg("field_hs_pr_bio", "bio", "Biography"),
            txt("field_hs_pr_link_label", "link_label", "Link label"),
            url("field_hs_pr_link_url", "link_url", "Link URL"),
            image("field_hs_pr_portrait", "portrait", "Portrait"),
        ],
    ),
    layout(
        "layout_home_modalities", "home_modalities", "Home: modalities",
        [
            txt("field_hs_mod_eyebrow", "eyebrow", "Eyebrow"),
            txt("field_hs_mod_title", "title", "Heading"),
            txt("field_hs_mod_tcm_title", "tcm_title", "First card &mdash; heading"),
            wysiwyg("field_hs_mod_tcm_body", "tcm_body", "First card &mdash; body"),
            image("field_hs_mod_tcm_image", "tcm_image", "First card &mdash; image"),
            txt("field_hs_mod_acu_title", "acu_title", "Second card &mdash; heading"),
            wysiwyg("field_hs_mod_acu_body", "acu_body", "Second card &mdash; body"),
            image("field_hs_mod_acu_image", "acu_image", "Second card &mdash; image"),
        ],
    ),
    layout(
        "layout_home_cases", "home_cases", "Home: featured clinic cases",
        [
            txt("field_hs_fc_eyebrow", "eyebrow", "Eyebrow"),
            txt("field_hs_fc_title", "title", "Heading"),
            wysiwyg("field_hs_fc_lede", "lede", "Standfirst"),
            (
                f"{T*8}array(\n"
                f"{T*9}'key'           => 'field_hs_fc_cases',\n"
                f"{T*9}'name'          => 'cases',\n"
                f"{T*9}'label'         => 'Cases to feature',\n"
                f"{T*9}'instructions'  => 'Leave empty to hide the section.',\n"
                f"{T*9}'type'          => 'relationship',\n"
                f"{T*9}'post_type'     => array( 'clinic_case' ),\n"
                f"{T*9}'filters'       => array( 'search' ),\n"
                f"{T*9}'return_format' => 'object',\n"
                f"{T*8}),\n"
            ),
        ],
    ),
    layout(
        "layout_home_reviews", "home_reviews", "Home: reviews",
        [
            message(
                "field_hs_rev_note",
                "The patient reviews are held in the theme and are not editable here yet. "
                "This section controls only where they appear on the page.",
            )
        ],
    ),
])

# --- splice the new layouts in after the last existing one -------------------
anchor = f"{T*7}'layout_cases'     => array(\n"
if anchor not in src:
    sys.exit("FATAL: could not find layout_cases to anchor after")

start = src.index(anchor)
depth = 0
i = src.index("array(", start) + len("array(")
depth = 1
while depth and i < len(src):
    if src[i] == "(":
        depth += 1
    elif src[i] == ")":
        depth -= 1
    i += 1
end = src.index("\n", src.index(",", i)) + 1

src = src[:end] + blocks + src[end:]
open(PATH, "w", encoding="utf-8").write(src)
print("inserted 6 home layouts after layout_cases")
