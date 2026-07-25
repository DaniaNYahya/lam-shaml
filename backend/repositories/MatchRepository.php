<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class MatchRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    public function upsert(array $match): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO match_records
             (request_id, matched_request_id, name_score, location_score, age_score, gender_score, total_score, status)
             VALUES (:request_id, :matched_request_id, :name_score, :location_score, :age_score, :gender_score, :total_score, "pending")
             ON DUPLICATE KEY UPDATE
                name_score = VALUES(name_score),
                location_score = VALUES(location_score),
                age_score = VALUES(age_score),
                gender_score = VALUES(gender_score),
                total_score = VALUES(total_score)'
        );
        $stmt->execute($match);
        return (int)$this->pdo->lastInsertId();
    }

    public function listAdmin(): array
    {
        return $this->pdo
            ->query('SELECT * FROM match_records ORDER BY total_score DESC, created_at DESC LIMIT 100')
            ->fetchAll();
    }

    public function updateStatus(int $id, string $status, ?string $notes = null): void
    {
        $stmt = $this->pdo->prepare('UPDATE match_records SET status = :status, admin_notes = :notes WHERE match_id = :id');
        $stmt->execute(['status' => $status, 'notes' => $notes, 'id' => $id]);
    }

    public function createReport(array $report): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO possible_match_reports (account_id, request_id, matched_request_id, notes, contact_phone)
             VALUES (:account_id, :request_id, :matched_request_id, :notes, :contact_phone)'
        );
        $stmt->execute($report);
        return (int)$this->pdo->lastInsertId();
    }

    public function listReports(): array
    {
        return $this->pdo
            ->query('SELECT * FROM possible_match_reports ORDER BY created_at DESC LIMIT 100')
            ->fetchAll();
    }
}
