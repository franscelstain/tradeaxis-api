# Legacy Semantic Extract — LX-MD-0032-EVD-01

- Source ID: `LS-MD-0032`
- Original path: `audit/MARKET_BENCHMARK_INDICATOR_EXTENSION_INVENTORY.md`
- Original SHA1: `D0BE40DBBA4BFF89D35ED251D64531E8EF56FC39`
- Extract role: `EVIDENCE`
- Source range: `L123-L131`
- Extract body SHA1: `296069E02163A9B4F47C4F274A6F63AB5E7B804C`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Runtime Validation Commands
```bash
php artisan market-data:daily --requested_date=2026-05-19 --source_mode=api --output_dir=storage/app/market_data/daily/2026-05-19 -vvv
php artisan market-data:promote --requested_date=2026-05-19 --source_mode=api --run_id=<RUN_ID> --output_dir=storage/app/market_data/promote/2026-05-19 -vvv
php artisan market-data:evidence:export --run_id=<RUN_ID> --output_dir=storage/app/market_data/evidence/2026-05-19/run -vvv
php artisan market-data:replay:fixture:generate <RUN_ID> --case=valid_case --output_dir=storage/app/market_data/replay-fixtures/2026-05-19/valid_case -vvv
php artisan market-data:replay:verify <RUN_ID> storage/app/market_data/replay-fixtures/2026-05-19/valid_case --output_dir=storage/app/market_data/evidence/2026-05-19/replay -vvv
```


<!-- LEGACY_EXTRACT_BODY_END -->
