<?php
declare(strict_types=1);

namespace LamShaml\Controllers;

use LamShaml\Core\Auth;
use LamShaml\Core\Csrf;
use LamShaml\Core\Session;
use LamShaml\Core\View;
use LamShaml\Repositories\UserRepository;

final class AuthController
{
    public function registerForm(): string
    {
        return View::render('auth/register', ['title' => 'إنشاء حساب']);
    }

    public function register(): string
    {
        Csrf::verify();
        $data = $this->input();
        $errors = $this->validateRegister($data);
        $users = new UserRepository();
        if (!$errors && $users->emailOrPhoneExists($data['email'], $data['phone'])) {
            $errors[] = 'البريد الإلكتروني أو رقم الهاتف مستخدم مسبقاً.';
        }
        if ($errors) {
            Session::rememberOld($data);
            return View::render('auth/register', ['title' => 'إنشاء حساب', 'errors' => $errors]);
        }
        $id = $users->create($data);
        Auth::login($id);
        flash('success', 'تم إنشاء الحساب بنجاح.');
        redirect('dashboard');
    }

    public function loginForm(): string
    {
        return View::render('auth/login', ['title' => 'تسجيل الدخول']);
    }

    public function login(): string
    {
        Csrf::verify();
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $key = 'login_attempts_' . sha1($_SERVER['REMOTE_ADDR'] ?? 'local');
        $attempts = $_SESSION[$key] ?? ['count' => 0, 'until' => 0];
        if (($attempts['until'] ?? 0) > time()) {
            return View::render('auth/login', ['title' => 'تسجيل الدخول', 'errors' => ['تم قفل المحاولة مؤقتاً. الرجاء الانتظار دقيقة.']]);
        }
        $user = (new UserRepository())->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $attempts['count'] = ($attempts['count'] ?? 0) + 1;
            if ($attempts['count'] >= 5) {
                $attempts = ['count' => 0, 'until' => time() + 60];
            }
            $_SESSION[$key] = $attempts;
            return View::render('auth/login', ['title' => 'تسجيل الدخول', 'errors' => ['بيانات الدخول غير صحيحة.']]);
        }
        if ($user['status'] !== 'active') {
            return View::render('auth/login', ['title' => 'تسجيل الدخول', 'errors' => ['هذا الحساب غير نشط أو محظور.']]);
        }
        unset($_SESSION[$key]);
        Auth::login((int)$user['account_id']);
        flash('success', 'مرحباً بك.');
        redirect($user['role'] === 'admin' ? 'admin' : 'dashboard');
    }

    public function logout(): string
    {
        Csrf::verify();
        Auth::logout();
        redirect('');
    }

    private function input(): array
    {
        return [
            'full_name' => trim((string)($_POST['full_name'] ?? '')),
            'phone' => trim((string)($_POST['phone'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'password' => (string)($_POST['password'] ?? ''),
            'password_confirmation' => (string)($_POST['password_confirmation'] ?? ''),
            'city' => trim((string)($_POST['city'] ?? '')),
            'role' => in_array($_POST['role'] ?? 'user', ['user', 'organization'], true) ? $_POST['role'] : 'user',
            'privacy' => (string)($_POST['privacy'] ?? ''),
        ];
    }

    private function validateRegister(array $data): array
    {
        $errors = [];
        if (mb_strlen($data['full_name']) < 3) $errors[] = 'الاسم الكامل مطلوب.';
        if (!preg_match('/^[0-9+\-\s]{7,30}$/', $data['phone'])) $errors[] = 'رقم الهاتف غير صالح.';
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'البريد الإلكتروني غير صالح.';
        if (strlen($data['password']) < 8) $errors[] = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.';
        if ($data['password'] !== $data['password_confirmation']) $errors[] = 'تأكيد كلمة المرور غير مطابق.';
        if ($data['city'] === '') $errors[] = 'المدينة أو المنطقة مطلوبة.';
        if ($data['privacy'] !== '1') $errors[] = 'يجب الموافقة على سياسة الخصوصية.';
        return $errors;
    }
}
