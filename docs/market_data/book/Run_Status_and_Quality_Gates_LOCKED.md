# RUN STATUS AND QUALITY GATES (LOCKED)

## Purpose
Mengunci cara requested date berpindah dari hasil import menuju outcome final platform.

Dokumen ini sekarang mengikuti arsitektur **IMPORT vs PROMOTE split**.

---

## Phase ownership (LOCKED)

### Import phase
Import phase hanya menghasilkan:
- canonical bars
- invalid rows
- import telemetry
- bars coverage evidence

Import phase tidak memberi readable success.
Import phase tidak membuat seal.
Import phase tidak memutuskan final consumer publishability.

### Promote phase
Promote phase memiliki ownership untuk:
- coverage validation
- indicators
- eligibility
- hash
- seal
- finalize
- terminal status
- publishability state
- effective readable date resolution

---

## Allowed terminal status
Terminal status platform yang diizinkan tetap:
- `SUCCESS`
- `HELD`
- `FAILED`

Tidak ada terminal enum baru yang diperkenalkan oleh split ini.

---

## Publishability state
Publishability minimum:
- `READABLE`
- `NOT_READABLE`

Requested date hanya boleh `READABLE` bila promote phase menyelesaikan seluruh precondition sukses.

---

## Promote preconditions for readable success (LOCKED)
Requested date hanya boleh berakhir `SUCCESS + READABLE` bila seluruh syarat ini terpenuhi:

1. import phase selesai tanpa fatal corruption pada persisted bars
2. bars coverage gate = `PASS`
3. indicator artifact berhasil dihitung
4. eligibility artifact berhasil dibangun
5. required hash berhasil dihitung
6. dataset berhasil diseal
7. finalize menetapkan requested date/readable candidate itu sah untuk consumer

Jika satu saja gagal, requested date tidak boleh dianggap readable.

---

## HELD
Requested date harus berakhir `HELD` bila requested date tidak readable, tetapi sistem masih berada pada jalur operasional yang terkontrol.

Contoh minimum:
- coverage gate `FAIL` dan prior readable fallback tersedia
- source/import result parsial membuat requested date tidak lolos promote, tetapi prior readable fallback masih tersedia
- source blocker terjadi dan implementation memilih menahan requested date sambil tetap menandai `NOT_READABLE`

`HELD` bukan readable success.
`HELD` bukan publish baru untuk requested date.

---

## FAILED
Requested date harus berakhir `FAILED` bila:
- terjadi fatal failure global pada import atau promote
- coverage/readiness tidak bisa dievaluasi secara terpercaya dan tidak ada jalur aman yang dipilih implementation
- indicator / eligibility / hash / seal gagal tanpa safe continuation
- publication/readability decision tidak dapat dipertahankan secara audit-safe

`FAILED` menunjukkan run tidak dapat diselesaikan dengan outcome operasional yang dapat diterima untuk requested date itu.

---

## Coverage interaction (LOCKED)
Coverage dipakai di promote phase sebagai gate resmi.

- `PASS` memungkinkan promote lanjut ke stage berikutnya
- `FAIL` membuat requested date non-readable
- `BLOCKED` membuat requested date non-readable dan menandakan basis evaluasi tidak aman/tidak bermakna

Coverage sendiri tidak otomatis memberi `SUCCESS`.
Coverage hanya salah satu gate wajib di promote.

---

## Source-blocker interpretation after split
Per-ticker source blocker pada import tidak otomatis berarti `FAILED`.
Yang menentukan outcome final adalah hasil persisted bars + coverage + promote decision.

Artinya:
- partial import boleh terjadi
- requested date tetap bisa ditahan (`HELD`) atau gagal (`FAILED`) di promote
- requested date tidak boleh pernah dibaca sebagai sukses hanya karena sebagian ticker berhasil diimport

---

## Required audit-visible final fields
Minimum field final yang harus audit-visible:
- `trade_date_requested`
- `trade_date_effective`
- `terminal_status`
- `publishability_state`
- `coverage_gate_state`
- final reason/final outcome note minimum
- fallback outcome bila ada

---

## Anti-ambiguity rules
Implementasi/dokumen tidak boleh:
- menyebut import selesai sebagai publish success
- menyebut per-ticker request success ratio sebagai readability gate
- memotong phase boundary sehingga `market-data:daily` atau `market-data:backfill` dianggap otomatis menjalankan indicators/seal/finalize tanpa command promote
- memperlakukan `HELD` sebagai requested-date readable success

---

## Cross-contract alignment
Harus sinkron dengan:
- `EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`
- `Dataset_Seal_and_Freeze_Contract_LOCKED.md`
- `EOD_Cutoff_and_Finalization_Contract_LOCKED.md`
- `../ops/Commands_and_Runbook_LOCKED.md`
