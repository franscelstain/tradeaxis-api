# Change Impact Declaration — `MD-B18-A002`

- ID: `CI-MD-B18-A002-001`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260903-001`
- Predecessor attempt: `MD-B18-A001`, closure `SC-MD-B18-A001-001` — **withdrawn, not edited**
- Remediates: `F-MD-B19-A001-002` (P1)
- Blocking dependency: `MD-DEP-0009` — `MD-B19` is the blocked logical stage; return-to `MD-B19-A001`
- Status: `IN_PROGRESS — REMEDIATION`
- Strategy meaning change: `NO`
- Governance authority change: `NO`

Issued after `MD-B18-A002-BL001` and before any material `MD-B18` mutation, so that it directs the
attempt rather than describing it afterwards.

## Objective

Re-enter `MD-B18` to repair a closure that is not supportable, and re-close it only on proof that
establishes each predicate.

`MD-B18-A001` closed at `121/121`. `F-MD-B19-A001-002` measured two defects behind that figure:

1. **85 of 121 denominator rows carry no `predicate_context=` / `normalized_predicate=`**, which
   `STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` §3 requires and §8 makes a closure
   precondition. `MarketDataReplayVerificationClosureGate` lacked the condition its peer gates
   enforce, so the stage closed with eight green conditions and this unmeasured.
2. **121 predicates were bound to 11 guard pairs — one per family.** Checked against guard bodies,
   38 predicates are established by a different obligation and 20 by a proper subset. **58 of 85 are
   not established by the guard bound to them.**

## What this attempt does NOT do

- It does not edit `SC-MD-B18-A001-001`, `E-MD-B18-A001-001`, or any issued immutable record.
- It does not change strategy bytes. Every predicate here is composed from parent and child text
  already frozen in the owner document; composition is recorded in `notes`, not in `rule_text`.
- It does not weaken a predicate to make a guard fit. Where the guard is insufficient, the guard is
  written or the row returns to `NOT_ASSESSED` — the predicate is not narrowed.
- It does not re-open `MD-B09`, `MD-B10`, `MD-B11` or `MD-B12`, which carry the same §8 records gap
  (38, 7, 129, 1). Those are reported in `F-MD-B19-A001-002` and are not owned here.
- It makes no claim about `MD-B14`–`MD-B17`, which share the one-pair-per-family shape but were not
  measured predicate by predicate.

## 1. Measured entry state

Measured against the matrix this attempt's baseline locks, before any mutation:

| | |
|---|---|
| `MD-B18` rows, all classes | 155 |
| Denominator (`MANDATORY` + `CONDITIONAL_APPLICABLE`) | **121** |
| — carrying a normalized predicate | 36 |
| — **carrying none** | **85** |
| Distinct positive guards across the denominator | **11** |
| Distinct proof families | 11 |
| Predicates per guard | **11.0** — the highest in the package |
| `coverage_status = SATISFIED` at entry | 121 |

The `121/121` is the figure this attempt must stop asserting until re-proven. It is carried here as
the entry measurement, not as inherited coverage.

## 2. Scope of intended mutation

**Traceability matrix (`MUTABLE_TRACEABLE`)** — `MD-B18` rows only:

- add `predicate_context=` and `normalized_predicate=` to all 121 denominator rows;
- return rows whose bound guard does not establish their predicate to `NOT_ASSESSED`, clearing
  `current_evidence_ids` for those rows;
- where the composed predicate belongs to another stage's executable responsibility, correct
  `primary_stage` under §7 rather than manufacturing a guard here.

No row outside `primary_stage = MD-B18` is written. Foreign-row isolation is asserted by the binder
and by a closure condition, after `MD-B18-A001`'s binder silently re-quoted 6226 rows it did not own.

**Proof surface** — `MarketDataReplayVerificationProofSpec`, `ProofGate`, `ProofSelfTest`,
`ProofBinder`, `ClosureGate`:

- the gate gains the reviewed per-predicate proof basis requirement already added to
  `MarketDataOperationsProofGate`;
- the closure gate's `context_binding_and_normalized_predicate` condition, added under
  `MD-B19-A001` when the defect was found, stays and is re-probed here.
- the closure gate admits only A002 Stage/Attempt/Baseline/Epoch evidence and its exact hashed
  manifest. `F-MD-B18-A002-005` records that the gate initially retained A001 constants and treated
  the withdrawn predecessor artifact pack as current A002 proof.

**Current-state generator** — `GenerateMarketDataCurrentState.php`:

- report `CONDITIONAL_PENDING` as a provisional denominator rather than `FINAL`;
- include `BLOCKING` dependencies in the open-dependency summary, not only statuses prefixed
  `OPEN`, so `MD-DEP-0009` remains visible at the decision boundary.

**Tests** — new guards under `tests/Unit/MarketData/` for predicates that currently have none.

**Application code** — none intended. If a guard written here fails against real behaviour, that is
a finding and a fix in its own right, not an occasion to adjust the guard.

## 3. Re-verification required before closure

- every one of the 121 rows carries a parent/context binding and a normalized predicate;
- every row still `SATISFIED` names a guard that establishes **that** predicate, with a reviewed
  one-line basis;
- the closure gate's nine conditions each independently mutation-proven;
- proof gate green in `--bound` mode with zero rows lacking a basis;
- full suite green before and after binding;
- new evidence and a new closure manifest issued; `MD-DEP-0009` discharged.

## 4. Risk this declaration accepts

## Approval execution boundary — 2026-09-13

The user approved the recommended package on this continuation. Before applying it, whole-parent
review found that MD-S050-R0037 governs R0038-R0041, not only the two rows named in the package.
The approved ownership correction for MD-S002-R0004 (MD-B22 primary, MD-B18 supporting), the
downstream reference decision for MD-S004-R0007, and the MD-B21 mirror deferral may be recorded now.
All four conditional siblings are to remain/revert to APPLICABILITY_PENDING, with the parent
condition included in each normalized predicate. This is invalidation, not a final applicability
decision. The two additional siblings require user review under F-MD-B18-A002-006 / MD-DEP-0012.

Intended matrix delta: exactly six source-identity-preserving rows, no SATISFIED or NOT_APPLICABLE
binding. Back up the matrix and every registry before applying, then assert changed-row identity
and counts. Issue a decision, finding and non-closure evidence; register explicit relationships
and MD-B21/MD-B22 dependencies without opening either stage. Do not change the final-denominator
constants to make a provisional gate green. Validate the stopped state and regenerate navigation.

## Successor approval execution boundary — 2026-09-13 22:46 +07:00

The user now approves the recommendation to extend the same parent condition to the two omitted
siblings R0038/R0039. D-MD-B18-A002-002 records this bounded successor authorization before its
execution; D001 and all previously issued evidence remain immutable. There is no strategy change.

Intended next mutations: record a fresh integrated AS_KNOWN capability control and a single-landed
unavailable-capability mutation with byte restoration and green after-control; issue non-closure
evidence for the false parent condition; normalize exactly R0038-R0041 together from pending to
conditional N/A only if that evidence passes. Preserve all source fields and the other 6497 rows,
and tabulate each transition from a pre-write copy. This determines applicability, not satisfaction.

After that evidence-backed normalization, align the live proof surface to the final owned set,
require actual per-predicate bases and review their guard bodies. Do not interpret a nonempty basis
or a smaller denominator as proof. If the review exposes an unresolved owner/scope decision, record
the precise missing obligation and dependency and stop there, without making that decision silently.
Registry writes remain backed up and counted. Validation follows targeted, stage, governance and
full-suite order, with CURRENT_STATE regenerated, never hand-edited. No new attempt or stage opens.

## Validation-discovered binding guard correction — before implementation

The post-normalization targeted run returned 27 tests / 73 assertions with two failures: the
classification gate rejects all four evidenced N/A rows because it equates evidence presence
exclusively with SATISFIED. F-MD-B18-A002-009 records this executable guard defect. Traceability
standard sections 4-6 and Stage Closure Manifest Standard instead require false-condition
evidence/rationale for N/A. Do not clear E005 from the matrix to satisfy the erroneous guard.

Scope now includes MarketDataClassificationConsistencyGate and ClassificationConsistencyGateTest:
admit evidence for exactly REQUIRED / CONDITIONAL_NOT_APPLICABLE / NOT_APPLICABLE as well as the
existing satisfied binding; retain rejection of half-cleared or other evidence-bearing states.
The classification check does not certify evidence sufficiency; B18 normalization still validates
the actual E005 identity and raw/source hashes. Add single-row lifecycle mutation tests and probe
the corrected branch with green controls before and after byte restoration. No authority, matrix,
ownership or application behavior changes are authorized by this correction. Regenerate navigation
and rerun targeted, stage, governance and full-suite validation in order after the repair.

### Original risk statement

The honest outcome of §3 may be that `MD-B18` closes at **less than 121/121**, with predicates
returned to `NOT_ASSESSED` or reassigned to their owning stage. That is an acceptable and expected
result. `MARKET_DATA_DOCUMENT_AUTHORITY.md` §9 forbids manufacturing a pass; a smaller honest
denominator with real proof is the goal, not the restoration of the previous number.

## Successor decision execution boundary — 2026-09-14 (MD-DEP-0014, MD-DEP-0015)

The user decided both open questions on 2026-09-14. D-MD-B18-A002-003 records them, together with
the reviewed R0056 guard design, before any of the mutations below. There is no strategy change and
no change to governance authority.

Intended mutations, in this order:

1. **Decision and matrix.** Record D003 and resolve MD-DEP-0014 as a decision, not as proof. The
   matrix changes in exactly one row: MD-S050-R0056's `supporting_stages` goes from empty to
   `MD-B22`, and its notes gain a D003 marker. Rule text, fingerprint, normalized predicate,
   `MANDATORY`, primary `MD-B18`, `NOT_ASSESSED` and empty evidence stay as they are. The write is
   backed up and the delta counted. The B18 denominator stays at 115, and B22's primary population
   stays at 28.
2. **Environment, outside the repository.** The user supplied a clean MariaDB data directory built
   from the XAMPP template, and isolated the old one as `D:/xampp/mysql/data_260914`. This attempt
   will:
   - verify the clean instance;
   - create `tradeaxis` and `tradeaxis_testing` as `utf8mb4` / `utf8mb4_unicode_ci`, the character
     set and collation the old `db.opt` files declare;
   - apply the 72 repository migrations to both;
   - run the authoritative `DatabaseSeeder`, which the clean-install record for MD-B03-A001
     includes.

   The only access to `data_260914` is a read-only listing of its directories and file sizes.
   Nothing is copied from it, written to it or repaired in it, and the old instance is not started.
3. **Test surface.** `B18ProductionPathReplayFixturesTest` gains the aggregate guard reviewed in
   D003 and a self-test of its harness. The existing fixture bodies are not changed. Mutation
   probes P1–P6 from D003 are run, each landing exactly once, with controls green before and after
   and restoration by byte copy.
4. **Proof basis.** R0056 moves from `INCOMPLETE` to `PROVEN` only after the aggregate runs green on
   the clean instance and every probe is caught. If either fails, it stays `INCOMPLETE`.
5. **Records.**
   - F-010 and MD-DEP-0015 resolve only against the criteria in D003, which report availability,
     integrity and runtime proof separately. Recovering the old data becomes its own non-blocking
     dependency and is never mixed into B18 proof.
   - F-009 closes only on re-executed probes.
   - F-005 stays open until the A002 closure pack exists.

No application code change is intended. Any guard that fails against real behaviour becomes a
finding; the guard is not adjusted to fit.

### Validation-discovered successor for the false-condition evidence — 2026-09-14

Adding the R0056 aggregate changed the bytes of `B18ProductionPathReplayFixturesTest.php`, which is
one of the seven tested sources whose hashes E-MD-B18-A002-005 binds. The normalization gate
therefore reports `CONDITION_EXECUTION_STALE` for that file. The gate is right: E005 was executed
against earlier bytes.

The remedy is fresh execution, not a revert of the guard and not a relaxed hash check. This
attempt will:

1. On the clean instance, re-run E005's exact capability control before and after, and its exact
   AS_KNOWN mutation probe: an exception at the entry of
   `AsKnownReplayExecutionService::execute`, landing once, then restored by byte copy.
2. Issue `E-MD-B18-A002-008`. It supersedes E005 only for execution identity. D002 remains the
   authority for the decision, and the four children, the condition and the frozen source stay as
   they are.
3. Point the normalization gate's evidence constant and path at E008, with every check left as it
   is.
4. Change exactly four matrix rows, R0038–R0041, moving `current_evidence_ids` from E005 to E008,
   with a counted delta.
5. Update the one `ClassificationConsistencyGateTest` assertion that names E005.
6. Probe the gate again: a single-row binding mutation, and a tested-source staleness mutation.
   Each must land once and be caught, with controls green either side.

Applicability, the population and the denominator do not change.

### Successor execution boundary — 2026-09-14 11:06 (F-011 option 1; clean-instance integrity lost)

The user chose F-011's recommendation. D-MD-B18-A002-004 records that choice before any of the
mutations below. Before issuing this amendment, the restarted clean instance was checked read-only.
Its startup shows the future-LSN signature: pages up to LSN of about 2.36 million against a system
LSN of 300,565, with 112 doublewrite copies ignored. The damage covers the system tablespace and 11
`tradeaxis_testing` tables, and is recorded as F-MD-B18-A002-012. That instance therefore no longer
meets D003's integrity criterion.

Intended mutations:

1. **`MigrationIntegrityAndDriftTest::test_no_applied_migration_has_lost_its_file`.** The exact
   out-of-scope orphan set becomes "the out-of-scope orphans are a subset of the declared watchlist
   set", and the in-scope set must stay empty. Three probes, each landing once, with the file
   restored by byte copy:
   - an undeclared watchlist-like orphan injected into the applied-migration list must turn it red;
   - an injected in-scope orphan must turn it red;
   - an injected declared orphan must stay green.

   The only database access is the existing read-only query on the migration ledger.
2. **Records.** Issue D004 and F012, move F011 to `PARTIALLY_RESOLVED`, add notes to MD-DEP-0015,
   update the register resume point, regenerate navigation.
3. **Per-predicate review.** It continues only on guards that do not use MariaDB (113 predicates).
   The two MariaDB guards (R0056, MD-S003-R0025), the full suite and binding wait for a sound
   instance.

This work performs no start, stop, repair, configuration change or file action on either MariaDB
data directory.

### Review-discovered executable defect — 2026-09-14 11:17 (PAIR 01, F-013)

Per-predicate review of PAIR 01 (`B18ReplayBoundInputIdentityContractTest`, 16 predicates) found
that the guard proves the exporter passes the identities through. It does not prove that the
identities bind what the contract names. Reading the code and the schema shows five things:

- **Publication-mode `temporal_identity_hash` and `calendar_status_hash` are always empty.** The
  values are read from `eod_publications`/`eod_runs` columns that do not exist in the base SQL or
  in any migration, and nothing in `app/` writes them.
- **`reason_registry_hash` is a hash of constant state names in both modes.** It never reads the
  reason registry.
- **In publication mode, coverage, eligibility and price-product versions are not bound.**
  `formula_registry_hash` covers indicator configuration only.
- **In publication mode, only the factor-set hash stands for events.** Event revisions and
  verification states are not bound.
- **In publication mode, the dataset boundary is not bound.**

This is recorded as `F-MD-B18-A002-013`.

Intended mutations:

1. **ProofBasis.** Move exactly eight bases from `PROVEN` to `INCOMPLETE`, each with its reason:
   `MD-S050-R0008`, `-R0009`, `-R0012`, `-R0014`, `MD-S019-R0067`, `-R0068`, `-R0069`, `-R0071`.
   The proof gate will then report them by name. That is the honest state, not a regression to
   hide.
2. **Records.** Register F013, update the register and navigation, and add a review ledger entry
   for PAIR 01.
3. **Application code: none yet.** Remediation changes domain behaviour and schema: persisting
   temporal and calendar identities at seal time, deriving the reason-registry identity from the
   registry itself, and binding the missing versions and event states. It follows contract first.
   F013 carries a draft contract, and no code is written until the user has reviewed it.
4. **Review continues** on the remaining pairs. Pairs that depend on the same frozen identities
   (for example `MD-S050-R0002`, "a divergence in any frozen input denies pass") are assessed
   against this defect rather than against their fixtures.

### Review continuation — 2026-09-14 11:23 (PAIRS 05, 14, 15, 20; F-013 carry-forward, F-014)

- **PAIR 14 and PAIR 15.** The guards for `MD-S050-R0002` and `MD-S003-R0003` prove the
  comparison logic, but their run fixture fabricates `temporal_identity_hash` and
  `calendar_status_hash`. The test's own comment admits that without these values the block would
  be empty strings. Production never persists them (F-013 §1), so both predicates depend on a
  frozen identity that does not exist.
- **PAIR 05 and PAIR 20.** The guards for `MD-S019-R0073`, `MD-S003-R0004`, `MD-S005-R0095` and
  `MD-S019-R0009` export one stored replay record twice. They neither rebuild a publication nor
  rerun a replay, whereas MD-S005 L140 and MD-S019 Invariant 1 mean a rebuild or rerun. This is
  recorded as `F-MD-B18-A002-014`.

Intended mutations:

- Move exactly six more bases from `PROVEN` to `INCOMPLETE`, each with its reason: the two above
  under F013, the four under F014.
- Issue and register F014.
- Update F013, the register and navigation.

F014's remediation is expected to be test-only: a rebuild guard and a replay-rerun guard. It
becomes an application change only if executing those guards exposes a defect. No application code
changes here.

### Environment re-entry — 2026-09-14, owner-rebuilt MariaDB instance

The database owner rebuilt `D:\xampp\mysql\data` from `mysql_install_db.exe` (10.4.27) and proved
its lifecycle. The evidence is `D:\xampp\mysql\health_audit_20260914\XAMPP_MARIADB_HEALTH_REPORT.md`.
`data_260914` was not touched.

The report was read in full and the runtime checked read-only before this amendment. The server
runs as PID 7648 from 13:24:15, with `datadir` `D:\xampp\mysql\data\` and only the system
databases. Its LSN is consistent (61497 flushed, 0 dirty pages), and the log and the Windows events
recorded since this start show no error.

Intended actions:
1. Create `tradeaxis` and `tradeaxis_testing` (`utf8mb4_unicode_ci`), apply the 72 repository
   migrations and `DatabaseSeeder` to both, then verify the schema and run `CHECK TABLE`.
2. Run the targeted B18 surface, including the two MariaDB guards (R0056, MD-S003-R0025).
3. Re-evaluate F-012, F-010 and MD-DEP-0015 against this evidence only.

Constraints:
- No large load.
- No start, stop or restart. If one becomes necessary, it follows the report's SOP
  (`mysql_start.bat`; `mysqladmin` TCP shutdown).
- Fail-safe: any `Bad file descriptor`, `mysql.db` corruption, future-LSN, doublewrite anomaly,
  unexpected crash recovery or system-table failure stops runtime proof, with no repair.

F-013 and F-014 are contract and proof issues, and this re-entry does not affect them.

### Review continuation and environment lifecycle — 2026-09-14 14:02

**Environment result.** Both databases were rebuilt on the owner-rebuilt instance: 72/72
migrations, 62 tables, 9 triggers, `CHECK TABLE` clean, 437 reason codes. Targeted run: 373/1686
green, including the R0056 aggregate and MD-S003-R0025 on MariaDB. Full suite: 2258/21556 with
exactly seven failures, all `ProductionCorpusInvariantOracleTest` population controls (F-011(b)).
No fail-safe pattern was logged.

Intended environment records:
- F-012 and F-010 become RESOLVED.
- MD-DEP-0015 stays BLOCKING. Its only unmet criterion is now the full suite, and that waits on the
  MD-DEP-0016 recovery for the corpus oracle.

**Review result.** PAIRS 02, 03, 04, 06, 07 and 08 cover 30 predicates.
- 15 keep `PROVEN`. Each has a reviewed basis and a caught retention probe.
- 15 move to `INCOMPLETE` under `F-MD-B18-A002-015`:
  - five are executable defects;
  - nine are guard gaps or rebinds, fixable in tests;
  - one is an ownership or interpretation question.

Intended mutations:
- Move exactly those 15 bases.
- Issue F015 and `E-MD-B18-A002-010`, and register them.
- Update MD-DEP-0015, MD-DEP-0017, the register and navigation.

No application code changes. The executable defects join the consolidated remediation package
under MD-DEP-0017.

### Review continuation — 2026-09-14 16:03 (PAIRS 09–13, 16–19)

**Review result.** Nine pairs cover 14 predicates. PAIRS 14, 15 and 20 had already been reviewed.
- 3 keep `PROVEN`. Each has a reviewed basis and a caught probe: `MD-S085-R0452`,
  `MD-S050-R0032` and `MD-S050-R0005`.
- 11 move to `INCOMPLETE` under `F-MD-B18-A002-016`:
  - one is an executable defect: replay backfill selects each publication from the current
    pointer, against `MD-S050-R0027`;
  - five are guard gaps. The probes behind them landed and were not caught by any test in the
    directory;
  - four are rebinds to existing executing guards. Discriminating probes showed the recorded basis
    missing a defect that the target caught;
  - one, `MD-S019-R0074`, carries F-013 forward.

Intended mutations:
- Move exactly those bases.
- Issue F016, the PAIR 09–19 ledger and `E-MD-B18-A002-011` with three probe manifests, and
  register them.
- Update MD-DEP-0017, the register and navigation.

No application or test code changes. Everything joins the consolidated remediation package under
MD-DEP-0017.

### Review continuation — 2026-09-14 23:51 (PAIRS 21–40)

**Review result.** Twenty pairs cover 20 predicates.
- 4 keep `PROVEN`: `MD-S082-R0216`, `MD-S082-R0217`, `MD-S082-R0225` and `MD-S082-R0015`.
  Every positive and negative has a caught probe, and R0217's freeze clause is probed on the guard
  that carries it.
- 16 move to `INCOMPLETE` under `F-MD-B18-A002-017`:
  - one executable gap owned with F-013. Only a missing configuration snapshot is `BLOCKED`, and
    a probe against the real service replayed empty temporal, calendar and observation
    identities as `PASS`;
  - seven bases that execute nothing. The recorded pair parses a list and checks method names,
    and discriminating probes left it green while the executing member went red;
  - four guard gaps or rebinds (`MD-S002-R0003`, `MD-S002-R0006`, `MD-S065-R0003`,
    `MD-S003-R0025`);
  - four F-013 carry-forwards (`MD-S003-R0023`, `MD-S004-R0004`, `MD-S082-R0218`,
    `MD-S082-R0224`).

Intended mutations:
- Move exactly those 16 bases.
- Issue F017, the PAIR 21–40 ledger and `E-MD-B18-A002-012` with two probe manifests, and
  register them.
- Update MD-DEP-0017, the register and navigation.

No application or test code changes. Everything joins the consolidated remediation package under
MD-DEP-0017.

### Review continuation — 2026-09-15 00:29 (PAIRS 41–68; review complete)

**Review result.** Twenty-eight pairs cover 28 predicates. PAIR 69 (`MD-S050-R0056`) was proven
earlier by the executed-corpus aggregate, so the per-predicate review of all 69 pairs and 115
predicates is complete.

- 19 keep `PROVEN`. Each has a caught behaviour probe. Where the recorded negative is
  structural or a stub, a vacuity probe shows the positive carries its own control.
- 9 move to `INCOMPLETE` under `F-MD-B18-A002-018`. Details: three pass against a cutoff wall (MD-S050-R0022, MD-S050-R0023, MD-S041-R0032), two never exercise knowledge time (MD-S003-R0011, MD-S050-R0028), three are mistargeted (MD-S003-R0014, MD-S050-R0025, MD-S055-R0025), and one leaves the null-reason class uncompared (MD-S050-R0029); MD-S041-R0032 and MD-S055-R0025 also carry F-013.

Intended mutations:
- Move exactly those 9 bases.
- Issue F018, the PAIR 41–68 ledger and `E-MD-B18-A002-013` with its probe manifest, and
  register them.
- Update MD-DEP-0017, the register (resume point: draft the consolidated remediation package) and
  navigation.

No application or test code changes.
