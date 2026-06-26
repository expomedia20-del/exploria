# EXPLORIA - Product Backlog v1.0

**وضعیت:** Official Working Backlog - ساخته‌شده از FRD v1.1 Pilot Operations Update

| فیلد | مقدار |
| --- | --- |
| نام سند | Exploria_Product_Backlog_v1.0 |
| مبنا | BRD v1.1 + FRD v1.1 + CPL-01..CPL-26 |
| تعداد User Story | 108 |
| Must | 67 |
| Should | 37 |
| Could | 4 |
| تخمین کل | 602 Story Points |

## 1. تصمیم نسخه
این Backlog از FRD v1.1 تولید شده است و FRD v1.0 برای برنامه‌ریزی Sprint مبنا نیست. هدف آن تبدیل الزامات MVP نرم‌افزاری و MVCP تجاری به Epic، User Story، Priority، Sprint، Dependency و Test Case است.

## 2. Epic Summary
| Epic | Module | Story Count | Must | Should | Could | Sprint |
| --- | --- | ---: | ---: | ---: | ---: | --- |
| EPIC-00 - Release Boundaries & Traceability | REL | 4 | 3 | 1 | 0 | Sprint 0 |
| EPIC-01 - Authentication, Consent & PWA Access | AUTH | 6 | 4 | 1 | 1 | Sprint 1 |
| EPIC-02 - Venue, Zone, Hub & Touchpoints | VENUE | 6 | 4 | 2 | 0 | Sprint 1 |
| EPIC-03 - Pilot Operations & Daily Runbook | PILOT | 6 | 5 | 1 | 0 | Sprint 3 |
| EPIC-04 - Field Ambassadors & Scripts | AMB | 5 | 2 | 2 | 1 | Sprint 3 |
| EPIC-05 - Field Asset & Hardware Control | ASSET | 5 | 3 | 2 | 0 | Sprint 3 |
| EPIC-06 - Interactive Media Network | MEDIA | 5 | 3 | 2 | 0 | Sprint 3 |
| EPIC-07 - Campaign Scenario & Content OS | CAMPAIGN | 5 | 3 | 2 | 0 | Sprint 2 |
| EPIC-08 - QR & Scan Validation Engine | QR | 6 | 4 | 2 | 0 | Sprint 1 |
| EPIC-09 - Quest & Mission Execution | QUEST | 6 | 4 | 2 | 0 | Sprint 2 |
| EPIC-10 - Reward, Coupon & Fraud Control | REWARD | 6 | 4 | 2 | 0 | Sprint 2 |
| EPIC-11 - Merchant Onboarding & Dashboard | MERCH | 6 | 4 | 2 | 0 | Sprint 4 |
| EPIC-12 - Commercial Agreements & Settlement | COMM | 5 | 2 | 2 | 1 | Sprint 4 |
| EPIC-13 - Event Tracking & Data Dictionary | EVENT | 5 | 3 | 2 | 0 | Sprint 2 |
| EPIC-14 - Analytics, Attribution & Decision Gate | ANALYTICS | 6 | 4 | 2 | 0 | Sprint 5 |
| EPIC-15 - Admin Governance, RACI & Permissions | ADMIN | 5 | 3 | 2 | 0 | Sprint 4 |
| EPIC-16 - Support, Complaints & Incidents | SUPPORT | 5 | 3 | 2 | 0 | Sprint 5 |
| EPIC-17 - Offline & Manual Fallback | OFFLINE | 5 | 2 | 2 | 1 | Sprint 5 |
| EPIC-18 - Data Ownership, Privacy & Security | DATA | 6 | 4 | 2 | 0 | Sprint 4 |
| EPIC-19 - Pilot Exit, Transition & Launch Kit | EXIT | 5 | 3 | 2 | 0 | Sprint 5 |

## 3. Sprint Plan
| Sprint | Theme | Modules | Exit Criteria |
| --- | --- | --- | --- |
| Sprint 0 | Backlog & Scope Foundation | REL | همه Storyها trace، scope و اولویت داشته باشند. |
| Sprint 1 | User Entry + Venue + QR Core | AUTH, VENUE, QR | کاربر بتواند QR را اسکن کند، وارد شود و اسکن قابل انتساب ثبت شود. |
| Sprint 2 | Event + Campaign + Quest + Reward Core | EVENT, CAMPAIGN, QUEST, REWARD | قیف scan to reward در حالت پایه کار کند. |
| Sprint 3 | MVCP Field Operations | PILOT, AMB, ASSET, MEDIA | پایلوت واقعی بتواند روزانه مدیریت و گزارش شود. |
| Sprint 4 | Commercial, Merchant, Admin, Data Governance | MERCH, COMM, ADMIN, DATA | کسب‌وکار، قرارداد، دسترسی و داده با کنترل پایه آماده باشد. |
| Sprint 5 | Analytics, Support, Offline, Exit | ANALYTICS, SUPPORT, OFFLINE, EXIT | گزارش هیئت‌مدیره و دروازه تصمیم پایلوت آماده باشد. |
| Sprint 6 | Pilot Hardening & UAT | All | آمادگی اجرای MVCP در اکوپارک/ارم/برج میلاد تأیید شود. |

## 4. Backlog Usage Rule
هر Story که وضعیت آن از Not Started خارج می‌شود باید Requirement ID، CPL Trace، معیار پذیرش، Test Case و Definition of Done داشته باشد. هیچ Story بدون Trace وارد Sprint نشود.

## 5. Next Output
خروجی بعدی پس از تأیید این Backlog، Sprint 0/1 Plan، دیتابیس اولیه، API Contract و UI Flow خواهد بود.