<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class ApiTokenRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    public function create(int $accountId, string $tokenHash, string $expiresAt): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO api_tokens (account_id, token_hash, expires_at) VALUES (:account_id, :token_hash, :expires_at)'
        );
        $stmt->execute([
            'account_id' => $accountId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);
    }

    public function accountForToken(string $tokenHash): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT a.account_id, a.full_name, a.phone, a.email, a.city, a.role, a.status, a.created_at
             FROM api_tokens t
             JOIN accounts a ON a.account_id = t.account_id
             WHERE t.token_hash = :hash AND t.expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute(['hash' => $tokenHash]);
        return $stmt->fetch() ?: null;
    }

    public function delete(string $tokenHash): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM api_tokens WHERE token_hash = :hash');
        $stmt->execute(['hash' => $tokenHash]);
    }
}
