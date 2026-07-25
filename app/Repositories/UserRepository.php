<?php
declare(strict_types=1);

namespace LamShaml\Repositories;

use LamShaml\Core\Database;
use PDO;

final class UserRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM accounts WHERE account_id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM accounts WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function emailOrPhoneExists(string $email, string $phone): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM accounts WHERE email = ? OR phone = ?');
        $stmt->execute([$email, $phone]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO accounts (full_name, phone, email, password_hash, city, role, status) VALUES (?, ?, ?, ?, ?, ?, "active")');
        $stmt->execute([
            $data['full_name'],
            $data['phone'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['city'],
            $data['role'] ?? 'user',
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function paginate(int $page = 1, int $perPage = 20): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $stmt = $this->pdo->prepare('SELECT SQL_CALC_FOUND_ROWS * FROM accounts ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return ['items' => $stmt->fetchAll(), 'total' => (int)$this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn()];
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->pdo->prepare('UPDATE accounts SET status = ? WHERE account_id = ?');
        $stmt->execute([$status, $id]);
    }
}
