<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\MegaMenu\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Zero\MegaMenu\Model\Links;

class LinksTest extends TestCase
{
    public function testParsesLabelAndUrlPerLineAndSkipsTheRest(): void
    {
        self::assertSame(
            [['label' => 'Size guide', 'url' => 'size-guide'], ['label' => 'Blog', 'url' => 'https://example.com/blog']],
            Links::parse("Size guide | size-guide\n\nno url\n| no label\nBlog|https://example.com/blog\n")
        );
        self::assertSame([], Links::parse(''));
    }

    public function testStorePathsGetTheBaseUrlAndAbsoluteUrlsStay(): void
    {
        self::assertSame('https://shop.test/size-guide', Links::url('size-guide', 'https://shop.test/'));
        self::assertSame('/blog', Links::url('/blog', 'https://shop.test/'));
        self::assertSame('https://example.com/x', Links::url('https://example.com/x', 'https://shop.test/'));
        self::assertSame('//cdn.example.com/x', Links::url('//cdn.example.com/x', 'https://shop.test/'));
        self::assertSame('', Links::url('  ', 'https://shop.test/'));
    }
}
