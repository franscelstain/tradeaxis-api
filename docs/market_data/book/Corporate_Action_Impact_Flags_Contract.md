# Corporate Action Impact Flags Contract (LOCKED)

## Purpose

Define publication-bound event-risk and contamination facts without turning corporate actions into trading signals.

## Required fields

For each affected instrument/date, flags must expose:

- corporate-action/event revision identity
- action type and verification state
- primary `ex_date` or explicit verified effective anchor
- affected/contamination window and calculation rule
- price- and volume-continuity states
- adjustment/factor revision if applied
- unresolved/missing-factor state
- reason codes and source evidence reference
- publication/config identity

## Window rules (LOCKED)

- Price continuity and event-risk windows anchor on verified `ex_date` by default.
- A detected break may add a conservative contamination anchor, but it cannot replace event identity or verify a factor.
- `action_date` is not a silent fallback for ex-date.
- Unknown anchor/terms produce explicit unresolved contamination, not a fabricated clean flag.
- Late event revisions recompute every dependent date through correction/republication.

## Flag semantics

- Event-day/window flags are factual context, not buy/sell, ranking, or avoidance recommendations.
- An adjustment-active verified event may restore arithmetic continuity while remaining disclosed as event context.
- An unresolved event or break blocks affected eligibility/indicator validity under governed reasons.
- Absence of an event row means no evidence, not proof of no risk.

## Forbidden behavior

- exact-date-only `action_date` logic when ex-date/window semantics are required
- clearing a break because a nearby unverified event exists
- emitting `clean` when verification/factor/anchor is unknown
- mutating prior flags bound to a sealed publication

## Acceptance criterion

For any blocked or released row, the platform can explain the event revision, anchor, factor/verification state, affected window, reason, and publication that produced the flag.
