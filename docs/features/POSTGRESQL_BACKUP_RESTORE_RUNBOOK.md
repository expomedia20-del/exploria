# EXPLORIA — Runbook پشتیبان‌گیری و بازیابی PostgreSQL

## قواعد ایمنی

- Credential فقط از متغیرهای `EXPLORIA_PG_*` دریافت می‌شود.
- فایل Backup خارج از Repository و در فضای رمزنگاری‌شده نگهداری شود.
- آزمون Restore فقط روی دیتابیس مستقل با پسوند `_restore_test` یا `-restore-test` مجاز است.
- اجرای Restore روی Staging یا Production با این اسکریپت عمداً رد می‌شود.

## ایجاد و بررسی Backup

### Windows / PowerShell

```powershell
$env:EXPLORIA_PG_DATABASE='exploria_staging'
$env:EXPLORIA_PG_USERNAME='...'
$env:EXPLORIA_PG_PASSWORD='...'
.\scripts\backup-postgresql.ps1 -OutputDirectory 'D:\secure-backups\exploria'
```

اسکریپت اتصال را بررسی، Archive سفارشی PostgreSQL را ایجاد، ساختار آن را با `pg_restore --list` اعتبارسنجی و یک Manifest یکپارچگی با پسوند `.sha256` در کنار Backup تولید می‌کند. Manifest فقط SHA-256 و نام فایل را نگه می‌دارد و نباید شامل Credential باشد.

### Linux / VPS

```bash
export EXPLORIA_PG_DATABASE='exploria_staging'
export EXPLORIA_PG_USERNAME='...'
export EXPLORIA_PG_PASSWORD='...'
export EXPLORIA_BACKUP_DIRECTORY='/srv/secure-backups/exploria'
./scripts/backup-postgresql.sh
```

اسکریپت Linux سطح دسترسی Archive و Manifest جدید را با `umask 077` و `chmod 600` محدود می‌کند. بهتر است Credential از Secret Store نشست استقرار تزریق و پس از پایان نشست پاک شود.

## آزمون بازیابی

دیتابیس خالی و ایزوله‌ای مانند `exploria_restore_test` باید از قبل Provision شود:

### Windows / PowerShell

```powershell
$env:EXPLORIA_PG_RESTORE_DATABASE='exploria_restore_test'
.\scripts\test-postgresql-restore.ps1 -BackupPath 'D:\secure-backups\exploria\exploria-staging.dump'
```

### Linux / VPS

```bash
export EXPLORIA_PG_RESTORE_DATABASE='exploria_restore_test'
./scripts/test-postgresql-restore.sh '/srv/secure-backups/exploria/exploria-staging.dump'
```

پیش از بازیابی، وجود و قالب Manifest، تطابق نام فایل و SHA-256 واقعی Archive به‌صورت Fail-Closed کنترل می‌شود. پس از بازیابی نیز وجود جدول `migrations` کنترل می‌شود. موفقیت این فرمان باید دوره‌ای ثبت شود؛ وجود Backup یا Hash بدون آزمون Restore برای Gate پایلوت کافی نیست.

SHA-256 فقط خرابی یا تغییر Archive را آشکار می‌کند و جایگزین رمزنگاری، انتقال Off-host، سیاست Retention، کنترل دسترسی یا Restore Drill واقعی نیست.
