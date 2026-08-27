# Change Impact Declaration — `MD-B00-A004`

- ID: `CI-MD-B00-A004-001`
- Stage / Attempt / Baseline / Epoch: `MD-B00` / `MD-B00-A004` / `MD-B00-A004-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260823-001`
- Stage precondition: `SC-MD-B00-A003-001`
- Dependencies: `MD-DEP-0004` stage-entry semantic normalization
- Status: `EXECUTED`
- Strategy meaning change: `NO`

## Objective

Close the blind spot that let two executable obligations sit as `REFERENCE_ONLY` through every green
gate, and stop the per-stage hand search from being the only way to find the third.

## The blind spot

`MIXED_RUN` fires only when a contiguous enumerated run inside one document and section holds both
`REQUIRED` and `REFERENCE_ONLY` members. Two shapes escape it entirely:

1. a **uniformly** reference run — nothing to mix;
2. a **paragraph** — `detectRuns` requires a list marker, so a standalone sentence is never grouped
   into a run at all.

Both corrected defects were the second shape. `MD-S066-R0002` (`Mask tokens/cookies/keys.`) and
`MD-S067-R0010` (`All reason codes are retained. A primary reason is routing compatibility only.`)
are standalone paragraphs stating executable obligations. Each sat `REFERENCE_ONLY` with empty notes
across two attempts, and each was found only by reading the contract by hand in `MD-B07-A002` and
`MD-B08-A002`.

## What this attempt does not do

It does not teach the gate to recognise an obligation. The gate's own contract forbids that:

> Grammatical mood is deliberately absent. Encoding "has a modal" here would rebuild the defect this
> gate exists to detect.

The deontic regex used during the audit stays out of the gate. What is enforceable without judging
meaning is whether a decision was **made and attributed**, which is what
`STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` §8.4 requires and what both defects lacked.

## Scope

- `UNEXPLAINED_REFERENCE` — in a stage that has completed its reference-population re-check, every
  active non-structural `REFERENCE_ONLY` row must record what it was normalized to and which stage
  confirmed ownership. Governed by a second allowlist, `DECISION_RECORDED_STAGES`, on the same
  principle as `NORMALIZED_STAGES`: a stage joins when its re-check lands. Stages outside it are
  counted and reported, never excused.
- `BINDING_COHERENCE` — a `SATISFIED` row carries evidence and an evidence-carrying row is
  `SATISFIED`. Half-cleared proof state is not a legible outcome.
- `MIN_REFERENCE_ROWS` floor, so a reference scan that matches nothing cannot look like a clean
  corpus.
- `MD-B07` and `MD-B08` normalizations now **record** the reference decision instead of erasing it,
  which is what makes those two stages eligible for the new allowlist.

## Impact assessment

- Strategy / schema / configuration / application source: **no change**.
- Storage: not inspected, not mutated.
- Tests/gates: three new fail-closed cases in the gate self-test, each asserted to land before the
  gate is judged.
- Compatibility: no issued evidence is edited; no stage denominator changes.

## Closure boundary

Closure requires both new checks demonstrated to fail when removed, the allowlisted stages green,
the remaining backlog reported rather than suppressed, and all governance gates plus the full suite
passing.

## Actual impact and result

- **Gate result**: `PASS`. `reference_non_structural` 1819, `reference_decision_recorded` 175,
  `reference_unexplained` 1644, `binding_incoherent` 0.
- **Allowlist**: `MD-B00`, `MD-B05`, `MD-B07`, `MD-B08`, `MD-B10`, `MD-B13` — all clean.
- **Backlog now machine-visible**, where it was previously invisible: `MD-B01` 160, `MD-B02` 17,
  `MD-B04` 28, `MD-B06` 1, `MD-B09` 5, `MD-B11` 201, `MD-B12` 54, `MD-B14` 198, `MD-B15` 131,
  `MD-B16` 53, `MD-B17` 140, `MD-B18` 79, `MD-B19` 538, `MD-B20` 39. Total **1644**.
- **Falsifiability**: disabling the two new checks turns exactly the three new self-tests red;
  restoring them returns 12/12.
- **Regression test**: `MD-S066-R0002` restored to its pre-`MD-B07-A002` state inside the test now
  produces `UNEXPLAINED_REFERENCE`. The defect that started this cannot recur silently.
- **Honest limit, recorded in the gate docblock**: a generated note proves a pass ran, not that a
  human thought. This catches the row nobody looked at, not the row somebody looked at and misjudged.
  `MD-S067-R0010` was the second kind and would still have needed a human reading the contract.
