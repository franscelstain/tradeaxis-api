# Finding — `F-MD-B18-A001-001`

- ID: `F-MD-B18-A001-001`
- Stage / Attempt / Baseline: `MD-B18` / `MD-B18-A001` / `MD-B18-A001-BL001`
- Raised at: 2026-09-07T09:10:00+07:00
- Severity: `P1`
- Status: `OPEN`
- Class: `PROOF_SURFACE_NOT_ADMISSIBLE`
- Blocks: binding of the 121 `MD-B18` required predicates, and therefore `MD-B18` closure
- Blocks strategy change: `NO` — no strategy authority is implicated

## Statement

The `MD-B18` proof surface reports `121/121` proof-map coverage and its readiness gate and mutation
self-test both exit zero, but the map cannot support binding. Two independent defects were measured,
not inferred.

## Defect 1 — proof families are assigned by substring matching on rule text

`MarketDataReplayVerificationProofSpec::familyFor()` selects a predicate's proof family with a
cascading `strpos()` chain over `section.' '.rule_text`, ending in an unconditional
`return 'mode_admission';`. Measured over the actual 121 rows:

| Family | Rules | How they got there |
|---|---|---|
| `evidence` | 41 | `evidence` 26, `pass` 9, `fail` 3, `mismatch` 2, `blocked` 1 |
| `bound_inputs` | 25 | `config` 10, `bound input` 8, `factor` 3, `hash` 3, `formula` 1 |
| `as_known` | 18 | `as-known` 9, `survivorship` 7, `knowledge` 2 |
| `exact_publication` | 15 | **no keyword matched — all 15 by document id / catch-all** |
| `mode_admission` | 10 | `mode` 3, **7 by catch-all** |
| `temporal_identity` | 5 | `symbol` 2, `listing` 1, `calendar` 1, `status` 1 |
| `source_observation` | 4 | `source observation` 2, `provider outage` 2 |
| `operations` | 2 | `runtime` 1, `environment` 1 |
| `independent_oracle` | 1 | **1 by catch-all** |

**23 of 121 predicates reach a family with no semantic signal at all**, and the earlier branches
steal predicates from the later ones because the chain is ordered rather than exclusive. Twelve
predicates are filed under `evidence` without containing the word:

- `MD-S050-R0028` — *"As-known replay performs bitemporal resolution with `effective_at <= target context` and `recorded_at <= knowledge_cutoff`"* — an as-known predicate, captured by `pass`/`fail` inside surrounding text.
- `MD-S050-R0045` — *"Replay compares an output against itself under fixed inputs"* — an admissibility-boundary predicate.
- `MD-S002-R0005` — *"all anti-survivorship and as-known isolation fixtures passing"* — captured by `passing`.
- `MD-S050-R0039` — *"No volume of publication-replay `PASS` results substitutes for a single as-known fixture"* — captured by `PASS`.

`STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` §2 requires the bound proof to establish the
semantic predicate. A family chosen because the sentence happens to contain `pass` does not.

Every other closed stage in this package uses an explicit reviewed `rule_id => family` map
(`MD-B14` 147 entries, `MD-B17` 245 entries). `MD-B18` is the only stage using a heuristic.

## Defect 2 — 103 of 121 positive proofs are source-text greps that cannot detect their own defect

Eight of the nine families name `B18ReplayContractStaticGuardTest` as both positive and negative
proof. That class reads source files with `file_get_contents()` and asserts substrings. Counting by
predicate: **103 of 121 positive proofs are string-presence assertions; 18 execute behaviour.**

This was tested rather than asserted. `test_as_known_has_a_separate_cutoff_bound_execution_path`
requires the text `DB::rollBack()` to appear in `AsKnownReplayExecutionService`. The probe removed
the actual rollback call and left the identical text in a comment:

```
CONTROL  static guard : PASS
CONTROL  runtime guard: PASS

MUTATION applied: rollback call removed, the literal 'DB::rollBack()' still present

MUTANT   static guard : GREEN (ESCAPED)
MUTANT   runtime guard: RED (caught)

AFTER RESTORE static : PASS
AFTER RESTORE runtime: PASS
verdict=STATIC_GUARD_CANNOT_DETECT_THE_DEFECT
```

The isolated replay would have committed its projection mutation, and the guard that owns that
predicate stayed green. The probe target is byte-identical after restore.

A string can appear in a comment, a dead constant, a log line, or a docblock explaining why the
thing is *not* done. The `MD-B18` declaration already states the principle — *"static implementation
presence is insufficient"* — but the map binds 103 predicates to exactly that.

## Why the existing gates did not catch it

`MarketDataReplayVerificationProofReadinessGate` checks denominator size, map size, family use,
orphans and premature binding. `MarketDataReplayVerificationProofSelfTest` mutates the map. Neither
asks what a named guard *does*, and neither asks whether a family assignment was derived or fell
through. Both are correct about what they check and silent about this.

## Remediation required before any binding

1. Replace `familyFor()`'s heuristic with an explicit reviewed `rule_id => family` map covering all
   121 rows, and make an unmapped rule a hard failure instead of a catch-all.
2. Re-point each family's positive and negative proof at guards that execute the behaviour. The
   behavioural suite for this largely already exists and is not being used by the map:
   `AsKnownReplayBoundaryTest`, `AsKnownReplayExecutionServiceTest`, `ReplayVerificationServiceTest`,
   `ReplayModeContractTest`, `SourceObservationImmutabilityTest`,
   `SourceObservationAsKnownBoundaryTest`, `TemporalIdentityLayerContractTest`,
   `ReplayResultRepositoryIntegrationTest`, `ReplayEvidenceExportServiceTest`,
   `ReplayComparisonDetectsDivergenceTest`, `ReplayMismatchClassificationTest`,
   `ReplayAdmissibilityVerdictStorabilityTest`, `ReplayConfigIdentityVariesWithConfigTest`,
   `OpsCommandSurfaceTest`.
3. Add behavioural guards where a family has none, rather than retaining a grep for that family.
4. Extend the readiness gate so it fails on an unmapped rule and on a family whose named positive
   guard performs no execution, and prove both new assertions with mutations.

Static assertions may remain where the predicate really is structural — a migration declaring a
column, a schema mirror matching a migration — but they may not stand as the proof of a behavioural
predicate.

## Not done

No predicate is bound. No `MD-B18` runtime evidence is issued. The denominator is unchanged at 121;
this finding is about how proof is attributed, not about how many obligations exist. No strategy or
governance authority is touched, and no predicate text is reinterpreted to fit an existing guard.
