# Change Impact Declaration — `MD-B10-A001`

- ID: `CI-MD-B10-A001-001`
- Stage / Attempt / Baseline / Epoch: `MD-B10` / `MD-B10-A001` / `MD-B10-A001-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260823-001`
- Predecessor closure: `SC-MD-B09-A002-001`
- Dependencies: `MD-DEP-0004 OPEN_NON_BLOCKING`; B10 stage-entry portion normalized, global remainder `312 / 10`
- Status: `ISSUED — FINAL_R5_PROOF_PASS; EVIDENCE_BOUND; B10_CLOSURE_COMPLETE`
- Strategy meaning change: `NO`

## Objective

Revalidate and, only where current authority requires it, remediate the existing publication lifecycle as `EXISTING_UNVERIFIED`. The B10 proof owner covers immutable publication identity/history, candidate-to-seal transition, manifest/hash binding, one-current pointer integrity, correction/reseal/supersession lifecycle, failed-correction pointer preservation, and no in-place rewrite of sealed/publication-bound artifacts.

## Stage-entry normalization

The provisional Stage Register denominator was not closure-authoritative. Current stage-scoped semantic normalization yields:

- mandatory denominator: **1072**;
- optional capabilities: **1**;
- primary-stage reference/context: **239**;
- moved to another primary proof owner while B10 supports: **1**;
- transitional / applicability pending: **0**;
- B10 mixed-classification debt: **0**;
- global `MD-DEP-0004` remainder: **312 mixed members across 10 unopened stages**.

All 1072 mandatory predicates remain `NOT_ASSESSED` and unbound before fresh B10 runtime proof.

## Initial material-impact boundary

Existing publication/correction/history implementation is not assumed conformant. Revalidation is scoped to actual B10-owned executable surfaces, including where applicable:

- `EodPublicationRepository`, `EodArtifactRepository`, correction repository and publication-finalization services;
- finalize/seal/correction/repair commands and current-pointer resolution;
- publication-bound `*_history` snapshot behavior and no-in-place-rewrite enforcement;
- deterministic artifact/publication manifest identity and seal bindings;
- correction failure/rejection paths and current-pointer preservation;
- tests/static guards proving positive and negative lifecycle invariants;
- B10 traceability/proof spec, readiness/bound gate, binder and mutation self-test.

No schema or migration change is presumed. A migration is permitted only if actual authority-backed persistence semantics cannot be implemented against the current schema. No strategy byte may be changed to make legacy code/tests pass.

## Predecessor impact discipline

B09 canonical RAW PASS is a valid precondition but not B10 proof. If a B10 change invalidates a closed predecessor invariant, revalidation must identify the exact predecessor rule and preserve predecessor evidence/closure immutability. No predecessor reopening is assumed at CI issue.

## Closure boundary

B10 may close only after all current mandatory predicates have fresh proof, negative/fail-closed publication/correction semantics are demonstrated, full-suite regression is green if required by current proof ownership, governed evidence is issued and bound atomically, no harmful residue remains, and traceability/classification/documentation/relationship/current-state gates pass. `MD-B11` must remain unopened in this work unit.

## Final non-local implementation surface before local proof

The initial revalidation found executable B10 gaps and remediated them inside the same attempt without changing strategy or governance authority:

- seal/snapshot sequencing now completes publication-bound immutable snapshots, verifies declared counts and content hashes, prepares the deterministic publication manifest, and only then transitions the candidate to `SEALED` inside the same outer transaction;
- post-seal snapshot completion is rejected rather than silently filling history, and the canonical rejection semantic is `SEALED_PUBLICATION_IMMUTABLE`;
- direct database immutability is deployable through `2026_08_24_000001_enforce_sealed_history_and_projection_reconciliation.php`, which creates INSERT/UPDATE/DELETE guards for all three history tables and supplies a reversible down path;
- the existing `publication_manifest_hash` field is now produced from canonical, non-self-referential semantic manifest content, persisted before seal, and revalidated at seal/current-pointer lifecycle boundaries;
- an independent projection reconciliation service and command now compare pointer-resolved current publication history against the rebuildable live projections in both directions, detect missing/orphan/value/binding mismatches, persist counts/sample/hash, and never repair or delete content;
- scheduling/configuration, MariaDB schema reference, SQLite test mirror, operational trigger SQL, deployed-schema probe and targeted positive/fail-closed tests are synchronized with those executable changes.

No B09 evidence, closure record, or strategy byte is rewritten. The B09 canonical-RAW/source-observation invariants remain predecessor preconditions and no B10 change creates a parallel source/provenance truth.

## Proof-tooling drift corrected before local handoff

A fresh non-local static re-run against the current repository exposed one proof-tooling defect after the migration's repair-safe MariaDB shape had evolved: `MarketDataPublicationLifecycleProofGate` still searched for the obsolete helper-per-event migration source shape (`HISTORY_TABLES` plus `createInsertTrigger` / `createUpdateTrigger` / `createDeleteTrigger`) while the current migration correctly uses the explicit `TRIGGERS` definition map. The dedicated `MarketDataB10MigrationStaticGate` already accepted the current executable migration, so this was a stale proof-gate assertion rather than an implementation or authority defect.

The defect is remediated inside `MD-B10-A001` by:

- making `MarketDataPublicationLifecycleProofGate` delegate database-immutability source readiness to the dedicated B10 migration/ops/schema gate instead of duplicating obsolete migration-shape logic;
- strengthening `MarketDataB10MigrationStaticGate` to verify the exact trigger-name → history-table → event mapping in both the migration and the locked operational SQL, in addition to the existing count, canonical rejection, explicit-index, repair-safe DDL and reconciliation-table checks.

No strategy or governance authority changed. No runtime/application behavior or migration semantics changed in this correction; only current B10 proof tooling and mutable orchestration/control-plane text are affected.

## Proof model and local-proof boundary

The current B10 proof specification maps every one of the **1072** mandatory semantic predicates to one of **31** stage-owned proof families. Each family declares the implementation surfaces, a positive executable proof, a negative/fail-closed proof, and where applicable a deployed MariaDB probe. The current proof gate requires all mandatory rows to remain `NOT_ASSESSED` with no evidence binding before returned local proof.

Current non-local proof state:

- mandatory proof plan: `1072 / 1072` mapped;
- unbound proof-plan rows: `0`;
- premature `SATISFIED`: `0`;
- runtime pending: `1072`;
- proof binder `--validate-only`: `PASS`;
- proof self-test: `15/15` (`1` control + `14` fail-closed mutations);
- B10 migration/operational-SQL static gate: `PASS` (`3` history tables / `9` required triggers);
- local MariaDB migration/deployment, direct SQL mutation rejection, PHPUnit integration, reconciliation execution and full-suite proof remain pending and are not inferred from static inspection.

## Residue state before returned local proof

The previously identified implementation gaps are **implementation-remediated**, but the stage residue verdict remains `INCONCLUSIVE_RESIDUE_EVIDENCE` until fresh local execution confirms the deployed schema and runtime lifecycle. The two tracked residue families therefore remain proof-pending, not silently erased:

- `PUBLICATION_SEAL_ATOMICITY_AND_HISTORY_FREEZE_GAPS` → implementation remediated; runtime/deployed proof pending;
- `PROJECTION_RECONCILIATION_AND_MANIFEST_COMPLETENESS_PENDING` → implementation remediated; runtime/deployed proof pending.

`MD-DEP-0004` remains `OPEN_NON_BLOCKING` with the current matrix-derived downstream remainder of `312 / 10` after the B10 entry obligation was completed.

## Current status

`ISSUED — FINAL_R5_PROOF_PASS; EVIDENCE_BOUND; B10_CLOSURE_COMPLETE`

No B10 mandatory predicate may be bound to evidence until the consolidated local proof package returns and is admitted. `MD-B11` remains unopened.

## Pre-local-cycle-1 static verification

The pre-runtime repository state before returned local cycle 1 was revalidated after the initial proof/control-plane synchronization:

- executable/tooling PHP syntax: `490/490 PASS`;
- B10 traceability: `1072 mandatory / 1 optional / 1 moved / 239 reference / 0 applicability pending / 0 premature satisfied-or-bound` — `PASS`;
- B10 proof readiness: `1072/1072` proof-plan rows, `31` proof families, `1072` runtime pending, `0` unbound plan rows, `0` premature satisfied — `PASS`;
- proof binder `--validate-only`: `PASS`, no traceability write performed;
- B10 proof self-test: `15/15` (`1` control + `14` fail-closed mutations) — `PASS`;
- B10 migration / operational-SQL static gate: `PASS` (`3` history tables / `9` required triggers / exact trigger→table→event mapping / reconciliation persistence); an injected trigger-event mapping mutation was rejected `FAIL` and the restored control returned `PASS`;
- classification consistency: `312` mixed reference members across `10` unopened downstream stages — `PASS`;
- documentation integrity: `907 / 907 / 907 / 907`, strategy freeze `91` registered / `0` mismatches — `PASS`;
- relationship integrity: `130` records / `216` relationships / `0` validity errors / `0` completeness gaps — `PASS`;
- relationship mutation self-test — `PASS`;
- canonical `CURRENT_STATE.md` generated twice byte-identically with SHA-256 `AD9B141F1F49A0589FB180703223C1E88075570E6F19FC2FF895EE3B9D2E8AF1`.

These results are `STATIC_PROOF` only. MariaDB migration deployment, nine active trigger guards, rollback-safe direct SQL rejection, targeted PHPUnit lifecycle/reconciliation behavior, and the full PHPUnit suite remain `LOCAL_RUNTIME_PROOF_REQUIRED`. Therefore all 1072 mandatory B10 predicates remain unbound and `NOT_ASSESSED` at handoff.


## Returned local proof cycle 1 and impact-based remediation

Returned package `LOCAL-B10-A001.zip` was admitted only as execution evidence, not as authority. Its
declared per-file SHA-256 values match the uploaded bytes. Actual results were:

- `LOCAL-B10-001`: PASS — migration command exit `0` and reported `Nothing to migrate`; the
  deployed-schema probe independently confirmed the B10 migration is recorded `APPLIED`.
- `LOCAL-B10-002`: FAIL — reconciliation table and all nine expected trigger definitions were
  present, but only the three INSERT attempts reached the canonical
  `SEALED_PUBLICATION_IMMUTABLE` signal. UPDATE/DELETE attempts failed for another database reason.
  Full-suite production-corpus output simultaneously reported `15` history triggers where the
  current repository defines exactly the nine B10 guards, exposing local deployed-schema drift.
- `LOCAL-B10-003`: INCOMPLETE — no targeted PHPUnit output was returned.
- `LOCAL-B10-004`: FAIL — reconciliation stopped on canonical hashing of the valid published reason
  token `RIGHTS_ISSUE@2026-07-22`.
- `LOCAL-B10-005`: FAIL — `1857` tests / `16288` assertions, `32` errors and `17` failures,
  exit `2`. The failures cluster into the same B10 remediation surfaces rather than 49 independent
  defects.

Root-cause remediation remains inside `MD-B10-A001`:

- canonical semantic-document hashing now accepts exact integer leaves without weakening strict
  row-field numeric ownership, and legacy reason-set canonicalization accepts date-qualified
  corporate-action tokens such as `RIGHTS_ISSUE@YYYY-MM-DD`;
- coverage `FAIL` / `NOT_EVALUABLE` no longer turns the seal stage into an exception: the candidate
  remains unsealed/non-current and FINALIZE owns the governed HELD/FAILED + NOT_READABLE outcome;
- the reconciliation command emits existing registered command reason codes instead of introducing
  parallel unregistered codes;
- privileged projection reconciliation is explicitly excluded only from consumer-read/latest-date
  static prohibitions, consistent with the locked audit/reconciliation exception; normal consumer
  gateways remain unchanged;
- stale publication/migration/manifest test guards and correction fixtures are synchronized to the
  current B10 pre-seal snapshot/manifest semantics rather than weakening the executable invariant;
- the operational runbook now documents the registered reconciliation command and its no-repair
  boundary;
- the deployed-schema probe now uses exact publication/date/ticker primary-key targets, reports
  noncanonical database errors, and detects unexpected triggers on the three governed history
  tables. The repository migration itself is unchanged: local extra triggers are environment/schema
  drift and must not be hidden by application changes.

`LOCAL-B10-001` remains valid because this remediation does not change the B10 migration.
`LOCAL-B10-002`, `LOCAL-B10-004`, and `LOCAL-B10-005` require corrected rerun; `LOCAL-B10-003`
remains required because it was not returned. No runtime-dependent predicate is promoted before
those outputs return.


## Final post-remediation non-local verification before corrected local proof

After returned local cycle 1, impact-based remediation and final control-plane synchronization were completed without changing the B10 migration, strategy authority, governance authority, predecessor closure, baseline, denominator, or attempt identity. `ProductionCorpusInvariantOracleTest` deliberately retains its exact-history-trigger-count assertion: unexpected local history triggers are environment/schema drift to remove, not a test expectation to weaken.

Final static/non-local state after the last repository edits:

- PHP syntax across application, migrations, tests and Market Data proof tooling: `499/499 PASS`;
- B10 traceability: `1072 mandatory / 1 optional / 1 moved / 239 reference / 0 applicability pending / 0 premature satisfied-or-bound` — `PASS`;
- B10 proof gate: `1072/1072` mapped across `31` proof families, `1072` runtime pending, `0` unbound plan rows, `0` premature satisfied — `PASS`;
- proof binder `--validate-only`: `PASS`; no traceability write performed;
- B10 proof self-test: `15/15` (`1` control + `14` fail-closed mutations) — `PASS`;
- B10 migration/operational-SQL static gate: `PASS` (`3` history tables / `9` expected triggers / exact trigger→table→event mapping / reconciliation schema / short indexes / partial-DDL recovery); an injected `UPDATE→DELETE` trigger-definition mutation was rejected `FAIL` as required;
- classification consistency: `6495` active traceability rows, `312` mixed reference members across `10` unopened stages, B10 included among normalized stages — `PASS`;
- documentation integrity: `907` physical / `907` role-registry / `907` document-ID / `907` current-verification mappings, strategy freeze `91` registered / `0` mismatches — `PASS`;
- relationship integrity: `130` records / `216` relationships / `0` validity errors / `0` completeness gaps — `PASS`;
- relationship mutation self-test — `PASS`;
- generated `CURRENT_STATE.md` now resolves the already-open stage from the governed attempt identity independent of the resume verb, explicitly shows `MD-B10 / MD-B10-A001 / MD-B10-A001-BL001`, and generates byte-identically on consecutive runs.

These are `STATIC_PROOF` only. `LOCAL-B10-001` from returned cycle 1 remains valid because the migration is unchanged. Corrected local proof must remove only unexpected non-repository triggers from the three B10 history tables and then execute the deployed-schema mutation probe, targeted affected/B10 proof tests, live reconciliation, and full suite. All `1072` mandatory predicates remain `NOT_ASSESSED` and unbound until those returned outputs are verified.
## Returned corrected local proof R1 and remediation R2

Returned `LOCAL-B10-A001-R1` is execution evidence, not authority semantics. Actual results were:

- `LOCAL-B10-R1-001` PASS: six unexpected legacy history triggers were removed under the allowlist guard;
- `LOCAL-B10-R1-002` PASS: the deployed B10 migration/reconciliation table and exact nine MariaDB history triggers were present and all nine direct INSERT/UPDATE/DELETE mutation attempts were canonically blocked;
- `LOCAL-B10-R1-003` FAIL: targeted PHPUnit stopped at `MarketDataPipelineIntegrationTest` with 4 errors and 22 failures;
- `LOCAL-B10-R1-004` FAIL: current projection reconciliation for 2026-07-28 resolved publication 73666 but reported 59 mismatches;
- `LOCAL-B10-R1-005` FAIL: full suite completed with 1859 tests / 16955 assertions / 8 errors / 24 failures.

R2 root-cause remediation stays inside `MD-B10-A001` and does not modify strategy or the B10 migration:

- internal pointer-switch validation now checks raw structural pointer/publication/run identity during the transitional write phase and preserves the strict consumer-readable resolver check after final run-state commit;
- sealed publication semantic manifest hashing no longer binds mutable run `final_reason_code`; terminal reasons remain operational evidence rather than sealed content identity;
- pipeline test doubles initialize inherited publication-repository dependencies and publication repository fixtures use valid deterministic SHA-256 artifact hashes;
- history-trigger static guards follow the current explicit trigger-definition map rather than obsolete helper/variable shapes;
- a controlled exact-date projection repair service/command rebuilds only non-authoritative current projections from the immutable pointer-resolved publication history, requires complete history, requires operator `--apply --reason`, and commits only when independent post-repair reconciliation returns PASS; reconciliation remains independently verification-only and automatic repair is not scheduled;
- B10 proof tooling includes the controlled repair surface in the projection-reconciliation proof family and statically rejects a history-mutating repair implementation.

`LOCAL-B10-001`, `LOCAL-B10-R1-001`, and `LOCAL-B10-R1-002` remain valid because R2 does not change the migration, trigger definitions, or deployed-schema probe. R2 requires targeted affected PHPUnit, one controlled 2026-07-28 projection rebuild, fresh reconciliation, and the full suite. All 1072 mandatory B10 predicates remain `NOT_ASSESSED` and unbound until corrected runtime proof is returned and admitted.
## R2 final non-local verification before corrected local proof

Fresh post-remediation static verification confirms: PHP syntax clean across the repository; B10 proof gate PASS at 1072/1072 with 31 proof families and 1072 runtime-pending rows; binder validate-only PASS; proof mutation self-test 15/15 PASS; B10 migration static gate PASS at 3 history tables / 9 triggers; classification PASS with B10 debt zero and global MD-DEP-0004 remainder 312/10; documentation integrity PASS at 907/907; strategy freeze 91 with zero mismatch; relationship integrity PASS at 130 records / 216 relationships; relationship/documentation mutation self-test PASS. CURRENT_STATE is regenerated deterministically after this synchronization.

These results are static/source proof only. R2 runtime admission remains blocked until targeted PHPUnit, controlled projection repair, fresh reconciliation, and full PHPUnit return from the patched local repository.

## Returned corrected local proof R2 and remediation R3

Returned `LOCAL-B10-A001-R2` is execution evidence, not authority semantics. Actual results were:

- `LOCAL-B10-R2-001` FAIL: targeted PHPUnit stopped at `MarketDataPipelineIntegrationTest` with `56` tests / `714` assertions / `22` failures, exit `1`; remaining targeted files did not execute;
- `LOCAL-B10-R2-002` FAIL: controlled projection repair rolled back because independent post-rebuild reconciliation remained FAIL; command returned `COMMAND_EXECUTION_FAILED`, exit `1`;
- `LOCAL-B10-R2-003` FAIL: current publication `73666` still had `59` projection/history mismatches, `failed_count=1`, exit `1`; this result does not authorize immutable-history mutation;
- `LOCAL-B10-R2-004` FAIL: full suite completed with `1863` tests / `17138` assertions / `3` errors / `26` failures, exit `2`.

R3 root-cause remediation remains inside the same `MD-B10-A001` attempt:

- correction candidate lineage is now bound to the prior current publication before HASH/manifest/seal, so immutable correction identity cannot change between HASH and FINALIZE;
- fail-closed finalize no longer repopulates an intentionally-null `trade_date_effective`, and redundant transitional service-level pointer verification is removed while repository write-side validation and strict post-finalize consumer resolution remain fail-closed;
- controlled projection repair exposes explicit `--dry-run`, rejects conflicting `--dry-run` + `--apply`, validates publication snapshot completeness and exact immutable-history `run_id` binding before declaring history repairable, and blocks invalid history with `PROJECTION_REPAIR_HISTORY_IDENTITY_INVALID`;
- dry-run emits publication/run repair identity, artifact-level mismatch counts and a bounded mismatch sample; apply remains exact-date/operator-reason guarded and rolls back unless independent post-rebuild reconciliation is PASS;
- affected pipeline/projection/static tests, proof gate and operator runbook are synchronized to the current implementation contract without weakening immutable-history or consumer-read protections.

Cumulative proof preserved as valid: `LOCAL-B10-001`, `LOCAL-B10-R1-001`, and `LOCAL-B10-R1-002`. Corrected R3 local proof requires affected targeted PHPUnit, explicit projection-repair dry-run preflight for `2026-07-28`, controlled apply only if the immutable current-publication history is reported `REPAIRABLE_FROM_IMMUTABLE_HISTORY`, fresh independent reconciliation, then the full suite. All `1072` mandatory predicates remain `NOT_ASSESSED` and unbound until corrected runtime proof returns and is admitted.

## Returned corrected local proof R3 and remediation R4

Returned `LOCAL-B10-A001-R3` is execution evidence, not authority semantics. Actual results were:

- `LOCAL-B10-R3-001` FAIL: targeted PHPUnit stopped at `MarketDataPipelineIntegrationTest` with `56` tests / `714` assertions / `22` failures, exit `1`; remaining targeted files did not execute;
- `LOCAL-B10-R3-002` PASS: explicit dry-run for `2026-07-28` resolved publication `73666` / run `72941`, validated complete immutable-history identity, reported `REPAIRABLE_FROM_IMMUTABLE_HISTORY`, and localized all `59` pre-existing mismatches to eligibility projection values for `trading_status_revision_id` and `trading_status_source_observation_id`, exit `0`;
- `LOCAL-B10-R3-003` FAIL: controlled apply rolled back because post-rebuild reconciliation still reported the same `59` eligibility lineage mismatches, exit `1`;
- `LOCAL-B10-R3-004` FAIL: independent reconciliation still reported `59` mismatches / `failed_count=1`, exit `1`;
- `LOCAL-B10-R3-005` FAIL: full suite completed with `1865` tests / `17157` assertions / `1` error / `22` failures, exit `2`. The sole error was the projection-repair unit fixture missing required `eligibility_reasons_json`; the 22 failures remained concentrated in `MarketDataPipelineIntegrationTest`.

R4 root-cause remediation remains inside the same `MD-B10-A001` attempt and does not modify strategy, governance authority, or the B10 migration:

- `completeEligibility()` now persists the normalized pre-seal `quality_gate_state` derived from coverage (`PASS`, `FAIL`, or `BLOCKED`) in the same governed telemetry update that stores coverage. This preserves the locked manifest rule that sealed semantic identity binds quality/readiness state while preventing HASH/SEAL from observing an empty quality state that is only populated later during pointer promotion;
- projection rebuild now copies `trading_status_revision_id` and `trading_status_source_observation_id` from immutable publication eligibility history into the rebuildable current eligibility projection, closing the exact `59` mismatches isolated by the passing R3 dry-run without mutating history;
- the projection-repair unit fixture now includes the required canonical `eligibility_reasons_json` field and proves the two trading-status lineage identifiers are actually restored by repair;
- B10 remediation regression coverage now statically locks both pre-seal quality persistence and eligibility lineage-copy behavior.

Cumulative proof preserved as valid: `LOCAL-B10-001`, `LOCAL-B10-R1-001`, `LOCAL-B10-R1-002`, and `LOCAL-B10-R3-002`. Corrected R4 local proof does not rerun migration/trigger deployment or immutable-history repairability preflight. It requires affected targeted PHPUnit, controlled projection apply for `2026-07-28`, fresh independent reconciliation, then the full suite. All `1072` mandatory predicates remain `NOT_ASSESSED` and unbound until corrected runtime proof returns and is admitted.


## Returned corrected local proof R4 and deployed-repair proof gap

Returned `LOCAL-B10-A001-R4` is execution evidence, not authority semantics. Its declared SHA-256 values match the returned files. Actual results were:

- `LOCAL-B10-R4-001` PASS: all eleven affected targeted PHPUnit files executed successfully. In particular, `MarketDataPipelineIntegrationTest` returned `56 tests / 1268 assertions` with zero failures/errors, and every targeted invocation exited `0`;
- `LOCAL-B10-R4-002` INCOMPLETE for the intended deployed-repair execution claim: the correct controlled apply command exited `0`, but the current projection was already reconciled before the command (`before_reconciliation_state=PASS`, `before_mismatch_count=0`), so the service correctly returned `repair_state=NO_CHANGE` instead of exercising the required `REBUILT_AND_VERIFIED` branch. This is not an implementation failure, but it does not prove how the prior `59` mismatches were removed;
- `LOCAL-B10-R4-003` PASS: independent reconciliation exercised `2026-07-28`, publication `73666`, with `mismatch_count=0`, `failed_count=0`, final `status=PASS`, exit `0`;
- `LOCAL-B10-R4-004` PASS: full PHPUnit completed `1866 tests / 17723 assertions`, zero failures/errors, exit `0`.

The R4 repository-state handoff is consistent with the cumulative B10 patch lineage plus local proof archives; no test-created tracked source mutation was identified. `LOCAL-B10-001`, `LOCAL-B10-R1-001`, `LOCAL-B10-R1-002`, `LOCAL-B10-R3-002`, `LOCAL-B10-R4-001`, `LOCAL-B10-R4-003`, and `LOCAL-B10-R4-004` remain valid.

Because the R4 apply command did not execute a rebuild, B10 closure still lacks one deployed proof of the operator repair path itself. R5 adds a proof-only MariaDB probe under `tools/market_data/` that:

- refuses production environments;
- requires the real current projection to be clean before the probe;
- injects one eligibility trading-status-lineage mismatch only inside an outer database transaction;
- proves independent reconciliation detects that mismatch;
- calls the real `PublicationProjectionRepairService` and requires `REBUILT_AND_VERIFIED` plus zero post-repair mismatches;
- verifies the two eligibility lineage fields are restored from immutable history and history itself is unchanged;
- rolls the entire injected mismatch/repair transaction back; and
- requires an independent post-rollback reconciliation PASS, leaving the pre-probe projection state intact.

This is proof-tooling remediation only. It does not modify application/runtime behavior, strategy, governance authority, or migrations. The existing R4 full-suite PASS therefore remains valid; R5 requires only the affected static regression test plus the deployed transactional repair probe. All `1072` mandatory predicates remain `NOT_ASSESSED` and unbound until that final runtime proof is returned and admitted.


## Final R5 proof admission and closure

Returned `LOCAL-B10-A001-R5` completed the final proof gap without changing application/runtime or migration semantics:

- R5 regression: `5 tests / 30 assertions`, PASS, exit `0`;
- rollback-safe deployed MariaDB repair probe: baseline `PASS/0`, injected drift `FAIL/1`, actual `REBUILT_AND_VERIFIED`, post-repair `PASS/0`, lineage restored, history unchanged, outer transaction rolled back, and post-rollback `PASS/0`;
- package SHA declarations match the actual returned proof files and repository-state captures.

Governed evidence `E-MD-B10-A001-001` admits the complete cumulative B10 proof. The B10 binder atomically binds all `1072/1072` mandatory predicates to that evidence. Post-binding proof/traceability, classification, documentation, relationship, freeze, mutation and deterministic-current-state controls pass. Closure is issued as `SC-MD-B10-A001-001`. No local proof rerun is required because closure-only control-plane changes do not alter executable runtime semantics.
