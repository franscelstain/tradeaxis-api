# PERFORMANCE SLO AND LIMITS (LOCKED)

## Purpose
Mengunci pembacaan SLO operasional agar tidak bentrok dengan boundary kontrak import vs promote.

Dokumen ini mengatur target operasional dan batas interpretasi.
Dokumen ini tidak boleh dipakai untuk melonggarkan contract readiness, coverage, atau consumer safety.

---

## Core principle (LOCKED)
Performa penting, tetapi **ketepatan tanggal, traceability source, dan consumer safety lebih penting**.

Karena itu:
- sistem memilih data yang cukup lengkap dan dapat diaudit dibanding publish cepat yang belum aman
- SLO tidak boleh dipakai untuk menurunkan threshold coverage
- SLO tidak boleh dipakai untuk mengizinkan bypass promote
- SLO tidak boleh dipakai untuk mengubah source-of-truth rules

---

## Import phase SLO

### Daily import target
Untuk satu requested trading date:
- import diharapkan selesai dalam waktu operasional yang wajar untuk ticker universe resmi
- partial failure per ticker tetap dianggap penyelesaian import yang valid selama command tidak crash dan evidence tersimpan
- requested date harus tetap tercatat eksplisit pada evidence import

### Historical backfill target
Untuk range tanggal:
- progress harus dievaluasi per trading date, bukan sekadar per keseluruhan range
- satu tanggal yang mengalami banyak per-ticker failure tidak boleh menjatuhkan seluruh range secara tidak terklasifikasi
- resumability lebih penting daripada fail-fast semu
- batching historical range adalah perilaku operasional utama, bukan pengecualian

### Import telemetry minimum
Import harus cukup observably complete untuk operator:
- ticker universe count
- ticker attempted count
- ticker success count
- ticker failure count
- failure reason summary
- bars coverage evidence minimum
- requested date / date range evidence
- source identity / source mode evidence

---

## Promote phase SLO

### Promote latency target
Untuk satu requested date yang import evidence-nya sudah siap dan coverage `PASS`:
- indicators, eligibility, hash, seal, finalize harus selesai dalam jalur promote yang konsisten
- target operasional promote harus dibaca terpisah dari import runtime

### Coverage-first rule
Promote tidak boleh menghabiskan resource ke indicators, eligibility, hash, seal, atau finalize bila coverage belum lolos.
Coverage adalah gate awal promote.

---

## Rate-limit / timeout interpretation
Pada jalur Yahoo aktif:
- `429` dan timeout diperlakukan sebagai per-ticker import failure transient
- itu bukan alasan otomatis untuk menghentikan seluruh import date-run
- outcome final requested date diputuskan dari bars coverage dan promote result
- keberadaan default query window seperti `10d` harus dianggap problem transport strategy, bukan batas domain capability

---

## Hard limits that SLO may not override
SLO tidak boleh mengoverride aturan berikut:
- coverage minimum = **95%**
- requested date `READABLE` hanya setelah promote sukses penuh
- satu publication candidate hanya memiliki satu source-of-truth mode
- consumer hanya boleh membaca publication sealed/current/readable
- silent rewrite data lama dilarang

---

## Operational alert guidance
Operator harus menganggap ini sebagai tanda masalah penting:
- failure ratio ticker tinggi pada import
- bars coverage mendekati threshold minimum
- coverage di bawah threshold
- promote gagal sebelum seal/finalize
- hash/seal/finalize gagal setelah coverage pass
- historical range membutuhkan retry berulang karena provider limitation yang persisten
- source conflict memerlukan correction/recovery run resmi

---

## Outcome interpretation
- import partial dengan evidence lengkap masih bisa dianggap import selesai
- requested date baru bisa readable setelah promote sukses penuh
- bila coverage gagal, requested date tetap `NOT_READABLE`
- bila fallback/readability policy memilih `HELD`, itu tetap bukan requested-date success
- performa yang baik tidak pernah membenarkan readable publication yang tidak aman

---

## Storage growth and maintenance guardrail
SLO ini juga harus dibaca bersama policy storage:
- data canonical/published disimpan jangka panjang
- maintenance dilakukan lewat partitioning, archive, dan purge artifact non-authoritative
- purge tidak boleh menghapus bukti minimum yang masih berada dalam retention contract

---

## Cross-contract alignment
Harus sinkron dengan:
- `Commands_and_Runbook_LOCKED.md`
- `../book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`
- `../book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`
- `../book/EOD_Data_Retention_and_History_Rewrite_Policy_LOCKED.md`
- `../book/CONSUMER_READ_CONTRACT_LOCKED.md`
