# EXPLORIA Client Demo Guide

This guide is the shortest reliable path for presenting the current EXPLORIA local demo.

For the step-by-step board presentation script, use `docs/PRESENTATION_RUNBOOK.md`.

## Demo URLs

Use these URLs on the current machine:

```text
Board-facing entry:
http://127.0.0.1:8004/board

Full mock ecosystem demo:
http://127.0.0.1:8004/demo/ecosystem

Employer demo hub:
http://127.0.0.1:8004/demo

Proposal coverage:
http://127.0.0.1:8004/demo/proposal

Mission and rewards simulator:
http://127.0.0.1:8004/demo/missions

Visitor QR landing:
http://127.0.0.1:8004/scan/ep1405-a7f3k9m2q8x4

Operational dashboard:
http://127.0.0.1:8004/dashboard

QR registry:
http://127.0.0.1:8004/admin/qr-codes

Campaign registry:
http://127.0.0.1:8004/admin/campaigns

Mission, treasure, and reward registry:
http://127.0.0.1:8004/admin/missions

Partner registry:
http://127.0.0.1:8004/admin/partners

Partner dashboard:
http://127.0.0.1:8004/partner/dashboard

Standalone advertising admin:
http://127.0.0.1:8004/admin/ads

Partner advertising submission:
http://127.0.0.1:8004/partner/ads


Display schedule API:
http://127.0.0.1:8004/api/v1/display/ecopark-entry-fixed-display/schedule
Venue and hub registry:
http://127.0.0.1:8004/admin/venues
```

## Demo Credentials

Admin panel:

```text
Email: admin@example.test
Password: password
```

Read-only admin/demo viewer:

```text
Email: viewer@example.test
Password: password
```

Visitor demo account:

```text
Email: demo@example.test
Password: password
```

Shop partner panel:

```text
Email: cafe.eco@example.test
Password: password
```

Hub/ravaq manager panel:

```text
Email: ravaq.manager@example.test
Password: password
```

The local OTP provider uses a fixed test code only in local/test environments:

```text
Mobile: 09120000000
OTP: 123456
```

For a fresh repeat of the same flow, use another valid Iranian-format mobile number such as:

```text
09121111111
```

## Presentation Flow

1. Open the board-facing entry:
   `http://127.0.0.1:8004/board`
2. Open the full mock ecosystem demo:
   `http://127.0.0.1:8004/demo/ecosystem`
3. Show the four role panels: visitor, shop/partner, ravaq manager, and platform admin.
4. Show mock definitions for treasures, rewards, discounts, partner offers, standalone ads, sponsor placements, ravaq capacity, rules, and anti-fraud controls.
5. Explain that this page is front-end mock data for the complete product vision, while the QR/OTP/consent/dashboard flow is the working MVP core.
6. Open the proposal coverage page:
   `http://127.0.0.1:8004/demo/proposal`
7. Show how the demo covers the board proposal: visitor journey, treasure campaign, touchpoints, hub scenarios, business value, and KPI.
8. Show the live demo cards and the complete QR -> OTP -> Consent -> Visit -> Dashboard flow.
9. Open the mission simulator:
   `http://127.0.0.1:8004/demo/missions`
10. Complete the first missions and show that points, rewards, level, and the locked challenge update live.
11. Open the QR landing page:
    `http://127.0.0.1:8004/scan/ep1405-a7f3k9m2q8x4`
12. Show that the QR is bound to the Abbasabad Eco Park pilot location.
13. Click the start/continue action to go to mobile OTP.
14. Enter `09120000000` or another valid demo mobile number.
15. Enter OTP `123456`.
16. Accept the Persian consent page.
17. Show the visit experience page created from the QR flow.
18. On the visit experience page, start/complete real database-backed missions and show issued rewards in the wallet area.
19. Open the dashboard and show operational counters.
20. Open `/admin/qr-codes` and show the QR registry row, binding, status, and scan link.
21. Open `/admin/campaigns` and show that campaigns can be created and linked to QR codes.
22. Open `/admin/missions` and show the real database-backed mission, treasure, point, and reward foundation.
23. Open `/partner/dashboard` as `cafe.eco@example.test` and show partner reward definitions, pending redemption codes, and code confirmation.
24. Submit a new partner offer from `/partner/dashboard`, then open `/admin/missions` as admin and approve or reject the pending offer.
25. Open `/partner/ads` as `cafe.eco@example.test`, submit a standalone ad request, then open `/admin/ads` and approve or reject it.
26. Open `/hub/dashboard` as `ravaq.manager@example.test` and show that the ravaq manager only sees the managed hub, partner, display inventory, scoped ads, and scoped partner offers.

## What Is Ready To Claim

- Persian RTL visitor flow exists from QR landing to OTP, consent, visit experience, and dashboard.
- Board-facing entry page exists at `/board` so the meeting starts from a polished product-style gateway.
- Full mock ecosystem page exists at `/demo/ecosystem` for visitor, shop, ravaq manager, admin, treasures, rewards, discounts, partner offers, standalone ads, sponsors, and rules.
- Standalone advertising requirements are recorded in `docs/features/STANDALONE_ADVERTISING_REQUIREMENTS.md` so upload, approval, fixed/mobile display publishing, scheduling, and reporting are not missed in real development.
- A confirmed visit is created after QR consent acceptance.
- The visit experience page can now start and complete real mission records, award points, unlock the locked challenge, and issue rewards to the visitor wallet.
- Dashboard counters are backed by database records, not static placeholders.
- QR registry page exists for `admin`, `operator`, and `viewer` roles; `admin` and `operator` can create new QR records.
- Campaign registry page exists for `admin`, `operator`, `viewer`, and `hub_manager` roles; `admin` and `operator` can create campaign records.
- Mission/reward registry page exists for `admin`, `operator`, `viewer`, and `hub_manager` roles, backed by database records for mission templates, mission instances, treasures, rewards, user progress, reward wallet, and redemption skeleton.
- Partner dashboard exists for `shop_partner` and `sponsor` roles. It shows the partner's own reward definitions, issued rewards, pending/confirmed redemptions, supports confirming a customer redemption code, and lets the partner submit a new offer/discount for admin review.
- Admin mission/reward registry can approve or reject partner-submitted offers. Submitted offers remain `draft` until approved and become `active` only after review.
- Standalone advertising skeleton exists with real tables for ad requests, creatives, display devices, placements, approvals, and events.
- Partner advertising page exists at `/partner/ads`; a partner or sponsor can submit an ad request for fixed/mobile displays, QR landing, reward page, map/route, or post-mission placement.
- Admin advertising page exists at `/admin/ads`; admin/operator can approve or reject all pending ad requests, while hub managers are restricted to managed hub/partner scope.
- Hub/ravaq manager dashboard exists at `/hub/dashboard` with API `/api/v1/hub/dashboard`; it shows only managed hubs, partners, display devices, scoped ad requests, scoped partner offers, review notes, decision times, and scheduling controls for approved display ads.
- Display publishing API exists at /api/v1/display/{deviceCode}/schedule, and display clients can record ad events at /api/v1/display/{deviceCode}/events.
- Venue/hub registry page exists for `admin`, `operator`, `viewer`, and `hub_manager` roles.
- Mission/reward simulator shows points, rewards, levels, locked missions, and next experience layers.
- Proposal coverage page maps the 22-slide Eco Park board proposal to the current demo and pilot minimums.
- Demo uses local SQLite and local Persian font assets, so it does not depend on external font CDNs.

## Safe Restart

If the local server is closed, run this from the project root:

```powershell
.\scripts\start-demo.ps1
```

Then open:

```text
http://127.0.0.1:8004/demo
```

## Current Technical Checkpoint

Last completed product-code commit:

```text
Use git log -1 --oneline after pulling/transferring the latest project folder.
```

Latest verified quality gates:

```text
composer test
npm run format:check
npm run types:check
npm run lint:check
npm run build
```

All passed on 2026-06-25.

Local runtime note: on 2026-06-25 the local SQLite database was backed up, then `php artisan migrate --force` and `php artisan db:seed --class=PilotLocationSeeder --force` were run so `/hub/dashboard` can load against the current schema.
## Demo Addendum - Hub Display Operations Queue

Use `/hub/dashboard` as `ravaq.manager@example.test` to show the real scoped hub manager workflow:

1. Review/approve a ravaq ad request.
2. Schedule the approved mobile-display ad to the managed mobile display.
3. Show the active display operations queue.
4. Cancel the scheduled placement and explain that approval does not publish an ad until a manager explicitly schedules it.

This is backed by `/api/v1/hub/dashboard`, `POST /api/v1/hub/ads/{adRequest}/schedule`, `POST /api/v1/hub/ad-placements/{adPlacement}/cancel`, and `/api/v1/display/{deviceCode}/schedule`.
## Demo Addendum - Admin Display Operations

Use `/admin/display-operations` as `admin@example.test` to show the global operations layer:

1. Review and approve a partner ad.
2. Schedule the approved compatible placement to an active fixed or mobile display.
3. Show the active display queue, event counters, impressions, clicks, caps, and last event time.
4. Cancel the placement globally and show that it disappears from the display schedule feed.

This demonstrates the difference between local hub/ravaq operations and platform-wide admin operations.
