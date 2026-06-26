# 20 — EXPLORIA AI Development Framework v1.0

## Laravel + React Monolithic Application Rules

**Document Status:** MVP Development Control Framework  
**Version:** v1.0  
**Project:** EXPLORIA اکوسیستم  
**Purpose:** ایجاد چهارچوب، قوانین و استانداردهای لازم برای توسعه برنامه توسط AI و Developer، با تمرکز بر کیفیت فنی، توسعه‌پذیری، خوانایی، امنیت، تست‌پذیری و انطباق کامل با زبان فارسی.

---

## 1. هدف سند

این سند برای هدایت توسعه فنی MVP اکسپلوریا تهیه شده است تا در زمان استفاده از AI برای تولید کد، خروجی‌ها از نظر معماری، کیفیت، امنیت، خوانایی، توسعه‌پذیری و تجربه کاربری کنترل‌شده باشند.

این سند جایگزین BRD، FRD، MVP Scope Lock یا UAT نیست؛ بلکه سند مکمل فنی برای کنترل کیفیت تولید کد توسط AI و Developer است.

---

## 2. اصل راهبردی پروژه

پروژه EXPLORIA در فاز MVP باید با رویکرد زیر توسعه یابد:

- Backend با Laravel
- Frontend با React
- معماری Monolithic در یک پروژه واحد
- استفاده از یک Codebase یکپارچه
- تمرکز بر زبان فارسی، راست‌چین بودن کامل و تجربه کاربری مدرن
- توسعه مرحله‌ای، قابل تست و قابل تحویل به Developer
- جلوگیری از تولید کد پراکنده، غیرقابل نگهداری یا خارج از Scope توسط AI

---

## 3. معماری کلان پیشنهادی

### 3.1 نوع معماری

معماری پروژه باید به صورت **Laravel + React Monolith** باشد.

یعنی:

- Laravel هسته اصلی برنامه، Routing اصلی، Authentication، API داخلی، Business Logic و Database Layer را مدیریت می‌کند.
- React برای صفحات تعاملی، داشبوردها، کامپوننت‌ها و رابط کاربری استفاده می‌شود.
- پروژه نباید به دو Repository جداگانه Backend و Frontend تقسیم شود، مگر در نسخه‌های آینده و پس از تصمیم معماری رسمی.
- Build و Deployment باید از یک پروژه واحد انجام شود.

### 3.2 الگوی اتصال Laravel و React

برای MVP، یکی از این دو الگو قابل قبول است:

#### گزینه اول — Preferred

**Laravel + React + Inertia-style Architecture**

در این حالت:

- Laravel مسیرها و کنترلرها را مدیریت می‌کند.
- React صفحات Frontend را رندر می‌کند.
- ارتباط بین Backend و Frontend ساده‌تر، Monolithic و قابل کنترل‌تر است.
- برای MVP مناسب‌تر است، چون پیچیدگی API-first را کاهش می‌دهد.

#### گزینه دوم — Alternative

**Laravel Internal API + React SPA داخل همان پروژه**

در این حالت:

- React داخل پروژه Laravel قرار می‌گیرد.
- APIها توسط Laravel ارائه می‌شوند.
- Routing بخشی از Frontend و بخشی از Backend کنترل می‌شود.
- این روش فقط در صورتی مجاز است که Developer نیاز قطعی به SPA داخلی داشته باشد.

### 3.3 ممنوعیت معماری در MVP

در MVP موارد زیر ممنوع است مگر با تایید رسمی Product Owner:

- Microservices
- Repository جداگانه برای Frontend
- Repository جداگانه برای Backend
- API Gateway مستقل
- Queueهای پیچیده بدون نیاز MVP
- Event-driven architecture سنگین
- استفاده از تکنولوژی‌های اضافی بدون نیاز مستقیم MVP
- تولید کد صرفاً به دلیل پیشنهاد AI بدون ارتباط با Scope

---

## 4. ساختار پیشنهادی پروژه

ساختار پروژه باید ساده، قابل فهم و قابل توسعه باشد.

```text
exploria/
├── app/
│   ├── Actions/
│   ├── DTOs/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Policies/
│   ├── Services/
│   └── Support/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
├── resources/
│   ├── js/
│   │   ├── components/
│   │   ├── layouts/
│   │   ├── pages/
│   │   ├── hooks/
│   │   ├── utils/
│   │   ├── types/
│   │   └── app.jsx or app.tsx
│   ├── css/
│   └── views/
├── routes/
│   ├── web.php
│   ├── api.php
│   └── auth.php
├── tests/
│   ├── Feature/
│   └── Unit/
├── package.json
├── vite.config.js
└── composer.json
```

---

## 5. قوانین عمومی توسعه توسط AI

### 5.1 اصل کنترل خروجی AI

AI فقط باید بر اساس اسناد تاییدشده پروژه کد تولید کند.

اسناد مرجع:

- BRD v1.1
- FRD v1.1
- Product Backlog
- Technical Design Pack
- UI/UX Wireframe Pack
- MVP Scope Lock
- MVP Delivery Control
- MVP UAT Acceptance Criteria
- MVP Readiness Lock Register

هر خروجی AI که خارج از این اسناد باشد باید به عنوان **Out of Scope** علامت‌گذاری شود.

### 5.2 قوانین تولید کد توسط AI

AI در زمان تولید کد باید:

- ابتدا هدف Feature را توضیح دهد.
- فایل‌هایی که تغییر می‌دهد را مشخص کند.
- فقط کد لازم برای همان Feature را تولید کند.
- از تولید کدهای غیرضروری، Placeholderهای زیاد یا معماری پیچیده خودداری کند.
- برای هر Feature، مسیر تست و Acceptance Criteria را مشخص کند.
- کد را طوری بنویسد که Developer بتواند آن را بازبینی و اصلاح کند.

### 5.3 ممنوعیت‌های AI

AI نباید:

- ساختار پروژه را بدون مجوز تغییر بنیادی دهد.
- فایل‌های زیاد و بی‌ارتباط ایجاد کند.
- Business Logic را در React قرار دهد.
- Validation را فقط در Frontend انجام دهد.
- داده حساس را در کد Hardcode کند.
- توکن، رمز، API Key یا Credential تولید یا ذخیره کند.
- Migrationهای غیرقابل بازگشت یا مخرب ایجاد کند.
- Feature خارج از MVP اضافه کند.
- UI انگلیسی یا چپ‌چین تولید کند.
- متن‌های فارسی را با ترجمه ماشینی ضعیف یا نامفهوم تولید کند.

---

## 6. قوانین Backend با Laravel

### 6.1 اصول Backend

Backend باید:

- مسئول Business Logic اصلی باشد.
- مسئول Validation سمت سرور باشد.
- مسئول Authorization و Role Access باشد.
- مسئول اتصال به Database باشد.
- مسئول تولید پاسخ‌های قابل اعتماد برای Frontend باشد.

### 6.2 Controller Rules

Controller باید سبک و خوانا باشد.

Controller نباید شامل موارد زیر باشد:

- Queryهای پیچیده طولانی
- Business Logic سنگین
- محاسبات چندمرحله‌ای
- کد تکراری
- تصمیم‌گیری‌های پیچیده مربوط به Domain

Controller فقط باید:

- Request را دریافت کند.
- Validation را از Form Request بگیرد.
- Service یا Action مناسب را فراخوانی کند.
- Response مناسب برگرداند.

### 6.3 Service / Action Rules

Business Logic باید در Service یا Action قرار گیرد.

قانون پیشنهادی:

- برای عملیات ساده و مستقل از `Actions` استفاده شود.
- برای منطق چندمرحله‌ای یا دامنه‌ای از `Services` استفاده شود.

نمونه نام‌گذاری:

```text
app/Actions/CreatePilotRevenueRecord.php
app/Services/PilotDashboardSummaryService.php
```

### 6.4 Request Validation

تمام ورودی‌های مهم باید با Form Request اعتبارسنجی شوند.

قابل قبول:

```text
app/Http/Requests/StorePilotRevenueRequest.php
```

غیرقابل قبول:

- Validation پراکنده داخل Controller
- اعتماد به Validation سمت Frontend
- ذخیره داده بدون Sanitization و Validation

### 6.5 Model Rules

Modelها باید ساده و دامنه‌محور باشند.

Model مجاز است شامل موارد زیر باشد:

- Relationships
- Casts
- Scopes ساده
- Accessors/Mutators محدود

Model نباید شامل Business Logic سنگین باشد.

### 6.6 Database Rules

Migrationها باید:

- قابل بازبینی باشند.
- نام‌گذاری شفاف داشته باشند.
- از نوع داده مناسب استفاده کنند.
- برای کلیدهای خارجی و Indexها طراحی دقیق داشته باشند.
- از حذف داده در Migration بدون تایید جلوگیری کنند.

Seederها باید:

- داده نمونه MVP تولید کنند.
- داده تستی قابل تشخیص داشته باشند.
- با داده واقعی اشتباه گرفته نشوند.

### 6.7 API / Response Rules

Responseها باید ساختارمند باشند.

الگوی پیشنهادی:

```json
{
  "status": "success",
  "message": "عملیات با موفقیت انجام شد.",
  "data": {}
}
```

برای خطا:

```json
{
  "status": "error",
  "message": "درخواست معتبر نیست.",
  "errors": {}
}
```

### 6.8 Authorization Rules

تمام مسیرهای مدیریتی باید تحت کنترل Authorization باشند.

حداقل نقش‌های MVP:

- Admin
- Operator / Staff
- Viewer / Read-only, if required

هیچ مسیر مدیریتی نباید بدون کنترل دسترسی قابل استفاده باشد.

---

## 7. قوانین Frontend با React

### 7.1 اصول Frontend

React باید فقط مسئول تجربه کاربری، نمایش داده، تعامل کاربر و مدیریت State رابط باشد.

React نباید مسئول Business Logic اصلی پروژه باشد.

### 7.2 ساختار Frontend

ساختار پیشنهادی:

```text
resources/js/
├── components/
│   ├── ui/
│   ├── forms/
│   ├── dashboard/
│   └── shared/
├── layouts/
│   ├── AppLayout.jsx
│   ├── AuthLayout.jsx
│   └── AdminLayout.jsx
├── pages/
│   ├── auth/
│   ├── dashboard/
│   ├── pilot/
│   └── admin/
├── hooks/
├── utils/
├── types/
└── constants/
```

### 7.3 Component Rules

کامپوننت‌ها باید:

- کوچک و قابل استفاده مجدد باشند.
- فقط یک مسئولیت اصلی داشته باشند.
- نام‌گذاری شفاف داشته باشند.
- از Props واضح استفاده کنند.
- در صورت پیچیده شدن، به چند کامپوننت کوچک‌تر تقسیم شوند.

کامپوننت نباید:

- بیش از حد طولانی باشد.
- چندین مسئولیت نامرتبط داشته باشد.
- Query یا Logic مربوط به Backend را در خود نگه دارد.
- متن‌های ثابت پراکنده و بدون ساختار داشته باشد.

### 7.4 Page Rules

هر Page باید:

- Layout مشخص داشته باشد.
- عنوان فارسی مشخص داشته باشد.
- Loading State داشته باشد.
- Empty State داشته باشد.
- Error State داشته باشد.
- در موبایل و دسکتاپ قابل استفاده باشد.

### 7.5 State Management Rules

برای MVP:

- از State محلی React استفاده شود، مگر نیاز واقعی وجود داشته باشد.
- از ابزارهای پیچیده State Management بدون ضرورت استفاده نشود.
- Stateهای فرم از Stateهای داده جدا نگه داشته شوند.

ممنوع مگر با تایید:

- State Management سنگین و غیرضروری
- Global Store برای داده‌های ساده
- نگهداری داده حساس در Local Storage

---

## 8. قوانین طراحی فارسی و RTL

### 8.1 اصل زبان فارسی

رابط کاربری MVP باید کاملاً فارسی باشد.

الزامات:

- جهت کلی صفحه: `rtl`
- زبان صفحه: `fa`
- فونت مناسب فارسی
- اعداد و تاریخ‌ها قابل خواندن برای کاربر فارسی‌زبان
- متن‌ها رسمی، ساده و حرفه‌ای
- عدم استفاده از متن انگلیسی در UI نهایی، مگر برای اصطلاحات فنی ضروری

### 8.2 طراحی راست‌چین

تمام اجزای UI باید راست‌چین باشند:

- Sidebar
- Navbar
- فرم‌ها
- جدول‌ها
- کارت‌ها
- پیام‌های خطا
- Modalها
- Dropdownها
- Breadcrumbها

### 8.3 فونت فارسی

فونت پیشنهادی:

- Vazirmatn
- IRANSans, در صورت وجود لایسنس معتبر
- Estedad
- Shabnam

قانون:

- نباید از فونت‌های نامناسب فارسی مثل Arial به عنوان فونت اصلی استفاده شود.
- فونت باید خوانا، مدرن و مناسب داشبورد مدیریتی باشد.

### 8.4 لحن متن‌های رابط کاربری

لحن UI باید:

- محترمانه
- رسمی ولی ساده
- کوتاه
- قابل فهم
- بدون اصطلاحات پیچیده

نمونه‌های قابل قبول:

```text
ورود به حساب کاربری
داشبورد مدیریتی
گزارش درآمد پایلوت
اطلاعاتی برای نمایش وجود ندارد.
عملیات با موفقیت انجام شد.
خطایی رخ داد. لطفاً دوباره تلاش کنید.
```

نمونه‌های نامناسب:

```text
Login
Submit
Dashboard
Something went wrong
No data found
```

---

## 9. اصول طراحی UI مدرن

### 9.1 سبک بصری

طراحی باید:

- مدرن
- مینیمال
- تمیز
- مناسب سامانه مدیریتی
- قابل اعتماد
- سبک و سریع
- بدون شلوغی بصری

### 9.2 اصول Layout

رابط کاربری باید شامل موارد زیر باشد:

- Header یا Top Bar واضح
- Sidebar مدیریتی در سمت راست
- فضای محتوای اصلی با فاصله‌گذاری مناسب
- کارت‌های آماری واضح
- جدول‌های خوانا
- فرم‌های ساده و مرحله‌ای در صورت نیاز

### 9.3 اصول رنگ

رنگ‌ها باید محدود، حرفه‌ای و سازگار باشند.

پیشنهاد:

- رنگ اصلی: آبی عمیق یا آبی سازمانی
- رنگ ثانویه: خاکستری روشن
- رنگ موفقیت: سبز کنترل‌شده
- رنگ هشدار: نارنجی یا زرد کنترل‌شده
- رنگ خطا: قرمز استاندارد

قانون:

- رنگ نباید بیش از حد زیاد و نامنظم باشد.
- هر رنگ باید معنا داشته باشد.
- تضاد رنگی باید برای خوانایی کافی باشد.

### 9.4 Responsive Design

تمام صفحات MVP باید در اندازه‌های زیر قابل استفاده باشند:

- Desktop
- Laptop
- Tablet
- Mobile, در حد قابل قبول MVP

برای MVP اولویت با Desktop و Laptop است، اما Layout نباید در Mobile کاملاً خراب شود.

---

## 10. قوانین فرم‌ها

فرم‌ها باید:

- Label فارسی واضح داشته باشند.
- Placeholder کوتاه داشته باشند.
- Validation Error فارسی نمایش دهند.
- دکمه اصلی واضح داشته باشند.
- Loading State داشته باشند.
- پس از موفقیت، پیام مناسب نمایش دهند.

نمونه پیام خطا:

```text
وارد کردن عنوان الزامی است.
مقدار واردشده معتبر نیست.
لطفاً یک تاریخ معتبر انتخاب کنید.
```

نمونه پیام موفقیت:

```text
اطلاعات با موفقیت ذخیره شد.
گزارش با موفقیت ثبت شد.
```

---

## 11. قوانین Dashboard

داشبورد MVP باید:

- خلاصه وضعیت پایلوت را نمایش دهد.
- کارت‌های آماری قابل فهم داشته باشد.
- وضعیت‌ها را با رنگ و متن فارسی نمایش دهد.
- در صورت نبود داده، Empty State مناسب داشته باشد.
- اعداد، تاریخ و وضعیت‌ها را قابل فهم نشان دهد.

نمونه وضعیت‌ها:

```text
فعال
در انتظار بررسی
تکمیل‌شده
دارای خطا
نیازمند اقدام
```

---

## 12. قوانین جدول‌ها

جدول‌ها باید:

- عنوان ستون فارسی داشته باشند.
- راست‌چین باشند.
- Loading State داشته باشند.
- Empty State داشته باشند.
- Pagination یا محدودیت نمایش داشته باشند.
- ستون عملیات در سمت مناسب RTL قرار گیرد.

نمونه ستون‌ها:

```text
عنوان
وضعیت
تاریخ ثبت
ثبت‌کننده
عملیات
```

---

## 13. قوانین امنیتی

### 13.1 Authentication

تمام بخش‌های مدیریتی باید نیازمند ورود باشند.

### 13.2 Authorization

هر عملیات مهم باید کنترل سطح دسترسی داشته باشد.

### 13.3 Input Security

تمام ورودی‌ها باید سمت Backend اعتبارسنجی شوند.

### 13.4 Sensitive Data

موارد زیر نباید در کد ذخیره شود:

- Password
- API Key
- Token
- Secret
- Production Credential

### 13.5 Error Exposure

خطاهای فنی نباید مستقیم به کاربر نهایی نمایش داده شوند.

قابل قبول:

```text
خطایی رخ داد. لطفاً دوباره تلاش کنید.
```

غیرقابل قبول:

```text
SQLSTATE[23000]: Integrity constraint violation...
```

---

## 14. قوانین کیفیت کد

کد باید:

- ساده
- خوانا
- قابل تست
- قابل توسعه
- قابل بازبینی
- کم‌تکرار
- دارای نام‌گذاری واضح

### 14.1 Naming Rules

نام‌ها باید معنی‌دار باشند.

قابل قبول:

```text
PilotDashboardSummaryService
StoreRevenueRecordRequest
AdminDashboardPage
RevenueStatusBadge
```

غیرقابل قبول:

```text
DataService
TestController
NewPage
Comp1
Helper2
```

### 14.2 Comment Rules

Comment فقط زمانی مجاز است که دلیل تصمیم یا منطق پیچیده را توضیح دهد.

Comment برای توضیح کد بدیهی ممنوع است.

### 14.3 Duplication Rules

کد تکراری باید استخراج شود به:

- Component
- Hook
- Service
- Action
- Utility

---

## 15. قوانین تست

### 15.1 Backend Tests

برای Backend حداقل تست‌های زیر لازم است:

- Authentication Test
- Authorization Test
- Validation Test
- Feature Test برای مسیرهای اصلی MVP
- Test برای Dashboard Summary
- Test برای ثبت و بازیابی داده‌های پایلوت

### 15.2 Frontend Tests

برای Frontend، در MVP حداقل موارد زیر باید کنترل شود:

- Rendering صفحات اصلی
- نمایش Loading State
- نمایش Error State
- نمایش Empty State
- صحت RTL Layout
- صحت متن‌های فارسی کلیدی

### 15.3 Manual QA

قبل از تحویل MVP باید موارد زیر دستی بررسی شوند:

- ورود کاربر
- مشاهده داشبورد
- مشاهده خلاصه پایلوت
- ثبت یا مشاهده داده‌های اصلی MVP
- کنترل خطاها
- واکنش‌گرایی اولیه
- عدم وجود متن انگلیسی ناخواسته
- راست‌چین بودن صفحات

---

## 16. قوانین Git و Commit

### 16.1 Branch Rules

ساختار پیشنهادی:

```text
main
staging
develop
feature/mvp-dashboard
feature/pilot-revenue
fix/rtl-layout
```

### 16.2 Commit Rules

Commitها باید کوچک و قابل فهم باشند.

نمونه قابل قبول:

```text
feat: add pilot dashboard summary page
fix: correct RTL alignment in admin sidebar
test: add validation tests for revenue records
refactor: move dashboard logic to service layer
```

نمونه نامناسب:

```text
update
fix
new changes
final final
```

---

## 17. قوانین Pull Request / Code Review

هر Pull Request باید شامل موارد زیر باشد:

- خلاصه تغییرات
- Feature یا سند مرجع
- فایل‌های اصلی تغییر یافته
- Screenshot برای UI
- تست‌های انجام‌شده
- ریسک‌های احتمالی
- وضعیت آمادگی برای Merge

هیچ Pull Request نباید بدون Review وارد Branch اصلی شود.

---

## 18. قوانین استفاده از AI Prompt برای توسعه

### 18.1 Prompt استاندارد برای تولید Feature

AI باید با Prompt کنترل‌شده استفاده شود.

الگوی پیشنهادی:

```text
You are developing EXPLORIA MVP.
Tech stack: Laravel backend + React frontend in a single monolithic project.
UI language: Persian only.
Layout direction: RTL.
Do not create out-of-scope features.
Follow the approved MVP Scope Lock and UAT Acceptance Criteria.

Task:
[شرح دقیق Feature]

Rules:
- Keep Laravel controllers thin.
- Put business logic in Services or Actions.
- Use Form Requests for validation.
- Keep React components small and reusable.
- Use Persian UI texts.
- Respect RTL layout.
- Provide changed files list.
- Provide test checklist.
```

### 18.2 Prompt استاندارد برای اصلاح کد

```text
Review the following code for EXPLORIA MVP.
Check it against:
- Laravel best practices
- React component quality
- Monolithic architecture rules
- Persian RTL UI compliance
- Security issues
- Maintainability
- Testability

Return:
1. Critical issues
2. Suggested fixes
3. Refactored code only if necessary
4. Test checklist
```

### 18.3 Prompt استاندارد برای UI فارسی

```text
Design or improve this React UI for a Persian RTL admin dashboard.
Requirements:
- All text must be Persian.
- Direction must be RTL.
- Layout must be modern, clean, and professional.
- Avoid visual clutter.
- Include loading, empty, and error states.
- Use reusable components.
- Do not use English UI labels unless technically unavoidable.
```

---

## 19. Definition of Done برای هر Feature

هر Feature فقط زمانی Done محسوب می‌شود که موارد زیر انجام شده باشد:

- کد Backend تکمیل شده باشد.
- کد Frontend تکمیل شده باشد.
- Validation سمت Backend وجود داشته باشد.
- Authorization لازم اعمال شده باشد.
- UI فارسی و RTL باشد.
- Loading State وجود داشته باشد.
- Empty State وجود داشته باشد.
- Error State وجود داشته باشد.
- تست یا چک‌لیست تست انجام شده باشد.
- Developer Review انجام شده باشد.
- Feature با MVP Scope Lock منطبق باشد.
- هیچ متن انگلیسی ناخواسته در UI باقی نمانده باشد.
- هیچ Secret یا داده حساس در کد قرار نگرفته باشد.

---

## 20. MVP Technical Acceptance Gate

قبل از اعلام آمادگی MVP، موارد زیر باید تایید شوند:

| Gate | Requirement | Status |
|---|---|---|
| G1 | پروژه در یک Codebase واحد Laravel + React قرار دارد | Pending |
| G2 | Backend مسیرهای اصلی MVP را پشتیبانی می‌کند | Pending |
| G3 | Frontend فارسی و RTL است | Pending |
| G4 | داشبورد MVP قابل مشاهده است | Pending |
| G5 | Authentication فعال است | Pending |
| G6 | Authorization برای مسیرهای مدیریتی فعال است | Pending |
| G7 | داده نمونه MVP قابل مشاهده است | Pending |
| G8 | Error / Empty / Loading States وجود دارد | Pending |
| G9 | تست‌های پایه انجام شده‌اند | Pending |
| G10 | UAT Criteria قابل اجرا است | Pending |

---

## 21. AI Output Review Checklist

هر خروجی AI باید با این چک‌لیست بررسی شود:

```text
[ ] آیا خروجی با MVP Scope منطبق است؟
[ ] آیا معماری Monolithic حفظ شده است؟
[ ] آیا Backend با Laravel و Frontend با React رعایت شده است؟
[ ] آیا Business Logic در Backend قرار دارد؟
[ ] آیا Controllerها سبک هستند؟
[ ] آیا Validation سمت Backend وجود دارد؟
[ ] آیا UI کاملاً فارسی است؟
[ ] آیا Layout راست‌چین است؟
[ ] آیا کد قابل توسعه است؟
[ ] آیا نام‌گذاری‌ها واضح هستند؟
[ ] آیا کد تکراری غیرضروری وجود ندارد؟
[ ] آیا تست یا چک‌لیست تست ارائه شده است؟
[ ] آیا داده حساس در کد وجود ندارد؟
[ ] آیا خروجی خارج از Scope تولید نشده است؟
```

---

## 22. Non-Functional Requirements برای MVP

### 22.1 Performance

- صفحات اصلی باید سبک و سریع باشند.
- Queryهای سنگین باید بهینه شوند.
- داده‌های داشبورد نباید با چندین Request غیرضروری دریافت شوند.

### 22.2 Maintainability

- ساختار پوشه‌ها باید ثابت بماند.
- کد باید قابل توسعه برای نسخه‌های بعدی باشد.
- Featureها باید مستقل و قابل فهم باشند.

### 22.3 Accessibility

- دکمه‌ها باید متن واضح داشته باشند.
- کنتراست رنگ‌ها باید مناسب باشد.
- فرم‌ها باید Label قابل فهم داشته باشند.
- فقط به رنگ برای انتقال مفهوم وضعیت تکیه نشود.

### 22.4 Localization

- UI باید فارسی باشد.
- جهت باید RTL باشد.
- تاریخ‌ها و اعداد باید برای کاربر فارسی‌زبان قابل فهم باشند.

---

## 23. Change Control برای AI Development

هر تغییر مهم باید ثبت شود:

| Change ID | Description | Reason | Scope Impact | Approved By | Status |
|---|---|---|---|---|---|
| CHG-MVP-001 | Initial AI Development Framework | کنترل کیفیت توسعه توسط AI | داخل Scope | Product Owner | Draft |

تغییراتی که نیاز به تایید دارند:

- تغییر معماری پروژه
- اضافه شدن Feature جدید
- تغییر Roleها
- تغییر ساختار داده اصلی
- تغییر مسیرهای اصلی Dashboard
- تغییر زبان یا جهت UI
- استفاده از Packageهای سنگین

---

## 24. Package Usage Rules

هر Package جدید باید قبل از استفاده بررسی شود:

- آیا واقعاً برای MVP لازم است؟
- آیا نگهداری می‌شود؟
- آیا باعث پیچیدگی غیرضروری می‌شود؟
- آیا با Laravel + React Monolith سازگار است؟
- آیا جایگزین ساده‌تر دارد؟

ممنوع:

- نصب Package صرفاً به پیشنهاد AI
- استفاده از Packageهای ناشناخته برای بخش‌های حساس
- اضافه کردن وابستگی‌های زیاد برای کارهای ساده

---

## 25. Documentation Rules

Developer باید برای هر Feature مهم موارد زیر را ثبت کند:

- هدف Feature
- مسیرهای Backend
- صفحه یا Componentهای Frontend
- داده‌های ورودی و خروجی
- Validationها
- دسترسی‌ها
- تست انجام‌شده
- محدودیت‌ها

---

## 26. Final Rule

در توسعه MVP اکسپلوریا، اصل حاکم این است:

> AI باید سرعت توسعه را افزایش دهد، اما نباید معماری، کیفیت، امنیت، زبان فارسی، RTL بودن، Scope محصول یا کنترل پروژه را قربانی کند.

هیچ کدی صرفاً به دلیل اینکه AI آن را تولید کرده است قابل قبول نیست. هر خروجی باید با این سند، اسناد MVP و معیارهای پذیرش پروژه تطبیق داده شود.

---

## 27. Approval Status

| Role | Name | Decision | Date |
|---|---|---|---|
| Product Owner | TBD | Pending | TBD |
| Technical Lead / Developer | TBD | Pending | TBD |
| QA / Reviewer | TBD | Pending | TBD |

**Current Status:** Ready for Product Owner Review and Developer Alignment
