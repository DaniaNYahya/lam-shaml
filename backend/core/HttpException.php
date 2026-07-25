<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class HttpException extends RuntimeException
{
    public function __construct(string $message, int $statusCode)
    {
        parent::__construct($message, $statusCode);
    }
}
