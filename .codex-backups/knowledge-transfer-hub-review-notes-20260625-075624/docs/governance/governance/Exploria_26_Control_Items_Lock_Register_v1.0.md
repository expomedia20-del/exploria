# EXPLORIA - 26 Control Items Lock Register v1.0

**وضعیت:** LOCKED BASELINE / قفل محتوایی

این سند ۲۶ محور کنترلی Commercial Pilot Control Layer را به عنوان محدوده قفل‌شده برای BRD v1.1، FRD v1.1، MVP/MVCP، Product Backlog و Pilot Runbook ثبت می‌کند. حذف، ادغام یا کاهش هر محور فقط با Change Request رسمی مجاز است.

## قانون قفل
- هیچ‌یک از ۲۶ محور نباید از BRD v1.1، FRD v1.1 و اسناد اجرایی پایلوت حذف شود.
- هر محور باید حداقل یک خروجی قابل مشاهده در BRD، یک الزام یا Rule در FRD، و یک آیتم اجرایی در MVP/MVCP یا Runbook داشته باشد.
- هر تغییری باید با شناسه Change Request، دلیل، اثر بر زمان/هزینه/ریسک و تصمیم مالک پروژه ثبت شود.

## ماتریس قفل ۲۶ محور
| کد | محور قفل‌شده | اثر مخرب حذف | مقصد BRD | مقصد FRD | مقصد MVP/MVCP/Runbook |
|---|---|---|---|---|---|
| CPL-01 | مدل قراردادی و تقسیم درآمد | ابهام در سهم‌ها، مسیر وصول، تسویه و مالکیت ارزش اقتصادی باعث قفل شدن مذاکره و درآمدزایی می‌شود. | فصل مدل تجاری و قراردادها | Business Rules، Settlement، Revenue Attribution | MVP تجاری / قرارداد پایلوت |
| CPL-02 | حاکمیت و RACI عملیاتی | اگر مسئولیت‌ها روشن نباشد، مجوز، نصب، پشتیبانی، تصمیم توقف/توسعه و مدیریت بحران دچار تعارض می‌شود. | Governance & Partnership Model | Role Matrix، Permissions، Admin Roles | Runbook عملیاتی |
| CPL-03 | طراحی آزمایش پایلوت و خط مبنا | بدون خط مبنا، اثبات اینکه رشد تعامل/فروش ناشی از اکسپلوریا بوده قابل دفاع نیست. | Pilot Experimental Design | Baseline Metrics، Control/Comparison logic | Dashboard و گزارش پایان پایلوت |
| CPL-04 | Unit Economics | ممکن است پایلوت جذاب به نظر برسد ولی هزینه جذب، سفیر، محتوا و تجهیزات در مقیاس‌پذیری اقتصادی شکست بخورد. | Commercial Viability | Cost per scan/user/mission، ROI logic | KPI مالی پایلوت |
| CPL-05 | Venue / Zone / Hub / Touchpoint Model | بدون مدل مکان‌مند، QR، مأموریت، رسانه و پاداش به هاب‌ها و مسیر واقعی وصل نمی‌شوند. | Venue Operating Model | Venue/Zone/Hub/Touchpoint entities | پیکربندی محل پایلوت |
| CPL-06 | Venue Launch Kit | هر محل جدید از صفر طراحی می‌شود و توسعه از اکوپارک/ارم به برج میلاد و شهرهای بعدی کند می‌شود. | Scalability Model | Launch checklist، Templates | کیت راه‌اندازی هر Venue |
| CPL-07 | Pilot Operations Runbook | اجرای پایلوت سلیقه‌ای می‌شود و داده‌های روزانه قابل اتکا نخواهند بود. | Pilot Operations Chapter | Pilot entity، Daily reports، Shift controls | دفترچه اجرای ۱۴ روزه |
| CPL-08 | سفیران میدانی و اسکریپت تعامل | سفیران بدون اسکریپت واحد، تجربه کاربر، نرخ اسکن و کیفیت داده را ناپایدار می‌کنند. | Field Operations | Ambassador roles، Interaction logs | آموزش و چک‌لیست سفیران |
| CPL-09 | مدیریت سخت‌افزار و دارایی میدانی | کوله‌پشتی، نمایشگر، پاوربانک، اینترنت و QR در میدان مستهلک، گم یا خراب می‌شوند. | Asset Responsibility | Asset registry، Status، Assignment | تحویل/تحویل‌گیری روزانه |
| CPL-10 | Interactive Media Network | اگر رسانه هولوگرافیک و نمایشگر ثابت به سیستم داده وصل نشوند، فقط نمایش تبلیغاتی بدون سنجش می‌مانند. | Media Network Strategy | Media Point، Content Schedule، QR binding | شبکه رسانه‌ای پایلوت |
| CPL-11 | Campaign Scenario Library | Quest Engine بدون سناریوهای آماده، تجربه زنده و قابل فروش به هاب‌ها تولید نمی‌کند. | Campaign Strategy | Campaign templates، Mission types | سناریوهای اکوپارک/ارم/برج میلاد |
| CPL-12 | Campaign Content Operating System | پلتفرم ساخته می‌شود اما محتوای روزانه، مناسبتی و هولوگرافیک برای فعال‌سازی ندارد. | Content & Campaign Calendar | Content objects، Approval status | تقویم کمپین |
| CPL-13 | Brand Safety و تأیید محتوا | محتوای نامناسب، پیام اسپانسر، عکس افراد یا محتوای کودک می‌تواند اعتراض و ریسک حقوقی ایجاد کند. | Brand & Content Governance | Content approval workflow | فرآیند تأیید قبل از انتشار |
| CPL-14 | Merchant Onboarding | کسب‌وکارها بدون فرآیند ورود، پکیج، QR اختصاصی و تعهد پاداش وارد مدل درآمدی نمی‌شوند. | Merchant Business Model | Merchant profile، Package، Reward rules | فرآیند جذب کسب‌وکار |
| CPL-15 | Sales Playbook کسب‌وکار و اسپانسر | تیم فروش بدون متن، پکیج، پاسخ اعتراض و نمونه گزارش، جذب کسب‌وکار را کند و فرسایشی می‌کند. | B2B Sales Strategy | Offer templates، Sponsor packages | اسناد فروش و مذاکره |
| CPL-16 | Fraud / Abuse / Redemption Control | اسکن تکراری، حساب‌های متعدد، مصرف چندباره کوپن و تبانی فروشگاه می‌تواند هزینه و بی‌اعتمادی ایجاد کند. | Risk & Control | Anti-fraud rules، Redemption constraints | کنترل مصرف پاداش |
| CPL-17 | حقوقی، مجوز، بیمه و مسئولیت | اجرای عمومی با کودک، خانواده، سفیر، رسانه محیطی و داده کاربر بدون پوشش حقوقی ریسک توقف دارد. | Legal & Compliance | Terms، Consent، Incident responsibility | مجوزها و بیمه |
| CPL-18 | Data Ownership و Data Governance | ابهام مالکیت داده، دسترسی میزبان/کسب‌وکار/اسپانسر و حق حذف داده می‌تواند مانع قرارداد شود. | Data Governance | Data access levels، retention، deletion | سیاست داده |
| CPL-19 | Event Tracking و Data Dictionary | داشبورد بدون تعریف رویدادها، آمارهای زیبا اما غیرقابل اعتماد و غیرقابل تحلیل تولید می‌کند. | Measurement Model | Event schema، Data dictionary | پایه Analytics |
| CPL-20 | Attribution و اثبات درآمدزایی | بدون اتصال QR/رسانه/مأموریت/پاداش/مراجعه، ارزش مالی پروژه قابل دفاع نیست. | Revenue Proof Model | Attribution pipeline، conversion funnel | گزارش ROI |
| CPL-21 | Offline / Low Connectivity Plan | ضعف اینترنت، باز نشدن QR یا قطعی داشبورد تجربه کاربر و ثبت داده را مختل می‌کند. | Operational Risk | Offline capture، manual fallback، sync | سناریوی اضطراری میدان |
| CPL-22 | Support و Complaint Handling | مشکلات جایزه، QR، امتیاز، فروشگاه و حریم خصوصی اگر مدیریت نشود، اعتماد پایلوت را تخریب می‌کند. | Customer Support Model | Ticket/issue log، FAQ، Admin handling | فرم گزارش مشکل |
| CPL-23 | تجربه کاربران خاص و دسترس‌پذیری | خانواده‌ها، کودکان، سالمندان، کم‌سواد دیجیتال، گردشگران و افراد دارای معلولیت ممکن است از تجربه حذف شوند. | Inclusive Experience | Low-friction flows، accessibility rules | PWA/بدون نصب اجباری |
| CPL-24 | Pilot Exit / Transition Plan | پایان پایلوت، QRها، پاداش‌های مصرف‌نشده، گزارش کسب‌وکار و ادامه/توقف باید مدیریت شود. | Pilot Lifecycle | Closure workflow، migration/extension logic | گزارش و جمع‌آوری پایلوت |
| CPL-25 | Pilot Decision Gate | بدون معیار رسمی، تصمیم هیئت‌مدیره درباره توسعه، تکرار یا توقف سلیقه‌ای می‌شود. | Board Decision Matrix | Success thresholds، KPI gate | گزارش نهایی تصمیم |
| CPL-26 | مرزبندی Demo / MVP / Pilot / Product | مخلوط شدن نسخه نمایشی، MVP نرم‌افزاری، پایلوت تجاری و محصول نهایی باعث انتظار غلط و scope creep می‌شود. | Scope Definition | Feature status، release boundaries | نقشه نسخه‌ها |

## Gates
1. قبل از نهایی شدن BRD v1.1: همه کدهای CPL-01 تا CPL-26 باید در BRD Trace شوند.
2. قبل از نهایی شدن FRD v1.1: همه کدهای CPL-01 تا CPL-26 باید به Requirement، Business Rule، Data Entity، KPI یا Operational Rule تبدیل شوند.
3. قبل از Product Backlog: هیچ User Story نباید بدون Trace به BRD/FRD/CPL ایجاد شود.
4. قبل از اجرای پایلوت: Pilot Runbook باید تمام کدهای میدانی و کنترلی را پوشش دهد.