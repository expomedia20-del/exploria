# EXPLORIA — گزارش Due-diligence ارائه‌دهندگان پیش از Staging

## 1. کنترل سند

| فیلد | مقدار |
|---|---|
| تاریخ | 2026-08-18 |
| آخرین تطبیق Repository | 2026-08-21 — `main@3648754` پس از Merge PR #5 |
| مجوز مالک | Due-diligence بدون خرید برای Liara؛ Sandbox/Quote بدون ارسال واقعی برای Kavenegar |
| محدودیت قطعی | داده، Log و Backup فقط ایران؛ پرداخت ریالی |
| وضعیت | `LEGAL/OPERATIONS ROLES ASSIGNED — WRITTEN VENDOR ANSWERS AND SIGN-OFFS PENDING` |

این گزارش مجوز خرید، ساخت Account با اطلاعات خصوصی، ورود Credential، ارسال OTP واقعی یا پذیرش قرارداد نیست. ادعاهای بازاریابی به‌تنهایی Evidence عملیاتی محسوب نمی‌شوند.

## 2. Verdict اجرایی

| Candidate | Verdict | علت |
|---|---|---|
| Liara VPS/DBaaS/Mail/Object Storage | `CONDITIONAL HOLD` | Fit فنی مناسب است، اما Privacy Policy امکان انتقال/دسترسی بین‌المللی را باقی می‌گذارد و چند کنترل عملیاتی عمومی اثبات نشده است. |
| Kavenegar OTP | `PENDING DUE-DILIGENCE` | HTTPS/OTP/Delivery Evidence مناسب است، ولی Evidence عمومی کافی برای محل و مدت نگهداری Mobile/OTP، DPA، MFA و سقف هزینه وجود ندارد. |
| IPPanel | `RESERVE COMPARATOR` | فقط در صورت رد Kavenegar یا پاسخ ناکافی بررسی عمیق می‌شود؛ Fallback خودکار مجاز نیست. |

## 3. Liara — Evidence و Gap

| معیار | نتیجه | Evidence | Gap/اقدام لازم | اهمیت |
|---|---|---|---|---|
| پرداخت و محل سرویس | `PASS WITH CONDITION` | Pricing ریالی و Endpointهای `api.iran.liara.ir`، `storage-service.iran.liara.ir` و `mail-service.iran.liara.ir` | Invoice/مالیات و Region دقیق هر سرویس در Quote قفل شود. | Medium |
| Iran-only بدون انتقال/دسترسی خارجی | `FAIL` | Privacy Policy می‌گوید اطلاعات در ایران ذخیره می‌شود، اما ممکن است به هر نقطه جهان منتقل یا از آنجا قابل دسترسی باشد. | تعهد مکتوب قراردادی برای عدم انتقال، عدم Replication و عدم دسترسی خارج ایران لازم است؛ در غیر این صورت Candidate رد می‌شود. | Blocker |
| IaaS سازگار | `PASS` | Ubuntu 24.04/22.04، SSH Key، IPv4/IPv6، Metrics و Operations History در API رسمی وجود دارد. | Firewall/Snapshot، Network isolation و Recovery Console باید در Sandbox اثبات شود. | High |
| PostgreSQL | `PASS WITH CONDITION` | PostgreSQL، Backup خودکار/دستی، Download Backup، Metrics، Events و Public Network Control مستند است. | Version/Extension، TLS enforcement، PITR window، RPO/RTO، Retention و Restore به Instance جدا مکتوب و Drill شود. | Blocker before real data |
| Mail | `PASS WITH CONDITION` | SMTP، SPF، DKIM، Return Path، Delivery Event و Dev/Live mode مستند است. | DMARC، Bounce/Suppression، Retention محتوا، TLS و Deliverability E2E اثبات شود. | High |
| Object Storage | `PASS WITH CONDITION` | S3 compatibility، Private bucket، Presigned URL و Key محدود به Bucket مستند است. | Encryption-at-rest، Versioning/Lifecycle، حذف قطعی و Egress/Export مکتوب شود. Laravel Adapter نیز هنوز نصب نیست. | High |
| SLA | `PENDING` | SLA فقط برای Account استارتاپ/سازمانی است؛ جبران حداکثر 30٪ و فقط Credit است و درخواست ظرف 7 روز Evidence می‌خواهد. | نوع Account، Target واقعی، Maintenance و Escalation قراردادی مشخص شود؛ SLA جایگزین معماری Recovery نیست. | High |
| Backup responsibility | `FAIL AS SOLE BACKUP` | Terms مسئولیت Backup و داده ازدست‌رفته را بر عهده مشتری می‌گذارد. | Backup رمزگذاری‌شده در Provider/Account مستقل ایرانی و Restore Drill الزامی است. | Blocker |
| Identity/MFA/RBAC/Audit | `NOT VERIFIED` | Evidence عمومی کافی پیدا نشد. | MFA اجباری، Recovery، چندکاربره/RBAC، API token rotation و Audit export باید اثبات شود. | Blocker before activation |
| Export/Delete/DPA/Subprocessors | `NOT VERIFIED` | Privacy/Terms عمومی پاسخ قراردادی کامل نمی‌دهد. | DPA، Subprocessor list، زمان حذف، Export و Incident notification مکتوب لازم است. | Blocker before real data |

منابع رسمی: `https://liara.ir/pricing`، `https://liara.ir/terms`، `https://liara.ir/privacy-policy/`، `https://liara.ir/sla`، `https://developers.liara.ir/pages/iaas`، `https://developers.liara.ir/pages/dbaas`، `https://developers.liara.ir/pages/mail` و `https://developers.liara.ir/pages/object-storage`.

## 4. Kavenegar — Evidence و Gap

| معیار | نتیجه | Evidence | Gap/اقدام لازم | اهمیت |
|---|---|---|---|---|
| OTP و API | `PASS WITH CONDITION` | HTTPS REST، API Key، VerifyLookup، Delivery Status و JSON Response مستند است. | Template، Sender، خط خدماتی و Eligibility شخص حقیقی در Sandbox/Quote تأیید شود. | High |
| وضعیت سرویس | `PASS WITH CONDITION` | Status Page عمومی و API monitor وجود دارد. | SLA، Incident notification و Escalation قراردادی هنوز لازم است. | High |
| بودجه | `PASS WITH CONDITION` | پلن پیشرفته/فوق‌پیشرفته برای OTP و اعتبار آزمایشی اعلام شده است. | هزینه هر OTP، مالیات، پیام ناموفق، Low-balance و Hard Cost Cap Quote شود. | High |
| محل/مدت نگهداری Mobile و OTP | `NOT VERIFIED` | Policy عمومی قابل اتکایی در منابع رسمی بررسی‌شده پیدا نشد. | Region، Retention، Masking، حذف، Access staff و Subprocessorها مکتوب شود. | Blocker before real send |
| DPA/Incident/Data Rights | `NOT VERIFIED` | Evidence عمومی کافی موجود نیست. | قرارداد پردازش، Incident notice، Export/Delete و پاسخ Data Subject مکتوب لازم است. | Blocker before real send |
| MFA/RBAC/API Key lifecycle | `NOT VERIFIED` | مستند API درباره Authentication است، نه امنیت Console. | MFA، Role، Masking، Rotation، Revocation و Audit log اثبات شود. | High |
| سازگاری Repository | `FAIL DIRECT CONFIG` | `app/Infrastructure/Otp/HttpOtpProvider.php:34` Payload عمومی `mobile/code/sender/expires_minutes` می‌فرستد؛ Kavenegar `receptor/token/template` و API Key در مسیر می‌خواهد. | Adapter اختصاصی زیر Contract موجود `OtpProvider` و تست Redaction/Timeout/401/403/402/5xx لازم است. | Blocker before E2E |

منابع رسمی: `https://kavenegar.com/rest.html`، `https://kavenegar.com/pricing.html` و `https://status.kavenegar.com/`.

## 5. Gapهای Repository مرتبط با Provider

| حوزه | Evidence Repository | وضعیت |
|---|---|---|
| OTP | `app/Infrastructure/Otp/HttpOtpProvider.php:34` و `.env.staging.example:56` | Adapter Kavenegar وجود ندارد؛ تغییر بعد از Vendor approval و CR فنی محدود لازم است. |
| Mail | `config/mail.php:40` و `.env.staging.example:41` | SMTP موجود است؛ الگوی Staging مقدار `MAIL_MAILER` را عمداً خالی می‌گذارد و Gate بدون Mailer واقعی Fail-Closed می‌شود. فعال‌سازی فقط پس از Provider/Credential/Domain/E2E مجاز است. |
| Storage | `config/filesystems.php:50` و `app/Services/StandaloneAdvertisingService.php:200` | Config S3 موجود است، ولی Adapter S3 نصب نیست و Service از Disk `public` استفاده می‌کند. |
| Backup | `scripts/backup-postgresql.sh` و `scripts/backup-postgresql.ps1` | Dump معتبر و Manifest مستقل SHA-256 ساخته می‌شود و Restore/Deploy در نبود یا عدم تطابق آن Fail-Closed است؛ Upload، Encryption و Lifecycle هنوز وجود ندارد. |
| Restore | `scripts/test-postgresql-restore.sh:24` و `.ps1:35` | Fail-closed naming وجود دارد؛ Drill خارجی و RTO/RPO Evidence ندارد. |
| Queue/Scheduler | `deploy/systemd/*` و `docs/staging/EXPLORIA_Operational_Architecture_Decision_v1.0.md:28` | Template موجود است؛ Scheduler Task واقعی و اجرای Staging اثبات نشده است. |

## 6. پرسش‌نامه مکتوب برای Liara

1. آیا همه داده‌های VPS، DBaaS، Mail، Object Storage، Log و Backup فقط در ایران ذخیره، Replicate، پردازش و پشتیبانی می‌شوند؟ بند Privacy Policy درباره انتقال/دسترسی جهانی دقیقاً چه سرویس‌هایی را شامل می‌شود؟
2. آیا تعهد Iran-only را در قرارداد/تیکت رسمی قابل استناد تأیید می‌کنید؟
3. PostgreSQL Version/Extension، TLS، PITR، Retention، RPO/RTO، Maintenance و Restore به Instance جدا چیست؟
4. MFA، RBAC، Audit Log، API Token rotation و Recovery حساب چگونه است؟
5. Snapshot VPS، Firewall، Network isolation و Export کامل چیست؟
6. Encryption-at-rest، Key ownership، Lifecycle/Versioning و حذف قطعی Object Storage چیست؟
7. Retention محتوای Mail/Log، Bounce/Suppression و Incident notification چیست؟
8. DPA، Subprocessorها، Portability، زمان حذف پس از Closure و SLA/Escalation سازمانی را ارائه کنید.

## 7. پرسش‌نامه مکتوب برای Kavenegar

1. Mobile، متن/Token OTP، Message ID، Delivery Event و Log در کدام Region و برای چه مدت نگهداری می‌شوند؟
2. آیا داده یا دسترسی پشتیبانی از ایران خارج می‌شود؟ DPA/Subprocessor list چیست؟
3. آیا Console از MFA، Role، Masking Mobile، Audit و API Key rotation پشتیبانی می‌کند؟
4. شرایط VerifyLookup، Template و خط خدماتی برای مالک شخص حقیقی چیست؟
5. هزینه دقیق هر OTP، مالیات، پیام ناموفق، Rate/Burst limit و Hard Cost Cap چیست؟
6. SLA، Status Callback، Incident notification و Support escalation چیست؟
7. Export/Delete و پاسخ به درخواست حذف/اصلاح داده چگونه اجرا می‌شود؟

## 8. Exit Gate

خرید یا ساخت Staging فقط وقتی مجاز است که:

- Liara تعهد Iran-only قابل استناد بدهد یا Candidate ایرانی جایگزین انتخاب شود.
- نماینده نام‌دار و مسئول شرکت مدیا پارس پاسخ‌های عملیاتی را بازبینی و Operations Acceptance را امضا کند.
- Security Approver نام‌دار، Access/Secret/Incident controls و Risk Acceptance را امضا کند.
- آقای سیفی به‌عنوان Legal Approver معرفی‌شده، قرارداد/DPA/Privacy را کتبی تأیید کند.
- Provider دوم مستقل ایرانی برای Backup/Uptime انتخاب شود.
- Quote داخل سقف مصوب باشد و Owner آن را برای خرید مشخص تأیید کند.
- Sandbox Kavenegar بدون PII واقعی، پس از Domain/Account security و پاسخ‌های Data Retention اجرا شود.

**نتیجه:** `NO PURCHASE / NO ACCOUNT PROVISIONING / NO REAL DATA — VENDOR WRITTEN ANSWERS REQUIRED`
