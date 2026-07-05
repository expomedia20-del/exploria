# Exploria Current Handoff

Latest working commits:

- `888c974 feat: add internal team supervision panel`
- `811df21 feat: add internal operations team panel`
- `2c7a66e feat: expand role authority model`

Local session start:

- Use `C:\Users\HPRAN\Documents\New project\exploria-start-here.bat` at the start of each local session.
- The local server must run from `E:\exploria-codebase-current`, not from `C:\Users\HPRAN\Documents\New project\exploria-runtime-copy`.
- Main local URL: `http://127.0.0.1:8004`
- Internal operations panel: `http://127.0.0.1:8004/admin/internal-operations`
- Role structure panel: `http://127.0.0.1:8004/admin/role-operations`

Previous synced commit:

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
Continue from the latest pushed main commit.

Read AGENTS.md and docs 20, 21, 24, and 25.
Check git status.
Start the site on http://127.0.0.1:8004 using:
C:\Users\HPRAN\Documents\New project\exploria-start-here.bat
Make sure the server runs from E:\exploria-codebase-current.
Continue from the role/panel work:
- /venue/dashboard is the read-only venue manager panel.
- /ravaq/dashboard is the ravaq / zone manager panel.
- /admin/internal-operations is the internal Exploria supervision panel.
- /admin/access-scopes should show account/role governance, approval sensitivity, and scope boundaries.
Next recommended work: controlled access-change workflow, then visitor/participant panel completion.
```
