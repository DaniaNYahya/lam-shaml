<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\HttpException;
use App\Repositories\AuditLogRepository;
use App\Repositories\RequestRepository;
use App\Services\Events\NotificationObserver;
use App\Services\Events\RequestEventPublisher;

class RequestService
{
    private ValidationService $validator;
    private RequestRepository $requests;

    public function __construct()
    {
        $this->validator = new ValidationService();
        $this->requests = new RequestRepository();
    }

    public function create(array $account, array $input, ?array $image): array
    {
        $this->validator->require($input, ['request_type', 'full_name', 'age', 'gender', 'city', 'description', 'contact_phone']);
        $this->validator->oneOf('request_type', $input['request_type'], ['missing', 'found']);
        $this->validator->oneOf('priority', $input['priority'] ?? 'normal', ['low', 'normal', 'high']);
        $gender = $this->normalizeGender($input['gender']);

        $document = (new ImageUploadService())->store($image);
        $requestId = $this->requests->create(
            [
                'account_id' => (int)$account['account_id'],
                'request_type' => $input['request_type'],
                'status' => 'pending',
                'priority' => $input['priority'] ?? 'normal',
                'description' => trim($input['description']),
                'contact_phone' => trim($input['contact_phone']),
            ],
            [
                'full_name' => trim($input['full_name']),
                'normalized_name' => ArabicNormalizer::normalize($input['full_name']),
                'age' => (int)$input['age'],
                'gender' => $gender,
                'original_city' => trim($input['city']),
                'relationship' => $input['relationship'] ?? null,
                'health_status' => $input['health_status'] ?? null,
                'distinctive_marks' => $input['distinctive_marks'] ?? null,
            ],
            [
                'city' => trim($input['city']),
                'area' => $input['area'] ?? null,
                'last_known_place' => $input['last_known_place'] ?? null,
                'current_location' => $input['current_location'] ?? null,
                'last_seen_date' => ($input['last_seen_date'] ?? '') !== '' ? $input['last_seen_date'] : null,
            ],
            $document
        );

        (new AuditLogRepository())->record((int)$account['account_id'], 'create', 'reunification_requests', $requestId);
        $publisher = new RequestEventPublisher();
        $publisher->subscribe(new NotificationObserver());
        $publisher->publish('request.created', ['account_id' => $account['account_id'], 'request_id' => $requestId]);
        $matches = (new MatchingService())->createMatchesFor($requestId);

        return [
            'request' => $this->requests->find($requestId),
            'matches' => $matches,
        ];
    }

    public function findForAccount(array $account, int $id): array
    {
        $request = $this->requests->find($id);
        if (!$request) {
            throw new HttpException('Request not found', 404);
        }
        if ($account['role'] !== 'admin' && (int)$request['account_id'] !== (int)$account['account_id']) {
            throw new HttpException('You cannot access this request', 403);
        }
        return $request;
    }

    private function normalizeGender(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        if (in_array($value, ['male', 'm', 'ذكر', 'رجل'], true)) {
            return 'male';
        }
        if (in_array($value, ['female', 'f', 'أنثى', 'انثى', 'امرأة'], true)) {
            return 'female';
        }
        return 'unknown';
    }
}
