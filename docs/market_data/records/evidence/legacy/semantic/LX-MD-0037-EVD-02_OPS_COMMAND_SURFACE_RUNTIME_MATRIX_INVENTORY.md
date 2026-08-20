# Legacy Semantic Extract — LX-MD-0037-EVD-02

- Source ID: `LS-MD-0037`
- Original path: `audit/OPS_COMMAND_SURFACE_RUNTIME_MATRIX_INVENTORY.md`
- Original SHA1: `D6E40A3FC4141C4D0798627BD21A5F34418206F8`
- Extract role: `EVIDENCE`
- Source range: `L116-L151`
- Extract body SHA1: `B175679CE350FED6202A349CF103E3ED37AA9809`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
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
php artisan --env=testing market-data:eod-indicators:recompute-current --help
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


<!-- LEGACY_EXTRACT_BODY_END -->
