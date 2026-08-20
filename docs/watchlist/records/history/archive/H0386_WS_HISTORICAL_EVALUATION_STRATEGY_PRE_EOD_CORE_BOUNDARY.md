# Watchlist Weekly Swing — Historical Evaluation Strategy

## Purpose

Backtest dan calibration harus membuktikan behavior yang benar-benar dipakai pengguna: **final qualified RECOMMENDATION/TOP_PICKS**, bukan PLAN candidate state sebagai proxy.

Evaluation harus point-in-time, reproducible, executable-price aware, dan net of realistic trading friction.

Default evaluation window adalah 2 tahun. Window berbeda harus mempunyai evaluation identity berbeda.

Historical evaluation tidak boleh membuat synthetic historical CONFIRM dari informasi yang tidak tersedia pada decision time. Baseline backtest/OOS dan core forward shadow membuktikan final **EOD Top Picks**. Optional D+1 CONFIRM actionability hanya dibuktikan secara forward bila valid decision-time data tersedia; kekurangan CONFIRM data tidak memengaruhi core proof verdict.

## Lifecycle Position

- **Stage:** `WS-S06` — Historical Evaluation Model.
- **Consumes:** exact frozen EOD selection/trade-plan semantics from `WS-S01..WS-S04` and historical point-in-time Market Data.
- **Produces:** causal executable final-Top-Pick outcomes suitable for IS/OOS evaluation.
- **Next:** `WS-S07` IS sufficiency and winner freeze.

## Inputs

Evaluation menggunakan:
- historical authoritative Market Data EOD publication/read product under `WS_MARKET_DATA_INPUT_REQUIREMENTS.md`;
- trading calendar yang sah;
- exact Weekly Swing strategy identity;
- deterministic paramset/grid yang dibekukan sebelum outcome dibaca.

## A. Point-in-Time and Universe Rules

- Universe evaluation mengikuti Weekly Swing eligibility pada historical `asof_eod_date`.
- Backtest tidak boleh memakai future identity/status/sector/corporate-action knowledge.
- Historical requested date/publication yang tidak tersedia tetap explicit missing/insufficient evidence; tidak boleh diganti current publication atau prior-date fallback tanpa label/identity yang memang menjadi replay request.
- Watchlist tidak boleh reconstruct indicator, benchmark, status, sector, atau producer data-usability dari Market Data internal tables.
- Trading day mengikuti canonical exchange calendar.
- Historical source snapshot harus menghasilkan PLAN dan RECOMMENDATION yang sama pada replay.

## B. Selection Object Under Test

Urutan evaluation per trade date:

`Market Data → PLAN → final RECOMMENDATION/TOP_PICKS → price evaluation`

Hanya final Top Picks yang menjadi canonical evaluated trades.

`RECOMMENDATION_CANDIDATES` yang tidak lulus final recommendation tidak boleh masuk canonical recommendation return distribution.

## C. Entry Model

- PLAN dan recommendation dibentuk pada trading day `D` setelah valid EOD input tersedia.
- Earliest canonical entry adalah next trading day `D+1`.
- Canonical entry price adalah executable `open(D+1)`.
- Jika valid executable open tidak tersedia, trade di-skip; fallback ke later close sebagai pengganti open dilarang pada canonical proof.

## D. Executable-Bar Rule

- Published/readable EOD row dan executable trading bar adalah konsep berbeda.
- Entry/exit hanya memakai bar dengan harga sah dan positive tradable volume.
- Missing/non-executable bar tidak boleh menghasilkan synthetic fill atau synthetic zero return.
- Trade **tanpa valid pre-entry executable fill** boleh di-skip dengan reason yang deterministic; trade yang **sudah mempunyai valid entry fill tidak boleh di-skip** hanya karena exit berikutnya non-executable.
- Skip reason harus dapat dihitung dan direplay.

## D1. Operational Entry-Time Validity

Canonical historical `open(D+1)` fill hanya valid bila point-in-time evidence menunjukkan EOD publication/recommendation dapat tersedia sebelum governed `D+1` earliest entry time dengan minimum decision lead time **30 minutes**.

Jika availability timing tidak dapat dibuktikan pada historical data, historical return proof tetap dapat menilai selection mechanics tetapi **tidak sendirian membuktikan operational D+1-open executability**; operational timing wajib dibuktikan pada forward shadow.

Jika recommendation secara faktual terlambat pada shadow/live-equivalent run, earlier open tidak boleh dipakai sebagai synthetic fill. Later fill hanya boleh dipakai bila separately frozen causal execution rule dan adequate intraday/current data tersedia; otherwise trade adalah pre-entry non-executable.

## D2. Post-Entry Non-Executability / Suspension Resolution

Sekali canonical entry fill terjadi, trade menjadi **committed evaluation exposure** dan wajib tetap berada dalam denominator/result lineage sampai economically resolved.

Jika predeclared exit jatuh pada bar/session yang tidak executable karena suspension, zero tradable volume, unavailable Regular-Market execution, delisting transition, atau equivalent market constraint:

1. desired exit timestamp/reason tetap direkam;
2. exit execution ditunda ke **first later point-in-time executable Regular-Market opportunity** yang sah, menggunakan first causal executable price sesuai frozen execution rule;
3. actual holding extension dan `non_executable_exit_days` wajib dilaporkan;
4. trade tetap masuk return, downside, tail-risk, dan ranking statistics;
5. official source-backed cash settlement/recovery dapat dipakai bila memang merupakan economic resolution yang causal dan lebih tepat daripada exchange fill.

Jika exposure belum mempunyai executable/official economic resolution sampai evaluation cutoff, trade diberi state `UNRESOLVED_POST_ENTRY_EXPOSURE` dan untuk production-qualification acceptance metrics diperlakukan konservatif sebagai **`ret_net = -100%`**. Unresolved exposure count harus dilaporkan terpisah dan tidak boleh disembunyikan melalui sample exclusion.

Maximum holding horizon 5 trading day adalah **strategy intent under normal executability**. Forced extension akibat market non-executability adalah execution-risk outcome, bukan izin untuk strategy memilih holding period baru berdasarkan future return.

## E. Weekly Swing Exit Horizon and Causality

Maximum holding horizon adalah **5 trading day sejak entry**.

Satu evaluation identity harus memakai tepat satu predeclared exit policy. Runtime tidak boleh memilih exit model berdasarkan future outcome.

Canonical Weekly Swing membatasi exit policy ke dua causal families yang sudah dapat diuji secara eksplisit:

### 1. STOP_TARGET_TIME

- stop dan target ditetapkan sebelum entry;
- opening gap dievaluasi sebelum intraday high/low;
- jika stop dan target sama-sama tersentuh pada bar yang sama dan urutan tidak observable, assumption harus konservatif;
- bila tidak ada exit sebelumnya, fallback paling lambat pada executable close hari kelima.

### 2. SEQUENTIAL_SIGNAL_NEXT_OPEN

- profit/loss signal threshold ditetapkan sebelum entry;
- signal hanya boleh memakai informasi yang sudah published pada close hari tersebut;
- signal pada close `Dn` paling cepat dieksekusi pada executable open `D(n+1)`;
- future return tidak boleh dipakai untuk memilih route;
- bila tidak ada signal exit, fallback paling lambat pada executable close hari kelima.

Hanya satu family boleh menjadi active production strategy identity pada satu waktu. Perubahan family adalah evaluation-breaking change dan membutuhkan proof baru.

## F. Executable Price and IDX Fraction

Entry/exit memakai raw tradable OHLC, bukan adjusted/fractional synthetic prices.

Theoretical price level harus diterjemahkan ke valid executable IDX price fraction secara konservatif bila exit policy membutuhkan level harga. Tick/fraction tier, minimum Regular-Market price, dan price-band fact yang dipakai harus berasal dari effective-dated Market Data market-structure authority valid pada trade date; current exchange tier tidak boleh di-hardcode mundur ke histori.

Market Data memiliki fakta struktur exchange; Watchlist memiliki cara menerapkannya untuk executable-price evaluation. Fakta band/tick tidak boleh dipakai sebagai alpha score kecuali suatu strategy revision secara eksplisit mengadopsinya dengan proof identity baru.

Jika executable **entry** price tidak dapat dibuktikan, trade fail-closed sebagai pre-entry non-executable. Jika masalah executability muncul setelah valid entry, resolution mengikuti `D2` dan exposure tidak boleh dihapus dari return distribution.

## G. Trade-Plan Risk Binding

Evaluation memakai exact predeclared trade plan yang terikat pada final recommendation.

- STOP_TARGET_TIME wajib mempunyai valid stop, target, dan minimum risk/reward.
- SEQUENTIAL_SIGNAL_NEXT_OPEN wajib mempunyai predeclared profit/loss thresholds, next-open routing, dan bounded D5 fallback.
- Exit rule tidak boleh dibuat ulang setelah entry atau dipilih berdasarkan realized future path.

## H. Trading Cost and Slippage

Production qualification tidak boleh menggunakan frictionless assumption.

Canonical production-use proof harus mempunyai explicit **production cost profile** yang merepresentasikan intended real trading account, minimal:
- all-in buy transaction cost;
- all-in sell transaction cost;
- non-zero adverse entry slippage;
- non-zero adverse exit slippage.

Cost/slippage harus dinyatakan dalam deterministic versioned terms dan diterapkan sebelum `ret_net` dihitung.

Zero-slippage atau simplified fixed-cost run boleh dipertahankan untuk diagnostic/legacy comparison, tetapi **tidak dapat menjadi satu-satunya production qualification proof**.

Selain baseline production cost profile, strategy harus mempunyai adverse-friction stress profile yang lebih konservatif. Candidate untuk real use harus tetap mempunyai positive net-return expectation pada stress proof.

## I. Capital Independence

Evaluation notional boleh dibakukan untuk reproducibility dan lot executability, tetapi notional tidak boleh mengubah recommendation membership atau rank.

Affordability user tertentu bukan bagian dari strategy-selection proof.

## J. Return Semantics

- `ret_net` dihitung setelah seluruh canonical buy/sell cost dan slippage;
- win berarti `ret_net > 0`;
- seluruh final Top Pick dengan valid entry fill masuk return distribution sampai economically resolved; hanya pre-entry non-executable trade yang boleh di-skip;
- no-recommendation date adalah valid strategy outcome dan tidak diberi synthetic trade.

## K. Statistical, Economic, Benchmark, and Tail-Risk Metric Inputs

Historical evaluator wajib menghasilkan raw inputs untuk downstream acceptance berikut tanpa memilih winner:

### Statistical uncertainty

- date-clustered / block-bootstrap distribution yang menjaga picks pada recommendation date yang sama tetap satu dependency cluster;
- `avg_ret_net` 95% confidence interval;
- benchmark-relative mean 95% confidence interval;
- explicit recommendation-day count sebagai independent evidence unit selain raw trade count.

### Economic significance

Canonical minimum economic edge floor untuk baseline production proof adalah:

`avg_ret_net_top >= +0.0025` (**+0.25% net per executable Top Pick**) setelah baseline production friction.

Floor ini tidak boleh diturunkan setelah outcome dibaca. Strategy tetap harus memenuhi confidence and benchmark tests; average di atas +0.25% saja tidak cukup.

### Benchmark / selection uplift

Untuk setiap executable Top Pick, evaluator harus menghasilkan causal matched-horizon:

- `excess_ret_vs_ihsg` menggunakan primary point-in-time IHSG benchmark bila required producer input tersedia;
- `selection_uplift_vs_eligible_universe` terhadap deterministic same-date eligible-universe baseline menggunakan exact causal entry/horizon rule.

Benchmark/universe baseline tidak boleh menggunakan future constituent knowledge atau current universe back-projection.

### Tail and path risk

Minimum raw metrics:

- `p05_ret_net_top`;
- `expected_shortfall_05_ret_net_top`;
- maximum adverse excursion (`MAE`) dan distribution-nya;
- `max_consecutive_losing_trades` dan `max_consecutive_losing_recommendation_days`;
- date-level equal-reference-notional equity curve maximum drawdown;
- count/duration of post-entry non-executable exit extensions.

Date-level equal-reference-notional curve hanya proof diagnostic untuk clustering/downside dan **bukan portfolio-construction recommendation**.

## L. Ranking Proof

Evaluation tidak cukup hanya membuktikan aggregate Top Picks return.

Evidence juga harus menunjukkan bahwa canonical recommendation ranking mempunyai utility, minimal:
- rank #1 net-return expectation tidak negatif;
- rank ordering tidak menunjukkan systematic inversion di mana lower-ranked recommendations secara konsisten lebih baik daripada higher-ranked recommendations;
- hubungan recommendation score dengan realized net return harus dihitung dan dilaporkan.

## M. Historical Evaluation Preparation

`WS-S06` menyiapkan outcome yang sah untuk downstream proof tanpa memilih winner atau membaca outcome stage berikutnya.

Urutan wajib:

1. freeze historical window, Market Data identity, strategy identity, production cost/slippage baseline, dan deterministic candidate grid sebelum outcome evaluation dimulai;
2. replay exact eligibility → PLAN → final RECOMMENDATION logic pada designated historical dates;
3. menghasilkan executable final-Top-Pick outcomes, no-recommendation dates, skipped-trade facts, dan ranking data yang dibutuhkan acceptance metrics;
4. menjaga setiap candidate/evaluation identity terpisah dan reproducible;
5. menyerahkan IS outcomes yang lengkap ke `WS-S07` tanpa memilih pemenang berdasarkan OOS, stress, atau shadow information.

`WS-S06` tidak memiliki authority untuk menurunkan acceptance floor, memilih best-IS winner, membaca untouched OOS, atau mengubah strategy berdasarkan downstream outcome.

## Evaluation Objective

Calibration mencari robust Weekly Swing behavior, bukan maximum average return tunggal.

Objective mencakup:
- positive net-return expectation after realistic friction;
- robust median/distribution return;
- bounded downside;
- period stability;
- sufficient sample/coverage;
- useful recommendation ranking;
- deterministic replay.

## Failure Rules

Historical evaluation gagal sebagai valid `WS-S06` outcome bila:
- point-in-time replay tidak valid;
- exact PLAN/final RECOMMENDATION equivalence tidak dapat dibuktikan;
- required executable entry/exit price tidak dapat divalidasi secara causal;
- production baseline cost/slippage profile tidak terikat secara deterministik;
- required downstream metric inputs tidak dapat dihasilkan;
- evaluation identity berubah di tengah replay tanpa identity baru.

IS, OOS, adverse-friction, atau required core-shadow gate failure adalah verdict stage berikutnya dan menghentikan core progression; kegagalan tersebut tidak boleh dipakai untuk mengubah historical evaluation rules secara post-hoc. Optional CONFIRM evidence insufficiency hanya membatasi status capability CONFIRM dan tidak menghentikan core Top-Picks proof.
