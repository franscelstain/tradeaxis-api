# MARKET DATA PLATFORM (EOD)

## Purpose
Market Data Platform adalah domain upstream yang ditujukan untuk menghasilkan data product EOD yang **valid, decision-grade, point-in-time, reproducible, auditable, stabil, dan aman dibaca downstream**.

`decision-grade` didefinisikan dalam empat kondisi terukur di `book/Terminology_and_Scope.md`, relatif terhadap decision horizon Weekly Swing. Ia adalah **target property, bukan klaim yang sudah terbukti**: tidak ada dokumen yang boleh menyatakan output market-data sudah decision-grade sebelum re-audit order 22 membuktikan keempat kondisinya dengan executed evidence.

Watchlist Weekly Swing saham IDX adalah **initial consumer profile** yang membantu menentukan scope EOD dan kebutuhan field awal; ia bukan pemilik ataupun ukuran kelulusan market-data. Kesiapan market-data dinilai dari kebenaran, kelengkapan, provenance, temporal integrity, reproducibility, publication safety, dan activation-aware freshness. Hasil screening, ranking, sinyal, profitabilitas, atau usefulness strategi tidak boleh menjadi syarat `DOCUMENTATION_STRATEGY_READY`, `IMPLEMENTATION_CONFORMANT`, maupun `OPERATIONALLY_VALIDATED` untuk domain ini.

Yahoo Finance adalah bootstrap source fase sekarang. Arsitektur dan kontrak domain tetap provider-neutral agar source dapat ditingkatkan pada masa depan tanpa mendesain ulang indikator atau consumer contract.

---

## Market and product scope (LOCKED)

Scope canonical utama adalah **saham IDX, Regular Market, End-of-Day (EOD)**:
- satu trade date mengikuti kalender IDX dan timezone platform `Asia/Jakarta`
- bar baru dianggap EOD setelah Regular Market session selesai
- cash market dan negotiated market tidak boleh tercampur diam-diam ke canonical Regular-Market EOD
- tick-by-tick, order book/market depth, intraday/ultra-low-latency data, execution routing, dan multi-exchange platform bukan scope fase sekarang

Alur ownership produk harus satu arah:

`source observation -> canonical RAW EOD -> analytical price product -> indicators/market facts -> data-quality and data-usability facts -> sealed market-data read product`

Market-data berhenti pada read product tersebut. Watchlist boleh mengonsumsinya melalui kontrak terpisah dan tetap memiliki pemilihan kandidat, threshold tradability, alpha/ranking, serta keputusan trading. Batas ini berlaku walaupun implementasi berada di repository yang sama.

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

## Consumer horizon, dataset boundary, dan operating phase

> **Owner: `book/Terminology_and_Scope.md`.** Bagian ini adalah ringkasan orientasi. Definisi, konsekuensi, dan aturan interpretasi lengkap dimiliki dokumen tersebut. Bila keduanya tampak berbeda, Terminology yang berlaku dan README yang harus diperbaiki.

**Decision horizon.** Weekly Swing memiliki decision horizon **5 hari perdagangan IDX** dengan rentang tahan praktis **3 sampai 15 hari perdagangan**, selalu dinyatakan dalam hari perdagangan, bukan hari kalender. Horizon ini melahirkan kewajiban terukur — radius kontaminasi, toleransi keterlambatan, dan biaya warm-up — yang angkanya dimiliki kontrak indikator dan operasi.

**Empat batas waktu yang berbeda dan tidak boleh dicampur:**

| Konsep | Nilai / marker | Artinya |
|---|---|---|
| Intentional dataset start | `2023-01-02` | Baseline awal yang dipilih sengaja, bukan akibat source gagal |
| Archived proof window | `2023-01-02` – `2025-10-31` | Rentang executed evidence lama. **Bukan** dataset end, capability limit, retention limit, atau freshness claim |
| Development data frontier | bergerak | Trade date terakhir yang ter-ingest saat pembangunan. Gap sesudahnya bukan production incident sebelum activation |
| Operational activation | `OPERATIONAL_START_DATE` | Batas ketika freshness operasional mulai wajib. Tidak pernah ditetapkan secara implisit oleh backfill atau proof yang pernah jalan |

Prasyarat operasional sebelum marker activation boleh ditetapkan — marker eksplisit, controlled catch-up, scheduling terbukti, alert dan stale-consumer protection aktif, bukti recovery dari partial failure, dan SLO yang dihitung hanya sejak boundary — dimiliki dan dirinci oleh `book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`. Jumlah dan isinya tidak diulang di sini agar tidak menyimpang dari ownernya.

---

## Data-product terminology

> **Owner: `book/Terminology_and_Scope.md`.** Ringkasan orientasi; definisi penuh dan 15 aturan interpretasi terkunci ada di sana.

Empat hal yang paling sering dikacaukan menjadi satu:

| Istilah | Ringkasnya |
|---|---|
| Raw source observation | Payload provider beserta provenance. Immutable, dan belum menjadi canonical bar |
| `RAW` price product | Canonical EOD OHLCV pada scale yang diobservasi market, tanpa adjustment. **Bukan** sinonim payload provider |
| `STRUCTURAL_ADJUSTED` | OHLC coherent dengan volume inverse bila semantics mengharuskan, hanya dari corporate action terverifikasi dan berversi. Default target basis untuk profil indikator EOD awal |
| `TOTAL_RETURN` | Produk terpisah untuk performance evaluation. Bukan alias `STRUCTURAL_ADJUSTED` |

Provider `adj_close` bukan salah satu dari ketiga price product di atas, tidak boleh menjadi per-row fallback, dan tidak boleh dicampur dengan `close` dalam satu vector. Satu run memakai satu price basis eksplisit dan berversi.

**Coverage**, **quality**, **liquidity**, **event risk**, dan **data usability** adalah lima dimensi terpisah yang wajib tetap dapat dijelaskan sendiri-sendiri. Threshold tradability, preferensi likuiditas, dan kebijakan menghindari event tertentu milik downstream.

Bila factor material belum terverifikasi, affected range di-quarantine atau eligibility diblokir. Anomaly tidak pernah menjadi izin mengubah history atau membuat adjustment otomatis.

---

## Deterministic platform decisions now locked
Keputusan final lintas dokumen:
- minimum delivery-coverage prerequisite untuk requested-date readability = **98%**; coverage pass tidak pernah cukup tanpa hard gates lain
- bounded delivery gap di bawah 2% dapat melewati delivery gate hanya bila denominator temporal benar, seluruh listing yang missing tetap memiliki explicit delivery/eligibility reasons, dan semua independent quality/provenance/product/seal gates lulus
- “partial import” sebagai proses yang belum selesai, belum dievaluasi, atau kehilangan artifact/reason rows tidak boleh menjadi readable publication
- sistem memilih **data cukup lengkap tapi boleh lebih lambat** dibanding **data cepat tapi belum cukup lengkap**
- source order resmi = primary `api_free/yahoo_finance`, secondary controlled recovery `manual_file`
- conflict resolution = **source priority + validation + correction flow**, bukan voting dan bukan merge bebas
- consumer hanya boleh membaca publication yang sealed/current/readable melalui effective-date pointer contract
- consumer dilarang membaca raw table tanpa publication context, dilarang memakai `MAX(date)`, dan dilarang menghitung ulang indicator
- silent rewrite historical published data dilarang

---

## Yahoo Finance current-phase decision

`api_free/yahoo_finance` adalah **bootstrap primary source yang dipilih dengan sengaja** untuk membuktikan bahwa data product market-data dapat dibangun, dioperasikan, dan memberi manfaat pada use case awal sebelum mengeluarkan biaya untuk data berbayar.

Keputusan ini bukan kesalahan strategi, bukan klaim bahwa Yahoo Finance adalah sumber resmi IDX, dan bukan penetapan provider final. Arsitektur canonical market-data tetap provider-neutral dan quality bar tidak diturunkan: provenance, validation, coverage, quarantine, correction, publication, serta readability safety tetap wajib.

Evaluasi vendor, pembelian data, dual-feed, dan migrasi provider bukan pekerjaan fase sekarang. Kelanjutan tersebut baru dipertimbangkan setelah manfaat yang terukur atau kebutuhan SLA, licensing, authoritative correction, field coverage, dan commercial use membenarkannya.

Rationale, safeguards, current non-goals, dan future decision triggers dijelaskan di `book/Yahoo_Finance_Bootstrap_Source_Strategy.md`.

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
Consumer downstream wajib:
- baca hanya dari publication (sealed + readable + current)
- resolve `trade_date_effective` dari publication pointer / readability contract
- pakai publication context saat membaca bars/indicators/eligibility

Consumer downstream dilarang:
- query raw table langsung tanpa publication context
- pakai `MAX(date)`
- hitung ulang indicator
- bypass coverage gate
- menentukan tanggal sendiri dengan recency guessing

---

## What this domain owns
Domain ini tetap menjadi owner untuk:
- immutable source observations dan provenance
- canonical EOD bars
- price-product identity dan adjustment lineage
- versioned indicators
- coverage, quality, liquidity, event-risk, dan data-usability/readiness facts
- coverage gate semantics
- seal/publication/readability behavior
- replay and correction behavior
- upstream audit evidence
- date-driven import contract

---

## Reading order
Untuk memahami baseline implementasi baru, baca urutan ini:
1. `book/Terminology_and_Scope.md`
2. `book/Market_Data_Strategy_Implementation_Blueprint_LOCKED.md`
3. `book/Domain_Boundary_Invariants_LOCKED.md`
4. `book/Market_Data_Implementation_Conformance_Matrix_LOCKED.md`
5. `book/Market_Data_Implementation_Command_Protocol_LOCKED.md`
6. `audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md`
7. `book/Yahoo_Finance_Bootstrap_Source_Strategy.md`
8. owner contracts stage 4–19 sesuai blueprint/matrix
9. `db/Database_Schema_Contracts_MariaDB.md` dan `db/MARKET_DATA_DICTIONARY.md`
10. `tests/README.md` lalu test contracts/fixtures stage 21
11. ops runbooks yang dirujuk setiap stage
12. `audit/reports/AUDIT_FINAL_STATE.md` hanya untuk status audit, bukan untuk menciptakan behavior domain

Untuk mengetahui hasil tiap order beserta angka penentunya, masuk lewat section **Hasil per order — indeks berurutan 1→22** pada dokumen tersebut. Ia berurutan naik dan menunjuk ke detail masing-masing, sementara section detailnya sendiri tersusun menurun mengikuti urutan penulisan.

Blueprint adalah owner work order `W00`–`W22`. Conformance matrix adalah owner assignment/traceability agar tidak ada dokumen, deliverable, proof, atau evidence yang terlewat. Command protocol dan implementation ledger menentukan command yang admitted, bentuk hasil, verdict, remediation loop, dan next permitted command. Makna behavior tetap dimiliki owner contract paling spesifik yang dirujuk pada setiap stage.

Implementasi dijalankan satu work order pada satu waktu. Mulai dengan **`MD-RUN W00 market-data.`**; command itu mengimplementasikan, mengaudit, meremediasi, dan mengaudit ulang hanya `W00` sampai `PASS`, kemudian memberikan exact command **`MD-RUN W01 market-data.`**. Ulangi command successor yang diberikan secara berurutan sampai `W22`; jangan melompati predecessor.

---

## Anti-assumption rules (LOCKED)
Dokumen dalam domain ini tidak boleh lagi menyatakan atau menyiratkan bahwa:
- scope canonical mencampur Regular Market dengan cash/negotiated market
- data sebelum `2023-01-02` hilang akibat kegagalan source dalam scope aktif
- archived proof window adalah dataset end atau freshness claim
- development data frontier adalah capability limit atau production incident sebelum operational activation
- historical proof otomatis menetapkan `OPERATIONAL_START_DATE`
- raw provider payload sama dengan canonical `RAW` price product
- provider `adj_close` adalah coherent adjusted OHLCV product
- coverage, quality, liquidity, event risk, dan eligibility adalah konsep yang sama
- eligibility berarti buy signal, ranking alpha, atau persetujuan strategy
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

## Documentation and implementation state

Status dokumentasi strategi aktif:

> **`DOCUMENTATION_STRATEGY_READY` — canonical data-readiness implementation baseline for IDX Regular-Market EOD**

Artinya scope, semantics, invariants, target data products, implementation order, schema meaning, proof specification, dan operational boundary telah cukup lengkap untuk mengarahkan pembangunan. Status ini tidak menyatakan code, migration, database, tests, scheduler, atau operasi saat ini sudah conformant atau production-ready.

Status ini tidak bergantung pada tersedianya implementasi watchlist atau hasil strategi Weekly Swing. Weekly Swing hanya menjadi initial consumer profile dan compatibility target pada batas read contract.

Implementasi wajib mengikuti work order pada `book/Market_Data_Strategy_Implementation_Blueprint_LOCKED.md`, menutup seluruh assignment pada `book/Market_Data_Implementation_Conformance_Matrix_LOCKED.md`, dan memakai command/result/remediation lifecycle pada `book/Market_Data_Implementation_Command_Protocol_LOCKED.md` beserta current-state ledger. Setelah implementasi selesai, audit terpisah harus menentukan `IMPLEMENTATION_CONFORMANT` dan kemudian, bila activation/evidence gates relevan telah terpenuhi, `OPERATIONALLY_VALIDATED`.

Klaim lama `FULL GLOBAL MARKET-DATA PRODUCTION READY` dan `MARKET_DATA_PRODUCTION_READY_LOCKED` tidak berlaku untuk data-readiness baseline yang telah dikoreksi. Historical proof tetap evidence untuk behavior lama, bukan izin untuk menyimpang dari baseline ini.

State dokumentasi harus dibaca sebagai berikut:
- `2023-01-02` adalah intentional dataset start
- `2023-01-02` sampai `2025-10-31` adalah archived proof window, bukan current-freshness proof
- last ingested trade date adalah development data frontier yang bergerak; nilai evidence as-of mengikuti audit report kanonik
- operational activation belum terjadi sampai marker activation ditetapkan dan seluruh activation gate dibuktikan
- source-state/internal conformance lama bukan klaim official IDX authority, commercial data SLA, redistribution right, atau decision-grade correctness
