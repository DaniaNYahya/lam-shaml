<?php
declare(strict_types=1);

namespace LamShaml\Controllers;

use LamShaml\Core\View;
use LamShaml\Repositories\RequestRepository;
use LamShaml\Services\MatchingService;

final class SearchController
{
    public function index(): string
    {
        $filters = [
            'name' => trim((string)($_GET['name'] ?? '')),
            'age' => trim((string)($_GET['age'] ?? '')),
            'gender' => (string)($_GET['gender'] ?? ''),
            'city' => trim((string)($_GET['city'] ?? '')),
            'area' => trim((string)($_GET['area'] ?? '')),
            'place' => trim((string)($_GET['place'] ?? '')),
            'request_type' => (string)($_GET['request_type'] ?? ''),
            'status' => (string)($_GET['status'] ?? ''),
        ];
        $rows = [];
        if (array_filter($filters, static fn ($value) => $value !== '')) {
            $needle = [
                'full_name' => $filters['name'],
                'age' => $filters['age'],
                'gender' => $filters['gender'] ?: 'unknown',
                'city' => $filters['city'],
                'area' => $filters['area'],
                'last_known_place' => $filters['place'],
            ];
            $matching = new MatchingService();
            foreach ((new RequestRepository())->searchCandidates($filters) as $row) {
                $score = $matching->score($needle, $row);
                $rows[] = $row + ['search_score' => $score['total_score']];
            }
            usort($rows, static fn ($a, $b) => $b['search_score'] <=> $a['search_score']);
        }
        return View::render('search/index', ['title' => 'البحث الذكي', 'filters' => $filters, 'rows' => $rows]);
    }
}
