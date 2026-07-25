<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\HttpException;

class ValidationService
{
    public function require(array $data, array $fields): void
    {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
                throw new HttpException("Missing field: $field", 422);
            }
        }
    }

    public function email(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new HttpException('Invalid email address', 422);
        }
    }

    public function oneOf(string $field, string $value, array $allowed): void
    {
        if (!in_array($value, $allowed, true)) {
            throw new HttpException("Invalid $field value", 422);
        }
    }
}
