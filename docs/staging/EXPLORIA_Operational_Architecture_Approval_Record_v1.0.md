# EXPLORIA — رکورد تصویب معماری عملیاتی Provider-agnostic v1.0

## 1. کنترل سند

| فیلد | مقدار |
|---|---|
| نوع | Architecture Approval Record — بدون انتخاب Vendor |
| تاریخ | 2026-08-21 |
| تصمیم متناظر | `PRE-DEC-07` |
| سند تحت Review | `docs/staging/EXPLORIA_Operational_Architecture_Decision_v1.0.md` |
| مبنای Codebase | `main@3d4e145` — Merge PR #8 |
| وضعیت | `READY FOR EXTERNAL DECISION — NOT APPROVED` |
| Production/Pilot | `NO-GO` |

این رکورد برای تصمیم صریح Product، Security و Operations دربارهٔ Baseline معماری است. تصویب آن Provider، Plan، خرید، Credential، Server یا اجرای Staging را تصویب نمی‌کند. نام تماس خصوصی، امضا، قرارداد، Quote و Credential باید بیرون Repository بمانند؛ در Git فقط شناسه Evidence غیرحساس ثبت می‌شود.

## 2. نتیجه ممیزی و مرز تصمیم

| موضوع | نتیجه ممیزی | اثر بر تصمیم |
|---|---|---|
| معماری | Laravel + React Monolith حفظ شده و هیچ Microservice/Kubernetes/Repository جدید پیشنهاد نشده است. | قابل بررسی در `PRE-DEC-07` |
| Provider neutrality | Mail از SMTP استاندارد، Storage از Laravel Disk، Monitoring از خروجی استاندارد Host/Application و Runtime از Driverهای Laravel استفاده می‌کند. | Vendor باید در `PRE-DEC-08..13` جداگانه انتخاب شود. |
| Fail-closed gates | Mail محلی، Storage غیرعملیاتی، Monitoring محلی-only، Queue/Cache/Session نامعتبر و Scheduler بدون Task واقعی رد می‌شوند. | Baseline بدون Evidence خارجی Production را PASS نمی‌کند. |
| Storage | ADR قبلی Object Storage/S3 را پیش از تصمیم نیازمندی قطعی فرض کرده بود، در حالی که Service فعلی Disk `public` دارد و S3 Adapter نصب نیست. | Baseline به «Storage پایدار و خارج از Release» اصلاح شد؛ Backend و Adapter فقط پس از `PRE-DEC-11`. |
| Runtime | Database Driver برای Queue/Cache/Session پیشنهاد حداقلی است و Redis الزام نشده است. | پذیرش/رد صریح لازم؛ Dependency جدید فقط با Requirement. |
| Scheduler | systemd template موجود است ولی Task واقعی وجود ندارد. | Approval معماری مجوز Task نمایشی نیست؛ Task پس از Retention approval. |
| Exit Gate | متن قبلی، Approval ADR را به تکمیل Provider Register وابسته می‌کرد و با ترتیب Checklist دور منطقی داشت. | Baseline approval از Provider activation جدا شد. |

## 3. تصمیم‌های مستقل Baseline

هر ردیف باید مستقل تصمیم‌گیری شود. پذیرش کلی بدون تعیین ردیف‌های Conditional یا Rejected معتبر نیست.

| ID | Baseline مورد تصمیم | گزینه‌های معتبر | Product | Security | Operations | شرط/دلیل |
|---|---|---|---|---|---|---|
| `ADR-CORE-01` | جداسازی Staging/Production در Server، DB، Credential، Storage، Mail و Alert | Approve / Conditional / Reject | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `TBD` |
| `ADR-CORE-02` | Secret خارج Git و اتصال بیرونی از طریق Laravel Adapter یا Host Agent | Approve / Conditional / Reject | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `TBD` |
| `ADR-MAIL-01` | SMTP استاندارد Laravel با TLS؛ Transport محلی در Staging ممنوع | Approve / Conditional / Reject | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `TBD` |
| `ADR-STORAGE-01` | Storage پایدار، خارج از Release و Private-by-default؛ Backend در `PRE-DEC-11` | Approve / Conditional / Reject | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `TBD` |
| `ADR-MON-01` | خروجی استاندارد stderr/syslog + Host Agent و Probe مستقل؛ بدون SDK Vendor در Domain | Approve / Conditional / Reject | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `TBD` |
| `ADR-BACKUP-01` | Backup منطقی Off-host مستقل با Integrity/Encryption/Lifecycle و Restore Drill | Approve / Conditional / Reject | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `TBD` |
| `ADR-RUNTIME-01` | Database Driver حداقلی Queue/Cache/Session و Scheduler systemd؛ Redis فقط با نیاز اثبات‌شده | Approve / Conditional / Reject | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `TBD` |
| `ADR-SCORE-01` | Scorecard اجباری Security/Availability/Portability/Cost/Data/Support/Ops | Approve / Conditional / Reject | `DECISION REQUIRED` | `DECISION REQUIRED` | `DECISION REQUIRED` | `TBD` |

## 4. تصمیم اختصاصی Storage بدون تحمیل S3

`ADR-STORAGE-01` فقط نیاز عملیاتی را قفل می‌کند:

- داده Uploadشده نباید داخل Release غیرپایدار یا Repository نگهداری شود؛
- Application Storage و Backup Failure Domain/Access باید جدا باشند؛
- دسترسی پیش‌فرض Private، TLS، Encryption at Rest، Lifecycle/Delete و Least Privilege لازم است؛
- Backend باید از طریق Laravel Disk قابل تنظیم باشد.

انتخاب Backend در `PRE-DEC-11` یکی از این نتایج را خواهد داشت:

| گزینه | زمانی قابل قبول است | اثر کد/Dependency |
|---|---|---|
| Persistent filesystem مستقل از Release | Single-host requirement، durability، backup، access و lifecycle را اثبات کند. | Refactor حداقلی Disk؛ Adapter جدید لازم نیست. |
| Object storage | Requirement دوام/مقیاس/جداسازی یا Provider approval آن را لازم کند. | فقط پس از Approval، Adapter لازم و تست Regression/Composer audit. |
| `NOT REQUIRED` | اگر Upload واقعی از Scope حذف یا به‌طور رسمی Deferred شود. | مسیر غیرلازم باید Fail-closed/غیرفعال بماند؛ حذف Feature نیازمند Change Control است. |

هیچ‌یک از این گزینه‌ها در `PRE-DEC-07` انتخاب نمی‌شوند و S3-compatible بودن به‌تنهایی Requirement محسوب نمی‌شود.

## 5. شروطی که Approval Baseline را متوقف نمی‌کنند

موارد زیر بعد از تصویب Baseline ولی قبل از خرید/Provisioning/Activation باید در تصمیم‌های جداگانه بسته شوند:

- Hosting/PostgreSQL: `PRE-DEC-08`؛
- OTP: `PRE-DEC-09`؛
- Mail: `PRE-DEC-10`؛
- Storage backend: `PRE-DEC-11`؛
- Monitoring/Logging/Uptime: `PRE-DEC-12`؛
- Backup destination: `PRE-DEC-13`؛
- Runtime/Retention task: `PRE-DEC-14`؛
- Budget/Quote: `PRE-DEC-15`؛
- Domain/role mailboxes: `PRE-DEC-16`.

بازبودن این ردیف‌ها مانع Review معماری نیست، اما هرگونه اجرا، خرید یا ورود Credential را متوقف نگه می‌دارد.

## 6. قواعد نتیجه

| نتیجه | شرط معتبر |
|---|---|
| `Approved` | هر هشت ردیف Product/Security/Operations را دارد و هیچ شرط باز ندارد. |
| `Approved with conditions` | هر شرط Owner، Due Date و محدوده دقیق دارد و شرط، Security/Secret/Backup fail-closed را دور نمی‌زند. |
| `Rejected` | ردیف ردشده، دلیل و Change Request/مسیر جایگزین مشخص دارد. |

سکوت، رأی ناقص یا صرفاً تکمیل‌کردن نام‌ها Approval نیست. اگر یکی از Approverهای سه‌گانه ردیفی را Reject کند، وضعیت کل ADR تا حل تعارض `REJECTED/PENDING CHANGE` می‌ماند.

## 7. رکورد Sign-off بیرونی

| فیلد | مقدار |
|---|---|
| ADR Version/Commit reviewed | `DECISION REQUIRED` |
| Product decision/date | `DECISION REQUIRED` |
| Security decision/date | `DECISION REQUIRED` |
| Operations decision/date | `DECISION REQUIRED` |
| External Evidence Reference | `DECISION REQUIRED — NON-SENSITIVE ID ONLY` |
| Conditions/Owner/Due Date | `DECISION REQUIRED` |
| نتیجه نهایی | `Approved` / `Approved with conditions` / `Rejected` |

## 8. Exit Gate `PRE-DEC-07`

`PRE-DEC-07` فقط زمانی بسته است که:

1. همه ردیف‌های بخش 3 نتیجه صریح سه Approver را داشته باشند؛
2. شرط‌ها Owner و Due Date داشته باشند؛
3. Storage backend و Vendor به تصمیم‌های بعدی واگذار شده و پیشاپیش فرض نشده باشند؛
4. External Evidence Reference غیرحساس ثبت شود؛
5. ADR و Approval Pack نتیجه یکسان نشان دهند.

بسته‌شدن `PRE-DEC-07` فقط Baseline معماری را قفل می‌کند. Provider selection، Dependency، Config، Provisioning، Evidence عملیاتی، UAT و Production Readiness همچنان جدا و `NO-GO` هستند.
