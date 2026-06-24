# EXPLORIA Real Product Phase 1 Plan

این سند نقطه شروع توسعه واقعی پس از دموی کارفرمایی است. هدف فاز ۱ این نیست که همه اکوسیستم را یک‌باره بسازیم؛ هدف این است که ستون فقرات محصول واقعی تثبیت شود تا پنل فروشگاه، مدیر رواق، گنج، پاداش، تبلیغات مستقل و اسپانسرها روی پایه درست توسعه پیدا کنند.

## وضعیت فعلی

### پیاده‌سازی واقعی موجود

- ورود با موبایل و OTP محلی
- رضایت‌نامه فارسی
- ساختار پایه مکان: `Venue`, `Zone`, `Hub`, `Touchpoint`
- رجیستری QR
- مسیر اسکن QR
- ثبت Visit تاییدشده
- داشبورد عملیاتی پایه
- نقش‌های فعلی: `admin`, `operator`, `viewer`

### دمو/ماک موجود

- `/board`: ورودی رسمی جلسه
- `/demo/ecosystem`: تصویر کامل اکوسیستم با mock data
- `/demo/missions`: شبیه‌ساز ماموریت، امتیاز، پاداش و سطح
- `/demo/proposal`: تطبیق دمو با پروپوزال اکوپارک

### هنوز واقعی نشده

- پنل فروشگاه
- پنل مدیر رواق
- پنل مدیر کل کامل
- تعریف گنج‌ها
- ماموریت‌های واقعی
- پاداش، کوپن، تخفیف و ضدتقلب پاداش
- تبلیغات مستقل از کمپین
- نمایشگر ثابت/سیار
- اسپانسرینگ
- گزارش‌های مالی و attribution تجاری

## هدف فاز ۱

ساخت پایه واقعی اکوسیستم برای اکوپارک عباس‌آباد:

1. تکمیل مدل مکان، هاب، رواق و زیرمجموعه‌ها
2. آماده‌سازی نقش‌ها و سطح دسترسی واقعی
3. واقعی‌سازی مدیریت QR و کمپین پایه
4. ایجاد اسکلت فروشگاه/شریک تجاری
5. ایجاد اسکلت ماموریت، گنج و پاداش
6. ایجاد اسکلت تبلیغات مستقل و نمایشگرها
7. آماده‌سازی dashboard مدیریتی برای داده واقعی

## نقش‌های محصول واقعی

### Visitor

بازدیدکننده‌ای که با QR وارد می‌شود، ماموریت انجام می‌دهد، امتیاز می‌گیرد، پاداش مصرف می‌کند و مسیر پیشنهادی بعدی را می‌بیند.

### Shop Partner

فروشگاه یا کسب‌وکار عضو که تخفیف، پیشنهاد، پاداش یا تبلیغ ثبت می‌کند و گزارش مراجعه و مصرف کد را می‌بیند.

### Hub/Ravaq Manager

مدیر هاب یا رواق که فروشگاه‌ها، ظرفیت کمپین، تبلیغات، پاداش‌ها و تاییدهای محدوده خود را مدیریت می‌کند.

### Platform Admin

مدیر کل پلتفرم که همه مکان‌ها، نقش‌ها، QRها، کمپین‌ها، قوانین، تبلیغات، نمایشگرها و گزارش‌ها را کنترل می‌کند.

### Sponsor / External Brand

برند عضو یا غیرعضو که تبلیغ، اسپانسرینگ مسیر، اسپانسرینگ گنج یا جایگاه نمایش درخواست می‌کند.

## مدل داده پیشنهادی فاز ۱

### تکمیل مکان و ساختار تجاری

جدول‌های موجود:

- `venues`
- `zones`
- `hubs`
- `touchpoints`
- `campaigns`
- `qr_codes`
- `visits`

جدول‌های جدید پیشنهادی:

- `partner_accounts`: حساب فروشگاه، برند، اسپانسر یا زیرمجموعه هاب
- `partner_locations`: اتصال شریک به venue/zone/hub/touchpoint
- `hub_management_assignments`: اتصال مدیر رواق/هاب به محدوده مدیریتی
- `partner_users`: اتصال کاربران به فروشگاه یا برند با نقش مشخص

### ماموریت، گنج و پاداش

جدول‌های جدید پیشنهادی:

- `mission_templates`: تعریف ماموریت‌ها
- `mission_instances`: ماموریت فعال در یک کمپین/هاب
- `user_mission_progress`: وضعیت انجام ماموریت توسط کاربر
- `treasures`: تعریف گنج ساده، خانوادگی، فروشگاهی، ویژه
- `reward_definitions`: تعریف پاداش، تخفیف، کوپن، هدیه یا قرعه‌کشی
- `user_rewards`: پاداش اختصاص‌یافته به کاربر
- `reward_redemptions`: مصرف پاداش توسط کاربر یا فروشگاه

### تبلیغات مستقل و نمایشگرها

جزئیات کامل‌تر در این سند ثبت شده است:

```text
docs/features/STANDALONE_ADVERTISING_REQUIREMENTS.md
```

جدول‌های جدید پیشنهادی:

- `advertisers`: فروشگاه عضو، برند غیرعضو، اسپانسر
- `ad_requests`: درخواست تبلیغ مستقل از کمپین
- `ad_creatives`: فایل‌ها و محتوای تبلیغاتی
- `ad_placements`: جایگاه نمایش تبلیغ
- `display_devices`: نمایشگر ثابت یا سیار
- `display_schedules`: زمان‌بندی انتشار روی نمایشگرها
- `ad_approvals`: تایید، رد یا درخواست اصلاح توسط رواق/ادمین
- `ad_events`: نمایش، کلیک، تعامل، playback و attribution

### رویدادها و گزارش‌گیری

جدول‌های جدید پیشنهادی:

- `event_logs`: رویدادهای اسکن، شروع ماموریت، تکمیل، پاداش، تبلیغ و مراجعه
- `attribution_links`: اتصال رویداد به کمپین، QR، فروشگاه، تبلیغ یا پاداش
- `daily_kpi_snapshots`: خلاصه روزانه برای داشبورد مدیریتی

## ترتیب اجرای فاز ۱

### Sprint 1.1 - Role & Partner Foundation

خروجی:

- توسعه enum نقش‌ها
- اضافه شدن نقش‌های `visitor`, `shop_partner`, `hub_manager`, `sponsor`
- جدول partner accounts
- اتصال user به partner یا hub
- seed داده اکوپارک برای چند فروشگاه/هاب

معیار پذیرش:

- ادمین بتواند کاربر دارای نقش فروشگاه یا مدیر رواق داشته باشد.
- داده partner در دیتابیس واقعی ذخیره شود.
- تست نقش‌ها و middleware سبز باشد.

### Sprint 1.2 - Venue/Hub Management Hardening

خروجی:

- تکمیل UI/API مدیریت Venue/Zone/Hub/Touchpoint
- افزودن فیلدهای عملیاتی برای هاب و رواق
- اتصال مدیر رواق به محدوده مدیریتی

معیار پذیرش:

- مدیر رواق فقط داده محدوده خودش را ببیند.
- ادمین همه مکان‌ها و هاب‌ها را مدیریت کند.
- QR همچنان به touchpoint معتبر وصل بماند.

### Sprint 1.3 - QR & Campaign Core

خروجی:

- مدیریت واقعی campaign پایه
- اتصال QR به campaign و touchpoint
- وضعیت QR، اعتبار زمانی، محدودیت اسکن و anti-duplicate
- UI بهبود یافته QR Registry

معیار پذیرش:

- ادمین بتواند QR جدید برای یک touchpoint بسازد.
- اسکن QR invalid/expired درست کنترل شود.
- visit attribution به venue/campaign/touchpoint حفظ شود.

### Sprint 1.4 - Mission/Treasure/Reward Skeleton

خروجی:

- مدل ماموریت
- مدل گنج
- مدل پاداش
- progress کاربر
- پاداش mock به پاداش دیتابیسی تبدیل شود.

معیار پذیرش:

- کاربر بتواند یک ماموریت واقعی را شروع و تکمیل کند.
- امتیاز و پاداش در دیتابیس ثبت شود.
- داشبورد بتواند تعداد ماموریت و پاداش را از دیتابیس بخواند.

### Sprint 1.5 - Partner Offer & Redemption Skeleton

خروجی:

- فروشگاه بتواند پیشنهاد/تخفیف ثبت کند.
- مدیر رواق یا ادمین پیشنهاد را تایید کند.
- کاربر بتواند پاداش/کد را مصرف کند.
- فروشگاه مصرف را تایید کند.

معیار پذیرش:

- پیشنهاد فروشگاه بدون تایید منتشر نشود.
- مصرف پاداش دوباره قابل تکرار نباشد.
- گزارش مصرف فروشگاه قابل مشاهده باشد.

### Sprint 1.6 - Standalone Ads Skeleton

خروجی:

- ثبت درخواست تبلیغ مستقل
- مدل creative و placement
- وضعیت تایید رواق/ادمین
- مدل نمایشگر ثابت/سیار
- گزارش رویداد نمایش/کلیک به صورت پایه

معیار پذیرش:

- فروشگاه یا برند بتواند درخواست تبلیغ ثبت کند.
- ادمین بتواند درخواست را تایید/رد کند.
- تبلیغ تاییدشده به جایگاه نمایش و بازه زمانی وصل شود.
- event پایه نمایش و کلیک ثبت شود.

## API Surface پیشنهادی

### Admin

- `GET /api/v1/admin/venues`
- `GET /api/v1/admin/hubs`
- `GET /api/v1/admin/partners`
- `POST /api/v1/admin/partners`
- `GET /api/v1/admin/campaigns`
- `POST /api/v1/admin/campaigns`
- `GET /api/v1/admin/qr-codes`
- `POST /api/v1/admin/qr-codes`

### Mission & Reward

- `GET /api/v1/missions/available`
- `POST /api/v1/missions/{mission}/start`
- `POST /api/v1/missions/{mission}/complete`
- `GET /api/v1/rewards/wallet`
- `POST /api/v1/rewards/{reward}/redeem`

### Partner

- `GET /api/v1/partner/dashboard`
- `POST /api/v1/partner/offers`
- `GET /api/v1/partner/redemptions`
- `POST /api/v1/partner/redemptions/{code}/confirm`

### Advertising

- `POST /api/v1/ads/requests`
- `GET /api/v1/admin/ads/requests`
- `POST /api/v1/admin/ads/requests/{adRequest}/approve`
- `POST /api/v1/admin/ads/requests/{adRequest}/reject`
- `GET /api/v1/display/schedule`
- `POST /api/v1/ads/events`

## UI اولویت‌دار

### Admin

- مدیریت مکان/هاب/رواق
- مدیریت QR
- مدیریت کمپین
- مدیریت نقش‌ها
- تایید پیشنهاد و تبلیغ
- داشبورد KPI

### Hub/Ravaq Manager

- فروشگاه‌های محدوده
- پیشنهادهای در انتظار تایید
- تبلیغات در انتظار تایید
- ظرفیت کمپین
- گزارش محدوده

### Shop Partner

- پروفایل فروشگاه
- تعریف تخفیف/پاداش
- تایید مصرف کد
- ثبت تبلیغ مستقل
- گزارش مراجعه و مصرف

### Visitor

- ماموریت‌های نزدیک
- مسیر پیشنهادی
- کیف پاداش
- وضعیت سطح
- مصرف پاداش

## قواعد مهم توسعه

- هیچ mock demo نباید مستقیماً به عنوان رفتار واقعی محسوب شود مگر اینکه دیتابیس، تست و authorization داشته باشد.
- هر قابلیت تجاری باید owner، approval، status و audit trail داشته باشد.
- هر داده‌ای که به درآمد، پاداش، تخفیف یا تبلیغ مربوط است باید event قابل ردگیری داشته باشد.
- نقش‌های مدیریتی باید محدوده دسترسی داشته باشند؛ مدیر رواق نباید کل سیستم را ببیند.
- MVP واقعی باید ساده بماند: ابتدا اکوپارک، سپس توسعه کنترل‌شده به مکان‌های بعدی.

## خارج از فاز ۱

- settlement مالی کامل
- قراردادهای حقوقی کامل شریک‌ها
- real-time display playback پیشرفته
- recommendation engine
- نقشه تعاملی کامل با routing پیشرفته
- اپلیکیشن native
- اتصال SMS production

## Definition of Done فاز ۱

- تست backend و frontend سبز باشد.
- هر جدول جدید migration، model، factory/seed حداقلی و تست داشته باشد.
- هر endpoint جدید authorization داشته باشد.
- داشبورد حداقل KPIهای واقعی را از دیتابیس بخواند.
- مسیر QR فعلی خراب نشود.
- مستند انتقال دانش و بکاپ بعد از هر برش به‌روزرسانی شود.

## اولین اقدام کدنویسی پیشنهادی

شروع از Sprint 1.1:

1. گسترش `UserRole`
2. افزودن جدول‌های `partner_accounts`, `partner_locations`, `partner_users`, `hub_management_assignments`
3. seed چند partner برای اکوپارک
4. ایجاد admin page/API بسیار ساده برای مشاهده partnerها
5. تست role middleware برای نقش‌های جدید

## وضعیت اجرای Sprint 1.1

شروع اجرای Sprint 1.1 انجام شد:

- نقش‌های `visitor`, `shop_partner`, `hub_manager`, `sponsor` به `UserRole` اضافه شدند.
- جدول‌های `partner_accounts`, `partner_locations`, `partner_users`, `hub_management_assignments` اضافه شدند.
- seed اکوپارک با کافه اکو، فروشگاه رواق و اسپانسر مسیر خانوادگی تکمیل شد.
- مسیرهای `GET /admin/partners` و `GET /api/v1/admin/partners` اضافه شدند.
- صفحه ساده admin برای مشاهده partnerها اضافه شد.
