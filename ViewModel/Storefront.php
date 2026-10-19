<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\MegaMenu\ViewModel;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Zero\MegaMenu\Model\Config;
use Zero\MegaMenu\Model\Links;
use Zero\MegaMenu\Model\Menu;

/**
 * The menu template (templates/topmenu.phtml): which departments open a
 * panel and with what, and the menu's own links.
 */
class Storefront implements ArgumentInterface
{
    /** @var array<int, array>|null */
    private ?array $panels = null;

    public function __construct(
        private readonly Menu $menu,
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    /**
     * The panel of a menu node ("category-node-<id>"), or null for a plain dropdown.
     */
    public function panel(string $nodeId): ?array
    {
        if (!$this->isEnabled() || !preg_match('/^category-node-(\d+)$/', $nodeId, $m)) {
            return null;
        }
        $this->panels ??= $this->menu->panels();

        return $this->panels[(int) $m[1]] ?? null;
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    public function links(string $placement): array
    {
        if (!$this->isEnabled()) {
            return [];
        }
        $links = [];
        foreach ($this->menu->links($placement) as $row) {
            $links[] = ['label' => $row['label'], 'url' => $this->url($row['url'])];
        }

        return $links;
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    public function extraLinks(array $panel): array
    {
        return array_map(
            fn (array $link) => ['label' => $link['label'], 'url' => $this->url($link['url'])],
            Links::parse($panel['links'])
        );
    }

    public function promoImageUrl(array $panel): string
    {
        return $panel['promo_image'] === ''
            ? ''
            : $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . ltrim($panel['promo_image'], '/');
    }

    public function url(string $url): string
    {
        return Links::url($url, $this->storeManager->getStore()->getBaseUrl());
    }
}
