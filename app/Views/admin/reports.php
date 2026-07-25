<h1>تقارير التطابق المحتمل</h1>
<div class="table-responsive"><table class="table align-middle">
  <thead><tr><th>رقم</th><th>المبلّغ</th><th>الحالتان</th><th>ملاحظات</th><th>تواصل</th><th>الحالة</th></tr></thead>
  <tbody><?php foreach ($rows['items'] as $r): ?><tr>
    <td>#<?= (int)$r['report_id'] ?></td><td><?= e($r['reporter_name']) ?></td><td><?= e($r['left_name']) ?> / <?= e($r['right_name']) ?></td><td><?= e($r['notes']) ?></td><td><?= e($r['contact_phone']) ?></td><td><?= e($r['status']) ?></td>
  </tr><?php endforeach; ?></tbody>
</table></div>
<?php require APP_PATH . '/Views/partials/pagination.php'; ?>
