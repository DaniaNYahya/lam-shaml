<?php

declare(strict_types=1);

namespace App\Services\Matching;

use App\Services\ArabicNormalizer;

class LocationMatchingStrategy
{
    public function score(?string $leftCity, ?string $leftArea, ?string $rightCity, ?string $rightArea): float
    {
        $cityMatch = ArabicNormalizer::normalize((string)$leftCity) === ArabicNormalizer::normalize((string)$rightCity);
        $areaMatch = ArabicNormalizer::normalize((string)$leftArea) === ArabicNormalizer::normalize((string)$rightArea);
        if ($cityMatch && $areaMatch) {
            return 100.0;
        }
        if ($cityMatch) {
            return 75.0;
        }
        if ($areaMatch && $leftArea && $rightArea) {
            return 45.0;
        }
        return 0.0;
    }

    public function placeScore(?string $left, ?string $right): float
    {
        $a = ArabicNormalizer::normalize((string)$left);
        $b = ArabicNormalizer::normalize((string)$right);
        if ($a === '' || $b === '') {
            return 0.0;
        }
        similar_text($a, $b, $percent);
        return round($percent, 2);
    }
}
