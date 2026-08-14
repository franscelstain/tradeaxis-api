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

## Date-level anomaly checks (LOCKED)

Row-level validation cannot, by construction, see a pattern across rows. A defect affecting many instruments on one acquisition date presents as many individually admissible rows, and every per-row rule passes.

This contract therefore owns the date-level checks and their thresholds. At minimum, for each requested trade date:

- **Zero-volume share.** The proportion of delivered bars with `volume = 0`, compared against the dataset baseline and against neighbouring trading dates. A materially elevated share is an acquisition-fault finding for the date, not a market observation about the instruments.
- **Flat-bar share.** The proportion of bars whose `open`, `high`, `low`, and `close` are identical. Elevation here has the same acquisition-fault reading and additionally suppresses true range for every dependent window.
- **Cross-field contradiction count.** The number of rows rejected under the cross-field consistency rule in `EOD_Bars_Contract.md`. A non-zero count concentrated on one date is systematic, not incidental.

Rules:

- Thresholds are configured values bound to the run's configuration snapshot, never implicit judgement.
- A date-level finding is quality evidence. It does not delete or alter rows; it blocks or holds according to the gate rules above and feeds the correction lifecycle.
- Comparison against neighbouring dates uses governed trading days, never calendar days.
- Absence of a date-level finding is not evidence the date is clean; these checks detect concentration, and a defect spread evenly across dates produces no concentration to detect.

## Capability boundary (LOCKED)

**What the quality gates prove.** That each declared gate was evaluated for the run, that its outcome and reason codes are explicit, that a failing gate blocks or holds rather than degrading silently, and that concentration-style defects on a single acquisition date are surfaced.

**What they cannot prove.**

- **That the data is correct.** Every gate here tests a declared property. A defect with no declared gate passes all of them, and the run reports clean.
- **That a defect exists at all, when it is evenly spread.** The date-level checks detect concentration by comparison against neighbouring trading dates. A fault affecting every date equally shifts the baseline it is compared against and produces no signal.
- **That a passing threshold means a normal date.** Thresholds are configured boundaries, not statements about the market. A date just under its threshold is not thereby healthy.
- **That the gate set is complete.** Gates are added when a failure mode is known. Silence about an unknown failure mode is the default state, not a finding.

Consequently a clean run status may be cited as evidence that **every declared gate passed**, never as evidence that **the requested date is correct**. This is the same limit the coverage, canonicalization, and detection contracts state for their own mechanisms, and it composes with theirs rather than covering for them.

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
