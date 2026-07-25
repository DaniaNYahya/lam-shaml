<div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
  <h1>لوحة المسؤول</h1>
  <div class="d-flex gap-2"><a class="btn btn-outline-success" href="<?= url('admin/users') ?>">المستخدمون</a><a class="btn btn-outline-success" href="<?= url('admin/requests') ?>">البلاغات</a><a class="btn btn-outline-success" href="<?= url('admin/matches') ?>">التطابقات</a><a class="btn btn-outline-success" href="<?= url('admin/reports') ?>">التقارير</a></div>
</div>
<section class="row g-3 mb-4">
  <div class="col-sm-3"><div class="metric"><strong><?= $stats['total'] ?></strong><span>بلاغات</span></div></div>
  <div class="col-sm-3"><div class="metric"><strong><?= $stats['pending'] ?></strong><span>معلقة</span></div></div>
  <div class="col-sm-3"><div class="metric"><strong><?= $stats['active'] ?></strong><span>نشطة</span></div></div>
  <div class="col-sm-3"><div class="metric"><strong><?= $stats['resolved'] ?></strong><span>محلولة</span></div></div>
</section>
<div class="row g-4">
  <div class="col-lg-6">
    <h2>إحصاءات المدن</h2>
    <div class="list-group"><?php foreach ($cityStats as $c): ?><div class="list-group-item d-flex justify-content-between"><span><?= e($c['city']) ?></span><strong><?= (int)$c['total'] ?></strong></div><?php endforeach; ?></div>
  </div>
  <div class="col-lg-6">
    <h2>آخر سجلات التدقيق</h2>
    <div class="list-group"><?php foreach ($audit as $a): ?><div class="list-group-item"><strong><?= e($a['action']) ?></strong><small class="text-muted d-block"><?= e($a['table_name']) ?> #<?= (int)$a['record_id'] ?> - <?= e($a['created_at']) ?></small></div><?php endforeach; ?></div>
  </div>
</div>
