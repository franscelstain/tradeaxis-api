# Watchlist Documentation

Watchlist saat ini hanya memiliki satu active strategy: **Weekly Swing**. Root documentation sengaja dibagi menjadi tiga fungsi permanen agar programmer baru langsung mengetahui mana authority, mana working area, dan mana record.

## START HERE — Single Entry Point

Siapa pun yang baru ingin memahami, membangun, melanjutkan, atau mengaudit Watchlist **harus mulai dari**:

[`START_HERE.md`](START_HERE.md)

Jangan menentukan build order dari campaign number, tracker historis, evidence, atau nama file lama.

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

Mental model yang wajib dipakai:

**Authority menentukan → Development mengerjakan → Records membuktikan/merekam.**

### `authority/` — stable current authority

- [`authority/strategy/`](authority/strategy/README.md) — canonical Weekly Swing behavior.
- [`authority/governance/`](authority/governance/README.md) — lifecycle, recording, stage, residue, traceability, audit, change control.

Default: **controlled revision**. Implementation tidak boleh mengubah authority agar pekerjaan terlihat lulus.

### `development/` — active working area

- [`development/implementation/`](development/implementation/README.md) — technical translation dan current stage execution.
- [`development/research/`](development/research/README.md) — experiment/hypothesis.
- [`development/findings/`](development/findings/README.md) — discovered problems/insights dan remediation lineage.

Default: **mutable/working tetapi traceable**, sesuai lifecycle masing-masing.

### `records/` — factual/issued/historical records

- [`records/evidence/`](records/evidence/README.md) — actual results/proof.
- [`records/decisions/`](records/decisions/README.md) — issued decisions/supersession.
- [`records/history/`](records/history/README.md) — immutable archive/superseded/migration records.

Default: **append/immutable-oriented**. Records bukan working area dan bukan fallback current authority.

## Short Read / Build Order

1. [`START_HERE.md`](START_HERE.md)
2. strategy chapters di [`authority/strategy/`](authority/strategy/README.md)
3. current governance yang diwajibkan oleh START_HERE
4. [`development/implementation/STRATEGY_ALIGNMENT_REQUIRED.md`](development/implementation/STRATEGY_ALIGNMENT_REQUIRED.md)
5. [`development/implementation/WS_IMPLEMENTATION_BUILD_SEQUENCE.md`](development/implementation/WS_IMPLEMENTATION_BUILD_SEQUENCE.md)
6. current resume pointer: [`development/implementation/WS_IMPLEMENTATION_STAGE_REGISTER.md`](development/implementation/WS_IMPLEMENTATION_STAGE_REGISTER.md)
7. gunakan `development/findings/` saat ada masalah dan `records/` untuk evidence/decision/history sesuai perannya

## Active Product Direction

Core product flow:

`trusted Market Data -> eligible candidates -> immutable PLAN -> qualified RECOMMENDATION/TOP PICKS -> manual buy decision support`

Optional non-blocking enhancement:

`qualified TOP PICKS -> D+1 CONFIRM (when valid decision-time data is available) -> ACTIONABLE / NOT_ACTIONABLE`

Jika CONFIRM data tidak tersedia, core Top Picks tetap valid; missing CONFIRM data bukan core failure.

## Canonical Build / Proof Order

Urutan strategis authoritative berada di [`authority/strategy/WS_END_TO_END_STRATEGY_LIFECYCLE.md`](authority/strategy/WS_END_TO_END_STRATEGY_LIFECYCLE.md). Core runtime adalah `WS-S00..WS-S04`; `WS-S05` optional non-blocking CONFIRM; core proof `WS-S06..WS-S11`.

Current implementation state tetap:

`STRATEGY_REVISED_IMPLEMENTATION_ALIGNMENT_PENDING`

Historical evidence tetap historical dan tidak ditulis ulang.

## Upstream Market Data Boundary

Watchlist adalah consumer `market_data`. Current binding owner adalah [`authority/strategy/WS_MARKET_DATA_INPUT_REQUIREMENTS.md`](authority/strategy/WS_MARKET_DATA_INPUT_REQUIREMENTS.md), dengan technical translation di [`development/implementation/MARKET_DATA_INTAKE_IMPLEMENTATION_CONTRACT.md`](development/implementation/MARKET_DATA_INTAKE_IMPLEMENTATION_CONTRACT.md). Watchlist tidak boleh membuat producer-internal Market Data path menjadi authority paralel.

## Mandatory Governance for Implementation

Sebelum dan selama implementation, ikuti:

- [`authority/governance/DOCUMENT_RECORDING_STANDARD.md`](authority/governance/DOCUMENT_RECORDING_STANDARD.md) — no silent semantic update;
- [`authority/governance/WORK_BASELINE_LOCK_STANDARD.md`](authority/governance/WORK_BASELINE_LOCK_STANDARD.md) — immutable attempt baseline before code change;
- [`authority/governance/STAGE_EXECUTION_AND_REWORK_STANDARD.md`](authority/governance/STAGE_EXECUTION_AND_REWORK_STANDARD.md) — re-entry, convergence, strict DONE/closure;
- [`authority/governance/IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md`](authority/governance/IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md) — recurring residue gate;
- [`authority/governance/DOCUMENT_INTEGRITY_GATE_STANDARD.md`](authority/governance/DOCUMENT_INTEGRITY_GATE_STANDARD.md) — executable integrity checks before attempt/stage/package closure;
- [`authority/governance/STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md`](authority/governance/STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md) + [`authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv`](authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv) — rule-by-rule strategy coverage.

Stage `DONE` saja tidak membuktikan seluruh strategy terpenuhi. 100% mandatory coverage hanya sah bila semua applicable mandatory rule `SATISFIED`, required evidence lengkap, dan harmful residue open = 0.

## One Document, One Role

Setiap dokumen current/future mempunyai tepat satu authoritative role. Baca [`ONE_DOCUMENT_ONE_AUTHORITATIVE_ROLE_STANDARD.md`](authority/governance/ONE_DOCUMENT_ONE_AUTHORITATIVE_ROLE_STANDARD.md) sebelum membuat atau memperluas semantic document. Supporting references boleh; authority kedua harus menjadi record terpisah.
