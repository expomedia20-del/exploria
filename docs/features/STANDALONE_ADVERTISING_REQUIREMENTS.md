# Standalone Advertising Requirements

## Purpose

This document records the product requirements that must not be lost during the transition from mock demo to real development.

The current `/demo/ecosystem` page shows standalone advertising as mock front-end data. The real product must support advertising content outside campaign missions, for member shops, hub sub-units, non-member brands, and sponsors.

## Actors

- Member shop or venue partner
- Sub-unit inside a hub or ravaq
- Ravaq or hub manager
- Platform admin
- External non-member brand
- Sponsor

## Required Capabilities

- Each member shop must be able to submit advertising content independently of a mission campaign.
- Each sub-unit inside a hub/ravaq must be able to submit advertising content under its parent hub permissions.
- Non-member brands must be able to request advertising placements, subject to stricter admin review.
- Sponsors must be able to request named placements, sponsored routes, sponsored treasures, sponsored rewards, or display inventory.
- Ad submissions must support content upload, including image/video assets, title, body copy, CTA text, target URL or in-app destination, and validity dates.
- The approval workflow must support ravaq manager review and platform admin review.
- Admins must be able to approve, reject, request edits, schedule, pause, or archive ads.
- Ads must be assignable to fixed displays, mobile displays, QR landing pages, reward pages, map/route pages, and post-mission moments.
- Fixed and mobile display inventory must be modeled separately so each display can have location, hub, status, supported media formats, and schedule slots.
- Ads must support scheduling by date, time window, hub, touchpoint, audience segment, and priority.
- Ads must support budget, impression cap, click/interaction cap, sponsor package, and billing status.
- The system must track impressions, clicks/interactions, attributed visits, attributed purchases where available, and revenue.
- The system must preserve moderation history, reviewer, timestamps, uploaded asset versions, and final published creative.

## Demo Coverage

The current demo covers this conceptually in `/demo/ecosystem`:

- Standalone ads outside campaigns
- Member shop ad request
- Non-member brand ad request
- Sponsor request
- Ravaq/admin approval states
- Display placement table
- Ad performance report

## Not Yet Implemented

- Real database schema for ads, creatives, display inventory, schedules, and approvals
- Real file upload and media validation
- Real approval workflow
- Real publishing to fixed or mobile displays
- Real tracking events for impressions/clicks/display playback
- Real billing, sponsor packages, or partner invoices

## Suggested Implementation Order

1. Model advertisers, ad requests, creatives, placements, displays, schedules, approvals, and metrics.
2. Build upload flow for image/video creatives with validation and preview.
3. Build partner/shop ad submission panel.
4. Build ravaq manager approval queue.
5. Build platform admin moderation and scheduling panel.
6. Build display inventory management for fixed and mobile screens.
7. Add publishing API for display clients.
8. Add impression, interaction, playback, and attribution tracking.
9. Add reporting and billing exports.
