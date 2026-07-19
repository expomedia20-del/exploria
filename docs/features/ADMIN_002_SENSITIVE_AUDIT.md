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
- `audit.reward_approved`، `audit.reward_rejected` و `audit.reward_revision_requested`
- `audit.qr_created`، `audit.qr_updated` و `audit.qr_deleted`
- `audit.user_created`، `audit.user_role_updated`، `audit.user_access_deactivated` و `audit.user_deleted`
- `audit.access_scope_created`، `audit.access_scope_reactivated` و `audit.access_scope_deactivated`
- `audit.ad_approved` و `audit.ad_rejected`
- `audit.sponsor_proposal_status_updated` و `audit.sponsor_proposal_activated`
- `audit.partner_profile_updated`، `audit.partner_offer_created` و `audit.partner_offer_updated`
- `audit.treasure_created`، `audit.treasure_updated` و `audit.treasure_deleted`

همه رویدادها از `RecordAdminAuditAction` وارد `event_log` append-only می‌شوند. Payload فقط شامل کد، عنوان و وضعیت عملیاتی شیء است و شناسه Session صرفاً به‌شکل SHA-256 ذخیره می‌شود.

## مشاهده و دسترسی

- Event Monitor برای نقش‌های داخلی مجاز فقط‌خواندنی است.
- فیلتر مستقل نوع رویداد و بازه تاریخ دارد.
- نوع، کد و عنوان شیء برای رکوردهای حذف‌شده نیز از Payload امن قابل مشاهده می‌ماند.
- برای User و Access Scope فقط شناسه داخلی و داده عملیاتی Role/Scope نمایش داده می‌شود؛ نام، ایمیل و موبایل وارد Audit Payload نمی‌شوند.
- Audit پروفایل Partner فقط کد، عنوان و وضعیت تجاری را نگه می‌دارد و Contact Name/Mobile را ثبت نمی‌کند.
- حساب مدیریتی جدید با رمز تصادفی غیرقابل‌پیش‌بینی ساخته می‌شود و کاربر رمز نهایی را از مسیر بازیابی رمز تعیین می‌کند؛ رمز موقت در پاسخ یا Log منتشر نمی‌شود.
- Visitor و کاربر ناشناس به Monitor دسترسی ندارند؛ Viewer امکان Mutation ندارد.

## Acceptance و Verification

- تغییر موفق باید دقیقاً پس از Mutation، Audit متناظر با `actor_user_id`، `object_type` و `object_id` بسازد.
- Mutation ناموفق نباید Audit موفق بسازد.
- حذف شیء نباید قابلیت تشخیص کد آن در Audit را از بین ببرد.
- خروجی Monitor نباید Session خام، موبایل، OTP یا Token داشته باشد.
- ورودی Notes تصمیم پاداش با `ReviewRewardRequest` و تغییرات Role/Scope/Account با Form Requestهای اختصاصی اعتبارسنجی می‌شوند.
- تست‌های متمرکز Venue و Campaign Core: PASS.
- Pint و PHPStan: PASS.
- PHPUnit کامل: 251 تست و 2,218 Assertion — PASS.
- ESLint، Prettier، TypeScript و Production Build — PASS.

## خارج از دامنه

- Export عمومی Audit، سامانه متمرکز Log، Retention خودکار و Alerting پیشرفته تا پیش از تصمیم Pilot خارج از این مرحله‌اند.
