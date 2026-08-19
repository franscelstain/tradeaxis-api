# Weekly Swing Implementation — Strategy Alignment Required

## Status

Canonical Weekly Swing strategy has been revised around **qualified final Top Picks and D+1 actionability**. Existing implementation contracts, fixtures, examples, guidance, runtime code, and historical evidence may still represent pre-revision semantics.

This document is an implementation handoff marker. It is not evidence that code has been changed.

## Canonical Stage Mapping Requirement

Implementation alignment harus mengikuti authoritative lifecycle `WS-S00..WS-S11` di `../../authority/strategy/WS_END_TO_END_STRATEGY_LIFECYCLE.md`. Detailed execution order untuk programmer adalah `WS-B00..WS-B12` di [`WS_IMPLEMENTATION_BUILD_SEQUENCE.md`](WS_IMPLEMENTATION_BUILD_SEQUENCE.md). New contributors harus mulai dari [`../../START_HERE.md`](../../START_HERE.md).

Technical decomposition boleh berbeda, tetapi setiap implementation unit harus dapat ditelusuri ke stage input/output/exit condition yang relevan. Implementation tidak boleh:
- menjalankan scoring sebelum eligibility/classification selesai;
- membentuk final Top Picks sebelum PLAN immutable;
- memakai CONFIRM untuk membentuk atau mengubah recommendation;
- mengklaim proof/production readiness dengan melewati required IS/OOS/friction/core-shadow stage;
- membuat optional CONFIRM sebagai prerequisite untuk menyelesaikan core PLAN/RECOMMENDATION/Top Picks atau core proof.

## Canonical Strategy Changes to Translate

1. PLAN group `TOP_PICKS` and old PRIMARY/SECONDARY semantics are removed from canonical PLAN behavior.
2. Canonical PLAN states are `RECOMMENDATION_CANDIDATES`, `WATCH_ONLY`, `AVOID`.
3. `TOP_PICKS` is reserved for final qualified RECOMMENDATION only.
4. All active hard-gate/scoring features must be valid for recommendation candidacy; old missing-component zero-fill cannot keep a ticker recommendation-eligible.
5. `score_total` uses normalized weighted-sum semantics over momentum, breakout/setup, participation/volume, and risk quality; frozen transforms/weights are part of strategy identity.
6. Recommendation count is qualification-driven, not target/quota-driven.
7. `recommendation_score = PLAN score_total` for canonical baseline.
8. Capital/affordability must not change recommendation membership or rank.
9. CONFIRM applies only to final Top Picks on canonical D+1 entry window and is optional/non-blocking.
10. Core runtime completes at final Top Picks (`WS-S04`); missing CONFIRM data or missing CONFIRM artifact must not fail PLAN/RECOMMENDATION.
11. Canonical CONFIRM availability/actionability states distinguish `NOT_REQUESTED`, `UNAVAILABLE_RETRYABLE`, `ACTIONABLE`, `NOT_ACTIONABLE`, and `EXPIRED_UNCONFIRMED`.
12. `NOT_ACTIONABLE` requires valid evaluated decision-time data; missing/stale/incomplete/delayed data must not become a negative decision.
13. `UNAVAILABLE_RETRYABLE` may be reevaluated when valid data arrives within the canonical entry window.
14. Non-recommended candidate CONFIRM is no longer canonical product behavior.
15. Backtest/OOS must evaluate final recommendation output, not PLAN priority bucket.
16. Core production qualification requires realistic cost profile, non-zero slippage, adverse-friction stress, ranking-quality metrics, and core forward shadow proof; CONFIRM proof is capability-specific and does not block core production review.
17. No fixed/forced candidate or recommendation count is canonical.

## Known Impacted Technical Documentation

At minimum review/update before claiming implementation conformance:

- `contracts/WS_DATA_MODEL_MARIADB.md`
- `contracts/WS_PARAMSET_JSON_CONTRACT.md`
- `contracts/WS_PARAMETER_REGISTRY_COMPLETE.md`
- `contracts/WS_PARAMSET_VALIDATOR_SPEC.md`
- `contracts/WS_REASON_CODES_AND_HASH.md`
- `contracts/WS_RECOMMENDATION_INPUT_OUTPUT_CONTRACT.md`
- `contracts/WS_BACKTEST_EVALUATION_TECHNICAL_CONTRACT.md`
- `contracts/WS_BACKTEST_OOS_RUNTIME_IMPLEMENTATION_CONTRACT.md`
- `contracts/WS_BACKTEST_PERSISTENCE_AND_UNIVERSE_SCHEMA_CONTRACT.md`
- `guides/WS_IMPLEMENTATION_SCOPE_AND_BOUNDARY.md`
- `guides/WS_MODULE_MAPPING.md`
- `guides/WS_RUNTIME_ARTIFACT_FLOW.md`
- `guides/WS_API_GUIDANCE.md`
- `guides/WS_CANONICAL_FIELD_MATRIX.md`
- `guides/WS_PERSISTENCE_GUIDANCE.md`
- `guides/WS_TEST_IMPLEMENTATION_GUIDANCE.md`
- `guides/WS_DELIVERY_CHECKLIST.md`
- `guides/WS_IMPLEMENTATION_BLUEPRINT.md`
- `tests/WS_CONTRACT_TEST_CHECKLIST.md`
- `tests/WS_RECOMMENDATION_REASON_CODES_AND_TESTS.md`
- affected fixtures/examples/reference documents
- recommendation/backtest persistence schema and SQL where semantics depend on old names/behavior.

## Conformance Rule

Until code + technical contracts + tests are updated and evidenced, the correct state is:

**STRATEGY_REVISED_IMPLEMENTATION_ALIGNMENT_PENDING**

Historical runtime evidence remains historical and must not be rewritten to claim conformance with the revised strategy.


## Recurring Residue / Conformance Requirement

Alignment tidak selesai hanya karena path baru sudah bekerja. Setiap impacted technical/code surface wajib diperiksa terhadap [`../../authority/governance/IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md`](../../authority/governance/IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md).

Known examples yang harus dicari berdasarkan reachability/semantics, bukan nama saja:

- PLAN `TOP_PICKS`/PRIMARY/SECONDARY legacy branch;
- missing-feature zero-fill;
- capital-aware membership/rank;
- non-recommended CONFIRM;
- direct Market Data table/recompute fallback;
- legacy liquidity/volume semantic interpretation;
- old fixture/test/reason-code branches yang masih dapat membuat current behavior berbeda.

Alignment tetap pending bila harmful residue masih reachable.

## Current Stage Execution Governance

Any implementation alignment work must use:

- `../../authority/governance/STAGE_EXECUTION_AND_REWORK_STANDARD.md`;
- `WS_IMPLEMENTATION_STAGE_REGISTER.md`.

A failed alignment attempt does not close the stage. Rerun must read prior evidence/findings/decisions, record convergence, and keep remediation active while a credible resolution path remains.

## Canonical Rule Coverage Requirement

Alignment tidak dianggap selesai hanya karena technical docs/code sudah dipatch. Current strategy coverage harus dibuktikan melalui [`../../authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv`](../../authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv). Semua active mandatory/conditional rule harus berakhir `SATISFIED`; optional CONFIRM dapat `OPTIONAL_NOT_REQUESTED` jika capability tidak diminta.
