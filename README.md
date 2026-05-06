# Wellspring Theme

Custom WordPress theme for [wellspringhealth.ca](https://wellspringhealth.ca), based on the [Underscores (`_s`)](https://underscores.me) starter from Automattic.

## Stack

- WordPress 6.x (classic theme, custom blocks where needed)
- PHP 8.0+
- SASS for stylesheets, compiled to `style.css`
- Vanilla JS for interactivity
- Hosted on Hostinger Business
- Auto-deployed from `main` via Hostinger's Git integration

## Local development

Install Composer + Node dependencies:

```sh
composer install
npm install
```

Common commands:

```sh
npm run watch          # watch SASS, recompile on save
npm run compile:css    # one-shot SASS build
composer lint:wpcs     # WordPress PHP coding standards
```

## Deploy

Push to `main`. Hostinger's Git integration pulls the repo into
`wp-content/themes/wellspring/` automatically.

## Structure

```
wellspring-theme/
├── style.css              theme metadata + compiled CSS
├── functions.php          theme bootstrap
├── header.php / footer.php
├── index.php / page.php / single.php / archive.php
├── inc/                   PHP includes (template tags, customizer, etc.)
├── template-parts/        partial templates
├── sass/                  SASS source
├── js/                    JS (navigation, customizer)
└── languages/             translations
```

## License

GPL v2 or later, inherited from Underscores.
