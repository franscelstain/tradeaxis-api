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
| `COVERAGE_GATE_DISABLED` | COVERAGE | HARD | Coverage gate runtime switch was disabled; coverage must remain not evaluable and cannot create readable publication. |
| `COVERAGE_CANONICAL_BAR_EVIDENCE_DISABLED` | COVERAGE | HARD | Canonical bar evidence requirement was disabled; coverage must remain not evaluable because readable coverage requires canonical bar proof. |
| `RUN_INDICATORS_MISSING` | RUN | HARD | Required indicator artifact or required indicator row set for the requested date is not available. |
| `RUN_ELIGIBILITY_MISSING` | RUN | HARD | Eligibility snapshot for the requested date is not available. |
| `RUN_HASH_MISSING` | RUN | HARD | One or more mandatory content hashes are missing at finalization time. |
| `RUN_HASH_FAILED` | RUN | HARD | Hash computation failed or produced unusable output. |
| `RUN_SEAL_PRECONDITION_FAILED` | RUN | HARD | Seal execution was attempted before all locked preconditions were satisfied. |
| `RUN_SEAL_WRITE_FAILED` | RUN | HARD | Seal metadata could not be written successfully. |
| `DATASET_HASH_CREATED` | DATASET | INFO | Dataset hash was created from canonical serialized artifact rows. |
| `DATASET_HASH_VERIFIED` | DATASET | INFO | Dataset hash/seal context was verified against stored canonical manifest context. |
| `DATASET_HASH_MISSING` | DATASET | HARD | Dataset seal or finalize was blocked because mandatory artifact hash context is missing. |
| `DATASET_HASH_MISMATCH` | DATASET | HARD | Recomputed or mirrored dataset hash does not match stored artifact hash context. |
| `DATASET_MANIFEST_INVALID` | DATASET | HARD | Dataset manifest is missing required run/date/source/hash/coverage context. |
| `DATASET_SEAL_INVALID` | DATASET | HARD | Dataset seal state is invalid or cannot be verified from manifest/hash context. |
| `SEALED_DATASET_MUTATION_BLOCKED` | DATASET | HARD | Runtime attempted to mutate a sealed/finalized/readable dataset through a normal artifact path and was blocked. |
| `FINALIZE_HASH_MISSING` | RUN | HARD | Finalize was blocked because the candidate publication or run is missing mandatory hash context. |
| `FINALIZE_HASH_MISMATCH` | RUN | HARD | Finalize was blocked because run hash context differs from candidate publication hash context. |
| `FINALIZE_SEAL_MISSING` | RUN | HARD | Finalize was blocked because candidate publication seal state is missing. |
| `FINALIZE_SEAL_INVALID` | RUN | HARD | Finalize was blocked because candidate publication seal timestamp or verification context is invalid. |
| `RUN_FINALIZE_BEFORE_CUTOFF` | RUN | HARD | Final success was attempted before the cutoff policy allowed it. |
| `RUN_LOCK_CONFLICT` | RUN | HARD | Run-ownership conflict or duplicate writer activity occurred during hash, seal, or finalize stages. |
| `RUN_FINALIZE_IDEMPOTENCY_POINTER_INVALID` | RUN | HARD | A previously completed readable finalize run no longer matches the current publication pointer and must be fail-safed before idempotent short-circuit. |
| `RUN_SOURCE_TIMEOUT` | RUN | WARN | The source timed out and retry policy was already applied or exhausted. |
| `RUN_SOURCE_RATE_LIMIT` | RUN | WARN | The source hit rate limiting and affected data acquisition. |
| `RUN_SOURCE_AUTH_ERROR` | RUN | HARD | Source authentication failure or credential/config error blocked data acquisition. |
| `RUN_SOURCE_RESPONSE_CHANGED` | RUN | HARD | A source schema or response-contract change was detected. |
| `RUN_SOURCE_BAD_REQUEST` | RUN | HARD | Source provider returned HTTP 400 or equivalent bad request during acquisition; diagnostic context must identify ticker/window/systemic scope. |
| `RUN_SOURCE_INVALID_SYMBOL` | RUN | WARN | Source provider rejected an individual ticker/symbol; partial acquisition may continue and coverage gate decides publishability. |
| `RUN_SOURCE_PROVIDER_REJECTED_RANGE` | RUN | HARD | Source provider rejected the requested acquisition range/window or global request parameters. |
| `NO_FAILED_SOURCE_ACQUISITION_CHECKPOINT` | RUN | WARN | Resume with only-failed found no failed source acquisition checkpoint to retry. |
| `RUN_SOURCE_PARTIAL_COVERAGE` | RUN | WARN | The source returned incomplete symbol coverage for the requested date. |
| `RUN_SOURCE_PARTIAL_RESPONSE` | RUN | WARN | The source adapter returned only part of the requested provider response and coverage gate must decide publishability. |
| `RUN_SOURCE_NO_VALID_DATA` | RUN | HARD | Source acquisition produced zero valid canonical EOD bars; empty source output must remain non-readable and must not publish. |
| `RUN_SOURCE_MANUAL_FILE_EMPTY` | RUN | HARD | Manual file existed but contained no data rows; empty manual file import/promote is blocked. |
| `RUN_SOURCE_MANUAL_FILE_NO_VALID_ROWS` | RUN | HARD | Manual file rows were parsed but no row produced a valid canonical bar; run must remain non-readable. |
| `SOURCE_PROVIDER_HTTP_ERROR` | SOURCE | HARD | Source provider returned a non-transient HTTP error that must not be treated as successful data. |
| `SOURCE_PROVIDER_MALFORMED_RESPONSE` | SOURCE | HARD | Source provider response could not be parsed into canonical market-data payload. |
| `SOURCE_PROVIDER_RETRY_EXHAUSTED` | SOURCE | HARD | Source provider retry policy was exhausted without usable data. |
| `SOURCE_PROVIDER_PARTIAL_RESPONSE` | SOURCE | WARN | Source provider returned only partial usable response context. |
| `SOURCE_ALL_SYMBOLS_FAILED` | SOURCE | HARD | All requested symbols failed source acquisition. |
| `SOURCE_FAILURE_HELD` | SOURCE | WARN | Source failure caused the run to be held safely without readable publication. |
| `SOURCE_FAILURE_NOT_READABLE` | SOURCE | HARD | Source failure caused the output to remain not readable. |
| `MANUAL_FILE_MISSING` | SOURCE | HARD | Manual file path was missing or unresolved. |
| `MANUAL_FILE_UNREADABLE` | SOURCE | HARD | Manual file could not be opened or read. |
| `MANUAL_FILE_EMPTY` | SOURCE | HARD | Manual file contained no data rows. |
| `MANUAL_FILE_HEADER_INVALID` | SOURCE | HARD | Manual file header is missing or invalid. |
| `MANUAL_FILE_ALL_ROWS_INVALID` | SOURCE | HARD | Manual file rows were all rejected as invalid. |
| `MANUAL_FILE_NO_VALID_ROWS` | SOURCE | HARD | Manual file produced zero valid canonical rows. |
| `MANUAL_FILE_ROW_COUNT_MISMATCH` | SOURCE | HARD | Manual file reported row count does not match accepted canonical row count. |
| `MANUAL_FILE_SOURCE_HASH_MISSING` | SOURCE | HARD | Manual file source hash is missing and source identity cannot be proven. |
| `MANUAL_FILE_IMPORT_BLOCKED` | SOURCE | HARD | Manual file import was blocked by fail-safe policy. |
| `MANUAL_FILE_NOT_READABLE` | SOURCE | HARD | Manual file output must remain not readable. |
| `BARS_ARTIFACT_EMPTY` | ARTIFACT | HARD | Bars artifact contained zero valid rows. |
| `INDICATORS_ARTIFACT_EMPTY` | ARTIFACT | HARD | Indicators artifact contained zero rows required for publication proof. |
| `ELIGIBILITY_ARTIFACT_EMPTY` | ARTIFACT | HARD | Eligibility artifact contained zero rows and coverage cannot pass. |
| `HASH_INPUT_EMPTY` | ARTIFACT | HARD | Hash input was empty and cannot produce publication proof. |
| `SEAL_TARGET_EMPTY` | ARTIFACT | HARD | Seal target was empty and cannot be sealed as readable. |
| `PUBLICATION_CANDIDATE_EMPTY` | ARTIFACT | HARD | Publication candidate had no valid rows or proof context. |
| `EVIDENCE_PROOF_INCOMPLETE` | EVIDENCE | HARD | Evidence proof is incomplete and must not be treated as replayable proof. |
| `FINALIZE_BLOCKED_NO_VALID_DATA` | RUN | HARD | Finalize was blocked because there was no valid data proof. |
| `FINALIZE_BLOCKED_SOURCE_FAILED` | RUN | HARD | Finalize was blocked because source acquisition failed. |
| `FINALIZE_BLOCKED_EMPTY_ARTIFACT` | RUN | HARD | Finalize was blocked because required artifact proof was empty. |
| `FINALIZE_BLOCKED_COVERAGE_NOT_EVALUABLE` | RUN | HARD | Finalize was blocked because coverage could not be evaluated. |
| `FINALIZE_BLOCKED_COVERAGE_FAILED` | RUN | HARD | Finalize was blocked because coverage failed. |
| `FINALIZE_BLOCKED_HASH_MISSING` | RUN | HARD | Finalize was blocked because hash proof was missing. |
| `FINALIZE_BLOCKED_SEAL_MISSING` | RUN | HARD | Finalize was blocked because seal proof was missing. |
| `FINALIZE_BLOCKED_CANDIDATE_MISSING` | RUN | HARD | Finalize was blocked because candidate publication was missing. |
| `FINALIZE_BLOCKED_POINTER_INVALID` | RUN | HARD | Finalize was blocked because pointer target validation failed. |
| `FINALIZE_NOT_READABLE_NO_VALID_DATA` | RUN | HARD | Finalize produced not-readable state because no valid data existed. |
| `FINALIZE_HELD_SOURCE_FAILURE` | RUN | WARN | Finalize held the run because source failure prevented a readable publication. |
| `PUBLISHABILITY_BLOCKED_NO_VALID_DATA` | PUBLISHABILITY | HARD | Publishability was blocked because no valid data existed. |
| `PUBLISHABILITY_BLOCKED_SOURCE_FAILURE` | PUBLISHABILITY | HARD | Publishability was blocked because source acquisition failed. |
| `PUBLISHABILITY_BLOCKED_EMPTY_ARTIFACT` | PUBLISHABILITY | HARD | Publishability was blocked because artifact proof was empty. |
| `PUBLISHABILITY_BLOCKED_COVERAGE_NOT_EVALUABLE` | PUBLISHABILITY | HARD | Publishability was blocked because coverage was not evaluable. |
| `PUBLISHABILITY_BLOCKED_MISSING_SEAL` | PUBLISHABILITY | HARD | Publishability was blocked because seal proof was missing. |
| `PUBLISHABILITY_BLOCKED_POINTER_INVALID` | PUBLISHABILITY | HARD | Publishability was blocked because pointer target was invalid. |
| `PUBLISHABILITY_NOT_READABLE_FAIL_SAFE` | PUBLISHABILITY | HARD | Publishability was forced to not readable by fail-safe policy. |
| `POINTER_SWITCH_BLOCKED_NO_VALID_DATA` | POINTER | HARD | Pointer switch was blocked because candidate had no valid data. |
| `POINTER_SWITCH_BLOCKED_SOURCE_FAILURE` | POINTER | HARD | Pointer switch was blocked because source acquisition failed. |
| `POINTER_SWITCH_BLOCKED_EMPTY_CANDIDATE` | POINTER | HARD | Pointer switch was blocked because candidate publication proof was empty. |
| `POINTER_SWITCH_BLOCKED_NOT_READABLE` | POINTER | HARD | Pointer switch was blocked because candidate was not readable. |
| `POINTER_SWITCH_BLOCKED_INVALID_TARGET` | POINTER | HARD | Pointer switch was blocked because target validation failed. |
| `CURRENT_PUBLICATION_PRESERVED` | POINTER | INFO | Current publication pointer was preserved after failed candidate. |
| `CORRECTION_BASELINE_PRESERVED_FAIL_SAFE` | POINTER | INFO | Correction baseline was preserved because candidate failed fail-safe proof. |
| `EVIDENCE_SOURCE_FAILURE_INCLUDED` | EVIDENCE | INFO | Evidence included source failure context. |
| `EVIDENCE_EMPTY_DATASET_INCLUDED` | EVIDENCE | INFO | Evidence included empty dataset context. |
| `EVIDENCE_POINTER_PRESERVATION_INCLUDED` | EVIDENCE | INFO | Evidence included pointer preservation context. |
| `EVIDENCE_FAIL_SAFE_CONTEXT_MISSING` | EVIDENCE | HARD | Evidence is missing required fail-safe context. |
| `REPLAY_NO_VALID_DATA_MISMATCH` | REPLAY | HARD | Replay detected no-valid-data context mismatch. |
| `REPLAY_SOURCE_FAILURE_MISMATCH` | REPLAY | HARD | Replay detected source-failure context mismatch. |
| `REPLAY_EMPTY_ARTIFACT_MISMATCH` | REPLAY | HARD | Replay detected empty-artifact context mismatch. |
| `REPLAY_UNEXPECTED_READABLE_OUTPUT` | REPLAY | HARD | Replay detected unexpected readable output. |
| `REPLAY_UNEXPECTED_POINTER_SWITCH` | REPLAY | HARD | Replay detected unexpected pointer switch. |
| `REPLAY_FAIL_SAFE_CONTEXT_MISSING` | REPLAY | HARD | Replay proof is missing required fail-safe context. |
| `SOURCE_NO_VALID_DATA` | SOURCE | HARD | Canonical fail-safe alias for source acquisition that produced no valid data. |
| `SOURCE_PROVIDER_EMPTY_RESPONSE` | SOURCE | HARD | Canonical fail-safe alias for provider response that contained no usable rows. |
| `ARTIFACT_EMPTY` | ARTIFACT | HARD | Canonical fail-safe alias for an artifact with zero valid rows. |
| `EMPTY_ARTIFACT_NOT_READABLE` | ARTIFACT | HARD | Empty artifact cannot be sealed, finalized, promoted, or exposed as readable. |
| `POINTER_PRESERVED_FAIL_SAFE` | POINTER | INFO | Current pointer was preserved because candidate proof was unsafe or non-readable. |
| `EVIDENCE_FAIL_SAFE_CONTEXT_INCLUDED` | EVIDENCE | INFO | Evidence export included no-data/source-failure/pointer-preservation fail-safe context. |
| `REPLAY_FAIL_SAFE_REASON_MISMATCH` | REPLAY | HARD | Replay detected a mismatch in expected vs actual fail-safe reason context. |
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
| `READABLE_PUBLICATION_RESOLVED` | READ_SIDE | INFO | A read-side consumer resolved a current sealed readable publication through the authoritative pointer. |
| `NO_READABLE_PUBLICATION` | READ_SIDE | HARD | A read-side consumer could not resolve a current readable publication through the authoritative pointer and must return no data. |
| `COMMAND_MISSING_REQUIRED_INPUT` | COMMAND | HARD | Operator command input is missing or empty for a required argument or option. |
| `COMMAND_INVALID_DATE_FORMAT` | COMMAND | HARD | Operator command date input does not use the locked `YYYY-MM-DD` format. |
| `COMMAND_INVALID_SOURCE_MODE` | COMMAND | HARD | Operator command source mode is outside the locked API/manual-file source modes. |
| `COMMAND_INVALID_PROMOTE_MODE` | COMMAND | HARD | Operator command promote mode is unsupported by the locked promote contract. |
| `COMMAND_CONFLICTING_OPTIONS` | COMMAND | HARD | Operator command options are mutually exclusive or ambiguous. |
| `COMMAND_DESTRUCTIVE_GUARD_REQUIRED` | COMMAND | HARD | Operator command requested a destructive or force action without the required explicit guard/reason. |
| `COMMAND_DRY_RUN_ONLY` | COMMAND | INFO | Operator command completed a dry-run preview and intentionally did not mutate final state. |
| `COMMAND_APPLY_CONFIRMED` | COMMAND | INFO | Operator command mutation was executed only after explicit apply confirmation. |
| `COMMAND_EXECUTION_FAILED` | COMMAND | HARD | Operator command execution failed and surfaced a reason-coded blocking outcome. |
| `COMMAND_CORRECTION_NOT_FOUND` | COMMAND | HARD | Operator command referenced a correction id that does not exist. |
| `COMMAND_CORRECTION_STATUS_NOT_APPROVABLE` | COMMAND | HARD | Correction approve command was blocked because only REQUESTED corrections are approvable. |
| `COMMAND_CORRECTION_STATUS_NOT_EXECUTABLE` | COMMAND | HARD | Operator command attempted to execute a correction whose lifecycle status is not executable. |
| `RUN_PARTIAL_DATA` | RUN | HARD | Coverage failed because only part of the requested-date universe had canonical valid EOD data. |
| `RUN_DATA_DELAYED` | RUN | WARN | Coverage failed while requested-date data was still inside the controlled delayed-data window. |
| `RUN_STALE_DATA` | RUN | HARD | Source rows were outside the requested trade date and must not count as available coverage. |
| `RUN_COMPUTE_FAILED` | RUN | HARD | Indicator computation stage failed and the run cannot continue silently. |
| `RUN_COVERAGE_EVALUATION_FAILED` | RUN | HARD | Coverage evaluation failed before a deterministic gate result could be persisted. |
| `RUN_ELIGIBILITY_FAILED` | RUN | HARD | Eligibility build stage failed and the run cannot continue silently. |
| `RUN_FINALIZE_FAILED` | RUN | HARD | Finalize stage failed before a safe terminal state could be completed. |
| `RUN_NON_CURRENT_PROMOTION` | RUN | HARD | Promotion was requested for a target that must not become the current readable publication. |
| `RUN_REPAIR_CANDIDATE_PARTIAL` | RUN | WARN | Repair candidate is intentionally partial and must not be promoted as normal current readable data. |
| `RUN_CURRENT_PUBLICATION_INTEGRITY_REPAIRED` | RUN | WARN | Current publication mirror or pointer integrity was repaired or fail-safed to preserve readable-state contract. |
| `RUN_TERMINAL_STATUS_NOT_SUCCESS` | RUN | HARD | Publication pointer validation found a run whose terminal status is not SUCCESS. |
| `RUN_PUBLISHABILITY_NOT_READABLE` | RUN | HARD | Publication pointer validation found a run whose publishability state is not READABLE. |
| `RUN_COVERAGE_GATE_NOT_PASS` | RUN | HARD | Publication pointer validation found a run whose coverage gate state is not PASS. |
| `RUN_COVERAGE_TELEMETRY_INVALID` | RUN | HARD | Readable pointer validation found invalid coverage telemetry for a candidate run. |
| `RUN_CURRENT_MIRROR_NOT_SET` | RUN | HARD | Readable pointer validation found that the run current-publication mirror is not set. |
| `RUN_PUBLICATION_ID_MISMATCH` | RUN | HARD | Readable pointer validation found a mismatch between run publication id and pointer publication id. |
| `RUN_PUBLICATION_VERSION_MISMATCH` | RUN | HARD | Readable pointer validation found a mismatch between run publication version and pointer publication version. |
| `RUN_ROW_MISSING` | RUN | HARD | Readable pointer validation could not find the linked run row. |
| `RUN_SEALED_AT_MISSING` | RUN | HARD | Readable pointer validation found the linked run has no sealed timestamp. |
| `PUBLICATION_ROW_MISSING` | PUBLICATION | HARD | Current pointer validation could not find the linked publication row. |
| `PUBLICATION_TRADE_DATE_MISMATCH` | PUBLICATION | HARD | Current pointer validation found publication trade date mismatch. |
| `PUBLICATION_NOT_MARKED_CURRENT` | PUBLICATION | HARD | Current pointer validation found the publication row is not marked current. |
| `PUBLICATION_NOT_SEALED` | PUBLICATION | HARD | Current pointer validation found the publication is not SEALED. |
| `PUBLICATION_SEALED_AT_MISSING` | PUBLICATION | HARD | Current pointer validation found a sealed publication without sealed timestamp. |
| `POINTER_RUN_ID_MISMATCH` | POINTER | HARD | Current pointer validation found run id mismatch between pointer and publication/run context. |
| `POINTER_PUBLICATION_ID_MISMATCH` | POINTER | HARD | Current pointer validation found publication id mismatch between expected and resolved pointer target. |
| `POINTER_PUBLICATION_VERSION_MISMATCH` | POINTER | HARD | Current pointer validation found publication version mismatch. |
| `POINTER_SEALED_AT_MISSING` | POINTER | HARD | Current pointer validation found pointer sealed timestamp is missing. |
| `RUN_PUBLICATION_LINK_CREATED` | RUN | INFO | Publication lineage link was created from a valid originating run. |
| `RUN_PUBLICATION_LINK_VERIFIED` | RUN | INFO | Publication lineage link to its originating run was verified. |
| `RUN_PUBLICATION_LINK_MISSING` | RUN | HARD | Publication lineage is missing either the publication row or originating run. |
| `RUN_PUBLICATION_LINK_INVALID` | RUN | HARD | Publication lineage points to an invalid originating run or invalid publication context. |
| `RUN_PUBLICATION_MIRROR_MISMATCH` | RUN | HARD | Run-publication mirror fields disagree across run, publication, pointer, or trade-date context. |
| `PUBLICATION_RUN_NOT_FOUND` | PUBLICATION | HARD | Publication lineage points to a run id that cannot be found. |
| `PUBLICATION_RUN_STATE_INVALID` | PUBLICATION | HARD | Publication lineage points to a run whose state cannot produce a readable/current publication. |
| `POINTER_PUBLICATION_LINK_CREATED` | POINTER | INFO | Current pointer linkage to a publication was created. |
| `POINTER_PUBLICATION_LINK_VERIFIED` | POINTER | INFO | Current pointer linkage to its target publication was verified. |
| `POINTER_PUBLICATION_LINK_MISSING` | POINTER | HARD | Current pointer linkage is missing the target publication relationship. |
| `POINTER_PUBLICATION_LINK_INVALID` | POINTER | HARD | Current pointer linkage points to an invalid publication target. |
| `POINTER_PUBLICATION_NOT_FOUND` | POINTER | HARD | Current pointer target publication row could not be found. |
| `POINTER_PUBLICATION_TRADE_DATE_MISMATCH` | POINTER | HARD | Current pointer target publication trade date does not match pointer trade date. |
| `POINTER_PUBLICATION_STATE_INVALID` | POINTER | HARD | Current pointer target publication or run state is not readable/current-safe. |
| `POINTER_PUBLICATION_SEAL_INVALID` | POINTER | HARD | Current pointer target publication is not sealed with valid seal metadata. |
| `POINTER_PUBLICATION_HASH_INVALID` | POINTER | HARD | Current pointer target publication hash context is missing or mismatched. |
| `POINTER_ORPHAN_DETECTED` | POINTER | HARD | Current pointer is orphaned from a valid publication/run lineage. |
| `POINTER_SWITCH_STARTED` | POINTER | INFO | Atomic pointer switch validation started. |
| `POINTER_SWITCH_COMPLETED` | POINTER | INFO | Atomic pointer switch completed after validation. |
| `POINTER_SWITCH_FAILED` | POINTER | HARD | Atomic pointer switch failed before a valid current publication was established. |
| `POINTER_SWITCH_ROLLED_BACK` | POINTER | WARN | Pointer switch was rolled back or previous current publication was restored. |
| `POINTER_POST_SWITCH_VERIFIED` | POINTER | INFO | Pointer resolver returned the promoted publication after switch. |
| `POINTER_POST_SWITCH_MISMATCH` | POINTER | HARD | Pointer resolver did not return the expected promoted publication after switch. |
| `CURRENT_PUBLICATION_DEMOTED` | PUBLICATION | INFO | Previous current publication was demoted during an allowed pointer switch. |
| `CURRENT_PUBLICATION_PROMOTED` | PUBLICATION | INFO | Candidate publication was promoted to current after all validation passed. |
| `CURRENT_PUBLICATION_REPLACE_BLOCKED` | PUBLICATION | HARD | Replacement of an existing current publication was blocked because force/audit controls were missing or invalid. |
| `CURRENT_PUBLICATION_FORCE_REPLACED` | PUBLICATION | WARN | Operator-controlled force replace switched current publication with audit reason. |
| `CORRECTION_BASELINE_LINK_VERIFIED` | CORRECTION | INFO | Correction baseline publication/run linkage was verified. |
| `CORRECTION_BASELINE_LINK_MISSING` | CORRECTION | HARD | Correction baseline publication/run linkage is missing. |
| `CORRECTION_BASELINE_LINK_INVALID` | CORRECTION | HARD | Correction baseline linkage is not a valid current readable publication. |
| `CORRECTION_REPLACEMENT_LINK_CREATED` | CORRECTION | INFO | Correction replacement publication/run linkage was created. |
| `CORRECTION_REPLACEMENT_LINK_VERIFIED` | CORRECTION | INFO | Correction replacement publication/run linkage was verified before publication. |
| `CORRECTION_REPLACEMENT_LINK_INVALID` | CORRECTION | HARD | Correction replacement publication/run linkage is invalid. |
| `CORRECTION_POINTER_SWITCH_CREATED` | CORRECTION | INFO | Correction pointer switch was created for a valid replacement publication. |
| `CORRECTION_POINTER_SWITCH_BLOCKED` | CORRECTION | HARD | Correction pointer switch was blocked because replacement or baseline linkage was unsafe. |
| `CORRECTION_LINEAGE_INCOMPLETE` | CORRECTION | HARD | Correction lineage is incomplete across baseline, replacement, run, publication, or pointer switch. |
| `CORRECTION_BASELINE_POINTER_PRESERVED` | CORRECTION | INFO | Correction preserved the baseline current pointer on unchanged or failed replacement. |
| `REPLAY_LINEAGE_MATCHED` | REPLAY | INFO | Replay lineage matched expected run-publication-pointer-correction proof. |
| `REPLAY_RUN_PUBLICATION_MISMATCH` | REPLAY | HARD | Replay detected a run-publication lineage mismatch. |
| `REPLAY_POINTER_PUBLICATION_MISMATCH` | REPLAY | HARD | Replay detected a pointer-publication lineage mismatch. |
| `REPLAY_CORRECTION_LINEAGE_MISMATCH` | REPLAY | HARD | Replay detected correction baseline/replacement lineage mismatch. |
| `EVIDENCE_LINEAGE_CONTEXT_INCLUDED` | EVIDENCE | INFO | Evidence export included full run-publication-pointer-correction lineage context. |
| `EVIDENCE_RUN_PUBLICATION_CONTEXT_INCLUDED` | EVIDENCE | INFO | Evidence export included run-publication linkage context. |
| `EVIDENCE_POINTER_CONTEXT_INCLUDED` | EVIDENCE | INFO | Evidence export included current pointer target context. |
| `EVIDENCE_CORRECTION_LINEAGE_CONTEXT_INCLUDED` | EVIDENCE | INFO | Evidence export included correction baseline/replacement lineage context. |
| `EVIDENCE_LINEAGE_CONTEXT_MISSING` | EVIDENCE | WARN | Evidence export found missing lineage context and marked evidence incomplete. |
| `CORRECTION_ARTIFACT_BASELINE_OR_CANDIDATE_MISSING` | CORRECTION | HARD | Correction artifact comparison cannot run because baseline or candidate publication is missing. |
| `CORRECTION_ARTIFACT_HASH_INCOMPLETE` | CORRECTION | HARD | Correction artifact comparison found missing hash context and cannot prove deterministic change. |
| `CORRECTION_ARTIFACT_UNCHANGED` | CORRECTION | INFO | Correction artifact comparison found no content change; current publication must be preserved. |
| `CORRECTION_ARTIFACT_CHANGED` | CORRECTION | INFO | Correction artifact comparison found deterministic content change and reseal/publish may proceed through normal guards. |
| `CORRECTION_PUBLISHED` | CORRECTION | INFO | Correction lifecycle published a changed, resealed publication safely. |
| `CORRECTION_FAILED` | CORRECTION | HARD | Correction lifecycle failed or was blocked before safe publication. |
| `CORRECTION_CANCELLED` | CORRECTION | INFO | Correction lifecycle was consumed without publication because current content was unchanged or cancelled safely. |
| `EVIDENCE_COMPLETE` | EVIDENCE | INFO | Evidence export includes all required operator-grade context sections. |
| `EVIDENCE_INCOMPLETE` | EVIDENCE | WARN | Evidence export completed with one or more missing context sections that must be visible to the operator. |
| `REPLAY_MATCH` | REPLAY | INFO | Replay expected proof matched observed proof across deterministic fields. |

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
| `REPLAY_HISTORICAL_PUBLICATION_RESOLVED` | REPLAY | INFO | Replay resolved a selector-scoped historical sealed publication for actual-state proof without current pointer fallback. |
| `REPLAY_CURRENT_PUBLICATION_RESOLVED` | REPLAY | INFO | Replay resolved the current readable publication for current-context actual-state proof. |
| `REPLAY_NO_PUBLICATION_ACTUAL_STATE` | REPLAY | INFO | Replay built actual state for a run that has no readable publication proof. |
| `REPLAY_HISTORICAL_PUBLICATION_MISSING` | REPLAY | HARD | Replay historical actual-state selector did not resolve a publication. |
| `REPLAY_HISTORICAL_PUBLICATION_UNSEALED` | REPLAY | HARD | Replay historical actual-state publication is not sealed. |
| `REPLAY_PUBLICATION_RUN_MISMATCH` | REPLAY | HARD | Replay historical actual-state publication does not belong to the selected run or mirror context. |
| `REPLAY_HISTORICAL_ARTIFACT_SCOPE_MISMATCH` | REPLAY | HARD | Replay historical actual-state artifact scope is not publication-scoped to the selected publication. |
| `REPLAY_EXPECTED_HISTORICAL_ACTUAL_CURRENT_MISMATCH` | REPLAY | HARD | Replay expected a historical publication context but actual state resolved a current publication context, or the reverse. |
| `REPLAY_CURRENT_POINTER_MOVED_HISTORICAL_VALID` | REPLAY | INFO | Replay verified a historical sealed publication while the current pointer has moved to another publication. |

| `IMPORT_ONLY_ACCEPTED` | IMPORT_PROMOTE | INFO | Import-only request accepted; data may be ingested but not promoted. |
| `IMPORT_ONLY_COMPLETED` | IMPORT_PROMOTE | INFO | Import-only ingest completed with traceable candidate/import context. |
| `IMPORT_ONLY_NOT_PROMOTED` | IMPORT_PROMOTE | INFO | Import-only run completed without readable publication or pointer switch. |
| `IMPORT_PROMOTE_BOUNDARY_VERIFIED` | IMPORT_PROMOTE | INFO | Import/promote boundary was verified. |
| `IMPORT_PROMOTE_BOUNDARY_VIOLATION` | IMPORT_PROMOTE | HARD | Import/promote boundary violation detected. |
| `REQUEST_MODE_MISSING` | IMPORT_PROMOTE | HARD | Request mode is missing from a run context that requires explicit intent. |
| `REQUEST_MODE_INVALID` | IMPORT_PROMOTE | HARD | Request mode is not one of the allowed market-data intents. |
| `REQUEST_MODE_IMPORT_BLOCKED_FROM_PROMOTE` | IMPORT_PROMOTE | HARD | Import-only request attempted to enter a promote/publish stage. |
| `REQUEST_MODE_PROMOTE_GATE_REQUIRED` | IMPORT_PROMOTE | HARD | Promote request requires publishability gates before publication. |
| `SOURCE_MODE_VERIFIED` | SOURCE | INFO | Source mode and source identity were verified. |
| `SOURCE_MODE_MISSING` | SOURCE | HARD | Source mode is missing. |
| `SOURCE_MODE_INVALID` | SOURCE | HARD | Source mode is invalid. |
| `SOURCE_MODE_IMMUTABLE` | SOURCE | HARD | Source mode changed within a run and was blocked. |
| `MANUAL_FILE_IMPORT_ACCEPTED` | SOURCE | INFO | Manual file import was accepted as import-only context. |
| `MANUAL_FILE_IMPORT_FAILED` | SOURCE | HARD | Manual file import failed. |
| `API_IMPORT_ACCEPTED` | SOURCE | INFO | API import was accepted as import-only context. |
| `API_IMPORT_FAILED` | SOURCE | HARD | API import failed. |
| `SOURCE_IMPORT_NOT_PROMOTED` | SOURCE | INFO | Source import completed without promotion. |
| `SOURCE_PROVIDER_RATE_LIMITED` | SOURCE | WARN | Source provider rate limited the request. |
| `SOURCE_PROVIDER_TIMEOUT` | SOURCE | WARN | Source provider timed out. |
| `SOURCE_PROVIDER_UNAVAILABLE` | SOURCE | WARN | Source provider unavailable. |
| `PROVIDER_SMOKE_OK` | PROVIDER_SMOKE | INFO | Safe single-ticker provider smoke returned valid data without publication, seal, finalize, full-universe fetch, or pointer switch. |
| `PROVIDER_RATE_LIMITED` | PROVIDER_SMOKE | WARN | Safe provider smoke was rate limited by the upstream provider and must not be counted as PASS. |
| `PROVIDER_TIMEOUT` | PROVIDER_SMOKE | WARN | Safe provider smoke timed out before a valid single-ticker response was proven. |
| `PROVIDER_REQUEST_HEADER_CONTEXT_MISMATCH` | PROVIDER_SMOKE | WARN | Safe provider smoke proved the provider endpoint works with browser-like headers while a minimal PHP request context is blocked. |
| `PROVIDER_NETWORK_ERROR` | PROVIDER_SMOKE | WARN | Safe provider smoke hit a network or upstream transport failure before valid data was proven. |
| `PROVIDER_RESPONSE_PARSE_FAILED` | PROVIDER_SMOKE | HARD | Safe provider smoke received an HTTP-success provider response but could not parse the payload safely. |
| `PROVIDER_EMPTY_OR_INVALID_RESPONSE` | PROVIDER_SMOKE | HARD | Safe provider smoke returned no usable rows or an invalid provider payload. |
| `PROVIDER_TRADE_DATE_NOT_FOUND_IN_RESPONSE` | PROVIDER_SMOKE | HARD | Safe provider smoke received provider data but the selected trade date was not present in returned timestamps. |
| `PROVIDER_SMOKE_TICKER_REQUIRED` | PROVIDER_SMOKE | HARD | Safe provider smoke was blocked because no ticker was provided. |
| `PROVIDER_SMOKE_INVALID_TICKER` | PROVIDER_SMOKE | HARD | Safe provider smoke was blocked because the ticker format was invalid. |
| `PROVIDER_SMOKE_FULL_UNIVERSE_BLOCKED` | PROVIDER_SMOKE | HARD | Safe provider smoke blocked multi-ticker or full-universe execution. |
| `IMPORT_SIDE_EFFECT_BLOCKED` | IMPORT_PROMOTE | HARD | Import-only side effect was blocked. |
| `IMPORT_POINTER_WRITE_BLOCKED` | IMPORT_PROMOTE | HARD | Import-only attempted to update current pointer. |
| `IMPORT_READABLE_STATE_BLOCKED` | IMPORT_PROMOTE | HARD | Import-only attempted to mark a run readable. |
| `IMPORT_PUBLICATION_CURRENT_BLOCKED` | IMPORT_PROMOTE | HARD | Import-only attempted to mark a publication or run current. |
| `IMPORT_CORRECTION_PUBLISH_BLOCKED` | IMPORT_PROMOTE | HARD | Import-only attempted to publish a correction. |
| `PROMOTE_STARTED` | IMPORT_PROMOTE | INFO | Promote request started. |
| `PROMOTE_COMPLETED` | IMPORT_PROMOTE | INFO | Promote request completed after all gates passed. |
| `PROMOTE_BLOCKED` | IMPORT_PROMOTE | HARD | Promote request was blocked before publication. |
| `PROMOTE_COVERAGE_REQUIRED` | IMPORT_PROMOTE | HARD | Promote requires coverage gate evaluation. |
| `PROMOTE_COVERAGE_FAILED` | IMPORT_PROMOTE | HARD | Promote blocked because coverage gate failed. |
| `PROMOTE_HASH_REQUIRED` | IMPORT_PROMOTE | HARD | Promote requires deterministic hash proof. |
| `PROMOTE_SEAL_REQUIRED` | IMPORT_PROMOTE | HARD | Promote requires sealed dataset proof. |
| `PROMOTE_FINALIZE_REQUIRED` | IMPORT_PROMOTE | HARD | Promote requires finalize decision. |
| `PROMOTE_POINTER_VALIDATION_REQUIRED` | IMPORT_PROMOTE | HARD | Promote requires pointer target validation. |
| `PROMOTE_POINTER_SWITCH_COMPLETED` | IMPORT_PROMOTE | INFO | Promote completed current pointer switch. |
| `PROMOTE_POINTER_SWITCH_BLOCKED` | IMPORT_PROMOTE | HARD | Promote pointer switch was blocked. |
| `MANUAL_FILE_IMPORT_ONLY_ACCEPTED` | IMPORT_PROMOTE | INFO | Manual file import-only run accepted. |
| `MANUAL_FILE_IMPORT_ONLY_NOT_PROMOTED` | IMPORT_PROMOTE | INFO | Manual file import-only run did not promote. |
| `MANUAL_FILE_PROMOTE_STARTED` | IMPORT_PROMOTE | INFO | Manual file promote started. |
| `MANUAL_FILE_PROMOTE_COVERAGE_REQUIRED` | IMPORT_PROMOTE | HARD | Manual file promote requires coverage gate. |
| `MANUAL_FILE_PROMOTE_COVERAGE_FAILED` | IMPORT_PROMOTE | HARD | Manual file promote coverage failed. |
| `MANUAL_FILE_PROMOTE_COMPLETED` | IMPORT_PROMOTE | INFO | Manual file promote completed. |
| `MANUAL_FILE_FORMAT_INVALID` | SOURCE | HARD | Manual file format is invalid. |
| `MANUAL_FILE_SOURCE_HASH_RECORDED` | SOURCE | INFO | Manual file source hash was recorded. |
| `API_IMPORT_STARTED` | SOURCE | INFO | API import started. |
| `API_IMPORT_COMPLETED` | SOURCE | INFO | API import completed. |
| `API_IMPORT_HELD` | SOURCE | WARN | API import entered HELD state. |
| `API_IMPORT_RATE_LIMITED` | SOURCE | WARN | API import was rate limited. |
| `API_IMPORT_TIMEOUT` | SOURCE | WARN | API import timed out. |
| `API_IMPORT_PARTIAL_DATA` | SOURCE | WARN | API import returned partial data and must not promote automatically. |
| `API_PROMOTE_COVERAGE_REQUIRED` | IMPORT_PROMOTE | HARD | API promote requires coverage gate. |
| `API_PROMOTE_COVERAGE_FAILED` | IMPORT_PROMOTE | HARD | API promote coverage failed. |
| `API_PROMOTE_COMPLETED` | IMPORT_PROMOTE | INFO | API promote completed. |
| `CORRECTION_IMPORT_ACCEPTED` | CORRECTION | INFO | Correction import accepted without publication. |
| `CORRECTION_IMPORT_NOT_PROMOTED` | CORRECTION | INFO | Correction import was not promoted. |
| `CORRECTION_PROMOTE_REQUIRED` | CORRECTION | HARD | Correction publication requires explicit promote/finalize path. |
| `CORRECTION_PROMOTE_BLOCKED` | CORRECTION | HARD | Correction promote blocked. |
| `CORRECTION_PROMOTE_COMPLETED` | CORRECTION | INFO | Correction promote completed. |
| `EVIDENCE_IMPORT_CONTEXT_INCLUDED` | EVIDENCE | INFO | Evidence export included import context. |
| `EVIDENCE_PROMOTE_CONTEXT_INCLUDED` | EVIDENCE | INFO | Evidence export included promote context. |
| `EVIDENCE_IMPORT_PROMOTE_BOUNDARY_INCLUDED` | EVIDENCE | INFO | Evidence export included import/promote boundary context. |
| `EVIDENCE_IMPORT_PROMOTE_CONTEXT_MISSING` | EVIDENCE | WARN | Evidence export is missing import/promote boundary context. |
| `REPLAY_IMPORT_PROMOTE_MATCHED` | REPLAY | INFO | Replay import/promote context matched expected proof. |
| `REPLAY_IMPORT_PROMOTE_MISMATCH` | REPLAY | HARD | Replay import/promote context mismatch. |
| `REPLAY_IMPORT_STATUS_MISMATCH` | REPLAY | HARD | Replay import status mismatch. |
| `REPLAY_PROMOTE_STATUS_MISMATCH` | REPLAY | HARD | Replay promote status mismatch. |
| `REPLAY_UNEXPECTED_PUBLICATION_PROMOTION` | REPLAY | HARD | Replay detected unexpected publication promotion or pointer switch. |

## Locked usage notes
- `ELIG_MISSING_BAR` and `ELIG_INSUFFICIENT_HISTORY` may coexist as different row outcomes on different dates/tickers, but one row stores only the single most specific blocking reason.
- `RUN_SOURCE_TIMEOUT` and `RUN_SOURCE_RATE_LIMIT` do not automatically force `FAILED`; terminal status still follows the decision table and gate results.
- `RUN_HASH_MISSING`, `RUN_HASH_FAILED`, `RUN_SEAL_PRECONDITION_FAILED`, and `RUN_SEAL_WRITE_FAILED` are always incompatible with final `SUCCESS`.
- `COVERAGE_THRESHOLD_MET`, `COVERAGE_BELOW_THRESHOLD`, and `COVERAGE_UNIVERSE_EMPTY` are coverage-evaluation outcomes and may appear in coverage telemetry or coverage-oriented operator surfaces even when the dominant run reason code is different.
- `RUN_COVERAGE_NOT_EVALUABLE` is the run-level blocked/not-readable reason used when finalize consumes a non-meaningful coverage evaluation outcome.
- Session snapshot reason codes must never be used to justify fallback of sealed EOD datasets.
- Replay reason codes are proof/comparison outcomes. They do not create readable publications; they explain why fixture vs actual proof matched, mismatched, or failed safe.

---

## Amendment 2026-05-26 - Out-of-order import impact reason

| Code | Domain | Severity | Meaning |
|---|---|---:|---|
| `AFFECTED_PUBLICATION_REQUIRES_CORRECTION` | CORRECTION | HARD | A changed historical EOD bar can affect at least one already readable downstream publication; silent mutation is blocked and correction/reseal/republication is required. |

## Amendment 2026-05-27 - Impact execution states

Impact execution states are artifact states, not canonical reason codes and not terminal run statuses. They are documented in `Error_Taxonomy_and_Run_Status_Decision_Table_LOCKED.md` and artifact/runbook docs. Do not add them to the reason-code seed unless they become persisted `reason_code` values.

## Amendment 2026-05-27 - Publication reprocess reason-like codes

The following codes may appear in publication reprocess summaries or run events:

| Code | Domain | Severity | Meaning |
|---|---|---:|---|
| `REQUESTED_DATE_PROMOTED_BY_PRIMARY_PIPELINE` | PUBLICATION_REPROCESS | INFO | The requested date was already handled by the primary promote/hash/seal/finalize flow, so downstream publication reprocess has no extra work for that date. |
| `AFFECTED_DATE_RUN_NOT_FOUND` | PUBLICATION_REPROCESS | HARD | A non-readable affected date could not be promoted because no persisted run exists for that date/source mode. |
| `PUBLICATION_REPROCESS_NOT_READABLE` | PUBLICATION_REPROCESS | HARD | The affected-date promote flow completed without producing a readable publication. |
| `PUBLICATION_REPROCESS_FAILED` | PUBLICATION_REPROCESS | HARD | Publication reprocess failed before completing promote/hash/seal/finalize. |
| `PUBLICATION_REPROCESS_REPLAY_FAILED` | PUBLICATION_REPROCESS | HARD | Publication reprocess produced a readable run, but requested replay verification did not pass. |

These codes do not permit silent mutation of already-readable dates. `AFFECTED_PUBLICATION_REQUIRES_CORRECTION` remains the required reason for readable/current affected dates.
