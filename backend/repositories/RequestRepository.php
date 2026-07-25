<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class RequestRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    public function create(array $request, array $member, array $location, ?array $document = null): int
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO reunification_requests (account_id, request_type, status, priority, description, contact_phone)
                 VALUES (:account_id, :request_type, :status, :priority, :description, :contact_phone)'
            );
            $stmt->execute($request);
            $requestId = (int)$this->pdo->lastInsertId();

            $member['request_id'] = $requestId;
            $stmt = $this->pdo->prepare(
                'INSERT INTO family_members
                 (request_id, full_name, normalized_name, age, gender, original_city, relationship, health_status, distinctive_marks)
                 VALUES (:request_id, :full_name, :normalized_name, :age, :gender, :original_city, :relationship, :health_status, :distinctive_marks)'
            );
            $stmt->execute($member);

            $location['request_id'] = $requestId;
            $stmt = $this->pdo->prepare(
                'INSERT INTO locations (request_id, city, area, last_known_place, current_location, last_seen_date)
                 VALUES (:request_id, :city, :area, :last_known_place, :current_location, :last_seen_date)'
            );
            $stmt->execute($location);

            if ($document) {
                $document['request_id'] = $requestId;
                $stmt = $this->pdo->prepare(
                    'INSERT INTO documents (request_id, file_type, file_url) VALUES (:request_id, :file_type, :file_url)'
                );
                $stmt->execute($document);
            }

            $this->pdo->commit();
            return $requestId;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare($this->baseSelect() . ' WHERE r.request_id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function mine(int $accountId): array
    {
        $stmt = $this->pdo->prepare($this->baseSelect() . ' WHERE r.account_id = :account_id ORDER BY r.created_at DESC');
        $stmt->execute(['account_id' => $accountId]);
        return $stmt->fetchAll();
    }

    public function candidatesFor(int $requestId, string $oppositeType): array
    {
        $stmt = $this->pdo->prepare(
            $this->baseSelect() .
            ' WHERE r.request_id <> :id AND r.request_type = :type AND r.status IN ("pending", "active", "approved") LIMIT 100'
        );
        $stmt->execute(['id' => $requestId, 'type' => $oppositeType]);
        return $stmt->fetchAll();
    }

    public function search(array $filters): array
    {
        $where = ['r.status IN ("pending", "active", "approved")'];
        $params = [];
        foreach (['request_type' => 'r.request_type', 'city' => 'l.city', 'area' => 'l.area', 'gender' => 'm.gender'] as $key => $column) {
            if (!empty($filters[$key])) {
                if ($key === 'request_type') {
                    $where[] = "$column = :$key";
                    $params[$key] = $filters[$key];
                } else {
                    $where[] = "$column LIKE :$key";
                    $params[$key] = '%' . $filters[$key] . '%';
                }
            }
        }
        if (!empty($filters['age'])) {
            $where[] = 'ABS(COALESCE(m.age, 0) - :age) <= 5';
            $params['age'] = (int)$filters['age'];
        }
        $stmt = $this->pdo->prepare($this->baseSelect() . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY r.created_at DESC LIMIT 50');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function adminList(int $page = 1, int $perPage = 50): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $stmt = $this->pdo->prepare($this->baseSelect() . ' ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->pdo->prepare('UPDATE reunification_requests SET status = :status WHERE request_id = :id');
        $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public function stats(?int $accountId = null): array
    {
        $where = $accountId ? 'WHERE account_id = :account_id' : '';
        $stmt = $this->pdo->prepare(
            "SELECT
                COUNT(*) requests,
                SUM(request_type = 'missing') missing,
                SUM(request_type = 'found') found
             FROM reunification_requests $where"
        );
        $stmt->execute($accountId ? ['account_id' => $accountId] : []);
        $row = $stmt->fetch() ?: [];

        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) matches FROM match_records mr
             JOIN reunification_requests r ON r.request_id = mr.request_id ' .
             ($accountId ? 'WHERE r.account_id = :account_id' : '')
        );
        $stmt->execute($accountId ? ['account_id' => $accountId] : []);
        $matches = $stmt->fetchColumn();

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM notifications' . ($accountId ? ' WHERE account_id = :account_id' : ''));
        $stmt->execute($accountId ? ['account_id' => $accountId] : []);

        return [
            'requests' => (int)($row['requests'] ?? 0),
            'missing' => (int)($row['missing'] ?? 0),
            'found' => (int)($row['found'] ?? 0),
            'matches' => (int)$matches,
            'notifications' => (int)$stmt->fetchColumn(),
        ];
    }

    private function baseSelect(): string
    {
        return 'SELECT r.*, m.full_name, m.normalized_name, m.age, m.gender, m.original_city, m.relationship,
                       m.health_status, m.distinctive_marks, l.city, l.area, l.last_known_place,
                       l.current_location, l.last_seen_date,
                       CONCAT(LEFT(r.contact_phone, 3), "••••", RIGHT(r.contact_phone, 2)) AS contact_phone
                FROM reunification_requests r
                JOIN family_members m ON m.request_id = r.request_id
                JOIN locations l ON l.request_id = r.request_id';
    }
}
