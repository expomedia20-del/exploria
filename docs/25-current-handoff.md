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

1. Fix the unrelated TypeScript tuple issue in `resources/js/pages/dashboard.tsx` so full typecheck becomes clean again.
2. Continue public landing polish only if the current visual direction is approved.
3. Add controlled venue manager comments / readiness confirmations.
4. Harden ravaq / zone panel as a dedicated UI while keeping hub scope behind the scenes.
5. Review partner ads, standalone ads, and display operations after role panels settle.

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
