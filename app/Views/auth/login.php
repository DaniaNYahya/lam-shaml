<div class="form-shell narrow">
  <h1>تسجيل الدخول</h1>
  <?php if (!empty($errors)): ?><div class="alert alert-danger"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
  <form method="post">
    <?= csrf_field() ?>
    <label class="form-label">البريد الإلكتروني</label>
    <input type="email" required name="email" class="form-control mb-3">
    <label class="form-label">كلمة المرور</label>
    <input type="password" required name="password" class="form-control mb-3">
    <button class="btn btn-success w-100">دخول</button>
  </form>
</div>
