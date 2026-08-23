# Decision — dedicated reason code for zero-volume price movement

- ID: `D-MD-20260823-01`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline reviewed: `MD-B09` / `MD-B09-A001` / `MD-B09-A001-BL001`
- Finding: `F-MD-B09-A001-001`
- Dependency: `MD-DEP-0008`
- Blocked rule: `MD-S023-R0044`
- Issued: 2026-08-23
- Decision status: `ISSUED — PENDING EXPLICIT USER AUTHORIZATION FOR STRATEGY REVISION`
- Strategy impact if authorised: `CONTROLLED_CORRECTION` limited to one new canonical reason-code row in `MD-S085`

## Question

`MD-S023-R0044` already locks one executable invalid-bar semantic: `volume = 0` with intra-session price movement is invalid, must never become canonical, and must be rejected with its own reason code. `MD-S085` owns the canonical reason-code vocabulary used by `eod_invalid_bars.invalid_reason_code`, but contains no dedicated code for that condition. The review must decide whether an existing BAR code may be overloaded, whether the EOD rule should be weakened, or whether the reason registry is incomplete.

## Authority review

1. `MD-S023-R0044` is specific: the defect is a cross-field contradiction between zero volume and price movement, not a negative-volume, non-positive-price, missing-field, or simple OHLC-order defect.
2. `Reason_Codes_Registry.md` states that one code has one meaning only and owns the canonical invalid-bar vocabulary.
3. `BAR_NEGATIVE_VOLUME`, `BAR_NON_POSITIVE_PRICE`, `BAR_MISSING_REQUIRED_FIELD`, and `BAR_INVALID_OHLC_ORDER` therefore cannot be reused without semantic drift.
4. An application-only unregistered code would bypass the locked registry and break seed/registry/evidence consistency.
5. The EOD semantic itself is unambiguous and should not be weakened: the authority defect is the missing registry representation of an already-locked condition.

## Decision

1. Preserve `MD-S023-R0044` byte-for-byte and preserve its semantic rule.
2. Complete the canonical BAR vocabulary with exactly one dedicated code: `BAR_ZERO_VOLUME_PRICE_MOVEMENT`.
3. The governed semantics are: category `BAR`, severity `HARD`; a source-backed row with `volume = 0` and non-identical `open/high/low/close` is invalid/rejected evidence and is never canonical.
4. No existing reason code changes meaning. No other reason-registry row is authorised by this decision.
5. After an authorised strategy correction, executable seed/registry mirrors, validation code, negative tests, traceability, and current proof must conform to the new frozen vocabulary.
6. `MD-B09-A001-BL001` and `E-MD-B09-A001-001` remain immutable partial records. Because the strategy freeze changes, implementation resumes only through the governance-required successor B09 attempt with a new baseline/CI; this decision does not create that attempt.

## Authorization state

**Explicit user authorization has not yet been given.** Therefore this decision does **not** itself authorize editing `authority/strategy/registry/Reason_Codes_Registry.md`, the freeze manifest, the strategy change log, or executable reason-code seed/runtime surfaces. `MD-DEP-0008` remains `OPEN_BLOCKING`.

The only authorised action after this issued decision is to wait for explicit user approval of the bounded correction above.

## Invalidation / revalidation impact if authorised

- Strategy byte scope: one additive row in `MD-S085` for `BAR_ZERO_VOLUME_PRICE_MOVEMENT`; `MD-S023` remains byte-identical.
- Required change control: `DOCUMENT_CHANGE_LOG.md` entry and successor strategy-freeze identity/manifest.
- Invalidated current B09 attempt for closure: `MD-B09-A001` remains immutable partial evidence tied to the prior freeze.
- Required successor work: new B09 attempt/baseline/CI, actual validator + reason seed/mirror/tests/tooling remediation, fresh runtime proof, traceability binding, residue, governed evidence, integrity gates, and closure.
- Predecessor stages B00-B08 are not automatically reopened; affected-proof analysis must determine whether any current proof explicitly depends on exhaustive BAR vocabulary.

## Scope limit

This decision does not authorize another reason code, does not modify validation thresholds, does not weaken invalid-row separation, does not change publication semantics, and does not enter MD-B10.
