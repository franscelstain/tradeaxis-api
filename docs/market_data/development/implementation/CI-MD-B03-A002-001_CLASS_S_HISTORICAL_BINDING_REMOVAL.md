# MD Change Impact Declaration — CI-MD-B03-A002-001

- ID: `CI-MD-B03-A002-001`
- Stage / Attempt / Baseline / Epoch: `MD-B03` / `MD-B03-A002` / `MD-B03-A002-BL001` / `MD-REBASELINE-20260820-001`
- Record class: `MUTABLE_TRACEABLE`
- Issued: 2026-08-22, after the A002 baseline lock and before any A002 test, implementation, or record mutation.

## Why this attempt, and why now

`MD-B01` stands at `206/207`. Its one remaining row, `MD-S020-R0067`, is blocked by `F-MD-B01-A003-001`, whose subject is the wording of a frozen strategy contract. `DOCUMENT_CHANGE_POLICY.md` §2 requires explicit user authorization for a strategy-byte change, so no implementation attempt can resolve it. `MD-B01` therefore stays the blocked logical stage.

`STAGE_EXECUTION_AND_REWORK_STANDARD.md` §3 permits a blocked logical stage to remain `IN_PROGRESS` while another stage is opened or re-entered to remediate a **declared** dependency. `MD-DEP-0003` is declared, registered, and `OPEN`; `MD-B03` already owns half of it and closed the Class R half under `MD-B03-A001`. This attempt is the recorded successor for the Class S half.

The block on `MD-B01` was re-tested before this attempt was opened rather than inherited. `CONSUMER_READ_CONTRACT_LOCKED.md` names `eligible = 1` once, in a list of signals that cannot establish freshness, and states `data_usable` nowhere. The sentence prevents a *freshness* misreading; `MD-S020-R0067` requires the repetition that prevents the *tradability* misreading. Those are different misreadings, so the finding stands and the rule is not satisfiable by reinterpretation.

## The defect

`D-MD-20260820-02` decomposed 26 composite audit documents into role-pure `LX-MD-*` extracts under `records/history/archive/semantic/` and removed the physical originals. **24 test files still bind executable assertions to those removed paths.** They produce 67 failures and 4 errors in `tests/Unit/MarketData` across 1740 tests, and have done so for the whole epoch.

`CURRENT_VERIFICATION_REBASELINE_STANDARD.md` states that all pre-epoch `PASS/FAIL/PARTIAL/DONE/READY/CONFORMANT` statements have **zero current-verification effect**. `F-MD-B00-A001-001` adds that repointing these tests at the `HISTORICAL_ONLY` extracts would smuggle inherited PASS into the current epoch and must be rejected in review.

So these assertions cannot be repaired by repointing. They are executable bindings to material that governance has already ruled out as proof — harmful residue under `IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md`, and a permanently red suite besides. A control that has been red for an entire epoch stops being read, which is the same failure mode `F-MD-B01-A001-001` records for an unreachable coverage target.

## Scope, and the line this attempt does not cross

Measured at this baseline, the 24 files fall into two shapes:

| Shape | Files | Content |
|---|---|---|
| The file's whole subject is the retired audit ledger — no assertion touches a live surface | 6 | `AuditCrossReferenceIntegrityTest`, `AuditDocsSynchronizationStaticGuardTest`, `OpsEnvironmentBaselineStaticGuardTest`, `OperationalReadinessStaticGuardTest`, `TestingDatabaseIsolationStaticGuardTest`, `TestCoverageBehavioralStaticGuardTest` |
| The file guards live code, schema, or configuration **and also** asserts on a removed inventory | 18 | the remainder, including `GlobalConvergenceClosureTest`, whose dead path is built by concatenation |

**In scope:** removing the executable bindings to `HISTORICAL_ONLY` documents. This needs no new current authority — governance has already ruled the target inadmissible — and it is residue removal under §19 of the execution contract. Where a file retains live assertions, those assertions stay and keep guarding. Where nothing survives removal, the file is retired.

**Not in scope:** writing *new* current-authority guards to replace whatever protection the removed assertions provided. That requires the owning stage's current requirements, which `MD-B06`, `MD-B15`, `MD-B17`, `MD-B19`, `MD-B21`, and `MD-B22` have not yet established. Each removal is therefore recorded against its owning stage so the replacement obligation is visible rather than implied.

## Planned proof method

1. Enumerate every dead-path binding by resolving each referenced document against the filesystem, including paths assembled by concatenation, so the population is measured rather than assumed.
2. For each file, separate assertions that resolve only through a removed document from assertions that reach a live surface.
3. Remove the former; keep the latter; retire only a file with nothing left.
4. Re-execute each touched suite individually and the whole directory, and require the failure count to fall to zero without any suite losing a live invariant.
5. Prove the removal did not silently delete protection: for every retired file, record which invariant it held and which stage now owes a replacement.
6. Add a gate that fails when any test binds to a path that does not resolve, so this defect class cannot recur silently.

## Affected areas

| Area | Impact |
|---|---|
| Strategy | None; no strategy byte is read or written. |
| Governance | `MD_DEPENDENCY_REGISTRY.csv` is updated for `MD-DEP-0003`, and a new dependency is registered for the `MD-B01` strategy blocker that §3 requires to be identified by ID. |
| Schema / configuration / runtime / application code | No mutation planned. |
| Tests | Material: up to 24 files lose their `HISTORICAL_ONLY` bindings; some are retired entirely. |
| Traceability | None expected. No `MD-B01` row depends on these files; if any row turns out to, its binding is invalidated rather than carried. |
| Evidence | Additive A002 evidence. `E-MD-B03-A001-001` and `-002` remain unedited. |
| Raw artifacts / storage | None. The removed targets are documents, not runtime artifacts. |

## Compatibility and residue risk

The dominant risk is deleting a guard that still protects something. A file whose docblock describes a live invariant may hold that invariant in one assertion and a dead binding in the next, and removing the file wholesale would take both. Every file is therefore read before it is touched, and the live assertions are the reason a file survives.

Second risk: a replacement obligation that disappears with the code. A retired guard leaves a hole that nothing records unless the record is made deliberately.

Third risk: measuring the population with a pattern that misses concatenated paths. `GlobalConvergenceClosureTest` builds its dead path from a fragment and would be absent from a naive scan of quoted literals — it was found by executing the suite, not by reading it.

## Dependencies and relationships

- Successor to `MD-B03-A001`; predecessor baseline `MD-B03-A001-BL001`.
- Remediates `MD-DEP-0003`, declared and registered before this attempt opened.
- `MD-B01` remains the blocked logical stage; return-to after this remediation is `MD-B01`, which still awaits the authorised strategy-change process.
- `F-MD-B00-A001-001` owns the Class R/Class S taxonomy and is updated with the Class S result.

## Strategy semantic change

`NO`.

## Executed impact and result

- Strategy, schema, configuration, provider behaviour, runtime, and application code changed: `NO`. No strategy byte was read or written.
- **71 test methods removed across 24 files; 2 files retired.** `AuditCrossReferenceIntegrityTest` and `AuditDocsSynchronizationStaticGuardTest` had no surviving test — every method in each checked the internal consistency of the LUMEN audit ledger. The other 22 files kept their live tests.
- **`tests/Unit/MarketData`: 1740 tests / 4 errors / 67 failures → 1669 tests / 11362 assertions / 0 errors / 0 failures.** 1740 − 71 = 1669 exactly, so nothing outside the removal set was lost and nothing else broke. **The whole suite now passes: 1673 tests, 11373 assertions.** It is the first green suite of the epoch; at `MD-B00-A001` closure it stood at 1488 tests with 26 errors and 108 failures.
- Five dead private helpers removed. A sixth, `ProductionValidationRuntimeProofStaticGuardTest::registeredMarketDataCommands`, was attempted, broke the parse, and was reverted automatically by the per-step verification. It is inert and is recorded rather than forced.
- Tooling added: `MarketDataHistoricalBindingRemoval` (governed per-method removal, presence-checked, retiring a file only when no test survives) and `TestPathBindingIntegrityTest` (fails when a test binds to a documentation path that does not resolve, or reads a sealed `LX-MD-*` extract as current proof).
- **Scope extended once, deliberately, and recorded.** `TestPathBindingIntegrityTest` found on its first execution that `OPERATIONAL_RUNBOOK.md` — a current implementation document — still instructed operators to execute a gate "defined in" `docs/market_data/ops/OPS_ENVIRONMENT_BASELINE.md`, removed by the same decomposition. The existing guard passed because it checked only that the runbook *contained the string*. The clause now states the gate directly (the version bounds and blocking reason codes were already in the runbook text), is not repointed at the `HISTORICAL_ONLY` extracts, and records that no current document owns the environment baseline as a contract. This was a documentation edit the Affected areas table above did not plan; it is a dangling pointer left by the decomposition `MD-DEP-0003` covers, and A001 rebound 60 such paths under the same dependency.
- Governance: **`MD-DEP-0005` registered.** §3 requires a blocked logical stage to name its blocking dependency by ID, and the sole blocker of `MD-B01` had never been registered as one — invisible to the dependency registry and to the generated current state. It carries both resolutions `F-MD-B01-A003-001` names as defensible, and is marked as having no implementation remediation path. `MD-DEP-0003` updated with the Class S result.
- Negative proof: two on-disk mutations against the new gate, each verified as landed, each failing it, then restored. In-tool: every method name is presence-checked before removal — the first run refused seven names that did not exist, and the set was regenerated from the measured failure list rather than from recollection.
- **A tool defect was found and fixed mid-execution.** The removal tool's docblock absorption used `(?:(?!\*\/).)*` anchored with `$` under `/s`; being greedy it began at the first docblock in the file and ran to the last `*/` before the target method, deleting a class's closing brace. It emptied two files. `php -l` across the whole test directory after each application caught it before anything reached a suite run, the corpus was reconstructed from git, and the tool now walks backwards line by line. Orphaned helpers became report-only.
- Raw artifacts/storage: none inspected, mutated, or claimed.
- Traceability: unchanged. No `MD-B01` row depended on any removed file, verified by re-running all six gates.
- Findings/dependencies: `F-MD-B00-A001-001` Class S binding half closed, replacement-guard half open; `F-MD-B01-A003-001` re-tested and confirmed, now also `MD-DEP-0005`; `F-MD-B01-A014-001`, `F-MD-B01-A001-001`, `F-MD-B01-A008-001` unchanged.

## Current boundary

The A002 executable scope is exhausted. `MD-B03` does not close here: the replacement guards for six operator-facing contracts need current authority their owning stages have not written, and the stage's own closure prerequisites remain. Execution returns to `MD-B01`, which is blocked by `MD-DEP-0005` and has no implementation remediation path.
