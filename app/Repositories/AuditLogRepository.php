<?php
declare(strict_types=1);

namespace LamShaml\Repositories;

use LamShaml\Core\Database;
use PDO;

final class AuditLogRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    public function log(?int $accountId, string $action, string $table, int $recordId): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO audit_logs (account_id, action, table_name, record_id) VALUES (?, ?, ?, ?)');
        $stmt->execute([$accountId, $action, $table, $recordId]);
    }

    public function latest(int $limit = 20): array
    {
        $stmt = $this->pdo->prepare('SELECT a.*, u.full_name FROM audit_logs a LEFT JOIN accounts u ON u.account_id = a.account_id ORDER BY a.created_at DESC LIMIT :limit');
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
