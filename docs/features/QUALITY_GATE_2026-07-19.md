# EXPLORIA — گزارش کنترل کیفیت ده‌محوره ۲۰۲۶-۰۷-۱۹

## نتیجه اجرایی

این گزارش نتیجه اجرای یک‌جای ده محور کنترل کیفیت پیش از ادامه توسعه قابلیت‌های جدید است.

| محور | نتیجه | شاهد |
|---|---|---|
| ۱. Baseline و CI | PASS | Git روی `main`، Migrationهای محلی اجراشده و Workflowهای CI موجودند. |
| ۲. جریان حیاتی | PASS خودکار / PARTIAL مرورگر | تست‌های QR، OTP، Consent، Visit، Mission، Reward، Redemption و Event Monitor سبز هستند؛ کنترل کلیک Chrome کامل نشد. |
| ۳. نقش‌ها و Authorization | PASS خودکار / PARTIAL بصری | تست نقش‌ها و Scopeها سبز؛ پنل شریک و منوی نقش مدیر در DOM بررسی شد. |
| ۴. Responsive و Accessibility | PARTIAL PASS | صفحه عمومی و QR در عرض ۳۹۰ بررسی شد؛ RTL، Label و نام کنترل‌های اصلی موجود بود. |
| ۵. Loading/Empty/Error | PASS خودکار / نیازمند مشاهده تکمیلی | تست‌های اعتبارسنجی و خطا سبز؛ مشاهده همه Stateها در مرورگر تعاملی باقی است. |
| ۶. PostgreSQL | CI ADDED / LOCAL BLOCKED | PostgreSQL 18 فعال است اما Credential دیتابیس تست محلی تنظیم نشده؛ Job مستقل PostgreSQL به CI افزوده شد. |
| ۷. امنیت و وابستگی‌ها | PASS | `npm audit` و `composer audit` بدون Advisory؛ اسکن الگوهای Secret نیز موردی نشان نداد. |
| ۸. Performance و Stress | PASS پایه | Stress Demo با `--execute-visitor` و Checklist صددرصد؛ پاسخ محلی Home حدود ۰٫۰۵۷ و QR حدود ۰٫۰۷۳ ثانیه. |
| ۹. Staging/Production | EXTERNAL GATE | محیط فعلی Local و Debug روشن است؛ Domain، SSL، Credential، Backup و سیاست Log باید در Staging واقعی تنظیم شوند. |
| ۱۰. اصلاح، مستندات و تحویل | PASS | خروج حساب به الگوی Wayfinder یکپارچه شد، شواهد UAT و CI PostgreSQL به‌روزرسانی شدند. |

## نتایج قابل تکرار

```text
composer ci:check
PHPUnit: 251 passed / 2,218 assertions
PHPStan: 0 error
Pint, ESLint, Prettier, TypeScript: PASS

npm audit: 0 vulnerability
composer audit: no security advisory

php artisan exploria:prepare-stress-demo --execute-visitor
Checklist progress: 100%
```

## Gateهای وابسته به محیط بیرونی

موارد زیر بدون Credential یا تصمیم رسمی نباید با مقدار ساختگی تکمیل شوند:

- Provider واقعی OTP و سیاست هزینه/Rate Limit؛
- متن حقوقی نسخه نهایی Consent؛
- Domain، SSL و محیط Staging؛
- Credential دیتابیس PostgreSQL آزمایشی محلی؛
- سیاست Backup، Retention لاگ و مانیتورینگ؛
- UAT انسانی و تکمیل مقادیر واقعی گزارش ROI پایلوت.

## Acceptance

- توسعه قابلیت جدید فقط پس از سبز ماندن CI مجاز است.
- Merge باید هر دو Job عمومی و PostgreSQL را سبز کند.
- UAT تعاملی نقش‌ها و فرم‌ها پیش از تحویل پایلوت باید تکمیل و Screenshotهای نهایی ثبت شود.
