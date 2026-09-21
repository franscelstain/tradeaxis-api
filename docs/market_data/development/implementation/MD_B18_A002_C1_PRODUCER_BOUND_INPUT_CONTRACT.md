# C1 — producer-bound input context: implementation review v1

Status: **REVIEW_READY — NOT IMPLEMENTED**. Role: IMPLEMENTATION_GUIDANCE,
MUTABLE_UNTIL_CLOSURE; no strategy authority. Same MD-B18 / MD-B18-A002 /
MD-B18-A002-BL001 / MD-REBASELINE-20260820-001. CI-MD-B18-A002-001 precedes this document.
Decision boundary: D-MD-B18-A002-005. Source inspection: E-MD-B18-A002-020.

This is the concrete C1 contract/schema and producer mapping review point requested before domain
code. Q1–Q6 remain approved. The original consolidated package stays byte-preserved as E014 review
context; D005 overrides its old pending labels and broad Q4 exclusions. This document refines C1
under those decisions and does not authorize a new stage, historical reconstruction or relock.

## 1. Outcome, scope and review decision

**Recommendation:** capture the exact input values/revisions at the producer's consumption boundary,
retain them per run, then freeze a versioned bundle in the existing publication lineage binding.
Publication replay loads that bundle by explicit publication ID. Missing or unverifiable required
inputs produce BLOCKED with named paths. Re-querying today's tables at HASH/SEAL is not capture.

The review is of three concrete implementation choices within Q1/Q2:

1. One append-only per-run capture table, and four nullable versioned-context columns on the
   existing `md_publication_lineage_bindings` table (§4). No parallel publication authority.
2. Repeated consumption of an already captured logical input slot must reuse identical bytes;
   a different result is a capture conflict and fails closed. It cannot overwrite the prior slot
   or seal. A changed acquisition/recovery selection must use the existing authorized run/recovery
   lifecycle, not a new lifecycle invented by this contract (§5). Implementation must stop for an
   owner decision if an existing authorized flow cannot meet this constraint within B18.
3. V1 publications keep their bytes and existing read classification. Exact verification of a
   publication without a complete V2 bundle is BLOCKED. New candidates under the changed executable
   must satisfy V2 before seal/promotion; no synthetic default upgrades (§6).

Alternative for this handoff: revise these choices before implementation. A seal-only read of live
tables is not an acceptable alternative: it cannot prove which values were consumed earlier.
Deferring producer capture would permit a fail-closed diagnostic patch but would leave C1 incomplete.
Reviewing this document does not re-open Q1–Q6 or grant authority to reconstruct historical identity.

No application, migration, base SQL, runtime configuration or test body is changed by this document.
The next executable action at this handoff is review of this contract. After review, amend current
CI with the exact implementation slice before capture code/schema changes.

## 2. Source-backed problem and normative composition

- `MarketDataPipelineService::completeHash` calls `PublicationGovernanceBindingService::bind`
  after ingest, indicators and eligibility. The binder reads calendar revisions and current formula
  config at that late point. Its identity hash is a board-resolution subset, not full temporal identity.
- `EodIndicatorsComputeService::compute` consumes bar windows, boundary-seeded ATR history,
  benchmark, sector, event/contamination, factor and config inputs before that binding call.
  `EodEligibilityBuildService::build` separately consumes universe, status, expectation and dormancy.
- `ReplayVerificationService::actualBoundInputContext` reads nonexistent publication/run
  temporal/calendar fields and hashes live config/nominal reason states. The direct repository
  requires the extended hashes only in AS_KNOWN. An apparently valid hex hash is not proof of content.
- `eod_reason_codes` is mutable and has no bitemporal revision contract in its current schema.
  Its state names or `updated_at` cannot prove a registry snapshot at a historical cutoff.
- The base SQL currently has no `md_publication_lineage_bindings` definition; the foundation
  migration creates it, and later migrations extend it. C1 must account for both claimed schema
  surfaces, without rewriting issued migration history or unrelated global schema (§4).

Frozen authority: S050 publication mode and all nine Required bound inputs; S019 Invariant 1
and the entire Invariant 14 antecedent/consequence; S005 hash/manifest rules; S018 seal preconditions;
S043–S046 publication/pointer/lineage; S034 formatting. Those rules control every choice below.

The normalized meaning of each S019-R0066…R0072 ingredient is: **when all seven invariant-14
inputs are identical, including this ingredient, replay reproduces identical semantic outputs and
hashes**. Presence of the ingredient alone is insufficient. R0073 is the consequence of the complete
antecedent, not an isolated hash check. R0009 imports the full R0005–R0008 invariant into rerun/replay.
Current matrix shorthand is recorded in the E020 snapshot. This review does not modify it or carry
proof forward as if complete composition were already proven. Any later context correction covers
the entire parent, preserves source fingerprints, tabulates transitions and invalidates affected
verification before binding. Applicability stays unchanged; no denominator reduction is proposed.

## 3. Three separate objects and acyclic hashing

### 3.1 Producer input bundle — `md_publication_inputs_v2`

Captured inputs consumed to produce the publication, independent of a later test fixture. Required
root members: `schema_version`, `scope`, `components`, `component_manifest`, `semantic_input_hash`.
Scope includes market/product scope, requested/effective trade date, intentional dataset boundary,
knowledge coordinate of the original run, source/request mode and declared producer contracts.
Component entries contain a stable slot, exact domain payload, content hash, selection context,
row population and immutable source references sufficient to verify the content.

Do not put the later replay mode/fixture ID/fixture hash into the original producer's input hash:
they did not exist as inputs to the original publication. They belong to the verification binding.
Likewise exclude output hashes and the publication manifest hash from the producer input hash.

### 3.2 Publication binding and seal

Existing publication lineage binds the V2 input bundle hash plus existing config, observation,
factor, product and temporal component hashes. Compute outputs using captured inputs; compute the
three artifact hashes using S005; compute a versioned publication manifest containing the bundle
hash, outputs and declared lineage; then seal. There is no hash cycle. The bundle must not include
its own digest when serialized. The manifest must not include its own digest either.

Existing V1 manifest bytes/hash algorithm remain verifiable as V1. V2 uses an explicit version
discriminator with a separate payload builder; never reinterpret an old seal as V2 because some
new nullable columns exist. The version discriminator and input hash participate in V2 manifest
identity. A V2 candidate cannot select a V1 validation path to bypass completeness.

### 3.3 Verification binding — explicit publication plus independently reviewed fixture

`PUBLICATION_EXACT` request/result binds explicit `publication_id`, publication version, expected
manifest/hash and fixture ID/version/hash, requested/effective dates, producer bundle hash and
expected publication/pointer/seal/output assertions. Expected values remain independent fixture
oracles; neither producer capture nor export is permission to generate expectations from actuals.
All nine S050 members are supplied by the composition of producer bundle, publication manifest and
fixture binding; no individual object pretends to be all three.

Current-pointer verification is a separately named assertion. A superseded historical publication
can be verified by explicit identity without requiring it to be current. Original pointer semantics
and correction relationships remain in the fixture/audit; current/latest lookup cannot fill gaps.

## 4. Concrete schema proposal (review only)

### 4.1 Append-only `md_run_input_captures`

| Column | Proposed MariaDB type / constraint | Meaning |
|---|---|---|
| input_capture_id | BIGINT UNSIGNED primary auto increment | Audit navigation only |
| run_id | BIGINT UNSIGNED NOT NULL; FK eod_runs, RESTRICT | Owning execution |
| stage_code | VARCHAR(32) NOT NULL | Consuming producer stage |
| component_key | VARCHAR(64) NOT NULL | One of the declared component domains |
| slot_hash | CHAR(64) ASCII binary NOT NULL | SHA-256 of stable logical slot: consumer contract, operation, selection arguments and domain scope |
| capture_schema_version | VARCHAR(64) NOT NULL | `md_producer_capture_v1` |
| selection_context_json | LONGTEXT NOT NULL, JSON_VALID check | Full declared query/scope and temporal coordinates |
| semantic_payload_json | LONGTEXT NOT NULL, JSON_VALID check | Canonical consumed values/revisions; or complete immutable content references |
| payload_hash | CHAR(64) ASCII binary NOT NULL | Digest recomputed from canonical payload |
| member_count | BIGINT UNSIGNED NOT NULL | Actual selected population |
| empty_basis_json | LONGTEXT nullable, JSON_VALID when non-null | Explicit legitimate-empty explanation; never a default |
| audit_context_json | LONGTEXT NOT NULL, JSON_VALID check | Producer/build/source row IDs and execution navigation |
| captured_at | DATETIME(6) NOT NULL | Capture audit time, distinct from source knowledge/availability |

Unique `(run_id, stage_code, component_key, slot_hash)`. Index run/stage for completion checks.
The hash fields must be 64 lowercase hex characters; JSON validity alone is insufficient.
No update/delete of an issued capture; same-key/same-byte retry returns the existing capture,
same-key/different-byte retry raises a conflict. No `updateOrInsert` merge of protected content.
Application guard plus MariaDB enforcement for the newly introduced immutable capture boundary;
test direct SQL update/delete and conflict writes. No claim that this proves arbitrary administrator
tampering impossible. Hash verification and independently retained evidence remain necessary.

Capture records for failed/unsealed stages remain audit material and do not imply admission.
Retention cannot remove a capture referenced by a sealed bundle. Deletion/cascade from an owning
run or publication must not erase referenced proof. No destructive cleanup migration is proposed.

### 4.2 Extend existing `md_publication_lineage_bindings`

Add nullable columns, without backfilling historical rows:

| Column | Proposed type | Meaning |
|---|---|---|
| bound_input_schema_version | VARCHAR(64) | `md_publication_inputs_v2`; null means legacy/unavailable |
| bound_input_context_json | LONGTEXT with JSON_VALID when non-null | Immutable assembled producer bundle, including capture references |
| bound_input_context_hash | CHAR(64) ASCII binary | Canonical semantic bundle digest |
| bound_input_capture_manifest_json | LONGTEXT with JSON_VALID when non-null | All required consumed slots, capture IDs/hashes/counts and completion evidence |

Application completeness checks require all four for every new candidate using V2. Existing
publication unique key and bindings remain. No parallel temporal/calendar columns on publication
or run are introduced. New assembly happens under the existing candidate lock. Once V2 binding is
issued, same bytes are idempotent; conflicting input requires rejection and the authorized lifecycle.
Sealed binding insert/update/delete and attempt to replace/reset the V2 discriminator must be
rejected through both repository path and tested database protections. Deleting the publication,
resetting SEALED to UNSEALED, or using upsert must not evade protection. If existing owner constraints
prevent lawful implementation of these protections, record the concrete blocker before widening scope.

### 4.3 Deployment, mirrors and rollback

One forward migration creates the capture table and extends lineage, validates parent existence
and keys, and installs bounded protections. Future base-SQL amendment describes the same C1
structures/constraints, including the lineage substrate it currently omits. Both fresh base schema
and actual migrated schema are inspected; a migration-only grep cannot claim full coverage.
Do not edit an already-applied migration to simulate delivery. Migration runs on both `tradeaxis`
and `tradeaxis_testing`; verify DDL parity and real MariaDB behavior before runtime proof.

SQLite mirror receives the required fields and behavioral fixture data, while D001's existing
FK/nullability deferral to B21 remains explicitly separate. No new waiver is implied. A destructive
down operation must refuse if captured/bound proof exists. Deployment rollback must fail closed for
V2 data rather than run a legacy executable that could bypass the new binding contract; operational
rollout decision/claim remains subject to owner governance, not performed in C1.

## 5. Producer mapping and capture contract

For each slot, **the returned materialized input used by the producer is the captured payload**.
Do not issue a second query just to describe it. Persist capture before dependent output commits,
inside the same DB transaction when possible. Acquisition outside the artifact transaction records
immutable observation envelopes first, then binds those exact outcomes at consumption. Failed
transactions cannot leave committed output with an absent capture.

The producer completion manifest declares every applicable slot and actual membership, independently
of which captures happen to exist. Compare expected slot set to captured slot set in both directions.
Capture count zero or an empty JSON object is never implicit success. A genuinely empty event set,
for example, includes domain tag, selection range, source/cutoff, evaluated population and reason;
missing source evidence remains BLOCKED. Source observations include failures/rejections relevant
to the run, so a provider outage cannot silently disappear from completeness.

| Slot | Producer / existing surface | Captured payload and required read-path coverage |
|---|---|---|
| C01 run/config | EodRunRepository::getOrCreateOwningRun, createAsKnownReplayRun, createPromoteRunFromSeed; MarketDataConfigSnapshotRepository::resolveForRun/find | Exact selected snapshot ID/content/hash, schema/resolver/registry/build versions, source/request/date/cutoff. Producers must consume the bound resolved config; live `config()` values cannot diverge from what is hashed. A seed/promote run cannot silently inherit one snapshot while consuming another. |
| C02 universe/identity | TickerMasterRepository::getUniverseForTradeDate/getProjectedUniverseForTradeDate; TemporalIdentityRepository::readProjectedUniverseAsOf/resolveByTickerCodes | Full selected issuer/instrument/listing/symbol/board membership and effective/recorded/retracted coordinates, stable IDs, omitted-listing reasons, dataset boundary. Freeze complete population, not only delivered bars. |
| C03 provider mappings | EquityProviderSymbolResolver::resolveContext; TemporalIdentityRepository::resolveProviderContext; SourceObservationRepository::bindResolvedIdentity | Every consumed provider mapping and source-row link, provider symbol, revision/content, applicability and knowledge interval. Manual-file mapping and acquired/recovered-row paths must bind equally; no API-only capture. |
| C04 calendar/session | MarketCalendarRepository::sessionContext, tradingDatesBetween, tradingDateWindowStart; EodBarsIngestService, CoverageGateEvaluator, ExpectedBarDecisionService | Session facts, conflict/unknown result, source/revision identities and knowledge bounds for target AND all dependency/history dates. Include trading-day chain used for lookbacks/ATR/contamination; not only target date's session_state. |
| C05 status/expectation | TemporalTradingStatusRepository::resolveForListing; ExpectedBarDecisionService::decideForListing; EodEligibilityBuildService::build; CoverageGateEvaluator::evaluate | Exact status/source/revision selection, effective/known time, full state/reason, expected/delivered membership and denominator. Reusing a cached status must reuse its capture; conflicting reads cannot silently collapse. |
| C06 observations | EodBarsIngestService::acquireSourceRows/ingestAcquiredRows; SourceObservationRepository capture/outcome/accepted/rejected/failure and manifest methods | Accepted, rejected, stale, schema-invalid, missing/superseding outcomes; payload hashes/references, provider/mapping, requested/selected dates, semantic availability times, adapter/schema/normalization versions. Carry contents or verifiable immutable references, not an unexplained digest. |
| C07 RAW/history | EodArtifactRepository::loadBarsForTradeDate/loadBarsWindow/loadAtrSeriesForTickerFromBoundary/replaceBarsHistoryFromPublication; EodIndicatorsComputeService::compute | Exact RAW rows and history dependencies, stable row keys, input publication/observation hashes, boundaries, quality and annotations; immutable references for every historical slice. Current projection must be materialized/bound before consumption and cannot stand in for historical identity. |
| C08 event/factor | AdjustmentFactorSetService::ensureForPublication/authoritativeEventsThrough/factorTermsForEvent/latestAssessment | Selected AND held/rejected event revisions, complete terms, effective/learned/verified/recorded state, factor-set content/revision, factor decisions/reasons, source-scale assessments; factor_set_hash alone is insufficient. |
| C09 ancillary/contamination | EodIndicatorsComputeService; BenchmarkIndicatorComputeService; SectorClassificationRepository; EventRiskSourceRepository; PriceScaleBreakRepository; EodArtifactRepository::loadDormantTickerIds | Benchmark, sector, event-risk, contamination and dormancy inputs actually used, including null/unavailable causes and dependency dates. These are input dependencies of C1, not a new watchlist/readiness policy. |
| C10 registries/versions | MarketDataConfigSnapshotRepository; PlatformConfigRegistry; MarketDataSemanticBindings; eod_reason_codes; formula/indicator specification and executable implementation | Canonical reason entries `(code,category,description,severity,is_active)` sorted by code, plus registry source identity; full formula/indicator/price-product/coverage/eligibility/read-model/serialization/build identity. Registry content and applied executable build must agree; build string alone is insufficient. No generic `development-worktree` identity or surrogate snapshot fallback. |
| C11 market structure | PublicationGovernanceBindingService::bindMarketStructure and its producer inputs | Capture the actual board/price-band/floor/tick facts used when eligibility is augmented at HASH, then hash that result. This is legitimate production at HASH; it does not authorize recapturing earlier C02/C04/C05 inputs from live roots. |
| C12 completion/binding | MarketDataPipelineService::completeHash; PublicationGovernanceBindingService::bind; EodPublicationRepository manifest methods | Exact completed capture-slot membership, cross-stage identity consistency and dependency closure. Assemble from captures and immutable inputs; derive temporal/calendar/event/formula/reason projections from that same bundle, with versioned algorithms. |

### Alternate entry paths and operation inverses

The implementation slice must account for all of these before its coverage claim:

- Normal `completeIngest`, `completeIngestWithAcquiredRows`, `completeRecoveredRowsPartial`,
  `importDailyFromAcquiredRows`, `applyRecoveredRowsPartial`; normal/correction/hash/seal/finalize.
- API range acquisition and manual source adapters; direct ingest calls; import-only to promote
  through `createPromoteRunFromSeed`; partial/analytical/corpus reconstruction callers. Merely reading
  reconstruction source is allowed here; executing a reconstruction/recovery remains unauthorized.
- Candidate creation/reuse, `bindCandidateAcquisitionProvenance`, `bindCandidateAnalyticalProduct`,
  `updateCandidateHashes`, manifest preparation, normal and partial seal, promotion, pointer restore,
  `clearCurrentPublicationState`, `discardCandidatePublication`, artifact copy/replace/delete.
- Direct SQL and repository mutation surfaces for the new capture/binding, not only the service.
  Guard bodies must check sealed state under the transaction/lock used for writing. A caller-supplied
  stale object or pre-check outside the transaction cannot authorize a write after a concurrent seal.

Capture component, slot and producer build are mandatory on every relevant entry path. Optional
constructor dependencies or legacy test fixtures cannot silently disable capture. Existing flows
without complete producer evidence fail closed; positive fixtures must construct real captured
inputs through the same repositories rather than populate fabricated replay-only hash properties.

## 6. Binding → seal → reader → admission

1. **Capture:** immutable per-run facts/config returned to and consumed by each producer, with
   dependency membership and transaction rollback behavior (§5).
2. **Binding:** lock candidate + owning run; verify ownership/cutoff/config consistency; validate
   complete slot manifest; canonicalize and persist V2 input context on existing lineage. Derive
   compatibility component hashes from those contents; compare them, do not maintain independent
   competing values. No new registry lookup or mutable calendar resolution for an earlier stage.
3. **Seal:** verify every required slot/content reference and recomputed digest before output hash,
   manifest or seal admission. Both `sealCandidatePublication` and its partial route enforce the
   same V2 completeness. `prepareCandidateManifestForSeal`, integrity-context checks and promotion
   cannot bypass it. Failure rolls back and preserves predecessor/current pointer and sealed bytes.
4. **Reader:** `publicationManifestContext`, `buildManifestByPublicationId`, explicit historical
   replay resolution, evidence export and read-product repository use the same version-aware
   projection. Historical verification does not consult current/latest as fallback. V1 ordinary
   read eligibility follows the existing owner contract, while V1 exact verification is BLOCKED
   unless complete authoritative bound evidence is already available and valid under its contract.
   This patch does not retroactively relabel all old current publications or clear their pointers.
5. **Admission:** ReplayVerificationService, direct ReplayResultRepository persistence, replay
   exporter, fixture verifier/backfill and evidence-admission paths share a validator for both
   exact and as-known required inputs. Non-BLOCKED requires complete verifiable binding; unavailable
   input is BLOCKED, execution divergence is FAIL/MISMATCH. A BLOCKED record retains explicit mode,
   requested identity, known fields, exact missing paths and original reason; cannot claim MATCH or
   ADMISSIBLE. A negative fixture expectation matching BLOCKED is not a replay PASS (C2 interface).

`ReplayBackfillService`, `FullRangeCurrentEvidenceReplayService`, `ReplaySmokeSuiteService` and
`MarketDataEvidenceExportService` are included in reader/admission mapping. Q3 manifest/backfill
behavior is implemented in C2 after C1; C1 must expose explicit identity/missing-path contracts now.
B19 lifecycle defects remain return-to obligations, not silently repaired here.

## 7. Serialization, reason history and Q4 boundary

All semantic hashes use S005/S034 canonical rules: SHA-256 lowercase, UTF-8, deterministic field and
stable row ordering, canonical JSON for object/set fields, explicit number/null/boolean/date rules.
Validate the serialized bytes, not merely `json_decode` equivalence. Every component is tagged with
its schema/domain so an empty event set cannot masquerade as empty identity or absent registry.

The bundle has a **semantic projection** and a separately retained **audit projection**. No generic
recursive removal of `id`, `*_at` or timestamp fields is allowed. Stable listing/revision identities,
availability/effective/known/learned/verified time, source timestamps where semantic, dates, reasons,
correction lineage and all values remain semantic. Field-specific S005 navigation exclusions apply
only to content hashes; navigation IDs remain verified in audit/fixture identity. Q4 is not authority
to drop `run_id`, publication ID or other fields from an entire result by convention. C3 must submit
its explicit field classification and compare independent reruns, not repeated export of one record.

For reasons, capture actual complete registry content at producer start/use and apply that snapshot
to the run. `created_at`/`updated_at` are retained for audit but are not a historical revision proof.
AS_KNOWN may use a registry snapshot only with authoritative content and known/effective coordinates
that establish its eligibility at the cutoff. Where no such historical snapshot exists, record
missing reason-registry proof and BLOCKED; do not use today's rows or infer history from timestamps.
Adding temporal registry governance is not silently included: if required beyond this frozen-snapshot
mechanism, record a separate decision need before code. No historical reconstruction is performed.

## 8. Per-predicate acceptance and single-defect probes — PLANNED

Every line below is a proof obligation, not a PASS. Each control must establish persisted producer
inputs and a real explicit publication; then mutate exactly one consumed instance and assert the
named component/path fails. Before/after controls green, landed count 1, byte-copy restore.

| Predicate(s) | Required assertion for that predicate | Planned defect |
|---|---|---|
| S050-R0007 | Request and persisted/exported result agree on explicit mode, fixture ID/version/hash and dates/cutoff. | Drop only persisted fixture identity, leaving all other fields valid. |
| S050-R0008; S019-R0067 | Complete historical universe/dataset/issuer/instrument/listing/symbol/mapping revisions are those consumed; with all seven invariant-14 inputs unchanged, outputs/hashes reproduce. | Change one provider mapping or omitted listing/dataset boundary in captured input; assert component mismatch, not an unrelated error. |
| S050-R0009; S019-R0068 | Exact target and dependency calendar/session/status facts are frozen; invariant-14 repeated outputs/hashes hold with the complete antecedent. | Drop one dependency-date session or one status knowledge coordinate while target-date hashes remain valid. |
| S050-R0010; S019-R0066 | Full observation outcomes and adapter/schema/normalization versions survive producer→bundle→fixture; full-antecedent rerun reproduces. | Drop one rejected observation outcome or only normalization version; population still nonzero. |
| S050-R0011 | RAW input set and history dependencies are identified, immutable and independently comparable. | Replace one historical slice with current projection while all other bindings match. |
| S050-R0012; S019-R0069 | Consumed event terms/verification, factor decisions and contamination content are bound; full-antecedent rerun reproduces. | Change one verification/held decision without changing an already populated factor-set digest; require recomputation/detection. |
| S050-R0013; S019-R0070 | Full snapshot ID and content hash identify actual consumed config; full-antecedent rerun reproduces. | Make one producer use live config different from the run snapshot. |
| S050-R0014; S019-R0071 | Every listed formula/registry/product/coverage/eligibility/read-model/build/version component binds actual content; full-antecedent rerun reproduces. | Edit one reason registry entry with the nominal state list unchanged; separately drop each required version member. |
| S050-R0015 | Fixture's explicit publication, pointer/seal and all three output hash assertions are compared individually. | Remove expected publication ID or expected eligibility hash only; reject incomplete fixture. |
| S050-R0016 | Any missing required input yields BLOCKED with named paths at service AND direct persistence/export/admission; no current fallback. | Bypass just direct-repository exact-mode completeness, then prove a non-BLOCKED incomplete insert is rejected by its guard. |
| S019-R0072 | Canonical serialization of identical complete inputs yields identical bytes under declared formatting rules. | Alter one ordering/null/number serialization rule while other components remain fixed. |
| S019-R0009, R0073, R0074; S050-R0002; S003-R0003 | Full invariant 1/14 and exact-publication contract: independently rerun with all consumed inputs fixed; changing current roots cannot leak into exact identity. | Poison one current root after seal; reader must use bound historical data or BLOCKED, never silent substitution. |

Do not treat a grouped row as family proof: each listed predicate needs its own reviewed explanation,
assertion result and evidence IDs. The complete retained PAIR01 list still comes from package §8;
eight strengthenings and 42 other retained revalidations remain. No basis is promoted by this review.

Cross-cutting C1 probes: omit one declared capture slot; zero-row scan; capture hash/content mismatch;
same-slot conflicting retry; partial-ingest bypass; direct SQL update/delete; stale-object seal race;
manifest version downgrade; one base-SQL instance wrong while migrations correct; one migration
instance wrong while base SQL correct; old incomplete publication BLOCKED without pointer mutation.
Schema scans enumerate base SQL plus all migrations, assert populations, and fail on one bad instance.

## 9. Impact, validation and handoff

Bounded implementation touches capture/config consumption and producer plumbing, lineage assembler,
version-aware publication manifest/seal, exact replay projection, repository persistence and export/
admission. Owner behavior remains S018/S043–S046; no new readiness primary ownership. Revalidate
affected B04 temporal, B10 hash, B11 publication and B17 read-product paths when implementation lands;
register explicit cross-stage evidence relationships before claiming supporting proof. There is no
automatic inheritance of their prior closure or primary B22 R0014 acceptance.

Review criteria: all nine S050 members and full S019 antecedent/consequence are accounted for;
schema is additive and historical bytes unchanged; real consumed input is captured; every alternate
writer/reader/inverse has a declared enforcement point; no broad Q4 exclusions or current fallback;
planned guards detect a single broken instance. Source inventory/callers and schema occurrences are
retained in E020's raw manifest. Static mapping is not runtime proof of complete dynamic call coverage.

At issue: concrete C1 review package outstanding **1 → 0**; code implementation slices remain
**5/5** (capture, binding, seal, reader, admission); B18 incomplete **64 → 64**, SATISFIED **0/114**.
F013 and MD-DEP-0017 remain open/blocking for executable proof. MD-DEP-0015 corpus criterion and
separate MD-DEP-0016 recovery remain unchanged; no action on `data_260914`.

SINGLE EXACT NEXT EXECUTABLE RESUME: review this C1 v1 contract/schema and producer mapping under
D005. After that review, record its outcome and current CI implementation slice, then implement
capture first in the same MD-B18-A002. No application/schema changes precede that review point.
