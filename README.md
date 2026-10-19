# Zero_MegaMenu

The category menu, managed in Content > Mega Menu: a department can open a
full-width panel instead of a dropdown, and the menu can carry links of its
own. CSS only on the storefront, like the theme's menu.

## Install

Requires Magento Open Source 2.4.7–2.4.9 and PHP 8.2–8.4 with the GD extension built with WebP (`php -r 'var_dump(function_exists("imagewebp"));'` says `true`; Magento's own PHP requirements cover the rest). Without WebP in GD, Zero skips its WebP image copies and serves the originals.

```bash
composer require fixstacks/module-zero-mega-menu
bin/magento setup:upgrade
bin/magento setup:di:compile           # production mode
bin/magento setup:static-content:deploy # production mode
bin/magento cache:flush
```

Composer installs `fixstacks/module-zero-core` with it.

Part of [`fixstacks/zero`](https://fixstacks.com/zero), which installs the Zero theme and every Zero module at once.

## Storefront

The menu keeps the theme's markup (a link plus a sibling `<details
class="menu-toggle">` per node with children: an accordion on phones,
dropdowns on `:hover` / `:focus-within` on desktop). A department with a
panel gets, instead of the dropdown list, a `<div class="submenu mega-panel">`
that spans the whole navigation:

- its subcategories as column headings, each with its own subcategories
  under it (or headings only), in 2, 3 or 4 columns;
- an extra group of links typed in the admin ("Size guide | size-guide");
- a promo card: a picture, a heading, a text and a button, linking to a URL
  or to the department.

On phones the panel is the accordion's content, stacked, without the
promo. Links of the menu's own (Blog, Brands, a page) sit before or after
the categories. Nothing needs JavaScript.

Pages carry the `zero_megamenu` cache tag (a plugin on the menu block's
identities); saving in the admin cleans them.

Zero/default's layout (`Zero_MegaMenu/layout/default.xml`, inherited by
every Zero theme) puts the template on `catalog.topnav`; a store view on
another theme, such as Luma, keeps that theme's own menu.

## Admin

Content > Mega Menu (resource `Zero_MegaMenu::menu`): the departments — the
top-level categories of every store's root — with "Add a panel" / "Edit",
and the menu's own links. A panel's form: active, full-width or dropdown,
columns, subcategory levels, extra links with a heading, the promo card
(picture upload into `pub/media/zero/megamenu`, heading, text, link,
button). A link's form: label, URL (full, or a path on the store),
placement, position, active.

## Store views

A panel or a link can be limited to one store view ("Store View" in its
form; default: all). Texts are in one language, so a store with several
languages limits each panel or link to the view that speaks it; the other
views show the plain category dropdown and none of the menu's own links.
(One panel per department: it is shown in all store views or in one.)

## Configuration

Stores > Configuration > Zero > Mega Menu > Enabled: off, the plain
category menu, whatever the admin holds.

## Tests

- `Test/Unit/Model/LinksTest.php`: the extra links text and store paths.
- `dev/tests/zero-e2e/tests/mega-menu-admin.spec.ts`: a panel with extra
  links and a promo on the first department, a link of the menu's own; both
  on the storefront; then removed.

## License

OSL-3.0 or AFL-3.0, your choice: see `NOTICE.md`.

---

<a href="https://vilmate.com"><picture>
  <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/Fixstacks/zero/main/docs/images/vilmate-logo-dark.svg">
  <img src="https://raw.githubusercontent.com/Fixstacks/zero/main/docs/images/vilmate-logo.svg" alt="Vilmate" height="34" align="right">
</picture></a>

Zero is made by [Fixstacks](https://fixstacks.com/zero) in partnership with [Vilmate](https://vilmate.com), a software development company.
Documentation: https://fixstacks.com/zero/docs. Demo store: https://zero.fixstacks.com.
