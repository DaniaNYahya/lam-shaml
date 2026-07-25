<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MatchRepository;
use App\Repositories\RequestRepository;
use App\Services\Events\NotificationObserver;
use App\Services\Events\RequestEventPublisher;
use App\Services\Matching\ExactNameMatchingStrategy;
use App\Services\Matching\FuzzyNameMatchingStrategy;
use App\Services\Matching\LocationMatchingStrategy;

class MatchingService
{
    private RequestRepository $requests;
    private MatchRepository $matches;
    private ExactNameMatchingStrategy $exact;
    private FuzzyNameMatchingStrategy $fuzzy;
    private LocationMatchingStrategy $location;

    public function __construct()
    {
        $this->requests = new RequestRepository();
        $this->matches = new MatchRepository();
        $this->exact = new ExactNameMatchingStrategy();
        $this->fuzzy = new FuzzyNameMatchingStrategy();
        $this->location = new LocationMatchingStrategy();
    }

    public function createMatchesFor(int $requestId): array
    {
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
            $score['request_id'] = $requestId;
            $score['matched_request_id'] = (int)$candidate['request_id'];
            $this->matches->upsert($score);
            $created[] = $score;

            $publisher = new RequestEventPublisher();
            $publisher->subscribe(new NotificationObserver());
            $publisher->publish('match.found', [
                'account_id' => $request['account_id'],
                'request_id' => $requestId,
                'score' => $score['total_score'],
            ]);
        }
        return $created;
    }

    public function score(array $left, array $right): array
    {
        $nameScore = max(
            $this->exact->score($left['full_name'] ?? '', $right['full_name'] ?? ''),
            $this->fuzzy->score($left['full_name'] ?? '', $right['full_name'] ?? '')
        );
        $locationScore = $this->location->score($left['city'] ?? '', $left['area'] ?? '', $right['city'] ?? '', $right['area'] ?? '');
        $placeScore = $this->location->placeScore($left['last_known_place'] ?? '', $right['last_known_place'] ?? '');
        $ageScore = $this->ageScore($left['age'] ?? null, $right['age'] ?? null);
        $genderScore = $this->genderScore($left['gender'] ?? 'unknown', $right['gender'] ?? 'unknown');
        $total = ($nameScore * 0.50) + ($ageScore * 0.15) + ($locationScore * 0.15) + ($genderScore * 0.10) + ($placeScore * 0.10);

        return [
            'name_score' => round($nameScore, 2),
            'location_score' => round(($locationScore + $placeScore) / 2, 2),
            'age_score' => round($ageScore, 2),
            'gender_score' => round($genderScore, 2),
            'total_score' => round($total, 2),
        ];
    }

    private function ageScore(mixed $left, mixed $right): float
    {
        if ($left === null || $right === null || $left === '' || $right === '') {
            return 50.0;
        }
        $diff = abs((int)$left - (int)$right);
        return max(0.0, 100.0 - ($diff * 8));
    }

    private function genderScore(string $left, string $right): float
    {
        if ($left === 'unknown' || $right === 'unknown') {
            return 50.0;
        }
        return $left === $right ? 100.0 : 0.0;
    }
}
