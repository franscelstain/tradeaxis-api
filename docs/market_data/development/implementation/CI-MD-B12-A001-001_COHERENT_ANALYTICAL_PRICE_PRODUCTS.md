# CI-MD-B12-A001-001 — Coherent Analytical Price Products

## Identity

- Stage: `MD-B12`
- Attempt: `MD-B12-A001`
- Baseline: `MD-B12-A001-BL001`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260823-001`
- Predecessor closure: `SC-MD-B11-A001-001`
- Status: `ISSUED — IMPLEMENTATION / PROOF IN PROGRESS`

## Stage-entry normalization

Current B12 traceability was re-derived before material executable mutation. The current B12 surface is:

- `45` mandatory predicates;
- `78` reference/context rows;
- `0` conditional/applicability-pending rows;
- `0` `MANDATORY_OR_CONDITIONAL` rows;
- `0` current B12 predicate evidence bindings.

Stage entry corrected two kinds of migration debt without changing strategy bytes:

1. one status-only line (`MD-S012-R0001`) is reference context rather than an executable predicate;
2. six previously `REFERENCE_ONLY` members of mixed price-product lists are direct B12 obligations and are now mandatory (`MD-S012-R0002..R0005`, `MD-S083-R0003..R0004`).

The remaining transitional B12 predicates are normalized to mandatory because each obligation exists for the current product engine; conditional branches inside those predicates remain mandatory fail-closed behavior rather than conditional applicability.

`MD-DEP-0004` is discharged for B12 only and remains `OPEN_NON_BLOCKING` globally at `268 / 8` unopened downstream stages.

## Strategy scope affected

B12 implementation/proof is bounded by current rules owned by:

- `MD-S008` — canonical `RAW` versus analytical-product layer separation;
- `MD-S012` — selected analytical defaults, one-basis-per-run persistence, coherence, consumer boundary and capability limitation;
- `MD-S019` — deterministic one-product-per-run invariant for Weekly Swing technical indicators;
- `MD-S020` — semantic correctness and stable price/unit basis as a market-data readiness prerequisite;
- `MD-S083` — coherent `RAW` / `STRUCTURAL_ADJUSTED` / `TOTAL_RETURN` product separation, factor lineage, action-specific transformation mechanics, deterministic output and fail-closed capability boundary.

Strategy meaning changed: **NO**.

## Executable impact surface

Expected/allowed material B12 changes include:

- centralizing analytical price-product construction so indicator computation cannot maintain a separate close/OHLC adjustment implementation;
- preserving immutable `RAW` values while producing a separately identified `STRUCTURAL_ADJUSTED` vector;
- explicit `TOTAL_RETURN` product selection and fail-closed construction when verified distribution/reference terms are unavailable;
- coherent OHLC transformation with previous-close consumption on the same basis;
- action-specific volume transformation only when a verified volume factor exists; absence of a volume factor must mean "no proven volume transformation", never multiply volume by zero or infer an inverse ratio;
- deterministic analytical product identity/version, factor-set identity, formula/config identity, analytical as-of date and content hash;
- explicit contamination/unavailability when factor provenance, factor revision coverage, or total-return distribution inputs are unresolved;
- post-run verifiability for non-empty and zero-row analytical publications;
- publication/readability guards preventing unidentified or mixed-basis analytical rows from becoming readable;
- positive and negative/fail-closed tests plus stage-scoped proof tooling.

## Database/schema impact

Existing V2 analytical identity columns and factor-set tables are the starting foundation. Additive persistence is permitted only if current rows cannot otherwise store the B12 identity/provenance required by authority. No migration may rewrite canonical `RAW`, sealed publication history, factor revisions used by sealed publications, or historical analytical values merely to attach metadata.

## Runtime / provider / backfill impact

- No provider field, including Yahoo `adj_close`, becomes a platform analytical fallback.
- `STRUCTURAL_ADJUSTED` consumes only current publication-bound verified factor revisions selected by B11 foundations.
- `TOTAL_RETURN` must remain unavailable/contaminated when its verified distribution/reference inputs are incomplete; price discontinuity is not a substitute.
- Existing historical rows with incomplete analytical identity are not silently relabelled; correction/recompute ownership remains with the governed publication lifecycle.
- No broad `storage/**` scan is required. Runtime artifacts, if needed, will be admitted only through current B12 governed evidence after local execution.

## Tests / tooling / evidence impact

B12 requires:

- targeted unit/integration tests for product selection, coherent OHLC/volume mechanics, one-basis persistence, deterministic hashes and fail-closed paths;
- static guards proving provider `adj_close` cannot enter the analytical product path and RAW persistence is not rewritten;
- stage-scoped traceability specification/gate;
- stage-scoped proof specification/readiness gate;
- atomic binder with validate-only mode;
- fail-closed mutation/self-test;
- final documentation, classification, relationship and current-state checks.

A test file or source inspection is not execution evidence. B12 predicates remain `NOT_ASSESSED` until current proof is returned and admitted.

## Compatibility / residue risk

Legacy indicator code already contains an in-memory structural adjustment path and B11 factor-set foundation. That work is `EXISTING_UNVERIFIED` for B12 and must be preserved where conformant rather than rewritten for appearance.

Known risk areas at attempt entry:

- price-product construction is private to `IndicatorVectorService`, so no single reusable B12 product boundary exists yet;
- the current loop multiplies `volume_factor` directly, which is unsafe when verified event semantics do not supply a volume factor;
- `TOTAL_RETURN` exists in vocabulary/config but has no executable construction path;
- current publication identity strongly assumes `STRUCTURAL_ADJUSTED`, so generic analytical product construction and indicator-default enforcement must remain distinct concepts;
- current proof does not yet establish exact factor-coverage semantics or deterministic analytical product hashes for the full B12 contract.

These are candidate implementation gaps under current authority, not strategy defects.

## Dependency / relationship impact

- predecessor closure `SC-MD-B11-A001-001` is a stage precondition and supplies verified event/factor foundations only through explicit current implementation use; it contributes no inherited B12 predicate proof;
- `MD-DEP-0004` B12 entry obligation is complete; global dependency remains open downstream;
- no B12 blocking dependency exists at attempt opening.

## Closure boundary

B12 cannot close until all `45` mandatory predicates are proven under `MD-B12-A001-BL001`, runtime artifacts required by the final proof plan are admitted, residue is conformant or explicitly qualified, relationships are complete, and post-binding traceability/proof/classification/documentation/relationship/current-state gates pass.


## Pre-local-proof synchronization

The material B12 implementation is complete for the current work unit and is frozen for local proof. Current executable changes centralize analytical product construction, introduce `structural_adjusted_v2` / `structural_factor_product_v2`, preserve `raw_eod_v1`, keep `TOTAL_RETURN` explicitly unavailable until a governed distribution formula exists, enforce exact verified factor revision lineage, require accepted source-observation payload hashes for adjustment authority, exclude future factors/bars from earlier as-of products, and apply action-specific volume mechanics without inferred generic inverse factors.

No database migration was required. Historical sealed `structural_adjusted_v1` rows are not relabelled or rewritten.

Current pre-runtime proof state is intentionally unbound: `0/45` B12 mandatory predicates are `SATISFIED`; all 45 remain `NOT_ASSESSED` pending returned local execution evidence. The B12 proof plan maps exactly `45/45` mandatory predicates across `8` proof families. Traceability gate, static analytical-product gate, proof-readiness gate, validate-only binder and proof mutation/self-test must all pass in the final non-local cycle before handoff.

Local runtime proof remains required before evidence issuance, atomic binding or stage closure.

## Returned local proof cycle R1 — test/proof remediation

The returned `LOCAL-B12-A001.zip` archive was read from its seven actual files; archive SHA-256 is `DA3870C08A31C26A991D7A7225F01E092EC0F899C9817FCDD35F6C9E7FD1A697`. The bundled per-file SHA-256 ledger matches all six returned proof/state files exactly.

R1 results are admitted only as execution facts, not as semantic authority:

- `LOCAL-B12-A001-000`: `WRONG_COMMAND_OR_SCOPE`. The preflight hard-coded predecessor-era Git HEAD `b3e8b6c772b38dcab96d09e96b17b8b7bcf67ffc`, while the tested repository reported HEAD `5ca9829b7ec40b1209a39d42711825ef456c1661`. The returned status/diff nevertheless reconstruct the expected B12 pre-proof patch exactly enough for proof diagnosis: 22 tracked modified paths and 11 expected untracked B12 files, with no unexpected test side-effect path. Git HEAD equality was therefore an invalid proof condition and is removed from the corrected preflight.
- `LOCAL-B12-A001-001`: `FAIL` — `45` tests, `137` assertions, `3` failures, exit `1`.
- `LOCAL-B12-A001-002`: `PASS` — `1` test, `44` assertions, exit `0`. This pipeline/publication proof remains valid because the R1 remediation changes tests/control notes only and does not change application/runtime code, schema, configuration, or publication semantics.
- `LOCAL-B12-A001-003`: `FAIL` — `1891` tests, `17894` assertions, `1` error, `3` failures, exit `2`.

The three failures share stale test semantics rather than an executable product defect:

1. `PRIVATE_PLACEMENT` was asserted as a volume-rescaling action. Current locked `Corporate_Action_Type_Registry_LOCKED.md` classifies it `price_continuity_impact=NONE` and `volume_continuity_impact=NONE`; share-count change alone is not authority to invent historical traded-volume rescaling.
2. Two direct coherent-product tests supplied a historical bar and a later factor but no analytical as-of date. Under the locked formula `B < ex_date <= D`, that factor is correctly future knowledge for the inferred earlier `D` and must not be applied. The helper is corrected to declare an as-of at the verified factor date.
3. `StageEightGovernanceBindingTest` created `AUTHORITATIVE_VERIFIED` revisions without the source observation/reference/hash now required by the locked factor provenance model. The fixture is corrected to seed an accepted immutable source observation with canonical payload hash and bind the revision to it.

The R1 application/runtime implementation is unchanged by this remediation. Strategy authority, governance authority, migrations, immutable Baseline Lock, denominator (`45`), reference/context count (`78`), applicability, proof-family ownership, relationships and B11 closure are unchanged. Runtime-dependent B12 traceability remains `0/45 SATISFIED` until corrected proof returns.

Corrected local proof is impact-scoped: run a content/state preflight that does not require Git HEAD equality, rerun the original B12 targeted proof plus the repaired StageEight factor-provenance fixture, retain `LOCAL-B12-A001-002` as `REMAINS_VALID`, then run the full suite last. No pipeline/publication rerun is required unless later remediation touches an application/runtime surface.

## Returned local proof cycle R2 — residual fixture remediation

The returned `LOCAL-B12-A001-R2.zip` archive was read from all six actual files. Its bundled SHA-256 ledger matches all five referenced proof/state files exactly.

R2 execution facts:

- `LOCAL-B12-A001-R2-000`: `PASS` — baseline/CI present and all three R1-remediated test hashes matched, exit `0`.
- `LOCAL-B12-A001-R2-001`: `FAIL` — `48` tests, `159` assertions, `1` error, exit `2`.
- cumulative R1 `LOCAL-B12-A001-002`: `REMAINS_VALID` — `1` test, `44` assertions, exit `0`.
- `LOCAL-B12-A001-R2-003`: `FAIL` — `1891` tests, `17906` assertions, `1` error, exit `2`.

Both R2 failures are the same residual direct-fixture defect in `CoherentPriceProductBoundaryTest::test_a_factor_that_changes_nothing_keeps_the_selected_product`. The R1 helper remediation set analytical as-of `D` to the latest factor ex-date only. That is sufficient when bars precede the factor, but this test intentionally supplies a bar after the factor (`bar=2026-05-10`, `ex_date=2026-04-20`). The helper therefore constructed `D=2026-04-20`, making the `2026-05-10` input bar legitimately fail closed as `ANALYTICAL_BAR_AFTER_AS_OF`.

Current locked semantics remain unchanged: for bar date `B`, apply verified factors satisfying `B < ex_date <= D`, and no analytical input bar may be later than `D`. The fixture helper is corrected so direct analytical tests choose `D` as the latest date across both supplied bar dates and verified factor dates. This models an analytical evaluation date that is not earlier than any supplied input while preserving the same future-knowledge exclusion rule.

No application/runtime, strategy, governance authority, schema, migration, configuration, denominator, applicability, proof-family ownership, relationship, predecessor-closure, or factor semantics are changed by this R2 remediation. The R2 preflight is invalidated only as current-patch admission because it fingerprints the now-corrected fixture file; it is replaced by an R3 content preflight. R1 `LOCAL-B12-A001-002` remains valid. R2 targeted/full-suite failures must be rerun after the fixture-only remediation. Runtime-dependent B12 traceability remains `0/45 SATISFIED` until corrected proof returns.



## Returned local proof cycle R3 — terminal proof admission

The returned `LOCAL-B12-A001-R3.zip` archive was read from all five actual files. Its bundled SHA-256 ledger matches all four referenced proof/state artifacts exactly. The requested `LOCAL-B12-A001-R3-000_preflight.txt` file is absent and is therefore classified `INCOMPLETE`, not PASS. That auxiliary preflight is non-blocking for terminal admission because it owns no B12 semantic predicate or runtime proof family: the six hashes it was intended to check were independently reproduced from the source repository plus the issued pre-local/R1/R2 patches, and the returned post-test status/diff matches that same cumulative repository state.

Terminal execution facts:

- cumulative R1 `LOCAL-B12-A001-002`: `REMAINS_VALID` — pipeline/publication integration **1 test / 44 assertions**, zero failures/errors, exit `0`; R1/R2 remediation changed no application/runtime/publication/schema/config surface.
- `LOCAL-B12-A001-R3-001`: `PASS` — corrected targeted B12 runtime/fail-closed proof **48 tests / 161 assertions**, zero failures/errors, exit `0`.
- `LOCAL-B12-A001-R3-003`: `PASS` — full PHPUnit regression **1891 tests / 17908 assertions**, zero failures/errors, exit `0`.
- returned R3 repository status/diff: cumulative patch lineage reconstructible, no unexpected tracked test side effect.

No further executable remediation is required. The local proof satisfies the existing 45-predicate / 8-family plan. `E-MD-B12-A001-001` admits the cumulative proof and the atomic binder binds exactly **45/45** mandatory predicates. Post-binding traceability/proof/classification/documentation/relationship/current-state controls must pass before `SC-MD-B12-A001-001` is terminal. Strategy meaning, schema, migrations, denominator, applicability and predecessor closure remain unchanged.
