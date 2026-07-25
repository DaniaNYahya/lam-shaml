<?php
declare(strict_types=1);

namespace LamShaml\Repositories;

use LamShaml\Core\Database;
use PDO;

final class NotificationRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    public function create(int $accountId, string $message, string $type = 'info'): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO notifications (account_id, message, type) VALUES (?, ?, ?)');
        $stmt->execute([$accountId, $message, $type]);
    }

    public function createAdmin(string $message, string $type = 'admin'): void
    {
        $ids = $this->pdo->query("SELECT account_id FROM accounts WHERE role = 'admin' AND status = 'active'")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($ids as $id) {
            $this->create((int)$id, $message, $type);
        }
    }

    public function unreadCount(int $accountId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM notifications WHERE account_id = ? AND is_read = 0');
        $stmt->execute([$accountId]);
        return (int)$stmt->fetchColumn();
    }

    public function list(int $accountId, int $page = 1, int $perPage = 20): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $stmt = $this->pdo->prepare('SELECT SQL_CALC_FOUND_ROWS * FROM notifications WHERE account_id = :id ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue('id', $accountId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return ['items' => $stmt->fetchAll(), 'total' => (int)$this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn()];
    }

    public function markRead(int $accountId, ?int $id = null): void
    {
        if ($id) {
            $stmt = $this->pdo->prepare('UPDATE notifications SET is_read = 1 WHERE account_id = ? AND notification_id = ?');
            $stmt->execute([$accountId, $id]);
            return;
        }
        $stmt = $this->pdo->prepare('UPDATE notifications SET is_read = 1 WHERE account_id = ?');
        $stmt->execute([$accountId]);
    }
}
