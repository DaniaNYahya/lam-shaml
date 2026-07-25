<div class="form-shell narrow">
  <h1>إرسال تطابق محتمل</h1>
  <?php if (!empty($errors)): ?><div class="alert alert-danger"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
  <form method="post">
    <?= csrf_field() ?>
    <p class="text-muted">البلاغ الحالي #<?= (int)$requestId ?> والبلاغ المقابل #<?= (int)$matchedId ?></p>
    <label class="form-label">ملاحظاتك</label><textarea name="notes" class="form-control mb-3" rows="5" required></textarea>
    <label class="form-label">وسيلة التواصل</label><input name="contact_phone" class="form-control mb-3" required>
    <button class="btn btn-success">إرسال للمراجعة</button>
  </form>
</div>
