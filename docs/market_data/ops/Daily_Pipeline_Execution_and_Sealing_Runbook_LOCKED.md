# DAILY IMPORT AND PROMOTE RUNBOOK (LOCKED)

## Current State
READY FOR IMPLEMENTATION

---

## Purpose
Dokumen ini menjelaskan alur operasional harian setelah phase dipisah.

Command yang dipakai:
- `market-data:daily`
- `market-data:promote`

---

## Date-driven note
Walaupun dokumen ini fokus pada flow harian, `market-data:daily` tetap command yang menerima **1 requested trade date eksplisit**.
Dokumen ini tidak boleh dibaca sebagai recent-only shortcut yang mengikuti default provider window.

---

## Daily operator flow
1. jalankan `market-data:daily` untuk tanggal target
2. review import evidence minimum
3. review ticker failure summary
4. review bars coverage evidence minimum
5. bila requested date perlu dibuka ke downstream, jalankan `market-data:promote`

---

## Import expectations
`market-data:daily` hanya mengerjakan import:
- acquisition
- ticker loop
- mapping
- dedup
- validation
- write bars
- write invalid rows
- write telemetry
- write bars coverage evidence minimum

Import harian tidak boleh:
- menghitung indicators
- membangun eligibility
- hash
- seal
- finalize

---

## Coverage rule
Coverage dinilai pada promote, menggunakan canonical valid bars hasil import.
Coverage tidak berasal dari request-success count.

Evidence minimum yang harus terlihat untuk operator:
- resolved coverage universe
- `coverage_universe_count`
- `coverage_available_count`
- `coverage_ratio`
- `coverage_gate_state` bila promote sudah dijalankan

---

## Promote expectations
`market-data:promote` adalah satu-satunya jalur untuk:
- coverage validation
- indicators
- eligibility
- hash
- seal
- finalize

Requested date hanya boleh readable bila:
- import evidence tersedia
- coverage lolos
- indicators/eligibility/hash/seal/finalize lolos

---

## Failure interpretation
- per-ticker import failure tidak otomatis menghentikan date-run
- fatal import failure boleh menghentikan import
- coverage fail membuat requested date tetap non-readable
- promote failure setelah coverage pass tetap membuat requested date non-readable

---

## Anti-drift rules
Dokumen ini tidak boleh lagi dipakai untuk menyiratkan bahwa:
- satu command harian langsung menjalankan publish path penuh
- import harian otomatis membuat requested date readable
- coverage basis berasal dari successful request count
- `market-data:daily` hanya cocok untuk tanggal recent karena provider default punya jendela tertentu
