# Watchlist Documentation

Watchlist saat ini hanya memiliki satu active strategy: **Weekly Swing**. Tujuan dokumentasi dipisahkan berdasarkan authority agar implementasi tidak lagi menulis progress/history ke canonical strategy.

## Read Order

1. `governance/DOCUMENTATION_ARCHITECTURE.md`
2. `governance/DOCUMENT_CHANGE_POLICY.md`
3. `strategy/weekly_swing/README.md`
4. canonical Weekly Swing strategy docs
5. `implementation/` saat menerjemahkan strategy ke software
6. `evidence/`, `findings/`, `decisions/`, dan `history/` hanya sesuai kebutuhan traceability

## Active Scope

- domain: `watchlist`
- strategy: `weekly_swing`
- runtime recommendation flow currently documented: `PLAN -> RECOMMENDATION -> CONFIRM`
- output adalah saran; bukan order execution, portfolio lifecycle, holdings, atau trade journal.

## Upstream Boundary

Watchlist adalah consumer `market_data`. Fakta market, publication/read model, readiness, OHLCV, indicators, corporate-action handling, dan producer-side data semantics tetap dimiliki `docs/market_data/`.

## Documentation Roles

- `strategy/` — canonical/stable behavior.
- `implementation/` — technical translation.
- `research/` — non-canonical experiments.
- `evidence/` — actual results.
- `findings/` — discovered issues/insights.
- `decisions/` — explicit decisions.
- `history/` — superseded/historical records.
- `governance/` — authority and change rules.
