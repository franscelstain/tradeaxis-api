# MARKET DATA PLATFORM (EOD)

## Purpose
Market Data Platform adalah domain upstream untuk menghasilkan dataset EOD yang canonical, deterministic, auditable, dan aman dibaca downstream.

---

## Official architecture split
Mulai baseline ini, flow resmi dibagi menjadi dua phase.

### 1. Import phase
Fungsi resmi import:
- source acquisition
- ticker-level processing
- mapping
- dedup
- validation
- canonical bars write
- invalid-row write
- bars coverage evidence
- telemetry

Command resmi import:
- `market-data:daily`
- `market-data:backfill`

### 2. Promote phase
Fungsi resmi promote:
- coverage validation
- indicators
- eligibility
- hash
- seal
- finalize

Command resmi promote:
- `market-data:promote`

Tidak ada alias command promote lain.

---

## Date-driven capability (LOCKED)
Kapabilitas domain ini wajib **date-driven**.

Artinya sistem harus mampu:
- menerima **requested trade date** tunggal apa pun
- menerima **date range** apa pun untuk trading dates yang valid
- menjalankan import untuk tanggal historical maupun tanggal terbaru
- memperlakukan tanggal target sebagai input domain utama, bukan turunan dari default provider

Kontrak ini berlaku untuk seluruh jalur import dan promote.
Kemampuan domain tidak boleh dibatasi oleh default query provider seperti `range=10d`.

---

## Deterministic platform decisions now locked
Keputusan final lintas dokumen:
- coverage minimum untuk requested-date readability = **95%**
- partial import boleh terjadi, tetapi partial readable publication tidak boleh
- sistem memilih **data cukup lengkap tapi boleh lebih lambat** dibanding **data cepat tapi belum cukup lengkap**
- source order resmi = primary `api_free/yahoo_finance`, secondary controlled recovery `manual_file`
- conflict resolution = **source priority + validation + correction flow**, bukan voting dan bukan merge bebas
- consumer hanya boleh membaca publication yang sealed/current/readable melalui effective-date pointer contract
- consumer dilarang membaca raw table tanpa publication context, dilarang memakai `MAX(date)`, dan dilarang menghitung ulang indicator
- silent rewrite historical published data dilarang

---

## Provider limitation abstraction (LOCKED)
Provider hanyalah mekanisme transport.
Provider **bukan** source of truth domain dan **bukan** penentu batas capability sistem.

Untuk jalur default aktif `yahoo_finance`:
- request dilakukan per ticker
- provider gratis dapat rate limit
- provider dapat memiliki default query window seperti `range=10d`
- provider dapat membutuhkan parameter seperti `period1` / `period2` atau windowing ekuivalen

Konsekuensinya:
- limitation provider wajib diisolasi di source adapter / import strategy
- domain tetap wajib mendukung arbitrary date request
- import strategy boleh memakai windowing, explicit date range, looping batch, retry, backoff, atau mekanisme lain yang menjaga kontrak date-driven
- default provider window tidak boleh dianggap sebagai batas historis resmi platform

---

## Storage and maintenance policy summary (LOCKED)
Ringkasan resmi:
- canonical bars, indicators, eligibility, runs, publications, correction trail, dan current pointer adalah record jangka panjang dan tidak boleh di-purge dengan TTL operasional biasa
- historical correction tidak boleh override diam-diam; harus lewat correction + reseal + supersession trail
- artifact evidence non-authoritative boleh dipurge sesuai retention contract, tetapi minimum audit evidence harus tetap dipertahankan selama window retention resminya
- maintenance dilakukan dengan partitioning by trade date sebagai default baseline

---

## Consumer safety summary (LOCKED)
Watchlist dan consumer lain wajib:
- baca hanya dari publication (sealed + readable + current)
- resolve `trade_date_effective` dari publication pointer / readability contract
- pakai publication context saat membaca bars/indicators/eligibility

Watchlist dan consumer lain dilarang:
- query raw table langsung tanpa publication context
- pakai `MAX(date)`
- hitung ulang indicator
- bypass coverage gate
- menentukan tanggal sendiri dengan recency guessing

---

## What this domain owns
Domain ini tetap menjadi owner untuk:
- canonical EOD bars
- indicators
- eligibility/readiness semantics
- coverage gate semantics
- seal/publication/readability behavior
- replay and correction behavior
- upstream audit evidence
- date-driven import contract

---

## Reading order
Untuk memahami baseline implementasi baru, baca urutan ini:
1. `book/Terminology_and_Scope.md`
2. `book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`
3. `book/Source_Data_Acquisition_Contract_LOCKED.md`
4. `book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`
5. `book/CONSUMER_READ_CONTRACT_LOCKED.md`
6. `book/Run_Status_and_Quality_Gates_LOCKED.md`
7. `book/EOD_Data_Retention_and_History_Rewrite_Policy_LOCKED.md`
8. `ops/Commands_and_Runbook_LOCKED.md`
9. `ops/Bootstrap_and_Backfill_Runbook_LOCKED.md`
10. `ops/Performance_SLO_and_Limits_LOCKED.md`

---

## Anti-assumption rules (LOCKED)
Dokumen dalam domain ini tidak boleh lagi menyatakan atau menyiratkan bahwa:
- provider default adalah source of truth domain
- `range=10d` adalah capability limit platform
- sistem hanya ditujukan untuk recent-only ingestion
- `market-data:daily` menjalankan jalur publish/readability
- `market-data:backfill` otomatis mempublish dataset
- coverage dihitung dari successful request count
- import success berarti requested date readable
- import menjalankan indicators, eligibility, hash, seal, atau finalize
- consumer boleh membaca raw table tanpa publication context
- publish cepat boleh mengalahkan coverage/readability safety

---

## State
Documentation baseline ini adalah target resmi untuk sesi implementasi berikutnya.

Status:
- READY FOR IMPLEMENTATION
- DATE-DRIVEN READY
- IMPORT vs PROMOTE CONSISTENT
- CRITICAL EDGE CONTRACTS LOCKED
