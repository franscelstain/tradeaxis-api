# Change Impact Declaration — `MD-B09-A001`

- ID: `CI-MD-B09-A001-001`
- Stage / Attempt / Baseline / Epoch: `MD-B09` / `MD-B09-A001` / `MD-B09-A001-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260822-001`
- Starting committed revision supplied by user: `b3e8b6c772b38dcab96d09e96b17b8b7bcf67ffc`
- Current governed predecessor: `SC-MD-B08-A001-001`
- Dependency: `MD-DEP-0004` per-stage applicability/ownership/classification normalization
- Status: `TERMINATED_NON_PASS — SUCCESSOR_REBASELINE_REQUIRED`
- Strategy meaning change: `NO within A001`; authority changed afterward through separately authorised `DOC-CHG-20260823-001`, so A001 cannot close under its predecessor freeze.

## Objective

Revalidate and remediate only where current authority requires the import-only canonical `RAW` boundary: provider-neutral canonicalization, stable listing/date identity, OHLCV validity, immutable provenance, invalid-row separation, deterministic duplicate/conflict handling, import-only candidate persistence, and explicit separation from readability/promotion. Existing implementation is `EXISTING_UNVERIFIED`.

## Stage-entry normalization result

- final current B09 mandatory denominator: **139**;
- B09 optional capabilities: **12**;
- moved to actual downstream proof owners: **46**;
- pure reference/context rows in the four B09-normalized authority documents: **28**;
- recovered missing semantic extraction rows: `MD-S008-R0087` (`volume`) and `MD-S036-R0033` (`promote`);
- transitional/conditional-pending applicability in B09: **0**;
- B09 mixed-classification entry debt: **0**;
- remaining `MD-DEP-0004` backlog: **439 members across 11 unopened stages**.

No predicate inherits PASS from MD-B08. All 139 mandatory B09 predicates remain `NOT_ASSESSED` until fresh proof is returned and admitted.

## Authority and implementation impact

- Strategy: no byte change authorized. Any genuine contradiction stops at the authority boundary.
- Canonical identity: prove logical canonical identity by stable `listing_id + trade_date`; `ticker_id` remains compatibility-only.
- Required canonical fields: prove OHLCV, RAW basis, immutable observation/provenance, source/mapping, run/publication/revision context, and source/platform timestamps without fabrication.
- Validation: positive OHLC, coherent high/low, integral non-negative volume, completed session, temporal identity, provenance/config references, and the explicit zero-volume-with-price-movement invalid rule.
- Missing/invalid: never fabricate zero bars; rejected/invalid rows remain outside canonical content with governed reason/evidence.
- Duplicate/conflict: equivalent duplicates are deterministic and traceable; conflicting same-listing/date rows fail closed; acquisition timestamp is never latest-wins authority.
- Economic preservation: no forward fill, interpolation, scale repair, automatic corporate-action adjustment, adjusted-close RAW fallback, or relabelled derived actual traded value.
- Import-only lifecycle: candidate/import artifacts may be written, but current pointer/readability/published correction/promote-side effects are forbidden. Successful import-only work closes as completed/not-readable/not-promoted with governed status.
- Configuration: every run/output must remain bound to exact immutable config snapshot/hash; no direct canonical write may bypass that requirement.
- Tests/tooling: current tests are not inherited proof. Add/repair executable tests and B09 traceability/proof tooling only for actual current semantics.
- Storage: no broad `storage/**` scan. No raw artifact is currently required for B09 stage-entry or static proof.
- Predecessor: MD-B08 is a valid precondition only; immutable predecessor evidence/closure will not be edited.

## Closure boundary

MD-B09 may close only after the 139 mandatory predicates have fresh defensible proof, B09 transitional/mixed debt remains zero, implementation and negative/fail-closed paths conform, no harmful residue remains, current evidence and relationships are issued, exact binding passes, required integrity gates pass, and current full-suite proof satisfies governance. MD-B10 must remain unopened until then.

## Authority blocker discovered before material application mutation

Targeted inspection of the current B09 validator against `MD-S023-R0044` found that the required zero-volume-with-intra-session-movement rejection has no dedicated code in the locked canonical Reason Codes Registry. Reusing another BAR code or emitting an unregistered code would invent semantics. `F-MD-B09-A001-001` / `MD-DEP-0008` therefore blocks material B09 implementation before any application/runtime change. `E-MD-B09-A001-001` records static proof only and binds zero of the 139 runtime-dependent predicates.

## Reviewed decision status

`D-MD-20260823-01` resolves the semantic choice in favor of a dedicated `BAR_ZERO_VOLUME_PRICE_MOVEMENT` code without weakening `MD-S023-R0044`. Explicit user authorization was subsequently received and applied through `DOC-CHG-20260823-001` / successor freeze `MD-STRATEGY-FREEZE-20260823-001`. A001 is intentionally not resumed after that freeze change; additive evidence `E-MD-B09-A001-002` records its `PARTIAL_REBASELINE_REQUIRED` disposition and execution continues only in `MD-B09-A002`.
