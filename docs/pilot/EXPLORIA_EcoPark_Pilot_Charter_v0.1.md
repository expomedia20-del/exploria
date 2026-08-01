# EXPLORIA EcoPark Pilot Charter v0.1

## 1. کنترل سند

| فیلد | مقدار |
|---|---|
| نوع سند | Working Pilot Charter - غیرجایگزین اسناد Canonical |
| مرحله اجرایی | Stage 2 - Pilot Lock |
| تاریخ Snapshot | 2026-08-01 |
| مکان | اکوپارک عباس‌آباد (`ecopark-abbasabad`) |
| وضعیت | `PROPOSED — PO CONFIRMATION REQUIRED` |
| هدف | قفل‌کردن دامنه، دوره، نقش‌ها، KPI، بودجه، Gateها و پیش‌شرط‌های پایلوت اول |
| مجوز اجرای عمومی | صادر نشده است |

این Charter یک مبنای پیشنهادی برای تصمیم مالک محصول است و به‌تنهایی مجوز نصب QR، مصرف بودجه، عقد قرارداد، استفاده از داده واقعی یا اجرای عمومی پایلوت محسوب نمی‌شود. در صورت تعارض، سلسله‌مراتب سند 21 اعمال می‌شود.

## 2. مراجع و Trace

- BRD v1.1: فصل‌های 3 تا 15 و `BRD-PR-001` تا `BRD-PR-024`
- FRD v1.1: `PILOT-001..006`، `ANALYTICS-001..006` و KPIهای فصل 8
- MVP Scope Lock: مسیر `QR → PWA → OTP → Consent → Attributed Event → Dashboard`
- MVP Delivery Control، UAT و Readiness Register: اسناد 17 تا 19
- Open Decisions: `OD-001..009`
- 26 Control Items: `CPL-01..26`
- Feature Status Snapshot: `docs/status/EXPLORIA_Feature_Status_Register_v1.0.md`

## 3. تصمیم اجرایی پیشنهادی

### 3.1 موارد قفل‌شده توسط اسناد Canonical

| موضوع | تصمیم |
|---|---|
| Venue اول | اکوپارک عباس‌آباد، پایلوت Primary |
| مدت اجرا | ۱۴ روز تقویمی یا پایلوت مشابه با تاریخ شروع/پایان مصوب |
| مسیر حیاتی | QR، ورود موبایلی/OTP، Consent، Event قابل انتساب و Dashboard |
| مرزبندی | Demo با MVP نرم‌افزاری، MVCP میدانی و Product نهایی یکسان نیست |
| قاعده Decision Gate | بیش از ۸۰٪: توسعه؛ ۶۰٪ تا ۸۰٪: بهینه‌سازی/تکرار؛ کمتر از ۶۰٪: توقف یا بازطراحی |
| اصل داده | عدد هدف ساختگی ممنوع؛ KPI باید Target مصوب، Event قابل ردیابی و Baseline داشته باشد |
| اصل شروع | پایلوت بدون KPI، تاریخ، قرارداد/مجوز، نقش، بودجه و Gate فنی مصوب Active نمی‌شود |

### 3.2 Baseline پیشنهادی برای تأیید مالک محصول

| موضوع | پیشنهاد Stage 2 | وضعیت تصمیم |
|---|---|---|
| بازه پایلوت | ۱۴ روز؛ تاریخ دقیق پس از آماده‌شدن Staging، مجوز و تیم میدانی | `PO CONFIRMATION REQUIRED` |
| کمپین مادر | `ecopark-pilot-1405` برای مدیریت کل پایلوت | `PO CONFIRMATION REQUIRED` |
| سناریوی میدانی | `ecopark-online-treasure-map-game-campaign` با عنوان «کاشفان گنج پنهان» | `PO CONFIRMATION REQUIRED` |
| مسیر QR | دروازه حضور + باغ کتاب + آب‌وآتش + گنبد مینا + پل طبیعت + گنج پایانی رواق | `FIELD VALIDATION REQUIRED` |
| مخاطب | خانواده‌ها، نوجوانان، گردشگران شهری و گروه‌های علمی/فرهنگی؛ مشارکت کودک فقط با همراه/سرپرست و سیاست حقوقی مصوب | `LEGAL CONFIRMATION REQUIRED` |
| شرکای حداقلی | حداقل یک واحد غذایی/تحویل پاداش، یک واحد تجاری رواق و یک حامی واقعی | `PARTNER CONFIRMATION REQUIRED` |
| مدل ورود | PWA/Web بدون نصب اجباری، OTP واقعی و Consent نسخه‌دار | `STAGING/UAT REQUIRED` |
| دامنه رسانه | فقط دارایی‌های تست‌شده، دارای مالک، برنامه پخش و QR قابل انتساب | `FIELD VALIDATION REQUIRED` |

نام‌های فعلی فروشگاه، کافه و اسپانسر در Seed، هویت‌های نمایشی‌اند و نباید در قرارداد، چاپ، گزارش عمومی یا بودجه واقعی استفاده شوند.

## 4. وضعیت واقعی Codebase در شروع Stage 2

### 4.1 شواهد آماده Demo

| شاخص | مقدار مشاهده‌شده | برداشت مجاز |
|---|---:|---|
| Demo Readiness | 19 Pass / 0 Warning / 0 Fail | مناسب دمو و Dry Run محلی |
| Zone | 3 | مدل مکانی پایه موجود است |
| Hub | 7 | هاب‌های نمایشی قابل پیکربندی‌اند |
| Touchpoint | 8 | نقاط تعامل Demo موجودند |
| QR فعال | 8 | ۶ QR زنجیره حضوری + ۲ QR ورودی/دمو |
| کمپین فعال | 2 | هر دو با داده و بازه Demo |
| مشارکت کمپین | 8 عضویت روی دو کمپین | هویت‌ها و قراردادها واقعی نیستند |
| Partner Account | 6 | همگی باید پیش از پایلوت با طرف واقعی جایگزین/تأیید شوند |
| نمایشگر | 3 | اتصال سخت‌افزار و پخش میدانی هنوز تأیید نشده است |

### 4.2 مواردی که Pilot Readiness نیستند

- داده‌های Venue، کمپین، شریک، اسپانسر، بودجه و تاریخ فعلی Demo هستند.
- Production Readiness در Local برابر 4 Pass / 8 Fail است؛ این نتیجه برای محیط Local مورد انتظار است، ولی اجازه اجرای عمومی نمی‌دهد.
- پایگاه داده فعلی Local از SQLite استفاده می‌کند؛ PostgreSQL Staging هنوز Gate نشده است.
- OTP فعلی LocalFixed است؛ استفاده خارج از Local/Test ممنوع است.
- APP_URL فعلی HTTPS نیست و Queue/Session پایدار Production تنظیم نشده‌اند.
- متن حقوقی Consent، سیاست داده، قراردادها، مجوز نصب و بیمه نهایی نشده‌اند.
- اتصال فیزیکی نمایشگرها، اینترنت محل، برق/پاور و چاپ QR آزمون میدانی نشده است.

## 5. دامنه پایلوت پیشنهادی

### 5.1 داخل دامنه

- یک Venue: اکوپارک عباس‌آباد.
- یک سناریوی اصلی: «کاشفان گنج پنهان» در یک مسیر کنترل‌شده.
- شش QR میدانی اصلی با Binding قطعی به Venue/Touchpoint/Campaign.
- ورود با شماره موبایل، OTP واقعی، Consent نهایی و Session امن.
- ثبت اسکن معتبر/نامعتبر، ثبت‌نام، شروع/تکمیل مأموریت، صدور/مصرف پاداش، مراجعه منتسب و رخداد پشتیبانی.
- حداقل سه شریک واقعی با قرارداد و مسئول پاسخ‌گو.
- گزارش روزانه، گزارش پایان پایلوت و Board Decision Gate.
- Fallback محدود برای اینترنت ضعیف شامل پیام، Retry، Issue Log و رویه عملیاتی دستی مصوب.

### 5.2 خارج از دامنه

- اجرای هم‌زمان ارم یا برج میلاد.
- Native App، Microservices یا Repository جدا.
- Offline Sync کامل.
- Marketplace، Settlement خودکار کامل و Analytics/BI پیشرفته.
- توسعه قابلیت جدیدی که برای Gate همین پایلوت ضروری و دارای Change Request نباشد.
- استفاده از داده Demo به‌عنوان شاهد درآمد، رضایت یا عملکرد واقعی.

## 6. طراحی زمانی پیشنهادی

| فاز | بازه پیشنهادی | خروجی |
|---|---:|---|
| Baseline | ۷ روز قبل از اجرا با پوشش روزهای کاری/تعطیل | Footfall، مراجعه/فروش شرکای منتخب، خطا و ظرفیت عملیاتی |
| Dry Run | ۱ روز پیش از شروع | UAT نقش‌محور، تست موبایل/QR/OTP/Consent/پاداش/گزارش |
| Pilot Live | ۱۴ روز تقویمی | Daily Report مصوب و KPI روزانه |
| Close-out عملیاتی | حداکثر ۷۲ ساعت پس از پایان | غیرفعال‌سازی/تمدید QR، موجودی پاداش، Incident و گزارش اولیه |
| گزارش نهایی | حداکثر ۷ روز پس از پایان | گزارش میزبان، Merchant/Sponsor، محصول و Decision Gate |

تاریخ‌های تقویمی تا زمان تعیین مسئولان، مجوزها، بودجه، Staging و قراردادها `TBD` باقی می‌مانند.

## 7. RACI پیشنهادی

| فعالیت | Responsible | Accountable | Consulted | Informed |
|---|---|---|---|---|
| تصویب Charter، KPI و بودجه | Product Owner | کمیته راهبری/حامی کسب‌وکار | Pilot Manager، Tech/QA، Venue Host | همه ذی‌نفعان |
| مجوز نصب و اجرای عمومی | Venue Host | Venue Host | حقوقی، Pilot Manager | کمیته راهبری |
| آمادگی Staging و امنیت | Tech Lead/Developer | Product Owner | QA، Security/Operations | Pilot Manager |
| طراحی و قفل سناریو/محتوا | Campaign Manager | Product Owner | Venue Host، Sponsor، Brand/Legal | تیم میدان |
| عملیات روزانه و شیفت | Field Operations | Pilot Manager | Venue Host، Support | Product Owner |
| QR، دارایی و نمایشگر | Asset/Field Lead | Pilot Manager | Tech Lead، Venue Host | Support |
| پذیرش و مصرف پاداش | نماینده Merchant | Pilot Manager | Support، Commercial Lead | Product Owner |
| Incident و شکایت | Support Lead | Pilot Manager | Legal، Tech، Venue Host | Product Owner |
| کیفیت داده و Daily Report | Analyst/QA | Pilot Manager | Tech Lead | Steering Committee |
| گزارش نهایی و Decision Gate | Product/Analyst | Product Owner | همه مالکان KPI | ذی‌نفعان |

نام هر شخص، شماره تماس جایگزین و ساعات پاسخ‌گویی باید پیش از Gate شروع در Runbook ثبت شود.

## 8. KPI Register پیشنهادی

### 8.1 Targetهای پیشنهادی برای تصویب

این اعداد «پیشنهاد تصمیم» هستند، نه KPI مصوب یا داده واقعی. تا زمان تأیید Product Owner و Venue Host، در سامانه باید به‌صورت Placeholder باقی بمانند.

| KPI | تعریف | Target پیشنهادی | Cadence | مالک |
|---|---|---:|---|---|
| Critical Path Pass | عبور QR تا Event/Dashboard بدون Blocker | 100% سناریوهای Must | قبل از شروع/روزانه | QA/Tech |
| Data Traceability | Eventهای اصلی دارای QR، Venue، Campaign، Session/User و زمان معتبر | 100% | روزانه | Analyst/Tech |
| Scan Volume | اسکن معتبر به تفکیک QR و Touchpoint | `TBD` پس از Baseline Footfall | روزانه | Pilot Manager |
| Registration Rate | ثبت‌نام موفق / اسکن ورودی یکتا | ≥ 60% | روزانه | Product |
| Activation Rate | شروع اولین مأموریت / ثبت‌نام | ≥ 60% | روزانه | Campaign Manager |
| Mission Completion Rate | تکمیل مسیر / شروع مسیر | ≥ 50% | روزانه | Campaign Manager |
| Reward Issuance Integrity | پاداش صحیح صادرشده / تکمیل واجد شرایط | ≥ 95% | روزانه | Operations/Tech |
| Redemption Rate | پاداش مصرف‌شده / پاداش صادرشده | ≥ 30% | روزانه/Merchant | Commercial Lead |
| Merchant Conversion | مراجعه/مصرف منتسب / تعامل هدایت‌شده | ≥ 20% | روزانه/Merchant | Commercial Lead |
| QR Availability | زمان سالم بودن QRهای فعال / زمان برنامه‌ریزی‌شده | ≥ 98% | هر شیفت | Field Lead |
| Support Issue Rate | Issue و شکایت / کاربران فعال | ≤ 5% | روزانه | Support Lead |
| رضایت | میانگین بازخورد پایان تجربه | ≥ 4 از 5 | روزانه/پایان | Product |
| امنیت و حریم خصوصی | Incident بحرانی یا نشت PII | 0 | لحظه‌ای | Tech/Legal |
| Unit Economics | Cost per Scan/User/Mission و Value per Merchant Lead | `TBD` پس از بودجه و قرارداد | پایان | Commercial/Finance |

### 8.2 مدل امتیاز Decision Gate پیشنهادی

| سبد KPI | وزن پیشنهادی |
|---|---:|
| پایداری فنی و Traceability داده | 25% |
| تعامل و تکمیل تجربه | 25% |
| تبدیل تجاری و پاداش | 20% |
| عملیات میدانی و پشتیبانی | 15% |
| رضایت و تجربه فراگیر | 10% |
| حقوقی، امنیت و حریم خصوصی | 5% |
| جمع | 100% |

امتیاز هر سبد از نسبت تحقق KPIهای مصوب همان سبد محاسبه می‌شود. قاعده BRD اعمال می‌شود:

- بیش از ۸۰٪: پیشنهاد توسعه فاز دوم.
- ۶۰٪ تا ۸۰٪: بهینه‌سازی و تکرار کنترل‌شده.
- کمتر از ۶۰٪: توقف، بازطراحی یا تغییر سناریو/Venue.

### 8.3 Overrideهای ایمنی

موارد زیر مستقل از امتیاز کل، تصمیم را به `PAUSE/NO-GO` تبدیل می‌کنند:

- نشت PII، دورزدن Consent یا استفاده از داده کودک بدون مجوز.
- Blocker در مسیر ورود/ثبت Event یا ازبین‌رفتن داده حیاتی.
- حادثه ایمنی جدی، فقدان مجوز معتبر یا دستور توقف Venue Host.
- پاداش بدون موجودی/بودجه یا مصرف چندباره کنترل‌نشده با اثر مالی جدی.
- عدم دسترسی به Owner رخداد بحرانی در بازه SLA مصوب.

## 9. بودجه و کنترل مالی

ارقام مالی موجود در Seed/Stress Demo، داده نمایشی هستند و نباید مبنای قرارداد یا تخصیص واقعی قرار گیرند.

| سرفصل | مبلغ/سقف | مالک تأیید | شاهد لازم |
|---|---|---|---|
| پاداش و Redemption | `TBD` | Product Owner + Commercial/Finance | موجودی، سقف هر کاربر، انقضا و مسئول تأمین |
| سفیران و عملیات میدانی | `TBD` | Pilot Manager | تعداد شیفت، نرخ و ساعات |
| چاپ، استند و نصب QR | `TBD` | Venue Host + Pilot Manager | طرح، تعداد، محل و تأیید نصب |
| نمایشگر/رسانه/محتوا | `TBD` | Sponsor/Venue/Product Owner | برنامه پخش و Brand Approval |
| اینترنت، برق و تجهیزات | `TBD` | Field/Asset Lead | Inventory و جایگزین اضطراری |
| پشتیبانی و احتیاط | `TBD` | Product Owner | سقف مصرف و مجوز برداشت |
| سقف کل پایلوت | `TBD` | Product Owner/Steering Committee | مصوبه مکتوب |

تا قبل از تصویب سقف کل و قواعد مصرف، Reward/Commercial Flow فقط در حالت Demo باقی می‌ماند.

## 10. Gateهای ورود، اجرا و خروج

### G0 — Business & Legal Lock

- Charter، تاریخ، KPI، بودجه، RACI و Decision Gate امضا شده باشد.
- قرارداد حداقلی Venue و شرکای واقعی، مجوز نصب، بیمه/مسئولیت و Brand Safety تعیین تکلیف شده باشد.
- متن حقوقی Consent، Data Ownership، Retention، Deletion و داده کودک مصوب باشد.

### G1 — Staging & Security

- PostgreSQL Staging، HTTPS، APP_DEBUG خاموش، Queue/Session پایدار و Cookie امن فعال باشد.
- OTP Provider واقعی با SLA/هزینه و Fail-Closed تست شده باشد.
- Backup/Restore، Central Logging، Retention، Monitoring و Alerting آزموده باشد.
- Production Readiness Check بدون Fail عبور کند.

### G2 — Data & Field Readiness

- هویت Demo با Venue/Partner/Sponsor واقعی جایگزین یا صریحاً از داده رسمی جدا شده باشد.
- شش QR چاپی در محل نهایی، روی چند موبایل و شبکه واقعی تست شده باشند.
- دارایی، اینترنت، پاور، نمایشگر، محتوا، پاداش و Inventory مالک مشخص داشته باشند.
- Event Dictionary و KPI Mapping فریز شده باشد.

### G3 — UAT & Dry Run

- تمام سناریوهای Must UAT با نرخ 100% Pass و بدون Blocker باشند.
- UAT نقش‌محور Venue، Hub/Ravaq، Merchant، Sponsor، Support و Admin ثبت شود.
- مسیر کامل یک بازدیدکننده از QR تا Redemption و گزارش اجرا شود.
- Runbook، Escalation، Low Connectivity و Exit Plan تمرین شود.

### G4 — Daily Pilot Control

- چک‌لیست شروع/پایان هر شیفت و Daily Report تأییدشده وجود داشته باشد.
- KPI، Incident، خرابی، شکایت، موجودی پاداش و کیفیت داده روزانه مرور شود.
- نقض KPI بحرانی یا Override ایمنی موجب Pause/Escalation شود.

### G5 — Closure & Board Decision

- QRها Archive/Expire/Extend شوند و پاداش مصرف‌نشده تعیین تکلیف شود.
- گزارش جداگانه Venue، Merchant/Sponsor و تیم محصول تولید شود.
- محدودیت داده و کیفیت Attribution در گزارش آشکار باشد.
- تصمیم توسعه/تکرار/توقف همراه امضا و برنامه انتقال ثبت شود.

## 11. پوشش ۲۶ محور کنترلی

| CPL | نتیجه Stage 2 | Gate بعدی |
|---|---|---|
| 01 قرارداد و تقسیم درآمد | سرفصل قفل؛ فرمول/طرفین `TBD` | G0 |
| 02 RACI | نقش‌ها پیشنهاد شد؛ نام اشخاص `TBD` | G0 |
| 03 Baseline | دوره ۷روزه پیشنهاد شد؛ داده واقعی لازم است | G2 |
| 04 Unit Economics | KPI تعریف شد؛ هزینه/ارزش `TBD` | G0/G5 |
| 05 مدل مکان | ۳ Zone، ۷ Hub و ۸ Touchpoint Demo موجود | G2 |
| 06 Launch Kit | Charter پایه ایجاد شد؛ بسته میدانی کامل نیست | G2/G5 |
| 07 Runbook | Gateها تعریف شد؛ Runbook شیفت مستقل لازم است | G3 |
| 08 سفیران | مسئولیت تعریف شد؛ نفرات/آموزش/اسکریپت لازم است | G3 |
| 09 دارایی | دسته‌ها مشخص؛ Inventory و تحویل‌گیری لازم است | G2 |
| 10 رسانه تعاملی | ۳ نمایشگر Demo؛ اتصال میدانی لازم است | G2 |
| 11 کتابخانه سناریو | یک سناریوی اصلی پیشنهاد شد | G0 |
| 12 عملیات محتوا | تقویم و Approval نهایی لازم است | G2 |
| 13 Brand Safety | Approval و ممنوعیت‌ها لازم است | G0/G2 |
| 14 Merchant Onboarding | حداقل سه شریک واقعی پیشنهاد شد | G0/G2 |
| 15 Sales Playbook | خارج از قفل فنی؛ قبل از قرارداد لازم است | G0 |
| 16 Fraud/Abuse | سقف و Override تعریف شد؛ Policy/تست لازم است | G2/G3 |
| 17 حقوقی/مجوز/بیمه | باز و مانع اجرا | G0 |
| 18 Data Governance | باز و مانع اجرا | G0/G1 |
| 19 Event Tracking | هسته موجود؛ Dictionary باید فریز شود | G2 |
| 20 Attribution | هسته موجود؛ Baseline و ارزش تجاری لازم است | G2/G5 |
| 21 Low Connectivity | Fallback محدود داخل Scope؛ Runbook لازم است | G3 |
| 22 Support/Complaint | Owner تعریف شد؛ SLA/فرم/Escalation لازم است | G3 |
| 23 Accessibility | PWA و مخاطبان خاص لحاظ شد؛ تست میدانی لازم است | G3 |
| 24 Exit/Transition | G5 تعریف شد؛ فرم Closure لازم است | G3/G5 |
| 25 Decision Gate | Threshold قفل و وزن‌ها پیشنهاد شد | G0 |
| 26 مرزبندی نسخه‌ها | Demo/MVP/MVCP/Product صریح شد | قفل‌شده |

هیچ محور CPL حذف نشده است. این Charter جای Runbook، قرارداد، سند حقوقی، Launch Kit یا گزارش نهایی را نمی‌گیرد؛ فقط مالک و Gate هر خروجی را روشن می‌کند.

## 12. رجیستر تصمیم‌های مالک محصول

| ID | تصمیم موردنیاز | پیشنهاد | وضعیت |
|---|---|---|---|
| PO-PILOT-01 | تأیید اکوپارک به‌عنوان تنها Venue مرحله اول | تأیید | Pending |
| PO-PILOT-02 | تأیید دوره ۱۴روزه و الگوی ۷+۱۴+۷ | تأیید؛ تاریخ دقیق بعد از G1/G2 | Pending |
| PO-PILOT-03 | انتخاب کمپین مادر و سناریوی «کاشفان گنج پنهان» | تأیید | Pending |
| PO-PILOT-04 | تأیید مسیر شش QR پس از بازدید میدانی | تأیید مشروط | Pending |
| PO-PILOT-05 | تأیید Targetهای پیشنهادی و وزن Decision Gate | تأیید یا اصلاح عددی | Pending |
| PO-PILOT-06 | تعیین سقف بودجه و سقف پاداش هر کاربر | تصمیم مالی لازم | Pending |
| PO-PILOT-07 | معرفی Pilot Manager و مالکان RACI | معرفی اشخاص لازم | Pending |
| PO-PILOT-08 | معرفی Venue Host و حداقل سه شریک واقعی | معرفی/قرارداد لازم | Pending |
| PO-PILOT-09 | تصویب سیاست کودک، Consent و Data Governance | تأیید حقوقی لازم | Pending |
| PO-PILOT-10 | تأیید اینکه شروع عمومی فقط پس از عبور G0 تا G3 مجاز است | تأیید | Pending |

## 13. Acceptance Criteria مرحله 2

| معیار | نتیجه |
|---|---|
| Venue، سناریو، مسیر و مرز Scope مشخص است | Pass - پیشنهادی |
| دوره و طراحی Baseline/Live/Post مشخص است | Pass - تاریخ Pending |
| KPIها، Targetهای پیشنهادی و Decision Gate مشخص است | Pass - PO Approval Pending |
| RACI و مالکان عملیاتی مشخص‌اند | Pass - نام اشخاص Pending |
| بودجه و کنترل مصرف مشخص است | Conditional - مبالغ Pending |
| ۲۶ محور CPL بدون حذف Trace شده‌اند | Pass |
| Demo از Pilot/Production تفکیک شده است | Pass |
| موانع Staging، حقوقی، میدانی و تجاری صریح‌اند | Pass |

**نتیجه Stage 2:** `CONDITIONAL COMPLETE — CHARTER DRAFTED, PO LOCK PENDING`

## 14. Verification Record

| بررسی | نتیجه |
|---|---|
| `php artisan migrate:status` | همه Migrationهای Local اجرا شده‌اند |
| `php artisan exploria:demo-readiness --json` | 19 Pass / 0 Warning / 0 Fail |
| `php artisan exploria:production-readiness --json` | 4 Pass / 8 Fail در Local؛ اجرای عمومی مجاز نیست |
| بررسی Seed و مدل‌های اکوپارک | همه هویت‌ها/بازه‌ها/بودجه‌های فعلی به‌عنوان Demo تفکیک شدند |
| تغییر Codebase | هیچ کد، Schema، Package یا Role نرم‌افزاری تغییر نکرد |
| تغییر اسناد Canonical | انجام نشد؛ Sync-Handoff لازم نیست |
