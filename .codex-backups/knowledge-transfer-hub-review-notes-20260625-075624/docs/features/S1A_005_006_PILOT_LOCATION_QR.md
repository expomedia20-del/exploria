# S1A-005/006 — Pilot Location Core & QR Registry

## هدف و مرجع

- Work Items: `S1A-005` و `S1A-006`
- Acceptance: `AC-S1A-03`
- تصمیم‌ها: `OD-003` و `OD-004`

## دامنه اجراشده

- مدل‌های Venue، Zone، Hub، Touchpoint، Campaign حداقلی و QR Registry.
- Seed سه مکان بر اساس اولویت مالک پروژه:
  1. EcoPark عباس‌آباد: Primary و Active با زنجیره کامل Demo.
  2. پارک و شهربازی ارم: Secondary و Draft؛ توسعه در Slice بعد از تثبیت EcoPark.
  3. برج میلاد: Controlled Placeholder بدون Zone/QR فعال.
- یک Campaign حداقلی EcoPark فقط برای رعایت Binding اجباری QR.
- یک QR فعال Demo با مسیر Canonical `/scan/{code}`.
- Registry خواندنی تحت Laravel Session و Role Middleware.
- Landing فارسی، RTL و موبایل‌اول برای QR دمو.

## خارج از این Slice

- CRUD کامل Admin، Export چاپ، تولید تصویر QR، ScanEvent، تشخیص تکرار و Attribution.
- داده میدانی نهایی Zone/Hub/Touchpoint.
- فعال‌سازی Eram یا Milad.

## قواعد امنیت و داده

- QR فعال بدون Venue، Touchpoint و Campaign معتبر در دیتابیس قابل ایجاد نیست.
- Landing فقط وقتی QR و تمام Bindingهای اصلی Active و در بازه اعتبار باشند باز می‌شود.
- تمام Seedها با `is_demo=true` مشخص و اجرای Seeder تکرارپذیر است.

## Verification

- PHPUnit: 62 تست و 227 Assertion — PASS.
- تست Seed، Binding، Role، Registry، Landing و QR غیرفعال — PASS.
- Migration و Rollback کامل — PASS.
- Pint، Prettier، ESLint و TypeScript — PASS.
- Production Build: 2,297 module؛ chunk مستقل Landing حدود 3.83 kB — PASS.
- Smoke محلی Landing: HTTP 200، RTL و عنوان EXPLORIA — PASS.
- Registry بدون Authentication: HTTP 401 — PASS.
