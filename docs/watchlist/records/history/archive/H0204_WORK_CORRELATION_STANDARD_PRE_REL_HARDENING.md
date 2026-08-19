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
Stage Closure      SC-WS-B04-001
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

## 5. Search Levels

```text
WS-B04
→ seluruh current work yang berkaitan dengan stage B04

WS-B04-A003
→ seluruh record untuk satu attempt

F-WS-B04-A003-001
→ satu finding exact
```

Stage re-entry harus mulai dari Stage Register lalu Work Record Registry, bukan pencarian history acak.

## 6. Cross-attempt Continuity

Attempt lanjutan wajib menyebut predecessor:

```text
Attempt ID: WS-B04-A004
Predecessor Attempt: WS-B04-A003
Open Finding: F-WS-B04-A003-001
Previous Evidence: E-WS-B04-A003-003
```

Finding yang tetap terbuka boleh mempertahankan Record ID asal dan direferensikan attempt berikutnya; jangan membuat finding duplicate hanya untuk mengganti Attempt ID.

## 7. Successor / Decomposition Continuity

Successor/decomposition stage wajib menyimpan:

- `Origin Stage`;
- `Origin Attempt`;
- `Origin Decision`;
- residual objective yang dipindahkan.

Relationship tidak boleh hanya tersirat dari nama stage.

## 8. Relationship Integrity

Executable gate:

[`../../development/implementation/tests/WatchlistRelationshipIntegrityGate.php`](../../development/implementation/tests/WatchlistRelationshipIntegrityGate.php)

Gate minimal memvalidasi:

- unique current Record ID;
- valid Stage/Attempt/Work relationship;
- registered file path exists;
- referenced current record exists;
- Baseline ID/Attempt binding;
- dependency IDs and stage links;
- no circular `supersedes` chain;
- closure manifest relationship;
- `SATISFIED` traceability rows have valid evidence/reference when populated.

Current stage/attempt closure tidak boleh mengabaikan failed relationship gate.

## 9. No Forced Historical Backfill

Standard ini berlaku prospective. Historical C/R/B/P records dan record sebelum adoption tidak perlu direwrite hanya untuk memenuhi pattern baru. Bila historical record menjadi input current attempt, current attempt cukup mereferensikan path/Record ID historical tersebut secara eksplisit.
