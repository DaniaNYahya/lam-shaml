<?php
declare(strict_types=1);

namespace LamShaml\Services;

final class ArabicNormalizer
{
    public static function normalize(string $value, bool $removeArticle = true): string
    {
        $value = trim(mb_strtolower($value, 'UTF-8'));
        $value = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $value) ?? $value;
        $value = str_replace('ـ', '', $value);
        $value = strtr($value, [
            'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا',
            'ى' => 'ي', 'ة' => 'ه', 'ؤ' => 'و', 'ئ' => 'ي',
        ]);
        $value = preg_replace('/[^\p{Arabic}\p{Latin}\p{N}\s]/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        $value = trim($value);
        if ($removeArticle) {
            $tokens = array_map(static function (string $token): string {
                return mb_strlen($token, 'UTF-8') > 3 && str_starts_with($token, 'ال')
                    ? mb_substr($token, 2, null, 'UTF-8')
                    : $token;
            }, explode(' ', $value));
            $value = implode(' ', $tokens);
        }
        return $value;
    }

    public static function sortedTokens(string $value): string
    {
        $tokens = array_values(array_filter(explode(' ', self::normalize($value))));
        sort($tokens, SORT_STRING);
        return implode(' ', $tokens);
    }

    public static function similarity(string $left, string $right): float
    {
        $left = self::normalize($left);
        $right = self::normalize($right);
        if ($left === '' || $right === '') {
            return 0.0;
        }
        if (str_contains($left, $right) || str_contains($right, $left)) {
            return 92.0;
        }
        $a = self::sortedTokens($left);
        $b = self::sortedTokens($right);
        similar_text($a, $b, $percent);
        $lev = levenshtein($a, $b);
        $max = max(mb_strlen($a, 'UTF-8'), mb_strlen($b, 'UTF-8'), 1);
        $levScore = max(0, 100 - (($lev / $max) * 100));
        return round(max($percent, $levScore), 2);
    }
}
