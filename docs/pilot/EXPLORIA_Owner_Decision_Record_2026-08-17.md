# EXPLORIA — Owner Decision Record پیش از Staging

## 1. کنترل سند

| فیلد | مقدار |
|---|---|
| تاریخ ثبت | 2026-08-17 |
| نوع | Decision Record — تفکیک تصمیم قطعی از توصیه |
| وضعیت | `OWNER ACCEPTANCE AND ROLE DESIGNATIONS RECORDED — SIGN-OFF EVIDENCE PENDING` |
| دامنه | نقش‌ها، محل داده، ارز، بودجه و Candidateهای Staging |

این سند جایگزین مشاوره حقوقی، قرارداد Provider یا امضای Go/No-Go نیست. اطلاعات تماس خصوصی، شناسه هویتی، Credential و امضای اسکن‌شده نباید در Repository عمومی ثبت شوند.

## 2. تصمیم‌های قطعی اعلام‌شده توسط مالک

| موضوع | تصمیم | وضعیت |
|---|---|---|
| مالک پلتفرم/کسب‌وکار | علی رحمان سلیمانی‌زاده، شخص حقیقی | `OWNER CONFIRMED` |
| محل مجاز داده و Backup | ایران | `OWNER CONFIRMED — IRAN ONLY` |
| ارز و روش بودجه‌ریزی فعلی | ریال؛ بدون اتکا به پرداخت ارزی | `OWNER CONFIRMED` |
| ایمیل Privacy | یک نشانی شخصی خارج Repository اعلام شده است | `TEMPORARY ONLY — NOT APPROVED FOR PILOT` |

اصطلاح دقیق حقوقی «مالک داده» باید توسط مشاور حقوقی تعیین شود. مالک پلتفرم مسئول هدف و شیوه پردازش است، اما اشخاص موضوع داده حقوق دسترسی، اصلاح و حذف خود را حفظ می‌کنند.

## 3. نقش‌های پذیرفته‌شده و نقش‌های مستقل

| نقش | توصیه حداقلی | زمان الزام | وضعیت |
|---|---|---|---|
| Product Owner | علی رحمان سلیمانی‌زاده؛ چون مالک Scope، اولویت، بودجه و تصمیم Go/No-Go است. | اکنون | `OWNER ACCEPTED — 2026-08-18` |
| Finance Approver | علی رحمان سلیمانی‌زاده برای پروژه شخصی/خودتأمین؛ هر هزینه فقط داخل سقف مصوب و با Invoice. | پیش از خرید Staging | `OWNER ACCEPTED — 2026-08-18` |
| Privacy/Data Accountable Owner | علی رحمان سلیمانی‌زاده؛ پاسخ‌گوی کسب‌وکار و Data Request، نه جایگزین وکیل. | اکنون | `OWNER CONFIRMED; LEGAL REVIEW PENDING` |
| Legal Approver | آقای سیفی، وکیل دادگستری — معرفی‌شده توسط مالک؛ مشخصات حرفه‌ای و Engagement Reference خارج Repository نگهداری می‌شود. | پیش از ورود هر داده واقعی | `ROLE ASSIGNED BY OWNER — WRITTEN ACCEPTANCE/SIGN-OFF PENDING` |
| Incident Commander | علی رحمان سلیمانی‌زاده فقط برای Pre-Staging؛ پیش از Pilot یک جانشین آموزش‌دیده و کانال On-call مستقل لازم است. | Pre-Staging / Pilot | `OWNER ACCEPTED FOR PRE-STAGING; ALTERNATE BLOCKER BEFORE PILOT` |
| Operations Owner | شرکت مدیا پارس — معرفی‌شده توسط مالک برای Server، DB، Queue، Backup، Monitoring و Incident Operations. | پیش از فعال‌سازی Staging | `ORGANIZATION ASSIGNED — NAMED REPRESENTATIVE/ACCEPTANCE/ON-CALL PENDING` |
| Security Owner/Approver | متخصص نام‌دار از مدیا پارس یا شخص مستقل برای Access Review، Secret، Incident امنیتی و Risk Acceptance. | پیش از فعال‌سازی Staging | `BLOCKER — NAMED PERSON AND SIGN-OFF TBD` |
| QA/UAT Lead | فردی غیر از پیاده‌ساز اصلی برای Evidence و Sign-off. | پیش از UAT رسمی | `BLOCKER BEFORE UAT` |
| Venue/Field و Support Lead | نماینده محل Pilot و مسئول تماس/شکایت. | پیش از Pilot | `BLOCKER BEFORE PILOT` |

## 4. توصیه کانال Privacy و Security

ایمیل شخصی فقط برای Draft و مکاتبه اولیه قابل تحمل است. پیش از ورود داده واقعی:

1. نشانی‌های `privacy@<official-domain>` و `security@<official-domain>` یا یک نشانی رسمی نقش‌محور ایجاد شوند.
2. MFA، Password Manager، Recovery Code آفلاین و دسترسی جانشین تعریف شود.
3. پیام‌ها به Ticket/Incident Register کنترل‌شده متصل و مدت نگهداری آنها مصوب شود.
4. ایمیل شخصی فقط Recovery/Forwarding باشد و در Privacy Notice عمومی منتشر نشود.

## 5. مبنای حقوقی حداقلی برای Legal Review

- ماده 58 قانون تجارت الکترونیکی، پردازش دسته‌های حساس را بدون رضایت صریح ممنوع می‌کند.
- ماده 59 هدف مشخص، کمینه‌سازی، استفاده متناسب، صحت و امکان دسترسی/اصلاح/حذف را مقرر می‌کند.
- ماده 71 برای نقض شرایط مواد 58 و 59 ضمانت اجرای کیفری مقرر کرده است.
- مرجع بررسی‌شده: `https://www.wipo.int/wipolex/en/legislation/details/7711` و متن فارسی تنقیحی `https://nezamat.ir/post-34221/`.

این جمع‌بندی مشاوره حقوقی نیست. Legal Approver باید نوع شخصیت، متن Consent/Privacy، Retention، داده کودک، قرارداد پردازشگر، شکایت و حقوق کاربر را مستقل تأیید کند.

## 6. سقف‌های مالی پذیرفته‌شده

همه ارقام ریال هستند و در 2026-08-18 توسط Product Owner/Finance Approver پذیرفته شده‌اند. این سقف‌ها به‌تنهایی مجوز خرید نیستند؛ Vendor باید Gateهای Due-diligence را عبور کند و Invoice داخل سقف باشد.

| سرفصل | سقف پیشنهادی | کنترل هزینه |
|---|---:|---|
| Compute + PostgreSQL + IPv4 در Staging | `25,000,000 IRR/month` | Quote، Invoice و Alert مصرف |
| OTP | `10,000,000 IRR/month` و حداکثر `2,000 OTP/month`؛ هرکدام زودتر | Low-balance، Rate Limit و توقف سخت |
| Mail + Storage + Monitoring + Backup | `25,000,000 IRR/month` | تفکیک Invoice و Alert 50/75/90 درصد |
| کل هزینه تکرارشونده Staging | `60,000,000 IRR/month` | افزایش فقط با تصمیم جدید Finance |
| Reward واقعی در Staging/UAT | `0 IRR` | فقط داده و Reward غیرواقعی |
| Reward واقعی Pilot | `TBD` | کاربران واجد شرایط × سقف هر کاربر × دفعات مجاز + ذخیره احتیاطی |

**تصمیم مالی:** سقف‌های پنج ردیف اول `APPROVED WITH CONDITIONS` هستند. Reward واقعی Pilot همچنان `PENDING/BLOCKER BEFORE PILOT` است.

تعرفه رسمی بررسی‌شده Liara در 2026-08-17 برای برآورد اولیه، پلن‌های ایران، IPv4، PostgreSQL، Email و Object Storage را ریالی/تومانی فهرست می‌کند: `https://liara.ir/pricing`. تعرفه نهایی باید درست پیش از خرید دوباره دریافت شود.

## 7. توصیه Provider

| Capability | توصیه | وضعیت تصمیم |
|---|---|---|
| Staging/DB/Mail/Object Storage | Liara فقط به‌عنوان Candidate اول Due-diligence؛ خرید پس از پاسخ فنی، قراردادی، Security و Budget. | `DUE-DILIGENCE ACCEPTED — PURCHASE NOT AUTHORIZED` |
| OTP Sandbox | Kavenegar Candidate اول؛ مستندات HTTPS/OTP/Delivery و Status Page عمومی دارد. IPPanel فقط Comparator است. | `SANDBOX/QUOTE ACCEPTED — ACCOUNT/REAL SEND NOT AUTHORIZED` |
| OTP Integration | Adapter اختصاصی داخل Contract فعلی Laravel؛ قراردادن URL مستقیم در Provider عمومی موجود ممنوع است. | `TECHNICAL GAP — CHANGE REQUIRED AFTER VENDOR APPROVAL` |
| Backup | Provider/Account ایرانی دوم و مستقل از Failure Domain اصلی. | `BLOCKER — PROVIDER TBD` |
| Uptime/Alerting | Probe ایرانی مستقل از Application Provider و متصل به On-call. | `BLOCKER — PROVIDER/OWNER TBD` |
| Provider خارجی | برای داده، Log و Backup تحت تصمیم فعلی Iran-only مجاز نیست. | `NOT ELIGIBLE` |

Kavenegar در تعرفه رسمی، OTP را برای پلن‌های پیشرفته/فوق‌پیشرفته اعلام کرده و Sandbox/اعتبار آزمایشی دارد؛ هزینه هر پیام و قرارداد باید Quote شود: `https://kavenegar.com/pricing.html`.

نتیجه Evidence عمومی و پرسش‌های اجباری Vendor در `docs/staging/EXPLORIA_Provider_Due_Diligence_2026-08-18.md` ثبت شده است. Liara به علت امکان انتقال/دسترسی بین‌المللی مندرج در Privacy Policy فعلاً `CONDITIONAL HOLD` است.

## 8. تصمیم‌های بعدی مورد نیاز از مالک

1. ثبت پذیرش کتبی و Engagement Reference آقای سیفی خارج Repository و دریافت Legal Sign-off روی Privacy/Consent/Retention/Contract.
2. معرفی نماینده نام‌دار، جانشین و کانال On-call از شرکت مدیا پارس و تعیین Security Approver واجد صلاحیت.
3. دریافت پاسخ مکتوب Liara درباره انتقال بین‌المللی، DPA، RPO/RTO/PITR، MFA/Audit و حذف/خروج داده.
4. دریافت پاسخ مکتوب Kavenegar درباره محل/مدت نگهداری Mobile و OTP، DPA، MFA، Cost/Rate Cap و Template برای شخص حقیقی.
5. انتخاب دامنه رسمی برای ایمیل‌های نقش‌محور و Provider ایرانی دوم برای Backup/Uptime.
6. تعیین ظرفیت Pilot، سقف Reward هر کاربر و کل Liability فقط پس از طراحی Pilot.

**نتیجه فعلی:** `NO PURCHASE — NO REAL DATA — LEGAL/OPERATIONS ROLES ASSIGNED; WRITTEN SIGN-OFF, SECURITY AND VENDOR GATES PENDING`
