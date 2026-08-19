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
- Trade tanpa valid entry atau valid exit di-skip dan tidak masuk return distribution.
- Skip reason harus dapat dihitung dan direplay.

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

Jika executable price tidak dapat dibuktikan, evaluation fail-closed untuk trade tersebut.

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
- only executable final Top Pick trades masuk return distribution;
- no-recommendation date adalah valid strategy outcome dan tidak diberi synthetic trade.

## K. Ranking Proof

Evaluation tidak cukup hanya membuktikan aggregate Top Picks return.

Evidence juga harus menunjukkan bahwa canonical recommendation ranking mempunyai utility, minimal:
- rank #1 net-return expectation tidak negatif;
- rank ordering tidak menunjukkan systematic inversion di mana lower-ranked recommendations secara konsisten lebih baik daripada higher-ranked recommendations;
- hubungan recommendation score dengan realized net return harus dihitung dan dilaporkan.

## L. Historical Evaluation Preparation

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
