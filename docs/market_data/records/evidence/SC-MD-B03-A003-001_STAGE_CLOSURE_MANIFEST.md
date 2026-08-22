# MD Stage Closure Manifest — SC-MD-B03-A003-001

- ID: `SC-MD-B03-A003-001`
- Stage / Attempt / Baseline / Epoch: `MD-B03` / `MD-B03-A003` / `MD-B03-A003-BL001` / `MD-REBASELINE-20260820-001`
- Change Impact Declaration: `CI-MD-B03-A003-001`
- Governing decision: `D-MD-20260822-05`
- Evidence: `E-MD-B03-A003-001`, `E-MD-B03-A002-001`, `E-MD-B03-A001-001`, `E-MD-B03-A001-002`
- Role: `EVIDENCE`, scope `STAGE_CLOSURE_MANIFEST`, mutability `IMMUTABLE_AFTER_ISSUE`
- Supersedes: none — this is the first `MD-B03` closure

## Objective achieved

`MD-B03` builds the migration framework, additive schema skeleton, repository interfaces, reason registry, and test-harness skeleton. Its frozen exit intent is an enabling condition, not a set of traceability rows: "clean-install/upgrade path tersedia untuk setiap feature berikut; belum ada nullable placeholder yang dianggap conformant."

Both halves are proven, and the second had no test anywhere in the repository until `MD-B03-A003`.

## Required coverage

`0/0`, **validated rather than assumed**. `MD-DEP-0004` makes ownership validation a per-stage entry obligation and `MD-B03` had never performed it; the closure standard forbids closing on a provisional denominator. Checked both ways: the ten matrix rules whose text mentions an `MD-B03` deliverable all belong to other stages and name migrations only as a prohibition qualifier, and the build sequence confirms the stage's acceptance criteria are its exit intent. No rule names `MD-B03` as proof-owning stage.

## Exit criterion 1 — clean install and upgrade path

Re-executed, not inherited; `CURRENT_VERIFICATION_REBASELINE_STANDARD.md` gives the A001 run zero current-verification effect.

| | |
|---|---|
| Migration files | 62 |
| Applied on an isolated database | **62, one batch, exit 0** |
| Tables created | 51 |
| Second run | `Nothing to migrate` — the upgrade path is idempotent |
| Reason registry | `MarketDataReasonCodesSeeder` executed on that clean install: `eod_reason_codes` 43 → 436 |

The scratch database was created and dropped by the attempt.

## Exit criterion 2 — no nullable placeholder treated as conformant

`MigrationIntegrityAndDriftTest` quoted this clause in its own docblock and proved only the first half; the word `nullable` appeared exactly once in the file, inside that quotation, for the whole epoch.

Four guards now hold it against `DB_Schema_And_Migration_Sync_Contract_LOCKED.md`, which states the invariant exactly — nullable rollout columns "are not a weaker accepted production state; a later enforcement migration is required after verified backfill":

- the nine-area V2 rollout matrix and the deployed schema must agree that no area is relocked while its columns are nullable;
- no later migration may enforce `NOT NULL` on any of the 69 distinct V2 rollout columns while every area reads `open`;
- the contract's three statements and the dictionary's "implementation progress, not closure" must remain present;
- both enforcement detectors must fire on a violation and stay silent on an ordinary nullable declaration.

**The nullable columns were deliberately not enforced.** The contract requires verified backfill first; enforcing them to make the gate look satisfied would have been the defect the criterion exists to prevent.

## Tests and gates actually executed

| Gate | Result |
|---|---|
| `MigrationIntegrityAndDriftTest` | `PASS` — 9 tests / 21 assertions, 5 pre-existing plus 4 added |
| `MarketDataSqliteSchemaSyncTest` | `PASS` — 6 tests / 683 assertions |
| `ReasonCodeSeedExecutionTest` | `PASS` |
| `PublishedColumnHashCoverageTest` | `PASS` |
| `ArtifactIntegrityPolicyTest` | `PASS` |
| `DestructiveMigrationGuardTest` | `PASS` — 23 tests / 52 assertions |
| Database execution | `php artisan migrate --force` and `db:seed` against MariaDB 10.4.27 |
| PHPUnit | **1686 tests, 11432 assertions, 0 errors, 0 failures** |

Three landed mutations turn the new nullable guards red: an area marked relocked while its columns stay nullable, the dictionary's not-closure sentence rewritten, and a rollout column enforced `NOT NULL`.

## The process deviation, recorded not erased

`MD-B03-A001` was a material attempt with **no Change Impact Declaration**. `CHANGE_IMPACT_DECLARATION_STANDARD.md` §3 blocks `DONE` for that alone.

`D-MD-20260822-05` accepted it as a historical process deviation. The acceptance rests on revalidation having actually happened, not on the deviation being old: all seven A001 defects have current executed proof under attempts that did declare their impact — D1 and D2 by the A003 clean install and seeder run, D3 and D4 by that run applying the two migrations that previously died, D5 by measurement plus a guard, D6 by the self-test executing, D7 by every baseline lock since being produced by the corrected tool.

**No Change Impact Declaration was written for A001**, and no A001 baseline, evidence, or registry row was edited. §3 requires the declaration to exist early enough to guide the attempt; a record written three attempts later would satisfy §1 by falsifying §3.

One residual risk was found and closed rather than accepted: A001's path rebinding touched `app/`, `database/`, `config/`, and `tests/`, and only `tests/` was guarded against a path going dead again. `TestPathBindingIntegrityTest` now covers all four, mutation-proven against an `app/` class.

## Residue verdict

`CONFORMANT_NO_HARMFUL_RESIDUE_FOUND`. No strategy byte, schema shape, migration, configuration value, or runtime behaviour changed at A003. The isolated database was dropped. All guard mutations were restored and verified.

## Findings and dependencies at closure

| ID | State | Gates `MD-B03`? |
|---|---|---|
| `F-MD-B03-A003-001` | **CLOSED** — accepted as historical deviation, recorded permanently | no |
| `MD-DEP-0006` | **RESOLVED** | no |
| `MD-DEP-0001`, `MD-DEP-0002` | RESOLVED at A001 | no |
| `MD-DEP-0003` | OPEN_NON_BLOCKING — binding half closed at A002; replacement-guard half owned by `MD-B19`/`MD-B15`/`MD-B17`/`MD-B21`/`MD-B22` | no |
| `F-MD-B00-A001-001` | PARTIALLY_RESOLVED — same replacement-guard obligation | no |

## Successor / resume state

`MD-B03` is `DONE`. Its deliverables — a proven clean-install path, a seeded reason registry, a synchronized SQLite mirror, and a working drift detector — are available to every stage that builds on them. The next executable stage is determined by the Stage Register.

## Non-inheritance statement

This closure grants current sufficiency to the `MD-B03` enabling proof chain and nothing else. It is not a general waiver of `CHANGE_IMPACT_DECLARATION_STANDARD.md` §3: a future material attempt without a declaration is blocked exactly as before, and `D-MD-20260822-05` is not precedent for accepting one whose technical proof has not since been re-established under a declared attempt.
