# CONSUMER READ CONTRACT (LOCKED)

## Purpose
Mengunci aturan downstream consumer agar watchlist dan consumer lain tidak bisa bypass publication safety, coverage gate, atau effective-date resolution.

Dokumen ini adalah kontrak ringkas yang downstream-facing dan enforceable.
Ia melengkapi `Downstream_Consumer_Read_Model_Contract_LOCKED.md`, tetapi menjadi entry contract yang lebih tegas untuk implementasi consumer.

---

## Allowed consumer inputs
Consumer boleh meminta:
- requested trade date `T`
- optional consumer-specific filters yang tidak mengubah publication resolution

Consumer tidak boleh meminta:
- raw table terbaru
- latest row by recency
- requested date override yang melanggar readability resolution

---

## Consumer read rule (LOCKED)
Consumer wajib membaca hanya dari:
- publication yang `SEALED`
- publication yang `is_current = 1`
- requested/effective date yang lolos readability contract
- current publication pointer / effective-date resolution yang sah

---

## Required resolution order (LOCKED)
Untuk request tanggal `T`, consumer wajib:
1. resolve apakah `T` readable
2. bila readable, ambil current sealed publication untuk `T`
3. bila tidak readable, resolve `trade_date_effective` menurut readability contract
4. bila fallback ada, baca current sealed publication untuk `trade_date_effective`
5. baca bars / indicators / eligibility hanya dari publication context itu

---

## Explicitly allowed
Consumer BOLEH:
- membaca publication manifest/current pointer
- membaca bars, indicators, dan eligibility yang terikat ke publication context ter-resolve
- menggunakan `trade_date_effective` yang diberikan upstream
- menolak read bila publication resolution ambigu

---

## Explicitly forbidden
Consumer DILARANG:
- query raw table langsung tanpa publication context
- hitung ulang indicator
- bangun ulang eligibility dari bars/indicators ad hoc
- pakai `MAX(date)` sebagai resolver
- pakai `MAX(run_id)` atau `MAX(updated_at)` sebagai resolver publication
- bypass coverage gate
- memilih superseded publication untuk normal read flow
- menentukan sendiri tanggal readable berdasarkan recency guessing
- merge dua publication dalam satu logical read

---

## Effective-date ownership
`trade_date_effective` adalah output upstream readability/publication contract.
Consumer wajib memakainya apa adanya.
Consumer tidak boleh menghitung sendiri effective date dengan logika alternatif.

---

## Ambiguity rule
Jika consumer tidak bisa membuktikan tepat satu publication current + sealed untuk date yang diresolve, maka:
- read harus gagal aman
- consumer tidak boleh fallback ke raw table
- consumer tidak boleh memilih date terbaru sebagai substitusi diam-diam

---

## Correction safety rule
Bila ada correction resmi untuk trade date D:
- consumer otomatis membaca publication corrected yang current
- publication lama menjadi audit-only
- consumer tidak perlu dan tidak boleh menulis branch logic sendiri untuk memilih versi lama

---

## Minimum join rule
Setiap logical read harus konsisten pada satu publication context:
- `trade_date`
- `publication_id`
- bars/indicators/eligibility berasal dari context yang sama

---

## Enforceable implementation consequences
Implementasi consumer yang patuh kontrak harus bisa menunjukkan minimal:
- dari pointer/readability mana `trade_date_effective` didapat
- `publication_id` mana yang sedang dibaca
- bahwa publication itu sealed/current/readable
- bahwa query bars/indicators/eligibility dibatasi ke publication context yang sama

---

## Cross-contract alignment
Harus sinkron dengan:
- `Downstream_Consumer_Read_Model_Contract_LOCKED.md`
- `Consumer_Readability_Decision_Table_LOCKED.md`
- `Run_Status_and_Quality_Gates_LOCKED.md`
- `EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`
- `Historical_Correction_and_Reseal_Contract_LOCKED.md`

---

## Anti-ambiguity rule (LOCKED)
Jika dua implementer yang jujur masih bisa membuat dua query consumer berbeda untuk request yang sama dan keduanya tampak valid, maka kontrak consumer read ini belum cukup keras.
Pada baseline ini, perilaku itu dianggap tidak boleh terjadi.
