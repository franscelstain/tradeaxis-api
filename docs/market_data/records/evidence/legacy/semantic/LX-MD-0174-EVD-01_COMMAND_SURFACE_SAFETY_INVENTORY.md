# Legacy Semantic Extract — LX-MD-0174-EVD-01

- Source ID: `LS-MD-0174`
- Original path: `ops/COMMAND_SURFACE_SAFETY_INVENTORY.md`
- Original SHA1: `4A1D5DF36286F6499A44A9A6E49E45976F3253D1`
- Extract role: `EVIDENCE`
- Source range: `L64-L104`
- Extract body SHA1: `1DDE335FC69D522B0CE17FC9C2FEC07BF7AAD06A`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Validation

- 2026-05-07 operator-local PASS: `CommandSurfaceSafetyStaticGuardTest.php` -> OK (5 tests, 81 assertions).
- 2026-05-07 operator-local PASS: `SessionSnapshotServiceTest.php` -> OK (6 tests, 38 assertions).
- 2026-05-07 operator-local PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "CommandSurface"` -> OK (47 tests, 348 assertions).
- 2026-05-07 operator-local PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "DryRun"` -> OK (2 tests, 15 assertions).
- 2026-05-07 operator-local PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Apply"` -> OK (4 tests, 26 assertions).
- 2026-05-07 operator-local PASS: full `vendor/bin/phpunit tests/Unit/MarketData` -> OK (312 tests, 3899 assertions).
- 2026-05-20 local PASS: `CommandSurfaceSafetyStaticGuardTest.php` -> OK (5 tests, 89 assertions); `OpsCommandSurfaceTest.php --filter "current_publication_repair"` -> OK (2 tests, 12 assertions); repair apply without reason blocked with `COMMAND_DESTRUCTIVE_GUARD_REQUIRED`.
- 2026-05-20 ops runtime matrix LOCKED: all 20 historical public market-data commands were registered and help-rendered with exit 0; invalid/missing input proof was command-owned and reason-coded; seeded runtime proof passed for finalize re-run, run/replay/correction evidence export, replay fixture generation, replay verify/smoke/backfill, repair dry-run/apply guard, purge dry-run/apply-zero, no-readable snapshot block, correction lifecycle blocks, and promote force guard; production-ready fixture proof passed for fresh daily/backfill/promote/stage success, real lock conflict, held/not-readable, failed source, repair apply invalid pointer, session snapshot success, evidence export, and replay verify/smoke/backfill. Full matrix is recorded in `docs/market_data/audit/OPS_COMMAND_SURFACE_RUNTIME_MATRIX_INVENTORY.md`.
- 2026-05-23 final reconciliation: current public market-data command surface was 21 commands after `market-data:provider:smoke`; provider-smoke safe-mode surface is enforced, `--json` emits JSON stdout, `--provider` overrides `market_data.source.api.provider`, and the final embedded live provider runtime proof is `provider_smoke_status=PASS` / `reason_code=PROVIDER_SMOKE_OK` / `http_status=200` with all non-destructive safety flags false.
- 2026-06-03 command surface extension: current public market-data command surface is 26 commands after including the already-public `market-data:backfill:lifecycle`, adding `market-data:evidence-replay:full-range-current` for proof-only full-range current publication evidence/replay, adding `market-data:sectors:import-memberships` for dry-run/apply guarded sector membership import, adding `market-data:sector-indexes:import-bars` for dry-run/apply guarded sector index bar import, and adding `market-data:sector-indexes:ingest-api` for dry-run/apply guarded sector index API import. The evidence/replay command does not fetch API data, import OHLC, promote, finalize, or switch current publication pointers; it exports run evidence, generates a runtime fixture per current publication, verifies replay, exports replay evidence, and writes a summary artifact. Historical runtime fact: the sector commands upserted source/membership or benchmark rows after validation/apply. **This behavior is superseded as a V2 target and is not admitted as immutability proof.**
- 2026-06-04 event-risk source context extension: current public market-data command surface is 28 commands after adding `market-data:events:import-corporate-actions` and `market-data:events:import-trading-status`. Historical runtime fact: these commands upserted source context rows after CSV validation/apply; **that overwrite semantics is superseded as a V2 target**; they do not import OHLC, recompute indicators, promote, finalize, or switch current publication pointers.
- 2026-06-04 missing-ticker lifecycle extension: public market-data command surface reached 29 commands after adding `market-data:backfill:missing-tickers`. The command scans ticker-master/current-bar gaps, supports `--ticker_codes`, uses API range acquisition for missing tickers only, and then runs the normal lifecycle so evidence/replay can prove the resulting current publication.
- 2026-06-06 current indicator recompute extension: current public market-data command surface is 30 commands after adding `market-data:eod-indicators:recompute-current`. The command recomputes publication-bound indicators from existing current readable bars through correction-current lifecycle without source acquisition, bar ingest, source/master writes, or `eod_bars` writes.
- 2026-05-20 post-ledger validation PASS: `OpsCommandSurfaceTest.php` OK (57 tests, 341 assertions), `CommandSurfaceSafetyStaticGuardTest.php` OK (5 tests, 89 assertions), `OpsCommandSurfaceRuntimeMatrixStaticGuardTest.php` OK (6 tests, 114 assertions), `Command` filter OK (97 tests, 1009 assertions), `Ops` filter OK (74 tests, 616 assertions), `StaticGuard` filter OK (176 tests, 4124 assertions), and full `tests/Unit/MarketData` OK (475 tests, 6942 assertions).
- 2026-07-31 price-continuity extension: current public market-data command surface is 33 commands after adding `market-data:detect-price-scale-breaks`, `market-data:repair-price-scale-stretches`, and `market-data:events:derive-corporate-actions`. Every count recorded above this line is the surface as it stood on that date and is left unedited; entries stating a "current" surface of 20 through 30 describe earlier states, not this one.
- 2026-07-31 gap closed: those three commands were registered in the console kernel and absent from the table above for the whole of their existence, because `CommandSurfaceSafetyStaticGuardTest` walked a hand-written roster of thirty command names rather than the kernel. A command missing from a roster is a command the roster cannot report. The check is now derived from the kernel, so an unlisted command fails the suite instead of passing it. Command counts stated in prose are therefore no longer the control: the derived check is.

## 2026-05-23 — Final Provider Smoke / Full PHPUnit PASS Document Reconciliation

[SESSION] FINAL_PROVIDER_SMOKE_FULL_PHPUNIT_DOC_SYNC

[SESSION_STATUS] OPS_RUNTIME_PARITY_PASSED

[FINAL_DECISION]
- Current source ZIP is documented as `OPS_RUNTIME_PARITY_PASSED`.
- Final provider smoke is `FINAL_PROVIDER_SMOKE=PASSED` and `LIVE_PROVIDER_SMOKE_PASSED`.
- Authoritative provider-smoke artifact: `storage/app/market-data/provider-smoke-safe-mode/command-output/provider-smoke-bbca.txt`.
- Provider smoke proof: `provider_smoke_status=PASS`, `reason_code=PROVIDER_SMOKE_OK`, `source_reason_code=none`, `http_status=200`, `returned_row_count=1`, `attempt_count=1`, `retry_max=0`, `retry_exhausted=false`.
- Safety proof: `publication_created=false`, `seal_executed=false`, `finalize_executed=false`, `pointer_switched=false`, `readable_publication_created=false`, `full_universe_fetch=false`.
- Scheduler due-run runtime proof remains present and no silent scheduler failure is claimed.
- Final targeted validation passed: `OpsCommandSurfaceRuntimeMatrixStaticGuardTest` -> OK (6 tests, 120 assertions).
- Final full validation passed: `vendor\bin\phpunit tests/Unit/MarketData` -> OK (492 tests, 7590 assertions), Time 00:36.861, Memory 40.00 MB.

[RECONCILIATION]
- Earlier wording that described provider smoke as provider-rate-limited, provider-blocked, or waiting for full MarketData PHPUnit is superseded for the current source ZIP.
- Future Yahoo/PublicApi rate limit, timeout, network, parse, empty-response, or missing-date outcomes remain valid reason-coded BLOCKED outcomes, but they are not the current final proof state.




<!-- LEGACY_EXTRACT_BODY_END -->
