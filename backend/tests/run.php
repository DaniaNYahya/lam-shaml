<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use App\Core\HttpException;
use App\Services\ArabicNormalizer;
use App\Services\Matching\ExactNameMatchingStrategy;
use App\Services\Matching\FuzzyNameMatchingStrategy;
use App\Services\Matching\LocationMatchingStrategy;
use App\Services\ValidationService;

$passed = 0;
$failed = 0;

function check(string $name, callable $test): void
{
    global $passed, $failed;
    try {
        $test();
        $passed++;
        echo "[PASS] $name\n";
    } catch (Throwable $e) {
        $failed++;
        echo "[FAIL] $name: {$e->getMessage()}\n";
    }
}

function assertTrue(bool $condition, string $message = 'assertion failed'): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

check('Arabic normalizer removes diacritics and variants', function (): void {
    assertTrue(ArabicNormalizer::normalize('أحمد عبدُ الرحمن') === 'احمد عبد رحمن');
    assertTrue(ArabicNormalizer::normalize('هُدى مصطفى على') === 'هدي مصطفي علي');
});

check('Exact strategy supports reordered tokens', function (): void {
    $strategy = new ExactNameMatchingStrategy();
    assertTrue($strategy->score('هدى مصطفى علي', 'علي هدى مصطفى') >= 90);
});

check('Fuzzy strategy catches close Arabic names', function (): void {
    $strategy = new FuzzyNameMatchingStrategy();
    assertTrue($strategy->score('أحمد عبد الرحمن سالم', 'احمد عبدالرحمن سالم') >= 70);
});

check('Location strategy scores same city and area', function (): void {
    $strategy = new LocationMatchingStrategy();
    assertTrue($strategy->score('غزة', 'الرمال', 'غزة', 'الرمال') === 100.0);
});

check('Validation rejects missing required fields', function (): void {
    try {
        (new ValidationService())->require(['email' => 'a@example.com'], ['email', 'password']);
    } catch (HttpException $e) {
        assertTrue($e->getCode() === 422);
        return;
    }
    throw new RuntimeException('expected validation exception');
});

echo "\nPassed: $passed, Failed: $failed\n";
exit($failed > 0 ? 1 : 0);
