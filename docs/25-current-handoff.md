# Exploria Current Handoff

## Repository

- GitHub: `https://github.com/expomedia20-del/exploria`
- Main local codebase: `C:\source\exploria`
- Runtime / transfer copies are not canonical and must not be used for development.
- Main local URL: `http://127.0.0.1:8004`

## Latest Working Baseline

- `0d76cbd fix: align pilot mission trigger mappings`
- `1effe0f fix: complete service layer type safety`
- `b9bb081 fix: normalize venue design and registry data`

Always verify the real latest point with:

```bash
git log -1 --oneline
git status --short
```

## Local Toolchain

Verified on 2026-07-18:

- Project: `C:\source\exploria`
- PHP: `C:\php\php.exe` (available through `PATH`)
- Node.js and npm: available through `PATH`
- Local database: SQLite at `database/database.sqlite` (Git-ignored)

The local startup script supports the legacy fixed toolchain, the project runtime, and PHP/npm available through `PATH`:

- `scripts/start-local.ps1`

## Startup

Run from the project root:

```powershell
powershell -ExecutionPolicy Bypass -File scripts\start-local.ps1
```

The server must run from the canonical repository:

```text
C:\source\exploria
```

Important local pages:

- Public landing: `http://127.0.0.1:8004`
- Admin dashboard: `http://127.0.0.1:8004/dashboard`
- Commercialization: `http://127.0.0.1:8004/admin/commercialization`
- Demo readiness / cycle: `http://127.0.0.1:8004/admin/demo-cycle`
- Role structure panel: `http://127.0.0.1:8004/admin/role-operations`
- Internal operations panel: `http://127.0.0.1:8004/admin/internal-operations`
- Venue manager panel: `http://127.0.0.1:8004/venue/dashboard`
- Ravaq / zone panel: `http://127.0.0.1:8004/ravaq/dashboard`

## Current Completed Slices

- Public landing page was redesigned and then balanced visually.
- The landing Hero now shows a controlled two-column product mockup, clearer CTA, and the four core stakeholder outcomes in the first viewport.
- EcoPark demo readiness check is available:

```bash
php artisan exploria:demo-readiness
php artisan exploria:demo-readiness --json
```

- Operational role/access templates are available.
- Venue manager read-only panel is available.
- Ravaq / zone manager panel route is available.
- Internal Exploria supervision panel is available.

## Verification Notes

Recent verification:

- Backend suite passed: 229 tests.
- PHPStan passed across the complete configured project with zero findings.
- TypeScript check passed.
- ESLint check passed with zero errors.
- Prettier formatting check passed.
- Vite production build passed.
- EcoPark Demo Readiness passed with 17 pass, 0 warning, and 0 fail.
- Role-based UAT suite passed with 103 tests and 1,145 assertions covering admin, access scope, partner, sponsor, venue, hub, and participant flows.
- Critical-path smoke suite passed with 41 tests and 382 assertions covering QR, OTP, consent, visit missions, and dashboard summary.
- Local HTTP smoke returned `200` for the public landing and login pages.
- `scripts/start-local.ps1` passed from `C:\source\exploria` using the PHP/npm available through `PATH`.
- Public landing was checked in desktop viewport.
- Public landing was checked in mobile viewport `390x844`.
- No horizontal overflow was observed.

Current limitation:

- Automated browser-backed visual UAT remained unavailable in the 2026-07-19 stabilization session because no controllable browser runtime was attached. HTTP, Feature tests, TypeScript, ESLint, formatting, PHPStan, and production build verification passed.

## Recommended Next Slice After Stabilization

1. Visual identity and UX polish:
   - Design an Exploria logo and replace the default Laravel-style mark across the app shell, auth pages, favicon, and any remaining brand surfaces.
   - Make participant, shop partner, sponsor, venue manager, ravaq/hub manager, and admin pages more mobile-friendly, warmer, and more visually varied.
   - Use place-specific imagery or generated/curated venue visuals in empty spaces and hero/summary areas, especially for participant and partner flows.
   - Add more expressive color treatment to buttons, icons, status chips, mission/reward cards, and role dashboards while preserving operational clarity.
2. Participant onboarding and consent:
   - After OTP verification, route the participant directly to the consent flow or next destination without an extra "view consent" button.
   - Show active consent only once per user/version; returning users who already accepted the active version should skip consent and continue to the visit/dashboard.
3. Participant experience redesign:
   - Add a participant-first Exploria home/dashboard that feels playful and place-aware before the user enters the operational visit screen.
   - Surface venue attractions and game value clearly: map-like journey, missions, treasures, rewards, and highlights such as Gonbad Mina, Ocean Park, and Tabiat Bridge.
   - Keep the online game optional but prominent for younger audiences, and use it to teach the full Exploria cycle through a map/game experience.
4. Demo presentation backlog to keep for later:
   - Build a Demo Presenter Mode that walks through venue evaluation, blueprint, campaign, QR, user journey, reward, redemption, and ROI.
   - Polish `/admin/commercialization` as a concise final report for venue managers, sponsors, and shops.
   - Add a pre-meeting readiness checklist with demo accounts, links, QR code, and redemption code.
   - Write a 5-7 minute demo script for sales/investor presentation.
5. Suggested additions:
   - Add role-specific welcome panels with one clear next action for each role.
   - Add skeleton/empty states with visual context instead of plain text-only blanks.
   - Add responsive QA checkpoints for 390px, 430px, tablet, and desktop on the main demo flow.
   - Add a small venue media library abstraction so future locations can provide images without hard-coding EcoPark everywhere.
   - Add a visual style guide page or component story area for cards, chips, buttons, reward badges, and mission states.

## Startup Prompt For Next Codex Session

```text
This is the Exploria project.
Repository: https://github.com/expomedia20-del/exploria
Main local codebase: C:\source\exploria
PHP: C:\php\php.exe or php.exe from PATH
Continue from the latest pushed main commit.

Read AGENTS.md and docs 20, 21, 24, and 25.
Check git status.
Start the site on http://127.0.0.1:8004 using:
powershell -ExecutionPolicy Bypass -File scripts\start-local.ps1

Continue from the public landing / role-panel work:
- The public landing Hero has been balanced.
- /venue/dashboard is the read-only venue manager panel.
- /ravaq/dashboard is the ravaq / zone manager panel.
- /admin/internal-operations is the internal Exploria supervision panel.
- /admin/access-scopes should show account/role governance, approval sensitivity, and scope boundaries.

The stabilization baseline has passing backend tests, role-based UAT, TypeScript, ESLint, formatting, PHPStan, production build, HTTP smoke, and Demo Readiness. Continue with manual responsive/browser UAT when a browser runtime is available, then proceed to the next approved existing-product slice.
```
