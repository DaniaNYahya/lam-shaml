# Lam Shaml - نظام لم شمل العائلات

نظام ويب إنساني يعمل بـ PHP 8 وMySQL على XAMPP لمساعدة العائلات على تسجيل المفقودين والأشخاص الذين تم العثور عليهم والبحث عن التطابقات المحتملة مع دعم عربي/RTL.

## التقنيات

- HTML5 وCSS3 وJavaScript وBootstrap 5.
- PHP 8+ بنمط MVC يدوي من دون Laravel.
- MySQL عبر PDO وPrepared Statements.
- جلسات PHP، CSRF، Security Headers، ورفع صور محدود ومضغوط.

## الهيكل

- `public/`: نقطة الدخول، الملفات العامة، `/health` و`uploads`.
- `app/Core`: الراوتر، الجلسات، CSRF، الاتصال، العرض، الحماية.
- `app/Controllers`: صفحات الويب وإجراءات POST.
- `app/Repositories`: طبقة الوصول إلى MySQL.
- `app/Services`: التطبيع العربي، المطابقة، رفع الصور.
- `database/`: `schema.sql` و`seed.sql` و`install.php`.
- `tests/`: مشغل اختبارات بسيط عبر PHP CLI.

## الأنماط المستخدمة

- MVC: المتحكمات في `app/Controllers` والواجهات في `app/Views`.
- Repository: كل عمليات SQL موجودة في `app/Repositories`.
- Strategy مبسطة: `MatchingService` يعزل حساب درجات الاسم والموقع والعمر والجنس.
- Observer مبسط: إنشاء إشعارات عند إنشاء البلاغ أو ظهور تطابق.

## تشغيل XAMPP

المسار المتوقع:

```text
C:\xampp\htdocs\lam-shaml
```

انسخ المشروع إلى هذا المسار، ثم شغّل Apache وMySQL من لوحة XAMPP. الإعدادات المحلية الافتراضية:

```text
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lam_shaml
DB_USERNAME=root
DB_PASSWORD=
```

انسخ `.env.example` إلى `.env` عند الحاجة وعدّل القيم محلياً فقط. لا ترفع `.env` إلى GitHub.

إنشاء قاعدة البيانات:

```powershell
C:\xampp\php\php.exe database\install.php
```

أو من phpMyAdmin استورد `database/schema.sql` ثم `database/seed.sql`.

رابط التشغيل:

```text
http://localhost/lam-shaml/public/
http://localhost/lam-shaml/public/health
```

بديل PHP built-in server، مع بقاء MySQL في XAMPP مشغلاً:

```powershell
C:\xampp\php\php.exe -S localhost:8000 -t public
```

## حسابات تجريبية

- Admin: `admin@lamshaml.com` / `Admin@123`
- User: `user@example.com` / `User@123`
- Organization: `org@example.com` / `Org@123`

## البحث والمطابقة

تطبيع الاسم يزيل التشكيل والتطويل، يوحد الهمزات، الألف المقصورة، التاء المربوطة، وبعض اختلافات كتابة الاسم، ويدعم اختلاف ترتيب أجزاء الاسم والبحث بجزء منه. الدرجة النهائية:

- الاسم: 50%.
- العمر: 15%.
- المدينة أو المنطقة: 15%.
- الجنس: 10%.
- آخر مكان معروف: 10%.

## تشغيل الاختبارات

```powershell
C:\xampp\php\php.exe tests\run.php
```

اختبار Syntax:

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { C:\xampp\php\php.exe -l $_.FullName }
```

## Security Checklist

- تستخدم كل استعلامات التطبيق PDO Prepared Statements.
- كلمات المرور مخزنة بـ `password_hash()` والتحقق بـ `password_verify()`.
- كل نماذج POST محمية برمز CSRF، والرفض يرجع 403.
- Session ID يتجدد بعد تسجيل الدخول، وتسجيل الخروج يدمر الجلسة.
- Rate limit محلي لمحاولات تسجيل الدخول الفاشلة.
- أرقام الهاتف مخفية في البحث والتفاصيل لغير صاحب البلاغ أو Admin.
- رفع الصور يتحقق من MIME الفعلي والحجم ويعيد التحجيم عبر GD.
- `.htaccess` يمنع فهرسة المجلدات والوصول إلى ملفات حساسة ومجلدات التطبيق.
- Security Headers مفعلة: CSP و`nosniff` و`DENY` وReferrer/Permissions Policy.
- تأكيد التطابق يتم داخل Transaction ويحدث الحالتين معاً.
- HSTS وفرض HTTPS مخصصان للإنتاج فقط حتى لا ينكسر localhost.
- هذا نظام إنساني، ولا يجب استخدامه مع بيانات حقيقية قبل مراجعة أمنية مستقلة.

## مشاكل شائعة

- إذا ظهرت 404 للروابط الجميلة، فعّل `mod_rewrite` في Apache أو افتح الصفحات عبر `index.php?path=...`.
- إذا فشل الاتصال بقاعدة البيانات، تحقق من تشغيل MySQL ومنفذ `3306`.
- إذا فشل رفع الصور، تحقق من تفعيل امتداد GD ومن قابلية `public/uploads` للكتابة.
- إذا أردت إعادة التثبيت، احذف `database/install.lock` يدوياً ثم شغّل `database/install.php`.
