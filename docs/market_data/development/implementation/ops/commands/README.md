# COMMAND DOCUMENTATION INDEX

Canonical operator source of truth:

- `../OPERATIONAL_RUNBOOK.md`

The command-specific docs below remain supporting material. They must not contradict the operational runbook, command signatures, evidence export behavior, replay behavior, or locked market-data contracts.

## Import commands
1. `01_DAILY_PIPELINE.md`
2. `02_IMPORT_DATA.md`
5. `05_BACKFILL.md`

## Promote commands
4. `04_FINALIZE_AND_PUBLISH.md`

## Evidence, replay, correction, and session snapshot commands
6. `06_CORRECTION.md`
7. `07_REPLAY_AND_EVIDENCE.md`
8. `08_SESSION_SNAPSHOT.md`

## Detected runtime command surface

Presence in this list means the command is registered, not approved. The names and options require verification against runtime help and executable safety behavior.

- `market-data:daily`
- `market-data:eod-bars:ingest`
- `market-data:eod-indicators:compute`
- `market-data:eod-indicators:recompute-current`
- `market-data:eod-eligibility:build`
- `market-data:audit:hash`
- `market-data:dataset:seal`
- `market-data:run:finalize`
- `market-data:promote`
- `market-data:backfill:lifecycle`
- `market-data:backfill:missing-tickers`
- `market-data:evidence:export`
- `market-data:evidence-replay:full-range-current`
- `market-data:sector-indexes:ingest-api`
- `market-data:sector-indexes:import-bars`
- `market-data:sectors:import-memberships`
- `market-data:events:import-corporate-actions`
- `market-data:events:record-authoritative-terms`
- `market-data:market-structure:record-authoritative-rules`
- `market-data:trading-status:record-authoritative-snapshot`
- `market-data:corpus:admit-conformant-suffix`
- `market-data:corpus:reconstruct-current`
- `market-data:reconcile:corporate-actions`
- `market-data:reconcile:publication-projection`
- `market-data:repair:publication-projection`
- `market-data:events:import-trading-status`
- `market-data:replay:verify`
- `market-data:replay:smoke`
- `market-data:replay:backfill`
- `market-data:replay:fixture:generate`
- `market-data:backfill`
- `market-data:session-snapshot`
- `market-data:session-snapshot:purge`
- `market-data:scheduled`
- `market-data:status`
- `market-data:correction:request`
- `market-data:correction:approve`
- `market-data:correction:run`
- `market-data:current-publication:repair`
- `market-data:provider:smoke`
- `market-data:detect-price-scale-breaks`
- `market-data:repair-price-scale-stretches`
- `market-data:events:derive-corporate-actions`

### Detection-only compatibility surfaces

- `market-data:repair-price-scale-stretches`: runtime help describes a detection-only compatibility command. Direct canonical/history bar mutation remains prohibited.
- `market-data:events:derive-corporate-actions`: runtime help describes a detection-only compatibility command. Price geometry cannot create or verify actions or factors.

## Notes

- penamaan file lama dipertahankan untuk stabilitas dokumentasi
- semantik resmi tetap mengikuti split **IMPORT PHASE** vs **PROMOTE PHASE**
- command surface resmi juga sekarang dikunci sebagai **date-driven**
- `market-data:promote` adalah satu-satunya command promote
- operator tidak boleh memakai raw/staging/latest/MAX(date) sebagai proof readability
- semua failure/hold/not-readable state harus ditindaklanjuti dari reason code dan runbook

## Current indicator recompute lock

`market-data:eod-indicators:recompute-current` is runtime-proven as the no-reimport indicator maintenance path. Runtime proof: full MarketData PHPUnit 640 tests / 9539 assertions on 2026-06-07, full-range recompute 807/807 success, and final current evidence/replay 807/807 MATCH/PASS. Latest docs-review validation on 2026-06-08 reran `vendor\bin\phpunit` and passed with OK (641 tests, 9547 assertions). It must remain read-only toward source/master tables and `eod_bars`.

## Runtime-verified command options

Captured from registered `php artisan help <command> --format=json` on 2026-10-19. This table describes the available syntax; command safety is established by executable refusal and workflow proof.

| Registered command | Command-specific options |
|---|---|
| `market-data:audit:hash` | `--requested_date`, `--source_mode`, `--run_id`, `--correction_id`, `--latest` |
| `market-data:backfill` | `--source_mode`, `--input_file`, `--output_dir`, `--continue_on_error` |
| `market-data:backfill:lifecycle` | `--source_mode`, `--input_file`, `--output_dir`, `--plan`, `--resume`, `--only-failed`, `--continue-on-error`, `--stop-on-error`, `--collect-all-errors`, `--max-dates-per-run`, `--with-evidence`, `--with-replay`, `--replay-fixture-root`, `--no-replay`, `--diagnose-source` |
| `market-data:backfill:missing-tickers` | `--source_mode`, `--ticker_codes`, `--input_file`, `--output_dir`, `--plan`, `--resume`, `--continue-on-error`, `--stop-on-error`, `--collect-all-errors`, `--max-dates-per-run`, `--skip-publication-reprocess`, `--with-evidence`, `--with-replay`, `--replay-fixture-root`, `--no-replay` |
| `market-data:corpus:admit-conformant-suffix` | `--campaign_id`, `--dry-run`, `--apply` |
| `market-data:corpus:reconstruct-current` | `--dry-run`, `--apply`, `--resume`, `--output_dir` |
| `market-data:correction:approve` | `--approved_by` |
| `market-data:correction:request` | `--trade_date`, `--reason_code`, `--reason_note`, `--requested_by` |
| `market-data:correction:run` | `--requested_date`, `--source_mode`, `--latest` |
| `market-data:current-publication:repair` | `--trade_date`, `--apply`, `--reason`, `--force_reason` |
| `market-data:daily` | `--requested_date`, `--source_mode`, `--input_file`, `--output_dir`, `--correction_id`, `--latest` |
| `market-data:dataset:seal` | `--requested_date`, `--source_mode`, `--run_id`, `--correction_id`, `--latest` |
| `market-data:detect-price-scale-breaks` | `--start`, `--end`, `--ticker`, `--dry-run`, `--apply` |
| `market-data:eod-bars:ingest` | `--requested_date`, `--source_mode`, `--run_id`, `--correction_id`, `--request_mode`, `--latest` |
| `market-data:eod-eligibility:build` | `--requested_date`, `--source_mode`, `--run_id`, `--correction_id`, `--latest` |
| `market-data:eod-indicators:compute` | `--requested_date`, `--source_mode`, `--run_id`, `--correction_id`, `--latest` |
| `market-data:eod-indicators:recompute-current` | `--force_replace_reason`, `--output_dir`, `--with-evidence`, `--with-replay`, `--continue-on-error`, `--max_dates`, `--dry-run` |
| `market-data:events:derive-corporate-actions` | `--check-recorded`, `--dry-run`, `--apply`, `--max_gap_pct` |
| `market-data:events:import-corporate-actions` | `--source_name`, `--dry-run`, `--apply` |
| `market-data:events:import-trading-status` | `--source_name`, `--dry-run`, `--apply` |
| `market-data:events:record-authoritative-terms` | `--dry-run`, `--apply` |
| `market-data:evidence-replay:full-range-current` | `--fixture_case`, `--fixture_root`, `--output_dir`, `--continue_on_error`, `--max_dates` |
| `market-data:evidence:export` | `--run_id`, `--correction_id`, `--replay_id`, `--trade_date`, `--output_dir` |
| `market-data:market-structure:record-authoritative-rules` | `--dry-run`, `--apply` |
| `market-data:promote` | `--requested_date`, `--source_mode`, `--run_id`, `--correction_id`, `--mode`, `--output_dir`, `--latest`, `--force_replace`, `--force_replace_reason`, `--force_reason` |
| `market-data:provider:smoke` | `--ticker`, `--trade_date`, `--dry-run`, `--max-tickers`, `--timeout`, `--provider`, `--retry-max`, `--json`, `--output_dir` |
| `market-data:reconcile:corporate-actions` | `--dry-run`, `--apply` |
| `market-data:reconcile:publication-projection` | `--start_date`, `--end_date`, `--latest` |
| `market-data:repair-price-scale-stretches` | `--ticker`, `--dry-run`, `--apply` |
| `market-data:repair:publication-projection` | `--trade_date`, `--dry-run`, `--apply`, `--reason` |
| `market-data:replay:backfill` | `--fixture_case`, `--fixture_root`, `--output_dir`, `--continue_on_error` |
| `market-data:replay:fixture:generate` | `--case`, `--output_dir`, `--publication_id` |
| `market-data:replay:smoke` | `--publication_id`, `--fixture_root`, `--output_dir` |
| `market-data:replay:verify` | `--mode`, `--publication_id`, `--knowledge_cutoff`, `--replay_id`, `--output_dir` |
| `market-data:run:finalize` | `--requested_date`, `--source_mode`, `--run_id`, `--correction_id`, `--latest` |
| `market-data:sector-indexes:import-bars` | `--source_name`, `--dry-run`, `--apply` |
| `market-data:sector-indexes:ingest-api` | `--provider`, `--symbol_suffix`, `--symbol_map_json`, `--dry-run`, `--apply`, `--continue_on_error`, `--allow_partial` |
| `market-data:sectors:import-memberships` | `--classification_system`, `--source_name`, `--source_authority_class`, `--operator_name`, `--reason_code`, `--dry-run`, `--apply` |
| `market-data:session-snapshot` | `--source_mode`, `--input_file`, `--output_dir` |
| `market-data:session-snapshot:purge` | `--before_date`, `--output_dir`, `--dry-run`, `--apply` |
| `market-data:scheduled` | `--source_mode` |
| `market-data:status` | `--requested_date`, `--run_id`, `--latest` |
| `market-data:trading-status:record-authoritative-snapshot` | `--dry-run`, `--apply` |
