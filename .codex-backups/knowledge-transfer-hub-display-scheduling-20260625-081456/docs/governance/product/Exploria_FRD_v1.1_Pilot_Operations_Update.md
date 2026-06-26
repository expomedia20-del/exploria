# EXPLORIA - FRD v1.1 Pilot Operations Update

**وضعیت:** Official Working Baseline - مبنای تولید Product Backlog v1.0

**جایگزین عملیاتی:** Exploria_FRD_Foundation_v1.0 برای ساخت Backlog دیگر مبنا نیست؛ نسخه v1.1 مبنای اصلی است.

| فیلد | مقدار |
| --- | --- |
| نام سند | Exploria_FRD_v1.1_Pilot_Operations_Update |
| نوع سند | FRD / سند الزامات عملکردی |
| مبنای ورودی | BRD v1.1 + Commercial Pilot Control Layer v1.0 + 26 Control Items Lock Register |
| دامنه | MVP نرم‌افزاری + MVCP تجاری برای EcoPark، Eram، Milad Tower |
| خروجی بعدی | Product Backlog v1.0 |

## 1. هدف نسخه v1.1

این نسخه FRD، الزامات نرم‌افزاری و عملیاتی مورد نیاز برای پایلوت تجاری قابل اجرا در میدان را قفل می‌کند. تمرکز نسخه v1.1 تبدیل BRD v1.1 و CPL-01 تا CPL-26 به Requirement، Business Rule، Entity، API، Event و KPI است.

## 2. دامنه و مرزبندی

| سطح | در FRD v1.1 |
| --- | --- |
| Demo | فقط برای ارائه و تصمیم‌سازی؛ نه مبنای Backlog عملیاتی. |
| MVP نرم‌افزاری | PWA/Web App، QR، Quest، Reward، Merchant، Admin، Analytics پایه. |
| MVCP تجاری | Venue واقعی، سفیر، تجهیزات، رسانه، کسب‌وکار، پایلوت ۱۴ روزه، KPI و گزارش تصمیم. |
| Product نهایی | نسخه مقیاس‌پذیر چندمکانی پس از اعتبارسنجی. |

## 3. پوشش ۲۶ محور قفل‌شده

| کد | محور | پوشش FRD v1.1 |
| --- | --- | --- |
| CPL-01 | مدل قراردادی و تقسیم درآمد | Business Rules، Revenue Attribution، Settlement، Merchant Package |
| CPL-02 | حاکمیت و RACI عملیاتی | Role Matrix، Permissions، RACI، Admin Roles |
| CPL-03 | طراحی آزمایش پایلوت و خط مبنا | Baseline Metrics، Control/Comparison logic، Report |
| CPL-04 | Unit Economics | Cost per Scan/User/Mission، Value per Merchant Lead، ROI |
| CPL-05 | Venue / Zone / Hub / Touchpoint Model | Venue entities، Touchpoint registry، QR binding |
| CPL-06 | Venue Launch Kit | Launch templates، Venue Profile، Zone/Hub checklist |
| CPL-07 | Pilot Operations Runbook | Pilot entity، Daily Report، Shift Control |
| CPL-08 | سفیران میدانی و اسکریپت تعامل | Ambassador roles، Shift، Interaction logs |
| CPL-09 | مدیریت سخت‌افزار و دارایی میدانی | Asset Registry، Assignment، Status، Incident |
| CPL-10 | Interactive Media Network | Media Point، Content Schedule، QR/Media binding |
| CPL-11 | Campaign Scenario Library | Campaign templates، Mission types، Venue scenarios |
| CPL-12 | Campaign Content Operating System | Content objects، Calendar، Approval status |
| CPL-13 | Brand Safety و تأیید محتوا | Content approval workflow، Policy، Restricted content |
| CPL-14 | Merchant Onboarding | Merchant profile، Package، Reward rules، Acceptance |
| CPL-15 | Sales Playbook کسب‌وکار و اسپانسر | Offer templates، Sponsor packages، CRM-like tracking |
| CPL-16 | Fraud / Abuse / Redemption Control | Anti-fraud rules، Redemption constraints، Alerts |
| CPL-17 | حقوقی، مجوز، بیمه و مسئولیت | Terms، Consent، Incident responsibility |
| CPL-18 | Data Ownership و Data Governance | Data access levels، Retention، Deletion، Ownership |
| CPL-19 | Event Tracking و Data Dictionary | Event schema، Data dictionary، Quality flags |
| CPL-20 | Attribution و اثبات درآمدزایی | Attribution pipeline، Conversion funnel، ROI report |
| CPL-21 | Offline / Low Connectivity Plan | Offline capture، Manual fallback، Sync queue |
| CPL-22 | Support و Complaint Handling | Ticket/Issue log، FAQ، Admin handling |
| CPL-23 | تجربه کاربران خاص و دسترس‌پذیری | Low-friction flows، Accessibility rules، PWA |
| CPL-24 | Pilot Exit / Transition Plan | Closure workflow، QR deactivation، Reward expiry |
| CPL-25 | Pilot Decision Gate | Success thresholds، KPI gate، Board report |
| CPL-26 | مرزبندی Demo / MVP / Pilot / Product | Feature status، Release boundaries، Scope control |

## 4. ماژول‌های عملکردی

### ماژول 0 - Release Scope, Version Boundaries & Traceability (REL)

**Trace CPL:** CPL-26، CPL-01..CPL-26

**هدف:** مرزبندی دقیق Demo، MVP نرم‌افزاری، MVCP تجاری و Product نهایی و الزام Traceability برای جلوگیری از Scope Creep پیش از بک‌لاگ.

**بازیگران:** Owner پروژه، Product Manager، Business Analyst، Tech Lead، Steering Committee

**Entityهای کلیدی:** Release, ScopeItem, RequirementTrace, ChangeRequest, DecisionRecord

| ID | Priority | Functional Requirement | Acceptance Criteria |
| --- | --- | --- | --- |
| REL-001 | Must | سیستم مستندسازی باید هر Requirement را به BRD v1.1 و حداقل یک CPL متصل کند. | هیچ Requirement بدون Trace وارد Product Backlog نشود. |
| REL-002 | Must | هر قابلیت باید وضعیت Demo / MVP / MVCP / Product داشته باشد. | Backlog بتواند قابلیت‌های غیر MVP را جدا نگه دارد. |
| REL-003 | Must | تغییر دامنه باید به Change Request با دلیل، اثر و تصمیم مالک پروژه متصل شود. | حذف یا کاهش CPL بدون Change Request ممکن نباشد. |
| REL-004 | Should | داشبورد مدیریتی داخلی بتواند وضعیت پوشش CPLها را نشان دهد. | برای هر CPL حداقل یک Requirement یا Rule دیده شود. |

**Business Rules:**

- FRD v1.1 جایگزین FRD v1.0 برای تولید Product Backlog است.

- FRD v1.0 فقط به‌عنوان baseline تاریخی نگهداری می‌شود.

- قابلیت‌های AI، Marketplace کامل، Franchise و Wallet پیشرفته خارج از MVP/MVCP اولیه هستند مگر با Change Request.

### ماژول 1 - Authentication, Consent & Low-Friction Access (AUTH)

**Trace CPL:** CPL-17، CPL-18، CPL-23، CPL-26

**هدف:** ورود سریع کاربر در محیط واقعی پایلوت با کمترین اصطکاک، ترجیحاً PWA/Web App بدون نصب اجباری، همراه با رضایت پایه و امنیت OTP.

**بازیگران:** بازدیدکننده، کاربر بازگشتی، ادمین، سفیر میدانی، سیستم OTP

**Entityهای کلیدی:** User, OTPRequest, Session, ConsentLog, GuestAccess, LoginAudit

| ID | Priority | Functional Requirement | Acceptance Criteria |
| --- | --- | --- | --- |
| AUTH-001 | Must | کاربر باید بتواند با موبایل و OTP وارد تجربه شود. | ورود موفق در شرایط عادی کمتر از 60 ثانیه انجام شود. |
| AUTH-002 | Must | سیستم باید ثبت رضایت پایه را قبل از ورود به تجربه اصلی انجام دهد. | ConsentLog شامل نسخه متن، زمان و منبع ثبت شود. |
| AUTH-003 | Must | ورود باید برای تجربه PWA/Web App بدون نصب اجباری طراحی شود. | کاربر پس از اسکن QR مستقیم به صفحه مناسب برسد. |
| AUTH-004 | Must | OTP باید محدودیت زمان، تلاش و ارسال مجدد داشته باشد. | OTP منقضی یا تلاش غیرمجاز رد شود. |
| AUTH-005 | Should | در حالت اینترنت ضعیف، سفیر بتواند مسیر راهنمایی یا ثبت خطای ورود را فعال کند. | خطای ورود به Issue Log یا Offline Log منتقل شود. |
| AUTH-006 | Could | برای گردشگران خارجی، زیرساخت ورود چندزبانه در فاز بعد آماده باشد. | فیلد زبان و متن رضایت قابل نسخه‌بندی باشد. |

**Business Rules:**

- داده اجباری ثبت‌نام در MVP فقط داده‌های ضروری است.

- شماره موبایل نباید در گزارش عمومی یا Merchant Dashboard افشا شود.

- حساب کودک یا نوجوان باید در فاز پایلوت با داده حداقلی و محتوای مناسب کنترل شود.

### ماژول 2 - Venue / Zone / Hub / Touchpoint Configuration (VENUE)

**Trace CPL:** CPL-05، CPL-06، CPL-20، CPL-25

**هدف:** تبدیل هر محل پایلوت به نقشه عملیاتی قابل پیکربندی شامل Venue، Zone، Hub، Touchpoint، Media Point، Merchant Node و Reward Point.

**بازیگران:** ادمین، تیم عملیات، میزبان Venue، مدیر کمپین، تحلیلگر داده

**Entityهای کلیدی:** Venue, Zone, Hub, Touchpoint, MediaPoint, MerchantNode, RewardPoint, VenueProfile

| ID | Priority | Functional Requirement | Acceptance Criteria |
| --- | --- | --- | --- |
| VENUE-001 | Must | ادمین باید بتواند Venue تعریف کند و آن را به پایلوت‌ها، کمپین‌ها و گزارش‌ها وصل کند. | EcoPark، Eram و Milad Tower به‌عنوان Venue قابل تعریف باشند. |
| VENUE-002 | Must | هر Venue باید بتواند Zone و Hubهای داخلی داشته باشد. | هاب‌هایی مثل رواق، فودکورت، گنبد مینا، زون بازی و سکوی دید قابل ثبت باشند. |
| VENUE-003 | Must | هر Touchpoint باید نوع، محل، مالک، QR مرتبط و وضعیت عملیاتی داشته باشد. | گزارش اسکن به Touchpoint و Hub قابل نسبت‌دهی باشد. |
| VENUE-004 | Must | هر Media Point و Merchant Node باید به Venue/Zone/Hub متصل شود. | Attribution رسانه و فروشگاه بدون اتصال مکانی پذیرفته نشود. |
| VENUE-005 | Should | سیستم باید Template برای Venue Launch Kit تولید یا نگهداری کند. | برای Venue جدید چک‌لیست راه‌اندازی آماده باشد. |
| VENUE-006 | Should | هر Venue Profile باید محدودیت‌ها، مجوزها، مخاطبان، ساعت اوج و ظرفیت را نگهداری کند. | پیش از فعال‌سازی پایلوت، Venue Profile تکمیل‌شده باشد. |

**Business Rules:**

- هیچ QR عملیاتی نباید بدون Venue و Touchpoint معتبر فعال شود.

- هر Venue حداقل یک Zone، یک Hub و یک Touchpoint فعال برای پایلوت نیاز دارد.

- برج میلاد تا زمان دریافت Venue Profile مستقل باید با status=Placeholder نگهداری شود.

### ماژول 3 - Pilot Operations, Runbook & Daily Control (PILOT)

**Trace CPL:** CPL-03، CPL-07، CPL-08، CPL-21، CPL-24، CPL-25

**هدف:** مدیریت اجرای ۱۴ روزه یا پایلوت‌های مشابه با آماده‌سازی، شیفت، گزارش روزانه، کنترل رخداد، خط مبنا، خروج و تصمیم نهایی.

**بازیگران:** Pilot Manager، تیم عملیات، سفیران، میزبان Venue، ادمین، کمیته راهبری

**Entityهای کلیدی:** Pilot, PilotDay, Shift, DailyReport, BaselineMetric, Incident, ClosureReport, DecisionGate

| ID | Priority | Functional Requirement | Acceptance Criteria |
| --- | --- | --- | --- |
| PILOT-001 | Must | ادمین باید بتواند Pilot با Venue، Zone، تاریخ شروع/پایان، KPI و وضعیت تعریف کند. | پایلوت ارم ۱۴ روزه و پایلوت‌های دیگر قابل ثبت باشند. |
| PILOT-002 | Must | سیستم باید خط مبنا یا Baseline Metrics را برای مقایسه قبل/حین/بعد نگهداری کند. | گزارش نهایی بتواند افزایش تعامل را نسبت به Baseline نشان دهد. |
| PILOT-003 | Must | هر روز پایلوت باید Daily Report شامل KPI، رخداد، خرابی، شکایت و یادداشت عملیات داشته باشد. | گزارش پایان روز بدون داده‌های اصلی تکمیل‌شده محسوب نشود. |
| PILOT-004 | Must | شیفت‌ها، سفیران، Touchpointهای فعال و تجهیزات هر روز باید قابل تخصیص باشند. | هر سفیر و دارایی به Shift مشخص وصل شود. |
| PILOT-005 | Must | سیستم باید وضعیت Pilot را از Planned تا Active، Paused، Completed، Extended یا Closed مدیریت کند. | QR و کمپین‌ها با وضعیت پایلوت هماهنگ شوند. |
| PILOT-006 | Should | در پایان پایلوت، Closure Report و Board Decision Gate تولید شود. | خروجی توسعه/تکرار/توقف با KPI مرتبط باشد. |

**Business Rules:**

- هیچ پایلوتی بدون KPI مصوب و تاریخ شروع/پایان فعال نشود.

- هر Daily Report باید توسط Pilot Manager تأیید شود.

- اگر KPIهای عملیاتی بحرانی نقض شدند، پایلوت باید Paused یا Escalated شود.

### ماژول 4 - Field Ambassadors & Interaction Scripts (AMB)

**Trace CPL:** CPL-08، CPL-22، CPL-23

**هدف:** مدیریت سفیران میدانی، آموزش، شیفت، اسکریپت تعامل، گزارش تعامل و پاسخ به سؤال یا مشکل کاربر.

**بازیگران:** سفیر میدانی، سرپرست شیفت، کاربر، Pilot Manager، Support Agent

**Entityهای کلیدی:** Ambassador, AmbassadorShift, InteractionScript, InteractionLog, TrainingRecord, FAQ

| ID | Priority | Functional Requirement | Acceptance Criteria |
| --- | --- | --- | --- |
| AMB-001 | Must | سیستم باید سفیران، نقش، وضعیت، آموزش و شیفت آن‌ها را نگهداری کند. | هر سفیر فعال در روز پایلوت به Shift متصل باشد. |
| AMB-002 | Must | برای هر کمپین/هَب، اسکریپت کوتاه تعامل سفیر باید قابل تعریف باشد. | سفیر بتواند پیام واحد برای دعوت به QR و توضیح پاداش داشته باشد. |
| AMB-003 | Should | سفیر بتواند تعامل‌های میدانی را به‌صورت شمارشی یا Log ساده ثبت کند. | تعداد تعامل، سؤال پرتکرار و مشکل در Daily Report دیده شود. |
| AMB-004 | Should | FAQ و پاسخ به اعتراض‌های رایج در پنل یا فایل عملیاتی قابل مشاهده باشد. | سفیر برای مشکلات QR/جایزه/حریم خصوصی پاسخ پایه داشته باشد. |
| AMB-005 | Could | امتیاز عملکرد سفیر بر اساس اسکن، تعامل و کیفیت گزارش محاسبه شود. | KPI سفیر برای بهبود عملیات در دسترس باشد. |

**Business Rules:**

- سفیر بدون آموزش و اسکریپت مصوب نباید در پایلوت فعال شود.

- سفیر نباید داده شخصی اضافه یا خارج از فرایند رسمی جمع‌آوری کند.

- مشکل پاداش یا شکایت کاربر باید به Support/Issue Log هدایت شود.

### ماژول 5 - Hardware & Field Asset Management (ASSET)

**Trace CPL:** CPL-09، CPL-10، CPL-21

**هدف:** کنترل دارایی‌های میدانی شامل کوله‌پشتی هولوگرافیک، نمایشگر ثابت، استند، QR چاپی، پاوربانک، اینترنت و تجهیزات سفیران.

**بازیگران:** Asset Manager، سفیر، سرپرست شیفت، تیم فنی، Pilot Manager

**Entityهای کلیدی:** Asset, AssetAssignment, AssetStatus, MaintenanceLog, DeviceCheck, SIMCard, BatteryPack

| ID | Priority | Functional Requirement | Acceptance Criteria |
| --- | --- | --- | --- |
| ASSET-001 | Must | هر دارایی میدانی باید شناسه، نوع، مالک، وضعیت و محل نگهداری داشته باشد. | کوله‌پشتی، نمایشگر، QR چاپی و پاوربانک قابل ثبت باشند. |
| ASSET-002 | Must | تحویل و تحویل‌گیری دارایی به سفیر/شیفت باید ثبت شود. | در پایان شیفت، وضعیت بازگشت یا خرابی مشخص باشد. |
| ASSET-003 | Must | خرابی، گم‌شدن، آسیب یا قطع اینترنت تجهیزات باید Incident یا MaintenanceLog ایجاد کند. | خرابی روی گزارش روزانه و KPI عملیاتی اثر بگذارد. |
| ASSET-004 | Should | برای هر دارایی رسانه‌ای، QR یا Content Schedule مرتبط نگهداری شود. | اثر هر دستگاه بر اسکن و تعامل قابل سنجش باشد. |
| ASSET-005 | Should | چک‌لیست شروع روز برای شارژ، اینترنت، محتوا و سلامت QR/نمایشگر وجود داشته باشد. | پیش از شروع پایلوت همه دارایی‌های حیاتی Pass شوند. |

**Business Rules:**

- رسانه یا QR بدون Asset/Touchpoint معتبر نباید در میدان استفاده شود.

- خرابی دارایی حیاتی باید به Pilot Manager و Daily Report منتقل شود.

- تجهیزات دارای مسئولیت مالی یا بیمه باید وضعیت تحویل دقیق داشته باشند.

### ماژول 6 - Interactive Media Network & Content Scheduling (MEDIA)

**Trace CPL:** CPL-10، CPL-12، CPL-13، CPL-20

**هدف:** اتصال رسانه‌های هولوگرافیک، نمایشگرهای ثابت و پیام‌های محیطی به محتوا، QR، کمپین و گزارش اثر رسانه.

**بازیگران:** مدیر رسانه، تیم محتوا، ادمین، میزبان، اسپانسر، سفیر

**Entityهای کلیدی:** MediaPoint, MediaAsset, ContentItem, ContentSchedule, MediaQRBinding, ContentApproval

| ID | Priority | Functional Requirement | Acceptance Criteria |
| --- | --- | --- | --- |
| MEDIA-001 | Must | سیستم باید Media Point تعریف کند و آن را به Venue/Zone/Hub/Asset متصل کند. | کوله‌پشتی و نمایشگر ثابت قابل ردیابی باشند. |
| MEDIA-002 | Must | هر محتوای رسانه‌ای باید Campaign، QR یا Call-to-Action مرتبط داشته باشد. | نمایش رسانه بدون مقصد سنجش‌پذیر مجاز نباشد. |
| MEDIA-003 | Must | محتوا باید وضعیت Draft، Pending Approval، Approved، Live، Archived داشته باشد. | محتوای تأییدنشده منتشر نشود. |
| MEDIA-004 | Should | برنامه پخش محتوا بر اساس تاریخ، ساعت، Venue و Media Point قابل ثبت باشد. | گزارش اسکن به زمان پخش قابل مقایسه باشد. |
| MEDIA-005 | Should | سیستم باید تعداد اسکن یا تعامل ناشی از هر Media Point را گزارش کند. | ROI رسانه برای هیئت‌مدیره و اسپانسر قابل نمایش باشد. |

**Business Rules:**

- هر محتوای کودک/خانواده باید Brand Safety و محتوای مناسب سن را رعایت کند.

- استفاده از تصویر افراد در محتوا فقط با سیاست مجاز و تأیید میزبان انجام شود.

- محتوای اسپانسری باید از نظر محدوده نمایش و مدت پخش قابل Audit باشد.

### ماژول 7 - Campaign Scenario Library & Content Operating System (CAMPAIGN)

**Trace CPL:** CPL-11، CPL-12، CPL-13، CPL-20

**هدف:** ایجاد سناریوهای آماده و قابل فروش برای اکوپارک، ارم و برج میلاد و مدیریت تقویم کمپین، پیام‌ها، مأموریت‌ها و محتوای روزانه.

**بازیگران:** Campaign Manager، Content Manager، ادمین، میزبان، اسپانسر، Merchant

**Entityهای کلیدی:** Campaign, ScenarioTemplate, MissionType, CampaignCalendar, ContentItem, ApprovalRecord

| ID | Priority | Functional Requirement | Acceptance Criteria |
| --- | --- | --- | --- |
| CAM-001 | Must | ادمین باید بتواند Campaign با Venue، هدف، تاریخ، سناریو و KPI تعریف کند. | کمپین کشف گنج، پایلوت ارم و سناریوی برج میلاد قابل ثبت باشند. |
| CAM-002 | Must | سیستم باید کتابخانه سناریو شامل Treasure Hunt، Food Tour، Family Mission، Skill Challenge، Timed Mission و Sponsored Mission داشته باشد. | هر Scenario Template دارای Mission Type و Reward Logic پایه باشد. |
| CAM-003 | Must | تقویم کمپین باید محتوا، مأموریت، رسانه و پاداش را در هر روز/ساعت پایلوت هماهنگ کند. | Daily Runbook بتواند از Campaign Calendar استفاده کند. |
| CAM-004 | Should | برای هر Venue، سناریوهای پیشنهادی قابل Template و Clone باشند. | راه‌اندازی Venue جدید از صفر شروع نشود. |
| CAM-005 | Should | Brand Safety و Approval Workflow برای محتوای کمپین اجباری باشد. | هیچ محتوای Live بدون Approval منتشر نشود. |

**Business Rules:**

- Quest Engine به‌تنهایی کافی نیست؛ هر Quest باید در یک Campaign/Scenario معنا داشته باشد.

- سناریوی اسپانسری باید بسته تجاری، KPI و محدودیت محتوا داشته باشد.

- محتوای روزانه باید قبل از شروع روز پایلوت تأیید و قفل شود.

### ماژول 8 - QR, Touchpoint & Scan Validation Engine (QR)

**Trace CPL:** CPL-05، CPL-16، CPL-19، CPL-20، CPL-21

**هدف:** تولید، نصب، اعتبارسنجی، ردیابی و کنترل سوءاستفاده QRها به‌عنوان نقطه اتصال مکان، رسانه، کمپین، مأموریت و پاداش.

**بازیگران:** کاربر، سفیر، ادمین، Merchant، سیستم ضدتقلب، Analytics

**Entityهای کلیدی:** QRCode, QRPlacement, QRScan, QRRule, QRValidation, OfflineScan, ScanRiskFlag

| ID | Priority | Functional Requirement | Acceptance Criteria |
| --- | --- | --- | --- |
| QR-001 | Must | هر QR باید شناسه یکتا، مقصد، Venue، Touchpoint، Campaign و وضعیت داشته باشد. | QR بدون اتصال مکانی/کمپینی فعال نشود. |
| QR-002 | Must | هر اسکن باید با زمان، کاربر/جلسه، QR، نتیجه و Source ثبت شود. | Event qr_scanned برای Analytics تولید شود. |
| QR-003 | Must | سیستم باید QR منقضی، غیرفعال، خارج از زمان یا خارج از Rule را رد کند. | اسکن نامعتبر امتیاز یا پاداش ایجاد نکند. |
| QR-004 | Must | کنترل اسکن تکراری و محدودیت تعداد اسکن برای کاربر/QR/بازه زمانی وجود داشته باشد. | رفتار مشکوک Risk Flag شود. |
| QR-005 | Should | در حالت اینترنت ضعیف، Offline Scan یا Manual Fallback با sync بعدی پشتیبانی شود. | داده آفلاین بعداً با برچسب Offline وارد Event Stream شود. |
| QR-006 | Should | ادمین بتواند QRهای چاپی را با برچسب محل نصب و تست روزانه خروجی بگیرد. | چک‌لیست نصب QR برای Runbook تولید شود. |

**Business Rules:**

- QR چاپ‌شده باید پیش از نصب تست شود.

- هر QR باید مالک عملیاتی مشخص داشته باشد.

- QRهای پایان پایلوت باید Archive یا Expire شوند.

### ماژول 9 - Quest Engine & Mission Execution (QUEST)

**Trace CPL:** CPL-11، CPL-19، CPL-20، CPL-23

**هدف:** اجرای مأموریت‌های قابل سنجش در سناریوهای مختلف با ثبت پیشرفت، اعتبارسنجی تکمیل، اتصال به پاداش و رعایت تجربه ساده کاربر.

**بازیگران:** کاربر، ادمین، Campaign Manager، Merchant، سفیر، Reward Engine

**Entityهای کلیدی:** Quest, QuestStep, QuestRule, UserQuestProgress, MissionCompletion, ScenarioType, QuestValidation

| ID | Priority | Functional Requirement | Acceptance Criteria |
| --- | --- | --- | --- |
| QUEST-001 | Must | سیستم باید Quest با Scenario، Venue/Hub، زمان، Rule، Reward و وضعیت تعریف کند. | مأموریت فعال در کمپین درست به کاربر نمایش داده شود. |
| QUEST-002 | Must | حداقل مأموریت QR-based، Timed، Merchant Visit و Reward-triggered پشتیبانی شود. | اکوپارک/ارم/برج میلاد بتوانند سناریوهای پایه اجرا کنند. |
| QUEST-003 | Must | پیشرفت و تکمیل مأموریت باید برای هر کاربر ثبت شود. | Event mission_started و mission_completed تولید شود. |
| QUEST-004 | Must | تکمیل مأموریت باید قابل اعتبارسنجی و Audit باشد. | تکمیل جعلی یا تکراری امتیاز ایجاد نکند. |
| QUEST-005 | Should | مأموریت‌های Family، Food Tour، Skill Challenge و Photo/Social به‌صورت Template قابل ایجاد باشند. | Campaign Manager بتواند Template را Clone کند. |
| QUEST-006 | Should | مسیر مأموریت باید برای کاربر ساده و کوتاه باشد. | کاربر با حداکثر چند اقدام اصلی مأموریت را بفهمد و شروع کند. |

**Business Rules:**

- هر Quest باید Reward Rule مشخص داشته باشد یا explicitly بدون پاداش تعریف شود.

- Quest منقضی نباید برای کاربر جدید شروع شود.

- Questهای کودکان و خانواده باید محتوای مناسب و مسیر امن داشته باشند.

### ماژول 10 - Reward, Points, Coupon, Redemption & Fraud Control (REWARD)

**Trace CPL:** CPL-01، CPL-14، CPL-16، CPL-20، CPL-22، CPL-24

**هدف:** صدور امتیاز، کوپن، تخفیف، جایزه یا تجربه ویژه و کنترل مصرف امن، یک‌بارمصرف، قابل ردیابی و قابل انتساب به Merchant/Campaign.

**بازیگران:** کاربر، Merchant Staff، ادمین، سیستم ضدتقلب، Support Agent، Analytics

**Entityهای کلیدی:** Reward, Coupon, PointLedger, Redemption, RedemptionCode, FraudFlag, RewardRule, ExpiryPolicy

| ID | Priority | Functional Requirement | Acceptance Criteria |
| --- | --- | --- | --- |
| REWARD-001 | Must | سیستم باید پاداش را بر اساس Rule مأموریت صادر کند. | پس از mission_completed، reward_issued ایجاد شود. |
| REWARD-002 | Must | کوپن باید وضعیت Active، Redeemed، Expired، Revoked داشته باشد. | کوپن مصرف‌شده دوباره قابل مصرف نباشد. |
| REWARD-003 | Must | مصرف پاداش در Merchant/Reward Point باید با کد یا تأیید معتبر ثبت شود. | Event reward_redeemed و Redemption رکورد شود. |
| REWARD-004 | Must | سقف مصرف، محدودیت زمانی، محدودیت تعداد و کنترل استفاده تکراری وجود داشته باشد. | تقلب‌های پایه Flag شوند. |
| REWARD-005 | Should | پاداش‌های پایان پایلوت باید سیاست انقضا/تمدید/تسویه داشته باشند. | Closure Report شامل پاداش‌های مصرف‌نشده باشد. |
| REWARD-006 | Should | اختلاف پاداش یا عدم پذیرش فروشگاه باید Issue/Ticket ایجاد کند. | Support بتواند مورد را پیگیری کند. |

**Business Rules:**

- هیچ پاداشی بدون مالک هزینه یا تأمین‌کننده مشخص نباید فعال شود.

- Merchant باید قبل از کمپین، تعهد پذیرش پاداش را تأیید کند.

- Point Ledger باید قابل Audit و غیرقابل ویرایش مستقیم باشد.

### ماژول 11 - Merchant Onboarding, Packages & Dashboard (MERCH)

**Trace CPL:** CPL-01، CPL-14، CPL-15، CPL-20

**هدف:** ورود کسب‌وکارها به اکوسیستم با پکیج تجاری، QR اختصاصی، تعهد پاداش، آموزش پذیرش و گزارش عملکرد قابل فروش.

**بازیگران:** Merchant Owner، Merchant Staff، تیم فروش، ادمین، Sponsor، Pilot Manager

**Entityهای کلیدی:** Merchant, MerchantUser, MerchantPackage, OfferTemplate, RewardCommitment, MerchantReport, SponsorPackage

| ID | Priority | Functional Requirement | Acceptance Criteria |
| --- | --- | --- | --- |
| MERCH-001 | Must | سیستم باید Merchant Profile شامل Venue/Hub، دسته، وضعیت، مسئول و پکیج داشته باشد. | Merchant در رواق، فودکورت، زون بازی یا برج میلاد قابل ثبت باشد. |
| MERCH-002 | Must | پکیج‌های پایه، رشد، ویژه، اسپانسری یا Media Bundle باید قابل تعریف باشند. | هر Merchant به یک Package متصل شود. |
| MERCH-003 | Must | هر Merchant باید Reward/Offer فعال، محدودیت و روش پذیرش داشته باشد. | کوپن فروشگاهی بدون تعهد پذیرش منتشر نشود. |
| MERCH-004 | Must | Merchant Dashboard باید اسکن، مراجعه، پاداش صادر/مصرف‌شده و قیف تبدیل مربوط به همان Merchant را نشان دهد. | Merchant داده سایر کسب‌وکارها را نبیند. |
| MERCH-005 | Should | Sales Playbook و Offer Template برای مذاکره و تمدید قابل نگهداری باشد. | تیم فروش بتواند نمونه گزارش و پیشنهاد قیمت را استفاده کند. |
| MERCH-006 | Should | فرایند تمدید/خروج Merchant پس از پایلوت بر اساس KPI و رضایت ثبت شود. | Merchant Decision در Closure Report دیده شود. |

**Business Rules:**

- Merchant باید قوانین Brand Safety و پذیرش پاداش را امضا/تأیید کند.

- دسترسی Merchant محدود به داده‌های خودش است.

- پکیج اسپانسری باید دامنه نمایش، مدت، KPI و قیمت داشته باشد.

### ماژول 12 - Contracts, Revenue Share & Settlement Control (COMM)

**Trace CPL:** CPL-01، CPL-04، CPL-14، CPL-15، CPL-20

**هدف:** ثبت ساختار تجاری پایلوت شامل جریان درآمد، سهم طرفین، پکیج‌ها، تسویه، اسپانسرینگ و منطق انتساب درآمد.

**بازیگران:** Business Owner، Finance، Venue Host، Merchant، Sponsor، Admin

**Entityهای کلیدی:** CommercialAgreement, RevenueStream, RevenueShareRule, InvoiceRecord, SettlementRecord, SponsorDeal

| ID | Priority | Functional Requirement | Acceptance Criteria |
| --- | --- | --- | --- |
| COMM-001 | Must | برای هر پایلوت باید Commercial Agreement شامل طرفین، دوره، Revenue Stream و سهم‌ها قابل ثبت باشد. | پایلوت بدون قرارداد/توافق تجاری حداقلی Active نشود. |
| COMM-002 | Must | جریان‌های درآمد مانند Campaign Fee، Sponsored Mission، Media Display، Merchant Package و Analytics Report قابل طبقه‌بندی باشند. | گزارش مالی به تفکیک جریان درآمد تولید شود. |
| COMM-003 | Should | Settlement Record برای دوره تسویه، مبلغ، وضعیت و طرف دریافت‌کننده نگهداری شود. | تسویه‌های پایان پایلوت قابل پیگیری باشند. |
| COMM-004 | Should | Unit Economics پایه مثل Cost per Scan/User/Mission و Value per Merchant Lead قابل ثبت/محاسبه باشد. | گزارش هیئت‌مدیره شامل شاخص اقتصادی باشد. |
| COMM-005 | Could | قیمت‌گذاری پکیج‌ها و اسپانسرینگ در نسخه بعد به CRM/Finance متصل شود. | ساختار داده قابل توسعه باشد. |

**Business Rules:**

- FRD v1.1 عملیات مالی کامل حسابداری را نمی‌سازد، اما داده لازم برای اثبات درآمد و تسویه را نگه می‌دارد.

- هر درآمد قابل گزارش باید به Campaign، Venue یا Merchant/Sponsor قابل نسبت‌دهی باشد.

### ماژول 13 - Event Tracking, Data Dictionary & Quality Flags (EVENT)

**Trace CPL:** CPL-03، CPL-19، CPL-20

**هدف:** تعریف رویدادهای قابل اتکا برای داشبورد، Attribution، خط مبنا، گزارش ROI و تصمیم هیئت‌مدیره.

**بازیگران:** سیستم، Analytics، ادمین، تیم داده، Product Manager

**Entityهای کلیدی:** Event, EventSchema, EventProperty, DataQualityFlag, EventSource, EventVersion

| ID | Priority | Functional Requirement | Acceptance Criteria |
| --- | --- | --- | --- |
| EVENT-001 | Must | سیستم باید رویدادهای اصلی qr_scanned، user_registered، mission_started، mission_completed، reward_issued، reward_redeemed، feedback_submitted را ثبت کند. | هر رویداد با timestamp و source تولید شود. |
| EVENT-002 | Must | هر رویداد باید تا حد امکان به Venue، Zone، Hub، Touchpoint، Campaign و Merchant متصل باشد. | رویداد بدون اتصال ضروری Quality Flag بگیرد. |
| EVENT-003 | Must | Data Dictionary باید تعریف، فیلدها، نوع داده و مالک هر رویداد را نگهداری کند. | تیم توسعه و تحلیل از تعریف واحد استفاده کنند. |
| EVENT-004 | Should | رویدادهای ambassador_interaction_logged، media_interaction_logged، incident_logged و offline_sync_completed در MVCP پشتیبانی شوند. | گزارش میدانی و رسانه‌ای تقویت شود. |
| EVENT-005 | Should | سیستم باید رویدادهای ناقص، تکراری یا مشکوک را علامت‌گذاری کند. | Analytics بتواند کیفیت داده را گزارش کند. |

**Business Rules:**

- تعریف Eventها پیش از شروع کدنویسی باید قفل شود.

- هر KPI باید به یک یا چند Event قابل ردیابی باشد.

- داده‌های شخصی غیرضروری نباید در Event Payload ذخیره شود.

### ماژول 14 - Analytics, Attribution, KPI & Board Decision Gate (ANALYTICS)

**Trace CPL:** CPL-03، CPL-04، CPL-19، CPL-20، CPL-25

**هدف:** ارائه داشبوردهای روزانه، تجاری، رسانه‌ای، عملیاتی و نهایی برای اثبات تعامل، درآمدزایی، Attribution و تصمیم توسعه/تکرار/توقف.

**بازیگران:** هیئت‌مدیره، Venue Host، Pilot Manager، Merchant، Sponsor، Product Team

**Entityهای کلیدی:** KPIDefinition, KPIValue, FunnelReport, AttributionReport, BoardReport, DecisionGateResult

| ID | Priority | Functional Requirement | Acceptance Criteria |
| --- | --- | --- | --- |
| ANALYTICS-001 | Must | Admin Dashboard باید KPIهای اصلی پایلوت را روزانه نمایش دهد. | اسکن، ثبت‌نام، کاربران فعال، مأموریت کامل، پاداش مصرف‌شده و خطاها دیده شوند. |
| ANALYTICS-002 | Must | قیف تبدیل Scan -> Registration -> Mission Start -> Mission Complete -> Reward Redeem باید قابل محاسبه باشد. | افت هر مرحله قابل مشاهده باشد. |
| ANALYTICS-003 | Must | Attribution Report باید اثر QR/Media/Campaign/Touchpoint/Merchant را تفکیک کند. | ارزش مالی و عملیاتی به منبع درست نسبت داده شود. |
| ANALYTICS-004 | Must | Board Decision Gate باید تحقق KPI را به سه وضعیت توسعه، بهینه‌سازی/تکرار یا توقف تبدیل کند. | گزارش نهایی شامل منطق تصمیم باشد. |
| ANALYTICS-005 | Should | Unit Economics و ROI پایه در گزارش نهایی محاسبه شود. | Cost per Scan/User/Mission و Value per Lead دیده شود. |
| ANALYTICS-006 | Should | Merchant و Sponsor فقط گزارش مرتبط با خود را ببینند. | حریم داده و قرارداد رعایت شود. |

**Business Rules:**

- اگر KPIهای هدف هنوز عددگذاری نشده‌اند، سیستم باید Target Placeholder داشته باشد نه عدد ساختگی.

- گزارش هیئت‌مدیره باید داده واقعی، خط مبنا و محدودیت‌های تحلیل را نشان دهد.

- داده ناقص باید از KPI رسمی جدا یا علامت‌گذاری شود.

### ماژول 15 - Admin Console, Governance, RACI & Permissions (ADMIN)

**Trace CPL:** CPL-02، CPL-17، CPL-18، CPL-26

**هدف:** مدیریت کل اکوسیستم پایلوت با دسترسی مبتنی بر نقش، RACI، Audit Log و کنترل تغییرات حساس.

**بازیگران:** Super Admin، Venue Admin، Pilot Manager، Campaign Manager، Merchant Admin، Support Agent، Analyst

**Entityهای کلیدی:** AdminUser, Role, Permission, RACIRecord, AuditLog, ChangeRequest, ApprovalRecord

| ID | Priority | Functional Requirement | Acceptance Criteria |
| --- | --- | --- | --- |
| ADMIN-001 | Must | سیستم باید نقش‌ها و دسترسی‌های متفاوت برای Admin، Venue، Merchant، Support و Analyst داشته باشد. | هر نقش فقط امکانات مجاز را ببیند. |
| ADMIN-002 | Must | تغییرات حساس روی Venue، Campaign، Quest، Reward، QR و Merchant باید Audit Log ایجاد کند. | چه کسی، چه چیزی، چه زمانی تغییر داده قابل مشاهده باشد. |
| ADMIN-003 | Must | RACI عملیاتی هر پایلوت باید قابل ثبت یا پیوست باشد. | تصمیم‌ها و مسئولیت‌ها در Runbook قابل ارجاع باشند. |
| ADMIN-004 | Should | Approval Workflow برای محتوا، پاداش، اسپانسرینگ و شروع پایلوت وجود داشته باشد. | مورد تأییدنشده Live نشود. |
| ADMIN-005 | Should | Change Request برای حذف/کاهش دامنه CPL یا تغییر KPI قابل ثبت باشد. | قانون قفل CPL اجرایی شود. |

**Business Rules:**

- دسترسی به داده کسب‌وکارها و کاربران باید کمینه و مبتنی بر نقش باشد.

- ادمین نباید بتواند Point Ledger را بدون رویداد جبرانی تغییر دهد.

- شروع پایلوت باید Approval از مالک پروژه یا کمیته داشته باشد.

### ماژول 16 - Support, Complaint Handling & Incident Management (SUPPORT)

**Trace CPL:** CPL-17، CPL-21، CPL-22، CPL-23

**هدف:** مدیریت مشکلات کاربر، QR، پاداش، فروشگاه، حریم خصوصی، تجهیزات و رخدادهای میدانی با سطح‌بندی و پاسخ‌گویی شفاف.

**بازیگران:** کاربر، سفیر، Support Agent، Merchant، Pilot Manager، Venue Host

**Entityهای کلیدی:** SupportTicket, IssueType, Incident, Escalation, FAQ, ResolutionLog, UserFeedback

| ID | Priority | Functional Requirement | Acceptance Criteria |
| --- | --- | --- | --- |
| SUPPORT-001 | Must | کاربر یا سفیر باید بتواند مشکل QR، پاداش، امتیاز یا فروشگاه را ثبت کند. | Ticket با نوع مشکل و وضعیت ایجاد شود. |
| SUPPORT-002 | Must | ادمین/Support باید وضعیت Open، In Review، Resolved، Rejected یا Escalated را مدیریت کند. | کاربر یا تیم عملیات وضعیت پیگیری را بداند. |
| SUPPORT-003 | Must | رخدادهای میدانی مانند ازدحام، اعتراض، خرابی، آسیب یا موضوع حقوقی باید Incident ثبت کنند. | Incident در Daily Report دیده شود. |
| SUPPORT-004 | Should | FAQ برای کاربر، سفیر و Merchant قابل نگهداری باشد. | سؤالات پرتکرار به محتوای آموزشی تبدیل شود. |
| SUPPORT-005 | Should | Feedback و رضایت کاربر پس از تجربه یا پاداش ثبت شود. | KPI رضایت و نقاط مشکل‌دار قابل تحلیل باشد. |

**Business Rules:**

- مشکلات پاداش باید به Merchant و Reward Engine قابل نسبت‌دهی باشد.

- رخدادهای حقوقی یا امنیتی باید Escalation فوری داشته باشند.

- بازخورد کاربر نباید داده حساس غیرضروری جمع‌آوری کند.

### ماژول 17 - Offline / Low Connectivity & Manual Fallback (OFFLINE)

**Trace CPL:** CPL-21، CPL-07، CPL-08، CPL-19

**هدف:** حفظ تجربه کاربر و کیفیت داده در شرایط ضعف اینترنت، باز نشدن QR، قطع داشبورد یا نیاز به ثبت دستی در میدان.

**بازیگران:** کاربر، سفیر، Pilot Manager، سیستم Sync، Support Agent

**Entityهای کلیدی:** OfflineAction, SyncQueue, ManualEntry, ConnectivityIssue, RecoveryLog

| ID | Priority | Functional Requirement | Acceptance Criteria |
| --- | --- | --- | --- |
| OFF-001 | Must | سیستم باید خطای اتصال یا باز نشدن QR را با پیام ساده و قابل فهم نمایش دهد. | کاربر سردرگم یا رها نشود. |
| OFF-002 | Must | سفیر باید بتواند مشکل اتصال یا ثبت دستی حداقلی را گزارش کند. | Offline/Manual Log در Daily Report دیده شود. |
| OFF-003 | Should | Offline Scan یا Offline Interaction با برچسب منبع و زمان ذخیره و پس از اتصال sync شود. | داده آفلاین در Event Stream با Quality Flag وارد شود. |
| OFF-004 | Should | Runbook باید سناریوی جایگزین برای QR خراب، اینترنت ضعیف و خرابی نمایشگر داشته باشد. | تیم میدان بداند در هر خطا چه کند. |
| OFF-005 | Could | بخشی از محتوای مأموریت یا FAQ در PWA cache شود. | تجربه پایه حتی با اینترنت ضعیف قابل مشاهده باشد. |

**Business Rules:**

- داده آفلاین باید از داده آنلاین تفکیک و علامت‌گذاری شود.

- ثبت دستی نباید پاداش مالی بدون اعتبارسنجی صادر کند.

- Recovery پس از قطعی باید قابل Audit باشد.

### ماژول 18 - Data Ownership, Privacy, Security & Retention (DATA)

**Trace CPL:** CPL-17، CPL-18، CPL-19، CPL-23

**هدف:** حفظ اعتماد کاربران، میزبان و کسب‌وکارها با حداقل‌گرایی داده، مالکیت شفاف داده، دسترسی محدود، نگهداری کنترل‌شده و آمادگی حذف/غیرفعال‌سازی.

**بازیگران:** کاربر، ادمین، Venue Host، Merchant، Sponsor، تیم امنیت، تیم داده

**Entityهای کلیدی:** ConsentLog, DataAccessPolicy, DataRetentionPolicy, DataDeletionRequest, DataAccessLog, SecurityEvent

| ID | Priority | Functional Requirement | Acceptance Criteria |
| --- | --- | --- | --- |
| DATA-001 | Must | سیستم باید سطح دسترسی داده را برای User، Venue، Merchant، Sponsor و Admin تفکیک کند. | Merchant یا Sponsor داده شخصی غیرمجاز نبیند. |
| DATA-002 | Must | داده شخصی فقط در حد لازم برای MVP/MVCP جمع‌آوری شود. | فیلدهای اختیاری و حساس اجباری نباشند. |
| DATA-003 | Must | سیاست نگهداری داده و گزارش‌های تجمیعی باید در سطح سند و سیستم قابل تعریف باشد. | دوره نگهداری برای رویدادها و داده‌های شخصی مشخص باشد. |
| DATA-004 | Must | دسترسی به داده و تغییرات حساس باید Log شود. | DataAccessLog و AuditLog قابل بررسی باشند. |
| DATA-005 | Should | درخواست غیرفعال‌سازی/حذف حساب برای فاز بعد آماده‌سازی شود. | ساختار داده با حذف یا anonymization سازگار باشد. |
| DATA-006 | Should | داده کودکان/خانواده باید با محتوای مناسب و کمینه‌سازی سختگیرانه مدیریت شود. | سناریوهای Family داده اضافی نخواهند. |

**Business Rules:**

- فروش داده شخصی کاربران ممنوع است.

- میزبان و کسب‌وکارها ترجیحاً گزارش تجمیعی دریافت می‌کنند نه داده خام کاربران.

- Data Governance نباید قربانی سرعت پایلوت شود.

### ماژول 19 - Pilot Exit, Transition, Launch Kit & Multi-Venue Scaling (EXIT)

**Trace CPL:** CPL-06، CPL-24، CPL-25، CPL-26

**هدف:** مدیریت پایان پایلوت، تمدید یا توقف، جمع‌آوری/تمدید QRها، پاداش‌های مصرف‌نشده، گزارش ذی‌نفعان و تکرارپذیری برای Venueهای بعدی.

**بازیگران:** Pilot Manager، Steering Committee، Venue Host، Merchant، Product Manager، Operations Team

**Entityهای کلیدی:** PilotClosure, TransitionPlan, VenueLaunchKit, PostPilotReport, MerchantExitRecord, QRClosureAction

| ID | Priority | Functional Requirement | Acceptance Criteria |
| --- | --- | --- | --- |
| EXIT-001 | Must | سیستم باید برای هر Pilot برنامه Closure شامل QRها، پاداش‌ها، گزارش‌ها و وضعیت Merchantها داشته باشد. | پایان پایلوت بدون Closure Plan ثبت نشود. |
| EXIT-002 | Must | پاداش‌های مصرف‌نشده باید سیاست Expire، Extend یا Convert داشته باشند. | کاربر و Merchant درباره وضعیت پاداش بلاتکلیف نمانند. |
| EXIT-003 | Must | گزارش پایان پایلوت باید نسخه‌های میزبان، Merchant/Sponsor و تیم محصول را تفکیک کند. | هر ذی‌نفع گزارش مناسب دریافت کند. |
| EXIT-004 | Should | Venue Launch Kit باید از نتایج پایلوت به‌روز شود. | درس‌آموخته‌ها در Venue بعدی استفاده شوند. |
| EXIT-005 | Should | تبدیل پایلوت موفق به فاز دوم باید با Transition Plan و KPI جدید انجام شود. | ادامه پروژه سلیقه‌ای و بدون دامنه نباشد. |

**Business Rules:**

- QRهای پایان پایلوت باید Expired/Archived یا به فاز بعد منتقل شوند.

- تصمیم توسعه/توقف باید با Board Decision Gate ثبت شود.

- Venue جدید نباید بدون Launch Kit حداقلی شروع شود.

## 5. API Map پیشنهادی

| Endpoint | Module | کارکرد | Role |
| --- | --- | --- | --- |
| POST /auth/request-otp | AUTH | ارسال OTP | Public |
| POST /auth/verify-otp | AUTH | اعتبار OTP، Session و Consent gate | Public |
| GET /me | AUTH/PROFILE | پروفایل و وضعیت کاربر | User |
| POST /venues | VENUE | ایجاد Venue | Admin |
| POST /venues/{id}/zones | VENUE | تعریف Zone | Admin |
| POST /venues/{id}/touchpoints | VENUE | تعریف Touchpoint | Admin |
| GET /venues/{id}/launch-kit | VENUE/EXIT | خروجی چک‌لیست Launch Kit | Admin |
| POST /pilots | PILOT | تعریف پایلوت | Admin |
| PATCH /pilots/{id}/status | PILOT | تغییر وضعیت پایلوت | Pilot Manager |
| POST /pilots/{id}/daily-reports | PILOT | ثبت گزارش روزانه | Pilot Manager |
| POST /pilots/{id}/closure | EXIT | گزارش پایان پایلوت | Pilot Manager |
| POST /ambassadors | AMB | ثبت سفیر | Admin |
| POST /shifts | PILOT/AMB | تعریف شیفت | Pilot Manager |
| POST /interactions/log | AMB/EVENT | ثبت تعامل میدانی | Ambassador |
| POST /assets | ASSET | ثبت دارایی میدانی | Asset Manager |
| POST /assets/{id}/assign | ASSET | تحویل دارایی به شیفت/سفیر | Asset Manager |
| POST /assets/{id}/incidents | ASSET/SUPPORT | ثبت خرابی یا رخداد دارایی | Ambassador |
| POST /media-points | MEDIA | تعریف Media Point | Admin |
| POST /content | MEDIA/CAMPAIGN | ثبت محتوای کمپین | Content Manager |
| POST /content/{id}/approval | MEDIA | تأیید محتوا | Venue/Brand Approver |
| POST /campaigns | CAMPAIGN | تعریف کمپین | Campaign Manager |
| GET /scenario-templates | CAMPAIGN | کتابخانه سناریو | Campaign Manager |
| POST /quests | QUEST | تعریف مأموریت | Campaign Manager |
| POST /quests/{id}/start | QUEST | شروع مأموریت | User |
| POST /quests/{id}/complete | QUEST | ثبت تکمیل مأموریت | User/System |
| POST /qr | QR | ایجاد QR | Admin |
| POST /qr/{code}/scan | QR/EVENT | ثبت و اعتبارسنجی اسکن | User |
| POST /qr/offline-sync | QR/OFFLINE | Sync اسکن آفلاین | System |
| POST /rewards | REWARD | تعریف پاداش | Admin |
| POST /coupons/{code}/redeem | REWARD/MERCH | مصرف کوپن | Merchant Staff |
| POST /rewards/dispute | SUPPORT/REWARD | گزارش مشکل پاداش | User/SUPPORT |
| POST /merchants | MERCH | ثبت Merchant | Admin |
| PATCH /merchants/{id}/package | MERCH/COMM | انتخاب پکیج تجاری | Business Admin |
| GET /merchant/dashboard | MERCH/ANALYTICS | داشبورد Merchant | Merchant |
| POST /commercial-agreements | COMM | ثبت توافق تجاری | Business Admin |
| POST /settlements | COMM | ثبت تسویه | Finance |
| POST /events | EVENT | ثبت Event عمومی | System |
| GET /analytics/funnel | ANALYTICS | قیف تبدیل | Admin |
| GET /analytics/attribution | ANALYTICS | گزارش Attribution | Admin |
| GET /analytics/board-report | ANALYTICS | گزارش Decision Gate | Steering Committee |
| POST /support/tickets | SUPPORT | ثبت تیکت | User/Ambassador |
| PATCH /support/tickets/{id} | SUPPORT | به‌روزرسانی وضعیت تیکت | Support Agent |
| POST /incidents | SUPPORT/PILOT | ثبت رخداد میدانی | Pilot Team |

## 6. Conceptual Data Model

| Entity/Table | شرح | فیلدهای کلیدی | Module |
| --- | --- | --- | --- |
| venues | Venueها | id, name, status, owner, city, launch_status | VENUE |
| zones | زون‌ها | id, venue_id, name, status, capacity_note | VENUE |
| hubs | هاب‌ها | id, zone_id, type, name, kpi_focus | VENUE |
| touchpoints | نقاط تماس | id, hub_id, type, location_note, qr_id, status | VENUE/QR |
| media_points | رسانه‌ها | id, hub_id, asset_id, type, content_schedule_id, status | MEDIA |
| pilots | پایلوت‌ها | id, venue_id, starts_at, ends_at, status, decision_gate_id | PILOT |
| pilot_days | روز پایلوت | id, pilot_id, date, status, summary | PILOT |
| shifts | شیفت‌ها | id, pilot_day_id, zone_id, starts_at, ends_at, supervisor_id | PILOT/AMB |
| daily_reports | گزارش روزانه | id, pilot_day_id, kpi_json, incidents_count, approved_by | PILOT/ANALYTICS |
| baseline_metrics | خط مبنا | id, venue_id, metric_name, value, period, source | PILOT/ANALYTICS |
| ambassadors | سفیران | id, name, status, training_status, contact_note | AMB |
| ambassador_shifts | شیفت سفیر | id, ambassador_id, shift_id, touchpoint_id, status | AMB |
| interaction_logs | تعامل میدانی | id, ambassador_id, shift_id, count, notes, occurred_at | AMB/EVENT |
| assets | دارایی‌ها | id, type, serial, owner, status, storage_location | ASSET |
| asset_assignments | تحویل دارایی | id, asset_id, shift_id, assigned_to, returned_at, condition_note | ASSET |
| maintenance_logs | نگهداری/خرابی | id, asset_id, issue_type, status, resolution | ASSET/SUPPORT |
| campaigns | کمپین‌ها | id, venue_id, scenario_type, title, starts_at, ends_at, status | CAMPAIGN |
| scenario_templates | سناریوها | id, type, title, default_rules, suitable_venues | CAMPAIGN |
| content_items | محتوا | id, campaign_id, type, status, approval_status, brand_safety_tag | MEDIA/CAMPAIGN |
| content_schedules | برنامه محتوا | id, content_id, media_point_id, start_time, end_time | MEDIA |
| qr_codes | QRها | id, code, target_type, target_id, venue_id, touchpoint_id, status | QR |
| qr_scans | اسکن‌ها | id, qr_id, user_id/session_id, result, risk_flag, scanned_at | QR/EVENT |
| offline_actions | اقدام آفلاین | id, action_type, payload_hash, sync_status, quality_flag | OFFLINE/EVENT |
| quests | مأموریت‌ها | id, campaign_id, scenario_type, title, status, reward_rule_id | QUEST |
| quest_steps | مراحل مأموریت | id, quest_id, type, rule_json, order | QUEST |
| user_quest_progress | پیشرفت کاربر | id, user_id, quest_id, status, completed_at | QUEST |
| rewards | پاداش‌ها | id, title, type, sponsor/merchant_id, cost_owner, status | REWARD |
| coupons | کوپن‌ها | id, code, reward_id, user_id, status, expires_at | REWARD |
| redemptions | مصرف پاداش | id, coupon_id, merchant_id, verified_by, redeemed_at, fraud_flag | REWARD/MERCH |
| point_ledger | دفتر امتیاز | id, user_id, points, direction, reason, source_event_id | REWARD |
| merchants | کسب‌وکارها | id, venue_id, hub_id, name, category, package_id, status | MERCH |
| merchant_packages | پکیج کسب‌وکار | id, name, price_note, benefits, kpi_commitment | MERCH/COMM |
| commercial_agreements | توافق تجاری | id, pilot_id, party_type, revenue_stream, share_rule, status | COMM |
| settlements | تسویه | id, agreement_id, amount, period, status | COMM |
| events | رویدادها | id, event_type, actor_id, venue_id, campaign_id, target_id, occurred_at, quality_flag | EVENT |
| kpi_values | مقادیر KPI | id, pilot_id, metric_name, value, target, date, source | ANALYTICS |
| attribution_reports | گزارش انتساب | id, pilot_id, source_type, source_id, conversions, value_estimate | ANALYTICS |
| decision_gates | دروازه تصمیم | id, pilot_id, achievement_pct, decision, approved_by | ANALYTICS |
| support_tickets | تیکت‌ها | id, user_id, type, status, related_entity, resolution | SUPPORT |
| incidents | رخدادها | id, pilot_id, type, severity, status, escalation_owner | SUPPORT/PILOT |
| consent_logs | رضایت‌ها | id, user_id, consent_version, accepted_at, source | DATA |
| data_access_logs | لاگ دسترسی داده | id, actor_id, data_scope, action, occurred_at | DATA |
| audit_logs | لاگ عملیات حساس | id, actor_id, action, entity_type, entity_id, created_at | ADMIN/DATA |

## 7. Event Dictionary

| Event | تعریف | Properties الزامی | Source |
| --- | --- | --- | --- |
| qr_scanned | اسکن QR توسط کاربر | venue_id, zone_id, hub_id, touchpoint_id, qr_id, campaign_id, media_point_id, user/session, result | QR/Analytics |
| user_registered | ثبت‌نام یا ورود موفق | source_qr, consent_status, venue_id, referral_source | Auth |
| mission_started | شروع مأموریت | quest_id, campaign_id, scenario_type, venue_id, hub_id | Quest |
| mission_completed | تکمیل مأموریت | quest_id, validation_type, time_to_complete, reward_rule_id | Quest/Reward |
| reward_issued | صدور پاداش | reward_id, user_id, merchant_id, cost_owner, expiry_time | Reward |
| reward_redeemed | مصرف پاداش | coupon_id, merchant_id, verified_by, fraud_flag, amount/value_note | Reward/Merchant |
| merchant_visited | مراجعه قابل انتساب به Merchant | merchant_id, campaign_id, attribution_source, qr_id | Merchant/Analytics |
| media_interaction_logged | اثر رسانه ثابت/متحرک | media_point_id, content_id, qr_id, scan_count/window | Media/Analytics |
| ambassador_interaction_logged | تعامل سفیر با بازدیدکننده | ambassador_id, shift_id, zone_id, interaction_count, issue_count | Ambassador |
| feedback_submitted | بازخورد یا رضایت کاربر | rating, issue_type, venue_id, campaign_id | Support/Analytics |
| incident_logged | رخداد میدانی | pilot_id, severity, category, asset_id/touchpoint_id, status | Support/Pilot |
| offline_sync_completed | همگام‌سازی داده آفلاین | offline_action_id, action_type, quality_flag, synced_at | Offline/Event |

## 8. KPI و Decision Gate

| KPI | تعریف | Events/Inputs | Cadence |
| --- | --- | --- | --- |
| Scan Volume | تعداد اسکن معتبر و نامعتبر به تفکیک QR/Touchpoint/Media | qr_scanned | روزانه |
| Registration Rate | ثبت‌نام موفق / اسکن ورودی | qr_scanned + user_registered | روزانه |
| Activation Rate | شروع اولین مأموریت / ثبت‌نام | mission_started | روزانه/کمپین |
| Mission Completion Rate | تکمیل مأموریت / شروع مأموریت | mission_started + mission_completed | روزانه/سناریو |
| Reward Issuance Rate | پاداش صادرشده / تکمیل مأموریت | reward_issued | روزانه |
| Redemption Rate | پاداش مصرف‌شده / پاداش صادرشده | reward_redeemed | کمپین/Merchant |
| Merchant Conversion | مراجعه یا مصرف قابل انتساب به Merchant / تعامل هدایت‌شده | merchant_visited + reward_redeemed | Merchant |
| Media Conversion | اسکن ناشی از Media Point / بازه پخش محتوا | media_interaction_logged + qr_scanned | رسانه |
| Cost per Scan/User/Mission | هزینه عملیات و رسانه / Scan یا User یا Mission | Cost inputs + Events | پایان پایلوت |
| Value per Merchant Lead | ارزش تخمینی مراجعه قابل انتساب به کسب‌وکار | merchant_visited + commercial value | پایان پایلوت |
| Support Issue Rate | تیکت و رخداد / کاربران فعال یا اسکن | support_tickets + incidents | روزانه |
| Decision Gate Achievement | درصد تحقق KPIهای مصوب | kpi_values vs target | پایان پایلوت |

## 9. Permission Matrix

| Role | دسترسی مجاز | محدودیت مهم |
| --- | --- | --- |
| Super Admin | همه Venueها، تنظیمات سیستم، کاربران ادمین، Audit | کاهش دامنه CPL بدون Change Request |
| Pilot Manager | Pilot، Daily Report، Shift، Incident، Decision Gate draft | داده شخصی غیرضروری کاربران |
| Venue Admin | Venue Profile، Approval، گزارش Venue | داده کسب‌وکارهای خارج از Venue |
| Campaign Manager | Campaign، Scenario، Quest، Content draft | انتشار محتوای تأییدنشده |
| Content Approver | تأیید/رد محتوا و Brand Safety | تغییر مالی و پاداش |
| Merchant Admin | پروفایل Merchant، پاداش‌های خودش، Dashboard خودش | داده سایر Merchantها |
| Merchant Staff | مصرف کوپن و مشاهده راهنمای پذیرش | ویرایش کمپین یا قیمت |
| Ambassador | FAQ، اسکریپت، ثبت Interaction/Issue ساده | داده شخصی یا گزارش‌های مالی |
| Support Agent | Ticket، Issue، Resolution، Escalation | گزارش‌های مالی کامل |
| Analyst | گزارش‌های تجمیعی و KPI | داده خام شخصی بدون مجوز |

## 10. Non-Functional Requirements

| Area | Priority | Requirement |
| --- | --- | --- |
| Performance | Must | اسکن QR و نمایش صفحه مقصد باید در شرایط عادی سریع باشد؛ تجربه پایلوت نباید حس کندی ایجاد کند. |
| Availability | Must | در زمان اجرای پایلوت، ماژول‌های QR، Quest، Reward و Dashboard باید پایدار و دارای پیام خطای قابل فهم باشند. |
| Security | Must | OTP، Session، Role-Based Access، Audit Log، محدودیت تلاش، و کنترل تقلب پایه الزامی است. |
| Privacy | Must | حداقل‌گرایی داده، Consent، عدم فروش داده شخصی، تفکیک دسترسی و گزارش تجمیعی رعایت شود. |
| Offline Resilience | Must | سناریوی اینترنت ضعیف، QR خراب، ثبت دستی و Sync بعدی در سطح MVCP پوشش داده شود. |
| Scalability | Should | طراحی داده و API باید از یک Venue به چند Venue بدون بازنویسی بنیادین توسعه یابد. |
| Maintainability | Should | هر Requirement شناسه، Priority، Acceptance و Trace به CPL داشته باشد. |
| Observability | Should | خطاها، Eventها، Incidentها و کیفیت داده باید قابل مشاهده و قابل پیگیری باشند. |
| Accessibility | Should | متن‌ها کوتاه، مسیرها ساده، PWA بدون نصب اجباری و سناریوهای کاربران خاص قابل پشتیبانی باشد. |
| Auditability | Must | تغییرات مالی، پاداش، محتوا، کمپین، Merchant و دسترسی داده باید قابل Audit باشد. |

## 11. Readiness Gates پیش از Product Backlog

| Gate | شرط عبور |
| --- | --- |
| Traceability Gate | همه Requirementها به BRD v1.1 و حداقل یک CPL وصل شده باشند. |
| Event Gate | Event Dictionary و KPI Mapping قفل شده باشد. |
| Data Gate | Entityهای Venue، Pilot، QR، Quest، Reward، Merchant، Event و Support نهایی شده باشند. |
| Operations Gate | Runbook، سفیر، تجهیزات، Offline و Support به Storyهای قابل اجرا تبدیل شده باشند. |
| Commercial Gate | Merchant Package، Revenue Stream و Attribution در Backlog دیده شوند. |
