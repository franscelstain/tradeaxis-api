# BOOTSTRAP AND BACKFILL RUNBOOK (LOCKED)

## Purpose
Menjamin bootstrap/backfill mengikuti split arsitektur baru.

Backfill sekarang adalah **import-range command**, bukan full publish pipeline.

---

## Date-driven role of backfill (LOCKED)
Backfill adalah **first-class citizen** untuk historical ingestion.

Artinya:
- backfill bukan fitur tambahan
- backfill adalah mekanisme inti untuk memasukkan historical bars berdasarkan tanggal target operator
- backfill harus mampu memproses range tanggal trading apa pun yang sah
- backfill harus tetap relevan baik untuk bootstrap awal maupun recovery historical date tertentu

---

## Official sequence

### Step 1 — Import historical bars
Jalankan:
- `market-data:backfill {start_date} {end_date}`

Tujuan:
- import bars per trading day
- simpan invalid rows
- simpan telemetry
- simpan bars coverage evidence minimum
- mempertahankan requested date identity per hari dalam range

### Step 2 — Review import readiness
Operator menilai:
- tanggal mana yang import evidence-nya cukup
- tanggal mana yang perlu diulang
- tanggal mana yang mungkin akan ditolak promote karena coverage lemah
- tanggal mana yang terkena provider limitation tetapi belum menjadi fatal run-level blocker

### Step 3 — Promote selected dates
Jalankan:
- `market-data:promote {requested_date}`

Promote menangani:
- coverage validation
- indicators
- eligibility
- hash
- seal
- finalize

---

## Yahoo-specific note
Karena active Yahoo path berjalan per ticker dan rawan `429`:
- backfill harus partial-tolerant
- backfill tidak boleh fail-fast hanya karena satu ticker/date mengalami rate limit
- default Yahoo window seperti `10d` tidak boleh dianggap batas historis sistem
- implementation boleh memakai windowing, `period1` / `period2`, looping batch, retry, backoff, dan throttle
- keputusan publishability tetap dipindahkan ke promote

---

## Scalability and resumability expectation
Backfill wajib diperlakukan sebagai jalur operasional yang scalable.

Minimum expectation:
- batch-aware per tanggal trading
- retry-aware untuk failure transient
- aman dijalankan ulang per range bila operating model mengizinkan
- tidak menyamakan partial provider failure dengan kegagalan total seluruh historical range

---

## Acceptance expectations
Bootstrap/backfill dianggap benar bila:
- bars historis berhasil diimport terpisah dari publishability
- coverage evidence tersedia
- promote hanya dijalankan dari bars hasil import
- tidak ada asumsi bahwa backfill otomatis menghasilkan readable publication
- tidak ada asumsi bahwa provider recent window menentukan batas historical ingestion

---

## Cross-contract alignment
Harus sinkron dengan:
- `Commands_and_Runbook_LOCKED.md`
- `../book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`
- `../book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`
