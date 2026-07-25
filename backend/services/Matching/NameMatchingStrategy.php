<?php

declare(strict_types=1);

namespace App\Services\Matching;

interface NameMatchingStrategy
{
    public function score(string $left, string $right): float;
}
