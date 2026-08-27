# Change Impact Declaration — `MD-B07-A002`

- ID: `CI-MD-B07-A002-001`
- Stage / Attempt / Baseline / Epoch: `MD-B07` / `MD-B07-A002` / `MD-B07-A002-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260823-001`
- Stage precondition: `SC-MD-B07-A001-001`
- Dependencies: `MD-DEP-0004` stage-entry semantic normalization
- Status: `EXECUTED`
- Strategy meaning change: `NO`

## Objective

Correct a classification defect in the closed `MD-B07-A001` entry review: `MD-S066-R0002`
(`Mask tokens/cookies/keys.`) is an executable obligation that the A001 normalization never
examined, and it therefore carried no proof obligation. Bind it, and remove the mechanism that
allowed a stage-entry review to omit a row without failing.

## Why the prior classification was defective

`MD-S066` is a four-line LOCKED contract containing exactly two imperative sentences.
`MD-S066-R0001` (`Secrets via env vars, never in logs.`) was bound as a mandatory predicate with a
full proof chain. `MD-S066-R0002` was left `REFERENCE_ONLY` with empty notes. The two sentences are
indistinguishable in grammatical form, subject, and enforceability; no principled reading classifies
one as an obligation and the other as context.

The cause was structural, not a judgement call. `MarketDataSourceAcquisitionNormalization.php`
selected its own scope from `SOURCE_DOCUMENT_COUNTS` (`MD-S053`, `MD-S054`) plus a hand-curated
`EXTERNAL_RULES` list of eight rules. Any B07-owned row in neither list hit an unguarded `continue`
and was never examined. Those two lists covered 166 of the 167 rows the matrix assigns to `MD-B07`.
`MD-S066-R0002` was the one row they missed, and nothing in the pass could report that.

`STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` §8.4 requires proof-owning-stage assignment to be
confirmed *for the stage*. A pass that silently reviews a subset does not satisfy that, and its
denominator is not the stage's denominator.

## Authority and traceability scope

- Semantic owner of the corrected predicate: `MD-S066` Credentials and Secrets Contract.
- Reinforcing authority: `MD-S053` — *"Credential, API key, cookie rahasia, authorization header, dan
  sensitive query value tidak boleh masuk envelope atau diagnostic sample."*
- A001 denominator 115 mandatory + 52 reference; A002 denominator **116 mandatory + 51 reference**.
- No other row changes classification. The remaining 51 reference rows were re-checked against §2/§3
  and are correctly non-executable: 26 list introducers, 1 bare label, 6 capability-boundary
  disclaimers, 8 purpose/rationale/pointer statements, 9 `MD-S054` normalized-field fragments whose
  obligations are owned by the `REQUIRED` Mapping-rules siblings, and 1 permission granted to the
  promote phase.

## Impact assessment

- Strategy: no strategy byte change is authorised or expected.
- Schema/data: none. No migration, no table, no column, no stored row is altered.
- Configuration: none.
- Runtime/application: **none**. The masking behaviour this predicate requires is already implemented
  and already guarded. This attempt adds no application source change; it binds proof that existed
  and was unclaimed.
- Tests/gates: `MarketDataSourceAcquisitionNormalization.php` gains a completeness assertion that
  fails when any active row the matrix assigns to `MD-B07` is not examined by the pass. The B07
  traceability and proof gates become attempt-aware so an A002-bound predicate is verified against
  the A002 attempt/evidence pair rather than silently accepted or falsely rejected.
- Compatibility: `E-MD-B07-A001-001` is not edited and its 115 bindings are unchanged. Only the newly
  bound predicate carries the A002 pair.
- Residue/rework: the correction is confined to the credential-masking predicate and the normalization
  scope mechanism. No other stage's rows, records, or denominators are touched.
- Evidence: issue `E-MD-B07-A002-001` after actual execution.
- Dependencies/downstream: only the B07 portion of `MD-DEP-0004` is affected. No downstream stage is
  opened. The same scope-selection defect is expected in other stages' normalizations and is reported,
  not fixed here.

## Closure boundary

Closure requires the corrected predicate bound to executed proof at 116/116; the completeness
assertion demonstrated to fail on a control run; the masking guard demonstrated to fail when the
behaviour is removed; no harmful residue; current evidence; complete relationships; and all required
integrity/governance gates passing.

## Actual impact and result

- **Traceability**: `MD-S066-R0002` promoted `REFERENCE_ONLY` → `REQUIRED`/`MANDATORY`/`SATISFIED`.
  B07 denominator 115 → **116**, reference 52 → **51**. `b07_owned_examined` = **167 of 167**.
- **Proof binding**: bound to `E-MD-B07-A002-001`, family `observation`, covering two distinct
  surfaces — the stored observation envelope and the persisted acquisition checkpoint cache.
- **Guard proven falsifiable**: neutering `BackfillLifecycleOrchestrator::redactDiagnosticString()`
  turns `ApiBackfillLifecycleStaticGuardTest::test_source_acquisition_cache_is_slim_valid_json_and_sanitized`
  red. The obligation is enforced by a guard that can fail, not by an unexercised code path.
- **Completeness assertion proven falsifiable**: removing `MD-S066-R0002` from `EXTERNAL_RULES` makes
  the normalization abort with `B07-owned rows never examined by this normalization: MD-S066-R0002`.
  The defect this attempt corrects can no longer recur silently in this stage.
- **Application source changed**: **NO**. **Strategy changed**: **NO**. **Storage**: not inspected,
  not mutated.
