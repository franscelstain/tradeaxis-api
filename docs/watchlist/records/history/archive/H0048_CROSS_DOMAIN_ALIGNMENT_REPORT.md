# Watchlist Weekly Swing ↔ Market Data Cross-Domain Alignment Report

**Date:** 2026-08-17  
**Scope:** documentation/contract alignment only  
**Verdict:** `PASS — CROSS_DOMAIN_DOCUMENTATION_ALIGNED`  
**Implementation conformance:** `PENDING`

## Objective

Menutup ambiguity antara Market Data sebagai authoritative fact provider dan Watchlist Weekly Swing sebagai decision-policy consumer, sehingga implementer dapat mengetahui secara eksplisit:

1. fakta apa yang dibutuhkan Weekly Swing;
2. kontrak Market Data mana yang memiliki semantic authority;
3. kapan field/fact tersebut required atau optional;
4. bagaimana Weekly Swing bereaksi ketika data valid, invalid, stale, missing, atau tidak tersedia;
5. batas tegas antara upstream fact ownership dan downstream strategy ownership.

## Canonical bridge created

### Strategy owner

`docs/watchlist/strategy/weekly_swing/WS_MARKET_DATA_INPUT_REQUIREMENTS.md`

Owner ini mengunci stage `WS-S01 — Trusted Market Data Binding` dan menjadi satu tempat canonical untuk pemetaan Weekly Swing need → Market Data authority → WS behavior.

### Technical translation owner

`docs/watchlist/implementation/weekly_swing/MARKET_DATA_INTAKE_IMPLEMENTATION_CONTRACT.md`

Owner ini mengunci producer-facing intake surface, logical DTO, readiness decision table, per-row behavior, compatibility aliases, replay, lineage, dan forbidden direct-table behavior.

### System bridge

`docs/system_audit/SYSTEM_CROSS_DOMAIN_INPUT_BASELINE.md`

Bridge lintas-domain sekarang menyatakan documentation intake readiness `PASS`, sementara code/runtime conformance tetap dinilai terpisah.

## Canonical mapping summary

| Weekly Swing need | Market Data authority | Weekly Swing behavior |
|---|---|---|
| Publication/read identity | versioned consumer read product, publication/read-model/config/formula/factor/lineage identity | bind ke PLAN dan replay; tidak mencampur identity |
| Trading calendar/session | governed IDX Regular-Market calendar + completed-session semantics | tidak menebak weekday/libur; current PLAN hanya untuk completed governed session |
| Temporal universe/listing | publication-bound temporal membership, listing interval, board/segment, stable listing identity | current master/ticker tidak diproyeksikan ke histori |
| Current readiness | `readiness_state`, `freshness_state`, requested/effective-date relation | current PLAN hanya `READABLE + FRESH + effective=requested` |
| Upstream data usability | `data_usable` + complete reasons | prerequisite upstream saja; bukan strategy eligibility |
| RAW executable price facts | consumer-exposed RAW Regular-Market OHLCV | actual entry/exit/display/price-floor basis; adjusted price bukan fill price |
| Analytical indicator basis | producer `STRUCTURAL_ADJUSTED` indicator artifact | gunakan published indicator; no local adjustment/recompute |
| Liquidity baseline | `adv20_close_volume_proxy_idr` | active WS liquidity threshold diterapkan ke proxy; legacy `dv20_idr` hanya alias proxy |
| Actual traded-value context | `adv20_traded_value_idr_actual` | optional context; menjadi selection metric hanya lewat strategy identity + re-proof baru |
| Participation | `vol_ratio_20` | active required field invalid/missing → tidak menjadi recommendation candidate |
| Volatility | `atr14_pct` | dipakai active risk quality/guard sesuai frozen identity |
| Momentum | baseline `roc20`; `roc5/roc10` jika active | active missing → fail-closed candidate path, no zero-fill |
| Breakout/setup | baseline `close_to_hh20_pct`; optional HH/range facts jika active | tidak reconstruct dari producer internals |
| Strategic price floor | RAW close fact | threshold adalah WS policy, harga adalah MD fact |
| Exchange market structure | effective-dated minimum Regular-Market price, tick/fraction ladder, upper/lower price bands | WS menerapkan executable-price rule; current tier tidak di-hardcode ke histori; bukan alpha fact secara default |
| Benchmark/regime | consumer-exposed point-in-time benchmark facts + revision/lineage | jika active dependency belum diekspos → `UNAVAILABLE`; tidak query/recompute `market_benchmark_*` |
| Sector context | temporal sector + sector/relative-strength facts | optional kecuali active strategy memakainya |
| Trading status | temporal status, `is_suspended`, source revision | factual entry-blocking state → AVOID; WS tidak mengubah meaning upstream |
| UMA/event risk | `is_uma`, event-risk facts/reasons | efek terhadap WATCH_ONLY/AVOID/penalty hanya dari active WS rule |
| Corporate action/contamination | event/factor/contamination validity | required affected field invalid → candidate fail-closed |
| Per-field validity/null reason | producer field validity + reason | required invalid → no scoring; optional missing hanya menonaktifkan dependent behavior |
| Historical replay | exact/as-known publication/read identity | no current-master/status/sector leakage; no silent prior-date substitution |
| D+1 CONFIRM | tidak dijamin EOD Market Data contract | optional; missing governed decision-time source → `UNAVAILABLE_RETRYABLE`, core Top Picks tetap valid |

## Ownership boundary locked

Market Data **owns facts and meaning**, termasuk:

- publication/readiness/freshness;
- temporal identity/universe/calendar/status;
- RAW and analytical price-product semantics;
- indicators and null reasons;
- liquidity actual/proxy meaning;
- sector/benchmark facts;
- corporate action/contamination;
- exchange market-structure facts.

Weekly Swing **owns decision policy**, termasuk:

- minimum liquidity/risk/momentum/setup thresholds;
- candidate eligibility/classification;
- scoring weights and transforms;
- final quality floor;
- Top Picks membership/ranking/count;
- entry/exit policy and five-day horizon;
- cost/slippage/stress model;
- optional CONFIRM decision semantics.

Market Data must not acquire Watchlist policy. Watchlist must not recreate Market Data facts.

## Critical gaps closed

1. Producer-internal `SUCCESS run`, current pointer, run mirror, and physical tables are no longer a parallel Watchlist intake contract.
2. Current PLAN date policy is explicit: `READABLE + FRESH + same-date` only.
3. `data_usable` is explicitly separated from Weekly Swing strategy eligibility.
4. Actual turnover versus close×volume proxy is no longer ambiguous.
5. `vol_ratio_20` is the canonical semantic field; old `vol_ratio` is compatibility-only.
6. Active field requiredness and null behavior are explicit and fail-closed per ticker.
7. Current intake and historical replay no longer have competing fallback semantics.
8. D+1 CONFIRM is explicitly outside the guarantee of the core EOD contract.
9. Calendar/session and temporal universe/listing dependencies are explicit.
10. Effective-dated tick/fraction/minimum-price/price-band facts now have an owner/application boundary.
11. Benchmark/regime dependencies cannot fall back to direct `market_benchmark_*` reconstruction.
12. Shared DB guidance is role-aware: producer dictionary for producer work; consumer contract for Watchlist intake; Watchlist dictionary for Watchlist persistence.
13. Legacy implementation tokens `dv20_idr` and `vol_ratio` now carry a current semantic override wherever active technical contracts still preserve them.
14. System readiness documents now distinguish documentation readiness from implementation conformance.

## Market Data contracts aligned

The downstream read contract was clarified so that:

- physical/database field names are not downstream semantic contracts;
- compatibility aliases must be explicit exact aliases;
- downstream consumers cannot require producer-internal run/pointer/table state as a second acceptance contract;
- requested benchmark-dependent context is exposed through the governed read surface rather than internal benchmark-table reads;
- requested point-in-time exchange market-structure facts can be exposed for executable-price evaluation without transferring scoring/tradability ownership to Market Data.

`EOD_Indicators_Contract.md` and formula references now use canonical semantic `vol_ratio_20`; legacy serialized `vol_ratio` remains compatibility-only.

## Watchlist implementation compatibility

Physical schemas, parameter keys, fixtures, verification docs, and historical contracts may still contain names such as:

- `dv20_idr` / `min_dv20_idr`;
- `vol_ratio` / `min_vol_ratio`.

They are not silently redefined. Active technical documents now state:

- `dv20_idr` semantics = `adv20_close_volume_proxy_idr` only;
- `vol_ratio` semantics = `vol_ratio_20` only when producer version proves exact equivalence;
- actual traded value cannot replace the proxy under the same proof identity.

Physical renaming/migration remains implementation work, not a strategy change.

## Traceability

Finding:

`docs/watchlist/findings/weekly_swing/WS_MARKET_DATA_CROSS_DOMAIN_BINDING_GAPS_2026-08-17.md`

Decision:

`docs/watchlist/decisions/weekly_swing/WS_MARKET_DATA_CROSS_DOMAIN_BINDING_ALIGNMENT_2026-08-17.md`

Pre-change strategy snapshot:

`docs/watchlist/history/weekly_swing/superseded/2026-08-17_pre-market-data-watchlist-binding/`

## Validation

Final automated/document-semantic validation:

- active Markdown files checked: **188**
- broken active Markdown links: **0**
- JSON files checked: **115**
- JSON parse errors: **0**
- CSV files checked: **18**
- CSV parse errors: **0**
- cross-domain semantic checks: **27/27 PASS**

Validation evidence: `WATCHLIST_MARKET_DATA_CROSS_DOMAIN_VALIDATION.json`.

## Final status

`CROSS_DOMAIN_DOCUMENTATION_ALIGNMENT=PASS`

`WATCHLIST_MARKET_DATA_CONTRACT_MAPPING=PASS`

`IMPLEMENTATION_CONFORMANCE=PENDING`

`PRODUCTION_READINESS=NOT_INFERRED`

The remaining work is no longer a documentation ambiguity between Market Data and Weekly Swing. The next work is implementation alignment: prove that current code, repositories, DTOs, schema mappings, fixtures, and runtime paths actually implement this contract without direct producer-internal bypasses or semantic alias drift.
