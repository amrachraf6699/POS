<?php

namespace App\Support;

final class LocaleDirection
{
    /**
     * @var array<int, string>
     */
    private const RTL_LOCALES = ['ar', 'fa', 'he', 'ur'];

    public static function for(string $locale): string
    {
        return self::isRtl($locale) ? 'rtl' : 'ltr';
    }

    public static function isRtl(string $locale): bool
    {
        return in_array(strtolower(str_replace('_', '-', $locale)), self::RTL_LOCALES, true)
            || in_array(strtolower(substr(str_replace('_', '-', $locale), 0, 2)), self::RTL_LOCALES, true);
    }
}
