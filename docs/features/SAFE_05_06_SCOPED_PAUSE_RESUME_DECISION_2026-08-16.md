# EXPLORIA — تصمیم SAFE-05/06 برای توقف و ازسرگیری کنترل‌شده

## 1. کنترل تصمیم

| فیلد | مقدار |
|---|---|
| تاریخ | 2026-08-16 |
| وضعیت | Approved for minimum implementation؛ تأیید عملیاتی Security/Operations پیش از Pilot همچنان Pending |
| طبقه‌بندی | Production Hardening / Before Pilot |
| معماری | Laravel + React Monolith؛ بدون Package، Migration یا سرویس جدید |
| Scope | فقط Campaign عملیاتی و اجزای متصل همان Campaign |

این تصمیم از MSN/MSE Requirement استخراج نشده است. مبنا فقط الزامات Canonical خود Exploria است:

- FRD `PILOT-005`: مدیریت وضعیت Paused و هماهنگی QR و Campaign.
- FRD Business Rule ماژول Pilot: نقض KPI بحرانی باید Pause یا Escalate شود.
- FRD `SUPPORT-003`: رخداد میدانی باید Incident قابل پیگیری داشته باشد.
- BRD بخش RACI: مالک تصمیم توسعه/تکرار/توقف باید مشخص باشد.
- Pilot Charter بخش 8.3 و G4: Override ایمنی و Incident بحرانی موجب Pause/No-Go است.
- Launch Kit: بازگشایی فقط با تأیید Incident Commander و مالک حوزه رخداد مجاز است.
- ROI Report Template: کوچک‌ترین دامنه آسیب‌دیده متوقف و Resume پس از اقدام اصلاحی و تأیید ثبت شود.

## 2. تصمیم حداقلی مصوب

1. Scope توقف در نسخه فعلی `campaign` است؛ Kill Switch سراسری ایجاد نمی‌شود.
2. Pause فقط برای Campaign فعال و توسط `admin` یا `operator` مجاز است.
3. Pause به دلیل و مرجع Incident/Ticket اجباری نیاز دارد.
4. Campaign به وضعیت `inactive` می‌رود؛ QRهای متصل بدون تغییر وضعیت مستقل خود، به‌علت وضعیت Campaign غیرقابل استفاده می‌شوند.
5. ادامه مأموریت و صدور پاداش جدید همان Campaign نیز Fail-Closed می‌شود.
6. تغییر عمومی Campaign یا Campaign Builder حق دورزدن Pause را ندارد.
7. Resume فقط توسط `admin` و با همان Incident Reference، اقدام اصلاحی، شاهد Recovery/Smoke Test، یادداشت تأیید و تأیید صریح مجاز است.
8. Actor، Timestamp، Reason، Incident Reference، Corrective Action، Recovery Evidence و Resume Approval هم در Metadata وضعیت جاری و هم در Event Log append-only ثبت می‌شوند.
9. وضعیت مستقل QR، Mission و Reward هنگام Pause به‌صورت انبوه بازنویسی نمی‌شود تا Resume باعث فعال‌سازی ناخواسته اجزای قبلاً غیرفعال نشود.

## 3. رفتار تغییرکرده

| جریان | قبل | بعد |
|---|---|---|
| QR کمپین متوقف | فقط وضعیت دستی Campaign/QR مؤثر بود | Pause اختصاصی Campaign فوراً Landing همه QRهای متصل را Fail-Closed می‌کند |
| Mission موجود | وضعیت Mission به‌تنهایی بررسی می‌شد | وضعیت Campaign نیز باید Active باشد |
| Reward Issuance | وضعیت و Governance پاداش بررسی می‌شد | Active بودن Campaign نیز الزامی است |
| فعال‌سازی مجدد | ویرایش عمومی یا Builder می‌توانست Status را Active کند | فقط مسیر Resume با Admin Approval مجاز است |
| Audit | تغییر Status عمومی قابل ثبت بود | دو رویداد اختصاصی `audit.campaign_paused` و `audit.campaign_resumed` ثبت می‌شوند |

## 4. موارد عمداً خارج از این تغییر

- Global Kill Switch، Feature Flag و Canary.
- ایجاد Entity کامل `Pilot`، `Incident`، `DailyReport` یا Incident Management عمومی.
- توقف سراسری Venue یا چند Campaign هم‌زمان.
- Two-person approval یا نقش جدید Incident Commander پیش از تصویب RACI.
- اتصال به سامانه Ticketing بیرونی.

این موارد فقط پس از نیاز اثبات‌شده، تصویب Product/Security/Operations و در صورت تغییر Scope یا Role با Change Request بررسی می‌شوند.

## 5. Gate پذیرش

- Pause بدون Reason یا Incident Reference رد شود.
- Operator بتواند Campaign فعال را Pause کند، ولی Resume برای او 403 باشد.
- Resume بدون Corrective Action، Recovery Evidence، Approval Note یا تأیید صریح رد شود.
- Incident Reference در Resume با Pause مطابقت داشته باشد.
- QR Landing، Mission Progress و Reward Issuance همان Campaign هنگام Pause بسته باشند.
- ویرایش عمومی و Campaign Builder نتوانند Pause را دور بزنند.
- پس از Resume مجاز، وضعیت Campaign فعال و QRهای مستقل واجد شرایط دوباره قابل استفاده باشند.
- Event Log شامل Actor، Timestamp و Evidence لازم باشد.

## 6. Approval و محدودیت اجرایی

مجوز مالک پروژه برای انتخاب و اجرای امن‌ترین گزینه در Thread رسمی مبنای اجرای حداقل فنی است. این سند جایگزین تصویب RACI، Incident Policy یا معرفی Incident Commander واقعی نیست. تا تکمیل گام 3 Roadmap، اجرای Pilot عمومی و Production همچنان `NO-GO` است.
