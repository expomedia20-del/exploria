# EXPLORIA — تصمیم معماری عملیاتی Mail / Storage / Monitoring / Backup v1.0

## 1. کنترل تصمیم

| فیلد | مقدار |
|---|---|
| نوع | Architecture Decision Record — Provider-agnostic |
| تاریخ | 2026-08-16 |
| وضعیت | `DRAFT RECOMMENDED — STEP 3 APPROVAL PENDING` |
| دامنه | Staging مستقل و Production آینده |
| معماری Canonical | Laravel + React Monolith در یک Repository |
| اثر بر Production | تا عبور Approvalها و Drillهای Staging، `NO-GO` |

این ADR فناوری و الگوی عملیاتی را تعیین می‌کند، نه Vendor را. نام Provider، Region، Contract، DPA، SLA و Budget در `docs/pilot/EXPLORIA_Pre_Staging_Governance_Approval_Pack_v1.0.md` تصویب می‌شود. این تصمیم Microservice، Repository جدید، Kubernetes یا Redis را وارد معماری MVP نمی‌کند.

## 2. Evidence وضعیت فعلی

| حوزه | Evidence Repository | وضعیت واقعی |
|---|---|---|
| Mail | `config/mail.php` و `.env.staging.example` | Transportهای Laravel موجودند، اما Staging روی `MAIL_MAILER=log` است و Provider/E2E واقعی ندارد. |
| Auth Mail | تست‌های `PasswordResetTest` و `VerificationNotificationTest` | Notification در Test پوشش دارد؛ Delivery، SPF/DKIM/DMARC، Bounce و UAT Mailbox اثبات نشده‌اند. |
| Storage | `config/filesystems.php` | Local/Public و Config مربوط به S3 موجود است. |
| Upload | `app/Services/StandaloneAdvertisingService.php` | Asset تبلیغ روی Disk ثابت `public` ذخیره می‌شود؛ تغییر `FILESYSTEM_DISK` آن را به Object Storage منتقل نمی‌کند. |
| S3 Adapter | `composer.json` و `composer.lock` | `league/flysystem-aws-s3-v3` و `aws/aws-sdk-php` نصب/Lock نشده‌اند؛ Config به‌تنهایی قابلیت عملیاتی نیست. |
| Logging | `config/logging.php` | Single/Daily/Stderr/Syslog/Papertrail تعریف شده؛ Environment فعلی `stack,single` است و مقصد مرکزی/Alert ندارد. |
| Readiness | `app/Services/ProductionReadinessService.php` | فقط غیر-null بودن Log Channel را می‌سنجد؛ Delivery مرکزی، Retention و Alert را اثبات نمی‌کند. |
| Health | `/up` و `/health` + `scripts/deploy-staging.sh` | Health HTTP و Rollback مبتنی بر Failure موجود است؛ Uptime Monitor بیرونی وجود ندارد. |
| Queue/Scheduler | `deploy/systemd/*` | Worker پایدار Database Queue و systemd timer آماده‌اند؛ `php artisan schedule:list` فعلاً Taskی نشان نمی‌دهد. |
| Backup | `scripts/backup-postgresql.*` | `pg_dump` سفارشی، `pg_restore --list`، Permission محدود و Manifest مستقل SHA-256 وجود دارد؛ Dump هنوز محلی است و Upload/Encryption/Retention ندارد. |
| Restore | `scripts/test-postgresql-restore.*` | Restore فقط روی DB با پسوند امن، تطبیق Manifest/SHA-256 و کنترل جدول migrations Fail-Closed است؛ مانور محلی PostgreSQL 18 موفق و اجرای واقعی بیرونی ثبت‌نشده است. |

## 3. اصول قفل‌شده پیشنهادی

1. Staging و Production از نظر Server، Database، Credential، Bucket، Mail Domain/Stream و Alert Channel جدا باشند.
2. Secret فقط در Secret Store یا Environment امن قرار گیرد و وارد Git، Screenshot یا Ticket عمومی نشود.
3. سرویس بیرونی از طریق Adapterهای استاندارد Laravel یا Agent زیرساخت متصل شود؛ Domain Logic به Vendor وابسته نشود.
4. Local/Test می‌تواند از `log`/`array` Mail و Local Storage استفاده کند؛ Staging/Pilot/Production باید Fail-Closed و واقعی باشند.
5. Backup همان Storage برنامه نیست و Snapshot همان Restore Evidence نیست.
6. هیچ Provider بدون Owner سازمانی، MFA، Billing Alert، Export/Deletion Plan و دسترسی جانشین تصویب نشود.

## 4. تصمیم Mail — ADR-MAIL-01

### تصمیم پیشنهادی

- برای MVP از SMTP استاندارد Laravel روی TLS استفاده شود؛ این گزینه Vendor-agnostic است و Dependency جدید لازم ندارد.
- Staging از Subdomain/Stream مستقل مانند `staging-mail` و فقط UAT Allowlist استفاده کند.
- `MAIL_MAILER=smtp` باشد؛ `log`، `array` و Failover به `log` در Staging/Production مجاز نیست، چون Failure واقعی را پنهان می‌کند.
- Password Reset و Verification که مسیرهای امنیتی‌اند، تا اثبات Queue Mail به‌صورت مستقیم ارسال شوند؛ Notificationهای غیرحیاتی بعدی می‌توانند Queue شوند.
- Domain باید SPF، DKIM و DMARC معتبر داشته باشد؛ From Address، Return-Path، Bounce/Suppression و Rate/Cost Cap ثبت شوند.
- محتوای Mail نباید OTP، Token یا PII اضافی را در Log وارد کند.

### Evidence لازم برای Gate

1. Provider/Plan، Owner، Contract/SLA و Budget ثبت‌شده.
2. Credential بیرون Repository و MFA روی حساب Provider.
3. ارسال واقعی Reset/Verification به UAT Mailbox، بررسی Header و Delivery.
4. تست Bounce/Rejected Recipient و مشاهده Alert بدون نشت محتوا.
5. Screenshot یا Export Evidence بدون Token/PII.

### Gap اجرایی

- `.env.staging.example` هنوز Mail را `log` معرفی می‌کند.
- Production Readiness هنوز Mail Transport واقعی را Gate نمی‌کند.
- E2E و Runbook خطای Mail وجود ندارد.

این Gapها پس از Provider Approval و در Work Item مستقل اصلاح می‌شوند؛ این ADR به‌تنهایی مجوز تغییر Config نیست.

## 5. تصمیم Storage — ADR-STORAGE-01

### تصمیم پیشنهادی

- Assetهای Uploadشده در Staging/Pilot/Production روی Object Storage سازگار با S3 نگهداری شوند؛ Local/Public فقط برای Local/Test یا Cache غیرحیاتی باشد.
- Bucket برنامه و Bucket Backup در دو Scope یا حساب دسترسی مستقل باشند.
- دسترسی پیش‌فرض Private باشد. فقط Asset تأییدشده تبلیغاتی از مسیر URL کنترل‌شده/CDN عمومی شود.
- Encryption at Rest، TLS، Versioning/Lifecycle، محدودیت MIME/Size، Malware Review عملیاتی و حذف Asset هنگام حذف/رد محتوا الزامی است.
- Keyها به Least Privilege محدود شوند: برنامه نباید مجوز حذف Backup و Backup Agent نباید دسترسی Application Runtime داشته باشد.

### سازگاری و Gap

- Laravel Disk abstraction با معماری Canonical سازگار است.
- Service تبلیغات Disk `public` را Hardcode کرده و باید به Disk قابل تنظیم اختصاصی مانند `media` منتقل شود.
- Adapter S3 نصب نیست؛ افزودن آن Dependency کنترل‌شده و نیازمند Review/Composer Audit است.
- URLهای موجود Public Local باید در Migration محتوا/Regression بررسی شوند؛ تغییر بدون Drill می‌تواند رفتار نمایش تبلیغ را تغییر دهد.

### Evidence لازم

1. Upload/Read/Delete فایل تست روی Staging و اثبات Private-by-default.
2. دسترسی عمومی فقط به Asset تأییدشده و رد Object Key حدس‌زده‌شده.
3. Lifecycle و حذف واقعی، Audit و بازیابی نسخه در سناریوی خطا.
4. E2E صفحه تبلیغ/نمایشگر پس از تغییر Disk.

## 6. تصمیم Monitoring/Logging/Alerting — ADR-MON-01

### تصمیم پیشنهادی

- Laravel به `stderr` یا `syslog` ساختاریافته بنویسد و Agent سطح Host لاگ را به مقصد مرکزی ارسال کند. این روش بدون SDK Vendor در Domain/Application باقی می‌ماند.
- Nginx، PHP-FPM، systemd Queue/Scheduler و PostgreSQL نیز از همان Agent با Source Label جدا ارسال شوند.
- Uptime Monitor بیرونی `/up` را از خارج Server بررسی کند؛ `/health` فقط شاهد کمکی است.
- داشبورد حداقلی شامل HTTP 5xx/Latency، DB Connection/Storage، Queue Depth/Failed Jobs، Scheduler Last Success، Disk/CPU/RAM، Backup Age و TLS Expiry باشد.
- Alertها به کانال سازمانی با Owner و جانشین برسند؛ Alert صرفاً در Dashboard بدون Notification قابل قبول نیست.
- PII Redaction پیش از ارسال و Retention طبق Approval Pack اعمال شود.

### Severity و Alert پیشنهادی

| Signal | Threshold اولیه | Severity | اقدام |
|---|---|---|---|
| `/up` ناموفق از دو Probe متوالی | حدود 2 دقیقه | P1 | On-call + بررسی Rollback |
| شواهد PII/Unauthorized Access | هر مورد | P0 | Scoped Pause، Incident و Privacy Owner |
| PostgreSQL unavailable | هر مورد | P0/P1 | توقف مسیرهای Mutation و Incident |
| Failed Job جدید | هر مورد در مسیر حیاتی | P1 | Triage و Replay فقط با Idempotency Evidence |
| Queue age بالاتر از 5 دقیقه | 2 بازه متوالی | P1 | Capacity/Worker check |
| Scheduler بدون Success بیش از 3 دقیقه پس از تعریف Task | هر مورد | P1 | systemd timer/service check |
| Backup معتبر قدیمی‌تر از 24 ساعت | هر مورد | P0 قبل Deploy / P1 عادی | Deploy Block و Backup جدید |
| Disk بالاتر از 80% | پایدار 15 دقیقه | P2 | Cleanup/Capacity Plan |
| TLS کمتر از 14 روز تا انقضا | روزانه | P1 | تمدید قبل از Expiry |

Threshold نهایی پس از Load Test گام 8 تنظیم می‌شود؛ PII، DB Loss و Backup Gate مستقل از Load Test هستند.

### Gap اجرایی

- مقصد مرکزی، Agent، Dashboard، Synthetic Probe، Alert Channel و On-call واقعی وجود ندارد.
- Readiness فعلی Delivery/Alert را چک نمی‌کند.
- هیچ Task زمان‌بندی‌شده‌ای برای اثبات Scheduler تعریف نشده است؛ افزودن Job نمایشی بدون نیاز Domain مجاز نیست. برای E2E، یک Task عملیاتی واقعی مانند Prune مصوب Retention لازم است.

## 7. تصمیم Backup/Restore — ADR-BACKUP-01

### تصمیم پیشنهادی

- لایه اول: Snapshot/PITR مدیریت‌شده PostgreSQL در صورت پشتیبانی Provider.
- لایه دوم: Logical `pg_dump --format=custom` روزانه به مقصد Off-host مستقل.
- هر Backup دارای Timestamp UTC، Database/Environment، Commit/Release، Size، SHA-256، نتیجه `pg_restore --list` و شناسه Upload باشد.
- انتقال با TLS و ذخیره با Encryption at Rest انجام شود؛ در صورت نبود تضمین Provider، رمزگذاری Client-side مصوب اضافه شود.
- Retention پیشنهادی Approval Pack: Daily 30 روز، Weekly 12 هفته و Monthly 12 ماه.
- Deploy فقط با Backup معتبر کمتر از 24 ساعت ادامه یابد؛ این قاعده در `scripts/deploy-staging.sh` موجود است.
- Restore و Deploy باید در نبود Manifest، قالب نامعتبر یا عدم تطابق SHA-256 به‌صورت Fail-Closed متوقف شوند؛ این کنترل در اسکریپت‌های Windows/Linux موجود است.
- Restore Drill روی Database ایزوله حداقل ماهانه، قبل از Pilot و قبل از Release پرریسک اجرا شود.
- Restore موفق فقط «اجرای pg_restore» نیست: Migration state، شمارش رکوردهای کلیدی، Reward reconciliation، Login/Consent/QR Smoke و زمان RTO باید ثبت شود.

### اهداف پیشنهادی برای تصویب

| شاخص | Staging/Pilot اولیه | وضعیت |
|---|---:|---|
| RPO | حداکثر 24 ساعت | Proposed |
| RTO | حداکثر 4 ساعت | Proposed |
| Backup Success | 100% روزانه | Proposed |
| Restore Drill | ماهانه و پیش از Pilot/Release پرریسک | Proposed |
| Off-host Copies | حداقل یک نسخه مستقل از Application Host | Required |

### Gap اجرایی

- اسکریپت فعلی Dump را با Manifest مستقل SHA-256 محافظت می‌کند؛ رمزگذاری، Upload و Expiry هنوز وجود ندارند.
- Backup Timer/Service و Alert شکست وجود ندارد.
- Provider Snapshot/PITR، مقصد Off-host و Restore Credential مستقل انتخاب نشده‌اند.
- Restore خارجی و RPO/RTO واقعی اندازه‌گیری نشده است.

Evidence مانور محلی Migration/Rollback، Backup/Restore و Tamper Test در `docs/staging/EXPLORIA_Pre_Staging_Local_Rehearsal_2026-08-20.md` ثبت شده است؛ این Evidence جایگزین Off-host، Encryption، Provider PITR یا RPO/RTO خارجی نیست.

## 8. Queue / Cache / Session / Scheduler — ADR-RUNTIME-01

- برای Staging/Pilot اولیه، PostgreSQL Database Driver برای Queue، Cache و Session انتخاب حداقلی و سازگار است.
- Redis قبل از Load Test گام 8 یا نیاز اثبات‌شده وارد نمی‌شود؛ افزودن آن بدون نیاز، سطح عملیات را افزایش می‌دهد.
- Queue Worker با systemd موجود اجرا می‌شود؛ تعداد Worker از 1 شروع و پس از Load Test تنظیم می‌شود.
- Scheduler با systemd timer هر دقیقه اجرا می‌شود؛ `schedule:work` موازی مجاز نیست.
- Session، Queue و Cache باید Table/Migration، Backup Impact و Prune Policy مشخص داشته باشند.
- گام 7 باید Job واقعی، Failure/Retry، Failed Jobs، Restart Worker، Scheduler Last Success و Session persistence across deploy را E2E اثبات کند.

## 9. Scorecard انتخاب Provider

| معیار | وزن پیشنهادی |
|---|---:|
| Security، MFA، Encryption، Access/Audit | 25% |
| Availability، SLA و Incident History/Status Page | 20% |
| سازگاری فنی و Portability/Export | 15% |
| هزینه شفاف، سقف مصرف و Billing Alert | 15% |
| Data Region، Contract، DPA و Deletion | 10% |
| Support و مسیر Escalation | 10% |
| سادگی عملیات و کیفیت مستندات | 5% |

Provider با نقص Security/Ownership/Export حتی با امتیاز کل بالا رد می‌شود. انتخاب Vendor واقعی باید حداقل دو گزینه قابل خرید را با همین Scorecard مقایسه کند و مرجع Quote/Contract بیرون Repository ثبت شود.

## 10. ترتیب اجرای امن پس از Approval

1. تکمیل Approval Pack گام 3 و انتخاب Provider/Plan واقعی.
2. ایجاد Staging مستقل، Accountهای سازمانی، MFA و Secret Store.
3. اعمال Mail/Log/Storage/Backup Config فقط روی Branch مستقل با تست و Rollback.
4. در صورت تصویب Object Storage، افزودن Adapter و Refactor Disk با تست Regression.
5. راه‌اندازی Agent Monitoring و Alert Channel پیش از ورود داده واقعی.
6. اجرای Backup/Restore و Deploy/Rollback Drill.
7. اجرای E2E واقعی Mail/Storage/Queue/Scheduler و ثبت Evidence.
8. Load Test و تنظیم Worker/Threshold/Capacity.

## 11. Exit Gate تصمیم معماری

این ADR فقط وقتی `APPROVED` می‌شود که Product، Security و Operations آن را تأیید و Provider Register حداقل برای Staging تکمیل کنند. اجرای واقعی هر قابلیت در گام‌های 5 تا 8 Evidence مستقل می‌خواهد.

**نتیجه فعلی:** `DRAFT RECOMMENDED — TECHNICAL BASELINE DEFINED; PROVIDER, BUDGET AND FORMAL APPROVAL PENDING`
