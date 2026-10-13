<?php

declare(strict_types=1);

namespace AdblockDetector\Tests;

use AdblockDetector\AakListProfile;
use PHPUnit\Framework\TestCase;

final class AakListProfileTest extends TestCase
{
    public function testAttributionConstants(): void
    {
        $this->assertSame('Reek', AakListProfile::AUTHOR);
        $this->assertSame('CC BY-SA 4.0', AakListProfile::SOURCE_LICENSE);
        $this->assertSame('https://raw.githubusercontent.com/reek/anti-adblock-killer/master/anti-adblock-killer-filters.txt', AakListProfile::SOURCE);
        $this->assertSame('b8b0e58261fda95d1eb7529d11ab700b9cb8b0c313be890e40c8cdf4246a730f', AakListProfile::SOURCE_SHA256);
    }

    public function testSelectorsAreNotEmptyAndContainKeyPatterns(): void
    {
        $selectors = AakListProfile::selectors();
        $this->assertNotEmpty($selectors);
        $this->assertContains('#adblock-message', $selectors);
        $this->assertContains('.deadblocker-header-bar', $selectors);
        $this->assertContains('#adbuddy-overlay', $selectors);
    }

    public function testScriptPatternsContainKnownAntiAdblockTools(): void
    {
        $scripts = AakListProfile::scriptPatterns();
        $this->assertNotEmpty($scripts);
        $this->assertContains('blockadblock', $scripts);
        $this->assertContains('fuckadblock', $scripts);
        $this->assertContains('anti-ad-buster.js', $scripts);
        $this->assertContains('deadblocker', $scripts);
    }

    public function testBaitClassesContainStandardAdIdentifiers(): void
    {
        $bait = AakListProfile::baitClasses();
        $this->assertNotEmpty($bait);
        $this->assertContains('adsbygoogle', $bait);
        $this->assertContains('ad-container', $bait);
        $this->assertContains('doubleclick', $bait);
    }
}
