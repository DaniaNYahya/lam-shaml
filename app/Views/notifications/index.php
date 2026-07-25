<div class="d-flex justify-content-between align-items-center mb-3">
  <h1>الإشعارات</h1>
  <form method="post" action="<?= url('notifications/read/all') ?>"><?= csrf_field() ?><button class="btn btn-outline-success">تحديد الكل كمقروء</button></form>
</div>
<div class="list-group">
  <?php foreach ($rows['items'] as $n): ?>
    <div class="list-group-item d-flex justify-content-between align-items-center <?= !$n['is_read'] ? 'fw-bold' : '' ?>">
      <span><?= e($n['message']) ?><small class="text-muted d-block"><?= e($n['created_at']) ?></small></span>
      <?php if (!$n['is_read']): ?><form method="post" action="<?= url('notifications/read/' . $n['notification_id']) ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline-success">مقروء</button></form><?php endif; ?>
    </div>
  <?php endforeach; if (!$rows['items']): ?><div class="empty-state">لا توجد إشعارات.</div><?php endif; ?>
</div>
<?php require APP_PATH . '/Views/partials/pagination.php'; ?>
