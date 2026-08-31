# Change Impact Declaration — `MD-B15-A001`

- ID: `CI-MD-B15-A001-001`
- Stage / Attempt / Baseline / Epoch: `MD-B15` / `MD-B15-A001` / `MD-B15-A001-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260823-001`
- Stage precondition: `SC-MD-B14-A001-001`
- Dependencies: `MD-DEP-0004` stage-entry semantic normalization
- Status: `IN_PROGRESS`
- Strategy meaning change: `NO`

## Objective

Open `MD-B15` — the temporal coverage expectation and delivery gate — and prove its 221 mandatory
predicates against current authority.

## 1. Affected strategy IDs and rules

Fourteen documents, 356 active rows. The four owner contracts:

| Document | Owner | Rows |
|---|---|---|
| `MD-S015` | Coverage Gate Enforcement Contract (LOCKED) | 113 |
| `MD-S014` | Coverage Edge Cases Contract (LOCKED) | 94 |
| `MD-S024` | EOD Coverage Delivery Gate Contract (LOCKED) | 72 |
| `MD-S016` | Coverage Universe and Bar Expectation Definition (LOCKED) | 42 |
| `MD-S029`, `MD-S053`, `MD-S041`, `MD-S040`, `MD-S058`, `MD-S001`, `MD-S023`, `MD-S085`, `MD-S020`, `MD-S057` | supporting invariants | 35 |

Stage-entry normalization resolved every transitional row before this declaration was issued:
**221 mandatory**, 2 optional-capability, 133 reference. Zero transitional applicability, zero
conditional-pending, zero mixed-classification debt, zero unexplained reference.

The denominator is far above the `0/89` the Stage Register carried, because 108 of the 221 came from
rows filed as reference or as mixed-run siblings. Those included the forbidden expectation-exclusion
list, the numerator exclusions, the three gate-state definitions, the reason-code mapping, the
required audit-visible fields and the forbidden fallback targets. A coverage rule filed as context
is still a coverage rule.

## 2. Affected areas

- **Runtime behaviour**: `CoverageGateEvaluator`, `CoverageGateStateNormalizer`,
  `ExpectedBarDecisionService`, `FinalizeDecisionService`, `MarketDataPipelineService`,
  `EodBarsIngestService`. Inspect denominator construction, `NOT_EXPECTED` exclusion, `UNKNOWN`
  fail-safe inclusion, delivery-versus-canonical-valid separation, threshold comparison, and the
  three gate states.
- **Evidence / proof mechanics**: coverage evidence must bind the version of every component its
  correctness depends on, not only its own contract version. See the confirmed gap below.
- **Schema / migration**: inspect whether the coverage evidence surface needs additional persisted
  identity columns. Any gap is remediated additively; no issued publication is rewritten.
- **Configuration**: `market_data.coverage_gate.*` and the legacy `market_data.platform.coverage_min`
  alias must resolve to the same locked `0.98` default.
- **Replay**: the replay-comparable coverage field list in `MD-S015` must be complete and compared.
- **Operator / ops behaviour**: command output must render coverage gate state, reason code and
  summary whenever coverage telemetry exists.
- **Provider / source behaviour**: none directly. Retry, rate limit and timeout are source-acquisition
  concerns that must not become coverage bypasses.
- **Tests / gates / generators**: build the `MD-B15` proof spec, proof gate, binder, self-test and
  closure gate; add guards for the obligations found unguarded.

## Confirmed executable gap at declaration time

`MD-S024-R0045` and `MD-S024-R0046` require every coverage evidence record to bind the
identity/universe resolver version and the calendar and trading-status revision identities used to
resolve expectation. `MD-S024-R0052` generalises it: *evidence binds the version of every component
its correctness depends on, not only the version of the contract that produced it.*

The evaluator publishes `coverage_contract_version`, `coverage_calibration_version` and
`universe_hash_schema_version`, and nothing else that identifies a component:

- **no universe resolver version.** The universe is resolved from `TickerMasterRepository` under a
  `coverage_universe_basis` config string. The string names the basis, not the implementation that
  applied it, so two records produced by different resolver behaviour are indistinguishable.
- **no calendar revision identity.** `MarketCalendarRepository::sessionContext()` already returns
  `calendar_revision_id` and `revision_uid`, and `ExpectedBarDecisionService` already carries them.
  `CoverageGateEvaluator` does not take the calendar repository at all.
- **no trading-status revision identity.** `EventRiskSourceRepository` resolves the suspension state
  that produces every `NOT_EXPECTED` exclusion but exposes no revision identity for it.

This is the exact condition `MD-S024` describes: a stored coverage result cannot be distinguished
from one produced by a resolver since found defective. Remediation is additive — bind identities the
platform already computes, and publish a resolver version for the one it does not.

## 3. Raw-artifact storage, path, manifest, hash and retention mechanics

Coverage proof is executed locally and its material output is a test transcript. Where a selected
proof depends on material output external to docs, the governed evidence must bind execution
identity, artifact or manifest path, and hash per
`RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md` §6 before that proof supports closure. Storage
is not scanned as a resume step; only artifacts the selected proof requires are inspected.

## 4. Compatibility risk

Preserve the closed boundaries of every predecessor stage. Specifically: the `0.98` threshold is
locked and may not move; `BLOCKED` may not be reintroduced as a coverage gate state; the denominator
may not shrink for dormancy, zero volume, illiquidity, provider absence or current activity; and
`coverage_available_count` may not be substituted for `coverage_delivered_count`. Reject any change
that makes a failing or non-evaluable candidate readable.

## 5. Residue and rework risk

Search scope is the coverage evaluation, expectation resolution, finalize decision, publishability
and pointer surface, evidence export, and replay comparison. The specific residues to look for: a
numerator that counts canonical-valid rows instead of delivered observations, an `UNKNOWN` silently
folded into `NOT_EXPECTED`, a zero denominator coerced to a ratio, a legacy `BLOCKED` reaching a
coverage field, and a fallback path that converts a failed candidate into a readable one.

## 6. Affected dependencies and relationships

`MD-DEP-0004` is discharged for `MD-B15` by the stage-entry normalization recorded above. No open
finding blocks this stage. `F-MD-B01-A014-001` remains open and is owned by `MD-B19`;
`F-MD-B14-A001-001` remains open and is a reason-code vocabulary matter outside this stage.

## 7. Strategy meaning change

**NO.** No strategy byte is changed. The coverage contracts are in the strategy freeze and verified
byte-for-byte. The remediation binds identities into implementation-owned evidence, which is where
the contracts already place the obligation.

## Closure boundary

Closure requires the conditions in `STAGE_CLOSURE_MANIFEST_STANDARD.md`, positive and fail-closed
proof for every mandatory predicate, no harmful residue, current evidence, complete relationships,
and all integrity gates passing.

## Actual impact and result

- **Stage-entry normalization**: complete and recorded before this declaration. 356 rows examined,
  221 mandatory, zero transitional, zero pending, zero unexplained reference, zero foreign rows
  altered. The pass asserts full-scope coverage, so a row it never examined is fatal rather than
  silent. It also back-fills the parent/context binding on rows carried in as required — the defect
  that reached the `MD-B14` denominator as `MD-S023-R0063` and was only caught at closure.
- **Remaining work**: the evidence-identity remediation above, the `MD-B15` proof surface, and the
  proof of all 221 predicates. Not yet claimed.
