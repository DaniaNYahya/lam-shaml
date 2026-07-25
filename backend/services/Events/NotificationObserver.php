<?php

declare(strict_types=1);

namespace App\Services\Events;

use App\Services\NotificationService;

class NotificationObserver
{
    public function handle(string $event, array $payload): void
    {
        $service = new NotificationService();
        if ($event === 'request.created') {
            $service->notify((int)$payload['account_id'], 'تم إنشاء البلاغ رقم ' . $payload['request_id'] . ' وبدأت المطابقة التلقائية.', 'request_created');
        }
        if ($event === 'match.found') {
            $service->notify((int)$payload['account_id'], 'يوجد تطابق محتمل بنسبة ' . round((float)$payload['score']) . '% يحتاج إلى مراجعة.', 'match_found');
        }
        if ($event === 'status.changed') {
            $service->notify((int)$payload['account_id'], 'تم تحديث حالة البلاغ رقم ' . $payload['request_id'] . ' إلى ' . $payload['status'], 'status_changed');
        }
    }
}
