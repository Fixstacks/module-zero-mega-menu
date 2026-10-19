<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 *
 * Zero_MegaMenu: the category menu, managed in Content > Mega Menu. A
 * department can open a full-width panel (its subcategories in columns,
 * extra links, a promo picture with a heading and a button) instead of a
 * dropdown, and the menu can carry links of its own (Blog, Brands, a page).
 * CSS only on the storefront, like the theme's menu. See README.md.
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Zero_MegaMenu', __DIR__);
