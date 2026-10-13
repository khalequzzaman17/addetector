<?php

declare(strict_types=1);

namespace AdblockDetector\Tests;

use AdblockDetector\AdblockDetector;
use AdblockDetector\Laravel\AdblockDetectorServiceProvider;
use AdblockDetector\Laravel\Facades\AdblockDetector as AdblockDetectorFacade;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

final class LaravelIntegrationTest extends TestCase
{
    public function testServiceProviderBindsSingletonAndAliases(): void
    {
        $app = new Container();
        $config = new class([
            'adblock-detector' => [
                'detector' => [
                    'minimum_strong_signals' => 1,
                    'periodic_check' => true,
                ],
                'notice' => [
                    'title' => 'Laravel Detected',
                    'close_text' => 'Dismiss',
                ],
            ],
        ]) {
            public function __construct(private readonly array $items)
            {
            }

            public function get(string $key, mixed $default = null): mixed
            {
                return $this->items[$key] ?? $default;
            }
        };

        $app->instance('config', $config);
        Container::setInstance($app);
        Facade::setFacadeApplication($app);

        $provider = new class($app) extends AdblockDetectorServiceProvider {
            // Override mergeConfigFrom to avoid requiring full Laravel application filesystem
            protected function mergeConfigFrom($path, $key)
            {
            }
        };

        $provider->register();

        $this->assertTrue($app->bound(AdblockDetector::class));
        $this->assertTrue($app->bound('adblock.detector'));

        /** @var AdblockDetector $instance */
        $instance = $app->make(AdblockDetector::class);
        $this->assertInstanceOf(AdblockDetector::class, $instance);
        $this->assertSame(1, $instance->getDetectorConfig()->minimumStrongSignals);
        $this->assertTrue($instance->getDetectorConfig()->periodicCheck);
        $this->assertSame('Laravel Detected', $instance->getNoticeConfig()->title);
        $this->assertSame('Dismiss', $instance->getNoticeConfig()->closeText);

        // Test Facade accessor resolution
        $rendered = AdblockDetectorFacade::render();
        $this->assertStringContainsString('Laravel Detected', $rendered);
        $this->assertStringContainsString('Dismiss', $rendered);
    }
}
