# SESSION SNAPSHOT

## Commands

- `market-data:session-snapshot`
- `market-data:session-snapshot:purge`

## Capture policy

`market-data:session-snapshot` is state-changing because it materializes the projection for a specific `(trade_date, snapshot_slot, listing_id)` scope. A retry may replace the **slot projection** only if the underlying acquisition/source observations remain append-only and auditable; it must never mutate the EOD canonical dataset or discard observation lineage. `ticker_id` is compatibility/display only.

It is allowed without a separate `--apply` flag because the projection mutation is slot-scoped, deterministic, and guarded by a specific readable publication/config context. The command must render `trade_date`, `trade_date_effective`, `publication_id`/scope publication, config snapshot/hash, `run_id`, capture counts, skipped counts, slot tolerance, and output artifact path.

`trade_date` and `snapshot_slot` are required by the operator contract. Parser-level optional arguments are allowed only so missing input returns `status=BLOCKED` with `reason_code=COMMAND_MISSING_REQUIRED_INPUT`; a missing slot must not fall through to a framework missing-argument error.

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
