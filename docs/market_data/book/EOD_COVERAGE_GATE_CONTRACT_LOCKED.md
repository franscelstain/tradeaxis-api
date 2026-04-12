# EOD COVERAGE GATE CONTRACT (LOCKED)

## Purpose
Mengunci cara coverage dihitung, cara kualitas data dibaca terhadap coverage, dan kapan requested date boleh menjadi `READABLE`.

Coverage gate dievaluasi pada **PROMOTE PHASE** menggunakan hasil bars yang sudah dihasilkan oleh **IMPORT PHASE**.

---

## Date-driven compatibility (LOCKED)
Coverage gate wajib tetap konsisten walaupun sistem bersifat date-driven.

Artinya:
- arbitrary date request tidak otomatis menghasilkan readable success
- historical import dan recent import dinilai dengan gate yang sama
- perluasan capability tanggal tidak mengubah numerator, denominator, threshold, hard-fail semantics, atau outcome gate

---

## Locked numerator
Coverage numerator harus dihitung dari jumlah ticker/date yang memiliki **canonical valid bars** setelah:
- mapping
- dedup
- validation

Coverage numerator tidak boleh dihitung dari:
- request success count
- raw payload count
- indicator rows
- eligibility rows

---

## Locked denominator
Coverage denominator mengikuti `Coverage_Universe_Definition_LOCKED.md`.
Denominator bukan jumlah ticker yang sempat di-request.
Denominator bukan jumlah ticker yang sempat merespons provider.
Denominator tetap harus sesuai requested trade date yang sedang dievaluasi.

---

## Locked minimum threshold
Minimum coverage threshold platform adalah **0.95 (95%)**.

Artinya:
- `coverage_ratio >= 0.95` dapat dinilai `PASS` bila syarat kualitas keras juga lolos
- `coverage_ratio < 0.95` harus dinilai `FAIL`
- tidak ada threshold alternatif per command, per provider, atau per mode tanpa perubahan kontrak eksplisit

Sistem ini memilih **data cukup lengkap dan boleh lebih lambat** dibanding **data lebih cepat tetapi belum cukup lengkap**.
Requested date tidak boleh dipromosikan hanya demi mengejar kecepatan bila coverage belum mencapai threshold.

---

## Gate states
Allowed states:
- `PASS`
- `FAIL`
- `BLOCKED`

Makna:
- `PASS`: ratio dievaluasi, memenuhi threshold, dan tidak ada hard data-quality blocker
- `FAIL`: ratio dievaluasi secara sah tetapi di bawah threshold
- `BLOCKED`: coverage tidak dapat dievaluasi secara aman/bermakna karena prerequisite, evidence basis, atau integritas dataset rusak

---

## Coverage vs data-quality behavior (LOCKED)
Coverage dan data quality diputuskan dengan aturan berikut:

### Soft row-level defects
Defect berikut diperlakukan sebagai **soft defect** pada level import:
- sebagian ticker gagal di-fetch setelah retry budget habis
- sebagian payload provider kosong untuk ticker tertentu
- sebagian row ditolak validasi row-level
- sebagian ticker/date missing sehingga numerator coverage berkurang

Soft defect **boleh** terjadi pada import evidence.
Tetapi requested date hanya boleh lanjut bila hasil akhirnya tetap memenuhi coverage gate.

### Hard dataset-integrity blockers
Kondisi berikut diperlakukan sebagai **hard blocker**:
- requested trade date tidak jelas atau mismatch dengan payload final yang dipersist
- mapping/dedup/validation menghasilkan basis canonical bars yang tidak dapat dipercaya
- coverage denominator tidak dapat dibuktikan
- evidence coverage hilang/rusak
- source date/window mismatch membuat valid-bar set tidak dapat dipastikan milik requested date
- corruption global pada persisted bars, indicator prerequisites, atau publication prerequisites

Hard blocker harus menghasilkan `BLOCKED`, bukan `PASS` dan bukan `soft success`.

---

## Retry and partial behavior (LOCKED)
Aturan resmi:
- retry dilakukan dulu untuk failure class transient pada **IMPORT PHASE**
- partial import **diizinkan hanya sebagai status import**, bukan sebagai consumer-readable success
- setelah retry budget habis, hasil yang ada tetap dievaluasi oleh coverage gate
- tidak ada bypass yang mengizinkan promote/readability hanya karena sebagian data sudah tersedia cepat

Ringkasnya:
- **partial allowed in import**
- **partial not allowed for requested-date readability unless final canonical coverage still reaches 95% and no hard blocker exists**

---

## Promote usage (LOCKED)
Coverage gate adalah bagian dari **PROMOTE PHASE**.

Urutan resminya:
1. import phase menulis canonical valid bars + invalid rows + telemetry
2. promote phase membaca bars hasil import
3. promote phase mengevaluasi coverage untuk requested trade date target
4. hanya bila coverage `PASS`, promote boleh lanjut ke indicators / eligibility / hash / seal / finalize

Coverage gate tidak dijalankan dari request-success ratio pada import.
Coverage gate tidak boleh dipindahkan menjadi sekadar provider-health metric.

---

## Outcome matrix
- `PASS` → promote boleh lanjut
- `FAIL` → requested date tetap `NOT_READABLE`
- `BLOCKED` → requested date tetap `NOT_READABLE`

Readable success tetap memerlukan stage promote lain yang lolos.

---

## Deterministic decision matrix
| Condition | Gate state | Requested-date readability |
|---|---|---|
| coverage ratio >= 95% dan tidak ada hard blocker | `PASS` | boleh lanjut ke promote stages berikutnya |
| coverage ratio < 95% tetapi masih bisa dihitung sah | `FAIL` | `NOT_READABLE` |
| coverage tidak bisa dibuktikan / basis integritas rusak | `BLOCKED` | `NOT_READABLE` |

---

## Required audit-visible fields
Minimum field yang harus tampak:
- `requested_trade_date`
- `coverage_universe_count`
- `coverage_available_count`
- `coverage_ratio`
- `coverage_min_threshold`
- `coverage_gate_state`
- `hard_blocker_present`
- `coverage_decision_reason`

---

## Anti-ambiguity rules
Tidak boleh:
- menghitung coverage dari successful HTTP requests
- menghitung coverage sebelum mapping/dedup/validation selesai
- menyamakan import completeness dengan readiness success
- melonggarkan gate hanya karena provider transport punya batas query window
- menganggap historical date memiliki gate yang berbeda dari recent date tanpa contract terpisah yang eksplisit
- menafsirkan partial import sebagai partial readable publication
- memilih data cepat tapi belum cukup lengkap sebagai readable success

---

## Cross-contract alignment
Harus sinkron dengan:
- `Coverage_Universe_Definition_LOCKED.md`
- `EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`
- `Run_Status_and_Quality_Gates_LOCKED.md`
- `CONSUMER_READ_CONTRACT_LOCKED.md`
- `../ops/Commands_and_Runbook_LOCKED.md`
