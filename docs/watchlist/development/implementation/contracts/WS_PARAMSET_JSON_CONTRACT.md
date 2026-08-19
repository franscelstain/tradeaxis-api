# 04 — Weekly Swing Paramset JSON Contract

> **Current Conformance:** `NOT_ASSESSED_REVALIDATION_REQUIRED`  
> **Verification Epoch:** `WS-REBASELINE-20260819-001`  
> Existing content is a revalidation input. Historical PASS/READY/DONE wording does not grant current conformance.


## Current Market Data Semantic Override

This technical document may retain legacy physical/parameter tokens for backward compatibility. Current Watchlist interpretation of producer fields is governed by `docs/watchlist/development/implementation/MARKET_DATA_INTAKE_IMPLEMENTATION_CONTRACT.md`, which delegates semantic ownership to the Market Data producer contracts:

- legacy `dv20_idr` / `*_dv20_idr` tokens apply only to Market Data canonical `adv20_close_volume_proxy_idr` (`RAW close × RAW volume` 20-session proxy); they MUST NOT be interpreted as `adv20_traded_value_idr_actual`;
- legacy serialized `vol_ratio` / `*_vol_ratio` tokens apply to canonical `vol_ratio_20` only when the selected Market Data read-model version declares exact semantic equivalence;
- direct Market Data table names, if preserved below as implementation history/debug context, are not current runtime intake authority;
- a future change from proxy liquidity to actual traded value, or to a different participation formula, is a strategy/proof identity change rather than a transparent field substitution.

Where wording below conflicts with this override or the canonical Weekly Swing strategy, this override + the canonical strategy wins until the implementation document is physically migrated.

## Purpose

Dokumen ini adalah owner normatif untuk shape JSON paramset Weekly Swing.

Implementation sync 2026-07-24: the active support example and validator fixtures now include the required `grouping.display_caps.value` object and use the canonical bootstrap evaluation floors `eval.min_trades.value = 120` and `eval.min_trades_oos.value = 40`. The example filename is retained for compatibility; persistence status is still DRAFT until the official promotion procedure passes.

## Scope

Dokumen ini mengunci:

- required top-level keys,
- nested section yang wajib ada,
- struktur audit object per parameter,
- unknown-key rule,
- dan hash-contract section yang wajib ada pada paramset aktif Weekly Swing.

## Ownership Rule

Dokumen ini adalah owner shape. Registry lengkap ada di `WS_PARAMETER_REGISTRY_COMPLETE.md`. Valid / invalid behavior ada di `WS_PARAMSET_VALIDATOR_SPEC.md`. Procedure promote / activate ada di `WS_CANONICAL_PARAMSET_PROCEDURES.md`.

## A. Required Top-Level Keys

Top-level keys yang wajib ada pada paramset Weekly Swing saat ini adalah:

- `policy_code`
- `policy_version`
- `schema_version`
- `paramset_code`
- `data_contract`
- `data_readiness`
- `liquidity`
- `risk`
- `setup`
- `scoring`
- `grouping`
- `plan_levels`
- `no_trade`
- `confirm_overlay`
- `eval`
- `hash_contract`
- `volume`

Top-level key di luar daftar ini dianggap unknown root key dan tidak termasuk kontrak aktif.

## B. Required Meta Keys

Field meta root berikut wajib ada:

- `policy_code` — canonical internal policy code saat ini: `WS`
- `policy_version` — canonical policy contract label untuk Weekly Swing paramset aktif; field ini bukan klaim aplikasi sudah memiliki release/versioning runtime
- `schema_version` — canonical schema contract label; fixture aktif saat ini memakai `PARAMSET_JSON`
- `paramset_code` — identifier paramset

Catatan interpretasi: nilai bootstrap aktif tidak memakai suffix seperti `_V1`. Jika fixture/file support lama memakai suffix tersebut pada `fixture_id` atau nama file, itu adalah identifier artefak contoh, bukan versioning aplikasi.

Untuk parity lintas runtime artifacts, `policy_code = WS` adalah canonical internal identifier. Runtime outputs dapat memakai `meta.policy = WEEKLY_SWING` sebagai runtime / display label untuk strategy yang sama.

## C. Required Section Keys

### `data_contract`
Wajib memiliki:
- `required_sources`
- `required_fields`
- `disabled_fields`

### `data_readiness`
Wajib memiliki:
- `min_coverage_ratio`
- `min_history_days`
- `max_missing_bar_days_60d`
- `reject_if_eod_incomplete`
- `outlier_ruleset`

### `liquidity`
Wajib memiliki:
- `min_dv20_idr`
- `dv20_strong_idr`
- `exclude_tickers`

Optional backward-compatible field untuk catalog C171 remediation:
- `max_dv20_idr`

### `volume`
Wajib memiliki:
- `min_vol_ratio`

Optional backward-compatible field untuk catalog C171 remediation:
- `max_vol_ratio`

### `risk`
Wajib memiliki:
- `min_atr14_pct`
- `max_atr14_pct`
- `atr_ideal_low`
- `atr_ideal_high`
- `stop_mode`
- `stop_atr_mult`
- `min_rr`

### `setup`
Wajib memiliki:
- `roc_lo`
- `roc_hi`
- `mom_roc20_soft_min`
- `bo_trigger_mode`
- `bo_near_below_pct`
- `bo_max_ext_pct`

### `scoring`
Wajib memiliki:
- `combine_mode`
- `weights`

`scoring.weights` wajib memiliki child key berikut pada `value`:
- `momentum`
- `breakout`
- `volume`
- `risk`

### `grouping`
Wajib memiliki:
- `secondary_target`
- `top_picks_target`
- `secondary_min_score_q`
- `top_min_score_q`
- `grouping_mode`
- `sort_keys`
- `rounding_mode`
- `min_count_overrides`
- `display_caps`

Optional backward-compatible field untuk catalog C171 remediation:
- `top_max_score_total`

### `plan_levels`
Wajib memiliki:
- `entry_mode`
- `entry_band_pct`

### `no_trade`
Wajib memiliki:
- `min_eligible_count`
- `no_trade_hides_all`

### `confirm_overlay`
Wajib memiliki:
- `snapshot_max_age_sec`
- `max_drift_from_entry_pct`

### `eval`
Wajib memiliki:
- `min_trades_oos`
- `min_trades`
- `min_days_covered`
- `min_p25_ret_net_top`
- `min_month_win_rate_min`
- `min_month_avg_ret_net_min`

Runtime backtest yang menghasilkan artifact evaluasi wajib membawa section `eval` ini ke `paramset_snapshot`. Threshold tidak boleh hilang di antara active/bootstrap paramset, strategy replay, metrics, dan artifact export. Bila salah satu threshold evaluasi wajib tidak dapat di-resolve, runtime proof harus fail closed dengan reason code evaluasi yang canonical dan tidak boleh menulis artifact PASS. Canonical bootstrap saat ini memakai `min_trades = 120` dan `min_trades_oos = 40`. `min_days_covered.value = 0` adalah sentinel dinamis, bukan floor nol; runtime wajib menulis nilai efektif `ceil(0.70 * total_trading_days_in_window)` pada metric sufficiency artifact.

### `hash_contract`
Wajib memiliki:
- `version`
- `order_by`
- `scales`
- `null_handling`

`hash_contract.scales.value` wajib memiliki:
- `close_price_dp`
- `hh20_dp`
- `roc20_dp`
- `atr14_pct_dp`
- `dv20_idr_dp`

## D. Audit Object Contract

Setiap parameter leaf pada section Weekly Swing menggunakan audit object dengan field wajib berikut:

- `value`
- `origin`
- `status`
- `bt_target`
- `rationale`
- `change_triggers`

Ketiadaan salah satu field audit wajib tersebut adalah contract violation.

## E. Type Contract

Aturan tipe minimal yang aktif dari fixtures saat ini:

- `value` pada threshold numerik harus bertipe numerik, bukan string numerik.
- `change_triggers` harus array.
- `required_sources`, `required_fields.value`, `exclude_tickers`, dan `grouping.sort_keys.value` harus array.
- `outlier_ruleset.value` dan `hash_contract.scales.value` harus object.

## F. Enum / Fixed-Value Contract

Nilai yang saat ini terkunci secara kontraktual dari fixtures aktif dan artifacts terkait mencakup setidaknya:

- `policy_code = WS`
- `schema_version = PARAMSET_JSON`
- `hash_contract.order_by.value = ticker_id_asc`
- `hash_contract.null_handling.value = EXCLUDE_FROM_HASH_PAYLOAD`
- `grouping.sort_keys.value = [score_total_desc, score_breakout_desc, score_momentum_desc, dv20_idr_desc, atr14_pct_asc, ticker_id_asc]`

Nilai enum / fixed lain yang dipakai runtime harus konsisten dengan registry dan validator spec.

## G. Unknown-Key Rule

Unknown root key dilarang. Fixture `paramset_unknown_key.json` merepresentasikan pelanggaran kontrak ini.

Unknown key pada nested structures juga dilarang bila tidak terdaftar di contract owner / registry canonical Weekly Swing.

## H. Supporting Artifact Mapping

Shape kontrak ini dicerminkan oleh:

- `db/PARAMSET_WS_ACTIVE_EXAMPLE.json`
- `fixtures/paramset_valid.json`
- seluruh fixture negatif `paramset_*`

Artifacts tersebut tidak boleh lebih authoritative daripada dokumen ini.

## Final Contract Rule

Jika sebuah key, nested structure, atau audit field tidak dapat ditelusuri ke kontrak yang ditetapkan di dokumen ini, key atau structure tersebut tidak boleh dianggap bagian dari paramset contract Weekly Swing.

## I. Backtest Catalog Projection Envelope

Backtest calibration may wrap the canonical runtime paramset with two audit-only sections:

- `bt_catalog`: `catalog_code`, `catalog_version`, `catalog_hash`, `row_code`, `row_hash`, and rationale;
- `bt_grid`: the exact immutable catalog payload; generated database surrogate `param_id` is excluded and remains in evaluation/binding identity.

Rules:

1. The wrapper does not mutate the production paramset contract or imply ACTIVE/promotion status.
2. Every explicit R2 catalog field must be projected into its canonical nested runtime key before replay.
3. An explicit catalog value may not be replaced by a runtime default.
4. The catalog metadata and projected paramset are included in the deterministic paramset hash; the hash must not depend on a database-generated surrogate `param_id`.
5. `risk.stop_atr_mult`, `risk.min_rr`, `grouping.top_picks_target=5`, `grouping.secondary_target=10`, fees, slippage, gap rule, price normalization, and holding horizon are fixed for the R2 entry-quality catalog.

## Campaign-specific references
C171-specific remediation bounds were moved to `../../research/campaign_contracts/WS_C171_PARAMSET_OPTIONAL_BOUND_CONTRACT.md`. They are not part of the canonical generic paramset contract.
