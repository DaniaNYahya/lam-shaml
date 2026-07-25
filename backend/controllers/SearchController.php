<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\RequestRepository;
use App\Services\ArabicNormalizer;
use App\Services\MatchingService;

class SearchController extends Controller
{
    public function index(Request $request): void
    {
        $query = $request->query();
        if (!empty($query['name'])) {
            $query['name'] = ArabicNormalizer::normalize((string)$query['name']);
        }
        if (!empty($query['gender'])) {
            $query['gender'] = $this->normalizeGender((string)$query['gender']);
        }
        $items = (new RequestRepository())->search($query);
        $matcher = new MatchingService();
        $probe = [
            'full_name' => $query['name'] ?? '',
            'age' => $query['age'] ?? null,
            'gender' => $query['gender'] ?? 'unknown',
            'city' => $query['city'] ?? '',
            'area' => $query['area'] ?? '',
            'last_known_place' => $query['last_known_place'] ?? '',
        ];
        foreach ($items as &$item) {
            $item['match_percent'] = !empty($query['name'])
                ? $matcher->score($probe, $item)['total_score']
                : 0;
        }
        usort($items, fn(array $a, array $b) => ($b['match_percent'] <=> $a['match_percent']) ?: ($b['request_id'] <=> $a['request_id']));
        Response::json(['items' => $items]);
    }

    private function normalizeGender(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        if (in_array($value, ['male', 'm', 'ذكر', 'رجل'], true)) {
            return 'male';
        }
        if (in_array($value, ['female', 'f', 'أنثى', 'انثى', 'امرأة'], true)) {
            return 'female';
        }
        return 'unknown';
    }
}
