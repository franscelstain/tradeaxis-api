# Session Snapshot Date Alignment with Effective Trade Date (LOCKED)

## Locked rules
- `session_snapshots.trade_date` must equal the resolved `trade_date_effective` D, not the wall-clock calendar date of capture.
- `captured_at` stores the actual capture timestamp.
- V2 target primary/unique identity is `(trade_date, snapshot_slot, listing_id)` so all snapshot data is aligned to the same stable point-in-time listing identity used by consumer readers. A legacy `(trade_date, snapshot_slot, ticker_id)` key is transitional compatibility only and must be migrated; no new snapshot surface may key only on `ticker_id`.

## Interpretation example (LOCKED)
If the wall-clock capture occurs on calendar date C but consumer fallback resolves `trade_date_effective = D` where `D < C`, then:
- `trade_date = D`
- `captured_at` remains on calendar date C

This is intentional.
The snapshot is indexed by the same effective dataset date that consumers are allowed to read, while the actual capture moment remains visible in `captured_at`.