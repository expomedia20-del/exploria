# Exploria Sponsor Roadmap Priorities

## Purpose

This note keeps the next sponsor-system priorities visible while phase 1 is implemented first. The sponsor area now supports manual sponsorships, sponsor proposals, campaign assignment, mission assignment, and inventory allocations. The remaining work should stay phased so the commercial layer does not become too large at once.

## Priority Backlog

1. Reward issuance and redemption cycle
   - Status: implemented as the current phase-1 baseline.
   - Issue a user-facing redemption code after eligible mission completion.
   - Reserve one unit from the selected partner allocation.
   - Let the partner confirm/redeem the code.
   - Move reserved inventory into redeemed inventory.

2. Sponsor and admin performance reporting
   - Show allocated, reserved, redeemed, and remaining counts.
   - Break down results by campaign, sponsor, reward, and partner unit.
   - Add simple daily redemption metrics.

3. Sponsor workflow notifications
   - Flag low inventory, exhausted allocations, pending assignment, and revision requests.
   - Surface state changes for sponsor, admin, and partner dashboards.

4. Finance and contract tracking
   - Track invoiced, paid, outstanding, and consumed value.
   - Connect consumed rewards to sponsorship budget and contract value.

5. Legal and fraud controls
   - Store claim terms, expiry, one-time-use rules, and audit trails.
   - Prevent duplicate redemption and invalid partner confirmation.
