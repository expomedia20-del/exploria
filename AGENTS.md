# EXPLORIA — دستورالعمل دائمی Codex

## مرجع الزامی

پیش از هر برنامه‌ریزی، طراحی، ایجاد فایل، ویرایش یا کدنویسی، سند زیر باید خوانده و رعایت شود:

`نسخ های اصلی تکمیلی/0000-ROOLS/20-EXPLORIA_AI_Development_Framework_Laravel_React_Monolith_v1.0.md`

این سند، چارچوب فنی و مقررات مصوب توسعه EXPLORIA است. BRD v1.1، FRD v1.1، Product Backlog، Technical Design، UI/UX Wireframe، MVP Scope Lock، Delivery Control، UAT و Readiness Register نیز مراجع مکمل و الزام‌آورند.

سند `نسخ های اصلی تکمیلی/0000-ROOLS/21-EXPLORIA_PreCoding_Master_Execution_Control_v1.0.md` مرجع وضعیت اجرایی، نسخه‌های Canonical و Gateهای ورود به کدنویسی است.

## تصمیم معماری قفل‌شده

- Backend: Laravel
- Frontend: React
- معماری: Monolith در یک Codebase و Repository واحد
- الگوی ترجیحی MVP: Laravel + React با معماری Inertia-style
- رابط کاربری: فارسی، RTL، واکنش‌گرا و مدرن
- بسته‌های قدیمی Node.js/Express فقط Prototype و مرجع تطبیقی هستند و مبنای پیاده‌سازی جدید نیستند، مگر با دستور صریح Product Owner.

## قواعد اجرای هر درخواست

1. ابتدا هدف Feature، سند/Requirement مرجع، Scope و Acceptance Criteria مشخص شود.
2. پیش از تغییر، فایل‌های درگیر اعلام شوند.
3. فقط حداقل تغییر لازم و داخل دامنه MVP انجام شود؛ قابلیت خارج از دامنه با برچسب `Out of Scope` متوقف شود.
4. Business Logic در Laravel و در Service/Action قرار گیرد؛ Controllerها سبک بمانند.
5. ورودی‌های مهم با Form Request در Backend اعتبارسنجی شوند و مسیرهای مدیریتی Authorization داشته باشند.
6. React فقط مسئول UI و State رابط باشد؛ Componentها کوچک و قابل استفاده مجدد باشند.
7. هیچ Secret، Token، API Key، Password یا Credential در کد ثبت نشود.
8. UI باید فارسی و RTL باشد و Loading، Empty و Error State داشته باشد.
9. هر Feature باید تست یا چک‌لیست تست، فهرست فایل‌های تغییرکرده و نتیجه Verification داشته باشد.
10. تغییر معماری، Scope، Role، مدل داده اصلی، مسیرهای اصلی Dashboard، زبان/جهت UI یا Package سنگین نیازمند تأیید رسمی کاربر است.

## Definition of Done

هیچ Feature کامل اعلام نشود مگر اینکه Backend و Frontend لازم، Validation، Authorization، UI فارسی/RTL، حالت‌های Loading/Empty/Error، تست، بررسی امنیت، انطباق با MVP Scope Lock و مستندات لازم تکمیل شده باشند.

## کنترل تغییر و حفاظت

- تغییرات مرحله‌ای و قابل بازبینی باشند.
- پیش از مراحل پرریسک یا تغییرات گسترده، Backup تاریخ‌دار ایجاد شود.
- پس از نصب Git، Commitها کوچک و با پیام روشن ثبت شوند.
- هیچ فایل یا تصمیم مصوب بدون Change Request یا دستور صریح کاربر حذف یا جایگزین نشود.
- پس از هر تغییر در اسناد مصوب، اسکریپت `00-بسته انتقال اسناد به کدنویسی/Sync-Handoff.ps1` اجرا و Manifest هش‌ها به‌روزرسانی شود.
