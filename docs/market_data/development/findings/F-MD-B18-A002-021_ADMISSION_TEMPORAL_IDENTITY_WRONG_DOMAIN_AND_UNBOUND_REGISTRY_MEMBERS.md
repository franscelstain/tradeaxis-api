# Finding — Admission's `temporal_identity_hash` binds market-structure/board data, not universe/listing/symbol/provider-mapping identity; several MD-S050/MD-S019 registry-version members remain unbound

- ID: `F-MD-B18-A002-021`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-09-22T10:30:00+07:00
- Severity: `P2` — a real, confirmed content-mapping defect in already-committed code (`E-MD-B18-A002-044`), discovered during the F-MD-B18-A002-013 per-predicate proof-basis review; not a regression against LOCKED strategy authority, and not blocking anything already closed
- Status: `OPEN — ONE_ITEM_REMAINS_GENUINELY_UNRESOLVED_NOT_INVENTED` (temporal_identity_hash domain
  mapping, dataset boundary and contamination decisions resolved `E-MD-B18-A002-046`;
  `serialization_version`/`executable_build_identity` resolved `E-MD-B18-A002-047` via a new
  Reader-side registry-content-decode capability; `read_model_version` resolved `E-MD-B18-A002-048` by
  capturing the already-established, already-reused canonical identity `'market_data_read_product_v1'`
  (not invented -- reused from `MarketDataReadProductService`/`EodPublicationRepository`). **`E048`
  re-verified `eligibility_version` against current authority (`config/market_data.php`, the eligibility
  LOCKED contracts, every eligibility-related service) and confirmed it has no existing identity
  anywhere at all** -- classified `GENUINELY_UNRESOLVED`, not implemented, not invented. This is the
  one item this finding stays open for.)
- Discovered during: `E-MD-B18-A002-045`'s per-predicate proof-basis review of the ten `MD-S050`/`MD-S019`/`MD-S003` bases `F-MD-B18-A002-013` carries
- Remediated (partial): `E-MD-B18-A002-046`, `E-MD-B18-A002-047`, `E-MD-B18-A002-048`
- Class: `BOUND_INPUT_IDENTITY_NOT_BOUND` (same class as its parent finding)

## Observed defect

`ReplayVerificationService::actualBoundInputContext()` (`E-MD-B18-A002-044`) sources
`temporal_identity_hash` from the manifest's `identity_revision_set_hash`. Reading
`PublicationInputBindingService::deriveCompatibilityHashes()` line by line shows that field is
derived **exclusively** from the run's `market-structure-consumed-inputs/v1` capture (C11 —
board/price-band/tick resolution), via `deriveMarketStructureBindings()`, hashing
`listing_id`/`normalized_board_code`/`board_identity_recorded_at`/`resolution_state`. It contains no
data at all from the C02 `universe_identity` capture
(`temporal-identity-revisions/v1` — issuer/instrument/listing/symbol/provider-mapping revisions),
and no representation of the "intentional dataset boundary"
(`config('market_data.scope.dataset_start')`) either.

`MD-S050-R0008` and its `MD-S019-R0067` Invariant-14 restatement both require exactly "intentional
dataset boundary and temporal universe/listing/symbol/provider mappings." The field E044 wired to
that name binds market-structure/board data instead — a genuine domain mismatch, not merely an
unproven-but-correct implementation. The two rules are not established by this field, regardless of
how thoroughly its current (wrong) content is tested.

Three further members several of the same predicates require remain unbound after E044, none of
them a mapping error, simply not yet captured/derived anywhere:

- **Contamination decisions** (`MD-S050-R0012`'s fourth listed item; the C09 ancillary/contamination
  domain). `PublicationInputBindingService` derives no compatibility hash for this domain at all —
  confirmed by an exhaustive grep of the file for `ancillary`/`contamination`/`dormant` (zero
  matches). `event_factor_hash` (E044) genuinely does bind event revisions, verification states and
  factor-set revisions/decisions now (`event_revision_set_hash` + `source_scale_assessment_set_hash`
  + `factor_decision_set_hash` + the existing `factor_set_hash`) — only the contamination member is
  missing.
- **Eligibility version** (one of the nine `MD-S050-R0014` members). No `eligibility` version
  concept exists anywhere in `ProducerRegistrySnapshot`'s captured payload or
  `MarketDataSemanticBindings::snapshot()` — confirmed by grep. This is a capture-completeness gap
  upstream of Admission, not something `actualBoundInputContext()` could bind even if it read the
  registry_versions capture more precisely.
- **`read_model_version`/`serialization_version`/`executable_build_identity`** (three more
  `MD-S050-R0014` members) still read live `config()` values in `actualBoundInputContext()`,
  unchanged by E044 — F013 did not name these three as defective, so E044 deliberately left them, but
  they remain a live-state read rather than the frozen producer identity `MD-S050-R0002`'s full
  conjunction requires. Note `buildManifestByPublicationId()` already exposes a manifest-level
  `read_model_version` (`lineage_read_model_version` / base `read_model_version`) and `build_id`
  (`lineage_build_id`) that `actualBoundInputContext()` does not currently read — a real,
  already-available (not requiring new capture) partial remediation path for a future turn.

None of this is a regression: every one of these gaps existed identically, or worse (as vacuous
constants or nonexistent columns), before `E-MD-B18-A002-044`. E044 is a genuine, confirmed
improvement for `calendar_status_hash` (now correctly combines real `calendar_revision_set_hash` +
`status_revision_set_hash`) and for three of `event_factor_hash`'s four required members. It did not
achieve full conjunction satisfaction for `MD-S050-R0002`/`MD-S003-R0003`, and it mislabeled one
field's domain rather than leaving it merely incomplete.

## Why this is filed separately from `F-MD-B18-A002-013`

`F-013` is the parent finding: "publication replay binds empty or nominal identities." This finding
is a specific, narrower, newly-discovered defect *within* F-013's own remediation (`E-044`) — the
`temporal_identity_hash` mislabeling is the reason `MD-S050-R0008`/`MD-S019-R0067` could not be
promoted during the `E-045` proof-basis review despite `E-044` existing, and the three additional
unbound members are the reason `MD-S050-R0014`/`MD-S019-R0071` could not be promoted either. Keeping
it as its own record, rather than folding it into F-013's narrative only, gives it its own
resolution lifecycle independent of F-013's broader (and already much larger) history.

## What remediation would require (not performed here)

This finding is raised by a proof-basis review turn; no code change accompanies it. A future
Admission-refinement work unit would need to, under its own Change Impact Declaration:

1. Source `temporal_identity_hash` from the C02 `universe_identity` compatibility hash instead of
   `identity_revision_set_hash` — which requires Binding to derive and persist a *new*, eighth
   compatibility hash for that domain (`PublicationInputBindingService::deriveCompatibilityHashes()`
   currently derives none), since no existing field already carries it.
2. Decide, with authority basis, whether "dataset boundary" needs its own bound field or can be
   folded into the same universe-identity hash.
3. Either derive a ninth compatibility hash for C09 ancillary/contamination content, or record a
   decision that this domain's Capture (`ancillary` component, already captured at C1 Capture time)
   is sufficient without a further compatibility-hash derivation and state why.
4. Decide whether "eligibility version" needs to be captured as new registry content (a Capture-side
   change, not just an Admission read) or is out of scope, with authority basis either way.
5. Source `read_model_version`/`build_identity`/`serialization_version` from the already-available
   manifest fields (or a corrected registry capture) instead of live config.

No decision is made on any of these here; this finding only records that the gap exists and exactly
where.

## Governed references

- Parent finding: `F-MD-B18-A002-013`
- Discovered in: `E-MD-B18-A002-045` (per-predicate proof-basis review)
- Concerns code introduced in: `E-MD-B18-A002-044` (Admission, PUBLICATION_EXACT)
- Concerns predicates: `MD-S050-R0002`, `MD-S050-R0008`, `MD-S050-R0009`, `MD-S050-R0012`,
  `MD-S050-R0014`, `MD-S003-R0003`, `MD-S019-R0067`, `MD-S019-R0068`, `MD-S019-R0069`,
  `MD-S019-R0071`

## Remediation — 2026-09-22T11:16:00+07:00 (`E-MD-B18-A002-046`)

Three of the five items listed under "What remediation would require" above turned out to be
`ALREADY_DECIDED` by existing authority and `IMPLEMENTATION_DERIVABLE` without reopening Capture or
adding a schema column, and are now implemented:

1. **Item 1 (temporal_identity_hash domain) -- done.** The C1 contract's own producer mapping table
   already separates C02 (`universe_identity`) from C11 (`market structure`) as distinct domains with
   distinct producer surfaces; using C11 content for this field was never an authorized choice, so
   correcting it required no new decision. `ReplayVerificationService::actualBoundInputContext()` now
   reads a new `componentGroupHash()` over the bound context's `universe_identity` capture references
   (already exposed by Reader, read-only) instead of `identity_revision_set_hash`.
2. **Item 2 (dataset boundary) -- done, as a consequence of item 1.** The C1 contract's own C02 row
   already lists dataset boundary as part of that domain's captured content, and
   `ProducerInputCompletionManifest::temporalMissing()` already requires `dataset_start` inside the
   `universe_identity` capture's `selection_context`, which its `slot_hash` already covers. No separate
   field was needed.
3. **Item 3 (contamination decisions) -- done.** The C1 contract's own producer table already assigns
   contamination to C09 (`ancillary`), the same component already used for benchmark/sector/event-risk;
   reading `ProducerAncillaryCapture::deriveContamination()`/`derivePriceScaleBreaks()` in full confirmed
   contamination and price-scale-break content is already captured and asserted as part of that exact
   component's payload. Its target bound field (`event_factor_hash`) was already decided by
   `B18ReplayBoundInputIdentityContractTest`'s own reviewed `identityMap()`, which maps the whole
   `MD-S050-R0012` bullet -- including contamination -- to that one field. `event_factor_hash` gained a
   fifth combined member, `componentGroupHash()` over the `ancillary` capture.

Both use the same new `componentGroupHash(array $components, string $componentKey)` helper: a
canonical hash over the bound context's own `(stage_code, slot_hash, payload_hash)` tuples for one
component_key, read entirely from data Reader already exposes. A new domain-isolation test,
`ReplayVerificationServiceTest::test_temporal_identity_and_event_factor_hash_are_domain_isolated_by_component`,
directly proves the defect class this finding named cannot recur undetected: changing only the
`universe_identity` component changes `temporal_identity_hash` and nothing else; changing only
`ancillary` changes `event_factor_hash` and nothing else.

Following through, `MarketDataReplayVerificationProofBasis` was updated: `MD-S050-R0008`/`R0009`/`R0012`
and their `MD-S019-R0067`/`R0068`/`R0069` Invariant-14 restatements moved from `INCOMPLETE` to `PROVEN`,
each rebound from `B18ReplayBoundInputIdentityContractTest` (which only ever proved evidence-export
pass-through of a fabricated metric row, never the real `actualBoundInputContext()` writer -- confirmed
unchanged and still insufficient) to `B18ReplayComparisonExhaustivenessTest`'s real-path perturbation
pair plus the new domain-isolation test where relevant.

**Two items remain, both classified `IMPLEMENTATION_DERIVABLE` (authority has already decided *where*
each belongs) but each requiring a materially larger, separately-scoped change than items 1-3, and
deliberately not rushed into the same turn:**

4. **Item 4 (eligibility version) -- not done.** The C1 contract's own C10 row already lists
   "eligibility" among the identity/version content `registry_versions` should capture, but
   `ProducerRegistrySnapshot::capture()` captures no eligibility-version concept at all today (confirmed
   by grep). Remediating this means reopening **Capture** to add a new captured field -- its own Change
   Impact Declaration, not an Admission-side read.
5. **Item 5 (`read_model_version`/`serialization_version`/`executable_build_identity`) -- not done.**
   The C1 contract's own C10 row already designates `registry_versions` as their home, and
   `ProducerRegistrySnapshot::capture()` already captures real `serialization_version`/`executable_build`
   content there -- but reading them precisely (rather than via the one combined `payload_hash` already
   used for `reason_registry_hash`/`formula_registry_hash`) requires decoding the `registry_versions`
   capture's raw `semantic_payload_json`, a Reader/Admission capability that does not exist yet.

`MD-S050-R0002`/`MD-S003-R0003`/`MD-S050-R0014`/`MD-S019-R0071` remain `INCOMPLETE` in
`MarketDataReplayVerificationProofBasis`, blocked exactly on items 4 and 5. This finding stays `OPEN`
for those two items only.

## Remediation (item 5, partial) and scope correction — 2026-09-22T12:10:00+07:00 (`E-MD-B18-A002-047`)

Before coding, the two remaining items (eligibility version; read_model_version/serialization_version/
executable_build_identity) were checked for a canonical ordering. Re-reading
`ProducerRegistrySnapshot::capture()` line by line while doing so found that item 5's own description
above was imprecise: only `serialization_version` and `executable_build.build_id` are real fields that
capture already writes (a genuine decode-only gap); **`read_model_version` does not exist anywhere in
that capture at all** -- it needs a new captured field, exactly like eligibility version, not a
decode. This is a correction to this finding's own item 5, recorded here rather than silently
absorbed into "done".

The ordering between "add a new Capture field" and "decode what Capture already writes" was
classified `IMPLEMENTATION_DERIVABLE` (infrastructure-before-consumer): building the general
registry-content-decode capability in Reader first is the lower-risk choice (it stays entirely within
Reader/Admission, the layer already under active work this whole multi-turn arc, and reopens no
"already valid, proven" layer), and the eventual Capture-reopening work for eligibility
version/`read_model_version` will itself want to expose its new fields through this same capability
rather than build a second one. This is not an arbitrary preference; it is which piece is a
prerequisite for the other's clean exposure.

`PublicationInputBindingService::verifyBoundContext()` (shared by Seal and Reader) now decodes the
already-hash-verified `registry_versions` component's raw `semantic_payload_json` once, via
`RunInputCaptureRepository::verify()` -- the same decode method every other capture-completeness
check in this codebase already uses, adding no new trust surface since it only reads content whose
integrity the existing `payload_hash` check immediately above it already established.
`PublicationInputBindingService::readBoundContext()`'s `VERIFIED` result gained a new `registry_content`
key exposing it. `ReplayVerificationService::actualBoundInputContext()` now reads
`serialization_version`/`executable_build_identity` from that decoded content -- precisely, via
`isset()` -- only when the bound context is `VERIFIED` and that specific field was actually decoded;
otherwise both report an honest empty string, never falling back to live config as before (the exact
failure mode this whole finding is about). Two targeted tests prove the real-value case and the
fail-closed-to-empty case; `B18ReplayComparisonExhaustivenessTest`'s shared stub was extended with a
real `registry_content` block so its full eleven-field perturbation suite exercises genuine content
for these two fields as well.

**Four of the original five items are now resolved. Two remain, both requiring a new field in
`ProducerRegistrySnapshot::capture()`'s payload** (eligibility version; `read_model_version`) -- the
same category of change, sharing one Capture-layer scope, which a future work unit should address
together under its own Change Impact Declaration and then wire through the decode capability this
evidence already built, rather than building a second decode path. No predicate was promoted:
`MD-S050-R0002`/`R0003`/`R0014` and `MD-S019-R0071` remain `INCOMPLETE`, still blocked exactly by
these two remaining items. Full `tests/Unit/MarketData`: 2412 tests, 34082 assertions, 0 errors, 7
failures (unchanged pre-existing MD-DEP-0015 baseline), 0 skips. Governance self-tests 12/12, 5699
assertions. **This finding remains `OPEN`, now for exactly two items, both needing the same
Capture-layer change.**

## Remediation (final derivable item) and eligibility_version reclassification — 2026-09-22T13:05:00+07:00 (`E-MD-B18-A002-048`)

Before coding, both remaining items were re-verified against `ProducerRegistrySnapshot::capture()`'s
actual current payload -- both still confirmed missing. They were then classified separately, not as
one symmetric pair. `read_model_version` has an already-established, already-reused canonical identity
elsewhere in this codebase: `'market_data_read_product_v1'`, the real (non-default) value set in
`MarketDataReadProductService::buildReadModel()`/`buildDegradedReadModel()` and
`EodPublicationRepository`'s own manifest construction (four call sites total), and already
`PublicationGovernanceBindingService`'s own fallback default. Classified `IMPLEMENTATION_DERIVABLE` --
capturing it is reuse, not invention -- and implemented: `ProducerRegistrySnapshot::capture()` gained
`'read_model_version' => 'market_data_read_product_v1'` (a fixed-literal field, the same pattern as
the existing `registry_contract` field, no config lookup needed since the value never varies).
`ReplayVerificationService::actualBoundInputContext()` now reads it from Reader's `registry_content`
decode (built `E-MD-B18-A002-047`), real when `VERIFIED`, honestly empty otherwise -- replacing a
live-config read that had always silently resolved to its hardcoded default (the config key
`market_data.governance.read_model_version` never existed) and that additionally carried a second,
independent inconsistency: it read the literal `'market_data_read_model_v1'`, a string that appears
nowhere else in the codebase, rather than the actually-established `'market_data_read_product_v1'`.

`eligibility_version` was re-checked against current authority in full: `config/market_data.php` has
no `'eligibility'` top-level section at all (unlike `coverage`, which has its own `contract_version`);
`EOD_Eligibility_Snapshot_Contract_LOCKED.md` and `Eligibility_Partial_Data_Behavior_LOCKED.md` define
no version tag; and no eligibility-related service
(`EodEligibilityBuildService`/`EligibilityDecisionService`/`CoverageGateEvaluator`) carries an
embedded literal analogous to `read_model_version`'s. **There is no existing identity to capture.**
Classified `GENUINELY_UNRESOLVED` and left exactly that way: not implemented, not invented. Choosing a
value here -- a new config key, a new hardcoded literal, or something else -- is an owner/authority
decision this work unit is not licensed to make on its own.

A new real-pipeline (not mocked) integration test seals a genuine publication through the real
`MarketDataPipelineService` and proves `PublicationInputBindingService::readBoundContext()` decodes
the real `read_model_version`/`serialization_version`/`executable_build.build_id` together, end to
end from Capture through Reader. A mocked test proves the positive and fail-closed-to-empty cases.
`B18ReplayComparisonExhaustivenessTest`'s shared stub gained the real `read_model_version` value; the
existing eleven-field perturbation suite still catches a divergence in it.

**Predicate review, explicitly not automatic:** all eleven `BOUND_INPUT_FIELDS` `ReplayVerificationService`
records are now individually real, frozen, and load-bearing for the first time -- but this is
necessary, not sufficient, for `MD-S050-R0002`/`MD-S003-R0003`'s full "using exactly ... frozen with
it" conjunction or `MD-S050-R0014`'s full enumeration, both of which explicitly name eligibility as a
required member that remains absent. `MD-S019-R0071`'s own narrower wording was not the target of
this change and is left at its prior assessment, not re-interpreted here. **No predicate was
promoted.** Full `tests/Unit/MarketData`: 2413 tests, 34098 assertions, 0 errors, 7 failures (unchanged
pre-existing MD-DEP-0015 baseline), 0 skips. Governance self-tests 12/12, 5707 assertions. **This
finding remains `OPEN`, now for exactly one item: `eligibility_version`, which needs an owner decision
before it can be captured.**
