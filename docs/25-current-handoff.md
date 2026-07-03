# Exploria Current Handoff

Last synced commit:

- `263c9e8 feat: expose ravaq manager panel`

Repository:

- `https://github.com/expomedia20-del/exploria`

Current completed slice:

- Operational role/access templates are available.
- Venue manager read-only panel is available at `/venue/dashboard`.
- Ravaq / zone manager panel route is available at `/ravaq/dashboard`.
- Related API routes are available at `/api/v1/venue/dashboard` and `/api/v1/ravaq/dashboard`.
- Docs 24 and 25 record the current operational status.

Verified before this handoff:

- Venue manager dashboard tests passed.
- Hub/ravaq dashboard tests passed.
- TypeScript check passed.
- Vite build passed.

Recommended next slice:

1. Add controlled venue manager comments / readiness confirmations.
2. Harden ravaq / zone panel as a dedicated UI while keeping hub scope behind the scenes.
3. Review partner ads, standalone ads, and display operations after role panels settle.

Startup prompt for the next Codex session:

```text
This is the Exploria project.
Repository: https://github.com/expomedia20-del/exploria
Continue from commit 263c9e8.

Read AGENTS.md and docs 20, 21, 24, and 25.
Check git status.
Start the site on http://127.0.0.1:8004.
Continue from the role/panel work:
- /venue/dashboard is the read-only venue manager panel.
- /ravaq/dashboard is the ravaq / zone manager panel.
Next recommended work: controlled venue readiness comments, then dedicated ravaq UI hardening.
```
