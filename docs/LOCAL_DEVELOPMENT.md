# EXPLORIA Local Development

This note records the reproducible local setup used for the recovered EXPLORIA codebase.

## Toolchain

Recommended local paths on the current machine:

```powershell
$env:PATH='E:\فایل 1 اصلی پروژه اکسپلوریا\فایل های تکمیلی قبل از کدنویسی\.toolchain\node;E:\exploria-toolchain-local\php;C:\Program Files\Git\cmd;' + $env:PATH
```

Composer PHAR:

```powershell
E:\exploria-toolchain-local\composer\composer.phar
```

The PHP 8.4 local runtime has these project-critical extensions enabled:

- `curl`
- `mbstring`
- `openssl`
- `pdo_pgsql`
- `pdo_sqlite`
- `sqlite3`
- `zip`

`memory_limit` is set to `512M` for PHPStan/Larastan.

## Composer Mirror

On this machine, Composer was configured to use the Liara mirror:

```powershell
php E:\exploria-toolchain-local\composer\composer.phar config -g repos.packagist composer https://package-mirror.liara.ir/repository/composer/
```

After changing the mirror, refresh lock metadata without changing package versions:

```powershell
php E:\exploria-toolchain-local\composer\composer.phar update --lock --no-install --no-scripts --no-progress --no-interaction
```

Then install dependencies:

```powershell
php E:\exploria-toolchain-local\composer\composer.phar install --prefer-dist --no-progress --no-interaction
npm install
```

## Local Environment

Create local `.env` from `.env.example`, then use SQLite for portable development:

```dotenv
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
CACHE_STORE=file
```

Generate an app key:

```powershell
php artisan key:generate --force
```

The `.env` file and `database/database.sqlite` are local runtime files and must stay out of Git.

## Database

Before destructive local migrations, back up `database/database.sqlite`.

Fresh local setup:

```powershell
php artisan config:clear
php artisan migrate:fresh --seed --force
```

Expected seed counts:

- `venues`: 3
- `zones`: 1
- `hubs`: 1
- `touchpoints`: 1
- `campaigns`: 1
- `qr_codes`: 1
- `consent_versions`: 1
- `users`: 1

Demo QR code:

```text
ep1405-a7f3k9m2q8x4
```

## Verification

Backend:

```powershell
php E:\exploria-toolchain-local\composer\composer.phar test
```

Frontend:

```powershell
npm run types:check
npm run lint:check
npm run build
```

Smoke endpoints after starting a local server:

```text
GET /up
GET /api/v1/consents/current
GET /scan/ep1405-a7f3k9m2q8x4
GET /dashboard
```

The browser flow to verify after local migration and seed:

```text
QR landing -> OTP request -> OTP verify -> Consent accept -> Visit experience -> Dashboard
```

Use this local-only OTP code:

```text
123456
```

After consent is accepted from the QR flow, the app creates one confirmed visit for the authenticated user and redirects to:

```text
/visits/{visit}
```

The dashboard should show non-placeholder operational stats, including:

- venues
- active QR codes
- OTP requests
- consent logs
- confirmed visits
