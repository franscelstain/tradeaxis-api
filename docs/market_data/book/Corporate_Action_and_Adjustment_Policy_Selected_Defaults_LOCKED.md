# Corporate Action and Adjustment Policy — Selected Defaults (LOCKED)

## Status

STRATEGY LOCKED / IMPLEMENTATION AND LONG-CHAIN PROOF REQUIRED.

## Selected analytical defaults

- Weekly Swing technical-indicator price basis: `STRUCTURAL_ADJUSTED`
- Canonical market-observation basis: `RAW`
- Performance/distribution evaluation basis: separately requested `TOTAL_RETURN` when supported
- Per-row or per-date fallback between price bases: **forbidden**
- Provider `adj_close` as analytical default/fallback: **forbidden**

## One-basis-per-run rule (LOCKED)

Every indicator/analytical run binds one explicit price-product identity, product version, factor-set hash/reference, formula/indicator-set version, and configuration snapshot.

If required verified factors are missing or conflicting, the affected vector/fields remain `NULL`/invalid and eligibility is blocked with explicit reasons. The run must not fall back to `RAW`, provider `adj_close`, or a mixed vector to manufacture continuity.

## Coherence rule

For `STRUCTURAL_ADJUSTED`:

- open, high, low, and close use the same cumulative verified price factors
- previous close used by true range is on the same basis
- volume uses the verified action-specific inverse/unit factor when semantics require it
- structural factors are applied consistently across the full dependency window
- raw bars remain unchanged

ATR/TR, HH/LL, moving averages, ROC, and any combined metric must consume the coherent vector. Adjusting close alone is forbidden.

## Version/change rule

Changing price basis, factor selection, factor revision, precision rule, or fallback behavior is output-affecting and requires:

- new product/formula/config version
- new hashes
- recomputation of every affected date from the stable dependency boundary
- new publication/correction lineage where prior output was sealed
- long-chain deterministic oracle proof

## Consumer boundary

Publications expose price-basis identity and quality/contamination state. Consumers must not infer basis from column names or reconstruct adjustments ad hoc.

## Acceptance criterion (LOCKED)

No vector mixes scales across dates or fields; missing verification fails safe; identical raw publication plus factor/config/formula versions reproduce identical analytical output.

## Cross-contract alignment

- `Corporate_Action_and_Adjustment_Policy.md`
- `../registry/Price_Adjustment_Contract_LOCKED.md`
- `EOD_Indicators_Contract.md`
- `../indicators/EOD_Indicators_Formula_Spec.md`
