# Watchlist Documentation Architecture

> **Status:** CANONICAL GOVERNANCE  
> **Purpose:** Menentukan letak setiap jenis informasi agar strategy, implementation, research, evidence, findings, decisions, dan history tidak kembali tercampur.

## Architecture

```text
docs/watchlist/
├── START_HERE.md     # single entry point / reading + build navigation; not a business-rule owner
├── governance/       # authority, change control, recording lifecycle, traceability, audit
├── strategy/         # canonical Weekly Swing behavior
├── implementation/   # technical translation
├── research/         # hypothesis/preregistration/candidate experiment
├── evidence/         # actual results/artifacts/status ledger
├── findings/         # discovered problem/insight record
├── decisions/        # issued decisions / supersession
└── history/          # immutable superseded/migration/archive
```

## Authority Order

1. `governance/` menentukan cara membaca, mencatat, mengubah, dan mengoreksi dokumen.
2. `strategy/` adalah owner perilaku Weekly Swing.
3. `implementation/` harus tunduk pada strategy; implementation tidak boleh membuat business rule baru.
4. `research/`, `evidence/`, `findings/`, `decisions/`, dan `history/` tidak boleh diam-diam mengubah strategy.
5. Universal no-silent-update/lifecycle rule berada di `DOCUMENT_RECORDING_STANDARD.md`.
6. Perubahan strategy hanya sah melalui `DOCUMENT_CHANGE_POLICY.md`.
7. Rule-by-rule implementation coverage dimiliki governance melalui `STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` + `STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv`; keduanya tidak menjadi business-rule owner.

## Mutability Summary

| Layer | Default mutability | Core rule |
|---|---|---|
| governance | controlled revision | material authority change harus traceable + prior authority dipertahankan |
| strategy | controlled revision | material behavior change melalui finding/evidence/decision |
| implementation | mutable traceable | material contract meaning change harus change-log + test/evidence |
| research | draft mutable; locked immutable | setelah preregistration/identity LOCKED tidak boleh rewrite |
| evidence | immutable/append-only | correction record baru; original tidak diubah |
| findings | lifecycle-update only | original observation tetap |
| decisions | issued immutable | perubahan keputusan melalui superseding decision |
| history | immutable | archive tidak diedit agar cocok current state |
| README/index/audit | mutable traceable | navigation/audit only; tidak membuat owner rule baru |

Detailed lifecycle: [`DOCUMENT_RECORDING_STANDARD.md`](DOCUMENT_RECORDING_STANDARD.md).

## Role Rules

### Strategy
Berisi **apa** yang ingin dicapai dan aturan perilaku Weekly Swing. Default-nya stabil. Progress campaign, hasil test, SHA1, command operator, dan outcome historis dilarang ditambahkan ke strategy.

### Implementation
Berisi **bagaimana** software memenuhi strategy. Boleh berubah karena refactor, schema/API translation, test implementation, atau optimasi teknis selama tidak mengubah semantics strategy. Material technical-contract change harus traceable.

### Research
Berisi sesuatu yang belum canonical. Research `DRAFT` boleh diperbaiki; setelah `LOCKED` experiment identity immutable. Research yang berhasil tidak otomatis menjadi strategy.

### Evidence
Berisi apa yang benar-benar terjadi. Evidence final historical/append-only dan tidak boleh ditulis ulang agar cocok keputusan terbaru. Correction adalah record baru.

### Findings
Berisi masalah/insight. Original observation tidak boleh ditulis ulang; lifecycle/resolution boleh ditambahkan/dirujuk.

### Decisions
Berisi keputusan eksplisit. Issued decision immutable. Perubahan keputusan memakai superseding decision baru.

### History / Superseded
Berisi aturan/addendum/snapshot lama. Immutable dan tidak boleh dipakai sebagai fallback current behavior.

### Governance / README / Audit
Governance menentukan authority/process; README mengarahkan pembaca; audit menilai conformance. Ketiganya tidak boleh diam-diam menjadi business-rule owner.

## Implementation Reading Rule

Implementer harus membaca dengan urutan:

`governance -> strategy -> implementation -> evidence/current status`

Implementer **tidak boleh** membaca campaign history lalu menganggapnya rule canonical hanya karena lebih baru secara tanggal.

## Single Entry Point Rule

`../START_HERE.md` adalah halaman pertama resmi. Ia hanya mengurutkan authority yang sudah ada; tidak membuat business rule baru.

Detailed technical build order berada di `../implementation/WS_IMPLEMENTATION_BUILD_SEQUENCE.md`. Strategy stage dependency tetap dimiliki `../strategy/WS_END_TO_END_STRATEGY_LIFECYCLE.md`.


## Traceability Layer Rule

Traceability berada di governance karena ia mengikat strategy owner ke implementation/test/evidence tanpa menyalin ownership business rule. Matrix current bersifat mutable-traceable; stable rule ID/supersession menjaga histori coverage.
