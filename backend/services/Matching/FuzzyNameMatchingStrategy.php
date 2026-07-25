<?php

declare(strict_types=1);

namespace App\Services\Matching;

use App\Services\ArabicNormalizer;

class FuzzyNameMatchingStrategy implements NameMatchingStrategy
{
    public function score(string $left, string $right): float
    {
        $a = ArabicNormalizer::normalize($left);
        $b = ArabicNormalizer::normalize($right);
        if ($a === '' || $b === '') {
            return 0.0;
        }
        similar_text($a, $b, $percent);
        $distance = levenshtein($a, $b);
        $max = max(strlen($a), strlen($b), 1);
        $levenshteinPercent = max(0, (1 - ($distance / $max)) * 100);
        return round(max($percent, $levenshteinPercent), 2);
    }
}
