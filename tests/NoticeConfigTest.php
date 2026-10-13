<?php

declare(strict_types=1);

namespace AdblockDetector\Tests;

use AdblockDetector\NoticeConfig;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class NoticeConfigTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $config = new NoticeConfig();

        $this->assertSame('Please disable your ad blocker', $config->title);
        $this->assertSame('Please disable your ad blocker and refresh the page to continue.', $config->message);
        $this->assertSame('Refresh', $config->refreshText);
        $this->assertNull($config->closeText);
        $this->assertSame('light', $config->theme);
        $this->assertSame('#2563eb', $config->primaryColor);
        $this->assertTrue($config->lockPage);
        $this->assertTrue($config->showNoscriptFallback);
        $this->assertSame('', $config->iconUrl);
        $this->assertSame('', $config->customCss);
    }

    public function testValidationRejectsInvalidTheme(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('theme must be either "light" or "dark"');
        new NoticeConfig(theme: 'neon');
    }

    public function testValidationRejectsEmptyPrimaryColor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('primaryColor cannot be empty');
        new NoticeConfig(primaryColor: '   ');
    }

    public function testWithMethodReturnsModifiedClone(): void
    {
        $config = new NoticeConfig(title: 'Initial Title');
        $modified = $config->with([
            'title' => 'Updated Title',
            'theme' => 'dark',
            'closeText' => 'Close',
        ]);

        $this->assertSame('Updated Title', $modified->title);
        $this->assertSame('dark', $modified->theme);
        $this->assertSame('Close', $modified->closeText);
        $this->assertSame('Initial Title', $config->title);
    }
}
