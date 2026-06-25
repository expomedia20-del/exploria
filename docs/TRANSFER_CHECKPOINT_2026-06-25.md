# EXPLORIA Transfer Checkpoint - 2026-06-25

## Latest completed work

- Built the real scoped hub/ravaq manager dashboard at `/hub/dashboard`.
- Added dashboard API at `/api/v1/hub/dashboard`.
- Added `HubManagerAccessService` so hub managers can only review ads/offers inside their assigned hub scope.
- Added `HubManagerDashboardService` to aggregate managed hubs, partners, display devices, scoped ad requests, and scoped partner offers.
- Updated ad and reward approval controllers so admin/operator remain global reviewers, while hub managers are scoped reviewers.
- Added sidebar link for `پنل مدیر رواق`.
- Added feature tests for scoped dashboard visibility and forbidden foreign approvals.
- Local SQLite was backed up before migration; latest app schema was migrated and seeded for browser verification.

## Verification passed

- `php artisan test tests/Feature/Hub/HubManagerDashboardTest.php tests/Feature/Advertising/StandaloneAdvertisingTest.php tests/Feature/Partner/PartnerRewardRedemptionTest.php`: 21 tests, 164 assertions.
- `composer test`: 116 tests, 623 assertions, plus Pint and PHPStan.
- `npm run types:check`.
- `npm run lint:check`.
- `npm run format:check`.
- `npm run build`.
- Browser verification: `http://127.0.0.1:8000/hub/dashboard` opened as `ravaq.manager@example.test` and showed one managed hub, one partner, and one mobile display.

## Local demo access

- URL: `http://127.0.0.1:8000/hub/dashboard`
- Email: `ravaq.manager@example.test`
- Password: `password`

## Continue from here

Next recommended development slice: turn the scoped hub dashboard into an operational workspace by adding scoped create/edit workflows for hub partners, display inventory scheduling, pending ad/offer queues with notes, and KPI summaries.

## Update - Hub Manager Review Notes

- Hub manager approval/rejection controls now include a decision note textarea.
- Ad review notes are stored in `ad_approvals.notes` and exposed in `/api/v1/hub/dashboard` as `reviewNotes`.
- Partner offer review notes are stored in `reward_definitions.metadata.review_notes` and exposed in `/api/v1/hub/dashboard` as `reviewNotes`.
- The API also returns `reviewedAt` for reviewed ads/offers so the dashboard can show decision timing.
- Additional verification: targeted hub/partner/advertising tests now cover 22 tests and 176 assertions; full `composer test`, `npm run types:check`, `npm run lint:check`, `npm run format:check`, and `npm run build` passed.

## Update - Hub Display Scheduling

- Added scoped display scheduling for approved hub/ravaq ads.
- New web route: `POST /hub/ads/{adRequest}/schedule`.
- New API route: `POST /api/v1/hub/ads/{adRequest}/schedule`.
- `HubAdScheduleController` validates approved ad status, managed display scope, active display status, placement/display type compatibility, date range, and priority.
- `/hub/dashboard` now shows scheduling controls for approved ads and current display assignment/priority.
- Targeted tests now cover scheduling to the managed mobile display and rejection of a foreign display.
- Verification passed: targeted hub/advertising tests, full `composer test` with 119 tests and 650 assertions, `npm run types:check`, `npm run lint:check`, `npm run format:check`, and `npm run build`.
## Update - Hub Display Operations Queue

- Approval and publishing are now separated: ad approval sets placements to `approved`, not `scheduled`.
- Display schedule API now returns only placements explicitly assigned to the requested display device.
- Added active display schedule queue to `/hub/dashboard` through `displayScheduleItems` from `/api/v1/hub/dashboard`.
- Added cancellation flow for hub managers:
  - Web route: `POST /hub/ad-placements/{adPlacement}/cancel`
  - API route: `POST /api/v1/hub/ad-placements/{adPlacement}/cancel`
- Cancellation clears `display_device_id`, returns the placement to `approved`, and removes it from display playback feeds.
- Verification passed after this update:
  - Targeted advertising + hub tests: 16 tests, 145 assertions.
  - `composer test`: 120 tests, 669 assertions, plus Pint and PHPStan.
  - `npm run types:check`.
  - `npm run lint:check`.
  - `npm run format:check`.
  - `npm run build`.

## Continue from here - Next Slice

Recommended next development slice: build the admin/global display operations console so platform admin can see all displays, scheduled ads, playback health, event volume, and override/cancel display assignments across hubs.
## Update - Admin Display Operations Console

- Added service/controller/page for global display operations:
  - `App\Services\AdminDisplayOperationsService`
  - `App\Http\Controllers\Admin\DisplayOperationsController`
  - `resources/js/pages/admin/display-operations/index.tsx`
- Added web routes:
  - `GET /admin/display-operations`
  - `POST /admin/display-operations/placements/{adPlacement}/schedule`
  - `POST /admin/display-operations/placements/{adPlacement}/cancel`
- Added API routes:
  - `GET /api/v1/admin/display-operations`
  - `POST /api/v1/admin/display-operations/placements/{adPlacement}/schedule`
  - `POST /api/v1/admin/display-operations/placements/{adPlacement}/cancel`
- Sidebar now links to `عملیات نمایشگرها`.
- Global admin/operator scheduling validates approved ad status, active display status, placement/display type compatibility, date range, and priority.
- Console reports display inventory, ready placements, active placements, event totals, impressions, clicks, and last event time.
- Verification passed after this update:
  - Targeted admin/advertising/hub tests: 20 tests, 190 assertions.
  - `composer test`: 124 tests, 714 assertions, plus Pint and PHPStan.
  - `npm run types:check`.
  - `npm run lint:check`.
  - `npm run format:check`.
  - `npm run build`.

## Continue from here - Next Slice

Recommended next development slice: add display heartbeat/playback telemetry so each display client can report online/offline status, current slot, playback result, and errors to the operations console.
