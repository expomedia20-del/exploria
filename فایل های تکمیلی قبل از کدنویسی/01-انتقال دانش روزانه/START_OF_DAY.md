# شروع روز کاری EXPLORIA

## مسیرهای مهم

- مسیر منابع اصلی:
  `E:\فایل 1 اصلی پروژه اکسپلوریا\فایل های تکمیلی قبل از کدنویسی`
- مسیر کاری توصیه‌شده:
  `E:\exploria-codebase-current`
- مسیر انتقال دانش:
  `E:\فایل 1 اصلی پروژه اکسپلوریا\فایل های تکمیلی قبل از کدنویسی\01-انتقال دانش روزانه`
- مسیر PHP پایدار:
  `E:\exploria-toolchain-local\php`
- مسیر Node:
  `E:\فایل 1 اصلی پروژه اکسپلوریا\فایل های تکمیلی قبل از کدنویسی\.toolchain\node`
- مسیر مستقیم Git در صورت نبودن در PATH:
  `C:\Program Files\Git\cmd\git.exe`

## ترتیب شروع

1. `PROJECT_STATE_2026-06-24.md` را بخوان.
2. `AGENTS.md` را از ریشه codebase بخوان.
3. قبل از هر تغییر، فایل‌های درگیر، scope و معیار پذیرش را مشخص کن.
4. اگر تغییر کدی انجام می‌شود، ابتدا بکاپ تاریخ‌دار بساز.
5. بعد از تغییر، نتیجه build/test و فایل‌های تغییرکرده را در لاگ همان روز ثبت کن.

## دستورهای مفید

```powershell
$env:PATH='E:\exploria-toolchain-local\php;E:\فایل 1 اصلی پروژه اکسپلوریا\فایل های تکمیلی قبل از کدنویسی\.toolchain\node;C:\Program Files\Git\cmd;' + $env:PATH
cd E:\exploria-codebase-current
php artisan --version
php artisan route:list --except-vendor
npm run types:check
npm run lint:check
npm run build
```

## Gate قبل از توسعه بزرگ

- بکاپ روز جاری وجود داشته باشد.
- وضعیت Git مشخص باشد.
- اگر توسعه بک‌اند است، وضعیت `.env` و دیتابیس محلی روشن شود.
- برای تست بک‌اند، ابتدا dev dependencies و PHPUnit باید قابل اجرا شوند.

