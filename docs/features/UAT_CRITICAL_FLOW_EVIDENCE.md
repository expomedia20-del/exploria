# UAT — شواهد جریان حیاتی EXPLORIA

## دامنه

جریان مصوب:

`QR → OTP → Consent → Visit → Mission → Reward → Redemption → Event Monitor`

## شواهد خودکار فعلی

- QR معتبر/نامعتبر/منقضی و Duplicate Scan: پوشش Feature Test.
- OTP درخواست/تأیید، Rate Limit و عدم نشت Mobile/OTP: پوشش Feature Test.
- Consent نسخه‌دار، نمایش و پذیرش: پوشش Feature Test.
- ساخت Visit منتسب به Venue/Touchpoint/Campaign: پوشش Feature Test.
- Start/Complete مأموریت، قفل امتیاز و مالکیت Visit: پوشش Feature Test.
- صدور پاداش، رزرو موجودی و مصرف توسط Partner مجاز: پوشش Feature Test.
- Eventهای lifecycle و Audit بدون Session/PII خام: پوشش Feature Test.
- Demo Readiness اکوپارک: 17 PASS، 0 Warning، 0 Fail.

## UAT بصری

در تلاش فعلی، Runtime مرورگر هیچ Browser متصلی گزارش نکرد. مطابق دستورالعمل Browser، آزمون بصری با ابزار جایگزین انجام نشد.

موارد باز پس از اتصال مرورگر:

- اجرای کامل جریان با کلیک و ورود واقعی در viewport موبایل.
- بررسی RTL، فونت، ترتیب Focus و خوانایی پیام‌های خطا.
- مشاهده Loading، Empty و Error State صفحات QR، Consent، Mission و Event Monitor.
- بررسی Responsive در عرض‌های موبایل، تبلت و دسکتاپ.
- ثبت Screenshot شواهد صفحات کلیدی.

## نتیجه فعلی

- UAT خودکار سمت سرور: PASS.
- Readiness داده دمو: PASS.
- UAT بصری مرورگری: BLOCKED — no connected browser.
- PHPUnit کامل: 251 تست و 2,218 Assertion — PASS.
- PHPStan، Pint، ESLint، Prettier، TypeScript و Production Build — PASS.
