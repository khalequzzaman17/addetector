<?php

declare(strict_types=1);

namespace AdblockDetector;

use JsonException;

/**
 * Production-quality, framework-agnostic ad-block detector and notice renderer.
 *
 * Combines multi-signal heuristic evaluation:
 * - Strong signals: bait element collapse/hiding, network script probe failures/timeouts,
 *   missing window.adsbygoogle globals, and blocker DOM markers.
 * - Weak signals: AakList-derived anti-adblock DOM selectors and script path patterns.
 */
final class AdblockDetector
{
    public function __construct(
        private readonly DetectorConfig $detector = new DetectorConfig(),
        private readonly NoticeConfig $notice = new NoticeConfig(),
    ) {
    }

    /**
     * Get the active detector configuration.
     */
    public function getDetectorConfig(): DetectorConfig
    {
        return $this->detector;
    }

    /**
     * Get the active notice configuration.
     */
    public function getNoticeConfig(): NoticeConfig
    {
        return $this->notice;
    }

    /**
     * Render the CSS, HTML dialog, and vanilla JavaScript detection bundle.
     * Returns an empty string if the detector is disabled.
     */
    public function render(): string
    {
        if (!$this->detector->enabled) {
            return '';
        }

        $id = 'abd_' . bin2hex(random_bytes(6));
        $config = $this->json([
            'id' => $id,
            'detector' => [
                'minimumStrongSignals' => $this->detector->minimumStrongSignals,
                'allowWeakSignalAssistance' => $this->detector->allowWeakSignalAssistance,
                'periodicCheck' => $this->detector->periodicCheck,
                'periodicIntervalMs' => $this->detector->periodicIntervalMs,
                'requestTimeoutMs' => $this->detector->requestTimeoutMs,
                'observeDomChanges' => $this->detector->observeDomChanges,
                'checkOnDomReady' => $this->detector->checkOnDomReady,
                'checkGoogleAds' => $this->detector->checkGoogleAds,
                'googleAdsUrl' => $this->detector->googleAdsUrl,
                'checkBaitElement' => $this->detector->checkBaitElement,
                'checkKnownMarker' => $this->detector->checkKnownMarker,
                'knownMarkers' => $this->detector->knownMarkers,
                'antiAdblockSelectors' => $this->detector->antiAdblockSelectors,
                'antiAdblockScriptPatterns' => $this->detector->antiAdblockScriptPatterns,
                'baitClasses' => $this->detector->baitClasses,
            ],
            'notice' => [
                'hasClose' => $this->notice->closeText !== null,
            ],
        ]);

        $title = $this->escape($this->notice->title);
        $message = $this->sanitizeMessage($this->notice->message);
        $refresh = $this->escape($this->notice->refreshText);
        $close = $this->notice->closeText === null
            ? ''
            : '<button type="button" class="abd__btn abd__btn--close" data-abd-close>' . $this->escape($this->notice->closeText) . '</button>';
        $icon = $this->notice->iconUrl === ''
            ? ''
            : '<img class="abd__icon" src="' . $this->escape($this->notice->iconUrl) . '" alt="" aria-hidden="true">';
        $lock = $this->notice->lockPage ? ' data-abd-lock' : '';
        $fallback = $this->notice->showNoscriptFallback
            ? '<noscript><div class="abd__noscript"><div class="abd__noscript-box"><h3>' . $title . '</h3><p>' . $message . '</p></div></div></noscript>'
            : '';

        return '<style>' . $this->css($id) . '</style>'
            . '<div id="' . $id . '" class="abd" aria-hidden="true"' . $lock . '>'
            . '<div class="abd__backdrop" aria-hidden="true"></div>'
            . '<div class="abd__dialog" role="dialog" aria-modal="true" aria-labelledby="' . $id . '_title" tabindex="-1">'
            . $icon
            . '<h2 id="' . $id . '_title" class="abd__title">' . $title . '</h2>'
            . '<div class="abd__message">' . $message . '</div>'
            . '<div class="abd__actions">'
            . $close
            . '<button type="button" class="abd__btn abd__btn--refresh" data-abd-refresh>' . $refresh . '</button>'
            . '</div>'
            . '</div>'
            . '</div>'
            . $fallback
            . '<script>(function(w,d,c){' . $this->javascript() . '})(window,document,' . $config . ');</script>';
    }

    /**
     * Build the client-side vanilla JavaScript detector.
     */
    private function javascript(): string
    {
        $code = <<<'JS'
const root = d.getElementById(c.id);
if (!root) return;
const dialog = root.querySelector('.abd__dialog');
const closeBtn = root.querySelector('[data-abd-close]');
const refreshBtn = root.querySelector('[data-abd-refresh]');
let shown = false;
let checking = false;
let observer = null;
let lastActiveElement = null;

const getFocusables = () => Array.from(root.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'))
  .filter(el => !el.hasAttribute('disabled') && (el.offsetWidth > 0 || el.offsetHeight > 0 || el.getClientRects().length > 0));

const show = () => {
  if (shown) return;
  shown = true;
  lastActiveElement = d.activeElement;
  root.setAttribute('aria-hidden', 'false');
  root.classList.add('abd--visible');
  if (root.hasAttribute('data-abd-lock')) {
    d.documentElement.classList.add('abd--locked');
  }
  const focusables = getFocusables();
  if (focusables.length > 0) {
    focusables[0].focus();
  } else if (dialog) {
    dialog.focus();
  }
};

const hide = () => {
  if (!shown) return;
  shown = false;
  root.setAttribute('aria-hidden', 'true');
  root.classList.remove('abd--visible');
  d.documentElement.classList.remove('abd--locked');
  if (lastActiveElement && typeof lastActiveElement.focus === 'function') {
    try { lastActiveElement.focus(); } catch (_) {}
  }
};

d.addEventListener('keydown', (e) => {
  if (!shown) return;
  if (e.key === 'Escape' && c.notice.hasClose) {
    e.preventDefault();
    hide();
    return;
  }
  if (e.key === 'Tab') {
    const focusables = getFocusables();
    if (focusables.length === 0) {
      e.preventDefault();
      return;
    }
    const first = focusables[0];
    const last = focusables[focusables.length - 1];
    if (e.shiftKey && d.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && d.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  }
});

if (refreshBtn) {
  refreshBtn.addEventListener('click', () => w.location.reload());
}
if (closeBtn) {
  closeBtn.addEventListener('click', hide);
}

const markerBlocked = () => {
  if (!c.detector.checkKnownMarker) return false;
  return c.detector.knownMarkers.some((marker) => {
    try {
      return !!d.getElementById(marker) || !!d.querySelector('.' + marker);
    } catch (_) {
      return false;
    }
  });
};

const baitBlocked = () => {
  if (!c.detector.checkBaitElement) return false;
  const bait = d.createElement('div');
  bait.className = c.detector.baitClasses.join(' ');
  bait.setAttribute('aria-hidden', 'true');
  bait.style.cssText = 'position:absolute!important;left:-10000px!important;top:-10000px!important;width:1px!important;height:1px!important;pointer-events:none!important;';
  (d.body || d.documentElement).appendChild(bait);
  let blocked = false;
  try {
    const style = w.getComputedStyle(bait);
    blocked = (
      bait.offsetHeight === 0 ||
      bait.offsetWidth === 0 ||
      bait.clientHeight === 0 ||
      bait.clientWidth === 0 ||
      style.display === 'none' ||
      style.visibility === 'hidden'
    );
  } catch (_) {
    blocked = false;
  }
  bait.remove();
  return blocked;
};

const networkBlocked = () => new Promise((resolve) => {
  if (!c.detector.checkGoogleAds) return resolve(false);
  if (typeof navigator.onLine === 'boolean' && !navigator.onLine) {
    return resolve(false);
  }
  const tag = d.createElement('script');
  let settled = false;
  const finish = (value) => {
    if (settled) return;
    settled = true;
    try { tag.remove(); } catch (_) {}
    resolve(value);
  };
  const timer = w.setTimeout(() => finish(true), c.detector.requestTimeoutMs);
  tag.onload = () => {
    w.clearTimeout(timer);
    finish(typeof w.adsbygoogle === 'undefined');
  };
  tag.onerror = () => {
    w.clearTimeout(timer);
    finish(true);
  };
  tag.src = c.detector.googleAdsUrl;
  tag.async = true;
  (d.head || d.documentElement).appendChild(tag);
});

const antiAdblockDomSignature = () => {
  return c.detector.antiAdblockSelectors.some((selector) => {
    try {
      const el = d.querySelector(selector);
      return !!el && el !== root && !root.contains(el);
    } catch (_) {
      return false;
    }
  });
};

const antiAdblockScriptSignature = () => {
  const scripts = Array.from(d.scripts);
  return scripts.some((script) => {
    const src = (script.src || '').toLowerCase();
    if (!src) return false;
    return c.detector.antiAdblockScriptPatterns.some((pattern) => src.includes(pattern.toLowerCase()));
  });
};

const check = async () => {
  if (checking) return;
  checking = true;
  if (observer) observer.disconnect();
  try {
    const strongSignals = [];
    if (markerBlocked()) strongSignals.push('marker');
    const [network, bait] = await Promise.all([
      networkBlocked(),
      Promise.resolve(baitBlocked())
    ]);
    if (network) strongSignals.push('network');
    if (bait) strongSignals.push('bait');

    const weakDom = antiAdblockDomSignature();
    const weakScript = antiAdblockScriptSignature();
    const hasWeakSignal = weakDom || weakScript;

    const strongCount = strongSignals.length;
    const shouldBlock = (
      strongCount >= c.detector.minimumStrongSignals ||
      (c.detector.allowWeakSignalAssistance && strongCount >= 1 && hasWeakSignal)
    );

    if (shouldBlock) {
      show();
    }
  } finally {
    checking = false;
    if (observer && c.detector.observeDomChanges) {
      observer.observe(d.body || d.documentElement, { childList: true, subtree: true });
    }
  }
};

const boot = () => {
  check();
  if (c.detector.periodicCheck) {
    w.setInterval(check, c.detector.periodicIntervalMs);
  }
  if (c.detector.observeDomChanges && w.MutationObserver) {
    observer = new MutationObserver((mutations) => {
      if (shown) return;
      const relevant = mutations.some(m => !root.contains(m.target));
      if (relevant) check();
    });
    observer.observe(d.body || d.documentElement, { childList: true, subtree: true });
  }
};

if (c.detector.checkOnDomReady && d.readyState === 'loading') {
  d.addEventListener('DOMContentLoaded', boot, { once: true });
} else {
  boot();
}
JS;
        return preg_replace('/\s+/', ' ', trim($code)) ?? $code;
    }

    /**
     * Generate responsive, themeable CSS scoped to the randomized root identifier.
     */
    private function css(string $id): string
    {
        $primary = $this->escape($this->notice->primaryColor);
        $isDark = $this->notice->theme === 'dark';
        $dialogBg = $isDark ? '#111827' : '#ffffff';
        $dialogColor = $isDark ? '#f9fafb' : '#1f2937';
        $backdropBg = 'rgba(0, 0, 0, 0.72)';
        $boxShadow = $isDark ? '0 20px 45px rgba(0,0,0,0.6)' : '0 20px 45px rgba(0,0,0,0.25)';
        $customCss = $this->notice->customCss;

        return '#' . $id . '{display:none;position:fixed;inset:0;z-index:2147483647;align-items:center;justify-content:center;padding:1.25rem;box-sizing:border-box}'
            . '#' . $id . '.abd--visible{display:flex}'
            . '#' . $id . ' .abd__backdrop{position:fixed;inset:0;background:' . $backdropBg . ';backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);z-index:-1}'
            . '#' . $id . ' .abd__dialog{background:' . $dialogBg . ';color:' . $dialogColor . ';max-width:32rem;width:min(100%,32rem);padding:2rem;border-radius:1rem;box-shadow:' . $boxShadow . ';text-align:center;font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;line-height:1.5;outline:none}'
            . '#' . $id . ' .abd__icon{display:block;max-width:4.5rem;max-height:4.5rem;margin:0 auto 1.25rem;object-fit:contain}'
            . '#' . $id . ' .abd__title{margin:0 0 .75rem;font-size:1.35rem;font-weight:700}'
            . '#' . $id . ' .abd__message{margin:0 0 1.5rem;font-size:1rem;opacity:.92}'
            . '#' . $id . ' .abd__actions{display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap}'
            . '#' . $id . ' .abd__btn{padding:.65rem 1.25rem;font-size:.95rem;font-weight:600;border-radius:.5rem;cursor:pointer;transition:all .15s ease-in-out;border:1px solid transparent}'
            . '#' . $id . ' .abd__btn--refresh{background:' . $primary . ';color:#ffffff;border-color:' . $primary . '}'
            . '#' . $id . ' .abd__btn--refresh:hover{filter:brightness(1.1)}'
            . '#' . $id . ' .abd__btn--close{background:transparent;color:inherit;border-color:currentColor;opacity:.8}'
            . '#' . $id . ' .abd__btn--close:hover{opacity:1}'
            . '#' . $id . ' .abd__btn:focus-visible{outline:2px solid ' . $primary . ';outline-offset:2px}'
            . '.abd__noscript{position:fixed;inset:0;z-index:2147483647;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.85);padding:2rem;box-sizing:border-box}'
            . '.abd__noscript-box{background:#fff;color:#111;max-width:32rem;padding:2rem;border-radius:.75rem;text-align:center;font-family:system-ui,sans-serif}'
            . 'html.abd--locked,html.abd--locked body{overflow:hidden!important}'
            . ($customCss !== '' ? "\n" . $customCss : '');
    }

    /**
     * Safely encode JSON with HTML entities escaped to protect against inline XSS.
     *
     * @param array<string, mixed> $data
     * @throws JsonException
     */
    private function json(array $data): string
    {
        return json_encode(
            $data,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
        );
    }

    /**
     * Escape plain text strings for HTML output.
     */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Sanitize user-provided modal message, permitting only safe formatting tags.
     */
    private function sanitizeMessage(string $message): string
    {
        return strip_tags($message, '<strong><b><em><i><u><a><br><p>');
    }
}
