<?php
declare(strict_types=1);

namespace LamShaml\Controllers;

use LamShaml\Core\Auth;
use LamShaml\Core\Csrf;
use LamShaml\Core\HttpException;
use LamShaml\Core\Session;
use LamShaml\Core\View;
use LamShaml\Repositories\MatchRepository;
use LamShaml\Repositories\NotificationRepository;
use LamShaml\Repositories\RequestRepository;
use LamShaml\Services\ImageUploadService;
use LamShaml\Services\MatchingService;

final class RequestController
{
    public function createForm(string $type): string
    {
        Auth::requireLogin();
        if (!in_array($type, ['missing', 'found'], true)) {
            throw new HttpException(404, 'نوع البلاغ غير صحيح.');
        }
        return View::render('requests/form', ['title' => $type === 'missing' ? 'تسجيل شخص مفقود' : 'تسجيل شخص تم العثور عليه', 'type' => $type]);
    }

    public function store(string $type): string
    {
        $user = Auth::requireLogin();
        Csrf::verify();
        if (!in_array($type, ['missing', 'found'], true)) {
            throw new HttpException(404, 'نوع البلاغ غير صحيح.');
        }
        $data = $this->collect($type);
        $errors = $this->validate($data, $type);
        if ($errors) {
            Session::rememberOld($_POST);
            return View::render('requests/form', ['title' => 'تسجيل بلاغ', 'type' => $type, 'errors' => $errors]);
        }
        $document = (new ImageUploadService())->store($_FILES['photo'] ?? null);
        $id = (new RequestRepository())->create(
            [
                'account_id' => (int)$user['account_id'],
                'request_type' => $type,
                'priority' => $data['priority'],
                'description' => $data['description'],
                'contact_phone' => $data['contact_phone'],
            ],
            [
                'full_name' => $data['full_name'],
                'age' => $data['age'],
                'gender' => $data['gender'],
                'original_city' => $data['original_city'],
                'relationship' => $data['relationship'],
                'health_status' => $data['health_status'],
                'distinctive_marks' => $data['distinctive_marks'],
                'registered_by' => $data['registered_by'],
            ],
            [
                'city' => $data['city'],
                'area' => $data['area'],
                'last_known_place' => $data['last_known_place'],
                'current_location' => $data['current_location'],
                'last_seen_date' => $data['last_seen_date'],
            ],
            $document
        );
        (new NotificationRepository())->create((int)$user['account_id'], 'تم إنشاء البلاغ رقم ' . $id . ' وبدأت المطابقة التلقائية.', 'request_created');
        (new MatchingService())->createMatchesFor($id);
        flash('success', 'تم حفظ البلاغ بنجاح. رقم البلاغ: ' . $id);
        redirect('requests/' . $id);
    }

    public function show(string $id): string
    {
        $user = Auth::user();
        $request = (new RequestRepository())->find((int)$id);
        if (!$request) {
            throw new HttpException(404, 'البلاغ غير موجود.');
        }
        $canSeeContact = $user && ($user['role'] === 'admin' || (int)$user['account_id'] === (int)$request['account_id']);
        return View::render('requests/show', [
            'title' => 'تفاصيل البلاغ',
            'request' => $request,
            'matches' => (new MatchRepository())->forRequest((int)$id),
            'canSeeContact' => $canSeeContact,
        ]);
    }

    private function collect(string $type): array
    {
        return [
            'full_name' => trim((string)($_POST['full_name'] ?? ($type === 'found' ? 'غير معروف' : ''))),
            'age' => trim((string)($_POST['age'] ?? '')),
            'gender' => (string)($_POST['gender'] ?? 'unknown'),
            'original_city' => trim((string)($_POST['original_city'] ?? '')),
            'city' => trim((string)($_POST['city'] ?? '')),
            'area' => trim((string)($_POST['area'] ?? '')),
            'last_known_place' => trim((string)($_POST['last_known_place'] ?? '')),
            'current_location' => trim((string)($_POST['current_location'] ?? '')),
            'last_seen_date' => trim((string)($_POST['last_seen_date'] ?? '')),
            'relationship' => trim((string)($_POST['relationship'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'health_status' => trim((string)($_POST['health_status'] ?? '')),
            'distinctive_marks' => trim((string)($_POST['distinctive_marks'] ?? '')),
            'registered_by' => trim((string)($_POST['registered_by'] ?? '')),
            'contact_phone' => trim((string)($_POST['contact_phone'] ?? '')),
            'priority' => in_array($_POST['priority'] ?? 'normal', ['low', 'normal', 'high', 'urgent'], true) ? $_POST['priority'] : 'normal',
        ];
    }

    private function validate(array $data, string $type): array
    {
        $errors = [];
        foreach (['full_name' => 'الاسم', 'gender' => 'الجنس', 'city' => 'المدينة', 'description' => 'الوصف', 'contact_phone' => 'رقم التواصل'] as $key => $label) {
            if ($data[$key] === '') $errors[] = $label . ' مطلوب.';
        }
        if ($type === 'missing' && $data['last_known_place'] === '') $errors[] = 'آخر مكان شوهد فيه الشخص مطلوب.';
        if ($type === 'found' && $data['current_location'] === '') $errors[] = 'مكان وجود الشخص حالياً مطلوب.';
        if ($data['age'] !== '' && ((int)$data['age'] < 0 || (int)$data['age'] > 120)) $errors[] = 'العمر غير صالح.';
        if (!preg_match('/^[0-9+\-\s]{7,30}$/', $data['contact_phone'])) $errors[] = 'رقم التواصل غير صالح.';
        return $errors;
    }
}
