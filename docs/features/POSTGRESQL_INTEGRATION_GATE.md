# PostgreSQL Integration Gate

## وضعیت فعلی

- PostgreSQL 18.4 نصب است و سرویس `postgresql-x64-18` اجرا می‌شود.
- `psql` در PATH قابل استفاده است.
- اتصال بدون Credential به نقش `postgres` به‌درستی رد می‌شود؛ هیچ Password حدس زده یا در Repository ذخیره نشده است.
- تست روزمره همچنان روی SQLite in-memory ایزوله اجرا می‌شود و جایگزین Gate نهایی PostgreSQL نیست.

## Gate اضافه‌شده

- `phpunit.pgsql.xml` تست کامل را با Driver مصوب `pgsql` اجرا می‌کند و Credential ندارد.
- `scripts/test-postgresql.ps1` اطلاعات اتصال را فقط از متغیرهای `EXPLORIA_PG_*` دریافت می‌کند.
- پیش از `migrate:fresh`، اتصال و نام واقعی دیتابیس بررسی می‌شود.
- عملیات مخرب فقط روی نامی که صریحاً به `_test`، `-test`، `_testing` یا `-testing` ختم شود مجاز است.

## اجرای Gate

```powershell
$env:EXPLORIA_PG_DATABASE='exploria_testing'
$env:EXPLORIA_PG_USERNAME='exploria_testing'
$env:EXPLORIA_PG_PASSWORD='<local secret>'
.\scripts\test-postgresql.ps1
```

Secret باید فقط در Environment محلی قرار گیرد و نباید Commit شود.

## وضعیت Gate

- نصب Runtime و سرویس PostgreSQL: PASS.
- آمادگی پیکربندی تست و کنترل ایمنی: PASS.
- Migration و PHPUnit واقعی روی PostgreSQL: BLOCKED BY LOCAL DATABASE CREDENTIAL؛ پس از Provision نقش/دیتابیس آزمایشی اجرا شود.
- PHPUnit فعلی SQLite: 251 تست و 2,218 Assertion — PASS.
- PHPStan، Pint، ESLint، Prettier، TypeScript و Production Build — PASS.

## نکته Governance

سند `24-EXPLORIA_Toolchain_Readiness_v1.0.md` وضعیت تاریخی 2026-06-20 را نشان می‌دهد و درباره Git/PostgreSQL منقضی است. چون `Sync-Handoff.ps1` الزام‌شده در `AGENTS.md` در این Clone وجود ندارد، سند Canonical در این مرحله بازنویسی نشده و این گزارش به‌عنوان Evidence جاری ثبت شده است.
