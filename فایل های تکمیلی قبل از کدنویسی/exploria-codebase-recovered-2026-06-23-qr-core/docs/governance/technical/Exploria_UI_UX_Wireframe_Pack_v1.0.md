# Exploria_UI_UX_Wireframe_Pack_v1.0
## 1. هدف سند
این سند بسته وایرفریم و طراحی تجربه کاربری Sprint 1 اکوسیستم EXPLORIA است. تمرکز آن بر مسیر QR Scan → PWA → OTP → Consent → Attributed Scan Event و صفحات حداقلی ادمین، سفیر و مرچنت است. سند بر اساس BRD v1.1، FRD v1.1، Sprint 0/1 Execution Plan و Technical Design Pack v1.0 تهیه شده است.
## 2. اصول UX برای پایلوت
- PWA بدون نصب اجباری؛ ورود سریع و کم‌اصطکاک.
- موبایل‌اول، فارسی/RTL و قابل استفاده در فضای شلوغ.
- هر تعامل باید قابل Attribution باشد.
- Consent و Privacy قبل از رویدادهای شخصی.
- مسیر جایگزین برای Offline، خطای QR و کمک سفیر.
## 3. Screen Inventory
| ID | Surface | Screen | Purpose | Priority |
|---|---|---|---|---|
| UX-VIS-001 | Visitor PWA | QR Landing | باز شدن صفحه پس از اسکن QR، نمایش نام مکان/کمپین، CTA ثبت‌نام سریع | Sprint 1 / Must |
| UX-VIS-002 | Visitor PWA | OTP Login | ورود شماره موبایل، دریافت/تأیید OTP، مدیریت خطا و ارسال مجدد | Sprint 1 / Must |
| UX-VIS-003 | Visitor PWA | Consent & Rules | نمایش رضایت‌نامه، حریم خصوصی، قوانین پایلوت و تأیید کاربر | Sprint 1 / Must |
| UX-VIS-004 | Visitor PWA | Scan Success | ثبت موفق اسکن و نمایش Attribution به Venue/Zone/Hub/Touchpoint | Sprint 1 / Must |
| UX-VIS-005 | Visitor PWA | Campaign Home | نمایش مأموریت فعال، وضعیت امتیاز، CTA ادامه مسیر | Sprint 2 / Should |
| UX-VIS-006 | Visitor PWA | Mission Detail | شرح مأموریت، شرط تکمیل، QR بعدی یا مسیر پیشنهادی | Sprint 2 / Should |
| UX-VIS-007 | Visitor PWA | Reward Wallet | پاداش‌ها، وضعیت فعال/مصرف‌شده، تاریخ انقضا و QR مصرف | Sprint 3 / Should |
| UX-VIS-008 | Visitor PWA | Feedback / Support | فرم مشکل، رضایت، سوالات پرتکرار و پیگیری | Sprint 3 / Should |
| UX-ADM-001 | Admin | Admin Login | ورود ادمین با نقش و سطح دسترسی | Sprint 1 / Must |
| UX-ADM-002 | Admin | Venue Builder | تعریف Venue، Zone، Hub، Touchpoint و QRها | Sprint 1 / Must |
| UX-ADM-003 | Admin | QR Registry | لیست QRها، وضعیت فعال/غیرفعال، مقصد، کد و چاپ | Sprint 1 / Must |
| UX-ADM-004 | Admin | Event Monitor | نمایش رویدادهای اسکن، ثبت‌نام، consent و خطاها | Sprint 1 / Must |
| UX-ADM-005 | Admin | Pilot Dashboard | KPI روزانه پایلوت، اسکن، ثبت‌نام، نرخ تبدیل و Attribution | Sprint 2 / Should |
| UX-AMB-001 | Ambassador | Ambassador Fallback | ثبت کمک میدانی، اسکن دستی، خطاهای QR و تماس با پشتیبانی | Sprint 1 / Should |
| UX-MER-001 | Merchant | Merchant Mini Dashboard | مشاهده اسکن‌های هدایت‌شده و مصرف پاداش برای کسب‌وکار | Sprint 3 / Could |

## 4. Low-Fidelity Wireframes

### Visitor QR Landing
```
+-------------------------------------------+
| QR Landing / PWA                          |
+-------------------------------------------+
| [Logo EXPLORIA]                           |
| Venue: EcoPark / Eram / Milad             |
| Campaign: Active Pilot                    |
| Welcome message                           |
| Button: Start / ورود سریع                 |
| Link: Privacy & Rules                     |
| Offline notice area                       |
+-------------------------------------------+
```

### OTP Login
```
+-------------------------------------------+
| OTP Login                                 |
+-------------------------------------------+
| Mobile Number Input                       |
| Button: Send OTP                          |
| OTP Code Boxes                            |
| Resend timer                              |
| Error / Success message                   |
| Back to QR Landing                        |
+-------------------------------------------+
```

### Consent Screen
```
+-------------------------------------------+
| Consent & Rules                           |
+-------------------------------------------+
| Pilot Rules Summary                       |
| Data & Privacy Notice                     |
| Checkbox: I Agree                         |
| Button: Continue                          |
| Link: Full Terms                          |
| Support link                              |
+-------------------------------------------+
```

### Scan Success
```
+-------------------------------------------+
| Scan Success                              |
+-------------------------------------------+
| Success Icon                              |
| QR attributed to:                         |
| Venue / Zone / Hub                        |
| Touchpoint name                           |
| Button: Continue Mission                  |
| Button: Save / Share                      |
+-------------------------------------------+
```

### Admin Venue Builder
```
+-------------------------------------------+
| Admin Venue Builder                       |
+-------------------------------------------+
| Sidebar: Venues / QR / Events             |
| Venue dropdown                            |
| Add Zone                                  |
| Add Hub                                   |
| Add Touchpoint                            |
| Assign QR                                 |
| Publish / Disable                         |
+-------------------------------------------+
```

### Admin Event Monitor
```
+-------------------------------------------+
| Event Monitor                             |
+-------------------------------------------+
| Filters: Date / Venue / QR                |
| Cards: Scans / OTP / Consent              |
| Event table                               |
| Export CSV                                |
| Suspicious activity flag                  |
| Error logs                                |
+-------------------------------------------+
```

### Ambassador Fallback
```
+-------------------------------------------+
| Ambassador Fallback                       |
+-------------------------------------------+
| Ambassador login                          |
| Scan QR manually                          |
| User mobile optional                      |
| Issue type                                |
| Submit interaction log                    |
| Equipment checklist                       |
+-------------------------------------------+
```

### Merchant Mini Dashboard
```
+-------------------------------------------+
| Merchant Dashboard                        |
+-------------------------------------------+
| Merchant name                             |
| Campaign visits                           |
| Reward redeemed                           |
| Conversion estimate                       |
| Top QR source                             |
| Download report                           |
+-------------------------------------------+
```

## 5. Component Library
| ID | Component | Usage | Rule |
|---|---|---|---|
| CMP-001 | Primary CTA | دکمه اصلی اقدام مثل شروع، ادامه مأموریت، ارسال OTP | آبی اصلی، متن واضح، ارتفاع حداقل 44px |
| CMP-002 | QR Status Badge | نمایش وضعیت QR: فعال، منقضی، غیرفعال، خطادار | رنگ وضعیت + متن، قابل مشاهده در موبایل |
| CMP-003 | Consent Box | چک‌باکس تأیید قوانین و حریم خصوصی | بدون تأیید، دکمه ادامه غیرفعال باشد |
| CMP-004 | KPI Card | کارت داشبورد برای اسکن، ثبت‌نام، نرخ تبدیل | عدد بزرگ، عنوان کوتاه، زمان آخرین بروزرسانی |
| CMP-005 | Event Table | جدول رویدادها با فیلتر و خروجی | ستون‌های زمان، Event، QR، Venue، User/Anonymous |
| CMP-006 | Offline Banner | هشدار قطعی/ضعف اینترنت و مسیر جایگزین | نمایش در بالای صفحه، قابل بستن نباشد تا مشکل رفع شود |
| CMP-007 | Support Widget | ثبت مشکل یا سوال کاربر | انتخاب نوع مشکل، توضیح کوتاه، کد پیگیری |

## 6. UX Acceptance Gates
| ID | Gate | Priority |
|---|---|---|
| UX-GATE-001 | اسکن QR کاربر را بدون نصب اجباری اپ به PWA Landing برساند. | Must |
| UX-GATE-002 | کاربر بتواند با شماره موبایل و OTP وارد شود و خطاهای OTP قابل فهم باشد. | Must |
| UX-GATE-003 | قبل از ثبت رویدادهای شخصی، Consent روشن و قابل اثبات گرفته شود. | Must |
| UX-GATE-004 | هر اسکن موفق باید به Venue/Zone/Hub/Touchpoint/QR وصل شود. | Must |
| UX-GATE-005 | ادمین بتواند حداقل یک Venue، Zone، Hub، Touchpoint و QR فعال بسازد. | Must |
| UX-GATE-006 | Event Monitor بتواند اسکن، OTP و Consent را با فیلتر زمانی نشان دهد. | Must |
| UX-GATE-007 | در حالت اینترنت ضعیف، پیام خطا و مسیر کمک سفیر مشخص باشد. | Should |
| UX-GATE-008 | تمام صفحات Visitor موبایل‌اول، RTL و خوانا در نور محیطی باشند. | Must |
