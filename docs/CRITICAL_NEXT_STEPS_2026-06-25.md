# CRITICAL NEXT STEPS - EXPLORIA

Date: 2026-06-25
Last known commit: 1f5266f feat: add partner dashboard quick actions

## Current State

The real MVP foundation is running locally. Admin, hub/ravaq, display operations, partner rewards, partner ad summary, and partner quick actions are implemented and tested.

## Most Important Next Slice

Finish the partner/shop operational layer:

1. Editable partner/store profile: name, contact, operating notes, category, display visibility.
2. Offer inventory controls: stock adjustment, pause/resume offer, expiry/date rules.
3. Partner dashboard actions: edit offer, submit ad from dashboard, view approval notes.
4. Tests for partner scope and inventory changes.

## Why This Is Next

The board/demo already has admin and display operations. For product readiness, shops must be able to manage their own profile, offers, inventory, redemptions, and ads without admin hand-holding.

## Verified Before This Handoff

- Partner dashboard ad summary implemented.
- Partner dashboard quick actions implemented.
- PartnerRewardRedemptionTest passed: 10 tests / 72 assertions.
- npm run types:check passed.

## Important Local Paths

- Project: E:\exploria-codebase-current
- Backups: E:\exploria-codebase-current\.codex-backups
- Main checkpoint: docs\TRANSFER_CHECKPOINT_2026-06-25.md
- Demo guide: docs\CLIENT_DEMO_GUIDE.md