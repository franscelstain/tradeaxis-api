# Change Impact Declaration — `MD-B09-A002`

- ID: `CI-MD-B09-A002-001`
- Stage / Attempt / Baseline / Epoch: `MD-B09` / `MD-B09-A002` / `MD-B09-A002-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260823-001`
- Predecessor attempt: `MD-B09-A001` — `PARTIAL_REBASELINE_REQUIRED`, immutable partial evidence retained
- Predecessor stage closure: `SC-MD-B08-A001-001`
- Reviewed strategy decision: `D-MD-20260823-01`
- Controlled revision: `DOC-CHG-20260823-001`
- Dependencies: `MD-DEP-0008 RESOLVED` authority blocker; `MD-DEP-0004 OPEN_NON_BLOCKING` with the B09 entry obligation complete
- Status: `EXECUTED — COMPLETE — CLOSED BY SC-MD-B09-A002-001`
- Strategy meaning change within A002: `NO`; the separately authorised strategy correction was completed before this attempt baseline.

## Objective

Revalidate and remediate the current MD-B09 import-only canonical `RAW` boundary under the successor strategy freeze. Existing implementation is `EXISTING_UNVERIFIED`. The attempt must prove canonical OHLCV validity, temporal/provenance binding, invalid-row separation, deterministic dedup/conflict handling, import-only candidate persistence, and no promotion/readability side effects.

## Controlled-correction impact

`DOC-CHG-20260823-001` adds one structural/reference vocabulary row, `BAR_ZERO_VOLUME_PRICE_MOVEMENT`, to `MD-S085`. The behavioral owner remains mandatory `MD-S023-R0044`; the correction therefore does **not** enlarge the B09 behavioral denominator.

Current normalized B09 state:

- mandatory denominator: **139**;
- optional capabilities: **12**;
- primary-stage reference-only rows after the additive registry definition: **30**;
- moved to downstream proof owners: **46**;
- transitional/conditional-pending rows: **0**;
- mixed-classification debt: **0**;
- global `MD-DEP-0004` remainder: **439 members across 11 unopened stages**.

All 139 mandatory predicates remain `NOT_ASSESSED` until current A002 runtime proof is returned and admitted.

## Material executable impact

- Runtime validator: `EodBarsIngestService` must reject a coherent positive-price row when `volume = 0` and OHLC are not all identical, persist `BAR_ZERO_VOLUME_PRICE_MOVEMENT`, and never write that row as canonical. Flat positive zero-volume OHLC remains valid.
- Source observation boundary: Yahoo/provider row validation must classify the same contradiction as rejected observation evidence, using the same canonical reason code, without bypassing the B07 immutable observation model.
- Reason dictionary: `Reason_Codes_Seed.sql` must contain the authorised code and remain exactly synchronized with the locked registry; no existing code changes meaning.
- Tests: add positive flat-zero-volume proof, negative moving-zero-volume proof, provider-rejection proof, seed/registry/runtime registration proof, and preserve existing invalid OHLC/negative-volume semantics.
- Proof tooling: create B09 traceability/proof spec, readiness/bound gate, binder, and mutation self-test over all 139 mandatory predicates. Runtime-dependent rows remain pending before local execution.
- Affected predecessor verification: B03's exhaustive reason-seed execution invariant is affected by the additive vocabulary. Revalidate that invariant in A002 through current `ReasonCodeSeedExecutionTest`; B03 is not reopened unless that proof fails or exposes a separate predecessor defect.
- Schema/migrations: **none expected**; the existing `eod_reason_codes` table and invalid-bar schema already support the new stable code.
- Storage/raw artifacts: no raw `storage/**` artifact is required by the non-local work. Local runtime output will be returned as external/manual proof and admitted through current governed evidence if closure is reached.

## Compatibility / residue risk

- Do not broaden zero-volume rejection: `volume = 0` with `open = high = low = close > 0` remains admissible.
- Preserve reason precedence for unrelated defects: malformed/missing/non-positive/negative-volume/OHLC-order violations keep their existing reason semantics.
- Do not introduce an application-only reason code, second dictionary, or parallel provenance source.
- No publication/current-pointer/promotion behavior may be introduced by B09 remediation.

## Closure boundary

A002 may close MD-B09 only after all 139 mandatory predicates have fresh current proof, the reason dictionary is current and executable, negative/fail-closed behavior passes, full-suite proof is green, no harmful residue remains, exact binding is completed, governed evidence/relationships are issued, and documentation/relationship/traceability gates pass. MD-B10 remains unopened.

## Non-local implementation completion

The bounded executable remediation is complete for local proof handoff. `EodBarsIngestService` and `PublicApiEodBarsAdapter` reject zero-volume price movement with `BAR_ZERO_VOLUME_PRICE_MOVEMENT`, while flat positive zero-volume OHLC remains admissible and existing OHLC-order/negative-volume precedence is preserved. The reason seed is synchronized to the successor locked registry. Current B09 proof tooling maps the normalized 139-predicate denominator, remains pre-binding with all runtime-dependent rows `NOT_ASSESSED`, and includes mutation fail-closed checks for the new reason across registry/seed/runtime/test surfaces. No schema or migration change was required.

Current residue state before local execution: `IMPLEMENTATION_REMEDIATED_STATICALLY; RUNTIME_PROOF_PENDING`.


## Returned local proof cycle R1 — remediation

The first A002 local proof cycle did not close the attempt. `LOCAL-B09-A002-001`, `002`, and `003` passed and remain valid. `LOCAL-B09-A002-004` failed because the tested repository physically lacked the already-issued immutable B08 evidence/closure documents `E-MD-B08-A001-001` and `SC-MD-B08-A001-001` while current registries correctly referenced them. The remediation restores those exact immutable predecessor artifacts; it does not rewrite them or reopen B08.

The full suite executed 1838 tests / 17454 assertions and reported one error plus three failures. Root-cause review found no defect in the new B09 zero-volume implementation: targeted B09 canonical/import proof was green. The remaining failures were affected-test expectations/fixtures:

- the controlled `MD-S085` registry addition increased the strategy corpus from 463 to 464 rows, so the B04 traceability spec's exact corpus count required revalidation; the B04 behavioral denominator remains unchanged;
- benchmark and sector-index test fixtures used moving OHLC with `volume = 0`. Current authority treats acquired sector index bars under the same validation rules as other bars, and the generic benchmark adapter uses the same canonical row validator. Those fixtures are changed to positive integral volume so the tests continue to prove symbol/command behavior rather than intentionally violating `MD-S023-R0044`.

Affected predecessor proof analysis therefore expands from B03 reason-seed revalidation to include the B04 exact strategy-corpus-count guard. No B04 behavioral predicate is reclassified or reopened; corrected local proof must show the current B04 gate remains green under the successor strategy freeze.

No A002 application/runtime code is changed by this remediation. The successful deployed reason dictionary, canonical RAW/fail-closed, and import/provenance local proofs remain valid. Corrected local proof is limited to the affected regression tests, control-plane integrity, and the full suite.

## Returned local proof cycle R1 correction — residual stale assertion

The corrected R1 local cycle narrowed the remaining closure blocker to one stale PHPUnit assertion. `LOCAL-B09-A002-R1-002` passed the current B09/B04/classification/documentation/relationship control plane with B04 counts `114 mandatory / 181 moved / 639 reference` and deterministic CURRENT_STATE. Benchmark and sector-index regression tests also passed.

`LOCAL-B09-A002-R1-001` failed only `ConfigFoundationTraceabilityGateTest::test_current_b04_normalization_is_exact`: the executable B04 gate correctly returned `reference=639`, while that PHPUnit assertion still hard-coded the pre-authorised-registry value `638`. `LOCAL-B09-A002-R1-003` executed 1838 tests / 17475 assertions and failed only the same assertion. This is a stale test expectation left out of R1, not a strategy, implementation, applicability, or control-plane defect.

R2 changes only that exact assertion from `638` to `639`. No application/runtime, strategy authority, governance authority, migration, proof-map, denominator, or relationship semantics change. The previous A002 reason-dictionary, canonical RAW/fail-closed, import/provenance proofs remain valid. The R1 control-plane PASS is superseded by a fresh non-local static revalidation after this test-only correction; no additional local control-plane rerun is required. Final corrected local proof is limited to the affected B04 PHPUnit regression and the full suite.



## Returned local proof cycle R2 — final corrected proof

`LOCAL-B09-A002-R2-001` passed the only remaining affected B04 regression at 4 tests / 9 assertions with exit 0. `LOCAL-B09-A002-R2-002` passed the complete PHPUnit suite at 1838 tests / 17475 assertions with zero failures, errors or skips and exit 0. The returned repository status/diff matches the expected accumulated A001/A002/R1/R2 patch lineage and contains no unexpected test-created tracked mutation.

Together with the still-valid A002 deployed reason-dictionary proof (`LOCAL-B09-A002-001`), canonical RAW/fail-closed proof (`LOCAL-B09-A002-002`), import/provenance proof (`LOCAL-B09-A002-003`) and R1 control-plane proof, the current A002 runtime chain is complete. `E-MD-B09-A002-001` admits those actual returned artifacts. No runtime-dependent predicate is promoted from expectation alone; all 139 predicates are eligible for one atomic evidence binding only after the governed evidence record exists.

Affected predecessor revalidation is complete without reopening predecessor stages: B03 reason-seed execution/deployed dictionary proof is green and B04 exact strategy-corpus normalization is green at 114 mandatory / 181 moved / 639 reference. No harmful B09 executable residue remains.


## Final evidence binding and closure

`E-MD-B09-A002-001` admits the complete current A002 execution chain. The atomic binder promoted exactly 139 current B09 mandatory predicates to `SATISFIED` with `current_evidence_ids=E-MD-B09-A002-001`; the exact bound proof gate passes at 139/139 with zero errors. Post-binding/current-state controls are recorded by `SC-MD-B09-A002-001`. B09 residue is `CONFORMANT_NO_HARMFUL_RESIDUE_FOUND_IN_THE_B09_SURFACE`. `MD-DEP-0004` remains open non-blocking at 439/11 for unopened downstream stages; no B09 blocker remains. MD-B10 is not opened by this closure work unit.

Final post-binding controls: documentation integrity PASS at 897/897/897/897 with 91 frozen strategy documents and zero mismatches; relationship integrity PASS at 128 records / 213 relationships with zero validity/completeness gaps; B09 bound proof and mutation self-test PASS; classification remains 439/11; CURRENT_STATE generation is deterministic at SHA-256 `C9016824FA2134B0B51ABAF29E788EF116360DD39D5F425195F3184839E5AAC4`.
