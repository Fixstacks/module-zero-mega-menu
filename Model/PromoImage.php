<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\MegaMenu\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;

/**
 * A panel's promo picture, uploaded into pub/media/zero/megamenu: JPEG,
 * PNG, WebP or SVG, kept as uploaded (about 600×400 reads best).
 */
class PromoImage
{
    public const DIR = 'zero/megamenu';

    public function __construct(
        private readonly UploaderFactory $uploaderFactory,
        private readonly Filesystem $filesystem
    ) {
    }

    /**
     * @return string The picture's path under pub/media
     * @throws LocalizedException
     */
    public function save(array $upload): string
    {
        if (($upload['error'] ?? 0) !== UPLOAD_ERR_OK) {
            throw new LocalizedException(__('The picture didn\'t upload (too large?).'));
        }
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => $upload]);
            // No SVG: it can carry a script and would be served from the store's own origin.
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'png', 'webp']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $result = $uploader->save($this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath(self::DIR));
        } catch (\Throwable $e) {
            throw new LocalizedException(__('The picture couldn\'t be saved: %1', $e->getMessage()));
        }

        return self::DIR . '/' . $result['file'];
    }
}
