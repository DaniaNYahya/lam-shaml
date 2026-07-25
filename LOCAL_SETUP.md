# التشغيل المحلي على XAMPP

1. انسخ مجلد المشروع إلى:

```text
C:\xampp\htdocs\lam-shaml
```

2. افتح XAMPP Control Panel وشغّل Apache وMySQL.

3. أنشئ ملف `.env` من `.env.example` إذا احتجت تغيير المنفذ أو كلمة مرور MySQL.

4. ثبّت قاعدة البيانات:

```powershell
cd C:\xampp\htdocs\lam-shaml
C:\xampp\php\php.exe database\install.php
```

5. افتح الموقع:

```text
http://localhost/lam-shaml/public/
```

6. افحص الصحة:

```text
http://localhost/lam-shaml/public/health
```

7. افتح phpMyAdmin عند الحاجة:

```text
http://localhost/phpmyadmin/
```

## بيانات الدخول

- Admin: `admin@lamshaml.com` / `Admin@123`
- User: `user@example.com` / `User@123`
- Organization: `org@example.com` / `Org@123`

## بديل مؤقت

إذا كان PHP متاحاً من XAMPP، يمكن تشغيل خادم PHP المدمج:

```powershell
C:\xampp\php\php.exe -S localhost:8000 -t public
```

ثم افتح `http://localhost:8000/`. يجب أن يبقى MySQL من XAMPP مشغلاً.

## ملاحظات تشخيص

- `database/install.php` يعمل محلياً فقط ويضع `database/install.lock` بعد النجاح.
- إذا ظهر خطأ Rewrite، فعّل `mod_rewrite` من ملف Apache `httpd.conf`.
- إذا لم تعمل الصور، تحقق من امتداد GD ومن صلاحية الكتابة في `public/uploads`.
- لا يحتاج المشروع إلى Virtual Host، لكن يمكن إضافته كخيار متقدم.
