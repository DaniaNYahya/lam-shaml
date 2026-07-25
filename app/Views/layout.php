<?php
$user = LamShaml\Core\Auth::user();
$flashes = LamShaml\Core\Session::flashes();
$dir = ($_GET['lang'] ?? $_SESSION['lang'] ?? 'ar') === 'en' ? 'ltr' : 'rtl';
if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'en'], true)) $_SESSION['lang'] = $_GET['lang'];
$unread = $user ? (new LamShaml\Repositories\NotificationRepository())->unreadCount((int)$user['account_id']) : 0;
?>
<!doctype html>
<html lang="<?= $dir === 'rtl' ? 'ar' : 'en' ?>" dir="<?= e($dir) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'لم شمل') ?> - Lam Shaml</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap<?= $dir === 'rtl' ? '.rtl' : '' ?>.min.css" rel="stylesheet">
  <link href="<?= url('assets/app.css') ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold text-success" href="<?= url('') ?>">لم شمل</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div id="nav" class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?= url('search') ?>">البحث الذكي</a></li>
        <?php if ($user): ?>
          <li class="nav-item"><a class="nav-link" href="<?= url('dashboard') ?>">لوحتي</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= url('requests/create/missing') ?>">بلاغ مفقود</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= url('requests/create/found') ?>">بلاغ موجود</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= url('notifications') ?>">الإشعارات <span class="badge text-bg-success"><?= $unread ?></span></a></li>
          <?php if (($user['role'] ?? '') === 'admin'): ?><li class="nav-item"><a class="nav-link" href="<?= url('admin') ?>">Admin</a></li><?php endif; ?>
        <?php endif; ?>
      </ul>
      <div class="d-flex gap-2 align-items-center">
        <a class="btn btn-sm btn-outline-secondary" href="?lang=<?= $dir === 'rtl' ? 'en' : 'ar' ?>"><?= $dir === 'rtl' ? 'English' : 'العربية' ?></a>
        <?php if ($user): ?>
          <span class="small text-muted"><?= e($user['full_name']) ?></span>
          <form method="post" action="<?= url('logout') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">خروج</button></form>
        <?php else: ?>
          <a class="btn btn-sm btn-outline-success" href="<?= url('login') ?>">دخول</a>
          <a class="btn btn-sm btn-success" href="<?= url('register') ?>">حساب جديد</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
<main class="py-4">
  <div class="container">
    <?php foreach ($flashes as $type => $messages): foreach ($messages as $message): ?>
      <div class="alert alert-<?= $type === 'success' ? 'success' : 'warning' ?>"><?= e($message) ?></div>
    <?php endforeach; endforeach; ?>
    <?= $content ?>
  </div>
</main>
<footer class="border-top py-4 bg-white">
  <div class="container small text-muted d-flex flex-wrap justify-content-between gap-2">
    <span>Lam Shaml - نظام إنساني للمساعدة في لم شمل العائلات.</span>
    <span>الخصوصية أولاً: لا تعرض بيانات الاتصال للزوار أو نتائج البحث.</span>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('assets/app.js') ?>"></script>
</body>
</html>
