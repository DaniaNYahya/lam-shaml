# تشغيل مشروع Lam Shaml محلياً

هذا الملف يشرح أسرع طريقة لتشغيل المشروع على Windows باستخدام XAMPP.

## 1. نسخ المشروع

انسخ مجلد المشروع إلى المسار التالي:

```text
C:\xampp\htdocs\lam-shaml
```

يجب أن يصبح ملف الدخول موجوداً هنا:

```text
C:\xampp\htdocs\lam-shaml\public\index.php
```

## 2. تشغيل XAMPP

افتح XAMPP Control Panel ثم شغّل:

- Apache
- MySQL

## 3. إعداد قاعدة البيانات

افتح Terminal داخل مجلد المشروع:

```powershell
cd C:\xampp\htdocs\lam-shaml
```

ثم شغّل سكربت التثبيت:

```powershell
C:\xampp\php\php.exe database\install.php
```

إذا كان MySQL عندك يعمل على منفذ مختلف، عدّل ملف `.env` بعد نسخه من `.env.example`.

مثال للمنفذ الافتراضي:

```text
DB_PORT=3306
```

وإذا كان XAMPP مضبوطاً عندك على 3308:

```text
DB_PORT=3308
```

## 4. فتح المشروع

افتح الرابط:

```text
http://localhost/lam-shaml/public/
```

صفحة فحص الصحة:

```text
http://localhost/lam-shaml/public/health
```

phpMyAdmin:

```text
http://localhost/phpmyadmin/
```

## 5. بيانات الدخول التجريبية

```text
Admin
Email: admin@lamshaml.com
Password: Admin@123
```

```text
User
Email: user@example.com
Password: User@123
```

```text
Organization
Email: org@example.com
Password: Org@123
```

## 6. تشغيل بدون Apache كبديل

يمكن تشغيل PHP Built-in Server:

```powershell
C:\xampp\php\php.exe -S localhost:8000 -t public
```

ثم افتح:

```text
http://localhost:8000/
```

ملاحظة: يجب أن يبقى MySQL في XAMPP مشغلاً.

## 7. مشاكل شائعة

- إذا ظهرت صفحة خطأ 500، تأكد أن MySQL يعمل وأن بيانات `.env` صحيحة.
- إذا ظهر خطأ في الروابط، فعّل `mod_rewrite` من إعدادات Apache.
- إذا فشل رفع الصور، تأكد من تفعيل امتداد `GD` ومن قابلية الكتابة داخل `public/uploads`.
- إذا أردت إعادة تثبيت قاعدة البيانات، احذف الملف:

```text
database\install.lock
```

ثم شغّل:

```powershell
C:\xampp\php\php.exe database\install.php
```
