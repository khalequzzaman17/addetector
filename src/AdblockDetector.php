<?php

declare(strict_types=1);

namespace AdblockDetector;

final class AdblockDetector
{
    public function __construct(
        private readonly DetectorConfig $detector = new DetectorConfig(),
        private readonly NoticeConfig $notice = new NoticeConfig(),
    ) {
    }

    public function render(): string
    {
        if (!$this->detector->enabled) {
            return '';
        }

        $id = 'abd_' . bin2hex(random_bytes(5));
        $config = $this->json([
            'id' => $id,
            'detector' => [
                'checkGoogleAds' => $this->detector->checkGoogleAds,
                'checkBaitElement' => $this->detector->checkBaitElement,
                'checkKnownMarker' => $this->detector->checkKnownMarker,
                'periodicCheck' => $this->detector->periodicCheck,
                'periodicIntervalMs' => $this->detector->periodicIntervalMs,
                'requestTimeoutMs' => $this->detector->requestTimeoutMs,
                'googleAdsUrl' => $this->detector->googleAdsUrl,
                'knownMarkers' => array_values($this->detector->knownMarkers),
                'antiAdblockSelectors' => array_values($this->detector->antiAdblockSelectors),
                'antiAdblockScriptPatterns' => array_values($this->detector->antiAdblockScriptPatterns),
                'baitClasses' => array_values($this->detector->baitClasses),
            ],
        ]);

        $title = $this->escape($this->notice->title);
        $message = $this->notice->message;
        $refresh = $this->escape($this->notice->refreshText);
        $close = $this->notice->closeText === null ? '' : '<button type="button" data-abd-close>' . $this->escape($this->notice->closeText) . '</button>';
        $icon = $this->notice->iconUrl === '' ? '' : '<img class="abd__icon" src="' . $this->escape($this->notice->iconUrl) . '" alt="">';
        $lock = $this->notice->lockPage ? ' data-abd-lock' : '';
        $fallback = $this->notice->showNoscriptFallback ? '<noscript><div class="abd__noscript">' . $title . '<br>' . $message . '</div></noscript>' : '';

        return '<style>' . $this->css($id) . '</style>'
            . '<div id="' . $id . '" class="abd" aria-hidden="true"' . $lock . '>'
            . '<div class="abd__dialog" role="dialog" aria-modal="true" aria-labelledby="' . $id . '_title">'
            . $icon . '<h2 id="' . $id . '_title">' . $title . '</h2>'
            . '<div class="abd__message">' . $message . '</div>'
            . '<div class="abd__actions">' . $close . '<button type="button" data-abd-refresh>' . $refresh . '</button></div>'
            . '</div></div>' . $fallback
            . '<script>(function(w,d,c){' . $this->javascript() . '})(window,document,' . $config . ');</script>';
    }

    private function javascript(): string
    {
        $run = <<<'JS'
const root = d.getElementById(c.id);
if (!root) return;
let shown = false;
const show = () => { if (shown) return; shown = true; root.setAttribute('aria-hidden','false'); root.classList.add('abd--visible'); if (root.hasAttribute('data-abd-lock')) d.documentElement.classList.add('abd--locked'); };
const hide = () => { root.setAttribute('aria-hidden','true'); root.classList.remove('abd--visible'); d.documentElement.classList.remove('abd--locked'); };
const markerBlocked = () => c.detector.checkKnownMarker && c.detector.knownMarkers.some((selector) => { try { return !!d.getElementById(selector) || !!d.querySelector('.' + selector); } catch (_) { return false; } });
const antiAdblockSignature = () => c.detector.antiAdblockSelectors.some((selector) => { try { return !!d.querySelector(selector); } catch (_) { return false; } });
const antiAdblockScriptSignature = () => Array.from(d.scripts).some((script) => { const src = (script.src || '').toLowerCase(); return c.detector.antiAdblockScriptPatterns.some((pattern) => src.includes(pattern.toLowerCase())); });
const baitBlocked = () => { if (!c.detector.checkBaitElement) return false; const bait = d.createElement('div'); bait.className = c.detector.baitClasses.join(' '); bait.setAttribute('aria-hidden','true'); bait.style.cssText = 'position:absolute!important;left:-10000px!important;top:-10000px!important;width:1px!important;height:1px!important;'; (d.body || d.documentElement).appendChild(bait); const blocked = bait.offsetHeight === 0 || bait.offsetWidth === 0 || getComputedStyle(bait).display === 'none' || getComputedStyle(bait).visibility === 'hidden'; bait.remove(); return blocked; };
const googleBlocked = () => new Promise((resolve) => { if (!c.detector.checkGoogleAds || !navigator.onLine) return resolve(false); const tag = d.createElement('script'); let settled = false; const finish = (value) => { if (settled) return; settled = true; tag.remove(); resolve(value); }; const timer = w.setTimeout(() => finish(true), c.detector.requestTimeoutMs); tag.onload = () => { w.clearTimeout(timer); finish(typeof w.adsbygoogle === 'undefined'); }; tag.onerror = () => { w.clearTimeout(timer); finish(true); }; tag.src = c.detector.googleAdsUrl; tag.async = true; (d.head || d.documentElement).appendChild(tag); });
const check = async () => { if (markerBlocked() || antiAdblockSignature() || antiAdblockScriptSignature()) return show(); if (await googleBlocked()) return show(); if (baitBlocked()) return show(); hide(); };
root.querySelector('[data-abd-refresh]')?.addEventListener('click', () => w.location.reload());
root.querySelector('[data-abd-close]')?.addEventListener('click', hide);
const boot = () => { check(); if (c.detector.periodicCheck) w.setInterval(check, c.detector.periodicIntervalMs); };
if (c.detector.checkOnDomReady && d.readyState === 'loading') d.addEventListener('DOMContentLoaded', boot, {once:true}); else boot();
JS;
        return preg_replace('/\s+/', ' ', trim($run)) ?? $run;
    }

    private function css(string $id): string
    {
        $color = $this->escape($this->notice->primaryColor);
        $theme = $this->notice->theme === 'dark' ? 'background:#111827;color:#f9fafb' : 'background:#fff;color:#111827';
        return '#' . $id . '{display:none;position:fixed;inset:0;z-index:2147483647;align-items:center;justify-content:center;background:rgba(0,0,0,.68);padding:1rem}#' . $id . '.abd--visible{display:flex}.abd__dialog{' . $theme . ';max-width:32rem;width:min(100%,32rem);padding:2rem;border-radius:.75rem;box-shadow:0 1rem 4rem rgba(0,0,0,.3);text-align:center;font:16px/1.5 system-ui,sans-serif}.abd__dialog h2{margin:0 0 .75rem;font-size:1.35rem}.abd__message{margin-bottom:1.25rem}.abd__actions{display:flex;gap:.75rem;justify-content:center}.abd__actions button{border:1px solid ' . $color . ';border-radius:.4rem;padding:.6rem 1rem;cursor:pointer;background:' . $color . ';color:#fff}.abd__actions button[data-abd-close]{background:transparent;color:inherit}.abd__icon{display:block;max-width:5rem;max-height:5rem;margin:0 auto 1rem}.abd__noscript{position:fixed;inset:0;z-index:2147483647;display:grid;place-content:center;text-align:center;padding:2rem;background:#fff;color:#111;font:16px/1.5 system-ui,sans-serif}.abd__noscript br{display:none}.abd__noscript:after{content:" Please enable JavaScript to continue."}html.abd__locked,html.abd__locked body{overflow:hidden!important}';
    }

    private function json(array $value): string
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
