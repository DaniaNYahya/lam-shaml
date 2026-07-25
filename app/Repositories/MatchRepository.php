<?php
declare(strict_types=1);

namespace LamShaml\Repositories;

use LamShaml\Core\Database;
use PDO;

final class MatchRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    public function upsert(int $requestId, int $matchedId, array $score): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO match_records (request_id, matched_request_id, name_score, location_score, age_score, gender_score, place_score, total_score, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, "pending")
             ON DUPLICATE KEY UPDATE name_score = VALUES(name_score), location_score = VALUES(location_score), age_score = VALUES(age_score), gender_score = VALUES(gender_score), place_score = VALUES(place_score), total_score = VALUES(total_score)'
        );
        $stmt->execute([$requestId, $matchedId, $score['name_score'], $score['location_score'], $score['age_score'], $score['gender_score'], $score['place_score'], $score['total_score']]);
        $found = $this->pdo->prepare('SELECT match_id FROM match_records WHERE request_id = ? AND matched_request_id = ?');
        $found->execute([$requestId, $matchedId]);
        return (int)$found->fetchColumn();
    }

    public function forRequest(int $requestId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT mr.*, m.full_name, m.age, m.gender, l.city, l.area, r.request_type, r.status, d.file_path
             FROM match_records mr
             JOIN reunification_requests r ON r.request_id = mr.matched_request_id
             JOIN family_members m ON m.request_id = r.request_id
             JOIN locations l ON l.request_id = r.request_id
             LEFT JOIN documents d ON d.request_id = r.request_id
             WHERE mr.request_id = ?
             ORDER BY mr.total_score DESC"
        );
        $stmt->execute([$requestId]);
        return $stmt->fetchAll();
    }

    public function all(int $page = 1, int $perPage = 20): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $stmt = $this->pdo->prepare(
            "SELECT SQL_CALC_FOUND_ROWS mr.*, lm.full_name left_name, rm.full_name right_name
             FROM match_records mr
             JOIN family_members lm ON lm.request_id = mr.request_id
             JOIN family_members rm ON rm.request_id = mr.matched_request_id
             ORDER BY mr.status = 'pending' DESC, mr.total_score DESC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return ['items' => $stmt->fetchAll(), 'total' => (int)$this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn()];
    }

    public function report(int $accountId, int $requestId, int $matchedId, string $notes, string $contactPhone): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO possible_match_reports (account_id, request_id, matched_request_id, notes, contact_phone) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$accountId, $requestId, $matchedId, $notes, $contactPhone]);
        return (int)$this->pdo->lastInsertId();
    }

    public function reports(int $page = 1, int $perPage = 20): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $stmt = $this->pdo->prepare(
            "SELECT SQL_CALC_FOUND_ROWS p.*, a.full_name reporter_name, lm.full_name left_name, rm.full_name right_name
             FROM possible_match_reports p
             JOIN accounts a ON a.account_id = p.account_id
             JOIN family_members lm ON lm.request_id = p.request_id
             JOIN family_members rm ON rm.request_id = p.matched_request_id
             ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return ['items' => $stmt->fetchAll(), 'total' => (int)$this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn()];
    }

    public function decide(int $matchId, string $status, ?int $adminId): void
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM match_records WHERE match_id = ? FOR UPDATE');
            $stmt->execute([$matchId]);
            $match = $stmt->fetch();
            if (!$match) {
                throw new \RuntimeException('Match not found');
            }
            $update = $this->pdo->prepare('UPDATE match_records SET status = ?, admin_notes = ? WHERE match_id = ?');
            $update->execute([$status, $status === 'approved' ? 'تم التأكيد بواسطة المسؤول' : null, $matchId]);
            if ($status === 'approved' || $status === 'resolved') {
                $resolved = $this->pdo->prepare("UPDATE reunification_requests SET status = 'resolved' WHERE request_id IN (?, ?)");
                $resolved->execute([(int)$match['request_id'], (int)$match['matched_request_id']]);
            }
            $audit = $this->pdo->prepare('INSERT INTO audit_logs (account_id, action, table_name, record_id) VALUES (?, ?, "match_records", ?)');
            $audit->execute([$adminId, 'match_' . $status, $matchId]);
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
