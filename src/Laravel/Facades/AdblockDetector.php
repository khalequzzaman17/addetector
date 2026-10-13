<?php

declare(strict_types=1);

namespace AdblockDetector\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string render()
 * @method static \AdblockDetector\DetectorConfig getDetectorConfig()
 * @method static \AdblockDetector\NoticeConfig getNoticeConfig()
 *
 * @see \AdblockDetector\AdblockDetector
 */
class AdblockDetector extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'adblock.detector';
    }
}
