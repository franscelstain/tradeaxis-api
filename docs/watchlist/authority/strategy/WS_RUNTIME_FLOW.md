# Watchlist Weekly Swing — Canonical Runtime Flow

## Purpose

Dokumen ini menetapkan urutan canonical Weekly Swing dari EOD candidate generation sampai final ranked Top Picks, dengan optional CONFIRM sebagai current-actionability overlay yang tidak memblokir core product.

## Lifecycle Position

- **Core runtime:** `WS-S01..WS-S04`.
- **Optional branch:** `WS-S05` CONFIRM.
- **Consumes:** frozen `WS-S00` scope/objective.
- **Produces:** canonical dependency `Market Data → PLAN → RECOMMENDATION/TOP PICKS`, lalu optional `→ CONFIRM` bila valid decision-time data tersedia.
- **Core completion:** setiap valid trade date mempunyai deterministic EOD outcome termasuk valid no-pick state, tanpa membutuhkan CONFIRM.

## A. PLAN — EOD Candidate Formation

Pada akhir EOD untuk `trade_date`, Weekly Swing membentuk PLAN hanya dari intake yang lulus `WS_MARKET_DATA_INPUT_REQUIREMENTS.md`. Untuk new current PLAN, producer response harus readable, fresh, dan mempunyai `effective_trade_date` yang sama dengan requested `trade_date`. Explicit stale/prior-date fallback tidak boleh disamarkan sebagai PLAN tanggal baru.

PLAN harus mengikat publication/read-model identity yang diterima dan final/immutable sebelum RECOMMENDATION dibentuk.

PLAN berisi `RECOMMENDATION_CANDIDATES`, `WATCH_ONLY`, `AVOID`, serta plan-derived levels. PLAN tidak memiliki final Top Picks.

## B. RECOMMENDATION — Final Qualified Top Picks

Setelah PLAN immutable tersedia, RECOMMENDATION harus:

1. membaca candidate PLAN untuk `trade_date` yang sama;
2. menerapkan final recommendation qualification gates;
3. mempertahankan semua candidate yang lulus dan menolak semua candidate yang gagal;
4. mengurutkan seluruh candidate yang lulus menjadi `TOP_PICKS` rank `1..N`;
5. mengizinkan `N = 0`.

Recommendation tidak membaca CONFIRM dan tidak bergantung pada capital input untuk menentukan membership atau rank.

Setelah final Top Picks terbentuk, **core Weekly Swing runtime untuk trade date tersebut selesai**. CONFIRM bukan prerequisite untuk menyimpan, mempublikasikan, membaca, atau membuktikan recommendation EOD.

## B1. Recommendation Availability and Entry Cutoff

Final Top Picks runtime record wajib menyimpan `recommendation_available_at` dan effective-dated intended entry session.

Canonical `D+1 open` opportunity hanya sah bila:

`recommendation_available_at <= earliest_entry_time(D+1) - 30 minutes`

`earliest_entry_time(D+1)` harus berasal dari governed effective-dated exchange session/calendar fact, bukan hardcoded current clock schedule.

Jika SLA tersebut gagal:

- Top Picks tetap disimpan sebagai EOD recommendation history;
- system tidak boleh mengklaim bahwa user masih dapat memperoleh canonical D+1 open;
- production monitoring mencatat `LATE_RECOMMENDATION_PUBLICATION`;
- historical/shadow evaluation hanya boleh memakai later causal fill rule bila data dan strategy identity memang mendukungnya; otherwise canonical entry dinyatakan non-executable.

Operational lateness tidak boleh disembunyikan dengan backdating `recommendation_available_at`.

## C. CONFIRM — Optional Current Actionability Overlay

Canonical initial-entry session adalah next trading day setelah EOD recommendation. CONFIRM hanya berlaku pada ticker yang berada pada final Top Picks untuk entry window yang masih sah.

CONFIRM:
- membaca binding recommendation dan PLAN yang sama;
- tidak menambah ticker baru;
- tidak mengubah recommendation score atau rank;
- mengevaluasi current-entry condition hanya bila valid decision-time data tersedia.

Canonical product-level states:
- **NOT_REQUESTED** — CONFIRM belum diminta/dijalankan;
- **UNAVAILABLE_RETRYABLE** — valid current data belum tersedia; bukan failure dan boleh dicoba lagi selama entry window;
- **ACTIONABLE** — valid current data tersedia dan seluruh active gate lulus;
- **NOT_ACTIONABLE** — valid current data tersedia dan sedikitnya satu active actionability gate gagal;
- **EXPIRED_UNCONFIRMED** — entry window berakhir sebelum valid CONFIRM dapat dievaluasi.

Missing, stale, incomplete, delayed, atau temporarily unavailable current data **tidak boleh** dipetakan menjadi `NOT_ACTIONABLE` dan tidak boleh menggagalkan core Weekly Swing.

Jika state masih `UNAVAILABLE_RETRYABLE` dan valid data kemudian tersedia sebelum entry window berakhir, CONFIRM dapat dievaluasi ulang untuk menghasilkan `ACTIONABLE` atau `NOT_ACTIONABLE`.

## D. Consumer Decision Semantics

Untuk keputusan beli manual:

- `TOP_PICK` adalah qualified EOD recommendation yang sah;
- `TOP_PICK + ACTIONABLE` adalah Top Pick dengan tambahan current-actionability evidence;
- `TOP_PICK + NOT_ACTIONABLE` berarti valid EOD recommendation yang current-entry conditions-nya telah terbukti tidak layak pada saat CONFIRM;
- `TOP_PICK + NOT_REQUESTED/UNAVAILABLE_RETRYABLE/EXPIRED_UNCONFIRMED` tetap merupakan recommendation EOD, tetapi current actionability **unknown / not proven**, bukan negative decision;
- ticker non-recommended tidak boleh dipromosikan oleh CONFIRM menjadi alternatif buy recommendation.

Sistem tidak boleh mengklaim `ACTIONABLE` tanpa valid CONFIRM, tetapi ketiadaan label `ACTIONABLE` tidak membuat Top Pick menjadi gagal atau tidak valid sebagai EOD decision-support.

## E. Canonical Output Relationship

Core relationship:

`PLAN → RECOMMENDATION/TOP_PICKS`

Optional relationship:

`TOP_PICKS → optional CONFIRM actionability`

Invalid relationship:
- RECOMMENDATION dari ticker di luar PLAN;
- TOP PICKS dibentuk langsung dari PLAN candidate state tanpa final qualification;
- CONFIRM menjadi prerequisite untuk membentuk atau mempublikasikan Top Picks;
- missing CONFIRM data menggagalkan PLAN/RECOMMENDATION;
- CONFIRM menambah recommendation baru;
- CONFIRM mengubah historical recommendation membership/rank;
- capital mengubah recommendation quality ordering.

## Final Invariants

1. PLAN harus immutable sebelum recommendation.
2. Final Top Picks hanya dimiliki RECOMMENDATION.
3. Recommendation count sama dengan jumlah candidate yang benar-benar lulus qualification gate dan boleh nol.
4. Recommendation ranking harus deterministic dan capital-independent.
5. Core runtime selesai pada final Top Picks dan tidak bergantung pada CONFIRM.
6. CONFIRM hanya mengevaluasi final Top Picks sebagai optional overlay.
7. Data CONFIRM yang belum tersedia menghasilkan non-terminal availability state, bukan business failure.
8. `NOT_ACTIONABLE` memerlukan valid evaluated current data.
9. CONFIRM dapat mengubah current-actionability interpretation tetapi tidak historical recommendation state.
10. Expired entry window tidak boleh dihidupkan kembali tanpa explicit carry-forward strategy identity dan proof baru.


## EOD Core Completion and Manual-Execution Boundary

Core Weekly Swing runtime adalah **EOD-only decision support** dan selesai ketika deterministic ranked Top Picks beserta binding PLAN telah dipersist/published untuk trade date tersebut.

- Core runtime **MUST NOT** menunggu realtime price, intraday tick, orderbook, broker order state, atau broker fill untuk menyelesaikan PLAN/RECOMMENDATION.
- User-facing Top Pick boleh menyertakan PLAN-derived entry reference/zone, stop, target, horizon, score/rank, dan risk information, tetapi tidak boleh mengklaim guaranteed/exact investor fill.
- Actual order timing, order type, queue position, partial fill, dan broker execution adalah keputusan/hasil di luar core runtime.
- Missing optional CONFIRM data tidak mengubah membership, score, rank, atau historical validity dari EOD Top Picks.
- Jika future optional decision-time capability diperkenalkan, core runtime completion point tetap final EOD Top Picks kecuali controlled strategy revision secara eksplisit mengubah product scope.

## Runtime No-Local-Market-Fact Substitution

Runtime harus menjadi consumer strategy, bukan secondary Market Data pipeline.

- Core runtime **MUST** membaca market facts hanya dari bound producer-facing intake identity; direct external-provider fetch, direct internal Market Data table read, atau local indicator/feature build tidak boleh menjadi fallback runtime path.
- PLAN/RECOMMENDATION boleh mentransform authoritative facts menjadi Weekly Swing decision outputs, tetapi transform tersebut tidak boleh menghasilkan substitute market feature untuk mengisi producer field yang missing.
- Jika runtime menemukan required market fact missing setelah intake binding, affected path harus berhenti/fail closed sesuai `WS_MARKET_DATA_INPUT_REQUIREMENTS.md`; runtime tidak boleh menunggu atau menghitung alternate fact diam-diam.
- Persisted PLAN/RECOMMENDATION harus tetap dapat ditelusuri ke Market Data requested/effective date dan publication/read-model identity yang menjadi source facts; persistence Watchlist tidak menciptakan market-data authority baru.

## F. Requested-Date / Late-EOD Runtime State Machine

Current runtime **MUST** memisahkan tanggal data dari waktu process berjalan.

1. Resolve explicit governed `requested_trade_date`; jangan menganggap tanggal wall-clock adalah EOD target yang ready.
2. Request consumer-facing Market Data untuk exact requested date.
3. Jika response bukan `READABLE + FRESH + effective_trade_date == requested_trade_date`, return `MARKET_DATA_UNAVAILABLE_RETRYABLE`; jangan membuat PLAN, recommendation, atau current action intent baru.
4. Previous valid Watchlist/Market Data result boleh ditampilkan sebagai `PREVIOUS_CONTEXT` dengan effective date asli, tetapi tidak boleh masquerade sebagai current run.
5. Jika exact EOD kemudian ready, rerun exact requested date; system-run date boleh sudah berganti kalender.
6. Setelah Top Picks terbentuk, resolve `intended_entry_session = NEXT_TRADING_SESSION(effective_trade_date)` dari authoritative calendar dan hitung canonical entry cutoff dari frozen minimum lead time.
7. Bila `recommendation_generated_at <= canonical_entry_cutoff`, action window `OPEN`; each qualified Top Pick dapat mempublikasikan `ENTRY_CANDIDATE_NEXT_TRADING_SESSION`.
8. Bila `recommendation_generated_at > canonical_entry_cutoff`, action window `EXPIRED`; result hanya analysis/audit dan **MUST NOT** dipresentasikan sebagai current new-entry recommendation untuk intended session.
9. Expired result tidak boleh otomatis carry-forward ke session berikutnya. Current opportunity berikutnya harus berasal dari next governed EOD recommendation.
10. CONFIRM hanya branch setelah Top Pick valid dengan action window open; CONFIRM tidak dapat mengubah `MARKET_DATA_UNAVAILABLE_RETRYABLE` menjadi buy suggestion.

## G. User-Facing Current Recommendation Semantics

User-facing current output harus dapat membedakan secara eksplisit:

- data date (`effective_trade_date`);
- generation timestamp (`recommendation_generated_at`);
- intended entry session;
- recommendation rank/PLAN;
- EOD action intent;
- optional CONFIRM state bila capability tersedia.

Kalimat produk yang canonical adalah **“layak dipertimbangkan untuk entry pada next trading session sesuai PLAN”**, bukan “beli hari ini”, karena Watchlist berjalan setelah EOD dan tidak mengetahui actual investor execution.

## H. Temporal Ownership Enforcement in Runtime

Runtime wajib menegakkan ownership contract tanpa membuat alias lintas domain:

- `requested_trade_date` di-resolve dan direkam oleh Watchlist lifecycle sebelum intake; bila caller memberikan non-session/invalid date, validitas session harus diperiksa terhadap authoritative Market Data calendar dan tidak boleh silently roll ke session lain.
- `effective_trade_date`, `market_data_published_at`, dan `market_data_revision_id` pada run context adalah producer-owned values yang dicopy dari exact Market Data response; runtime Watchlist tidak boleh mengubah atau mengisi nilai tersebut.
- `recommendation_generated_at` dicatat oleh Watchlist ketika recommendation result/version selesai, sedangkan `intended_entry_session`, `canonical_entry_cutoff`, dan `action_window_status` dihitung oleh Watchlist dari authoritative session facts + frozen strategy timing; field-field ini tidak boleh dibaca sebagai output keputusan Market Data.
- Persisted run/PLAN/RECOMMENDATION harus menyimpan pemisahan producer temporal provenance dan Watchlist lifecycle temporal fields sehingga audit dapat membuktikan asal setiap nilai dan urutan causal-nya.
- Jika producer-owned temporal provenance required hilang/malformed, runtime harus berhenti sebelum current PLAN/Top Picks dibentuk; `recommendation_generated_at` atau local clock tidak boleh digunakan untuk menebak producer publication/effective date.

## Repeated Recommendation Continuity in EOD Runtime

Repeated signal adalah recommendation-history fact milik Watchlist, bukan portfolio state pengguna.

- Setiap governed EOD run **MUST** mengevaluasi candidate dari current authoritative EOD facts secara independen dari actual/hypothetical holding state.
- Current Top Pick yang juga qualified pada prior issued recommendation boleh membawa Watchlist-owned continuity fields `is_repeat_recommendation`, `first_qualified_trade_date`, `consecutive_qualified_sessions`, dan `previous_recommendation_id` bila exact prior lineage tersedia.
- Continuity fields **MUST NOT** mengubah baseline eligibility, score, rank, Top-Pick membership, stop/target, atau `ENTRY_CANDIDATE_NEXT_TRADING_SESSION` action intent.
- Missing prior recommendation history hanya membuat continuity metadata unavailable; ia **MUST NOT** membuat current EOD Top Picks gagal bila current Market Data/strategy inputs lengkap.
- Runtime **MUST NOT** meminta actual user holding, broker position, cash, atau previous actual fill untuk menentukan apakah ticker boleh muncul kembali sebagai Top Pick.
- Same-listing overlap suppression hanya berlaku di separately defined follower-replay proof; suppression tersebut **MUST NOT** disisipkan ke core runtime recommendation semantics.


## Deterministic Runtime Outcome and Rerun Semantics

- Setelah authoritative EOD input valid dan core evaluation selesai, runtime **MUST** menghasilkan tepat satu valid EOD outcome: non-empty qualified Top Picks atau empty `NO_ACTIONABLE_TOP_PICKS`; valid zero-pick state bukan exception path.
- `NO_ACTIONABLE_TOP_PICKS` yang selesai sebelum canonical entry cutoff **MUST** dihitung sebagai timely completed Watchlist run untuk operational availability, walaupun tidak mempunyai ticker untuk entry.
- Rerun exact frozen data/strategy/parameter identity **MUST** mempertahankan core recommendation truth; runtime tidak boleh memakai random ordering, unstable iteration order, local timezone default, atau mutable external state untuk mengubah candidate/PLAN/rank.
- Rerun yang dilakukan setelah action cutoff boleh menghasilkan lifecycle `ACTION_WINDOW_EXPIRED`, tetapi **MUST NOT** mengubah member set/rank/PLAN truth yang berasal dari EOD identity yang sama.
- Jika same-identity rerun menghasilkan core recommendation truth berbeda, runtime **MUST** menghasilkan integrity failure `NON_DETERMINISTIC_RECOMMENDATION` dan affected result tidak boleh dipublikasikan sebagai trusted replacement.
- Retry/replay **MUST NOT** overwrite issued PLAN/recommendation record; new run metadata harus mempertahankan explicit predecessor/source lineage bila record baru memang diperlukan.
