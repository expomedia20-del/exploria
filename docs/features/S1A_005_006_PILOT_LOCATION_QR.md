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
- درخواست OTP دارای منبع QR فقط پس از اعتبارسنجی مجدد QR و Bindingهای فعال صادر می‌شود.
- QR نامعتبر، غیرفعال، منقضی یا هنوزفعال‌نشده پیش از صدور OTP با پیام فارسی رد می‌شود.
- ثبت تکراری همان QR برای همان کاربر Visit جدید تولید نمی‌کند.
- تمام Seedها با `is_demo=true` مشخص و اجرای Seeder تکرارپذیر است.

## Verification

- تست‌های هدفمند Auth، Consent، QR و Visit: 37 تست و 187 Assertion — PASS.
- PHPUnit کامل: 236 تست و 2,040 Assertion — PASS.
- PHPStan کل پروژه: صفر Finding — PASS.
- تست Seed، Binding، Role، Registry، Landing و QR نامعتبر، غیرفعال، منقضی، آینده و تکراری — PASS.
- Migration و Rollback کامل — PASS.
- Pint، Prettier، ESLint و TypeScript — PASS.
- Production Build: 2,334 module؛ chunk مستقل Landing حدود 8.51 kB — PASS.
- Smoke محلی Landing: HTTP 200، RTL و عنوان EXPLORIA — PASS.
- Registry بدون Authentication: HTTP 401 — PASS.
