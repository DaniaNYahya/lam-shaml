<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class NotificationRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    public function create(int $accountId, string $message, string $type = 'info'): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO notifications (account_id, message, type) VALUES (:account_id, :message, :type)'
        );
        $stmt->execute(['account_id' => $accountId, 'message' => $message, 'type' => $type]);
        return (int)$this->pdo->lastInsertId();
    }

    public function listForAccount(int $accountId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM notifications WHERE account_id = :account_id ORDER BY created_at DESC LIMIT 50'
        );
        $stmt->execute(['account_id' => $accountId]);
        return $stmt->fetchAll();
    }

    public function markRead(int $accountId, int $notificationId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE notifications SET is_read = 1 WHERE notification_id = :id AND account_id = :account_id'
        );
        $stmt->execute(['id' => $notificationId, 'account_id' => $accountId]);
    }
}
