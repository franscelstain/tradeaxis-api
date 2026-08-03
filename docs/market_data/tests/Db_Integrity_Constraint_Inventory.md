# DB Integrity & Constraint Enforcement Inventory

Current admission status: **HISTORICAL PRE-V2 INVENTORY / SUPERSEDED FOR CURRENT SCHEMA CONFORMANCE**. The table below remains evidence for the legacy schema it inspected. Statements such as “no open gap” do not cover immutable observations/config snapshots, temporal identity/calendar/status, revisioned events/factors, V2 artifact bindings, complete eligibility dimensions, or as-known replay. Current target authority is `../db/DB_Schema_And_Migration_Sync_Contract_LOCKED.md` plus the semantic schema contract/dictionary.

Historical status: LOCKED after the then-current operator-local targeted and full MarketData PHPUnit validation passed.
Last updated: 2026-05-07
Related contract: `DB_INTEGRITY_CONSTRAINT_ENFORCEMENT_CONTRACT`

## Final rule

Market-data runtime code must not depend on a primary key, business-key uniqueness, pointer/publication/run relation, index, enum-like value, nullable/default assumption, or reason code that is not guaranteed by SQL schema/migration/SQLite mirror or protected by explicit implicit integrity guard and tests.

## Inventory

| Area / Table | Primary Key | Unique / Business Key | FK / Implicit Integrity | Runtime Index Contract | Enum-like / Reason Values | Current Gap | Action |
|---|---|---|---|---|---|---|---|
| `tickers` | `ticker_id` | `ticker_code` unique | implicit ticker universe identity | `ticker_code` | n/a | no open gap | guarded by SQL + SQLite mirror |
| `market_calendar` | `cal_date` | `cal_date` primary | implicit trading-date authority | `(is_trading_day, cal_date)` | n/a | no open gap | guarded by SQL + SQLite mirror |
| `eod_reason_codes` | `code` | `code` primary | registry/seed is source of truth | category/active indexes | reason-code registry/seed | missing `RUN_FINALIZE_IDEMPOTENCY_POINTER_INVALID` | added registry + seed row |
| `eod_bars` | `(trade_date, ticker_id)` | current readable row identity | publication/run linkage implicit via repository + evidence guards | `(ticker_id, trade_date)`, `run_id`, `publication_id`, `(publication_id, trade_date, ticker_id)` | source/reason via related run | publication-scoped read index missing from SQLite/migration | added SQL + migration + SQLite index |
| `eod_indicators` | `(trade_date, ticker_id)` | current readable indicator identity | publication/run linkage implicit via repository + evidence guards | `(ticker_id, trade_date)`, `run_id`, `invalid_reason_code`, `publication_id`, `(publication_id, trade_date, ticker_id)` | indicator invalid reason codes | publication-scoped read index missing from SQLite/migration | added SQL + migration + SQLite index |
| `eod_eligibility` | `(trade_date, ticker_id)` | current readable eligibility identity | publication/run linkage implicit via pointer-scoped read repositories | `(ticker_id, trade_date)`, `run_id`, `reason_code`, `publication_id`, `(publication_id, trade_date, ticker_id)` | eligibility reason codes | publication-scoped read index missing from SQLite/migration | added SQL + migration + SQLite index |
| `eod_invalid_bars` | `invalid_bar_id` | invalid evidence row identity | run linkage implicit | trade/ticker, run, reason, source row, duplicate loser indexes | invalid bar reason codes | no open gap | mirrored indexes in SQLite |
| `eod_runs` | `run_id` | run lifecycle identity | publication/correction linkage implicit and mirror-checked | requested/effective state, publication, correction, final reason, source identity, readable contract index | lifecycle, terminal, quality, publishability, coverage, stage | readable/source identity indexes missing from SQLite/migration | added SQL + migration + SQLite indexes |
| `eod_run_events` | `event_id` | event trail identity | run linkage implicit | run/time, trade-date/time, stage/time, reason, severity/time | event severity/reason codes | SQLite mirror lacked indexes | added SQLite indexes |
| `eod_publications` | `publication_id` | `(trade_date, publication_version)` unique | run linkage implicit; current uniqueness enforced by pointer + repository switch discipline | trade/current, readable lookup, run/date, source hash, sealed lookup | seal state | readable/run-date indexes missing from SQLite/migration | added SQL + migration + SQLite indexes |
| `eod_current_publication_pointer` | `trade_date` | `publication_id` unique | FK to publication; run/version mirror checked in repository | run, run/version | pointer state via publication/run | run/version index missing from SQLite/migration | added SQL + migration + SQLite index |
| `eod_dataset_corrections` | `correction_id` | correction lifecycle identity | prior/new run linkage implicit and repository/evidence guarded | trade/status, trade/status/execution, prior run, new run, prior/new run | correction status enum | status/execution + prior/new index missing from SQLite/migration | added SQL + migration + SQLite indexes |
| `eod_bars_history` | `(publication_id, trade_date, ticker_id)` | publication-bound snapshot row identity | FK to publication in SQL; immutable history policy | trade date, ticker/date, run | source | no open gap | SQL + SQLite primary key guarded |
| `eod_indicators_history` | `(publication_id, trade_date, ticker_id)` | publication-bound snapshot row identity | FK to publication in SQL; immutable history policy | trade date, ticker/date, run | indicator invalid reason | no open gap | SQL + SQLite primary key guarded |
| `eod_eligibility_history` | `(publication_id, trade_date, ticker_id)` | publication-bound snapshot row identity | FK to publication in SQL; immutable history policy | trade date, ticker/date, run | eligibility reason | no open gap | SQL + SQLite primary key guarded |
| `md_session_snapshots` | `snapshot_id` | `(trade_date, snapshot_slot, ticker_id)` unique | run/reason linkage implicit | trade/slot, captured_at | snapshot reason codes | no open gap | SQL + SQLite unique/index guarded |
| `md_replay_daily_metrics` | `(replay_id, trade_date)` | replay metric date identity | expected/actual proof linkage implicit | replay status, publishability, publication identity, effective date, comparison, coverage gate, artifact scope, version, config | replay status/comparison/seal/final reason codes | no open SQL gap | SQLite index mirror guarded |
| `md_replay_reason_code_counts` | `(replay_id, trade_date, reason_code)` | replay reason-code count identity | replay metric linkage implicit | `(replay_id, reason_code)` | replay reason codes | SQLite mirror missed composite PK | added SQLite composite PK |
| read-side consumers | n/a | pointer-resolved publication only | pointer/publication/run mirror enforced in repositories/static guards | pointer, publication, run, artifact publication indexes | `SUCCESS`, `READABLE`, `PASS`, `SEALED` | no latest-date bypass found in runtime scan | guarded by static tests |
| evidence export | n/a | run/publication/correction/replay lineage identity | implicit lineage through repositories | run, publication, pointer, correction, replay indexes | reason-code registry/seed | no open gap | guarded by static tests |
| replay verification | n/a | fixture expected/actual deterministic context | implicit proof package integrity | replay metric/reason indexes | replay reason codes | no open gap | guarded by static tests |

## Validation policy

Final validation has passed locally. Future changes touching market-data schema, repository read paths, pointer/publication lifecycle, reason codes, or SQLite mirror must re-run at minimum:

- `php artisan migrate:fresh --env=testing`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "DbIntegrity"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Schema"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Migration"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Repository"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Pointer"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Publication"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Coverage"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Readable"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Reason"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Integration"`
- `vendor/bin/phpunit tests/Unit/MarketData`

## Final validation result

Operator-local validation supplied after fix2:

- `vendor/bin/phpunit tests/Unit/MarketData --filter "Repository"` -> OK (38 tests, 220 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Pointer"` -> OK (65 tests, 837 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Publication"` -> OK (90 tests, 1007 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Coverage"` -> OK (48 tests, 527 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Integration"` -> OK (91 tests, 1443 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (305 tests, 3795 assertions)

Final status: `DONE` / `LOCKED`.

---

## DB Integrity FK / Implicit Integrity Decision hardening — 2026-05-17

Related inventory: `docs/market_data/audit/DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_INVENTORY.md`.

Decision: `HYBRID_REQUIRED`.

This hardening does not reopen the whole schema sync contract and does not claim the entire schema sync failed. It classifies the remaining live artifact relation risk: current live artifact tables keep mandatory `run_id` and `publication_id` context plus publication-scoped indexes, while phase-dependent relation validity remains protected by repository/service/evidence/replay/static guards. Stable proof relations keep explicit FK enforcement: `eod_current_publication_pointer.publication_id` and immutable history artifact `publication_id` references to `eod_publications(publication_id)`.

Historical transition status was `READY_FOR_LOCAL_RUNTIME_VALIDATION`; container PHPUnit was `BLOCKED_CONTAINER_RUNTIME_ENV` because `dom`, `mbstring`, `xml`, and `xmlwriter` were unavailable. Later operator-local `DbIntegrityFkImplicitIntegrityDecisionStaticGuardTest.php`, `DbIntegrity`, `StaticGuard`, and full `tests/Unit/MarketData` proof promoted this scope to DONE/LOCKED as recorded in Lumen.
