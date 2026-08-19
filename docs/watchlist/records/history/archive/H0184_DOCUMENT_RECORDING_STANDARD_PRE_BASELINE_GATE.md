# Watchlist Document Recording and Lifecycle Standard

> **Status:** CANONICAL GOVERNANCE  
> **Scope:** seluruh `docs/watchlist/`  
> **Purpose:** memastikan setiap fakta, perubahan, hasil, temuan, keputusan, dan histori dicatat pada tempat yang benar dan tidak dapat diubah diam-diam.
> **Physical architecture owner:** [`DOCUMENTATION_ARCHITECTURE.md`](DOCUMENTATION_ARCHITECTURE.md). Current root grouping is `authority/` → `development/` → `records/`; document-level lifecycle/mutability in this standard remains authoritative even when a file lives inside a mutable working group.

## 1. Core Principle — No Silent Semantic Update

Tidak ada **semantic record** penting yang boleh berubah tanpa jejak.

Aturan utama:

1. satu fakta hanya mempunyai satu role/owner yang jelas;
2. record yang sudah immutable tidak boleh ditulis ulang;
3. record yang mutable boleh diperbarui hanya sesuai lifecycle-nya dan perubahan material harus traceable;
4. koreksi tidak boleh menghapus fakta lama; koreksi harus menunjuk record yang dikoreksi;
5. keputusan baru tidak boleh mengubah keputusan lama; gunakan supersession;
6. implementation boleh berkembang, tetapi tidak boleh diam-diam mengubah strategy atau technical contract meaning;
7. navigation/README boleh dirapikan, tetapi tidak boleh menjadi tempat menyisipkan business rule baru;
8. history/archive tidak pernah menjadi current fallback authority.

Perubahan whitespace, typo, ejaan, formatting, atau perbaikan link yang **tidak mengubah arti** dianggap editorial dan tidak wajib membuat decision record. Perubahan yang mengubah meaning, status, acceptance, technical contract, result, atau interpretation adalah semantic change dan wajib mengikuti standar ini.

---

## 2. Mutability Classes

### A. `IMMUTABLE_AFTER_ISSUE`

Berlaku untuk:

- final evidence/result;
- issued decision;
- research/preregistration yang sudah `LOCKED`;
- archived/history record;
- immutable artifact/manifest yang memang berfungsi sebagai bukti.

**Rule:** isi record tidak boleh ditulis ulang. Jika salah, buat record koreksi/superseding baru yang mereferensikan record lama.

### B. `CONTROLLED_REVISION`

Berlaku untuk:

- canonical strategy;
- canonical governance.

**Rule:** perubahan semantic hanya sah melalui finding/evidence/impact/decision yang sesuai. Versi sebelumnya harus dipertahankan bila perubahan mengubah behavior/authority materially.

### C. `MUTABLE_TRACEABLE`

Berlaku untuk:

- implementation contract/guidance;
- implementation status/ledger/tracker;
- open finding lifecycle;
- README/index/navigation;
- audit checklist/prompt;
- draft research sebelum lock.

**Rule:** boleh diperbarui, tetapi semantic/material change harus mempunyai jejak pada `DOCUMENT_CHANGE_LOG.md` dan evidence/status terkait bila perubahan berasal dari implementation work.

Tidak ada kelas **“bebas edit tanpa jejak”** untuk semantic content.

---

## 3. Lifecycle per Document Type

| Document type | Lifecycle | Mutability | Update rule |
|---|---|---|---|
| Strategy | `DRAFT -> CANONICAL -> SUPERSEDED` | Controlled revision | semantic revision wajib finding + evidence + decision; old authority dipertahankan |
| Governance | `DRAFT -> CANONICAL -> SUPERSEDED` | Controlled revision | material authority/process change wajib decision + archived prior authority |
| Implementation contract/guidance | `DRAFT -> CURRENT -> SUPERSEDED` | Mutable traceable | boleh update untuk alignment/refactor; material contract change wajib change-log + tests/evidence |
| Implementation status/ledger | `ACTIVE/APPEND` | Append-oriented | current summary boleh berubah; historical entries tidak boleh ditulis ulang |
| Stage register | active/terminal state per stage | Mutable traceable + append event log | current summary boleh berubah; attempt/event history dan evidence tidak boleh di-rewrite |
| Research design | `DRAFT -> LOCKED -> COMPLETED/CANCELLED` | Draft mutable, locked immutable | setelah LOCKED, hypothesis/threshold/universe/gate tidak boleh diubah |
| Evidence/result | `FINAL` | Immutable | koreksi = evidence correction baru; original tetap utuh |
| Finding | `OPEN -> ACCEPTED/REJECTED/RESOLVED` | Lifecycle-update only | observation asli tetap; status/resolution ditambahkan/dirujuk |
| Decision | `DRAFT -> ISSUED -> SUPERSEDED` | Issued immutable | keputusan berubah = decision baru yang supersede decision lama |
| History/archive | `ARCHIVED` | Immutable | tidak diedit agar cocok dengan current state |
| README/index | `CURRENT` | Mutable traceable | navigation saja; dilarang membuat business/technical contract baru |
| Audit/checklist | `CURRENT` | Mutable traceable | acceptance/audit-rule change harus tercatat dan defer ke owner |

---

## 4. Mandatory Record Metadata

Record baru yang material harus memiliki metadata minimum yang cukup untuk menjawab **apa ini, statusnya apa, scope-nya apa, dan terhubung ke apa**.

### Minimum common metadata

```text
Document Type: <STRATEGY|GOVERNANCE|IMPLEMENTATION|STAGE_STATUS|RESEARCH|EVIDENCE|FINDING|DECISION|HISTORY>
Status: <lifecycle status>
Scope: watchlist / weekly_swing
Record ID: <stable id when record-based>
Created: YYYY-MM-DD
```

### Additional metadata when applicable

```text
Strategy Stage: WS-Sxx
Related Finding: <id/path>
Related Evidence: <id/path>
Related Decision: <id/path>
Supersedes: <id/path>
Superseded By: <id/path>
Run / Artifact Identity: <identity>
```

Tidak semua field wajib untuk setiap file. Field yang tidak relevan boleh dihilangkan. **Evidence, finding, decision, locked research, dan superseding record harus memiliki stable Record ID.**

Existing historical campaign files tidak wajib di-rename massal; aturan metadata/ID ini berlaku untuk record baru dan file yang materially direvisi setelah standard ini aktif.

---

## 5. Stable ID Convention for New Records

Gunakan ID pendek dan tidak bergantung pada filename panjang:

```text
F-WS-YYYYMMDD-NN   # finding
D-WS-YYYYMMDD-NN   # decision
E-WS-YYYYMMDD-NN   # evidence/correction evidence
R-WS-YYYYMMDD-NN   # new research/preregistration record when no campaign identity exists
DOC-CHG-YYYYMMDD-NN # documentation change-log entry
```

Campaign ID existing seperti `C171`, `B01`, `P01` boleh tetap dipakai jika memang identity resmi record tersebut.

---

## 6. Update vs New Record Decision Rule

### Edit existing file in place only when

- typo/format/link correction tanpa semantic change;
- README/navigation update;
- mutable implementation guidance/contract sedang diselaraskan dan old meaning tidak diperlukan sebagai historical authority;
- current summary pada ledger/tracker diperbarui tanpa menghapus entry lama;
- finding ditambahkan lifecycle status/resolution tanpa mengubah observation asli.

Material in-place semantic update pada `MUTABLE_TRACEABLE` **wajib** dicatat di `DOCUMENT_CHANGE_LOG.md`.

### Create a new record when

- evidence/result baru tersedia;
- evidence lama perlu dikoreksi;
- issued decision berubah;
- locked research perlu rule/threshold berbeda;
- finding baru ditemukan;
- strategy/governance materially berubah dan membutuhkan approved revision;
- implementation contract berubah sedemikian besar sehingga old contract perlu dipertahankan untuk migration/replay/audit.

---

## 7. Evidence Correction Rule

Evidence adalah fakta apa yang benar-benar tercatat pada saat run/review tertentu.

Jika evidence salah:

```text
EVIDENCE_ORIGINAL     # tetap immutable
        ↓
EVIDENCE_CORRECTION   # record baru
        ↓
menjelaskan error, corrected value, impact, dan record yang digantikan untuk interpretation
```

Dilarang membuka evidence final dan mengganti angka/result agar sesuai dengan pengetahuan terbaru.

---

## 8. Research Lock Rule

Sebelum `LOCKED`, draft research boleh diperbaiki.

Setelah `LOCKED`:

- hypothesis;
- universe;
- thresholds;
- score/gate;
- date split;
- acceptance criteria

menjadi immutable untuk experiment identity tersebut.

Jika ingin mengubahnya, buat research identity/version baru. Hasil experiment masuk `../../records/evidence/`, bukan ditambahkan ke preregistration seolah sudah diketahui dari awal.

---

## 9. Finding Rule

Finding mempunyai dua bagian konseptual:

1. **original observation** — tidak boleh ditulis ulang;
2. **lifecycle/resolution** — boleh ditambahkan atau direferensikan.

Finding tidak mengubah strategy atau implementation contract secara otomatis. Dampak material harus diputuskan melalui decision yang sesuai.

---

## 10. Decision Rule

Decision berstatus `ISSUED` adalah immutable.

Jika keputusan berubah:

```text
D-OLD  ISSUED
  ↓ superseded by
D-NEW  ISSUED
```

Dilarang mengubah isi `D-OLD` dari GO menjadi NO-GO, atau sebaliknya.

---

## 11. Implementation Change Rule

Implementation adalah layer yang paling sering berubah, tetapi perubahan tetap harus traceable.

### Non-material technical edit

Contoh:
- rename local variable;
- refactor internal tanpa contract/behavior change;
- query optimization dengan output identical;
- formatting/comment.

Tidak memerlukan finding/decision. Test harus tetap valid bila relevant.

### Material implementation/contract edit

Contoh:
- DTO/API field meaning berubah;
- persistence shape/identity berubah;
- reason semantics berubah;
- validation behavior berubah;
- stage input/output contract berubah;
- migration mengubah compatibility behavior.

Wajib:

1. defer ke canonical strategy;
2. catat pada `DOCUMENT_CHANGE_LOG.md`;
3. tambahkan/update tests;
4. jalankan recurring Residue & Conformance Check sesuai [`IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md`](IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md);
5. simpan evidence hasil validasi termasuk residue/conformance verdict;
6. update implementation status/contract tracker;
7. jika ternyata perubahan membutuhkan business-rule change, berhenti dan gunakan strategy-change process.

Implementation stage tidak boleh dinyatakan `DONE` hanya karena path baru bekerja. Harus dibuktikan juga bahwa tidak ada reachable harmful legacy path yang masih dapat mengubah current behavior/proof.

---

## 12. Residue / Conformance Recording Rule

Implementation residue adalah recurring implementation concern, bukan strategy baru. Gunakan [`IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md`](IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md).

Recording minimum:

- harmful residue material → finding + remediation + evidence;
- controlled compatibility residue → exact semantic mapping + tests + evidence;
- historical-only residue → tetap immutable pada history/evidence dan tidak menjadi current fallback;
- dead residue → jangan diklaim dead tanpa reachability evidence;
- residue evidence terakhir harus dapat ditemukan dari current stage register/attempt lineage.

Search/grep hanya discovery aid. Ia tidak cukup sebagai bukti bahwa implementation sudah bebas harmful residue.

---

## 13. Ledger / Tracker Rule

Ledger/tracker adalah append-oriented current-status record.

Diperbolehkan:
- memperbarui current summary/active session;
- menambahkan entry baru;
- menandai entry lama sebagai superseded melalui entry baru.

Dilarang:
- menghapus session lama;
- mengubah hasil historical session agar sesuai current state;
- menggunakan tracker sebagai business-rule owner.

Koreksi historical entry harus dibuat sebagai **correction entry baru** yang menunjuk entry asli.

---


## 14. Stage Execution / Re-entry / Closure Rule

Setiap current implementation stage wajib mengikuti [`STAGE_EXECUTION_AND_REWORK_STANDARD.md`](STAGE_EXECUTION_AND_REWORK_STANDARD.md).

Key rules:

- attempt yang gagal tidak boleh hilang; tutup attempt dengan evidence dan convergence;
- rerun wajib membaca latest attempt + open finding + active decision/remediation + change log;
- `DONE` hanya berarti declared stage objective/exit criteria tercapai;
- valid negative verdict pada evaluation/proof stage boleh coexist dengan `DONE` jika objective stage memang menghasilkan verdict sah;
- repeated failure, elapsed time, atau personal judgement tidak pernah cukup untuk terminal closure;
- `WAITING_VERIFIED_DEPENDENCY` tetap active dan harus memiliki evidence + resume trigger;
- terminal unresolved closure membutuhkan high burden of proof + reviewed decision;
- successor/decomposition tidak boleh menjadi cara mengganti nomor stage untuk menghindari masalah;
- current resume pointer wajib dijaga di `../../development/implementation/WS_IMPLEMENTATION_STAGE_REGISTER.md`.

Historical campaign status taxonomy tidak ditulis ulang, tetapi bukan authority lifecycle untuk `WS-Bxx` baru.

---

## 15. README / Index Rule

README/index hanya boleh:

- menunjukkan navigation/read order;
- menjelaskan role folder;
- menunjukkan current pointer/entry point;
- memperingatkan authority boundary.

README/index **tidak boleh** menjadi tempat mendefinisikan threshold, score formula, acceptance gate, persistence contract, atau hasil experiment baru.

---

## 16. Filename and Path Discipline

Untuk menjaga Windows-safe dan readability:

- gunakan semantic filename;
- hindari numeric prefix sebagai reading order kecuali number adalah domain/campaign identity;
- target filename `<= 80` karakter bila memungkinkan;
- target relative path dari `docs/` `<= 120` karakter;
- jangan membuat child folder baru hanya untuk satu/dua file jika role sudah jelas dari layer;
- history tetap flat di `../../records/history/archive/` dengan `ARCHIVE_INDEX.csv`.

Record ID harus disimpan di metadata; filename tidak perlu memuat seluruh kalimat keputusan.

---

## 17. Cross-Link and Traceability Rule

Material change idealnya dapat ditelusuri sebagai:

```text
strategy/governance owner
        ↓
implementation/research activity
        ↓
evidence
        ↓
finding (jika ada masalah/insight)
        ↓
decision (jika ada perubahan authority/behavior)
        ↓
new current owner / superseded history
```

Tidak semua pekerjaan membutuhkan seluruh rantai. **Namun tidak boleh ada semantic authority change tanpa alasan yang dapat ditelusuri.**

---

## 18. Documentation Change Log

`DOCUMENT_CHANGE_LOG.md` adalah append-only log untuk **material documentation lifecycle/change event**.

Wajib dicatat ketika:

- strategy/governance direvisi;
- implementation contract meaning materially berubah;
- documentation architecture/path materially berubah;
- current authority/supersession berubah;
- locked record dikoreksi melalui replacement/correction;
- migration besar mengubah cara pembaca menemukan owner docs.

Tidak perlu mencatat setiap typo/format-only edit.

---

## 19. Hard Prohibitions

Dilarang:

1. menulis ulang final evidence;
2. menulis ulang issued decision;
3. mengubah locked research;
4. menghapus historical ledger/session entry;
5. mengedit archive agar terlihat current;
6. menambah business rule ke README/audit/tracker/implementation tanpa strategy owner;
7. mengubah strategy karena code kebetulan bekerja berbeda;
8. mengubah implementation contract materially tanpa trace;
9. membuat file baru tanpa role/lifecycle yang jelas;
10. memakai file yang lebih baru sebagai authority hanya karena tanggalnya lebih baru;
11. menutup stage karena repeated failure/time/fatigue tanpa terminal evidence;
12. rerun stage tanpa membaca current prior-attempt lineage;
13. memakai `DONE` ketika stage objective/exit criteria belum tercapai.

---

## 20. Enforcement

Setiap implementation/audit session harus memeriksa:

- document role benar;
- lifecycle/status benar;
- mutability rule dipatuhi;
- material semantic change mempunyai change-log entry;
- evidence/decision/locked research tidak rewritten;
- correction/supersession trace tersedia;
- current owner dan history dapat dibedakan;
- stage re-entry/attempt/convergence/closure mengikuti `STAGE_EXECUTION_AND_REWORK_STANDARD.md`;
- current stage pointer di stage register sinkron dengan evidence/finding/decision.

Audit checklist aktif harus menilai standard ini sebagai bagian Documentation Layer Guard.

## 15. Strategy Traceability / Coverage Recording Rule

Canonical strategy-to-implementation coverage mengikuti [`STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md`](STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md) dan current matrix [`STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv`](STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv).

Aturan recording tambahan:

1. setiap current atomic strategy requirement harus mempunyai stable `rule_id` pada matrix;
2. matrix adalah current mutable-traceable coverage index, bukan business-rule owner;
3. perubahan strategy material harus menandai row lama `SUPERSEDED` dan membuat rule ID baru bila meaning berubah;
4. `SATISFIED` membutuhkan implementation/test/evidence/residue trace yang sah;
5. finding/regression yang membuka kembali behavior wajib menurunkan coverage status sampai revalidation selesai;
6. final 100% strategy coverage claim dilarang bila ada mandatory row selain `SATISFIED`;
7. optional CONFIRM `OPTIONAL_NOT_REQUESTED` tidak memblokir core coverage.

Matrix row tidak boleh dihapus hanya untuk meningkatkan persentase coverage. Historical/superseded row dipertahankan sesuai traceability standard.
