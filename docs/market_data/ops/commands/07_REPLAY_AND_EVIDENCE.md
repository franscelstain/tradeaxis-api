# REPLAY DAN EVIDENCE

## Commands
- market-data:replay:verify
- market-data:evidence:export

- market-data:replay:smoke
- market-data:replay:backfill
- market-data:replay:fixture:generate

## Runtime MATCH fixture generation

Use `market-data:replay:fixture:generate <run_id> --case=valid_case --output_dir=<fixture_path>` to generate a deterministic replay fixture from the actual run/publication/pointer/evidence context. This is required for runtime MATCH proof when committed smoke fixtures are intentionally static or stale against the current local run.

Example:

```text
php artisan market-data:replay:fixture:generate 1 --case=valid_case --output_dir=storage/app/market_data/replay-fixtures/generated-valid-run-1
php artisan market-data:replay:verify 1 storage/app/market_data/replay-fixtures/generated-valid-run-1 --output_dir=storage/app/market-data/replay
php artisan market-data:replay:smoke 1 --generate_runtime_valid_case --output_dir=storage/app/market-data/replay
```

Expected generated-fixture proof:

- `fixture_generated=1`
- `expected_result=MATCH`
- generated `manifest.json`
- generated `expected/expected_replay_result.json`
- generated `expected/expected_reason_code_counts.json`
- replay verify against the generated fixture returns `comparison_result=MATCH`, `replay_status=PASS`, and `mismatch_count=0`
- replay verify against an intentionally divergent fixture returns `comparison_result=MISMATCH`, `replay_status=FAIL`, actionable `mismatch_reason_codes`, and a non-zero exit code
- replay verify with missing fixture/context/runtime prerequisites returns command `status=BLOCKED` plus `replay_status=BLOCKED`; this is not a PASS and must be recorded as blocked runtime proof
