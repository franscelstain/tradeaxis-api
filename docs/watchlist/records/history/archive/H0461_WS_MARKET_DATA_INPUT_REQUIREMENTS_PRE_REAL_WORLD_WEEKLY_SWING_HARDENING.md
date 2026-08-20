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


## EOD-Only Dependency Lock

Core Weekly Swing **MUST** bergantung pada consumer-facing point-in-time **EOD** Market Data product, bukan hidden intraday/session state.

- Effective-dated calendar, board/status, corporate-action, benchmark/sector, dan exchange market-structure facts tetap sah sebagai EOD/reference facts; keberadaan fakta tersebut tidak mengubah Watchlist menjadi realtime system.
- Watchlist **MUST NOT** meminta orderbook, queue-depth, bid/ask stream, broker-fill stream, atau intraday tick feed sebagai syarat agar PLAN/Top Picks dapat terbentuk atau agar core proof dapat dinyatakan cukup.
- Jika EOD facts tidak cukup untuk membuktikan exact queue/fill pada suatu historical opportunity, evaluator wajib memakai conservative modeled-execution/uncertainty rule dari `WS_HISTORICAL_EVALUATION_STRATEGY.md`; ia tidak boleh mengarang orderbook history.
- Optional CONFIRM boleh memakai separately governed decision-time source bila capability tersebut diminta, tetapi source itu **MUST NOT** masuk required-field set core EOD strategy.
- Absence of decision-time/orderbook data adalah capability availability issue, bukan Market Data core failure dan bukan alasan untuk membatalkan EOD Top Picks.
- Future producer contract yang menambahkan realtime/intraday/orderbook facts tidak otomatis mengubah active Weekly Swing field set; adopsinya memerlukan explicit strategy/capability revision dan proof identity yang sesuai.

## Universal Market-Fact Ownership and No-Substitution Rule

Bagian ini adalah canonical owner untuk pemisahan fakta pasar dan keputusan Weekly Swing.

- Market Data **MUST** menjadi satu-satunya semantic owner untuk current maupun historical market facts yang dipakai Watchlist, termasuk factual feature baru yang belum disebut pada tabel requirement saat dokumen ini diterbitkan.
- Larangan local substitution mencakup reconstruct, recompute, repair, normalize, enrich, impute, infer, relabel, reinterpret, independently acquire dari external provider, atau membaca producer internals untuk menghasilkan market fact yang belum tersedia pada consumer contract.
- Ownership rule berlaku sama untuk current runtime, historical replay, IS, OOS, friction stress, forward shadow, production monitoring, dan optional capability yang mengonsumsi market facts.
- Exact compatibility alias/mapping hanya boleh digunakan bila producer contract/version menyatakan semantic equivalence; alias mapping tidak boleh melakukan formula transform yang menciptakan semantic market fact baru.
- Consumer-side cache/snapshot boleh dipakai untuk determinism/performance hanya bila payload, publication identity, lineage, dan meaning producer dipertahankan; cache tersebut tidak menjadi authority atau alternate source.
- Contoh factual needs yang tetap Market Data-owned bila kelak diperlukan meliputi market breadth, liquidity-stability statistics, sector leadership facts, regime inputs, session/half-day facts, corporate-action economic facts, listing/status facts, dan effective-dated exchange-structure facts.
- Watchlist boleh menghitung strategy outputs seperti threshold pass/fail, score transform/component, rank, entry/stop/target plan, matched-horizon strategy comparison, modeled net return, tail/path metrics, dan strategy-health verdict dari authoritative producer facts.
- Perhitungan yang menghasilkan reusable measurement tentang keadaan pasar/instrument dan tidak bergantung pada Weekly Swing identity tetap merupakan Market Data responsibility walaupun hanya membutuhkan arithmetic sederhana.
- Jika suatu output menggabungkan market facts dengan frozen Weekly Swing parameter dan meaning-nya berubah ketika strategy identity berubah, output tersebut dapat menjadi Watchlist strategy calculation; seluruh underlying market facts tetap producer-owned.
- Official Watchlist research/proof **MUST NOT** memakai locally derived substitute market fact lalu memperlakukannya sebagai comparable canonical evidence; research hanya boleh mencatat hypothesis/dependency sampai producer-facing fact tersedia.

## Explicit Upstream Market-Data Dependency Gap Protocol

Ketidaktersediaan fakta upstream harus terlihat sebagai dependency, bukan disembunyikan di code.

- Ketika required market fact belum tersedia pada authoritative consumer contract, Watchlist harus menghasilkan/menjaga explicit semantic state `UPSTREAM_MARKET_DATA_DEPENDENCY_GAP` untuk affected runtime/proof capability dan tidak membuat fallback market fact.
- Run-level dependency gap yang memengaruhi publication/readiness/calendar/universe/identity atau fact yang diperlukan seluruh run berarti new PLAN untuk affected date **MUST NOT** dibentuk.
- Ticker-level active fact gap membuat affected ticker fail closed dari recommendation candidacy sesuai eligibility/classification semantics tanpa mengubah ticker lain yang mempunyai complete authoritative facts.
- Proof-required historical fact gap menghasilkan `INSUFFICIENT EVIDENCE`/unavailable proof untuk affected gate dan **MUST NOT** diubah menjadi PASS dengan backfill lokal.
- Setelah Market Data menambahkan fact baru, Watchlist tetap tidak boleh memakainya sebagai active gate/score/ranking/proof behavior sampai controlled strategy identity secara eksplisit mengadopsinya dan required re-proof dijalankan.

## Delayed EOD Publication and Requested-Date Protocol

Watchlist tidak menggunakan wall-clock day sebagai bukti bahwa EOD hari tersebut sudah siap. Canonical target selalu explicit `requested_trade_date` dan producer menentukan readiness/effective date.

1. Untuk new PLAN, `effective_trade_date` **MUST** sama dengan `requested_trade_date`; system-run date/time boleh berbeda.
2. Jika requested EOD belum `READABLE + FRESH + same-date`, current run berakhir `MARKET_DATA_UNAVAILABLE_RETRYABLE` dan tidak membentuk PLAN/Top Picks baru.
3. Prior-date publication boleh ditampilkan sebagai explicitly labeled previous/stale context, tetapi tidak boleh disalin menjadi recommendation untuk requested date yang lebih baru.
4. Ketika exact requested EOD kemudian menjadi ready, Watchlist boleh retry requested date yang sama dan bind ke publication identity baru yang sah.
5. Producer/provider delay, retry, ingestion completeness, dan provider-specific recovery tetap tanggung jawab Market Data; Watchlist hanya membaca consumer-facing readiness/reason semantics.
6. Required fact yang active pada strategy path dan masih missing/invalid memblokir affected path sesuai fail-closed rules; missing optional/non-active fact tidak boleh secara otomatis menggagalkan core path.
7. `NEXT_TRADING_SESSION` dan session-open/cutoff anchor **MUST** berasal dari authoritative Market Data calendar/session facts; Watchlist tidak boleh memakai `trade_date + 1 calendar day`.
8. Jika same-date EOD baru tersedia setelah canonical entry cutoff untuk its next trading session, Market Data record tetap valid sebagai data tetapi Watchlist current action window untuk recommendation tersebut sudah expired.
9. Late-ready EOD tidak boleh di-roll-forward menjadi buy suggestion untuk session setelah intended next trading session; kesempatan baru memerlukan next governed EOD evaluation.

Canonical availability distinction:

- `MARKET_DATA_UNAVAILABLE_RETRYABLE` = requested EOD belum cukup untuk membentuk current recommendation;
- `READY_FOR_PLAN` = same-date authoritative EOD lengkap untuk active core path;
- `ACTION_WINDOW_EXPIRED` = EOD/strategy result dapat dihitung untuk analysis tetapi terlambat untuk current new-entry opportunity.

## Temporal Producer-Owned Field Binding

Ownership detail mengikuti `WS_PRODUCT_OBJECTIVE_AND_LAYERS.md` bagian **Canonical Temporal Field Ownership Contract**. Pada boundary Market Data, aturan tambahan berikut wajib berlaku:

- Consumer intake **MUST** memperoleh `effective_trade_date`, `market_data_published_at`, dan `market_data_revision_id` dari authoritative producer contract/publication identity; ketiganya tidak boleh direkonstruksi dari file time, database `updated_at`, wall-clock run time, atau latest available row.
- Watchlist boleh mempersist producer-owned temporal fields untuk provenance, tetapi nilai yang disimpan harus exact copy dari publication yang benar-benar dikonsumsi dan harus dapat ditelusuri kembali ke producer revision identity.
- Bila required producer-owned temporal field tidak tersedia, invalid, atau tidak coherent dengan publication identity, affected path berstatus `MARKET_DATA_UNAVAILABLE_RETRYABLE` atau `UPSTREAM_MARKET_DATA_DEPENDENCY_GAP` sesuai jenis gap; local fallback temporal fact dilarang.
- Market Data correction/revision adalah new producer publication lineage. Existing Watchlist recommendation tidak boleh di-placebo update agar tampak berasal dari revision baru; re-evaluation harus explicit dan menghasilkan lineage Watchlist baru bila dilakukan.
- Market Data memiliki calendar/session/open facts; Watchlist memiliki derivasi strategy seperti `intended_entry_session`, `canonical_entry_cutoff`, dan `action_window_status`. Producer tidak perlu dan tidak boleh menjadi owner keputusan actionability Weekly Swing tersebut.

