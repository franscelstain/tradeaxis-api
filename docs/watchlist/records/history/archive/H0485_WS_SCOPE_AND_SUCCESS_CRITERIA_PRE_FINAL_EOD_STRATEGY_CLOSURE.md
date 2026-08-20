# Watchlist Weekly Swing — Scope and Success Criteria

## Lifecycle Position

- **Stage:** `WS-S00` — Scope and Success Lock.
- **Produces:** frozen product boundary, success meaning, Top Picks semantics, dan out-of-scope guard.
- **Next:** `WS-S01` trusted Market Data binding.

## Current Active Scope

- domain: `watchlist`
- active policy: `weekly_swing`
- core layers: `PLAN`, `RECOMMENDATION`
- optional enhancement: `CONFIRM`

## Product Scope

Weekly Swing hanya berfungsi sebagai decision-support watchlist untuk memilih saham IDX yang paling layak dipertimbangkan untuk pembelian swing dengan maximum holding horizon 5 trading day.

Output utama produk adalah **qualified recommendations yang diurutkan sebagai TOP PICKS**. Jumlah Top Picks mengikuti kualitas kandidat yang tersedia dan boleh bernilai nol.

## In Scope

- konsumsi Market Data EOD yang sah, konsisten, point-in-time, dan dapat direplay;
- pembentukan candidate PLAN Weekly Swing;
- eligibility, setup, risk, scoring, dan ranking kandidat;
- final recommendation qualification;
- ranked Top Picks;
- PLAN-derived entry dan predeclared exit/risk plan information;
- optional CONFIRM sebagai pemeriksaan current actionability terhadap Top Picks ketika decision-time data tersedia;
- IS/OOS/core-shadow proof untuk membuktikan bahwa strategy mempunyai positive expected net return setelah realistic trading friction dan downside yang terkendali;
- optional CONFIRM proof bila capability current-actionability ingin dinyatakan proven.

## Out of Scope

- portfolio construction atau portfolio optimization;
- broker execution atau automatic order placement;
- order lifecycle;
- position management setelah pembelian;
- holdings / realized-unrealized PnL / trade journal;
- Market Data acquisition/internal processing;
- provider-specific acquisition logic;
- policy trading selain `weekly_swing`.

## High-Trust Success Standard

Weekly Swing hanya dapat disebut **high-trust** bila proof untuk exact strategy identity menunjukkan seluruh hal berikut secara bersamaan:

- recommendation terbentuk secara point-in-time dan causal tanpa leakage;
- recommendation tersedia cukup awal untuk keputusan manual sebelum canonical entry opportunity;
- executed trade tidak dapat menghilang dari statistik hanya karena exit kemudian tidak executable;
- positive expected net edge tetap ada setelah realistic friction, statistical uncertainty, dan multiple-testing/selection-bias control;
- edge mempunyai economic significance dan positive benchmark-relative/selection uplift, bukan sekadar nilai rata-rata yang infinitesimal di atas nol;
- Top-1/Top-3/Top-5 presentation subsets menunjukkan ranking utility yang konsisten dengan final ordered Top Picks;
- downside/tail risk, execution delay, liquidity/capacity, dan adverse friction berada dalam bounded production-use limits;
- untouched OOS benar-benar protected dan tidak dipakai ulang sebagai tuning set setelah outcome dibaca;
- forward shadow membuktikan operational availability, causal execution, dan live-equivalent behavior;
- setelah real use dimulai, rolling strategy-health monitoring dapat menghentikan new recommendation publication ketika material degradation terdeteksi sampai revalidation selesai.

High-trust proof tidak menjamin setiap Top Pick untung. Ia berarti positive edge dan downside control telah dibuktikan melalui conservative, reproducible, contamination-resistant evaluation.

## Hard Boundary Rules

1. Watchlist hanya memberi decision-support dan tidak melakukan transaksi.
2. `weekly_swing` adalah satu-satunya active watchlist policy.
3. Watchlist hanya mengonsumsi authoritative Market Data product dan tidak mendefinisikan ulang fakta pasar upstream.
4. Recommendation quality tidak boleh dikorbankan untuk memenuhi jumlah picks tertentu.
5. Bila tidak ada kandidat yang melewati seluruh qualification gate, output yang benar adalah **NO QUALIFIED TOP PICKS**.
6. Final Top Picks adalah output core Weekly Swing yang sah walaupun CONFIRM belum diminta atau current-entry data belum tersedia.
7. CONFIRM hanya menambah current-actionability evidence ketika data yang sah tersedia; ketidaktersediaan CONFIRM tidak boleh menggagalkan, menghapus, atau mererank Top Picks.
8. `NOT_ACTIONABLE` hanya boleh dihasilkan bila valid CONFIRM data tersedia dan active gate benar-benar dapat dievaluasi; missing/stale/incomplete data bukan negative decision.
9. Target strategy adalah positive expected net return setelah biaya dan slippage yang realistis dengan downside terkontrol; target ini bukan jaminan bahwa setiap trade akan untung.


## EOD-Only Core Product Boundary

Canonical core Weekly Swing adalah **EOD decision-support product**. Ia bukan intraday strategy, realtime trading engine, orderbook strategy, broker-routing service, atau automatic execution system.

1. Core `PLAN`, final `RECOMMENDATION/TOP_PICKS`, ranking, dan core production availability **MUST** dapat diselesaikan hanya dari authoritative point-in-time EOD Market Data plus governed calendar/market-structure facts yang memang diperlukan oleh strategy.
2. Realtime quote, intraday feed, orderbook, queue position, broker fill feed, dan automatic-order capability **MUST NOT** menjadi prerequisite candidate eligibility, scoring, ranking, Top Picks publication, core historical qualification, atau core production-use approval.
3. Ketiadaan realtime/intraday/orderbook data **MUST NOT** mengubah qualified EOD Top Pick menjadi gagal, menghapus recommendation, atau membuat core Weekly Swing unavailable.
4. Top Pick adalah **saran EOD untuk peluang Weekly Swing berikutnya**, bukan janji bahwa pengguna akan memperoleh harga tertentu pada sesi berikutnya.
5. Actual investor order placement, queue position, fill price, partial fill, dan broker execution berada di luar core authority; perbedaan actual execution pengguna tidak mengubah historical EOD recommendation truth.
6. Bila di masa depan intraday/orderbook/realtime capability diadopsi, capability tersebut harus menjadi separately governed optional capability atau new strategy identity dengan proof sendiri; ia tidak boleh diam-diam mengubah core EOD strategy.
7. `NEXT_OPEN` di historical proof adalah causal **reference execution model** untuk menguji strategy setelah signal EOD, bukan exact-fill guarantee kepada pengguna.

## Market-Data Ownership Hard Boundary

High-trust Weekly Swing memerlukan batas ownership yang universal, bukan daftar indikator yang bisa menjadi usang ketika Market Data berkembang.

- `MARKET_DATA_OWNS_ALL_MARKET_FACTS`: setiap observasi, pengukuran, klasifikasi, status, calendar/session fact, corporate-action fact, benchmark/sector fact, market-structure fact, atau derived market feature yang mempunyai arti independen dari Weekly Swing **MUST** dimiliki dan diekspos oleh Market Data.
- `WATCHLIST_MUST_NOT_CREATE_SUBSTITUTE_MARKET_FACTS`: Watchlist **MUST NOT** reconstruct, recompute, repair, enrich, normalize, impute, infer, reinterpret, atau independently source market fact untuk menggantikan fakta producer yang missing/unavailable.
- Watchlist hanya memiliki strategy-dependent outputs seperti eligibility, candidate state, score component, `score_total`, rank, trade plan, modeled execution, return/risk proof, dan production-health verdict; output tersebut boleh dihitung dari authoritative Market Data facts menggunakan frozen Weekly Swing identity.
- Kesederhanaan formula atau kemampuan menghitung nilai dari raw fields **tidak** memindahkan semantic ownership dari Market Data ke Watchlist.
- Jika strategy memerlukan market fact yang belum tersedia pada producer-facing contract, hasilnya adalah explicit `UPSTREAM_MARKET_DATA_DEPENDENCY_GAP`; affected path harus fail closed/unavailable sesuai scope-nya dan tidak boleh diselamatkan dengan local workaround.

## Weekly Swing Identity and Scope-Admission Lock

Untuk mencegah scope creep, setiap perubahan canonical strategy **MUST** tetap memenuhi identity berikut:

- `WEEKLY_SWING_ONLY`: signal berasal dari EOD dan horizon keputusan tetap swing beberapa trading session dengan canonical maximum holding 5 trading day; rule intraday/scalping tidak boleh masuk core.
- `EOD_ONLY`: core selection, PLAN, ranking, Top Picks, dan EOD action intent harus dapat diselesaikan tanpa realtime/orderbook/broker feed.
- `DECISION_SUPPORT_ONLY`: output memberi saran dan risk plan untuk keputusan manual; Watchlist tidak menempatkan order atau mengelola posisi investor.
- `MARKET_DATA_AS_FACT_OWNER`: seluruh market fact tetap berasal dari authoritative Market Data; Watchlist hanya membuat strategy-dependent decisions/outcomes.
- `TOP_PICKS_AS_PRODUCT`: perubahan harus mempunyai hubungan langsung dengan kualitas selection, qualification, ranking, action intent, risk, robustness, atau proof Top Picks Weekly Swing.
- `CAUSAL_AND_TESTABLE`: rule baru harus dapat diuji point-in-time menggunakan fakta yang secara sah tersedia pada decision date; rule yang membutuhkan future knowledge tidak boleh menjadi canonical.

Ide trading yang gagal salah satu identity test di atas harus dinyatakan `OUT_OF_CORE_SCOPE` atau tetap sebagai research hypothesis, bukan dimasukkan ke canonical Weekly Swing hanya karena umum digunakan dalam trading.

### Weekday / Calendar-Anomaly Boundary

- Canonical Weekly Swing **MUST NOT** mengasumsikan hari tertentu sebagai hari beli/jual terbaik; tidak ada default `BUY_TUESDAY`, `SELL_THURSDAY`, `SELL_FRIDAY`, atau rule weekday sejenis.
- Entry opportunity ditentukan oleh qualified EOD signal dan governed `NEXT_TRADING_SESSION`; exit ditentukan oleh frozen stop/target/time-exit semantics, bukan nama hari.
- Weekday/day-of-week hanya boleh diuji sebagai preregistered challenger/context. Jika ingin memengaruhi eligibility, score, entry, exit, atau ranking, ia menjadi material strategy identity dan wajib melewati IS/OOS/stress/shadow proof yang sama.
- Calendar/session identity tetap Market Data-owned; Watchlist tidak menghitung weekend/holiday/session berikutnya dari kalender sipil sendiri.

## Real-World Weekly Swing Followability and Execution Invariants

Penguatan real-world berikut tetap berada di dalam identitas **EOD Weekly Swing decision support** dan tidak mengubah Watchlist menjadi portfolio manager, realtime scanner, orderbook analyzer, atau broker execution engine.

- `CORE_RECOMMENDATION_IS_POSITION_INDEPENDENT`: membership, qualification, score, rank, dan action intent Top Picks **MUST NOT** bergantung pada actual user holding, cash balance, broker fill, atau portfolio state.
- Repeated qualification ticker yang sama pada beberapa EOD session tetap merupakan valid recommendation observation; core runtime boleh memberi continuity metadata tetapi **MUST NOT** menekan, menaikkan, atau menurunkan rank hanya karena ticker pernah direkomendasikan sebelumnya.
- Double-counting repeated signal terhadap penggunaan nyata dikendalikan pada **follower-replay proof**, bukan dengan membuat runtime bergantung pada posisi pengguna.
- Baseline active strategy hanya mendukung execution mechanism yang authoritative Market Data contract nyatakan kompatibel dengan normal Regular-Market continuous execution; unknown/unsupported mechanism tidak boleh masuk final recommendation path.
- Corporate action yang menimbulkan cash/quantity economic entitlement selama committed evaluation exposure **MUST** masuk economic-return proof menggunakan authoritative Market Data facts; price-only P&L tidak boleh diam-diam mengabaikan economic entitlement.
- Shortened/half-day/exception session **MUST** diperlakukan eksplisit; session-sensitive active feature hanya boleh dipakai sebagai normal recommendation input bila producer semantics menyatakan comparability-nya sah untuk session tersebut.
- Production modeled slippage tetap **EOD-only** tetapi harus condition-dependent dan deterministic; memburuknya liquidity, volatility, reference-order participation, atau tick burden tidak boleh menghasilkan modeled adverse slippage yang lebih kecil tanpa explicit strategy justification.
- Production qualification **MUST** mempunyai follower-replay evidence, edge-concentration stress, dan producer-correction sensitivity evidence sesuai sample availability rules pada proof stage.
- Seluruh penguatan real-world ini **MUST NOT** menjadikan realtime price, intraday tick, orderbook, queue position, atau broker state sebagai prerequisite core Watchlist.
