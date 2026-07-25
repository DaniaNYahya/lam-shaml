<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\NotificationRepository;

class NotificationController extends Controller
{
    public function index(Request $request): void
    {
        $account = $this->auth($request);
        Response::json(['items' => (new NotificationRepository())->listForAccount((int)$account['account_id'])]);
    }

    public function read(Request $request, array $params): void
    {
        $account = $this->auth($request);
        (new NotificationRepository())->markRead((int)$account['account_id'], (int)$params['id']);
        Response::json([], 200, 'Notification marked as read');
    }
}
