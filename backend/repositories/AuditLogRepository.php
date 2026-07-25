<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class AuditLogRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    public function record(?int $accountId, string $action, string $table, int $recordId): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO audit_logs (account_id, action, table_name, record_id)
             VALUES (:account_id, :action, :table_name, :record_id)'
        );
        $stmt->execute([
            'account_id' => $accountId,
            'action' => $action,
            'table_name' => $table,
            'record_id' => $recordId,
        ]);
    }
}
