# EXPLORIA — بسته تصمیم و تصویب حاکمیت پیش از Staging v1.0

## 1. کنترل سند

| فیلد | مقدار |
|---|---|
| نوع سند | Working Approval Pack — غیرجایگزین مشاوره و تأیید حقوقی |
| تاریخ | 2026-08-16؛ آخرین بازبینی 2026-08-17 |
| دامنه | Privacy، Retention/Deletion، Incident، RACI، Budget و Provider Approval |
| وضعیت | `LEGAL/OPERATIONS ROLES ASSIGNED — WRITTEN SIGN-OFF/SECURITY/VENDOR GATES PENDING` |
| Gate | پیش از خرید Staging، ورود داده واقعی، UAT رسمی یا Pilot |

این سند تصمیم‌های پیشنهادی محافظه‌کارانه، شواهد Repository و فیلدهای امضای لازم را در یک محل جمع می‌کند. وجود این فایل به معنی تأیید Product، Legal، Security، Operations یا Finance نیست. نام، تاریخ و مرجع مصوبه واقعی باید تکمیل شود؛ Secret، قرارداد، شماره تماس خصوصی و امضای اسکن‌شده نباید در Repository ذخیره شوند.

رجیستر تجمیعی TBDهای مسدودکننده، Gate هر تصمیم و تفکیک موارد فعال پیش از انتقال از اقدامات بعد از Staging در `docs/pilot/EXPLORIA_Pre_Server_Decision_Checklist_v1.0.md` نگهداری می‌شود.

Data Inventory اولیه، فرم‌های Sign-off و نقشه Work Itemهای مشروط برای `PRE-DEC-01..03` در `docs/pilot/EXPLORIA_Legal_Privacy_Retention_Decision_Pack_v1.0.md` نگهداری می‌شود. این بسته فقط ورودی تصمیم را آماده می‌کند و تا ثبت Evidence بیرونی هیچ ردیفی را Approved نمی‌کند.

توصیه فنی Provider-agnostic برای Mail، Storage، Monitoring، Backup و Runtime در `docs/staging/EXPLORIA_Operational_Architecture_Decision_v1.0.md` ثبت شده و تا تکمیل همین Approval Pack در وضعیت `DRAFT RECOMMENDED` باقی می‌ماند.

Shortlist تاریخ‌دار و Compatibility Gap ارائه‌دهندگان در `docs/staging/EXPLORIA_Provider_Shortlist_2026-08-16.md` ثبت شده است. Liara Stack، Kavenegar و IPPanel صرفاً Candidate هستند؛ هیچ Provider تصویب یا خریداری نشده است.

تصمیم‌های قطعی مالک و پیشنهادهایی که هنوز نیازمند پذیرش هستند در `docs/pilot/EXPLORIA_Owner_Decision_Record_2026-08-17.md` از یکدیگر تفکیک شده‌اند. ایمیل شخصی اعلام‌شده عمداً در Repository ثبت نشده است.

## 2. مراجع Canonical و شواهد فعلی

- BRD: `docs/governance/product/Exploria_BRD_v1.1_Pilot_Revenue_Update.md`؛ مسئولیت داده، RACI و تصمیم ادامه/توقف.
- FRD: `docs/governance/product/Exploria_FRD_v1.1_Pilot_Operations_Update.md`؛ `AUTH-002`، `DATA-004`، `DATA-005` و `SUPPORT-003`.
- Decision Register: `docs/governance/governance/22-EXPLORIA_Open_Decisions_Lock_Register_v1.0.md`؛ `OD-001`، `OD-002` و `OD-009`.
- Pilot Charter: `docs/pilot/EXPLORIA_EcoPark_Pilot_Charter_v0.1.md`؛ بخش‌های 7، 8.3، 9 و Gateهای G0/G1.
- Launch Kit: `docs/pilot/EXPLORIA_Stage_5_Controlled_Pilot_Launch_Kit_v1.0.md`؛ Gateهای حقوقی، تماس، Incident و Go/No-Go.
- Consent فنی نسخه‌دار و قابل ردیابی است: `database/migrations/2026_06_21_000003_create_consent_versions_table.php`، `database/migrations/2026_06_21_000004_create_consent_logs_table.php` و `app/Actions/Consent/AcceptConsentAction.php`.
- متن فعال Seed با نسخه `pilot-fa-0.1` صریحاً Demo و غیرنهایی است: `database/seeders/ConsentVersionSeeder.php`.
- Mobile در `User` و `OtpRequest` رمزگذاری و Hash مستقل نگهداری می‌شود: `app/Models/User.php` و `app/Models/OtpRequest.php`.
- Scan/Event فقط Hash نشست، IP و User-Agent را نگهداری می‌کند: `app/Actions/Events/RecordQrScanEventAction.php` و Migration مربوط به `scan_events`/`event_log`.
- حذف حساب خودکار Laravel موجود است، اما حذف/ناشناس‌سازی همه روابط Pilot با داده واقعی هنوز Drill و Evidence ندارد: `app/Http/Controllers/Settings/ProfileController.php` و `tests/Feature/Settings/ProfileUpdateTest.php`.
- ممیزی رابطه‌ها نشان می‌دهد `consent_logs.user_id` حذف User را محدود می‌کند، در حالی که روابط دیگر ترکیبی از Cascade و Null-on-delete هستند؛ تست حذف فعلی فقط User بدون سابقه عملیاتی را پوشش می‌دهد.
- `marketing_leads.mobile` در Model فعلی رمزگذاری یا Hidden نشده است؛ استفاده واقعی آن تا تصمیم Notice/Consent، دسترسی و Retention باز می‌ماند.
- اجرای `php artisan schedule:list` در 2026-08-21 هیچ Task واقعی نشان نداد؛ Retention/Prune هنوز نباید بدون تصویب ماتریس ساخته شود.
- Scoped Pause/Resume با Incident Reference و Audit محلی وجود دارد: `app/Services/CampaignOperationalControlService.php` و `docs/features/SAFE_05_06_SCOPED_PAUSE_RESUME_DECISION_2026-08-16.md`.
- Entity/Workflow عمومی Incident در Codebase وجود ندارد؛ برای Pilot حداقل امن، Incident Register کنترل‌شده بیرون سامانه مرجع است و شناسه آن در Pause/Resume ثبت می‌شود.

## 3. تصمیم‌های پیشنهادی Privacy و Consent

| ID | تصمیم پیشنهادی | وضعیت | تأیید لازم |
|---|---|---|---|
| GOV-PRIV-01 | ورود داده واقعی فقط پس از انتشار یک `ConsentVersion` جدید با `is_demo=false`؛ نسخه Demo نباید بازنویسی یا به‌عنوان متن حقوقی استفاده شود. | Proposed | Product + Legal |
| GOV-PRIV-02 | جمع‌آوری فقط برای ورود امن، اجرای تجربه، انتساب رویداد، پاداش، پشتیبانی و امنیت؛ استفاده تبلیغاتی/فروش Lead نیازمند رضایت جداگانه و اختیاری است. | Proposed | Product + Legal |
| GOV-PRIV-03 | Pilot تا تصویب سازوکار والد/سرپرست و سیاست داده کودک، فقط برای افراد واجد اهلیت اعلام‌شده اجرا شود؛ هیچ ادعای سنی خودکار از روی داده انجام نشود. | Proposed Safe Default | Product + Legal + Venue |
| GOV-PRIV-04 | Privacy Notice باید هویت دقیق متولی/کنترل‌کننده پردازش را، اعم از شخص حقیقی یا حقوقی، همراه هدف‌ها، دسته داده، دریافت‌کنندگان/پردازشگران، مدت نگهداری، حقوق کاربر، مسیر تماس و اثر انصراف روشن کند. اشخاص موضوع داده حقوق قانونی خود را حفظ می‌کنند. | Proposed | Legal |
| GOV-PRIV-05 | فهرست Providerها و محل پردازش/Backup باید پیش از فعال‌سازی در Processor Register تصویب شود؛ Credential خارج Repository می‌ماند. | Proposed | Legal + Security + Operations |
| GOV-PRIV-06 | داده Demo، Test و UAT از Production/Pilot جدا باشد و ورود PII واقعی در Screenshot، Ticket عمومی، Git و Log ممنوع بماند. | Technical Baseline | Security + QA |

### Gapهای واقعی Privacy

1. مالک پلتفرم به‌عنوان شخص حقیقی معرفی شده، اما عنوان حقوقی دقیق Controller/Processor، متن نهایی Privacy، نشانی رسمی دامنه‌ای و نسخه انتشار واقعی هنوز تأیید حقوقی نشده است.
2. رضایت مستقل Marketing/Lead و مسیر Withdraw اثبات نشده است.
3. سیاست کودک/اهلیت و روش اجرای آن تصویب نشده است.
4. Processor Register برای OTP، Mail، Storage، Monitoring، Hosting و Backup وجود ندارد.
5. Data Subject Request برای مشاهده، اصلاح، حذف یا محدودسازی، Owner/SLA و Evidence اجرایی ندارد.

## 4. ماتریس پیشنهادی Retention و Deletion

اعداد زیر «حداکثر پیشنهادی برای تصویب» هستند، نه تعهد حقوقی. Legal/Finance می‌تواند به علت الزام قانونی یا قرارداد، مدت را کم یا زیاد کند. در صورت Legal Hold، حذف فقط با مرجع مکتوب متوقف می‌شود.

| دسته داده | نمونه Repository | مدت پیشنهادی | اقدام پایان مدت | وضعیت |
|---|---|---:|---|---|
| OTP Request شامل Mobile رمزگذاری‌شده و Hash/Code Hash | `otp_requests` | 30 روز از Request | حذف امن رکورد | Proposed |
| Session و Password Reset | `sessions`، `password_reset_tokens` | 30 روز عدم فعالیت/انقضا | Prune خودکار | Proposed |
| حساب Visitor و Mobile رمزگذاری‌شده | `users` | تا پایان رابطه؛ حداکثر 30 روز پس از درخواست معتبر حذف | حذف یا ناشناس‌سازی کنترل‌شده روابط | Proposed |
| Consent Evidence | `consent_logs` و نسخه متن | 5 سال پس از آخرین تعامل یا طبق نظر Legal | حفظ نسخه/زمان/Subject حداقلی؛ بدون Mobile خام | Proposed — Legal Review |
| Scan، Visit، Mission و KPI عملیاتی | `scan_events`، `visits`، progress | 180 روز پس از پایان Pilot | ناشناس‌سازی یا حذف؛ نگهداری Aggregate غیرقابل بازشناسایی | Proposed |
| Reward/Redemption/Financial Evidence | `user_rewards`، redemption، ledger | 7 سال یا مدت مصوب Finance/Legal | آرشیو محدود و سپس حذف | Proposed — Finance/Legal Review |
| Audit و Security Event | `event_log` و Application/Security Log | 365 روز | حذف زمان‌بندی‌شده؛ P0 تحت Legal Hold مستثنا | Proposed |
| Incident Record | Incident Register بیرونی + Reference | 3 سال پس از Closure | حذف/آرشیو طبق Legal Hold | Proposed |
| Marketing Lead | `marketing_leads` | 90 روز بدون تبدیل یا بلافاصله پس از Withdraw | حذف یا ناشناس‌سازی | Proposed |
| Backup رمزگذاری‌شده | مقصد بیرون Repository | Daily: 30 روز؛ Weekly: 12 هفته؛ Monthly: 12 ماه | Expiry خودکار و ثبت نتیجه | Proposed |
| Evidence UAT/Pilot | مخزن مجاز بدون PII | 12 ماه پس از Go/No-Go | بازبینی و حذف | Proposed |

### مسیر حداقلی درخواست حذف

1. ثبت Request با شناسه غیرحساس و زمان.
2. احراز هویت متناسب بدون دریافت بیش از نیاز.
3. بررسی Legal Hold، تعهد مالی و رخداد امنیتی باز.
4. حذف/ناشناس‌سازی رکوردهای قابل حذف و قطع دسترسی فعال حداکثر در SLA مصوب پیشنهادی 30 روز.
5. ثبت Evidence بدون PII و اعلام نتیجه به درخواست‌کننده.
6. حذف از Backup در چرخه Expiry؛ Restore بعدی نباید داده حذف‌شده را دوباره فعال کند.

**Gap اجرایی:** حذف ساده حساب وجود دارد، اما Jobهای Retention، Data Inventory کامل، Anonymization وابستگی‌ها، Legal Hold و Restore-safe deletion پیاده‌سازی/آزمایش نشده‌اند. پس از تصویب این ماتریس، اجرای فنی آن یک Work Item مستقل پیش از ورود داده واقعی است.

## 5. Incident Policy پیشنهادی

| سطح | نمونه | Ack هدف | اقدام اولیه | اختیار Pause/Resume |
|---|---|---:|---|---|
| P0 | نشت/دسترسی غیرمجاز، فساد یا ازبین‌رفتن داده، خطر ایمنی، صدور مالی گسترده نادرست | فوری؛ حداکثر 5 دقیقه | توقف Scope متاثر، ایزوله‌سازی، حفظ Evidence و تماس با Incident Commander/Privacy | Admin/Operator می‌تواند Pause کند؛ Resume فقط Admin با تأیید Incident Commander و مالک حوزه |
| P1 | شکست مسیر حیاتی، OTP/QR/Reward گسترده یا مغایرت جدی موجودی | 15 دقیقه | Pause Scope متاثر، Triage و اطلاع Operations/Product | همان کنترل SAFE-05/06 |
| P2 | اختلال محدود با Workaround امن | 60 دقیقه | Ticket، Owner و موعد اصلاح | Pause با تصمیم Operations |
| P3 | نقص کم‌اثر/درخواست پشتیبانی | یک روز کاری | Backlog و پیگیری عادی | معمولاً بدون Pause |

هر Incident حداقل باید `incident_id`، Severity، زمان کشف، Reporter، Scope، شرح بدون PII، Owner، Timeline، تصمیم Pause، Corrective Action، Recovery Evidence، Resume Approver و Closure Date داشته باشد. قالب شناسه پیشنهادی `INC-YYYY-NNNN` است.

### قواعد غیرقابل اغماض

- P0 مستقل از KPI، نتیجه را `PAUSE/NO-GO` می‌کند.
- هیچ OTP، Token، Mobile کامل یا داده حساس در Incident/Ticket عمومی ثبت نشود.
- Resume بدون اقدام اصلاحی، Smoke Test/Recovery Evidence و تأیید مالک حوزه ممنوع است.
- تا انتخاب Ticketing Provider، یک Incident Register کنترل‌شده با دسترسی محدود خارج Repository قابل قبول است؛ Spreadsheet عمومی یا پیام‌رسان شخصی Evidence رسمی نیست.
- مانور Tabletop پیش از Staging و مانور واقعی Pause/Resume روی Staging پیش از Pilot الزامی است.

مانور فنی خودکار با داده مصنوعی در 2026-08-20 انجام و در `docs/staging/EXPLORIA_Incident_Tabletop_Local_Rehearsal_2026-08-20.md` ثبت شد. این Evidence فقط کنترل نرم‌افزاری را اثبات می‌کند و جایگزین Tabletop انسانی، سنجش زمان Ack/Escalation، امضای RACI یا Drill واقعی External Staging نیست.

## 6. RACI پیشنهادی و فیلدهای نام‌گذاری

| نقش | Accountable/اختیار اصلی | نام واقعی | جانشین | کانال On-call | وضعیت |
|---|---|---|---|---|---|
| Product Owner | Scope، KPI، Budget و Go/No-Go | علی رحمان سلیمانی‌زاده | `TBD` | خارج Repository | Owner accepted — 2026-08-18 |
| Incident Commander / Pilot Manager | فرمان Incident و ادامه/توقف | علی رحمان سلیمانی‌زاده — فقط موقت برای Pre-Staging | `TBD` | خارج Repository | Owner accepted; alternate required before Pilot |
| Tech Lead | Code/Deploy/Recovery فنی | `TBD` | `TBD` | خارج Repository | Pending |
| Privacy/Data Accountable Owner | هدف پردازش، Data Request و پاسخ‌گویی کسب‌وکار | علی رحمان سلیمانی‌زاده — مالک شخص حقیقی | `TBD` | نشانی شخصی خارج Repository | Owner-confirmed; independent Legal review pending |
| Legal Approver | متن Consent/Privacy، Retention، حقوق کاربر و قرارداد پردازشگر | آقای سیفی — وکیل دادگستری، معرفی‌شده توسط مالک | `TBD` | خارج Repository | Role assigned; written acceptance/sign-off pending |
| Security Owner | Incident امنیتی، Secret، Access Review و Risk Acceptance فنی | `TBD — متخصص مستقل` | `TBD` | خارج Repository | Blocker before Staging activation |
| Operations/Infrastructure | Server، DB، Queue، Backup و Monitoring | شرکت مدیا پارس — معرفی‌شده توسط مالک | `TBD` | خارج Repository | Organization assigned; named representative/on-call/sign-off pending |
| QA/UAT Lead | Evidence، Regression و UAT Sign-off | `TBD` | `TBD` | خارج Repository | Pending |
| Finance/Commercial | بودجه، Provider Contract و Reward Liability | علی رحمان سلیمانی‌زاده — پروژه خودتأمین | `TBD` | خارج Repository | Owner accepted — caps approved with conditions |
| Venue/Field Lead | ایمنی، نصب QR و عملیات محل | `TBD` | `TBD` | خارج Repository | Pending |
| Support Lead | Triage، شکایت و ارتباط کاربر | `TBD` | `TBD` | خارج Repository | Pending |

یک فرد می‌تواند در Pilot کوچک بیش از یک نقش داشته باشد، اما برای P0 حداقل Incident Commander و مالک فنی باید دو مسیر تماس مستقل و جانشین مشخص داشته باشند. Admin نرم‌افزار به‌تنهایی معادل Incident Commander یا Legal Approver نیست.

## 7. Budget Approval Register

| سرفصل | سقف/واحد | مالک تأیید | Evidence لازم | وضعیت |
|---|---|---|---|---|
| Staging Compute/PostgreSQL/IPv4 | `25,000,000 IRR/month` | Product + Finance | Quote/Invoice و دوره پرداخت | Approved with conditions — 2026-08-18 |
| OTP/SMS | `2,000 OTP/month` و `10,000,000 IRR/month`؛ هرکدام زودتر | Product + Commercial | تعرفه، SLA، Sender و Cost Alert | Approved with conditions — 2026-08-18 |
| Mail/Storage/Monitoring/Backup | `25,000,000 IRR/month` تجمیعی | Product + Operations + Finance | Quote، Region، Retention، Alert و Restore | Approved with conditions — 2026-08-18 |
| کل هزینه تکرارشونده Staging | `60,000,000 IRR/month` سقف سخت | Product + Finance | Cost Alert در 50/75/90 درصد و توقف خرید در 100 درصد | Approved with conditions — 2026-08-18 |
| Reward/Redemption در Staging/UAT | `0 IRR` تعهد واقعی؛ فقط داده و Reward غیرواقعی | Product + Finance/Commercial | جداسازی Test Data و عدم صدور تعهد واقعی | Approved safe default — 2026-08-18 |
| Reward/Redemption در Pilot | فرمول: کاربران واجد شرایط × سقف هر کاربر × دفعات مجاز + ذخیره احتیاطی؛ مبلغ هنوز `TBD` | Product + Finance/Commercial | مالک هزینه، موجودی، انقضا و Liability | Blocker before Pilot, not before technical Staging |
| Field/QR/Equipment/Contingency | `TBD` | Pilot Manager + Venue | Quote، تعداد، مسئول تحویل و سقف اضطراری | Blocker before Pilot |

تا سقف کل، سقف روزانه OTP، سقف Reward هر کاربر و اختیار مصرف اضطراری تصویب نشده باشد، خرید Provider یا ورود Reward واقعی مجاز نیست.

## 8. Provider Approval Register

| Capability | Provider/Plan واقعی | معیار اجباری | Data/Region | DPA/Contract | Owner | وضعیت |
|---|---|---|---|---|---|---|
| Independent Staging | Liara VPS/IaaS — Candidate پذیرفته‌شده برای Due-diligence، نه خرید | Linux، SSH محدود، Snapshot، SLA و جداسازی Production | Iran-only required; transfer conflict pending | `TBD` | Operations | Conditional hold — written answers required |
| PostgreSQL | Liara DBaaS PostgreSQL — Candidate پذیرفته‌شده برای Due-diligence | Backup/PITR، TLS، Credential مستقل و Restore Test | Iran-only required; transfer conflict pending | `TBD` | DBA/Operations | Conditional hold — written answers required |
| OTP/SMS | Kavenegar Sandbox/Quote — Candidate اول پذیرفته‌شده؛ IPPanel مقایسه دوم | HTTPS API، Sender مجاز، Delivery Report، Rate/Cost Cap، Retention و SLA | Iran-only required; public evidence pending | `TBD` | Product/Commercial | Sandbox/quote accepted; account/send pending |
| Transactional Mail | `TBD` | SPF/DKIM/DMARC، Bounce Handling، TLS و Suppression | `TBD` | `TBD` | Operations | Pending |
| Object Storage | `TBD` | S3-compatible یا Adapter مصوب، Encryption، Private Default و Lifecycle | `TBD` | `TBD` | Operations/Security | Pending |
| Central Logging/Monitoring | `TBD` | Laravel/System Metrics، PII Redaction، Alerting و Export | `TBD` | `TBD` | Security/Operations | Pending |
| Backup Destination | `TBD` | حساب/فضای مستقل، Encryption، Immutability/Lifecycle و Restore Access | `TBD` | `TBD` | Operations/DBA | Pending |
| Incident/Ticketing | `TBD` | Access Control، Timeline، Attachment Policy و Audit | `TBD` | `TBD` | Support/Security | Pending |

هیچ Provider صرفاً بر اساس قیمت انتخاب نمی‌شود. Data location، مالک حساب سازمانی، خروج داده، حذف حساب، Portability، SLA، وضعیت مالیاتی/قراردادی و مسیر Incident باید پیش از ثبت `Approved` بررسی شود.

## 9. Approval Record

| حوزه | تصمیم/نسخه | Approver واقعی | تاریخ | مرجع مصوبه بیرونی | نتیجه |
|---|---|---|---|---|---|
| Product Scope و Privacy Purpose | v1.0 | علی رحمان سلیمانی‌زاده — Product Owner | 2026-08-18 | Owner Decision Record | Approved with Legal conditions |
| Legal: Consent/Privacy/Child/Data Rights | v1.0 | آقای سیفی — وکیل دادگستری، معرفی‌شده توسط مالک | `TBD` | Engagement/Sign-off خارج Repository | Role assigned; decision pending |
| Retention/Deletion/Legal Hold | v1.0 | `TBD` | `TBD` | `TBD` | Pending |
| Incident Policy و RACI | v1.0 | Product Owner + شرکت مدیا پارس + Security Approver | `TBD` | Operations acceptance خارج Repository | Operations organization assigned; representatives/sign-off pending |
| Budget | v1.0 | علی رحمان سلیمانی‌زاده — Finance Approver | 2026-08-18 | Owner Decision Record | Approved with conditions; Pilot Reward TBD |
| Provider Shortlist/Contracts | v1.0 | Product + Finance + Operations/Security | 2026-08-18 | Provider Shortlist + Due-diligence | Candidates accepted; vendors/contracts not approved |
| Operational Architecture ADR | v1.0 | `TBD` Product/Security/Operations | `TBD` | `docs/staging/EXPLORIA_Operational_Architecture_Decision_v1.0.md` | Draft Recommended |

نتیجه هر ردیف فقط با یکی از `Approved`، `Approved with conditions` یا `Rejected` ثبت می‌شود. شرط باید Owner و Due Date داشته باشد. نقص Privacy، داده کودک، P0 Incident Owner، Backup یا Secret Management قابل `Approved with conditions` برای Pilot عمومی نیست.

## 10. Exit Gate گام 3 Roadmap

گام 3 فقط وقتی `PASS` است که:

- همه شش حوزه Approval Record دارای Approver، تاریخ، مرجع و نتیجه معتبر باشند.
- متن Consent/Privacy نهایی و Data Controller/Contact مشخص باشد.
- Retention Matrix و Data Request SLA تصویب شده و Work Itemهای اجرایی آن ثبت شده باشند.
- RACI با نام و جانشین و کانال امن تماس کامل باشد.
- سقف کل Budget، OTP و Reward تصویب شده باشد.
- Providerهای منتخب یا Shortlist مصوب با Owner و سقف هزینه مشخص باشند.
- هیچ Secret، قرارداد محرمانه یا PII در Git قرار نگرفته باشد.

**نتیجه فعلی:** `NO-GO — LEGAL/OPERATIONS ROLES ASSIGNED; WRITTEN APPROVALS, SECURITY AND VENDOR GATES PENDING`
