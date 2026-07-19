# EXPLORIA — Runbook پشتیبان‌گیری و بازیابی PostgreSQL

## قواعد ایمنی

- Credential فقط از متغیرهای `EXPLORIA_PG_*` دریافت می‌شود.
- فایل Backup خارج از Repository و در فضای رمزنگاری‌شده نگهداری شود.
- آزمون Restore فقط روی دیتابیس مستقل با پسوند `_restore_test` یا `-restore-test` مجاز است.
- اجرای Restore روی Staging یا Production با این اسکریپت عمداً رد می‌شود.

## ایجاد و بررسی Backup

```powershell
$env:EXPLORIA_PG_DATABASE='exploria_staging'
$env:EXPLORIA_PG_USERNAME='...'
$env:EXPLORIA_PG_PASSWORD='...'
.\scripts\backup-postgresql.ps1 -OutputDirectory 'D:\secure-backups\exploria'
```

اسکریپت اتصال را بررسی، Archive سفارشی PostgreSQL را ایجاد و ساختار آن را با `pg_restore --list` اعتبارسنجی می‌کند.

## آزمون بازیابی

دیتابیس خالی و ایزوله‌ای مانند `exploria_restore_test` باید از قبل Provision شود:

```powershell
$env:EXPLORIA_PG_RESTORE_DATABASE='exploria_restore_test'
.\scripts\test-postgresql-restore.ps1 -BackupPath 'D:\secure-backups\exploria\exploria-staging.dump'
```

پس از بازیابی، وجود جدول `migrations` کنترل می‌شود. موفقیت این فرمان باید دوره‌ای ثبت شود؛ وجود Backup بدون آزمون Restore برای Gate پایلوت کافی نیست.
