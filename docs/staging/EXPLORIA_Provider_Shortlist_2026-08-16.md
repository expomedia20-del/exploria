# EXPLORIA — Provider Shortlist پیش از Staging

## 1. کنترل سند

| فیلد | مقدار |
|---|---|
| تاریخ بررسی | 2026-08-16 |
| نوع | Decision Support — نه Approval و نه مجوز خرید |
| وضعیت | `SHORTLIST PREPARED — OWNER/BUDGET/LEGAL APPROVAL PENDING` |
| فرض کاری | کاربران Pilot دارای شماره ایران (`09xxxxxxxxx`) و UI فارسی هستند؛ محل داده و روش پرداخت باید توسط مالک پروژه تأیید شود. |

قیمت، SLA و شرایط قرارداد ممکن است تغییر کنند. فقط صفحه رسمی Product/Documentation در تاریخ بالا بررسی شده است؛ Quote، DPA، Data Region، مالیات، Egress، شرایط حذف و امکان خرید باید پیش از Approval دوباره کنترل شوند.

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
| Uptime/Alert | Better Stack یا Uptime Kuma روی Provider/Account مستقل | Better Stack Uptime/Heartbeat/On-call را ارائه می‌کند؛ Uptime Kuma نیز قابل میزبانی جداست. | امکان پرداخت/دسترسی، Data Region، Alert واقعی و استقلال از Application Provider |
| Off-host Backup | Provider دوم S3-compatible؛ Backblaze B2 فقط در صورت امکان قرارداد/پرداخت | B2 برای Backup/Recovery و API سازگار S3 طراحی شده است و Region بین‌المللی دارد. | تأیید Legal/Data Region، پرداخت ارزی، دسترسی پایدار، Encryption و Restore از ایران |

تمرکز Compute، DB، Mail و Media در یک Provider عملیات Pilot را ساده می‌کند، اما Common-mode Risk ایجاد می‌کند. بنابراین Uptime Probe و حداقل یک Backup قابل Restore باید بیرون همان حساب/Failure Domain باشد.

### مسیر B — International، فقط در صورت تأیید پرداخت و Legal

| Capability | Candidate | Evidence رسمی | مانع فعلی |
|---|---|---|---|
| Compute | DigitalOcean Droplet | VM با Firewall/Monitoring و SLA اعلام‌شده | پرداخت ارزی، دسترسی، Data Region و Contract نامشخص |
| PostgreSQL | DigitalOcean Managed PostgreSQL | Managed PostgreSQL با Backup و Planهای مشخص | همان محدودیت‌های پرداخت/داده |
| Media | DigitalOcean Spaces | S3-compatible و CDN | Adapter لازم و Data Region خارجی |
| Backup مستقل | Backblaze B2 | S3-compatible، Encryption و EU/US regions | پرداخت/دسترسی/Legal |
| Monitoring | Better Stack | Uptime، Heartbeat، Incident/On-call و Log Retention | پرداخت/دسترسی/Legal |
| OTP ایران | Kavenegar یا IPPanel | پشتیبانی شماره ایران | همچنان Adapter و قرارداد داخلی لازم است |

این مسیر فقط وقتی برتر است که دسترسی سازمانی پایدار، پرداخت ارزی، قرارداد و انتقال داده برون‌مرزی رسماً مجاز باشند. صرف قیمت دلاری کمتر یا امکانات بیشتر دلیل کافی نیست.

## 3. Evidence رسمی بررسی‌شده

| منبع رسمی | Claim قابل استفاده | URL |
|---|---|---|
| Liara Cloud VPS | VPS ایران، منابع/IPv4 و پرداخت ساعتی | `https://liara.ir/products/cloud-server` |
| Liara DBaaS | PostgreSQL، Backup/Restore، Metrics و کنترل Public Network | `https://developers.liara.ir/pages/dbaas` |
| Liara Object Storage | S3-compatible، SSL و لینک موقت | `https://liara.ir/products/object-storage` |
| Liara Mail | Transactional Email و SMTP | `https://liara.ir/products/email` |
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
- `.env.staging.example` روی `MAIL_MAILER=log` است؛ پس از Approval باید Fail-Closed شود.
- Production Readiness فعلی Mail واقعی را Gate نمی‌کند.

### Monitoring/Backup

- Metrics داخلی Provider جایگزین Synthetic Probe بیرونی و On-call نیست.
- Backup خودکار Provider جایگزین Dump قابل دانلود و Restore مستقل نیست.
- اسکریپت فعلی Upload، Encryption Client-side، SHA-256 Manifest و Lifecycle ندارد.

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
| Liara Stack | بالا | محتمل/ریالی | متوسط به علت تمرکز سرویس‌ها | متوسط | `PRIMARY DUE-DILIGENCE CANDIDATE` |
| Kavenegar OTP | متوسط؛ Adapter لازم | ریالی | کم با Contract داخلی | خوب برای API | `OTP PRIMARY CANDIDATE` |
| IPPanel OTP | متوسط؛ Adapter لازم | ریالی | کم با Contract داخلی | متوسط | `OTP COMPARISON CANDIDATE` |
| DigitalOcean Stack | بالا | نامشخص | متوسط | خوب | `CONDITIONAL INTERNATIONAL` |
| Backblaze B2 | بالا برای Backup | نامشخص | کم به علت S3 | خوب | `CONDITIONAL OFF-HOST` |
| Better Stack | بالا برای Uptime/Logs | نامشخص | متوسط | خوب | `CONDITIONAL MONITORING` |

## 7. تصمیم‌هایی که هنوز از مالک لازم است

1. ایران‌محور یا بین‌المللی بودن محل داده و قرارداد.
2. امکان پرداخت ارزی و داشتن حساب سازمانی پایدار.
3. سقف ماهانه Staging و سقف مصرف OTP/Mail/Storage/Monitoring/Backup.
4. پذیرش یا رد Liara به‌عنوان Candidate اصلی Due-diligence.
5. انتخاب Kavenegar یا IPPanel برای Sandbox/Quote؛ انتخاب نهایی فقط پس از E2E.
6. تعیین Provider دوم مستقل برای Backup و Uptime.

**Verdict فعلی:** `SHORTLIST ONLY — NO PROVIDER APPROVED, NO PURCHASE AUTHORIZED`
