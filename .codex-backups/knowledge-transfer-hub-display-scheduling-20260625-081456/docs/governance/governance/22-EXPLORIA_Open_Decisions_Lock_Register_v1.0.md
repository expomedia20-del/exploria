# 22 — EXPLORIA Open Decisions Lock Register v1.0

## 1. وضعیت

**Status:** Development Decisions Locked / Production Decisions Deferred by Gate  
**Date:** 2026-06-20  
**Scope:** تصمیم‌های لازم برای آغاز Sprint 1A بدون ایجاد وابستگی زودهنگام به سرویس‌های Production

## 2. اصل تصمیم‌گیری

هر تصمیم این سند باید یکی از سه وضعیت را داشته باشد:

- `LOCKED FOR DEVELOPMENT`: برای ساخت و تست محلی قطعی است.
- `DEFERRED BEFORE STAGING`: مانع توسعه محلی نیست، اما پیش از Staging باید نهایی شود.
- `DEFERRED BEFORE UAT/PILOT`: مانع ساخت هسته نیست، اما پیش از UAT یا پایلوت باید تأیید شود.

## 3. رجیستر تصمیم‌ها

| ID | موضوع | تصمیم توسعه‌ای | وضعیت | Gate بعدی |
|---|---|---|---|---|
| OD-001 | OTP Provider | تعریف Contract/Adapter در Laravel؛ استفاده از OTP ثابت `123456` فقط در Local/Test؛ عدم اتصال مستقیم Domain Logic به Provider | LOCKED FOR DEVELOPMENT | Provider واقعی و هزینه قبل از Staging |
| OD-002 | Consent | مدل Versioned Consent و ConsentLog؛ متن کوتاه آزمایشی با برچسب غیرنهایی؛ جلوگیری از ثبت تعامل حساس قبل از پذیرش | LOCKED FOR DEVELOPMENT | متن حقوقی نهایی قبل از UAT |
| OD-003 | QR Format | مسیر Canonical برابر `/scan/{code}`؛ کد یکتا و غیرقابل حدس؛ اتصال اجباری به Venue/Touchpoint/Campaign پیش از Activation | LOCKED FOR DEVELOPMENT | ابعاد چاپ، لوگو و URL دامنه قبل از نصب |
| OD-004 | Pilot Seed Data | EcoPark پایلوت Primary؛ Eram Secondary؛ Milad Tower به‌صورت Controlled Placeholder؛ داده‌ها صریحاً Demo علامت‌گذاری شوند | LOCKED FOR DEVELOPMENT | داده میدانی نهایی قبل از Sprint 1 QA |
| OD-005 | Environment | Local-first؛ همه URLها و تنظیمات وابسته به محیط از `.env`؛ هیچ Domain یا Secret در کد Hardcode نشود | LOCKED FOR DEVELOPMENT | Staging، Domain و SSL قبل از UAT |
| OD-006 | Offline Policy | در Sprint 1A فقط پیام اتصال ضعیف، Retry و Issue/Fallback Log؛ Offline Sync کامل خارج از Scope | LOCKED FOR DEVELOPMENT | مجوز ثبت دستی و سیاست عملیات قبل از Pilot |
| OD-007 | Admin Authentication | Laravel Session Authentication؛ نقش‌های اولیه `admin`، `operator` و `viewer`؛ همه مسیرهای Admin تحت Middleware/Policy | LOCKED FOR DEVELOPMENT | سیاست حساب سازمانی و MFA قبل از Production |
| OD-008 | Database | PostgreSQL؛ دیتابیس و Credential جدا برای Local/Test/Staging/Production؛ تست‌ها از DB ایزوله استفاده کنند | LOCKED FOR DEVELOPMENT | Provisioning محیط Staging قبل از UAT |
| OD-009 | Logging | Laravel structured application log؛ عدم ثبت OTP، Token، Mobile کامل یا PII خام؛ Audit Log برای تغییرات مدیریتی حساس | LOCKED FOR DEVELOPMENT | سامانه متمرکز، Retention و Alerting قبل از Pilot |

## 4. قواعد امنیتی غیرقابل تعویق

1. OTP ثابت فقط در `local` و `testing` مجاز است و در محیط‌های دیگر باید Fail-Closed باشد.
2. Secretها فقط از Environment دریافت می‌شوند.
3. شماره موبایل در Log باید Mask شود.
4. مسیرهای Admin بدون Authentication و Authorization مجاز نیستند.
5. QR غیرفعال، منقضی، خارج از بازه یا بدون Binding معتبر نباید Event پذیرفته تولید کند.
6. Consent باید با Version، Timestamp، Subject/Session و Source قابل ردیابی باشد.

## 5. تصمیم‌های خارج از Sprint 1A

- SMS Provider واقعی
- متن حقوقی نهایی
- MFA و SSO سازمانی
- Offline Sync کامل
- Reward/Wallet/Settlement
- Monitoring و Observability متمرکز Production
- طراحی چاپ نهایی QR و نصب میدانی

این موارد باید در Backlog باقی بمانند و نبودشان نباید با پیاده‌سازی موقت ناامن جبران شود.

## 6. Exit Gate

این Gate زمانی Pass است که:

- همه تصمیم‌های لازم برای Local Development وضعیت `LOCKED FOR DEVELOPMENT` داشته باشند.
- تصمیم‌های Production دارای موعد و Gate روشن باشند.
- هیچ Secret، Provider یا متن حقوقی غیرنهایی به‌صورت Hardcode وارد هسته نشود.

**Gate Result:** PASS FOR SPRINT 1A PLANNING
