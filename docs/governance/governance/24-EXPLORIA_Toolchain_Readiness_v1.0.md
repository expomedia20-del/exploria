# 24 — EXPLORIA Toolchain Readiness v1.0

**Date:** 2026-06-20  
**Status:** Conditional — Runtime Ready / Package Network, Git and PostgreSQL Pending

## Tool Matrix

| Tool | Target / Detected | Status | Note |
|---|---|---|---|
| Node.js | v24.17.0 LTS Portable | PASS | Official package; SHA256 verified |
| npm | v11.13.0 | PASS | Extracted with Node.js |
| PHP | PHP 8.3.31 NTS x64 Portable | PASS | Official VS16-compatible runtime; required extensions enabled in ASCII runtime path |
| Composer | v2.10.1 Portable | PASS / NETWORK BLOCKED | Composer runs; Package Manager network is blocked by Codex policy |
| Git | Not installed | PENDING | Required before normal development history |
| PostgreSQL | Not installed | PENDING | Required for final Sprint 1A integration validation |
| Docker | Not installed | OPTIONAL/PENDING | Preferred for reproducible PostgreSQL and local services |

## Immediate Gate

Runtime محلی آماده است. مانع فعلی، محدودیت شبکه PHP/Composer برای Packagist و GitHub Distribution Archives است. Codebase رسمی و Governance Handoff ایجاد شده، اما Framework Dependencyها تا رفع این محدودیت نصب نمی‌شوند.

## Temporary Development Rule

- تا آماده شدن PostgreSQL، Migrationها برای PostgreSQL طراحی می‌شوند.
- استفاده موقت از SQLite فقط برای Unit/Feature Testهای اولیه مجاز است و جایگزین Gate نهایی PostgreSQL نیست.
- شروع کدنویسی بدون Git فقط برای Bootstrap اولیه مجاز است و باید با Backup تاریخ‌دار محافظت شود.
