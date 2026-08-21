# EXPLORIA — بسته تصمیم حقوقی، حریم خصوصی و نگهداشت پیش از انتقال v1.0

## 1. کنترل سند

| فیلد | مقدار |
|---|---|
| نوع | Working Decision Pack — غیرجایگزین مشاوره یا تصویب حقوقی |
| تاریخ | 2026-08-21 |
| دامنه | `PRE-DEC-01`، `PRE-DEC-02` و `PRE-DEC-03` |
| مبنای Codebase | `main@3e0dce9` |
| Gate | پیش از ورود داده واقعی، ساخت Release Candidate یا انتقال به سرور |
| وضعیت | `AWAITING EXTERNAL APPROVAL — NO REAL DATA / NO SERVER TRANSFER` |
| Production/Pilot | `NO-GO` |

هدف این سند تبدیل سه تصمیم باز حقوقی و داده به ورودی قابل بازبینی و خروجی قابل ثبت است. این سند هیچ تصمیم حقوقی را تصویب نمی‌کند. نسخه امضاشده، مکاتبات، نام تماس خصوصی، قرارداد، PII و امضای اسکن‌شده باید بیرون Repository و در محل کنترل‌شده نگهداری شوند؛ در Git فقط شناسه مرجع غیرحساس ثبت می‌شود.

## 2. قواعد استفاده و واژگان وضعیت

هر گزاره در این بسته یکی از وضعیت‌های زیر را دارد:

| وضعیت | معنی |
|---|---|
| `OBSERVED` | واقعیت قابل مشاهده در Codebase فعلی |
| `PROPOSED` | پیشنهاد موجود که هنوز Approve نشده است |
| `DECISION REQUIRED` | انتخاب صریح Approver لازم است |
| `APPROVED EXTERNALLY` | فقط پس از وجود مرجع بیرونی، تاریخ و نتیجه معتبر قابل ثبت است |

قواعد Fail-Closed:

1. نبود پاسخ، سکوت یا تکمیل ناقص فرم معادل Approval نیست.
2. تا بسته‌شدن هر سه تصمیم، ورود PII واقعی، انتشار Consent نهایی و انتقال Release مجاز نیست.
3. هیچ مقدار پیشنهادی Retention نباید پیش از Approval به Job یا Scheduler تبدیل شود.
4. هیچ `ConsentVersion` آزمایشی نباید بازنویسی یا به‌عنوان متن حقوقی واقعی معرفی شود.
5. هر شرط Approval باید Owner، Due Date و External Evidence Reference داشته باشد.

## 3. خلاصه ممیزی خواندنی Codebase

| حوزه | داده/جدول‌های شاخص | وضعیت و کنترل فعلی | Gap پیش از داده واقعی |
|---|---|---|---|
| هویت و ورود | `users`، `otp_requests`، `password_reset_tokens`، `sessions`، `passkeys` | Mobile در `User` و `OtpRequest` با Cast رمزگذاری می‌شود و Hash مستقل دارد؛ OTP Code به‌صورت Hash است؛ Session شامل IP، User-Agent و Payload است. | هدف، مدت نگهداشت، Prune و پاسخ به درخواست داده تصویب نشده است. |
| رضایت | `consent_versions`، `consent_logs` | نسخه، زمان، کاربر، Source و Session Hash ثبت می‌شود؛ نسخه فعال Seed یعنی `pilot-fa-0.1` صریحاً `is_demo=true` است؛ Action فعلی فعال‌بودن نسخه را کنترل می‌کند اما Demoبودن را رد نمی‌کند. | متن نهایی، Controller/Contact، دامنه رضایت، Withdraw، کودک/اهلیت و نسخه انتشار تصویب نشده است. |
| بازدید و رویداد | `visits`، `scan_events`، `event_log` | Session/IP/User-Agent در مسیر Scan به‌صورت Hash ثبت می‌شوند؛ برخی Payloadهای JSON آزاد و دامنه‌ای‌اند. | Data Classification و قاعده ممنوعیت PII در Payload/Metadata، Retention و Anonymization تصویب نشده است. |
| مشارکت و بازی | `user_mission_progress`، `game_*`، `campaign_participants` | رکوردها به User و Campaign متصل‌اند؛ دعوت تیمی Mobile Hash نگه می‌دارد. | اثر حذف/ناشناس‌سازی روی پیشرفت، تیم، KPI و Aggregate باید تصمیم‌گیری شود. |
| پاداش و مالی | `user_rewards`، `reward_redemptions`، `financial_*` | شواهد عملیاتی/مالی به User یا Actor متصل می‌شوند و بخشی از روابط Cascade یا Null-on-delete است. | الزام نگهداشت مالی، حداقل داده قابل حفظ و روش Pseudonymization نیازمند Legal/Finance است. |
| تماس‌های تجاری | `partner_accounts`، `sponsor_accounts`، `marketing_leads` | Contact Name/Mobile ذخیره می‌شود؛ Mobile در `marketing_leads` در Model فعلی رمزگذاری یا مخفی نشده است. | مبنای جمع‌آوری، Consent/Notice بازاریابی، Withdraw، دسترسی و Retention باید پیش از استفاده واقعی تعیین شود. |
| محتوا و عملیات | Ad/Proposal/Checklist/Incident Reference و فیلدهای `notes`، `metadata`، `payload_json` | ساختارهای آزاد برای داده دامنه‌ای وجود دارد؛ برخی مسیرهای Event از Hash استفاده می‌کنند. | دستورالعمل Content Hygiene و ممنوعیت درج Secret/PII در فیلدهای آزاد و Log لازم است. |
| Queue/Cache/Backup/Log | `jobs`، `failed_jobs`، Cache/Session و خروجی‌های عملیاتی | Queue Payload و Failed Job می‌تواند نسخه‌ای از داده پردازش‌شده نگه دارد؛ مقصد Backup/Log خارجی هنوز انتخاب نشده است. | Retention/Prune، Redaction، دسترسی و حذف چرخه‌ای باید با `PRE-DEC-12..14` همسو شود. |

### 3.1 رفتار حذف مشاهده‌شده

- حذف شخصی حساب در `ProfileController` فقط User را حذف می‌کند و تست فعلی صرفاً کاربر بدون روابط عملیاتی را پوشش می‌دهد.
- `consent_logs.user_id` دارای `restrictOnDelete` است؛ بنابراین حذف ساده User پس از ثبت Consent می‌تواند متوقف شود.
- بعضی روابط مانند Visit، Mission/Reward و بازی `cascadeOnDelete` هستند؛ بعضی رویدادها و رکوردهای عملیاتی `nullOnDelete` هستند.
- مسیر مستقل Data Subject Request، Legal Hold، Anonymization، Suppression پس از Restore و Evidence بدون PII وجود ندارد.
- اجرای `php artisan schedule:list` در 2026-08-21 نتیجه `No scheduled tasks have been defined` داد؛ Retention Task واقعی هنوز وجود ندارد.

نتیجه ممیزی: حذف مستقیم حساب، معادل سیاست کامل حذف/ناشناس‌سازی نیست و بدون تصمیم `PRE-DEC-03` نباید برای داده واقعی معتبر فرض شود.

## 4. فرم بستن `PRE-DEC-01` — پذیرش نقش Legal Approver

این بخش باید با مرجع بیرونی تکمیل شود؛ متن یا امضای واقعی وارد Git نشود.

| فیلد تصمیم | مقدار لازم |
|---|---|
| Legal Approver Role/Organization | `DECISION REQUIRED` |
| محدوده Review | Privacy Notice، Consent، کودک/اهلیت، Data Rights، Retention/Deletion، Legal Hold و Processor Terms |
| تاریخ پذیرش نقش | `DECISION REQUIRED` |
| External Engagement Reference | `DECISION REQUIRED — NON-SENSITIVE ID ONLY` |
| کانال محرمانه تبادل اسناد | `DECISION REQUIRED — DO NOT RECORD ADDRESS/CREDENTIAL HERE` |
| جانشین یا مسیر Escalation | `DECISION REQUIRED` |
| نتیجه | `Approved` / `Approved with conditions` / `Rejected` |
| شروط، Owner و Due Date | `DECISION REQUIRED` |

شرط بسته‌شدن: پذیرش مکتوب نقش و Scope، تاریخ و مرجع بیرونی معتبر وجود داشته باشد. معرفی شفاهی یا درج نام به‌تنهایی کافی نیست.

## 5. فرم بستن `PRE-DEC-02` — Privacy، Consent و Data Rights

### 5.1 تصمیم‌های الزامی

| ID | پرسش تصمیم | خروجی الزامی | Approver |
|---|---|---|---|
| `PRIV-01` | Data Controller/متولی دقیق چه شخص/نهاد حقوقی است؟ | عنوان حقوقی، نشانی رسمی و Contact نقش‌محور بیرون Git | Legal + Product |
| `PRIV-02` | هدف هر دسته پردازش چیست و کدام داده واقعاً لازم است؟ | Purpose/Data Category Matrix و موارد ممنوع | Legal + Product |
| `PRIV-03` | مبنای مجاز هر هدف و دامنه Consent چیست؟ | تفکیک ورود/تجربه/پاداش/امنیت از Marketing | Legal |
| `PRIV-04` | Marketing Lead با چه Notice/Consent و Withdraw اجرا می‌شود؟ | متن و Flow مستقل یا تصمیم `NOT REQUIRED` | Legal + Product |
| `PRIV-05` | کودک/اهلیت چگونه تعریف و کنترل می‌شود؟ | محدوده سنی/اهلیت، نقش والد/سرپرست و روش اجرا | Legal + Product + Venue |
| `PRIV-06` | حقوق مشاهده، اصلاح، حذف، محدودسازی و شکایت چگونه دریافت می‌شوند؟ | کانال رسمی، احراز هویت متناسب، Owner و SLA | Legal + Privacy Owner |
| `PRIV-07` | Processorها و محل پردازش چگونه اعلام و کنترل می‌شوند؟ | Processor Register و معیار تغییر Provider | Legal + Security + Operations |
| `PRIV-08` | نسخه نهایی چگونه منتشر و قابل اثبات می‌شود؟ | Version ID یکتا، متن مصوب، تاریخ انتشار و Language | Legal + Product |

### 5.2 قواعد فنی ناشی از Approval

پس از Approval و فقط در Work Item مستقل:

- نسخه جدید Consent با شناسه جدید و `is_demo=false` اضافه شود؛ نسخه Demo حفظ و غیرفعال شود.
- مسیر واقعی نباید در محیط دارای داده واقعی، Consent آزمایشی را قابل پذیرش بداند.
- Withdraw و Data Rights مطابق نتیجه مصوب، با Authorization، Validation و Evidence بدون PII پیاده‌سازی شوند.
- Marketing Lead فقط در صورت Requirement مصوب، Notice/Consent مناسب و کنترل دسترسی/نگهداشت فعال بماند.
- Processor Register مرجع Config/Runbook باشد و نام Provider در Business Logic دامنه Hardcode نشود.

### 5.3 رکورد تصمیم

| فیلد | مقدار |
|---|---|
| Final Privacy/Consent Version | `DECISION REQUIRED` |
| Product Approver/Date | `DECISION REQUIRED` |
| Legal Approver/Date | `DECISION REQUIRED` |
| Venue/Security Approval حسب دامنه | `DECISION REQUIRED` |
| External Sign-off Reference | `DECISION REQUIRED — NON-SENSITIVE ID ONLY` |
| نتیجه و شروط | `DECISION REQUIRED` |

## 6. فرم بستن `PRE-DEC-03` — Retention، Deletion و Legal Hold

اعداد زیر از Approval Pack فعلی آمده‌اند و فقط ورودی Review هستند؛ هیچ‌کدام Approved نیستند.

| دسته | مدت پیشنهادی موجود | تصمیم نهایی | اقدام پایان مدت | Exception/Legal Hold Owner |
|---|---:|---|---|---|
| OTP Request | 30 روز | `DECISION REQUIRED` | Delete | `DECISION REQUIRED` |
| Session/Password Reset | 30 روز عدم فعالیت/انقضا | `DECISION REQUIRED` | Prune | `DECISION REQUIRED` |
| حساب Visitor | رابطه فعال؛ حداکثر پیشنهادی 30 روز پس از درخواست معتبر | `DECISION REQUIRED` | Delete/Anonymize | `DECISION REQUIRED` |
| Consent Evidence | 5 سال پیشنهادی | `DECISION REQUIRED` | حداقل‌سازی/آرشیو محدود | `DECISION REQUIRED` |
| Scan/Visit/Mission/KPI | 180 روز پس از Pilot | `DECISION REQUIRED` | Delete/Anonymize/Aggregate | `DECISION REQUIRED` |
| Reward/Redemption/Financial | 7 سال پیشنهادی | `DECISION REQUIRED` | آرشیو محدود/حذف | `DECISION REQUIRED` |
| Audit/Security Event | 365 روز | `DECISION REQUIRED` | Prune | `DECISION REQUIRED` |
| Incident Record | 3 سال پس از Closure | `DECISION REQUIRED` | Archive/Delete | `DECISION REQUIRED` |
| Marketing Lead | 90 روز بدون تبدیل یا Withdraw | `DECISION REQUIRED` | Delete/Anonymize | `DECISION REQUIRED` |
| Backup | Daily 30، Weekly 12 هفته، Monthly 12 ماه | `DECISION REQUIRED` | Lifecycle Expiry | `DECISION REQUIRED` |
| UAT/Pilot Evidence | 12 ماه پس از Go/No-Go | `DECISION REQUIRED` | Review/Delete | `DECISION REQUIRED` |

### 6.1 تصمیم‌های اجرایی لازم

| موضوع | خروجی تصمیم |
|---|---|
| Data Request Intake | کانال رسمی، Ticket Reference، Owner و جانشین |
| Identity Verification | حداقل داده و روش متناسب با ریسک |
| SLA | زمان پاسخ، تکمیل و Escalation برای هر نوع درخواست |
| Legal Hold | مجوزدهنده، Scope، Start/End، Review Date و Evidence Reference |
| Delete vs Anonymize | رفتار هر جدول/دسته و داده Aggregate مجاز |
| Consent Evidence | حداقل شناسه قابل حفظ پس از Anonymization و مبنای آن |
| Financial/Reward | داده الزامی قابل حفظ و دسترسی محدود با تأیید Finance/Legal |
| Free-form Fields | ممنوعیت PII/Secret و قاعده Redaction برای `notes`/`metadata`/`payload_json` |
| Queue/Failed Jobs/Logs | Prune و Redaction هماهنگ با داده اصلی |
| Backup/Restore | Expiry، حذف پس از Restore و جلوگیری از فعال‌شدن مجدد Subject حذف‌شده |
| Evidence | ثبت نتیجه بدون Mobile، Email، متن درخواست یا سند هویتی در Git |

### 6.2 رکورد تصمیم

| فیلد | مقدار |
|---|---|
| Retention Matrix Version | `DECISION REQUIRED` |
| Legal/Privacy Approver/Date | `DECISION REQUIRED` |
| Security Approver/Date | `DECISION REQUIRED` |
| Finance Approver/Date برای داده مالی | `DECISION REQUIRED` |
| External Sign-off Reference | `DECISION REQUIRED — NON-SENSITIVE ID ONLY` |
| نتیجه و شروط | `DECISION REQUIRED` |

## 7. نقشه Decision به Work Item

این جدول Backlog اجرایی پس از Approval را تعریف می‌کند؛ هیچ ردیف قبل از بسته‌شدن تصمیم متناظر مجوز اجرا ندارد.

| Work Item | تصمیم پیش‌نیاز | Acceptance حداقلی |
|---|---|---|
| Final Consent Publication | `PRE-DEC-02` | نسخه جدید غیرDemo، نسخه/زمان قابل اثبات، Demo غیرفعال، تست رد Consent نامعتبر/آزمایشی در محیط واقعی |
| Consent Withdraw/Data Rights | `PRE-DEC-02/03` | Authorization، Validation، SLA status، Audit بدون PII و تست‌های موفق/خطا |
| Account Delete/Anonymize | `PRE-DEC-03` | پوشش روابط Restrict/Cascade/Null، رفتار مالی/Consent مصوب، Idempotency و تست Integration |
| Marketing Lead Protection | `PRE-DEC-02/03` | تصمیم Keep/Remove، کنترل دسترسی، Encryption/Masking در صورت نیاز و Retention Test |
| Retention Commands | `PRE-DEC-03` | Dry-run، Batch limit، Legal Hold exclusion، Audit summary بدون PII و تست مرز زمانی |
| Scheduler Registration | `PRE-DEC-03/14` | Task واقعی، بدون overlap، Failure visibility و تست ثبت Task؛ نه Task نمایشی |
| Restore-safe Deletion | `PRE-DEC-03/13` | Procedure/ledger مصوب، Reconciliation پس از Restore و Drill در Staging |
| Log/Queue Pruning | `PRE-DEC-03/12/14` | Retention و Redaction مصوب، Failed Job policy و Evidence عملیاتی |

## 8. بسته Evidence بیرون Repository

حداقل اقلام خارجی:

1. پذیرش Scope توسط Legal Approver؛
2. نسخه نهایی Privacy Notice و Consent با شماره نسخه؛
3. تصمیم کودک/اهلیت و Data Rights؛
4. Retention Matrix و Legal Hold Policy تصویب‌شده؛
5. Processor Register اولیه؛
6. RACI درخواست داده و مسیر Escalation؛
7. صورت‌جلسه/Sign-off با تاریخ و نتیجه.

در Repository فقط این خلاصه مجاز است:

| Evidence ID | حوزه | تاریخ | نتیجه | Owner Role | شرط باز/Due Date |
|---|---|---|---|---|---|
| `TBD` | `PRE-DEC-01` | `TBD` | `TBD` | Legal | `TBD` |
| `TBD` | `PRE-DEC-02` | `TBD` | `TBD` | Legal/Product | `TBD` |
| `TBD` | `PRE-DEC-03` | `TBD` | `TBD` | Legal/Privacy/Security/Finance | `TBD` |

## 9. Exit Gate

این بسته فقط زمانی `CLOSED` می‌شود که:

- `PRE-DEC-01..03` هرکدام نتیجه، Approver، تاریخ و External Evidence Reference معتبر داشته باشند؛
- متن نهایی و Version ID، Controller/Contact، Child/Eligibility، Marketing و Data Rights تعیین شده باشند؛
- همه ردیف‌های Retention تصمیم نهایی، Owner، Exception و رفتار پایان مدت داشته باشند؛
- Work Itemهای ناشی از تصمیم‌ها ثبت و اولویت‌بندی شده باشند؛
- هیچ Secret، PII، قرارداد یا امضای واقعی وارد Repository نشده باشد.

بسته‌شدن این سند فقط اجازه آغاز Work Itemهای کدی متناظر را می‌دهد. انتقال به سرور، ورود داده واقعی، UAT و Production Readiness همچنان به Gateهای مستقل نیاز دارند و تا وجود Evidence خارجی در وضعیت `NO-GO` می‌مانند.
