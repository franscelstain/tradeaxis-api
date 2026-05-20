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
Result: exit 0, all expected public market-data commands registered.

| Command | Registered | Help Proof | Signature/Docs Sync | Guard Decision |
|---|---:|---:|---|---|
| `market-data:daily` | PASS | PASS | `--requested_date`, `--source_mode`, optional pipeline flags | command-owned date validation |
| `market-data:backfill` | PASS | PASS | parser args optional only for command-owned missing-input output; operator contract still requires start/end dates | `COMMAND_MISSING_REQUIRED_INPUT`, date validation |
| `market-data:promote` | PASS | PASS | `--requested_date` or `--run_id`, force replace guarded | date validation, force reason guard |
| `market-data:run:finalize` | PASS | PASS | `--requested_date`, `--source_mode`, `--run_id` | finalize/pointer contract |
| `market-data:eod-bars:ingest` | PASS | PASS | date/source options plus explicit `--request_mode` for stage-by-stage publish proof | command-owned request-mode validation + pipeline input validation |
| `market-data:eod-eligibility:build` | PASS | PASS | date/source options | pipeline input validation |
| `market-data:eod-indicators:compute` | PASS | PASS | date/source options | pipeline input validation |
| `market-data:audit:hash` | PASS | PASS | date/source options | pipeline input validation |
| `market-data:dataset:seal` | PASS | PASS | date/source options | seal precondition validation |
| `market-data:evidence:export` | PASS | PASS | exactly-one selector required by command | `COMMAND_MISSING_REQUIRED_INPUT` |
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

## Help Proof Matrix

All commands below returned exit 0 and rendered usage/options:

```text
php artisan --env=testing market-data:daily --help
php artisan --env=testing market-data:backfill --help
php artisan --env=testing market-data:promote --help
php artisan --env=testing market-data:run:finalize --help
php artisan --env=testing market-data:eod-bars:ingest --help
php artisan --env=testing market-data:eod-eligibility:build --help
php artisan --env=testing market-data:eod-indicators:compute --help
php artisan --env=testing market-data:audit:hash --help
php artisan --env=testing market-data:dataset:seal --help
php artisan --env=testing market-data:evidence:export --help
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
| Final registry/help loop | PASS: `php artisan --env=testing list market-data` exit 0; all 20 help commands exit 0 |
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

- all 20 public commands remain registered and help-renderable;
- invalid/missing input proof remains command-owned and reason-coded;
- targeted command/static/audit tests pass after ledger changes;
- full `tests/Unit/MarketData` passes in the supported runtime;
- seeded success, held/not-readable, failed, repeated/idempotency, lock conflict, repair apply, purge, evidence, replay, correction, and session snapshot paths are proven with safe fixtures and command output artifacts.

This LOCKED decision is scoped to the ops command surface runtime matrix. It does not mark the aggregate full market-data production proof pack as final production-ready.

## Next Action

- Use this locked ops command surface matrix as an input to the next aggregate Full Market-Data Validation / Production Proof Pack.
- Reopen this scope only if command signatures, operator output, repair/purge guards, evidence/replay behavior, or publication pointer semantics change.
