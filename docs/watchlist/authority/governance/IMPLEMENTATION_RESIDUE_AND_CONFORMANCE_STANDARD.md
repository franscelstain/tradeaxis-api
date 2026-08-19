# Watchlist Implementation Residue and Conformance Standard

> **Status:** CANONICAL GOVERNANCE  
> **Scope:** seluruh current/future Weekly Swing implementation stage (`WS-Bxx`), successor/decomposition stage, evaluator/proof path, compatibility layer, fixture/test, API/DTO, persistence mapping, config/feature flag, dan implementation documentation yang dapat memengaruhi current behavior  
> **Purpose:** memastikan implementation current tidak menjadi campuran behavior canonical baru dengan sisa behavior lama yang masih reachable atau masih dapat mengubah hasil.

## 1. Core Rule — Residue Check Is a Recurring Quality Gate

**Residue & Conformance Check wajib dilakukan pada setiap implementation stage yang membuat, mengubah, mengganti, memigrasikan, atau memvalidasi behavior.**

Ia bukan audit sekali jalan dan bukan optional cleanup.

Urutan minimum setiap stage:

```text
read current authority
→ implement / remediate
→ functional tests
→ negative / fail-closed tests
→ residue & conformance check
→ remediation jika harmful residue ditemukan
→ validation evidence
→ stage closure evaluation
```

`DONE` untuk implementation stage **dilarang** bila masih ada unresolved harmful residue dalam declared stage scope.

Untuk evaluation/proof stage, verdict tidak boleh dipercaya sebagai proof exact current identity bila evaluator/data path masih mempunyai harmful residue yang dapat mengubah universe, score, ranking, entry/exit, friction, outcome, atau identity yang dievaluasi.

---

## 2. Definition — What Is Implementation Residue?

Implementation residue adalah code, contract, schema semantic, query/path, parameter, config, feature flag, fallback, reason code, serializer, DTO/API field, fixture, test expectation, compatibility alias, documentation instruction, atau execution path lama yang masih tersisa setelah current authority/behavior berubah.

**Keberadaan sesuatu yang lama tidak otomatis berarti salah.** Yang dinilai adalah:

1. apakah masih current/reachable;
2. apakah dapat memengaruhi current behavior atau proof;
3. apakah semantics-nya sesuai canonical strategy + current implementation contract;
4. apakah keberadaannya memang diperlukan untuk compatibility/history;
5. apakah ada evidence yang membuktikan classification tersebut.

---

## 3. Mandatory Residue Classes

### A. `HARMFUL_RESIDUE`

Sisa lama yang masih reachable/current dan dapat menghasilkan behavior, output, acceptance, atau proof yang bertentangan dengan current authority.

Contoh:

- PLAN lama masih membentuk final `TOP_PICKS`;
- missing scoring feature masih di-zero-fill padahal current strategy fail-closed;
- non-recommended candidate masih dapat memperoleh valid CONFIRM;
- capital masih mengubah recommendation membership/rank;
- direct Market Data table read masih menjadi runtime fallback;
- `dv20_idr` diam-diam dibaca sebagai actual traded value;
- old reason code/enum masih mengaktifkan branch behavior superseded;
- fixture/test lama membuat implementation terlihat PASS walaupun semantics current tidak terpenuhi.

**Rule:** wajib dibuat finding/remediation bila material. Stage implementation tidak boleh `DONE` sampai harmful residue dihapus, dinonaktifkan, atau diisolasi secara terbukti sehingga tidak dapat lagi memengaruhi current behavior.

### B. `CONTROLLED_COMPATIBILITY_RESIDUE`

Legacy name/field/schema/adapter/endpoint yang masih diperlukan untuk compatibility tetapi **tidak boleh memiliki semantic authority sendiri**.

Boleh dipertahankan hanya jika semuanya terpenuhi:

1. exact current semantic mapping tertulis;
2. compatibility path terisolasi;
3. mapping tidak mengubah strategy identity secara diam-diam;
4. positive + negative tests membuktikan behavior equivalence/guard;
5. tidak ada fallback ke legacy semantics;
6. evidence menyatakan mengapa residue dipertahankan.

Contoh current Watchlist: legacy `dv20_idr` hanya boleh menjadi compatibility alias yang exact ke `adv20_close_volume_proxy_idr`, bukan actual traded value.

### C. `HISTORICAL_ONLY_RESIDUE`

Nama, rule, artifact, atau record lama yang tetap ada karena audit/history/evidence.

Sah hanya jika:

- tidak executable sebagai current behavior;
- tidak menjadi current authority/fallback;
- berada pada layer history/evidence atau diberi historical/superseded marker yang tidak ambigu.

Historical residue **tidak boleh dihapus hanya demi membuat scan bersih**.

### D. `DEAD_RESIDUE_CONFIRMED`

Code/config/path lama yang dibuktikan tidak reachable oleh current flow.

Boleh dibersihkan sebagai technical cleanup. Jika dipertahankan sementara, ia tidak boleh membingungkan authority atau dapat hidup kembali melalui config/feature flag yang tidak dijaga.

**Tidak boleh menyebut sesuatu dead hanya berdasarkan pendapat atau pencarian teks.** Harus ada evidence reachability/static/runtime/test yang cukup untuk scope risikonya.

---

## 4. What Residue Check Must Inspect

Minimal periksa impacted surface berikut sesuai stage:

1. **domain/application code path** — branch, service, calculator, comparator, selector, fallback;
2. **configuration/feature flags** — legacy option yang dapat menghidupkan behavior lama;
3. **parameter/registry/validator** — old key/default/validation semantics;
4. **persistence/schema mapping** — field alias, enum/status, migration compatibility, stored semantic;
5. **Market Data intake** — direct-table shortcut, recompute, stale fallback, legacy field interpretation;
6. **API/DTO/serializer** — old field/status/reason semantic yang masih keluar/masuk;
7. **reason codes / hash / identity** — legacy code yang masih memengaruhi branch atau reproducibility;
8. **fixtures/examples** — payload lama yang tidak lagi merepresentasikan current contract;
9. **unit/integration/contract tests** — test lama yang mengunci behavior superseded atau menutupi missing current test;
10. **commands/jobs/runtime wiring** — old command/service path yang masih callable/reachable;
11. **backtest/evaluator/proof** — old selection/ranking/entry/exit/friction identity yang dapat membuat proof tidak mewakili current product;
12. **implementation documentation** — instruction lama yang dapat membuat programmer menghidupkan kembali behavior superseded.

Tidak semua stage menyentuh semua item. Evidence harus menyatakan **scope yang diperiksa dan yang N/A**.

---

## 5. Mandatory Residue Evidence Packet

Setiap attempt yang mengubah/menilai implementation behavior harus mempunyai residue evidence, minimal:

```text
Stage ID:
Attempt ID:
Current strategy/contract identity:
Impacted surfaces inspected:
Search/static checks performed:
Reachability/runtime/behavior checks performed:
Tests/fixtures checked:
Residues found:
Residue classification:
Harmful residue remaining:
Controlled compatibility residue remaining:
Historical/dead residue notes:
Findings/remediation references:
Conformance verdict:
Evidence limitations:
```

Allowed `Conformance verdict`:

- `CONFORMANT_NO_HARMFUL_RESIDUE_FOUND`
- `CONFORMANT_WITH_CONTROLLED_COMPATIBILITY`
- `NON_CONFORMANT_HARMFUL_RESIDUE_OPEN`
- `INCONCLUSIVE_RESIDUE_EVIDENCE`

`CONFORMANT_*` tidak boleh diberikan hanya dari grep/search string. Search adalah discovery aid; reachability/behavior/contract evidence tetap diperlukan sesuai risiko.

---

## 6. Stage Closure Gate

### Implementation stage

`DONE` hanya boleh dipertimbangkan bila:

1. declared objective + exit criteria tercapai;
2. required functional/negative tests lulus;
3. residue check selesai untuk declared impacted scope;
4. tidak ada unresolved `HARMFUL_RESIDUE`;
5. setiap compatibility residue yang dipertahankan berstatus `CONTROLLED_COMPATIBILITY_RESIDUE` dengan mapping + tests + evidence;
6. residue evidence issued dan direferensikan stage register/latest attempt.

### Evaluation/proof stage

Stage dapat menghasilkan valid verdict `PASS` atau `FAIL` hanya bila proof path telah diverifikasi mewakili exact current strategy identity. Jika harmful residue dapat memengaruhi object yang dievaluasi, verdict tidak boleh dianggap valid current proof dan stage tetap remediation/inconclusive sesuai stage standard.

---

## 7. Re-entry / Rerun Rule

Pada rerun, setelah mandatory lineage read pada `STAGE_EXECUTION_AND_REWORK_STANDARD.md`, programmer wajib membaca:

- known residue dari attempt sebelumnya;
- residue finding yang masih OPEN/ACCEPTED unresolved;
- compatibility mapping yang masih current;
- `Do not repeat` yang terkait legacy path;
- residue evidence terakhir.

Known harmful residue **tidak boleh ditemukan ulang dari nol** jika sudah pernah dicatat. Attempt baru harus menjelaskan remediation atau materially different hypothesis.

Jika prior residue sudah diklaim cleared, rerun wajib memastikan ia tidak direintroduksi oleh merge/refactor/config/schema/fixture change.

---

## 8. Relationship With Findings, Decisions, and History

- harmful residue material → `../../development/findings/` + remediation/evidence;
- residue yang memaksa perubahan business strategy → jangan ubah strategy dari implementation; gunakan controlled strategy-change process;
- compatibility mapping teknis → implementation contract + tests + evidence + change-log bila material;
- historical residue → tetap pada history/evidence, jangan direwrite;
- dead residue cleanup → implementation change trace sesuai materialitas;
- residue yang tidak dapat diselesaikan mengikuti burden of proof terminal closure pada `STAGE_EXECUTION_AND_REWORK_STANDARD.md`; keberadaan residue saja tidak membenarkan penutupan stage.

---

## 9. Anti-Patterns — Forbidden

Dilarang:

1. menghapus semua identifier lama hanya untuk membuat scan terlihat bersih;
2. menyebut legacy code `dead` tanpa evidence;
3. mempertahankan compatibility alias tanpa exact semantic mapping;
4. menambah fallback lama agar test cepat PASS;
5. mengubah fixture/test supaya sesuai output code yang salah;
6. menganggap unit test baru PASS sebagai bukti tidak ada harmful residue;
7. menutup stage karena residue sulit dibersihkan sementara convergence/remediation masih nyata;
8. memindahkan harmful residue ke successor tanpa mapping residual objective dan evidence;
9. menulis ulang historical evidence agar tidak lagi menyebut behavior lama;
10. mengubah strategy agar residue implementation terlihat conformant.

---

## 10. Recurring Reading Rule

Dokumen ini wajib dibaca:

- sebelum `WS-B00` alignment dimulai;
- pada setiap build step `WS-Bxx` yang menyentuh current behavior/proof;
- pada setiap rerun/remediation;
- sebelum implementation stage dinilai `DONE`;
- sebelum audit menerima implementation conformance/proof verdict.

Entry points yang wajib menunjuk standard ini:

- `../../START_HERE.md`;
- `../../development/implementation/WS_IMPLEMENTATION_BUILD_SEQUENCE.md`;
- `STAGE_EXECUTION_AND_REWORK_STANDARD.md`;
- implementation prompt/checklist;
- audit prompt/checklist;
- current stage register.

---

## 11. One-line Rule

> **Current implementation is not conformant merely because the new path works; it must also be proven that no reachable old path can still change current behavior or proof.**

## 12. Traceability Matrix Binding

Residue verdict adalah bagian wajib dari canonical strategy coverage matrix.

Untuk setiap rule yang akan diberi status `SATISFIED`, `residue_state` pada [`STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv`](STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv) harus menunjuk verdict current yang sesuai:

- `CONFORMANT_NO_HARMFUL_RESIDUE_FOUND`; atau
- `CONFORMANT_WITH_CONTROLLED_COMPATIBILITY`.

`NOT_ASSESSED`, `NON_CONFORMANT_HARMFUL_RESIDUE_OPEN`, atau `INCONCLUSIVE_RESIDUE_EVIDENCE` tidak kompatibel dengan `SATISFIED`.

Satu residue evidence boleh mendukung beberapa rule jika scope/reachability yang diuji eksplisit mencakup semuanya; pointer evidence tetap harus dicatat pada row terkait.

## Attempt / Baseline Provenance

Residue verdict used for stage closure must identify the Attempt ID + Baseline ID under which reachability/behavior was evaluated. Residue evidence without current baseline provenance cannot close a stage after `WORK_BASELINE_LOCK_STANDARD.md` is active.

Executable documentation integrity PASS does not imply residue conformance; both gates remain independently required.

## 13. Correlated Residue Evidence

Current residue scan/remediation evidence wajib membawa Stage ID + Attempt/Work ID + Baseline ID dan diregister pada Work Record Registry. Known residue dari predecessor attempt tetap direferensikan, bukan diduplikasi hanya untuk menghasilkan ID baru.
