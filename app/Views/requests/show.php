<div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
  <div><h1>بلاغ رقم #<?= (int)$request['request_id'] ?></h1><p class="text-muted"><?= e($request['request_type']) ?> - <?= e($request['status']) ?> - <?= e($request['created_at']) ?></p></div>
  <a class="btn btn-outline-success" href="<?= url('matches/report/' . $request['request_id'] . '/' . $request['request_id']) ?>">لدي معلومات عن هذا الشخص</a>
</div>
<div class="row g-4">
  <div class="col-lg-4">
    <?php if ($request['file_path']): ?><img class="detail-photo" src="<?= url($request['file_path']) ?>" alt="صورة الحالة"><?php else: ?><div class="empty-photo">لا توجد صورة</div><?php endif; ?>
  </div>
  <div class="col-lg-8">
    <dl class="details-grid">
      <dt>الاسم</dt><dd><?= e($request['full_name']) ?></dd>
      <dt>العمر</dt><dd><?= e((string)$request['age']) ?></dd>
      <dt>الجنس</dt><dd><?= e($request['gender']) ?></dd>
      <dt>المدينة</dt><dd><?= e($request['city']) ?> <?= e($request['area']) ?></dd>
      <dt>آخر مكان/الموقع الحالي</dt><dd><?= e($request['last_known_place'] ?: $request['current_location']) ?></dd>
      <dt>الوصف</dt><dd><?= nl2br(e($request['description'])) ?></dd>
      <dt>الحالة الصحية</dt><dd><?= e($request['health_status']) ?></dd>
      <dt>علامات مميزة</dt><dd><?= e($request['distinctive_marks']) ?></dd>
      <dt>رقم التواصل</dt><dd><?= $canSeeContact ? e($request['contact_phone']) : e(mask_phone($request['contact_phone'])) ?></dd>
    </dl>
  </div>
</div>
<h2 class="mt-4">التطابقات المرتبطة</h2>
<div class="row g-3">
  <?php foreach ($matches as $m): ?>
    <div class="col-md-6"><div class="result-card">
      <div class="d-flex justify-content-between"><strong><?= e($m['full_name']) ?></strong><span class="score"><?= (int)$m['total_score'] ?>%</span></div>
      <p class="mb-1"><?= e($m['request_type']) ?> - <?= e($m['status']) ?> - <?= e($m['city']) ?></p>
      <a class="btn btn-sm btn-outline-success" href="<?= url('requests/' . $m['matched_request_id']) ?>">عرض التفاصيل</a>
      <a class="btn btn-sm btn-success" href="<?= url('matches/report/' . $request['request_id'] . '/' . $m['matched_request_id']) ?>">إبلاغ عن تطابق محتمل</a>
    </div></div>
  <?php endforeach; if (!$matches): ?><p class="text-muted">لا توجد تطابقات مسجلة بعد.</p><?php endif; ?>
</div>
