# Legacy Semantic Extract — LX-MD-0030-FND-02

- Source ID: `LS-MD-0030`
- Original path: `audit/LUMEN_CONTRACT_TRACKER.md`
- Original SHA1: `88A4CD4C9D12A578A2837DCB275B315C91D1492A`
- Extract role: `FINDING`
- Source range: `L4006-L4042`
- Extract body SHA1: `B552346E597A5FAEB85B1F00E7975142A97BE902`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-25 - API BACKFILL SOURCE ACQUISITION FAILURE CLASSIFICATION + DIAGNOSTIC OUTPUT CONTRACT UPDATE

[CONTRACT_STATUS]
- `PATCHED_PENDING_OPERATOR_RUNTIME_VALIDATION`.

[CONTRACT_UPDATE]
- API backfill lifecycle source acquisition failures must preserve domain source reason codes from adapter/service/orchestrator to command output.
- `COMMAND_EXECUTION_FAILED` is valid only when no domain reason code is available and the failure is truly command infrastructure related.
- HTTP 400 must be classified as source-domain failure (`RUN_SOURCE_BAD_REQUEST`, `RUN_SOURCE_INVALID_SYMBOL`, or `RUN_SOURCE_PROVIDER_REJECTED_RANGE`) with diagnostic context.
- Blocked source acquisition must return `stage=SOURCE_ACQUISITION`, source acquisition state, domain reason code, failed ticker/window sample, HTTP status, and diagnostic artifact path.
- Ticker-scoped provider failures may be partial acquisition failures; coverage gate remains the owner of final READABLE/NOT_READABLE decision.
- Systemic failures remain hard-blocking and must not create fake readable publication or replay fixture.

[CHECKPOINT_RESUME_CONTRACT]
- API range acquisition must emit window/ticker checkpoints with `source_acquisition_batch_id`, source mode, window start/end, ticker code, state, attempt count, reason code, HTTP status, error sample, rows count, and timestamps.
- `--resume` may skip SUCCESS window/ticker checkpoints.
- `--resume --only-failed` must retry only FAILED/RETRYING source acquisition checkpoints.
- If `--only-failed` has no failed source acquisition checkpoint, command must output `NO_FAILED_SOURCE_ACQUISITION_CHECKPOINT` rather than silently processing all requests.
- Resume must not intentionally refetch window/ticker checkpoints already marked SUCCESS.

[DIAGNOSTIC_ARTIFACT_CONTRACT]
- `source_acquisition_diagnostics.json` is the audit artifact for source acquisition success/partial/blocked state.
- Diagnostic output must include source mode, acquisition mode, requested/warmup range, window/ticker counts, estimated request count, acquisition state, failed ticker/window counts, failure sample, reason code, and timestamp.
- Diagnostic URL fields must be sanitized; token-like query parameters must be redacted.

[REASON_CODE_SYNC]
- Added and synchronized reason-code registry/seed entries: `RUN_SOURCE_BAD_REQUEST`, `RUN_SOURCE_INVALID_SYMBOL`, `RUN_SOURCE_PROVIDER_REJECTED_RANGE`, and `NO_FAILED_SOURCE_ACQUISITION_CHECKPOINT`.

[GUARDS]
- Behavioral tests cover HTTP 400 classification, partial ticker continuation, checkpoint creation, and resume-only-failed ticker selection.
- Static guards cover diagnostic/checkpoint artifacts, source-domain reason preservation, and no fallback reintroduction.

[VALIDATION_REQUIRED]
- Local operator must rerun targeted MarketData PHPUnit filters, full `tests/Unit/MarketData`, lifecycle plan, lifecycle full run, resume-only-failed, and optional diagnose-source command before marking this contract DONE.

---


<!-- LEGACY_EXTRACT_BODY_END -->
