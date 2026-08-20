# Watchlist Work Correlation and Record Registry Standard

> **Status:** CANONICAL GOVERNANCE
> **Scope:** seluruh current/future `WS-Bxx` implementation, remediation, validation, proof, dependency, finding, evidence, decision, residue, and closure records
> **Purpose:** membuat seluruh catatan dari satu pekerjaan dapat ditemukan dengan satu correlation key dan memastikan hubungan antarre record tidak bergantung pada ingatan manusia.

## 1. Canonical Correlation Key

Untuk current/future implementation work, **Attempt ID adalah canonical Work/Correlation ID**.

```text
Stage ID   : WS-B04
Attempt ID : WS-B04-A003
Work ID    : WS-B04-A003
```

`Work ID` tidak membuat identity baru yang berbeda dari Attempt ID. Field `Work ID` boleh ditulis eksplisit untuk searchability, tetapi nilainya wajib sama dengan Attempt ID.

Dengan demikian pencarian `WS-B04-A003` harus dapat menemukan baseline, change impact, finding, evidence, decision, residue proof, attempt record, dan closure/support record yang lahir dari attempt itu.

## 2. Current Record ID Pattern

Record baru yang lahir dari `WS-Bxx` work menggunakan correlation-first ID:

```text
Finding            F-WS-B04-A003-001
Evidence           E-WS-B04-A003-001
Decision           D-WS-B04-A003-001
Change Impact      CI-WS-B04-A003-001
Stage Closure      SC-WS-B04-A003-001
Dependency         DEP-WS-001
```

Baseline identity tetap memakai `WSBL-YYYYMMDD-NNN`, tetapi baseline evidence wajib membawa `Stage ID`, `Attempt ID/Work ID`, dan `Baseline ID`.

Existing historical/date-based IDs tidak perlu di-rename atau dibackfill. Mereka tetap valid historical records. Current/future `WS-Bxx` records setelah standard ini adopted wajib memakai correlation-first identity.

## 3. Mandatory Metadata for Current Work Records

Setiap current/future record yang terkait attempt wajib membawa minimal:

```text
Record ID:
Record Type:
Stage ID:
Attempt ID:
Work ID:
Baseline ID:
Created:
Status:
```

Tambahkan bila applicable:

```text
Related Finding:
Related Evidence:
Related Decision:
Related Dependency:
Strategy Rule IDs:
Supersedes:
Superseded By:
```

Filename membantu pencarian, tetapi **metadata di isi record adalah authority korelasi**. Rename file tidak boleh memutus relationship.

## 4. Work Record Registry

Canonical current-work index:

[`../../records/WORK_RECORD_REGISTRY.csv`](../../records/WORK_RECORD_REGISTRY.csv)

Registry adalah `MUTABLE_TRACEABLE` derived/current index. Ia tidak menggantikan source record dan tidak mengubah mutability record yang diindeks.

Setiap current/future `WS-Bxx` record yang issued/dibuka wajib mempunyai satu row registry.

Minimum columns:

- `record_id`
- `record_type`
- `stage_id`
- `attempt_id`
- `work_id`
- `baseline_id`
- `record_status`
- `file_path`
- related IDs
- `supersedes`
- `created_at`
- `notes`

## 5. Explicit Work Relationship Registry

Cross-attempt, cross-stage, dan cross-baseline relationship **tidak boleh hanya tersirat** dari kolom `related_*` atau prose. Canonical relationship index:

[`../../records/WORK_RELATIONSHIP_REGISTRY.csv`](../../records/WORK_RELATIONSHIP_REGISTRY.csv)

Minimum columns:

- `relationship_id`;
- `source_record_id`;
- `target_record_id`;
- `relationship_type`;
- `justification`;
- `reviewed_decision_id` bila diwajibkan;
- `created_at`;
- `notes`.

Allowed current relationship types:

- `INHERITED_FINDING`;
- `PRIOR_EVIDENCE`;
- `PRIOR_DECISION`;
- `PREDECESSOR_ATTEMPT`;
- `SUCCESSOR_ORIGIN`;
- `SUPERSEDES_CROSS_ATTEMPT`;
- `CROSS_BASELINE_CLOSURE_EVIDENCE`.

Semua explicit cross-attempt/cross-stage relation, termasuk cross-attempt `supersedes`, wajib memiliki justification. Relationship ID wajib berbentuk `REL-<source Attempt ID>-NNN`, misalnya `REL-WS-B04-A003-001`. `CROSS_BASELINE_CLOSURE_EVIDENCE` tambahan wajib menunjuk reviewed `DECISION` yang nyata.

Helper:

[`../../development/implementation/tests/RegisterWorkRelationship.php`](../../development/implementation/tests/RegisterWorkRelationship.php)

## 6. Search Levels

```text
WS-B04
→ seluruh current work yang berkaitan dengan stage B04

WS-B04-A003
→ seluruh record untuk satu attempt

F-WS-B04-A003-001
→ satu finding exact
```

Stage re-entry harus mulai dari Stage Register lalu Work Record Registry, bukan pencarian history acak.

## 7. Cross-attempt Continuity

Attempt lanjutan wajib menyebut predecessor:

```text
Attempt ID: WS-B04-A004
Predecessor Attempt: WS-B04-A003
Open Finding: F-WS-B04-A003-001
Previous Evidence: E-WS-B04-A003-003
```

Finding yang tetap terbuka boleh mempertahankan Record ID asal dan direferensikan attempt berikutnya; jangan membuat finding duplicate hanya untuk mengganti Attempt ID.

## 8. Successor / Decomposition Continuity

Successor/decomposition stage wajib menyimpan:

- `Origin Stage`;
- `Origin Attempt`;
- `Origin Decision`;
- residual objective yang dipindahkan.

Relationship tidak boleh hanya tersirat dari nama stage.

## 9. Relationship Integrity

Executable gate:

[`../../development/implementation/tests/WatchlistRelationshipIntegrityGate.php`](../../development/implementation/tests/WatchlistRelationshipIntegrityGate.php)

Gate **wajib machine-enforce 9/9 invariant berikut**:

1. **Attempt identity unique** — satu Attempt ID hanya boleh mewakili satu Stage dan satu Baseline ID; tepat satu `WORK_BASELINE_LOCK` per Attempt; Baseline ID tidak boleh dipakai Attempt lain; canonical final `STAGE_ATTEMPT_RECORD` maksimal satu per Attempt dan wajib tepat satu pada terminal stage.
2. **Record ID unique** — tidak ada duplicate current Record ID.
3. **Stage exists** — setiap current record/dependency Stage ID wajib menunjuk Stage Registry yang nyata.
4. **Baseline exists and is bound** — setiap current record wajib membawa Baseline ID yang benar-benar memiliki registered `WORK_BASELINE_LOCK` pada Attempt yang sama; baseline JSON identity harus cocok dengan registry.
5. **Related Finding type-safe** — target `related_finding_ids` wajib ada dan `record_type=FINDING`.
6. **Related Decision type-safe** — target `related_decision_ids` wajib ada dan `record_type=DECISION`.
7. **Supersedes acyclic** — chain `supersedes` tidak boleh circular.
8. **Closure baseline integrity** — closure-critical `related_evidence_ids` harus baseline-consistent dengan final Attempt. Evidence baseline lain hanya sah melalui explicit `CROSS_BASELINE_CLOSURE_EVIDENCE` + justification + reviewed Decision yang juga dibind ke closure. Legacy/unbaseline evidence boleh menjadi context tetapi tidak boleh menjadi closure-critical evidence.
9. **No silent cross-attempt association** — current record yang mereferensikan Finding/Evidence/Decision dari Attempt/Stage lain wajib mempunyai exact row di `WORK_RELATIONSHIP_REGISTRY.csv` dengan allowed relationship type dan justification.

Closure manifest Markdown identity dan daftar Supporting Records juga wajib konsisten dengan Work Record Registry, sehingga evidence tidak dapat ditambahkan ke prose closure tanpa relationship yang diketahui gate.

Current stage/attempt closure tidak boleh mengabaikan failed relationship gate.

## 10. No Forced Historical Backfill

Standard ini berlaku prospective. Historical C/R/B/P records dan record sebelum adoption tidak perlu direwrite hanya untuk memenuhi pattern baru. Bila historical record menjadi input current attempt, current attempt cukup mereferensikan path/Record ID historical tersebut secara eksplisit.
