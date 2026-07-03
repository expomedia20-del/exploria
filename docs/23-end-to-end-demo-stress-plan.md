# Exploria End-to-End Demo Stress Plan

## Purpose

This plan defines the next full-product demo cycle. The goal is not only to show a happy path, but to stress the hardest parts of Exploria: venue evaluation, blueprint selection, campaign creation, partner participation, sponsor proposals, mixed rewards, hidden treasures, QR entry, route operations, reward redemption, and reporting.

## Demo Campaign

- Working title: کاشفان گنج پنهان
- Starting point: ارزیابی مکان
- Target venue: اکوپارک عباس‌آباد
- Blueprint: بازی آنلاین نقشه گنج اکوپارک
- Demo objective: prove that a campaign can move from venue evaluation to user reward redemption with multiple commercial participants.

## Required Participant Mix

The demo must include these participant types:

- فروشگاه عضو کمپین: فروشگاه X
- واحد غذایی: کافه اکو
- اسپانسر مسیر خانوادگی: برند حامی خانواده
- اسپانسر جایزه یا محصول: کالا یا بسته ویژه
- مدیر هاب: مسئول هماهنگی مسیر و نمایشگر
- ادمین مرکزی: تایید، اتصال و کنترل نهایی
- کاربر مصرف‌کننده: انجام مسیر، دریافت امتیاز، گنج و پاداش

## Layered Incentive Design

The demo should include at least four incentive layers:

1. امتیاز پایه
   - Issued after every mission completion.
   - Used to measure progress and unlock higher tiers.

2. پاداش فروشگاهی
   - Provided by a shop or food unit.
   - Example: کد تخفیف یا نوشیدنی کوچک.
   - Redeemed through the partner dashboard.

3. پاداش اسپانسری
   - Provided by an external sponsor.
   - Example: تخفیف 70 درصد یا بسته ویژه.
   - Must include target partner allocations and inventory tracking.

4. گنج پنهان
   - Unlocked after a later mission step, not immediately at entry.
   - Can reveal a sponsor-backed reward, special message, or final treasure.

## Target Journey

1. ارزیابی مکان
   - Confirm venue profile, zones, hubs, touchpoints, business units, display potential, and campaign suitability.

2. انتخاب الگو
   - Select a blueprint that supports QR entry, route guidance, mission steps, reward tiers, and hidden treasure.

3. ثبت و انتخاب کمپین
   - Create the campaign and keep it selected through all downstream pages.

4. کارگاه ساخت کمپین
   - Review readiness gates and identify missing route, participant, QR, reward, and display items.

5. مشارکت فروشگاه‌ها و شرکا
   - Add or confirm participating shops and food units.
   - Assign execution roles such as reward redemption, route point, sales point, or operational support.

6. مشارکت اسپانسرها
   - Cover both manual sponsor setup and sponsor-submitted proposals.
   - Convert approved proposals into campaign support, partner assignments, reward definitions, treasure definitions, and inventory plans.

7. مأموریت، گنج و پاداش
   - Create or sync all mission steps.
   - Connect sponsor rewards to later mission steps, preferably step 3 or 4.
   - Connect hidden treasure to a mission and claim condition.
   - Register per-partner inventory shares.

8. QR و ورود کاربر
   - Ensure the QR code starts the right campaign and visit.
   - Confirm the visitor sees the intended first mission.

9. نقشه عملیات کمپین
   - Verify route sequence, hubs, touchpoints, partners, displays, sponsor exposure, and operational readiness.

10. اجرای کاربر
    - Complete missions as a visitor.
    - Award points.
    - Unlock treasure.
    - Issue sponsor or partner reward code.

11. تایید فروشگاه یا شریک
    - Confirm redemption code in partner dashboard.
    - Move inventory from allocated to reserved to redeemed.

12. گزارش نهایی
    - Admin should see campaign readiness, reward status, inventory use, pending redemptions, and sponsor/partner impact.

## Stress Cases To Test

- A campaign is not selected while the user is on a downstream page.
- A sponsor reward belongs to another campaign.
- Total reward stock does not match partner allocation sum.
- A reward is connected before any mission exists.
- The same sponsor has multiple reward items across multiple partner units.
- A hidden treasure is created independently and then connected to a sponsored reward.
- A partner with zero remaining inventory tries to redeem a code.
- A hub manager sees only scoped venue or hub data.
- A sponsor proposal is returned for revision and resubmitted.
- Manual sponsor setup and sponsor proposal activation both feed the same mission/reward registry.

## Acceptance Criteria

- The sidebar clearly follows the product journey from venue evaluation to operations.
- Every demo step preserves the selected campaign context.
- Mission, reward, treasure, partner, sponsor, QR, display, and route pages agree on the same campaign.
- Sponsor rewards can be assigned to later mission steps with inventory allocations.
- Completing the connected mission issues a redeemable reward code.
- Partner confirmation updates redemption and inventory state.
- The final campaign can be presented as a sellable operational demo.

## Current Next Implementation Slice

1. Verify the revised sidebar in desktop and collapsed states.
2. Run the demo from ارزیابی مکان through campaign selection.
3. Add missing selected-campaign context warnings where users can accidentally work on the wrong campaign.
4. Expand the existing demo preparation command or add a new stress-demo command to create the full layered scenario.
5. Execute the visitor and partner redemption path with the layered reward setup.

## Implementation Checkpoint - 2026-07-03

- Added `exploria:prepare-stress-demo` as the operational command for this plan.
- The command completes the demo venue profile, creates the selected campaign, keeps the blueprint context, prepares partners, sponsors, sponsor proposal items, manual sponsor support, QR entry, display readiness, layered rewards, hidden treasure, inventory allocation, visitor completion, issued reward, and confirmed redemption.
- The command is idempotent and can be rerun without duplicating the core proposal, missions, inventory allocations, or redemption code.
- Verification coverage: `tests/Feature/Demo/StressDemoCommandTest.php`.
