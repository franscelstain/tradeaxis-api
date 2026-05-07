# SESSION SNAPSHOT

## Commands

- `market-data:session-snapshot`
- `market-data:session-snapshot:purge`

## Capture policy

`market-data:session-snapshot` is state-changing because it replaces rows for a specific `(trade_date, snapshot_slot)` scope. It is allowed without a separate `--apply` flag because the mutation is slot-scoped, deterministic, and still guarded by the readable-current-publication contract inside `SessionSnapshotService`. The command must render `trade_date`, `trade_date_effective`, `publication_id`, `run_id`, capture counts, skipped counts, slot tolerance, and output artifact path.

## Purge policy

`market-data:session-snapshot:purge` is destructive because it deletes historical snapshot rows. It must be non-mutating by default.

Required behavior:

- default execution is `DRY_RUN`
- default execution emits `reason_code=COMMAND_DRY_RUN_ONLY`
- default execution counts `candidate_rows` and keeps `deleted_rows=0`
- actual delete requires explicit `--apply`
- applied execution emits `operation_mode=APPLIED`
- applied execution emits `reason_code=COMMAND_APPLY_CONFIRMED`
- applied execution renders `candidate_rows`, `deleted_rows`, cutoff context, and output artifact path

Example dry-run:

```bash
php artisan market-data:session-snapshot:purge --before_date=2026-03-01
```

Expected operator context:

```text
operation_mode=DRY_RUN
reason_code=COMMAND_DRY_RUN_ONLY
cutoff_timestamp=2026-03-01 23:59:59
cutoff_source=explicit_before_date
before_date=2026-03-01
candidate_rows=<count>
deleted_rows=0
next_action=Re-run with --apply after reviewing candidate_rows and cutoff context.
```

Example apply:

```bash
php artisan market-data:session-snapshot:purge --before_date=2026-03-01 --apply
```

Expected operator context:

```text
operation_mode=APPLIED
reason_code=COMMAND_APPLY_CONFIRMED
candidate_rows=<count-before-delete>
deleted_rows=<deleted-count>
```
