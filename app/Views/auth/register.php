<div class="form-shell">
  <h1>إنشاء حساب</h1>
  <?php if (!empty($errors)): ?><div class="alert alert-danger"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
  <form method="post" class="needs-validation" novalidate>
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">الاسم الكامل</label><input required name="full_name" class="form-control" value="<?= e(old('full_name')) ?>"></div>
      <div class="col-md-6"><label class="form-label">رقم الهاتف</label><input required name="phone" class="form-control" value="<?= e(old('phone')) ?>"></div>
      <div class="col-md-6"><label class="form-label">البريد الإلكتروني</label><input required type="email" name="email" class="form-control" value="<?= e(old('email')) ?>"></div>
      <div class="col-md-6"><label class="form-label">المدينة أو المنطقة</label><input required name="city" class="form-control" value="<?= e(old('city')) ?>"></div>
      <div class="col-md-6"><label class="form-label">كلمة المرور</label><input required minlength="8" type="password" name="password" class="form-control"></div>
      <div class="col-md-6"><label class="form-label">تأكيد كلمة المرور</label><input required minlength="8" type="password" name="password_confirmation" class="form-control"></div>
      <div class="col-md-6"><label class="form-label">نوع الحساب</label><select name="role" class="form-select"><option value="user">مستخدم</option><option value="organization">منظمة إنسانية</option></select></div>
      <div class="col-12 form-check"><input class="form-check-input" type="checkbox" name="privacy" value="1" id="privacy" required><label class="form-check-label" for="privacy">أوافق على سياسة الخصوصية وحماية بيانات العائلات.</label></div>
    </div>
    <button class="btn btn-success mt-3">إنشاء الحساب</button>
  </form>
</div>
