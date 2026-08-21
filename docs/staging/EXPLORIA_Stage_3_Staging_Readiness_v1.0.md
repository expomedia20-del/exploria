# EXPLORIA Stage 3 — Staging Readiness v1.0

## 1. کنترل سند

| فیلد | مقدار |
|---|---|
| نوع سند | Working Staging Readiness Record - غیرجایگزین اسناد Canonical |
| مرحله | Stage 3 — Staging & Security |
| تاریخ Snapshot | 2026-08-21 |
| وضعیت | `CONDITIONAL COMPLETE — REPOSITORY READY, EXTERNAL STAGING NOT PROVISIONED` |
| Scope | آماده‌سازی Repository و تمرین Fail-Closed؛ بدون استقرار عمومی |
| مجوز Production/Pilot | صادر نشده است |

این سند نتیجه اجرای G1 در Pilot Charter را ثبت می‌کند. آمادگی Repository با آماده‌بودن سرور واقعی یکسان نیست و هیچ مقدار Secret، Token، Password یا Credential در این سند یا Codebase ثبت نشده است.

## 2. هدف، مرجع و Acceptance Criteria

### هدف

آماده‌کردن مسیر امن و قابل تکرار برای استقرار Staging شامل PostgreSQL، HTTPS، Session امن، OTP واقعی، Queue/Cache پایدار، Backup/Restore، Deploy اتمیک و Health Check.

### Trace

- `OD-001`: Provider واقعی OTP پیش از Staging
- `OD-005`: Staging، Domain و SSL پیش از UAT
- `OD-008`: PostgreSQL و تفکیک Credential محیط‌ها
- `OD-009`: Logging، Retention و Alerting پیش از Pilot
- `CPL-17، CPL-18، CPL-19، CPL-21 و CPL-22`
- Pilot Charter: Gateهای `G0` تا `G3`
- MVP Delivery Control: `D-01، D-02، D-05، D-06 و D-07`

### معیار پذیرش Stage 3

| معیار | انتظار |
|---|---|
| تنظیم محیط | Staging فقط با APP_ENV مناسب، Debug خاموش و HTTPS عبور کند |
| دیتابیس | فقط PostgreSQL متصل و بدون Migration معوق پذیرفته شود |
| Session | Database/Redis، رمزنگاری‌شده، Secure، HttpOnly و SameSite امن باشد |
| OTP | Provider HTTP فقط با Endpoint معتبر HTTPS و Token بیرون Repository پذیرفته شود |
| Mail | Transport واقعی و غیرمحلی بدون fallback پنهان‌کننده Failure استفاده شود |
| Storage | Disk واقعی برنامه Write/Read/Delete شود و E2E بیرونی داشته باشد |
| Monitoring | Sink عملیاتی، مقصد مرکزی، Alert و On-call با Evidence اثبات شوند |
| Queue/Cache | Connection/Store ثبت‌شده، Backend پایدار، Probe/Evidence عملیاتی استفاده شود |
| Scheduler | Task واقعی مصوب و Last Success/Failure Alert با Evidence وجود داشته باشد |
| Operational Evidence | بسته تازه، مخصوص محیط و بیرون Repository برای هفت حوزه کامل باشد |
| Deploy | قبل از Build/Migration روی تنظیم ناقص Fail-Closed شود |
| Backup | Archive معتبر PostgreSQL قبل از Deploy موجود و Restore آن آزموده شود |
| وب | Redirect HTTPS، Security Header و No-Index برای Staging برقرار باشد |
| کیفیت | تست، تحلیل ایستا و Build بدون Failure عبور کند |

## 3. وضعیت ابزار و محیط محلی

| مورد | نتیجه | توضیح |
|---|---|---|
| PostgreSQL Server | Pass | PostgreSQL 18 نصب، سرویس فعال و Port 5432 در حال Listen است |
| PostgreSQL Client | Pass | `psql`، `pg_dump` و `pg_restore` موجودند |
| PHP PostgreSQL | Pass | افزونه‌های `pdo_pgsql` و `pgsql` فعال‌اند |
| OpenSSL در PHP | Pass | افزونه PHP فعال است |
| Docker | Not Required | برای این مسیر، PostgreSQL محلی نصب‌شده کافی است |
| Credential تست ایزوله | Missing | متغیرهای `EXPLORIA_PG_*` تنظیم نشده‌اند |
| اتصال PostgreSQL | Fail-Closed | اتصال بدون Password رد شد؛ Credential حدس زده یا در Repository ایجاد نشد |
| محیط فعلی برنامه | Local | SQLite، Debug فعال، HTTP و OTP محلی؛ برای توسعه مجاز ولی برای Staging نامعتبر |

## 4. تغییرات امنیتی Stage 3

### 4.1 OTP

- `HttpOtpProvider` اکنون Endpoint فاقد HTTPS یا URL نامعتبر را پیش از ارسال Request رد می‌کند.
- Production Readiness وضعیت Endpoint را به `missing`، `insecure-or-invalid` یا `securely-configured` تفکیک می‌کند.
- Token همچنان فقط از Environment دریافت می‌شود و در گزارش نمایش داده نمی‌شود.

### 4.2 Session

- الگوی Staging از `SESSION_ENCRYPT=true` استفاده می‌کند.
- `SESSION_SECURE_COOKIE=true` و `SESSION_HTTP_ONLY=true` اجباری‌اند.
- `SESSION_SAME_SITE` فقط `lax` یا `strict` پذیرفته می‌شود.
- Production Readiness همه این کنترل‌ها را در یک Gate بررسی می‌کند.

### 4.3 Deploy Preflight

اسکریپت Deploy پیش از Archive، Build یا Migration موارد زیر را کنترل می‌کند:

- PostgreSQL
- Queue و Cache پایدار
- Session Driver و چهار کنترل امنیت Session
- OTP Driver واقعی، Endpoint HTTPS و Token تنظیم‌شده
- APP_KEY، Environment، Debug، URL و Backup معتبر

هیچ مقدار حساس در خطا یا Log اسکریپت چاپ نمی‌شود.

### 4.4 Nginx

نمونه Staging اکنون علاوه بر Redirect به HTTPS و No-Index، موارد زیر را برای پاسخ‌های Static و Dynamic اعمال می‌کند:

- مخفی‌سازی Server Token
- HSTS
- Permissions Policy برای Camera، Microphone و Geolocation
- X-Content-Type-Options، X-Frame-Options و Referrer Policy

### 4.5 حاکمیت پاداش و PostgreSQL CI

- صدور پاداش فعال بدون مالک هزینه، مدل موجودی، ظرفیت معتبر، انقضا و محدودیت صدور در Gate آمادگی رد می‌شود.
- CI با PostgreSQL 18 آخرین Migration را اجرا، یک مرحله Rollback و سپس Migration مجدد را راستی‌آزمایی می‌کند.
- این شواهد فقط سازگاری Repository و Migration را اثبات می‌کنند؛ جایگزین Migration، reconciliation و Drill روی Staging مستقل نیستند.

### 4.6 Scoped Pause/Resume

- Campaign فعال با Reason و Incident Reference توسط Admin/Operator در Scope همان Campaign متوقف می‌شود.
- وضعیت Campaign ورودی QR، ادامه Mission و صدور Reward جدید همان Campaign را Fail-Closed می‌کند، بدون آنکه وضعیت مستقل اجزا به‌صورت انبوه بازنویسی شود.
- Resume فقط برای Admin و پس از ثبت Corrective Action، Recovery Evidence، Approval Note و تأیید صریح مجاز است.
- رویدادهای `audit.campaign_paused` و `audit.campaign_resumed` Actor، Timestamp و Evidence را append-only نگه می‌دارند.
- این قابلیت در Local تست شده است؛ مانور Incident/Pause/Recovery/Resume در Staging و تصویب RACI/Incident Policy همچنان Pending است.

### 4.7 Admission Gate عملیاتی

- نام دلخواه Queue/Cache دیگر کافی نیست؛ Connection/Store باید در Config ثبت و Backend مربوط قابل استفاده باشد.
- Cache و Disk واقعی مورد استفاده Upload با Probe موقت Write/Read/Delete سنجیده و پس از Probe پاک می‌شوند.
- Mailهای `log`/`array` و Failover دارای fallback محلی رد می‌شوند.
- Monitoring با `single`/`daily` محلی PASS نمی‌شود و به Sink عملیاتی همراه Evidence نیاز دارد.
- Session علاوه بر Cookie امن، Backend در دسترس و Evidence ماندگاری بین Deployها می‌خواهد.
- Scheduler بدون Task واقعی حتی با systemd timer موجود FAIL می‌شود؛ Job نمایشی به Repository افزوده نشده است.
- `report(..., false)` دیگر کنترل‌های اجرا‌نشده را PASS نمی‌کند و با وضعیت `not-checked` Fail-Closed می‌ماند.
- Evidence باید JSON تازه، مخصوص همان Environment، دارای Reference غیرحساس برای هر هفت حوزه و در مسیر Absolute بیرون Repository باشد.
- هیچ Redis، S3 Adapter، SDK Provider یا Credential در این Hardening افزوده نشده است.

## 5. معماری استقرار آماده‌شده

```text
Internet
   │
   ▼
Nginx :443 + TLS + Security Headers
   │
   ▼
Laravel Release (current symlink)
   ├── shared/.env                 خارج از Git
   ├── shared/storage              پایدار بین Releaseها
   ├── PostgreSQL                  Credential مستقل Staging
   ├── Database Queue Worker       systemd
   └── Scheduler                   systemd timer
```

Deploy به‌صورت Release-based انجام می‌شود، Revision و زمان استقرار ثبت می‌گردد، Symlink اتمیک تغییر می‌کند و در Failure، Release قبلی بازیابی می‌شود.

## 6. Runbook اجرای واقعی Staging

### Gate S3-01 — تصمیم‌های بیرونی

پیش از هر استقرار باید موارد زیر از مالک محصول/زیرساخت دریافت شود:

1. نام دامنه Staging و مالک DNS.
2. سرور Linux و حساب اختصاصی `exploria` با دسترسی محدود.
3. Credential مستقل PostgreSQL Staging و دو Database ایزوله Test/Restore-Test.
4. OTP Provider، Endpoint HTTPS، Token، Sender، SLA و سقف هزینه.
5. مقصد Log متمرکز، Retention و Alert Channel.
6. مسیر Backup رمزگذاری‌شده و سیاست نگهداری.

بسته ثبت تصمیم و Approval این Gate در `docs/pilot/EXPLORIA_Pre_Staging_Governance_Approval_Pack_v1.0.md` آماده شده است. تا تکمیل Approver، تاریخ، مرجع مصوبه، بودجه و Provider واقعی، Gate `S3-01` همچنان Fail باقی می‌ماند.

معماری Provider-agnostic پیشنهادی Mail/Storage/Monitoring/Backup در `docs/staging/EXPLORIA_Operational_Architecture_Decision_v1.0.md` ثبت شده است؛ این ADR تا عبور گام 3 و انتخاب Provider واقعی Draft است.

Candidateهای Provider، منابع رسمی و Gapهای Compatibility در `docs/staging/EXPLORIA_Provider_Shortlist_2026-08-16.md` ثبت شده‌اند؛ Shortlist مجوز خرید یا Approval نیست.

### Gate S3-02 — فایل Environment بیرون Repository

فایل `/var/www/exploria-staging/shared/.env` از `.env.staging.example` ساخته می‌شود. موارد زیر باید با Secret Manager یا کانال امن مقداردهی شوند:

```text
APP_KEY
DB_DATABASE
DB_USERNAME
DB_PASSWORD
OTP_HTTP_ENDPOINT
OTP_HTTP_TOKEN
MAIL_MAILER
MAIL_FROM_ADDRESS
LOG_CHANNEL
LOG_STACK
EXPLORIA_OPERATIONAL_EVIDENCE_PATH
```

فایل واقعی `.env` نباید Commit، ایمیل یا در Ticket عمومی پیوست شود.

### Gate S3-03 — PostgreSQL

نام Database تست خودکار باید به `_test` یا `_testing` ختم شود و نام Restore Drill باید به `_restore_test` یا `-restore-test` ختم شود. این قاعده مانع اجرای مخرب اسکریپت‌ها روی Database غیرایزوله می‌شود.

ترتیب Verification:

```powershell
$env:EXPLORIA_PG_BIN = 'C:\Program Files\PostgreSQL\18\bin'
$env:EXPLORIA_PG_DATABASE = '<isolated_testing_database>'
$env:EXPLORIA_PG_USERNAME = '<staging_test_user>'
$env:EXPLORIA_PG_PASSWORD = '<provided_outside_repository>'
./scripts/test-postgresql.ps1
```

سپس Backup از Database Staging و Restore روی Database ایزوله اجرا می‌شود:

```powershell
./scripts/backup-postgresql.ps1 -OutputDirectory '<secure_backup_directory>'
$env:EXPLORIA_PG_RESTORE_DATABASE = '<isolated_restore_test_database>'
./scripts/test-postgresql-restore.ps1 -BackupPath '<verified_backup_path>'
```

اسکریپت Backup در کنار Archive فایل `<backup_path>.sha256` می‌سازد. Restore و Deploy در نبود Manifest، قالب نامعتبر، نام فایل متفاوت یا عدم تطابق Hash متوقف می‌شوند. این کنترل یکپارچگی جایگزین رمزنگاری، مقصد Off-host یا Restore Drill واقعی نیست.

### Gate S3-04 — استقرار Linux

متغیرهای Deploy فقط در Session امن اپراتور تنظیم می‌شوند:

```text
EXPLORIA_DEPLOY_ROOT=/var/www/exploria-staging
EXPLORIA_DEPLOY_REF=<reviewed_commit_or_tag>
EXPLORIA_HEALTH_URL=https://<staging-domain>/up
EXPLORIA_VERIFIED_BACKUP_PATH=<verified_dump_path>
```

سپس `scripts/deploy-staging.sh` با حساب اختصاصی برنامه اجرا می‌شود. اجرای آن با `root` عمداً رد می‌شود.

### Gate S3-05 — پس از Deploy

- `/up` باید روی HTTPS پاسخ موفق دهد.
- `exploria:production-readiness --json` باید همه Checkها را بدون Fail عبور دهد؛ تعداد ثابت Check معیار نیست. پاداش فعال فاقد حاکمیت، Campaign دارای توقف عملیاتی، Evidence ناقص یا هر Runtime اثبات‌نشده Gate را متوقف می‌کند.
- `exploria:demo-readiness --json` باید بدون Fail باشد.
- Queue Worker و Scheduler باید Active باشند.
- Headerهای HSTS، No-Index و Permissions Policy باید روی پاسخ واقعی دیده شوند.
- یک OTP واقعی فقط با شماره مجاز UAT و سقف هزینه کنترل‌شده ارسال شود.
- Backup جدید و Restore Drill ثبت شود.

## 7. Gateهای خارجی باقی‌مانده

| ID | مانع | مالک تصمیم/تأمین | وضعیت |
|---|---|---|---|
| EXT-S3-01 | سرور Linux و دسترسی SSH محدود | Infrastructure Owner | Pending |
| EXT-S3-02 | Domain، DNS و گواهی TLS | Infrastructure/Venue | Pending |
| EXT-S3-03 | Credential ایزوله PostgreSQL | DBA/Infrastructure | Pending |
| EXT-S3-04 | OTP Provider و Credential | Product/Commercial/Tech | Pending |
| EXT-S3-05 | Central Logging، Retention و Alerting | Tech/Legal | Pending |
| EXT-S3-06 | محل Backup و Restore Drill | Infrastructure/DBA | Pending |
| EXT-S3-07 | UAT Accounts و شماره‌های مجاز | Product/QA | Pending |
| EXT-S3-08 | Mail و Storage Provider واقعی و آزمون E2E | Product/Infrastructure | Pending |
| EXT-S3-09 | Queue Worker، Scheduler، Cache و Session عملیاتی | Infrastructure/Operations | Pending |
| EXT-S3-10 | شش تصویب رسمی Privacy/Retention/Incident/RACI/Budget/Provider | Product/Legal/Security/Operations/Finance | Pack Prepared / Approvals Pending |

## 8. نتیجه Stage 3

Repository برای تحویل به اپراتور Staging سخت‌گیری شده و مسیر Deploy/Backup/Restore به‌صورت Fail-Closed آماده است. چون هیچ سرور، دامنه، OTP Credential یا Database Credential واقعی در اختیار این Stage قرار نگرفت، استقرار بیرونی انجام نشده و ادعای `STAGING LIVE` یا `PILOT READY` مجاز نیست.

**نتیجه:** `CONDITIONAL COMPLETE — EXTERNAL PROVISIONING REQUIRED`

## 9. فایل‌های اصلی Evidence

- `.env.staging.example`
- `.github/workflows/tests.yml`
- `app/Infrastructure/Otp/HttpOtpProvider.php`
- `app/Services/ProductionReadinessService.php`
- `app/Services/OperationalEvidenceService.php`
- `config/production_readiness.php`
- `app/Services/MissionRewardRegistryService.php`
- `app/Services/CampaignOperationalControlService.php`
- `database/migrations/2026_08_15_000001_add_reward_governance_controls.php`
- `scripts/deploy-staging.sh`
- `deploy/nginx/exploria-staging.conf.example`
- `composer.lock`
- `package-lock.json`
- `tests/Feature/Auth/HttpOtpProviderTest.php`
- `tests/Feature/Infrastructure/ProductionReadinessTest.php`
- `tests/Feature/Infrastructure/EnvironmentBaselineTest.php`
- `tests/Feature/Governance/RewardGovernanceTest.php`
- `tests/Feature/Infrastructure/RewardGovernanceReadinessTest.php`
- `tests/Feature/Campaign/CampaignOperationalControlTest.php`
- `docs/staging/EXPLORIA_Stage_3_Staging_Readiness_v1.0.md`
- `docs/status/EXPLORIA_Feature_Status_Register_v1.0.md`

## 10. Verification Record

| بررسی | نتیجه |
|---|---|
| Test Suite محلی پس از Hardening 2026-08-21 | 381 Test / 4881 Assertion / 0 Failure |
| تست هدفمند Scoped Pause/Resume | 4 Test / 70 Assertion / Pass |
| مانور فنی Incident + Readiness | 12 Test / 94 Assertion / Pass؛ Tabletop انسانی و External Staging همچنان Pending |
| GitHub CI روی PR شماره 3 | 4 Check موفق: Quality، PHP 8.4، PHP 8.5 و PostgreSQL |
| PostgreSQL CI | Migration Fresh، Rollback آخرین Migration، Migration مجدد و Test Suite موفق |
| ESLint / Prettier / TypeScript | Pass |
| Pint / PHPStan | Pass / 0 Error |
| Composer / NPM Audit | 0 Advisory / 0 Vulnerability |
| Production Build | Pass در Launch Assurance محلی؛ حدود 50.88 ثانیه |
| Migration روی SQLite کاری | 1 مورد معوق: `2026_08_15_000001_add_reward_governance_controls`؛ عمداً تغییر داده نشد |
| Demo Readiness | 19 Pass / 0 Warning / 0 Fail |
| Production Readiness در محیط کاری Local | 1 Pass / 17 Fail / `ready=false`؛ Mail/Monitoring/Scheduler/Evidence نیز Fail-Closed شدند |
| Headerهای HTTP محلی | Nosniff، SameOrigin، Referrer Policy و Permissions Policy فعال |
| Syntax اسکریپت Deploy | `bash -n` موفق |
| Backup Integrity Guard | تولید Manifest SHA-256 و کنترل Fail-Closed در Restore/Deploy؛ تست استاتیک و Syntax محلی موفق |
| PostgreSQL Tooling | Server/Client/PHP Extension آماده |
| PostgreSQL Migration/Test/Backup/Restore محلی | 36 Migration، Rollback/Re-apply، 374 Test / 4821 Assertion، Backup/Restore و Tamper Test موفق؛ Evidence: `EXPLORIA_Pre_Staging_Local_Rehearsal_2026-08-20.md` |
| PostgreSQL Migration/Test/Backup/Restore روی External Staging | Pending — زیرساخت و Credential واقعی ارائه نشده است |
| Incident Tabletop محلی | Technical Control Drill موفق؛ Evidence: `EXPLORIA_Incident_Tabletop_Local_Rehearsal_2026-08-20.md` |
| Secret Scan دستی فایل‌های تغییرکرده | هیچ Secret واقعی افزوده نشد |
| تغییر معماری/Role | انجام نشد |
| Schema/Dependency Hardening | Migration حاکمیت پاداش و Lockfileهای PR شماره 3 حفظ شد؛ در Hardening جاری Schema یا Dependency جدید افزوده نشد |

Full CI، Build و مانور Local Pre-Staging روی PostgreSQL 18 موقت اجرا شدند. Migration، Rollback/Re-apply، Reward reconciliation، Backup/Restore، Tamper Test و Launch Assurance محلی موفق‌اند؛ اما Deployment/Rollback، Providerهای واقعی و همین Drillها روی Staging مستقل هنوز انجام نشده‌اند و تا ارائه زیرساخت و Credential ایزوله، مانع `STAGING LIVE` باقی می‌مانند.
