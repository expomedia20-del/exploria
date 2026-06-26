# 21 — EXPLORIA Pre-Coding Master Execution Control v1.0

## 1. کنترل سند

| فیلد | مقدار |
|---|---|
| پروژه | EXPLORIA ECOSYSTEM |
| نسخه | v1.0 |
| تاریخ | 2026-06-20 |
| وضعیت | Pre-Coding Gates 1–3 Locked — Ready for Codebase Bootstrap |
| هدف | ایجاد مرجع واحد برای ادامه عملیات تا شروع کدنویسی کنترل‌شده توسط AI |

## 2. تصمیم معماری مصوب

تصمیم زیر با دستور صریح مالک پروژه در Thread رسمی Codex در تاریخ 2026-06-20 قفل شد:

- Backend با Laravel توسعه می‌یابد.
- Frontend با React توسعه می‌یابد.
- معماری Monolith و یک Codebase/Repository واحد است.
- الگوی ترجیحی MVP، Laravel + React با معماری Inertia-style است.
- رابط کاربری فارسی، RTL، واکنش‌گرا و مدرن است.
- هر تغییر معماری نیازمند Change Request و تأیید صریح مالک پروژه است.

## 3. سلسله‌مراتب مراجع اجرایی

در صورت تعارض، ترتیب زیر اعمال می‌شود:

1. دستور صریح و جدید مالک پروژه
2. `AGENTS.md`
3. AI Development Framework v1.0 — سند 20
4. MVP Scope Lock / Delivery Control / UAT / Readiness Register — اسناد 16 تا 19
5. BRD v1.1 و FRD v1.1
6. Product Backlog و Sprint 0/1 Plan
7. Technical Design، OpenAPI و UI/UX Wireframe
8. بسته‌های Starter و Integration قدیمی، فقط به‌عنوان Reference

## 4. رجیستر نسخه‌های Canonical

| حوزه | مرجع Canonical | وضعیت |
|---|---|---|
| کنترل AI | `AGENTS.md` | Active |
| چارچوب فنی | `20-EXPLORIA_AI_Development_Framework_Laravel_React_Monolith_v1.0.md` | Approved Baseline |
| دامنه تجاری | `Exploria_BRD_v1.1_Pilot_Revenue_Update` | Canonical |
| الزامات عملکردی | `Exploria_FRD_v1.1_Pilot_Operations_Update` | Canonical |
| Backlog | `Exploria_Product_Backlog_v1.0.xlsx` | Canonical Planning Source |
| برنامه Sprint | `Exploria_Sprint_0_1_Execution_Plan_v1.0.xlsx` | Reference؛ نیازمند قفل Sprint 1A |
| طراحی فنی | `Exploria_Technical_Design_Pack_v1.0` | Reference؛ باید با Laravel تطبیق داده شود |
| API Contract | `Exploria_OpenAPI_Sprint1_v1.0_PATCHED.yaml` | Canonical Contract Reference؛ پیاده‌سازی Laravel باید آن را بازاعتبارسنجی کند |
| UI/UX | `Exploria_UI_UX_Wireframe_Pack_v1.0` | Canonical UX Reference |
| MVP Governance | `EXPLORIA_MVP_Documentation_Pack_v1.0.zip` | Canonical Governance Pack |

## 5. تعیین تکلیف بسته‌های قدیمی

Frontend Starter، Backend Starter، Local Integration Pack، Integration QA و Integration Patch قبلی بر پایه React + Node.js/Express تهیه شده‌اند.

تصمیم قفل‌شده:

- این بسته‌ها مبنای ایجاد Codebase جدید نیستند.
- کد Node.js/Express به Laravel منتقل یا Copy-Paste نمی‌شود.
- فقط Contractها، Flowها، Test Scenarioها، Seed Concepts و یافته‌های QA قابل استفاده مجدد هستند.
- Smoke Test قدیمی فقط Evidence تاریخی است؛ Gate جدید باید روی Codebase Laravel اجرا شود.

## 6. دامنه قفل‌شده MVP اولیه

مسیر حیاتی:

`QR Scan → PWA → OTP → Consent → Attributed Scan Event → Admin Dashboard Summary`

قابلیت‌های Must اولیه:

1. QR Landing
2. OTP Login
3. Versioned Consent Capture
4. Attributed Scan Event
5. Venue / Zone / Hub / Touchpoint پایه
6. QR Registry
7. Admin Dashboard Summary
8. Authentication و Authorization پایه
9. Event/Audit Logging لازم برای مسیر حیاتی

قابلیت‌های Reward Engine کامل، Settlement، Marketplace، Analytics پیشرفته، Offline Sync کامل و معماری چندسرویسی خارج از Sprint 1A هستند.

## 7. Gateهای ورود به Codebase

| Gate | معیار | وضعیت |
|---|---|---|
| PCG-01 | معماری Laravel + React Monolith قفل شده باشد | PASS |
| PCG-02 | اسناد Canonical و سلسله‌مراتب تعارض مشخص باشد | PASS |
| PCG-03 | بسته‌های Node/Express به Reference تنزل داده شده باشند | PASS |
| PCG-04 | تصمیم‌های باز فنی و حقوقی تعیین تکلیف شوند | PASS — Development Baseline |
| PCG-05 | دامنه Sprint 1A و Story Trace قفل شود | PASS |
| PCG-06 | Acceptance Criteria و تست‌های Sprint 1A تصویب شوند | PASS |
| PCG-07 | پوشه رسمی Codebase و Handoff ایجاد شود | PASS |
| PCG-08 | ابزارهای لازم PHP/Composer/Node/Git/Database بررسی شوند | CONDITIONAL — Package Network, Git and PostgreSQL Pending |
| PCG-09 | Snapshot قبل از کدنویسی موجود باشد | PASS |

## 8. تصمیم‌های باز مرحله بعد

| ID | موضوع | تصمیم موردنیاز |
|---|---|---|
| OD-001 | OTP | Provider نهایی و سیاست Dev/Test |
| OD-002 | Consent | متن حقوقی نهایی و نسخه آزمایشی |
| OD-003 | QR | قالب URL، چاپ و شناسه‌گذاری |
| OD-004 | Pilot Data | Venue/Zone/Hub/Touchpoint اولیه |
| OD-005 | Environment | Local، Staging، Domain و SSL |
| OD-006 | Offline | حدود Fallback و ثبت دستی |
| OD-007 | Admin Auth | روش ورود و نقش‌های اولیه |
| OD-008 | Database | موتور و تفکیک محیط‌ها |
| OD-009 | Logging | محل Log، Retention و خطاها |

## 9. نتیجه گام اول

مبنای اجرایی، معماری، تصمیم‌های توسعه‌ای و Sprint 1A در اسناد 21 تا 23 قفل شدند. پروژه مجاز است وارد بررسی ابزارها، ایجاد پوشه رسمی Codebase و Bootstrap کنترل‌شده شود.
