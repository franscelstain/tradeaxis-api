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

## G03: missing input is `BLOCKED` (`MD-S050-R0016`) — executable defect fixed — 2026-09-24T00:19:07+07:00

**Classification: `IMPLEMENTATION_DEFECT` + `PROOF_GUARD_GAP`**, established from repository behaviour
before any code changed. Since `E-044`, a publication whose bound context is not `VERIFIED` is blocked.
The open question left by the reconstruction was the observation identity. The answer is that it
could pass.

**Defect.** I drove the real `ReplayVerificationService` through the exhaustiveness harness with a
`VERIFIED` bound context and a fixture declaring exactly the resolved inputs:

| Case | `source_observation_manifest_hash` | Result |
|---|---|---|
| control, all inputs bound | 64-char hash | PASS / MATCH / `ADMISSIBLE` |
| run `observation_manifest_hash` empty | `''` | **PASS / MATCH / `ADMISSIBLE`**, 0 mismatches |
| run `observation_manifest_hash` null | `''` | **PASS / MATCH / `ADMISSIBLE`**, 0 mismatches |

- **Why a verified context didn't help.** `actualBoundInputContext()` reads the observation identity
  and `canonical_raw_input_hash` off the run row, not from the verified context.
- **Why the state is reachable.** `EodPublicationRepository::sealProvenanceScope()` returns
  `ANALYTICAL_ONLY` for an `analytical_remediation_current` publication that has no acquisition
  manifest, and in that scope Seal does not require `observation_manifest_hash`. So the upstream
  contract allows this state; it does not rule it out.
- **The same gap on the write path.** `ReplayResultRepository::assertModeInputs()` required the seven
  input identities only for `AS_KNOWN`, so a `PUBLICATION_EXACT` PASS with an empty observation
  identity was persisted.

**Required inputs.**

| Required input | Source | `VERIFIED` guarantee | Missing → (after) | Fallback possible? | Proof |
|---|---|---|---|---|---|
| source observation identity | run row | none; Seal `ANALYTICAL_ONLY` permits empty | `BLOCKED`, recorded empty | no (P3) | 2 input cases, no-fallback test, P1–P3 |
| canonical raw input | run `bars_batch_hash` | none (run row) | `BLOCKED` | no | input case, P1 |
| temporal identity | `universe_identity` component group | `bind()` requires the slot and the digest fixes the list; `verifyBoundContext()` does not re-require it | `BLOCKED` | no | input case, P1 |
| calendar/status | composite over manifest revision-set hashes | never empty under `VERIFIED` | unreachable as empty | no | — (residual below) |
| event/factor | composite plus `ancillary` group | never empty; Seal requires the factor/scale hashes | unreachable as empty | no | — (residual below) |
| configuration | `configIdentityForRun()` | `config_snapshot_id` required | `REPLAY_CONFIG_UNBOUND`; a missing hash is an explicit marker | no | existing config test |
| formula / reason registry | `registry_versions` payload hash | `bind()` requires the slot | `BLOCKED` | no (E-066) | input case, P1 |
| read model / serialization / build | decoded `registry_content` | re-verified by Reader | `BLOCKED` | no (E-066) | 3 input cases, P1 |

**Remediation (minimal; nothing fabricated).**

- `replayAdmissibility()` now receives the resolved bound inputs. After the existing `VERIFIED`
  check, any `BOUND_INPUT_FIELDS` value that resolved empty makes the replay
  `REPLAY_BOUND_INPUT_INCOMPLETE`, naming each such field. The value is recorded empty and is never
  re-read from another source.
- `assertModeInputs()` applies the seven-identity check to both modes.
- `AS_KNOWN` is unchanged. Its inputs are captured as of the knowledge cutoff, and its write check
  was already in force.

**Rebinding.**

- Positive: `B18ReplayComparisonExhaustivenessTest::test_a_verified_publication_missing_a_required_input_is_blocked`
  (eight cases). Each leaves one input unavailable through the path that actually supplies it, and
  asserts `BLOCKED`/`NOT_ADMISSIBLE` (not PASS, not FAIL), the field named, and the value persisted
  empty.
- Negative: `::test_an_unavailable_observation_identity_is_not_filled_from_current_state`. Every
  selector except the fixture's explicit one returns a publication that does carry an observation
  identity. The replay must stay `BLOCKED` and resolve only the explicit publication.
- Direct write: seven `PUBLICATION_EXACT` cases, one per identity, added to
  `ReplayResultRepositoryIntegrationTest`.

**Probes.** Each was byte-restored and sha256-verified afterwards (service `cb2d14dc…2d6`,
repository `566083f2…d87`).

- **P1 — block removed:** 9 red (all eight input cases and the no-fallback test); 42 stayed green.
- **P2 — observation field dropped from the check:** exactly its two cases and the no-fallback test
  went red.
- **P3 — current-publication fallback:** only the no-fallback test went red (expected `BLOCKED`,
  got PASS).
- **P4 — write guard back to `AS_KNOWN` only:** exactly the seven new cases went red.

No separate probe per field: the contract states one rule for every input, and the implementation
is one loop. The provider already covers each field, and P2 shows that dropping one field is caught
by exactly that field's cases.

**Test fixtures corrected.** Six `ReplayVerificationServiceTest` tests expected PASS or FAIL on a
shared `VERIFIED` stub with no components or `registry_content`, and on a run with no observation
identity. `bind()` cannot produce that state. The stub now carries real bound inputs; the gate was
not weakened.

**Validation.**

- `tests/Unit/MarketData` in full: 2456 tests, 7 failures, all of them the `MD-DEP-0015`
  corpus-oracle baseline. Zero new failures.
- Every Replay and AsKnown suite is green.
- Proof readiness: 92 with a reviewed basis, 22 without.
- Proof self-test: only the baseline fails (23 entries); 10/10 injected scenarios pass.

**Residual observations (not remediated here).**

- A null calendar, status or event revision-set member is nullable at Binding and is still hashed
  into a non-empty composite.
- `read_model_version` is absent from the direct-write check in both modes. The admission check
  covers it for `PUBLICATION_EXACT`.
- A `PUBLICATION_EXACT` replay with no resolved publication skips bound-context admission, but its
  result cannot persist.

**Evidence record:** `E-MD-B18-A002-067`, registered in `DOCUMENT_ID_REGISTRY` (`MD-DOC-01202`),
`DOCUMENT_ROLE_REGISTRY`, `CURRENT_VERIFICATION_REGISTRY` and `WORK_RECORD_REGISTRY`.

**Proof-basis state:** `MD-S050-R0016` moved from `INCOMPLETE` to `PROVEN`. `PROVEN` 91→92,
`INCOMPLETE` 23→22. Formal traceability is unchanged at `0/114` `SATISFIED`.

**This finding remains `OPEN — REMEDIATION_IN_CONSOLIDATED_PACKAGE`**, with 13 of its own 16
predicates still `INCOMPLETE`. Next unit: G01-B (`MD-S003-R0023`/`MD-S004-R0004`).

## G03 closure audit: `MD-S050-R0016` returned to `INCOMPLETE` — 2026-09-24T07:30:22+07:00

This read-only audit, run before commit, checked the exact completeness meaning of `MD-S050-R0016`
against canonical authority. It found that the `E-MD-B18-A002-067` promotion was **premature**.
`E-067` is `IMMUTABLE_AFTER_ISSUE` and is not edited. Its remediation, guards and four probes stay
valid for the inputs they name. `E-MD-B18-A002-068` corrects the verdict.

**Scope derived from authority.** "Missing input is `BLOCKED`" (`Replay_Verification_Contract_LOCKED.md:33`)
applies to the required inputs of lines 23–31 (`MD-S050-R0007`…`R0015`), at the granularity the
contract names them. The approved package scopes it to "replay admission in both modes and
repository direct writes". Component completeness is validated at binding with named missing paths.
Admission consumes that result as the `VERIFIED` verdict (C1 §6).

| Required input / component | Canonical authority | Current representation | Guaranteed complete? | Missing behaviour | Covered by G03 proof? |
|---|---|---|---|---|---|
| source observation IDs/hashes | :26 | run `observation_manifest_hash` | no (`ANALYTICAL_ONLY`) | `BLOCKED` | yes (2 cases, no-fallback, P1–P3) |
| canonical RAW input set | :27 | run `bars_batch_hash` | no | `BLOCKED` | yes |
| temporal universe/listing/symbol/mappings | :24 | `universe_identity` component | binding slot | `BLOCKED` | yes |
| calendar/session + status revisions | :25 | captures C04/C05; composite projection | yes, at binding (`calendar_session.required_date.*`, `status_expectation.*`) | no `VERIFIED` context → `BLOCKED` | outside the admission check; binding guards |
| event revisions, verification, factor sets, contamination | :28 | captures C08/C09; composite projection | yes, at binding; Seal requires factor/scale hashes | no `VERIFIED` context → `BLOCKED` | outside the admission check; binding guards |
| **configuration snapshot ID/hash** | :29; Platform_Config_Registry `CONFIG_UNBOUND` | ID required; hash may be the `CONFIG_IDENTITY_UNRECORDED` marker | **no** | ID missing → `BLOCKED`; **hash missing → ADMISSIBLE/PASS** | **no — gap A** |
| formula / indicator / reason registry | :30 | `registry_versions` payload hash | binding slot | `BLOCKED` (exact) | yes (exact only) |
| **read-model version** | :30 | exact: `registry_content`; **as-known: undefined config key** | **no (as-known always empty)** | exact `BLOCKED`; **as-known PASS; not in the direct-write check (both modes)** | **exact only — gap B** |
| serialization / build versions | :30 | `registry_content`; storage-checked | exact: yes | `BLOCKED` / refused | yes |
| fixture identity, expected publication/seal/hash assertions | :23, :31 | fixture package | fixture completeness | `REPLAY_EXPECTED_PROOF_INCOMPLETE` → `BLOCKED` | `MD-S050-R0031` |

**Dispositions.**

- **Calendar/status components:** outside this predicate's admission check, and guaranteed at
  binding (`B18ProducerCalendarRegistryTest`, `B18ProducerTradingStatusPopulationTest`). A null
  compatibility member under a complete `VERIFIED` capture is Binding-nullable, not a missing input.
- **Event/factor components:** same disposition (`B18ProducerEventFactorCaptureTest`).
- **Config `CONFIG_IDENTITY_UNRECORDED`:** a disguised missing input. **Residual gap A.** I tested
  it with a temporary admission block: 41 of 51 exhaustiveness tests and 11 of 22
  `ReplayVerificationServiceTest` tests turned red, because their fixtures pass on the marker. I
  reverted the block (service sha256 `cb2d14dc…2d6`) because MariaDB stopped responding and the
  broad regression could not run.
- **`read_model_version`:** a required input. `AS_KNOWN` reads `governance.read_model_version`,
  which the configuration does not define, so every `AS_KNOWN` replay records it empty. The
  direct-write check omits it in both modes. `AS_KNOWN` reason-registry identity is a hash of nominal
  state names. **Residual gap B**, which needs an owner decision: the minimal fail-closed remedy
  blocks every `AS_KNOWN` replay, while the alternative binds an as-known read-model and
  reason-registry identity (`MD-S050-R0014`).
- **Seven-field direct-write boundary:** insufficient. It lacks `read_model_version` (gap B) and
  accepts the config marker (gap A).
- **Export:** `exportReplayEvidence` requires input identities only for `AS_KNOWN`. This is
  recorded as out of R0016 scope: C1 is guidance, and the package scopes R0016 to admission and
  direct writes.

**Eight G03 cases.** Every case maps to one required input:

| Case | Required input | Authority line (predicate) |
|---|---|---|
| observation (empty) | source observation IDs/hashes | :26 (R0010) |
| observation (null) | source observation IDs/hashes | :26 (R0010) |
| canonical raw | canonical RAW input set | :27 (R0011) |
| temporal | temporal universe/listing/symbol/mappings | :24 (R0008) |
| registry versions | formula, indicator registry, reason registry | :30 (R0014) |
| read model | read-model version | :30 (R0014) |
| serialization | hash/serialization version | :30 (R0014) |
| build | build version | :30 (R0014) |

None of the cases falls outside R0016, and none blanks a combined hash. Two required inputs have no
case: the configuration hash (gap A) and every `AS_KNOWN` input (gap B).

**Environment.** MariaDB stopped between runs. The log shows starts at 06:48 and 07:00 with no error
entries, and no `mysqld` process was running at 07:27. I did not start or restart it.

**State.** `MD-S050-R0016` is `INCOMPLETE`. `PROVEN` 92→91, `INCOMPLETE` 22→23. No production code
changed in this audit. This finding remains `OPEN — REMEDIATION_IN_CONSOLIDATED_PACKAGE`, with 14 of
its 16 predicates `INCOMPLETE`. **Next:** R0016 gap A. G01-B is not started.

## G03 residual Gap A closed: config identity completeness (`MD-S050-R0016`) — 2026-09-24T08:31:09+07:00

**Authority reverified before coding.** `Replay_Verification_Contract_LOCKED.md:29` requires "full
configuration snapshot ID/hash". `Platform_Config_Registry_LOCKED.md` is explicit: "The same
non-null snapshot ID and hash bind the run..."; "the resolved snapshot -- not the current
environment or registry -- is used for replay"; and "A sealed publication whose run carries no
non-null config snapshot ID **and hash** is `CONFIG_UNBOUND`... not admissible as evidence of
reproducibility... Publication replay over them is `BLOCKED`, not `PASS`, since a required bound
input is absent." Authority names ID **and** hash together, never an ID alone, and never mentions a
placeholder standing in for a missing hash. This settled the audit's four premises without needing
to stop for an authority gap.

**The defect.** A run with `config_snapshot_id` set but `config_hash` and `config_snapshot_ref`
both empty resolves `configIdentityForRun($run)` to the literal placeholder
`CONFIG_IDENTITY_UNRECORDED`. That marker is a non-empty string, so it satisfied both R0016 G03's
empty-string check and the direct-write boundary's `empty()` check, and the replay reached
`PASS`/`ADMISSIBLE` and was persisted as if the configuration had been resolved.

**Fix, minimal and never fabricating a value.**

| defect | production path | minimal change | resulting behaviour |
|---|---|---|---|
| Admission let a `VERIFIED`-context publication with an unrecorded run config hash replay `ADMISSIBLE`/`PASS` | `ReplayVerificationService::replayAdmissibility()` | Added `CONFIG_IDENTITY_UNRECORDED` as a shared `public const` (the literal `configIdentityForRun()` already returned) and one check: if the run's identity resolves to it, `BLOCKED` as `REPLAY_CONFIG_UNBOUND`. Scoped inside the existing "there is a publication to reproduce" branch, so a non-readable run with no publication (an `EXPECTED_DEGRADE` fixture) is not converted into a config-unbound case | A run with an ID but no hash or snapshot reference is `BLOCKED`/`NOT_ADMISSIBLE`, naming `REPLAY_CONFIG_UNBOUND`, regardless of which publication a fixture names |
| Direct write persisted a non-`BLOCKED` result carrying the marker | `ReplayResultRepository::assertModeInputs()` | One check after the existing `empty()` loop: the marker as `config_snapshot_hash` throws `REPLAY_CONFIG_UNBOUND`, in both modes | A non-`BLOCKED` result carrying the marker is refused before it reaches the table |

No value is invented anywhere in the fix, and no current/latest lookup was added; the check reads
only the already-resolved `$run`.

**Test fixtures.** The shared `runRow()` (`B18ReplayComparisonExhaustivenessTest`) and
`successReadableRun()` (`ReplayVerificationServiceTest`) carried a `config_snapshot_id` but no
`config_hash` -- every test built on them, the whole file's baseline in each case, was unknowingly
exercising a `CONFIG_UNBOUND` run. Both gained a real `config_hash`, matching their own stated
premise of a legitimately bound publication. The dedicated non-readable/`EXPECTED_DEGRADE` fixture
(`test_verify_replay_handles_non_readable_run_as_reason_coded_expected_degrade`) keeps its own run
object unchanged -- its scenario was not repurposed into a config-unbound one.

**New tests.** `test_a_config_snapshot_with_no_recorded_hash_is_blocked_rather_than_passed`
(positive, real path: blank `config_hash` → `BLOCKED`, `REPLAY_CONFIG_UNBOUND` named, persisted
value is the marker, never a fabricated hash). `test_an_unrecorded_config_hash_is_not_filled_from_current_state`
(negative: every selector but the fixture's explicit one is stubbed with a different config
identity; the replay stays `BLOCKED` and only the explicit selector is used).
`ReplayResultRepositoryIntegrationTest`'s data provider gained a case: a complete metric with the
marker as `config_snapshot_hash` is refused, never persisted.

**Probes**, each isolated to its own target, byte-restored and sha256-verified. (Each file was run
separately after discovering PHPUnit's CLI silently executes only the first of several positional
file arguments.)

- **P1 — admission check removed:** exactly the 2 new admission tests red; 51 others in the file,
  plus the repository (18/18) and service (22/22) suites, stayed green.
- **P2 — storage check bypassed:** exactly the 1 new repository case red (17/18 stayed green); the
  exhaustiveness (53/53) and service (22/22) suites stayed green.
- **P3 — a hypothetical current/latest fallback introduced** (resolve the current publication and
  use its config identity instead of `BLOCKED`): only the no-fallback test red; 52 others green.

**Validation.** All 31 replay/AS_KNOWN suites green, including the MariaDB-backed
`B18ProductionPathReplayFixturesTest`. Full `tests/Unit/MarketData`: 2459 tests, 34569 assertions, 7 failures, 0 errors -- exact match to the known `MD-DEP-0015` corpus-oracle baseline (`ProductionCorpusInvariantOracleTest`), zero new failures, zero baseline entries resolved.
MariaDB was reachable at the start of this unit (not started or restarted by this session; its log
showed clean restarts with no future-LSN warnings).

**R0016 remains `INCOMPLETE`.** Per explicit instruction this Gap A closure does not promote the
predicate: Gap B (`AS_KNOWN` read-model version and reason-registry identity) is untouched and
still open. `PROVEN`/`INCOMPLETE` counts are unchanged at 91/23. Formal `0/114` `SATISFIED` is
unchanged.

**Evidence record:** `E-MD-B18-A002-069`, registered in `DOCUMENT_ID_REGISTRY` (`MD-DOC-01204`),
`DOCUMENT_ROLE_REGISTRY`, `CURRENT_VERIFICATION_REGISTRY` and `WORK_RECORD_REGISTRY`.

## Authority decision needed: Gap B (`AS_KNOWN` read-model / reason-registry identity)

Not implemented in this unit; restated here as a decision, not a chosen behaviour.

- **What authority requires:** `Replay_Verification_Contract_LOCKED.md:30` lists read-model,
  formula/registry and build versions among the required bound inputs for *both* replay modes;
  `MD-S050-R0016` requires any missing one to be `BLOCKED` in both modes.
- **What authority/config does not define:** `config/market_data.php` has no
  `governance.read_model_version` key (confirmed at runtime); `AsKnownReplaySnapshotService` reads
  it anyway and always gets an empty string. No authority document defines what `AS_KNOWN`
  read-model or reason-registry identity should bind, or states that it is intentionally exempt.
- **Why this is not an implementation choice:** two conforming remedies exist and are not
  equivalent -- fail-closed (`BLOCK` every `AS_KNOWN` replay until resolved, removing `AS_KNOWN`
  admissibility for the whole corpus) versus binding a real as-known identity (which requires
  deciding what that identity is: a new config key, a fixed constant, something else -- a
  strategy/config decision, not a code-only fix). Choosing between "block everything" and "invent an
  identity" without authority direction repeats exactly the kind of silent choice this predicate's
  own remediation history has already found defective.
- **Minimum clarification needed:** whether `AS_KNOWN` read-model/reason-registry identity is
  required and must be bound to a real, authoritative source before `AS_KNOWN` can be non-`BLOCKED`,
  or exempted for `AS_KNOWN` with an explicit, recorded reason -- and if required, what that source
  is.
- **Affected scope:** `AsKnownReplaySnapshotService::capture()`; the `AS_KNOWN` branch of
  `ReplayResultRepository::assertModeInputs()`; every existing `AS_KNOWN` replay result and fixture.

**This finding remains `OPEN — REMEDIATION_IN_CONSOLIDATED_PACKAGE`**, with 14 of its 16 predicates
`INCOMPLETE` (`MD-S050-R0016` among them, on Gap B alone). **Next:** `MD-S050-R0016` Gap B, an
authority/strategy decision -- not started. `G01-B` not started.

## Gap B1 owner decision recorded; Gap B2 and predicate impact located — 2026-09-24T11:51:50+07:00

**Gap B1 decided (`D-MD-B18-A002-008`, Option 1).** For AS_KNOWN replay, `read_model_version` binds
the replay/read-product contract version of the artifact being rendered. The current canonical
identity is `market_data_read_product_v1`, the same identity the publication path already binds
(`F-MD-B18-A002-021`/`E-MD-B18-A002-048`). It is not the current runtime code version, the build
identity, the configuration snapshot effective at the cutoff, the `MD-S082` minimum consumer
read-model version, or a newly invented AS_KNOWN configuration value. A historical artifact rendered
under V1 stays bound to V1 even if a later contract V2 exists.

Before the record was written, the owner's choice was reverified against `MD-S021` (versioned read
product), `MD-S045` (read-model version is the interpretation of the artifact rows), `MD-S050`
(`:17`, `:30`, `:33`, `:107` "the versioned as-known read product"), `MD-S004` (backtests consume a
versioned snapshot/export of the read model), and `MD-S082` (`:289`/`:291` govern configuration and
registry revisions; the register has no read-model key). No conflict was found. The decision adds no
configuration key, changes no strategy byte, and needs no `DOCUMENT_CHANGE_LOG.md` entry.

**Implementation has not started.** AS_KNOWN still reads the nonexistent
`governance.read_model_version` and records it empty, and the direct-write boundary still does not
require `read_model_version`.

**Gap B2 remains authority-determined fail-closed and is unimplemented.** No historical
reason-registry identity exists: `MD-S085` defines no registry version or revision history,
`MD-S082:289`/`:291` forbid using the current registry, and the producer already marks the as-known
reason registry as missing (`registry_versions.reason_registry.authoritative_known_at_cutoff`).
Today AS_KNOWN emits a hash of hard-coded state names in its place.

**Predicate impact (read-only locator; nothing changed).**

- **`MD-S050-R0014` (`PROVEN`) is over-claimed.** The predicate and the package scope it to "every
  fixture/manifest", which means both modes. Its basis, promoted under `F-MD-B18-A002-013`/
  `E-MD-B18-A002-050`, covers only `PUBLICATION_EXACT`. In AS_KNOWN the reason-registry member is
  nominal and the read-model member is unbound. That is the same "reason registry nominal" defect
  `F-MD-B18-A002-013` originally recorded, and the exact case the package's planned R0014 probe
  targets ("force a constant reason hash"). The claim is inaccurate now, independent of any Gap B
  implementation.
- **`MD-S019-R0071` (`PROVEN`)** rests on the same publication-only basis. Invariant 14 covers both
  modes ("Current state must not leak into either mode"). It appears to share the over-claim and
  should be reverified independently rather than corrected by association.
- **`MD-S050-R0005` (`PROVEN`)**: the requirement (new artifacts; never mutates or impersonates the
  publication) is still valid, and the claim is true today. Its negative guard, however, drives an
  AS_KNOWN replay with missing inputs to `MISMATCH`/`FAIL`. Once Gap B2 blocks AS_KNOWN, that outcome
  becomes unreachable, so the proof must be rebound in the same unit that lands Gap B2. The positive
  guards (mode, cutoff, null `publication_id`, untouched publication, comparison surface) survive only
  if a `BLOCKED` AS_KNOWN result is still persisted with those fields.

**Next order.** Correct the over-claimed `PROVEN` status before any Gap B implementation, so a claim
already known to be invalid does not carry across a semantic change to the same inputs. `R0005` is
rebound inside the Gap B2 implementation unit, where its guard stops being executable.

`MD-S050-R0016` remains `INCOMPLETE`. Proof basis unchanged at 91 `PROVEN` / 23 `INCOMPLETE`. Formal
`0/114` `SATISFIED` unchanged. **This finding remains `OPEN — REMEDIATION_IN_CONSOLIDATED_PACKAGE`**,
with 14 of its 16 predicates `INCOMPLETE`. **Next:** governed correction of `MD-S050-R0014`'s
`PROVEN` status (AS_KNOWN over-claim), with `MD-S019-R0071` reverified independently in the same unit.
Not started. `G01-B` not started.

## `MD-S050-R0014` / `MD-S019-R0071` PROVEN claims corrected; `F-MD-B18-A002-013` reopened — 2026-09-24T13:23:51+07:00

This unit corrected already-`PROVEN` claims before any Gap B implementation; no production or test
code changed (`E-MD-B18-A002-070`).

- **`MD-S050-R0014`** covers every fixture/manifest, in both modes. `E-MD-B18-A002-050` proved it on
  the `PUBLICATION_EXACT` path only. Verified by execution in AS_KNOWN:
  - changing one of 437 `eod_reason_codes` rows and adding a code left `reason_registry_hash` and the
    whole as-known `snapshot_hash` unchanged;
  - `read_model_version` is empty.

  A constant AS_KNOWN reason hash (the package's own R0014 probe) was caught by none of the bound
  guards or AS_KNOWN suites. The same constant in `PUBLICATION_EXACT` turned the bound negative guard
  red. Classification: `IMPLEMENTATION_AND_PROOF_DEFECT`. Returned to `INCOMPLETE`.
- **`MD-S019-R0071`**, reviewed independently. Invariant 14 names both modes. Its formula,
  indicator and price-product ingredient is bound in AS_KNOWN; its reason-registry ingredient is not.
  Classification: `IMPLEMENTATION_AND_PROOF_DEFECT`, on that ingredient only. Returned to
  `INCOMPLETE`.
- **`F-MD-B18-A002-013` reopened.** Its closure rested on all ten carried predicates being `PROVEN`,
  and the unremediated defect is its own item 2 ("nominal in both modes"). E-050 stays as issued;
  its publication-mode facts remain valid.
- **`MD-S050-R0005`** is unchanged. Its claim is true today. Its negative guard must be rebound in
  the Gap B2 unit, where that guard stops being reachable. That unit must also keep persisting a
  `BLOCKED` AS_KNOWN result with its mode, cutoff, null `publication_id` and source, which `R0005`'s
  positive guard and `MD-S082-R0218`'s negative guard assert.

Proof basis 91 → 89 `PROVEN`, 23 → 25 `INCOMPLETE`, derived from the file. `MD-S050-R0016` remains
`INCOMPLETE`. Formal `0/114` `SATISFIED` is unchanged, and `D-MD-B18-A002-008` is unchanged.

**The claim corrections Gap B touches are now complete.** Gap B changes only the AS_KNOWN read-model
and reason-registry members and AS_KNOWN admission. The `PROVEN` predicates resting on those —
`MD-S050-R0014` and `MD-S019-R0071` — are corrected, and `MD-S050-R0005`'s rebind belongs to B2.

**Next:** Gap B1 implementation under `D-MD-B18-A002-008`:
- AS_KNOWN binds `market_data_read_product_v1` instead of reading the nonexistent configuration key;
- the direct write requires `read_model_version` in both modes;
- a discriminating proof.

Gap B2 follows: AS_KNOWN reason registry fail-closed, with `MD-S050-R0005` rebound in that unit.
This finding remains `OPEN — REMEDIATION_IN_CONSOLIDATED_PACKAGE`, with 14 of its own 16 predicates
`INCOMPLETE`. `G01-B` has not started.

## Gap B1 implemented: AS_KNOWN read-model contract identity — 2026-09-24T14:53:40+07:00

`E-MD-B18-A002-071` implements `D-MD-B18-A002-008` exactly, re-verified against the decision itself
before coding (no intervening repository state changed its applicability).

**Canonical source.** No existing named constant carried `market_data_read_product_v1`; it was a
literal duplicated across five files. Added `MarketDataReadProductService::READ_MODEL_VERSION` (the
natural `MD-S021` owner). That class's own two pre-existing literal occurrences were deliberately
left untouched, not switched to the constant: `ConsumerReadProductAntiBypassTest` source-scans this
file for the exact substring `'read_model_version' => 'market_data_read_product_v1'`, and a first
attempt at replacing both with `self::READ_MODEL_VERSION` broke that guard, caught by the first full
regression run below and reverted before any governance record was written. The other four
already-established, already-proven publication-path call sites were also left untouched -- out of
Gap B1's AS_KNOWN scope.

**Change.** `AsKnownReplaySnapshotService::capture()`'s `read_model_version` now reads
`MarketDataReadProductService::READ_MODEL_VERSION` directly -- no `config()` call, no cutoff
resolution, no dependency on the AS_KNOWN config snapshot. `ReplayResultRepository::assertModeInputs()`
gained `read_model_version` as the eighth member of the existing both-modes required-input loop.
Gap B2 (`reason_registry_hash`, checked only for emptiness) is untouched.

**Real-path proof.** A positive test through the full `verifyAsKnownAgainstFixture()`/stored-metric
path, asserted against both the constant and the literal string independently (so a wrong value in
the constant itself is still caught -- the first draft of this test, checked only against the
constant, was corrected after probe P2 showed it did not discriminate). A config-independence test:
real `config()` mutated on two already-registered keys, called directly against
`AsKnownReplaySnapshotService::capture()` rather than the full verifier, because the full
canonicalizer run independently re-validates the seeded run's bound config snapshot against live
config and would reject the mutation as unrelated drift (confirmed by first hitting
`INPUT_CAPTURE_LIVE_CONFIG_DIVERGENCE` through the full path). Direct-write refusal cases for a
missing `read_model_version` in both modes.

**Probes**, each byte-restored and sha256-verified (re-run a second time against the corrected file
after the anti-bypass fix), each suite file run separately: reverting AS_KNOWN to the config-derived
value turned 6 of 7 `B18AsKnownModeIsolationTest` tests red (the storage boundary and the binding fix
reinforcing each other); an incorrect canonical literal turned exactly the corrected positive test
red; removing the storage check turned exactly the two new repository cases red. The publication-mode
control suites (`B18ReplayComparisonExhaustivenessTest`, `ReplayVerificationServiceTest`,
`ConsumerReadProductAntiBypassTest`) stayed green under every probe.

**Two incidents surfaced and resolved during validation, neither left unaddressed:**

1. The first full regression run found one real, unexpected failure --
   `ConsumerReadProductAntiBypassTest::test_the_payload_declares_its_product_and_read_model_version`
   -- caused by the unnecessary literal-to-constant replacement described above. Fixed by reverting
   those two occurrences; confirmed green afterward, including a second full probe re-run.
2. A second full regression run, launched to re-validate that fix, reported 51 errors and 15 failures
   across unrelated MariaDB-backed producer-capture suites this unit never touched. The MariaDB error
   log showed no crash, restart or error entry across any of the runs. Diagnosed as cross-process test
   contention: the probe re-verification was run concurrently with that background regression against
   the same MariaDB instance. Resolved by confirming file hashes were unchanged from the corrected,
   probe-verified state and running a third, fully isolated regression (no other process running)
   before trusting a result.

**Validation (authoritative, isolated run).** Full `tests/Unit/MarketData`: 2463 tests, 34603
assertions, 7 failures, 0 errors -- exact match to the known `MD-DEP-0015` corpus-oracle baseline,
zero new failures. MariaDB verified reachable before each attempt; not started or restarted by this
session.

**Not promoted.** `MD-S050-R0016`, `MD-S050-R0014` and `MD-S019-R0071` remain `INCOMPLETE`: Gap B2
(AS_KNOWN reason-registry identity, fail-closed) is a required member of all three and is
unimplemented. Proof basis unchanged at 89 `PROVEN` / 25 `INCOMPLETE`. `D-MD-B18-A002-008`,
`E-MD-B18-A002-050`, `E-MD-B18-A002-067`, `E-MD-B18-A002-068`, `E-MD-B18-A002-069` and
`E-MD-B18-A002-070` are unchanged.

**Evidence record:** `E-MD-B18-A002-071`, registered in `DOCUMENT_ID_REGISTRY` (`MD-DOC-01207`),
`DOCUMENT_ROLE_REGISTRY`, `CURRENT_VERIFICATION_REGISTRY` and `WORK_RECORD_REGISTRY`.

This finding remains `OPEN — REMEDIATION_IN_CONSOLIDATED_PACKAGE`, with 14 of its own 16 predicates
`INCOMPLETE`. **Next:** `MD-S050-R0016` Gap B2 -- AS_KNOWN historical reason-registry fail-closed
remediation, including the `MD-S050-R0005` proof impact/rebind. Not started. `G01-B` not started.

## Gap B2 implemented: AS_KNOWN reason-registry identity fail-closed — 2026-09-24T21:09:22+07:00

`E-MD-B18-A002-073` implements Gap B2. Authority recheck first: `Reason_Codes_Registry.md`
(`MD-S085`) read in full again -- its five "Registry rules" cover naming, one-code-one-meaning,
description drift and deprecation only, with no revision, version, effective-time or recorded-time
concept anywhere. There is therefore no historical reason-registry state an as-known replay could
legitimately bind as of its knowledge cutoff.

**Change.** `AsKnownReplaySnapshotService::REASON_REGISTRY_IDENTITY_UNAVAILABLE` replaces the old
`reason_registry_hash` -- previously a hash of two hardcoded constant arrays plus a config key that
never existed, the same "non-empty is not present" shape `E-MD-B18-A002-069` found for the config
identity. `ReplayVerificationService::verifyAsKnownAgainstFixture()` gained an early-return block,
placed before the execution service is invoked (AS_KNOWN's canonicalizer has real side-effect/expense
concerns the publication-mode admissibility check does not, so it must never run once the result is
already known to be BLOCKED): every as-known replay whose captured snapshot carries the unavailable
marker is persisted directly as `BLOCKED`/`NOT_ADMISSIBLE`, unconditionally, before any comparison
against the fixture executes. `ReplayResultRepository::assertModeInputs()` gained a direct-write
refusal for a non-BLOCKED result carrying the marker, in both modes.

**Real-path proof.** The base (non-perturbed) as-known scenario is BLOCKED, not PASS. A
fixture-perturbed scenario is still BLOCKED, not MISMATCH/FAIL -- the block fires on the captured
snapshot alone, independent of fixture content. Inserting a new row into the live
`eod_reason_codes` table does not make a historical as-known replay admissible. Direct-write refusal
confirmed in both replay modes.

**Four probes**, each byte-restored and sha256-verified, each suite file run separately: restoring
the old fabricated-looking hash turned 3 of 9 `B18AsKnownModeIsolationTest` tests red; bypassing the
admission check turned 6 of 9 red (as errors, once the marker reaches the now-also-corrected storage
boundary); deriving the hash from a live table read turned the same 3 tests red as the first probe;
removing the storage check turned exactly the 2 new `ReplayResultRepositoryIntegrationTest` cases
red. `B18AsKnownSnapshotIsolationTest` and `ReplayVerificationServiceTest` (publication-mode control)
stayed green under every probe.

**A real defect surfaced by the first full regression, found and fixed.** The BLOCKED metric's
`final_reason_code` was first set to the bare literal `REPLAY_REASON_REGISTRY_IDENTITY_UNAVAILABLE`.
The first isolated full run (2467 tests, 8 failures) found one genuine new failure --
`EmittedReasonCodeRegistrationTest::test_every_reason_code_emitted_by_the_runtime_is_registered` --
proving the literal was emitted but registered nowhere. Adding it to `Reason_Codes_Seed.sql` and the
paired `Reason_Codes_Registry.md` (kept synchronized by
`LoggingTraceabilityReasonCodesStaticGuardTest`) caused two further failures:
`GovernanceGateReadOnlyExecutionTest`'s `STRATEGY_FREEZE` check (`Reason_Codes_Registry.md` is
registered in `MARKET_DATA_STRATEGY_FREEZE_MANIFEST.json` as `MD-S085` under the current freeze, sha1
pinned) and its `TRACEABILITY_MATRIX` check (inserting a row shifted twenty positionally-derived
`MD-S085-R####` requirement ids). The one prior precedent for touching this exact document
(`DOC-CHG-20260823-001` / `D-MD-20260823-01`) required an explicit, quoted user authorization
instruction before any byte of it changed. This unit's governing instruction authorized Gap B2's
production remediation, not amending a LOCKED strategy-authority document, and explicitly said not to
start `G01-B` -- signalling strict scope discipline was wanted. Both edits were fully reverted (`git
checkout` was refused by the harness's own destructive-action classifier, so the inserted lines were
removed individually instead); `Reason_Codes_Registry.md`'s sha1 reconfirmed identical to the frozen
manifest's registered value, and the one row seeded into `tradeaxis_testing.eod_reason_codes` during
the attempt was deleted. `final_reason_code` is set to `null` in the early-return block instead,
matching the precedent already set by `replayAdmissibility()`'s own `NOT_ADMISSIBLE` override (Gap
A's `CONFIG_IDENTITY_UNRECORDED` path, `REPLAY_BOUND_INPUT_INCOMPLETE`, `REPLAY_CONFIG_UNBOUND`),
none of which ever assign a bespoke `final_reason_code` either -- their block-reasons live only in
free text (`mismatch_summary`/`comparison_note`), which is exactly why they were never caught by this
same scan. The cause remains fully legible via `mismatch_summary` (the `REPLAY_REASON_REGISTRY_
IDENTITY_UNAVAILABLE: ...` text) and via `reason_registry_hash` (the marker itself). No new reason
code was registered; no LOCKED strategy document was modified; none of this unit's own tests assert
`final_reason_code` for this case, so none needed to change.

**`MD-S050-R0005` impact.** Gap B2 makes every as-known replay BLOCKED before any comparison runs,
unconditionally -- confirmed by inspection: `capture()`'s `reason_registry_hash` assignment has no
conditional logic capable of ever not returning the unavailable marker. R0005's negative clause
(`Replay_Verification_Contract_LOCKED.md:17`, a genuine as-known divergence must be caught as
MISMATCH/FAIL) therefore has no reachable comparison to exercise any more -- not merely untested, but
structurally unreachable through the real verifier. Per instruction, not force-replaced with a
same-named guard proving a different claim: the renamed test at the old name now proves "BLOCKED
regardless of fixture divergence", a true and useful but different fact. `MD-S050-R0005` moved from
`PROVEN` to `INCOMPLETE` (positive clause kept; negative set to `null`; classification
`IMPLEMENTATION_DEPENDENCY_UNAVAILABLE`). Remediation would require real historical reason-registry
versioning (out of Gap B2's scope) or an owner decision narrowing R0005's own proof shape.

**`MD-S050-R0014` / `MD-S019-R0071` reviewed independently, both remain `INCOMPLETE`.** Both require
positively binding/reproducing the reason-registry version, which fail-closed `BLOCKED` correctly
refuses to fabricate but does not supply. `R0071`'s narrower, adjacent guarantee -- current registry
state must never leak into historical replay -- is proven for this ingredient
(`test_mutating_the_current_reason_registry_does_not_make_as_known_admissible`), but that is not
R0071's own reproducibility claim.

**`MD-S050-R0016` not promoted.** Gap A + Gap B1 + Gap B2 are all now implemented and proven for
their specific inputs. AS_KNOWN's config path (`MarketDataConfigSnapshotRepository::resolveAsKnown()`)
was confirmed fail-closed by construction via a bounded, read-only check (not an executed probe).
Calendar/status, event/factor, temporal universe, source-observation and canonical-raw-input captures
were explicitly not independently probed for a missing-input case in this unit -- out of Gap B2's
scope, so `MD-S050-R0016` stays `INCOMPLETE` rather than being promoted on an assumption.

**Validation (authoritative, isolated run).** Full `tests/Unit/MarketData`: 2467 tests, 34637
assertions, 7 failures, 0 errors -- exact match to the known `MD-DEP-0015` corpus-oracle baseline,
zero new failures. Two intermediate runs (8 and 9 failures) were diagnosed as real defects of this
unit's own making and fixed, not MariaDB contention. MariaDB verified reachable before each attempt;
not started or restarted by this session.

**Proof basis:** `PROVEN` 89 -> 88, `INCOMPLETE` 25 -> 26 (`MD-S050-R0005` only transition).
`D-MD-B18-A002-008`, `E-MD-B18-A002-050`, `E-MD-B18-A002-067` through `E-MD-B18-A002-072` are
unchanged. `MARKET_DATA_STRATEGY_FREEZE_MANIFEST.json` (`MD-STRATEGY-FREEZE-20260922-001`) is
unchanged -- the attempted edit was reverted before issuance.

**Evidence record:** `E-MD-B18-A002-073`, registered in `DOCUMENT_ID_REGISTRY` (`MD-DOC-01209`),
`DOCUMENT_ROLE_REGISTRY`, `CURRENT_VERIFICATION_REGISTRY` and `WORK_RECORD_REGISTRY`.

This finding remains `OPEN — REMEDIATION_IN_CONSOLIDATED_PACKAGE`. Gap A, Gap B1 and Gap B2 (the
executable gap this finding owns with F-013) are now all implemented and proven for their respective
inputs; `MD-S050-R0016`, `MD-S050-R0014` and `MD-S019-R0071` remain `INCOMPLETE` for the reasons
above, and `MD-S050-R0005` is newly `INCOMPLETE`. **Next:** `G01-B` (`MD-S003-R0023` /
`MD-S004-R0004`) -- not started by this unit. Registering
`REPLAY_REASON_REGISTRY_IDENTITY_UNAVAILABLE` in the LOCKED `Reason_Codes_Registry.md` remains
available as a separate, explicitly user-authorized unit if a bespoke `final_reason_code` for this
state is later wanted; no currently `INCOMPLETE` predicate requires it.
