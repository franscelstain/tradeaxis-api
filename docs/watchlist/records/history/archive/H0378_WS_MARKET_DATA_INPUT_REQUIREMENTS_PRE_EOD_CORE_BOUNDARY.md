# Watchlist Weekly Swing — Market Data Input Requirements

## Purpose

Dokumen ini mengunci apa yang **Weekly Swing butuhkan** dari Market Data dan apa yang **Weekly Swing lakukan** setelah fakta upstream diterima. Dokumen ini tidak mendefinisikan ulang formula, publication mechanics, status semantics, sector classification, corporate action, atau arti field Market Data. Semua meaning tersebut tetap dimiliki producer.

## Lifecycle Position

- **Stage:** `WS-S01` — Trusted Market Data Binding.
- **Producer:** `docs/market_data/`.
- **Consumer:** `docs/watchlist/authority/strategy/`.
- **Consumes:** satu producer-facing versioned Market Data read product untuk requested EOD date atau explicit replay identity.
- **Produces:** trusted Weekly Swing intake context + per-ticker facts yang boleh masuk `WS-S02`.

## Single Allowed Meaning of Intake

Weekly Swing hanya boleh membangun core EOD intake dari consumer-facing Market Data contracts, terutama:

- `../../../market_data/book/CONSUMER_READ_CONTRACT_LOCKED.md`;
- `../../../market_data/book/Downstream_Consumer_Read_Model_Contract_LOCKED.md`;
- `../../../market_data/book/Downstream_Data_Readiness_Guarantee_LOCKED.md`;
- `../../../market_data/book/EOD_Eligibility_Snapshot_Contract_LOCKED.md`;
- `../../../market_data/book/Market_Calendar_Requirements_Contract.md`;
- `../../../market_data/book/Tickers_and_Identity_Dependency_Contract_LOCKED.md`;
- `../../../market_data/book/Trading_Status_Source_Contract_LOCKED.md`;
- `../../../market_data/registry/Indicator_Registry_Baseline_LOCKED.md`;
- `../../../market_data/registry/Volume_and_Turnover_Normalization_LOCKED.md`;
- `../../../market_data/registry/Exchange_Market_Structure_Facts_LOCKED.md`;
- owner producer lain yang dirujuk oleh contracts tersebut.

Watchlist tidak boleh menjadikan raw/canonical/current/history table Market Data, `MAX(trade_date)`, current master/status, benchmark table, publication pointer table, run table, atau reconstruction query lokal sebagai kontrak intake paralel.

## Current EOD Run Acceptance

Untuk membentuk **new current Weekly Swing PLAN** pada requested EOD date `D`, intake harus memenuhi seluruh run-level condition berikut:

1. producer read response berhasil di-resolve melalui consumer contract;
2. `readiness_state = READABLE`;
3. `freshness_state = FRESH`;
4. `effective_trade_date = requested_trade_date = D`;
5. seluruh row/fact family berasal dari publication identity yang sama;
6. publication/read-model/config/formula/factor identity yang diwajibkan response tersedia untuk binding/replay.

Jika Market Data secara sah mengembalikan prior effective date dengan `STALE`/`DEGRADED`, data tersebut boleh dibaca sebagai **explicit stale context**, tetapi **tidak boleh diberi label sebagai PLAN baru untuk requested date D**. Weekly Swing menunggu data D atau menghasilkan current-run availability/no-output state; ia tidak memindahkan tanggal secara diam-diam.

`HELD`, `FAILED`, `BUILDING`, `SUPERSEDED`, atau `NOT_AVAILABLE` tidak dapat membentuk new current PLAN. Kondisi availability upstream bukan alasan untuk mengganti producer contract dengan direct table read.

## Row-Level Data Usability Versus Strategy Eligibility

Market Data `data_usable=true` berarti upstream declared integrity/readiness gates tidak menemukan blocking objection. Itu **bukan** Weekly Swing candidate approval.

Urutan canonical:

`Market Data data_usable` → `Weekly Swing absolute eligibility` → `candidate classification` → `score/rank`

Rules:

- `data_usable=false` → ticker tidak boleh masuk recommendation-candidate path pada run tersebut;
- `data_usable=true` → ticker baru **boleh dievaluasi**, belum otomatis layak direkomendasikan;
- compatibility field `eligible` dari Market Data, bila masih ada, dibaca hanya sebagai alias upstream `data_usable`, tidak pernah sebagai `strategy_eligible`;
- Watchlist tidak boleh recompute producer-side data usability.

## Weekly Swing Need → Market Data Authority → WS Behavior

| Weekly Swing need | Market Data authority / semantic input | Requirement class | Weekly Swing behavior |
|---|---|---|---|
| Publication/read identity | consumer read contract; `publication_id`, read-model/version, requested/effective dates, lineage/config/formula/factor identities | always required | bind ke PLAN/replay; jangan campur publication/version |
| Trading calendar / completed session | producer Regular-Market calendar + session-completion semantics | always required | requested date harus merupakan completed governed trading session; jangan menebak weekday/libur sendiri |
| Temporal universe / listing membership | publication-bound temporal universe, listing interval, board/segment identity, stable `listing_id` | always required | hanya listing yang expected/in-universe pada D dievaluasi; current ticker/master tidak boleh diproyeksikan ke histori |
| Current EOD readiness | `readiness_state`, `freshness_state`, requested/effective-date relation | always required for current PLAN | hanya `READABLE + FRESH + same-date` membentuk new current PLAN |
| Stable instrument identity | publication-bound `listing_id` / stable instrument identity; symbol hanya presentation | always required | persistence/replay pakai stable identity; ticker text bukan historical key |
| Upstream row usability | `data_usable` + complete upstream reason set | always required | false = keluar dari recommendation path; true = lanjut ke WS eligibility |
| Executable EOD price context | consumer-exposed Regular-Market `RAW` OHLCV | required when price/trade-plan/evaluation uses it | price floor, display/trade reference, dan historical executable-price logic memakai declared RAW basis; jangan memakai adjusted price sebagai actual order price |
| Analytical technical basis | producer indicator artifact on declared `STRUCTURAL_ADJUSTED` basis | required through indicators | Watchlist memakai published indicator value; tidak recompute adjustment/indicator |
| Liquidity floor | **bootstrap canonical:** `adv20_close_volume_proxy_idr`; legacy `dv20_idr` hanya compatibility alias untuk metric yang sama | active hard-gate required | threshold Weekly Swing diterapkan ke proxy yang explicitly named; jangan menyebutnya actual turnover |
| Actual traded-value context | `adv20_traded_value_idr_actual` bila source-backed complete | optional until separately adopted | boleh ditampilkan/audit context; menjadi selection metric hanya melalui strategy identity + re-proof baru |
| Participation/volume quality | registry semantic `vol_ratio_20`; producer serialization alias hanya boleh dipetakan bila semantik/formula sama | active score/gate required | missing/invalid saat aktif → ticker tidak dapat menjadi recommendation candidate |
| Volatility/risk quality | `atr14_pct` | active score/gate required | digunakan sebagai risk guard/component sesuai frozen strategy identity |
| Momentum | baseline `roc20`; `roc5`/`roc10` hanya bila active identity memakainya | strategy-dependent required | active metric missing → fail-closed candidate path; tidak zero-fill |
| Breakout/setup | baseline `close_to_hh20_pct`; `hh20`, `range_position_20_pct`, range facts bila active | strategy-dependent required | hanya metric active yang menjadi required; no consumer recompute from hidden producer internals |
| Price floor | current-date consumer-exposed `RAW close` | strategy-dependent required | threshold harga adalah WS policy; Market Data hanya memberi fakta harga |
| Exchange market-structure facts | effective-dated Regular-Market minimum-price, tick/fraction ladder, upper/lower auto-rejection band + revision/source identity | required only when active trade-plan/evaluation needs executable-price validation | Watchlist menerapkan rounding/execution feasibility secara causal; tidak hardcode current tier ke histori dan tidak mengubah fakta exchange menjadi scoring alpha |
| Market regime / benchmark context | consumer-exposed point-in-time benchmark context with producer-owned revision/lineage; exact benchmark fields are frozen by the active WS identity | required only when active strategy uses regime | tidak boleh reconstruct IHSG dari `market_benchmark_*` tables; jika active identity membutuhkan mis. IHSG ROC20/MA-slope tetapi producer contract belum mengeksposnya, capability itu `UNAVAILABLE` dan candidate path tidak boleh menebak/recompute |
| Sector context / rotation | `sector_code`, `sector_roc20`, `rs_20_vs_sector`, `sector_rs_20_vs_ihsg` with temporal membership/revisions | optional unless active | missing optional sector context tidak menggagalkan unrelated indicators; bila active gate/score memakai field, field menjadi required |
| Trading status | `trading_status_code`, `is_suspended`, source revision | execution safety fact | known state yang mencegah normal Regular-Market entry → `AVOID`; non-blocking status facts tidak otomatis menjadi Market Data failure |
| UMA / event-risk facts | `is_uma`, `event_risk_flag`, ordered reasons | strategy-dependent | fakta tidak diubah; hanya active WS risk rule yang boleh memetakan ke WATCH_ONLY/AVOID/penalty |
| Corporate-action / contamination | producer event/factor/contamination state and field-level validity | required when it affects active fields | affected required field invalid → candidate path fail-closed; event flag sendiri bukan alasan post-hoc tanpa active rule |
| Per-field null/reasons | indicator/read-model field validity + null reason | always respected | required field null/invalid → ticker tidak discore; optional field null hanya menonaktifkan dependent optional behavior |
| Historical point-in-time replay | exact/as-known consumer replay/publication identity | required for backtest/OOS | tidak memakai current master/status/sector; tidak substitute prior date silently |
| D+1 decision-time CONFIRM | **tidak dijamin oleh current EOD read contract** kecuali producer-facing decision-time contract tersedia | optional | tanpa source yang governed → `UNAVAILABLE_RETRYABLE`; tidak menghambat core Top Picks |

## Operational Publication-Time and Benchmark-Proof Inputs

Untuk membuktikan real-use executability, Watchlist membutuhkan producer-facing timestamps/identity yang memungkinkan penentuan kapan EOD facts benar-benar readable. Watchlist tidak boleh mengarang publication completion time untuk membuat D+1 open terlihat executable.

Untuk benchmark-relative proof yang aktif, producer contract harus menyediakan point-in-time benchmark series/facts yang cukup untuk menghitung causal matched-horizon benchmark return. Canonical primary benchmark adalah IHSG/IDX Composite bila producer contract mengekspos authoritative point-in-time series tersebut. Jika required benchmark input tidak tersedia, benchmark-relative production proof adalah `INSUFFICIENT EVIDENCE`; Watchlist tidak boleh merekonstruksi benchmark dari hidden producer internals.

## Weekly Swing Needs That Market Data Does Not Own

Agar boundary tidak melebar, kebutuhan berikut tetap milik Watchlist walaupun memakai fakta upstream:

| Weekly Swing need | Market Data role | Weekly Swing ownership |
|---|---|---|
| liquidity threshold / risk band / momentum bounds / setup threshold | supplies versioned facts only | threshold dan active paramset identity |
| score weights, final quality floor, rank/tie-break, jumlah Top Picks | none beyond supplying facts | full strategy authority |
| entry/exit family, stop/target/signal thresholds, holding horizon | may supply RAW bars + exchange structure facts | full trade-plan/evaluation authority |
| fee, slippage, adverse-friction stress | none | evaluation/proof authority |
| user capital/affordability | none | optional presentation only; tidak mengubah recommendation/rank |
| D+1 CONFIRM decision | EOD contract does not guarantee decision-time data | optional Watchlist capability using a separately governed source |

Tidak adanya owner Market Data untuk policy di atas bukan gap. Gap baru ada bila Watchlist membutuhkan **fakta pasar** tetapi tidak ada producer-facing contract yang mengekspos fakta tersebut secara point-in-time.

## Liquidity Basis Lock

Current Weekly Swing bootstrap uses `adv20_close_volume_proxy_idr` as the canonical liquidity selection measure because it is explicitly identified by Market Data as the `RAW close × RAW volume` 20-session proxy and is compatible with existing historical `dv20_idr` evidence.

Hard rules:

- `dv20_idr` may be accepted only as a compatibility alias of `adv20_close_volume_proxy_idr`;
- it must never be described as actual exchange traded value;
- `adv20_traded_value_idr_actual` must not silently replace the proxy under the same paramset/proof identity;
- adopting actual traded value for selection is a new strategy identity and requires new IS/OOS/friction proof.

## Required-Field Rule

Field requiredness is the intersection of:

1. producer read-model minimum requirements; and
2. fields actually used by the active Weekly Swing hard gates, score components, trade-plan derivation, or final qualification.

Missing optional producer context does not fail the whole Weekly Swing run. Missing active required field removes only the affected ticker from recommendation candidacy unless the missing fact is run-level publication/readiness identity.

## No Consumer Recalculation Rule

Watchlist must not reconstruct or recompute:

- adjustments/factors;
- ATR/ROC/MA/range/volume-ratio;
- sector membership or benchmark series;
- trading-status history;
- producer coverage/data-usability verdict;
- publication freshness/readability.

Thresholds, score transforms, ranking, risk preference, and recommendation membership remain Watchlist-owned downstream policy.

## Historical Evaluation Rule

Historical evaluation binds the exact/as-known Market Data identity available under the replay mode. A missing requested historical publication/date is explicit missing evidence; it is never replaced by a newer corrected/current state or an unlabeled prior date.

## CONFIRM Boundary

The current Market Data core contract is EOD-oriented. Weekly Swing must not assume that it provides D+1 intraday/current decision-time data. CONFIRM may consume a future governed producer-facing source when such a contract exists. Until then, the correct state is availability uncertainty, not core strategy failure.

## Final Invariants

1. Market Data owns fact meaning; Watchlist owns decision policy.
2. One producer-facing read path, no normal direct-table intake.
3. Current PLAN requires readable, fresh, same-date EOD data.
4. `data_usable` is upstream usability, never strategy eligibility.
5. Required active field missing means no recommendation candidacy for that ticker, not synthetic zero/default.
6. Liquidity basis is explicit and cannot switch actual/proxy under one proof identity.
7. Historical replay is exact/as-known and no-lookahead.
8. Calendar, temporal universe/listing, board/status, and exchange market-structure facts are resolved point-in-time; current reference state is never back-projected.
9. A benchmark/regime dependency that is not exposed by the selected producer contract is unavailable, never locally reconstructed.
10. Optional CONFIRM does not create a hidden requirement for an intraday Market Data product.
