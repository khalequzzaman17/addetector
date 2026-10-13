<?php

declare(strict_types=1);

namespace AdblockDetector\Tests;

use AdblockDetector\DetectorConfig;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class DetectorConfigTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $config = new DetectorConfig();

        $this->assertTrue($config->enabled);
        $this->assertSame(2, $config->minimumStrongSignals);
        $this->assertTrue($config->allowWeakSignalAssistance);
        $this->assertFalse($config->periodicCheck);
        $this->assertSame(30000, $config->periodicIntervalMs);
        $this->assertSame(4000, $config->requestTimeoutMs);
        $this->assertTrue($config->observeDomChanges);
        $this->assertTrue($config->checkOnDomReady);
        $this->assertTrue($config->checkGoogleAds);
        $this->assertTrue($config->checkBaitElement);
        $this->assertTrue($config->checkKnownMarker);
        $this->assertSame('balanced', $config->sensitivity);
        $this->assertContains('stndz-style', $config->knownMarkers);
        $this->assertContains('#adblock-message', $config->antiAdblockSelectors);
        $this->assertContains('blockadblock', $config->antiAdblockScriptPatterns);
        $this->assertContains('adsbygoogle', $config->baitClasses);
    }

    public function testSensitivityPresets(): void
    {
        $strict = DetectorConfig::strict();
        $this->assertSame(1, $strict->minimumStrongSignals);
        $this->assertTrue($strict->allowWeakSignalAssistance);
        $this->assertSame('strict', $strict->sensitivity);

        $balanced = DetectorConfig::balanced();
        $this->assertSame(2, $balanced->minimumStrongSignals);
        $this->assertTrue($balanced->allowWeakSignalAssistance);
        $this->assertSame('balanced', $balanced->sensitivity);

        $lenient = DetectorConfig::lenient();
        $this->assertSame(2, $lenient->minimumStrongSignals);
        $this->assertFalse($lenient->allowWeakSignalAssistance);
        $this->assertSame('lenient', $lenient->sensitivity);
    }

    public function testValidationRejectsInvalidInterval(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('periodicIntervalMs must be at least 1000');
        new DetectorConfig(periodicIntervalMs: 500);
    }

    public function testValidationRejectsInvalidTimeout(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('requestTimeoutMs must be at least 250');
        new DetectorConfig(requestTimeoutMs: 100);
    }

    public function testValidationRejectsTooLowMinimumStrongSignals(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('minimumStrongSignals must be between 1 and 3');
        new DetectorConfig(minimumStrongSignals: 0);
    }

    public function testValidationRejectsTooHighMinimumStrongSignals(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('minimumStrongSignals must be between 1 and 3');
        new DetectorConfig(minimumStrongSignals: 4);
    }

    public function testValidationRejectsInvalidSensitivity(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('sensitivity must be one of: lenient, balanced, strict, custom');
        new DetectorConfig(sensitivity: 'ultra');
    }

    public function testWithMethodReturnsModifiedClone(): void
    {
        $config = new DetectorConfig(minimumStrongSignals: 2);
        $modified = $config->with([
            'minimumStrongSignals' => 3,
            'periodicCheck' => true,
        ]);

        $this->assertSame(3, $modified->minimumStrongSignals);
        $this->assertTrue($modified->periodicCheck);
        $this->assertSame(2, $config->minimumStrongSignals);
    }
}
