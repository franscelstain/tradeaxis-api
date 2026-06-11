# WS C02 Operator Validation Commands

## Status

```text
OPERATOR_VALIDATION_REQUIRED
```

These commands must be run by an operator in the supported project environment. The authoring sandbox cannot claim these as PASS because PHPUnit is blocked by missing PHP extensions and Artisan is blocked by the project PHP-version guard.

Authoring sandbox blockers:

```text
php vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistBacktestC02" = BLOCKED / exit code 1 / missing dom, mbstring, xml, xmlwriter
php artisan list = BLOCKED / exit code 2 / ENV_UNSUPPORTED_PHP_VERSION / current PHP 8.4.16
```

## 1. C02 unit/static tests

Command:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC02"
```

Expected output markers:

```text
OK (
```

Pass criteria:

- output contains `OK (`;
- output has no `FAILURES!`, `ERRORS!`, or failed assertions;
- exit code is `0`.

Fail criteria:

- any failed assertion, error, warning that fails the suite, or non-zero exit code.

Expected exit code:

```text
0
```

This result must not be claimed PASS until operator output is provided.

## 2. Full Watchlist unit tests

Command:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected output markers:

```text
OK (
```

Pass criteria:

- output contains `OK (`;
- all Watchlist unit/static tests pass;
- exit code is `0`.

Fail criteria:

- any failed assertion, error, warning that fails the suite, or non-zero exit code.

Expected exit code:

```text
0
```

This result must not be claimed PASS until operator output is provided.

## 3. Seed C02 catalog

Command:

```powershell
php artisan watchlist:backtest-c02-param-grid-seed
```

Expected output markers:

```text
status=PASS
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06
catalog_version=C02
catalog_count=8
catalog_hash=7287c438e15bd03d6beb4796e4d5159ecd8ed59a
r1_immutable=1
r2_immutable=1
c01_immutable=1
oos_executed=0
production_ready=0
```

Pass criteria:

- all expected markers above appear;
- `reason_code` is absent or empty;
- exit code is `0`.

Fail criteria:

- `status=BLOCKED`;
- catalog hash/count mismatch;
- missing immutable marker;
- non-zero exit code.

Expected exit code:

```text
0
```

This result must not be claimed PASS until operator output is provided.

## 4. Rerun C02 seed idempotency

Command:

```powershell
php artisan watchlist:backtest-c02-param-grid-seed
```

Expected output markers:

```text
status=PASS
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06
catalog_hash=7287c438e15bd03d6beb4796e4d5159ecd8ed59a
inserted_count=0
existing_count=8
r1_immutable=1
r2_immutable=1
c01_immutable=1
production_ready=0
```

Pass criteria:

- rerun is idempotent;
- no row is updated;
- no historical catalog snapshot changes;
- exit code is `0`.

Fail criteria:

- `status=BLOCKED`;
- `updated_count` is not `0`;
- catalog hash/count mismatch;
- non-zero exit code.

Expected exit code:

```text
0
```

This result must not be claimed PASS until operator output is provided.

## 5. Run C02 IS calibration twice

Command run 1:

```powershell
php artisan watchlist:backtest-is-calibrate --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06 --from=2023-01-02 --to=2025-05-21 --output=storage/app/watchlist/backtest/c02-is-run-1.json --overwrite
```

Command run 2:

```powershell
php artisan watchlist:backtest-is-calibrate --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06 --from=2023-01-02 --to=2025-05-21 --output=storage/app/watchlist/backtest/c02-is-run-2.json --overwrite
```

Expected output markers for each run:

```text
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06
catalog_version=C02
catalog_count=8
catalog_hash=7287c438e15bd03d6beb4796e4d5159ecd8ed59a
is_from=2023-01-02
is_to=2025-05-21
max_allowed_market_data_date=2025-05-21
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
production_ready=0
artifact_hash=
artifact_path=
```

Expected status behavior:

- If C02 produces at least one valid IS param: `status=PASS`, `is_valid_param_count > 0`, exit code `0`.
- If C02 still fails canonical IS gates: `status=C02_GRID_FAILED_IS_QUALITY`, `is_valid_param_count=0`, exit code `1` is acceptable as a quality-failed calibration artifact, not as production readiness.

Pass criteria for infrastructure validation:

- not `status=BLOCKED`;
- artifact path is written;
- expected markers appear;
- OOS markers remain `0`/unchanged;
- every evaluated row is reason-coded or valid;
- exit code matches the status behavior above.

Fail criteria:

- `status=BLOCKED`;
- missing artifact;
- OOS service/repository/table mutation marker is not clean;
- boundary marker fails;
- catalog hash/count mismatch.

This result must not be claimed PASS until operator output is provided.

## 6. Compare C02 two-run artifact hashes

Command:

```powershell
php -r '$a=json_decode(file_get_contents("storage/app/watchlist/backtest/c02-is-run-1.json"), true); $b=json_decode(file_get_contents("storage/app/watchlist/backtest/c02-is-run-2.json"), true); echo "artifact_hash_run_1=".($a["meta"]["artifact_hash"] ?? "").PHP_EOL; echo "artifact_hash_run_2=".($b["meta"]["artifact_hash"] ?? "").PHP_EOL; echo "hash_equal=".(($a["meta"]["artifact_hash"] ?? null) === ($b["meta"]["artifact_hash"] ?? null) ? "1" : "0").PHP_EOL;'
```

Expected output markers:

```text
artifact_hash_run_1=<non-empty sha1>
artifact_hash_run_2=<same sha1>
hash_equal=1
```

Pass criteria:

- both artifact hashes are non-empty;
- `hash_equal=1`;
- command exit code is `0`.

Fail criteria:

- missing hash;
- `hash_equal=0`;
- JSON read failure;
- non-zero exit code.

Expected exit code:

```text
0
```

This result must not be claimed PASS until operator output is provided.

## OOS boundary

Do not run OOS for C02 until C02 IS calibration has a valid frozen binding and the separate OOS proof session is explicitly opened.
