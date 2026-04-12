# SOURCE DATA ACQUISITION CONTRACT (LOCKED)

## Purpose
Mengunci kontrak akuisisi source untuk **IMPORT PHASE**.

Source acquisition hanya bertanggung jawab membawa raw/provider payload sampai menjadi canonical bar rows yang tervalidasi.
Source acquisition bukan owner readability consumer.

---

## Phase boundary (LOCKED)
Source acquisition berada sepenuhnya di **IMPORT PHASE**.

Tanggung jawabnya:
- request source payload berdasarkan tanggal target eksplisit
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

Winner untuk satu run ditentukan oleh source mode run itu sendiri.
Satu run hanya boleh punya **satu source-of-truth mode** untuk publication candidate yang sedang dibangun.

Artinya:
- run `api_free` tidak boleh diam-diam mengisi sebagian ticker dari `manual_file` lalu mengaku satu source tunggal
- run `manual_file` yang sah boleh menjadi source-of-truth hanya bila memang dijalankan sebagai recovery/correction resmi

---

## Row acceptance rule
Satu canonical bar hanya boleh diterima bila:
- ticker dapat dipetakan sah
- trade date source dapat dibuktikan cocok dengan requested trade date target
- field minimum lolos validasi row-level
- row tersebut memenangkan dedup rule resmi

Row yang gagal syarat ini harus masuk invalid/rejection evidence, bukan ikut numerator coverage.

---

## Minimum storage outputs
Minimum storage outputs dari source acquisition:
- canonical valid bars
- invalid/rejected rows atau evidence ekuivalen
- import telemetry minimum
- source provenance minimum (`source_mode`, `source_name`, `ingested_at`, `run_id` atau ekuivalennya)
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
- requested date evidence yang eksplisit
- ticker universe count
- ticker attempted count
- ticker success count
- ticker failure count
- failure reason summary
- invalid-row evidence
- coverage input metrics
- source-of-truth identity

Promote phase boleh menolak requested date bila coverage tidak cukup.
Import phase sendiri tidak boleh mengklaim requested date readable.

---

## Conflict handling (LOCKED)
Jika akuisisi menemukan perbedaan antara source aktif dan source recovery pada ticker/date yang sama:
- jangan merge row antar source
- jangan pilih nilai terbaru berdasarkan timestamp provider saja
- gunakan source priority contract
- jalankan correction/recovery flow bila memang mau mengganti source-of-truth publication

---

## Anti-ambiguity rules
Tidak boleh:
- menganggap fetch success = publish success
- menulis canonical row tanpa provenance source minimum
- menerima row untuk requested date yang tidak bisa dibuktikan cocok
- menggabungkan dua source menjadi satu publication candidate tanpa kontrak correction/recovery
- menurunkan quality bar hanya demi menyelesaikan import lebih cepat

---

## Cross-contract alignment
Harus sinkron dengan:
- `EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`
- `EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`
- `Run_Status_and_Quality_Gates_LOCKED.md`
- `CONSUMER_READ_CONTRACT_LOCKED.md`
- `../ops/Commands_and_Runbook_LOCKED.md`
