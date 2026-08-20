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

Jika recommendation secara faktual terlambat pada shadow/live-equivalent run, earlier open tidak boleh dipakai sebagai synthetic fill. Canonical EOD proof memperlakukan opportunity tersebut sebagai pre-entry non-executable. Later fill hanya dapat menjadi separately versioned optional execution capability bila governed decision-time data tersedia; capability tersebut bukan requirement core Weekly Swing.

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
- opening gap dievaluasi sebelum daily-bar high/low touch logic;
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


## Historical Modeled-Execution Boundary

Canonical historical evaluator membuktikan **EOD recommendation edge under a conservative modeled execution**, bukan actual broker/user fill.

- `open(D+1)` adalah causal next-session **reference price** setelah recommendation EOD `D`; ia bukan bukti bahwa seluruh pengguna memperoleh exact exchange open.
- Canonical modeled entry/exit menggabungkan frozen reference price rule dengan explicit fee, adverse slippage, capacity, market-structure, dan executability assumptions sebelum `ret_net` dihitung.
- Core historical proof **MUST NOT** membutuhkan realtime/intraday/orderbook history. Bila EOD information tidak cukup untuk mengetahui queue/fill secara exact, evaluator wajib menggunakan conservative uncertainty treatment atau pre-entry non-executable state sesuai frozen rules, bukan synthetic precision.
- Daily OHLC `high/low` yang sudah menjadi historical EOD bar boleh dipakai untuk mengetahui bahwa stop/target level tersentuh; penggunaan daily-bar facts tersebut bukan intraday-feed dependency. Bila urutan stop/target dalam bar yang sama tidak observable, assumption harus konservatif.
- Forward shadow membuktikan bahwa recommendation tersedia secara operasional sebelum intended entry opportunity; shadow tidak diwajibkan membaca orderbook untuk membuktikan core EOD product.
- Evaluation output **MUST** membedakan `reference_price` / `modeled_entry_price` / `modeled_exit_price` dari `actual_investor_fill`; field terakhir tidak boleh diklaim tanpa separate actual-execution dataset dan tidak menjadi prerequisite core strategy proof.
- Actual user/broker fill yang berbeda dari modeled execution adalah execution realization di luar core strategy authority dan tidak boleh digunakan post-hoc untuk memilih strategy rule yang lebih menguntungkan.

## Historical Market-Fact Ownership and Replay Gap

Backtest yang high-trust harus memakai fakta yang benar-benar dimiliki producer pada historical identity, bukan membangun dataset alternatif di evaluator.

- Historical evaluator **MUST** menggunakan exact/as-known producer-facing market facts dan **MUST NOT** reconstruct indicator, adjustment, sector, status, benchmark, calendar, corporate-action, liquidity, atau market-structure fact dari raw/internal/current data.
- Required historical market fact yang missing pada replay identity menghasilkan missing/`INSUFFICIENT EVIDENCE` untuk affected sample/gate; evaluator tidak boleh melakukan local historical backfill agar sample menjadi lengkap.
- Evaluator boleh menghitung strategy-specific outcomes seperti modeled entry/exit, matched-horizon return, benchmark excess return, net return, MAE/path/tail metrics, dan Top-K statistics dari authoritative underlying facts karena meaning output tersebut terikat pada Weekly Swing evaluation identity.
- Producer correction/revision hanya masuk historical evaluation melalui governed producer lineage/replay identity; Watchlist tidak boleh repair historical market facts sendiri atau memilih corrected/current value secara post-hoc.
- Setiap evaluated recommendation/outcome harus tetap traceable ke exact Market Data publication/replay identity yang menyediakan underlying facts sehingga local shadow dataset tidak menjadi alternate source of truth.

## Next-Trading-Session and Publication-Latency Causality

Historical evaluator harus memakai governed trading-session sequence, bukan calendar arithmetic.

- Canonical entry reference session adalah `NEXT_TRADING_SESSION(effective_trade_date)` dari historical point-in-time Market Data calendar; Friday-to-Monday/holiday gaps ditangani sebagai next session, bukan `+1 day`.
- Weekday tidak mempunyai intrinsic buy/sell preference pada baseline evaluator; exit tetap mengikuti stop/target/time-exit identity.
- `NEXT_OPEN` berarti RAW open pada governed next trading session sebagai **modeled reference**, bukan exact investor fill.
- Jika recommendation secara faktual/operasional baru tersedia setelah canonical entry cutoff, evaluator **MUST NOT** memakai open yang telah lewat sebagai causal modeled fill.
- Late recommendation tidak boleh otomatis digeser ke session berikutnya untuk menyelamatkan trade; later-entry rule hanya sah bila preregistered strategy identity secara eksplisit mendefinisikan dan membuktikannya.
- Historical strategy-quality replay dan operational-timeliness proof harus dibedakan: bila historical producer publication timestamp tidak tersedia, evaluator tidak boleh mengarang timestamp/cutoff PASS; operational timeliness tersebut harus berstatus `NOT_PROVEN_HISTORICALLY` dan dibuktikan melalui forward shadow/live-equivalent evidence sebelum production-use approval.
- Historical evidence harus menyimpan `effective_trade_date`, modeled `intended_entry_session`, dan availability/cutoff state yang digunakan sehingga replay dapat membuktikan bahwa tidak ada stale-date masquerade atau expired-open reuse.

## Real-World Execution, Economic-Return, and Followability Model

### Supported execution mechanism

- Canonical modeled entry/exit **MUST** hanya menggunakan historical recommendation/session yang authoritative execution-mode fact-nya berada pada frozen supported-mode set; unsupported/unknown mechanism tidak boleh diasumsikan setara hanya karena EOD OHLCV tersedia.
- Jika execution mode berubah menjadi unsupported/non-executable setelah recommendation tetapi sebelum canonical entry, opportunity menjadi pre-entry non-executable dengan deterministic reason dan tidak menerima synthetic fill.
- Jika execution mode menjadi non-executable setelah valid entry, committed exposure tetap mengikuti post-entry resolution rule dan tidak boleh dihapus dari denominator.
- Historical evaluator **MUST NOT** membangun orderbook/queue simulation untuk menutup execution-mode uncertainty yang tidak tersedia dari EOD facts.

### Condition-dependent EOD slippage

Canonical production slippage model menggunakan frozen adverse function:

`modeled_slippage_bps = max(min_slippage_bps, base_bps + liquidity_addon_bps + volatility_addon_bps + participation_addon_bps + tick_addon_bps)`

- Seluruh component mapping/threshold/bps **MUST** versioned dan dibekukan sebelum outcome evaluation; OOS/shadow outcome tidak boleh dipakai untuk memilih tier yang lebih menguntungkan.
- `liquidity_addon_bps` hanya boleh berasal dari authoritative liquidity fact; liquidity yang lebih buruk **MUST NOT** menurunkan adverse slippage tier.
- `volatility_addon_bps` hanya boleh berasal dari authoritative EOD volatility fact; volatility yang lebih tinggi **MUST NOT** menurunkan adverse slippage tier.
- `participation_addon_bps` menggunakan frozen `reference_order_notional_idr` terhadap authoritative liquidity/capacity denominator; participation yang lebih tinggi **MUST NOT** menurunkan adverse slippage tier.
- `tick_addon_bps` menggunakan effective-dated authoritative price/tick facts dan **MUST** konservatif terhadap coarse tick burden.
- Entry slippage diterapkan adverse terhadap buyer dan exit slippage adverse terhadap seller; zero/minimum slippage hanya boleh terjadi sesuai frozen non-zero production floor.
- Missing required slippage input **MUST NOT** diberi default optimistis; affected production-proof outcome menjadi insufficient/non-executable sesuai fact criticality.
- Adverse-friction stress **MUST** memakai component assumptions yang tidak lebih ringan daripada baseline production profile.

### Corporate-action economic return

- Price-path evaluation dan economic-return evaluation **MUST** dibedakan: stop/target/executability tetap memakai canonical tradable-price semantics, sedangkan `ret_net` harus mencerminkan economic cash/quantity entitlement yang sah selama exposure.
- Cash dividend/distribution **MUST** dimasukkan sebagai economic proceeds bila authoritative point-in-time corporate-action facts membuktikan hypothetical position berhak menerima distribution tersebut; payment date setelah exit tidak menghapus entitlement yang sudah terbentuk.
- Structural corporate action yang mengubah share quantity atau economic price basis **MUST** menggunakan authoritative producer terms untuk menjaga economic continuity; Watchlist tidak boleh menghitung adjustment factor alternatif dari raw prices.
- Elective/complex corporate action yang tidak mempunyai frozen deterministic Weekly Swing treatment **MUST** menghasilkan `CORPORATE_ACTION_TREATMENT_UNSUPPORTED` untuk affected production-proof exposure dan tidak boleh disederhanakan post-hoc berdasarkan hasil terbaik.
- Missing/incoherent required corporate-action facts menghasilkan `EVALUATION_DATA_INCOMPLETE_CORPORATE_ACTION`; affected sample tidak boleh dipakai untuk mengklaim production qualification sampai authoritative facts tersedia.
- Corporate-action treatment **MUST** causal dan terikat exact producer replay/revision identity; future/current master knowledge tidak boleh digunakan mundur.

### Repeated signal versus follower replay

- `SIGNAL_QUALITY` dataset boleh mempertahankan setiap issued daily Top Pick sebagai recommendation observation, termasuk repeated same-listing recommendation, karena objective-nya menilai kualitas signal per EOD session.
- `FOLLOW_TOP1`, `FOLLOW_TOP3`, dan `FOLLOW_TOP5` **MUST** dibangun sebagai separate deterministic follower-replay views untuk mendekati cara output dapat diikuti secara manual tanpa mengubah core recommendation engine.
- Untuk `FOLLOW_TOPK`, bila current EOD mempunyai qualified Top Picks kurang dari `K`, replay **MUST** memakai hanya seluruh Top Picks yang benar-benar tersedia; no-pick/short-list tidak boleh dipadding dengan non-qualified candidate.
- Dalam follower replay, satu listing **MUST NOT** membuka exposure kedua selama hypothetical exposure sebelumnya pada listing yang sama masih committed/open; recommendation berikutnya dicatat sebagai `REPEAT_WHILE_EXPOSED` tanpa new fill.
- Setelah prior same-listing exposure economically closed, new exposure hanya boleh dibuka dari later independently valid EOD recommendation yang action window-nya sah; prior signal tidak boleh carry-forward.
- Follower replay menggunakan frozen equal `reference_order_notional_idr` per newly opened exposure, tanpa optimization, rebalancing, cash-allocation search, leverage tuning, atau affordability personalization.
- Concurrent different-listing exposures boleh terjadi secara mekanis dari Top-K flow dan **MUST** dilaporkan melalui `max_concurrent_positions`, `peak_reference_capital_required`, holding duration, net return, drawdown, dan loss-streak metrics; metric ini adalah proof diagnostic, bukan portfolio advice.
- Follower-replay return **MUST** memakai execution, corporate-action, friction, stop/target/time-exit, non-executable resolution, dan Market Data identity yang sama dengan canonical trade evaluation.

### Shortened / half-day session consistency

- Historical replay **MUST** menggunakan producer-owned session type dan exact same session-comparability rule yang dipakai runtime; historical evaluator tidak boleh menormalkan volume/session-sensitive features sendiri.
- Recommendation yang seharusnya `WATCH_ONLY` karena `SESSION_FEATURE_COMPARABILITY_UNPROVEN` **MUST NOT** muncul sebagai historical Top Pick hanya untuk menambah sample.
- Jika producer semantics secara explicit menyatakan active session-sensitive features comparable pada shortened session, trade boleh dievaluasi normal dan session type tetap dilaporkan sebagai diagnostic context.

### Edge-concentration raw stress inputs

Evaluator **MUST** menghasilkan deterministic stress views yang mempertahankan frozen strategy tetapi menghapus outcome group setelah canonical trade outcomes terbentuk:

- remove best-profit ticker;
- remove best-profit sector;
- remove best-profit calendar month;
- remove best-profit recommendation date cluster;
- trim top 1% winning trades by `ret_net` with deterministic tie handling;
- diagnostic trim top 5% winning trades.

Setiap stress view **MUST** melaporkan remaining trade count, recommendation-day count, avg/median net return, confidence interval bila computable, dan downside metrics; stress view tidak boleh dipakai untuk memilih blacklist ticker/sector/month baru.

### Producer-correction sensitivity inputs

- Jika Market Data mempunyai explicit correction/revision pair untuk historical recommendation date, evaluator **MUST** dapat replay original as-known publication dan corrected publication dengan exact strategy identity yang sama.
- Paired correction replay **MUST** menghasilkan `classification_flip`, `top1_flip`, `top3_membership_flip`, `action_intent_flip`, dan absolute `score_shift` tanpa mengubah thresholds/weights di antara pair.
- Correction-sensitivity dataset hanya menggunakan genuine producer revision lineage; absence of correction **MUST NOT** diganti synthetic perturbation dan synthetic perturbation tidak boleh disebut producer-correction evidence.


## Canonical Same-Bar Ambiguity and Replay Determinism

- Setelah valid long entry, bila daily EOD bar menunjukkan `low <= stop` dan `high >= target` tetapi canonical EOD facts tidak menentukan event order, evaluator **MUST** resolve trade sebagai `STOP_FIRST` untuk production-qualification proof.
- Evaluator **MUST NOT** menggunakan daily close, jarak level dari open, asumsi path `open→high→low→close`, atau heuristic lain untuk memilih target-first ketika sequence tidak observable.
- Jika canonical opening/gap rule secara deterministic sudah menyelesaikan event sebelum same-bar ambiguity muncul, opening/gap rule **MUST** diterapkan terlebih dahulu; `STOP_FIRST` berlaku pada residual within-bar order yang tidak observable.
- Same-bar resolution identity **MUST** sama pada IS, OOS, adverse-friction stress, follower replay, dan forward-equivalent historical evaluation; evaluator tidak boleh memakai optimistic rule hanya pada stage tertentu.
- Exact Market Data revision, strategy/parameter identity, cost/slippage identity, evaluation window, corporate-action treatment, and same-bar policy **MUST** menghasilkan identical canonical trade outcomes dan aggregate metrics pada replay.
- Evaluation precision/rounding/tie handling yang dapat memengaruhi trigger, return, ranking bucket, confidence input, atau verdict **MUST** frozen/versioned; platform-dependent floating behavior tidak boleh membuat proof berubah.
- Any same-identity evaluation replay yang menghasilkan materially different trade outcomes/metrics **MUST** berstatus `NON_DETERMINISTIC_EVALUATION` dan tidak dapat digunakan sebagai IS/OOS/production proof sampai root cause ditutup.
