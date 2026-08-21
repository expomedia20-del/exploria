# EXPLORIA — مانور فنی محلی Incident و Scoped Pause/Resume — 2026-08-20

## 1. نتیجه و حدود ادعا

**نتیجه:** `TECHNICAL CONTROL DRILL PASS — HUMAN TABLETOP AND EXTERNAL STAGING DRILL PENDING`

این مانور فقط با داده مصنوعی و Test Environment اجرا شد. نتیجه، کنترل‌های نرم‌افزاری SAFE-05/06 را اثبات می‌کند؛ اما پاسخ‌گویی انسانی، زمان Ack، کانال On-call، هماهنگی واقعی Product/Security/Operations، Incident Register بیرونی یا رفتار زیرساخت Staging را تأیید نمی‌کند. هیچ داده واقعی، پیامک، Credential یا رخداد واقعی در این Evidence وجود ندارد.

## 2. مبنای الزامی Exploria

- FRD `PILOT-005`، Business Ruleهای Pilot و `SUPPORT-003`؛
- BRD RACI و اختیار تصمیم ادامه/توقف؛
- Pilot Charter، Gate ایمنی G4 و Launch Kit؛
- `docs/features/SAFE_05_06_SCOPED_PAUSE_RESUME_DECISION_2026-08-16.md`؛
- `docs/pilot/EXPLORIA_Pre_Staging_Governance_Approval_Pack_v1.0.md` که Tabletop پیش از Staging و Drill واقعی پیش از Pilot را لازم می‌داند.

این مانور از MSN/MSE Requirement استخراج نشده و معماری Canonical Laravel + React Monolith را تغییر نمی‌دهد.

## 3. سناریوی مصنوعی

| فیلد | مقدار مانور |
|---|---|
| Severity | P1 مصنوعی — اختلال گسترده مسیر QR و خطر ادامه Mission/صدور Reward در Campaign متاثر |
| Scope | فقط Campaign پایلوت مصنوعی؛ بدون Global Kill Switch |
| Actor توقف | Operator مصنوعی |
| Approver ازسرگیری | Admin مصنوعی |
| Incident Reference | شناسه‌های تستی `INC-2026-001` تا `INC-2026-004` |
| داده | Seeder و Factory؛ بدون PII واقعی |
| معیار خروج | Fail-Closed در Pause و Resume فقط پس از Evidence و Approval معتبر |

## 4. توالی کنترل و Evidence

| مرحله | Evidence اجرایی | نتیجه |
|---|---|---|
| ثبت Pause ناقص | درخواست بدون Reason/Incident Reference با 422 رد شد | PASS |
| Pause دامنه‌دار | Operator فقط Campaign متاثر را متوقف کرد؛ Status به `inactive` و Scope به `campaign` تغییر کرد | PASS |
| Actor و زمان | `paused_by_user_id` و `paused_at` در Metadata ثبت شدند | PASS |
| هماهنگی QR | QR متصل بدون بازنویسی وضعیت مستقل، در Landing با 404 بسته شد | PASS |
| هماهنگی Mission | شروع Progress موجود هنگام Pause با Validation fail-closed رد شد | PASS |
| هماهنگی Reward | صدور Reward جدید هنگام Pause رد شد و هیچ `user_reward` ساخته نشد | PASS |
| Readiness | وجود Campaign متوقف، Gate `operational_pause` را Fail کرد | PASS |
| جلوگیری از دورزدن | General Edit و Campaign Builder نتوانستند Campaign را فعال کنند | PASS |
| تفکیک اختیار | Resume توسط Operator با 403 رد شد | PASS |
| Evidence اجباری | Resume بدون Corrective Action، Recovery Evidence، Approval Note و تأیید صریح با 422 رد شد | PASS |
| Incident linkage | Resume با Incident Reference ناهماهنگ رد شد | PASS |
| Resume کنترل‌شده | Admin با Incident همسان، اقدام اصلاحی، شاهد بازیابی و تأیید صریح Campaign را فعال کرد | PASS |
| Recovery | QR دوباره قابل استفاده و Gate `operational_pause` Pass شد | PASS |
| Audit Trail | رویدادهای append-only `audit.campaign_paused` و `audit.campaign_resumed` شامل Actor، Scope و Evidence ثبت شدند | PASS |

## 5. Verification Record

فرمان اجراشده:

```powershell
php artisan test tests/Feature/Campaign/CampaignOperationalControlTest.php tests/Feature/Infrastructure/ProductionReadinessTest.php
```

نتیجه: `12 Test / 94 Assertion / 0 Failure`.

زیرمجموعه اختصاصی Scoped Pause/Resume شامل `4 Test / 70 Assertion` است. فایل‌های اصلی Evidence:

- `app/Services/CampaignOperationalControlService.php`
- `app/Http/Requests/Admin/PauseCampaignRequest.php`
- `app/Http/Requests/Admin/ResumeCampaignRequest.php`
- `tests/Feature/Campaign/CampaignOperationalControlTest.php`
- `app/Services/ProductionReadinessService.php`

## 6. حداقل رکورد لازم برای مانور انسانی بعدی

Incident Register کنترل‌شده بیرون Repository باید حداقل شامل این فیلدها باشد:

| فیلد | الزام |
|---|---|
| Incident ID | قالب مصوب و غیرحساس، مانند `INC-YYYY-NNNN` |
| Severity / Detected At / Reporter | زمان و منبع کشف بدون PII |
| Scope / Owner | کوچک‌ترین دامنه متاثر و مالک پاسخ |
| Timeline / Pause Decision | Ack، Escalation، تصمیم و Actor |
| Corrective Action | اقدام انجام‌شده و Owner |
| Recovery Evidence | Smoke Test، Release/Commit و نتیجه |
| Resume Approver | Admin مجری و تأیید Incident Commander/مالک حوزه |
| Closure Date / Follow-up | زمان بستن و اقدامات پیشگیرانه |

Secret، OTP، Token، Mobile کامل، Screenshot دارای PII و Credential نباید در این رکورد وارد شوند.

## 7. Gateهای باز

- تصویب کتبی Incident Policy و RACI؛
- تعیین Security Owner؛
- معرفی نماینده نام‌دار و جانشین/On-call شرکت مدیا پارس؛
- تعیین جانشین Incident Commander؛
- انتخاب Incident Register/Ticketing کنترل‌شده بیرون Repository؛
- اجرای Tabletop انسانی با اندازه‌گیری Ack/Escalation و ثبت امضای نقش‌ها؛
- اجرای Pause/Resume واقعی روی External Staging و کنترل Queue، Cache، Session، Monitoring و Providerها؛
- اجرای Drill پیش از Pilot با Incident Commander و مالک حوزه واقعی.

این Evidence چک‌باکس‌های G3 مربوط به مانور انسانی یا Staging واقعی را تکمیل نمی‌کند. وضعیت Production همچنان `NO-GO` است.
