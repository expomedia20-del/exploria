# EXPLORIA Client Demo Guide

This guide is the shortest reliable path for presenting the current EXPLORIA local demo.

For the step-by-step board presentation script, use `docs/PRESENTATION_RUNBOOK.md`.

## Demo URLs

Use these URLs on the current machine:

```text
Board-facing entry:
http://127.0.0.1:8000/board

Full mock ecosystem demo:
http://127.0.0.1:8000/demo/ecosystem

Employer demo hub:
http://127.0.0.1:8000/demo

Proposal coverage:
http://127.0.0.1:8000/demo/proposal

Mission and rewards simulator:
http://127.0.0.1:8000/demo/missions

Visitor QR landing:
http://127.0.0.1:8000/scan/ep1405-a7f3k9m2q8x4

Operational dashboard:
http://127.0.0.1:8000/dashboard

QR registry:
http://127.0.0.1:8000/admin/qr-codes

Campaign registry:
http://127.0.0.1:8000/admin/campaigns

Partner registry:
http://127.0.0.1:8000/admin/partners

Venue and hub registry:
http://127.0.0.1:8000/admin/venues
```

## Demo Credentials

Admin panel:

```text
Email: admin@example.test
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
   `http://127.0.0.1:8000/board`
2. Open the full mock ecosystem demo:
   `http://127.0.0.1:8000/demo/ecosystem`
3. Show the four role panels: visitor, shop/partner, ravaq manager, and platform admin.
4. Show mock definitions for treasures, rewards, discounts, partner offers, standalone ads, sponsor placements, ravaq capacity, rules, and anti-fraud controls.
5. Explain that this page is front-end mock data for the complete product vision, while the QR/OTP/consent/dashboard flow is the working MVP core.
6. Open the proposal coverage page:
   `http://127.0.0.1:8000/demo/proposal`
7. Show how the demo covers the board proposal: visitor journey, treasure campaign, touchpoints, hub scenarios, business value, and KPI.
8. Show the live demo cards and the complete QR -> OTP -> Consent -> Visit -> Dashboard flow.
9. Open the mission simulator:
   `http://127.0.0.1:8000/demo/missions`
10. Complete the first missions and show that points, rewards, level, and the locked challenge update live.
11. Open the QR landing page:
    `http://127.0.0.1:8000/scan/ep1405-a7f3k9m2q8x4`
12. Show that the QR is bound to the Abbasabad Eco Park pilot location.
13. Click the start/continue action to go to mobile OTP.
14. Enter `09120000000` or another valid demo mobile number.
15. Enter OTP `123456`.
16. Accept the Persian consent page.
17. Show the visit experience page created from the QR flow.
18. Open the dashboard and show operational counters.
19. Open `/admin/qr-codes` and show the QR registry row, binding, status, and scan link.
20. Open `/admin/campaigns` and show that campaigns can be created and linked to QR codes.

## What Is Ready To Claim

- Persian RTL visitor flow exists from QR landing to OTP, consent, visit experience, and dashboard.
- Board-facing entry page exists at `/board` so the meeting starts from a polished product-style gateway.
- Full mock ecosystem page exists at `/demo/ecosystem` for visitor, shop, ravaq manager, admin, treasures, rewards, discounts, partner offers, standalone ads, sponsors, and rules.
- Standalone advertising requirements are recorded in `docs/features/STANDALONE_ADVERTISING_REQUIREMENTS.md` so upload, approval, fixed/mobile display publishing, scheduling, and reporting are not missed in real development.
- A confirmed visit is created after QR consent acceptance.
- Dashboard counters are backed by database records, not static placeholders.
- QR registry page exists for `admin`, `operator`, and `viewer` roles; `admin` and `operator` can create new QR records.
- Campaign registry page exists for `admin`, `operator`, `viewer`, and `hub_manager` roles; `admin` and `operator` can create campaign records.
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
http://127.0.0.1:8000/demo
```

## Current Technical Checkpoint

Last completed commit:

```text
758c0b4 feat: add venue registry foundation
```

Latest verified quality gates:

```text
composer test
npm run format:check
npm run types:check
npm run lint:check
npm run build
```

All passed on 2026-06-24.
