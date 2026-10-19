<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\MegaMenu\Model;

/**
 * The extra links of a panel, typed one per line as "Label | URL". A URL
 * without a scheme or a leading slash is a store path ("blog", "brands").
 */
class Links
{
    /**
     * @return array<int, array{label: string, url: string}>
     */
    public static function parse(string $text): array
    {
        $links = [];
        foreach (preg_split('/\R/', $text) ?: [] as $line) {
            $parts = array_map('trim', explode('|', $line, 2));
            if (($parts[0] ?? '') === '' || ($parts[1] ?? '') === '') {
                continue;
            }
            $links[] = ['label' => $parts[0], 'url' => $parts[1]];
        }

        return $links;
    }

    /**
     * A typed URL as a link: absolute ones as they are, a path under the store.
     */
    public static function url(string $url, string $baseUrl): string
    {
        $url = trim($url);
        if ($url === '' || preg_match('#^(https?:)?//#i', $url) || str_starts_with($url, '/') || str_starts_with($url, '#') || str_starts_with($url, 'mailto:')) {
            return $url;
        }

        return rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
    }
}
