<?php
declare(strict_types=1);

namespace LamShaml\Services;

use LamShaml\Repositories\MatchRepository;
use LamShaml\Repositories\NotificationRepository;
use LamShaml\Repositories\RequestRepository;

final class MatchingService
{
    public function __construct(
        private ?RequestRepository $requests = null,
        private ?MatchRepository $matches = null,
        private ?NotificationRepository $notifications = null
    ) {}

    public function createMatchesFor(int $requestId): array
    {
        $this->requests ??= new RequestRepository();
        $this->matches ??= new MatchRepository();
        $this->notifications ??= new NotificationRepository();
        $request = $this->requests->find($requestId);
        if (!$request) {
            return [];
        }
        $opposite = $request['request_type'] === 'missing' ? 'found' : 'missing';
        $created = [];
        foreach ($this->requests->candidatesFor($requestId, $opposite) as $candidate) {
            $score = $this->score($request, $candidate);
            if ($score['total_score'] < 60) {
                continue;
            }
            $matchId = $this->matches->upsert($requestId, (int)$candidate['request_id'], $score);
            $created[] = ['match_id' => $matchId] + $score;
            $this->notifications->create((int)$request['account_id'], 'ظهر تطابق محتمل بنسبة ' . (int)$score['total_score'] . '% للبلاغ رقم ' . $requestId, 'match_found');
            $this->notifications->createAdmin('تطابق محتمل جديد يحتاج مراجعة بين البلاغين ' . $requestId . ' و' . $candidate['request_id'], 'match_review');
        }
        return $created;
    }

    public function score(array $left, array $right): array
    {
        $name = ArabicNormalizer::similarity($left['full_name'] ?? '', $right['full_name'] ?? '');
        $age = $this->ageScore($left['age'] ?? null, $right['age'] ?? null);
        $city = $this->textScore(($left['city'] ?? '') . ' ' . ($left['area'] ?? ''), ($right['city'] ?? '') . ' ' . ($right['area'] ?? ''));
        $gender = $this->genderScore($left['gender'] ?? 'unknown', $right['gender'] ?? 'unknown');
        $place = $this->textScore($left['last_known_place'] ?? '', ($right['last_known_place'] ?? '') . ' ' . ($right['current_location'] ?? ''));
        $total = ($name * 0.50) + ($age * 0.15) + ($city * 0.15) + ($gender * 0.10) + ($place * 0.10);
        return [
            'name_score' => round($name, 2),
            'age_score' => round($age, 2),
            'location_score' => round($city, 2),
            'gender_score' => round($gender, 2),
            'place_score' => round($place, 2),
            'total_score' => round($total, 2),
        ];
    }

    private function ageScore(mixed $left, mixed $right): float
    {
        if ($left === null || $right === null || $left === '' || $right === '') {
            return 50.0;
        }
        return max(0.0, 100.0 - abs((int)$left - (int)$right) * 8);
    }

    private function genderScore(string $left, string $right): float
    {
        if ($left === 'unknown' || $right === 'unknown') {
            return 50.0;
        }
        return $left === $right ? 100.0 : 0.0;
    }

    private function textScore(string $left, string $right): float
    {
        $left = ArabicNormalizer::normalize($left);
        $right = ArabicNormalizer::normalize($right);
        if ($left === '' || $right === '') {
            return 50.0;
        }
        if (str_contains($left, $right) || str_contains($right, $left)) {
            return 100.0;
        }
        similar_text($left, $right, $percent);
        return round($percent, 2);
    }
}
