<?php
declare(strict_types=1);

namespace LamShaml\Controllers;

use LamShaml\Core\Auth;
use LamShaml\Core\Csrf;
use LamShaml\Core\View;
use LamShaml\Repositories\AuditLogRepository;
use LamShaml\Repositories\MatchRepository;
use LamShaml\Repositories\NotificationRepository;
use LamShaml\Repositories\RequestRepository;
use LamShaml\Repositories\UserRepository;

final class AdminController
{
    public function index(): string
    {
        Auth::requireRole(['admin']);
        $requests = new RequestRepository();
        return View::render('admin/index', [
            'title' => 'لوحة المسؤول',
            'stats' => $requests->stats(),
            'cityStats' => $requests->cityStats(),
            'audit' => (new AuditLogRepository())->latest(),
        ]);
    }

    public function users(): string
    {
        Auth::requireRole(['admin']);
        $page = max(1, (int)($_GET['page'] ?? 1));
        return View::render('admin/users', ['title' => 'إدارة المستخدمين', 'rows' => (new UserRepository())->paginate($page), 'page' => $page]);
    }

    public function userStatus(string $id): string
    {
        $admin = Auth::requireRole(['admin']);
        Csrf::verify();
        $status = in_array($_POST['status'] ?? '', ['active', 'blocked', 'pending'], true) ? $_POST['status'] : 'active';
        (new UserRepository())->updateStatus((int)$id, $status);
        (new AuditLogRepository())->log((int)$admin['account_id'], 'user_status_' . $status, 'accounts', (int)$id);
        flash('success', 'تم تحديث حالة المستخدم.');
        redirect('admin/users');
    }

    public function requests(): string
    {
        Auth::requireRole(['admin']);
        $page = max(1, (int)($_GET['page'] ?? 1));
        return View::render('admin/requests', ['title' => 'إدارة البلاغات', 'rows' => (new RequestRepository())->all($page), 'page' => $page]);
    }

    public function requestStatus(string $id): string
    {
        $admin = Auth::requireRole(['admin']);
        Csrf::verify();
        $status = in_array($_POST['status'] ?? '', ['pending', 'active', 'approved', 'rejected', 'more_info', 'resolved'], true) ? $_POST['status'] : 'pending';
        $owner = (new RequestRepository())->updateStatus((int)$id, $status);
        (new AuditLogRepository())->log((int)$admin['account_id'], 'request_status_' . $status, 'reunification_requests', (int)$id);
        if ($owner) {
            (new NotificationRepository())->create($owner, 'تم تغيير حالة البلاغ رقم ' . (int)$id . ' إلى ' . $status, 'status_changed');
        }
        flash('success', 'تم تحديث البلاغ.');
        redirect('admin/requests');
    }

    public function matches(): string
    {
        Auth::requireRole(['admin']);
        $page = max(1, (int)($_GET['page'] ?? 1));
        return View::render('admin/matches', ['title' => 'إدارة التطابقات', 'rows' => (new MatchRepository())->all($page), 'page' => $page]);
    }

    public function matchStatus(string $id): string
    {
        $admin = Auth::requireRole(['admin']);
        Csrf::verify();
        $status = in_array($_POST['status'] ?? '', ['approved', 'rejected', 'resolved'], true) ? $_POST['status'] : 'rejected';
        (new MatchRepository())->decide((int)$id, $status, (int)$admin['account_id']);
        flash('success', 'تمت معالجة التطابق داخل Transaction.');
        redirect('admin/matches');
    }

    public function reports(): string
    {
        Auth::requireRole(['admin']);
        $page = max(1, (int)($_GET['page'] ?? 1));
        return View::render('admin/reports', ['title' => 'تقارير التطابق', 'rows' => (new MatchRepository())->reports($page), 'page' => $page]);
    }
}
