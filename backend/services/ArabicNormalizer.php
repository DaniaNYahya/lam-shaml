<?php

declare(strict_types=1);

namespace App\Services;

class ArabicNormalizer
{
    public static function normalize(string $value, bool $removeArticle = true): string
    {
        $value = trim(mb_strtolower($value, 'UTF-8'));
        $value = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $value) ?? $value;
        $value = str_replace('ـ', '', $value);
        $value = strtr($value, [
            'أ' => 'ا',
            'إ' => 'ا',
            'آ' => 'ا',
            'ٱ' => 'ا',
            'ى' => 'ي',
            'ة' => 'ه',
            'ؤ' => 'و',
            'ئ' => 'ي',
        ]);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        $value = trim($value);
        if ($removeArticle) {
            $parts = array_map(function (string $part): string {
                return mb_strlen($part, 'UTF-8') > 3 && str_starts_with($part, 'ال')
                    ? mb_substr($part, 2, null, 'UTF-8')
                    : $part;
            }, explode(' ', $value));
            $value = implode(' ', $parts);
        }
        return $value;
    }

    public static function sortedTokens(string $value): string
    {
        $parts = array_filter(explode(' ', self::normalize($value)));
        sort($parts, SORT_STRING);
        return implode(' ', $parts);
    }
}
