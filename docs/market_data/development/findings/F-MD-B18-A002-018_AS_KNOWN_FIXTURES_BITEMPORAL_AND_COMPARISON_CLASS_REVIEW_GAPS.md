# Finding — as-known fixture, bitemporal resolution and comparison-class review gaps

- ID: `F-MD-B18-A002-018`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-09-15T00:29:04+07:00 (system clock)
- Severity: `P1` for closure. Bases that pass against a refusal, bases that never exercise knowledge
  time, and mistargeted bases.
- Status: `OPEN — REMEDIATION_IN_CONSOLIDATED_PACKAGE`
- Class: `PROOF_BASIS_VACUOUS_OR_MISTARGETED`
- Found by: per-predicate review of PAIRS 41–68 (28 predicates). The per-predicate review of all 69
  pairs is complete with this finding.
- Probe evidence: `E-MD-B18-A002-013`
- Dependency: `MD-DEP-0017` (consolidated remediation review)

## Bases that pass against a "wall"

A *wall* is an implementation that refuses or hides everything as soon as a cutoff is supplied. It
satisfies "later facts are invisible" and is not a knowledge-time filter.
`B18AsKnownTemporalSequenceTest`'s docblock already says the older boundary guards would hold
against one.

| Predicate | Recorded positive | Probe |
|---|---|---|
| MD-S050-R0022 (a calendar/status fact corrected after T) | `AsKnownReplayBoundaryTest::test_a_calendar_revision_recorded_after_the_cutoff_is_invisible`. It seeds one late-recorded revision rather than a correction, and asserts it resolves without a cutoff and is refused with one. | `P5X-CALENDAR-WALL` (calendar refuses every cutoff read): recorded pair green; `B18AsKnownTemporalSequenceTest::test_a_calendar_revision_recorded_before_the_cutoff_resolves_at_that_cutoff` red |
| MD-S041-R0032 (as-known must not use a future calendar correction) | same test | same probe: pair green |
| MD-S050-R0023 (a corporate action learned or verified later) | `AsKnownReplayBoundaryTest::test_a_corporate_action_recorded_after_the_cutoff_is_invisible`. Its second assertion is made without a cutoff, so it does not stop a filter that hides everything under one. | `P54-EVENT-WALL` (the knowledge-cutoff helper hides all rows): recorded positive green |

Remedy: rebind to fixtures that hold one revision known before the cutoff and one learned after:
- calendar: the before-cutoff test and the MariaDB as-known family;
- status: the suspension-lift sequence;
- corporate action: a two-revision fixture such as the as-known snapshot's event context.

Each rebind gets a behaviour probe and a wall probe.

## Knowledge time not exercised

| Predicate | Gap | Probe | Remedy |
|---|---|---|---|
| MD-S003-R0011 (calendar/session/status revisions respect effective and knowledge time) | The positive `test_status_revision_selection_applies_both_effective_and_knowledge_time` varies effective time only. Its revision is recorded before the cutoff. | `P45-STATUS-KNOWN` (status `recorded_at` bound removed): pair green; `test_a_status_revision_recorded_after_the_cutoff_is_invisible` red | Add the status knowledge-time guard to the binding |
| MD-S050-R0028 (as-known bitemporal resolution; ties and corrections deterministic; ambiguity fails closed) | Same positive, so only the status root and only effective time are covered. The rule is universal over as-known resolution. | `P45-STATUS-KNOWN` and `P48-CONFIG-KNOWN` (config `recorded_at` bound removed): pair green in both | A per-root guard set (identity, status, calendar, event, config, factor), each with both time predicates, plus the tie and ambiguity rules |

## Mistargeted bases

| Predicate | Recorded basis | Probe | Rebind to |
|---|---|---|---|
| MD-S003-R0014 (provider adjusted-close fallback is impossible) | Positive: `ReplayVerificationServiceTest::test_replay_detects_analytical_factor_set_identity_drift`, a replay comparison of factor-set hashes | `P44-ADJ-CLOSE-CANONICAL` (the canonical row keeps provider `adj_close`): positive green; negative `CanonicalRawImportBoundaryTest::test_provider_adjusted_close_never_reaches_the_canonical_row` red | The canonical-import guard, plus `CoherentPriceProductBoundaryTest::test_provider_adjusted_close_is_not_scaled_by_a_platform_factor` |
| MD-S050-R0025 (fixture: an original and a corrected immutable publication) | Positive: a mocked replay of one historical publication; no corrected publication in the fixture | `P55-ASKNOWN-PUBLICATION` (as-known resolution ignores the seal time): positive green; `B18CorrectionReadPathScenarioTest::test_a_correction_sealed_later_is_invisible_to_an_earlier_cutoff` red | That read-path test |
| MD-S055-R0025 (replay uses the mapping effective on T, and only revisions known by the cutoff) | The positive covers listing knowledge time. The negative, `test_an_as_known_config_resolution_refuses_rather_than_inventing_one`, is about configuration, not mapping. | inspection | The symbol-change and provider-remapping guards. The publication half carries F-013 (below). |

## Comparison-class gap

**MD-S050-R0029.** A replay passes only when every expected value, null reason, state, lineage,
content hash, manifest and seal assertion matches.
- The perturbation table files `final_reason_code` under "null reason". The comparison that carries
  the reason distribution is `compareReasonCodeCounts` (`ReplayVerificationService:1191`).
  `P68-REASON-COUNTS-OFF` disabled it, and the recorded pair stayed green.
- The table only asserts that *some* mismatch occurs, not which comparison fired. The seal class is
  compared twice (`seal_state` and `publication_seal_state`), so disabling one comparison left the
  seal perturbation red for the other reason, and `P68-SEAL-COMPARE-OFF` stayed green.

Remedy: a null-reason perturbation, and an assertion that each perturbation names its own mismatch
field. `ReplayVerificationServiceTest::test_verify_replay_marks_mismatch_with_reason_code_when_reason_code_counts_diverge`
is a candidate binding for the reason distribution.

## Carried forward under F-013

- **MD-S041-R0032.** Its first sentence says historical processing uses the calendar revision
  governed for the replay mode. In publication mode the calendar identity is empty (§1), so no
  governed calendar revision is bound.
- **MD-S055-R0025.** Publication replay does not freeze the mapping identity; the temporal identity
  is empty (§1).

## Kept `PROVEN` after review — probe evidence in E013, E011 and E012

- **MD-S003-R0019.**
  - `P41-PRIOR`: prior current mirrors were left in place, and the positive went red.
  - `P41-POINTER`: the pointer was not moved, and the positive went red.
  - `P41-NEG-PK`: the mirror primary key was removed, and the negative went red. The production
    engine enforces the same key; `B18ScenarioFamiliesOnMariaDbTest` asserts it in the correction
    family. Binding the MariaDB assertion would be the stronger negative.
- **MD-S058-R0069.** `P42-SUPERSESSION`: the supersession knowledge bound was removed, and both
  guards went red.
- **MD-S050-R0024.**
  - `P48-CONFIG-KNOWN`: the config knowledge bound was removed, and the positive went red.
  - `P43-CONFIG-WALL`: a config wall turned it red too, because the earlier configuration must
    still resolve under the cutoff.
- **MD-S003-R0022.**
  - `P35-SNAPSHOT-FREEZE` (E012): caught.
  - `P5X-CALENDAR-WALL`: turned it red, so the positive carries its own control.
- **MD-S050-R0019, R0020, R0021.** The behaviour probes are P11 in E011. The vacuity probes
  (`P5X-UNIVERSE-EMPTY`, `P5X-MAPPING-MISSING`) turned each red. The structural negative
  `test_every_named_fixture_guard_exists_and_is_executable` does not add to that.
- **MD-S050-R0035.** `P56-MODE-DEFAULT` turned the positive red; `P56-MODE-REFUSE-ALL` turned the
  negative red.
- **MD-S050-R0026.** `P57-OUTAGE-DROPPED`: filtering FAILED observations out of the manifest turned
  it red.
- **MD-S003-R0006.**
  - `P58-UNKNOWN-AS-NOT-EXPECTED`: unknown treated as bar-not-expected, and the positive went red.
  - `P58-SUSPENSION-EXPIRES`: an open suspension stopped carrying forward, and the negative went red.
- **MD-S003-R0008.** `P59-FALLBACK-DATE-DROPPED` turned the positive red; `P59-ALWAYS-HOLD` turned
  the negative red. For `P59-ALWAYS-HOLD`, the harness printed `landed=0` only because the
  replacement block already exists at `FinalizeDecisionService:150-152`. The anchor was unique and
  exactly one replacement was made.
- **MD-S003-R0007.** `P60-STALE-ACCEPTED`, `P60-STALE-WIDENED`, `P60-ZERO-PRICE` and
  `P60-SCHEMA-INVALID` each turned their guard red. That includes the two pre-existing members the
  recorded basis names but had not probed.
- **MD-S003-R0012.** `P61-SYNTHETIC-ADMITTED` and `P61-ADMIT-NOTHING` each turned the positive red.
  The recorded negative, `CorporateActionCandidateBoundaryTest::test_a_price_derived_action_does_not_suppress_contamination`,
  tests `EventRiskSourceRepository::isAdjustable`. That method is private, returns `false`
  unconditionally, and is called nowhere, so the negative guards a stub. The positive carries its
  own control, and the negative should be rebound.
- **MD-S003-R0013.** `P62-HIGH-NOT-SCALED` turned the positive red; `P62-ADJ-CLOSE-SCALED` turned
  the negative red.
- **MD-S003-R0015.** `P63-COEFFICIENT` turned both red; `P63-TRUNCATED-CHAIN` turned the negative
  red.
- **MD-S003-R0016.** `P64-PROXY-ADMITTED` turned the positive red; `P64-PROXY-INTO-ACTUAL` turned
  the negative red.
- **MD-S003-R0017.**
  - `P65-HISTORICAL-DENIED-SUMMARY` turned the positive red.
  - `P65-SEALED-GUARD-OFF` turned the negative red.
  - The positive's publication row is mocked, but every field it carries is written by
    `EodEvidenceRepository:246-250` for a non-current pointer, and the test forbids the
    current-pointer lookup outright.
- **MD-S003-R0018.** `P41-POINTER` turned the positive red; `P66-CANDIDATE-BORN-CURRENT` turned the
  negative red.
- **MD-S003-R0020.** `P67-BASE-FALLBACK-DATE` turned the positive red; `P59-ALWAYS-HOLD` turned the
  negative red.

## Harness notes

- `IndicatorVectorService.php` is CRLF. Its two multi-line anchors were rerun CRLF-aware in the
  follow-up.
- `P65-HISTORICAL-DENIED` first probed `MarketDataEvidenceExportService:621`, which the test does
  not read. The follow-up probed `:230`.
