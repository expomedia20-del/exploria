# EXPLORIA Feature Status Register v1.0

## 1. کنترل سند

| فیلد | مقدار |
|---|---|
| نوع سند | Working Status Snapshot - غیرجایگزین اسناد Canonical |
| تاریخ Snapshot | 2026-08-16 |
| Codebase | Laravel + React Monolith / Inertia-style |
| شاخه مبنا | `main` |
| Commit مبنا | `a9a9e45` — Merge PR #3 |
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
| Serviceهای Domain/Application | 36 |
| فایل‌های تست PHP | 62 |
| نتیجه Test Suite محلی | 374 Test / 4820 Assertion / 0 Failure |
| CI روی PR شماره 3 | 4 Check موفق: Quality، PHP 8.4، PHP 8.5 و PostgreSQL |
| تحلیل ایستا و کیفیت | PHPStan 0 Error؛ ESLint، TypeScript، Pint و Prettier موفق |
| Build تولیدی | Launch Assurance محلی 2026-08-20: موفق؛ حدود 50.88 ثانیه |
| Demo Readiness | 19 Pass / 0 Warning / 0 Fail |
| Production Readiness با PostgreSQL موقت و APP_ENV=local | 6 Pass / 8 Fail / `ready=false` - Fail-Closed مورد انتظار |
| Migration روی SQLite کاری | 1 مورد معوق: `2026_08_15_000001_add_reward_governance_controls`؛ عمداً اجرا نشد |
| مانور Local Pre-Staging PostgreSQL 18 | 36 Migration، Rollback/Re-apply، 374 Test / 4821 Assertion، Reconciliation، Backup/Restore، Tamper Test و Launch Assurance موفق |
| NPM Audit در 2026-08-16 | 0 Vulnerability |
| Composer Audit در 2026-08-16 | 0 Advisory |

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
| توقف/ازسرگیری عملیاتی | Scoped Pause در سطح Campaign، انسداد هماهنگ QR/Mission/Reward، Incident Reference، Recovery Evidence، Admin Resume Approval و Audit append-only | `CampaignOperationalControlService`، صفحه Campaign و `CampaignOperationalControlTest` | `VERIFIED_LOCAL` | تصویب RACI/Incident Policy و مانور Pause/Resume در Staging |
| QR | Registry، Binding، وضعیت، Scan Landing و کدهای عملیاتی | `/admin/qr-codes`، `/scan/{code}` | `VERIFIED_LOCAL` | قالب دامنه، چاپ، ابعاد و نصب میدانی |
| Attribution | ثبت Scan/Visit/Event متصل به QR، مکان، کمپین و کاربر/Session | `ScanEvent`، `Visit`، Event Log و تست‌های Feature | `VERIFIED_LOCAL` | Data Dictionary و Baseline پایلوت |
| داشبورد | خلاصه مدیریتی و پنل‌های عملیاتی نقش‌محور | `/dashboard` و Dashboard Serviceها | `DEMO_READY` | KPI مصوب، مقایسه Baseline و گزارش تصمیم‌ساز |
| مأموریت و گنج | Mission Template/Instance، Treasure، Progress و Completion | `/admin/missions`، `/demo/missions` | `DEMO_READY` | سناریوی نهایی، محتوا، Brand Safety و UAT میدانی |
| پاداش و Redemption | تعریف/تأیید پاداش، تخصیص موجودی، مصرف و کنترل مالک هزینه/موجودی/ظرفیت/انقضا/صدور | `RewardDefinition`، `MissionRewardRegistryService`، Migration و تست‌های Reward Governance | `PILOT_BLOCKED` | اجرای Migration و reconciliation در Staging؛ سپس بودجه، مسئول تأمین و سیاست مصرف واقعی |
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
| PostgreSQL | Server/Client و CI با PostgreSQL 18؛ مانور موقت محلی شامل Migration، Rollback/Re-apply، Reconciliation، Backup/Restore و Tamper Test موفق است | `.github/workflows/tests.yml`، `phpunit.pgsql.xml`، اسکریپت‌ها و `docs/staging/EXPLORIA_Pre_Staging_Local_Rehearsal_2026-08-20.md` | `PILOT_BLOCKED` | تکرار همین Drillها روی Staging واقعی و مستقل |
| استقرار | Release-based Deploy، Backup Gate با SHA-256 Fail-Closed، Rollback، `/up` و Preflight امنیت Session/OTP | `scripts/deploy-staging.sh` و `docs/staging/EXPLORIA_Stage_3_Staging_Readiness_v1.0.md` | `PRODUCTION_BLOCKED` | Provisioning سرور/دامنه، Backup رمزگذاری‌شده Off-host و اجرای واقعی Backup/Restore و Deployment Drill |
| Logging/Monitoring | Application Log، Event/Audit پایه و Production Readiness Check | Logging Config و Audit Actionها | `PRODUCTION_BLOCKED` | Retention، Central Logging، Metrics و Alerting |
| Offline | پیام خطا/Retry و Fallback محدود؛ Sync کامل پیاده‌سازی نشده است | تصمیم OD-006 و Scope Lock | `OUT_OF_SCOPE` | Change Request پس از اثبات نیاز پایلوت |
| حقوقی و Data Governance | مالک و سقف‌ها پذیرفته شده‌اند؛ آقای سیفی Legal Approver و شرکت مدیا پارس Operations Owner معرفی شده‌اند، اما امضای کتبی، Security Approver و Vendor Contract هنوز نهایی نیست | OD-002، OD-009، Approval Pack، Owner Decision Record و `EXPLORIA_Provider_Due_Diligence_2026-08-18.md` | `PILOT_BLOCKED` | Legal/Operations sign-off، نماینده/On-call مدیا پارس، Security Approver، پاسخ Vendor، متن حقوقی، دامنه رسمی، جانشین Incident و Provider دوم ایرانی |
| Native/Microservices | در معماری MVP وجود ندارد | Framework-20 و PCG-01 | `OUT_OF_SCOPE` | فقط با Change Request و تصمیم معماری آینده |
| Analytics/Settlement پیشرفته | اجزای پایه داده/مالی وجود دارد؛ موتور کامل نهایی نیست | Scope Lock و Backlog | `OUT_OF_SCOPE` | پس از گزارش و Decision Gate پایلوت |

## 5. Blockerهای رسمی پیش از پایلوت

1. Pilot Charter در `docs/pilot/EXPLORIA_EcoPark_Pilot_Charter_v0.1.md` تدوین شده است؛ قفل نهایی تاریخ، اشخاص، KPI، بودجه و RACI به تأیید Product Owner نیاز دارد.
2. Staging مستقل با HTTPS و PostgreSQL ایزوله؛ اجرای Migration، Reward reconciliation، Backup/Restore و Deployment/Rollback Drill در همان محیط.
3. Provider واقعی OTP و آزمون E2E واقعی OTP، Mail و Storage.
4. بسته تصمیم Privacy، Retention/Deletion، Incident، RACI، Budget و Provider آماده است؛ شش Approval رسمی، متن حقوقی و مقادیر واقعی هنوز Pending هستند.
5. QR Domain/Print/Installation Plan و داده میدانی تأییدشده.
6. Reward Budget، Anti-Fraud، مسئول تأمین و Redemption Policy.
7. تصویب RACI/Incident Policy و اجرای مانور Scoped Pause/Resume روی Staging؛ حداقل فنی، Incident Linkage و Audit Trail در Local پیاده‌سازی شده است.
8. Queue، Cache، Session و Scheduler پایدار و راستی‌آزمایی‌شده در Staging.
9. Central Monitoring، Logging، Alerting، On-call و Runbook Incident مصوب.
10. UAT رسمی روی Staging و صورت‌جلسه امضاشده Go/No-Go.

## 6. نتیجه Stage 3

آمادگی Repository برای Staging با وضعیت `CONDITIONAL COMPLETE` ثبت شد. Session رمزنگاری‌شده، OTP فقط روی Endpoint معتبر HTTPS، Preflight استقرار، Headerهای Nginx، Runbook عملیاتی، Gate حاکمیت پاداش و PostgreSQL CI آماده‌اند. استقرار بیرونی انجام نشده است، زیرا سرور، دامنه/TLS، PostgreSQL Credential، Providerهای واقعی و سامانه Monitoring/Backup واقعی ارائه نشده‌اند. این وضعیت مجوز Production یا Pilot نیست.

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
