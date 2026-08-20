# Legacy Semantic Extract — LX-MD-0040-CTX-02

- Source ID: `LS-MD-0040`
- Original path: `audit/READ_SIDE_CONSUMER_SURFACE_FINAL_SWEEP_INVENTORY.md`
- Original SHA1: `89C4B1A3A221C2A0F9B52AA735566CF5C0F8B107`
- Extract role: `CONTEXT`
- Source range: `L43-L96`
- Extract body SHA1: `0D5628140F622B116F317A9A6FD358F50327B10D`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Candidate surface baseline

| Candidate Surface | File/Path | Why Candidate | Initial Classification | Needs Trace? |
|---|---|---|---|---|
| Root route | `routes/web.php` | HTTP entrypoint check. | `NOT_MARKET_DATA` | No; returns app version only. |
| Example controller | `app/Http/Controllers/ExampleController.php` | Controller surface check. | `NOT_MARKET_DATA` | No; no market-data logic. |
| Session snapshot command | `app/Console/Commands/MarketData/CaptureSessionSnapshotCommand.php` | Operator command exposes publication/run/scope summary. | `READ_SIDE_CONSUMER` | Yes. |
| Session snapshot service | `app/Application/MarketData/Services/SessionSnapshotService.php` | Reads current publication context before capturing snapshot scope. | `READ_SIDE_CONSUMER` | Yes. |
| Eligibility snapshot scope | `app/Infrastructure/Persistence/MarketData/EligibilitySnapshotScopeRepository.php` | Reads eligibility scope for snapshot. | `READ_SIDE_CONSUMER` | Yes. |
| Evidence export command/service/repository | `app/Console/Commands/MarketData/ExportEvidenceCommand.php`, `MarketDataEvidenceExportService.php`, `EodEvidenceRepository.php` | Exposes proof package, not a normal API/dashboard consumer. | `EVIDENCE_REPLAY_AUDIT` | Yes, to prevent latest fallback. |
| Replay verify/smoke/backfill | `ReplayVerificationService.php`, `ReplayBackfillService.php`, replay commands | Compares expected proof and historical proof. | `EVIDENCE_REPLAY_AUDIT` | Yes, to ensure explicit selector/pointer context. |
| Repair current publication command | `RepairCurrentPublicationIntegrityCommand.php` | Reads pointer/current state for repair diagnostics. | `ADMIN_REPAIR_DIAGNOSTIC` | Yes, classification only. |
| Pipeline/import/build/hash/seal/finalize | `EodBarsIngestService.php`, `EodIndicatorsComputeService.php`, `EodEligibilityBuildService.php`, `MarketDataPipelineService.php`, `EodArtifactRepository.php` | Producer/write-side lifecycle uses live/history tables. | `WRITE_SIDE_PRODUCER` | Yes, classification only. |
| Tests/static guards | `tests/Unit/MarketData/**`, `tests/Support/**` | Fixtures and enforcement checks contain forbidden strings by design. | `TEST_ONLY` | No runtime consumer patch. |
| Docs/book/audit/examples | `docs/market_data/**` | Contract/audit evidence and examples. | `DOCS_ONLY` | No runtime consumer patch. |

## Consumer Surface Matrix

| Consumer Surface | Entrypoint | Classification | Pointer Resolver | Effective Date Source | Direct Table Risk | Proof | Status |
|---|---|---|---|---|---|---|---|
| Session snapshot capture | `market-data:session-snapshot` -> `CaptureSessionSnapshotCommand` -> `SessionSnapshotService::capture` | `READ_SIDE_CONSUMER` | `EodPublicationRepository::findCurrentPublicationForTradeDate`, which delegates to `resolveCurrentReadablePublicationForTradeDate` | Resolved publication row (`$publication->trade_date`) and returned `trade_date_effective` | No raw/latest shortcut in service; scope repository reads eligibility through pointer/current/readable joins. | Trace confirms controlled exception when no readable current publication exists. | `CONFIRMED_POINTER_RESOLVED` |
| Session snapshot eligibility scope | `SessionSnapshotService::capture` -> `EligibilitySnapshotScopeRepository::getScopeForTradeDate` | `READ_SIDE_CONSUMER` | Direct pointer-scoped join on `eod_current_publication_pointer`, `eod_publications`, `eod_runs` | `ptr.trade_date` / `pub.trade_date` / `elig.trade_date` equality | Direct `eod_eligibility` read is allowed only because it is pointer-scoped and validates sealed/readable/PASS/current mirror. | Query enforces `pub.is_current = 1`, `pub.seal_state = SEALED`, `run.terminal_status = SUCCESS`, `run.publishability_state = READABLE`, `run.coverage_gate_state = PASS`, `run.is_current_publication = 1`, and mirror columns. | `CONFIRMED_POINTER_RESOLVED` |
| Evidence run publication lookup | `market-data:evidence:export --run_id` -> `MarketDataEvidenceExportService` -> `EodEvidenceRepository::findPublicationForRun` | `EVIDENCE_REPLAY_AUDIT` | Direct pointer-scoped join on pointer/publication/run | Explicit `run_id` and pointer trade date | Not a normal consumer; guarded against latest fallback. | `findPublicationForRun` validates sealed/current/SUCCESS/READABLE/PASS/current mirror and uses no `orderByDesc(trade_date)` or MAX/latest date. | `CONFIRMED_POINTER_RESOLVED` |
| Evidence eligibility export / reason codes | `EodEvidenceRepository::exportEligibilityRows`, `dominantReasonCodes` | `EVIDENCE_REPLAY_AUDIT` | `readableEligibilityQuery` plus `readablePublicationContextExists` | Explicit `tradeDate`, optional explicit `publicationId`, pointer equality | Direct `eod_eligibility` read is allowed only after pointer/current/readable/PASS/mirror validation. | No event reason-code leakage when readable publication context does not exist. | `CONFIRMED_POINTER_RESOLVED` |
| Replay verification | `market-data:replay:verify` -> `ReplayVerificationService` | `EVIDENCE_REPLAY_AUDIT` | `findReadableCurrentPublicationForRun($runId, $tradeDate)` | Explicit run and trade date from replay context | Not a normal consumer; comparison proof path. | No latest-row replay metric lookup without explicit `trade_date`; command requires explicit fixture path. | `CONFIRMED_POINTER_RESOLVED` |
| Replay backfill | `ReplayBackfillService` | `EVIDENCE_REPLAY_AUDIT` | `findCurrentPublicationForTradeDate($tradeDate)` | Explicit backfill date | Not API/dashboard consumer; evidence generation path. | Uses current readable publication resolver and returns blocked/no-readable state when absent. | `CONFIRMED_POINTER_RESOLVED` |
| Current publication repair | `market-data:current-publication:repair` -> `RepairCurrentPublicationIntegrityCommand` | `ADMIN_REPAIR_DIAGNOSTIC` | `findRawCurrentPublicationStateForTradeDate` intentionally reads diagnostic raw pointer/current state | Explicit `--trade_date` | Diagnostic repair surface, not consumer-readable source. | Output is diagnostic/repair state and requires `--apply` for mutation. | `NOT_A_CONSUMER` |
| Daily/import/build/promote/finalize pipeline | market-data producer commands and services | `WRITE_SIDE_PRODUCER` | Producer lifecycle, not consumer resolver | Requested run date / candidate publication context | Direct live/history artifact access expected for ingest/build/hash/seal/finalize. | Classified producer/write-side; do not patch as read-side consumer. | `NOT_A_CONSUMER` |
| HTTP controllers/resources/dashboard/report | `routes/web.php`, `app/Http/Controllers/**`, `resources/views/**` | `NOT_MARKET_DATA` | N/A | N/A | No current market-data HTTP/dashboard/report output exists in this ZIP. | Static trace found only root version route and empty example controller. | `NOT_A_CONSUMER` |

## Raw/Latest Scan Matrix

| File | Pattern | Runtime Path | Consumer Classification | Risk | Action | Status |
|---|---|---|---|---|---|---|
| `app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php` | `eod_current_publication_pointer`, `eod_publications`, `is_current`, `findLatestReadablePublicationBefore`, `findRawCurrentPublicationStateForTradeDate` | Yes | Gateway + internal pipeline/diagnostic helper | Consumer would be risky only if using internal fallback/raw helpers as gateway. | Existing aliases `findCurrentPublicationForTradeDate` and `findPointerResolvedPublicationForTradeDate` delegate to official resolver. Internal fallback remains classified producer/fail-safe only. | `CONFIRMED_POINTER_RESOLVED` / `NOT_A_CONSUMER` for fallback/raw helpers |
| `app/Infrastructure/Persistence/MarketData/EligibilitySnapshotScopeRepository.php` | Direct `eod_eligibility` read | Yes | `READ_SIDE_CONSUMER` | Direct artifact table access would be risky without pointer. | No patch needed; query is pointer-scoped and enforces sealed/SUCCESS/READABLE/PASS/current mirror. | `CONFIRMED_POINTER_RESOLVED` |
| `app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php` | Direct `eod_eligibility`, `eod_run_events`, replay metric tables | Yes | `EVIDENCE_REPLAY_AUDIT` | Latest fallback / invalid context leakage risk. | No patch needed; publication/eligibility/reason-code paths require readable pointer context; replay metric requires explicit `trade_date`. | `CONFIRMED_POINTER_RESOLVED` |
| `app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php` | Direct `eod_bars`, `eod_indicators`, `eod_eligibility`, history tables, `orderBy('trade_date')` | Yes | `WRITE_SIDE_PRODUCER` | Not a consumer; stable ordering could be mistaken for latest shortcut. | No patch; `orderBy('trade_date')` is deterministic ascending artifact ordering, not `latest`/MAX fallback. | `NOT_A_CONSUMER` |
| `app/Application/MarketData/Services/MarketDataPipelineService.php` | `findLatestReadablePublicationBefore`, raw current state helper | Yes | `WRITE_SIDE_PRODUCER` / internal fail-safe | Could be unsafe if exposed to consumer. | Existing contract classifies fallback as internal pipeline preservation only. No API/dashboard consumer uses it. | `NOT_A_CONSUMER` |
| `app/Application/MarketData/Services/MarketDataEvidenceExportService.php` | Raw pointer context fields | Yes | `EVIDENCE_REPLAY_AUDIT` | Could be confused as consumer fallback. | Raw pointer context is labelled evidence/fallback diagnostic context for incomplete proof, not readable data source. | `NOT_A_CONSUMER` |
| `app/Console/Commands/MarketData/ExportEvidenceCommand.php` | `latest` wording in validation message | Yes | `EVIDENCE_REPLAY_AUDIT` | Replay evidence could fallback to latest row if trade date omitted. | Already blocked: replay evidence export requires explicit `--trade_date`. | `CONFIRMED_POINTER_RESOLVED` |
| `routes/web.php`, `app/Http/Controllers/**`, `resources/views/**` | None | Yes | `NOT_MARKET_DATA` | No API/dashboard surface found. | No patch. | `NOT_A_CONSUMER` |

Forbidden latest scan result: no consumer app path uses `MAX(trade_date)`, `max('trade_date')`, `latest('trade_date')`, `orderByDesc('trade_date')`, or `orderBy('trade_date', 'desc')` as a readable-data resolver. The only `orderBy('trade_date')` hit in app runtime is deterministic ascending artifact ordering in `EodArtifactRepository::applyStableArtifactOrder`, classified as producer/write-side support.

## Consumer end-to-end trace summary

| Consumer | Route/Caller | Service | Repository/Gateway | Pointer Resolver Method | Publication ID Source | Effective Trade Date Source | Output Proof | Status |
|---|---|---|---|---|---|---|---|---|
| Session snapshot | `market-data:session-snapshot` | `SessionSnapshotService::capture` | `EodPublicationRepository` + `EligibilitySnapshotScopeRepository` | `findCurrentPublicationForTradeDate` -> `resolveCurrentReadablePublicationForTradeDate` | Pointer-resolved publication row | Publication `trade_date` returned as `trade_date_effective` | Summary includes `publication_id`, `run_id`, `trade_date_effective`; throws if no readable current publication. | `CONFIRMED_POINTER_RESOLVED` |
| Eligibility scope | Session snapshot service caller | N/A | `EligibilitySnapshotScopeRepository` | Pointer/publication/run joins | `elig.publication_id = ptr.publication_id` | `elig.trade_date = ptr.trade_date = pub.trade_date` | Returns only pointer-scoped rows; empty if readable/PASS/mirror invalid. | `CONFIRMED_POINTER_RESOLVED` |
| Evidence run export | `market-data:evidence:export --run_id` | `MarketDataEvidenceExportService` | `EodEvidenceRepository` | `findPublicationForRun` / `findReadableCurrentPublicationForRun` | Pointer-resolved publication row | Run requested/effective date plus pointer trade date | Evidence output is proof package, not consumer shortcut. | `CONFIRMED_POINTER_RESOLVED` |
| Replay verification | `market-data:replay:verify` | `ReplayVerificationService` | `EodPublicationRepository` | `findReadableCurrentPublicationForRun` | Pointer-resolved publication row for explicit run/date | Explicit replay trade date | Replay comparison requires fixture and explicit context. | `CONFIRMED_POINTER_RESOLVED` |


<!-- LEGACY_EXTRACT_BODY_END -->
