# Legacy Semantic Extract — LX-MD-0212-EVD-01

- Source ID: `LS-MD-0212`
- Original path: `patches/TRADING_STATUS_SEMANTICS_LONG_SUSPENSION_CLOSURE_2026_07_02.md`
- Original SHA1: `A26CB394EAA179AFC224499200FE3C7B5AAE0E7B`
- Extract role: `EVIDENCE`
- Source range: `L35-L158`
- Extract body SHA1: `ED701B597BF3C00E1473E78FDC6E3CB446B4832F`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Code validation

Operator-local validation after the final v2 fix:

```text
vendor\bin\phpunit tests\Unit\MarketData\EventRiskSourceRepositoryTest.php
OK (12 tests, 91 assertions)

vendor\bin\phpunit tests\Unit\MarketData\ImportTradingStatusEventsCommandTest.php
OK (5 tests, 37 assertions)

vendor\bin\phpunit tests\Unit\MarketData\MarketDataSqliteSchemaSyncTest.php
OK (5 tests, 301 assertions)

vendor\bin\phpunit tests\Unit\MarketData
OK (651 tests, 9627 assertions)
```

## Long-suspension source import proof

Command executed locally:

```powershell
php artisan market-data:events:import-trading-status storage/app/market_data/events/idx_suspension_gt_6m.csv --apply -vvv
```

Result:

```text
status=APPLIED
reason_code=COMMAND_APPLY_CONFIRMED
input_file=storage/app/market_data/events/idx_suspension_gt_6m.csv
source_name=manual_trading_status_csv
operation_mode=APPLY
row_count=59
valid_row_count=59
upserted_count=59
error_count=0
status_codes=LONG_SUSPENSION_GT_6M
```

Database verification:

```text
status_code=LONG_SUSPENSION_GT_6M
source_name=idx_suspension_gt_6m
source_ref=20260630-pengumuman-potensi-delisting-juni-2026.xlsx
total=59
```

## 2026-06-29 final lifecycle proof

Command executed locally:

```powershell
php artisan market-data:backfill:lifecycle 2026-06-29 2026-06-29 `
  --source_mode=api `
  --with-evidence `
  --with-replay `
  -vvv
```

Final result:

```text
run_id=37961
publication_id=38228
ticker_count=887
tickers=872/887
coverage=PASS
promote=SUCCESS / PROMOTED
evidence=EXPORTED
fixture=GENERATED
replay=VERIFIED
readable=YES
final_reason_code=RUN_SOURCE_PARTIAL_RESPONSE
```

Coverage proof from `run_summary.json`:

```text
coverage_expected_count=887
coverage_available_count=872
coverage_missing_count=15
coverage_ratio=0.983089
coverage_min_threshold=0.98
coverage_gate_state=PASS
coverage_reason_code=COVERAGE_THRESHOLD_MET
publishability_state=READABLE
```

Publication-specific debug proof:

```text
trade_date=2026-06-29
publication_id=38228
base_universe=956
suspended=69
coverage_universe=887
candidate_bars=872
available_in_universe=872
missing=15
```

Residual non-blocking missing requested-date rows:

```text
COWL
DUCK
ENVY
GOLL
LCGP
LMAS
MABA
MTRA
OCAP
PLAS
SCPI
SRIL
SUGI
TDPM
TRIL
```


<!-- LEGACY_EXTRACT_BODY_END -->
