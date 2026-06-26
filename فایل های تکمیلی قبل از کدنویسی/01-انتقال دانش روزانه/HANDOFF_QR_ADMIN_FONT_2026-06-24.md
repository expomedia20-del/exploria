# انتقال دانش - مدیریت QR و فونت فارسی - 2026-06-24

## خلاصه وضعیت

در این گام صفحه مدیریت کدهای QR داخل پنل ساخته شد و فونت فارسی محلی `Vazirmatn` به پروژه اضافه شد. مسیر اجرایی سایت همچنان روی سرور محلی زیر آماده است:

```text
http://127.0.0.1:8000
```

صفحه جدید:

```text
http://127.0.0.1:8000/admin/qr-codes
```

## تغییرات اصلی سورس

- `app/Http/Controllers/Admin/QrRegistryController.php`
  - متد `page` برای خروجی Inertia اضافه شد.
  - خروجی JSON قبلی برای `/api/v1/admin/qr-codes` حفظ شد.
- `routes/web.php`
  - route جدید `admin.qr-codes.page` با middlewareهای `auth` و `role:admin,operator,viewer` اضافه شد.
- `resources/js/pages/admin/qr-codes/index.tsx`
  - صفحه عملیاتی مدیریت QR ساخته شد.
  - آمار کل/فعال/پایلوت و جدول QRها نمایش داده می‌شود.
  - دکمه باز کردن هر QR به مسیر `/scan/{code}` وصل است.
- `resources/js/components/app-sidebar.tsx`
  - آیتم «مدیریت QR» به منوی پنل اضافه شد.
- `resources/js/components/nav-main.tsx`
  - عنوان گروه منو به «عملیات» تغییر کرد.
- `resources/js/app.tsx` و `resources/css/app.css`
  - فونت محلی `@fontsource-variable/vazirmatn` وارد شد.
  - `--font-sans` روی `Vazirmatn Variable` تنظیم شد.
- `tests/Feature/Venue/PilotLocationQrTest.php`
  - تست دسترسی viewer به صفحه رجیستری QR اضافه شد.

## وابستگی جدید

```text
@fontsource-variable/vazirmatn@5.2.8
```

این وابستگی در `package.json` و `package-lock.json` ثبت شده است.

## نتایج اعتبارسنجی

- `composer test`: موفق
  - Pint: موفق
  - PHPStan: موفق
  - PHPUnit: موفق، 67 تست و 274 assertion
- `npm run format:check`: موفق
- `npm run types:check`: موفق
- `npm run lint:check`: موفق
- `npm run build`: موفق
- `php artisan route:list --except-vendor`: موفق، مسیر `/admin/qr-codes` اضافه شد.
- تست مرورگری صفحه `/admin/qr-codes`: موفق
  - عنوان صفحه دیده شد.
  - کد دمو `ep1405-a7f3k9m2q8x4` دیده شد.
  - مکان پایلوت اکوپارک عباس آباد دیده شد.
  - فونت body برابر `Vazirmatn Variable` بود.
  - خطای console دیده نشد.

## نکته مهم برای build

برای `npm run build` باید مسیر PHP هم داخل `PATH` باشد، چون Wayfinder هنگام build دستور `php artisan wayfinder:generate --with-form` را اجرا می‌کند:

```powershell
$env:Path='E:\exploria-toolchain-local\php;E:\فایل 1 اصلی پروژه اکسپلوریا\فایل های تکمیلی قبل از کدنویسی\.toolchain\node;' + $env:Path
npm run build
```

## وضعیت قابل ادامه

آخرین commit قبل از این گام:

```text
4c405b0 feat: record pilot visits and show operational dashboard
```

بعد از commit این گام، hash جدید باید در همین پوشه و در manifest بکاپ ثبت شود.

آخرین commit بعد از این گام:

```text
9da010b feat: add qr registry UI and Persian font
```

گام پیشنهادی بعدی:

1. ساخت ابزار چاپ/دانلود QR برای اپراتور
2. اضافه کردن فیلتر/جستجو در صفحه مدیریت QR
3. شروع طراحی مدل عمیق‌تر برای campaign، touchpoint و scan events
