# Standalone Advertising Requirements

## Purpose

This document records the product requirements that must not be lost during the transition from mock demo to real development.

The current `/demo/ecosystem` page shows standalone advertising as mock front-end data. The real product must support advertising content outside campaign missions, for member shops, hub sub-units, non-member brands, and sponsors.

## Actors

- Member shop or venue partner
- Sub-unit inside a hub or ravaq
- Ravaq or hub manager
- Platform admin
- External non-member brand
- Sponsor

## Required Capabilities

- Each member shop must be able to submit advertising content independently of a mission campaign.
- Each sub-unit inside a hub/ravaq must be able to submit advertising content under its parent hub permissions.
- Non-member brands must be able to request advertising placements, subject to stricter admin review.
- Sponsors must be able to request named placements, sponsored routes, sponsored treasures, sponsored rewards, or display inventory.
- Ad submissions must support content upload, including image/video assets, title, body copy, CTA text, target URL or in-app destination, and validity dates.
- The approval workflow must support ravaq manager review and platform admin review.
- Admins must be able to approve, reject, request edits, schedule, pause, or archive ads.
- Ads must be assignable to fixed displays, mobile displays, QR landing pages, reward pages, map/route pages, and post-mission moments.
- Fixed and mobile display inventory must be modeled separately so each display can have location, hub, status, supported media formats, and schedule slots.
- Ads must support scheduling by date, time window, hub, touchpoint, audience segment, and priority.
- Ads must support budget, impression cap, click/interaction cap, sponsor package, and billing status.
- The system must track impressions, clicks/interactions, attributed visits, attributed purchases where available, and revenue.
- The system must preserve moderation history, reviewer, timestamps, uploaded asset versions, and final published creative.

## Demo Coverage

The current demo covers this conceptually in `/demo/ecosystem`:

- Standalone ads outside campaigns
- Member shop ad request
- Non-member brand ad request
- Sponsor request
- Ravaq/admin approval states
- Display placement table
- Ad performance report

## وضعیت اجرایی تا ۱۴۰۵/۰۵/۰۳

موارد زیر اکنون در کد اصلی Laravel + React پیاده‌سازی شده‌اند:

- مدل‌های واقعی درخواست تبلیغ، محتوای خلاقه، جایگاه، نمایشگر، تایید و رویداد
- فرم مستقل فروشگاه و اسپانسر با بارگذاری JPEG/WebP، پیش‌نمایش و کنترل ۱۶:۹، حداقل `800×450` و حداکثر `250KB`
- ویترین عمومی بدون امتیاز با تصویر ثابت و سیاست پنج انتشار نخست رایگان
- پاپ‌آپ اختیاری امتیازآور با تصویر ثابت، متن، زمان ۸ تا ۱۵ ثانیه و اتصال به مراحل ۲ تا ۹ بازی
- چرخه بازبینی شامل تایید، درخواست اصلاح مستدل، ارسال مجدد، رد مستدل، توقف، فعال‌سازی مجدد و بایگانی
- نمایش دلیل آخرین تصمیم برای فروشگاه، اسپانسر، مدیر هاب و ادمین
- زمان‌بندی محلی تبلیغ تاییدشده توسط مدیر هاب فقط روی نمایشگر فعال، هم‌نوع، هم‌مکان و سازگار با فرمت محتوا
- عملیات مرکزی نمایشگر با محدودسازی داده بر اساس scope نقش
- API نمایشگر با توکن مشتق‌شده از کلید برنامه، محدودسازی نرخ و کنترل تعلق جایگاه/تبلیغ به همان دستگاه
- شناسه یکتای رویداد برای جلوگیری از ثبت تکراری آمار نمایشگر و بازی
- کنترل هم‌زمان سقف نمایش و سقف کلیک
- نمونه‌های تصویری سبک ۱۶:۹ برای بازی، ویترین واحدهای غذایی مستقل و نمایشگر محیطی

موارد زیر هنوز خارج از تکمیل این مرحله‌اند و باید در Backlog تجاری جداگانه مدیریت شوند:

- صورتحساب واقعی، درگاه پرداخت تعرفه و صدور فاکتور تبلیغ‌دهنده
- تسویه درآمد و گزارش مالی قراردادی اسپانسر
- Attribution خرید حضوری بدون اتصال به صندوق/POS واحد تجاری
- CDN و پردازش خودکار تصاویر در محیط Production

## قرارداد امنیت API نمایشگر

- هر درخواست برنامه پخش، heartbeat یا رویداد باید `Authorization: Bearer <device-token>` یا هدر `X-Exploria-Display-Token` داشته باشد.
- توکن از `APP_KEY` و کد نمایشگر مشتق می‌شود و نباید در کد، لاگ یا Inertia props منتشر شود.
- اپراتور مجاز توکن راه‌اندازی هر دستگاه را با دستور `php artisan exploria:display-token {device-code}` دریافت و فقط در تنظیمات امن همان دستگاه ثبت می‌کند.
- رویداد فقط وقتی پذیرفته می‌شود که `placement_id` روی همان نمایشگر، در بازه زمانی فعال و برای تبلیغ تاییدشده زمان‌بندی شده باشد.
- `event_id` یک UUID یکتا است؛ ارسال مجدد همان رخداد نتیجه قبلی را برمی‌گرداند و شمارنده را دوباره افزایش نمی‌دهد.

## چک‌لیست UAT تبلیغات

1. فروشگاه و اسپانسر تبلیغ تصویری را ثبت و پیش‌نمایش کنند.
2. ادمین علت اصلاح یا رد را ثبت کند و تبلیغ‌دهنده همان علت را در پنل خود ببیند.
3. نسخه اصلاح‌شده بدون ایجاد درخواست گم‌شده دوباره وارد صف بررسی شود.
4. تایید تبلیغ محیطی به معنی زمان‌بندی خودکار نباشد.
5. مدیر هاب فقط نمایشگر سازگار و داخل scope خود را ببیند و زمان‌بندی/لغو کند.
6. مدیر مکان وضعیت را فقط پایش کند و عملیات خارج از اختیارش نمایش داده نشود.
7. ویترین عمومی تصویر را بدون امتیاز نمایش دهد؛ پاپ‌آپ مرحله‌ای فقط پس از تعامل اختیاری امتیاز بدهد.
8. توقف یا بایگانی تبلیغ آن را از برنامه پخش و پیشنهادهای فعال خارج کند.

## ترتیب توسعه بعدی

1. اتصال تعرفه، سفارش و صورتحساب به چرخه تاییدشده فعلی.
2. افزودن CDN و پردازش امن تصویر در محیط عملیاتی.
3. اتصال خرید/مصرف پاداش به Attribution تبلیغ.
4. گزارش خروجی مالی و عملکرد برای تبلیغ‌دهنده، مدیر مکان و ادمین.
