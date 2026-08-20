# Watchlist Strategy-to-Implementation Traceability and Coverage Standard

> **Status:** CANONICAL GOVERNANCE  
> **Scope:** current Watchlist Weekly Swing canonical strategy → implementation/proof coverage  
> **Canonical matrix:** [`STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv`](STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv)  
> **Business-rule owner:** No — strategy meaning tetap dimiliki `../strategy/`.

## 1. Purpose

Standard ini memastikan klaim **"seluruh mandatory Weekly Swing strategy sudah terpenuhi"** hanya dapat dibuat bila setiap atomic strategy requirement mempunyai trace dari strategy owner menuju implementation/proof, test, evidence, dan residue verdict.

Stage-level `DONE` saja tidak cukup untuk membuktikan tidak ada strategy requirement yang tertinggal.

## 2. Canonical Coverage Unit

Setiap substantive non-heading clause dari current canonical strategy owner diinventaris pada matrix dengan stable `rule_id`. Clause diklasifikasikan `REQUIRED` bila harus mempunyai implementation/proof trace atau `REFERENCE_ONLY` bila hanya navigation/purpose/lifecycle metadata. Pendekatan ini mencegah definition/invariant penting hilang hanya karena tidak ditulis sebagai bullet `harus`.

Current baseline inventory:

- total canonical strategy clauses inventoried: **1006**;
- required traceability units: **823**;
- reference-only/navigation units: **183**;
- mandatory/conditional required units: **708**;
- optional CONFIRM required units: **115**;
- current canonical strategy owner files inventoried: **14**.

Matrix adalah **coverage index**, bukan business-rule owner. Jika `rule_text` di matrix dan strategy owner berbeda, strategy owner menang dan matrix harus ditandai stale lalu diperbarui melalui traceable documentation change.

## 3. Stable Rule ID Rule

`rule_id` yang sudah diterbitkan tidak boleh dipakai ulang untuk semantic requirement berbeda.

Jika strategy wording berubah tanpa meaning change, row boleh mempertahankan ID setelah fingerprint/reference diperbarui secara traceable.

Jika strategy meaning berubah material:

1. row lama dipertahankan dan `active=NO`, `coverage_status=SUPERSEDED`;
2. decision strategy-change harus direferensikan;
3. rule baru mendapat ID baru;
4. inherited implementation/test/evidence tidak otomatis dianggap memenuhi rule baru;
5. rule baru kembali membutuhkan verification/residue evidence.

Dilarang renumber massal hanya agar matrix terlihat rapi.

## 4. Required Matrix Columns

| Column | Meaning |
|---|---|
| `rule_id` | stable requirement identity |
| `active` | current rule atau superseded |
| `strategy_stage` | `WS-Sxx` strategy stage |
| `rule_class` | scope/data/core/optional/proof/orchestration |
| `coverage_requirement` | `REQUIRED` atau `REFERENCE_ONLY` |
| `applicability` | mandatory, conditional-active, atau optional capability |
| `strategy_owner` | canonical owner file; use current Watchlist-root-relative path such as `authority/strategy/WS_*.md` |
| `source_heading` / `source_line` | human navigation pointer |
| `rule_text` | copied/normalized source clause; bukan authority baru |
| `rule_fingerprint_sha1` | stale-detection helper |
| `verification_build_stage` | primary `WS-Bxx` closure stage |
| `implementation_ref` | code/contract/config mapping |
| `test_ref` | test/fixture/check that proves behavior |
| `evidence_ref` | immutable evidence result |
| `residue_state` | recurring residue/conformance verdict |
| `coverage_status` | current satisfaction state |
| `proof_or_runtime_verdict` | separate business/proof verdict when applicable |
| `open_finding` | unresolved gap pointer |

### Path Reference Convention

Path references in current matrix rows must follow the current `authority / development / records` architecture. Historical paths belong in historical evidence/history records, not in current mapping columns. Physical rename without semantic rule change may update path columns/fingerprints editorially while preserving stable `rule_id`.

## 5. Coverage Status Vocabulary

Allowed current statuses:

- `REFERENCE_ONLY` — clause diinventaris untuk completeness/navigation tetapi bukan unit yang harus dipetakan ke code/test;
- `NOT_ASSESSED` — required unit belum diperiksa terhadap current code/proof;
- `MAPPED_UNVERIFIED` — implementation mapping sudah diketahui tetapi belum dibuktikan;
- `IMPLEMENTED_UNVERIFIED` — behavior tampak ada tetapi test/evidence/residue belum lengkap;
- `SATISFIED` — current strategy rule terimplementasi/terpenuhi dan mempunyai evidence yang sah;
- `FAILED_REMEDIATION_OPEN` — current attempt gagal dan remediation masih aktif;
- `WAITING_VERIFIED_DEPENDENCY` — menunggu dependency yang terbukti dan mempunyai resume trigger;
- `INCONCLUSIVE` — evidence belum cukup;
- `OPTIONAL_NOT_REQUESTED` — optional capability tidak diminta dan tidak memblokir core;
- `OPTIONAL_SATISFIED` — optional capability diminta dan sudah terpenuhi;
- `SUPERSEDED` — rule tidak lagi current setelah controlled strategy revision.

`SATISFIED` tidak boleh diberikan hanya karena class/file/code ditemukan.

## 6. Mandatory Satisfaction Gate

Satu active row dengan `coverage_requirement=REQUIRED` dan applicability mandatory/conditional hanya boleh `SATISFIED` bila minimal:

1. exact strategy owner dan identity diketahui;
2. implementation mapping eksplisit (`implementation_ref`);
3. positive behavior test sesuai kebutuhan;
4. negative/fail-closed test bila relevan;
5. immutable evidence menunjuk hasil validasi current identity;
6. residue state = `CONFORMANT_NO_HARMFUL_RESIDUE_FOUND` atau `CONFORMANT_WITH_CONTROLLED_COMPATIBILITY`;
7. tidak ada open finding yang membuktikan rule masih salah/incomplete.

Untuk rule yang tidak membutuhkan code (misalnya orchestration/boundary), `implementation_ref` dapat menunjuk governance/config/build artifact yang benar-benar menegakkan requirement tersebut; tetap harus ada evidence yang relevan.

## 7. Stage `DONE` Gate

Sebelum implementation stage `WS-Bxx` dinyatakan `DONE`:

- semua active matrix row dengan `verification_build_stage = WS-Bxx` dan applicability mandatory/active harus `SATISFIED`;
- optional CONFIRM row dapat tetap `OPTIONAL_NOT_REQUESTED` bila capability tidak diminta;
- tidak boleh ada `NOT_ASSESSED`, `MAPPED_UNVERIFIED`, `IMPLEMENTED_UNVERIFIED`, `FAILED_REMEDIATION_OPEN`, `WAITING_VERIFIED_DEPENDENCY`, atau `INCONCLUSIVE` pada mandatory rule stage tersebut;
- recurring residue gate tetap wajib menurut `IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md`.

Support stage yang memang tidak memiliki direct strategy row tetap menggunakan Definition of Done teknisnya, tetapi tidak boleh menutup gap strategy milik stage lain.

## 8. Final 100% Strategy Coverage Gate

Klaim **100% mandatory strategy implementation coverage** hanya sah bila:

```text
active mandatory/conditional rules = SATISFIED mandatory/conditional rules
open mandatory coverage gaps       = 0
unassessed mandatory rules          = 0
harmful residue open                = 0
```

Optional CONFIRM `OPTIONAL_NOT_REQUESTED` tidak menurunkan core coverage.

Klaim ini **berbeda** dari production/proof success. Contoh: evaluator OOS dapat terimplementasi 100% dan seluruh OOS traceability rule `SATISFIED`, tetapi valid OOS business verdict tetap dapat `FAIL`. Dalam kondisi itu implementation coverage lengkap, production-readiness tetap tidak lulus.

## 9. Re-entry / Rework Rule

Pada rerun `WS-Bxx`, programmer wajib membaca matrix rows stage tersebut bersama:

- stage register;
- latest attempt evidence;
- open findings;
- active remediation/decision;
- known residue evidence.

Row yang belum `SATISFIED` adalah explicit resume backlog. Programmer tidak boleh memilih hanya rule yang mudah dan mengabaikan row lain.

## 10. Strategy Change Synchronization

Setiap controlled strategy revision wajib melakukan traceability impact check sebelum change dianggap selesai:

1. identifikasi row terdampak;
2. pertahankan/supersede stable IDs sesuai semantic impact;
3. tambahkan rule baru yang belum ada;
4. set affected coverage ke revalidation state;
5. update stage register/implementation plan bila mapping berubah;
6. jangan mewariskan evidence lama tanpa proof identity yang sah.

Strategy revision tidak boleh dianggap complete bila matrix masih kehilangan current rule.

## 11. Implementation Change Synchronization

Material implementation change wajib memperbarui matrix bila:

- implementation owner/path berubah;
- test mapping berubah;
- evidence baru menggantikan interpretation lama;
- residue compatibility path berubah;
- satu rule berpindah verification stage;
- finding membuka kembali rule yang sebelumnya dianggap satisfied.

Jika regression/reintroduced residue ditemukan, `SATISFIED` wajib diturunkan ke state yang sesuai sampai revalidation selesai.

## 12. Audit Rule

Audit completeness wajib menjawab dua pertanyaan berbeda:

1. **Stage completion:** apakah stage objective/exit criteria selesai?
2. **Strategy coverage:** apakah setiap mandatory rule yang menjadi tanggung jawab stage sudah `SATISFIED`?

Audit tidak boleh menyimpulkan 100% strategy coverage hanya dari jumlah stage `DONE`, test-suite global PASS, atau absence of known finding.

## 13. Current Initialization

Matrix baseline dibuat dari seluruh current canonical strategy owner. Karena source code belum diaudit terhadap baseline ini:

- seluruh substantive clause strategy diinventaris;
- `REFERENCE_ONLY` row dimulai `REFERENCE_ONLY` dengan implementation/test/evidence/residue `N/A`;
- required mandatory/conditional row dimulai `NOT_ASSESSED`;
- required optional CONFIRM row dimulai `OPTIONAL_NOT_REQUESTED`;
- required `implementation_ref`, `test_ref`, dan `evidence_ref` dimulai `TBD`;
- required `residue_state` dimulai `NOT_ASSESSED`.

Ini bukan klaim bahwa implementation tidak ada; ini berarti belum ada current traceability evidence yang sah terhadap canonical strategy terbaru.

## 14. One-line Rule

> **No mandatory Weekly Swing strategy rule may disappear inside a stage summary: every rule must be individually traceable to implementation/proof, test, evidence, residue verdict, and final satisfaction state.**

## Baseline / Attempt Binding

`SATISFIED` evidence harus dapat ditelusuri ke:

`rule_id -> WS-Bxx -> Attempt ID -> Baseline ID -> implementation/test/residue/integrity evidence`.

Baseline ID membuktikan authority/starting state yang dipakai ketika rule diverifikasi. Row tidak boleh dinaikkan menjadi `SATISFIED` dari evidence yang tidak mempunyai attempt/baseline provenance setelah Work Baseline Lock Standard aktif.

Jika baseline authority drift terjadi selama verification, row tetap/revert ke non-satisfied state sampai revalidation pada baseline baru selesai.

## 15. Work / Evidence Correlation Binding

Saat row `REQUIRED` dinaikkan ke `SATISFIED`, current evidence harus dapat ditelusuri ke Stage ID + Attempt/Work ID + Baseline ID. Current evidence Record ID harus diregister pada Work Record Registry; legacy evidence boleh direferensikan sebagai supporting historical input tetapi tidak menggantikan current conformance evidence bila current rule behavior telah berubah.

## Current Verification Epoch Gate

For verification epoch `WS-REBASELINE-20260819-001`:

- all required mandatory/conditional rows start/restart `NOT_ASSESSED` unless produced under this epoch;
- historical/pre-epoch evidence may only be supporting context;
- `SATISFIED` requires current Work Record evidence whose Baseline Lock carries the active epoch;
- no old PASS/OOS/shadow/production verdict can be inherited directly into current coverage.
