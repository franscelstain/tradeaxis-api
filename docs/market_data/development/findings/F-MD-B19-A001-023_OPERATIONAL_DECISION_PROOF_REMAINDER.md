# Finding — F-MD-B19-A001-023

- ID: `F-MD-B19-A001-023`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Raised at: 2026-10-20T21:38:12+07:00
- Severity: `P2`
- Status: `OPEN`
- Evidence: `E-MD-B19-A001-034`
- Change impact: `CI-MD-B19-A001-001`

## Exact remaining operational predicates

E034 proposes 25 predicate bases: 6 commands/runbook, 1 scheduling, and 18 operator decision rows. Independent acceptance remains 595/750 until review; 620/750 is proposed. Formal SATISFIED is 0/750. The operations gate remains FAIL with 130 bases missing and eight families without a family guard.

The eight remaining command predicates are MD-S064-R0001, R0006, R0007, R0009, R0011, R0012, R0013 and R0016. They require command-specific proof for shared authority, historical rollback, as-known replay, retention boundaries, pre-apply previews, force and anti-mutation safety, and the full supported-runtime command matrix.

The eight remaining scheduling predicates are MD-S078-R0003, R0004, R0006, R0007, R0009, R0015, R0017 and R0019. The remaining obligations concern complete logical-key identity/checkpoint semantics, every state-changing fencing path and audited lease recovery, deployment activation/rehearsal and provider-production behavior, schedule-specific prohibition proof, and the exact capability boundaries for manual work and clean schedule records.

The 25 remaining operator rows are MD-S071-R0002, R0004, R0006, R0008, R0009, R0010, R0013, R0014, R0016, R0018, R0021, R0024-R0029, R0036-R0042 and R0048. The exact row-level map and tests are in E034_per_predicate_map.json. Missing executable branches include schema-drift outcome through the full operator decision, hash/artifact-completeness remediation, missing-hash readability, unsafe post-switch ambiguity, replay scope/config diagnosis, and the anti-ambiguity acceptance rule.

## Owner authority boundary

F018 severity escalation and incident closure remain OPEN pending the Project Owner decision. E034 does not infer escalation thresholds or closure criteria. Relevant decision rows remain held, including A immediate escalation, B escalation for reproducibility drift, D critical/escalate actions, E escalation-if-needed, F seriousness/severity and the cross-tree anti-ambiguity score. Independent failure refusal, pointer safety, correction and replay mismatch behavior remains eligible for separate proof.

## Verification observations

`B19RunEvidenceMariaDbProofTest` was updated only because the current MariaDB append-only event migration correctly rejected its former UPDATE/DELETE mutation technique. The revised test asserts both refusals and preserved event bytes/count, then verifies run export remains complete; contradictory payload interpretation remains covered by the telemetry proof test. No production code or frozen authority changed.

The targeted `B18ReplayComparisonExhaustivenessTest` execution returned 69 failures in 94 tests (267 assertions), mostly `NOT_ADMISSIBLE`/`BLOCKED` where its older fixture expectations demanded replay MATCH/MISMATCH/FAIL. This batch did not modify replay production, replay fixtures, or B18 authority. The failure remains explicit for independent disposition and is not represented as a pass or as an expected-failure aggregate.

Deployment activation, full-suite PASS, candidate-v6, formal SATISFIED, B19 closure, and independent admission of E034 proposals are not claimed.


## E035 successor trace and current resume

E035 preserves the E034 record and package as immutable. It adds per-predicate source and execution trace for the 22 E034 proposals held for traceability: four command/runbook rows and 18 operator-decision rows. Each row is bound to its exact canonical traceability matrix entry, positive/negative test method, source hash, method range, assertion lines, and hashed testdox output. These remain proposed until independent review; they are not counted again. The accepted baseline remains 598/750, repository proposal 620/750, formal SATISFIED 0/750. E035 adds zero new predicate bases.

The E035 review identified and repaired a command defect in `ScheduledWorkflowCommand::processExitCode`: retryable timeout, owner lock conflict, and blocked missing-calendar outcomes all returned process code 2. MariaDB red runs observed 2 where the command contract requires distinct categories; restored runs now return 3, 5, and 6 respectively, while preserving scheduler outcome, reason set and run/publication/date fields. Readable=0, invalid input=1, held=2 and integrity=4 are also asserted by focused controls. The selected MariaDB suite passes 5/118. Actual evidence export now runs through `ExportEvidenceCommand` on MariaDB: complete evidence is ADMITTED_COMPLETE; contradictory source identity remains ADMITTED_INCOMPLETE despite process code 0; conflicting selectors refuse before output creation. The exact E035 trace and raw output inventory are in the successor record and review archive.

Bounded impact: 37 accepted proof-basis rows reference `B19RunEvidenceMariaDbProofTest::test_real_sources_attempts_run_pack_anomaly_and_historical_resolution`; the immutable E034 candidate map also had the held MD-S064-R0008 link to this method, which E035 replaces with the direct evidence-command test. That method's direct event UPDATE/DELETE counterfactual was replaced because MariaDB's append-only trigger correctly refuses mutation. Its current MariaDB class passes 2/195, asserts `RUN_EVENT_APPEND_ONLY`, unchanged event count/bytes and complete export; contradictory event identity remains independently covered by the telemetry refusal test (1/3). E034's historical acceptance is not withdrawn; the current method has a new source identity and current-build execution evidence.

The B18ReplayComparisonExhaustivenessTest failure remains visible at 69 failures / 94 tests / 267 assertions from E034. The replay source, test and authority were not changed in E035; this is not reclassified as expected or PASS. The exact remaining operational scope is still 8 commands, 8 scheduling and 25 operator predicates; deployment activation and F018 severity/closure remain blocked by unavailable proof/authority.

MD-B19-A001: after E035 successor trace and runtime correction, resume the remaining 41 operational predicates: commands_and_runbook 8 (MD-S064-R0001,R0006,R0007,R0009,R0011,R0012,R0013,R0016); scheduling_and_locking 8 (MD-S078-R0003,R0004,R0006,R0007,R0009,R0015,R0017,R0019); operator_decision_trees 25 (MD-S071-R0002,R0004,R0006,R0008,R0009,R0010,R0013,R0014,R0016,R0018,R0021,R0024-R0029,R0036-R0042,R0048). Keep F018 severity/closure held, R0324 HELD under D008/F016, MD-DEP-0025 ACTIVE, D003 NULL semantics, candidate-v5 and prior E032 residual holds. Accepted 598/750; proposed 620/750; formal SATISFIED 0/750. No candidate-v6, B19 closure or full-suite claim.


## E036 R0043 trace repair and operational scope disposition

E036 closes E035's single trace gap for `MD-S071-R0043`. The positive service test output already names its exact method; the negative operator-command test was rerun alone with `--testdox` (1 test / 7 assertions) and its transcript is linked with the test source SHA-256 and assertion lines. The mismatch returns nonzero with mismatch reason/context and does not force evidence export. This repairs traceability for a previously proposed basis: no denominator increase or double count. Independent accepted baseline is 619/750, repository proposal 620/750, formal SATISFIED 0/750. E034 and E035 remain immutable.

The remaining 41 predicates remain open. Their specific proof gaps are enumerated by family above: eight command-specific safety/runtime branches, eight scheduling/locking branches including deployment-specific evidence, and 25 operator decisions with missing executable branches; seven F018 escalation/severity/closure rows remain held for Project Owner authority. No additional basis is proposed from generic or adjacent tests. B18's 69/94 replay-comparison failure remains visible.

**Single exact resume point:** MD-B19-A001: continue commands MD-S064-R0001/R0006/R0007/R0009/R0011/R0012/R0013/R0016; scheduling MD-S078-R0003/R0004/R0006/R0007/R0009/R0015/R0017/R0019; operator MD-S071-R0002/R0004/R0006/R0008/R0009/R0010/R0013/R0014/R0016/R0018/R0021/R0024-R0029/R0036-R0042/R0048. Keep deployment activation and F018-dependent predicates held; preserve R0324/D008/F016, MD-DEP-0025, D003 NULL semantics, strategy freeze and candidate-v5. Accepted baseline 619/750; proposed 620/750; formal SATISFIED 0/750.

## E037 hash and artifact operator decisions

Four new operator decision bases are proposed: `MD-S071-R0009`, `R0013`, `R0014`, `R0018`. The exact positive and negative MariaDB methods, assertion lines, current source hashes, independently expected decisions and raw output hashes are in `E037_per_predicate_map.json`. A complete real publication seals and becomes readable. Removing an indicator projection after HASH produces a blocked seal command with an upstream-completeness action, no seal and no requested-date pointer. Independently nulling each of the bars, indicators and eligibility hashes produces `RUN_SEAL_PRECONDITION_FAILED`, an ERROR `SEAL_BLOCKED` event, and no readable requested-date publication. The restored-row retry of the failed run remains blocked by its owner fence. This does not prove an executable upstream repair workflow, so `R0010` remains unproved.

The price-scale apply command previously returned success while performing detection only; it now refuses the prohibited apply. A new MariaDB migration refuses sealed-publication identity updates and deletion while allowing current-pointer metadata transitions needed by correction. These production corrections are bounded safety work; `MD-S064-R0013` remains unproved because the entire direct-current-row and canonical/history prohibition set has not been established by this batch. E037 proposes 624/750 against the independently accepted 620/750; formal SATISFIED remains 0/750. The operations gate still fails with 126 missing bases and eight unassigned family guards. The other 37 operational rows remain without complete proof; F018's seven authority-dependent rows and deployment activation remain held. E015–E036, D003 NULL, R0324/D008/F016, MD-DEP-0025, candidate-v5 and the B18 69/94 replay failure remain visible and unchanged.


## E038 current publication row fencing and recovery

E038 adds one proposed predicate basis, MD-S071-R0027. The migrated MariaDB correction world demonstrates deterministic restoration of a known safe prior publication after successive corrections and provider failure, and refuses the same restore paired with a nonexistent run identity while preserving the current pointer. A counterfactual direct SQL attempt to mark the superseded sealed row current fails on the new database trigger. Publication promotion and restore reorder pointer/mirror changes within their existing transactions. The migrated test checks all 30 sealed-publication identity fields plus insert/update/delete refusals across all three sealed history families. Green controls: correction/recovery 1/76, scheduled publish/idempotent skip 1/40, price-scale/history/identity matrix and migration rollback 1/88.

`MD-S064-R0013` remains held: the protected sealed-history, sealed-publication identity, direct-current `is_current` mirror and price-scale command surfaces are exercised, but direct SQL mutation of both the live `eod_bars` canonical projection and `eod_current_publication_pointer` has no new database fence. The new trigger protects the row mirror when the pointer names another publication; it does not prohibit direct pointer-table writes. No command basis is claimed. The E038 mutation of the pointer guard failed at the intended direct-current assertion; migration bytes were restored and the trigger reinstalled before green. Repository proposal is 625/750 against independently accepted E037 baseline 624/750; formal SATISFIED remains zero. Remaining operational scope is 36: commands 8, scheduling 8, operator decisions 20. The deployment and F018 authority holds remain exact and visible.
