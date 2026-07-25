<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\NotificationRepository;

class NotificationService
{
    private NotificationRepository $notifications;

    public function __construct()
    {
        $this->notifications = new NotificationRepository();
    }

    public function notify(int $accountId, string $message, string $type = 'info'): void
    {
        $this->notifications->create($accountId, $message, $type);
    }
}
