<?php

declare(strict_types=1);

namespace AdblockDetector\Tests;

use AdblockDetector\AdblockDetector;
use AdblockDetector\DetectorConfig;
use AdblockDetector\NoticeConfig;
use PHPUnit\Framework\TestCase;

final class AdblockDetectorTest extends TestCase
{
    public function testDisabledDetectorReturnsEmptyString(): void
    {
        $detector = new AdblockDetector(new DetectorConfig(enabled: false));
        $this->assertSame('', $detector->render());
    }

    public function testRenderOutputsEscapedTitleAndSanitizedMessage(): void
    {
        $detector = new AdblockDetector(
            new DetectorConfig(),
            new NoticeConfig(
                title: 'Malicious <script>alert(1)</script> Title & "Quotes"',
                message: 'Allowed <strong>bold</strong> and <a href="#">link</a> but <script>evil()</script>',
                closeText: 'Close <me>',
            )
        );

        $html = $detector->render();

        // Title should be HTML-escaped
        $this->assertStringContainsString('Malicious &lt;script&gt;alert(1)&lt;/script&gt; Title &amp; &quot;Quotes&quot;', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);

        // Close button text should be escaped
        $this->assertStringContainsString('Close &lt;me&gt;', $html);

        // Message should retain safe formatting tags, strip malicious script tags
        $this->assertStringContainsString('Allowed <strong>bold</strong>', $html);
        $this->assertStringNotContainsString('<script>evil()</script>', $html);
    }

    public function testCustomTitleMessageAndButtons(): void
    {
        $detector = new AdblockDetector(
            new DetectorConfig(),
            new NoticeConfig(
                title: 'Custom Header',
                message: 'Custom explanation text here.',
                refreshText: 'Try Reloading',
                closeText: 'Dismiss Notice',
                iconUrl: 'https://example.com/icon.png',
            )
        );

        $html = $detector->render();

        $this->assertStringContainsString('Custom Header', $html);
        $this->assertStringContainsString('Custom explanation text here.', $html);
        $this->assertStringContainsString('Try Reloading', $html);
        $this->assertStringContainsString('Dismiss Notice', $html);
        $this->assertStringContainsString('data-abd-close', $html);
        $this->assertStringContainsString('https://example.com/icon.png', $html);
    }

    public function testOmissionOfCloseButtonWhenNull(): void
    {
        $detector = new AdblockDetector(
            new DetectorConfig(),
            new NoticeConfig(closeText: null)
        );

        $html = $detector->render();
        $this->assertStringNotContainsString('class="abd__btn abd__btn--close"', $html);
    }

    public function testAccessibilityAttributesArePresent(): void
    {
        $detector = new AdblockDetector();
        $html = $detector->render();

        $this->assertStringContainsString('role="dialog"', $html);
        $this->assertStringContainsString('aria-modal="true"', $html);
        $this->assertStringContainsString('aria-labelledby=', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
    }

    public function testGeneratedOutputContainsRequiredJsSignals(): void
    {
        $detector = new AdblockDetector();
        $html = $detector->render();

        // Strong signals
        $this->assertStringContainsString('baitBlocked', $html);
        $this->assertStringContainsString('networkBlocked', $html);
        $this->assertStringContainsString('markerBlocked', $html);
        $this->assertStringContainsString('adsbygoogle', $html);

        // Weak signals
        $this->assertStringContainsString('antiAdblockDomSignature', $html);
        $this->assertStringContainsString('antiAdblockScriptSignature', $html);

        // Concurrency guard and MutationObserver infinite loop safety
        $this->assertStringContainsString('checking', $html);
        $this->assertStringContainsString('observer.disconnect()', $html);

        // Offline safety
        $this->assertStringContainsString('navigator.onLine', $html);

        // Dynamic DOM and lifecycle
        $this->assertStringContainsString('MutationObserver', $html);
        $this->assertStringContainsString('DOMContentLoaded', $html);

        // Keyboard navigation
        $this->assertStringContainsString('Escape', $html);
        $this->assertStringContainsString('Tab', $html);
    }

    public function testNoUpstreamChpBrandingOrWordpressDependencies(): void
    {
        $detector = new AdblockDetector();
        $html = $detector->render();

        $this->assertStringNotContainsString('chpadblock.com', $html);
        $this->assertStringNotContainsString('toolkitspro.com', $html);
        $this->assertStringNotContainsString('Code Help Pro', $html);
        $this->assertStringNotContainsString('wp_', $html);
        $this->assertStringNotContainsString('ABSPATH', $html);
        $this->assertStringNotContainsString('eval(', $html);
    }

    public function testNoscriptFallbackToggle(): void
    {
        $withFallback = new AdblockDetector(new DetectorConfig(), new NoticeConfig(showNoscriptFallback: true));
        $this->assertStringContainsString('<noscript>', $withFallback->render());

        $withoutFallback = new AdblockDetector(new DetectorConfig(), new NoticeConfig(showNoscriptFallback: false));
        $this->assertStringNotContainsString('<noscript>', $withoutFallback->render());
    }

    public function testDarkThemeStyling(): void
    {
        $detector = new AdblockDetector(new DetectorConfig(), new NoticeConfig(theme: 'dark'));
        $html = $detector->render();

        $this->assertStringContainsString('#111827', $html);
        $this->assertStringContainsString('#f9fafb', $html);
    }
}
