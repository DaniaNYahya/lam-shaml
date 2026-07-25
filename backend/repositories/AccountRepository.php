<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class AccountRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO accounts (full_name, phone, email, password_hash, city, role, status)
             VALUES (:full_name, :phone, :email, :password_hash, :city, :role, :status)'
        );
        $stmt->execute([
            'full_name' => $data['full_name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'city' => $data['city'],
            'role' => $data['role'] ?? 'user',
            'status' => $data['status'] ?? 'active',
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM accounts WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT account_id, full_name, phone, email, city, role, status, created_at FROM accounts WHERE account_id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function list(int $page = 1, int $perPage = 25): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $stmt = $this->pdo->prepare(
            'SELECT account_id, full_name, phone, email, city, role, status, created_at
             FROM accounts ORDER BY created_at DESC LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->pdo->prepare('UPDATE accounts SET status = :status WHERE account_id = :id');
        $stmt->execute(['status' => $status, 'id' => $id]);
    }
}
