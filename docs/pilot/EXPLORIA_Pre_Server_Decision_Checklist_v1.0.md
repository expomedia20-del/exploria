# EXPLORIA — چک‌لیست تصمیم‌های مسدودکننده پیش از انتقال به سرور v1.0

## 1. کنترل سند

| فیلد | مقدار |
|---|---|
| نوع | Working Decision Checklist — غیرجایگزین اسناد Canonical یا Approval رسمی |
| تاریخ | 2026-08-21 |
| مبنای Codebase | `main@3e0dce9` — Merge PR #5، PR #6 و Quality Gate dependency update |
| مبنای تطبیق اسناد | `0d20d95` — Post-PR5 readiness reconciliation |
| Scope فعال | فقط تصمیم‌هایی که پیش از خرید، Provisioning، انتقال Release یا ورود داده واقعی باید بسته شوند |
| خارج از Scope فعال | اجرای فنی روی سرور، Drillهای Staging، UAT واقعی و عملیات Pilot |
| وضعیت | `OPEN — NO PURCHASE / NO REAL DATA / NO SERVER TRANSFER` |
| Production/Pilot | `NO-GO` |

این سند همه TBDهای مسدودکننده را در یک رجیستر عملیاتی جمع می‌کند. وجود ردیف یا علامت‌گذاری آن به معنی Approval نیست. نام تماس خصوصی، Credential، Token، قرارداد محرمانه، امضای اسکن‌شده یا PII نباید در Repository ثبت شود؛ فقط شناسه Evidence غیرحساس مجاز است.

بستهٔ اجرایی جمع‌آوری تصمیم و Evidence برای سه ردیف نخست در `docs/pilot/EXPLORIA_Legal_Privacy_Retention_Decision_Pack_v1.0.md` آماده شده است. آماده‌بودن فرم‌ها وضعیت `PRE-DEC-01..03` را تغییر نمی‌دهد؛ این ردیف‌ها تا Sign-off بیرونی همچنان باز هستند.

## 2. Gate و قواعد بستن تصمیم

| Gate | زمان الزام | وضعیت در برنامه فعلی |
|---|---|---|
| `D0` | پیش از هر تغییر کد یا Dependency وابسته به Provider/Policy | فعال |
| `D1` | پیش از خرید، Provisioning یا انتقال Release به سرور | فعال |
| `D2` | پس از ایجاد Staging ولی پیش از ورود داده واقعی، UAT رسمی یا Pilot | ثبت‌شده ولی خارج از دستور کار فعلی |

هر تصمیم فقط وقتی `CLOSED` است که این پنج مورد وجود داشته باشد:

1. تصمیم صریح و بدون Placeholder؛
2. Approver واجد اختیار؛
3. تاریخ و نتیجه `Approved`، `Approved with conditions` یا `Rejected`؛
4. شناسه مرجع بیرونی غیرحساس برای Sign-off، Quote یا Contract؛
5. تعیین اثر تصمیم بر Code، Config، Runbook و Owner اقدام بعدی.

## 3. تصمیم‌های فعال پیش از انتقال

### 3.1 حقوقی، داده و مالکیت عملیات

| ID | Gate | وضعیت | تصمیم مسدودکننده | وضعیت فعلی | برای بسته‌شدن | Approver لازم | مرجع |
|---|---|---|---|---|---|---|---|
| `PRE-DEC-01` | D1 | `PARTIAL` | پذیرش کتبی نقش Legal Approver | نقش معرفی شده و فرم پذیرش آماده است؛ پذیرش/Sign-off مکتوب Pending است | Engagement Reference غیرحساس، تاریخ پذیرش و محدوده Review | Legal + Product | Legal/Privacy/Retention Pack §4؛ Approval Pack §6/§9؛ Owner Record §3/§8 |
| `PRE-DEC-02` | D0/D1 | `OPEN` | متن نهایی Privacy، Consent، سیاست کودک/اهلیت و Data Rights | ممیزی داده و فرم تصمیم آماده است؛ متن Seed فقط Demo است و تصمیم حقوقی نهایی وجود ندارد | نسخه مصوب، Data Controller و Contact رسمی، هدف‌ها، Processorها، Withdraw و سیاست کودک | Legal + Product + Venue/Security حسب موضوع | Legal/Privacy/Retention Pack §3/§5؛ Approval Pack §3/§9؛ `OD-002`؛ Pilot Charter `PO-PILOT-09` |
| `PRE-DEC-03` | D0/D1 | `OPEN` | Retention، Deletion، Legal Hold و SLA درخواست داده | Data Inventory اولیه و فرم تصمیم آماده است؛ ماتریس فقط Proposed است و Job/Anonymization/Restore-safe deletion وجود ندارد | تصویب مدت‌ها و Exceptions، Owner/SLA و تعیین Work Itemهای فنی | Legal + Privacy Owner + Security + Finance حسب داده | Legal/Privacy/Retention Pack §3/§6/§7؛ Approval Pack §4/§9 |
| `PRE-DEC-04` | D1 | `PARTIAL` | RACI فنی و عملیاتی پیش از Handoff | Product/Finance/Privacy Owner مشخص‌اند؛ Tech Lead و Security Owner نام‌دار نیستند؛ مدیا پارس نماینده و On-call نام‌دار ندارد | Tech Lead، Security Approver، نماینده Operations، جانشین‌ها و کانال امن تماس خارج Repository | Product + Operations + Security | Approval Pack §6؛ Owner Record §3 |
| `PRE-DEC-05` | D1 | `PARTIAL` | Incident Policy، اختیار Pause/Resume و Incident Register | کنترل نرم‌افزاری محلی موجود است؛ Policy/RACI انسانی و Register رسمی تصویب نشده‌اند | Severity/SLA، Incident Commander، جانشین، Register کنترل‌شده، Escalation و Sign-off | Product + Operations + Security + Legal | Approval Pack §5/§9 |
| `PRE-DEC-06` | D1 | `OPEN` | Secret و Access Governance | ممنوعیت Secret در Git قفل است؛ Secret Store/Password Manager، MFA، Recovery، Rotation و Revocation عملیاتی تعیین نشده‌اند | ابزار/فرآیند مصوب، Account Owner، جانشین، MFA، Rotation/Revocation و Access Review | Security + Operations | Owner Record §4؛ Due-diligence §3/§4 |

### 3.2 معماری عملیاتی، Provider و بودجه

| ID | Gate | وضعیت | تصمیم مسدودکننده | وضعیت فعلی | برای بسته‌شدن | Approver لازم | مرجع |
|---|---|---|---|---|---|---|---|
| `PRE-DEC-07` | D0/D1 | `OPEN` | تصویب ADR عملیاتی Provider-agnostic | `DRAFT RECOMMENDED` | نتیجه رسمی Product/Security/Operations و ثبت شروط/ردها | Product + Security + Operations | Operational ADR §1/§11؛ Approval Pack §9 |
| `PRE-DEC-08` | D1 | `CONDITIONAL HOLD` | Hosting و PostgreSQL مستقل | Liara فقط Candidate و `CONDITIONAL HOLD` است؛ تعهد Iran-only، DPA، MFA/Audit، PITR/RPO/RTO و Export پاسخ مکتوب ندارد | انتخاب یا رد Candidate، Quote، Region قراردادی، جداسازی محیط، Owner و Exit Plan | Product + Finance + Legal + Security + Operations/DBA | Due-diligence §3/§8؛ Approval Pack §8 |
| `PRE-DEC-09` | D0/D1 | `PENDING DUE-DILIGENCE` | OTP Provider و مدل Integration | Kavenegar Candidate اول است؛ Retention/DPA/MFA/Cost Cap و سازگاری Adapter باز است | Provider/Plan، پاسخ‌های قراردادی، Owner، سقف هزینه و تصمیم صریح درباره Adapter اختصاصی | Product + Commercial/Finance + Legal + Security + Tech | Due-diligence §4/§7؛ Owner Record §7 |
| `PRE-DEC-10` | D0/D1 | `OPEN` | Transactional Mail و Domain/Stream | SMTP در Laravel موجود و Gate Fail-Closed است؛ Provider/Plan/Domain/DPA مشخص نیست | Provider/Plan، Owner، Domain/From/Return-Path، TLS، SPF/DKIM/DMARC، Bounce/Suppression و Budget | Product + Operations + Legal/Security | Approval Pack §8؛ Operational ADR `ADR-MAIL-01` |
| `PRE-DEC-11` | D0/D1 | `OPEN` | سیاست Application Storage | Disk `public` در سرویس تبلیغات ثابت است؛ Object Storage و Adapter تصویب نشده‌اند | تصمیم صریح درباره Storage موردنیاز، Public/Private policy، Lifecycle/Deletion و فقط در صورت نیاز، مجوز Refactor/Dependency | Product + Operations + Security + Tech | Operational ADR `ADR-STORAGE-01`؛ Due-diligence §5 |
| `PRE-DEC-12` | D1 | `OPEN` | Central Logging، Monitoring، Uptime و Alert Ownership | Gate Repository آماده است؛ Provider/Agent/Retention/Alert Channel/On-call و Probe مستقل TBD هستند | مقصد و Agent، Redaction، Retention، Export، Alert Channel، Owner/جانشین و Uptime Provider مستقل ایرانی | Security + Operations + Legal | Operational ADR `ADR-MON-01`؛ Owner Record §7 |
| `PRE-DEC-13` | D1 | `OPEN` | مقصد Backup مستقل | Manifest SHA-256 و Restore Guard موجود است؛ مقصد Off-host مستقل، Encryption و Lifecycle TBD هستند | Provider/Account دوم ایرانی، Failure Domain مستقل، Encryption، Retention، Restore Access و Owner | Operations/DBA + Security + Legal/Finance | Operational ADR `ADR-BACKUP-01`؛ Owner Record §7 |
| `PRE-DEC-14` | D0/D1 | `OPEN` | Queue/Cache/Session/Scheduler baseline | PostgreSQL Driver پیشنهاد ADR است؛ Redis الزام نشده؛ Retention Task واقعی Scheduler هنوز تصویب نشده است | تصویب یا رد Database baseline، سیاست Prune/Retention و نام Task واقعی؛ هر Dependency دیگر فقط با Requirement مصوب | Product + Tech + Operations + Legal برای Retention | Operational ADR `ADR-RUNTIME-01`؛ Staging Readiness §4.7 |
| `PRE-DEC-15` | D1 | `PARTIAL` | مجوز مالی خرید داخل سقف‌های مصوب | سقف‌های Staging `Approved with conditions` هستند؛ Quote/Invoice و Vendor Approval خرید وجود ندارد | Quote تازه، Invoice/Tax، تطبیق با سقف، Billing Alert و Approver خرید مشخص | Product + Finance + Operations | Approval Pack §7؛ Owner Record §6 |
| `PRE-DEC-16` | D1 | `OPEN` | Domain و مالکیت کانال‌های رسمی | Domain نهایی و Ownerهای DNS، `privacy@` و `security@` تعیین نشده‌اند | نام Domain، مالک DNS، دسترسی جانشین و مالک Mailboxهای نقش‌محور؛ فعال‌سازی فنی بعداً انجام می‌شود | Product + Operations + Legal/Security | `OD-005`؛ Owner Record §4/§8 |

## 4. نقشه تصمیم به Work Item کد

تا بسته‌شدن تصمیم متناظر، این تغییرات نباید اجرا شوند:

| تصمیم | Work Item احتمالی پس از Approval | قاعده محدودکننده |
|---|---|---|
| `PRE-DEC-02` | افزودن ConsentVersion نهایی با `is_demo=false` و Flow مصوب Withdraw | نسخه Demo بازنویسی نشود |
| `PRE-DEC-03` و `PRE-DEC-14` | Prune/Retention Job، Scheduler Task واقعی، Anonymization و Restore-safe deletion | Job نمایشی یا مدت ساختگی اضافه نشود |
| `PRE-DEC-09` | Adapter واقعی OTP و تست Redaction/Timeout/خطا | Provider در Domain Logic Hardcode نشود |
| `PRE-DEC-11` | Disk قابل‌تنظیم media و Adapter/Dependency لازم | S3/Dependency بدون Requirement مصوب اضافه نشود |
| `PRE-DEC-10` | فقط Config/Runbook یا تغییر حداقلی اثبات‌شده Mail | Secret و Credential وارد Git نشود |
| `PRE-DEC-12` | Structured logging/Redaction یا Config لازم برای Agent مصوب | SDK Vendor بدون ضرورت وارد Monolith نشود |

## 5. TBDهای ثبت‌شده ولی خارج از دستور کار فعلی

موارد زیر پیش از UAT/Pilot واقعی مسدودکننده‌اند، اما چون می‌توانند بعد از ایجاد Staging و پیش از اجرای عمومی بسته شوند، فعلاً اقدام فعال محسوب نمی‌شوند:

| ID/گروه | تصمیم باقی‌مانده | Gate نهایی | وضعیت فعلی |
|---|---|---|---|
| `PO-PILOT-01..05` | تأیید Venue، دوره ۱۴روزه، کمپین/سناریو، مسیر شش QR و Target/وزن KPI | پیش از G2/G3 | `DEFERRED — OUT OF CURRENT WORK PLAN` |
| `PO-PILOT-06` | سقف کل Pilot، Reward هر کاربر، موجودی، انقضا و Liability | پیش از Pilot | `DEFERRED — OUT OF CURRENT WORK PLAN` |
| `PO-PILOT-07` | Pilot Manager، QA/UAT Lead و مالکان کامل RACI Pilot | پیش از UAT/Pilot | `DEFERRED — OUT OF CURRENT WORK PLAN` |
| `PO-PILOT-08` | Venue Host، حداقل سه شریک واقعی و قرارداد/Brand Approval | پیش از Pilot | `DEFERRED — OUT OF CURRENT WORK PLAN` |
| Field Budget | سفیران، چاپ/استند/QR، نمایشگر، اینترنت/برق، تجهیزات و Contingency | پیش از Pilot | `DEFERRED — OUT OF CURRENT WORK PLAN` |
| `OD-003/OD-006` | ابعاد/چاپ نهایی QR و سیاست ثبت دستی در اتصال ضعیف | پیش از نصب/Pilot | `DEFERRED — OUT OF CURRENT WORK PLAN` |
| Pilot Calendar | تاریخ دقیق Baseline، Dry Run، Live و Close-out | پس از G1/G2 و پیش از شروع | `DEFERRED — OUT OF CURRENT WORK PLAN` |
| UAT Inputs | حساب‌های نقش‌محور، شماره‌های مجاز، داده UAT و Sign-off Lead | پیش از UAT رسمی | `DEFERRED — OUT OF CURRENT WORK PLAN` |

`PO-PILOT-09` در `PRE-DEC-02/03` جذب شده است، زیرا تصمیم Privacy/Consent/Child/Retention می‌تواند Work Item کد ایجاد کند و نباید تا بعد از انتقال عقب بیفتد. `PO-PILOT-10` یک Gate Rule است و تا عبور G0 تا G3 در وضعیت `NO-GO` باقی می‌ماند؛ تصمیم فنی جدید تولید نمی‌کند.

## 6. اقدامات فنی خارج از این چک‌لیست

این موارد Decision TBD نیستند و مطابق دستور مالک فعلاً از برنامه فعال خارج‌اند:

- Provisioning سرور Linux، SSH و PostgreSQL؛
- فعال‌سازی DNS/TLS و ساخت `.env` واقعی؛
- ورود Secretها و اجرای Deploy/Migration؛
- فعال‌سازی Queue Worker و Scheduler روی systemd؛
- E2E واقعی OTP، Mail، Storage، Queue، Cache، Session و Scheduler؛
- نصب Agent مانیتورینگ، Uptime Probe و آزمون Alert؛
- Backup/Restore Drill خارجی؛
- تولید Operational Evidence و عبور Production Readiness؛
- UAT روی URL واقعی و تست میدانی QR.

## 7. ترتیب بستن تصمیم‌های فعال

1. `PRE-DEC-01..03`: حقوقی، Consent/Privacy و Retention؛
2. `PRE-DEC-04..06`: RACI، Security/Secret و Incident؛
3. `PRE-DEC-07`: تصویب ADR عملیاتی؛
4. `PRE-DEC-08..16`: Provider، Runtime، Domain و مجوز مالی؛
5. ایجاد Work Itemهای محدود بخش 4 فقط برای تصمیم‌های `CLOSED`؛
6. به‌روزرسانی Approval Pack و Status Register با Evidence Referenceهای غیرحساس؛
7. ساخت Release Candidate و Handoff Pack در Work Item مستقل.

## 8. Exit Gate

وضعیت این Checklist فقط وقتی `PRE-SERVER DECISIONS CLOSED` می‌شود که:

- همه ردیف‌های `PRE-DEC-01..16` بسته یا صریحاً با دلیل مصوب `NOT REQUIRED` شده باشند؛
- هیچ شرط باز بدون Owner و Due Date باقی نماند؛
- Work Itemهای کدی ناشی از تصمیم‌ها مشخص و تست‌پذیر باشند؛
- هیچ Secret، PII یا سند محرمانه وارد Git نشده باشد؛
- Approval Pack و ADR نتیجه نهایی یکسان داشته باشند.

بسته‌شدن این Checklist فقط مجوز ساخت Release Candidate/Handoff است و جایگزین Staging، Evidence عملیاتی، UAT یا Go-Live Approval نیست. Production/Pilot تا عبور Gateهای خارجی همچنان `NO-GO` است.
