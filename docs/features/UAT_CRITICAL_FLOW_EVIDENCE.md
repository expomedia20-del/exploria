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

در اجرای ۲۰۲۶-۰۷-۱۹، Chrome متصل شد و صفحه عمومی، صفحه QR و پنل شریک در DOM و نمای موبایل بررسی شدند. متن فارسی، RTL، عنوان‌های قابل فهم، Label فرم‌ها و نام دسترس‌پذیر کنترل‌های اصلی در صفحات بررسی‌شده قابل مشاهده بود.

موارد باز برای تکمیل UAT تعاملی:

- اجرای کامل جریان با کلیک و ورود واقعی در viewport موبایل.
- بررسی RTL، فونت، ترتیب Focus و خوانایی پیام‌های خطا.
- مشاهده Loading، Empty و Error State صفحات QR، Consent، Mission و Event Monitor.
- بررسی Responsive در عرض‌های موبایل، تبلت و دسکتاپ.
- ثبت Screenshot شواهد صفحات کلیدی.

کنترل کلیک خودکار Chrome در این اجرا درخواست‌های ناوبری را حتی برای لینک ساده اجرا نکرد؛ بنابراین آزمون کلیکی نقش‌ها و ارسال فرم‌ها به‌عنوان PASS ثبت نشد. این محدودیت مربوط به اتصال کنترل مرورگر است و بدون شواهد به‌عنوان نقص برنامه طبقه‌بندی نمی‌شود.

## نتیجه فعلی

- UAT خودکار سمت سرور: PASS.
- Readiness داده دمو: PASS.
- UAT بصری مرورگری: PARTIAL PASS — صفحه عمومی، QR و پنل شریک در نمای موبایل بررسی شد؛ تعامل کلیکی و گردش کامل نقش‌ها همچنان نیازمند اجرای دستی یا اتصال قابل‌کنترل است.
- PHPUnit کامل: 254 تست و 2,226 Assertion — PASS.
- PHPStan، Pint، ESLint، Prettier، TypeScript و Production Build — PASS.
- Audit وابستگی‌های npm و Composer در ۲۰۲۶-۰۷-۱۹ — PASS، بدون Advisory شناخته‌شده.
- Stress Demo با سفر کامل بازدیدکننده و مصرف پاداش — PASS، پیشرفت چک‌لیست ۱۰۰٪.
