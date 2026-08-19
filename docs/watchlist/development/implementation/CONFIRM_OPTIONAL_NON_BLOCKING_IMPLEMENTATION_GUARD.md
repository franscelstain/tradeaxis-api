# Weekly Swing Implementation Guard — Optional Non-Blocking CONFIRM

> **Current Conformance:** `NOT_ASSESSED_REVALIDATION_REQUIRED`  
> **Verification Epoch:** `WS-REBASELINE-20260819-001`  
> Existing content is a revalidation input. Historical PASS/READY/DONE wording does not grant current conformance.


## Status

This is a **current implementation-alignment guard** derived from canonical strategy `../../authority/strategy/WS_D1_CONFIRM_ACTIONABILITY.md` and `../../authority/strategy/WS_END_TO_END_STRATEGY_LIFECYCLE.md`.

Until all older technical contracts/examples are fully aligned, this guard overrides any implementation guidance that makes CONFIRM mandatory, allows CONFIRM to change recommendation membership/rank, or treats missing CONFIRM data as a core run failure.

## Core Completion Contract

Minimum successful core runtime output is:

`PLAN → RECOMMENDATION/TOP PICKS`

CONFIRM artifact/output is **not required** for:
- PLAN success;
- RECOMMENDATION/Top Picks success;
- publishing/reading EOD Top Picks;
- historical evaluation / IS / OOS / friction proof;
- core forward-shadow proof;
- core production-use review.

## CONFIRM Trigger Contract

CONFIRM may run only for a final Top Pick and may be triggered when:
- a consumer explicitly requests current actionability; or
- an optional scheduled/current-data process has valid decision-time input.

No CONFIRM request is a valid state.

## Missing-Data Contract

If current-entry data is missing, delayed, stale, incomplete, or temporarily unavailable:

- do not fail PLAN/RECOMMENDATION;
- do not remove/reorder Top Picks;
- do not emit business `NOT_ACTIONABLE`;
- return/record `UNAVAILABLE_RETRYABLE` when an evaluation attempt exists;
- allow reevaluation when valid data arrives before entry-window expiry.

If the entry window expires without valid evaluation, use `EXPIRED_UNCONFIRMED` semantics. This is not a core run failure.

## Evaluated States

- `NOT_REQUESTED`
- `UNAVAILABLE_RETRYABLE`
- `ACTIONABLE`
- `NOT_ACTIONABLE`
- `EXPIRED_UNCONFIRMED`

`NOT_ACTIONABLE` requires valid input and an actually evaluated failed actionability gate.

## Technical Errors

Schema/persistence/runtime exceptions may be recorded as technical errors, but after Top Picks are valid they must not back-propagate into PLAN/RECOMMENDATION failure. Retry is allowed while the canonical entry window remains open.

## Persistence Rule

CONFIRM persistence is conditional on a CONFIRM attempt/result. Absence of a CONFIRM record is valid and must not make the core artifact set incomplete.

## Test Minimum

Implementation acceptance must include tests proving:

1. PLAN + RECOMMENDATION succeed with no CONFIRM request;
2. missing CONFIRM data produces non-blocking availability state;
3. a later valid snapshot can transition the same Top Pick from `UNAVAILABLE_RETRYABLE` to `ACTIONABLE` or `NOT_ACTIONABLE` within the entry window;
4. CONFIRM technical failure cannot mutate/fail already-valid Top Picks;
5. non-recommended tickers cannot become valid CONFIRM targets;
6. `NOT_ACTIONABLE` cannot be emitted from missing/stale/incomplete data;
7. core proof/readiness does not require CONFIRM evidence.

## Alignment State

Other pre-revision technical semantics remain subject to `STRATEGY_ALIGNMENT_REQUIRED.md`. This guard only closes the optional/non-blocking CONFIRM ambiguity and does not claim full implementation conformance.
