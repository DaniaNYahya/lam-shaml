<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div><h1>مرحباً <?= e($user['full_name']) ?></h1><p class="text-muted mb-0">هذه آخر بلاغاتك وإشعاراتك.</p></div>
  <div class="d-flex gap-2"><a class="btn btn-success" href="<?= url('requests/create/missing') ?>">بلاغ مفقود</a><a class="btn btn-outline-success" href="<?= url('search') ?>">بحث</a></div>
</div>
<section class="row g-3 mb-4">
  <div class="col-sm-3"><div class="metric"><strong><?= $stats['total'] ?></strong><span>بلاغاتي</span></div></div>
  <div class="col-sm-3"><div class="metric"><strong><?= $stats['pending'] ?></strong><span>معلقة</span></div></div>
  <div class="col-sm-3"><div class="metric"><strong><?= $stats['active'] ?></strong><span>نشطة</span></div></div>
  <div class="col-sm-3"><div class="metric"><strong><?= $unread ?></strong><span>إشعارات جديدة</span></div></div>
</section>
<div class="table-responsive"><table class="table align-middle">
  <thead><tr><th>رقم</th><th>النوع</th><th>الحالة</th><th>الاسم</th><th>التاريخ</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows['items'] as $r): ?>
    <tr><td>#<?= (int)$r['request_id'] ?></td><td><?= e($r['request_type']) ?></td><td><span class="badge text-bg-light"><?= e($r['status']) ?></span></td><td><?= e($r['full_name']) ?></td><td><?= e($r['created_at']) ?></td><td><a class="btn btn-sm btn-outline-success" href="<?= url('requests/' . $r['request_id']) ?>">تفاصيل</a></td></tr>
  <?php endforeach; if (!$rows['items']): ?><tr><td colspan="6" class="text-center text-muted">لا توجد بلاغات بعد.</td></tr><?php endif; ?>
  </tbody>
</table></div>
<?php require APP_PATH . '/Views/partials/pagination.php'; ?>
