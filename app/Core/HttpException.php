<?php
declare(strict_types=1);

namespace LamShaml\Core;

use RuntimeException;

final class HttpException extends RuntimeException
{
    public function __construct(public int $status, string $message)
    {
        parent::__construct($message, $status);
    }
}
