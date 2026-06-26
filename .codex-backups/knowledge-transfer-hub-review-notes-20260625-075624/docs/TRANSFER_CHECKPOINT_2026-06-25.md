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
