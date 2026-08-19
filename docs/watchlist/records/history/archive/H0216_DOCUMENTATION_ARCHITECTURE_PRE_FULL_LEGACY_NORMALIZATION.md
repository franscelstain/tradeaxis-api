# Watchlist Documentation Architecture

> **Status:** CANONICAL GOVERNANCE  
> **Purpose:** menentukan letak setiap jenis informasi sehingga current authority, active development work, dan historical/factual records tidak kembali terlihat setara atau tercampur.

## Root Architecture

```text
docs/watchlist/
├── README.md
├── START_HERE.md
├── authority/
│   ├── strategy/
│   └── governance/
├── development/
│   ├── implementation/
│   ├── research/
│   └── findings/
└── records/
    ├── evidence/
    ├── decisions/
    └── history/
```

Root group menunjukkan **fungsi permanen**, bukan temporary status. Dilarang membuat `active/`, `inactive/`, `done/`, `failed/`, atau folder status lain yang menyebabkan file sering dipindah ketika lifecycle berubah.

## Three-Group Mental Model

### 1. `authority/` — what must be followed

- `../strategy/`: owner current Weekly Swing behavior/acceptance.
- `./`: owner documentation authority, recording lifecycle, stage execution, residue, traceability, audit, controlled revision.

Default mutability: **CONTROLLED_REVISION**.

### 2. `development/` — where work evolves

- `../../development/implementation/`: technical translation, contracts, stage execution/status, tests, guides, DB, examples.
- `../../development/research/`: draft/locked experiments; research tidak otomatis canonical.
- `../../development/findings/`: discovered problem/insight, root cause, remediation lineage.

Default mutability: sesuai lifecycle; aktif berubah tetapi **traceable**. Locked research tetap immutable. Original finding observation tidak ditulis ulang.

### 3. `records/` — what happened / was decided / was archived

- `../../records/evidence/`: actual result/proof; final evidence immutable, correction-by-new-record.
- `../../records/decisions/`: issued decisions; perubahan melalui superseding decision.
- `../../records/history/`: superseded/migration/archive; immutable dan bukan fallback current authority.

Default mutability: **append/immutable-oriented**.

## Authority / Work / Record Flow

```text
authority/strategy + authority/governance
                  ↓
        development/implementation
          ↙                  ↘
development/findings       development/research
          ↓                  ↓
       records/evidence → records/decisions
                  ↓
            records/history
```

Diagram ini bukan workflow wajib untuk setiap event; ia menunjukkan arah ownership. Evidence atau decision tidak pernah menjadi business-rule owner hanya karena lebih baru.

## Authority Order

1. `authority/governance/` menentukan cara membaca, mencatat, mengubah, mengoreksi, dan menutup pekerjaan.
2. `authority/strategy/` menentukan current Weekly Swing behavior.
3. `development/implementation/` harus tunduk pada strategy dan governance.
4. `development/research/` dan `development/findings/` dapat memicu controlled change tetapi tidak mengubah authority otomatis.
5. `records/evidence/`, `records/decisions/`, dan `records/history/` merekam fakta/keputusan/histori; record tidak menjadi current behavior authority kecuali governance secara eksplisit mempromosikan perubahan ke authority.

## Mutability Summary

| Physical area | Document role | Default treatment |
|---|---|---|
| `authority/strategy/` | canonical behavior | controlled revision |
| `authority/governance/` | process/change authority | controlled revision |
| `development/implementation/` | technical translation/status | mutable traceable |
| `development/research/` | hypothesis/experiment | draft mutable; locked immutable |
| `development/findings/` | discovered issues | lifecycle-update only |
| `records/evidence/` | actual proof/results | immutable after issue |
| `records/decisions/` | issued decisions | immutable; supersession |
| `records/history/` | historical/superseded | immutable |
| root/group README/index | navigation | mutable traceable, no owner rule |

Detailed lifecycle: [`DOCUMENT_RECORDING_STANDARD.md`](DOCUMENT_RECORDING_STANDARD.md).

## Implementation Reading Rule

Implementer baru mengikuti:

`START_HERE -> authority/strategy -> required authority/governance -> development/implementation build sequence -> current stage register -> findings/evidence/decisions only as referenced by current work`

Implementer **tidak boleh** membaca history/campaign evidence lalu menganggapnya current rule hanya karena tanggalnya lebih baru.

## Single Entry Point

[`../../START_HERE.md`](../../START_HERE.md) adalah halaman pertama resmi Watchlist. Detailed technical build order berada di [`../../development/implementation/WS_IMPLEMENTATION_BUILD_SEQUENCE.md`](../../development/implementation/WS_IMPLEMENTATION_BUILD_SEQUENCE.md). Strategy stage dependency tetap dimiliki [`../strategy/WS_END_TO_END_STRATEGY_LIFECYCLE.md`](../strategy/WS_END_TO_END_STRATEGY_LIFECYCLE.md).

## Traceability Rule

Rule-by-rule coverage tetap berada di governance melalui [`STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md`](STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md) dan [`STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv`](STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv). Matrix mengikat authority ke implementation/test/evidence/residue tanpa mengambil alih business-rule ownership.

## Implementation Session Control Plane

Physical folders stay role-based. Per-attempt control is provided without adding status folders:

`START_HERE -> current authority -> Stage Register -> Work Baseline Lock -> Attempt Record -> tests/residue/traceability -> executable integrity gate -> evidence/closure`

Baseline files and final attempt records live in `records/evidence/runs/`; templates/helpers remain under `development/implementation/`. This keeps root architecture stable while every implementation session remains reproducible and traceable.

## Searchable Work Chain

Current/future implementation records use a correlation-first chain without adding new root folders:

`Stage ID -> Attempt/Work ID -> Baseline -> Change Impact -> Findings/Evidence/Decision/Dependency -> Residue/Traceability -> Attempt Record -> Closure Manifest`

Current indexes:

- `../../records/WORK_RECORD_REGISTRY.csv` — current work record index;
- `../../development/implementation/WS_DEPENDENCY_REGISTRY.csv` — verified dependency/blocker index;
- `../../development/implementation/CURRENT_STATE.md` — generated human-readable state.

These are indexes/orchestration aids, not business-rule owners.
