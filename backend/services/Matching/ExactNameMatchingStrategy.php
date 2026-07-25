<?php

declare(strict_types=1);

namespace App\Services\Matching;

use App\Services\ArabicNormalizer;

class ExactNameMatchingStrategy implements NameMatchingStrategy
{
    public function score(string $left, string $right): float
    {
        if (ArabicNormalizer::normalize($left) === ArabicNormalizer::normalize($right)) {
            return 100.0;
        }
        return ArabicNormalizer::sortedTokens($left) === ArabicNormalizer::sortedTokens($right) ? 96.0 : 0.0;
    }
}
