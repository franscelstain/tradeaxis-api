# MD Change Impact Declaration — CI-MD-B03-A003-001

- ID: `CI-MD-B03-A003-001`
- Stage / Attempt / Baseline / Epoch: `MD-B03` / `MD-B03-A003` / `MD-B03-A003-BL001` / `MD-REBASELINE-20260820-001`
- Record class: `MUTABLE_TRACEABLE`
- Issued: 2026-08-22, after the A003 baseline lock and before any A003 test, implementation, or record mutation.

## Why this attempt

`MD-B01` is blocked by `MD-DEP-0005`, which needs an authorised strategy revision or a reviewed governance decision — neither available to an implementation attempt. `MD-B03` is the active remediation stage under §3 and has never been evaluated for closure. Three things stand between it and a verdict, and all three are executable now:

1. **Its `0/0` strategy coverage has never been validated.** `MD-DEP-0004` makes ownership validation a per-stage entry obligation; `MD-B03` has not performed it. A `0/0` denominator that nobody checked is not the same as a stage that genuinely owns no rules, and `STAGE_CLOSURE_MANIFEST_STANDARD.md` forbids claiming `DONE` from a provisional denominator.
2. **Half of its frozen exit intent has no test.** `MD_IMPLEMENTATION_BUILD_SEQUENCE.md` states the exit gate as "clean-install/upgrade path tersedia untuk setiap feature berikut; **belum ada nullable placeholder yang dianggap conformant**." `MigrationIntegrityAndDriftTest` quotes that sentence in its own docblock and proves only the first half — the word `nullable` appears once in the file, inside the quotation. The second half is asserted by no test in the repository.
3. **`MD-B03-A001` was a material attempt with no correlated Change Impact Declaration.** It fixed seven defects in migrations, the seeder, and the drift detector. Governance requires a CI for material attempts, and closure requires it to be present.

MariaDB 10.4.27 is reachable, so the deliverable proofs are executable rather than environment-blocked.

## Affected strategy rules

None. `MD-B03` owns zero traceability rows, and this attempt's first task is to establish whether that is correct rather than to assume it. No row is advanced, demoted, or rebound.

## Planned proof method

1. **Validate the `0/0` claim from both directions.** Search the matrix for rules whose subject is an `MD-B03` deliverable, and read the stage's exit intent to establish what its acceptance criteria actually are. A word search alone is the weak-pattern trap this stage has already recorded; the build sequence is the authority for what the stage must deliver.
2. **Execute the clean-install/upgrade proof** against the reachable database rather than citing the A001 run. Historical `PASS` is not inherited.
3. **Build the missing nullable-placeholder guard** against the owner contract, `DB_Schema_And_Migration_Sync_Contract_LOCKED.md`, which states the invariant precisely: nullable rollout columns "are not a weaker accepted production state; a later enforcement migration is required after verified backfill", and backfill and enforcement must be "distinct observable stages".
4. Pair every absence assertion with a positive locator, and prove each guard fails closed under a landed mutation.
5. **Do not back-date the missing A001 Change Impact Declaration.** §13 forbids repairing a timing deviation by writing the record late or editing issued evidence. It is declared as a governed finding and its effect on closure is stated plainly.
6. Issue a closure manifest only if every criterion in `STAGE_CLOSURE_MANIFEST_STANDARD.md` is met. If one is not, record exactly which.

## Affected areas

| Area | Impact |
|---|---|
| Strategy | None; no strategy byte is read or written. |
| Schema / migrations | **No mutation.** The nullable V2 columns stay nullable: the contract requires a verified backfill before any enforcement migration, and this attempt performs no backfill. Enforcing them here would be the exact defect the exit gate forbids. |
| Application code / configuration / runtime | No mutation planned. |
| Tests | Material: a new guard for the unproven half of the exit gate. |
| Traceability | None. `MD-B03` holds no rows. |
| Evidence | Additive A003 evidence and, if criteria are met, an `SC-MD-B03-A003-001` closure manifest. A001 and A002 records remain unedited. |
| Raw artifacts / storage | Database execution against a live MariaDB instance. Any artifact the proof depends on is bound to the evidence record. |

## Compatibility and residue risk

The dominant risk is closing a stage because its numbers are small. `0/0` coverage and a green suite make `MD-B03` look finished; the closure standard asks for more than a percentage, and one of its acceptance criteria has been untested for the whole epoch.

Second risk: fixing the nullable gate by enforcing nullability. That would look like progress and would violate the contract, which requires backfill first and treats a premature enforcement migration as the failure, not the fix. This attempt guards the invariant; it does not change the columns.

Third risk: proving criterion 1 by citing A001. The clean-install result must be re-executed, because `CURRENT_VERIFICATION_REBASELINE_STANDARD.md` gives historical results zero current-verification effect.

## Dependencies and relationships

- Successor to `MD-B03-A002`; predecessor baseline `MD-B03-A002-BL001`.
- `MD-DEP-0003` remains `OPEN_NON_BLOCKING`; its replacement-guard half is owned by `MD-B19`/`MD-B15`/`MD-B17`/`MD-B21`/`MD-B22`, not by `MD-B03`. The `MD-B03` stage row still says otherwise and is corrected by this attempt.
- `MD-DEP-0004` entry obligation is performed here for `MD-B03`.
- `MD-B01` remains the blocked logical stage; return-to is unchanged.

## Strategy semantic change

`NO`.

## Executed impact and result

- Strategy, schema, migrations, configuration, application code, and runtime behaviour changed: `NO`. **The nullable V2 columns were deliberately left nullable** — the contract requires a verified backfill before enforcement, and enforcing them to satisfy the gate would have been the defect the gate exists to prevent.
- **`0/0` coverage validated, not assumed.** Ten matrix rules mention an `MD-B03` deliverable; every one belongs to another stage and names migrations only as a prohibition qualifier. The build sequence settles it: `MD-B03`'s acceptance criteria are an enabling exit intent, not traceability rows. The denominator is no longer provisional.
- **Exit criterion 1 re-executed, not inherited.** Isolated database: 62/62 migrations applied in one batch, 51 tables, exit 0; second run reports `Nothing to migrate`; reason registry seeded 43 → 436. The database was dropped afterwards.
- **Exit criterion 2 now has a test for the first time.** The word `nullable` appeared once in `MigrationIntegrityAndDriftTest`, inside its own quotation of the exit gate. Four guards were added against `DB_Schema_And_Migration_Sync_Contract_LOCKED.md`: matrix/schema agreement on relock state, no rollout column enforced `NOT NULL` before a verified backfill, the progress-not-closure statements still present, and a detector-adequacy check. Three landed mutations proved them red; all restored.
- Test execution: `MigrationIntegrityAndDriftTest` 9 tests / 21 assertions, plus the five other stage-deliverable suites, all PASS. Whole suite: **1677 tests, 11388 assertions, 0 failures**.
- **Closure withheld on exactly one criterion.** `MD-B03-A001` was material and has no correlated Change Impact Declaration; `CHANGE_IMPACT_DECLARATION_STANDARD.md` §3 blocks `DONE` for that reason alone. Every other criterion is met, and the full table is recorded in `E-MD-B03-A003-001`. No `SC-MD-B03` manifest was issued, so no record claims a closure the criteria do not support.
- The missing declaration was **not** written. §3 requires it to exist early enough to guide the attempt, and §13 forbids repairing a timing deviation by writing the record late. Raised as `F-MD-B03-A003-001`, registered as `MD-DEP-0006`.
- Records: the stale `MD-B03` stage-row claim that closure needs the per-stage replacement guards is corrected — that obligation moved to `MD-B19`/`MD-B15`/`MD-B17`/`MD-B21`/`MD-B22` at A002.
- Raw artifacts/storage: no `storage/**` artifact required, produced, or claimed; the runtime proof is database execution bound to this attempt's evidence.

## Current boundary

`MD-B03`'s technical work is complete. The stage is held open by `MD-DEP-0006`, a reviewed governance decision with no implementation remediation path — the same shape as `MD-DEP-0005` holding `MD-B01`. Both open stages now name their blocker by ID.
