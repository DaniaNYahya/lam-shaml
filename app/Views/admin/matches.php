<h1>إدارة التطابقات</h1>
<div class="table-responsive"><table class="table align-middle">
  <thead><tr><th>رقم</th><th>البلاغان</th><th>الدرجة</th><th>الحالة</th><th>إجراء</th></tr></thead>
  <tbody><?php foreach ($rows['items'] as $m): ?><tr>
    <td>#<?= (int)$m['match_id'] ?></td><td><?= e($m['left_name']) ?> / <?= e($m['right_name']) ?></td><td><?= (int)$m['total_score'] ?>%</td><td><?= e($m['status']) ?></td>
    <td><form method="post" action="<?= url('admin/matches/' . $m['match_id'] . '/status') ?>" class="d-flex gap-2"><?= csrf_field() ?><select name="status" class="form-select form-select-sm"><option value="approved">قبول</option><option value="rejected">رفض</option><option value="resolved">تأكيد لم الشمل</option></select><button class="btn btn-sm btn-success">تنفيذ</button></form></td>
  </tr><?php endforeach; ?></tbody>
</table></div>
<?php require APP_PATH . '/Views/partials/pagination.php'; ?>
