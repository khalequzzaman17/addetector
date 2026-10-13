<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use AdblockDetector\AdblockDetector;
use AdblockDetector\DetectorConfig;
use AdblockDetector\NoticeConfig;

$html = (new AdblockDetector(
    new DetectorConfig(checkGoogleAds: false, checkBaitElement: true),
    new NoticeConfig(title: 'Test <title>', message: '<strong>Disable it</strong>', closeText: 'Close'),
))->render();

assert(str_contains($html, 'Ads blocker detected') === false);
assert(str_contains($html, 'Test &lt;title&gt;'));
assert(str_contains($html, 'data-abd-refresh'));
assert(str_contains($html, 'checkBaitElement'));
assert(str_contains($html, 'antiAdblockSelectors'));
assert(!str_contains($html, 'chpadblock.com'));
echo "OK\n";
