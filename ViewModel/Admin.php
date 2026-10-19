<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\MegaMenu\ViewModel;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Zero\MegaMenu\Model\Menu;

/**
 * Content > Mega Menu (view/adminhtml/templates/): the departments (the
 * store's top-level categories) with their panel settings, the menu's own
 * links, and the form for one of them.
 */
class Admin implements ArgumentInterface
{
    public function __construct(
        private readonly Menu $menu,
        private readonly CategoryCollectionFactory $categoryCollectionFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly RequestInterface $request,
        private readonly UrlInterface $url,
        private readonly FormKey $formKey
    ) {
    }

    /**
     * The top-level categories of every store's root, in menu order.
     *
     * @return array<int, array{id: int, name: string, store: string, in_menu: bool, panel: array|null}>
     */
    public function departments(): array
    {
        $roots = [];
        foreach ($this->storeManager->getGroups() as $group) {
            $roots[(int) $group->getRootCategoryId()] = (string) $group->getName();
        }
        $collection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect(['name', 'include_in_menu', 'is_active'])
            ->addFieldToFilter('level', 2)
            ->addFieldToFilter('parent_id', ['in' => array_keys($roots) ?: [0]])
            ->setOrder('parent_id', 'ASC')
            ->setOrder('position', 'ASC');
        $departments = [];
        foreach ($collection as $category) {
            $departments[] = [
                'id' => (int) $category->getId(),
                'name' => (string) $category->getName(),
                'store' => $roots[(int) $category->getParentId()] ?? '',
                'in_menu' => (bool) $category->getIncludeInMenu() && (bool) $category->getIsActive(),
                'panel' => $this->menu->forCategory((int) $category->getId()),
            ];
        }

        return $departments;
    }

    /**
     * @return array<int, array>
     */
    public function links(): array
    {
        return array_values(array_filter($this->menu->all(), static fn (array $row) => !$row['category_id']));
    }

    /**
     * The row being edited: by id, or a fresh panel for ?category=, or a fresh link.
     */
    public function current(): ?array
    {
        $id = (int) $this->request->getParam('id');

        return $id ? $this->menu->get($id) : null;
    }

    /**
     * The store views a panel or a link can be limited to.
     *
     * @return array<int, string> name by id
     */
    public function storeViews(): array
    {
        $views = [];
        foreach ($this->storeManager->getStores() as $store) {
            $views[(int) $store->getId()] = $store->getWebsite()->getName() . ' / ' . $store->getName() . ' (' . $store->getCode() . ')';
        }

        return $views;
    }

    public function storeViewLabel(int $storeId): string
    {
        return $storeId === 0 ? (string) __('All store views') : ($this->storeViews()[$storeId] ?? '#' . $storeId);
    }

    public function categoryId(): int
    {
        return (int) ($this->current()['category_id'] ?? $this->request->getParam('category'));
    }

    public function categoryName(int $categoryId): string
    {
        foreach ($this->departments() as $department) {
            if ($department['id'] === $categoryId) {
                return $department['name'];
            }
        }

        return '#' . $categoryId;
    }

    public function promoUrl(array $row): string
    {
        return $row['promo_image'] === '' ? '' : $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . ltrim($row['promo_image'], '/');
    }

    public function url(string $route, array $params = []): string
    {
        return $this->url->getUrl($route, $params);
    }

    public function formKey(): string
    {
        return $this->formKey->getFormKey();
    }
}
