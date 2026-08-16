# Watchlist Documentation Architecture

> **Status:** CANONICAL GOVERNANCE
> **Purpose:** Menentukan letak setiap jenis informasi agar strategy, implementation, research, evidence, findings, decisions, dan history tidak kembali tercampur.

## Architecture

```text
docs/watchlist/
├── governance/       # aturan otoritas dokumen dan cara perubahan
├── strategy/         # tujuan/aturan Weekly Swing yang canonical dan stabil
├── implementation/   # translation strategy ke schema, service, API, test, procedure, fixture
├── research/         # hypothesis, preregistration, candidate design, calibration experiment
├── evidence/         # hasil aktual: IS/OOS/shadow/runtime/operator output/artifacts/ledger
├── findings/         # masalah/temuan/diagnostic record
├── decisions/        # keputusan yang menerima/menolak perubahan, GO/NO-GO, promotion/closure
└── history/          # superseded contract dan append history yang tidak lagi authoritative
```

## Authority Order

1. `governance/` menentukan cara membaca dan mengubah dokumen.
2. `strategy/weekly_swing/` adalah owner perilaku Weekly Swing.
3. `implementation/` harus tunduk pada strategy; implementation tidak boleh membuat business rule baru.
4. `research/`, `evidence/`, `findings/`, `decisions/`, dan `history/` tidak boleh diam-diam mengubah strategy.
5. Perubahan strategy hanya sah melalui change rule pada `DOCUMENT_CHANGE_POLICY.md`.

## Role Rules

### Strategy
Berisi **apa** yang ingin dicapai dan aturan perilaku Weekly Swing. Default-nya stabil. Progress C/R/S/P, hasil test, SHA1, command operator, dan outcome historis dilarang ditambahkan ke strategy.

Canonical strategy owner file hanya boleh berisi objective, scope bisnis, behavioral rule, formula/threshold strategy, gate, invariant, dan acceptance criteria. Metadata document-role/change-control, reading order, ownership map, audit instruction, implementation pointer, physical schema/storage, command/test/fixture, serta migration/superseded note harus berada di README/governance/implementation/history sesuai perannya.

### Implementation
Berisi **bagaimana** software memenuhi strategy. Boleh berubah karena refactor, schema/API translation, test implementation, atau optimasi teknis selama tidak mengubah semantics strategy.

### Research
Berisi sesuatu yang belum canonical: hypothesis, candidate, preregistration, bounded remediation, calibration, dan experiment design. Research yang berhasil tidak otomatis menjadi strategy.

### Evidence
Berisi apa yang benar-benar terjadi. Evidence bersifat historical/append-only dan tidak boleh ditulis ulang agar cocok dengan keputusan terbaru.

### Findings
Berisi masalah atau insight yang ditemukan dari implementation/research/evidence. Finding belum mengubah strategy sampai ada keputusan yang menerima dampaknya.

### Decisions
Berisi keputusan eksplisit berdasarkan evidence/finding. Jika keputusan mengubah behavior canonical, keputusan harus menyebut strategy docs yang terdampak dan versi lama harus dipertahankan di history/superseded.

### History / Superseded
Berisi aturan atau addendum lama yang pernah relevan tetapi tidak menjadi current authority. History tidak boleh dibaca sebagai fallback strategy.

## Implementation Reading Rule

Implementer harus membaca dengan urutan:

`governance -> strategy -> implementation -> evidence/current status`

Implementer **tidak boleh** membaca campaign history lalu menganggapnya sebagai rule canonical hanya karena rule tersebut lebih baru secara tanggal.
