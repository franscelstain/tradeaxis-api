# CI-MD-B11-A001-001 — Verified Corporate-Action Lifecycle and Anomaly-Only Detection

## Identity

- Stage: `MD-B11`
- Attempt: `MD-B11-A001`
- Baseline: `MD-B11-A001-BL001`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260823-001`
- Predecessor closure: `SC-MD-B10-A001-001`
- Status: `ISSUED — LOCAL_CYCLE_1_PARTIAL; R1_REMEDIATION_COMPLETE; CORRECTED_LOCAL_PROOF_PENDING`

## Stage-entry normalization

Current B11 traceability was re-derived before material executable mutation. The current B11 surface is:

- `138` mandatory predicates;
- `273` reference/context rows;
- `0` conditional/applicability-pending rows;
- `0` `MANDATORY_OR_CONDITIONAL` rows;
- `0` current B11 predicate evidence bindings.

The one prior optional row (`MD-S079-R0153`) is supersession/context wording rather than a current executable capability and is therefore reference-only. Eleven mixed-run members remain reference/context after explicit semantic-owner review because they describe downstream price products, capability limitations, or exchange-market-structure ownership rather than independently testable B11 obligations. The remaining twenty-seven mixed-run predicate rows were promoted to required.

`MD-DEP-0004` is discharged for B11 only and remains `OPEN_NON_BLOCKING` globally at `274 / 9` unopened downstream stages.

## Strategy scope affected

B11 implementation/proof is bounded by the current rules owned by:

- `MD-S008` — canonical price-scale validation boundary;
- `MD-S010` — publication-bound corporate-action impact flags;
- `MD-S011` — corporate-action event lifecycle, verification hierarchy, continuity verdicts, candidate linkage, completeness reconciliation;
- `MD-S023` — RAW EOD immutability/correction boundary;
- `MD-S059` — provider/bootstrap source behavior relevant to observed-price anomaly evidence;
- `MD-S079` — corporate-action type semantics and unknown-type fail-safe behavior;
- `MD-S080` — exchange market-structure facts used only as detector diagnostics;
- `MD-S084` — price-scale-break detector candidate-only/quarantine/no-repair contract.

Strategy meaning changed: **NO**.

## Executable impact surface

Expected/allowed material B11 changes include:

- append-only price-scale break candidate evidence using stable listing identity and immutable publication/source-observation lineage;
- append-only candidate review/dismissal evidence;
- verified corporate-action revision lifecycle and diagnostic candidate linkage;
- factor-decision activation restricted to verified revisions with sufficient terms and verified continuity anchor;
- explicit external exchange/CSD reconciliation state for event completeness;
- disabling legacy price-gap-derived event/factor and in-place bar repair behavior;
- current event-risk/contamination resolution against revisioned `ex_date` semantics rather than legacy `action_date` fallback;
- database migration/schema mirror updates required for new evidence/reconciliation records;
- operator command for external corporate-action reconciliation;
- positive, negative/fail-closed, integration and static proof tooling.

## Database/schema impact

Expected additive tables are limited to B11 evidence/control state. Existing immutable RAW/history/publication tables must not become mutation targets. Existing `md_corporate_action_revisions`, `md_adjustment_factor_sets`, `md_adjustment_factors`, `md_source_scale_assessments`, and `md_adjustment_factor_decisions` remain canonical revision/factor foundations.

## Runtime / provider / backfill impact

- Detector execution may append candidate evidence but must never create verified corporate-action terms or active factors from price geometry.
- A candidate may be linked diagnostically to a nearby revision, but only an independently verified event revision with sufficient terms may release factor-related quarantine.
- Full-range action-completeness claims require explicit reconciliation against an authoritative exchange/CSD corpus from the intentional dataset start.
- The existing three-event historical KSEI manifest is admissible event evidence for its declared events only; it explicitly is **not** a complete corporate-action corpus and must never satisfy the completeness claim.
- No broad `storage/**` scan is required. Any current runtime proof artifact will be admitted only through current B11 governed evidence after local execution.

## Tests / tooling / evidence impact

B11 requires stage-scoped traceability/proof specification, readiness gate, atomic binder, and fail-closed self-test. Runtime-dependent predicates remain `NOT_ASSESSED` until actual local MariaDB/PHPUnit/command output is returned and admitted.

## Compatibility / residue risk

Legacy `market_data_corporate_actions` and `market_data_price_scale_breaks` surfaces may remain for historical compatibility, but they must not silently regain authority over verification, ex-date, factor activation, or candidate release. Compatibility reads must be explicitly subordinate to current V2 revision/candidate evidence.

A current proof gap remains if no authoritative exchange/CSD full-range corpus is available for historical completeness reconciliation. That state is an explicitly qualified external proof limitation, not a synthetic PASS; it does not authorize local semantic substitution.

## Dependency / relationship impact

- predecessor closure `SC-MD-B10-A001-001` is a stage precondition only and contributes no inherited B11 predicate proof;
- `MD-DEP-0004` B11 entry obligation is complete; global dependency remains open downstream;
- any external-completeness blocker identified by executed proof must be recorded under current dependency governance rather than hidden in prose.

## Closure boundary

B11 cannot close until all 138 mandatory predicates are proven under `MD-B11-A001-BL001`, runtime artifacts required by the proof plan are admitted, relationships are complete, residue is conformant or explicitly qualified under authority, and post-binding traceability/proof/documentation/relationship gates pass.


## Pre-local-proof synchronization — 2026-08-25

The non-local B11 work unit is complete and ready for one consolidated local proof cycle. Current implementation includes append-only candidate/review evidence, verified revision ownership, factor verification gating, V2 ex-date event-risk semantics, disabled price-derived mutation paths, bidirectional external exchange/CSD reconciliation, additive B11 schema state, targeted positive/fail-closed tests, stage-scoped proof mapping/gate/binder/self-test, migration static gate and deployed MariaDB schema probe.

Current proof readiness remains deliberately pre-runtime:

- mandatory denominator: `138`;
- proof plan mappings: `138/138`;
- proof families: `8`;
- runtime SATISFIED: `0/138`;
- applicability pending: `0`;
- B11 mixed-classification debt: `0`;
- local MariaDB/PHPUnit/command output: pending.

External action completeness remains fail-closed: a reconciliation manifest with `scope_complete=false` may prove the rejection/qualification path, but cannot support an action-complete historical claim. A complete exchange/CSD corpus is not fabricated by repository evidence.
## Local proof cycle 1 — returned result and R1 remediation

Returned `LOCAL-B11-A001` execution evidence is retained as a failed/partial proof cycle, not rewritten as PASS:

- `LOCAL-B11-001` PASS — B11 migration applied successfully;
- `LOCAL-B11-002` PASS — deployed MariaDB contains all three required B11 tables/columns;
- `LOCAL-B11-003` FAIL — first four targeted files PASS, then `CorporateActionLifecycleB11RegressionTest` stopped fail-fast on a stale string-interpolation guard;
- `LOCAL-B11-004` FAIL — negative destructive guard PASS, but dry-run path parsing failed before reconciliation because of an invalid regex delimiter/escape sequence;
- `LOCAL-B11-005` FAIL — 1879 tests / 17712 assertions / 42 errors / 5 failures. Forty errors share one missing `Schema` facade import in `EventRiskSourceRepository`; one is the stale `$row` guard; one is a legacy detector fixture that no longer satisfies listing/calendar prerequisites. The five failures are stale legacy-factor/quarantine expectations, missing runbook registration, and two legacy detector fixtures.

R1 remediation preserves current strategy semantics: it restores the missing facade import; replaces the path parser with a PHP-7-compatible delimiter-safe absolute-path check; fixes the non-interpolating static guard; updates legacy tests to the current `LEGACY_UNVERIFIED` fail-closed boundary; supplies stable listing plus verified-calendar prerequisites to detector fixtures; and registers `market-data:reconcile:corporate-actions` in the operational runbook. Migration/schema and the four targeted files that already passed are not invalidated.

No B11 predicate is promoted by this remediation. Corrected local proof remains required before governed evidence admission or closure.
## Local proof R1 — returned result and R2 remediation

Returned `LOCAL-B11-A001-R1` execution evidence is retained exactly as returned:

- `LOCAL-B11-R1-001` FAIL — `CorporateActionLifecycleB11RegressionTest` stopped fail-fast at 4 tests / 18 assertions / 1 failure because dead legacy source still contained an `action_date` fallback helper even though current runtime no longer used it;
- `LOCAL-B11-R1-002` FAIL — destructive-guard negative path PASS, but the valid PowerShell-generated UTF-8 manifest carried a BOM and `reconcileFile()` passed the BOM directly to `json_decode`, yielding `CORPORATE_ACTION_AUTHORITY_MANIFEST_JSON_INVALID`;
- `LOCAL-B11-R1-003` FAIL — full suite completed at 1879 tests / 17862 assertions with only 2 failures: the same dead fallback source guard and a stale `PriceAdjustmentTest` expectation that a factor stored on `market_data_corporate_actions` could release quarantine.

R2 remediation does not alter strategy meaning or B11 schema. It removes the dead action-date fallback identity helper, makes the retained legacy `isAdjustable()` boundary fail closed for every legacy row, updates the stale legacy-factor expectation to require quarantine until a verified V2 revision is bound, and strips an UTF-8 BOM only for JSON decoding while preserving the exact raw manifest bytes for SHA-256 evidence identity. A focused unit proof covers the PowerShell BOM case.

Cumulative `LOCAL-B11-001` migration and `LOCAL-B11-002` deployed-schema proof remain valid because R2 does not change migrations or deployed schema. No mandatory predicate is promoted until corrected R2 local proof returns.

