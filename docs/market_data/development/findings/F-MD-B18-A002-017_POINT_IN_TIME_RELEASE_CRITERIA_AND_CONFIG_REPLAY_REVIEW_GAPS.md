# Finding — point-in-time lists, release criteria and config replay review gaps

- ID: `F-MD-B18-A002-017`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-09-14T23:51:54+07:00 (system clock)
- Severity: `P1` for closure. Bases that execute nothing, an executable gap already owned by F-013,
  and carry-forwards.
- Status: `OPEN — REMEDIATION_IN_CONSOLIDATED_PACKAGE`
- Class: `PROOF_BASIS_STRUCTURAL_AND_EXECUTABLE_GAP`
- Found by: per-predicate review of PAIRS 21–40 (20 predicates)
- Probe evidence: `E-MD-B18-A002-012`
- Dependency: `MD-DEP-0017` (consolidated remediation review)

## Executable gap — owned with F-013

**`MD-S050-R0016`: "Missing input is `BLOCKED`" holds only for the configuration snapshot.**
`ReplayVerificationService::replayAdmissibility` (`:2190-2219`) blocks only two cases: a
self-generated fixture and a run with no configuration snapshot.

To test the rule, I ran a probe against the real service through the existing exhaustiveness
harness (`pairs-21-32-probes/R0016-missing-identity-demo.txt`):

| Case | replay_status | comparison_result |
|---|---|---|
| all frozen identities present (control) | PASS | MATCH |
| temporal, calendar and observation identities empty | **PASS** | MATCH, `ADMISSIBLE` |
| fixture declares a temporal identity, actual is empty | **FAIL** | MISMATCH |
| configuration snapshot missing (control) | BLOCKED | NOT_ADMISSIBLE |

Production stores the temporal and calendar identities empty in publication mode (F-013 §1). Every
production publication replay is therefore in the second or third row. Remedy: F-013 items 1 and 5
(identities persisted at seal; an empty identity is `BLOCKED` and refused at persistence).

## Bases that execute nothing

The recorded positive and negative parse a contract list and look for method names with `strpos`.
They cannot fail when the behaviour they cite breaks. This is the shape PAIR 06 and PAIR 17 already
recorded.

| Predicate | Recorded pair | Probe: recorded pair / executing member |
|---|---|---|
| MD-S004-R0003 | no-backfill map + method existence | delisted listings removed from the universe (`P2X-T199`): green / red |
| MD-S004-R0005 | survivorship map + "at least five distinct guards" | same probe: green / red |
| MD-S004-R0008 | acceptance-fixture map + existence and distinctness | same probe: green / red |
| MD-S004-R0002 | cutoff-bounded input map + existence | calendar knowledge-time bound removed (`P24`): green / red |
| MD-S002-R0005 | `assertCorpusExecutable` + existence; the four corpus members are themselves map and existence tests | `P2X-T199`: pair green, all four corpus members green / delisting fixture red |
| MD-S002-R0008 | `assertCorpusExecutable` | sealed-publication immutability disabled (`P31`): green / superseded-publication member red |
| MD-S002-R0007 | `assertCorpusExecutable` | no probe of its own. It is the same helper that `P2X`, `P27` and `P31` show executes nothing, while its members are executing oracle comparisons. |

Remedy: bind each list to an aggregate harness that executes every member and fails when a member
fails, skips, is missing or makes no assertion (the R0056 corpus-harness pattern), with one landed
probe per member. "A family-level guard is semantically identical to a family-level criterion" is
true only of a guard that runs the family.

## Guard gaps

| Predicate | Gap | Evidence | Remedy |
|---|---|---|---|
| MD-S002-R0003 | The criterion names value, null-reason, lineage, **config**, **factor**, hash, seal and publication mismatches. The named corpus has no config or factor class. Its coverage test checks only value, null reason, state, lineage, content hash and seal. The frozen-input classes it could borrow rest on the F-013 fabricated perturbation. | Config-identity comparison disabled (`P27`): the pair and all 16 corpus tests green; `ReplayComparisonDetectsDivergenceTest`, outside the corpus, red | Add config and factor classes to the corpus, plus probes |
| MD-S002-R0006 | Structural positive, and the denominator-shrinkage clause rests on `SourceObservationAsKnownBoundaryTest`'s outage manifest test, which never touches the denominator (as for MD-S003-R0005 in F-016) | inspection; shared helper as above | Evaluator-level outage guard in the corpus, plus the aggregate harness |
| MD-S065-R0003 | The predicate is the rerun rule: "reruns must use the registry version effective for the requested trade date or explicitly documented override". The recorded pair tests identity minting, which is the protocol's first sentence. Production conforms: all three `resolveForRun` callers pass the requested trade date (`EodRunRepository:33`, `:182`, `:324`). | Effective-time bound removed (`P34-CFG-EFFECTIVE`): pair green; `B18ConfigEffectiveTimeSelectionTest::test_the_configuration_effective_for_the_run_context_governs_and_not_the_newest` red | Rebind to the effective-time guard, plus a guard on the rerun path (`:324`) |
| MD-S003-R0025 | The recorded pair is a family-map parse and a substrate check. The exact-publication family on MariaDB runs a SQL query the test writes itself against `md_config_snapshots`, so no repository is exercised. The families' member scenarios (for example MD-S003-R0002 to R0010) are themselves INCOMPLETE under F-013, F-014 and F-016. | `P34-CFG-EFFECTIVE`: pair and family test green on MariaDB, 0 skipped; repository guard red | Each family runs through production repositories on MariaDB, and the criterion is reassessed once its members are proven |

## Carried forward under F-013

| Predicate | Why |
|---|---|
| MD-S003-R0023 | The individual-identity guard asserts twelve non-empty identities on a hand-built metric row. Production publication mode stores `temporal_identity_hash` and `calendar_status_hash` empty (§1), so "all frozen revision/snapshot IDs" are not all recorded. |
| MD-S004-R0004 | "Listing identity" is mapped to `bound_inputs.temporal_identity_hash`, which is empty in production publication mode (§1). "Factor/formula versions" is `formula_registry_hash`, read from today's configuration (§3). |
| MD-S082-R0218 | "Current registry state must never leak into historical replay." The replay computes `formula_registry_hash` from `config('market_data.indicators')` at replay time (`ReplayVerificationService:1033`), and `reason_registry_hash` from a constant (`:1034`). §2 and §3. |
| MD-S082-R0224 | "Current environment drift cannot change publication replay." `formula_registry_hash`, `read_model_version`, `serialization_version` and `executable_build_identity` are read from the environment at replay time (`:1033-1037`), so drift changes what publication replay binds and compares. The recorded guard proves only that a configuration snapshot is frozen before seal. |

## Kept `PROVEN` after review — probe evidence in E012

- **`MD-S082-R0216`.**
  - `P34-CFG-EFFECTIVE`: the effective-time bound was removed, and the positive went red.
  - `P34-CFG-COMPARE`: `config_snapshot_hash` was skipped in the bound-input comparison only, and the negative went red.
  - The actual configuration identity is `eod_runs.config_hash`. `EodRunRepository` writes it from the resolved snapshot, and the seal checks it against the publication.
- **`MD-S082-R0217`.**
  - `P35-CFG-ASKNOWN`: the `recorded_at` bound was removed, and the positive went red.
  - `P35-CFG-RECORDED`: `recorded_at` was collapsed onto `effective_at`, and the negative went red.
  - `P35-SNAPSHOT-FREEZE`: a non-deterministic snapshot hash turned `B18AsKnownSnapshotIsolationTest::test_a_later_cutoff_exposes_later_revisions_without_rewriting_the_earlier_snapshot` red. That test is the guard for "then freezes a new replay snapshot"; the recorded basis cites it only as "asserted elsewhere".
- **`MD-S082-R0225`.**
  - `P38-SEAL-CALL`: the before-seal check was removed, and the positive went red.
  - `P38-SEAL-BOUNDARY`: the comparison was made exclusive, and the negative went red.
  - Scope: configuration revisions, since the item sits in the configuration registry's before-seal list.
- **`MD-S082-R0015`.**
  - `P40-UNBOUND-OFF`: the `CONFIG_UNBOUND` rule was disabled, and the positive went red.
  - `P40-UNBOUND-ALWAYS`: the rule was forced on, and the negative went red.
  - The blocked row keeps `admission_state` `NOT_ADMISSIBLE` and names `REPLAY_CONFIG_UNBOUND`, so the state travels with any citation.

## Also observed

- `B18ScenarioFamiliesOnMariaDbTest`: the degraded family calls `FinalizeDecisionService` without the
  database, and the correction family asserts the pointer primary key directly on the table. So "on
  MariaDB" means the production engine for the constraint and repository families only.
- `EodRunRepository` writes `eod_runs.config_hash` from the resolved snapshot on every path
  (`:59`, `:118`, `:158`, `:197`, `:223`, `:326`). The exhaustiveness harness's `runRow()` carries
  none, so its replays compare the `CONFIG_IDENTITY_UNRECORDED` marker. This does not weaken
  `MD-S082-R0216`, whose probes are recorded above, but a harness that carried a real hash would
  be a stronger fixture.

## G01-A: registry-state leak / environment-drift rebinds (`MD-S082-R0218`/`MD-S082-R0224`) — first bounded unit — 2026-09-23T23:18:33+07:00

Both predicates reconstructed independently from repository authority (`Platform_Config_Registry_LOCKED.md`
lines 290 and 301) rather than promoted merely because they share the same F-013 remediation.

**`MD-S082-R0218`** — "Current registry state must never leak into historical replay. Alternate-scenario
runs are explicitly labeled and cannot impersonate the historical publication." The recorded positive,
`B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_frozen_input_denies_pass`, proves
comparison-completeness only: it overwrites the fixture's expected value and asserts the comparison then
denies `PASS`, but never varies whether the underlying "actual" value is a live or frozen read — a
hypothetical live-config leak would pass this test identically to a correct implementation. Verified by
direct reading that `actualBoundInputContext()`'s `read_model_version`/`serialization_version`/
`executable_build_identity` fields (`ReplayVerificationService.php:1111-1119`) are each an honest empty
string when unverified/undecoded, never a `config()`/environment read. Rebound to
`ReplayVerificationServiceTest::test_registry_content_fields_come_from_decoded_capture_only_when_verified`,
which directly asserts this. The impersonation clause was already correctly bound to
`B18AsKnownModeIsolationTest::test_an_as_known_result_claims_no_publication_and_records_its_own_mode` and
is unchanged. **Probe:** replaced the field's empty-string fallback with a literal non-empty placeholder
(equivalent to a live/current-fallback violation — the real `config()` helper is unavailable in this
unit-test bootstrap); the target turned red (expected `''` vs actual the placeholder), 21 of 22 sibling
tests stayed green, and `B18ReplayComparisonExhaustivenessTest`'s 42 tests stayed fully green — confirming
that suite genuinely cannot see this class of defect. Byte-restored; sha256 unchanged (`a40a79a5…532`).
Control re-run: 22/22 green (89 assertions).

**`MD-S082-R0224`** — "current environment drift cannot change publication replay" (before-seal validation
item 5). The recorded positive, `B18BeforeSealValidationTest::test_a_candidate_without_its_lineage_binding_cannot_seal`,
proves a real but different property: `config_snapshot_id` binding is mandatory at seal.
`verifyBoundContext()` was read in full — it does not require a `registry_versions` component to be
present at all, so this test cannot establish that formula/read-model/serialization/build identity (the
fields `F-MD-B18-A002-021` actually found reading live environment, fixed by `E-044`/`E-047`/`E-048`) are
frozen. Rebound to `test_formula_and_reason_registry_hash_come_from_the_registry_versions_component`,
which directly asserts `formula_registry_hash`/`reason_registry_hash` equal the real captured
`payload_hash`, change when it changes, and are an honest empty string with no `registry_versions`
component — never a stale fallback. Negative:
`test_registry_content_fields_come_from_decoded_capture_only_when_verified`, covering the other three
environment-drift fields. **Probe:** replaced the field's empty-string initial value with a literal
non-empty placeholder; the target turned red on the "absent" case, 21 of 22 siblings stayed green
(including the R0218 guard, confirming the two rebinds are independent), `B18ReplayComparisonExhaustivenessTest`
stayed fully green. Byte-restored; sha256 unchanged. Control re-run: 22/22 green (89 assertions).

Zero production code changed for either predicate. `PROVEN` 89→91, `INCOMPLETE` 25→23.

**Evidence record:** `E-MD-B18-A002-066`, registered in `DOCUMENT_ID_REGISTRY` (`MD-DOC-01201`),
`DOCUMENT_ROLE_REGISTRY`, `CURRENT_VERIFICATION_REGISTRY`, and `WORK_RECORD_REGISTRY`.

**Proof-basis state:** Promoted `MD-S082-R0218` and `MD-S082-R0224` from `INCOMPLETE` to `PROVEN`, each
independently.

**This finding remains `OPEN — REMEDIATION_IN_CONSOLIDATED_PACKAGE`** with 14 of its own 16 predicates
still `INCOMPLETE`.
