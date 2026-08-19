# Watchlist Documentation

Watchlist saat ini hanya memiliki satu active strategy: **Weekly Swing**. Dokumentasi dipisahkan berdasarkan authority agar strategy, implementation, research, evidence, findings, decisions, dan history tidak bercampur.

## Read Order

1. `governance/DOCUMENTATION_ARCHITECTURE.md`
2. `governance/DOCUMENT_CHANGE_POLICY.md`
3. `strategy/weekly_swing/README.md`
4. `strategy/weekly_swing/WS_END_TO_END_STRATEGY_LIFECYCLE.md` sebagai authoritative lifecycle map `WS-S00..WS-S11` (core runtime `S00..S04`, optional `S05`, core proof `S06..S11`)
5. canonical Weekly Swing strategy docs sesuai Required Reading Order pada strategy index
6. `implementation/weekly_swing/STRATEGY_ALIGNMENT_REQUIRED.md`
7. `implementation/` saat menerjemahkan strategy ke software
8. `evidence/`, `findings/`, `decisions/`, dan `history/` untuk traceability

## Active Product Direction

Core product flow:

`trusted Market Data -> eligible candidates -> immutable PLAN -> qualified RECOMMENDATION/TOP PICKS -> manual buy decision support`

Optional non-blocking enhancement:

`qualified TOP PICKS -> D+1 CONFIRM (when valid decision-time data is available) -> ACTIONABLE / NOT_ACTIONABLE`

If CONFIRM data is not available, the core Top Picks output remains valid. The CONFIRM state is `NOT_REQUESTED`, `UNAVAILABLE_RETRYABLE`, or eventually `EXPIRED_UNCONFIRMED`; absence of CONFIRM data is not a core Watchlist failure.

- domain: `watchlist`
- active strategy: `weekly_swing`
- Top Picks adalah final qualified recommendations, bukan PLAN group
- jumlah Top Picks quality-driven dan boleh nol
- output adalah decision-support, bukan order execution atau portfolio lifecycle

## Canonical Build / Proof Order

Urutan strategis authoritative bukan prefix filename, tetapi lifecycle di `strategy/weekly_swing/WS_END_TO_END_STRATEGY_LIFECYCLE.md`. Core runtime adalah `WS-S00..WS-S04`; `WS-S05` adalah optional non-blocking CONFIRM branch; core proof adalah `WS-S06..WS-S11`. Technical implementation harus memetakan dependency ke lifecycle ini dan tidak boleh membuat core completion bergantung pada tersedianya data CONFIRM.

## Current Alignment State

Canonical strategy sudah direvisi untuk qualified Top Picks dan optional non-blocking CONFIRM. Technical implementation contracts/code/evidence belum boleh dianggap otomatis conformant dengan revision tersebut. Selama alignment berlangsung, `implementation/weekly_swing/CONFIRM_OPTIONAL_NON_BLOCKING_IMPLEMENTATION_GUARD.md` menjadi guard aktif untuk dependency CONFIRM.

Current handoff state:

`STRATEGY_REVISED_IMPLEMENTATION_ALIGNMENT_PENDING`

Historical evidence tetap historical dan tidak ditulis ulang.

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
