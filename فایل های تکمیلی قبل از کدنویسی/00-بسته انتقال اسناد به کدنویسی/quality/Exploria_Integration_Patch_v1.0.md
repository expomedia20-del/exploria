# Exploria Integration Patch v1.0

**Project:** EXPLORIA ECOSYSTEM  
**Patch Target:** Local Integration Pack v1.0 / OpenAPI Sprint 1 Contract  
**Status:** Static Patch Applied - Runtime Smoke Test Pending  
**Date:** 2026-06-19

## 1. Purpose

این پچ برای رفع دو ایراد ثبت‌شده در `Exploria_Integration_QA_Report_v1.0` ساخته شد:

1. **DEF-001:** خطای Parse در OpenAPI YAML به‌دلیل کوتیشن‌گذاری نشدن `info.description`.
2. **DEF-002:** نبود مسیر `/admin/dashboard/summary` در OpenAPI، درحالی‌که این مسیر در Backend پیاده‌سازی شده و در Smoke Test استفاده می‌شود.

## 2. Additional Alignment

برای کاهش ریسک عدم تطابق قرارداد API با Backend، این پچ همچنین `adminApiKey` را به OpenAPI اضافه کرد؛ چون Backend برای مسیرهای Admin از هدر `x-admin-api-key` استفاده می‌کند.

## 3. Files Updated

- `contracts/Exploria_OpenAPI_Sprint1_v1.0.yaml`
- `apps/backend/openapi/Exploria_OpenAPI_Sprint1_v1.0.yaml`
- `apps/frontend/docs/Exploria_OpenAPI_Sprint1_v1.0.yaml`
- `README.md` در پکیج کامل Patch شده

## 4. Static Validation Result

| File | YAML Parse | Dashboard Path | Admin Security | Dashboard Schema |
|---|---|---|---|---|
| `contracts/Exploria_OpenAPI_Sprint1_v1.0.yaml` | PASS | PASS | PASS | PASS |
| `apps/backend/openapi/Exploria_OpenAPI_Sprint1_v1.0.yaml` | PASS | PASS | PASS | PASS |
| `apps/frontend/docs/Exploria_OpenAPI_Sprint1_v1.0.yaml` | PASS | PASS | PASS | PASS |

## 5. New / Corrected Contract Items

### 5.1 Fixed `info.description`

OpenAPI اکنون بدون خطای YAML parse خوانده می‌شود.

### 5.2 Added `/admin/dashboard/summary`

این مسیر به OpenAPI اضافه شد تا با Backend و Smoke Test هم‌راستا شود.

**Response Schema:** `DashboardSummary`

Fields:

- `totalUsers`
- `totalScans`
- `acceptedScans`
- `openIssues`

### 5.3 Added `adminApiKey`

Security scheme جدید:

```yaml
adminApiKey:
  type: apiKey
  in: header
  name: x-admin-api-key
```

این Security روی مسیرهای `/admin/*` اعمال شد.

## 6. Deliverables

- Patch ZIP: `Exploria_Integration_Patch_v1.0.zip`
- Full patched Local Integration Pack: `Exploria_Local_Integration_Pack_v1.0_PATCHED.zip`
- Patched OpenAPI standalone: `Exploria_OpenAPI_Sprint1_v1.0_PATCHED.yaml`
- Patch report: `Exploria_Integration_Patch_v1.0.docx/pdf/md`

## 7. Remaining Required Action

Docker Runtime E2E هنوز باید روی سیستم توسعه‌دهنده اجرا شود؛ چون در محیط فعلی Docker در دسترس نیست.

```bash
cp .env.example .env
docker compose up --build
make smoke
```

## 8. Gate Recommendation

پس از اعمال این Patch، وضعیت از **No-Go برای Contract** به **Ready for Local Runtime Smoke Test** تغییر می‌کند.  
بعد از اجرای موفق `make smoke` و ثبت خروجی، می‌توان Integration QA را به وضعیت Passed ارتقا داد.
