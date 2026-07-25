<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\HttpException;
use App\Repositories\AccountRepository;
use App\Repositories\ApiTokenRepository;
use App\Repositories\AuditLogRepository;
use DateTimeImmutable;

class AuthService
{
    private AccountRepository $accounts;
    private ApiTokenRepository $tokens;
    private ValidationService $validator;

    public function __construct()
    {
        $this->accounts = new AccountRepository();
        $this->tokens = new ApiTokenRepository();
        $this->validator = new ValidationService();
    }

    public function register(array $data): array
    {
        $this->validator->require($data, ['full_name', 'phone', 'email', 'password', 'city']);
        $this->validator->email($data['email']);
        if ($this->accounts->findByEmail($data['email'])) {
            throw new HttpException('Email already exists', 409);
        }
        if (strlen((string)$data['password']) < 8) {
            throw new HttpException('Password must be at least 8 characters', 422);
        }

        $id = $this->accounts->create([
            'full_name' => trim($data['full_name']),
            'phone' => trim($data['phone']),
            'email' => trim($data['email']),
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'city' => trim($data['city']),
            'role' => 'user',
            'status' => 'active',
        ]);
        (new AuditLogRepository())->record($id, 'register', 'accounts', $id);
        return $this->issueSession($this->accounts->findById($id));
    }

    public function login(array $data): array
    {
        $this->validator->require($data, ['email', 'password']);
        $account = $this->accounts->findByEmail($data['email']);
        if (!$account || !password_verify($data['password'], $account['password_hash'])) {
            throw new HttpException('Invalid email or password', 401);
        }
        if ($account['status'] !== 'active') {
            throw new HttpException('Account is not active', 403);
        }
        unset($account['password_hash']);
        return $this->issueSession($account);
    }

    public function currentAccount(?string $plainToken): ?array
    {
        if (!$plainToken) {
            return null;
        }
        return $this->tokens->accountForToken(hash('sha256', $plainToken));
    }

    public function logout(?string $plainToken): void
    {
        if ($plainToken) {
            $this->tokens->delete(hash('sha256', $plainToken));
        }
    }

    private function issueSession(?array $account): array
    {
        if (!$account) {
            throw new HttpException('Account not found', 404);
        }
        $token = rtrim(strtr(base64_encode(random_bytes(40)), '+/', '-_'), '=');
        $config = require BASE_PATH . '/config/config.php';
        $expires = (new DateTimeImmutable('+' . $config['auth']['token_days'] . ' days'))->format('Y-m-d H:i:s');
        $this->tokens->create((int)$account['account_id'], hash('sha256', $token), $expires);
        unset($account['password_hash']);
        return ['account' => $account, 'token' => $token];
    }
}
