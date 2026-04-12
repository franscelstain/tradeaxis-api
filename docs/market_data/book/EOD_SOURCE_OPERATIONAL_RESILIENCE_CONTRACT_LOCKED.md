# EOD SOURCE OPERATIONAL RESILIENCE CONTRACT (LOCKED)

## Current State
READY FOR IMPLEMENTATION

---

## Purpose
Dokumen ini mengunci resilience contract untuk **IMPORT PHASE** pada platform EOD Market Data.

Dokumen ini tidak mengatur indicator compute, eligibility build, hash, seal, atau finalize.
Semua itu berada di **PROMOTE PHASE**.

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
1. **Primary:** `api_free` / `yahoo_finance`
2. **Secondary controlled recovery:** `manual_file`

Tidak ada source ketiga pada baseline ini.
Tidak ada majority-vote resolution.
Tidak ada merge antar source untuk membentuk satu requested-date publication.

`manual_file` hanya boleh dipakai bila:
- operator memang menjalankan source mode itu secara eksplisit; atau
- kontrak recovery/correction resmi memerintahkan perpindahan ke jalur itu

---

## Provider context locked for active path
Untuk active default provider `yahoo_finance`:
- request dilakukan **per ticker**
- symbol IDX dirender sebagai `<ticker>.JK`
- provider gratis dapat memberi `HTTP 429`
- provider dapat memiliki default acquisition window seperti `10d`
- karena request fan-out terjadi per ticker, ketahanan jalur import harus **partial-tolerant**
- kegagalan beberapa ticker tidak boleh otomatis menghentikan seluruh import date-run

Dokumen ini tidak mengunci provider baru.
Dokumen ini hanya mengunci perilaku minimum untuk provider path aktif yang sudah ada.

---

## Provider limitation abstraction (LOCKED)
Provider limitation harus diserap oleh import strategy, bukan diwariskan ke domain contract.

Implementation wajib siap mendukung hal-hal berikut sesuai provider capability aktual:
- explicit date-range request
- `period1` / `period2` style request
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
2. bila gagal dengan transient class (`429`, timeout, temporary transport failure), lakukan retry sampai retry budget habis
3. bila gagal dengan non-transient class (auth/config/parser/global mapping/date mismatch), tandai failure tanpa retry berlebihan
4. setelah seluruh ticker selesai diproses atau retry budget habis, simpan hasil import evidence
5. evaluate coverage pada promote
6. hanya bila operating mode eksplisit memerintahkan recovery source, run berikutnya boleh memakai `manual_file`

Satu run tidak boleh berpindah source-of-truth di tengah publication candidate lalu menggabungkan hasil dua source menjadi satu state tanpa kontrak correction/recovery yang eksplisit.

---

## Partial-tolerant import rule
Untuk active path yang request per ticker:
- partial ticker failure **boleh** terjadi pada import
- command import tidak boleh crash hanya karena beberapa ticker gagal
- hasil partial harus tetap disimpan pada telemetry / failure evidence
- requested date tetap dievaluasi akhirnya melalui **bars coverage berbasis canonical valid bars**, bukan jumlah request sukses

---

## Conflict resolution (LOCKED)
Jika source A dan source B menghasilkan nilai berbeda untuk ticker/date yang sama, aturan finalnya adalah:
- tidak ada voting
- tidak ada averaging
- tidak ada merge-field-per-field
- pemenang ditentukan oleh **source priority + run mode + validation**

Detailnya:
- dalam run normal, primary source yang lolos validasi menjadi source-of-truth
- secondary `manual_file` hanya menjadi source-of-truth bila run memang dijalankan dalam mode itu atau correction resmi mempublikasikannya
- bila source yang sedang aktif gagal lolos validasi keras, data tersebut tidak boleh dipromosikan; hasilnya harus non-readable sampai run recovery/correction resmi selesai

---

## Source-of-truth and traceability minimum
Setiap canonical row / publication context harus dapat ditelusuri minimal ke:
- `requested_trade_date`
- `source_mode`
- `source_name`
- `run_id`
- `ingested_at` atau acquisition timestamp ekuivalen
- source attempt / failure summary
- publication/correction reference bila source berubah melalui correction flow

Tanpa jejak ini, publication tidak audit-safe.

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
- `source_of_truth_decision`

---

## Anti-ambiguity rules
Tidak boleh:
- menyebut provider limitation sebagai capability limit domain
- mempromosikan requested date hanya karena sebagian ticker berhasil
- menganggap manual fallback sebagai merge bebas antar-source
- membiarkan dua source sama-sama dianggap source-of-truth untuk satu publication yang sama
- memakai majority vote atau best-effort merge tanpa kontrak baru

---

## Cross-contract alignment
Harus sinkron dengan:
- `Source_Data_Acquisition_Contract_LOCKED.md`
- `EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`
- `Run_Status_and_Quality_Gates_LOCKED.md`
- `CONSUMER_READ_CONTRACT_LOCKED.md`
- `../ops/Commands_and_Runbook_LOCKED.md`
