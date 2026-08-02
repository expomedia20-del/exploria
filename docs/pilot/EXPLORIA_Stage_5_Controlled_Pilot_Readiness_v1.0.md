# EXPLORIA — گزارش آمادگی پایلوت کنترل‌شده (مرحله ۵)

**نسخه:** 1.0  
**تاریخ اجرا:** ۱۴۰۵/۰۵/۱۱ (2026-08-02)  
**Baseline:** `e57b14d` روی `main` و همگام با `origin/main`  
**محیط اجرا:** Local، `http://127.0.0.1:8004`  
**وضعیت:** مرحله ۵ محلی تکمیل شد؛ پایلوت عمومی و Production همچنان NO-GO هستند.

## ۱. جمع‌بندی تصمیم

| تصمیم | نتیجه |
|---|---|
| ادامه توسعه و مانور Local | **GO** |
| ورود به Staging واقعی | **NO-GO تا تأمین زیرساخت و عبور Gateها** |
| اجرای پایلوت عمومی/میدانی | **NO-GO تا تأیید حقوقی، عملیاتی و میدانی** |
| استقرار Production | **خارج از دامنه مرحله ۵ و غیرمجاز در وضعیت فعلی** |

سبز بودن تست‌ها به معنی آماده‌بودن Production نیست. فرمان Production Readiness در محیط Local عمداً `ready=false` گزارش کرد و این رفتار در Launch Assurance به یک الزام Fail-closed تبدیل شد.

## ۲. خروجی‌های ایجادشده

1. بسته اجرایی و قابل‌تکمیل پایلوت در `EXPLORIA_Stage_5_Controlled_Pilot_Launch_Kit_v1.0.md` ایجاد شد.
2. Gateهای G0 تا G5، دفتر پیش‌نیازهای خارجی، ماتریس تماس، چک‌لیست شیفت، Incident/Escalation، Fallback اتصال ضعیف، KPI و صورت‌جلسه Go/No-Go یکپارچه شدند.
3. `scripts/run-launch-assurance.ps1` با دو حالت صریح تقویت شد:
   - حالت Local با `-LocalDryRun`: سلامت Runtime و آمادگی داده را می‌سنجد و الزاماً بررسی می‌کند که Local به Production تبدیل نشده باشد.
   - حالت Staging/Production: Production Readiness را Fail-closed نگه می‌دارد و فقط Health URL مبتنی بر HTTPS را می‌پذیرد.
4. Demo Readiness و Health Check به زنجیره Launch Assurance اضافه شدند.
5. کشف Composer اصلاح شد تا Composer معتبر روی PATH یا Toolchain محلی قابل استفاده باشد و نبود آن همچنان باعث توقف شود.
6. تست محافظ زیرساخت برای رفتارهای جدید افزوده شد.

هیچ Package، Migration، Role، مدل داده، مسیر اصلی یا معماری تغییر نکرد.

## ۳. نتیجه Verification فنی

| کنترل | نتیجه |
|---|---|
| همگام‌بودن Baseline با `origin/main` | Pass |
| `php artisan migrate --force --no-interaction` | Pass — `Nothing to migrate` |
| Multi-campaign Assurance | Pass — ۱۰ Pass، ۰ Warning، ۰ Fail |
| Demo Readiness | Pass — ۱۹ Pass، ۰ Warning، ۰ Fail |
| Production Readiness در Local | Expected Block — ۴ Pass، ۸ Fail، `ready=false` |
| Launch Assurance پیش‌فرض روی Local | Pass — در Gate مربوط به Staging/Production متوقف شد |
| Runtime Health | Pass — HTTP 200 روی `/up` |
| تست هدفمند زیرساخت | Pass — ۵ تست، ۱۱۵ Assertion |
| تست کامل | Pass — ۳۶۳ تست، ۴۶۹۸ Assertion |
| ESLint | Pass |
| Prettier | Pass |
| TypeScript `tsc --noEmit` | Pass |
| Laravel Pint | Pass |
| PHPStan | Pass — ۰ Error |
| Vite Production Build | Pass — ۲۳۳۹ Module |
| Composer Audit | Pass — ۰ Advisory و ۰ Abandoned package |
| NPM Production Audit | Pass — ۰ Vulnerability |
| Parse اسکریپت PowerShell | Pass |
| Bash syntax check | Not Run — Bash روی میزبان Windows موجود نبود |
| PostgreSQL test gate | Not Run — Credential و دیتابیس تست ایزوله ارائه نشده بود |
| PostgreSQL Backup/Restore rehearsal | Not Run — محیط ایزوله و Backup واقعی ارائه نشده بود |

دستور نهایی اجراشده:

```powershell
.\scripts\run-launch-assurance.ps1 `
  -LocalDryRun `
  -HealthUrl 'http://127.0.0.1:8004/up'
```

## ۴. نتیجه مانور مرورگری

سه مسیر با نشست Admin آزمایشی و Snapshot تازه DOM بازبینی شدند:

| مسیر | نتیجه | مشاهده کلیدی |
|---|---|---|
| `/admin/demo-cycle` | Pass | پنج مرحله دمو قابل مشاهده، داده Demo حاضر و مسیر End-to-End اجراشده است. |
| `/admin/commercialization` | Pass | قیف تجاری، KPI، ROI پیشنهادی و بسته‌های مذاکره رندر شدند. |
| `/admin/events/scan-log` | Pass | مانیتور فقط‌خواندنی، فیلترها و Audit رویدادها بدون موبایل/IP/Session خام رندر شدند. |

اعداد این صفحات متعلق به داده Local/Demo هستند و نباید به‌عنوان KPI پایلوت واقعی یا ادعای تجاری قطعی استفاده شوند.

Screenshotها در نشست Browser گرفته شدند، اما Plugin آن‌ها را به فایل Workspace منتقل نکرد؛ به همین دلیل هیچ Screenshotی به‌عنوان Evidence قابل‌بازیابی در این گزارش ادعا نشده است. اعتبار مانور بر Snapshotهای DOM، URL و عنوان صفحات استوار است.

## ۵. یافته‌های مانور

### یافته بسته‌شده

**S5-F01 — کشف محدود Composer در Launch Assurance**

- شدت: P1 برای Runbook، بدون اثر بر Runtime.
- مشاهده: اسکریپت فقط یک `composer.phar` محلی قدیمی را می‌پذیرفت و Composer معتبر سیستم را پیدا نمی‌کرد.
- اقدام: Resolution روی PATH/Toolchain افزوده و با تست محافظ پوشش داده شد.
- وضعیت: Closed و در اجرای کامل تأیید شد.

### یافته‌های باز غیرمسدودکننده برای Local

**S5-F02 — چک‌لیست عملیاتی هنوز کامل نیست**

- صفحه Demo Cycle مقدار ۹ از ۱۲ (۷۵٪) را نشان می‌دهد.
- مسیر Stress Demo نیز ۸ از ۱۱ (۷۳٪) و سه آیتم نیازمند اقدام دارد.
- اثر: مانع نمایش Local نیست، اما پیش از پایلوت عمومی باید با مالک واقعی و مدرک تکمیل شود.
- وضعیت: Open؛ بخشی از G2/G3.

**S5-F03 — متن قدیمی تعداد کنترل‌ها در صفحه تجاری‌سازی**

- متن قیف تجاری «17 pass» را نشان می‌دهد، در حالی که فرمان فعلی Demo Readiness برابر ۱۹ Pass است.
- شدت: P3 محتوایی؛ روی محاسبه Gate یا Runtime اثر ندارد.
- اقدام پیشنهادی: پیش از ارائه رسمی، متن ثابت با خروجی پویا یا عدد فعلی همگام شود.
- وضعیت: Open؛ در این مرحله به‌دلیل پرهیز از تغییر Feature اصلاح نشد.

## ۶. Gateهای باز خارجی

موارد زیر خارج از Workspace هستند و بدون ورودی/اختیار واقعی قابل تکمیل نیستند:

- سرور Linux و دسترسی SSH محدودشده.
- دامنه، DNS و TLS معتبر.
- PostgreSQL ایزوله و Credential مدیریت‌شده.
- Queue/Cache/Session پایدار و امن.
- Provider واقعی OTP، Endpoint مبتنی بر HTTPS و Secret امن.
- Logging مرکزی، Retention، Alerting و مالک On-call.
- Backup معتبر و Restore موفق روی دیتابیس ایزوله مجاز.
- تأیید حقوقی Consent/Privacy و سیاست نگهداری/حذف داده.
- مجوز کتبی محل، مسئول ایمنی و اختیار توقف.
- نام عوامل، شرکای واقعی، موجودی جایزه، تاریخ، ظرفیت و بودجه.
- UAT رسمی با نماینده کسب‌وکار و صورت‌جلسه امضاشده Go/No-Go.

## ۷. معیار شروع اقدام بعدی

مرحله بعد فقط هنگامی باید «Staging واقعی» نامیده شود که حداقل سرور، دامنه/TLS، PostgreSQL، OTP و Logging/Backup در اختیار تیم قرار گرفته باشند. پس از آن باید:

1. `.env.staging` با Secretهای خارج از Repository تکمیل شود.
2. Deployment اتمیک و Migration روی Staging اجرا شود.
3. Production Readiness، PostgreSQL test، Backup و Restore همگی Pass شوند.
4. UAT نقش‌محور روی URL واقعی تکرار شود.
5. G0 تا G3 در Launch Kit با نام، مدرک و امضا بسته شوند.

تا آن زمان، وضعیت معتبر پروژه: **آماده ادامه توسعه و مانور محلی؛ غیرآماده برای پایلوت عمومی و Production** است.

## ۸. فایل‌های تغییرکرده مرحله ۵

- `scripts/run-launch-assurance.ps1`
- `tests/Feature/Infrastructure/EnvironmentBaselineTest.php`
- `docs/pilot/EXPLORIA_Stage_5_Controlled_Pilot_Launch_Kit_v1.0.md`
- `docs/pilot/EXPLORIA_Stage_5_Controlled_Pilot_Readiness_v1.0.md`
