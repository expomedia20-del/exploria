# Exploria Current Handoff

## Repository

- GitHub: `https://github.com/expomedia20-del/exploria`
- Main local codebase: `E:\exploria-codebase-current`
- Runtime / transfer copy, if present: `E:\New project - Copy`
- Main local URL: `http://127.0.0.1:8004`

## Latest Working Commits

- `3a10b86 feat: balance public landing hero`
- `c807fb2 feat: redesign exploria public landing page`
- `667e081 feat: add ecopark demo readiness check`

Always verify the real latest point with:

```bash
git log -1 --oneline
git status --short
```

## Local Toolchain

Preferred fixed toolchain path:

- `E:\exploria-toolchain-local`

Verified on 2026-07-08:

- PHP: `E:\exploria-toolchain-local\php\php.exe`
- PHP version: `8.4.22`
- Composer: `E:\exploria-toolchain-local\composer\composer.phar`
- Composer version: `2.10.1`

Node/NPM were not found inside the fixed toolchain path during this handoff. For frontend build on this PC, use the project runtime Node:

- `E:\exploria-codebase-current\.codex-runtime\node\npm.cmd`

The local startup script now prefers the fixed PHP path first and falls back to the project runtime PHP if needed:

- `scripts/start-local.ps1`

## Startup

Run from the project root:

```powershell
powershell -ExecutionPolicy Bypass -File scripts\start-local.ps1
```

The server must run from:

```text
E:\exploria-codebase-current
```

not from a runtime copy.

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

- Vite build passed after setting local PHP and Node paths.
- Public landing was checked in desktop viewport.
- Public landing was checked in mobile viewport `390x844`.
- No horizontal overflow was observed.

Known unrelated issue:

- `npm run types:check` still stops on an older, unrelated tuple typing issue in `resources/js/pages/dashboard.tsx`.

## Recommended Next Slice

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
Main local codebase: E:\exploria-codebase-current
Fixed toolchain path: E:\exploria-toolchain-local
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

Next recommended work: fix the unrelated dashboard TypeScript tuple issue, then continue controlled access-change workflow or venue readiness confirmations.
```
