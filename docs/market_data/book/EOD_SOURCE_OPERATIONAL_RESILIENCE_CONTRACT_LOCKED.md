# EOD SOURCE OPERATIONAL RESILIENCE CONTRACT (LOCKED)

## Current State
STRATEGY LOCKED / DEVELOPMENT-PHASE CAPABLE / OPERATIONAL ACTIVATION NOT YET PROVEN

Dokumen ini tidak boleh digunakan sebagai production-readiness claim. Decision-grade operational relock memerlukan activation marker, catch-up, automated import/promote, stale protection, incident visibility, dan consecutive trading-day executed proof.

---

## Purpose
Dokumen ini mengunci resilience contract untuk **IMPORT PHASE** pada platform EOD Market Data.

Dokumen ini tidak mengatur indicator compute, eligibility build, hash, seal, atau finalize.
Semua itu berada di **PROMOTE PHASE**.

Kontrak ini membedakan development frontier dari operational freshness dan memastikan source failure tidak pernah menghasilkan silent readable data.

---

## Locked architectural split

### Import phase
Import phase hanya mencakup:
- source acquisition
- request loop per ticker
- retry / backoff / throttle
- mapping source payload menjadi source rows
- dedup
- validation row-level
- write canonical `eod_bars`
- write invalid rows
- hitung bars coverage input evidence
- simpan telemetry import

Import phase **tidak boleh**:
- menghitung indicators
- membangun eligibility
- menghitung consumer dataset hash
- membuat seal
- memfinalisasi readability publication

### Promote phase
Promote phase membaca hasil import yang sudah tersimpan lalu:
- memvalidasi readiness berbasis bars coverage
- menghitung indicators
- membangun eligibility
- menghitung hash
- membuat seal
- memfinalisasi terminal status / publishability state

---

## Date-driven capability (LOCKED)
Resilience contract ini wajib mendukung **arbitrary date ingestion**.

Artinya:
- requested trade date tunggal apa pun harus bisa dicoba secara eksplisit
- historical range apa pun harus bisa diproses bertahap secara operasional
- adapter, retry, throttle, dan loop strategy harus melayani tanggal target domain
- provider default yang punya jendela query terbatas tidak boleh memaksa operating model menjadi recent-only

Date-driven capability di sini berarti kemampuan **mencoba dan memproses tanggal target secara sah**.
Readable success tetap ditentukan kemudian oleh coverage gate dan promote outcome.

---

## Locked source order and fallback policy
Urutan source resmi untuk jalur aktif adalah:
1. **Primary:** `api_free/yahoo_finance`
2. **Secondary controlled recovery:** `manual_file`

Tidak ada source ketiga pada baseline ini.
Tidak ada majority-vote resolution.
Tidak ada merge antar source untuk membentuk satu requested-date publication.

`manual_file` hanya boleh dipakai bila:
- operator memang menjalankan source mode itu secara eksplisit; atau
- kontrak recovery/correction resmi memerintahkan perpindahan ke jalur itu

### Batas kapasitas jalur pemulihan (LOCKED)

`manual_file` adalah **rescue satu tanggal, bukan jalur kelangsungan operasi.**

Satu requested date mencakup seluruh universe aktif yang berukuran ratusan instrument, dan setiap baris tetap tunduk pada observation envelope, provenance, schema, stale, dan validation yang sama ketatnya. Itu dapat dikerjakan sesekali untuk satu tanggal; ia tidak dapat menggantikan akuisisi harian selama periode yang panjang.

Konsekuensi operasional yang mengikat:

- Kegagalan primary source yang berlangsung berhari-hari **tidak** memiliki jalur pemulihan yang setara. Keadaan itu adalah `stale`/`failed` yang harus terlihat dan dieskalasi, bukan sesuatu yang ditambal dengan recovery manual berulang.
- Rencana kelangsungan tidak boleh disusun dengan asumsi `manual_file` sanggup menutup gap panjang. Menyebutnya jalur pemulihan tanpa menyatakan batas ini membuat rencana kelangsungan terlihat ada padahal tidak.
- Berapa hari perdagangan berturut-turut kegagalan yang mengubah insiden operasional menjadi bukti bahwa kapabilitas source menjadi penghambat adalah ambang yang wajib ditetapkan kontrak operasi, sesuai `Yahoo_Finance_Bootstrap_Source_Strategy.md`.

### Current-phase strategic rationale

Primary `api_free/yahoo_finance` adalah **bootstrap source yang dipilih dengan sengaja** untuk membuktikan manfaat market-data dan watchlist Weekly Swing sebelum platform menanggung biaya data berbayar.

Keputusan ini bukan kesalahan strategi dan tidak menjadikan Yahoo Finance source of truth domain. Keputusan ini sah selama limitation provider diserap oleh adapter/import strategy, source identity tetap terbuka, dan seluruh validation, coverage, quarantine, correction, publication, serta readability gate tetap berlaku tanpa pelonggaran.

Kontrak fase aktif ini tidak mewajibkan evaluasi vendor, pembelian data, dual-feed, atau migrasi provider sekarang. Kelanjutan menuju provider berbayar adalah future decision setelah manfaat atau kebutuhan SLA, licensing, authoritative correction, dan commercial use membenarkannya.

Rationale, safeguard, non-goal fase aktif, dan future decision trigger dijelaskan di `Yahoo_Finance_Bootstrap_Source_Strategy.md`.

---

## Acquisition shape locked for active path

Kontrak ini mengunci perilaku minimum berdasarkan **bentuk akuisisi**, bukan berdasarkan nama provider. Simbol, kode status, jendela default, dan kapabilitas provider yang berlaku dimiliki oleh capability matrix pada `Yahoo_Finance_Bootstrap_Source_Strategy.md`; mengulanginya di sini akan melanggar aturan dokumen ini sendiri bahwa provider limitation tidak diwariskan ke domain contract.

Bentuk yang mengikat pada jalur aktif:

- **fan-out per instrument** — satu requested date menghasilkan banyak unit akuisisi independen, satu per instrument dalam universe;
- karena itu ketahanan jalur import wajib **partial-tolerant**: kegagalan sebagian unit tidak boleh menghentikan seluruh import date-run;
- source gratis tanpa SLA dapat menerapkan **pembatasan laju** dan dapat menolak permintaan tanpa pemberitahuan;
- source dapat memiliki **jendela akuisisi default** yang lebih sempit dari kebutuhan domain, sehingga windowing eksplisit wajib tersedia.

Dokumen ini tidak mengunci provider baru dan tidak menjadikan source aktif sebagai domain truth. Ketiadaan SLA, support, dan authoritative correction adalah paparan yang sudah dinyatakan pada owner strategi source.

---

## Source access self-protection (LOCKED)

Perilaku retry platform sendiri adalah salah satu ancaman terbesar terhadap kelangsungan akses ke source gratis tak resmi. Aturan retry di bawah melindungi **run**; aturan ini melindungi **source**.

Universe aktif berukuran ratusan instrument, sehingga satu requested date sudah menghasilkan ratusan permintaan sebelum retry apa pun. Kegagalan menyeluruh yang direspons dengan retry penuh melipatgandakan beban tepat ketika source sedang menolak — cara tercepat kehilangan akses secara permanen, yaitu risiko kelangsungan yang dinyatakan pada owner strategi source.

Karena itu wajib:

- **Throttle dan concurrency ceiling** aktif pada seluruh jalur akuisisi, bukan hanya pada retry.
- **Circuit breaker.** Ketika rasio kegagalan melewati ambang yang dikonfigurasi, akuisisi berhenti untuk run itu dan menghasilkan degraded/failed evidence. Ia tidak melanjutkan sisa universe dengan harapan sebagian berhasil.
- **Backoff yang meningkat** untuk kelas transient, bukan interval tetap.
- **Retry budget yang terdeklarasi.** Batasnya adalah nilai konfigurasi yang tercatat pada config register, bukan penilaian implementasi. Retry budget yang tidak terdeklarasi tidak boleh dianggap ada.

Config key yang mengikat aturan ini dimiliki kontrak ini dan terdaftar pada `../registry/Platform_Config_Registry_LOCKED.md`: `market_data.provider.api_retry_max`, `market_data.provider.api_backoff_ms`, `market_data.provider.api_throttle_qps`, dan `market_data.provider.circuit_breaker_error_rate`.

Menghentikan akuisisi lebih awal untuk melindungi akses adalah **outcome yang sah dan wajib terlihat**, bukan kegagalan yang perlu disamarkan. Ia menghasilkan degraded/failed state seperti kegagalan lainnya, dan tidak pernah menghasilkan readable publication.

---

## Development-versus-operational boundary (LOCKED)

### Development phase

Selama `OPERATIONAL_START_DATE` atau governance marker ekuivalen belum ditetapkan:

- latest ingested trade date adalah development data frontier yang bergerak
- gap setelah frontier bukan production incident dan bukan capability limit
- `daily_enabled=false` dapat diterima sebagai development-state choice
- gap tidak menghalangi koreksi historical integrity, schema, corporate action, indicator, replay, atau provenance
- run yang benar-benar dijalankan tetap wajib fail-safe, traceable, dan tidak boleh menghasilkan false success

Development state tidak mengizinkan klaim fresh/current yang tidak terbukti. Ia hanya berarti kewajiban consecutive daily freshness belum mulai dihitung.

### Operational activation gates

Arti dan konsekuensi istilah *operational activation* dimiliki `Terminology_and_Scope.md`. Yang dimiliki di sini adalah **gate operasional yang harus terbukti sebelum marker itu boleh ditetapkan** — proof, bukan terminologi.

Freshness menjadi hard requirement hanya setelah marker activation ditetapkan untuk forward paper watchlist, user-facing watchlist, atau penggunaan rutin.

Sebelum activation wajib:

1. menetapkan `OPERATIONAL_START_DATE` atau marker governed ekuivalen
2. melakukan controlled catch-up seluruh expected trading dates dari development frontier sampai activation boundary
3. mengaktifkan dan membuktikan daily import **dan** promote/readiness scheduling
4. mengaktifkan stale alert, source-degraded alert, lock/retry monitoring, dan stale-consumer protection
5. membuktikan idempotent retry/backfill dan recovery dari partial failure
6. mulai menghitung consecutive operational SLO hanya sejak activation boundary

Operational activation tidak menetapkan provider berbayar sebagai requirement; source aktif dinilai berdasarkan executed operational evidence.

## Operational source state model (LOCKED)

Untuk setiap expected trade date setelah activation, keadaan source harus terlihat secara eksplisit sebagai semantic minimum berikut:

- **complete/healthy:** expected acquisition selesai dan dapat dilanjutkan ke promote gates
- **partial/degraded:** sebagian expected observation gagal, terlambat, malformed, atau quarantined
- **failed:** systemic/config/schema/storage failure menghentikan acquisition yang sah
- **held/quarantined:** data tersedia tetapi belum aman dipakai karena integrity, stale, schema, or anomaly evidence
- **stale:** requested latest trade date belum memiliki governed readable publication, sementara prior publication mungkin masih ada

Exact runtime status/reason code mengikuti registry owner, tetapi tidak boleh menormalkan keadaan partial, failed, held, quarantined, atau stale menjadi success.

Setelah activation, missing/late/partial requested date wajib menghasilkan observable degraded or incident evidence. Prior readable date tetap beridentitas prior date dan tidak boleh disajikan sebagai fresh requested date.

---

## Provider limitation abstraction (LOCKED)
Provider limitation harus diserap oleh import strategy, bukan diwariskan ke domain contract.

Implementation wajib siap mendukung hal-hal berikut sesuai provider capability aktual:
- explicit date-range request
- request berbatas rentang eksplisit dengan parameter batas awal dan akhir, apa pun penamaannya di adapter
- windowing yang mencakup requested date target
- batching historical fetch
- retry / backoff / throttle untuk failure transient

Provider limitation tidak boleh dipakai untuk:
- menurunkan threshold coverage
- mengganti requested date operator
- mengklaim requested date readable saat data belum cukup

---

## Retry / backoff / handoff decision flow (LOCKED)
Aturan resmi:
1. coba primary source untuk ticker/date target
2. bila gagal dengan transient class — sinyal pembatasan laju, timeout, atau kegagalan transport sementara — lakukan retry sampai retry budget terdeklarasi habis, dengan throttle dan circuit breaker tetap berlaku
3. bila gagal dengan non-transient class (auth/config/parser/global mapping/date mismatch), tandai failure tanpa retry berlebihan
4. setelah seluruh ticker selesai diproses atau retry budget habis, simpan hasil import evidence
5. evaluate coverage pada promote
6. hanya bila operating mode eksplisit memerintahkan recovery source, run berikutnya boleh memakai `manual_file`

Satu run tidak boleh berpindah selected acquisition source di tengah publication candidate lalu menggabungkan hasil dua source menjadi satu state tanpa kontrak correction/recovery yang eksplisit.

Retry harus idempotent pada observation/run/checkpoint identity. Retry response menghasilkan observation baru atau linkage retry yang immutable; ia tidak boleh menimpa payload lama atau menggandakan canonical row tanpa dedup identity.

---

## Partial-tolerant import rule
Untuk active path yang request per ticker:
- partial ticker failure **boleh** terjadi pada import
- command import tidak boleh crash hanya karena beberapa ticker gagal
- hasil partial harus tetap disimpan pada telemetry / failure evidence
- requested date tetap dievaluasi akhirnya melalui **bars coverage berbasis canonical valid bars**, bukan jumlah request sukses

Partial-tolerant berarti import dapat menyelesaikan pengumpulan evidence tanpa process crash. Istilah ini tidak berarti partial data boleh menjadi silent readable publication; readability hanya dapat dipertimbangkan setelah denominator temporal, locked `0.98` delivery gate, explicit missing reasons, dan seluruh independent hard gates dievaluasi.

---

## Quarantine and no-auto-repair rule (LOCKED)

Source failure, schema drift, missing bar, stale response, anomaly, atau price discontinuity hanya boleh menghasilkan retry, rejection, held/degraded state, quarantine, or explicit correction workflow.

Jalur source resilience dilarang otomatis:

- mengisi missing bar dengan prior close, zero OHLCV, interpolation, atau synthetic candle
- meng-copy prior-date observation lalu memberi requested date baru
- mengubah scale harga/volume berdasarkan anomaly detector
- menganggap price break sebagai verified corporate action
- mengedit canonical or history rows in-place
- memakai `manual_file` tanpa operator/governed recovery decision
- menurunkan coverage/quality gate agar run terlihat berhasil

Detector boleh membuat anomaly candidate dan evidence. Perubahan content hanya boleh melalui verified source/corporate-action evidence serta revisioned correction/publication lifecycle.

Jika quarantine/rejection membuat gate tidak terpenuhi, promote harus held/failed dan requested date tidak boleh readable.

---

## Capability boundary (LOCKED)

Model lima keadaan di atas mengklasifikasikan **apa yang teramati platform**, bukan apa yang terjadi di pasar. Batasnya harus dinyatakan karena keadaan `complete/healthy` adalah pernyataan paling menenangkan yang dihasilkan kontrak ini.

**Yang dibuktikan model keadaan.** Bahwa unit akuisisi yang diharapkan selesai, gagal, tertunda, atau terkarantina; bahwa kegagalan terlihat dan terhitung; bahwa keadaan selain sukses tidak pernah dinormalkan menjadi sukses.

**Yang tidak dapat dibuktikannya.**

- **Bahwa `complete/healthy` berarti datanya benar.** Keadaan ini mengukur penyelesaian akuisisi, bukan kebenaran nilai. Source yang mengirim harga yang salah namun lengkap dan berbentuk sempurna menghasilkan `complete/healthy`.
- **Bahwa ketiadaan response berarti tidak ada perdagangan.** Ketiadaan hanya berarti tidak terkirim. Apakah sebuah bar seharusnya ada ditentukan oleh kalender dan trading status, bukan oleh kontrak ini. Bila ekspektasi itu sendiri salah, model keadaan tidak memiliki cara mengetahuinya — ia akan melaporkan `complete/healthy` untuk sesi yang seharusnya diharapkan tetapi tidak pernah tercatat.
- **Bahwa kelas kegagalan yang dilaporkan sudah tepat.** Klasifikasi transient versus non-transient berasal dari sinyal transport. Source yang mengembalikan kegagalan yang menyamar sebagai sukses, atau sebaliknya, akan salah diklasifikasikan berikut keputusan retry-nya.
- **Bahwa retry yang berhasil membuktikan kegagalan sebelumnya bersifat transient.** Ia hanya membuktikan percobaan kedua berhasil.

Konsekuensinya: **`complete/healthy` tidak boleh dikutip sebagai bukti kualitas data atau kelengkapan sesi.** Ia menyatakan akuisisi berjalan sebagaimana mestinya terhadap ekspektasi yang diberikan kepadanya.

---

## Conflict resolution (LOCKED)
Jika source A dan source B menghasilkan nilai berbeda untuk ticker/date yang sama, aturan finalnya adalah:
- tidak ada voting
- tidak ada averaging
- tidak ada merge-field-per-field
- pemenang ditentukan oleh **source priority + run mode + validation**

Detailnya:
- dalam run normal, primary source yang lolos validasi menjadi selected acquisition source
- secondary `manual_file` hanya menjadi selected acquisition source bila run memang dijalankan dalam mode itu atau correction resmi mempublikasikannya
- bila source yang sedang aktif gagal lolos validasi keras, data tersebut tidak boleh dipromosikan; hasilnya harus non-readable sampai run recovery/correction resmi selesai

---

## Selected source and traceability minimum
Setiap canonical row / publication context harus dapat ditelusuri minimal ke:
- `requested_trade_date`
- `source_mode`
- `source_name`
- `run_id`
- `ingested_at` atau acquisition timestamp ekuivalen
- source attempt / failure summary
- publication/correction reference bila source berubah melalui correction flow

Tanpa jejak ini, publication tidak audit-safe.

Traceability minimum ini tunduk pada immutable observation envelope, schema validation, dan stale validation di `Source_Data_Acquisition_Contract_LOCKED.md`.

---

## Retry budget boundary
Retry/backoff/throttle hanya berlaku pada jalur **source acquisition import**.

Aturan keras:
- retry hanya untuk failure class yang memang transient, terutama rate limit dan timeout
- retry budget efektif harus dibatasi secara aman oleh implementasi/operator policy
- retry tidak boleh dipakai untuk auth/config/global parser failure
- retry tidak boleh dipakai untuk menyamarkan hard integrity mismatch

---

## Final interpretation boundary
Walaupun import bersifat partial-tolerant:
- requested date tetap dievaluasi lewat coverage/promote
- import retry bukan bentuk publishability override
- import completion bukan readable success
- fallback source bukan alasan untuk menggabungkan dua source state secara diam-diam
- last-known-good publication bukan bukti bahwa requested latest date fresh
- source failure tidak pernah mengotorisasi synthetic or in-place repair

---

## Required audit-visible fields
Minimum field yang harus tampak:
- `requested_trade_date`
- `source_mode`
- `source_name`
- `source_priority`
- `retry_attempt_count`
- `failure_class_summary`
- `coverage_input_available_count`
- `coverage_input_universe_count`
- active source decision (`source_of_truth_decision` hanya boleh dipertahankan sebagai legacy field name, bukan domain-truth claim)
- observation/payload hash or immutable reference
- schema validation state
- stale/freshness state
- quarantine/rejection counts and reasons
- latest expected and latest readable trade date after activation

---

## Anti-ambiguity rules
Tidak boleh:
- menyebut provider limitation sebagai capability limit domain
- mempromosikan requested date hanya karena sebagian ticker berhasil
- menganggap manual fallback sebagai merge bebas antar-source
- membiarkan dua source sama-sama dianggap selected source untuk satu publication yang sama
- memakai majority vote atau best-effort merge tanpa kontrak baru
- menyebut development frontier gap sebagai incident sebelum activation
- menyebut requested latest date fresh hanya karena prior publication masih readable
- menghasilkan readable success dari source state partial, failed, held, quarantined, stale, atau schema-unknown
- menjalankan automatic data repair untuk menutupi source failure
- menjalankan retry tanpa throttle, circuit breaker, dan retry budget yang terdeklarasi
- memperlakukan penghentian akuisisi demi melindungi akses sebagai kegagalan yang perlu disamarkan
- mengutip `complete/healthy` sebagai bukti kualitas data atau kelengkapan sesi

---

## Cross-contract alignment
Harus sinkron dengan:
- `Yahoo_Finance_Bootstrap_Source_Strategy.md`
- `Source_Data_Acquisition_Contract_LOCKED.md`
- `EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`
- `Run_Status_and_Quality_Gates_LOCKED.md`
- `CONSUMER_READ_CONTRACT_LOCKED.md`
- `../ops/Commands_and_Runbook_LOCKED.md`
