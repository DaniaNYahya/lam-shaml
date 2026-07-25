<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\AccountRepository;
use App\Repositories\AuditLogRepository;
use App\Repositories\MatchRepository;
use App\Repositories\RequestRepository;
use App\Services\Events\NotificationObserver;
use App\Services\Events\RequestEventPublisher;
use App\Services\ValidationService;

class AdminController extends Controller
{
    public function dashboard(Request $request): void
    {
        $this->admin($request);
        Response::json(['stats' => (new RequestRepository())->stats()]);
    }

    public function users(Request $request): void
    {
        $this->admin($request);
        Response::json(['users' => (new AccountRepository())->list()]);
    }

    public function userStatus(Request $request, array $params): void
    {
        $admin = $this->admin($request);
        $input = $request->input();
        (new ValidationService())->oneOf('status', $input['status'] ?? '', ['active', 'blocked', 'pending']);
        (new AccountRepository())->updateStatus((int)$params['id'], $input['status']);
        (new AuditLogRepository())->record((int)$admin['account_id'], 'update_status', 'accounts', (int)$params['id']);
        Response::json([], 200, 'User status updated');
    }

    public function requests(Request $request): void
    {
        $this->admin($request);
        Response::json(['requests' => (new RequestRepository())->adminList()]);
    }

    public function requestStatus(Request $request, array $params): void
    {
        $admin = $this->admin($request);
        $input = $request->input();
        (new ValidationService())->oneOf('status', $input['status'] ?? '', ['pending', 'active', 'approved', 'rejected', 'more_info', 'resolved']);
        $repo = new RequestRepository();
        $item = $repo->find((int)$params['id']);
        $repo->updateStatus((int)$params['id'], $input['status']);
        (new AuditLogRepository())->record((int)$admin['account_id'], 'update_status', 'reunification_requests', (int)$params['id']);
        if ($item) {
            $publisher = new RequestEventPublisher();
            $publisher->subscribe(new NotificationObserver());
            $publisher->publish('status.changed', [
                'account_id' => $item['account_id'],
                'request_id' => $params['id'],
                'status' => $input['status'],
            ]);
        }
        Response::json([], 200, 'Request status updated');
    }

    public function matches(Request $request): void
    {
        $this->admin($request);
        Response::json(['matches' => (new MatchRepository())->listAdmin()]);
    }

    public function matchStatus(Request $request, array $params): void
    {
        $admin = $this->admin($request);
        $input = $request->input();
        (new ValidationService())->oneOf('status', $input['status'] ?? '', ['pending', 'approved', 'rejected', 'resolved']);
        (new MatchRepository())->updateStatus((int)$params['id'], $input['status'], $input['admin_notes'] ?? null);
        (new AuditLogRepository())->record((int)$admin['account_id'], 'update_status', 'match_records', (int)$params['id']);
        Response::json([], 200, 'Match status updated');
    }

    public function reports(Request $request): void
    {
        $this->admin($request);
        Response::json(['reports' => (new MatchRepository())->listReports()]);
    }
}
