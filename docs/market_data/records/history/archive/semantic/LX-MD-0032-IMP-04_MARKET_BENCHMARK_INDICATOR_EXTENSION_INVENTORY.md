# Legacy Semantic Extract — LX-MD-0032-IMP-04

- Source ID: `LS-MD-0032`
- Original path: `audit/MARKET_BENCHMARK_INDICATOR_EXTENSION_INVENTORY.md`
- Original SHA1: `D0BE40DBBA4BFF89D35ED251D64531E8EF56FC39`
- Extract role: `IMPLEMENTATION`
- Source range: `L217-L245`
- Extract body SHA1: `987C4A88FF390CB016D81F0103637FCD65667A5A`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-06-10 Addendum - Backfill Lifecycle Benchmark Non-Blocking Recovery

Status: `RESOLVED_RUNTIME_PROVEN`.

Observed defect:
- `market-data:backfill:lifecycle 2026-06-09 2026-06-09 --source_mode=api --with-evidence --with-replay -vvv` initially ended `HELD / RUN_SOURCE_NO_VALID_DATA` even though source acquisition had 948 equity rows for the requested date.
- `MarketDataPipelineService` attempted benchmark ingest before equity ingest. A benchmark target-date miss therefore terminated the pipeline before `EodBarsIngestService` could consume available equity rows.
- The fallback effective date `2026-06-08` was correct fail-safe behavior for those held runs; the defect was the premature benchmark blocker, not the fallback mechanism.

Final implementation rule:
- Equity bars are ingested first.
- Benchmark ingest occurs after equity ingest.
- Benchmark-source unavailability is non-blocking for the equity lifecycle.
- Benchmark-dependent outputs may be `NULL` when benchmark input is unavailable; fake values remain forbidden.
- Coverage, promotion, evidence, and replay continue to enforce the normal equity publication contracts.

Operator runtime proof after the fix:
- Run: `run_id=37919`, requested/effective date `2026-06-09`.
- Equity ingest: 948 accepted, 0 rejected, 0 invalid.
- Lifecycle: `import=SUCCESS`, `coverage=PASS`, `promote=SUCCESS`, `evidence=EXPORTED`, `fixture=GENERATED`, `replay=VERIFIED`, `readable=YES`.
- Derived data: 948 `eod_indicators` rows and 948 `eod_eligibility` rows for the target date.
- Benchmark indicator event: 12 benchmark-indicator rows written, zero invalid benchmark indicators.
- Current pointer: `publication_id=38186`, `run_id=37919`, `publication_version=1`, sealed at `2026-06-10 21:07:07`.
- Regression: `vendor\bin\phpunit tests\Unit\MarketData` -> OK (641 tests, 9554 assertions).

Decision:
- Benchmark unavailability cannot hold an otherwise publishable equity run before equity ingestion.
- The 2026-06-09 lifecycle and publication pointer are runtime-proven.
- No additional manual validation remains for this defect.

<!-- LEGACY_EXTRACT_BODY_END -->
