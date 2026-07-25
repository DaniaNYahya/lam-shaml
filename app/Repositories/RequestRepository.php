<?php
declare(strict_types=1);

namespace LamShaml\Repositories;

use LamShaml\Core\Database;
use LamShaml\Services\ArabicNormalizer;
use PDO;

final class RequestRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    public function create(array $request, array $member, array $location, ?array $document): int
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare('INSERT INTO reunification_requests (account_id, request_type, status, priority, description, contact_phone) VALUES (?, ?, "pending", ?, ?, ?)');
            $stmt->execute([$request['account_id'], $request['request_type'], $request['priority'], $request['description'], $request['contact_phone']]);
            $id = (int)$this->pdo->lastInsertId();

            $stmt = $this->pdo->prepare('INSERT INTO family_members (request_id, full_name, normalized_name, age, gender, original_city, relationship, health_status, distinctive_marks, registered_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $id,
                $member['full_name'],
                ArabicNormalizer::normalize($member['full_name']),
                $member['age'] !== '' ? $member['age'] : null,
                $member['gender'],
                $member['original_city'],
                $member['relationship'] ?: null,
                $member['health_status'] ?: null,
                $member['distinctive_marks'] ?: null,
                $member['registered_by'] ?: null,
            ]);

            $stmt = $this->pdo->prepare('INSERT INTO locations (request_id, city, area, last_known_place, current_location, last_seen_date) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $id,
                $location['city'],
                $location['area'] ?: null,
                $location['last_known_place'] ?: null,
                $location['current_location'] ?: null,
                $location['last_seen_date'] ?: null,
            ]);

            if ($document) {
                $stmt = $this->pdo->prepare('INSERT INTO documents (request_id, file_type, file_path) VALUES (?, ?, ?)');
                $stmt->execute([$id, $document['file_type'], $document['file_path']]);
            }
            $this->pdo->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare($this->baseSelect() . ' WHERE r.request_id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function mine(int $accountId, int $page = 1, int $perPage = 10): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $stmt = $this->pdo->prepare($this->baseSelect() . ' WHERE r.account_id = :account_id ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue('account_id', $accountId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $count = $this->pdo->prepare('SELECT COUNT(*) FROM reunification_requests WHERE account_id = ?');
        $count->execute([$accountId]);
        return ['items' => $stmt->fetchAll(), 'total' => (int)$count->fetchColumn()];
    }

    public function all(int $page = 1, int $perPage = 20): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $stmt = $this->pdo->prepare($this->baseSelect() . ' ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return ['items' => $stmt->fetchAll(), 'total' => (int)$this->pdo->query('SELECT COUNT(*) FROM reunification_requests')->fetchColumn()];
    }

    public function candidatesFor(int $requestId, string $type): array
    {
        $stmt = $this->pdo->prepare($this->baseSelect() . " WHERE r.request_id <> ? AND r.request_type = ? AND r.status IN ('pending','active','approved') LIMIT 100");
        $stmt->execute([$requestId, $type]);
        return $stmt->fetchAll();
    }

    public function searchCandidates(array $filters): array
    {
        $where = ["r.status IN ('pending','active','approved','resolved')"];
        $params = [];
        foreach (['request_type' => 'r.request_type', 'gender' => 'm.gender', 'status' => 'r.status'] as $key => $column) {
            if (($filters[$key] ?? '') !== '') {
                $where[] = "$column = ?";
                $params[] = $filters[$key];
            }
        }
        foreach (['city' => 'l.city', 'area' => 'l.area', 'place' => 'l.last_known_place'] as $key => $column) {
            if (($filters[$key] ?? '') !== '') {
                $where[] = "$column LIKE ?";
                $params[] = '%' . $filters[$key] . '%';
            }
        }
        if (($filters['age'] ?? '') !== '') {
            $where[] = 'ABS(COALESCE(m.age, 0) - ?) <= 8';
            $params[] = (int)$filters['age'];
        }
        if (($filters['name'] ?? '') !== '') {
            $where[] = '(m.normalized_name LIKE ? OR m.full_name LIKE ?)';
            $name = '%' . ArabicNormalizer::normalize($filters['name']) . '%';
            $params[] = $name;
            $params[] = '%' . $filters['name'] . '%';
        }
        $stmt = $this->pdo->prepare($this->baseSelect() . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY r.created_at DESC LIMIT 120');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status): ?int
    {
        $owner = $this->pdo->prepare('SELECT account_id FROM reunification_requests WHERE request_id = ?');
        $owner->execute([$id]);
        $accountId = $owner->fetchColumn();
        $stmt = $this->pdo->prepare('UPDATE reunification_requests SET status = ? WHERE request_id = ?');
        $stmt->execute([$status, $id]);
        return $accountId ? (int)$accountId : null;
    }

    public function stats(?int $accountId = null): array
    {
        $where = $accountId ? 'WHERE account_id = :id' : '';
        $stmt = $this->pdo->prepare("SELECT COUNT(*) total, SUM(status IN ('pending','active','approved')) active, SUM(status = 'resolved') resolved, SUM(status = 'pending') pending FROM reunification_requests $where");
        $stmt->execute($accountId ? ['id' => $accountId] : []);
        $row = $stmt->fetch() ?: [];
        return [
            'total' => (int)($row['total'] ?? 0),
            'active' => (int)($row['active'] ?? 0),
            'resolved' => (int)($row['resolved'] ?? 0),
            'pending' => (int)($row['pending'] ?? 0),
        ];
    }

    public function cityStats(): array
    {
        return $this->pdo->query('SELECT l.city, COUNT(*) total FROM locations l GROUP BY l.city ORDER BY total DESC LIMIT 8')->fetchAll();
    }

    private function baseSelect(): string
    {
        return "SELECT r.*, a.full_name AS owner_name, a.role AS owner_role,
                       m.full_name, m.normalized_name, m.age, m.gender, m.original_city,
                       m.relationship, m.health_status, m.distinctive_marks, m.registered_by,
                       l.city, l.area, l.last_known_place, l.current_location, l.last_seen_date,
                       d.file_path, d.file_type
                FROM reunification_requests r
                JOIN accounts a ON a.account_id = r.account_id
                JOIN family_members m ON m.request_id = r.request_id
                JOIN locations l ON l.request_id = r.request_id
                LEFT JOIN documents d ON d.request_id = r.request_id";
    }
}
