<?php

use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;

function dateformat(
    $date,
    $format = 'd.m.Y H:i:s',
    $timezone = 'UTC',
    $locale = 'tr_TR.utf8',
    $diff_for_humans = false
): string|bool {
    try {
        setlocale(LC_TIME, $locale);
        $dt = Carbon::parse($date);

        if ($timezone) {
            $dt->setTimezone($timezone);
        }

        if ($locale) {
            $dt->locale($locale);
        }

        if ($diff_for_humans) {
            return $dt->diffForHumans();
        } else {
            return $dt->translatedFormat($format);
        }
    } catch (InvalidFormatException $e) {
        abort(404);
    }
}

function replace_characters($text): array|string|null
{
    return preg_replace("/([^\p{Latin}A-Za-z0-9\"', ._@öÖçÇşŞğĞüÜıİА-Яа-яЁё|₺€$\p{Cyrillic}-])/um", '', $text);
}

function GetPost($request): array|string|null
{
    if ($request != null) {
        if (is_array($request)) {
            return addslashes(strip_tags($request[0]));
        } else {
            return addslashes(strip_tags($request));
        }
    }

    return null;

}

function content($content): string
{
    $allowed = '<br><br/><br /><a><b><strong><em><i><div><p><img><li><ul><ol><table><tr><td><h1><h2><h2><h3><h4><h5><h6><span><code><pre><blockquote><u><iframe><del><strike><s><sub><sup><hr>';

    return addslashes(sanitizeHtml(strip_tags((string) $content, $allowed)));
}

/**
 * Remove XSS vectors that survive strip_tags(): event-handler attributes and
 * dangerous URL schemes on allowed tags. strip_tags() keeps ALL attributes of
 * allowed tags, so <img onerror=...>, <a href="javascript:...">, and
 * <iframe src="javascript:..."> would otherwise pass through untouched.
 *
 * NOTE: this is a targeted hardening, not a full HTML sanitizer. For defense in
 * depth, migrating to mews/purifier (HTMLPurifier) is recommended (requires a
 * dependency change / approval).
 */
function sanitizeHtml(string $html): string
{
    if (trim($html) === '') {
        return $html;
    }

    $dangerousSchemes = ['javascript:', 'vbscript:', 'data:'];
    $urlAttributes = ['href', 'src', 'xlink:href', 'action', 'formaction', 'background', 'poster'];

    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    // Wrap so we never emit a doctype/html/body wrapper, and force UTF-8.
    $loaded = $dom->loadHTML(
        '<?xml encoding="UTF-8"><div id="__sanitize_root__">'.$html.'</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();

    if (! $loaded) {
        // Could not parse — fall back to fully escaping rather than trusting it.
        return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
    }

    $xpath = new DOMXPath($dom);
    foreach ($xpath->query('//*') as $element) {
        if (! $element instanceof DOMElement) {
            continue;
        }
        foreach (iterator_to_array($element->attributes) as $attr) {
            $name = strtolower($attr->name);
            $value = strtolower(trim(preg_replace('/\s+/', '', $attr->value)));

            if (str_starts_with($name, 'on')) {
                $element->removeAttribute($attr->name);

                continue;
            }
            if (in_array($name, $urlAttributes, true)) {
                // Allow safe inline raster images (WYSIWYG editors paste these),
                // but never SVG (can carry script) or other data: payloads.
                $isSafeDataImage = (bool) preg_match('#^data:image/(png|jpe?g|gif|webp);base64,#', $value);
                foreach ($dangerousSchemes as $scheme) {
                    if (str_starts_with($value, $scheme)) {
                        if ($scheme === 'data:' && $isSafeDataImage) {
                            break;
                        }
                        $element->removeAttribute($attr->name);
                        break;
                    }
                }
            }
        }
    }

    $root = $dom->getElementById('__sanitize_root__');
    $clean = '';
    if ($root) {
        foreach ($root->childNodes as $child) {
            $clean .= $dom->saveHTML($child);
        }
    }

    return $clean;
}

/**
 * Comments never need HTML. Reduce to plain text on input.
 */
function sanitizeComment($comment): string
{
    return trim(strip_tags((string) $comment));
}

function stripslashesNull($text): string
{
    if ($text != null) {
        return stripslashes($text);
    } else {
        return '';
    }
}

function replaceCDN($text): string
{
    if (config('app.cdn_url') != null && config('app.cdn_url') != config('app.url')) {
        return str_replace(config('app.url'), config('app.cdn_url'), $text);
    } else {
        return $text;
    }

}
