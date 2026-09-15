# Finding — publication replay binds empty or nominal identities

- ID: `F-MD-B18-A002-013`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-09-14T11:17:22+07:00 (system clock)
- Severity: `P1` — an executable defect, plus a proof basis that overclaims
- Status: `OPEN — REMEDIATION_CONTRACT_AWAITS_USER_REVIEW`
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
