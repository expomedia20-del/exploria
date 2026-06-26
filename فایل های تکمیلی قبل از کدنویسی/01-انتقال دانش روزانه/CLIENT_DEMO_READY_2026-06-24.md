# آماده ارائه به کارفرما - 2026-06-24

## وضعیت فوری

دموی محلی EXPLORIA برای ارائه آماده است و روی این آدرس اجرا می‌شود:

```text
http://127.0.0.1:8000
```

مسیرهای مهم ارائه:

```text
Board Entry:
http://127.0.0.1:8000/board

Full Mock Ecosystem Demo:
http://127.0.0.1:8000/demo/ecosystem

Employer Demo Hub:
http://127.0.0.1:8000/demo

Proposal Coverage:
http://127.0.0.1:8000/demo/proposal

Mission and Rewards:
http://127.0.0.1:8000/demo/missions

QR Landing:
http://127.0.0.1:8000/scan/ep1405-a7f3k9m2q8x4

Dashboard:
http://127.0.0.1:8000/dashboard

QR Registry:
http://127.0.0.1:8000/admin/qr-codes

Campaign Registry:
http://127.0.0.1:8000/admin/campaigns

Mission/Reward Registry:
http://127.0.0.1:8000/admin/missions

Partner Registry:
http://127.0.0.1:8000/admin/partners

Partner Dashboard:
http://127.0.0.1:8000/partner/dashboard

Standalone Advertising Admin:
http://127.0.0.1:8000/admin/ads

Partner Advertising Submission:
http://127.0.0.1:8000/partner/ads

Venue/Hub Registry:
http://127.0.0.1:8000/admin/venues
```

سناریوی گام‌به‌گام ارائه در این فایل ثبت شد:

```text
docs/PRESENTATION_RUNBOOK.md
PRESENTATION_RUNBOOK_2026-06-24.md
```

برنامه شروع توسعه واقعی سایت و اکوسیستم در این فایل ثبت شد:

```text
docs/REAL_PRODUCT_PHASE_1_PLAN.md
```

شروع Sprint 1.1 واقعی انجام شد: نقش‌های جدید، جدول‌های partner، seed اکوپارک برای کافه اکو/فروشگاه رواق/اسپانسر مسیر خانوادگی، API و صفحه admin partner registry اضافه شدند.

جزئیات اجرای Sprint 1.1:

- نقش‌های `visitor`, `shop_partner`, `hub_manager`, `sponsor` اضافه شدند.
- جدول‌های `partner_accounts`, `partner_locations`, `partner_users`, `hub_management_assignments` اضافه شدند.
- مسیرهای `GET /admin/partners` و `GET /api/v1/admin/partners` اضافه شدند.
- صفحه admin partner registry ساخته شد:
  `http://127.0.0.1:8000/admin/partners`
- دیتابیس محلی migrate و `PilotLocationSeeder` دوباره اجرا شد.
- کاربر admin دمو در `DatabaseSeeder` ثبت شد:
  `admin@example.test / password`
- لینک «مدیریت شرکا» به sidebar پنل اصلی اضافه شد.

شروع Sprint 1.2 واقعی انجام شد:

- مسیرهای `GET /admin/venues` و `GET /api/v1/admin/venues` اضافه شدند.
- صفحه «مدیریت مکان‌ها» برای نمایش Venue، Zone، Hub، Touchpoint، شرکا و مدیران هاب ساخته شد.
- لینک «مدیریت مکان‌ها» به sidebar پنل اصلی اضافه شد.
- commit آماده ادامه:
  `758c0b4 feat: add venue registry foundation`

شروع Sprint 1.3 واقعی انجام شد:

- صفحه و API رجیستری کمپین اضافه شد:
  `http://127.0.0.1:8000/admin/campaigns`
- ادمین/اپراتور می‌تواند کمپین جدید بسازد.
- صفحه QR با فرم ساخت QR جدید تکمیل شد.
- ساخت QR کنترل می‌کند که مکان، کمپین و نقطه تماس با هم هم‌محدوده باشند.
- فیلدهای اعتبار زمانی، سقف اسکن و بازه ضدتکرار در فرم QR آمده است.
- لینک «کمپین‌ها» به sidebar اضافه شد.
- commit آماده ادامه:
  `5962a35 feat: add campaign and qr creation core`

شروع Sprint 1.4 واقعی انجام شد:

- جدول‌های ماموریت، نمونه ماموریت، گنج، تعریف پاداش، پیشرفت کاربر، کیف پاداش و مصرف پاداش اضافه شدند.
- seed اکوپارک چهار ماموریت معادل صفحه دمو، یک گنج خانوادگی/تیمی و چهار پاداش دیتابیسی می‌سازد.
- صفحه رجیستری ماموریت/گنج/پاداش اضافه شد:
  `http://127.0.0.1:8000/admin/missions`
- API خواندن رجیستری اضافه شد:
  `GET /api/v1/admin/missions`
- لینک «ماموریت و پاداش» به sidebar اضافه شد.
- commit آماده ادامه:
  `56b815c feat: add mission reward foundation`

ادامه Sprint 1.4 واقعی انجام شد:

- صفحه تجربه بازدید بعد از QR حالا ماموریت‌های واقعی دیتابیسی را نمایش می‌دهد.
- کاربر می‌تواند ماموریت را start و complete کند.
- تکمیل ماموریت، امتیاز را در `user_mission_progress` ثبت می‌کند.
- تکمیل ماموریت، پاداش متناظر را در `user_rewards` صادر می‌کند.
- ماموریت چالش قفل‌شده تا رسیدن به حداقل امتیاز باز نمی‌شود.
- APIهای جریان کاربر اضافه شدند:
  `GET /api/v1/visits/{visit}/missions`
  `POST /api/v1/visits/{visit}/missions/{mission}/start`
  `POST /api/v1/visits/{visit}/missions/{mission}/complete`
  `GET /api/v1/rewards/wallet`
- commit آماده ادامه:
  `c175e17 feat: connect visit mission flow`

شروع Sprint 1.5 واقعی انجام شد:

- پنل واقعی فروشگاه/شریک اضافه شد:
  `http://127.0.0.1:8000/partner/dashboard`
- حساب تست فروشگاه:
  `cafe.eco@example.test / password`
- API خواندن داشبورد فروشگاه اضافه شد:
  `GET /api/v1/partner/dashboard`
- API و فرم تایید مصرف کد پاداش اضافه شد:
  `POST /partner/redemptions/confirm`
  `POST /api/v1/partner/redemptions/confirm`
- وقتی کاربر ماموریت دارای پاداش وابسته به partner را کامل کند، سیستم برای پاداش او `reward_redemption` با کد مصرف یکتا می‌سازد.
- فروشگاه فقط پاداش‌ها و کدهای مربوط به خودش را می‌بیند و نمی‌تواند کد فروشگاه دیگر را تایید کند.
- تایید مصرف کد، وضعیت `reward_redemptions` و `user_rewards` را به وضعیت مصرف‌شده/تاییدشده منتقل می‌کند.
- commit کدنویسی محصول:
  `5bb0db8 feat: add partner reward redemption dashboard`
- آخرین commit آماده ادامه:
  `09994ff docs: update partner dashboard checkpoint`

ادامه Sprint 1.5 واقعی انجام شد:

- فروشگاه می‌تواند از پنل خودش پیشنهاد/تخفیف جدید ثبت کند:
  `POST /partner/offers`
  `POST /api/v1/partner/offers`
- پیشنهاد فروشگاه در جدول `reward_definitions` با status `draft` و metadata زیر ذخیره می‌شود:
  `source = partner_offer_submission`
  `approval_status = pending_review`
- ادمین، اپراتور یا مدیر رواق می‌تواند پیشنهاد را تایید یا رد کند:
  `POST /admin/rewards/{reward}/approve`
  `POST /admin/rewards/{reward}/reject`
  `POST /api/v1/admin/rewards/{reward}/approve`
  `POST /api/v1/admin/rewards/{reward}/reject`
- تایید پیشنهاد آن را `active` می‌کند؛ رد پیشنهاد آن را `inactive` می‌کند.
- viewer اجازه تایید یا رد پیشنهاد ندارد.
- صفحه فروشگاه فرم ثبت پیشنهاد/تخفیف دارد و صفحه admin mission/reward دکمه‌های تایید/رد برای پیشنهادهای در انتظار نشان می‌دهد.
- commit کدنویسی محصول:
  `c3b503f feat: add partner offer approval flow`
- آخرین commit آماده ادامه:
  `e3cc6d4 docs: update partner offer checkpoint`

شروع Sprint 1.6 واقعی انجام شد:

- جدول‌های تبلیغات مستقل اضافه شدند:
  `ad_requests`, `ad_creatives`, `display_devices`, `ad_placements`, `ad_approvals`, `ad_events`
- مدل‌های تبلیغات مستقل اضافه شدند:
  `AdRequest`, `AdCreative`, `DisplayDevice`, `AdPlacement`, `AdApproval`, `AdEvent`
- seed اکوپارک دو نمایشگر نمونه می‌سازد:
  `ecopark-entry-fixed-display`
  `ecopark-mobile-promo-display`
- صفحه ثبت تبلیغ فروشگاه/اسپانسر اضافه شد:
  `http://127.0.0.1:8000/partner/ads`
- صفحه مدیریت و تایید تبلیغات مستقل اضافه شد:
  `http://127.0.0.1:8000/admin/ads`
- APIهای فروشگاه/اسپانسر اضافه شدند:
  `GET /api/v1/partner/ads`
  `POST /api/v1/partner/ads`
- APIهای مدیریت اضافه شدند:
  `GET /api/v1/admin/ads`
  `POST /api/v1/admin/ads/{adRequest}/approve`
  `POST /api/v1/admin/ads/{adRequest}/reject`
- فروشگاه/اسپانسر می‌تواند درخواست تبلیغ با عنوان، متن، CTA، لینک مقصد، نوع creative، جایگاه نمایش، بازه زمانی، بودجه و سقف نمایش ثبت کند.
- درخواست تبلیغ با status `pending_review` ذخیره می‌شود و creative و placement اولیه هم ساخته می‌شوند.
- ادمین/اپراتور/مدیر رواق می‌تواند تایید یا رد کند؛ viewer فقط حق مشاهده دارد.
- تایید تبلیغ، درخواست و creative را `approved` و placement را `scheduled` می‌کند.
- رد تبلیغ، درخواست، creative و placement را `rejected` می‌کند.
- commit کدنویسی محصول:
  `c3e64d2 feat: add display advertising publishing api`
- آخرین commit آماده ادامه:
  `8654916 docs: update display publishing checkpoint`


ادامه Sprint 1.6 برای API نمایشگر انجام شد:

- API خواندن برنامه تبلیغ نمایشگر اضافه شد:
  `GET /api/v1/display/{deviceCode}/schedule`
  نمونه:
  `GET /api/v1/display/ecopark-entry-fixed-display/schedule`
- API ثبت event نمایشگر اضافه شد:
  `POST /api/v1/display/{deviceCode}/events`
- eventهای مجاز فعلی:
  `impression`, `click`, `playback_start`, `playback_complete`, `scan`
- schedule فقط تبلیغ تاییدشده (`approved`) با placement زمان‌بندی‌شده (`scheduled`) را برای نمایشگر فعال برمی‌گرداند.
- اگر تبلیغ هنوز تایید نشده باشد، schedule آن را نشان نمی‌دهد و event برای آن پذیرفته نمی‌شود.
- تست هدفمند API نمایشگر: 9 تست و 69 assertion پاس شد.
- `composer test` پس از API نمایشگر: 112 تست و 588 assertion پاس شد.
- commit کدنویسی محصول:
  `c3e64d2 feat: add display advertising publishing api`
## سناریوی پیشنهادی ارائه

1. مسیر رسمی هیئت‌مدیره را باز کنید:
   `http://127.0.0.1:8000/board`
2. وارد دموی کامل اکوسیستم شوید:
   `http://127.0.0.1:8000/demo/ecosystem`
3. توضیح دهید این صفحه، فرانت ماک برای تصویر کامل محصول است: پنل کاربر، پنل فروشگاه، پنل مدیر رواق، پنل مدیر کل، تعریف گنج، پاداش، تخفیف، تبلیغات مستقل از کمپین، اسپانسرها، قوانین و ضدتقلب.
4. تاکید کنید مسیر QR/OTP/consent/dashboard هسته MVP متصل‌تر به دیتابیس است، اما صفحه اکوسیستم برای نمایش vision محصول با mock data ساخته شده است.
5. نشان دهید جلسه از یک ورودی شبیه سایت کامل شروع می‌شود و هیئت‌مدیره می‌تواند نقش مدیریتی، نقش بازدیدکننده، QR، ماموریت، پاداش و داشبورد را از همانجا ببیند.
6. مسیر QR را باز کنید و نشان دهید QR به پایلوت اکوپارک عباس آباد وصل است.
7. برای ارائه کامل‌تر، صفحه دمو را باز کنید:
   `http://127.0.0.1:8000/demo`
8. صفحه پوشش پروپوزال را باز کنید:
   `http://127.0.0.1:8000/demo/proposal`
9. نشان دهید اسلایدهای کلیدی پروپوزال با دمو چگونه پوشش داده شده‌اند: سفر بازدیدکننده، کمپین گنج، touchpoint، هاب‌ها، مدل اقتصادی و KPI.
10. از کارت‌های زنده صفحه دمو وارد مسیر QR شوید.
11. صفحه ماموریت و پاداش را باز کنید:
   `http://127.0.0.1:8000/demo/missions`
12. چند ماموریت اول را تکمیل کنید و نشان دهید امتیاز، کیف پاداش، سطح و ماموریت قفل‌شده تغییر می‌کند.
13. وارد مسیر OTP شوید.
14. شماره نمونه وارد کنید:
   `09120000000`
15. کد OTP ثابت محلی را وارد کنید:
   `123456`
16. رضایت‌نامه فارسی را تایید کنید.
17. صفحه تجربه بازدید را نشان دهید.
18. داشبورد را باز کنید و آمار عملیاتی را نشان دهید.
19. صفحه مدیریت QR را باز کنید و binding کد QR به مکان، نقطه تماس و کمپین را توضیح دهید.

## اگر سرور بسته شد

از مسیر پروژه:

```text
E:\exploria-codebase-current
```

این دستور را اجرا کنید:

```powershell
.\scripts\start-demo.ps1
```

## وضعیت فنی آخرین نقطه سالم

آخرین commit آماده ارائه:

```text
8654916 docs: update display publishing checkpoint
```

آخرین commit کدنویسی محصول:

```text
c3e64d2 feat: add display advertising publishing api
```

این نقطه شامل Demo Hub کارفرمایی، راهنمای دمو و اسکریپت راه‌اندازی زیر است:

```text
http://127.0.0.1:8000/board
http://127.0.0.1:8000/demo/ecosystem
docs/CLIENT_DEMO_GUIDE.md
docs/PRESENTATION_RUNBOOK.md
docs/REAL_PRODUCT_PHASE_1_PLAN.md
docs/features/STANDALONE_ADVERTISING_REQUIREMENTS.md
scripts/start-demo.ps1
```

کنترل‌های موفق بعد از ساخت Demo Hub:

- `composer test`
- `npm run format:check`
- `npm run types:check`
- `npm run lint:check`
- `npm run build`
- تست مرورگری `/demo` بدون خطای console
- تست مرورگری `/demo/missions` بدون خطای console؛ پس از تکمیل سه ماموریت، امتیاز ۵۲۰، سطح سفیر محلی، سه پاداش و باز شدن ماموریت قفل‌شده تایید شد.
- تست مرورگری `/demo/proposal` بدون خطای console؛ نقشه سفر، کشف گنج، touchpoint، مدل اقتصادی و KPI تایید شد.

تست‌های موفق:

- `composer test`
- `npm run format:check`
- `npm run types:check`
- `npm run lint:check`
- `npm run build`
- `composer test` پس از Sprint 1.1: 75 تست و 311 assertion پاس شد.
- `composer test` پس از شروع Sprint 1.2: 78 تست و 335 assertion پاس شد.
- `composer test` پس از شروع Sprint 1.3: 84 تست و 381 assertion پاس شد.
- `composer test` پس از شروع Sprint 1.4: 90 تست و 420 assertion پاس شد.
- `composer test` پس از اتصال جریان ماموریت به Visit: 95 تست و 459 assertion پاس شد.
- `composer test` پس از شروع پنل فروشگاه و مصرف پاداش: 100 تست و 499 assertion پاس شد.
- تست هدفمند پس از ثبت پیشنهاد فروشگاه و تایید/رد مدیریتی: 14 تست و 99 assertion پاس شد.
- تست هدفمند تبلیغات مستقل و پنل فروشگاه/پاداش: 14 تست و 112 assertion پاس شد.
- `composer test` پس از شروع Sprint 1.6 تبلیغات مستقل: 109 تست و 571 assertion پاس شد.
- `npm run types:check`
- `npm run lint:check`
- `npm run build`
- `npm run format:check`
- تست مرورگری `/board` بدون خطای console؛ عنوان، H1، لینک ورود هیئت‌مدیره، لینک ورود بازدیدکننده و لینک QR تایید شد.
- تست مرورگری `/demo/ecosystem` بدون خطای console؛ پنل کاربر، پنل فروشگاه، پنل مدیر رواق، پنل مدیر کل، گنج‌ها، پاداش‌ها، تخفیف‌ها، تبلیغات مستقل، برند غیرعضو، اسپانسر، تایید ادمین، جایگاه تبلیغ و گزارش عملکرد تایید شد.
- تست تعامل نقش فروشگاه در `/demo/ecosystem`؛ تغییر پنل به «داشبورد فروشگاه شریک» و نمایش «اعتبارسنجی کد مشتری» تایید شد.
- تست مرورگری `/` بدون خطای console؛ لینک ورود رسمی هیئت‌مدیره تایید شد.
- تست مرورگری مسیر `/admin/qr-codes`

## نکات بیان برای کارفرما

- این نسخه، دمو/پایلوت محلی است و هنوز محصول نهایی production نیست.
- برای جلسه هیئت‌مدیره، شروع ارائه از `/board` باشد تا دمو شبیه ورود به سایت کامل دیده شود.
- برای نمایش کامل محصول از `/demo/ecosystem` استفاده شود؛ این صفحه mock frontend است و backend واقعی برای پنل‌های فروشگاه/رواق/تعریف گنج/تبلیغات مستقل هنوز پیاده‌سازی نشده است.
- تعریف MVP فعلی: مسیر واقعی‌تر QR، ورود موبایلی، رضایت‌نامه، ثبت بازدید، داشبورد و QR Registry. تعریف demo vision: صفحه اکوسیستم با داده ماک برای نقش‌ها، گنج‌ها، پاداش‌ها، تخفیف‌ها، تبلیغات مستقل و اسپانسرها.
- نیازمندی توسعه واقعی تبلیغات مستقل در `docs/features/STANDALONE_ADVERTISING_REQUIREMENTS.md` ثبت شد: بارگذاری محتوای تبلیغاتی توسط فروشگاه/زیرمجموعه هاب/برند غیرعضو/اسپانسر، تایید مدیر رواق یا ادمین، زمان‌بندی، انتشار روی نمایشگر ثابت و سیار، جایگاه نمایش، بودجه، گزارش و ردگیری عملکرد.
- مسیر اصلی ارزش محصول قابل نمایش است: QR، ورود سریع موبایلی، رضایت‌نامه، ثبت بازدید و داشبورد.
- داده‌ها از دیتابیس محلی می‌آیند و صرفا متن ثابت روی صفحه نیستند.
- فونت فارسی و assetهای اصلی محلی هستند و به CDN فونت وابسته نیستند.
- گام بعدی پیشنهادی برای نزدیک شدن به پایلوت واقعی: چاپ/دانلود QR، فیلترهای صفحه QR، و اتصال OTP واقعی/SMS در محیط staging.
