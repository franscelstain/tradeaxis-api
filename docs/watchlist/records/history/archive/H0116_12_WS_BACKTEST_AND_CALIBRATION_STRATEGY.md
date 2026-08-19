# 12 — WS Backtest & Calibration Strategy

## Purpose

Backtest dan calibration harus membuktikan behavior yang benar-benar dipakai pengguna: **final qualified RECOMMENDATION/TOP_PICKS**, bukan PLAN candidate state sebagai proxy.

Evaluation harus point-in-time, reproducible, executable-price aware, dan net of realistic trading friction.

Default evaluation window adalah 2 tahun. Window berbeda harus mempunyai evaluation identity berbeda.

## Inputs

Evaluation menggunakan:
- historical authoritative Market Data EOD publication/read product;
- trading calendar yang sah;
- exact Weekly Swing strategy identity;
- deterministic paramset/grid yang dibekukan sebelum outcome dibaca.

## A. Point-in-Time and Universe Rules

- Universe evaluation mengikuti Weekly Swing eligibility pada historical `asof_eod_date`.
- Backtest tidak boleh memakai future identity/status/sector/corporate-action knowledge.
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

Theoretical price level harus diterjemahkan ke valid executable IDX price fraction secara konservatif bila exit policy membutuhkan level harga.

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

## L. Calibration Flow

1. freeze historical window, Market Data identity, strategy identity, cost/slippage profile, dan deterministic candidate grid;
2. replay exact PLAN + RECOMMENDATION logic pada IS;
3. hitung minimum sufficiency, return, risk, stability, dan ranking metrics;
4. hanya candidate strategy/paramset yang melewati seluruh IS floor yang dapat diranking;
5. freeze satu best-IS binding;
6. evaluate exact frozen binding pada untouched OOS tanpa retuning;
7. evaluate adverse-friction stress proof;
8. hanya strategy yang lulus OOS + stress dapat diteruskan ke forward shadow validation.

## Calibration Objective

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

Evaluation gagal sebagai production qualification bila:
- point-in-time replay tidak valid;
- PLAN atau final RECOMMENDATION equivalence tidak dapat dibuktikan;
- required metrics tidak tersedia;
- IS gate gagal;
- OOS gate gagal;
- adverse-friction stress menghilangkan positive expected edge;
- ranking terbukti systematically inverted;
- evaluation identity berubah tanpa proof baru.
