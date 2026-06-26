# 23 — EXPLORIA Sprint 1A Scope & Acceptance Lock v1.0

## 1. هدف Sprint

ساخت اولین Increment قابل اجرا و قابل تست از EXPLORIA روی معماری Laravel + React Monolith برای مسیر:

`QR Landing → OTP → Consent → Attributed Scan Event → Admin Dashboard Summary`

**Status:** Scope & Acceptance Locked  
**Date:** 2026-06-20

## 2. محدوده داخل Sprint 1A

| Work Item | شرح | Trace |
|---|---|---|
| S1A-001 | Bootstrap یک Codebase واحد Laravel + React با الگوی Inertia-style | Framework-20, PCG-01 |
| S1A-002 | زیرساخت Environment، PostgreSQL، Migration، Seeder و تست ایزوله | ADR-001, OD-005, OD-008 |
| S1A-003 | مدل User، درخواست OTP، Verify و Session Authentication | US-005, US-007, US-008 / AUTH-001,003,004 |
| S1A-004 | Consent Version، نمایش متن و ConsentLog | US-006 / AUTH-002 |
| S1A-005 | مدل پایه Venue، Zone، Hub و Touchpoint با Seed دمو | US-011..013 / VENUE-001..003 |
| S1A-006 | QR Registry حداقلی با Binding و وضعیت | US-043 / QR-001 |
| S1A-007 | QR Scan Validation، کنترل نامعتبر/تکراری و ثبت Attribution | US-044..046 / QR-002..004 |
| S1A-008 | Event Log و Audit لازم برای مسیر حیاتی | EVENT baseline, DATA baseline |
| S1A-009 | Admin Dashboard Summary پایه با داده Backend | MVP-07, DEF-002 Contract Reference |
| S1A-010 | صفحات فارسی RTL برای Landing، OTP، Consent، Success و Admin Summary | UX-V-001..004, UX-A-001 |
| S1A-011 | تست Backend، Frontend و Smoke مسیر حیاتی | TC-001..004, UAT-01..06 |

## 3. محدوده خارج از Sprint 1A

- Reward، Coupon، Wallet و Redemption
- Merchant Billing، Contract و Settlement
- Campaign/Quest Engine کامل
- Analytics و Attribution پیشرفته
- Offline Sync کامل
- SMS Provider واقعی
- Staging/Production Deployment
- پنل کامل Merchant و Ambassador
- Native Mobile App

ورود هرکدام از این موارد نیازمند Change Request است.

## 4. ترتیب اجرای Workstreamها

### Track 0 — Foundation

S1A-001، S1A-002 و قواعد امنیت/تست.

### Track A — Visitor Access

S1A-003، S1A-004 و صفحات OTP/Consent.

### Track B — Venue & QR Core

S1A-005، S1A-006 و S1A-007.

### Track C — Evidence & Admin

S1A-008، S1A-009، S1A-010 و S1A-011.

## 5. Acceptance Criteria

| ID | معیار پذیرش | سطح |
|---|---|---|
| AC-S1A-01 | برنامه از یک Codebase واحد Laravel + React اجرا شود | Must |
| AC-S1A-02 | UI اصلی فارسی، RTL و Mobile-first باشد | Must |
| AC-S1A-03 | QR دمو به Venue و Touchpoint معتبر متصل و Landing قابل باز شدن باشد | Must |
| AC-S1A-04 | OTP ثابت فقط در Local/Test کار کند و Session معتبر ایجاد شود | Must |
| AC-S1A-05 | Consent فعال نمایش داده و پذیرش با Version/Timestamp ثبت شود | Must |
| AC-S1A-06 | Scan معتبر با QR، Venue، Touchpoint، User/Session و زمان ذخیره شود | Must |
| AC-S1A-07 | QR غیرفعال، منقضی یا تکراری نتیجه پذیرفته تولید نکند | Must |
| AC-S1A-08 | Admin Summary حداقل تعداد کاربران، اسکن‌ها، اسکن‌های پذیرفته و Issueهای باز را نشان دهد | Must |
| AC-S1A-09 | مسیرهای Admin تحت Authentication و Authorization باشند | Must |
| AC-S1A-10 | Logها فاقد OTP، Token و موبایل کامل باشند | Must |
| AC-S1A-11 | Loading، Empty و Error State برای صفحات اصلی وجود داشته باشد | Must |
| AC-S1A-12 | تست خودکار مسیرهای بحرانی و Smoke محلی بدون Blocker عبور کند | Must |

## 6. Test Matrix حداقلی

### Backend

- OTP request/verify در Local/Test
- ممنوعیت OTP ثابت در محیط غیرمجاز
- Validation و Rate Limit پایه OTP
- Consent current/accept/version trace
- QR active/inactive/expired/duplicate
- Attribution integrity
- Admin authorization
- Dashboard summary values
- عدم نشت PII در Logهای آزمون‌پذیر

### Frontend

- Render صفحات اصلی
- RTL و متن‌های کلیدی فارسی
- Loading، Empty و Error State
- Validation پیام‌های فرم
- تکمیل Flow از Landing تا Success

### Smoke

1. Health/Application boot
2. Demo QR landing
3. OTP request/verify
4. Consent current/accept
5. Attributed scan event
6. Admin dashboard summary

## 7. Definition of Done Sprint 1A

Sprint فقط زمانی Done است که:

1. همه Mustها Pass باشند.
2. هیچ Blocker یا High بدون تصمیم رسمی باز نباشد.
3. Migrationها قابل Rollback و Seederها قابل تشخیص به‌عنوان Demo باشند.
4. هیچ Secret یا Credential در Repository نباشد.
5. فایل‌های تغییرکرده، تست‌ها و Known Issues مستند شده باشند.
6. خروجی Build و Smoke Evidence تولید شده باشد.
7. AI Output Review Checklist سند 20 تکمیل شده باشد.
8. Product Owner و QA بتوانند UAT-01 تا UAT-06 را اجرا کنند.

## 8. Gate Result

دامنه، Trace، Acceptance Criteria، Test Matrix و Definition of Done برای Sprint 1A قفل شدند.

**Gate Result:** PASS — READY FOR TOOLCHAIN CHECK AND CODEBASE BOOTSTRAP
