<?php
declare(strict_types=1);

namespace LamShaml\Controllers;

use LamShaml\Core\Auth;
use LamShaml\Core\View;
use LamShaml\Repositories\NotificationRepository;
use LamShaml\Repositories\RequestRepository;

final class DashboardController
{
    public function index(): string
    {
        $user = Auth::requireLogin();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $requests = new RequestRepository();
        return View::render('dashboard', [
            'title' => 'لوحة التحكم',
            'user' => $user,
            'stats' => $requests->stats((int)$user['account_id']),
            'rows' => $requests->mine((int)$user['account_id'], $page),
            'unread' => (new NotificationRepository())->unreadCount((int)$user['account_id']),
            'page' => $page,
        ]);
    }
}
