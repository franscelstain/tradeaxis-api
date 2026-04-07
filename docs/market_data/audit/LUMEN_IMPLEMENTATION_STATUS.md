# LUMEN_IMPLEMENTATION_STATUS.md

## SESSION UPDATE

- Batch: Failure-Side Source Context Propagation
- Status: PARTIAL (regression fix applied, waiting local proof)

### What was implemented
- `MarketDataPipelineService` persists minimum source-context notes before `failStage()` for source-acquisition failures, so failed runs do not lose operator-facing source telemetry.
- Failure-side notes include `source_name`, `source_attempt_count`, optional `source_final_http_status`, explicit `source_final_reason_code`, and manual `source_input_file` when applicable.
- Regression fix applied: failure-side `source_name` for API mode now stays on the logical contract name (`API_FREE`) instead of drifting to provider/default-source naming.
- Regression fix applied: failed command rendering test now mocks `MarketDataPipelineService` directly, so command-surface coverage no longer leaks the real source-acquisition exception path.
- `AbstractMarketDataCommand`, `MarketDataBackfillService`, and `MarketDataEvidenceExportService` surface `final_reason_code` inside `source_summary` / `source_context` when the run notes contain it.
- Tests cover failed-run notes propagation, evidence export summary, backfill summary, and command rendering for failure-side source telemetry.

### Evidence available from ZIP
- Syntax check passed:
  - `php -l app/Application/MarketData/Services/MarketDataPipelineService.php`
  - `php -l app/Console/Commands/MarketData/AbstractMarketDataCommand.php`
  - `php -l app/Application/MarketData/Services/MarketDataEvidenceExportService.php`
  - `php -l app/Application/MarketData/Services/MarketDataBackfillService.php`
  - `php -l tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`
  - `php -l tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php`
  - `php -l tests/Unit/MarketData/MarketDataBackfillServiceTest.php`
  - `php -l tests/Unit/MarketData/OpsCommandSurfaceTest.php`

### Evidence still waiting for local manual run
- PHPUnit focused verification for:
  - `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`
  - `tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php`
  - `tests/Unit/MarketData/MarketDataBackfillServiceTest.php`
  - `tests/Unit/MarketData/OpsCommandSurfaceTest.php`
- Full PHPUnit regression run.

### Remaining gap after this batch
- This batch only closes the narrow failure-side telemetry propagation gap.
- The broader external-source operational resilience family is still not fully closed at project level because live/runtime operator proof and broader operational readiness remain outside ZIP-only verification.

### Final State
- PARTIAL

- Latest regression fix tightens pipeline source telemetry so API failure-side notes/events always emit logical `API_FREE`, not provider default labels. Validation still depends on local PHPUnit.
