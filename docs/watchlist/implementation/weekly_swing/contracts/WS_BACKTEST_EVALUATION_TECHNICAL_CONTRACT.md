# Weekly Swing Backtest Evaluation Technical Contract

> **Doc Role:** IMPLEMENTATION CONTRACT
> **Strategy owner:** `../../../strategy/weekly_swing/12_WS_BACKTEST_AND_CALIBRATION_STRATEGY.md`
> **Acceptance owners:** `../../../strategy/weekly_swing/validation/16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md`, `../../../strategy/weekly_swing/validation/17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md`

Dokumen ini menampung technical translation yang dipindahkan dari canonical strategy pada cleanup 2026-08-16. Dokumen ini **tidak boleh mengubah behavior strategy**; bila terjadi konflik, strategy owner menang dan implementation harus diperbaiki.

## Physical Backtest Surfaces

Current implementation menggunakan surface berikut sesuai DDL/manifest aktif:
- `watchlist_bt_param_grid`;
- `watchlist_bt_eval`;
- `watchlist_bt_picks_ws`;
- `watchlist_bt_universe_ws`;
- `watchlist_bt_cutoffs_ws`;
- `watchlist_bt_oos_eval_ws` untuk OOS proof.

DDL owner: `../db/BACKTEST_SCHEMA_DDL.sql`.
Artifact owner: `../evidence_contracts/18_WS_BACKTEST_ARTIFACT_MANIFEST_LOCKED.md`.
Universe persistence detail: `WS_BACKTEST_PERSISTENCE_AND_UNIVERSE_SCHEMA_CONTRACT.md`.

## Implementation Reason Codes / Diagnostics

Technical reason identifiers untuk evaluation dapat mencakup:
- `BT_FALLBACK_ENTRY_PRICE`;
- `BT_SKIP_NO_TRADABLE_ENTRY`;
- `BT_SKIP_NO_TRADABLE_EXIT`;
- `BT_SKIP_NON_EXECUTABLE_PRICE_ENTRY`;
- `BT_SKIP_NON_EXECUTABLE_PRICE_EXIT`;
- `BT_SKIP_NOT_ENOUGH_NOTIONAL`;
- `BT_SKIP_MISSING_OHLC_ENTRY`;
- `BT_SKIP_MISSING_OHLC_EXIT`;
- `BT_AMBIGUOUS_HIT_STOP_PRIOR`;
- `WATCHLIST_BACKTEST_EMPTY_RECOMMENDATION_VALID`.

Identifier dapat diextend melalui reason-code contract, tetapi semantic outcome harus tetap sesuai canonical strategy.

## Runtime Markers / Serialization

Current technical markers yang menerjemahkan canonical evaluation semantics:

```text
tradable_bar_rule = POSITIVE_VOLUME_REQUIRED
min_tradable_volume = 1
price_fraction_rule = IDX_EQUITY_PRICE_BANDS
price_fraction_reference = THEORETICAL_LEVEL
price_normalization_rule = CONSERVATIVE_STOP_FLOOR_TARGET_CEIL
source_price_mode = RAW_TRADABLE_OHLC_REQUIRED
```

Artifact harus membedakan theoretical trigger price dari executed price dan menyimpan fill/gap diagnostic yang cukup untuk replay.

## Parameter-to-Persistence Coverage

Semua parameter dengan origin backtest yang memengaruhi evaluation harus:
- mempunyai deterministic grid/persistence mapping;
- mempunyai universe/picks/evaluation evidence;
- lulus implementation coverage verification.

Technical verification owner:
- `../verification/14_WS_BT_COVERAGE_MATRIX_LOCKED.md`;
- `../verification/15_WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md`.

Implementation coverage guard/test historically identified as `BT_COVERAGE_GUARD` must remain semantically equivalent: missing grid mapping, required evidence coverage, or contract coverage failure must block calibration validity. The identifier itself is implementation-level and may evolve only with synchronized tests/fixtures.

## Calibration Selection Query Semantics

Implementation boleh menggunakan SQL/repository query apa pun selama hasilnya sama dengan strategy acceptance + ranking order. Current relational projection ekuivalen dengan:

```sql
SELECT pg.*
FROM watchlist_bt_param_grid pg
JOIN watchlist_bt_eval ev
  ON ev.policy_code = pg.policy_code
 AND ev.param_id    = pg.param_id
WHERE pg.policy_code = 'WS'
  AND ev.picks_count >= :min_trades
  AND ev.days_covered >= :min_days_covered
  AND ev.avg_ret_net_top > 0
  AND ev.median_ret_net_top >= 0
  AND ev.p25_ret_net_top >= :min_p25_ret_net_top
  AND ev.month_win_rate_min >= :min_month_win_rate_min
  AND ev.month_avg_ret_net_min >= :min_month_avg_ret_net_min
ORDER BY
  ev.avg_ret_net_top DESC,
  ev.median_ret_net_top DESC,
  ev.month_win_rate_min DESC,
  ev.p25_ret_net_top DESC,
  ev.param_id ASC
LIMIT 1;
```

Threshold binding harus berasal dari canonical strategy/paramset representation. Placeholder/sentinel tidak boleh dipakai sebagai effective threshold.

## Metric Persistence Guidance

Current implementation may persist the canonical minimum metric names directly in `watchlist_bt_eval`. Derived metrics seperti `loss_rate_top = 1 - win_rate_top` tidak perlu dipersist bila dapat direcompute deterministically.

`days_covered` harus dihitung dari completed valid evaluation dates dan tidak boleh diisi langsung dari requested replay-date count.

Skipped/non-executable trade harus mempunyai null/no-return semantic, bukan synthetic zero return, dan tetap masuk diagnostic counters.

## Evaluation Model Serialization

Current minimum serialized evaluation-model format:

```text
ENTRY=ENTRY_RULE;EXIT=EXIT_RULE;HOLD=INT_DAYS;FEE=FEE_MODEL;SLIP=DECIMAL_PCT;GAP=GAP_RULE;PX=PRICE_RULE
```

Allowed baseline values:
- `ENTRY_RULE`: `NEXT_OPEN`;
- `EXIT_RULE`: `STOP_TP_OR_TIME`, `TIME_ONLY` (non-canonical variants must carry distinct identity);
- `FEE_MODEL`: `IDR_FIXED`, `IDR_TIERED`;
- `SLIP`: non-negative decimal string;
- `GAP_RULE`: `OPEN`;
- `PRICE_RULE`: `IDX_BANDS`.

Examples:
- `ENTRY=NEXT_OPEN;EXIT=STOP_TP_OR_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS`;
- `ENTRY=NEXT_OPEN;EXIT=TIME_ONLY;HOLD=5;FEE=IDR_TIERED;SLIP=0.001;GAP=OPEN;PX=IDX_BANDS`.

Current DDL must provide sufficient width for the full canonical string; current backtest schema uses `VARCHAR(96)`.

## Evidence / Artifact Binding

Physical evidence must persist enough information to bind:
- strategy/evaluation identity;
- paramset/grid identity;
- explicit from/to dates;
- IS/OOS binding;
- ordered-date/split identity;
- metric outputs;
- execution diagnostics needed for replay.

Exact artifact allowlist remains owned by `../evidence_contracts/18_WS_BACKTEST_ARTIFACT_MANIFEST_LOCKED.md`.

## Compatibility Rule

Refactor, query optimization, batching, storage changes, or reason-code changes are permitted only if they preserve canonical strategy output/evidence semantics. Any behavioral difference requires finding → evidence → decision → strategy revision, not silent implementation drift.
