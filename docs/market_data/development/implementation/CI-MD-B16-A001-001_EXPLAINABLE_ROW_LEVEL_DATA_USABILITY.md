# Change Impact Declaration — `MD-B16-A001`

- ID: `CI-MD-B16-A001-001`
- Stage / Attempt / Baseline / Epoch: `MD-B16` / `MD-B16-A001` / `MD-B16-A001-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260823-001`
- Stage precondition: `SC-MD-B15-A001-001`
- Dependencies: `MD-DEP-0004` stage-entry semantic normalization
- Status: `IN_PROGRESS`
- Strategy meaning change: `NO`

## Objective

Open `MD-B16` — explainable row-level data usability — and prove its 75 mandatory predicates
against current authority.

## 1. Affected strategy IDs and rules

Seven documents, 102 active rows. The owner contracts:

| Document | Owner | Rows |
|---|---|---|
| `MD-S027` | EOD Eligibility Facts Snapshot Contract (LOCKED) | 68 |
| `MD-S031` | Eligibility Partial and Degraded Data Behavior (LOCKED) | 26 |
| `MD-S039` | Invalid Bar Storage Policy (LOCKED) | 1 |
| `MD-S058`, `MD-S019`, `MD-S041`, `MD-S085` | supporting invariants | 7 |

Stage-entry normalization resolved every transitional row before this declaration was issued:
**75 mandatory**, 2 optional-capability, 25 reference. Zero transitional applicability, zero
conditional-pending, zero mixed-classification debt, zero unexplained reference.

The Stage Register carried `0/28`. The real denominator is 75, because 41 predicates arrived filed
as reference context or as mixed-run siblings while carrying obligations: the liquidity and
status-and-event dimension lists, the decision-and-explanation fields, the prohibition on
reconstructing a dimension from an overloaded reason code, the registry-only reason rule, the
degraded-behaviour cases, and the run-level distinction.

## 2. Affected areas

- **Runtime behaviour**: `EodEligibilityBuildService`, `EligibilityDecisionService`,
  `ExpectedBarDecisionService`, `MarketDataPipelineService`. Inspect per-row dimension persistence,
  reason-set retention and ordering, the fail-safe absence rule, and the prohibition on liquidity or
  dormancy influencing usability.
- **Schema / migration**: the required dimension list is load-bearing rather than descriptive. See
  the confirmed gap below; remediation is additive.
- **Evidence / proof mechanics**: issue new `MD-B16-A001` governed evidence after actual execution.
- **Replay / backfill**: eligibility rows and reason sets are frozen with the publication and replay
  must reproduce the complete fact and reason set.
- **Operator / ops behaviour**: none directly.
- **Provider / source behaviour**: none directly. Invalid provider rows stay out of canonical
  `eod_bars` and contribute to eligibility reasoning only.
- **Tests / gates / generators**: build the `MD-B16` proof spec, gate, binder, self-test and closure
  gate; add guards for the obligations found unguarded.

## Confirmed executable gap at declaration time

`MD-S027` enumerates the fact dimensions every eligibility row must persist **separately**, and
states the consequence of not doing so in unusually direct terms:

> Absence of the first-class facts is a **defect against this contract**, never a licence to
> overload `reason_code`.

> Until the required fields exist, the snapshot is **not conformant** and any claim of
> explainability made on its behalf must say so explicitly.

Seven dimensions are persisted today: universe membership, bar expectation, delivery, canonical
quality, liquidity, temporal status, and event risk, plus the ordered reason set in
`eligibility_reasons_json`. **Three required dimensions are absent from the row entirely:**

- **`source/provenance state`** (`MD-S027-R0009`). The bar carries `source` and
  `source_observation_id`; the eligibility row carries neither, so a consumer cannot see whether a
  delivered observation was traceable to accepted source evidence without reading the bar table —
  which the acceptance criterion forbids ("without ... reading internal tables").
- **`analytical price-basis and contamination state`** (`MD-S027-R0012`). The indicator row carries
  `price_product_code` and `corporate_action_window_reasons`; the eligibility row carries only
  `event_risk_state`, derived from a single indicator flag. A contaminated window and a clean one
  under an unexpected price basis are indistinguishable on the eligibility row.
- **`indicator validity and warm-up/nullability state`** (`MD-S027-R0013`, `MD-S031-R0006`,
  `MD-S031-R0007`). The indicator row carries `is_valid`, `invalid_reason_code` and — since
  `MD-B14-A001` — the per-field `null_reasons_json`. The eligibility row carries none of it, so
  "affected indicators `NULL`, warm-up state and reasons explicit" is not satisfied on the row that
  is supposed to explain the instrument.

All three are derivable from data the build service already holds in memory: `loadBarsForTradeDate`
and `loadIndicatorsForTradeDate` both return the whole row. Nothing needs to be recomputed or
acquired; the row simply does not carry what the contract requires it to carry. Remediation is
additive — three columns and their population, reaching the current table, the history table, the
SQLite mirror, the pipeline column list and the artifact repository.

## 3. Raw-artifact storage, path, manifest, hash and retention mechanics

Proof is executed locally and its material output is a test transcript. Where a selected proof
depends on material output external to docs, the governed evidence binds execution identity,
artifact or manifest path, and hash per `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md` §6
before that proof supports closure. Storage is not scanned as a resume step.

## 4. Compatibility risk

Preserve every closed predecessor boundary. Specifically: `eligible` keeps its upstream-only
meaning and never becomes ranking, tradability or preference; liquidity and dormancy never change
the coverage denominator and never make otherwise valid data unusable; coverage `PASS` never
overrides a quality or eligibility blocker; and the `0.98` coverage threshold bound in `MD-B15` is
untouched. The three new columns are additive and nullable; no issued publication is rewritten.

## 5. Residue and rework risk

Search scope is the eligibility build, decision service, expectation resolution, publication
projection and replay comparison. The specific residues to look for: a dimension inferred from
`reason_code` rather than persisted, an `eligible=false` row with an empty reason set, an
`eligible=true` row carrying an unresolved blocking reason, a missing fact defaulting to usable
rather than failing safe, and a liquidity observation reaching the usability decision.

## 6. Affected dependencies and relationships

`MD-DEP-0004` is discharged for `MD-B16` by the stage-entry normalization recorded above. No open
finding blocks this stage. `F-MD-B01-A014-001` remains open and is owned by `MD-B19`;
`F-MD-B14-A001-001` remains open and is a reason-code vocabulary matter outside this stage.

## 7. Strategy meaning change

**NO.** No strategy byte is changed. The dimension list is already in the frozen contract; this
attempt makes the implementation carry what the contract already requires.

## Closure boundary

Closure requires the conditions in `STAGE_CLOSURE_MANIFEST_STANDARD.md`, positive and fail-closed
proof for every mandatory predicate, no harmful residue, current evidence, complete relationships,
and all integrity gates passing.

## Actual impact and result

- **Stage-entry normalization**: complete and recorded before this declaration. 102 rows examined,
  75 mandatory, zero transitional, zero pending, zero unexplained reference, zero foreign rows
  altered.
- **Remaining work**: the three-dimension remediation above, the `MD-B16` proof surface, and the
  proof of all 75 predicates. Not yet claimed.
