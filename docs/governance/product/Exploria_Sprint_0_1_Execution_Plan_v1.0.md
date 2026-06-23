# EXPLORIA - Sprint 0/1 Execution Plan v1.0

**وضعیت:** Working Execution Baseline - ساخته‌شده از Product Backlog v1.0 و FRD v1.1  
**تاریخ:** 2026-06-19  
**دامنه:** Sprint 0 و Sprint 1 برای شروع توسعه MVP/MVCP

## 1. تصمیم اجرایی
این برنامه، Product Backlog v1.0 را به برنامه اجرایی Sprint 0 و Sprint 1 تبدیل می‌کند. مبنای برنامه‌ریزی، **FRD v1.1 - Pilot Operations Update** است و نه FRD v1.0.

## 2. خلاصه ظرفیت
| Sprint | Theme | Story Count | Story Points | Recommendation |
| --- | --- | ---: | ---: | --- |
| Sprint 0 | Governance, Scope, Traceability and Tech Foundation | 4 | 12 | قابل اجرا در حدود ۵ روز کاری |
| Sprint 1 | User Entry + Venue + QR Core | 18 | 102 | برای یک تیم کوچک سنگین است؛ بهتر است به ۳ Track موازی یا 1A/1B/1C تقسیم شود |

## 3. Sprint 0 - هدف و خروجی
Sprint 0 برای کدنویسی محصول نهایی نیست؛ هدف آن آماده‌سازی محیط، قفل مبنا، Traceability، تصمیمات معماری و Gate شروع توسعه است.

### Sprint 0 Stories
| Story ID | Module | Req ID | Priority | Scope | Estimate | Summary |
| --- | --- | --- | --- | --- | ---: | --- |
| US-001 | REL | REL-001 | P1 | MVP+MVCP | 3 | به‌عنوان Product Owner / Business Analyst می‌خواهم سیستم مستندسازی باید هر Requirement را به BRD v1.1 و حداقل یک CPL متص |
| US-002 | REL | REL-002 | P1 | MVP+MVCP | 3 | به‌عنوان Product Owner / Business Analyst می‌خواهم هر قابلیت باید وضعیت Demo / MVP / MVCP / Product داشته باشد. تا اجرای |
| US-003 | REL | REL-003 | P1 | MVP+MVCP | 3 | به‌عنوان Product Owner / Business Analyst می‌خواهم تغییر دامنه باید به Change Request با دلیل، اثر و تصمیم مالک پروژه مت |
| US-004 | REL | REL-004 | P2 | MVP+MVCP | 3 | به‌عنوان Product Owner / Business Analyst می‌خواهم داشبورد مدیریتی داخلی بتواند وضعیت پوشش CPLها را نشان دهد. تا اجرای پ |

### Sprint 0 Task Breakdown
| Task ID | Stream | Task | Story Trace | Owner | Priority | Window | Output |
| --- | --- | --- | --- | --- | --- | --- | --- |
| S0-T01 | Governance | BRD v1.1, FRD v1.1, CPL Register and Backlog are declared as current baselines | US-001 | Product Owner / BA | P0 | Day 1 | Signed baseline note and document links |
| S0-T02 | Scope Control | Create requirement traceability rule and Change Request workflow | US-001, US-003 | BA + PM | P0 | Day 1 | Traceability/CR template |
| S0-T03 | Release Boundary | Classify every active item as Demo / MVP / MVCP / Product | US-002 | Product Owner | P0 | Day 1-2 | Scope classification sheet |
| S0-T04 | Architecture | Confirm working implementation assumptions: PWA/Web App, OTP, REST API, PostgreSQL, Admin Web | US-002 | Tech Lead | P0 | Day 2 | Architecture Decision Record ADR-001 |
| S0-T05 | Environment | Create repositories, branching, dev/staging environments, secrets policy and CI skeleton | US-002 | DevOps / Tech Lead | P0 | Day 2-3 | Repo + CI baseline |
| S0-T06 | Data Foundation | Create initial entity list for Release, RequirementTrace, User, Consent, Venue, QR, ScanEvent | US-001, US-002 | Backend Lead + DBA | P0 | Day 3 | Initial logical schema |
| S0-T07 | Quality Gate | Define Definition of Ready, Definition of Done and UAT gate for Sprint 1 | US-001..US-004 | QA Lead + PM | P1 | Day 4 | DoR/DoD checklist |
| S0-T08 | CPL Dashboard | Prepare lightweight traceability dashboard for CPL coverage and sprint readiness | US-004 | BA + Analyst | P2 | Day 5 | CPL coverage view |

## 4. Sprint 1 - ساخت هسته ورود، مکان و QR
Sprint 1 باید اولین حلقه قابل نمایش را بسازد: **QR Scan -> PWA -> OTP Login -> Consent -> Attributed Scan Event**.

### Sprint 1 Stories
| Story ID | Module | Req ID | Priority | Scope | Estimate | Summary |
| --- | --- | --- | --- | --- | ---: | --- |
| US-005 | AUTH | AUTH-001 | P0 | MVP | 5 | به‌عنوان بازدیدکننده می‌خواهم کاربر باید بتواند با موبایل و OTP وارد تجربه شود. تا اجرای پایلوت و سنجش آن مطابق FRD v1.1 |
| US-006 | AUTH | AUTH-002 | P0 | MVP | 5 | به‌عنوان بازدیدکننده می‌خواهم سیستم باید ثبت رضایت پایه را قبل از ورود به تجربه اصلی انجام دهد. تا اجرای پایلوت و سنجش آ |
| US-007 | AUTH | AUTH-003 | P0 | MVP | 5 | به‌عنوان بازدیدکننده می‌خواهم ورود باید برای تجربه PWA/Web App بدون نصب اجباری طراحی شود. تا اجرای پایلوت و سنجش آن مطاب |
| US-008 | AUTH | AUTH-004 | P0 | MVP | 5 | به‌عنوان بازدیدکننده می‌خواهم OTP باید محدودیت زمان، تلاش و ارسال مجدد داشته باشد. تا اجرای پایلوت و سنجش آن مطابق FRD v |
| US-009 | AUTH | AUTH-005 | P2 | MVP | 3 | به‌عنوان بازدیدکننده می‌خواهم در حالت اینترنت ضعیف، سفیر بتواند مسیر راهنمایی یا ثبت خطای ورود را فعال کند. تا اجرای پای |
| US-010 | AUTH | AUTH-006 | P3 | MVP | 2 | به‌عنوان بازدیدکننده می‌خواهم برای گردشگران خارجی، زیرساخت ورود چندزبانه در فاز بعد آماده باشد. تا اجرای پایلوت و سنجش آ |
| US-011 | VENUE | VENUE-001 | P0 | MVP+MVCP | 8 | به‌عنوان ادمین عملیات می‌خواهم ادمین باید بتواند Venue تعریف کند و آن را به پایلوت‌ها، کمپین‌ها و گزارش‌ها وصل کند. تا ا |
| US-012 | VENUE | VENUE-002 | P0 | MVP+MVCP | 5 | به‌عنوان ادمین عملیات می‌خواهم هر Venue باید بتواند Zone و Hubهای داخلی داشته باشد. تا اجرای پایلوت و سنجش آن مطابق FRD  |
| US-013 | VENUE | VENUE-003 | P0 | MVP+MVCP | 5 | به‌عنوان ادمین عملیات می‌خواهم هر Touchpoint باید نوع، محل، مالک، QR مرتبط و وضعیت عملیاتی داشته باشد. تا اجرای پایلوت و |
| US-014 | VENUE | VENUE-004 | P0 | MVP+MVCP | 5 | به‌عنوان ادمین عملیات می‌خواهم هر Media Point و Merchant Node باید به Venue/Zone/Hub متصل شود. تا اجرای پایلوت و سنجش آن |
| US-015 | VENUE | VENUE-005 | P2 | MVP+MVCP | 3 | به‌عنوان ادمین عملیات می‌خواهم سیستم باید Template برای Venue Launch Kit تولید یا نگهداری کند. تا اجرای پایلوت و سنجش آن |
| US-016 | VENUE | VENUE-006 | P2 | MVP+MVCP | 3 | به‌عنوان ادمین عملیات می‌خواهم هر Venue Profile باید محدودیت‌ها، مجوزها، مخاطبان، ساعت اوج و ظرفیت را نگهداری کند. تا اج |
| US-043 | QR | QR-001 | P0 | MVP | 8 | به‌عنوان بازدیدکننده / سیستم QR می‌خواهم هر QR باید شناسه یکتا، مقصد، Venue، Touchpoint، Campaign و وضعیت داشته باشد. تا |
| US-044 | QR | QR-002 | P0 | MVP | 8 | به‌عنوان بازدیدکننده / سیستم QR می‌خواهم هر اسکن باید با زمان، کاربر/جلسه، QR، نتیجه و Source ثبت شود. تا اجرای پایلوت و |
| US-045 | QR | QR-003 | P0 | MVP | 8 | به‌عنوان بازدیدکننده / سیستم QR می‌خواهم سیستم باید QR منقضی، غیرفعال، خارج از زمان یا خارج از Rule را رد کند. تا اجرای  |
| US-046 | QR | QR-004 | P0 | MVP | 8 | به‌عنوان بازدیدکننده / سیستم QR می‌خواهم کنترل اسکن تکراری و محدودیت تعداد اسکن برای کاربر/QR/بازه زمانی وجود داشته باشد |
| US-047 | QR | QR-005 | P1 | MVP | 8 | به‌عنوان بازدیدکننده / سیستم QR می‌خواهم در حالت اینترنت ضعیف، Offline Scan یا Manual Fallback با sync بعدی پشتیبانی شود |
| US-048 | QR | QR-006 | P1 | MVP | 8 | به‌عنوان بازدیدکننده / سیستم QR می‌خواهم ادمین بتواند QRهای چاپی را با برچسب محل نصب و تست روزانه خروجی بگیرد. تا اجرای  |

### Sprint 1 Tracks
| Track | Modules | Purpose | Recommended Owner | Points |
| --- | --- | --- | --- | ---: |
| Track A | AUTH | ورود کاربر، OTP، Consent و PWA | Frontend + Backend Auth | 25 |
| Track B | VENUE | Venue، Zone، Hub، Touchpoint و Nodeهای مکانی | Admin + Backend Data | 29 |
| Track C | QR | QR Registry، Scan Validation، Duplicate Control و Event Hook | Backend QR + Event | 48 |

### Sprint 1 Task Breakdown
| Task ID | Stream | Task | Story Trace | Owner | Priority | Window | Output |
| --- | --- | --- | --- | --- | --- | --- | --- |
| S1-T01 | Track A / AUTH | Design PWA landing flow after QR scan | US-007, US-043 | Frontend Lead | P0 | Day 1-2 | Clickable PWA entry flow |
| S1-T02 | Track A / AUTH | Implement mobile OTP request, verify and session creation | US-005, US-008 | Backend + Frontend | P0 | Day 2-6 | Working OTP flow |
| S1-T03 | Track A / AUTH | Implement consent capture and ConsentLog versioning | US-006 | Backend + Legal/BA | P0 | Day 3-6 | Consent screen + log record |
| S1-T04 | Track A / AUTH | Create low connectivity error route and ambassador guidance message | US-009 | Frontend + Support | P2 | Day 6-8 | Error/fallback screen |
| S1-T05 | Track A / AUTH | Prepare language and consent version fields for future multilingual support | US-010 | Backend | P3 | Day 8-9 | Schema support only |
| S1-T06 | Track B / VENUE | Implement Venue CRUD with EcoPark, Eram, Milad Tower seed records | US-011 | Backend + Admin UI | P0 | Day 1-4 | Venue admin page/API |
| S1-T07 | Track B / VENUE | Implement Zone and Hub configuration under Venue | US-012 | Backend + Admin UI | P0 | Day 3-6 | Zone/Hub forms |
| S1-T08 | Track B / VENUE | Implement Touchpoint registry with type, status, QR binding fields | US-013 | Backend + Admin UI | P0 | Day 5-8 | Touchpoint registry |
| S1-T09 | Track B / VENUE | Implement Media Point and Merchant Node as location-linked node types | US-014 | Backend + Admin UI | P0 | Day 7-9 | Node type configuration |
| S1-T10 | Track B / VENUE | Prepare Venue Launch Kit and Venue Profile minimal forms | US-015, US-016 | BA + Admin UI | P2 | Day 8-10 | Checklist/form templates |
| S1-T11 | Track C / QR | Implement QR entity with unique code, destination, status, Venue, Touchpoint, Campaign fields | US-043 | Backend | P0 | Day 1-4 | QR registry model/API |
| S1-T12 | Track C / QR | Implement QR generation/export for printed labels and install checklist | US-048 | Backend + Admin UI | P1 | Day 3-6 | Printable QR list |
| S1-T13 | Track C / QR | Implement scan endpoint and event capture for time, session/user, QR and result | US-044 | Backend + Event | P0 | Day 4-7 | Scan API + qr_scanned event |
| S1-T14 | Track C / QR | Implement scan validation for inactive/expired/out-of-window QR | US-045 | Backend | P0 | Day 6-8 | Validation rules |
| S1-T15 | Track C / QR | Implement duplicate scan and rate-limit controls with risk flag | US-046 | Backend + Security | P0 | Day 7-9 | Risk flag on suspicious scans |
| S1-T16 | Track C / QR | Implement offline/manual scan placeholder with sync tag design | US-047 | Backend + Frontend | P1 | Day 8-10 | Offline data contract/prototype |
| S1-T17 | QA/UAT | End-to-end scenario: QR scan -> PWA -> OTP -> Consent -> attributed scan event | US-005..US-008, US-043..US-046 | QA + Full Team | P0 | Day 9-10 | E2E test evidence |
| S1-T18 | Pilot Readiness | Prepare Sprint 1 demo pack for Owner: user flow, admin venue config, QR scan log | All Sprint 1 | PM + BA | P1 | Day 10 | Demo checklist |

## 5. Sprint 1 Exit Gate
Sprint 1 فقط زمانی کامل محسوب می‌شود که این سناریو بدون خطای بحرانی در Staging اجرا شود:

1. ادمین Venue و Touchpoint نمونه تعریف کند.
2. ادمین QR را به Venue/Touchpoint/Campaign وصل کند.
3. کاربر QR را اسکن کند.
4. کاربر بدون نصب اجباری وارد PWA شود.
5. کاربر با OTP وارد شود.
6. Consent ثبت شود.
7. Event `qr_scanned` با کاربر/جلسه، QR، Venue، Touchpoint، زمان و نتیجه ثبت شود.
8. اسکن نامعتبر یا تکراری کنترل شود.

## 6. ریسک‌های اصلی Sprint 0/1
| Risk ID | Risk | Probability | Impact | Mitigation | Owner |
| --- | --- | --- | --- | --- | --- |
| R-01 | Sprint 1 capacity overload | High | High | Run AUTH, VENUE and QR as parallel tracks or split into Sprint 1A/1B/1C. | PM + Tech Lead |
| R-02 | OTP provider delay | Medium | High | Mock OTP in dev/staging; select provider in Sprint 0. | Tech Lead |
| R-03 | Venue data ambiguity | Medium | High | Use seed templates and keep Milad Tower as Placeholder until Venue Profile is complete. | BA + Operations |
| R-04 | QR attribution gap | High | High | Block QR activation until required links are filled. | Backend Lead |
| R-05 | Connectivity weakness | Medium | High | Build low-connectivity guidance and offline/manual placeholder in Sprint 1. | Frontend + Ops |
| R-06 | Consent/legal copy not approved | Medium | Medium | Use short baseline consent in Sprint 1 and assign legal approval before pilot. | Owner + Legal |

## 7. RACI خلاصه
| Role | Scope/Trace | Venue Config | Auth/PWA | QR/Event | QA/UAT | Sprint Demo | Pilot Readiness |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Product Owner | A | A | A | C | A | A | A |
| Project Manager / Scrum Master | R | C | C | C | R | R | R |
| Business Analyst | R | R | C | C | C | R | R |
| Tech Lead | C | R | R | R | R | C | C |
| Backend Lead | I | C | R | R | R | C | C |
| Frontend Lead | I | C | R | C | R | C | C |
| QA Lead | C | C | C | C | R | R | C |
| Operations Lead | C | R | C | R | C | C | R |

## 8. خروجی بعدی
پس از تأیید این برنامه، خروجی بعدی باید **Technical Design Pack v1.0** باشد که شامل Database Schema، API Contract و UI Flow برای Sprint 1 است.
