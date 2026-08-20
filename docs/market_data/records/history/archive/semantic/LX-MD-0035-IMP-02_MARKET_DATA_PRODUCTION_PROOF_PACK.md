# Legacy Semantic Extract — LX-MD-0035-IMP-02

- Source ID: `LS-MD-0035`
- Original path: `audit/MARKET_DATA_PRODUCTION_PROOF_PACK.md`
- Original SHA1: `9D1E95DE0523A6EFF6B7E31DF54056B51CB33F26`
- Extract role: `IMPLEMENTATION`
- Source range: `L306-L340`
- Extract body SHA1: `D8937F904FB2B31A8AA6B91CF9DF230102D7867B`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 18. 2026-05-21 Testing DB Isolation / Safe Migration Guard

Scope: `TESTING_DATABASE_ISOLATION_SAFE_MIGRATION_CONTRACT`.

Decision for this blocker: `TESTING_DB_ISOLATION_GUARD_PASSED`.

Source lock impact: `MARKET_DATA_PRODUCTION_READY_LOCKED` remains valid. This patch does not change market-data service/repository/provider/replay/correction/finalize/pointer behavior or migration schema.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.

Runtime evidence root: `storage/app/market-data/testing-database-isolation-safe-migration/**`.

Claimed validation requiring missing artifact support:

- CLI environment selection proof: `--env=testing` resolves `APP_ENV=testing`, `DB_CONNECTION=mysql`, and `DB_DATABASE=tradeaxis_testing`.
- Negative guard proof: `php artisan migrate:fresh --env=testing --database=nonexistent` exits 3 with reason code `BLOCKED_TESTING_DATABASE_ENV`.
- Migration status proof: `php artisan migrate:status --env=testing` exits 0.
- Migration fresh proof: `php artisan migrate:fresh --env=testing` exits 0 and runs all 29 migrations against `tradeaxis_testing`.
- Required table proof: `tickers`, `market_calendar`, `eod_runs`, `eod_publications`, `eod_current_publication_pointer`, `md_replay_daily_metrics`, `eod_dataset_corrections`, and `md_session_snapshots` are present in `tradeaxis_testing`.
- Static guard scope added: `tests/Unit/MarketData/TestingDatabaseIsolationStaticGuardTest.php` -> OK (4 tests, 41 assertions).
- Audit/governance scope: `vendor/bin/phpunit tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` -> OK (10 tests, 430 assertions).
- Production/ops targeted scope: ProductionValidation OK (13 tests, 220 assertions), OperationalReadiness OK (10 tests, 204 assertions), OpsEnvironment OK (8 tests, 107 assertions), ConfigEnvGovernance OK (10 tests, 123 assertions).
- StaticGuard filter: `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (180 tests, 4191 assertions).
- Full MarketData suite: `vendor/bin/phpunit tests/Unit/MarketData` -> OK (479 tests, 7009 assertions), Time 00:18.274, Memory 40.00 MB.
- New command output artifacts are UTF-8 plain text without null-byte/UTF-16 evidence noise.

Open blocker:

- `BLOCKED_TESTING_DATABASE_ENV`: closed for this patched source state when using CLI `--env=testing`.

Remaining rollout blockers:

- `PROVIDER_SMOKE_SAFE_MODE_SURFACE_ADDED`: safe live provider smoke is passed.

Final note: this section closes the highest-risk DB isolation gap reported by the ops runtime parity audit. Scheduler status is superseded by the later Production Scheduler / Cron Deployment Proof section; scheduler runtime artifact synchronization and safe provider smoke remain rollout blockers.



<!-- LEGACY_EXTRACT_BODY_END -->
