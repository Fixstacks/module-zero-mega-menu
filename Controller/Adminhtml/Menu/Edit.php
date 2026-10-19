<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\MegaMenu\Controller\Adminhtml\Menu;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * The form of a department's panel (?id= or ?category=) or of a link (?id= or nothing).
 */
class Edit extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Zero_MegaMenu::menu';

    public function execute()
    {
        $page = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $page->setActiveMenu('Zero_MegaMenu::menu');
        $page->getConfig()->getTitle()->prepend(
            $this->getRequest()->getParam('category') || $this->getRequest()->getParam('id') ? __('Mega Menu') : __('New Menu Link')
        );

        return $page;
    }
}
