# وضعیت پروژه EXPLORIA - 2026-06-23

## خلاصه اجرایی

نسخه قابل ادامه پروژه از آرشیو کامل `Exploria_EcoPark_QR_Core_Source_2026-06-21_13-42-42.zip` بازیابی شد. پوشه فعال قدیمی `exploria-codebase` ناقص است و برای ادامه مستقیم مناسب نیست، چون پوشه‌های مهم `routes`, `resources`, `tests`, `public` و `vendor` در آن وجود ندارند.

برای ادامه کار، کپی کاری زیر ساخته شد:

`E:\exploria-codebase-current`

این مسیر ASCII است و برای اجرای PHP/Composer/Node از مسیر فارسی قابل اعتمادتر است.

## منابع Canonical خوانده‌شده

- `AGENTS.md`
- `راهنمای بازیابی پروژه در کدکس.md`
- `docs/governance/governance/20-EXPLORIA_AI_Development_Framework_Laravel_React_Monolith_v1.0.md`
- `docs/governance/governance/21-EXPLORIA_PreCoding_Master_Execution_Control_v1.0.md`
- `docs/BOOTSTRAP_STATUS.md`
- `docs/DELIVERY_SLICES.md`
- `docs/features/S1A_004_CONSENT.md`
- `docs/features/S1A_005_006_PILOT_LOCATION_QR.md`

## وضعیت پیاده‌سازی

- معماری مصوب: Laravel + React monolith با Inertia-style.
- Bootstrap پایه قبلا ثبت شده: Laravel 13.16.1, React 19, TypeScript.
- S1A-004 Consent طبق سند feature انجام شده و قبلا با 54 تست و 196 assertion پاس اعلام شده است.
- S1A-005/006 Pilot Location + QR Registry طبق سند feature انجام شده و قبلا با 62 تست و 227 assertion پاس اعلام شده است.
- EcoPark عباس‌آباد پایلوت اصلی فعال است.
- Eram Park در وضعیت draft است.
- Milad Tower در وضعیت placeholder کنترل‌شده است.
- QR demo برای `/scan/{code}` وجود دارد.
- QR Registry حداقلی تحت auth و role middleware طراحی شده است.

## یافته‌های مهم امروز

- `git` در PATH سیستم فعلی موجود نیست.
- در `.git` پوشه ناقص قدیمی، HEAD برابر `c376152184973a8a76a286b007c9166a76e60c54` است و log تا commit `feat: add versioned pilot consent flow` دیده می‌شود؛ آرشیو QR Core ظاهرا جلوتر از این git checkout است.
- آخرین source کامل موجود: `Codex-Archives\Exploria_EcoPark_QR_Core_Source_2026-06-21_13-42-42.zip`.
- مسیر فارسی پروژه برای بعضی ابزارها مشکل‌ساز شد، به‌خصوص PHP extension loading و Composer.
- PHP runtime به مسیر ASCII زیر کپی شد و openssl در آن فعال است:
  `E:\exploria-toolchain-local\php83`
- Node runtime موجود:
  `E:\فایل 1 اصلی پروژه اکسپلوریا\فایل های تکمیلی قبل از کدنویسی\.toolchain\node`

## Verification امروز

- PHP syntax check روی نسخه بازیابی‌شده: PASS برای 194 فایل.
- `npm install` روی `E:\exploria-codebase-current`: PASS، 425 package نصب شد، 0 vulnerability.
- `npm run types:check`: PASS.
- `npm run lint:check`: PASS.
- `npm run build`: BLOCKED، چون Composer نصب نشده و `vendor/autoload.php` وجود ندارد.
- `composer install`: BLOCKED، هنگام دانلود packageها در نوشتن فایل‌های موقت داخل `vendor/composer` خطای `Permission denied` می‌دهد.
- Backend tests اجرا نشد، چون `vendor` کامل نیست.

## ریسک‌ها و کارهای باز

- Git باید نصب یا به PATH اضافه شود؛ بدون Git commit و بازیابی تاریخچه قابل اعتماد نیست.
- Composer install باید حل شود؛ تا آن زمان build کامل و PHPUnit قابل اجرا نیست.
- مسیر کاری توصیه‌شده فعلا `E:\exploria-codebase-current` است، نه پوشه ناقص قدیمی.
- `.env` تولید/تنظیم نشده است؛ فقط `.env.example` در کپی کاری وجود دارد.
- متن حقوقی Consent هنوز demo و غیرنهایی است و قبل از UAT باید تایید شود.
- داده‌های میدانی Zone/Hub/Touchpoint هنوز demo هستند.
- Featureهای خارج از Slice فعلی هنوز نباید شروع شوند: Reward, Settlement, Marketplace, Advanced Analytics, Offline Sync کامل.

## مرحله پیشنهادی بعدی

1. حل Composer install و تولید `vendor`.
2. نصب Git یا معرفی مسیر Git به PATH.
3. ساخت `.env` local امن از `.env.example` و key generation.
4. اجرای migration/seed/test روی دیتابیس local.
5. اجرای `npm run build` پس از آماده شدن vendor.
6. شروع Slice بعدی: Attributed Scan Event و سپس Admin Dashboard Summary حداقلی EcoPark.
