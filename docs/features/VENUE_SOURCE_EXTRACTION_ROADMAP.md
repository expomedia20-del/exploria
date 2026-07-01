# Venue Source Extraction Roadmap

Status: Post-MVP development candidate

## Purpose

The MVP venue profile keeps the official source URL and supports manual entry of facilities and attractions. The next development version should add a controlled extraction flow:

1. Admin registers an official venue source URL.
2. System fetches the public page through a safe backend service.
3. System extracts candidate facilities, attractions, services, and constraints.
4. Admin reviews, edits, rejects, or accepts each candidate.
5. Accepted items are merged into the venue profile without duplicates.

## MVP Boundary

Automatic crawling and AI extraction are intentionally outside the current MVP. The MVP keeps:

- Source URL registration.
- Manual fast entry.
- Source-based curated suggestions for the Abbasabad Ecopark pilot.
- Admin review before any item becomes operational data.

## Development Requirements

- URL allowlist or safe URL validation.
- Request timeout, redirect limit, and response size limit.
- HTML parsing and text extraction in Laravel service/action.
- Audit trail for extracted candidates and admin decisions.
- Duplicate detection against current venue facilities.
- Error, empty, and pending states in the Persian RTL admin UI.
- Tests for successful extraction, invalid URLs, duplicate handling, and authorization.

## Acceptance Criteria

- Given a valid official venue URL, the admin can request extraction and see a candidate list.
- No candidate is saved to `location_profile.facilities` before admin approval.
- The system shows clear empty/error states when the source has no usable content or cannot be fetched.
- Existing manually entered facilities are preserved.
