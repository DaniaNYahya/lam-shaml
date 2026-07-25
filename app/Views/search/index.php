<h1>البحث الذكي</h1>
<form class="search-panel mb-4" method="get">
  <div class="row g-2">
    <div class="col-md-4"><input class="form-control" name="name" placeholder="الاسم أو جزء منه" value="<?= e($filters['name']) ?>"></div>
    <div class="col-md-2"><input class="form-control" type="number" name="age" placeholder="العمر" value="<?= e($filters['age']) ?>"></div>
    <div class="col-md-2"><select class="form-select" name="gender"><option value="">الجنس</option><option value="male">ذكر</option><option value="female">أنثى</option><option value="unknown">غير معروف</option></select></div>
    <div class="col-md-2"><input class="form-control" name="city" placeholder="المدينة" value="<?= e($filters['city']) ?>"></div>
    <div class="col-md-2"><input class="form-control" name="area" placeholder="المنطقة" value="<?= e($filters['area']) ?>"></div>
    <div class="col-md-3"><input class="form-control" name="place" placeholder="آخر مكان معروف" value="<?= e($filters['place']) ?>"></div>
    <div class="col-md-3"><select class="form-select" name="request_type"><option value="">نوع البلاغ</option><option value="missing">مفقود</option><option value="found">موجود</option></select></div>
    <div class="col-md-3"><select class="form-select" name="status"><option value="">الحالة</option><option value="pending">معلق</option><option value="active">نشط</option><option value="resolved">محلول</option></select></div>
    <div class="col-md-3"><button class="btn btn-success w-100">بحث</button></div>
  </div>
</form>
<div class="row g-3">
  <?php foreach ($rows as $r): ?>
    <div class="col-lg-6"><div class="result-card">
      <div class="d-flex gap-3">
        <?php if ($r['file_path']): ?><img class="thumb" src="<?= url($r['file_path']) ?>" alt="صورة مصغرة"><?php endif; ?>
        <div class="flex-grow-1">
          <div class="d-flex justify-content-between"><h2><?= e($r['full_name']) ?></h2><span class="score"><?= (int)$r['search_score'] ?>%</span></div>
          <p><?= e($r['age'] ? (string)$r['age'] : 'العمر غير معروف') ?> - <?= e($r['gender']) ?> - <?= e($r['city']) ?></p>
          <p class="small text-muted"><?= e($r['request_type']) ?> - <?= e($r['status']) ?> - الهاتف: <?= e(mask_phone($r['contact_phone'])) ?></p>
          <a class="btn btn-sm btn-outline-success" href="<?= url('requests/' . $r['request_id']) ?>">عرض التفاصيل</a>
          <?php if (LamShaml\Core\Auth::user()): ?><a class="btn btn-sm btn-success" href="<?= url('matches/report/' . $r['request_id'] . '/' . $r['request_id']) ?>">إرسال بلاغ تطابق محتمل</a><?php endif; ?>
        </div>
      </div>
    </div></div>
  <?php endforeach; if (!$rows): ?><div class="empty-state">ابدأ البحث بالاسم أو المدينة أو أي معلومة متاحة.</div><?php endif; ?>
</div>
