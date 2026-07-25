# رفع مشروع Lam Shaml على GitHub

هذا الملف يشرح طريقة رفع المشروع على GitHub بشكل صحيح وآمن.

## قبل الرفع

تأكد أن الملفات التالية موجودة:

```text
app/
public/
database/
tests/
README.md
LOCAL_SETUP.md
RUN_PROJECT.md
start-local.bat
.env.example
.gitignore
```

ولا ترفع الملفات التالية:

```text
.env
database/install.lock
LamShaml_WEB_FINAL.zip
LamShaml_source.zip
public/uploads/*
```

ملف `.env` يحتوي إعدادات محلية، لذلك يجب أن يبقى على جهازك فقط.

## التأكد من .gitignore

يجب أن يحتوي `.gitignore` على:

```gitignore
.env
database/install.lock
public/uploads/*
!public/uploads/.htaccess
*.zip
*.log
```

## إنشاء Repository على GitHub

1. افتح GitHub.
2. اضغط New repository.
3. اكتب اسم المستودع مثلاً:

```text
lam-shaml
```

4. لا تضف README من GitHub إذا كان README موجوداً في المشروع.
5. اضغط Create repository.

## رفع المشروع بالأوامر

افتح Terminal داخل مجلد المشروع:

```powershell
cd "C:\Users\MoH\Documents\New project"
```

ثم نفذ:

```powershell
git init
git add .
git status
git commit -m "Initial Lam Shaml web project"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/lam-shaml.git
git push -u origin main
```

استبدل:

```text
YOUR_USERNAME
```

باسم حسابك في GitHub.

## إذا كان المستودع موجوداً مسبقاً

استخدم:

```powershell
git remote -v
```

إذا لم يكن remote موجوداً:

```powershell
git remote add origin https://github.com/YOUR_USERNAME/lam-shaml.git
```

إذا كان موجوداً وتريد تغييره:

```powershell
git remote set-url origin https://github.com/YOUR_USERNAME/lam-shaml.git
```

ثم:

```powershell
git add .
git commit -m "Update Lam Shaml project"
git push
```

## ملاحظات مهمة

- لا ترفع قاعدة بيانات حقيقية أو بيانات أشخاص حقيقيين.
- لا ترفع ملف `.env`.
- ارفع `database/schema.sql` و`database/seed.sql` فقط كقاعدة وبيانات وهمية للتجربة.
- بعد تنزيل المشروع من GitHub على جهاز آخر، انسخ `.env.example` إلى `.env` وعدل بيانات الاتصال.
- لتشغيل المشروع محلياً راجع:

```text
RUN_PROJECT.md
LOCAL_SETUP.md
```

## فحص سريع قبل الرفع

نفذ:

```powershell
git status
```

وتأكد أن الملفات الحساسة غير ظاهرة ضمن الملفات التي سترفع.
