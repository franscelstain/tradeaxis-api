# Legacy Role Extract — WS — IMPLEMENTATION

> **Document Type:** IMPLEMENTATION
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0550-IMP-03`
> **Legacy Source ID:** `LS-WS-0550`
> **Legacy Work Key:** `WS`
> **Original Path:** `docs/watchlist/system/policies/weekly_swing/12_WS_BACKTEST_SCHEMA_AND_CALIBRATION.md`
> **Original SHA1:** `F36B415CA47D448CF0C5EA5AEDE987D497FFEF42`
> **Source Sections:** L324-L326 DDL; L372-L396 Schema: watchlist_bt_universe_ws (AUDIT) (LOCKED); L397-L459 C171 Real-IS Remediation Catalog and DRAFT-Only Persistence (LOCKED); L1620-L1641 C171 C01 tick-risk guard adapter enforcement addendum
> **Extract Body SHA1:** `44B8F49F5A6AA034C30FCE3B6A3E7F609F62CA21`
> **Current Authority:** NO

The body below is an exact copy of the identified original sections. No semantic rewriting was applied.

---

## DDL
Schema backtest (DDL) disimpan sebagai artefak di: [`db/BACKTEST_SCHEMA_DDL.sql`](db/BACKTEST_SCHEMA_DDL.sql).

## Schema: watchlist_bt_universe_ws (AUDIT) (LOCKED)

Tabel ini **wajib** ada untuk membuat backtest reproducible dan audit-friendly.

Lokasi DDL: [`db/BACKTEST_SCHEMA_DDL.sql`](db/BACKTEST_SCHEMA_DDL.sql)

Kolom (harus match DDL):
- `asof_eod_date` (DATE, NOT NULL) — tanggal EOD universe
- `ticker_id` (INT, NOT NULL) — id ticker
- `required_ok` (TINYINT(1), NOT NULL) — data-quality only (bukan eligibility final)
- `reason_code` (VARCHAR(32), NULL) — reason WS_* (contoh: `WS_DATA_MISSING`) saat `required_ok=0`
- `missing_fields` — daftar field required yang missing/invalid (CSV string)
- `guard_ok` — 1 jika lolos semua guardrail, 0 jika tidak
- `eligible_ok` — 1 jika required_ok=1 dan guard_ok=1
- `dv20_idr`, `atr14_pct`, `vol_ratio` — metric snapshot untuk debug equivalence
- `vol_ratio` disimpan sebagai `DECIMAL(20,6)` agar snapshot audit tidak overflow pada rasio historis ekstrem ketika denominator volume sangat kecil; nilai tidak boleh di-clamp hanya agar muat ke schema.

Primary key:
- `(asof_eod_date, ticker_id)`

Indexes:
- `idx_bt_univ_ws_req (asof_eod_date, required_ok)`
- `idx_bt_univ_ws_reason (asof_eod_date, reason_code)`
- `idx_bt_univ_ws_elig (asof_eod_date, eligible_ok)`

## C171 Real-IS Remediation Catalog and DRAFT-Only Persistence (LOCKED)

The immutable failed official baseline remains:

```text
eval_id=188
param_set_id=1
params_hash=b7f3c207b989c55c93f8f61b1fcceea2c343a151
canonical_is_gates_pass=0
```

It may not be edited or deleted. Diagnostic evidence permits one finite,
decision-time remediation catalog only:

```text
catalog_code=WS_BT_GRID_REAL_IS_REMEDIATION_C171_R1_2026_07
catalog_version=C171-R1
catalog_count=5
catalog_hash=82b0fcbf17823fda5ab59bd2dba3d947b4f9e233
```

The official param-grid schema is extended with nullable columns:

```text
max_dv20_idr BIGINT UNSIGNED NULL
max_vol_ratio DECIMAL(20,6) NULL
top_max_score_total DECIMAL(10,6) NULL
```

Nullable preserves immutable legacy catalog rows. Every C171-R1 row must provide
all three values and pass exact catalog version/count/hash validation. A seed is
idempotent only when `(policy_code, catalog_code, row_code)` and the complete
persisted payload are identical. A changed row fails closed.

DRAFT persistence requirements:

- source identity is exactly `eval_id=188`, `param_set_id=1`, and the baseline
  hash above;
- completed diagnostic artifact and candidate-design artifact hashes/file SHA1s
  must match the locked implementation constants;
- exactly five canonical JSON payloads are validated, bound to exact `param_id`,
  and imported as new immutable DRAFTs;
- rerun is idempotent and must not edit an existing DRAFT payload/hash;
- persistence stage does not invoke official IS, OOS, promotion, PLAN,
  recommendation, CONFIRM, activation, rollout, or publication;
- each unchanged DRAFT must later receive a separate official IS run on
  `2023-01-02` through `2025-05-21`;
- failed candidate evaluations remain evidence; no best-failed fallback;
- C172/OOS remains forbidden until one candidate passes every unchanged
  canonical IS gate.

Decision-time semantics:

```text
dv20_idr > max_dv20_idr          => WS_LIQ_HIGH
vol_ratio > max_vol_ratio         => WS_VOLR_HIGH
score_total > top_max_score_total => forbidden from TOP qualified pool
```

The TOP cutoff uses the capped TOP score pool. This is not post-return filtering
and may not inspect D+1..D+5 outcomes or OOS evidence.

## C171 C01 tick-risk guard adapter enforcement addendum

The V2 evidence-propagation audit correctly failed closed and did not persist a corrected evaluation because the scoring-layer paramset adapter omitted candidate-universe-only guard fields. The scoring adapter must preserve, at minimum:

```text
liquidity.max_dv20_idr
volume.max_vol_ratio
risk.stop_atr_mult
risk.min_rr
risk.max_signal_tick_risk_expansion_pct
```

Current corrected identity:

```text
EVIDENCE_PIPELINE_VERSION=WS_C171_C01_TICK_RISK_GUARD_ENFORCEMENT_PIPELINE_V3
EVIDENCE_PIPELINE_HASH=9e9933b363026623b7ab5629f3281fa680a53a2e
STRATEGY_IMPLEMENTATION_VERSION=WS_CANONICAL_IS_C171_V1
```

A corrected official IS run is invalid unless the candidate-universe service receives the exact tick-risk threshold from the immutable paramset, all above-threshold rows contain `WS_TICK_RISK_HIGH` in their full reason-code set, and no above-threshold row remains eligible. Because the repaired adapter also restores max-liquidity and max-volume guards, V3 comparison requires paramset 5 as the control plus paramsets 7-11; V1 metrics must not serve as a direct control for V3. Existing V1 evals and historical evals 192 and 194-198 remain immutable. OOS and promotion remain forbidden.
