# Legacy Semantic Extract — LX-MD-0033-EVD-01

- Source ID: `LS-MD-0033`
- Original path: `audit/MARKET_DATA_CONSUMER_READ_MODEL_INVENTORY.md`
- Original SHA1: `A63ADB11787063B5198FC2AB1A3E1DA244D95EC8`
- Extract role: `EVIDENCE`
- Source range: `L180-L299`
- Extract body SHA1: `645D2B73D82DDACBF521F24F114A347A9928596A`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## RUNTIME VALIDATION COMMANDS

If local DB already contains `2026-05-19`, `run_id=3`, and `publication_id=2`, validate via read-model tests or read-only preview commands if added.

Runtime artifact verified in this source tree:
- `storage/app/market_data/daily/2026-05-19/market_data_daily_summary.json` records `run_id=3`, `publication_id=2`, accepted rows `913`, and `benchmark_import_status=COMPLETED`.
- `storage/app/market_data/promote/2026-05-19/market_data_promote_summary.json` records `run_id=3`, `publication_id=2`, `terminal_status=SUCCESS`, `publishability_state=READABLE`, `coverage_gate_state=PASS`, `seal_state=SEALED`, `pointer_switched=true`, `coverage_ratio=1`.

If local DB does not contain the state, operator-local commands:

```bash
php artisan market-data:daily --requested_date=2026-05-19 --source_mode=api --output_dir=storage/app/market_data/daily/2026-05-19 -vvv
php artisan market-data:promote --requested_date=2026-05-19 --source_mode=api --run_id=<RUN_ID> --output_dir=storage/app/market_data/promote/2026-05-19 -vvv
```

Manual database proof:

```sql
SELECT *
FROM eod_current_publication_pointer
WHERE trade_date = '2026-05-19';
```

```sql
SELECT
  p.publication_id,
  p.trade_date,
  p.publication_version,
  p.is_current,
  p.seal_state,
  r.run_id,
  r.terminal_status,
  r.publishability_state,
  r.coverage_gate_state
FROM eod_publications p
JOIN eod_runs r ON r.run_id = p.run_id
WHERE p.trade_date = '2026-05-19'
ORDER BY p.publication_id DESC;
```

Expected publication/run proof:
- publication_id=2
- run_id=3
- is_current=1
- seal_state=SEALED
- terminal_status=SUCCESS
- publishability_state=READABLE
- coverage_gate_state=PASS

Indicator proof:

```sql
SELECT
  ticker_code,
  trade_date,
  roc5,
  roc10,
  roc20,
  hh20,
  ll20,
  ma20,
  ma50,
  close_to_hh20_pct,
  close_to_ll20_pct,
  range_20_pct,
  range_position_20_pct,
  close_vs_ma20_pct,
  close_vs_ma50_pct,
  ma20_slope_pct,
  rs_20_vs_ihsg,
  sector_code,
  sector_roc20,
  rs_20_vs_sector,
  sector_rs_20_vs_ihsg
FROM eod_indicators
WHERE trade_date = '2026-05-19'
ORDER BY ticker_code
LIMIT 20;
```

Benchmark proof:

```sql
SELECT *
FROM market_benchmarks
WHERE benchmark_code = 'IHSG';

SELECT *
FROM market_benchmark_bars
WHERE benchmark_code = 'IHSG'
ORDER BY trade_date DESC
LIMIT 10;

SELECT *
FROM market_benchmark_indicators
WHERE benchmark_code = 'IHSG'
ORDER BY trade_date DESC
LIMIT 10;
```

2026-06-02 weekly-swing extension note:
- Watchlist read output now exposes `roc_5`, `roc_10`, `ll20`, `close_to_ll20_pct`, `range_20_pct`, and `range_position_20_pct` from the same pointer-scoped `eod_indicators` join.
- Benchmark read output now exposes IHSG `ma20_slope_pct`, `close_to_ma20_pct`, and `close_to_ma50_pct` from `market_benchmark_indicators`.
- The read model remains current-readable-publication only and does not add scoring/ranking/entry decisions.

2026-06-03 sector-code source surface note:
- Watchlist read output now exposes nullable `sector_code`, `sector_name`, and `sector_index_code` from the pointer-scoped indicator row and active sector taxonomy.
- Missing membership remains NULL; read-side code does not infer sector from raw/latest ticker metadata.

2026-06-03 sector-rotation source surface note:
- Watchlist read output now exposes nullable `sector_roc20`, `rs_20_vs_sector`, and `sector_rs_20_vs_ihsg` from the pointer-scoped indicator row.
- Missing sector index history remains NULL; read-side code does not infer, forward-fill, or fabricate sector rotation values.

## EVIDENCE / REPLAY IMPACT

EVIDENCE_EXPORT: UNAFFECTED_BY_READ_MODEL
REPLAY_VERIFY: UNAFFECTED_BY_READ_MODEL

This session adds read-only consumer surfaces and reason-code registry support. It does not alter evidence artifact generation, evidence admission, replay hashing, replay comparison, candidate publication promotion, or current pointer switching.


<!-- LEGACY_EXTRACT_BODY_END -->
