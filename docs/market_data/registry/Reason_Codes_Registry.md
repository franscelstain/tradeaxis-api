# Reason Codes Registry (LOCKED)

## Purpose
Define the canonical reason-code vocabulary used by Market Data Platform across:
- `eod_invalid_bars.invalid_reason_code`
- `eod_indicators.invalid_reason_code`
- `eod_eligibility.reason_code`
- `eod_run_events.reason_code`

This registry is intentionally upstream-only. It does not encode watchlist scores, groups, picks, or strategy actions.

## Registry rules (LOCKED)
1. Codes are stable identifiers and must be uppercase snake case.
2. One code has one meaning only.
3. Description text may be clarified over time, but code semantics must not drift silently.
4. Deprecated codes must not be physically reused for a different meaning.
5. Severity is the default registry severity; actual run outcome is still decided by the locked decision table.

## Canonical registry
| code | category | severity | description |
|---|---|---:|---|
| `RUN_COVERAGE_LOW` | RUN | HARD | Coverage ratio for the requested date is below the locked minimum threshold. |
| `RUN_COVERAGE_NOT_EVALUABLE` | RUN | HARD | Coverage could not be evaluated meaningfully for the requested date, so requested-date publication must remain not readable. |
| `COVERAGE_THRESHOLD_MET` | COVERAGE | INFO | Coverage evaluation passed because available canonical EOD bars met or exceeded the locked minimum threshold. |
| `COVERAGE_BELOW_THRESHOLD` | COVERAGE | HARD | Coverage evaluation failed because available canonical EOD bars stayed below the locked minimum threshold. |
| `COVERAGE_UNIVERSE_EMPTY` | COVERAGE | HARD | Coverage could not be evaluated because the resolved coverage universe for the requested date was empty. |
| `RUN_INDICATORS_MISSING` | RUN | HARD | Required indicator artifact or required indicator row set for the requested date is not available. |
| `RUN_ELIGIBILITY_MISSING` | RUN | HARD | Eligibility snapshot for the requested date is not available. |
| `RUN_HASH_MISSING` | RUN | HARD | One or more mandatory content hashes are missing at finalization time. |
| `RUN_HASH_FAILED` | RUN | HARD | Hash computation failed or produced unusable output. |
| `RUN_SEAL_PRECONDITION_FAILED` | RUN | HARD | Seal execution was attempted before all locked preconditions were satisfied. |
| `RUN_SEAL_WRITE_FAILED` | RUN | HARD | Seal metadata could not be written successfully. |
| `RUN_FINALIZE_BEFORE_CUTOFF` | RUN | HARD | Final success was attempted before the cutoff policy allowed it. |
| `RUN_LOCK_CONFLICT` | RUN | HARD | Run-ownership conflict or duplicate writer activity occurred during hash, seal, or finalize stages. |
| `RUN_SOURCE_TIMEOUT` | RUN | WARN | The source timed out and retry policy was already applied or exhausted. |
| `RUN_SOURCE_RATE_LIMIT` | RUN | WARN | The source hit rate limiting and affected data acquisition. |
| `RUN_SOURCE_AUTH_ERROR` | RUN | HARD | Source authentication failure or credential/config error blocked data acquisition. |
| `RUN_SOURCE_RESPONSE_CHANGED` | RUN | HARD | A source schema or response-contract change was detected. |
| `RUN_SOURCE_PARTIAL_COVERAGE` | RUN | WARN | The source returned incomplete symbol coverage for the requested date. |
| `RUN_SOURCE_PARTIAL_RESPONSE` | RUN | WARN | The source adapter returned only part of the requested provider response and coverage gate must decide publishability. |
| `RUN_SOURCE_MANUAL_FILE_NOT_FOUND` | RUN | HARD | The configured manual-file source was not found. |
| `RUN_SOURCE_MANUAL_FILE_NOT_READABLE` | RUN | HARD | The configured manual-file source could not be opened or read. |
| `RUN_SOURCE_MANUAL_FILE_MALFORMED` | RUN | HARD | The configured manual-file source could not be parsed or normalized safely. |
| `RUN_SOURCE_MODE_UNSUPPORTED` | RUN | HARD | The requested source mode is not supported by the selected source adapter. |
| `RUN_SOURCE_MALFORMED_PAYLOAD` | RUN | HARD | The source payload could not be normalized safely. |
| `BAR_DUPLICATE_SOURCE_ROW` | BAR | WARN | More than one source row mapped to the same `(trade_date, ticker_id)`, requiring deterministic winner selection. |
| `BAR_INVALID_OHLC_ORDER` | BAR | HARD | Received OHLC values violated canonical ordering rules. |
| `BAR_NON_POSITIVE_PRICE` | BAR | HARD | Received price value was zero or negative in a field that must be positive. |
| `BAR_NEGATIVE_VOLUME` | BAR | HARD | Received volume value was negative. |
| `BAR_MISSING_REQUIRED_FIELD` | BAR | HARD | One or more mandatory source fields were missing. |
| `BAR_TICKER_MAPPING_MISSING` | BAR | WARN | Source row `ticker_code` could not be resolved deterministically to `ticker_id` via the ticker master. |
| `IND_INSUFFICIENT_HISTORY` | INDICATOR | WARN | Required trading-day history is not yet sufficient for deterministic indicator computation. |
| `IND_MISSING_DEPENDENCY_BAR` | INDICATOR | HARD | A required canonical bar in the trading-day dependency chain is missing. |
| `IND_INVALID_BAR_INPUT` | INDICATOR | HARD | A canonical bar input required for indicator computation is invalid. |
| `IND_COMPUTE_ERROR` | INDICATOR | HARD | Indicator computation failed because of logic or runtime error. |
| `ELIG_MISSING_BAR` | ELIGIBILITY | WARN | A ticker in the coverage universe does not have a canonical valid bar for the requested date. |
| `ELIG_MISSING_INDICATORS` | ELIGIBILITY | HARD | Eligibility cannot be determined because required indicators are unavailable. |
| `ELIG_INVALID_INDICATORS` | ELIGIBILITY | WARN | An indicator row exists but required indicators are marked invalid. |
| `ELIG_INSUFFICIENT_HISTORY` | ELIGIBILITY | WARN | Eligibility is blocked because required indicator history is still insufficient. |
| `ELIG_UNIVERSE_DEPENDENCY_MISSING` | ELIGIBILITY | HARD | An upstream dependency required to determine universe membership is unavailable. |
| `ELIG_FETCH_FAILURE` | ELIGIBILITY | WARN | Eligibility is blocked because ticker-level source acquisition failed and required upstream artifacts could not be formed safely. |
| `SNAP_SOURCE_TIMEOUT` | INTRADAY | WARN | The session-snapshot source timed out. |
| `SNAP_SOURCE_RATE_LIMIT` | INTRADAY | WARN | The session-snapshot source hit rate limiting. |
| `SNAP_PARTIAL_SCOPE` | INTRADAY | WARN | The session snapshot captured only part of the planned scope. |
| `SNAP_SOURCE_ERROR` | INTRADAY | WARN | The session-snapshot source failed for an operational reason that does not block EOD. |

| `REPLAY_FIXTURE_SCHEMA_MISMATCH` | REPLAY | HARD | Replay fixture manifest or schema version does not match the locked replay fixture contract. |
| `REPLAY_EXPECTED_PROOF_INCOMPLETE` | REPLAY | HARD | Replay expected proof package is missing required deterministic lifecycle context. |
| `REPLAY_ACTUAL_PROOF_INCOMPLETE` | REPLAY | HARD | Replay actual proof package is missing required lifecycle evidence. |
| `REPLAY_REQUESTED_DATE_MISMATCH` | REPLAY | HARD | Replay requested trade date differs between expected proof and actual proof. |
| `REPLAY_EFFECTIVE_DATE_MISMATCH` | REPLAY | HARD | Replay effective trade date differs between expected proof and actual proof. |
| `REPLAY_REQUEST_MODE_MISMATCH` | REPLAY | HARD | Replay request/promote/publish target context differs between expected proof and actual proof. |
| `REPLAY_SOURCE_MODE_MISMATCH` | REPLAY | HARD | Replay source mode differs between expected proof and actual proof. |
| `REPLAY_SOURCE_IDENTITY_MISMATCH` | REPLAY | HARD | Replay source identity or source row-count context differs between expected proof and actual proof. |
| `REPLAY_SOURCE_FILE_HASH_MISMATCH` | REPLAY | HARD | Replay manual source file hash differs between expected proof and actual proof. |
| `REPLAY_PROVIDER_CONTEXT_MISMATCH` | REPLAY | HARD | Replay provider/API retry, timeout, or HTTP context differs between expected proof and actual proof. |
| `REPLAY_COVERAGE_STATE_MISMATCH` | REPLAY | HARD | Replay coverage gate state or coverage count context differs between expected proof and actual proof. |
| `REPLAY_COVERAGE_RATIO_MISMATCH` | REPLAY | HARD | Replay coverage ratio or threshold differs between expected proof and actual proof. |
| `REPLAY_COVERAGE_REASON_MISMATCH` | REPLAY | HARD | Replay coverage reason code differs between expected proof and actual proof. |
| `REPLAY_ARTIFACT_HASH_MISMATCH` | REPLAY | HARD | Replay artifact hash or artifact row-count context differs between expected proof and actual proof. |
| `REPLAY_SEAL_STATE_MISMATCH` | REPLAY | HARD | Replay seal state differs between expected proof and actual proof. |
| `REPLAY_PUBLICATION_STATE_MISMATCH` | REPLAY | HARD | Replay publication state/readability context differs between expected proof and actual proof. |
| `REPLAY_PUBLICATION_VERSION_MISMATCH` | REPLAY | HARD | Replay publication version differs between expected proof and actual proof. |
| `REPLAY_POINTER_TARGET_MISMATCH` | REPLAY | HARD | Replay pointer target differs between expected proof and actual proof. |
| `REPLAY_POINTER_RESOLUTION_MISMATCH` | REPLAY | HARD | Replay pointer resolution state differs between expected proof and actual proof. |
| `REPLAY_FALLBACK_CONTEXT_MISMATCH` | REPLAY | HARD | Replay fallback context differs between expected proof and actual proof. |
| `REPLAY_CORRECTION_BASELINE_MISMATCH` | REPLAY | HARD | Replay correction baseline or candidate publication context differs between expected proof and actual proof. |
| `REPLAY_FINAL_STATUS_MISMATCH` | REPLAY | HARD | Replay final terminal or publishability state differs between expected proof and actual proof. |
| `REPLAY_FINAL_REASON_CODE_MISMATCH` | REPLAY | HARD | Replay final reason code or reason-code counts differ between expected proof and actual proof. |
| `REPLAY_LINEAGE_MISMATCH` | REPLAY | HARD | Replay lineage chain differs between expected proof and actual proof. |
| `REPLAY_UNEXPECTED_SUCCESS` | REPLAY | HARD | Replay produced a success-looking result when the expected proof required failure or degrade. |
| `REPLAY_UNEXPECTED_FAILURE` | REPLAY | HARD | Replay produced failure when the expected proof required a successful deterministic match. |
| `REPLAY_NON_DETERMINISTIC_OUTPUT` | REPLAY | HARD | Replay output contains a deterministic-field mismatch not covered by a more specific replay reason code. |

## Locked usage notes
- `ELIG_MISSING_BAR` and `ELIG_INSUFFICIENT_HISTORY` may coexist as different row outcomes on different dates/tickers, but one row stores only the single most specific blocking reason.
- `RUN_SOURCE_TIMEOUT` and `RUN_SOURCE_RATE_LIMIT` do not automatically force `FAILED`; terminal status still follows the decision table and gate results.
- `RUN_HASH_MISSING`, `RUN_HASH_FAILED`, `RUN_SEAL_PRECONDITION_FAILED`, and `RUN_SEAL_WRITE_FAILED` are always incompatible with final `SUCCESS`.
- `COVERAGE_THRESHOLD_MET`, `COVERAGE_BELOW_THRESHOLD`, and `COVERAGE_UNIVERSE_EMPTY` are coverage-evaluation outcomes and may appear in coverage telemetry or coverage-oriented operator surfaces even when the dominant run reason code is different.
- `RUN_COVERAGE_NOT_EVALUABLE` is the run-level blocked/not-readable reason used when finalize consumes a non-meaningful coverage evaluation outcome.
- Session snapshot reason codes must never be used to justify fallback of sealed EOD datasets.
- Replay reason codes are proof/comparison outcomes. They do not create readable publications; they explain why fixture vs actual proof matched, mismatched, or failed safe.