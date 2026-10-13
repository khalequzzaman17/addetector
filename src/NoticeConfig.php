<?php

declare(strict_types=1);

namespace AdblockDetector;

use InvalidArgumentException;

/**
 * Configuration for the ad-block detection notice modal and UI presentation.
 */
final class NoticeConfig
{
    /**
     * @param string $title Header title displayed in the modal dialog.
     * @param string $message Descriptive guidance text explaining why ad blocking was detected.
     * @param string $refreshText Label for the reload/refresh button.
     * @param string|null $closeText Optional label for the dismissal button; null hides the close button.
     * @param string $theme Visual color scheme ('light' or 'dark').
     * @param string $primaryColor Primary accent color hex/rgb string for buttons and highlights.
     * @param bool $lockPage Prevent scrolling of the underlying document when notice is visible.
     * @param bool $showNoscriptFallback Include a styled noscript fallback message when JS is disabled.
     * @param string $iconUrl Optional image URL displayed above the notice title.
     * @param string $customCss Optional custom CSS injected alongside modal styles.
     */
    public function __construct(
        public readonly string $title = 'Please disable your ad blocker',
        public readonly string $message = 'Please disable your ad blocker and refresh the page to continue.',
        public readonly string $refreshText = 'Refresh',
        public readonly ?string $closeText = null,
        public readonly string $theme = 'light',
        public readonly string $primaryColor = '#2563eb',
        public readonly bool $lockPage = true,
        public readonly bool $showNoscriptFallback = true,
        public readonly string $iconUrl = '',
        public readonly string $customCss = '',
    ) {
        if (!in_array($this->theme, ['light', 'dark'], true)) {
            throw new InvalidArgumentException('theme must be either "light" or "dark".');
        }

        if (trim($this->primaryColor) === '') {
            throw new InvalidArgumentException('primaryColor cannot be empty.');
        }
    }

    /**
     * Return a cloned instance with modified options.
     *
     * @param array<string, mixed> $values
     */
    public function with(array $values): self
    {
        return new self(...array_merge([
            'title' => $this->title,
            'message' => $this->message,
            'refreshText' => $this->refreshText,
            'closeText' => $this->closeText,
            'theme' => $this->theme,
            'primaryColor' => $this->primaryColor,
            'lockPage' => $this->lockPage,
            'showNoscriptFallback' => $this->showNoscriptFallback,
            'iconUrl' => $this->iconUrl,
            'customCss' => $this->customCss,
        ], $values));
    }
}
