# Finding — Admission's `temporal_identity_hash` binds market-structure/board data, not universe/listing/symbol/provider-mapping identity; several MD-S050/MD-S019 registry-version members remain unbound

- ID: `F-MD-B18-A002-021`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-09-22T10:30:00+07:00
- Severity: `P2` — a real, confirmed content-mapping defect in already-committed code (`E-MD-B18-A002-044`), discovered during the F-MD-B18-A002-013 per-predicate proof-basis review; not a regression against LOCKED strategy authority, and not blocking anything already closed
- Status: `OPEN — DISCOVERED_DURING_PROOF_BASIS_REVIEW_NOT_YET_REMEDIATED`
- Discovered during: `E-MD-B18-A002-045`'s per-predicate proof-basis review of the ten `MD-S050`/`MD-S019`/`MD-S003` bases `F-MD-B18-A002-013` carries
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
