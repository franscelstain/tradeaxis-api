# SOURCE DATA ACQUISITION CONTRACT (LOCKED)

## Purpose
Mengunci kontrak akuisisi source untuk **IMPORT PHASE**.

Source acquisition hanya bertanggung jawab membawa immutable raw/provider observation sampai menjadi canonical bar rows yang tervalidasi.
Source acquisition bukan owner readability consumer.

Raw observation capture wajib terjadi sebelum normalization/canonicalization. Canonical row, invalid row, dan acquisition failure harus dapat ditelusuri kembali ke observation envelope yang membentuk atau menolaknya.

---

## Phase boundary (LOCKED)
Source acquisition berada sepenuhnya di **IMPORT PHASE**.

Tanggung jawabnya:
- request source payload berdasarkan tanggal target eksplisit
- capture immutable observation envelope atau immutable payload reference sebelum parsing/canonicalization
- normalize source payload
- map ke source row internal
- dedup source row
- validasi row-level
- persist canonical valid rows
- persist invalid rows / rejection evidence
- persist import telemetry dan provenance minimum
- menyerahkan evidence yang cukup untuk coverage gate

Source acquisition tidak boleh:
- menghitung indicators
- membangun eligibility
- menghitung hash consumer dataset
- membuat publication seal
- mengubah current publication pointer
- menentukan requested date readable

---

## Allowed source modes
Minimum source mode yang valid:
- `api_free`
- `manual_file`

`manual_file` tetap valid untuk:
- controlled recovery
- replay-like controlled ingestion
- operator-driven historical fill yang kontraknya eksplisit

Semua source mode, termasuk `manual_file`, tunduk pada observation-envelope, provenance, schema, stale-data, validation, dan correction rules yang sama. Manual input tidak boleh menjadi jalur bypass.

---

## Date-driven input contract (LOCKED)
Input domain utama adalah **requested trade date** atau **requested trading-date range**.

Karena itu source acquisition wajib:
- menerima tanggal target eksplisit
- menjaga tanggal target itu tetap traceable sampai telemetry akhir
- menolak perilaku yang diam-diam mengganti target date operator tanpa evidence

Keberhasilan source acquisition tidak dinilai dari seluruh ticker harus sukses request.
Keberhasilan import juga tidak berarti requested date readable.

---

## Allowed acquisition strategies
Untuk memenuhi kontrak tanggal bebas, implementation source acquisition wajib siap memakai satu atau kombinasi strategi berikut:
- explicit date request
- explicit date-range request
- `period1` / `period2`
- bounded windowing yang tetap membuktikan requested date target
- retry / backoff / throttle untuk failure transient
- fallback ke `manual_file` bila operating model memang mengizinkan jalur recovery terkontrol

Yang dilarang:
- menjadikan default query window provider sebagai capability limit platform
- mengganti requested date operator tanpa evidence dan without explicit contract
- langsung menganggap requested date sukses publish hanya karena fetch berhasil

---

## Source order and acquisition winner (LOCKED)
Urutan resmi:
1. `api_free` / `yahoo_finance`
2. `manual_file` sebagai controlled recovery path

Urutan ini adalah keputusan untuk **fase bootstrap pembuktian manfaat**, bukan penetapan Yahoo Finance sebagai provider final atau authoritative exchange source.

Pada fase sekarang, fokus pekerjaan tetap pada historical integrity, quality controls, deterministic publication, dan pembuktian manfaat watchlist Weekly Swing. Pemilihan, pembelian, dan integrasi provider berbayar bukan current implementation scope. Arah kelanjutannya baru dievaluasi bila manfaat yang terukur atau kebutuhan SLA, licensing, authoritative correction, field coverage, dan commercial use membenarkan biaya tersebut.

Strategic rationale dan batas fase lengkap ada di `Yahoo_Finance_Bootstrap_Source_Strategy.md`.

Winner untuk satu run ditentukan oleh source mode run itu sendiri.
Satu run hanya boleh punya **satu active acquisition source mode** untuk publication candidate yang sedang dibangun.

Istilah legacy `source-of-truth mode` bila masih ada pada telemetry hanya berarti source winner untuk acquisition run tersebut. Istilah itu tidak menjadikan provider sebagai domain truth; governed canonical publication tetap menjadi domain product.

Artinya:
- run `api_free` tidak boleh diam-diam mengisi sebagian ticker dari `manual_file` lalu mengaku satu source tunggal
- run `manual_file` yang sah boleh menjadi active acquisition source hanya bila memang dijalankan sebagai recovery/correction resmi

---

## Immutable observation envelope (LOCKED)

Setiap provider response atau manual-file acquisition unit wajib menghasilkan immutable observation envelope sebelum canonical row ditulis.

Envelope minimal harus mengikat:

- stable `observation_id` atau content-addressed identity ekuivalen
- `run_id`, acquisition batch/checkpoint identity, dan requested date/range
- `source_mode`, provider/source name, dan provider symbol yang benar-benar diminta
- stable instrument/listing mapping reference bila sudah dapat di-resolve
- sanitized request identity atau manual-file identity tanpa credential/token
- provider observation timestamp bila tersedia
- platform `received_at`/`ingested_at` timestamp
- HTTP/status/content-type metadata atau file metadata yang relevan
- provider schema/version/fingerprint bila tersedia atau derived schema fingerprint
- adapter/parser version
- immutable payload hash dan byte length
- immutable payload body, bounded raw fragment, atau immutable object/reference yang dapat diverifikasi dengan hash
- acquisition outcome dan linkage ke canonical rows, invalid rows, atau failure/quarantine evidence

Payload storage boleh dibatasi berdasarkan ukuran dan retention contract, tetapi audit identity tidak boleh hilang. Bila payload penuh tidak disimpan, immutable reference, cryptographic hash, schema evidence, timestamps, dan bounded diagnostic sample wajib cukup untuk membuktikan observation yang diproses.

Credential, API key, cookie rahasia, authorization header, dan sensitive query value tidak boleh masuk envelope atau diagnostic sample.

## Observation immutability and revision rule (LOCKED)

- Observation envelope yang sudah direkam tidak boleh di-update untuk membuat re-fetch terlihat sebagai response lama.
- Re-fetch provider untuk ticker/date yang sama menghasilkan observation identity baru dan lineage baru, walaupun payload sama.
- Payload revision provider tidak boleh mengubah canonical atau sealed history secara otomatis.
- Correction harus memilih observation baru secara eksplisit dan menghasilkan correction/publication lineage sesuai owner contract.
- Bila envelope/reference tidak dapat dipersist atau diverifikasi, acquisition unit harus gagal/held; canonical row tidak boleh ditulis tanpa provenance.

## Provider-neutral normalization boundary (LOCKED)

Provider-specific request parameters, nested payload paths, error shapes, symbol suffix, dan schema quirks berhenti di adapter/import layer.

Normalized observation harus memakai domain-neutral field names dan semantics. Provider symbol disimpan untuk lineage, bukan dipakai sebagai stable instrument identity. Indicator, eligibility, publication, dan consumer contract tidak boleh bergantung pada Yahoo-specific field path atau response shape.

## Schema and payload validation (LOCKED)

Sebelum row acceptance, adapter wajib memvalidasi minimal:

- response type/content type dan parseability
- required container/series structure
- timestamp/date array alignment dengan quote arrays
- required OHLCV field presence dan supported types
- array cardinality/index consistency
- provider symbol/exchange metadata consistency bila tersedia
- date/timezone conversion dan requested-date inclusion
- non-empty response semantics untuk instrument/date yang expected
- schema fingerprint/version drift terhadap adapter version yang aktif

Unknown or incompatible schema, HTML/error body yang menyamar sebagai success, truncated payload, misaligned arrays, dan malformed values harus menghasilkan explicit schema failure plus quarantine/rejection evidence. Ia tidak boleh diperlakukan sebagai empty but successful acquisition.

## Stale and requested-date validation (LOCKED)

Stale validation harus membedakan historical request dari operational latest-date request:

- untuk setiap request, payload harus membuktikan requested trade date/range yang benar; response terbaru tetapi tidak memuat target tetap gagal
- provider timestamp, trade date, exchange timezone, dan session-completion state harus konsisten
- historical payload tidak disebut stale hanya karena tanggalnya lama, tetapi tetap harus cocok dengan requested boundary
- setelah operational activation, latest-date acquisition yang terlambat atau berhenti pada prior trade date menghasilkan explicit degraded/held evidence dan tidak boleh membuat requested date readable
- prior readable publication boleh dipertahankan sebagai prior-date state, tetapi tidak boleh diberi label fresh untuk requested date baru

## Row acceptance rule
Satu canonical bar hanya boleh diterima bila:
- immutable observation envelope/reference tersedia dan hash-verifiable
- ticker dapat dipetakan sah
- trade date source dapat dibuktikan cocok dengan requested trade date target
- field minimum lolos validasi row-level
- row tersebut memenangkan dedup rule resmi

Row yang gagal syarat ini harus masuk invalid/rejection evidence, bukan ikut numerator coverage.

---

## Minimum storage outputs
Minimum storage outputs dari source acquisition:
- immutable observation envelopes atau immutable payload references/hashes
- canonical valid bars
- invalid/rejected rows atau evidence ekuivalen
- import telemetry minimum
- full source provenance yang mengikat observation, adapter/parser, request, timestamps, schema evidence, dan run
- date-target evidence yang menunjukkan tanggal target import yang diminta

---

## Coverage handoff rule
Source acquisition wajib menyerahkan basis coverage yang benar:
- numerator basis = canonical valid bars untuk requested date
- denominator mengikuti coverage universe contract
- source acquisition tidak boleh mengganti coverage menjadi request-success ratio
- date-driven capability tidak mengubah final gate coverage

---

## Minimum telemetry / evidence
Minimum evidence yang harus tersedia:
- observation identity dan payload hash/reference
- requested date evidence yang eksplisit
- ticker universe count
- ticker attempted count
- ticker success count
- ticker failure count
- failure reason summary
- invalid-row evidence
- coverage input metrics
- active acquisition source identity
- schema/stale validation outcome

Promote phase boleh menolak requested date bila coverage tidak cukup.
Import phase sendiri tidak boleh mengklaim requested date readable.

---

## Conflict handling (LOCKED)
Jika akuisisi menemukan perbedaan antara source aktif dan source recovery pada ticker/date yang sama:
- jangan merge row antar source
- jangan pilih nilai terbaru berdasarkan timestamp provider saja
- gunakan source priority contract
- jalankan correction/recovery flow bila memang mau mengganti selected source pada publication baru

Conflict detection atau source revision tidak pernah memberi izin untuk mengubah observation atau published history in-place.

---

## Anti-ambiguity rules
Tidak boleh:
- menganggap fetch success = publish success
- menulis canonical row tanpa immutable observation identity dan provenance lengkap
- menerima row untuk requested date yang tidak bisa dibuktikan cocok
- menggabungkan dua source menjadi satu publication candidate tanpa kontrak correction/recovery
- menurunkan quality bar hanya demi menyelesaikan import lebih cepat
- memperlakukan unknown schema, stale response, atau malformed payload sebagai successful empty dataset
- membiarkan provider-specific payload path menjadi field contract downstream

---

## Cross-contract alignment
Harus sinkron dengan:
- `Yahoo_Finance_Bootstrap_Source_Strategy.md`
- `EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`
- `EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`
- `Run_Status_and_Quality_Gates_LOCKED.md`
- `CONSUMER_READ_CONTRACT_LOCKED.md`
- `../ops/Commands_and_Runbook_LOCKED.md`

---

## API range-window acquisition addendum
Untuk `source_mode=api` range backfill, provider acquisition boleh mengambil banyak tanggal dalam satu request window per ticker.

Contract yang tetap wajib:
- acquisition window tidak mengubah pipeline date-scope
- requested `trade_date` tetap diproses chronological
- satu requested `trade_date` tetap memiliki run pipeline sendiri
- publication/readability/evidence/replay tetap per requested date
- warmup data boleh di-acquire/import sebagai support history, tetapi tidak otomatis dipromote sebagai requested publication target

Minimum telemetry tambahan:
- `source_acquisition_mode=range_window`
- `source_acquisition_batch_id`
- `source_window_start`
- `source_window_end`
- `warmup_start`
- `requested_start`
- `requested_end`
- `source_acquisition_state`
- `expected_ticker_count`
- `success_ticker_count`
- `failed_ticker_count`

Warmup contract:
- `warmup_start` is a market-calendar trading-day boundary, not a calendar-day approximation.
- The boundary must be resolved from the first requested trading date using configured warmup trading-day count.
- If the requested date is missing from `market_calendar` or the prior trading-day window is insufficient, source acquisition must be blocked before import/promote.
- Warmup bars are support history for rolling indicators and benchmark dependencies; they do not become requested publication targets unless the operator requested those dates.

Yahoo Finance chart API harus menggunakan precise `period1` / `period2` boundaries for range-window acquisition. Provider `range=10d` style defaults are not sufficient for arbitrary historical backfill windows.

---

## API range-window checkpoint/resume addendum
API range-window acquisition must persist checkpoint rows at window/ticker granularity.

Checkpoint identity:
- `window_start`
- `window_end`
- `ticker_code`

Failure telemetry isolation:
- failed checkpoint `reason_code`, `http_status`, `error_sample`, `provider_error_sample`, `sanitized_url`, `failure_scope`, `attempt_count`, and `rows_count` must come from the same checkpoint identity
- timeout/non-HTTP failure must not inherit HTTP status or provider body from a different ticker
- successful checkpoint rows must not carry stale failure sample fields

Resume-only-failed state vocabulary:
- `RETRY_SUCCESS`
- `PARTIAL_RETRY_SUCCESS`
- `FAILED_RETRY_BLOCKED`
- `NO_FAILED_CHECKPOINT`
- `SYSTEMIC_FAILED` only for true global/provider/config acquisition failure

Resume-only-failed diagnostics must include failed checkpoint total/eligible/retried/skipped counts, retry success/failure counts, skipped reasons, and a failure sample consistent with `source_acquisition_checkpoint.json`.


## API range-window market-calendar warmup addendum
For lifecycle API backfill, range-window source acquisition must resolve warmup through the market calendar.

Forbidden:
- `requested_start - N calendar days` as the source of truth
- fixed holiday buffers as the source of truth
- publishing requested dates when the warmup calendar dependency cannot be proven

Required:
- `warmup_start = tradingDateWindowStart(first_requested_trading_date, MARKET_DATA_API_BACKFILL_WARMUP_TRADING_DAYS)` or equivalent repository contract, capped at the first available trading date when the dataset itself starts later than the requested warmup horizon
- fail-fast validation for non-trading requested dates
- no fail-fast solely because the dataset-start boundary has fewer prior trading dates than the ideal warmup horizon; early indicators must be NULL per field as needed
- telemetry that records `warmup_start`, `requested_start`, `requested_end`, and `source_acquisition_mode=range_window`

This rule exists so rolling indicators and benchmark indicators do not become NULL merely because a long holiday/weekend sequence made calendar-day warmup shorter than the required trading-day history, while still allowing deterministic NULL outputs at the beginning of the available dataset.
