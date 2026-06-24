# EXPLORIA Client Demo Guide

This guide is the shortest reliable path for presenting the current EXPLORIA local demo.

## Demo URLs

Use these URLs on the current machine:

```text
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
```

## Demo Credentials

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

1. Open the employer demo hub:
   `http://127.0.0.1:8000/demo`
2. Open the proposal coverage page:
   `http://127.0.0.1:8000/demo/proposal`
3. Show how the demo covers the board proposal: visitor journey, treasure campaign, touchpoints, hub scenarios, business value, and KPI.
4. Show the live demo cards and the complete QR -> OTP -> Consent -> Visit -> Dashboard flow.
5. Open the mission simulator:
   `http://127.0.0.1:8000/demo/missions`
6. Complete the first missions and show that points, rewards, level, and the locked challenge update live.
7. Open the QR landing page:
   `http://127.0.0.1:8000/scan/ep1405-a7f3k9m2q8x4`
8. Show that the QR is bound to the Abbasabad Eco Park pilot location.
9. Click the start/continue action to go to mobile OTP.
10. Enter `09120000000` or another valid demo mobile number.
11. Enter OTP `123456`.
12. Accept the Persian consent page.
13. Show the visit experience page created from the QR flow.
14. Open the dashboard and show operational counters.
15. Open `/admin/qr-codes` and show the QR registry row, binding, status, and scan link.

## What Is Ready To Claim

- Persian RTL visitor flow exists from QR landing to OTP, consent, visit experience, and dashboard.
- A confirmed visit is created after QR consent acceptance.
- Dashboard counters are backed by database records, not static placeholders.
- QR registry page exists for `admin`, `operator`, and `viewer` roles.
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
9da010b feat: add qr registry UI and Persian font
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
