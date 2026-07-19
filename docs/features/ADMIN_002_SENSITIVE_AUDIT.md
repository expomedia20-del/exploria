# ADMIN-002 — Audit تغییرات حساس مدیریتی

## هدف و مرجع

- Requirement: `ADMIN-002` و `DATA-004`
- هدف: ثبت قابل مشاهده‌ی عامل، شیء و زمان تغییرات حساس بدون نگهداری PII، Token یا Session خام.
- دامنه این مرحله: Venue، Campaign، Mission/Quest، Reward و QR.

## رویدادهای پوشش‌داده‌شده

- `audit.venue_updated`
- `audit.campaign_created`، `audit.campaign_updated` و `audit.campaign_deleted`
- `audit.mission_created`، `audit.mission_updated` و `audit.mission_deleted`
- `audit.reward_created`، `audit.reward_updated` و `audit.reward_deleted`
- `audit.qr_created`، `audit.qr_updated` و `audit.qr_deleted`

همه رویدادها از `RecordAdminAuditAction` وارد `event_log` append-only می‌شوند. Payload فقط شامل کد، عنوان و وضعیت عملیاتی شیء است و شناسه Session صرفاً به‌شکل SHA-256 ذخیره می‌شود.

## مشاهده و دسترسی

- Event Monitor برای نقش‌های داخلی مجاز فقط‌خواندنی است.
- فیلتر مستقل نوع رویداد و بازه تاریخ دارد.
- نوع، کد و عنوان شیء برای رکوردهای حذف‌شده نیز از Payload امن قابل مشاهده می‌ماند.
- Visitor و کاربر ناشناس به Monitor دسترسی ندارند؛ Viewer امکان Mutation ندارد.

## Acceptance و Verification

- تغییر موفق باید دقیقاً پس از Mutation، Audit متناظر با `actor_user_id`، `object_type` و `object_id` بسازد.
- Mutation ناموفق نباید Audit موفق بسازد.
- حذف شیء نباید قابلیت تشخیص کد آن در Audit را از بین ببرد.
- خروجی Monitor نباید Session خام، موبایل، OTP یا Token داشته باشد.
- تست‌های متمرکز Venue و Campaign Core: PASS.
- Pint و PHPStan: PASS.
- PHPUnit کامل: 248 تست و 2,164 Assertion — PASS.
- ESLint، Prettier، TypeScript و Production Build — PASS.

## خارج از دامنه

- Export عمومی Audit، سامانه متمرکز Log، Retention خودکار و Alerting پیشرفته تا پیش از تصمیم Pilot خارج از این مرحله‌اند.
