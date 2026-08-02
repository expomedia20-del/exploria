# EXPLORIA Feature Status Register v1.0

## 1. کنترل سند

| فیلد | مقدار |
|---|---|
| نوع سند | Working Status Snapshot - غیرجایگزین اسناد Canonical |
| تاریخ Snapshot | 2026-08-02 |
| Codebase | Laravel + React Monolith / Inertia-style |
| شاخه مبنا | `main` |
| هدف | تفکیک روشن قابلیت‌های آماده، نمایشی، مسدود پیش از پایلوت و خارج از Scope |

این رجیستر وضعیت اجرایی Codebase را ثبت می‌کند و جایگزین BRD، FRD، Scope Lock، Backlog یا اسناد Governance مصوب نیست. در صورت تعارض، سلسله‌مراتب سند 21 اعمال می‌شود.

## 2. تعریف وضعیت‌ها

| وضعیت | تعریف |
|---|---|
| `VERIFIED_LOCAL` | پیاده‌سازی در محیط Local با تست/تحلیل ایستا/Build قابل تأیید است. |
| `DEMO_READY` | قابلیت با داده Demo و مسیر UI قابل ارائه است؛ لزوماً برای عملیات واقعی نهایی نیست. |
| `PILOT_BLOCKED` | هسته یا UI موجود است، اما تصمیم حقوقی، تجاری، داده میدانی یا اتصال بیرونی پیش از پایلوت لازم است. |
| `PRODUCTION_BLOCKED` | قابلیت برای محیط واقعی به زیرساخت، امنیت، مانیتورینگ یا Gate استقرار نیاز دارد. |
| `OUT_OF_SCOPE` | طبق Scope Lock فعلی نباید بدون Change Request توسعه یابد. |

## 3. شواهد کمی Snapshot

| شاخص | مقدار |
|---|---:|
| Routeهای ثبت‌شده | 242 |
| صفحات React | 51 |
| Modelهای Laravel | 52 |
| Serviceهای Domain/Application | 34 |
| فایل‌های تست PHP | 59 |
| نتیجه Test Suite | 361 Test / 4671 Assertion / 0 Failure |
| تحلیل ایستا و کیفیت | PHPStan 0 Error؛ ESLint، TypeScript، Pint و Prettier موفق |
| Build تولیدی | 2339 Module / موفق |
| Demo Readiness | 19 Pass / 0 Warning / 0 Fail |
| Production Readiness در محیط Local | 4 Pass / 8 Fail - مورد انتظار برای Local |
| NPM Audit پس از Stage 3 | 0 Vulnerability |
| Composer Audit پس از Stage 3 | 0 Advisory / 0 Abandoned Package |

## 4. ماتریس وضعیت قابلیت‌ها

| حوزه | قابلیت فعلی | شواهد اصلی | وضعیت | Gate بعدی |
|---|---|---|---|---|
| معماری | Codebase یکپارچه Laravel + React و Build با Vite | `composer.json`، `package.json`، `resources/js` | `VERIFIED_LOCAL` | حفظ Monolith و CI سبز |
| وب عمومی | Landing بازاریابی فارسی، معرفی مخاطبان، پکیج‌ها و ثبت درخواست دمو | `/`، `welcome.tsx`، `MarketingLandingController` | `DEMO_READY` | بازبینی متن فروش و اتصال فرآیند پیگیری واقعی |
| برد ارائه | ورود رسمی جلسه، اکوسیستم، پروپوزال و مأموریت‌های نمایشی | `/demo` و صفحات `resources/js/pages/demo` | `DEMO_READY` | اسکریپت ارائه و Reset داده قبل از جلسه |
| Authentication | ورود Session، بازیابی رمز، تأیید ایمیل، 2FA/Passkey و مسیرهای امنیت حساب | Fortify، صفحات `auth` و `settings/security` | `VERIFIED_LOCAL` | سیاست حساب سازمانی و MFA پیش از Production |
| OTP | Contract/Provider، Rate Limit، OTP ثابت Local/Test و الزام HTTPS برای Provider بیرونی | `/access`، `HttpOtpProvider` و تست‌های Auth/Readiness | `PILOT_BLOCKED` | Provider واقعی، Credential، هزینه، SLA و آزمون ارسال در Staging |
| Consent | Consent نسخه‌دار و ثبت پذیرش با زمان و Subject | `/consent`، مدل‌ها و تست‌های Consent | `PILOT_BLOCKED` | متن حقوقی نهایی و سیاست نگهداری/حذف داده |
| مدل مکان | Venue، Zone، Hub، Touchpoint، پروفایل و فعال‌سازی مکان | `/admin/venues`، `VenueActivationService` | `DEMO_READY` | داده میدانی، مالک هر نقطه و تأیید نصب واقعی |
| کمپین | Registry، Builder، Blueprint، Activation و نقشه عملیات | `/admin/campaigns`، `/admin/campaign-builder` | `DEMO_READY` | قفل کمپین پایلوت و UAT نقش‌محور |
| QR | Registry، Binding، وضعیت، Scan Landing و کدهای عملیاتی | `/admin/qr-codes`، `/scan/{code}` | `VERIFIED_LOCAL` | قالب دامنه، چاپ، ابعاد و نصب میدانی |
| Attribution | ثبت Scan/Visit/Event متصل به QR، مکان، کمپین و کاربر/Session | `ScanEvent`، `Visit`، Event Log و تست‌های Feature | `VERIFIED_LOCAL` | Data Dictionary و Baseline پایلوت |
| داشبورد | خلاصه مدیریتی و پنل‌های عملیاتی نقش‌محور | `/dashboard` و Dashboard Serviceها | `DEMO_READY` | KPI مصوب، مقایسه Baseline و گزارش تصمیم‌ساز |
| مأموریت و گنج | Mission Template/Instance، Treasure، Progress و Completion | `/admin/missions`، `/demo/missions` | `DEMO_READY` | سناریوی نهایی، محتوا، Brand Safety و UAT میدانی |
| پاداش و Redemption | تعریف/تأیید پاداش، تخصیص موجودی و تأیید مصرف | Reward Modelها، پنل Partner و تست‌ها | `PILOT_BLOCKED` | بودجه، سقف، ضدتقلب، مسئول تأمین و سیاست مصرف |
| بازی اکوپارک | مسیر بازی آنلاین/حضوری، Party/Invitation و QR ایستگاه‌ها | `/games/ecopark-treasure` و تست‌های Game | `DEMO_READY` | انتخاب سناریوی پایلوت و آزمون روی موبایل واقعی |
| مشارکت‌کنندگان | عضویت کمپین، Onboarding، اتصال نقش و پنل شخصی | `/admin/campaign-participants`، `/participant/dashboard` | `DEMO_READY` | لیست اشخاص واقعی و RACI عملیات |
| فروشگاه/Partner | پروفایل، پیشنهاد، تبلیغ، پاداش و Redemption | `/partner/dashboard`، `/partner/ads` | `DEMO_READY` | Merchant Onboarding، قرارداد و UAT فروشگاه واقعی |
| اسپانسر | پروفایل، Proposal، Assignment، Activation و گزارش نقش | `/sponsor/dashboard`، `/admin/sponsors` | `DEMO_READY` | پکیج قیمت، قرارداد، Brand Approval و KPI واقعی |
| تبلیغات و نمایشگر | درخواست/تأیید تبلیغ، Placement، Schedule و Telemetry پایه | `/admin/ads`، `/admin/display-operations` | `PILOT_BLOCKED` | اتصال سخت‌افزار، فرمت محتوا و آزمون پخش میدانی |
| نقش و دسترسی | User Role، Access Scope، Role Operations و Authorization مسیرها | `/admin/users`، `/admin/access-scopes`، Middlewareها | `VERIFIED_LOCAL` | حساب‌های واقعی، Least Privilege و Review دسترسی |
| اقتصاد و کیف پول | حساب مالی، Ledger و نماهای مدیریتی | `/admin/finance-wallets`، Financial Services | `DEMO_READY` | مدل قراردادی، Revenue Share و قواعد Settlement |
| تجاری‌سازی | بسته‌های فروش، Sponsor/Merchant Narrative و Lead Inbox | `/admin/commercialization`، `/admin/marketing-leads` | `DEMO_READY` | قیمت‌گذاری، Sales Playbook و مالک پیگیری Lead |
| پشتیبانی | راهنمای نقش، صفحه Support و مسیرهای دسترسی مشترک | `/admin/support`، `/admin/users/guide` | `DEMO_READY` | SLA، Ticket/Complaint Workflow و Escalation واقعی |
| چرخه دمو | چک‌لیست 72 ساعت، روز اجرا، خروجی فروش و Stress Demo | `/admin/demo-cycle`، Readiness Serviceها | `DEMO_READY` | تبدیل چک‌لیست Demo به Runbook پایلوت مصوب |
| PostgreSQL | Server 18، Client Tooling، PHP Extension، Configuration و تست Fail-Closed موجود است | `.env.example`، `phpunit.pgsql.xml` و اسکریپت‌های PostgreSQL | `PILOT_BLOCKED` | ارائه Credential ایزوله و اجرای Migration/Test/Backup/Restore |
| استقرار | Release-based Deploy، Backup Gate، Rollback، `/up` و Preflight امنیت Session/OTP | `scripts/deploy-staging.sh` و `docs/staging/EXPLORIA_Stage_3_Staging_Readiness_v1.0.md` | `PRODUCTION_BLOCKED` | Provisioning سرور/دامنه و اجرای واقعی Deployment Drill |
| Logging/Monitoring | Application Log، Event/Audit پایه و Production Readiness Check | Logging Config و Audit Actionها | `PRODUCTION_BLOCKED` | Retention، Central Logging، Metrics و Alerting |
| Offline | پیام خطا/Retry و Fallback محدود؛ Sync کامل پیاده‌سازی نشده است | تصمیم OD-006 و Scope Lock | `OUT_OF_SCOPE` | Change Request پس از اثبات نیاز پایلوت |
| حقوقی و Data Governance | مدل فنی Consent/Audit موجود؛ سیاست‌های رسمی نهایی نیست | OD-002، OD-009 و CPL-17/18 | `PILOT_BLOCKED` | متن حقوقی، Data Ownership، Retention و Incident Policy |
| Native/Microservices | در معماری MVP وجود ندارد | Framework-20 و PCG-01 | `OUT_OF_SCOPE` | فقط با Change Request و تصمیم معماری آینده |
| Analytics/Settlement پیشرفته | اجزای پایه داده/مالی وجود دارد؛ موتور کامل نهایی نیست | Scope Lock و Backlog | `OUT_OF_SCOPE` | پس از گزارش و Decision Gate پایلوت |

## 5. Blockerهای رسمی پیش از پایلوت

1. Pilot Charter در `docs/pilot/EXPLORIA_EcoPark_Pilot_Charter_v0.1.md` تدوین شده است؛ قفل نهایی تاریخ، اشخاص، KPI و بودجه به تأیید Product Owner نیاز دارد.
2. اجرای Test Suite و Migration روی PostgreSQL واقعی.
3. Provider واقعی OTP و حذف کامل OTP ثابت از محیط غیرLocal.
4. متن حقوقی Consent و سیاست Data Governance.
5. QR Domain/Print/Installation Plan و داده میدانی تأییدشده.
6. Reward Budget، Anti-Fraud و Redemption Policy.
7. Runbook روز اجرا، Support، Incident و Low Connectivity Fallback.
8. Staging با HTTPS، Queue/Session پایدار، Backup/Restore و Monitoring.

## 6. نتیجه Stage 3

آمادگی Repository برای Staging با وضعیت `CONDITIONAL COMPLETE` ثبت شد. Session رمزنگاری‌شده، OTP فقط روی Endpoint معتبر HTTPS، Preflight استقرار، Headerهای Nginx و Runbook عملیاتی آماده‌اند. استقرار بیرونی انجام نشده است، زیرا سرور، دامنه/TLS، PostgreSQL Credential، OTP Credential و سامانه Monitoring واقعی ارائه نشده‌اند.

مرجع Evidence: `docs/staging/EXPLORIA_Stage_3_Staging_Readiness_v1.0.md`

## 7. مواردی که فعلاً نباید توسعه یابند

- Native Mobile App
- Microservices یا Repository جدا
- Offline Sync کامل
- Marketplace و Settlement خودکار کامل
- Analytics پیشرفته بدون KPI و Baseline مصوب
- توسعه هم‌زمان چند Venue پیش از نتیجه پایلوت اول

## 8. Gate به‌روزرسانی رجیستر

این رجیستر باید در نقاط زیر بازبینی شود:

1. پس از Merge هر Stage اصلی در `main`.
2. پس از تغییر Scope یا تصمیم Product Owner.
3. پیش از Staging، UAT و Pilot Go/No-Go.
4. پس از اجرای پایلوت و ثبت گزارش نهایی.
