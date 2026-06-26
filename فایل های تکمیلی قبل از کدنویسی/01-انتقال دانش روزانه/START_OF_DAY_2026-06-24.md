# شروع روز - 2026-06-24

## از کجا شروع کنیم

1. مسیر کاری اصلی را باز کنید:
   `E:\exploria-codebase-current`
2. برای PHP از این مسیر استفاده کنید:
   `E:\exploria-toolchain-local\php\php.exe`
3. برای Composer از این فایل استفاده کنید:
   `E:\exploria-toolchain-local\composer\composer.phar`
4. اگر Git در PowerShell شناخته نشد، مسیر مستقیم زیر را استفاده کنید:
   `C:\Program Files\Git\cmd\git.exe`

## وضعیت آماده

- npm dependencies نصب شده‌اند.
- Composer production dependencies نصب شده‌اند.
- Composer dev dependencies هم نصب شده‌اند.
- build frontend موفق است.
- routes لاراول قابل مشاهده‌اند.
- Git repository محلی آماده است و commit پایه `9d48b6a` روی branch `main` ثبت شده است.
- آخرین commit سالم بعد از نصب dev و سبز شدن quality gate: `303af0a`.
- آخرین commit مستندات توسعه محلی: `023c05b`.
- آخرین commit مسیر Visit/Dashboard: `4c405b0`.
- PHP محلی حالا `pdo_sqlite` و `sqlite3` را هم بارگذاری می‌کند.
- PHP محلی `memory_limit=512M` دارد.
- mirror لیارا برای Composer تنظیم شده است.
- `.env` محلی روی SQLite آماده است.
- دیتابیس توسعه با seed پایلوت آماده است.
- سند توسعه محلی: `docs/LOCAL_DEVELOPMENT.md`

## احتیاط‌ها

- `vendor/bin/phpunit`، `vendor/bin/phpstan` و `vendor/bin/pint` موجودند.
- پوشه قدیمی `exploria-codebase` داخل مسیر فارسی منابع ناقص است و نباید مبنای توسعه باشد.

## اولین کار پیشنهادی

بعد از بکاپ امروز، سراغ آماده‌سازی `.env` و دیتابیس توسعه بروید.

اگر این مرحله قبلاً انجام شده بود، گام بعدی بررسی عمیق flowهای `QR -> OTP -> Consent -> Scan -> Dashboard` و سپس افزودن فونت فارسی محلی است.

وضعیت تازه: flow اصلی اکنون تا `Visit Experience` و داشبورد عملیاتی اجرا می‌شود. گام بعدی پیشنهادی، فونت محلی فارسی و UI مدیریت QR است.
