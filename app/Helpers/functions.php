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
    return addslashes(strip_tags($content, '<br><br/><br /><a><b><strong><em><i><div><p><img><li><ul><ol><table><tr><td><h1><h2><h2><h3><h4><h5><h6><span><code><pre><blockquote><u>'));
}

function stripslashesNull($text): string
{
    if ($text != null) {
        return stripslashes($text);
    } else {
        return '';
    }
}
