# EcoPark End-to-End Demo Runbook

This runbook turns the EcoPark pilot into a sale-ready demo cycle.

## Scope

- Venue: EcoPark Abbasabad
- Primary campaign: EcoPark pilot visit 1405
- Online game: out of the core readiness gate for now
- Goal: prove one complete operational and commercial loop without role confusion

## Stage 1: Scenario And Acceptance

Pass criteria:

- Venue manager sees overall readiness, risks, and reports.
- Ravaq/hub manager sees only the relevant hub/zone and coordination issues.
- Shop/food/sponsor accounts manage their own offer, reward, ad, and redemption actions.
- Participant can enter with mobile, accept consent, start participation, and continue the visit.

## Stage 2: Data And Access Readiness

Pass criteria:

- Active venue, active campaign, QR, route, missions, rewards, and treasure exist.
- At least one partner shop/food unit and one sponsor are connected to the campaign.
- Internal Exploria team accounts are managed manually.
- Public visitors are created automatically through mobile entry and become participants by choice.

Operational page:

- `/admin/demo-cycle` now reads the EcoPark readiness checks and shows the status of campaign, QR, missions, partners, rewards, access scopes, and the Ravaq hub.

## Stage 3: Participant Journey

Pass criteria:

- Participant can select an active campaign.
- Participant can see next step, QR guidance, missions, wallet, rewards, visit history, and incentives.
- Individual, family, and team participation states are understandable.
- No admin dashboard or internal control is reachable by a normal visitor.

Operational page:

- `/admin/demo-cycle` shows visits, completed missions, issued rewards, and participant count.
- `/participant/dashboard` is the participant's main page after mobile entry and consent.

## Stage 4: Reward, Partner, Ad, And Display Execution

Pass criteria:

- Issued rewards can be redeemed or confirmed by the correct partner.
- Partner sees only their own performance and actions.
- Ravaq/hub manager can coordinate readiness but cannot manage shop finances.
- Approved ads and displays have a clear status or schedule.

Operational page:

- `/admin/demo-cycle` shows pending rewards, confirmed/redeemed rewards, and active displays.
- `/partner/dashboard`, `/partner/ads`, `/sponsor/dashboard`, `/admin/sponsors`, `/admin/ads`, and `/admin/display-operations` are the execution pages for this stage; store advertising and sponsor proposals stay on separate role surfaces.

## Stage 5: Commercial Report

Pass criteria:

- Final report shows visits, missions, rewards, redemptions, sponsor interactions, partner contribution, and display exposure.
- Pilot venue package, campaign sponsor package, and member unit package are ready for pricing.
- The demo can be explained in less than 10 minutes with a live site and a short ROI summary.

Operational page:

- `/admin/demo-cycle` shows the commercial readiness metrics and the three package directions.
- `/dashboard` remains the operational report page until the dedicated ROI report is built.

## Next Build Order

1. Add the demo cycle dashboard page.
2. Add a readiness score that maps directly to the five stages above.
3. Add one-click demo execution evidence: participant visit, reward issuance, partner redemption, display/ad status, and admin report.
4. Add the ROI/commercial report for sponsor and venue.
5. Prepare the pricing and contract documents.
