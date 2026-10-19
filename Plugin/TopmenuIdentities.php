<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\MegaMenu\Plugin;

use Magento\Theme\Block\Html\Topmenu;
use Zero\MegaMenu\Model\Menu;

/**
 * The menu block's page cache identities gain the mega menu's tag, so a
 * change in Content > Mega Menu cleans every cached page with the menu.
 */
class TopmenuIdentities
{
    /**
     * @param string[] $identities
     * @return string[]
     */
    public function afterGetIdentities(Topmenu $subject, array $identities): array
    {
        $identities[] = Menu::CACHE_TAG;

        return $identities;
    }
}
