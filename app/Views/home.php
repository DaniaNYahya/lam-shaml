<section class="hero">
  <div>
    <p class="kicker">Lam Shaml</p>
    <h1>لم شمل العائلات يبدأ ببلاغ آمن ومعلومة واضحة</h1>
    <p>منصة ويب خفيفة لتسجيل المفقودين والأشخاص الذين تم العثور عليهم، والبحث عن التشابهات مع مراعاة اختلاف كتابة الأسماء العربية.</p>
    <div class="d-flex flex-wrap gap-2">
      <a class="btn btn-success btn-lg" href="<?= url('search') ?>">البحث عن شخص مفقود</a>
      <a class="btn btn-outline-success btn-lg" href="<?= url('requests/create/missing') ?>">تسجيل شخص مفقود</a>
      <a class="btn btn-outline-dark btn-lg" href="<?= url('requests/create/found') ?>">تسجيل شخص تم العثور عليه</a>
    </div>
  </div>
</section>
<section class="row g-3 my-4">
  <div class="col-md-4"><div class="metric"><strong><?= $stats['total'] ?></strong><span>إجمالي البلاغات</span></div></div>
  <div class="col-md-4"><div class="metric"><strong><?= $stats['active'] ?></strong><span>حالات نشطة</span></div></div>
  <div class="col-md-4"><div class="metric"><strong><?= $stats['resolved'] ?></strong><span>حالات محلولة</span></div></div>
</section>
<section class="full-band">
  <h2>كيف يعمل النظام؟</h2>
  <div class="row g-3">
    <div class="col-md-3"><div class="step">1. أنشئ حساباً آمناً.</div></div>
    <div class="col-md-3"><div class="step">2. أضف بلاغ مفقود أو موجود.</div></div>
    <div class="col-md-3"><div class="step">3. يقارن النظام الحالات تلقائياً.</div></div>
    <div class="col-md-3"><div class="step">4. يراجع المسؤول التطابق قبل كشف أي تواصل.</div></div>
  </div>
</section>
<section class="privacy-note mt-4">
  <h2>الخصوصية والحماية</h2>
  <p>يعرض البحث بيانات عامة فقط، ويخفي أرقام الهاتف ومعلومات التواصل حتى يراجع المسؤول التطابق أو يكون صاحب البلاغ هو من يفتح التفاصيل.</p>
</section>
