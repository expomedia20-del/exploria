# EXPLORIA — بسته تصمیم عملیات، امنیت و Incident پیش از انتقال v1.0

## 1. کنترل سند

| فیلد | مقدار |
|---|---|
| نوع | Working Decision Pack — غیرجایگزین تصویب Operations/Security/Legal |
| تاریخ | 2026-08-21 |
| دامنه | `PRE-DEC-04`، `PRE-DEC-05` و `PRE-DEC-06` |
| مبنای Codebase | `main@9abc321` — Merge PR #7 |
| Gate | پیش از Provisioning، انتقال Release یا ورود Credential واقعی |
| وضعیت | `AWAITING OPERATIONS/SECURITY APPROVAL — NO SERVER TRANSFER` |
| Production/Pilot | `NO-GO` |

این سند واقعیت‌های Codebase، تصمیم‌های انسانی لازم و Work Itemهای مشروط را از یکدیگر جدا می‌کند. نام و کانال تماس خصوصی، Password، Recovery Code، Token، SSH Key، `.env` واقعی، قرارداد و ضمیمه Incident نباید در Repository ثبت شوند. در Git فقط نقش، وضعیت و شناسه Evidence غیرحساس مجاز است.

## 2. نتیجه ممیزی خواندنی

| حوزه | کنترل مشاهده‌شده | Gap پیش از انتقال |
|---|---|---|
| RACI | Product، Finance، Privacy و Incident Commander موقت در Owner Record مشخص شده‌اند؛ شرکت Operations در سطح سازمان معرفی شده است. | Tech Lead، Security Owner، نماینده نام‌دار Operations، جانشین‌ها، On-call و پذیرش مکتوب وجود ندارند. |
| Incident | Pause/Resume در سطح Campaign، Incident Reference، Recovery Evidence، تفکیک Operator/Admin و Audit در Local تست شده‌اند. | Severity/SLA، Incident Register، Escalation، ارتباطات، Human Tabletop و امضای نقش‌ها باز هستند. |
| Secrets | `.env` واقعی Git-ignore است؛ `.env.staging.example` Credentialها را خالی می‌گذارد؛ Deploy بدون `APP_KEY`، DB/OTP/Mail و Evidence معتبر Fail می‌شود. | Secret Store/Password Manager، Account Owner، MFA، Recovery، Rotation، Revocation و Access Review تصویب نشده‌اند. |
| Host/Deploy | اسکریپت Deploy اجرای root را رد می‌کند، `umask 027` دارد و Release تمیز/Backup معتبر را الزام می‌کند؛ Backup script سطح دسترسی `600` اعمال می‌کند. | هویت Deploy، SSH/Sudo policy، Key lifecycle، جداسازی وظایف و Break-glass Owner تعیین نشده‌اند. |
| Application Auth | Role middleware، Access Scope، Audit، Password Reset، 2FA و Passkey capability وجود دارند. | MFA برای نقش‌های privileged اجباری نیست؛ Account lifecycle و دوره Access Review مصوب نیست. |
| Revocation | Scopeها را می‌توان غیرفعال و Audit کرد. | `deactivateAccess` فقط Scopeها را غیرفعال می‌کند؛ فیلد Account Disabled وجود ندارد و Roleهای `admin/operator/viewer` در `UserAccessScopeService` دسترسی Global می‌گیرند. پس این عمل معادل قطع ورود یا Revocation کامل نیست. |
| Account onboarding | اکانت مدیریتی با Password تصادفی ۴۸کاراکتری ساخته می‌شود و کاربر باید از Password Reset رمز را تعیین کند. | اثبات مالکیت Mailbox، مهلت فعال‌سازی، الزام MFA، تحویل امن و لغو اکانت استفاده‌نشده تعریف نشده است. |
| CI/Repository | Workflowهای GitHub فقط `contents: read` دارند و Secret واقعی در الگوها وجود ندارد. | Branch/Repository owners، MFA، Recovery و دوره بازبینی دسترسی GitHub باید بیرون Codebase تصویب شوند. |
| Incident content | Reason، Incident Reference، Corrective Action، Recovery Evidence و Approval Note در Metadata/Event Log ثبت می‌شوند. | این فیلدها آزادند و Redaction خودکار ندارند؛ Content Hygiene و Retention مصوب لازم است. |

نتیجه: کنترل‌های نرم‌افزاری محلی کافی برای ادعای آمادگی عملیاتی نیستند. هیچ‌یک از سه Gate با این ممیزی `PASS` نمی‌شود.

## 3. فرم بستن `PRE-DEC-04` — RACI فنی و عملیاتی

### 3.1 نقش‌های لازم پیش از انتقال

| نقش | مسئولیت Accountable | وضعیت فعلی | خروجی لازم |
|---|---|---|---|
| Product Owner | Scope، Budget و Go/No-Go | Assigned/Accepted در Owner Record | External reference و جانشین حسب سیاست |
| Incident Commander پیش از Staging | فرمان Incident و Pause/Resume انسانی | Primary موقت پذیرفته شده | کانال On-call و Escalation؛ جانشین پیش از Pilot |
| Tech Lead/Release Owner | Commit/Release approval، Migration، Rollback و Recovery فنی | `TBD` | فرد نام‌دار، پذیرش Scope و جانشین |
| Security Owner/Approver | Secret، Access Review، Incident امنیتی و Risk Acceptance | `TBD — BLOCKER` | فرد واجد صلاحیت، استقلال تصمیم و جانشین |
| Operations Representative | Host، DB، Queue، Scheduler، Monitoring و Backup | Organization assigned | نماینده نام‌دار، پذیرش مکتوب، On-call و جانشین |
| DBA/Backup Restore Owner | Database change، Backup verification و Restore authorization | در Organization ادغام‌شده ولی نامشخص | Owner صریح و جانشین؛ می‌تواند همان Operations Representative باشد اگر تصویب شود |
| Privacy/Data Owner | Data Request و Privacy Incident coordination | Assigned؛ Legal review باز | اتصال به RACI Incident و کانال رسمی |
| Finance/Commercial | خرید و Provider account ownership | Assigned/Accepted | Approver خرید و Billing escalation |

نقش‌های QA/UAT، Venue/Field و Support Pilot طبق Checklist فعلاً Deferred هستند؛ فقط اگر یک فرد از آنها برای عملیات پیش از انتقال لازم اعلام شود وارد Scope این Gate می‌شوند.

### 3.2 ماتریس تصمیم RACI

در نسخه بیرونی، برای هر ردیف دقیقاً یک `A` و حداقل یک `R` تعیین شود.

| فعالیت | A | R | C | I | جانشین/After-hours |
|---|---|---|---|---|---|
| Release approval و تعیین Commit | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` |
| Deploy/Rollback authorization | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` |
| Migration و Database recovery | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` |
| Secret/Access grant و revoke | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` |
| Backup verification و Restore approval | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` |
| Alert triage و Incident declaration | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` |
| Campaign Pause/Resume | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` |
| Security/Privacy escalation | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` |

### 3.3 رکورد پذیرش

| فیلد | مقدار |
|---|---|
| RACI Version | `DECISION REQUIRED` |
| Product approval/date | `DECISION REQUIRED` |
| Operations acceptance/date | `DECISION REQUIRED` |
| Security acceptance/date | `DECISION REQUIRED` |
| External Evidence Reference | `DECISION REQUIRED — NON-SENSITIVE ID ONLY` |
| شروط، Owner و Due Date | `DECISION REQUIRED` |

## 4. فرم بستن `PRE-DEC-05` — Incident Policy و Human Tabletop

### 4.1 تصمیم‌های Policy

| ID | موضوع | تصمیم لازم |
|---|---|---|
| `INC-01` | Severity | تعریف P0 تا P3 و مثال‌های امنیت، داده، مالی و مسیر حیاتی |
| `INC-02` | Ack/Escalation | زمان هدف، کانال اول/دوم و رفتار عدم پاسخ |
| `INC-03` | Declaration | چه نقشی Incident را اعلام، Severity را تغییر یا Scope را گسترش می‌دهد |
| `INC-04` | Pause | اختیار Operator/Admin/Incident Commander و کوچک‌ترین Scope امن |
| `INC-05` | Resume | Recovery Evidence، Approverهای لازم و Separation of Duties |
| `INC-06` | Register | محل کنترل‌شده، Access، Audit، Attachment policy و Retention |
| `INC-07` | Communication | چه کسی Product، Security، Privacy، Provider و ذی‌نفعان را مطلع می‌کند |
| `INC-08` | Evidence hygiene | ممنوعیت Secret/PII و قواعد Redaction برای متن‌های آزاد |
| `INC-09` | Closure | معیار Closure، Follow-up Owner و Risk Acceptance باقی‌مانده |
| `INC-10` | Business continuity | رفتار در نبود Provider/On-call، قطع Mail/OTP یا از دسترس‌رفتن Admin UI |

زمان‌های فعلی P0 تا P3 در Approval Pack فقط `PROPOSED` هستند و تا Sign-off، SLA محسوب نمی‌شوند.

### 4.2 محدوده کنترل نرم‌افزاری فعلی

- Pause/Resume فعلی فقط Campaign را پوشش می‌دهد و Global Kill Switch ایجاد نشده است.
- Resume نرم‌افزاری توسط Admin انجام می‌شود؛ این Role نرم‌افزاری به‌تنهایی معادل Incident Commander یا تأیید انسانی نیست.
- Queue، Cache، Session، Provider و زیرساخت Staging در مانور Local اثبات نشده‌اند.
- اگر Policy به Scope یا اختیار جدید نیاز داشته باشد، Work Item کدی مستقل و تست‌پذیر لازم است؛ این بسته آن را پیشاپیش پیاده‌سازی نمی‌کند.

### 4.3 Human Tabletop پیش از انتقال

این مانور بدون سرور واقعی قابل اجرا و برای اثبات پذیرش نقش‌ها لازم است:

1. یک سناریوی مصنوعی P1 و یک سناریوی امنیت/Privacy انتخاب شود؛
2. زمان Detect، Ack، Escalation و تصمیم Pause ثبت شود؛
3. نبود Primary و فعال‌شدن جانشین تمرین شود؛
4. Incident Register بیرونی با داده غیرواقعی تکمیل شود؛
5. شرط Resume، Recovery Evidence و Approverها مرور شوند؛
6. Gapها با Owner و Due Date ثبت شوند.

| Evidence field | مقدار |
|---|---|
| Tabletop Reference/Date | `DECISION REQUIRED` |
| Participants by Role | `DECISION REQUIRED — NO PRIVATE CONTACTS` |
| Scenario IDs | `DECISION REQUIRED` |
| Ack/Escalation result | `DECISION REQUIRED` |
| Open actions/owners/dates | `DECISION REQUIRED` |
| Sign-off result | `Approved` / `Approved with conditions` / `Rejected` |

مانور واقعی روی External Staging همچنان Deferred و پیش از UAT/Pilot الزامی است.

## 5. فرم بستن `PRE-DEC-06` — Secret و Access Governance

### 5.1 Secret/Account Inventory بدون مقدار محرمانه

| دسته | نمونه | Owner | جانشین | محل نگهداری | MFA/Recovery | Rotation/Revocation trigger |
|---|---|---|---|---|---|---|
| Source control | GitHub organization/repository | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` |
| Host access | SSH/Sudo/Recovery console | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` |
| Laravel | `APP_KEY` و environment config | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | N/A/Access control | `DECISION REQUIRED` |
| Database | User/Password/Certificate | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` |
| OTP/Mail | API token/SMTP credential | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` |
| Monitoring/Logging | Agent/API/Webhook credentials | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` |
| Backup | Encryption key/restore account | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` |
| DNS/Role mailboxes | Registrar/DNS/Mail admin | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` |
| Device/API access | Display/API tokens حسب Scope واقعی | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | حسب نوع | `DECISION REQUIRED` |
| Break-glass | حساب/کلید اضطراری محدود | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | Recovery sealed | استفاده، پایان Incident یا تغییر Custodian |

این جدول نام Provider یا ابزار خاصی را تحمیل نمی‌کند. ابزار منتخب باید کنترل دسترسی، Audit، Export، Recovery و Revocation موردنیاز را اثبات کند.

### 5.2 تصمیم‌های چرخه دسترسی

| موضوع | خروجی لازم |
|---|---|
| Joiner | Approver، Role/Scope حداقلی، اثبات مالکیت Mailbox و فعال‌سازی زمان‌دار |
| Mover | بازبینی دسترسی قبلی پیش از اعطای Role/Scope جدید |
| Leaver/Incident revoke | قطع Login، Session، Scope، Provider/API/SSH و Recovery access با Evidence |
| Privileged MFA | نقش‌های مشمول، روش‌های مجاز، Recovery و Exception process |
| Shared accounts | ممنوعیت یا Exception مستند با Custodian/Audit/Rotation |
| Access review | Owner، تناوب مصوب، دامنه و Evidence غیرحساس |
| Secret rotation | رویدادها/تناوب مصوب، Dual-control و Rollback |
| Break-glass | Custodian، شرط استفاده، Alert، بازبینی و Rotation پس از استفاده |
| Environment separation | Credential مستقل Staging/Production و ممنوعیت Copy/Re-use |
| Secure transfer | کانال مجاز برای bootstrap؛ ممنوعیت Email/Ticket/Git عمومی |

### 5.3 رکورد تصمیم

| فیلد | مقدار |
|---|---|
| Secret/Access Policy Version | `DECISION REQUIRED` |
| Security Approver/Date | `DECISION REQUIRED` |
| Operations Approver/Date | `DECISION REQUIRED` |
| Product/Risk acceptance حسب Exception | `DECISION REQUIRED` |
| External Evidence Reference | `DECISION REQUIRED — NON-SENSITIVE ID ONLY` |
| نتیجه و شروط | `DECISION REQUIRED` |

## 6. نقشه Decision به Work Item

| Work Item | پیش‌نیاز | Acceptance حداقلی |
|---|---|---|
| Full account disable/revoke | `PRE-DEC-04/06` | وضعیت Account مصوب، قطع Login/Session و Role/Scope، حفاظت از آخرین Admin، Audit و تست‌های Authorization |
| Privileged MFA enforcement | `PRE-DEC-06` | نقش‌های مصوب، Recovery/exception fail-closed و تست Login/step-up |
| Account onboarding expiry | `PRE-DEC-06` | اثبات Mailbox، مهلت فعال‌سازی، Account unused cleanup و Audit |
| Incident content validation/redaction | `PRE-DEC-05/03` | قواعد مصوب برای فیلدهای آزاد، عدم ثبت Secret/PII و تست Redaction/Validation |
| Access review evidence/export | `PRE-DEC-04/06` | گزارش Role/Scope/last review بدون Secret، Owner و Sign-off |
| Incident scope extension | `PRE-DEC-05` | فقط اگر Policy نیاز جدید اثبات کند؛ Authorization و Recovery test |
| External secret injection/runbook | `PRE-DEC-06` | بدون Secret در Git، least privilege، file permission، rotation/revoke drill |

هیچ Package، SDK، Secret Manager، Ticketing Provider یا معماری جدید بدون Requirement مصوب اضافه نمی‌شود.

## 7. Evidence بیرون Repository و Exit Gate

حداقل Evidence خارجی:

1. RACI پذیرفته‌شده با Primary، جانشین و On-call؛
2. پذیرش Tech Lead، Security Owner و Operations Representative؛
3. Incident Policy و Incident Register decision؛
4. نتیجه Human Tabletop و Action register؛
5. Secret/Account Inventory بدون درج Secret در Git؛
6. MFA، Recovery، Rotation، Revocation و Access Review policy؛
7. نتیجه Sign-off و شروط با Owner/Due Date.

| Evidence ID | حوزه | تاریخ | نتیجه | Owner Role | شرط باز/Due Date |
|---|---|---|---|---|---|
| `TBD` | `PRE-DEC-04` | `TBD` | `TBD` | Product/Operations/Security | `TBD` |
| `TBD` | `PRE-DEC-05` | `TBD` | `TBD` | Incident Commander/Operations/Security/Legal | `TBD` |
| `TBD` | `PRE-DEC-06` | `TBD` | `TBD` | Security/Operations | `TBD` |

این بسته فقط وقتی `CLOSED` است که هر سه ردیف Approver، تاریخ، نتیجه و External Evidence Reference معتبر داشته باشند و Work Itemهای کدی لازم ثبت شده باشند. بسته‌شدن آن مجوز ورود Secret واقعی به Git یا عبور Production Readiness نیست. انتقال، Staging drill، UAT و Production تا Gateهای مستقل همچنان `NO-GO` هستند.
