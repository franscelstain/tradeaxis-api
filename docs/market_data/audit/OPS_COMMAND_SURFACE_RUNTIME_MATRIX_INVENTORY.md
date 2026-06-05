# Ops Command Surface Runtime Matrix Inventory

Status: LOCKED.
Contract: OPS_COMMAND_SURFACE_RUNTIME_MATRIX_CONTRACT.
Implementation: Ops Command Surface Runtime Matrix.
Source session date: 2026-05-20.

This inventory records the operator-runtime proof for the public market-data command surface. It is intentionally not a full market-data production-ready claim. The command surface is locked for registry, help, command-owned invalid input, fresh success/held/failed runtime flows, repeated execution behavior, lock conflict output, repair/purge guards, evidence/replay behavior, and clear operator output. The prior fixture-limited blocked cases were closed by the isolated ops runtime fixture pack generated on 2026-05-20.

## Runtime Environment

- PHP CLI: PHP 7.4.33.
- PHPUnit: PHPUnit 9.6.34.
- Required extensions available: dom, mbstring, pdo_mysql, pdo_sqlite, xml, xmlreader, xmlwriter.
- `vendor/` and `.env.testing` are present in this source ZIP.
- Local artisan runtime DB: `tradeaxis` for this proof run; commands were invoked with `--env=testing` for parity with the existing matrix command convention.
- Migration status: available/applied for the market-data runtime database.
- Runtime proof artifact roots:
  - `storage/app/market-data/ops-command-surface-runtime-matrix/**` for the prior enforced matrix.
  - `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/**` for the lock matrix and command output artifacts.

## Decision

- Ops command registry/help/invalid-input surface: PASS.
- Key seeded runtime matrix: PASS for finalized readable run re-run, evidence export, replay fixture generation, replay verify, replay smoke, replay backfill, repair dry-run, purge dry-run, purge safe apply-zero, correction failed/not-executable output, correction request missing-baseline output, and promote force guard.
- Production-ready fixture matrix: PASS for fresh daily import, fresh backfill import, stage-by-stage full publish, promote success, real lock conflict, held/not-readable partial promote, failed empty-source daily run, repair dry-run/apply invalid pointer, repair no-op after apply, successful session snapshot capture, evidence export, replay fixture generation, replay verify, replay smoke, and replay backfill.
- Destructive repair/purge guard: PASS.
- Previously fixture-limited runtime cases: CLOSED by `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/fixture_manifest.json`.
- Current implementation status: DONE.
- Current contract status: LOCKED.
- DONE/LOCKED is used for the ops command surface scope only; full market-data production-ready remains a separate proof-pack decision.

## Command Registry Proof

Command: `php artisan --env=testing list market-data`.
Result: exit 0, current source reports 29 public market-data commands registered. The 2026-05-20 matrix remains the historical 20-command runtime fixture proof; the lifecycle backfill command is public in the current source, the provider-smoke overlay added the safe live-provider command surface and final provider PASS proof, the 2026-06-03 extensions add proof-only full-range current evidence/replay orchestration plus dry-run/apply guarded sector membership, sector-index CSV bar import, and sector-index API bar import, the 2026-06-04 event-risk extension adds dry-run/apply guarded corporate-action plus trading-status source imports, and the current 2026-06-04 missing-ticker extension adds targeted lifecycle backfill for current `eod_bars` ticker/date gaps.

| Command | Registered | Help Proof | Signature/Docs Sync | Guard Decision |
|---|---:|---:|---|---|
| `market-data:daily` | PASS | PASS | `--requested_date`, `--source_mode`, optional pipeline flags | command-owned date validation |
| `market-data:backfill` | PASS | PASS | parser args optional only for command-owned missing-input output; operator contract still requires start/end dates | `COMMAND_MISSING_REQUIRED_INPUT`, date validation |
| `market-data:backfill:lifecycle` | PASS | PASS | start/end range, source mode, plan/diagnose/resume/evidence/replay options | lifecycle orchestrator owns date/source/checkpoint validation |
| `market-data:backfill:missing-tickers` | PASS | PASS | start/end range, `source_mode=api`, `--ticker_codes`, plan/resume/error-policy/evidence/replay options | ticker-master/current-bars gap scan; plan is non-mutating; lifecycle orchestrator owns promote/evidence/replay |
| `market-data:promote` | PASS | PASS | `--requested_date` or `--run_id`, force replace guarded | date validation, force reason guard |
| `market-data:run:finalize` | PASS | PASS | `--requested_date`, `--source_mode`, `--run_id` | finalize/pointer contract |
| `market-data:eod-bars:ingest` | PASS | PASS | date/source options plus explicit `--request_mode` for stage-by-stage publish proof | command-owned request-mode validation + pipeline input validation |
| `market-data:eod-eligibility:build` | PASS | PASS | date/source options | pipeline input validation |
| `market-data:eod-indicators:compute` | PASS | PASS | date/source options | pipeline input validation |
| `market-data:audit:hash` | PASS | PASS | date/source options | pipeline input validation |
| `market-data:dataset:seal` | PASS | PASS | date/source options | seal precondition validation |
| `market-data:evidence:export` | PASS | PASS | exactly-one selector required by command | `COMMAND_MISSING_REQUIRED_INPUT` |
| `market-data:evidence-replay:full-range-current` | PASS | PASS | optional start/end date range; omitted range uses current publication pointer min/max; fixture/output/error-continuation options | proof-only current pointer resolver; no import/promote/finalize; `NO_READABLE_PUBLICATION` on missing current readable date |
| `market-data:sector-indexes:ingest-api` | PASS | PASS | start/end date range, provider, symbol suffix/map, dry-run/apply guard | command-owned date/provider validation; default `COMMAND_DRY_RUN_ONLY`; explicit `COMMAND_APPLY_CONFIRMED` on apply; fail-closed on incomplete provider response |
| `market-data:sector-indexes:import-bars` | PASS | PASS | input CSV, source name, dry-run/apply guard | command-owned CSV validation; default `COMMAND_DRY_RUN_ONLY`; explicit `COMMAND_APPLY_CONFIRMED` on apply |
| `market-data:sectors:import-memberships` | PASS | PASS | input CSV, classification system, source name, dry-run/apply guard | command-owned CSV validation; default `COMMAND_DRY_RUN_ONLY`; explicit `COMMAND_APPLY_CONFIRMED` on apply |
| `market-data:events:import-corporate-actions` | PASS | PASS | input CSV, source name, dry-run/apply guard | command-owned CSV validation; default `COMMAND_DRY_RUN_ONLY`; explicit `COMMAND_APPLY_CONFIRMED` on apply |
| `market-data:events:import-trading-status` | PASS | PASS | input CSV, source name, dry-run/apply guard | command-owned CSV validation; default `COMMAND_DRY_RUN_ONLY`; explicit `COMMAND_APPLY_CONFIRMED` on apply |
| `market-data:replay:verify` | PASS | PASS | parser args optional only for command-owned missing-input output; operator contract still requires run id and fixture path | `COMMAND_MISSING_REQUIRED_INPUT`, `replay_status=BLOCKED` |
| `market-data:replay:smoke` | PASS | PASS | parser run id optional only for command-owned missing-input output | `COMMAND_MISSING_REQUIRED_INPUT`, service failure catch |
| `market-data:replay:backfill` | PASS | PASS | parser dates optional only for command-owned missing-input output | `COMMAND_MISSING_REQUIRED_INPUT` |
| `market-data:replay:fixture:generate` | PASS | PASS | parser run id optional only for command-owned missing-input output | `COMMAND_MISSING_REQUIRED_INPUT` |
| `market-data:correction:request` | PASS | PASS | trade date/reason required by command validation | correction baseline guard |
| `market-data:correction:approve` | PASS | PASS | parser id optional only for command-owned missing-input output | `COMMAND_MISSING_REQUIRED_INPUT`, `COMMAND_CORRECTION_NOT_FOUND` |
| `market-data:correction:run` | PASS | PASS | parser id optional only for command-owned missing-input output | `COMMAND_MISSING_REQUIRED_INPUT`, lifecycle status guard |
| `market-data:current-publication:repair` | PASS | PASS | dry-run default, `--apply` guarded by reason | `COMMAND_DESTRUCTIVE_GUARD_REQUIRED` |
| `market-data:session-snapshot` | PASS | PASS | parser args optional only for command-owned missing-input output | `COMMAND_MISSING_REQUIRED_INPUT`, readable publication guard |
| `market-data:session-snapshot:purge` | PASS | PASS | dry-run default, `--apply` explicit | `COMMAND_DRY_RUN_ONLY`, `COMMAND_APPLY_CONFIRMED` |
| `market-data:provider:smoke` | PASS | PASS | safe single-ticker dry-run provider smoke; `--json` emits JSON stdout; `--provider` overrides API provider config | `PROVIDER_SMOKE_TICKER_REQUIRED`, `PROVIDER_SMOKE_FULL_UNIVERSE_BLOCKED`, final live proof `provider_smoke_status=PASS` / `reason_code=PROVIDER_SMOKE_OK` / `http_status=200` |

## Provider-Smoke Safe-Mode Overlay

`market-data:provider:smoke` is part of the current public command surface. Including `market-data:backfill:lifecycle`, `market-data:evidence-replay:full-range-current`, `market-data:sectors:import-memberships`, `market-data:sector-indexes:import-bars`, `market-data:sector-indexes:ingest-api`, `market-data:events:import-corporate-actions`, `market-data:events:import-trading-status`, and `market-data:backfill:missing-tickers` brings the current command count to 29. The provider overlay is intentionally separated from the 2026-05-20 seeded fixture matrix because live provider behavior is environment/upstream dependent; the full-range evidence/replay extension is proof-only and uses existing current publications, sector/event imports only write source, membership, or benchmark/source rows after CSV/API validation and explicit apply, and missing-ticker backfill enters the normal lifecycle only for current bar gaps.

Current proof from this reconciliation:

```text
php artisan list market-data -> 29 public market-data commands registered
php artisan market-data:provider:smoke --help -> exit 0
php artisan market-data:backfill:lifecycle --help -> exit 0
php artisan market-data:backfill:missing-tickers --help -> exit 0
php artisan market-data:evidence-replay:full-range-current --help -> exit 0
php artisan market-data:sector-indexes:ingest-api --help -> exit 0
php artisan market-data:sector-indexes:import-bars --help -> exit 0
php artisan market-data:sectors:import-memberships --help -> exit 0
php artisan market-data:events:import-corporate-actions --help -> exit 0
php artisan market-data:events:import-trading-status --help -> exit 0
php artisan market-data:provider:smoke -> exit 1, provider_smoke_status=BLOCKED, reason_code=PROVIDER_SMOKE_TICKER_REQUIRED
php artisan market-data:provider:smoke --ticker=BBCA --trade_date=2026-05-20 --dry-run --retry-max=0 -> exit 0, provider_smoke_status=PASS, reason_code=PROVIDER_SMOKE_OK, http_status=200, returned_row_count=1, retry_exhausted=false
```

Provider smoke safe-mode invariants and final proof:

- `provider_smoke_status=PASS`
- `reason_code=PROVIDER_SMOKE_OK`
- `source_reason_code=none`
- `http_status=200`
- `returned_row_count=1`
- `attempt_count=1`
- `retry_max=0`
- `retry_exhausted=false`
- `adapter_reason_code=PROVIDER_SMOKE_OK`
- `request_url=https://query1.finance.yahoo.com/v8/finance/chart/BBCA.JK?interval=1d&range=10d&includePrePost=false&events=div%2Csplits&corsDomain=finance.yahoo.com`
- `publication_created=false`
- `seal_executed=false`
- `finalize_executed=false`
- `pointer_switched=false`
- `readable_publication_created=false`
- `full_universe_fetch=false`

Decision: command surface coverage is current at 21 commands and final provider smoke runtime proof is PASS. `PROVIDER_RATE_LIMITED`, `PROVIDER_TIMEOUT`, and `PROVIDER_NETWORK_ERROR` remain BLOCKED outcomes for future runs, but the current final artifact records `provider_smoke_status=PASS`, `reason_code=PROVIDER_SMOKE_OK`, and `http_status=200`.

## Help Proof Matrix

All commands below returned exit 0 and rendered usage/options:

```text
php artisan --env=testing market-data:daily --help
php artisan --env=testing market-data:backfill --help
php artisan --env=testing market-data:backfill:lifecycle --help
php artisan --env=testing market-data:promote --help
php artisan --env=testing market-data:run:finalize --help
php artisan --env=testing market-data:eod-bars:ingest --help
php artisan --env=testing market-data:eod-eligibility:build --help
php artisan --env=testing market-data:eod-indicators:compute --help
php artisan --env=testing market-data:audit:hash --help
php artisan --env=testing market-data:dataset:seal --help
php artisan --env=testing market-data:evidence:export --help
php artisan --env=testing market-data:evidence-replay:full-range-current --help
php artisan --env=testing market-data:sector-indexes:ingest-api --help
php artisan --env=testing market-data:sector-indexes:import-bars --help
php artisan --env=testing market-data:sectors:import-memberships --help
php artisan --env=testing market-data:events:import-corporate-actions --help
php artisan --env=testing market-data:events:import-trading-status --help
php artisan --env=testing market-data:replay:verify --help
php artisan --env=testing market-data:replay:smoke --help
php artisan --env=testing market-data:replay:backfill --help
php artisan --env=testing market-data:replay:fixture:generate --help
php artisan --env=testing market-data:correction:request --help
php artisan --env=testing market-data:correction:approve --help
php artisan --env=testing market-data:correction:run --help
php artisan --env=testing market-data:current-publication:repair --help
php artisan --env=testing market-data:session-snapshot --help
php artisan --env=testing market-data:session-snapshot:purge --help
php artisan market-data:provider:smoke --help
```

## Invalid Input Runtime Matrix

| Command | Invocation | Exit | Output Summary | State Effect | Status |
|---|---|---:|---|---|---|
| `market-data:daily` | `php artisan --env=testing market-data:daily --requested_date=not-a-date` | 1 | `status=BLOCKED`, `reason_code=COMMAND_INVALID_DATE_FORMAT` | no pipeline mutation | PASS |
| `market-data:promote` | `php artisan --env=testing market-data:promote --requested_date=not-a-date` | 1 | `status=BLOCKED`, `reason_code=COMMAND_INVALID_DATE_FORMAT` | no promotion | PASS |
| `market-data:backfill` | `php artisan --env=testing market-data:backfill` | 1 | `status=BLOCKED`, `reason_code=COMMAND_MISSING_REQUIRED_INPUT` | no backfill | PASS |
| `market-data:backfill` | `php artisan --env=testing market-data:backfill not-a-date 2026-01-01` | 1 | `status=BLOCKED`, `reason_code=COMMAND_INVALID_DATE_FORMAT` | no backfill | PASS |
| `market-data:eod-bars:ingest` | `php artisan --env=testing market-data:eod-bars:ingest --requested_date=2026-05-13 --source_mode=manual_file --request_mode=bad` | 1 | `status=BLOCKED`, `reason_code=COMMAND_INVALID_REQUEST_MODE` | no ingest mutation | PASS |
| `market-data:evidence:export` | `php artisan --env=testing market-data:evidence:export` | 1 | `status=BLOCKED`, `reason_code=COMMAND_MISSING_REQUIRED_INPUT` | no evidence artifact claimed | PASS |
| `market-data:replay:verify` | `php artisan --env=testing market-data:replay:verify` | 1 | `status=BLOCKED`, `reason_code=COMMAND_MISSING_REQUIRED_INPUT`, `replay_status=BLOCKED` | no replay success row claimed | PASS |
| `market-data:replay:verify` | `php artisan --env=testing market-data:replay:verify 1` | 1 | `status=BLOCKED`, `reason_code=COMMAND_MISSING_REQUIRED_INPUT`, `replay_status=BLOCKED`, `run_id=1` | no replay success row claimed | PASS |
| `market-data:replay:smoke` | `php artisan --env=testing market-data:replay:smoke 0` | 1 | `status=BLOCKED`, `reason_code=COMMAND_MISSING_REQUIRED_INPUT`, `replay_status=BLOCKED`, `run_id=0` | no suite run | PASS |
| `market-data:replay:smoke` | `php artisan --env=testing market-data:replay:smoke 1` | 1 | `status=BLOCKED`, `reason_code=COMMAND_EXECUTION_FAILED`, `replay_status=BLOCKED` for missing default fixture root | no PASS claimed | PASS |
| `market-data:replay:backfill` | `php artisan --env=testing market-data:replay:backfill` | 1 | `status=BLOCKED`, `reason_code=COMMAND_MISSING_REQUIRED_INPUT` | no backfill replay | PASS |
| `market-data:replay:fixture:generate` | `php artisan --env=testing market-data:replay:fixture:generate 0` | 1 | `status=BLOCKED`, `reason_code=COMMAND_MISSING_REQUIRED_INPUT` | no fixture generated | PASS |
| `market-data:correction:request` | `php artisan --env=testing market-data:correction:request --trade_date=2026-01-01` | 1 | `status=BLOCKED`, `reason_code=COMMAND_MISSING_REQUIRED_INPUT` | no correction row | PASS |
| `market-data:correction:approve` | `php artisan --env=testing market-data:correction:approve 0` | 1 | `status=BLOCKED`, `reason_code=COMMAND_MISSING_REQUIRED_INPUT` | no approval | PASS |
| `market-data:correction:approve` | `php artisan --env=testing market-data:correction:approve 999999` | 1 | `status=BLOCKED`, `reason_code=COMMAND_CORRECTION_NOT_FOUND`, `correction_id=999999` | no approval | PASS |
| `market-data:correction:run` | `php artisan --env=testing market-data:correction:run 0` | 1 | `status=BLOCKED`, `reason_code=COMMAND_MISSING_REQUIRED_INPUT` | no correction run | PASS |
| `market-data:current-publication:repair` | `php artisan --env=testing market-data:current-publication:repair --trade_date=not-a-date` | 1 | `status=BLOCKED`, `reason_code=COMMAND_INVALID_DATE_FORMAT` | no repair | PASS |
| `market-data:session-snapshot:purge` | `php artisan --env=testing market-data:session-snapshot:purge --before_date=not-a-date` | 1 | `status=BLOCKED`, `reason_code=COMMAND_INVALID_DATE_FORMAT` | no delete | PASS |
| `market-data:session-snapshot` | `php artisan --env=testing market-data:session-snapshot 2026-01-01` | 1 | `status=BLOCKED`, `reason_code=COMMAND_MISSING_REQUIRED_INPUT` | no snapshot | PASS |
| `market-data:session-snapshot` | `php artisan --env=testing market-data:session-snapshot not-a-date PREOPEN` | 1 | `status=BLOCKED`, `reason_code=COMMAND_INVALID_DATE_FORMAT` | no snapshot | PASS |
| `market-data:promote` | `php artisan --env=testing market-data:promote --run_id=6 --force_replace=true` | 1 | `status=BLOCKED`, `reason_code=COMMAND_DESTRUCTIVE_GUARD_REQUIRED`, `force_replace=true` | no force replacement | PASS |

## Production-Ready Runtime Matrix

Fixture setup: `php tests/Support/MarketData/SeedOpsCommandRuntimeMatrixFixture.php` -> exit 0, `status=FIXTURE_READY`, `database=tradeaxis`, `ticker_count=913`, target dates `2026-05-11` through `2026-05-18`, manifest `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/fixture_manifest.json`.

Command output proof root: `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/command-output/**`.

| Command | Invocation | Exit | Output Summary | State Effect | Proof | Status |
|---|---|---:|---|---|---|---|
| `market-data:daily` | `php artisan --env=testing market-data:daily --requested_date=2026-05-11 --source_mode=manual_file --input_file=storage/app/market_data/eod_bars/2026-05-11.json --output_dir=storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/daily-2026-05-11` | 0 | `run_id=30`, `request_mode=import_only`, `accepted_row_count=913`, `promoted=false`, `pointer_switched=false`, `seal_state=UNSEALED` | fresh import-only run; no readable publication claim | `command-output/daily-success-2026-05-11.txt` and daily summary artifact | PASS |
| `market-data:backfill` | `php artisan --env=testing market-data:backfill 2026-05-12 2026-05-12 --source_mode=manual_file --input_file=storage/app/market_data/eod_bars/2026-05-12.json --output_dir=storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/backfill-2026-05-12` | 0 | `all_imported=1`, `all_passed=1`, one case imported with `accepted_row_count=913` | fresh backfill import-only case; no publication pointer mutation | `command-output/backfill-success-2026-05-12.txt` | PASS |
| `market-data:eod-bars:ingest` | `php artisan --env=testing market-data:eod-bars:ingest --requested_date=2026-05-13 --source_mode=manual_file --request_mode=full_publish` | 0 | `run_id=32`, `request_mode=full_publish`, `publication_id=26`, `accepted_row_count=913` | stage-by-stage publish run started with explicit full-publish context | `command-output/stage-ingest-full-publish-2026-05-13.txt` | PASS |
| `market-data:eod-indicators:compute` | `php artisan --env=testing market-data:eod-indicators:compute --requested_date=2026-05-13 --source_mode=manual_file --run_id=32` | 0 | `run_id=32`, indicator rows written for the stage run | deterministic indicator stage completed | `command-output/stage-indicators-2026-05-13.txt` | PASS |
| `market-data:eod-eligibility:build` | `php artisan --env=testing market-data:eod-eligibility:build --requested_date=2026-05-13 --source_mode=manual_file --run_id=32` | 0 | `run_id=32`, eligibility rows written for the stage run | eligibility artifact completed | `command-output/stage-eligibility-2026-05-13.txt` | PASS |
| `market-data:audit:hash` | `php artisan --env=testing market-data:audit:hash --requested_date=2026-05-13 --source_mode=manual_file --run_id=32` | 0 | `bars_batch_hash`, `indicators_batch_hash`, and `eligibility_batch_hash` printed | hash command is runnable and deterministic after `completeHash()` visibility fix | `command-output/stage-hash-2026-05-13.txt` | PASS |
| `market-data:dataset:seal` | `php artisan --env=testing market-data:dataset:seal --requested_date=2026-05-13 --source_mode=manual_file --run_id=32` | 0 | `seal_state=SEALED`, `sealed_by=system` | dataset sealed before finalize | `command-output/stage-seal-2026-05-13.txt` | PASS |
| `market-data:run:finalize` | `php artisan --env=testing market-data:run:finalize --requested_date=2026-05-13 --source_mode=manual_file --run_id=32` | 0 | `terminal_status=SUCCESS`, `publishability_state=READABLE`, `coverage_gate_state=PASS`, `current_publication_id=26`, `pointer_switched=true` | stage-by-stage full publish created readable current publication | `command-output/stage-finalize-2026-05-13.txt` | PASS |
| `market-data:promote` | import then `php artisan --env=testing market-data:promote --requested_date=2026-05-14 --source_mode=manual_file --run_id=33 --output_dir=storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/promote-2026-05-14` | 0 | `run_id=33`, `terminal_status=SUCCESS`, `publishability_state=READABLE`, `coverage_gate_state=PASS`, `publication_id=27`, `current_publication_id=27`, `seal_state=SEALED` | fresh promote success and current pointer switch | `command-output/promote-success-2026-05-14.txt` | PASS |
| `market-data:session-snapshot` | `php artisan --env=testing market-data:session-snapshot 2026-05-14 OPEN_CHECK --source_mode=manual_file --input_file=storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/input/session-snapshot-2026-05-14.json --output_dir=storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/session-snapshot-2026-05-14` | 0 | `publication_id=27`, `run_id=33`, `scope_count=913`, `captured_count=913`, `skipped_count=0`, `slot_anchor_time=09:10:00` | readable-publication snapshot captured with full scope | `command-output/session-snapshot-success-2026-05-14.txt` and snapshot summary artifact | PASS |
| `market-data:promote` lock baseline | import then `php artisan --env=testing market-data:promote --requested_date=2026-05-15 --source_mode=manual_file --run_id=34 --output_dir=.../lock-promote-baseline-2026-05-15` | 0 | baseline `run_id=34`, `SUCCESS`, `READABLE`, `current_publication_id` present | baseline current pointer established for conflict proof | `command-output/lock-promote-baseline-2026-05-15.txt` | PASS |
| `market-data:promote` lock conflict | `php artisan --env=testing market-data:promote --requested_date=2026-05-15 --source_mode=manual_file --output_dir=storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/lock-promote-conflict-2026-05-15` | 1 | `run_id=35`, `terminal_status=HELD`, `publishability_state=NOT_READABLE`, `reason_code=RUN_LOCK_CONFLICT`, `pointer_switched=false` | current pointer preserved; duplicate replacement did not silently switch | `command-output/lock-promote-conflict-2026-05-15.txt` | PASS |
| `market-data:promote` held path | partial import then `php artisan --env=testing market-data:promote --requested_date=2026-05-16 --source_mode=manual_file --run_id=36 --output_dir=.../held-partial-promote-2026-05-16` | 1 | `terminal_status=HELD`, `publishability_state=NOT_READABLE`, `coverage_gate_state=FAIL`, `coverage_summary=available=5/913`, `reason_code=RUN_PARTIAL_DATA` | partial manual file is held and not readable; no pointer switch | `command-output/held-partial-promote-2026-05-16.txt` | PASS |
| `market-data:daily` failed path | `php artisan --env=testing market-data:daily --requested_date=2026-05-17 --source_mode=manual_file --input_file=storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/input/eod-bars-2026-05-17-empty.json --output_dir=.../failed-empty-daily-2026-05-17` | 1 | `run_id=37`, `terminal_status=FAILED`, `publishability_state=NOT_READABLE`, `reason_code=RUN_SOURCE_MANUAL_FILE_EMPTY`, `error=Manual file source contains no valid data rows...` | empty source blocked; no publication/pointer success | `command-output/failed-empty-daily-2026-05-17.txt` | PASS |
| `market-data:current-publication:repair` dry-run | `php artisan --env=testing market-data:current-publication:repair --trade_date=2026-05-18` | 1 | `status=INVALID_CURRENT_PUBLICATION`, integrity reasons, `operation_mode=DRY_RUN`, `reason_code=COMMAND_DRY_RUN_ONLY`, next action | invalid pointer fixture detected without mutation | `command-output/repair-invalid-dry-run-2026-05-18.txt` | PASS |
| `market-data:current-publication:repair` apply | `php artisan --env=testing market-data:current-publication:repair --trade_date=2026-05-18 --apply --reason="ops runtime matrix invalid pointer fixture reviewed"` | 0 | `operation_mode=APPLIED`, `reason_code=COMMAND_APPLY_CONFIRMED`, `repair_action=CLEARED_INVALID_CURRENT_STATE`, pointer after blank | invalid current state cleared with explicit reason | `command-output/repair-invalid-apply-2026-05-18.txt` | PASS |
| `market-data:current-publication:repair` after apply | `php artisan --env=testing market-data:current-publication:repair --trade_date=2026-05-18` | 0 | `status=OK`, no invalid current pointer | repeated repair is safe no-op after apply | `command-output/repair-invalid-after-apply-2026-05-18.txt` | PASS |
| `market-data:evidence:export` | `php artisan --env=testing market-data:evidence:export --run_id=33 --output_dir=storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/evidence-run-33` | 0 | `selector=run`, `terminal_status=SUCCESS`, `publishability_state=READABLE`, `coverage_gate_state=PASS`, `evidence_admission_state=ADMITTED_COMPLETE`, `file_count=10` | run evidence artifacts written | `command-output/evidence-export-run-2026-05-14.txt` and `evidence-run-33/**` | PASS |
| `market-data:replay:fixture:generate` | `php artisan --env=testing market-data:replay:fixture:generate 33 --case=ops_matrix_production_ready --output_dir=storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/fixtures/run-33-ops-production-ready` | 0 | `fixture_generated=1`, `publication_id=27`, `pointer_publication_id=27`, manifest and expected proof paths printed | replay fixture generated from actual promoted run | `command-output/replay-fixture-generate-2026-05-14.txt` | PASS |
| `market-data:replay:verify` | `php artisan --env=testing market-data:replay:verify 33 storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/fixtures/run-33-ops-production-ready --output_dir=storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/replay-verify-run-33` | 0 | `replay_id=15`, `comparison_result=MATCH`, `replay_status=PASS`, `mismatch_count=0` | replay proof row/artifacts written | `command-output/replay-verify-2026-05-14.txt` and `replay-verify-run-33/**` | PASS |
| `market-data:replay:smoke` | `php artisan --env=testing market-data:replay:smoke 33 --fixture_root=storage/app/market-data/replay-fixtures --output_dir=storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/replay-smoke-run-33 --generate_runtime_valid_case` | 0 | `all_passed=1`; valid case PASS `replay_id=16`; mismatch case expected/observed MISMATCH `replay_id=17`; broken/missing fixture cases BLOCKED | smoke suite proves PASS/FAIL/BLOCKED replay output | `command-output/replay-smoke-2026-05-14.txt` | PASS |
| `market-data:replay:backfill` | `php artisan --env=testing market-data:replay:backfill 2026-05-14 2026-05-14 --fixture_case=run-33-ops-production-ready --fixture_root=storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/fixtures --output_dir=storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/replay-backfill-run-33` | 0 | `all_passed=1`, `run_id=33`, `replay_id=18`, `replay_status=PASS` | replay backfill persisted result/artifacts | `command-output/replay-backfill-2026-05-14.txt` | PASS |

## Seeded Runtime Matrix

| Command | Invocation | Exit | Output Summary | State Effect | Proof | Status |
|---|---|---:|---|---|---|---|
| `market-data:run:finalize` | `php artisan --env=testing market-data:run:finalize --requested_date=2026-02-18 --source_mode=manual_file --run_id=6` | 0 | `run_id=6`, `terminal_status=SUCCESS`, `publishability_state=READABLE`, `promote_status=PROMOTED`, `pointer_switched=true`, `current_publication_id=5`, `publication_id=5`, `coverage_gate_state=PASS`, `seal_state=SEALED` | seeded readable publication finalized/current | command output | PASS |
| `market-data:run:finalize` | same command re-run | 0 | same `run_id=6`, `publication_id=5`, `current_publication_id=5`, readable/sealed/PASS output | repeated execution idempotency proof; no silent duplicate current pointer in output | command output | PASS |
| `market-data:evidence:export` | `php artisan --env=testing market-data:evidence:export --run_id=6 --output_dir=storage/app/market-data/ops-command-surface-runtime-matrix/evidence-run-6` | 0 | `selector=run`, `selector_id=6`, `terminal_status=SUCCESS`, `publishability_state=READABLE`, `coverage_gate_state=PASS`, `publication_id=5`, `file_count=10` | evidence artifacts written | `storage/app/market-data/ops-command-surface-runtime-matrix/evidence-run-6/**` | PASS |
| `market-data:evidence:export` | `php artisan --env=testing market-data:evidence:export --replay_id=10 --trade_date=2026-02-18 --output_dir=storage/app/market-data/ops-command-surface-runtime-matrix/evidence-replay-10` | 0 | `selector=replay`, `selector_id=10`, `replay_status=PASS`, `comparison_result=MATCH`, `status=SUCCESS`, `file_count=6` | replay evidence artifacts written | `storage/app/market-data/ops-command-surface-runtime-matrix/evidence-replay-10/**` | PASS |
| `market-data:evidence:export` | `php artisan --env=testing market-data:evidence:export --correction_id=3 --output_dir=storage/app/market-data/ops-command-surface-runtime-matrix/evidence-correction-3` | 0 | `selector=correction`, `selector_id=3`, `status=CONSUMED_CURRENT`, `reseal_status=NOT_RESEALED_UNCHANGED`, `file_count=2` | correction evidence artifacts written | `storage/app/market-data/ops-command-surface-runtime-matrix/evidence-correction-3/**` | PASS |
| `market-data:replay:fixture:generate` | `php artisan --env=testing market-data:replay:fixture:generate 6 --case=ops_matrix_valid --output_dir=storage/app/market-data/ops-command-surface-runtime-matrix/fixtures/run-6-ops-matrix` | 0 | `fixture_generated=1`, `run_id=6`, `coverage_gate_state=PASS`, `publication_id=5`, `pointer_publication_id=5`, `manifest_path=.../manifest.json` | fixture artifacts written | `storage/app/market-data/ops-command-surface-runtime-matrix/fixtures/run-6-ops-matrix/**` | PASS |
| `market-data:replay:verify` | `php artisan --env=testing market-data:replay:verify 6 storage/app/market-data/ops-command-surface-runtime-matrix/fixtures/run-6-ops-matrix --output_dir=storage/app/market-data/ops-command-surface-runtime-matrix/replay-verify-run-6` | 0 | `replay_id=11`, `comparison_result=MATCH`, `replay_status=PASS`, `mismatch_count=0` | replay result persisted; artifacts written | `storage/app/market-data/ops-command-surface-runtime-matrix/replay-verify-run-6/**` | PASS |
| `market-data:replay:smoke` | `php artisan --env=testing market-data:replay:smoke 6 --fixture_root=storage/app/market-data/replay-fixtures --output_dir=storage/app/market-data/ops-command-surface-runtime-matrix/replay-smoke-run-6 --generate_runtime_valid_case` | 0 | `all_passed=1`; valid case PASS `replay_id=12`; mismatch case expected/observed MISMATCH `replay_id=13`; broken/missing fixture cases BLOCKED with reason codes | replay smoke result rows/artifacts written | `storage/app/market-data/ops-command-surface-runtime-matrix/replay-smoke-run-6/**` | PASS |
| `market-data:replay:backfill` | `php artisan --env=testing market-data:replay:backfill 2026-02-18 2026-02-18 --fixture_case=run-6-ops-matrix --fixture_root=storage/app/market-data/ops-command-surface-runtime-matrix/fixtures --output_dir=storage/app/market-data/ops-command-surface-runtime-matrix/replay-backfill-run-6` | 0 | `all_passed=1`, `trade_date=2026-02-18`, `run_id=6`, `replay_id=14`, `replay_status=PASS` | replay backfill result persisted/artifacts written | `storage/app/market-data/ops-command-surface-runtime-matrix/replay-backfill-run-6/**` | PASS |
| `market-data:current-publication:repair` | `php artisan --env=testing market-data:current-publication:repair --trade_date=2026-02-18` | 0 | `status=OK`, no invalid current pointer | dry-run/no-op; no mutation | command output | PASS |
| `market-data:current-publication:repair` | `php artisan --env=testing market-data:current-publication:repair --trade_date=2026-02-18 --apply` | 1 | `status=BLOCKED`, `reason_code=COMMAND_DESTRUCTIVE_GUARD_REQUIRED` | no repair mutation | command output | PASS |
| `market-data:session-snapshot:purge` | `php artisan --env=testing market-data:session-snapshot:purge --before_date=2026-01-01 --dry-run --output_dir=storage/app/market-data/ops-command-surface-runtime-matrix/purge-dry-run` | 0 | `operation_mode=DRY_RUN`, `COMMAND_DRY_RUN_ONLY`, `candidate_rows=0`, `deleted_rows=0`, next action printed | no delete | `storage/app/market-data/ops-command-surface-runtime-matrix/purge-dry-run/**` | PASS |
| `market-data:session-snapshot:purge` | `php artisan --env=testing market-data:session-snapshot:purge --before_date=2026-01-01 --apply --output_dir=storage/app/market-data/ops-command-surface-runtime-matrix/purge-apply-zero` | 0 | `operation_mode=APPLIED`, `COMMAND_APPLY_CONFIRMED`, `candidate_rows=0`, `deleted_rows=0` | safe apply-zero no-op | `storage/app/market-data/ops-command-surface-runtime-matrix/purge-apply-zero/**` | PASS |
| `market-data:session-snapshot` | `php artisan --env=testing market-data:session-snapshot 2026-01-01 PREOPEN --output_dir=storage/app/market-data/ops-command-surface-runtime-matrix/session-snapshot-no-readable` | 1 | `status=BLOCKED`, `reason_code=NO_READABLE_PUBLICATION`, `snapshot_slot=PREOPEN` | no snapshot rows | command output | PASS |
| `market-data:correction:run` | `php artisan --env=testing market-data:correction:run 4 --requested_date=2026-02-18 --source_mode=manual_file` | 1 | `status=BLOCKED`, `reason_code=COMMAND_CORRECTION_STATUS_NOT_EXECUTABLE`, `correction_status=FAILED` | no correction rerun/publish | command output | PASS |
| `market-data:correction:request` | `php artisan --env=testing market-data:correction:request --trade_date=2026-01-01 --reason_code=CORRECTION_OPERATOR_REQUESTED --reason_note=\"ops matrix no baseline proof\" --requested_by=codex` | 1 | `status=BLOCKED`, `reason_code=CORRECTION_BASELINE_LINK_MISSING` | no correction row | command output | PASS |
| `market-data:promote` | `php artisan --env=testing market-data:promote --run_id=6 --force_replace=true` | 1 | `status=BLOCKED`, `reason_code=COMMAND_DESTRUCTIVE_GUARD_REQUIRED` | no force replacement | command output | PASS |

## Previously Blocked Cases Closed

The prior enforced matrix kept these cases blocked because no isolated fixture pack had been prepared. The production-ready fixture matrix above closes them with concrete command output under `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/command-output/**`.

| Case | Reason | Status |
|---|---|---|
| New `market-data:daily` success path | isolated manual-file fixture for `2026-05-11` produced `run_id=30`, `accepted_row_count=913`, import-only output | CLOSED_RUNTIME_PROOF_PASS |
| New `market-data:backfill` success path | isolated one-day backfill fixture for `2026-05-12` produced `all_imported=1`, `all_passed=1` | CLOSED_RUNTIME_PROOF_PASS |
| New `market-data:promote` success path | isolated import/promote fixture for `2026-05-14` produced `run_id=33`, `SUCCESS`, `READABLE`, `current_publication_id=27` | CLOSED_RUNTIME_PROOF_PASS |
| Stage commands success path (`eod-bars`, indicators, eligibility, hash, seal, finalize) | `2026-05-13` stage chain used explicit `--request_mode=full_publish` and finalized `run_id=32` as readable/current | CLOSED_RUNTIME_PROOF_PASS |
| Real lock conflict | `2026-05-15` second promote returned exit 1, `terminal_status=HELD`, `reason_code=RUN_LOCK_CONFLICT`, `pointer_switched=false` | CLOSED_RUNTIME_PROOF_PASS |
| `current-publication:repair --apply` against invalid pointer | invalid pointer fixture for `2026-05-18` was dry-run detected, applied with reason, then rerun as no-op `status=OK` | CLOSED_RUNTIME_PROOF_PASS |
| `market-data:session-snapshot` success path | `2026-05-14 OPEN_CHECK` captured `913/913` rows against readable current publication `27` | CLOSED_RUNTIME_PROOF_PASS |

## Patch Summary

- Backfill, replay verify, replay smoke, replay backfill, replay fixture generation, correction approve/run, and session snapshot capture now expose parser-optional arguments where needed so the command can render `status=BLOCKED` and a reason code instead of a raw Symfony missing-argument error.
- `ReplaySmokeSuiteCommand` catches service failures and renders `status=BLOCKED`, an actionable `reason_code`, and `replay_status=BLOCKED`.
- `ApproveCorrectionCommand` catches missing/non-executable correction records and renders `COMMAND_CORRECTION_NOT_FOUND` or `COMMAND_CORRECTION_STATUS_NOT_EXECUTABLE`.
- `market-data:eod-bars:ingest` now accepts explicit `--request_mode` for stage-by-stage publish proof while preserving import-only as the default.
- `MarketDataPipelineService::completeHash()` is public so `market-data:audit:hash` can execute the documented hash stage at runtime.
- New command/static tests cover missing required input, request-mode validation, service-failure behavior, and hash-stage command visibility.

## Validation Matrix

| Validation | Result |
|---|---|
| `php -l` changed command/test PHP files | PASS |
| Final registry/help/provider-smoke/full-range-current/sector-import/event-import/missing-ticker loop | PASS for current surface: `php artisan list market-data` exit 0; 29 public market-data commands registered; backfill lifecycle help exits 0; missing-ticker lifecycle help exits 0; provider-smoke help exits 0; full-range current evidence/replay help exits 0; sector membership import help exits 0; sector index CSV bar import help exits 0; sector index API import help exits 0; corporate action import help exits 0; trading status import help exits 0; final provider smoke artifact records `provider_smoke_status=PASS`, `reason_code=PROVIDER_SMOKE_OK`, `http_status=200`, and all non-destructive safety flags false. Historical 2026-05-20 fixture loop remains 20-command proof before lifecycle backfill, provider-smoke, full-range current proof orchestration, sector imports, event imports, and missing-ticker lifecycle were included in the current command surface count. |
| Final invalid-input loop | PASS: daily/promote/backfill/evidence/replay/correction/snapshot/repair/purge/force-guard invalid cases exit 1 with `status=BLOCKED` and reason codes |
| `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php` | PASS: OK (57 tests, 341 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/CorrectionCommandsTest.php` | PASS: OK (11 tests, 60 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/CommandSurfaceSafetyStaticGuardTest.php` | PASS: OK (5 tests, 89 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/OperationalReadinessStaticGuardTest.php` | PASS: OK (10 tests, 204 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php` | PASS: OK (8 tests, 107 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php` | PASS: OK (13 tests, 220 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceRuntimeMatrixStaticGuardTest.php` | PASS: OK (6 tests, 114 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Command"` | PASS: OK (97 tests, 1009 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Ops"` | PASS: OK (74 tests, 616 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Operational"` | PASS: OK (11 tests, 211 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "RuntimeProof"` | PASS: OK (13 tests, 220 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` | PASS: OK (10 tests, 404 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "AuditDocs"` | PASS: OK (10 tests, 404 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` | PASS: OK (176 tests, 4124 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData` | PASS: OK (475 tests, 6942 assertions) |

## Lock Condition

`OPS_COMMAND_SURFACE_RUNTIME_MATRIX_CONTRACT` is LOCKED for the ops command surface scope because:

- all 21 public commands remain registered and help-renderable, with provider-smoke tracked as a safe-mode live-provider overlay and final PASS artifact;
- invalid/missing input proof remains command-owned and reason-coded;
- targeted command/static/audit tests pass after ledger changes;
- full `tests/Unit/MarketData` passes in the supported runtime;
- seeded success, held/not-readable, failed, repeated/idempotency, lock conflict, repair apply, purge, evidence, replay, correction, and session snapshot paths are proven with safe fixtures and command output artifacts.

This LOCKED decision is scoped to the ops command surface runtime matrix. It does not mark the aggregate full market-data production proof pack as final production-ready.

## Next Action

- Use this locked ops command surface matrix as an input to the next aggregate Full Market-Data Validation / Production Proof Pack.
- Reopen this scope only if command signatures, operator output, repair/purge guards, evidence/replay behavior, or publication pointer semantics change.


## 2026-05-22 Provider Smoke PASS Reconciliation

Status: DONE.

This reconciliation updates the ops command surface runtime matrix after the final provider-smoke proof was rerun successfully.

Final provider-smoke evidence:

```text
php artisan market-data:provider:smoke --ticker=BBCA --trade_date=2026-05-20 --dry-run --retry-max=0
provider_smoke_status=PASS
reason_code=PROVIDER_SMOKE_OK
source_reason_code=none
http_status=200
returned_row_count=1
attempt_count=1
retry_max=0
retry_exhausted=false
publication_created=false
seal_executed=false
finalize_executed=false
pointer_switched=false
readable_publication_created=false
full_universe_fetch=false
```

Artifact: `storage/app/market-data/provider-smoke-safe-mode/command-output/provider-smoke-bbca.txt`.

Decision: the prior provider-smoke overlay text that described the live proof as `BLOCKED` / `PROVIDER_RATE_LIMITED` is superseded by the final `PASS` / `PROVIDER_SMOKE_OK` artifact. Future provider rate-limit, timeout, network, parse, empty-response, or missing-date outcomes remain valid BLOCKED outcomes, but they are not the current final proof state.

## 2026-05-23 — Final Provider Smoke / Full PHPUnit PASS Document Reconciliation

[SESSION] FINAL_PROVIDER_SMOKE_FULL_PHPUNIT_DOC_SYNC

[SESSION_STATUS] OPS_RUNTIME_PARITY_PASSED

[FINAL_DECISION]
- Current source ZIP is documented as `OPS_RUNTIME_PARITY_PASSED`.
- Final provider smoke is `FINAL_PROVIDER_SMOKE=PASSED` and `LIVE_PROVIDER_SMOKE_PASSED`.
- Authoritative provider-smoke artifact: `storage/app/market-data/provider-smoke-safe-mode/command-output/provider-smoke-bbca.txt`.
- Provider smoke proof: `provider_smoke_status=PASS`, `reason_code=PROVIDER_SMOKE_OK`, `source_reason_code=none`, `http_status=200`, `returned_row_count=1`, `attempt_count=1`, `retry_max=0`, `retry_exhausted=false`.
- Safety proof: `publication_created=false`, `seal_executed=false`, `finalize_executed=false`, `pointer_switched=false`, `readable_publication_created=false`, `full_universe_fetch=false`.
- Scheduler due-run runtime proof remains present and no silent scheduler failure is claimed.
- Final targeted validation passed: `OpsCommandSurfaceRuntimeMatrixStaticGuardTest` -> OK (6 tests, 120 assertions).
- Final full validation passed: `vendor\bin\phpunit tests/Unit/MarketData` -> OK (492 tests, 7590 assertions), Time 00:36.861, Memory 40.00 MB.

[RECONCILIATION]
- Earlier wording that described provider smoke as provider-rate-limited, provider-blocked, or waiting for full MarketData PHPUnit is superseded for the current source ZIP.
- Future Yahoo/PublicApi rate limit, timeout, network, parse, empty-response, or missing-date outcomes remain valid reason-coded BLOCKED outcomes, but they are not the current final proof state.



---

## 2026-05-23 — SOURCE READY → FULL PRODUCTION READY GAP CLOSURE

[SESSION] SOURCE_READY_FULL_PRODUCTION_READY_GAP_CLOSURE

[SESSION_STATUS] MARKET_DATA_PRODUCTION_READY_LOCKED

[INPUT_SOURCE_ZIP]
- Source ZIP: `tradeaxis-api.zip`
- Source ZIP SHA-256: `86b29452bf563b1f52d9c423072049b0babb6640be5e2ede0dcb1551fa1be325`

[ZIP_HASH_NOTE]
- Locked source-state ZIP hash refers to the operator-local source ZIP used for validation: `86b29452bf563b1f52d9c423072049b0babb6640be5e2ede0dcb1551fa1be325`.
- Uploaded/distribution ZIP hash for this handoff is `6f87f611937f04dac905bd9ea726df8d6579a165860edfb9d914c70c3b2c770c`; it may differ because of packaging/repackaging during handoff.
- This does not change the validated source-state decision as long as the extracted source tree, audit docs, runtime artifacts, and validation outputs match the locked source state.


[FINAL_DECISION]
- `FULLY_PRODUCTION_READY`
- `MARKET_DATA_PRODUCTION_READY_LOCKED`
- `OPS_RUNTIME_PARITY_PASSED`
- `FINAL_PROVIDER_SMOKE=PASSED`
- `LIVE_PROVIDER_SMOKE_PASSED`
- `FULL_MARKET_DATA_PHPUNIT=PASSED` is backed by the latest operator-local full suite: `vendor/bin/phpunit tests/Unit/MarketData` -> OK (511 tests, 7871 assertions).

[DOC_RECONCILIATION]
- Previous provider-rate-limit/provider-blocked/provider-smoke-review-required wording is `SUPERSEDED_BY_FINAL_PROVIDER_SMOKE_PASS_AND_FULL_MARKETDATA_PHPUNIT_PASS` for the current source state.
- Previous scheduler missing-artifact wording is `SUPERSEDED_BY_SCHEDULER_DUE_RUN_AND_NON_SILENT_FAILURE_PROOF` for the current source state.
- Scheduler proof is not overclaimed: current artifacts prove due-run execution and non-silent reason-coded failure visibility, not a successful scheduled daily production run.

[SCHEDULER_PROOF]
- `SCHEDULER_DUE_RUN_PROOF_PASSED`
- `SCHEDULER_NON_SILENT_FAILURE_PROOF_PASSED`
- `SCHEDULER_SUCCESSFUL_DAILY_RUN_PROOF_NOT_CLAIMED`
- Scheduler metadata refreshed in `storage/app/market-data/production-scheduler-cron-deployment-proof/reconciliation/artifact-presence-audit.txt` to the uploaded source ZIP identity.

[CODE_PATCHES]
- Provider empty/invalid response now returns `provider_smoke_status=BLOCKED` with `reason_code=PROVIDER_EMPTY_OR_INVALID_RESPONSE`; parse-failed and missing selected trade date outcomes are also BLOCKED.
- Coverage gate flags are runtime-enforced fail-closed: `enabled=false` and `require_canonical_bar_evidence=false` return `NOT_EVALUABLE`; zero-universe behavior records `coverage_zero_universe_blocked`.
- Finalize predecision now uses persisted candidate `seal_state` and run `sealed_at` proof instead of hardcoded `true` / `SEALED`.
- Correction approve transition is strict: only `REQUESTED` can become `APPROVED`; other states are blocked with `COMMAND_CORRECTION_STATUS_NOT_APPROVABLE`.

[VALIDATION]
- Sandbox syntax validation passed for changed PHP source and test files with `php -l`.
- Sandbox PHPUnit could not run because this PHP CLI lacks required PHPUnit extensions: `dom`, `mbstring`, `xml`, and `xmlwriter`.
- Operator-local validation completed after gap-closure patch: ProviderSmokeSafeModeStaticGuardTest OK (6 tests, 169 assertions); Coverage OK (72 tests, 800 assertions); Finalize OK (51 tests, 392 assertions); Correction OK (75 tests, 1416 assertions); StaticGuard OK (194 tests, 4785 assertions); Full MarketData suite: OK (511 tests, 7871 assertions).

[NEXT_ACTION]
- None for Final Provider Smoke Passed / Ops Runtime Parity Lock. Current source state is DONE / LOCKED / PASSED.
- Future changes to provider headers, endpoint template, scheduler proof, audit docs, command surface, or market-data runtime artifacts must rerun targeted guards and full `vendor/bin/phpunit tests/Unit/MarketData`.
- Recommended next independent hardening scope: CI / Regression Guard to enforce this validation automatically.

[SUPERSEDES]
- Previous provider-smoke / provider-rate-limit / ops-parity review-required next actions are superseded by the final provider smoke PASS and full MarketData PHPUnit PASS.
- Previous active-looking scheduler missing-artifact wording is superseded by current due-run/non-silent-failure artifacts; successful scheduled daily production run proof remains not claimed.

## 2026-05-24 — Market Benchmark + Indicator Extension Runtime Matrix Re-Check

Status: `PASS`.

This append-only reconciliation records the latest current source-state proof after the market benchmark + indicator extension.

- `MARKET_BENCHMARK_INDICATOR_EXTENSION_STATUS=PASS`
- `MARKET_DATA_PRODUCTION_READY_LOCKED=YES`
- `FULL_MARKET_DATA_PHPUNIT=PASSED`
- Full `vendor/bin/phpunit tests/Unit/MarketData` -> OK (511 tests, 7871 assertions).
- Targeted proof: Benchmark OK (14 tests, 84 assertions); Indicator OK (18 tests, 104 assertions); MarketBenchmarkIndicatorExtensionStaticGuardTest OK (5 tests, 46 assertions); AuditDocsSynchronizationStaticGuardTest OK (10 tests, 468 assertions); StaticGuard OK (199 tests, 4930 assertions).
- Runtime proof: daily import `run_id=3` for `2026-05-19` completed with `accepted_row_count=913`, `source_final_status=SUCCESS`, `benchmark_import_status=COMPLETED`, and `benchmark_rows_written=1`.
- Promote proof: `publication_id=2`, `terminal_status=SUCCESS`, `publishability_state=READABLE`, `coverage_gate_state=PASS`, `coverage_ratio=1.0000`, `seal_state=SEALED`, and `pointer_switched=true`.
- Evidence proof: `evidence_completeness_state=COMPLETE`, `evidence_admission_state=ADMITTED_COMPLETE`, `pointer_resolve_status=RESOLVED_READABLE_CURRENT`, and `file_count=11`.
- Replay proof: `replay_id=2`, `comparison_result=MATCH`, `replay_status=PASS`, and `mismatch_count=0`.
- Benchmark proof: `IHSG` is stored as benchmark/index with provider symbol `^JKSE`; `^JKSE.JK` and `IHSG.JK` remain forbidden; benchmark `IND_INSUFFICIENT_HISTORY` is expected until enough historical IHSG bars exist.

Final current-source decision: `FULL_MARKET_DATA_PRODUCTION_READY=YES`, with no remaining blocker for this benchmark/indicator scope.
