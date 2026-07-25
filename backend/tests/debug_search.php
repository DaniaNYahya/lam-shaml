<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

try {
    $repo = new \App\Repositories\RequestRepository();
    $items = $repo->search(['name' => \App\Services\ArabicNormalizer::normalize('احمد عبدالرحمن سالم')]);
    echo "items=" . count($items) . PHP_EOL;
    if ($items) {
        var_export($items[0]);
    }
} catch (Throwable $e) {
    echo get_class($e) . ': ' . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
