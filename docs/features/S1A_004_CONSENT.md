# S1A-004 — Versioned Consent

## هدف و مرجع

- Work Item: `S1A-004`
- Acceptance: `AC-S1A-05` و `UX-GATE-003`
- تصمیم: `OD-002`

## خروجی

- نمایش عمومی آخرین رضایت‌نامه فعال فارسی از `GET /api/v1/consents/current`.
- ثبت پذیرش فقط برای کاربر دارای Laravel Session از `POST /api/v1/consents/accept`.
- صفحه فارسی و RTL در `/consent` با حالت‌های Loading، Empty، Error و Success.
- ثبت Version، User، Timestamp، Hash نشست و Source؛ پذیرش تکراری همان نسخه رکورد جدید نمی‌سازد.
- هدایت خودکار کاربر جدید پس از OTP به رضایت‌نامه فعال.
- عبور خودکار کاربر بازگشتی از رضایت‌نامه‌ای که همان نسخه فعال را قبلاً پذیرفته است.
- ادامه مستقیم کاربر بازگشتی از QR به بازدید متناظر، بدون ایجاد ConsentLog تکراری.

## امنیت و حریم خصوصی

- شناسه خام Session ذخیره یا در Response نمایش داده نمی‌شود.
- پذیرش نسخه غیرفعال رد می‌شود.
- متن Seed فعلی صریحاً Demo و غیرنهایی است.

## Verification

- Migration و Rollback: PASS
- Seed نسخه `pilot-fa-0.1`: PASS
- تست‌های هدفمند Auth، Consent، QR و Visit: 32 تست و 178 Assertion — PASS
- PHPUnit کامل: 231 تست و 2,031 Assertion — PASS
- PHPStan کل پروژه: صفر Finding — PASS
- TypeScript، ESLint و Production Build — PASS

## محدودیت باز

متن حقوقی نهایی باید قبل از UAT با نسخه جدید و تأیید رسمی Product Owner جایگزین شود.
