# S1A-007/008 — QR Scan Events & Event Log

## هدف و مرجع

- Work Items: `S1A-007` و `S1A-008`
- Acceptance: `AC-S1A-06`، `AC-S1A-07`، `AC-S1A-08` و `AC-S1A-10`
- Trace: `QR-002..004`، `EVENT-001`، `VR-004`، `VR-005` و `TC-001..003`

## دامنه اجراشده

- جدول `scan_events` برای Attribution اسکن‌های QR شناخته‌شده به QR، مکان، نقطه تماس، کمپین، کاربر/نشست و زمان.
- جدول `event_log` برای رویدادهای append-only مسیر حیاتی.
- ثبت `qr_scanned` پس از Consent و پذیرش اسکن معتبر.
- ثبت `invalid_scan` برای QR ناشناخته، غیرفعال، منقضی یا خارج از بازه اعتبار.
- ثبت `duplicate_scan_flagged` برای عبور از سقف اسکن کاربر در پنجره زمانی QR.
- ثبت امن `otp_requested`، `otp_verified` و `consent_accepted` در Event Log.
- جلوگیری از ساخت Visit یا Progress تکراری برای همان کاربر و QR.
- نمایش تعداد کل اسکن‌ها و اسکن‌های پذیرفته‌شده در Dashboard مدیریتی.
- صفحه و API فقط‌خواندنی Scan Log برای Admin، Operator و Viewer با فیلتر نتیجه و سقف ۱۰۰ رکورد آخر.

## امنیت و حریم خصوصی

- Eventها append-only هستند و از طریق Model قابل ویرایش یا حذف نیستند.
- IP، User-Agent و شناسه Session فقط به‌صورت SHA-256 ذخیره می‌شوند.
- شماره موبایل، OTP، Token و Session خام در Event Payload ذخیره نمی‌شوند.
- QR ناشناخته بدون ایجاد ScanEvent ناقص، فقط در Event Log ثبت می‌شود.

## خارج از دامنه

- Analytics پیشرفته، Risk Scoring چندعاملی و Device Fingerprinting.
- Offline Sync کامل و Issue Workflow.
- Export عمومی Eventها یا دسترسی مستقیم Partner به داده خام.

## Verification

- تست Accepted، Invalid، Duplicate، Attribution، Hashing و Append-only — PASS.
- Migration Rollback و Reapply ایزوله — PASS.
- تست Dashboard برای کل اسکن‌ها و اسکن‌های پذیرفته‌شده — PASS.
- تست Eventهای OTP/Consent، عدم نشت موبایل و کد OTP، فیلتر Scan Log و Authorization — PASS.
- PHPUnit کامل: 246 تست و 2,123 Assertion — PASS.
- PHPStan کل پروژه: صفر Finding — PASS.
- Pint، ESLint، Prettier، TypeScript و Production Build — PASS.
- Production Build: 2,335 module و chunk مستقل Event Monitor حدود 3.99 kB — PASS.
