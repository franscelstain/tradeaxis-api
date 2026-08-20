# Legacy Semantic Extract — LX-MD-0039-EVD-04

- Source ID: `LS-MD-0039`
- Original path: `audit/PRODUCTION_VALIDATION_INVENTORY.md`
- Original SHA1: `A8D8B95268BF27AB2D2EE5D169FEE87AFCAF4EB7`
- Extract role: `EVIDENCE`
- Source range: `L451-L498`
- Extract body SHA1: `B8A6AE3C9BE0F5DCCF8B4574B72B0830C2C20A80`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-21 Production Rollout Validation Runtime Parity Proof

Scope: `PRODUCTION_ROLLOUT_VALIDATION_RUNTIME_PARITY_PROOF`.

Status: `SUPERSEDED_BY_FINAL_PASSED_RECONCILIATION` for current full rollout parity. Source-state `MARKET_DATA_PRODUCTION_READY_LOCKED` remains valid and final ops runtime parity is now `OPS_RUNTIME_PARITY_PASSED` for the current source ZIP.

Evidence root: `storage/app/market-data/production-rollout-validation-runtime-parity/**`.

| Validation area | Result | Evidence summary | Status |
|---|---|---|---|
| PHP/runtime baseline | PHP 7.4.33, required extensions present | `php -v`, `php -m` | PASS |
| Composer | Composer 2.8.4; `composer validate` valid | `composer --version`, `composer validate` | PASS |
| Artisan boot | Lumen 8.3.4, no warning/deprecation/noise | `php artisan list`, `php artisan --version` | PASS |
| Command registry/help | 21 market-data commands registered; requested help commands exit 0 | help outputs under command-output root plus provider-smoke safe-mode artifact | PASS; previous provider rate-limit wording is historical and superseded by final provider smoke PASS |
| Targeted static guards | AuditDocs OK (10/419), ProductionValidation OK (13/220), OperationalReadiness OK (10/204), OpsEnvironment OK (8/107), ConfigEnvGovernance OK (10/123) | final rerun outputs | PASS |
| Filtered suites | AuditDocs OK (10/419), StaticGuard OK (176/4139), Production OK (14/253), Operational OK (11/211), OpsEnvironment OK (8/107) | final rerun outputs | PASS |
| Full MarketData suite | OK (475 tests, 6957 assertions), Time 00:10.716, Memory 38.00 MB | `vendor/bin/phpunit tests/Unit/MarketData` | PASS |
| Manual-file import/promote | Import-only stayed not promoted and no pointer switch; promote returned SUCCESS/READABLE/PASS/SEALED | `run_id=30`, `publication_id=24` | PASS |
| Evidence export | Run evidence admitted and complete | `market-data:evidence:export --run_id=30`, 10 files | PASS |
| Replay verify current | Runtime fixture MATCH/PASS | `replay_id=19`, `run_id=33`, `mismatch_count=0` | PASS |
| Replay verify historical | Historical non-current publication MATCH/PASS | `replay_id=20`, `publication_id=2`, `NOT_CURRENT_POINTER`, `HISTORICAL_PUBLICATION_AUDIT` | PASS |
| Correction lifecycle | Request/approve/run/evidence/rerun guard validated | `correction_id=5`, `CONSUMED_CURRENT`, `ADMITTED_COMPLETE`, rerun blocked | PASS |
| Migration chain | All 29 migrations run cleanly; tables present under explicit `DB_DATABASE=tradeaxis_testing` override | plain `--env=testing` did not select `.env.testing` DB | PASS_WITH_ENVIRONMENT_BLOCKER |
| Scheduler/cron | `schedule:run` cleanly exits with no ready commands; code registers daily only when enabled | `schedule:list` unavailable; `MARKET_DATA_DAILY_ENABLED` not enabled | BLOCKED_DEPLOYMENT_PROOF |
| Storage/log/evidence path | Required paths exist and writable | write probes under storage paths | PASS |
| Live provider smoke | Not executed | no dry-run/ticker-limit command surface; broad provider fetch avoided | BLOCKED_SAFE_PROVIDER_SMOKE |

Blocker classification:

- Source-code P0/P1 blocker: none found.
- `BLOCKED_TESTING_DATABASE_ENV`: plain `php artisan migrate:fresh --env=testing` operated against `.env` database `tradeaxis`, not `.env.testing` database `tradeaxis_testing`; explicit env override was required for the intended testing DB.
- `OPS_DEPLOYMENT_TASK_REQUIRED`: production scheduler/cron enablement, log routing, and no-silent-failure proof still need deployment environment validation.
- `PROVIDER_SMOKE_SAFE_MODE_SURFACE_ADDED`: live provider smoke requires a safe dry-run/limited ticker path or an isolated staging DB/provider plan.

Final rollout decision for this session:

- `OPS_RUNTIME_PARITY_PASSED`.
- `MARKET_DATA_PRODUCTION_READY_LOCKED` remains valid for the locked source state.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.

Post-doc validation:

- `vendor/bin/phpunit tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` -> OK (10 tests, 421 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php` -> OK (13 tests, 220 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php` -> OK (8 tests, 107 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (176 tests, 4141 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (475 tests, 6959 assertions).



<!-- LEGACY_EXTRACT_BODY_END -->
