<?php

declare(strict_types=1);

namespace AdblockDetector;

final class NoticeConfig
{
    public function __construct(
        public readonly string $title = 'Ads blocker detected',
        public readonly string $message = 'Please disable your ad blocker to continue supporting this website.',
        public readonly string $refreshText = 'Refresh',
        public readonly ?string $closeText = null,
        public readonly string $theme = 'light',
        public readonly string $primaryColor = '#2563eb',
        public readonly bool $lockPage = true,
        public readonly bool $showNoscriptFallback = true,
        public readonly string $iconUrl = '',
    ) {
        if (!in_array($this->theme, ['light', 'dark'], true)) {
            throw new \InvalidArgumentException('theme must be light or dark.');
        }
    }
}
