# Watchlist Weekly Swing — Implementation

> **Physical role:** `docs/watchlist/development/implementation/` — active technical working area; mutable-traceable dan tunduk pada `docs/watchlist/authority/`.

Technical translation dari canonical strategy. **Mulai pembangunan dari** [`WS_IMPLEMENTATION_BUILD_SEQUENCE.md`](WS_IMPLEMENTATION_BUILD_SEQUENCE.md) setelah membaca [`../../START_HERE.md`](../../START_HERE.md).

Jika melanjutkan stage yang pernah dikerjakan, baca [`WS_IMPLEMENTATION_STAGE_REGISTER.md`](WS_IMPLEMENTATION_STAGE_REGISTER.md) dan jalankan [`../../authority/governance/STAGE_EXECUTION_AND_REWORK_STANDARD.md`](../../authority/governance/STAGE_EXECUTION_AND_REWORK_STANDARD.md) **sebelum** menyentuh code.

Current alignment state tetap `STRATEGY_REVISED_IMPLEMENTATION_ALIGNMENT_PENDING` sampai code/runtime dibuktikan sesuai strategy baru.

## Recording / Mutability Rule

Implementation adalah **MUTABLE_TRACEABLE**:

- refactor non-material boleh dilakukan tanpa decision;
- material contract/API/DTO/schema/reason/validation meaning change wajib dicatat pada [`../../authority/governance/DOCUMENT_CHANGE_LOG.md`](../../authority/governance/DOCUMENT_CHANGE_LOG.md), diuji, dan mempunyai evidence/status update;
- implementation tidak boleh diam-diam mengubah canonical strategy;
- bila perubahan technical ternyata membutuhkan business-rule change, hentikan alignment dan gunakan strategy change process.

Universal rule: [`../../authority/governance/DOCUMENT_RECORDING_STANDARD.md`](../../authority/governance/DOCUMENT_RECORDING_STANDARD.md).

## Work Baseline + Attempt + Integrity Gate

Sebelum code/contract change pada `WS-Bxx`:

1. issue baseline sesuai [`../../authority/governance/WORK_BASELINE_LOCK_STANDARD.md`](../../authority/governance/WORK_BASELINE_LOCK_STANDARD.md);
2. gunakan [`examples/WS_STAGE_ATTEMPT_RECORD_TEMPLATE.md`](examples/WS_STAGE_ATTEMPT_RECORD_TEMPLATE.md);
3. jalankan [`tests/WatchlistDocumentationIntegrityGate.php`](tests/WatchlistDocumentationIntegrityGate.php) sebagai preflight;
4. jalankan gate lagi dengan `--baseline` sebelum attempt close/stage `DONE`.

Baseline/attempt evidence tidak menggantikan test atau strategy coverage; ia mengikat bukti tersebut ke exact authority/source state.

## Recurring Residue / Conformance Rule

Setiap `WS-Bxx` yang menyentuh behavior/proof wajib mengikuti [`../../authority/governance/IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md`](../../authority/governance/IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md). Functional test PASS belum cukup untuk `DONE`; pastikan tidak ada reachable `HARMFUL_RESIDUE`. Compatibility residue hanya boleh dipertahankan dengan exact mapping, isolation, tests, dan evidence.

## Folder

- `contracts/` — data model, paramset, I/O, validator, reason semantics, backtest/runtime contracts.
- `db/` — schema, DDL, SQL, migration/seed, persistence dictionary.
- `guides/` — module/API/persistence/test guidance, procedures, glossary, matrices, worked reference.
- `tests/` — fixtures, contract-test specs, verification inputs.
- `examples/` — non-authoritative example payload/output.

Tidak ada subfolder `weekly_swing/` lagi karena current Watchlist hanya mempunyai satu active strategy.


## Stage Resume Discipline

- `DONE` hanya jika declared stage objective/exit criteria tercapai.
- failed attempt boleh dan harus dicatat; stage tetap aktif bila remediation/convergence masih nyata.
- `WAITING_VERIFIED_DEPENDENCY` bukan terminal closure.
- terminal unresolved closure membutuhkan evidence + reviewed decision.
- evaluation stage dapat `DONE` dengan verdict `FAIL` bila valid verdict memang objective stage; downstream gate kemudian berhenti sesuai strategy.

## Strategy Traceability Rule

Setiap build/rework stage wajib memakai [`../../authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv`](../../authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv) sebagai rule-level coverage backlog. Matrix bukan business-rule owner; meaning tetap dari `../../authority/strategy/`. Stage `DONE` tidak boleh diberikan bila masih ada mandatory row stage yang belum `SATISFIED`.
## Work Correlation / Registries / Closure

Current/future `WS-Bxx` attempt memakai Attempt ID sebagai Work ID. Gunakan:

- [`../../authority/governance/WORK_CORRELATION_AND_RECORD_REGISTRY_STANDARD.md`](../../authority/governance/WORK_CORRELATION_AND_RECORD_REGISTRY_STANDARD.md)
- [`../../records/WORK_RECORD_REGISTRY.csv`](../../records/WORK_RECORD_REGISTRY.csv)
- [`WS_DEPENDENCY_REGISTRY.csv`](WS_DEPENDENCY_REGISTRY.csv)
- [`examples/WS_CHANGE_IMPACT_DECLARATION_TEMPLATE.md`](examples/WS_CHANGE_IMPACT_DECLARATION_TEMPLATE.md)
- [`examples/WS_STAGE_CLOSURE_MANIFEST_TEMPLATE.md`](examples/WS_STAGE_CLOSURE_MANIFEST_TEMPLATE.md)
- [`tests/WatchlistRelationshipIntegrityGate.php`](tests/WatchlistRelationshipIntegrityGate.php)
- [`CURRENT_STATE.md`](CURRENT_STATE.md) (generated)

Material change wajib punya Change Impact Declaration. Terminal stage wajib punya Stage Closure Manifest. Verified dependency yang menahan stage wajib masuk Dependency Registry.


- [`../../records/WORK_RELATIONSHIP_REGISTRY.csv`](../../records/WORK_RELATIONSHIP_REGISTRY.csv) — explicit cross-attempt/cross-stage/cross-baseline links.
