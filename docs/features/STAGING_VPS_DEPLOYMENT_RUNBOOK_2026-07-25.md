# EXPLORIA — Runbook استقرار Staging روی VPS

تاریخ: ۱۴۰۵/۰۵/۰۳ — 2026-07-25

## تصمیم اجرایی

نسخه فعلی ابتدا روی یک محیط مستقل با نام `staging` مستقر می‌شود. این محیط برای UAT نقش‌ها، آزمون میدانی QR و پایلوت محدود است و نباید با داده یا Secret محیط Production مشترک باشد.

استقرار مستقیم روی Production تا عبور کامل از Gateهای زیر مجاز نیست:

```bash
php artisan exploria:production-readiness --json
php artisan exploria:demo-readiness --json
```

## ورودی‌های بیرونی موردنیاز

این مقادیر نباید در Git ثبت شوند:

- نشانی SSH و نام کاربر VPS؛
- دامنه Staging و دسترسی DNS؛
- گواهی معتبر TLS؛
- Credential مستقل PostgreSQL؛
- Endpoint و Token سرویس واقعی OTP؛
- مسیر امن Backup خارج از Repository؛
- تصمیم دسترسی عمومی، VPN، IP Allowlist یا Basic Auth برای Staging.

تا زمانی که این ورودی‌ها در اختیار اپراتور قرار نگرفته‌اند، اجرای اتصال و استقرار واقعی متوقف می‌ماند؛ آدرس، Token یا Credential ساختگی قابل قبول نیست.

## خط مبنای سرور

- Linux پایدار و به‌روز؛
- Nginx؛
- PHP 8.4 FPM و افزونه‌های لازم Laravel شامل `pdo_pgsql`؛
- Composer 2؛
- Node.js 22 و npm؛
- PostgreSQL و ابزارهای `psql`، `pg_dump` و `pg_restore`؛
- Git، curl و tar؛
- کاربر غیر root با نام پیشنهادی `exploria`.

اسکریپت استقرار عمداً اجرای مستقیم با کاربر root را رد می‌کند.

## ساختار پیشنهادی

```text
/var/www/exploria-staging/
├── repository/       # Clone کنترل‌شده Git
├── shared/
│   ├── .env          # Secretهای Staging؛ خارج از Git
│   └── storage/      # فایل‌های پایدار بین Releaseها
├── releases/         # نسخه‌های immutable
└── current           # Symlink نسخه فعال
```

ایجاد اولیه این پوشه‌ها توسط ادمین سرور انجام و مالکیت آنها به کاربر `exploria` واگذار می‌شود. مسیر استقرار به‌صورت ایمنی باید دقیقاً به `/exploria-staging` ختم شود.

## آماده‌سازی Environment

فایل `.env.staging.example` فقط الگو است. نسخه واقعی باید در مسیر زیر ساخته شود:

```text
/var/www/exploria-staging/shared/.env
```

حداقل مقادیر واقعی:

```dotenv
APP_ENV=staging
APP_DEBUG=false
APP_URL=https://staging.example.com
APP_KEY=

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=exploria_staging
DB_USERNAME=
DB_PASSWORD=

QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true

OTP_DRIVER=http
OTP_HTTP_ENDPOINT=
OTP_HTTP_TOKEN=
```

`APP_KEY` فقط یک‌بار در محیط Staging تولید و خارج از Repository نگهداری می‌شود. Production باید کلید و Credential مستقل داشته باشد.

## Backup اجباری پیش از Migration

```bash
cd /var/www/exploria-staging/repository

export EXPLORIA_PG_DATABASE='exploria_staging'
export EXPLORIA_PG_USERNAME='...'
export EXPLORIA_PG_PASSWORD='...'
export EXPLORIA_BACKUP_DIRECTORY='/srv/secure-backups/exploria'

backup_path="$(./scripts/backup-postgresql.sh)"
test -f "$backup_path.sha256"
pg_restore --list "$backup_path" >/dev/null
```

استقرار بدون Archive معتبر و جدید یا بدون Manifest معتبر SHA-256 رد می‌شود. اسکریپت Deploy پیش از Migration نام فایل و Hash واقعی را با Manifest تطبیق می‌دهد. حداکثر عمر پیش‌فرض Backup برای استقرار ۱۴۴۰ دقیقه است.

## اجرای استقرار اتمیک Staging

Repository سرور باید تمیز و Ref موردنظر قبلاً به GitHub پوش شده باشد:

```bash
cd /var/www/exploria-staging/repository

export EXPLORIA_DEPLOY_ROOT='/var/www/exploria-staging'
export EXPLORIA_DEPLOY_REF='origin/staging'
export EXPLORIA_HEALTH_URL='https://staging.example.com/up'
export EXPLORIA_VERIFIED_BACKUP_PATH="$backup_path"

./scripts/deploy-staging.sh
```

اسکریپت این کنترل‌ها را به‌ترتیب انجام می‌دهد:

1. رد اجرای root، مسیر گسترده، URL بدون HTTPS، Repository کثیف و Backup فاقد Manifest/Hash معتبر؛
2. Fetch و استخراج دقیق Commit انتخاب‌شده در یک Release جدید؛
3. اتصال `.env` و `storage` مشترک؛
4. نصب وابستگی Production و Build رابط؛
5. Maintenance کوتاه برای نسخه قبلی؛
6. Migration اجباری فقط پس از Backup؛
7. اجرای Production Readiness و Demo Readiness؛
8. جابه‌جایی اتمیک Symlink `current`؛
9. Restart صف و خروج از Maintenance؛
10. کنترل HTTPS مسیر `/up`.

در صورت خطا، نسخه قبلی تا حد ممکن دوباره فعال می‌شود و Release ناموفق برای بررسی باقی می‌ماند. Migration خودکار Rollback نمی‌شود؛ بنابراین Migrationهای انتشار باید backward-compatible باشند.

## Nginx و TLS

الگوی پایه:

```text
deploy/nginx/exploria-staging.conf.example
```

پیش از فعال‌سازی:

- `staging.example.com` با دامنه واقعی جایگزین شود؛
- مسیر PHP-FPM با نسخه نصب‌شده تطبیق داده شود؛
- Certificate معتبر صادر شود؛
- `nginx -t` موفق باشد؛
- سیاست محدودسازی دسترسی Staging اعمال شود.

هدر `X-Robots-Tag` در الگو از Index شدن محیط آزمایشی جلوگیری می‌کند، اما جای کنترل دسترسی را نمی‌گیرد.

## Queue و Scheduler

الگوها:

```text
deploy/systemd/exploria-staging-queue.service.example
deploy/systemd/exploria-staging-scheduler.service.example
deploy/systemd/exploria-staging-scheduler.timer.example
```

پس از تطبیق User و مسیر:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now exploria-staging-queue.service
sudo systemctl enable --now exploria-staging-scheduler.timer
sudo systemctl status exploria-staging-queue.service
sudo systemctl status exploria-staging-scheduler.timer
```

## کنترل بعد از استقرار

```bash
curl --fail --show-error https://staging.example.com/up
php artisan exploria:production-readiness --json
php artisan exploria:demo-readiness --json
php artisan migrate:status
php artisan queue:monitor database:default --max=100
```

سپس UAT با حساب‌های نقش‌محور و آزمون واقعی ۶ QR روی حداقل دو موبایل انجام می‌شود. داده Staging باید آزمایشی و قابل تشخیص باشد.

## معیار Go/No-Go برای Production

- Production Readiness بدون Fail؛
- Demo Readiness برابر ۱۹ از ۱۹؛
- QR میدانی برابر ۶ از ۶؛
- تست کامل PHP و Frontend سبز؛
- Backup جدید و Restore Test موفق؛
- عدم وجود اشکال P0/P1؛
- تایید متن رضایت‌نامه و سیاست نگهداری داده؛
- ثبت نتیجه UAT نقش‌ها و پایلوت محدود؛
- تهیه برنامه Rollback، مانیتورینگ و پشتیبانی روز افتتاح.

درگاه پرداخت، POS، تسویه بانکی و فاکتور رسمی خارج از این استقرار Staging هستند و نیازمند تصمیم و Change Request جداگانه‌اند.
