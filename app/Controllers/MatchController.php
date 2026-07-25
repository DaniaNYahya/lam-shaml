<?php
declare(strict_types=1);

namespace LamShaml\Controllers;

use LamShaml\Core\Auth;
use LamShaml\Core\Csrf;
use LamShaml\Core\View;
use LamShaml\Repositories\MatchRepository;
use LamShaml\Repositories\NotificationRepository;

final class MatchController
{
    public function reportForm(string $requestId, string $matchedId): string
    {
        Auth::requireLogin();
        return View::render('matches/report', [
            'title' => 'إرسال تطابق محتمل',
            'requestId' => (int)$requestId,
            'matchedId' => (int)$matchedId,
        ]);
    }

    public function report(string $requestId, string $matchedId): string
    {
        $user = Auth::requireLogin();
        Csrf::verify();
        $notes = trim((string)($_POST['notes'] ?? ''));
        $phone = trim((string)($_POST['contact_phone'] ?? ''));
        if ($notes === '' || !preg_match('/^[0-9+\-\s]{7,30}$/', $phone)) {
            return View::render('matches/report', [
                'title' => 'إرسال تطابق محتمل',
                'requestId' => (int)$requestId,
                'matchedId' => (int)$matchedId,
                'errors' => ['الملاحظات ورقم التواصل الصحيح مطلوبان.'],
            ]);
        }
        $id = (new MatchRepository())->report((int)$user['account_id'], (int)$requestId, (int)$matchedId, $notes, $phone);
        (new NotificationRepository())->createAdmin('طلب تطابق محتمل جديد رقم ' . $id . ' يحتاج مراجعة.', 'possible_match_report');
        flash('success', 'تم إرسال طلب التطابق للمراجعة.');
        redirect('requests/' . (int)$requestId);
    }
}
