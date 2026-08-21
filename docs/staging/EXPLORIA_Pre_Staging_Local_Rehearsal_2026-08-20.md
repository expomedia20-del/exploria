# EXPLORIA — گزارش مانور Local Pre-Staging — 2026-08-20

## 1. نتیجه و حدود ادعا

**نتیجه:** `LOCAL PRE-STAGING VERIFIED — EXTERNAL STAGING STILL REQUIRED`

این مانور فقط روی یک Cluster موقت PostgreSQL 18، متصل به `127.0.0.1` و با داده کاملاً مصنوعی اجرا شد. هیچ داده واقعی، OTP، Mail، Storage، دامنه عمومی، TLS معتبر، Queue Worker، Scheduler، Monitoring یا زیرساخت خارجی در این گزارش تأیید نمی‌شود. این Evidence مجوز `STAGING LIVE`، `PILOT READY` یا `PRODUCTION GO` نیست.

## 2. Evidence ده‌بندی

| بند | Evidence | نتیجه |
|---|---|---|
| PostgreSQL ایزوله | دو Database موقت با نام‌های دارای پسوند ایمن `_testing` و `_restore_test` روی PostgreSQL 18 | PASS |
| Migration کامل | اجرای 36 Migration از Database خالی؛ Migration معوق Reward Governance نیز اعمال شد | PASS |
| Rollback/Re-apply | حذف و بازسازی 6 ستون حاکمیتی، 2 Unique Constraint و رکورد Migration | PASS |
| Test Suite PostgreSQL | اجرای مستقل اولیه: 373 Test / 4809 Assertion؛ اجرای نهایی: 374 Test / 4821 Assertion | PASS |
| Reward reconciliation | Duplicate در `user_rewards` و `reward_redemptions` برابر صفر؛ Reward نامعتبر صفر؛ Campaign متوقف صفر | PASS |
| Readiness شبیه Staging | 13 Pass / 1 Fail؛ تنها Failure عمدی `LocalFixedOtpProvider` و نتیجه کلی Fail-Closed | PASS |
| Backup | Archive واقعی PostgreSQL، کنترل `pg_restore --list` و Manifest مستقل SHA-256؛ نمونه اولیه 221860 بایت | PASS |
| Restore | Restore روی Database مستقل؛ تطابق 36 Migration، 8 User، 1 Campaign، 4 Reward و 6 ستون حاکمیتی | PASS |
| Tamper Test | تغییر یک بایت از کپی Archive؛ توقف با `Backup checksum verification failed`؛ مقصد قبل و بعد `36:8` | PASS |
| Launch Assurance | Multi-campaign 10/10، Demo 19/19، Full CI، Build، PostgreSQL، Backup و Restore با Exit Code صفر | PASS |

## 3. خروجی نهایی Launch Assurance

| Gate | نتیجه |
|---|---|
| Multi-campaign Assurance | 10 Pass / 0 Warning / 0 Fail |
| Demo Readiness | 19 Pass / 0 Warning / 0 Fail |
| Local Production Readiness | 6 Pass / 8 Fail / `ready=false` — مورد انتظار |
| Full CI | ESLint، Prettier، TypeScript، Pint و PHPStan موفق؛ 374 Test / 4820 Assertion |
| Production Build | Vite Build موفق؛ حدود 50.88 ثانیه |
| PostgreSQL Gate | 374 Test / 4821 Assertion |
| Backup/Restore داخل Orchestrator | هر دو موفق |
| کل Launch Assurance نهایی | Exit Code 0؛ حدود 334.4 ثانیه |

## 4. Gapهای کشف و اصلاح‌شده

1. `run-launch-assurance.ps1` مقدار `APP_ENV=local` را به PHPUnit منتقل می‌کرد و پاسخ‌های 419 ایجاد می‌شد. CI اکنون با `APP_ENV=testing` اجرا و مقدار قبلی در `finally` بازیابی می‌شود.
2. متغیرهای Runtime دیتابیس وارد Full CI می‌شدند و Test Suite را روی Database Fixtureدار اجرا می‌کردند. متغیرهای DB اکنون برای CI Snapshot/Remove و سپس Restore می‌شوند؛ PostgreSQL در Gate مستقل خود اجرا می‌شود.
3. `MultiCampaignAssuranceService` برای Venue ناموجود مقدار متنی را با ستون UUID مقایسه می‌کرد. رفتار اکنون بدون Query Error و به‌صورت Fail-Closed با مجموعه خالی است.

## 5. Cleanup و حفاظت

- Credential تصادفی فقط در پوشه Temp نگهداری شد و وارد خروجی، Repository یا Git نشد.
- Cluster موقت متوقف، اتصال پورت موقت بسته و همه Archiveها، Manifestها و فایل‌های موقت حذف شدند.
- سرویس نصب‌شده `postgresql-x64-18` پس از Cleanup همچنان Running بود.
- Database SQLite فعلی پروژه و داده‌های Local آن تغییر نکردند.

## 6. Gateهای باز

- External Staging مستقل، دامنه و TLS معتبر؛
- PostgreSQL و Secretهای واقعی Staging؛
- Backup رمزگذاری‌شده Off-host و Restore Drill روی زیرساخت مستقل؛
- OTP/Mail/Storage واقعی و E2E؛
- Queue/Cache/Session/Scheduler عملیاتی؛
- Central Monitoring/Alerting/On-call؛
- Load Test و Capacity Sizing؛
- Privacy/Retention/Deletion approval، UAT رسمی و Go/No-Go امضاشده.

**Production Readiness همچنان `NO-GO` است.**
