# EXPLORIA — Gate آمادگی Staging و Production

## هدف

پیش از هر استقرار، دستور زیر باید اجرا شود:

```bash
php artisan exploria:production-readiness
```

خروجی ماشینی:

```bash
php artisan exploria:production-readiness --json
```

دستور در صورت وجود هر تنظیم ناامن با Exit Code خطا متوقف می‌شود.

## کنترل‌های اجباری

- `APP_ENV` فقط `staging` یا `production`؛
- `APP_DEBUG=false`؛
- وجود `APP_KEY` در Environment؛
- `APP_URL` با HTTPS؛
- `DB_CONNECTION=pgsql`؛
- اتصال واقعی PostgreSQL و نبود Migration معوق؛
- Provider واقعی و غیرمحلی OTP که عملاً در Container برنامه Bind شده باشد؛ نام دلخواه در `OTP_DRIVER` کافی نیست؛
- Mailer ثبت‌شده و واقعی؛ `log`، `array` و Failover دارای fallback محلی رد می‌شوند؛
- Disk مورد استفاده Upload با Probe واقعی Write/Read/Delete؛
- Log Sink عملیاتی؛ Stack صرفاً محلی مانند `single`/`daily` برای Monitoring کافی نیست؛
- Queue ثبت‌شده با Driver پایدار، Backend در دسترس و Failed Job Store؛
- Cache ثبت‌شده، غیر `array`/`null` و دارای Round-trip موفق؛
- Session روی database یا redis با Backend در دسترس؛
- Cookieهای `Secure` و `HttpOnly`؛
- Scheduler دارای حداقل یک Task واقعی مصوب؛
- بسته Evidence عملیاتی تازه و بیرون Repository برای Mail، Storage، Monitoring، Queue، Cache، Session و Scheduler.

## بسته Evidence عملیاتی

مسیر فایل با `EXPLORIA_OPERATIONAL_EVIDENCE_PATH` تعیین می‌شود و باید Absolute، خوانا و بیرون Repository باشد. سن پیش‌فرض Evidence حداکثر ۲۴ ساعت است و با `EXPLORIA_OPERATIONAL_EVIDENCE_MAX_AGE_MINUTES` فقط بر اساس Runbook مصوب قابل تغییر است.

حداقل ساختار فایل:

```json
{
  "environment": "staging",
  "verified_at": "<ISO-8601 timestamp>",
  "checks": {
    "mail": { "status": "pass", "reference": "<non-secret evidence id>" },
    "storage": { "status": "pass", "reference": "<non-secret evidence id>" },
    "monitoring": { "status": "pass", "reference": "<non-secret evidence id>" },
    "queue": { "status": "pass", "reference": "<non-secret evidence id>" },
    "cache": { "status": "pass", "reference": "<non-secret evidence id>" },
    "session": { "status": "pass", "reference": "<non-secret evidence id>" },
    "scheduler": { "status": "pass", "reference": "<non-secret evidence id>" }
  }
}
```

این فایل Evidence واقعی تولید نمی‌کند؛ فقط نتیجه Drill بیرونی را به‌صورت Fail-Closed وارد Gate می‌کند. Reference نباید Token، Credential، PII، مقدار Placeholder یا URL دارای Secret باشد.

این Gate وجود Domain، Credential، Provider یا متن حقوقی نهایی را جعل نمی‌کند. مقادیر واقعی باید در سامانه مدیریت Secret محیط استقرار ثبت شوند و نباید وارد Git شوند.

تا زمانی که Evidence بیرونی و عملیات واقعی Staging ثبت نشده باشد، نتیجه Production همچنان `NO-GO` است.
