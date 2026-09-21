# Finding — publication replay binds empty or nominal identities

- ID: `F-MD-B18-A002-013`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-09-14T11:17:22+07:00 (system clock)
- Severity: `P1` — an executable defect, plus a proof basis that overclaims
- Status: `OPEN — C1_CAPTURE_IMPLEMENTED_PARTIALLY`
- Class: `BOUND_INPUT_IDENTITY_NOT_BOUND`
- Found by: per-predicate review of PAIR 01
  (`B18ReplayBoundInputIdentityContractTest`, 16 predicates)
- Directed by: `CI-MD-B18-A002-001`, review-discovered boundary 2026-09-14 11:17

## What the contract requires

`Replay_Verification_Contract_LOCKED.md` sets out what the two replay modes must bind:

- Line 9: publication replay uses exactly the observations, temporal master revisions,
  calendar/status revisions, event/factor revisions, configuration snapshot, formulas and
  registries, build/adapter versions, serialization rules and publication manifest *frozen with
  it*.
- Lines 21–31: every fixture or manifest binds, at minimum, the nine items listed there.
- Line 33: a missing input is `BLOCKED`.

`Determinism_Invariants_LOCKED.md` Invariant 14 (lines 110–122) makes the same identities the
antecedent of reproducibility.

## What the implementation binds

The exporter passes through twelve `bound_inputs` keys from the replay record
(`MarketDataEvidenceExportService.php:1811-1825`). The PAIR 01 guard proves that pass-through,
using a mocked record whose every value is populated. It does not look at where those values come
from.

**1. The publication-mode temporal and calendar identities are always empty.**
- `ReplayVerificationService.php:1029-1030` reads `$publication->temporal_identity_hash ??
  $run->temporal_identity_hash ?? ''`, and the same for `calendar_status_hash`.
- In the base schema (`Database_Schema_MariaDB.sql`), neither column exists on `eod_publications`
  (starting at L789) or `eod_runs` (starting at L632). Both exist only on
  `md_replay_daily_metrics` (L1110-1111). No migration adds them to either table.
- No code in `app/` computes or persists them at publication time.
- `ReplayResultRepository.php:188-199` requires those hashes only for `AS_KNOWN`. A
  `PUBLICATION_EXACT` result therefore stores empty strings and still passes.

So publication replay cannot verify the temporal master or calendar/status revisions frozen with a
publication, because no frozen identity exists to compare against.

**2. The reason-registry identity is nominal in both modes.**
- Publication mode: `ReplayVerificationService.php:1034` hashes
  `['coverage' => ['PASS','FAIL','NOT_EVALUATED'], 'replay' => ['PASS','FAIL','BLOCKED']]`.
- AS_KNOWN: `AsKnownReplaySnapshotService.php:80-85` hashes the same states plus a configuration
  string.
- Neither reads the reason registry itself (`eod_reason_codes`, MD-S085), so a registry change
  leaves the "bound" identity unchanged.

**3. Some versions are not bound in publication mode.**
- `ReplayVerificationService.php:1033` hashes only `config('market_data.indicators')`.
- Coverage, eligibility and price-product versions are not bound. AS_KNOWN does include coverage,
  eligibility and semantic bindings (`AsKnownReplaySnapshotService.php:71-77`).

**4. In publication mode only the factor-set hash stands for events.**
- `ReplayVerificationService.php:1031` and `:127-135` bind `factor_set_hash` only.
- Corporate-action event revisions and verification states are not bound. AS_KNOWN binds them
  through `eventFactorContext()` (`AsKnownReplaySnapshotService.php:126-152`).

**5. The dataset boundary is not bound in publication mode.** It appears only in the AS_KNOWN
context (`AsKnownReplaySnapshotService.php:91`).

## Per-predicate verdict for PAIR 01

| Predicate | Verdict | Reason |
|---|---|---|
| MD-S050-R0008 | **INCOMPLETE** | temporal identity empty and dataset boundary unbound in publication mode (1, 5) |
| MD-S050-R0009 | **INCOMPLETE** | calendar/status identity empty in publication mode (1) |
| MD-S050-R0012 | **INCOMPLETE** | event revisions and verification states unbound in publication mode (4); contamination decisions are bound only as `contamination_state` inside output batch hashes, never as an input |
| MD-S050-R0014 | **INCOMPLETE** | reason registry nominal (2); price-product, coverage and eligibility unbound in publication mode (3) |
| MD-S019-R0067 | **INCOMPLETE** | as R0008 (1) |
| MD-S019-R0068 | **INCOMPLETE** | as R0009 (1) |
| MD-S019-R0069 | **INCOMPLETE** | as R0012 (4) |
| MD-S019-R0071 | **INCOMPLETE** | as R0014 (2, 3) |
| MD-S050-R0007 | candidate, guard gap | the top-level mode, fixture and date fields are asserted present, but the negative guard never shows they come from the record |
| MD-S050-R0010 | candidate, guard gap | observation IDs, hashes, adapter and schema versions are in the manifest hash (`SourceObservationRepository.php:321-346`); the normalization version rides on the publication manifest's `canonicalization_version`, which no guard asserts |
| MD-S050-R0015 | candidate, guard gap | `expected_eligibility_batch_hash` and `expected_publication_id` are not in the guard's map |
| MD-S050-R0011, R0013; MD-S019-R0066, R0070, R0072 | candidate | composition is correct by reading the code; a per-key pass-through probe is still owed |

## Draft remediation contract — for user review, not yet implemented

1. **Seal-time identities.** When a publication is sealed, compute and persist
   `temporal_identity_hash` and `calendar_status_hash` on `eod_publications`. They cover the
   temporal universe, listing, symbol and provider-mapping revisions, the calendar/session and
   status revisions resolved for the run, and the intentional dataset boundary. A publication
   without them is refused at seal; publication replay reports `BLOCKED` rather than binding an
   empty value. This needs a forward migration, and a decision on historical publications:
   backfill as-known, or mark them `IDENTITY_UNAVAILABLE`.
2. **Reason-registry identity.** Derive it from the registry itself: a canonical hash of
   `eod_reason_codes` content or its governed revision, in both modes.
3. **Formula/registry identity in publication mode.** Bind the indicator registry and the
   coverage, eligibility and price-product versions frozen with the publication, not today's
   configuration. Publication mode then matches AS_KNOWN.
4. **Event identity in publication mode.** Bind the event revisions and verification states the
   publication used, together with its factor set.
5. **Fail closed.** `ReplayResultRepository` refuses a non-BLOCKED `PUBLICATION_EXACT` result
   with any of these identities empty.
6. **Guards.** Composition guards that fail when a single named ingredient is dropped, plus
   pass-through guards extended for R0007, R0010 and R0015. Each is mutation-probed.

The following need a user decision, because they change domain behaviour and schema: points 1–5,
the historical-publication policy in point 1, and whether the remediation belongs to MD-B18 or
needs a separate owner decision. No strategy change is proposed.

## Carry-forward — PAIR 14 and PAIR 15 (2026-09-14 11:23)

| Predicate | Verdict | Reason |
|---|---|---|
| `MD-S050-R0002` (publication replay uses exactly the identities frozen with it) | **INCOMPLETE** | The guard pair `B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_frozen_input_denies_pass` / `::test_a_fixture_declaring_every_frozen_input_correctly_still_passes` proves the comparison logic. But `runRow()` fabricates `temporal_identity_hash`, `calendar_status_hash`, `observation_manifest_hash` and `factor_set_hash`, and its comment says the block "would be empty strings" without them. Production persists no temporal or calendar identity (§1), so the predicate's temporal-master and calendar/status halves cannot be met. |
| `MD-S003-R0003` (exact publication verification of frozen … temporal revisions …) | **INCOMPLETE** | Its basis maps "temporal revisions" to the same fabricated perturbation. |

The remediation contract above covers both. Once seal-time identities exist, the same guards
re-pointed at persisted identities become their proof.

## Status of proof

The eight INCOMPLETE predicates stay `NOT_ASSESSED`, and their bases move out of `PROVEN`. Review
of the other pairs continues. Pairs whose guards depend on the same frozen identities are assessed
against this finding.


## Source-backed refinement — consolidated review package, 2026-09-15

Current read, recorded by E-MD-B18-A002-014, narrows §1 and supersedes the draft implementation
suggestion above for review purposes. PublicationGovernanceBindingService.php:68-70,102-118
already writes identity/calendar/status/event revision hashes into md_publication_lineage_bindings.
EodPublicationRepository.php:825,929,1834,1882 joins that binding and composes temporal identity.
The statement that no frozen temporal/calendar identity exists anywhere was too broad. Replay
reads nonexistent fields instead of this lineage projection; the binding content also omits parts
of the required universe/mapping/session/event/registry/version identity. The P1 defect and all
INCOMPLETE verdicts remain; no basis is promoted on this source read.

The consolidated MD_B18_A002_CONSOLIDATED_REMEDIATION_PACKAGE.md proposes extending the existing
versioned lineage context and refusing missing exact proof. It replaces the earlier parallel-column
proposal and the suggestion of filling exact identity from AS_KNOWN. Historical reconstruction and
scope remain user decisions Q1/Q2 under MD-DEP-0017; no schema/application/test change is authorized
by this refinement. Coverage remains 0/115, with 65 INCOMPLETE bases across F013–F018.


## Current C1 refinement — 2026-09-16T01:17:31.515180+00:00

D005 has approved Q1/Q2 and the bounded B18 scope; the older decision-pending text above is issue chronology, not a request to repeat Q1-Q6. E020 records the concrete C1 implementation contract/schema and producer mapping in MD_B18_A002_C1_PRODUCER_BOUND_INPUT_CONTRACT.md. Twelve capture domains account for all nine S050 bound-input members and the full S019 Invariant 1/14 composition. The mapping includes history, benchmark, sector, alternate ingest/recovery, direct persistence and inverse operations. Actual consumed inputs must be captured before producer output, not re-resolved at HASH/seal. Proposed run capture storage supports the existing publication-lineage authority; no parallel publication/run hash columns.

The review point is now the concrete additive schema, capture retry/conflict and legacy-read compatibility design; application/schema changes remain unimplemented. Existing eod_reason_codes has no authoritative historical revision proof, so AS_KNOWN cannot infer a registry snapshot from current rows/timestamps. Base SQL lacks the lineage table definition that foundation migrations provide; future C1 schema proof covers both surfaces. These are source-review findings within the existing incomplete bound-input defect, not newly satisfied predicates or a strategy change. F013 and MD-DEP-0017 remain OPEN/BLOCKING. B18 64 INCOMPLETE/114 required unchanged; supporting R0014 proof remains due.


## Executed C1 implementation — 2026-09-17T02:44:58.660158+00:00

User accepted the concrete C1 review before this patch; current CI records that acceptance before
code/schema changes. E021 proves append-only capture storage, strict canonical payload/slot checks,
idempotent retry and conflicting-member refusal, DB update/delete/upsert refusal, rollback/FK safety,
all three run-creation routes and active-run config reuse. Existing unbound runs no longer acquire
historical config identity from current state. Normal/recovered ingest captures consumed rows and
mapping results; indicators capture RAW/ATR and factor/ancillary inputs; eligibility captures universe,
materialized inputs and per-listing expectation before artifact writes. The actual full-pipeline
fixture persists 10 slots. No capture is silently disabled in production; isolated unit ports are
explicit mocks and do not count as persistence proof. New MariaDB schema exists in both databases;
all migrations also ran on a new disposable schema, removed after proof.

This is partial capture, not a complete input bundle: calendar/session raw revisions, complete
temporal/provider/status metadata, all observation outcomes, actual registry/build content and an
independent expected-slot completion manifest still need wiring/proof. The existing positive
pipeline still exercises its V1 binding/seal; that result is not V2 completeness or exact replay
proof. Binding/seal/reader/admission V2 remain unimplemented. No release/relock or closure is claimed.
F013 remains OPEN and DEP17 remains BLOCKING; basis64 and satisfied0/114 unchanged.


## C1 calendar/registry continuation - 2026-09-17T16:42:41.024384+00:00

Partial C1 continuation: exact calendar revisions/cutoff and scope completion, real registry/build artifact, market-structure input capture, isolated test port and table-qualified migration guard. Seven new caught probes. Whole input completeness remains BLOCKED: seven full-revision domains unimplemented; no predicate promotion.

Actual production scopes persist target/dependency calendar revisions before consumption, isolate cached reads per owning run/stage and preserve acquisition failure audit without holding a transaction across provider I/O. Atomic stages reject captured-member conflicts even when a consumer catches an exception. Registry content includes semantic entries and versions; the build references a verified content-addressed executable source archive, never a worktree label. Historical registry capture remains missing/BLOCKED instead of reconstructing current values. Market-structure coordinates are deterministic strings. The unit capture port does not enter production DB lookup/transactions.

The independent diagnostic rejects missing required history dates and missing full-revision contracts even if rows merely claim their operation names. It is deliberately not a finished whole-C1 validator. The 29-slot pipeline success exercises existing V1 paths, not new binding/seal/admission. New migration false positive was fixed by table-qualified rollout identities, not by relaxing enforcement for the original table; no schema changed in this continuation.

F013 remains OPEN - C1_CAPTURE_IMPLEMENTED_PARTIALLY. DEP17/DEP15 remain BLOCKING. No basis or matrix promotion. Next: MD-B18-A002: continue C1 capture at C02/C03 full temporal-identity and provider-mapping revision populations at actual producer consumption, including effective/recorded/supersedes coordinates and omission basis; then complete status, all observation outcomes, raw lineage, event/factor and ancillary full-revision contracts. Extend the independent completion validator and prove completeness before binding -> seal -> reader -> admission. Same attempt, accepted C1 contract, D005 and current CI; retain E021/E022 scoped proof without repeating it.


## C1 post-E022 validation correction - 2026-09-18T00:18:35.342848+00:00

Completed full suite: 2299 tests, 32511 assertions, 9 failures and 1 error. Seven failures are the unchanged ProductionCorpusInvariantOracleTest baseline. Two new failures exposed an unregistered ProducerInputScope::knownAt and an internal repository mock in a DB-backed completion test. One error exposed the old StageEight fixture without an explicit owning run identity. E023 corrects these tests without changing domain or acceptance logic: temporal inventory row, actual persisted calendar read rolled back through a savepoint (population 1 -> 0), and persisted fixture run 7 matching publication 10. OK (27 tests, 159 assertions). P18 caught the completion bypass once with green before/after controls and byte restoration. E022 omission-test source/P11 is historical; current omission proof is P18. Other valid probes are retained. Full post-correction suite is pending at E023 issue, attached later through current CI. F013 OPEN; DEP17/DEP15 BLOCKING; 64 incomplete, 0/114 satisfied.


## C1 C02/C03 actual producer population evidence - 2026-09-18T05:50:42.134063+00:00

C1 C02/C03 producer temporal/provider population capture and content validation: actual consumed full known revisions, deterministic selection/omission, immutable population references, source-row links and fail-closed retry/completion. Whole C1 BLOCKED; five later input domains remain; no proof promotion. Root cause: previous projection captures discarded source revision/provenance coordinates and omissions; component-name-only checks could accept nominal operation labels. Producers now consume one six-table known-revision capsule per scope; selections reference its exact hash, preserve effective/recorded/retracted coordinates and source PK/FK interval relationships, and record deterministic omissions. No inferred supersedes column/edge is invented where source schema has none. Source-row bindings retain the consumed observation row and mapping revision. Validator derives selection/omission from verified captured rows, validates reference hashes and declared scope membership, without current lookup. Retry changed-one-revision conflicts without overwrite. Normal and recovered production paths pass OK (147 tests, 9048 assertions). P19-P25 caught with exact landing and green before/after controls. Five later full-revision domains still block whole C1. 39 normal fixture slots are diagnostic only, not completeness proof. F013 OPEN; DEP17/DEP15 BLOCKING; 64 INCOMPLETE and 0/114 SATISFIED unchanged. Full post-record validation pending at issue and attached through CI.


## C1 C05 producer status population evidence - 2026-09-18T06:43:01.759744+00:00

C1 C05 scoped executable capture proof: full known trading-status revisions, effective/recorded/retracted/supersedes coordinates, authority/provenance inputs, selection and omission, immutable population reference, retry and historical fail-closed. Whole C1 BLOCKED; four later input domains remain; no coverage promotion. Full PHPUnit not rerun under latest user-limited C05 validation scope. Root cause: terminal status queries and summary returns discarded superseded/retracted/ineffective/unverified revisions and the consumed source registry/type/observation inputs. The scoped resolver now reads a single captured six-table population and preserves exact domain output plus terminal/selected revision IDs, authority evaluations and omission reasons. Content validation resolves from the verified capsule only; references bind run/stage/component/operation/knowledge/hash. No historical population is reconstructed from mutable current registries, and a consumer cannot swallow that capture failure. Repeat identical inputs is idempotent; changing one revision, registry, observation or dictionary input conflicts without overwrite. Empty population remains explicit UNKNOWN; authoritative conflict remains UNKNOWN with both omitted revisions explained. Initial fixture failures (missing board and old 39-slot assertion) corrected without weakening acceptance; normal pipeline now has 41 diagnostic slots, not a completeness proof. OK (87 tests, 5252 assertions). P26-P34 caught with one landed mutation, green before/after and byte restoration. F013 OPEN; DEP17/DEP15 BLOCKING; implementation 5, incomplete basis 64, satisfied 0/114 unchanged. Full-suite baseline remains E024 2315/32750 with seven corpus failures; no post-C05 full-suite claim.


## C1 C06 partial journal proof - 2026-09-18T17:25:23.576669+00:00

C1 C06 PARTIAL: producer-bound persisted observation journal implemented and scoped-tested/probed. Full consumption population, normalized/rejected row membership, selection/omission and recovered-read binding remain incomplete. Whole C1 BLOCKED; no proof coverage promotion. Root cause: SourceObservationRepository persisted immutable source outcomes but did not bind those envelopes/outcomes into producer input captures. Two production insertion paths now retain the exact persisted row and explicit parent/superseding content, bounded redacted payload/hash/reference and semantic coordinates. Journal declarations use independent real-storage membership checks; failed acquisition journals survive, and later acquisition can append without reinterpreting the failed outcome. Equal journal retry is idempotent; changed content conflicts. A wrapped capture error remains fail-closed. This is journal provenance, not a full consumed revision population or historical replay implementation. C06 consumption reads, row/comparison membership, selection/omission and completeness remain open. Initial validation harness errors and build-drift run are superseded by the fixed-source targeted run; see retained initial-validation-notes.txt. OK (103 tests, 6163 assertions). Five C06 probes P35-P39 caught; one landing, two green controls and byte restoration each. F013 OPEN, DEP17/DEP15 BLOCKING; four unfinished domains, five slices, 64 INCOMPLETE, 0/114 SATISFIED unchanged. No schema change or full PHPUnit run; E024 seven corpus failures remain prior baseline only.


## C1 C06 consumption completeness proof - 2026-09-19T00:28:01.759740+00:00

C1 C06 scoped whole-consumption completeness: normalized/rejected rows, revision comparison ancestry, prior selection and omissions, explicit manifests/accepted-lineage reads and normal/recovered producer selection captured and independently checked. Whole C1 BLOCKED on three later domains; no denominator coverage promotion. Root cause: boolean existsAccepted and hash-only manifests discarded their consumed row populations; recovered reads and prior-row selection lacked content-bound lineage. Production now consumes explicit-ID observation/normalized/rejected/binding/comparison capsules with parent, superseding and comparison ancestry. Pure evaluation retains prior highest-row selection and omission basis, reproduces the original lineage boolean and manifest hashes, and enforces as-known observation/binding cutoffs. Normal and recovered ingest retain exact ingress and selected/omitted partitions. Independent C06 validation requires content, population hashes, ingestion partition, required accepted reads and the normal explicit-ID manifest; empty or single-missing populations cannot pass. Failed-acquisition journal and scope retry semantics from E026 remain intact. Initial integration fixture selected an arbitrary capture by component; corrected to explicit operation identity. No production acceptance rule was changed to fix the fixture. OK (118 tests, 6299 assertions); final content/normal/recovered and changed calendar expectation: OK (15 tests, 4803 assertions). Eleven new C06 probes P40-P50 caught with one landing, two green controls and byte restoration. No P35-P39 replay. F013 OPEN, DEP17/DEP15 BLOCKING; unfinished C1 full-revision domains 4 -> 3, five slices, 64 INCOMPLETE, 0/114 SATISFIED unchanged. No schema change or full PHPUnit run; E024 seven corpus failures remain prior baseline only.


## C1 C07 RAW lineage partial capture and completed impacted pipeline validation - 2026-09-19T17:20:33.971545+00:00

C1 C07 partial RAW lineage capture and content validation on actual window/ATR/date/history-copy consumers. Scoped targeted/probe proof; independent whole-C07 read/projection/population completeness remains outstanding. Whole C1 BLOCKED; no proof coverage promotion. Root cause: ATR discarded all provenance before consumption; window/date reads lacked bound RAW source populations; history-copy ran outside producer capture scope. Real repositories now retain complete RAW rows before projection, per-row/content hashes, explicit publication identity, observation/normalized/binding/comparison ancestry, compatible normalization members and omission basis. Sealed inputs require matching immutable history for every member. History/current-copy captures explicit source table and target publication/run/date inside the same transaction. Existing canonical-write and sealed-publication error ordering is preserved. Missing references, changed retry payload, wrong normalization/source/history or historical live substitution fail closed. Main normal pipeline now uses fixture-authored bound historical inputs and validates 21 window/ATR rows and one date row, not slot count as completeness. No reconstruction of real historical inputs. Affected class result: All 57 affected pipeline cases PASS on current fixture/source state in disjoint final batches; initial 33 errors + 1 failure -> 0. Intermediate fixture version collision and event-count failure corrected. Full suite not run. The legacy fixture publication_id=0 and missing lineage were replaced by fixture-authored immutable publication/observation/normalized inputs. Fixture history version 2 avoids collision with explicit fallback version 1. The no-new-event assertion compares before/after counts to exclude prior historical owner events. No new runtime guard was weakened. These are distinct from seven-corpus baseline failures; no closure credit. Targeted coherent surface: PASS: 65 distinct tests / 6037 assertions across three disjoint final batches (all 57 pipeline cases and 8 C07 tests).. P51-P61: 11/11 caught, one landing and two green controls each, raw-byte restoration. Prior unaffected probes not repeated. Initial test source-drift invalidated while patching was rerun with stable sources; it is not a runtime defect. F013 OPEN, DEP17/DEP15 BLOCKING. Three unfinished domains, five slices, 64 INCOMPLETE, 0/114 SATISFIED unchanged. No schema change or full PHPUnit run; E024 seven corpus failures remain prior baseline only.


## E029 C07 independent completeness handoff - 2026-09-20T02:08:59.180545+00:00

C1 C07 independent consumed RAW/read/retained/projection completeness proven for window, ATR, date and both history-copy paths. Whole C1 BLOCKED on C08/C09; no denominator promotion. The E028 capture builder could certify its own count/hash without independently proving retained member and final projection correspondence. Producer scope now retains the original read population before lineage construction, compares every full RAW member, requires projection receipts and immutable read/lineage references, and rejects unfinished or inconsistent scopes. An independent oracle compares actual window, ATR, date and both copy destinations; copy destinations are read back in the same transaction. Destination digest is a completion audit, not an admitted domain input. Only created_at is omitted in copy comparison; publication/run undergo explicit target identity transformation, with validated DB integer-string normalization. Missing/extraneous/mismatched members, repaired envelope hashes, missing receipts and swallowed projection errors fail closed. Explicit empty reads and identical retry remain valid. C07 placeholder removed only after current targeted tests and P62-P71; C08/C09 placeholders remain. 54 tests / 5125 assertions PASS; final completion-manifest recheck 6 tests / 4864 assertions PASS (overlapping controls, not summed). Ten new probes P62-P71 caught, exact one landing and two green controls each; restored from original bytes. E028/P51-P61 not rerun. No schema change or full PHPUnit run; no unresolved broad regression. E024 seven corpus failures remain prior baseline only. F013 OPEN, DEP17/DEP15 BLOCKING. Full-revision C1 domains 3 -> 2; five implementation slices, 64 INCOMPLETE and 0/114 SATISFIED unchanged. No data_260914 action, new attempt, strategy change or user decision pending.
