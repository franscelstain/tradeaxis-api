# Watchlist Weekly Swing — Product Objective and Layers

## Purpose

Weekly Swing watchlist bertujuan mengubah Market Data EOD yang dapat dipercaya menjadi daftar saham yang benar-benar layak dipertimbangkan untuk pembelian swing, lalu mengurutkan kandidat yang lulus menjadi **Top Picks** dari kualitas tertinggi ke terendah.

Weekly Swing mempunyai dua core layer dan satu optional enhancement:

1. **PLAN** — membentuk dan memprioritaskan candidate setup;
2. **RECOMMENDATION** — menetapkan qualified recommendation dan final Top Picks ranking;
3. **CONFIRM (optional)** — bila valid decision-time data tersedia, memeriksa apakah Top Pick masih actionable ketika pengguna hendak mengambil keputusan beli.

## Lifecycle Position

- **Stage:** `WS-S00` — Scope and Success Lock.
- **Consumes:** Weekly Swing product intent under the scope lock.
- **Produces:** canonical product objective, layer meaning, dan final recommendation semantics.
- **Next:** `WS-S01` trusted Market Data binding.

## Upstream Contract Boundary

Weekly Swing does not own market-fact meaning. `WS-S01` is governed by `WS_MARKET_DATA_INPUT_REQUIREMENTS.md`: current EOD Top Picks may only originate from a readable, fresh, same-date producer-facing Market Data read product. Market Data `data_usable` is only an upstream integrity prerequisite; candidate eligibility, thresholds, score, rank, and recommendation remain Weekly Swing policy.

## Product Objective

Strategy mengutamakan **quality over quantity**.

Keberhasilan Weekly Swing tidak diukur dari banyaknya saham yang muncul, tetapi dari kemampuan strategy untuk:
- menolak kandidat yang tidak cukup layak;
- menghasilkan recommendation dengan positive expected net return setelah realistic trading friction;
- menjaga downside dan period stability;
- membuat rank lebih tinggi merepresentasikan kualitas yang setidaknya tidak lebih buruk daripada rank lebih rendah;
- menghasilkan output deterministik dan dapat direplay;
- mengizinkan no-trade ketika market tidak menyediakan peluang yang cukup baik;
- tetap menyelesaikan core Top Picks walaupun optional CONFIRM belum tersedia.

## Canonical Naming

Istilah **TOP PICKS** hanya digunakan untuk **final qualified RECOMMENDATION**.

PLAN menggunakan candidate states:
- `RECOMMENDATION_CANDIDATES`;
- `WATCH_ONLY`;
- `AVOID`.

PLAN candidate state tidak boleh dibaca sebagai final buy recommendation.

## Weekly Swing Architecture

Core sequence:

1. PLAN dibentuk dari authoritative EOD input dan candidate eligibility/setup strategy;
2. PLAN menjadi immutable;
3. RECOMMENDATION mengevaluasi candidate PLAN dan menghasilkan seluruh qualified Top Picks;
4. Top Picks diurutkan secara deterministik berdasarkan canonical quality score.

Optional current-actionability branch:

5. pada intended next-trading-day entry session, CONFIRM **dapat** mengevaluasi current actionability dari Top Pick bila valid current data tersedia, tanpa menulis ulang historical EOD recommendation.

Tidak adanya CONFIRM tidak mengubah core sequence menjadi gagal atau incomplete.

## Recommendation Meaning

Sebuah ticker hanya disebut **Top Pick** bila ticker tersebut:
- berasal dari PLAN candidate yang sah;
- melewati seluruh hard eligibility/risk/data gate;
- melewati final recommendation quality floor;
- memiliki predeclared trade plan yang sah;
- memenuhi exit-policy-specific risk requirement;
- kemudian diurutkan menggunakan canonical recommendation ranking.

Jumlah Top Picks adalah jumlah aktual ticker yang lulus seluruh gate. Tidak ada kewajiban mengisi jumlah minimum atau maksimum hanya untuk kebutuhan tampilan.

## Confirm Meaning

CONFIRM tidak memilih saham baru dan tidak mengubah EOD Top Picks ranking.

CONFIRM hanya menjawab pertanyaan tambahan:

> bila decision-time data yang sah tersedia, apakah Top Pick yang sudah direkomendasikan masih memenuhi current-entry conditions?

Top Pick tetap merupakan qualified EOD recommendation ketika CONFIRM:
- belum diminta (`NOT_REQUESTED`);
- belum dapat dievaluasi karena data belum tersedia (`UNAVAILABLE_RETRYABLE`);
- entry window selesai sebelum valid evaluation tersedia (`EXPIRED_UNCONFIRMED`).

`NOT_ACTIONABLE` hanya sah bila valid current data tersedia dan active actionability rule benar-benar gagal.

`UNAVAILABLE_RETRYABLE` bersifat non-terminal selama canonical entry window masih terbuka. Ketika valid data kemudian tersedia, CONFIRM dapat dievaluasi ulang dan menghasilkan `ACTIONABLE` atau `NOT_ACTIONABLE`.

CONFIRM menambah keyakinan current-entry; ia bukan prerequisite untuk membentuk, menyimpan, menilai, atau membuktikan core EOD Top Picks.

## Score and User-Facing Confidence Semantics

Canonical `score_total` / `recommendation_score` adalah **ordinal quality score**, bukan probability of profit. Nilai `0.80` tidak boleh ditampilkan atau ditafsirkan sebagai peluang untung `80%`.

Jika product kelak ingin menampilkan probability/confidence percentage, capability tersebut harus mempunyai separately versioned calibration identity dan dibuktikan pada untouched OOS/forward data dengan reliability/calibration evidence. Probability display tidak boleh dibuat dengan transform kosmetik dari `score_total`.

## User-Visible Top-K Semantics

Strategy membership tetap seluruh qualified Top Picks `1..N`. UI boleh menonjolkan Top-1, Top-3, atau Top-5, tetapi subset yang secara rutin menjadi fokus pengguna harus mempunyai proof tersendiri pada exact ranking yang sama. Presentation subset tidak boleh dipilih setelah return outcome diketahui.

## Operational Entry Availability Principle

Qualified EOD recommendation baru dapat dianggap mempunyai canonical `D+1 open` execution opportunity bila recommendation telah dipublikasikan sebelum effective-dated market entry cutoff dengan minimum decision lead time yang dipersyaratkan strategy. Recommendation yang terlambat tetap merupakan EOD information record, tetapi tidak boleh diberi synthetic historical/live fill pada harga yang sudah tidak dapat dicapai pengguna.

## Final Rules

1. PLAN menghasilkan candidates, bukan final Top Picks.
2. RECOMMENDATION adalah owner final Top Picks.
3. Top Picks boleh kosong dan jumlahnya tidak dipaksa oleh quota.
4. Rank #1 adalah qualified recommendation dengan canonical quality ordering tertinggi, bukan sekadar item pertama dari PLAN.
5. Capital/affordability tidak menentukan kualitas, membership, atau rank Top Picks.
6. CONFIRM optional dan tidak mengubah recommendation history, membership, score, atau rank.
7. Missing/stale/incomplete CONFIRM data menghasilkan availability state, bukan strategy failure atau synthetic `NOT_ACTIONABLE`.
8. Recommendation yang tidak dieksekusi pada canonical entry window tidak otomatis menjadi new-entry signal pada hari berikutnya.


## EOD Recommendation Truth vs Execution Truth

Weekly Swing membedakan tiga hal yang tidak boleh dicampur:

1. **Recommendation truth** — apakah ticker secara sah menjadi final Top Pick berdasarkan point-in-time EOD facts pada `D`;
2. **Historical modeled execution** — bagaimana outcome strategy diuji secara konservatif menggunakan frozen next-session reference price, fee, slippage, capacity, dan executability rules;
3. **Actual investor execution** — harga/order/fill nyata yang dipilih atau diperoleh pengguna/broker, yang berada di luar core Watchlist authority.

Core product berakhir pada ranked EOD Top Picks beserta PLAN/risk information untuk keputusan manual. Watchlist **MUST NOT** menyatakan modeled historical entry/exit sebagai actual investor fill.

Optional CONFIRM, bila kelak tersedia, hanya menambah decision-time actionability information dan tidak mengubah tiga-layer boundary tersebut atau menjadikan realtime/orderbook sebagai core dependency.

## Market Fact vs Weekly Swing Strategy Output Test

Setiap kebutuhan data/perhitungan baru wajib diklasifikasikan sebelum diimplementasikan.

- Jika suatu nilai tetap mempunyai arti pasar yang sama walaupun Weekly Swing tidak ada atau paramset Weekly Swing berubah, nilai tersebut diperlakukan sebagai **Market Data fact** dan bukan output Watchlist.
- Jika formula/semantic value didefinisikan oleh producer registry/contract atau menggambarkan keadaan instrument/market/session, owner-nya tetap Market Data walaupun formula teknisnya sederhana.
- Jika meaning output bergantung pada frozen Weekly Swing threshold, weight, trade-plan, execution, atau evaluation identity, output tersebut adalah strategy calculation yang boleh dimiliki Watchlist selama seluruh market input-nya authoritative.
- Jika ownership masih ambigu, default decision adalah **tidak menghitung lokal** dan membuka `UPSTREAM_MARKET_DATA_DEPENDENCY_GAP` sampai ownership/producer contract dipastikan.

## EOD Recommendation Action Intent

Final Top Picks tidak berhenti pada daftar ticker/rank. Untuk decision support yang tidak ambigu, setiap current qualified Top Pick **MUST** membawa EOD action intent yang menjelaskan opportunity berikutnya tanpa berubah menjadi order instruction.

- `recommendation_truth = QUALIFIED_EOD_TOP_PICK` berarti ticker lulus final EOD qualification/ranking untuk `effective_trade_date` tersebut.
- `action_intent = ENTRY_CANDIDATE_NEXT_TRADING_SESSION` berarti Top Pick layak **dipertimbangkan** untuk entry manual pada governed next trading session sesuai PLAN, karena recommendation tersedia sebelum canonical entry cutoff.
- `action_intent = ACTION_WINDOW_EXPIRED` berarti EOD ranking dapat tetap disimpan untuk analysis/audit, tetapi opportunity new-entry untuk intended session sudah lewat dan tidak boleh dipresentasikan sebagai current buy suggestion.
- `NO_QUALIFIED_TOP_PICKS` tetap merupakan outcome valid dan tidak mempunyai synthetic entry intent.
- Action intent bukan order, guaranteed fill, atau perintah `BUY`; pengguna tetap melakukan keputusan dan eksekusi manual.
- Optional CONFIRM hanya mempertajam current actionability dari Top Pick yang action window-nya masih sah; ia tidak menciptakan recommendation baru.

## Canonical Temporal Identity

Setiap current recommendation/run harus membedakan minimal:

- `requested_trade_date` — governed EOD trading session yang secara eksplisit diminta untuk dievaluasi; bukan otomatis wall-clock date ketika process dijalankan;
- `effective_trade_date` — EOD session yang benar-benar disediakan authoritative Market Data dan untuk new PLAN harus sama dengan `requested_trade_date`;
- `recommendation_generated_at` — waktu recommendation benar-benar selesai/tersedia;
- `intended_entry_session` — governed `NEXT_TRADING_SESSION` setelah `effective_trade_date` menurut authoritative Market Data calendar;
- `canonical_entry_cutoff` — latest timestamp agar recommendation masih mempunyai minimum decision lead time untuk intended entry opportunity;
- `action_window_status` — minimal `OPEN` atau `EXPIRED`.

Istilah `D+1` dalam materi lama hanya shorthand historis. Canonical strategy memakai `NEXT_TRADING_SESSION`, bukan penambahan satu calendar day.
