<?php
declare(strict_types=1);

namespace LamShaml\Controllers;

use LamShaml\Core\Auth;
use LamShaml\Core\Csrf;
use LamShaml\Core\View;
use LamShaml\Repositories\NotificationRepository;

final class NotificationController
{
    public function index(): string
    {
        $user = Auth::requireLogin();
        $page = max(1, (int)($_GET['page'] ?? 1));
        return View::render('notifications/index', [
            'title' => 'الإشعارات',
            'rows' => (new NotificationRepository())->list((int)$user['account_id'], $page),
            'page' => $page,
        ]);
    }

    public function read(string $id = ''): string
    {
        $user = Auth::requireLogin();
        Csrf::verify();
        (new NotificationRepository())->markRead((int)$user['account_id'], $id === 'all' ? null : (int)$id);
        redirect('notifications');
    }
}
