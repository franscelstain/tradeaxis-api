# Watchlist Dependency and Blocker Registry Standard

> **Status:** CANONICAL GOVERNANCE
> **Scope:** dependency yang dapat menahan, menunda, atau mengubah urutan current/future `WS-Bxx` implementation/proof

## 1. Canonical Registry

Current dependency index:

[`../../development/implementation/WS_DEPENDENCY_REGISTRY.csv`](../../development/implementation/WS_DEPENDENCY_REGISTRY.csv)

Registry adalah `MUTABLE_TRACEABLE` current index. Evidence dependency tetap berada di `records/evidence/`.

## 2. Dependency ID

Gunakan stable ID:

```text
DEP-WS-001
DEP-WS-002
```

Dependency dapat melintasi lebih dari satu attempt, karena itu ID tidak memakai Attempt ID. Setiap row tetap menyimpan `originating_work_id` dan affected stage/rule.

## 3. Mandatory Fields

- dependency ID;
- current status;
- consumer stage;
- originating Work ID;
- provider/owner;
- exact requirement;
- affected strategy rule IDs;
- evidence reference;
- resume trigger;
- first seen / last verified;
- resolution reference bila resolved.

## 4. Allowed Status

- `OPEN`
- `WAITING_VERIFIED_DEPENDENCY`
- `RESOLVED`
- `SUPERSEDED`
- `NOT_APPLICABLE`

`WAITING_VERIFIED_DEPENDENCY` harus punya objective evidence + concrete resume trigger. Ia bukan terminal stage closure.

## 5. Re-entry Rule

Jika Stage Register menunjuk dependency, rerun wajib membaca registry row + latest evidence sebelum mencoba workaround.

Tidak boleh membuat duplicate dependency baru untuk masalah yang sama hanya karena attempt berubah.
