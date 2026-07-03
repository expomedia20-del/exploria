# Exploria Operational Roles, Access, and Underbuilt Feature Redesign

## Purpose

This document locks the next operational structure before new panel work begins. The current product already has the core campaign, sponsor, reward, treasure, QR, and demo cycle. The next risk is operational ambiguity: who owns each venue, hub, ravaq, partner, display, advertisement, reward, and approval.

The goal is to define the role hierarchy, access scopes, missing panels, and redesign priorities for the operational features that have received less attention so far.

## Design Principles

- Keep one Laravel + React monolith.
- Keep business rules in Laravel services and authorization layers.
- Keep UI Persian and RTL.
- Add panels only when the role has a distinct operational responsibility.
- Prefer scope-based access over many hard-coded role exceptions.
- Separate "view/report" permission from "approve/change" permission.
- Every role panel must answer three questions: what needs attention, what can this role do today, and what must be escalated?

## Operational Hierarchy

1. Exploria central admin
   - Global platform owner.
   - Can see and manage every venue, campaign, role, access scope, sponsor, reward, ad, display, and report.

2. Regional admin
   - Optional next-stage role for province or commercial region.
   - Useful when multiple venues exist in one city or province.
   - Not required for the immediate MVP unless multi-region operations start.

3. Exploria project admin / venue project manager
   - Internal Exploria manager responsible for one contracted venue project.
   - Example: Exploria project manager for EcoPark Abbasabad or Eram.
   - Owns local execution team, campaign readiness, daily operation, issues, and stakeholder coordination.

4. Venue executive manager
   - Official venue-side manager for EcoPark, Eram, Milad Tower, etc.
   - Above hubs, ravaq, commercial units, venue constraints, and venue-level approvals.
   - Sees only assigned venue data.
   - Can review operational status and approve local venue-impacting changes, but should not change platform-wide settings.

5. Ravaq / zone manager
   - Manager of a commercial zone, cultural zone, food court, ravaq, or similar sub-area.
   - Above shops and units in that zone.
   - Below venue executive manager.
   - Should be modeled as a hub/zone-scoped manager, but visible in UI as "مدیر رواق / زون".

6. Hub manager
   - Manager of a specific operational hub such as entry, science hub, game route hub, display hub, or reward desk.
   - Handles scoped QR, display, partner requests, local ads, reward readiness, and field incidents.

7. Field operator / campaign operator
   - Exploria field execution staff.
   - Works on assigned campaign, route, touchpoints, checklists, support, and incidents.
   - Should not approve commercial offers or change financial/sponsor data.

8. Shop / partner manager
   - Manager of one shop, food unit, cultural unit, service unit, or commercial partner.
   - Manages profile, offers, redemption, inventory, and partner ad requests.

9. Sponsor manager
   - Manager for sponsor account.
   - Creates proposals, reward packages, target units, assets, and reporting needs.
   - Sees only own sponsorship/proposal/reporting scope.

10. Display advertising manager
    - Internal or delegated operator for display schedules and playback evidence.
    - Can coordinate independent ads, sponsor ads, partner ads, and campaign display placements.

11. Visitor / participant
    - Consumer user, family, team, or group participant.
    - Completes missions, receives points, unlocks treasure, redeems rewards.

## Role-To-Scope Model

| Role | Primary scope | Derived access | Main panel |
|---|---|---|---|
| super_admin | global | all | Admin dashboard |
| regional_admin | region | venues in region | Regional operations |
| project_admin | venue/project | venue, hubs, partners, campaigns | Project admin panel |
| venue_executive | venue | hubs, partners, displays, campaigns in venue | Venue manager panel |
| ravaq_manager | hub/zone | partners and displays in ravaq/zone | Ravaq / zone panel |
| hub_manager | hub | touchpoints, displays, partners in hub | Hub manager panel |
| field_operator | campaign | assigned campaign route and issues | Field operations panel |
| shop_manager | partner | own partner rewards, ads, redemptions | Partner dashboard |
| sponsor_manager | sponsor account | own proposals, rewards, reports | Sponsor dashboard |
| display_ads_manager | display_network/venue | display devices, schedules, playback | Display operations |
| viewer | global or scoped read-only | read-only | Reports / dashboards |

## Recommended Scope Types

The current project already has useful scope types: global, region, venue, project, hub, partner, campaign, display_network, and team. The next implementation should keep these but clarify usage:

- venue: EcoPark, Eram, Milad Tower, mall, museum, or park.
- project: an Exploria execution contract inside one venue or group of venues.
- hub: operational hub, ravaq, food court, entry point, cultural hub, science hub.
- partner: shop, food unit, cultural unit, sponsor-like internal unit, service provider.
- campaign: specific campaign such as "کاشفان گنج پنهان".
- display_network: group of fixed/mobile display devices.
- team: participant team or field execution team.

## Current Gaps In Existing Role System

The code already contains several useful role concepts in `config/exploria_roles.php`, including project admin, venue executive, hub manager, shop manager, internal sponsor, external sponsor, and display ads manager. The gaps are:

- The actual user enum currently has fewer login roles than the operational role catalog.
- Sidebar role visibility is still based on broad user roles, not the full operational role catalog.
- "مدیر مکان" does not yet have a dedicated panel.
- "مدیر رواق" is currently mixed into hub manager behavior and should be restored as a first-class operational label.
- Access scopes exist, but the UI needs clearer assignment templates by role.
- Several pages allow hub manager visibility, but the distinction between view, review, approve, and execute is not consistently visible.

## Panel Blueprint

### 1. Venue Manager Panel

Purpose: the official venue-side manager sees the health of one venue.

Required sections:

- Venue overview: active campaigns, today's visitors, active QR, pending issues.
- Campaign status: readiness, live status, route status, partner status.
- Hubs and ravaqs: each hub's readiness, manager, open issues, displays.
- Partners and shops: onboarding, active offers, redemptions, service problems.
- Sponsor visibility: sponsors active in this venue, pending venue-impacting activations.
- Displays and ads: active placements, pending approvals, playback issues.
- Reward and treasure status: issued, reserved, redeemed, failed.
- Requests and approvals: changes requiring venue coordination.
- Reports: daily summary, stakeholder report, export-ready numbers.

Can do:

- View all assigned venue data.
- Comment on or approve venue-impacting operational adjustments.
- Escalate issues to Exploria project admin.
- Confirm venue readiness gates.

Cannot do:

- Change global role settings.
- Approve sponsor contracts or financial settlement alone.
- See other venues.

### 2. Project Admin Panel

Purpose: Exploria's internal manager for a specific venue project.

Required sections:

- Full venue operation cockpit.
- Task board for field operators, hub managers, display manager, and support assistants.
- Campaign readiness gates and unresolved blockers.
- Stakeholder coordination: venue manager, ravaq managers, sponsors, shops.
- Incident log and daily operating report.
- Approval queue across partner offers, ads, display slots, sponsor execution, and reward changes.

Can do:

- Manage scoped venue project operations.
- Assign tasks and scopes.
- Approve operational changes within policy.
- Escalate financial/legal/system risks.

### 3. Ravaq / Zone Manager Panel

Purpose: restore the ravaq manager as a meaningful operational role without creating a duplicate hub system.

Required sections:

- Ravaq/zone dashboard: shops, active campaigns, pending offers, ads, display schedule.
- Unit readiness: profile completeness, active offers, inventory, redemption issues.
- Local approvals: partner ads, shop reward offers, display requests.
- Visitor flow and crowd notes for the zone.
- Daily summary to venue manager and project admin.

Implementation note:

- Use hub scope or a future zone scope behind the scenes.
- UI label should be "پنل مدیر رواق / زون".
- A ravaq can be implemented as a hub type today, but the user-facing language should not force every ravaq manager to understand "hub".

### 4. Hub Manager Panel

Purpose: manage a smaller operational hub or route point.

Required sections:

- Hub readiness checklist.
- QR and touchpoint health.
- Display devices in the hub.
- Partner requests inside the hub.
- Mission execution issues.
- Reward redemption issues.
- Incident/report submission.

Current status:

- Exists, but should be reviewed after adding venue/ravaq panels so responsibilities do not overlap.

### 5. Partner / Shop Dashboard

Purpose: let shops and partner units operate without admin doing everything.

Missing or underbuilt items:

- Clear separation between reward offer, discount offer, product/sample offer, and independent ad.
- Inventory view per campaign and per reward.
- Redemption workflow with pending, confirmed, rejected, and failed states.
- Operating hours and availability notes.
- Campaign-specific service rules.
- Staff handover notes.
- Issue reporting to hub/ravaq manager.

Advertising sub-feature:

- "تبلیغات جداگانه" should not be a small side link only.
- It should show active ad requests, pending review, rejected with reason, scheduled display slots, and playback evidence when available.

### 6. Campaign Participants / Members

Purpose: define who participates in a campaign and in what operational role.

Missing or underbuilt items:

- Participant role templates: redemption point, sales point, route host, content partner, display partner, sponsor unit.
- Readiness checklist for each participant.
- Contract/approval status separate from onboarding status.
- Connection map to mission, reward, QR, display, and sponsor package.
- Bulk invite or status update for many units.
- "Why this participant matters" note for admin and venue manager.

### 7. Partner Management

Purpose: master registry of partner units across venues.

Missing or underbuilt items:

- Distinguish partner account from campaign participant.
- Locations per partner: venue, zone, hub, touchpoint.
- Partner staff/users and access scopes.
- Partner readiness score.
- History of campaigns participated in.
- Performance summary: rewards offered, redemptions, ads, issues.
- Suspension/inactive reason.

### 8. Standalone Advertising

Purpose: handle ads that are not necessarily tied to a reward or mission.

Missing or underbuilt items:

- Clear ad request lifecycle: draft, submitted, under review, needs revision, approved, scheduled, played, rejected, canceled.
- Asset checklist: image/video format, duration, target display, campaign/venue compatibility.
- Owner distinction: partner ad, sponsor ad, venue message, Exploria message.
- Review workflow by hub/ravaq/display manager.
- Conflict detection with display schedule.
- Playback evidence and reporting.

### 9. Display Operations

Purpose: operational control of fixed/mobile screens and playback.

Missing or underbuilt items:

- Device health: online/offline, last heartbeat, last playback.
- Display network grouping by venue/hub.
- Schedule calendar by device.
- Priority rules: emergency venue notice, sponsor slot, campaign content, partner ad.
- Playback proof: time, device, creative, status, error.
- Fault reporting and escalation.
- Inventory of screen formats and allowed media.

### 10. Roles And Operations

Purpose: make the role model understandable and actionable.

Missing or underbuilt items:

- Persian labels for all operational roles.
- Role hierarchy diagram.
- "Can view / can request / can approve / can execute" matrix.
- Links from each role to its panel.
- Daily operations checklist per role.
- Role simulation: what this user sees after scope assignment.

### 11. Access Scope Assignment

Purpose: assign a user to the correct scope without mistakes.

Missing or underbuilt items:

- Preset templates:
  - Venue manager for EcoPark.
  - Ravaq manager for Ravaq Commercial Hub.
  - Hub manager for entry hub.
  - Shop manager for Cafe Eco.
  - Display manager for EcoPark display network.
- Validation warnings:
  - Role and scope mismatch.
  - User has broad role but no scope.
  - Scope assigned to inactive venue/hub/partner.
- Preview of derived access:
  - venues visible.
  - hubs visible.
  - partners visible.
  - campaigns visible.
- Audit log for assignment changes.

## Permission Matrix For Next Implementation

| Area | Admin | Project admin | Venue manager | Ravaq/zone manager | Hub manager | Shop/partner | Sponsor | Display manager |
|---|---|---|---|---|---|---|---|---|
| Venue profile | manage | request/manage scoped | view/comment/approve local | view scoped | view scoped | no | no | view display areas |
| Campaign registry | manage | manage scoped | view/comment | view scoped | view scoped | view own | view own sponsor campaigns | view display campaigns |
| Campaign participants | manage | manage scoped | view/comment | review scoped units | review scoped units | view own | view target units | view display partners |
| Partner registry | manage | manage scoped | view/comment | review scoped | review scoped | update own | no | view scoped |
| Partner offers | approve/manage | approve scoped | view/comment | review scoped | review scoped | submit/update own | no | no |
| Sponsor proposals | approve/manage | review scoped | view/comment if venue impact | view if zone impact | view if hub impact | no | submit/update own | view if display-related |
| Rewards/treasures | manage | manage scoped | view/comment | review scoped | review scoped | submit/fulfill own | submit sponsor rewards | no |
| QR/touchpoints | manage | manage scoped | view/comment | view scoped | report scoped | no | no | no |
| Ads | approve/manage | approve scoped | view/comment | review scoped | review scoped | submit own | submit own | schedule/manage |
| Displays | manage | manage scoped | view/comment | view scoped | manage assigned | no | view own placements | manage schedule/playback |
| Redemptions | view/manage | view scoped | view summary | view scoped | view scoped | confirm own | view sponsor-linked | no |
| Access scopes | manage | request/manage scoped team | request changes | request changes | request changes | no | no | no |

## Implementation Roadmap

### Phase A - Lock roles and labels

- Update operational role labels to Persian.
- Add explicit ravaq/zone manager role key or alias.
- Add venue manager and project admin visibility in role operations.
- Add access-scope templates for common assignments.

Acceptance criteria:

- Role operations page clearly shows hierarchy and daily responsibilities.
- Access scope page can assign a user to venue/hub/partner/campaign with preview.
- No user receives unrelated venue data.

### Phase B - Venue Manager Panel

- Add `/venue/dashboard` or `/admin/venue-operations` scoped by venue.
- Show venue campaign, hubs, partners, displays, ads, rewards, redemptions, and issues.
- Add read/comment/approval status without global admin powers.

Acceptance criteria:

- EcoPark manager sees only EcoPark.
- EcoPark manager sees ravaq, hubs, partners, displays, campaigns, sponsor impact, and reward status.
- EcoPark manager cannot edit global admin settings.

### Phase C - Ravaq / Zone Manager Panel

- Restore as visible panel in sidebar.
- Back it with hub or zone scope.
- Focus on shops, offers, ads, displays, redemptions, and daily zone summary.

Acceptance criteria:

- Ravaq manager sees only ravaq shops and ravaq display/ads.
- Ravaq manager can review scoped offers/ads if allowed.
- Ravaq manager cannot see other venue hubs.

### Phase D - Partner, Ads, and Display Hardening

- Improve partner dashboard advertising sub-flow.
- Improve campaign participants readiness and connection map.
- Improve partner management registry and staff/scope connection.
- Add ad lifecycle and display playback evidence.

Acceptance criteria:

- A shop can submit reward offer and separate ad request.
- Hub/ravaq/display manager can review or schedule scoped ads.
- Admin can see final evidence for sponsor/partner reporting.

### Phase E - Field Execution

- Add project field operator panel.
- Track route readiness, QR issues, visitor support, incidents, and daily closeout.

Acceptance criteria:

- Project admin can assign operational tasks.
- Field operator can report issue without changing commercial data.
- Venue manager sees relevant issue summaries.

## Suggested Immediate Next Build Slice

Start with Phase A and the smallest part of Phase B:

1. Persianize and clarify role operations catalog.
2. Add role keys/labels for:
   - project_admin: مدیر پروژه مکانی اکسپلوریا
   - venue_executive: مدیر مکان
   - ravaq_manager: مدیر رواق / زون تجاری
   - hub_manager: مدیر هاب
   - display_ads_manager: مدیر تبلیغات و نمایشگرها
3. Add access assignment templates for EcoPark:
   - EcoPark venue manager.
   - Ravaq commercial manager.
   - Entry hub manager.
   - Cafe Eco shop manager.
   - EcoPark display manager.
4. Add a first read-only venue manager panel for EcoPark-level visibility.

This keeps the next implementation useful, bounded, and aligned with the operational model.

## Implementation Update - 2026-07-04

Completed in the first Phase A build slice:

- Persian operational labels were added for the main role catalog.
- `ravaq_manager` was added as a distinct operational role for ravaq / commercial zone management.
- Access-scope assignment templates were added for common EcoPark assignments:
  - EcoPark project admin.
  - EcoPark venue manager.
  - EcoPark ravaq commercial manager.
  - EcoPark entry hub manager.
  - Cafe Eco shop manager.
  - EcoPark display manager.
- The admin access-scope page now shows those templates as quick registration cards, so an admin can pick a user and assign the correct role/scope without manually copying IDs.

Still pending from the immediate slice:

- First read-only venue manager panel for EcoPark-level visibility.
- Full Persian rewrite of older English responsibility text for roles that existed before this slice.

## Implementation Update - 2026-07-04 - Venue Manager Panel

Completed in the first Phase B build slice:

- Added `/venue/dashboard` as a read-only venue manager panel.
- Added `/api/v1/venue/dashboard` for scoped venue dashboard data.
- Added scoped venue access logic so a non-platform user only sees directly assigned venue scopes.
- Added EcoPark-level visibility for:
  - venue profile and status.
  - campaigns and campaign readiness counts.
  - hubs / ravaq sections.
  - partners and unit activity.
  - independent ads and display scheduling summary.
  - display device status.
  - rewards, redemptions, and treasures.
- Added the sidebar link `پنل مدیر مکان`.

Important boundary:

- This panel is read-only in this slice. It does not approve sponsor contracts, edit global settings, mutate role assignments, or perform financial actions.

Next recommended slice:

- Add venue manager comments / readiness confirmations as a controlled workflow.
- Add ravaq / zone manager panel using hub scope but user-facing ravaq language.
