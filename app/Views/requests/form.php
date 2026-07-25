<?php $isMissing = $type === 'missing'; ?>
<div class="form-shell">
  <h1><?= $isMissing ? 'تسجيل شخص مفقود' : 'تسجيل شخص تم العثور عليه' ?></h1>
  <?php if (!empty($errors)): ?><div class="alert alert-danger"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">الاسم الكامل</label><input name="full_name" class="form-control" value="<?= e(old('full_name', $isMissing ? '' : 'غير معروف')) ?>" required></div>
      <div class="col-md-3"><label class="form-label"><?= $isMissing ? 'العمر' : 'العمر التقريبي' ?></label><input type="number" min="0" max="120" name="age" class="form-control" value="<?= e(old('age')) ?>"></div>
      <div class="col-md-3"><label class="form-label">الجنس</label><select name="gender" class="form-select"><option value="unknown">غير معروف</option><option value="male">ذكر</option><option value="female">أنثى</option></select></div>
      <div class="col-md-4"><label class="form-label">المدينة الأصلية</label><input name="original_city" class="form-control" value="<?= e(old('original_city')) ?>"></div>
      <div class="col-md-4"><label class="form-label">المدينة/المنطقة</label><input name="city" class="form-control" value="<?= e(old('city')) ?>" required></div>
      <div class="col-md-4"><label class="form-label">الحي أو المنطقة</label><input name="area" class="form-control" value="<?= e(old('area')) ?>"></div>
      <div class="col-md-6"><label class="form-label"><?= $isMissing ? 'آخر مكان شوهد فيه' : 'مكان العثور عليه' ?></label><input name="last_known_place" class="form-control" value="<?= e(old('last_known_place')) ?>"></div>
      <div class="col-md-6"><label class="form-label">مكان وجوده حالياً</label><input name="current_location" class="form-control" value="<?= e(old('current_location')) ?>"></div>
      <div class="col-md-4"><label class="form-label">تاريخ آخر مشاهدة</label><input type="date" name="last_seen_date" class="form-control" value="<?= e(old('last_seen_date')) ?>"></div>
      <div class="col-md-4"><label class="form-label">صلة القرابة</label><input name="relationship" class="form-control" value="<?= e(old('relationship')) ?>"></div>
      <div class="col-md-4"><label class="form-label">مستوى الأولوية</label><select name="priority" class="form-select"><option value="low">منخفضة</option><option value="normal" selected>متوسطة</option><option value="high">عالية</option><option value="urgent">عاجلة</option></select></div>
      <div class="col-md-6"><label class="form-label">الحالة الصحية</label><input name="health_status" class="form-control" value="<?= e(old('health_status')) ?>"></div>
      <div class="col-md-6"><label class="form-label">العلامات المميزة</label><input name="distinctive_marks" class="form-control" value="<?= e(old('distinctive_marks')) ?>"></div>
      <div class="col-md-6"><label class="form-label">اسم المسجل أو الجهة</label><input name="registered_by" class="form-control" value="<?= e(old('registered_by')) ?>"></div>
      <div class="col-md-6"><label class="form-label">رقم هاتف للتواصل</label><input name="contact_phone" class="form-control" value="<?= e(old('contact_phone')) ?>" required></div>
      <div class="col-12"><label class="form-label">وصف الشخص أو الملابس</label><textarea name="description" class="form-control" rows="4" required><?= e(old('description')) ?></textarea></div>
      <div class="col-md-6"><label class="form-label">صورة اختيارية</label><input type="file" name="photo" class="form-control" accept="image/png,image/jpeg,image/webp"></div>
    </div>
    <button class="btn btn-success mt-3">حفظ البلاغ</button>
  </form>
</div>
