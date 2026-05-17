# DB Integrity FK / Implicit Integrity Decision Inventory

Status: `DONE_LOCAL_PHPUNIT_PASS`
Last updated: 2026-05-17
Related contract: `DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_CONTRACT`
Existing owner extended: `DB_INTEGRITY_CONSTRAINT_ENFORCEMENT_CONTRACT`

## Scope confirmation

Audit terakhir tidak menyatakan seluruh schema sync gagal. Scope sesi ini hanya menutup risiko bahwa beberapa live artifact relation masih bergantung pada asumsi code/repository, bukan jaminan eksplisit database constraint. Keputusan final untuk source-of-truth ZIP ini adalah mengunci policy `HYBRID_REQUIRED`: FK eksplisit dipakai hanya untuk relation yang stabil dan aman, sedangkan relation lifecycle yang phase-dependent tetap memakai implicit guard yang wajib tertulis, teruji, dan tidak boleh melemahkan current-pointer/read-side contract.

## Existing contract owner

| Existing Contract / Test / Doc | Role | Current Status | Relevance to DB Integrity Decision | Reuse / Extend / Do Not Touch |
|---|---|---|---|---|
| `DB_INTEGRITY_CONSTRAINT_ENFORCEMENT_CONTRACT` | Canonical owner untuk PK/unique/index/implicit guard DB integrity | LOCKED by prior operator-local proof | Basis kebijakan bahwa non-FK lifecycle relation harus punya implicit guard/test | Extend via this decision inventory; do not duplicate old contract wording |
| `DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT` | Four-way schema/runtime sync | DONE historical | Batas supaya sesi ini tidak dianggap schema sync ulang seluruh sistem | Do not reopen unless migration/schema actually changes |
| `READ_SIDE_POINTER_ENFORCEMENT_CONTRACT` | Consumer current-pointer-only contract | LOCKED | Live artifact current reads tidak boleh bypass pointer | Preserve |
| `REPLAY_HISTORICAL_DETERMINISM_HARDENING_CONTRACT` | Historical replay publication proof | LOCKED | Replay historical must remain publication-scoped and not current-pointer dependent | Preserve |
| `EVIDENCE_HISTORICAL_LINEAGE_COMPLETENESS_CONTRACT` | Historical evidence audit resolver | LOCKED | Evidence may resolve historical publication by explicit selector | Preserve |
| `DbIntegrityConstraintEnforcementStaticGuardTest.php` | Existing DB integrity static guard | Present | Guards PK/index/implicit policy and no latest/MAX shortcuts | Extend conceptually with new decision guard |
| `DbIntegrityFkImplicitIntegrityDecisionStaticGuardTest.php` | New decision static guard | Added in this session | Locks FK vs implicit decision matrix and audit docs sync | New guard |

## Final policy decision

Final policy: `HYBRID_REQUIRED`.

Rules:

1. Stable DB-owned relation may use FK when it does not create circular finalize/promotion/correction risk.
2. Current live artifact tables must keep mandatory `run_id` and `publication_id` columns plus publication-scoped indexes, but relation validity is enforced by repository/service/static guard, not FK, because the write lifecycle is phase-dependent.
3. Historical artifact tables keep explicit FK to `eod_publications(publication_id)` because they are immutable publication-bound proof surfaces.
4. Current pointer keeps explicit FK to `eod_publications(publication_id)` and uses repository mirror guard for `run_id`, `publication_version`, coverage PASS, `SUCCESS + READABLE`, and SEALED state.
5. Correction, replay, evidence, and publication/run mirror relations stay implicit because nullable/phase-dependent linkage is valid before finalize/publish.
6. No relation may remain `TBD` without blocker. Current source-of-truth ZIP has no `TBD` decision in this inventory.

## Schema Constraint Matrix

| Table | Column | Current Constraint | FK Exists | Unique/Index Exists | Nullable | Runtime Meaning | Risk | Decision |
|---|---|---|---:|---:|---:|---|---|---|
| `tickers` | `ticker_id` | PK | No downstream FK from live artifact | Yes | No | Master ticker identity | Orphan ticker reference if source bypasses ticker mapper | `IMPLICIT_GUARD_ACCEPTED` for downstream current artifacts; master PK remains explicit |
| `market_calendar` | `cal_date` | PK | No artifact FK | Yes | No | Trading-date authority | Invalid non-trading date if service bypasses calendar | `IMPLICIT_GUARD_ACCEPTED` via calendar/source validation |
| `eod_bars` | `trade_date,ticker_id` | PK | No | Yes | No | Current readable bar row identity | Orphan/mismatched current row if written outside repository | `HYBRID_REQUIRED`: PK/index + implicit guard |
| `eod_bars` | `run_id,publication_id` | NOT NULL + indexes | No | Yes | No | Run/publication context for current row | FK could false-block phase-dependent promote/correction; missing guard would allow stale row | `IMPLICIT_GUARD_ACCEPTED` with repository/read-side/evidence guard |
| `eod_indicators` | `trade_date,ticker_id` | PK | No | Yes | No | Current indicator row identity | Same as bars | `HYBRID_REQUIRED` |
| `eod_indicators` | `run_id,publication_id` | NOT NULL + indexes | No | Yes | No | Run/publication context | Same as bars | `IMPLICIT_GUARD_ACCEPTED` |
| `eod_eligibility` | `trade_date,ticker_id` | PK | No | Yes | No | Current eligibility row identity | Same as bars | `HYBRID_REQUIRED` |
| `eod_eligibility` | `run_id,publication_id` | NOT NULL + indexes | No | Yes | No | Run/publication context | Same as bars | `IMPLICIT_GUARD_ACCEPTED` |
| `eod_publications` | `publication_id` | PK | Referenced by pointer/history | Yes | No | Publication identity | Publication/run mirror mismatch | `IMPLICIT_GUARD_ACCEPTED` for run mirror; referenced by stable FKs where safe |
| `eod_publications` | `trade_date,publication_version` | Unique | n/a | Yes | No | Publication version identity | Duplicate version ambiguity | `EXPLICIT_FK_REQUIRED` not applicable; unique key required and present |
| `eod_current_publication_pointer` | `trade_date` | PK | n/a | Yes | No | Single current pointer per date | Multiple current pointer rows | `EXPLICIT_FK_REQUIRED` for publication; PK/unique present |
| `eod_current_publication_pointer` | `publication_id` | FK + unique | Yes | Yes | No | Pointer target publication | Broken pointer target | `EXPLICIT_FK_REQUIRED`, already present |
| `eod_current_publication_pointer` | `run_id,publication_version` | Index + mirror guard | No | Yes | No | Pointer mirror context | Pointer target mismatch | `IMPLICIT_GUARD_ACCEPTED` |
| `eod_bars_history` | `publication_id,trade_date,ticker_id` | PK + FK publication | Yes for publication | Yes | No | Immutable publication snapshot | Historical orphan if FK removed | `EXPLICIT_FK_REQUIRED`, already present |
| `eod_indicators_history` | `publication_id,trade_date,ticker_id` | PK + FK publication | Yes for publication | Yes | No | Immutable publication snapshot | Historical orphan if FK removed | `EXPLICIT_FK_REQUIRED`, already present |
| `eod_eligibility_history` | `publication_id,trade_date,ticker_id` | PK + FK publication | Yes for publication | Yes | No | Immutable publication snapshot | Historical orphan if FK removed | `EXPLICIT_FK_REQUIRED`, already present |
| `eod_runs` | `run_id` | PK | No downstream FK selected | Yes | No | Run lifecycle identity | Run/publication/correction mismatch | `IMPLICIT_GUARD_ACCEPTED` for lifecycle mirror |
| `eod_dataset_corrections` | correction linkage columns | Nullable + indexes | No | Yes | Yes | Phase-dependent correction lineage | FK could block requested/approved states before run/publication exists | `IMPLICIT_GUARD_ACCEPTED` |

## Live Artifact Relation Decision Matrix

| Relation | Current Enforcement | Failure Impact | FK Safe? | Guard Safe? | Decision | Reason |
|---|---|---|---:|---:|---|---|
| live artifact → ticker | PK on `tickers`, source mapper, coverage universe, artifact static guards | Orphan ticker row could pollute current artifact | No for this session | Yes | `IMPLICIT_GUARD_ACCEPTED` | Adding FK now risks broad migration/data cleanup; ticker validity is governed before artifact publication and must be tested |
| live artifact → trade date / calendar | calendar table + command/source date validation | Non-trading or wrong date rows | No for this session | Yes | `IMPLICIT_GUARD_ACCEPTED` | Calendar relation is operational/date validation, not stable FK on every artifact row |
| live artifact → run | NOT NULL + index + run/publication mirror guard | Wrong run context in current data | No | Yes | `IMPLICIT_GUARD_ACCEPTED` | Run identity is phase-dependent until finalize/publish |
| live artifact → publication | NOT NULL + index + publication-scoped artifact lookup | Stale/current row mismatch | No | Yes | `IMPLICIT_GUARD_ACCEPTED` | Current live table is replaceable current surface; history is FK-backed immutable proof |
| live artifact → current pointer | Read-side resolver + pointer guard | Read stale/non-current artifacts | No | Yes | `IMPLICIT_GUARD_ACCEPTED` | Current pointer validity is cross-table invariant, not row FK |
| history artifact → publication | PK + FK to publication | Historical orphan proof | Yes | Yes | `EXPLICIT_FK_REQUIRED` | Stable immutable relation; already enforced |
| publication → run | run id index + mirror guard | Publication not tied to successful readable run | No | Yes | `IMPLICIT_GUARD_ACCEPTED` | Avoid circular lifecycle block; repository validates mirror/final states |
| pointer → publication | FK + unique + PK | Broken current pointer | Yes | Yes | `EXPLICIT_FK_REQUIRED` | Stable pointer target relation; already enforced |
| pointer → run/version | index + `whereColumn` mirror checks | Pointer mismatch | No | Yes | `IMPLICIT_GUARD_ACCEPTED` | Must compare with publication/run state and coverage gate, not plain FK only |
| correction → prior/new run/publication | nullable indexed columns + correction repository/evidence guards | Broken correction lineage | No | Yes | `IMPLICIT_GUARD_ACCEPTED` | Valid lifecycle includes requested/approved states before all link fields exist |
| replay/evidence → historical publication | selector-scoped resolver + publication-scoped artifact export | Historical proof resolves current instead of selected publication | No | Yes | `IMPLICIT_GUARD_ACCEPTED` | Audit resolver must remain explicit-selector and reason-coded |

## Write Path Integrity Matrix

| Write Path | Entrypoint | Tables Written | Publication Context Known | Run Context Known | FK Safe | Guard Exists | Status |
|---|---|---|---:|---:|---:|---:|---|
| Daily pipeline | `market-data:daily` | runs, bars, indicators, eligibility, publication, pointer | Yes after publication creation | Yes | Partial | Yes | Guarded by pipeline/finalize/pointer checks |
| Bars ingest | `market-data:eod-bars:ingest` | `eod_bars` or history + invalid bars | Candidate/live context passed by service | Yes | No for live | Yes | Implicit guard accepted |
| Indicator compute | `market-data:eod-indicators:compute` | `eod_indicators` or history | Candidate/live context passed by service | Yes | No for live | Yes | Implicit guard accepted |
| Eligibility build | `market-data:eod-eligibility:build` | `eod_eligibility` or history | Candidate/live context passed by service | Yes | No for live | Yes | Implicit guard accepted |
| Promote | `market-data:promote` | publication/history/live/pointer | Yes after candidate materialization | Yes | Partial | Yes | Candidate-scoped coverage must stay intact |
| Correction run | `market-data:correction:run` | correction, candidate/history/live/pointer | Yes after approved correction materialization | Yes | No for correction linkage | Yes | Phase-dependent implicit guard required |
| Seal/finalize | `market-data:dataset:seal`, `market-data:run:finalize` | runs/publications/pointer/history | Yes | Yes | Partial | Yes | Cross-table invariant guard required |

## Read Path Integrity Matrix

| Read Path | Resolver | Tables Read | Current Pointer Required | Publication Scoped | Risk | Status |
|---|---|---|---:|---:|---|---|
| Consumer/API/dashboard read | `resolveCurrentReadablePublicationForTradeDate` / pointer repo | pointer, publication, run, live artifacts | Yes | Current only | Raw/latest bypass | LOCKED by read-side contract |
| Session snapshot | session snapshot service/repository | pointer/current publication/live data | Yes | Current only | Capturing non-current data | Guarded |
| Evidence export current | evidence service/repository | run/publication/pointer/artifacts | Depends on selector | Yes | Incorrect current/historical label | Guarded |
| Evidence export historical | `resolvePublicationForEvidenceAudit` | historical publication + history/live scoped rows | No current fallback | Yes | Losing old publication context | Guarded |
| Replay verify current | replay actual-state resolver | pointer/publication/run/artifacts | Yes | Current expected context | False MATCH from stale data | Guarded |
| Replay verify historical | replay wrapper around evidence audit resolver | historical publication/history rows | No current fallback | Yes | Current-pointer drift | Guarded |
| Correction baseline/candidate | correction services/repositories | correction/publication/history/live | Baseline current required before correction | Yes | Replacing wrong baseline | Guarded |

## FK Candidate Matrix

| FK Candidate | From Table | From Column | To Table | To Column | On Delete/Update | Add / Reject / Defer | Reason |
|---|---|---|---|---|---|---|---|
| pointer publication | `eod_current_publication_pointer` | `publication_id` | `eod_publications` | `publication_id` | restrict/default | Add already present | Stable target relation |
| bars history publication | `eod_bars_history` | `publication_id` | `eod_publications` | `publication_id` | restrict/default | Add already present | Immutable proof relation |
| indicators history publication | `eod_indicators_history` | `publication_id` | `eod_publications` | `publication_id` | restrict/default | Add already present | Immutable proof relation |
| eligibility history publication | `eod_eligibility_history` | `publication_id` | `eod_publications` | `publication_id` | restrict/default | Add already present | Immutable proof relation |
| live bars publication | `eod_bars` | `publication_id` | `eod_publications` | `publication_id` | restrict/default | Reject for now | Current live table is replaceable and phase-dependent; use implicit guard |
| live indicators publication | `eod_indicators` | `publication_id` | `eod_publications` | `publication_id` | restrict/default | Reject for now | Same as live bars |
| live eligibility publication | `eod_eligibility` | `publication_id` | `eod_publications` | `publication_id` | restrict/default | Reject for now | Same as live bars |
| live artifact ticker | live artifact tables | `ticker_id` | `tickers` | `ticker_id` | restrict/default | Defer with reason | Requires data cleanup/operator DB introspection and test strategy before physical FK |
| publication run | `eod_publications` | `run_id` | `eod_runs` | `run_id` | restrict/default | Reject for now | Avoid circular lifecycle/finalize issue; mirror guard is stronger than FK alone |
| correction prior/new run/publication | `eod_dataset_corrections` | nullable lineage ids | runs/publications | ids | restrict/default | Reject for now | Nullable phase-dependent lifecycle relation |

## Implicit Guard Matrix

| Guard | File | Method | Relation Protected | Failure Behavior | Test Coverage | Status |
|---|---|---|---|---|---|---|
| Live sealed baseline mutation guard | `EodArtifactRepository.php` | `assertLiveArtifactMutationAllowed` | current live artifact vs sealed/current/readable publication | Throws `SEALED_DATASET_MUTATION_BLOCKED` | Hash/seal and static guards | Present |
| Current pointer integrity reasons | `EodPublicationRepository.php` | `determineCurrentIntegrityViolationReasons` | pointer/publication/run/coverage/readable mirror | Returns reason list / fails pointer resolution | Pointer/readable/static tests | Present |
| Post-switch pointer validation | `EodPublicationRepository.php` | `assertCurrentPointerResolvedAfterSwitch` | finalize pointer target | Throws reason-coded runtime error | Publication/finalize tests | Present |
| Publication integrity context | `EodPublicationRepository.php` | `assertPublicationIntegrityContextComplete` | publication/run hash/seal state | Throws before unsafe publish | Hash/seal/finalize tests | Present |
| Evidence audit resolver | `EodEvidenceRepository.php` | `resolvePublicationForEvidenceAudit` | historical publication proof | Reason-coded missing/mismatch context | Evidence historical guard | Present |
| Replay actual-state resolver | `ReplayVerificationService.php` | `resolvePublicationForReplayActualState` | historical replay publication proof | Reason-coded replay mismatch/failure | Replay historical guard | Present |
| Correction lifecycle repository | `EodCorrectionRepository.php` | correction status/linkage methods | correction prior/new/baseline/replacement linkage | Refuses invalid lifecycle transition | Correction tests/static guard | Present |

## Patch Matrix

| Gap | File | Change | Why Safe | Test Coverage | Status |
|---|---|---|---|---|---|
| FK vs implicit policy was not explicit enough for live artifact relations | `DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_INVENTORY.md` | New decision inventory with matrices | Docs-only policy lock, no runtime behavior change | New static guard | Patched |
| Audit docs did not record this scoped decision as active session | `LUMEN_IMPLEMENTATION_STATUS.md`, `LUMEN_CONTRACT_TRACKER.md` | Added current working session/contract entries | Append-only; previous Replay entry preserved below | Audit docs sync guard expected locally | Patched |
| Schema comments did not name final decision explicitly | `Database_Schema_MariaDB.sql` | Added locked policy comment for hybrid FK/implicit integrity | Comment-only, no DDL change | New static guard | Patched |
| Static guard did not lock the new relation decision matrix | `DbIntegrityFkImplicitIntegrityDecisionStaticGuardTest.php` | Added targeted static guard | No runtime behavior change | `php -l` only in container | Patched |

## Validation Matrix

| Command | Result | Tests | Assertions | Status |
|---|---:|---:|---:|---|
| `php -l tests/Unit/MarketData/DbIntegrityFkImplicitIntegrityDecisionStaticGuardTest.php` | No syntax errors detected | n/a | n/a | PASS |
| `php vendor/bin/phpunit tests/Unit/MarketData/DbIntegrityFkImplicitIntegrityDecisionStaticGuardTest.php` | Blocked in container: missing `dom`, `mbstring`, `xml`, `xmlwriter` | n/a | n/a | BLOCKED_CONTAINER_RUNTIME_ENV |
| `vendor/bin/phpunit tests/Unit/MarketData/DbIntegrityFkImplicitIntegrityDecisionStaticGuardTest.php` | OK | 5 | 434 | PASS_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "DbIntegrity"` | OK | 11 | 874 | PASS_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` | OK | 146 | 3470 | PASS_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData` | OK | 416 | 6066 | PASS_LOCAL |

## Final rule

`HYBRID_REQUIRED` is the locked policy validated by operator-local runtime proof. Do not add physical FK to current live artifact publication/run/ticker relations without a separate migration/data-cleanup proof and full local PHPUnit proof. Do not remove existing pointer/history publication FKs. Do not treat implicit lifecycle integrity as optional: every non-FK relation must stay protected by repository/service/evidence/replay/static guard proof. Do not claim entire schema sync failure from this risk; this is scoped live artifact relation hardening. Current status is DONE/LOCKED for this decision scope based on supplied operator-local PHPUnit proof.
