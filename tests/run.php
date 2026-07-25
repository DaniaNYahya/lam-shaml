<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use LamShaml\Services\ArabicNormalizer;
use LamShaml\Services\MatchingService;

$passed = 0;
$failed = 0;

function check(string $name, bool $condition): void {
    global $passed, $failed;
    echo ($condition ? "[PASS] " : "[FAIL] ") . $name . PHP_EOL;
    $condition ? $passed++ : $failed++;
}

check('normalizes hamza and tashkeel', ArabicNormalizer::normalize('أَحْمَد') === 'احمد');
check('normalizes maqsurah and taa marbutah', ArabicNormalizer::normalize('هدى فاطمة') === 'هدي فاطمه');
check('supports token ordering similarity', ArabicNormalizer::similarity('أحمد عبد الرحمن سالم', 'سالم احمد عبدالرحمن') > 60);

$service = new MatchingService(
    requests: null,
    matches: null,
    notifications: null
);
$score = $service->score(
    ['full_name' => 'أحمد عبد الرحمن سالم', 'age' => 34, 'gender' => 'male', 'city' => 'غزة', 'area' => 'الرمال', 'last_known_place' => 'مستشفى الشفاء'],
    ['full_name' => 'احمد عبدالرحمن سالم', 'age' => 35, 'gender' => 'male', 'city' => 'غزة', 'area' => 'الرمال', 'last_known_place' => 'قرب مستشفى الشفاء']
);
check('matching score is high for close Arabic names', $score['total_score'] >= 75);
check('script tag escapes through helper', e('<script>alert(1)</script>') === '&lt;script&gt;alert(1)&lt;/script&gt;');
check('phone mask hides middle digits', mask_phone('0591234512') === '059••••12');
check('password hash verifies seed password', password_verify('Admin@123', '$2y$10$f8zQCIODBum.P79TNle/EO17TvgmSCOQAJe8cWCoShu4XRMvM5UU2'));

echo PHP_EOL . "Passed: $passed, Failed: $failed" . PHP_EOL;
exit($failed > 0 ? 1 : 0);
