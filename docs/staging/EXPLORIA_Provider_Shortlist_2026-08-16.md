# EXPLORIA — Provider Shortlist پیش از Staging

## 1. کنترل سند

| فیلد | مقدار |
|---|---|
| تاریخ بررسی | 2026-08-16؛ بازبینی قیمت/تصمیم مالک 2026-08-17 |
| آخرین تطبیق Repository | 2026-08-21 — `main@2e7fa6c` پس از Merge PR #9؛ قیمت/شرایط Vendor بازبینی مجدد نشده‌اند |
| نوع | Decision Support — نه Approval و نه مجوز خرید |
| وضعیت | `CANDIDATES ACCEPTED FOR DUE-DILIGENCE — VENDOR APPROVAL/PURCHASE PENDING` |
| تصمیم مالک | محل داده و Backup فقط ایران؛ بودجه و پرداخت فعلاً ریالی؛ Provider خارجی برای داده/Log/Backup واجد شرایط نیست. |

قیمت، SLA و شرایط قرارداد ممکن است تغییر کنند. فقط صفحه رسمی Product/Documentation در تاریخ بالا بررسی شده است؛ Quote، DPA، Data Region، مالیات، Egress، شرایط حذف و امکان خرید باید پیش از Approval دوباره کنترل شوند.

این Shortlist Requirement ایجاد نمی‌کند. به‌ویژه Object Storage/S3 فقط در صورت تصمیم `PRE-DEC-11` و Adapter فقط پس از مجوز Dependency قابل انتخاب است. رکورد تصمیم مستقل در `docs/staging/EXPLORIA_Pre_Server_Provider_Acquisition_Decision_Pack_v1.0.md` نگهداری می‌شود.

Product Owner در 2026-08-18 انجام Due-diligence Liara و Sandbox/Quote کاوه‌نگار را پذیرفت. نتیجه تفصیلی در `docs/staging/EXPLORIA_Provider_Due_Diligence_2026-08-18.md` نشان می‌دهد Liara به علت بند انتقال/دسترسی بین‌المللی Privacy Policy فعلاً `CONDITIONAL HOLD` است.

## 2. نتیجه پیشنهادی

### مسیر A — Iran-first، پیشنهاد اول برای بررسی

| Capability | Candidate | دلیل سازگاری | شرط قبل از Approval |
|---|---|---|---|
| Staging Compute | Liara Cloud VPS/IaaS | Linux VPS، IPv4 و پرداخت ساعتی/ریالی؛ با Runbook فعلی SSH و release-based deploy نزدیک‌تر از PaaS است. | اثبات Ubuntu/PHP/PostgreSQL client compatibility، SSH محدود، Snapshot، Firewall، SLA و Quote |
| PostgreSQL | Liara DBaaS PostgreSQL | PostgreSQL مدیریت‌شده، Backup/Restore و Metrics در API رسمی اعلام شده است. | Version دقیق، TLS، Private Network، PITR/Retention، Download Backup و Restore Drill |
| Application Media | Liara Object Storage | API سازگار با S3 و لینک موقت/SSL؛ با Laravel Flysystem قابل تطبیق است. | Private-by-default، Lifecycle/Versioning، Access Policy و نصب Adapter مصوب |
| Transactional Mail | Liara Mail SMTP | سرویس Transactional Email و SMTP رسمی؛ بدون Driver اختصاصی Laravel قابل استفاده است. | SPF/DKIM/DMARC، Bounce/Suppression، Rate Limit، SLA و UAT Delivery |
| OTP Primary Candidate | Kavenegar VerifyLookup | REST روی HTTPS، متد Verification Template، Delivery Status و Status Page رسمی دارد. | Quote/SLA، Hide/PII policy، Template approval، Cost Cap و Adapter اختصاصی |
| OTP Comparison Candidate | IPPanel Pattern API | API Token، Pattern Send، گزارش وضعیت و شماره ایران در مستندات رسمی دیده می‌شود. | Quote/SLA، Data/PII policy، Template approval، Cost Cap و Adapter اختصاصی |
| Uptime/Alert | Probe/Monitoring روی Provider و Account مستقل داخل ایران | از دسترس‌پذیری خارج Failure Domain برنامه، Heartbeat، Alert و On-call پشتیبانی کند. | Candidate دوم ایرانی، Data Retention، Alert واقعی و Drill |
| Off-host Backup | Provider/Account دوم داخل ایران و مستقل از Liara | Common-mode failure را کاهش دهد و Export/Restore مستقل ممکن باشد. | Encryption، Lifecycle، Hash Manifest، Credential مستقل و Restore Drill |

تمرکز Compute، DB، Mail و Media در یک Provider عملیات Pilot را ساده می‌کند، اما Common-mode Risk ایجاد می‌کند. بنابراین Uptime Probe و حداقل یک Backup قابل Restore باید بیرون همان حساب/Failure Domain باشد.

### مسیر B — International، تحت تصمیم فعلی غیرمجاز

| Capability | Candidate | Evidence رسمی | مانع فعلی |
|---|---|---|---|
| Compute | DigitalOcean Droplet | VM با Firewall/Monitoring و SLA اعلام‌شده | پرداخت ارزی، دسترسی، Data Region و Contract نامشخص |
| PostgreSQL | DigitalOcean Managed PostgreSQL | Managed PostgreSQL با Backup و Planهای مشخص | همان محدودیت‌های پرداخت/داده |
| Media | DigitalOcean Spaces | S3-compatible و CDN | Adapter لازم و Data Region خارجی |
| Backup مستقل | Backblaze B2 | S3-compatible، Encryption و EU/US regions | پرداخت/دسترسی/Legal |
| Monitoring | Better Stack | Uptime، Heartbeat، Incident/On-call و Log Retention | پرداخت/دسترسی/Legal |
| OTP ایران | Kavenegar یا IPPanel | پشتیبانی شماره ایران | همچنان Adapter و قرارداد داخلی لازم است |

این مسیر با تصمیم فعلی مالک مبنی بر Iran-only و Rial-only سازگار نیست و برای داده، Log و Backup انتخاب نمی‌شود. بازگشایی آن فقط با تصمیم مالک جدید و Legal/Security Review ممکن است.

## 2.1 برآورد رسمی اولیه، نه Quote خرید

صفحه رسمی قیمت Liara در بازبینی 2026-08-17، نمونه‌های زیر را اعلام می‌کرد: VPS دو گیگابایت `589,000 تومان/month` به‌علاوه IPv4 برابر `149,000 تومان/month`، DBaaS PostgreSQL دو گیگابایت `589,000 تومان/month`، Email پایه پولی `589,000 تومان/month` و Object Storage بیست گیگابایت `209,000 تومان/month`. قیمت و مالیات باید درست پیش از خرید دوباره کنترل شود.

صفحه رسمی Kavenegar، پلن پیشرفته را `600,000 تومان/year` و پلن فوق‌پیشرفته را `1,200,000 تومان/year` اعلام می‌کند و OTP را مناسب این دو پلن می‌داند؛ هزینه مصرف پیامک در Quote جداگانه لازم است.

## 3. Evidence رسمی بررسی‌شده

| منبع رسمی | Claim قابل استفاده | URL |
|---|---|---|
| Liara Cloud VPS | VPS ایران، منابع/IPv4 و پرداخت ساعتی | `https://liara.ir/products/cloud-server` |
| Liara DBaaS | PostgreSQL، Backup/Restore، Metrics و کنترل Public Network | `https://developers.liara.ir/pages/dbaas` |
| Liara Object Storage | S3-compatible، SSL و لینک موقت | `https://liara.ir/products/object-storage` |
| Liara Mail | Transactional Email و SMTP | `https://liara.ir/products/email` |
| Liara Terms/Privacy/SLA | مسئولیت Backup با مشتری، امکان انتقال/دسترسی بین‌المللی و سقف جبران SLA | `https://liara.ir/terms`، `https://liara.ir/privacy-policy/`، `https://liara.ir/sla` |
| Liara Platform API | Environment، Metrics، Domain/SSL و Disk | `https://developers.liara.ir/pages/paas` |
| Kavenegar REST | HTTPS REST، Verify/Lookup، Delivery Status و API Key | `https://kavenegar.com/rest.html` |
| Kavenegar Status | Public Status Page | `https://status.kavenegar.com/` |
| IPPanel API | Pattern Send، API Token و Delivery Report | `https://apidoc.ippanel.com/` |
| DigitalOcean Managed DB | Managed PostgreSQL و قیمت Native Currency | `https://www.digitalocean.com/pricing/managed-databases` |
| DigitalOcean Spaces | S3-compatible Storage و CDN | `https://www.digitalocean.com/products/spaces` |
| Backblaze B2 | S3-compatible Storage برای Backup/Recovery | `https://www.backblaze.com/cloud-storage/pricing` |
| Better Stack | Uptime، Heartbeat، Incident/On-call و Logs | `https://betterstack.com/pricing` |

صفحات بازاریابی فقط برای تشکیل Shortlist استفاده شده‌اند؛ Claimهای Security/SLA/Backup باید با قرارداد، مستند فنی و Drill واقعی اثبات شوند.

## 4. Compatibility Gapهای Repository

### OTP

`app/Infrastructure/Otp/HttpOtpProvider.php` یک Endpoint عمومی با Bearer Token و JSON زیر می‌فرستد:

```json
{
  "mobile": "09...",
  "code": "......",
  "sender": "EXPLORIA",
  "expires_minutes": 5
}
```

Kavenegar، API Key را در URL و پارامترهای `receptor/token/template` می‌خواهد. IPPanel نیز Pattern Code، Recipient array و `params` متفاوت دارد. بنابراین:

- قرار دادن مستقیم URL هیچ‌یک در `OTP_HTTP_ENDPOINT` مجاز نیست.
- پس از انتخاب Provider، یک Adapter اختصاصی جدید زیر `App\Infrastructure\Otp` با همان `OtpProvider` Contract لازم است.
- API Key نباید در URL لاگ‌شونده، Exception، Test Snapshot یا Repository ظاهر شود.
- Test باید Payload، Timeout، 401/403، اعتبار ناکافی، Provider 5xx، Delivery/Accepted semantics و Redaction را پوشش دهد.
- Fallback خودکار بین Providerها پیش از اثبات Idempotency مجاز نیست؛ می‌تواند OTP تکراری و هزینه مضاعف ایجاد کند.

### Storage

- `league/flysystem-aws-s3-v3` در Lockfile نصب نیست.
- `StandaloneAdvertisingService` Disk `public` را Hardcode کرده است.
- انتخاب S3 نیازمند Dependency Review، Disk قابل تنظیم، Migration/Compatibility Plan و E2E نمایش Asset است.

### Mail

- SMTP بدون Dependency جدید قابل استفاده است.
- `.env.staging.example` مقدار `MAIL_MAILER` را عمداً خالی می‌گذارد و بدون انتخاب Mailer واقعی Fail-Closed می‌شود.
- Production Readiness اکنون Mailer ثبت‌شده، Transport غیرمحلی و Evidence تازه را Gate می‌کند؛ Provider و E2E واقعی همچنان Pending هستند.

### Monitoring/Backup

- Metrics داخلی Provider جایگزین Synthetic Probe بیرونی و On-call نیست.
- Backup خودکار Provider جایگزین Dump قابل دانلود و Restore مستقل نیست.
- اسکریپت فعلی Manifest مستقل SHA-256 و کنترل یکپارچگی Fail-Closed دارد؛ Upload، Encryption Client-side و Lifecycle بیرونی هنوز وجود ندارند.

## 5. Due-diligence پرسش‌های اجباری

### برای تمام Providerها

1. مالک حقوقی حساب و Billing چه شخصیتی است و MFA/Recovery چگونه است؟
2. داده دقیقاً در کدام Region ذخیره و پردازش می‌شود؟
3. SLA، Support Escalation و Status Page چیست؟
4. Export، حذف نهایی، Closure حساب و Portability چگونه انجام می‌شود؟
5. چه Log/Auditی برای تغییر Credential و دسترسی وجود دارد؟
6. سقف هزینه، Billing Alert و قطع خودکار مصرف غیرعادی چیست؟

### برای OTP

1. زمان فعال‌سازی Template و خط خدماتی چقدر است؟
2. آیا Delivery Callback/Status قابل اتکا و Message ID یکتا وجود دارد؟
3. Mobile و متن پیام چند روز در پنل نگهداری و چگونه حذف می‌شود؟
4. Rate Limit، Burst Limit، Blacklist Behavior و هزینه پیام ناموفق چیست؟
5. آیا می‌توان نمایش Mobile را برای کاربران پنل Mask/Hide کرد؟

### برای DB/Backup

1. PostgreSQL Version، Extensionها، Connection Limit و Maintenance Window چیست؟
2. RPO/RTO و PITR Window دقیق چیست؟
3. Backup قابل Download است یا فقط داخل همان Provider Restore می‌شود؟
4. Restore به Instance جدا و Test Database چگونه است؟
5. Encryption، Key ownership و Private Network چگونه پیاده می‌شود؟

## 6. Scorecard موقت

امتیاز زیر فقط میزان شواهد عمومی و سازگاری فعلی را نشان می‌دهد، نه کیفیت قطعی Vendor.

| Candidate | Fit فنی | پرداخت ایران | Vendor Lock-in | Evidence عمومی | نتیجه فعلی |
|---|---|---|---|---|---|
| Liara Stack | بالا | ریالی | متوسط به علت تمرکز سرویس‌ها | متوسط | `CONDITIONAL HOLD — IRAN-ONLY CONTRACT CLARIFICATION` |
| Kavenegar OTP | متوسط؛ Adapter لازم | ریالی | کم با Contract داخلی | خوب برای API؛ Privacy/Retention عمومی ناکافی | `DUE-DILIGENCE — WRITTEN ANSWERS REQUIRED` |
| IPPanel OTP | متوسط؛ Adapter لازم | ریالی | کم با Contract داخلی | متوسط | `OTP COMPARISON CANDIDATE` |
| DigitalOcean Stack | بالا | ارزی/خارج ایران | متوسط | خوب | `NOT ELIGIBLE UNDER CURRENT OWNER DECISION` |
| Backblaze B2 | بالا برای Backup | ارزی/خارج ایران | کم به علت S3 | خوب | `NOT ELIGIBLE UNDER CURRENT OWNER DECISION` |
| Better Stack | بالا برای Uptime/Logs | ارزی/خارج ایران | متوسط | خوب | `NOT ELIGIBLE UNDER CURRENT OWNER DECISION` |

## 7. تصمیم‌هایی که هنوز از مالک لازم است

1. تعیین Provider ایرانی دوم مستقل برای Backup و Uptime.
2. معرفی نماینده نام‌دار و On-call شرکت مدیا پارس و تعیین Security Approver برای امضای Evidence فنی.
3. دریافت Legal Sign-off آقای سیفی و تصمیم نهایی درباره Vendorها فقط پس از پاسخ مکتوب پرسش‌های Due-diligence.

**Verdict فعلی:** `DUE-DILIGENCE IN PROGRESS — NO PROVIDER APPROVED, NO PURCHASE AUTHORIZED`
