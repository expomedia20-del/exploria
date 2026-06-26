# وضعیت پروژه اکسپلوریا - 2026-06-24

## خلاصه اجرایی

نسخه قابل ادامه پروژه از آرشیو کامل `Exploria_EcoPark_QR_Core_Source_2026-06-21_13-42-42.zip` بازیابی شد و مسیر کاری فعلی این است:

`E:\exploria-codebase-current`

پوشه قدیمی `exploria-codebase` داخل مسیر فارسی منابع، ناقص بود و برای ادامه توسعه قابل اتکا نیست؛ چند پوشه کلیدی مثل `routes`، `resources`، `tests`، `public` و `vendor` را نداشت.

## وضعیت ابزارها

- PHP محلی پایدار: `E:\exploria-toolchain-local\php\php.exe`
- نسخه PHP تاییدشده: PHP 8.4.22
- Composer phar: `E:\exploria-toolchain-local\composer\composer.phar`
- Node محلی: `E:\فایل 1 اصلی پروژه اکسپلوریا\فایل های تکمیلی قبل از کدنویسی\.toolchain\node`
- Git نصب شده است، ولی در نشست فعلی PowerShell هنوز از PATH شناخته نمی‌شود. مسیر مستقیم قابل استفاده:
  - `C:\Program Files\Git\cmd\git.exe`
- extensionهای SQLite در `E:\exploria-toolchain-local\php\php.ini` فعال شدند:
  - `pdo_sqlite`
  - `sqlite3`
- `memory_limit` در PHP محلی از `128M` به `512M` افزایش یافت تا PHPStan/Larastan پایدار اجرا شود.
- Composer global mirror روی لیارا تنظیم شد:
  - `https://package-mirror.liara.ir/repository/composer/`

## وضعیت Git

- در `E:\exploria-codebase-current` یک repository محلی تازه ساخته شد.
- branch فعلی: `main`
- commit پایه: `9d48b6a`
- پیام commit: `chore: establish recovered exploria baseline`
- commit بعد از نصب dev و سبز شدن quality gate: `303af0a`
- پیام commit: `chore: enable dev dependencies and quality gates`
- commit مستندات توسعه محلی: `023c05b`
- پیام commit: `docs: document local development runtime`
- commit ثبت بازدید و داشبورد عملیاتی: `4c405b0`
- پیام commit: `feat: record pilot visits and show operational dashboard`

## وابستگی‌ها

- `npm install` در مسیر کاری اصلی با موفقیت انجام شد.
- `composer install --no-dev --prefer-source --no-progress --no-interaction` در مسیر کاری اصلی با موفقیت انجام شد.
- `vendor/autoload.php` موجود است.
- بعد از تنظیم mirror لیارا و refresh کردن `composer.lock`، نصب کامل dev dependencies هم موفق شد.
- ابزارهای `vendor/bin/phpunit`، `vendor/bin/phpstan` و `vendor/bin/pint` اکنون موجودند.

## دلیل تغییر build

Build اولیه Vite به دلیل تلاش `laravel-vite-plugin/fonts` برای اتصال به `fonts.bunny.net` متوقف می‌شد. برای مستقل شدن build از شبکه:

- import مربوط به `laravel-vite-plugin/fonts` از `vite.config.ts` حذف شد.
- گزینه `fonts: [bunny(...)]` از تنظیمات Laravel Vite plugin حذف شد.
- دستور Blade `@fonts` از `resources/views/app.blade.php` حذف شد.

پیشنهاد مرحله بعد: افزودن فونت محلی فارسی مثل Vazirmatn به شکل asset داخلی پروژه.

## کنترل‌های انجام‌شده

در مسیر `E:\exploria-codebase-current`:

- `php artisan --version`: موفق، Laravel Framework 13.16.1
- `php artisan route:list --except-vendor`: موفق، 13 route شناسایی شد
- `npm run types:check`: موفق
- `npm run lint:check`: موفق
- `npm run build`: موفق

## وضعیت تست بک‌اند

مشکل نصب dev dependencies با تنظیم mirror لیارا حل شد. پیش از این، نصب روی بسته `phpstan/phpstan` هنگام نوشتن zip موقت با خطای زیر شکست می‌خورد:

`vendor/composer/tmp-...zip: Failed to open stream: Permission denied`

برای قابل اجرا شدن تست‌ها:

- پیش‌نیاز SQLite آماده شد، چون `phpunit.xml` دیتابیس تست را روی SQLite in-memory تنظیم کرده است.
- یک `APP_KEY` تستی غیر production داخل `phpunit.xml` ثبت شد.
- خطاهای Larastan/PHPStan در مدل‌ها، Requestها و `QrRegistryService` اصلاح شدند.

نتیجه نهایی:

- `composer test`: موفق
- `vendor/bin/phpstan analyse`: موفق از طریق Composer
- `php artisan test`: موفق، 62 تست و 227 assertion

تصمیم پیشنهادی:

1. فعلا توسعه را روی سورس پایدار و build موفق ادامه دهیم.
2. قبل از تغییرات بزرگ بک‌اند، `composer test` و `npm run build` اجرا شوند.
3. برای نصب مجدد وابستگی‌ها، mirror لیارا یا یک mirror قابل اعتماد مشابه فعال بماند.

## مسیرهای مهم

- سورس معتبر فعلی: `E:\exploria-codebase-current`
- کپی موقت موفق برای آزمایش: `C:\Temp\exploria-codebase-current`
- پوشه انتقال دانش: `E:\فایل 1 اصلی پروژه اکسپلوریا\فایل های تکمیلی قبل از کدنویسی\01-انتقال دانش روزانه`
- بکاپ‌ها: `E:\فایل 1 اصلی پروژه اکسپلوریا\فایل های تکمیلی قبل از کدنویسی\.codex-backups`

## وضعیت محیط توسعه محلی

- فایل `.env` محلی ساخته شد و طبق `.gitignore` وارد Git نمی‌شود.
- دیتابیس توسعه روی SQLite تنظیم شد:
  - `DB_CONNECTION=sqlite`
  - `DB_DATABASE=database/database.sqlite`
- قبل از migration، دیتابیس SQLite قبلی بکاپ گرفته شد:
  - `database.sqlite_before_local_env_2026-06-24.bak`
- `php artisan migrate:fresh --seed --force` با موفقیت اجرا شد.
- seed پایلوت فعلی:
  - venues: 3
  - zones: 1
  - hubs: 1
  - touchpoints: 1
  - campaigns: 1
  - qr_codes: 1
  - consent_versions: 1
  - users: 1
- demo QR:
  - `ep1405-a7f3k9m2q8x4`
- سند قابل تکرار توسعه محلی اضافه شد:
  - `docs/LOCAL_DEVELOPMENT.md`

## وضعیت مسیر اجرایی پایلوت

مسیر پایلوت اکنون از حالت سه‌مرحله‌ای خارج شد و تا داشبورد عملیاتی ادامه پیدا می‌کند:

`QR -> OTP -> Consent -> Visit Experience -> Dashboard`

موارد اضافه‌شده:

- جدول و مدل `Visit`
- ثبت بازدید تاییدشده بعد از پذیرش رضایت‌نامه از مسیر QR
- صفحه تجربه بازدید:
  - `/visits/{visit}`
- داشبورد عملیاتی با آمار واقعی:
  - مکان‌های پایلوت
  - QR فعال
  - درخواست‌های OTP
  - رضایت‌های ثبت‌شده
  - بازدیدهای ثبت‌شده
  - آخرین بازدیدها

نتیجه تست مرورگری:

- `/scan/ep1405-a7f3k9m2q8x4`: موفق
- OTP با شماره تست و کد `123456`: موفق
- پذیرش رضایت‌نامه: موفق
- هدایت به صفحه بازدید: موفق
- نمایش داشبورد عملیاتی: موفق
