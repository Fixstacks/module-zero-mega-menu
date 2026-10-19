<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\MegaMenu\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * The mega menu's rows (zero_megamenu): a department's panel (by category
 * id) or a link of the menu's own. Implements IdentityInterface so a save
 * cleans every page carrying the menu (clean_cache_by_tags).
 */
class Menu implements IdentityInterface
{
    public const CACHE_TAG = 'zero_megamenu';
    public const BEFORE = 'before';
    public const AFTER = 'after';

    private const TABLE = 'zero_megamenu';

    /** @var array<int, array>|null */
    private ?array $rows = null;

    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * Whether a row is for the store view being shown: its own, or every one (store_id 0).
     */
    private function isShown(array $row): bool
    {
        return $row['store_id'] === 0 || $row['store_id'] === (int) $this->storeManager->getStore()->getId();
    }

    /**
     * Every row, panels and links, by menu id.
     *
     * @return array<int, array>
     */
    public function all(): array
    {
        if ($this->rows === null) {
            $connection = $this->resource->getConnection();
            $this->rows = [];
            foreach ($connection->fetchAll($connection->select()->from($this->resource->getTableName(self::TABLE))->order(['position ASC', 'menu_id ASC'])) as $row) {
                $this->rows[(int) $row['menu_id']] = $this->normalize($row);
            }
        }

        return $this->rows;
    }

    public function get(int $menuId): ?array
    {
        return $this->all()[$menuId] ?? null;
    }

    /**
     * The active panels by category id.
     *
     * @return array<int, array>
     */
    public function panels(): array
    {
        $panels = [];
        foreach ($this->all() as $row) {
            if ($row['category_id'] && $row['is_active'] && $row['is_mega'] && $this->isShown($row)) {
                $panels[$row['category_id']] = $row;
            }
        }

        return $panels;
    }

    /**
     * The settings of one department (a row with its category id), if any.
     */
    public function forCategory(int $categoryId): ?array
    {
        foreach ($this->all() as $row) {
            if ($row['category_id'] === $categoryId) {
                return $row;
            }
        }

        return null;
    }

    /**
     * The active links of the menu's own, placed before or after the categories.
     *
     * @return array<int, array>
     */
    public function links(string $placement): array
    {
        return array_values(array_filter(
            $this->all(),
            fn (array $row) => !$row['category_id'] && $row['is_active'] && $row['placement'] === $placement && $this->isShown($row)
        ));
    }

    /**
     * @throws LocalizedException
     */
    public function save(?int $menuId, array $data): int
    {
        $categoryId = (int) ($data['category_id'] ?? 0) ?: null;
        $row = [
            'category_id' => $categoryId,
            'store_id' => $this->storeViewId((int) ($data['store_id'] ?? 0)),
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ];
        if ($categoryId) {
            $row += [
                'is_mega' => !empty($data['is_mega']) ? 1 : 0,
                'columns' => max(2, min(4, (int) ($data['columns'] ?? 3))),
                'depth' => max(1, min(2, (int) ($data['depth'] ?? 2))),
                'links' => trim((string) ($data['links'] ?? '')) ?: null,
                'links_heading' => mb_substr(trim((string) ($data['links_heading'] ?? '')), 0, 255) ?: null,
                'promo_image' => trim((string) ($data['promo_image'] ?? '')) ?: null,
                'promo_heading' => mb_substr(trim((string) ($data['promo_heading'] ?? '')), 0, 255) ?: null,
                'promo_text' => trim((string) ($data['promo_text'] ?? '')) ?: null,
                'promo_url' => mb_substr(trim((string) ($data['promo_url'] ?? '')), 0, 1024) ?: null,
                'promo_button' => mb_substr(trim((string) ($data['promo_button'] ?? '')), 0, 255) ?: null,
            ];
        } else {
            $label = mb_substr(trim((string) ($data['label'] ?? '')), 0, 255);
            $url = mb_substr(trim((string) ($data['url'] ?? '')), 0, 1024);
            if ($label === '' || $url === '') {
                throw new LocalizedException(__('A link needs a label and a URL.'));
            }
            $row += [
                'label' => $label,
                'url' => $url,
                'position' => max(0, (int) ($data['position'] ?? 0)),
                'placement' => ($data['placement'] ?? '') === self::BEFORE ? self::BEFORE : self::AFTER,
            ];
        }
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName(self::TABLE);
        if ($menuId) {
            $connection->update($table, $row, ['menu_id = ?' => $menuId]);
        } else {
            $connection->insert($table, $row);
            $menuId = (int) $connection->lastInsertId($table);
        }
        $this->rows = null;

        return $menuId;
    }

    /**
     * 0 (every store view) or the id of a store view that exists.
     */
    private function storeViewId(int $storeId): int
    {
        if ($storeId <= 0) {
            return 0;
        }
        foreach ($this->storeManager->getStores() as $store) {
            if ((int) $store->getId() === $storeId) {
                return $storeId;
            }
        }

        return 0;
    }

    public function delete(int $menuId): void
    {
        $this->resource->getConnection()->delete($this->resource->getTableName(self::TABLE), ['menu_id = ?' => $menuId]);
        $this->rows = null;
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG];
    }

    private function normalize(array $row): array
    {
        return [
            'menu_id' => (int) $row['menu_id'],
            'category_id' => (int) $row['category_id'] ?: null,
            'label' => (string) $row['label'],
            'url' => (string) $row['url'],
            'position' => (int) $row['position'],
            'placement' => (string) $row['placement'],
            'store_id' => (int) $row['store_id'],
            'is_active' => (bool) $row['is_active'],
            'is_mega' => (bool) $row['is_mega'],
            'columns' => (int) $row['columns'],
            'depth' => (int) $row['depth'],
            'links' => (string) $row['links'],
            'links_heading' => (string) $row['links_heading'],
            'promo_image' => (string) $row['promo_image'],
            'promo_heading' => (string) $row['promo_heading'],
            'promo_text' => (string) $row['promo_text'],
            'promo_url' => (string) $row['promo_url'],
            'promo_button' => (string) $row['promo_button'],
        ];
    }
}
