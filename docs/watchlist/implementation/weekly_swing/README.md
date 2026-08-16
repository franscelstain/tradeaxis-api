# Weekly Swing Implementation

Technical translation of `../../strategy/weekly_swing/`. This folder is intentionally mutable as software evolves, but it cannot alter canonical strategy semantics without the governance change process.

- `guidance/` — module/API/persistence/test/delivery translation.
- `contracts/` — technical input/output/paramset/hash/backtest runtime contracts.
- `persistence/` and `db/` — schema/SQL support.
- `testing/` — contract test definitions.
- `procedures/` — operator/promotion procedures.
- `evidence_contracts/` — technical manifest requirements for evidence.
- `fixtures/`, `examples/`, `reference/` — non-authoritative support artifacts.

## Backtest technical translation

- `contracts/WS_BACKTEST_EVALUATION_TECHNICAL_CONTRACT.md` — physical/serialization/query/reason-code translation of canonical backtest strategy.
