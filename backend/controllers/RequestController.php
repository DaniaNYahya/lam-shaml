<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\RequestRepository;
use App\Services\RequestService;

class RequestController extends Controller
{
    public function create(Request $request): void
    {
        $account = $this->auth($request);
        $result = (new RequestService())->create($account, $request->input(), $_FILES['image'] ?? null);
        Response::json($result, 201, 'Request created');
    }

    public function mine(Request $request): void
    {
        $account = $this->auth($request);
        $repo = new RequestRepository();
        Response::json([
            'items' => $repo->mine((int)$account['account_id']),
            'stats' => $repo->stats((int)$account['account_id']),
        ]);
    }

    public function show(Request $request, array $params): void
    {
        $account = $this->auth($request);
        $item = (new RequestService())->findForAccount($account, (int)$params['id']);
        Response::json(['request' => $item]);
    }

    public function stats(Request $request): void
    {
        Response::json(['stats' => (new RequestRepository())->stats()]);
    }
}
