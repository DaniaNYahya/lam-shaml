<h1>إدارة المستخدمين</h1>
<div class="table-responsive"><table class="table align-middle">
  <thead><tr><th>الاسم</th><th>البريد</th><th>الهاتف</th><th>الدور</th><th>الحالة</th><th>إجراء</th></tr></thead>
  <tbody><?php foreach ($rows['items'] as $u): ?><tr>
    <td><?= e($u['full_name']) ?></td><td><?= e($u['email']) ?></td><td><?= e(mask_phone($u['phone'])) ?></td><td><?= e($u['role']) ?></td><td><?= e($u['status']) ?></td>
    <td><form method="post" action="<?= url('admin/users/' . $u['account_id'] . '/status') ?>" class="d-flex gap-2"><?= csrf_field() ?><select name="status" class="form-select form-select-sm"><option>active</option><option>blocked</option><option>pending</option></select><button class="btn btn-sm btn-success">حفظ</button></form></td>
  </tr><?php endforeach; ?></tbody>
</table></div>
<?php require APP_PATH . '/Views/partials/pagination.php'; ?>
