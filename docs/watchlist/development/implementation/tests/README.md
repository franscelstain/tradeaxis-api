# Watchlist Implementation — Tests

Fixtures, contract tests, and verification inputs. Test artifacts are not strategy owners.

Build order tetap mengikuti [`../WS_IMPLEMENTATION_BUILD_SEQUENCE.md`](../WS_IMPLEMENTATION_BUILD_SEQUENCE.md).

## Work Baseline / Attempt Integrity

- Work Baseline Lock must be issued before material implementation change.
- Use `../examples/WS_STAGE_ATTEMPT_RECORD_TEMPLATE.md` for final attempt evidence.
- Run `../tests/WatchlistDocumentationIntegrityGate.php` before attempt/stage/package closure.

## Governance Executable Gates

- `WatchlistDocumentationIntegrityGate.php` — structural/document/baseline integrity.
- `WatchlistRelationshipIntegrityGate.php` — current Work/Stage/Attempt/Dependency/record relationship integrity.
- `GenerateWatchlistCurrentState.php` — regenerates current human-readable project state from canonical indexes.
- `RegisterWorkRecord.php` — appends a validated current/future WS-Bxx record into Work Record Registry.
