# F-MD-B09-A001-001 — Zero-volume movement rule has no canonical reason code

- Status: `RESOLVED`
- Severity: `P1`
- Stage / Attempt / Baseline / Epoch: `MD-B09` / `MD-B09-A001` / `MD-B09-A001-BL001` / `MD-REBASELINE-20260820-001`
- Blocks: `MD-S023-R0044` and `MD-B09` executable completion/closure
- Dependency: `MD-DEP-0008`
- Strategy bytes changed by this finding: `0`

## Finding

Two current locked strategy authorities cannot yet be implemented together without inventing semantics.

1. `authority/strategy/book/EOD_Bars_Contract.md` rule `MD-S023-R0044` locks zero volume with intra-session price movement as invalid and states that it is rejected **with its own reason code** and never stored as canonical.
2. `authority/strategy/registry/Reason_Codes_Registry.md` owns the canonical vocabulary used by `eod_invalid_bars.invalid_reason_code`, but its locked BAR vocabulary contains `BAR_DUPLICATE_SOURCE_ROW`, `BAR_INVALID_OHLC_ORDER`, `BAR_NON_POSITIVE_PRICE`, `BAR_NEGATIVE_VOLUME`, `BAR_MISSING_REQUIRED_FIELD`, mapping/provenance reasons, and no dedicated code for zero-volume-with-price-movement.

The existing codes are not semantically interchangeable: the condition is not negative volume, missing data, non-positive price, or merely OHLC ordering. Reusing one would violate the registry rule that one code has one meaning. Emitting an unregistered new code would bypass the registry owner.

## Why implementation stops here

The existing `EodBarsIngestService` does not yet enforce rule 10. Adding the cross-field validator is mechanically straightforward, but there is no current-authority terminal reason code that can be persisted for the rejection. This is therefore an authority-completeness blocker, not permission to choose a convenient code in application code or tests.

No application/runtime code has been changed under B09 after the immutable baseline. No strategy byte has been edited. `MD-S023-R0044` remains `NOT_ASSESSED`.

## Required governed resolution

Under `DOCUMENT_CHANGE_POLICY.md` section 2, strategy-byte correction requires all of: finding/rationale, supporting evidence, reviewed decision, explicit user authorization, change-log entry, new strategy freeze identity, and affected-current-verification invalidation/revalidation.

The safe resolution is to make the reason-code owner complete for the already-locked EOD rule (rather than weaken rule 10 or overload another code). Reviewed decision `D-MD-20260823-01` establishes the bounded identifier/semantics `BAR_ZERO_VOLUME_PRICE_MOVEMENT`; explicit user authorization for the strategy-byte correction is still pending.

Because any strategy correction changes the freeze that `MD-B09-A001-BL001` binds, the corrected strategy must be followed by the successor rebaseline/revalidation required by governance. `MD-B09-A001` remains an immutable partial attempt record and must not be rewritten.


## Resolution

Resolved by `D-MD-20260823-01`, explicit user authorization, `DOC-CHG-20260823-001`, and successor freeze `MD-STRATEGY-FREEZE-20260823-001`. A001 remains immutable/non-PASS under the prior freeze; executable remediation continues only in `MD-B09-A002`.
