# CORRECTION

## Commands
- market-data:correction:request
- market-data:correction:approve
- market-data:correction:run

## 2026-05-20 hardening

- `market-data:correction:request` validates `trade_date` and `reason_code`, then resolves the current sealed readable coverage-PASS baseline through the correction baseline resolver before a request row is created.
- Missing baseline blocks the request with `CORRECTION_BASELINE_LINK_MISSING`; it must not create a correction id.
- `market-data:correction:run` requires APPROVED/EXECUTING/RESEALED status and renders baseline, candidate, reseal, unchanged, and final outcome context.
- Unchanged corrections render `correction_outcome=UNCHANGED`, `correction_reseal_status=NOT_RESEALED_UNCHANGED`, and `candidate_publication_switch=false`.
- Current-publication repair is adjacent correction safety surface: `market-data:current-publication:repair --apply` requires `--reason` or `--force_reason` and records pointer before/after.
