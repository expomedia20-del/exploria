# EVENT-001/002 — چرخه رویداد تجربه و پاداش

## هدف و Trace

- Requirements: `EVENT-001` و `EVENT-002`
- جریان پوشش‌داده‌شده: ثبت‌نام → شروع مأموریت → تکمیل مأموریت → صدور پاداش → مصرف پاداش.
- Eventها در `event_log` append-only و بدون Email، Mobile، OTP، Token یا Session خام ثبت می‌شوند.

## رویدادهای پیاده‌سازی‌شده

- `user_registered` پس از ایجاد موفق کاربر عمومی.
- `mission_started` فقط در اولین انتقال مأموریت به وضعیت Started.
- `mission_completed` فقط در اولین تکمیل موفق مأموریت.
- `reward_issued` فقط هنگام ایجاد واقعی `UserReward`.
- `reward_redeemed` فقط پس از تأیید موفق مصرف توسط Partner مجاز.

درخواست تکراری یا ناموفق Event موفق جدید تولید نمی‌کند.

## Attribution و کیفیت

- Eventهای Mission و Reward به `venue_id`، `touchpoint_id` و `campaign_id` بازدید متصل‌اند.
- شناسه Visit، Mission، Reward Definition، Partner و Hub فقط در حد لازم در Payload عملیاتی ذخیره می‌شود.
- `quality_flag` برای داده کامل `false` است و در مصرف پاداشِ بدون Reward Definition به‌صورت Fail-safe فعال می‌شود.
- Event Monitor یک شمارنده مستقل «چرخه تجربه و پاداش» و فیلتر تمام Eventهای این چرخه دارد.

## Acceptance

- ثبت‌نام موفق یک `user_registered` و بدون PII ایجاد کند.
- Start/Complete تکراری Event تکراری نسازد.
- تکمیل قفل‌شده یا خارج از مالکیت Event موفق نسازد.
- صدور پاداش فقط هم‌زمان با ایجاد واقعی UserReward ثبت شود.
- مصرف نامعتبر یا تکراری `reward_redeemed` نسازد.

## خارج از دامنه این مرحله

- `feedback_submitted` تا زمان فعال‌شدن جریان واقعی Feedback.
- Eventهای میدانی Should مانند `ambassador_interaction_logged` و Offline Sync کامل.

## Verification

- PHPUnit کامل: 251 تست و 2,211 Assertion — PASS.
- PHPStan، Pint، ESLint، Prettier و TypeScript — PASS.
- Production Build با 2,335 module — PASS.
