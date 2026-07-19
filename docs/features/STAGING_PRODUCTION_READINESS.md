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
- Provider واقعی و غیرمحلی OTP؛
- Queue و Cache پایدار؛
- Session روی database یا redis؛
- Cookieهای `Secure` و `HttpOnly`؛
- Log Channel فعال.

این Gate وجود Domain، Credential، Provider یا متن حقوقی نهایی را جعل نمی‌کند. مقادیر واقعی باید در سامانه مدیریت Secret محیط استقرار ثبت شوند و نباید وارد Git شوند.
