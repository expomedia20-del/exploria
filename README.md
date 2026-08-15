# EXPLORIA Codebase

این پوشه Codebase رسمی توسعه EXPLORIA بر پایه Laravel + React Monolith است.

## وضعیت فعلی

Bootstrap رسمی و توسعه قابلیت‌های MVP انجام شده و Codebase روی شاخه `main` در Commit ادغامی `a9a9e45` قرار دارد. Snapshot راستی‌آزمایی‌شده 2026-08-16:

- چهار Check مربوط به PR شماره 3 شامل Linter/Quality، PHP 8.4، PHP 8.5 و PostgreSQL موفق بوده‌اند.
- Test Suite محلی: 369 Test / 4729 Assertion / 0 Failure.
- Pint، PHPStan، ESLint، Prettier و TypeScript بدون خطا اجرا شده‌اند.
- Composer Audit بدون Advisory و NPM Audit بدون Vulnerability است.
- Production Readiness در محیط Local عمداً Fail-Closed است: 3 Pass / 10 Fail و `ready=false`.
- Migration حاکمیت پاداش `2026_08_15_000001_add_reward_governance_controls` در دیتابیس Local معوق است؛ همان Migration و Rollback آن در PostgreSQL CI موفق بوده‌اند.
- Staging بیرونی، سرویس‌های واقعی و Gateهای عملیاتی هنوز تأیید نشده‌اند؛ بنابراین وضعیت Production همچنان **NO-GO** است.

مراجع وضعیت جاری:

- `docs/status/EXPLORIA_Feature_Status_Register_v1.0.md`
- `docs/staging/EXPLORIA_Stage_3_Staging_Readiness_v1.0.md`
- `docs/uat/EXPLORIA_Stage_4_UAT_Dry_Run_v1.0.md`
- `docs/pilot/EXPLORIA_Stage_5_Controlled_Pilot_Launch_Kit_v1.0.md`

## قواعد الزامی

- پیش از هر تغییر، `AGENTS.md` خوانده شود.
- اسناد `docs/governance` مرجع Scope، معماری، Acceptance و Gateها هستند.
- هیچ Secret یا فایل `.env` واقعی Commit نشود.
- سبز بودن Local/CI به معنی آماده‌بودن Staging، Pilot یا Production نیست؛ هر محیط باید Evidence و Go/No-Go مستقل داشته باشد.
