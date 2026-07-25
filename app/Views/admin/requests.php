<h1>إدارة البلاغات</h1>
<div class="table-responsive"><table class="table align-middle">
  <thead><tr><th>رقم</th><th>الاسم</th><th>المالك</th><th>النوع</th><th>الحالة</th><th>أولوية</th><th>إجراء</th></tr></thead>
  <tbody><?php foreach ($rows['items'] as $r): ?><tr>
    <td>#<?= (int)$r['request_id'] ?></td><td><a href="<?= url('requests/' . $r['request_id']) ?>"><?= e($r['full_name']) ?></a></td><td><?= e($r['owner_name']) ?></td><td><?= e($r['request_type']) ?></td><td><?= e($r['status']) ?></td><td><?= e($r['priority']) ?></td>
    <td><form method="post" action="<?= url('admin/requests/' . $r['request_id'] . '/status') ?>" class="d-flex gap-2"><?= csrf_field() ?><select name="status" class="form-select form-select-sm"><option>pending</option><option>active</option><option>approved</option><option>rejected</option><option>more_info</option><option>resolved</option></select><button class="btn btn-sm btn-success">حفظ</button></form></td>
  </tr><?php endforeach; ?></tbody>
</table></div>
<?php require APP_PATH . '/Views/partials/pagination.php'; ?>
