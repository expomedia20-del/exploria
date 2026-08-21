# EXPLORIA — بسته تصمیم Provider، خرید و Handoff پیش از انتقال v1.0

## 1. کنترل سند

| فیلد | مقدار |
|---|---|
| نوع | Provider-agnostic Decision Pack — بدون خرید یا Provisioning |
| تاریخ | 2026-08-21 |
| دامنه | `PRE-DEC-08` تا `PRE-DEC-16` |
| مبنای Codebase | `main@2e7fa6c` — Merge PR #9 |
| اطلاعات بازار | فقط اسناد تاریخ‌دار 2026-08-16/18؛ Quote و شرایط خرید باید بیرون Repository تازه‌سازی شوند. |
| وضعیت | `AWAITING EXTERNAL QUOTES/ANSWERS/APPROVALS — NO PURCHASE` |
| Production/Pilot | `NO-GO` |

این بسته معیار تصمیم را مشخص می‌کند و Provider خاصی را تحمیل یا تصویب نمی‌کند. Candidateهای اسناد قبلی فقط ورودی Due-diligence هستند. Account، Server، Database، Domain، Credential، Sandbox واقعی و خرید در این Work Item ایجاد نمی‌شوند. قرارداد، Quote، Invoice، نام تماس خصوصی، Secret و اطلاعات حساب باید بیرون Repository نگهداری شوند؛ فقط شناسه Evidence غیرحساس در Git ثبت می‌شود.

## 2. مرز کار پیش از انتقال

### داخل Scope فعال

- تعیین Requirement هر Capability و امکان ثبت `NOT REQUIRED` با دلیل مصوب؛
- دریافت پاسخ مکتوب/Quote تازه و بررسی Legal/Security/Operations؛
- انتخاب یا رد Provider/Plan و تعیین Owner/جانشین؛
- تصویب Storage و Runtime بدون افزودن Dependency؛
- تعیین سقف مالی، Domain و Mailbox ownership؛
- ثبت Work Itemهای کدی فقط پس از تصمیم بسته‌شده.

### خارج از Scope فعال

- ساخت Account یا ورود Credential؛
- خرید، Provisioning Server/PostgreSQL یا ساخت `.env` واقعی؛
- فعال‌سازی DNS/TLS، Mail domain یا Provider API؛
- نصب Agent، S3 Adapter، Redis یا هر Dependency؛
- Deploy/Migration، systemd، Backup upload و Restore خارجی؛
- E2E واقعی OTP/Mail/Storage/Monitoring/Runtime؛
- Operational Evidence، UAT و Go-live.

این تفکیک مانع انتخاب و Contract approval پیش از انتقال نمی‌شود، اما اجرای فنی را تا Work Item مجاز بعدی متوقف می‌کند.

## 3. خلاصه ممیزی Repository

| Capability | وضعیت مشاهده‌شده | نتیجه Fail-closed |
|---|---|---|
| Hosting/PostgreSQL | Runbook/Deploy/Backup/Restore برای Linux و PostgreSQL آماده است؛ External environment وجود ندارد. | بدون Server، DB و Evidence واقعی PASS نمی‌شود. |
| OTP | Contract داخلی `OtpProvider` و HTTP adapter عمومی وجود دارد؛ Endpoint/Token الگو خالی است. Payload عمومی با APIهای Vendor-specific الزاماً سازگار نیست. | بدون Provider/Adapter/E2E واقعی FAIL می‌ماند. |
| Mail | SMTP استاندارد Laravel موجود؛ `MAIL_MAILER` در Staging عمداً خالی است. | `log`/`array` یا Mailer خالی رد می‌شود. |
| Storage | Upload تبلیغ روی Disk ثابت `public` است؛ S3 Adapter نصب نیست. | انتخاب Storage یا تغییر `FILESYSTEM_DISK` به‌تنهایی قابلیت را واقعی نمی‌کند. |
| Monitoring | stderr/syslog و Readiness gate موجود؛ مقصد مرکزی، Probe و On-call وجود ندارد. | Sink صرفاً محلی و Evidence ناقص رد می‌شوند. |
| Backup | Dump/Manifest SHA-256 و Restore guard موجود؛ مقصد مستقل، Encryption/Lifecycle و Drill خارجی وجود ندارد. | Backup محلی جایگزین Evidence مستقل نیست. |
| Queue/Cache/Session | Database Driver در `.env.staging.example` انتخاب شده و Migrationها موجودند. | Driver نامعتبر و Evidence ناقص رد می‌شوند؛ Redis الزام نشده است. |
| Scheduler | systemd template موجود است؛ `schedule:list` هیچ Task واقعی ندارد. | Task صفر یا Evidence ناقص رد می‌شود. |
| Budget | سقف‌های داخلی با شرط تصویب شده‌اند؛ Quote/Invoice/Vendor approval وجود ندارد. | سقف بودجه به‌تنهایی مجوز خرید نیست. |
| Domain | `APP_URL` و Mailboxها Placeholder هستند. | Domain/DNS/Mail ownership تعیین نشده است. |

## 4. معیار مشترک هر Provider/Plan

هر Candidate باید برای Scope خودش این فیلدها را با Evidence مکتوب پاسخ دهد:

| معیار | خروجی الزامی |
|---|---|
| Legal identity/contract | طرف قرارداد، Invoice/Tax، Terms، DPA و Subprocessorها |
| Data location | محل Storage/Processing/Replication/Support access و تعهد Iran-only حسب تصمیم مالک |
| Security | MFA، RBAC، Audit، Token/Key lifecycle، Recovery و Incident notification |
| Operations | SLA، Maintenance، Status، Escalation، Export، Delete و Exit plan |
| Financial | Quote تازه، مالیات، Overage/Egress، سقف مصرف و Billing alert |
| Ownership | Account owner، جانشین، Billing owner و On-call بدون ثبت تماس خصوصی در Git |
| Evidence | External reference، تاریخ Review، نتیجه و شرط دارای Owner/Due Date |

Marketing claim یا صفحه قیمت بدون پاسخ Scope-specific برای `Approved` کافی نیست.

## 5. `PRE-DEC-08` — Hosting و PostgreSQL

### تصمیم‌های الزامی

| موضوع | خروجی |
|---|---|
| Compute model | Linux VPS/IaaS یا گزینه سازگار با Runbook؛ دلیل و محدودیت |
| Isolation | Staging مستقل، Network/Firewall، SSH/Sudo و Recovery console |
| PostgreSQL | Version/Extension، TLS، Network exposure، Backup/PITR، Maintenance |
| Recovery | RPO/RTO قراردادی، Export، Restore به DB ایزوله و Exit plan |
| Data/Legal | Region و عدم انتقال/دسترسی خارج Scope مصوب، DPA و deletion timeline |
| Account security | MFA، RBAC، Audit، API token rotation و جانشین |
| Commercial | Plan، Quote/Tax، SLA، سقف مصرف و Owner خرید |

| فیلد تصمیم | مقدار |
|---|---|
| Provider/Plan selected or rejected | `DECISION REQUIRED` |
| Legal/Security/Operations verdict | `DECISION REQUIRED` |
| External Quote/Answer Reference | `DECISION REQUIRED` |
| Product/Finance approval | `DECISION REQUIRED` |
| نتیجه/شرط/Owner/Due Date | `DECISION REQUIRED` |

Candidate دارای تعارض حل‌نشده Data Region، MFA/Audit، Export یا Backup نمی‌تواند `Approved with conditions` برای ورود داده واقعی تلقی شود.

## 6. `PRE-DEC-09` — OTP Provider و Integration

| موضوع | خروجی الزامی |
|---|---|
| Data | Mobile/Message/Delivery retention، masking، delete و support access |
| Contract | DPA، Incident notice، Subprocessor و Data Rights support |
| Account | MFA/RBAC/Audit، API key rotation/revoke و Recovery |
| Delivery | Template/Sender eligibility، callback/status، rate/burst و error taxonomy |
| Cost | هزینه موفق/ناموفق، مالیات، Low-balance و Hard Cost Cap |
| Code compatibility | Mapping دقیق Request/Response/Auth و تصمیم درباره Adapter زیر `OtpProvider` |

| فیلد تصمیم | مقدار |
|---|---|
| Provider/Plan | `DECISION REQUIRED` |
| Adapter required? | `YES` / `NO` با Evidence؛ پیش‌فرض `UNKNOWN` |
| Monthly OTP/IRR hard cap | `DECISION REQUIRED` |
| Legal/Security/Tech approval | `DECISION REQUIRED` |
| External reference/result/conditions | `DECISION REQUIRED` |

Sandbox بدون PII واقعی فقط پس از Account security و پاسخ Retention مجاز است. ارسال واقعی و Adapter تا Approval ممنوع‌اند.

## 7. `PRE-DEC-10` — Transactional Mail

| موضوع | خروجی الزامی |
|---|---|
| Transport | SMTP/TLS استاندارد و Port/Auth policy |
| Domain identity | From، Return-Path، Subdomain/Stream و UAT allowlist |
| DNS | SPF، DKIM و DMARC ownership/verification plan |
| Delivery | Bounce، Suppression، rejected recipient، rate limit و alert |
| Data | Content/log retention، staff access، DPA و deletion |
| Account/Cost | MFA/RBAC/rotation، Quote، cap و Billing alert |

| Provider/Plan | Domain/Stream | Owner/Backup | External reference | نتیجه |
|---|---|---|---|---|
| `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` |

Laravel SMTP موجود است؛ تا تصمیم بسته‌شده هیچ SDK یا Transport جدیدی اضافه نمی‌شود.

## 8. `PRE-DEC-11` — Application Storage

ابتدا Requirement انتخاب شود، سپس Backend:

| انتخاب | شرط پذیرش | اثر فنی |
|---|---|---|
| Persistent filesystem مستقل از Release | Single-host، durability، backup، lifecycle و private access را اثبات کند. | Disk قابل تنظیم؛ Dependency جدید لازم نیست. |
| Object storage | Requirement دوام/مقیاس/جداسازی و Provider approval آن را لازم کند. | فقط سپس Adapter/Composer audit/Regression مجاز است. |
| `NOT REQUIRED` | Upload واقعی رسماً خارج Scope یا Deferred شود. | مسیر باید Fail-closed/غیرفعال بماند؛ تغییر Scope نیازمند Approval است. |

تصمیم باید Data class، Public/Private policy، URL strategy، MIME/size، malware review، encryption، lifecycle/delete، export و جداسازی Backup را پوشش دهد.

| فیلد تصمیم | مقدار |
|---|---|
| Requirement/Backend | `DECISION REQUIRED` |
| Provider/Plan در صورت نیاز | `DECISION REQUIRED` |
| Adapter/Dependency authorization | `NOT AUTHORIZED` تا انتخاب Object Storage |
| Product/Security/Operations/Tech verdict | `DECISION REQUIRED` |
| External reference/result/conditions | `DECISION REQUIRED` |

## 9. `PRE-DEC-12` — Monitoring، Logging، Uptime و Alert

| موضوع | خروجی الزامی |
|---|---|
| Central destination | Log/System metrics destination و Host Agent؛ بدون Vendor SDK در Domain مگر نیاز مصوب |
| Sources | Laravel، Nginx، PHP-FPM، PostgreSQL، Queue، Scheduler و Backup |
| Privacy | Redaction قبل از ارسال، retention، access و export/delete |
| Uptime | Probe خارج Application failure domain و مسیر `/up` |
| Alert | کانال سازمانی، Primary/Backup On-call، Ack/escalation و test alert |
| Cost/Security | Quote/cap، MFA/RBAC/Audit و agent credential lifecycle |

| Central monitoring | Independent uptime | Owner/Backup | External reference | نتیجه |
|---|---|---|---|---|
| `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` |

Dashboard بدون Notification یا Sink محلی-only کافی نیست.

## 10. `PRE-DEC-13` — Backup مستقل

| موضوع | خروجی الزامی |
|---|---|
| Failure domain | مقصد/Account مستقل از Application Host و Primary Provider/account |
| Data/Legal | Iran-only حسب تصمیم مالک، DPA، deletion و support access |
| Security | TLS، Encryption at rest/client-side decision، key ownership، MFA/RBAC/Audit |
| Lifecycle | Retention نهایی پس از `PRE-DEC-03`، immutability/versioning و expiry |
| Restore | Download/export، Restore credential مستقل، RPO/RTO و test environment |
| Operations/Cost | Owner/backup، failure alert، Quote/Egress/Tax و exit plan |

| Destination/Plan | Independent from | Encryption/key owner | External reference | نتیجه |
|---|---|---|---|---|
| `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` |

Snapshot یا Backup داخل همان Failure Domain به‌تنهایی `PRE-DEC-13` را نمی‌بندد.

## 11. `PRE-DEC-14` — Queue، Cache، Session و Scheduler

| جزء | Baseline پیشنهادی | تصمیم | شرط |
|---|---|---|---|
| Queue | Laravel Database Driver روی PostgreSQL Staging | `DECISION REQUIRED` | Worker/Retry/failed job/retention E2E بعداً |
| Cache | Laravel Database Store | `DECISION REQUIRED` | Prune/locking/load validation بعداً |
| Session | Laravel Database Driver + encryption/secure cookie | `DECISION REQUIRED` | persistence/logout/revocation/load validation بعداً |
| Scheduler | systemd timer هر دقیقه | `DECISION REQUIRED` | Task واقعی فقط پس از Retention approval |
| Redis/alternative | `NOT REQUIRED BY CURRENT EVIDENCE` | فقط با Requirement جدید | Dependency/operations review الزامی |

| فیلد تصمیم | مقدار |
|---|---|
| Baseline verdict | `DECISION REQUIRED` |
| Retention task name/purpose | `BLOCKED BY PRE-DEC-03` |
| Tech/Operations/Product approval | `DECISION REQUIRED` |
| External reference/result/conditions | `DECISION REQUIRED` |

هیچ Task نمایشی برای PASS کردن Scheduler و هیچ Redis/S3/Dependency برای آینده‌نگری اضافه نمی‌شود.

## 12. `PRE-DEC-15` — Budget و مجوز خرید

سقف‌های موجود فقط کنترل داخلی‌اند؛ هر خرید به Quote تازه و تطبیق All-in نیاز دارد.

| Capability | Provider/Plan | Quote date/ref | Recurring + Tax/Egress | Cap/Alert | Approver/result |
|---|---|---|---:|---|---|
| Compute/PostgreSQL | `TBD` | `TBD` | `TBD` | سقف مصوب فعلی | `TBD` |
| OTP | `TBD` | `TBD` | `TBD` | OTP و IRR hard cap | `TBD` |
| Mail | `TBD` | `TBD` | `TBD` | داخل سقف تجمیعی | `TBD` |
| Storage | `TBD/NOT REQUIRED` | `TBD` | `TBD` | داخل سقف تجمیعی | `TBD` |
| Monitoring/Uptime | `TBD` | `TBD` | `TBD` | داخل سقف تجمیعی | `TBD` |
| Backup | `TBD` | `TBD` | `TBD` | داخل سقف تجمیعی | `TBD` |

برای بسته‌شدن باید Invoice/Tax، Setup fee، Overage/Egress، Billing cycle، Alertهای 50/75/90 درصد، توقف سخت و سقف کل `60,000,000 IRR/month` فعلی بازتأیید شوند. تغییر سقف نیازمند تصمیم جدید Product/Finance است.

## 13. `PRE-DEC-16` — Domain و کانال‌های رسمی

| تصمیم | خروجی |
|---|---|
| Official domain | نام نهایی و Legal/Product approval |
| Registrar/DNS owner | Primary، backup و Recovery process |
| DNS change control | Approver، audit/evidence و rollback |
| Role mailboxes | `privacy@`، `security@` و mailboxهای عملیاتی لازم با Owner/backup |
| Mail authentication | SPF/DKIM/DMARC owner و verification plan |
| Environment naming | Staging hostname و منع اشتباه با Production |
| Renewal | Billing owner، expiry alert و recovery |

| Domain | DNS owner/backup | Role mailbox owners | External reference | نتیجه |
|---|---|---|---|---|
| `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` |

Domain پس از Approval می‌تواند در اسناد عمومی ثبت شود؛ Credential، Recovery code و تماس خصوصی هرگز وارد Git نمی‌شوند. فعال‌سازی DNS/TLS بعد از انتقال در Work Item مستقل است.

## 14. نقشه تصمیم به Work Item پس از Approval

| تصمیم | Work Item مشروط | قاعده |
|---|---|---|
| `PRE-DEC-08` | Handoff/Provisioning runbook نهایی و Config contract | بدون Credential در Git؛ اجرای واقعی خارج Scope فعلی |
| `PRE-DEC-09` | OTP Adapter + redaction/error/timeout tests | فقط برای Provider مصوب؛ Domain contract ثابت |
| `PRE-DEC-10` | SMTP config/runbook و delivery failure tests | بدون SDK مگر Requirement |
| `PRE-DEC-11` | Configurable media disk؛ Adapter فقط اگر لازم | Regression Upload/URL/Delete الزامی |
| `PRE-DEC-12` | Structured output/redaction یا Host-agent config | SDK Vendor در Domain ممنوع مگر Requirement |
| `PRE-DEC-13` | Encryption/upload/lifecycle runbook و restore procedure | Retention هماهنگ با `PRE-DEC-03` |
| `PRE-DEC-14` | Retention commands و Scheduler registration | Task واقعی، idempotent و legal-hold aware |
| `PRE-DEC-15` | Purchase authorization record | Quote/Invoice بیرون Git |
| `PRE-DEC-16` | Public config/runbook values | Secret/Recovery بیرون Git |

## 15. Evidence Register و Exit Gate

| ID | External Evidence Reference | Approvers | نتیجه | شروط/Owner/Due Date |
|---|---|---|---|---|
| `PRE-DEC-08` | `TBD` | Product/Finance/Legal/Security/Operations | `TBD` | `TBD` |
| `PRE-DEC-09` | `TBD` | Product/Finance/Legal/Security/Tech | `TBD` | `TBD` |
| `PRE-DEC-10` | `TBD` | Product/Operations/Legal/Security | `TBD` | `TBD` |
| `PRE-DEC-11` | `TBD` | Product/Operations/Security/Tech | `TBD` | `TBD` |
| `PRE-DEC-12` | `TBD` | Security/Operations/Legal | `TBD` | `TBD` |
| `PRE-DEC-13` | `TBD` | Operations/DBA/Security/Legal/Finance | `TBD` | `TBD` |
| `PRE-DEC-14` | `TBD` | Product/Tech/Operations/Legal | `TBD` | `TBD` |
| `PRE-DEC-15` | `TBD` | Product/Finance/Operations | `TBD` | `TBD` |
| `PRE-DEC-16` | `TBD` | Product/Operations/Legal/Security | `TBD` | `TBD` |

هر ردیف فقط با `Approved`، `Approved with conditions` دارای Owner/Due Date، `Rejected` یا `NOT REQUIRED` دارای دلیل و Approver بسته می‌شود. تکمیل این بسته فقط تصمیم‌های پیش از انتقال را می‌بندد؛ Purchase execution، Provisioning، Credential injection، E2E، Evidence عملیاتی، UAT و Production Readiness همچنان Work Itemهای مستقل و `NO-GO` هستند.
