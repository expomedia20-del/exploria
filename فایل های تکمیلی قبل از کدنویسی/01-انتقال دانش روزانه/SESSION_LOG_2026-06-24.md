# لاگ نشست - 2026-06-24

## کارهای انجام‌شده

- سورس معتبر پروژه در `E:\exploria-codebase-current` به عنوان مسیر اصلی ادامه کار تثبیت شد.
- PHP 8.4 محلی با extensionهای لازم قابل اجرا شد.
- نصب Composer production/no-dev با روش `--prefer-source` موفق شد.
- نصب npm موفق بود.
- مشکل build وابسته به Bunny Fonts حذف شد تا build بدون اتصال شبکه انجام شود.
- کنترل‌های route، typecheck، lint و build با موفقیت گذشتند.
- repository محلی Git در `E:\exploria-codebase-current` ساخته شد و commit پایه ثبت شد: `9d48b6a`.
- extensionهای `pdo_sqlite` و `sqlite3` در PHP 8.4 محلی فعال شدند.
- نصب کامل dev dependencies دوباره امتحان شد، اما همچنان روی دانلود/نوشتن zip بسته `phpstan/phpstan` با خطای `Permission denied` شکست خورد.
- Composer mirror لیارا تنظیم شد و `composer.lock` با URLهای mirror refresh شد.
- نصب کامل Composer dependencies شامل dev با موفقیت انجام شد.
- `phpunit.xml` یک `APP_KEY` تستی گرفت تا تست‌ها بدون `.env` هم اجرا شوند.
- `memory_limit` PHP محلی برای PHPStan از `128M` به `512M` افزایش یافت.
- خطاهای PHPStan/Larastan در مدل‌ها، Requestها، landing controller و `QrRegistryService` اصلاح شدند.
- commit جدید ثبت شد: `303af0a` با پیام `chore: enable dev dependencies and quality gates`.
- `.env` محلی ساخته شد و برای SQLite توسعه‌ای تنظیم شد.
- دیتابیس SQLite قبلی قبل از اجرای migration بکاپ گرفته شد.
- `php artisan migrate:fresh --seed --force` موفق بود.
- smoke test با PHP built-in server موفق بود:
  - `/up`: 200
  - `/api/v1/consents/current`: 200
  - `/scan/ep1405-a7f3k9m2q8x4`: 200
- سند `docs/LOCAL_DEVELOPMENT.md` برای بازسازی محیط توسعه محلی اضافه شد.
- commit جدید ثبت شد: `023c05b` با پیام `docs: document local development runtime`.
- migration جدید `visits` اضافه شد.
- مدل `Visit` و action ثبت بازدید اضافه شدند.
- بعد از پذیرش رضایت‌نامه در مسیر QR، بازدید ثبت و کاربر به `/visits/{visit}` هدایت می‌شود.
- صفحه `resources/js/pages/visits/show.tsx` اضافه شد.
- داشبورد از placeholder به داشبورد آماری پایلوت تبدیل شد.
- تست‌های Feature برای ثبت بازدید، idempotency، مالکیت صفحه بازدید و آمار داشبورد اضافه شدند.
- commit جدید ثبت شد: `4c405b0` با پیام `feat: record pilot visits and show operational dashboard`.

## تغییرات سورس

- `vite.config.ts`
  - حذف import فونت Bunny
  - حذف آرایه `fonts` از Laravel Vite plugin
- `resources/views/app.blade.php`
  - حذف `@fonts`

## نتیجه اعتبارسنجی

- Laravel اجراپذیر است.
- route list تولید می‌شود.
- frontend از نظر TypeScript و ESLint سالم است.
- build تولیدی Vite کامل شد.
- کنترل نهایی 2026-06-24:
  - `php artisan route:list --except-vendor`: موفق، 13 route
  - `npm run types:check`: موفق
  - `npm run lint:check`: موفق
  - `npm run build`: موفق
  - PHP modules: `curl`, `mbstring`, `openssl`, `pdo_pgsql`, `pdo_sqlite`, `sqlite3`, `zip`
- کنترل نهایی بعد از نصب dev:
  - `composer test`: موفق
  - `php artisan test`: موفق، 62 تست، 227 assertion
  - `npm run types:check`: موفق
  - `npm run lint:check`: موفق
  - `npm run build`: موفق
- کنترل نهایی بعد از آماده‌سازی env/database:
  - `composer test`: موفق
  - `npm run types:check`: موفق
  - `npm run lint:check`: موفق
  - `npm run build`: موفق
- کنترل نهایی بعد از تکمیل Visit/Dashboard:
  - `composer test`: موفق، 66 تست، 260 assertion
  - `npm run types:check`: موفق
  - `npm run lint:check`: موفق
  - `npm run build`: موفق
  - تست مرورگری مسیر `QR -> OTP -> Consent -> Visit -> Dashboard`: موفق

## مورد باز

- بهتر است در گام بعدی `.env` محلی برای توسعه ساخته شود و اتصال دیتابیس PostgreSQL یا SQLite توسعه‌ای تصمیم‌گیری شود.
- پیشنهاد فنی بعدی: افزودن فونت محلی فارسی و سپس شروع بررسی عمیق domain/data flow پروژه.
- پیشنهاد فنی بعدی: افزودن فونت محلی فارسی و سپس ساخت UI مدیریت QR برای admin/operator/viewer.

## بکاپ‌های ساخته‌شده

- `E:\فایل 1 اصلی پروژه اکسپلوریا\فایل های تکمیلی قبل از کدنویسی\.codex-backups\Exploria_Working_Source_2026-06-24_after_build_fix.zip`
  - SHA256: `8D3DCA942B1B8CB433BDFEA0F198FDA7F346040FAE907EE36C42E1C2F974601D`
- `E:\فایل 1 اصلی پروژه اکسپلوریا\فایل های تکمیلی قبل از کدنویسی\.codex-backups\Exploria_Knowledge_Transfer_2026-06-24.zip`
  - هش نهایی این ZIP در manifest بیرون از خود ZIP ثبت می‌شود تا فایل انتقال دانش به خودش وابسته نشود.
- `E:\فایل 1 اصلی پروژه اکسپلوریا\فایل های تکمیلی قبل از کدنویسی\.codex-backups\php.ini_2026-06-24_sqlite_enabled.bak`
  - SHA256: `8EE184C1994BEC810E791CE02EE1BC4BB21A12E1FCCBF7539E63BAE3CDC5BE31`
- `E:\فایل 1 اصلی پروژه اکسپلوریا\فایل های تکمیلی قبل از کدنویسی\.codex-backups\Exploria_Working_Source_2026-06-24_dev_deps_quality_gate.zip`
  - شامل سورس بعد از نصب کامل dev dependencies و سبز شدن quality gate است.
- `E:\فایل 1 اصلی پروژه اکسپلوریا\فایل های تکمیلی قبل از کدنویسی\.codex-backups\Exploria_Knowledge_Transfer_2026-06-24_after_dev_install.zip`
  - هش نهایی در manifest بیرون از ZIP ثبت می‌شود.
- `E:\فایل 1 اصلی پروژه اکسپلوریا\فایل های تکمیلی قبل از کدنویسی\.codex-backups\php.ini_2026-06-24_sqlite_512m_enabled.bak`
  - شامل فعال‌سازی SQLite و `memory_limit=512M` است.

یادداشت: بکاپ سورس عمدا `vendor`، `node_modules`، `.git`، `public/build` و فایل‌های `.env` را شامل نمی‌شود.
