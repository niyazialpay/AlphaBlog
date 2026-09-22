<?php

use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
            return strip_tags($request[0]);
        } else {
            return strip_tags($request);
        }
    }

    return null;

}

function content($content): string
{
    $allowed = '<br><br/><br /><a><b><strong><em><i><div><p><img><li><ul><ol><table><tr><td><h1><h2><h2><h3><h4><h5><h6><span><code><pre><blockquote><u><iframe><del><strike><s><sub><sup><hr>';

    return sanitizeHtml(strip_tags((string) $content, $allowed));
}

function sanitizeHtml(string $html): string
{
    if (trim($html) === '') {
        return $html;
    }

    $dangerousSchemes = ['javascript:', 'vbscript:', 'data:'];
    $urlAttributes = ['href', 'src', 'xlink:href', 'action', 'formaction', 'background', 'poster'];

    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    $loaded = $dom->loadHTML(
        '<?xml encoding="UTF-8"><div id="__sanitize_root__">'.$html.'</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();

    if (! $loaded) {
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

function sanitizeComment($comment): string
{
    return trim(strip_tags((string) $comment));
}

function stripslashesNull($text): string
{
    return (string) $text;
}

function mediaConversionUrl(?Media $media, string $conversion, bool $fallbackToOriginal = true): ?string
{
    if ($media === null) {
        return null;
    }

    if ($media->hasGeneratedConversion($conversion)) {
        return $media->getFullUrl($conversion);
    }

    return $fallbackToOriginal ? $media->getFullUrl() : null;
}

function replaceCDN($text): string
{
    if (config('app.cdn_url') != null && config('app.cdn_url') != config('app.url')) {
        return str_replace(config('app.url'), config('app.cdn_url'), $text);
    } else {
        return $text;
    }

}
