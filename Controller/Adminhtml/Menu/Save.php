<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\MegaMenu\Controller\Adminhtml\Menu;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\LocalizedException;
use Zero\MegaMenu\Model\Menu;
use Zero\MegaMenu\Model\PromoImage;

/**
 * Saves or deletes a panel or a link, then cleans what carries the menu by
 * the zero_megamenu tag: the menu block's own HTML cache (the block has a
 * ttl in Magento_Theme's layout) and the pages (clean_cache_by_tags: the
 * page cache and Varnish).
 */
class Save extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Zero_MegaMenu::menu';

    public function __construct(
        Context $context,
        private readonly Menu $menu,
        private readonly PromoImage $promoImage,
        private readonly CacheInterface $cache
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $request = $this->getRequest();
        $id = (int) $request->getParam('id') ?: null;
        try {
            if ($id && $request->getParam('delete')) {
                $this->menu->delete($id);
                $this->messageManager->addSuccessMessage(__('Removed from the menu.'));
                $this->clean();

                return $this->resultRedirectFactory->create()->setPath('*/*/index');
            }
            $data = (array) $request->getPostValue();
            $current = $id ? $this->menu->get($id) : null;
            $data['promo_image'] = $request->getParam('remove_promo') ? '' : (string) ($current['promo_image'] ?? '');
            $upload = $request->getFiles('promo');
            if (is_array($upload) && !empty($upload['name'])) {
                $data['promo_image'] = $this->promoImage->save($upload);
            }
            $id = $this->menu->save($id, $data);
            $this->messageManager->addSuccessMessage(__('The menu is saved.'));
            $this->clean();
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/edit', $id ? ['id' => $id] : []);
    }

    private function clean(): void
    {
        $this->cache->clean([Menu::CACHE_TAG]);
        $this->_eventManager->dispatch('clean_cache_by_tags', ['object' => $this->menu]);
    }
}
