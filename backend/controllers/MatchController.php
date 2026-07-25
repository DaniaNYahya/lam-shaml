<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\AuditLogRepository;
use App\Repositories\MatchRepository;
use App\Services\ValidationService;

class MatchController extends Controller
{
    public function report(Request $request): void
    {
        $account = $this->auth($request);
        $input = $request->input();
        (new ValidationService())->require($input, ['request_id', 'matched_request_id', 'contact_phone']);
        if ((int)$input['request_id'] === (int)$input['matched_request_id']) {
            throw new HttpException('Request and matched request must be different', 422);
        }
        $id = (new MatchRepository())->createReport([
            'account_id' => (int)$account['account_id'],
            'request_id' => (int)$input['request_id'],
            'matched_request_id' => (int)$input['matched_request_id'],
            'notes' => $input['notes'] ?? null,
            'contact_phone' => trim((string)$input['contact_phone']),
        ]);
        (new AuditLogRepository())->record((int)$account['account_id'], 'report_possible_match', 'possible_match_reports', $id);
        Response::json(['report_id' => $id], 201, 'Report submitted');
    }
}
