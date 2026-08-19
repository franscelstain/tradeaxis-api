# Weekly Swing Implementation

Technical translation of `../../strategy/weekly_swing/`. This folder is intentionally mutable as software evolves, but it cannot alter canonical strategy semantics without the governance change process.

> **Current alignment state:** `STRATEGY_REVISED_IMPLEMENTATION_ALIGNMENT_PENDING`.
> Read `STRATEGY_ALIGNMENT_REQUIRED.md` before changing or auditing implementation. Existing technical docs/evidence may still describe pre-revision behavior and are not proof that the revised qualified-Top-Picks strategy has been implemented.
> For CONFIRM specifically, `CONFIRM_OPTIONAL_NON_BLOCKING_IMPLEMENTATION_GUARD.md` is the current alignment guard and must be applied before older CONFIRM guidance/contracts.

## Authoritative Build Sequence

New implementation work must follow [`WS_IMPLEMENTATION_BUILD_SEQUENCE.md`](WS_IMPLEMENTATION_BUILD_SEQUENCE.md), which maps strategy lifecycle `WS-S00..WS-S11` into build steps `WS-B00..WS-B12`. Start from [`../../START_HERE.md`](../../START_HERE.md) before using individual technical documents.

Do not infer build order from numeric prefixes in legacy technical filenames.

- `guidance/` — module/API/persistence/test/delivery translation.
- `contracts/` — technical input/output/paramset/hash/backtest runtime contracts.
- `persistence/` and `db/` — schema/SQL support.
- `testing/` — contract test definitions.
- `procedures/` — operator/promotion procedures.
- `evidence_contracts/` — technical manifest requirements for evidence.
- `fixtures/`, `examples/`, `reference/` — non-authoritative support artifacts.

## Backtest technical translation

- `contracts/WS_BACKTEST_EVALUATION_TECHNICAL_CONTRACT.md` — physical/serialization/query/reason-code translation of canonical backtest strategy. It requires alignment review against the revised final-recommendation evaluation target before being treated as conformant.
