# Logging / Traceability / Reason Codes Inventory

[LAST_UPDATED] 2026-05-07

[RELATED_CONTRACT] LOGGING_TRACEABILITY_REASON_CODES_CONTRACT

[VALIDATION_STATUS] LOCKED_LOCAL_PHPUNIT_PASS

## Final rule

Market-data lifecycle logging is valid only when every important state transition can be reconstructed from persisted run events, run telemetry, publication/pointer state, correction/replay/evidence context, and registered reason codes. Failure, held, blocked, skipped, not-readable, mismatch, and destructive-operation outcomes must be reason-coded. Happy-path success may keep `reason_code = null` only when the surrounding payload proves the successful context.

## Inventory

| Area | Event Coverage | Reason Code | Context Completeness | Runtime Proof | Gap | Patch | Test Result |
|---|---|---|---|---|---|---|---|
| daily pipeline | `RUN_CREATED`, `STAGE_STARTED`, per-stage completion/failure, `RUN_FINALIZED` | Failure/held via `RUN_*`; success may be null | requested/effective date, source mode, run id, stage, final state | run event stream + run telemetry | none after local validation | repository now persists `RUN_CREATED`; static guard added | local targeted/full PHPUnit PASS |
| ingest/import | `STAGE_STARTED`, `STAGE_COMPLETED`, `STAGE_FAILED` / held source failure | source failures via `RUN_SOURCE_*`, invalid rows via `BAR_*` | source identity, input file/provider, acquisition telemetry, row counts | run events + run telemetry | none after local validation | source context already carried; registry/seed reconciled | local targeted/full PHPUnit PASS |
| source provider/API | source acquisition telemetry and recoverable HELD event | `RUN_SOURCE_TIMEOUT`, `RUN_SOURCE_RATE_LIMIT`, `RUN_SOURCE_AUTH_ERROR`, `RUN_SOURCE_RESPONSE_CHANGED`, `RUN_SOURCE_MALFORMED_PAYLOAD`, `RUN_SOURCE_PARTIAL_RESPONSE` | provider, timeout, retry max, attempts, final HTTP status, final reason | adapter telemetry + held run event | none after local validation | registry/seed verified by guard | local targeted/full PHPUnit PASS |
| manual file | stage event + source telemetry | `RUN_SOURCE_MANUAL_FILE_NOT_FOUND`, `RUN_SOURCE_MANUAL_FILE_NOT_READABLE`, `RUN_SOURCE_MANUAL_FILE_MALFORMED` | local file identity, hash/size/row count when available | run notes + source telemetry + command output | none after local validation | registry/seed verified by guard | local targeted/full PHPUnit PASS |
| indicators | `STAGE_COMPLETED` or `STAGE_FAILED` | `RUN_COMPUTE_FAILED`, `IND_*` | indicator set version, invalid count, row count | run event + artifact rows | `RUN_COMPUTE_FAILED` missing from registry/seed | added registry/seed | local targeted/full PHPUnit PASS |
| eligibility | `STAGE_COMPLETED` or `STAGE_FAILED` | `RUN_ELIGIBILITY_FAILED`, `ELIG_*` | coverage snapshot, hard reject count, eligibility row count | run event + artifact rows | `RUN_ELIGIBILITY_FAILED` missing from registry/seed | added registry/seed | local targeted/full PHPUnit PASS |
| hash | `STAGE_COMPLETED` or `STAGE_FAILED` | `RUN_HASH_FAILED`, `RUN_HASH_MISSING` | batch hashes and publication id | run event + publication hash columns | none found in static trace | no code change | local targeted/full PHPUnit PASS |
| seal | `SEAL_BLOCKED`, `SEAL_FAILED`, `STAGE_COMPLETED` | `RUN_SEAL_PRECONDITION_FAILED`, `RUN_SEAL_WRITE_FAILED` | publication id, seal state, seal timestamp | run event + publication columns | none found in static trace | no code change | local targeted/full PHPUnit PASS |
| coverage | `STAGE_COMPLETED` with coverage payload | `COVERAGE_THRESHOLD_MET`, `COVERAGE_BELOW_THRESHOLD`, `COVERAGE_UNIVERSE_EMPTY`, `RUN_COVERAGE_LOW`, `RUN_COVERAGE_NOT_EVALUABLE` | expected/available/missing/ratio/threshold/missing sample | run telemetry + run event | `RUN_PARTIAL_DATA`, `RUN_DATA_DELAYED`, `RUN_STALE_DATA` were seed-only | added registry rows | local targeted/full PHPUnit PASS |
| finalize | `RUN_FINALIZED`, failure/held events | `RUN_FINALIZE_BEFORE_CUTOFF`, `RUN_FINALIZE_FAILED`, `RUN_LOCK_CONFLICT`, `RUN_NON_CURRENT_PROMOTION`, `RUN_REPAIR_CANDIDATE_PARTIAL` | decision inputs, coverage, fallback, current publication, correction context, manifest | run event + run final telemetry | several finalize reason codes missing from registry/seed | added registry/seed | local targeted/full PHPUnit PASS |
| publication | publication creation/seal/current state via repository and finalize event payload | `PUBLICATION_*`, `RUN_PUBLICATION_*` | publication id/version, seal state, run id, current flag | publication table + run event payload | pointer/publication integrity codes missing from registry/seed | added registry/seed | local targeted/full PHPUnit PASS |
| pointer | pointer switch/restore/cleanup context in finalize payload and repair command | `POINTER_*`, `RUN_LOCK_CONFLICT`, `RUN_CURRENT_PUBLICATION_INTEGRITY_REPAIRED` | previous/current publication, resolved target, requested/effective date | pointer table + finalize events | some restore/cleanup catch blocks had only comments | added reason-coded append events in recovery catch paths | local targeted/full PHPUnit PASS |
| promote | coverage + finalize + force replace event | `COMMAND_DESTRUCTIVE_GUARD_REQUIRED`, `COMMAND_APPLY_CONFIRMED`, `RUN_LOCK_CONFLICT` | promote mode, publish target, force reason, publication ids | command output + run events | none after local validation | no code change beyond registry sync | local targeted/full PHPUnit PASS |
| backfill | case summaries and terminal event lookup | `RUN_*`, `COVERAGE_*`, source codes | requested date, import status, final reason, coverage context | backfill result cases + run events | no persisted trace change in this patch | static guard covers terminal reason lookup | local targeted/full PHPUnit PASS |
| correction | `CORRECTION_SKIPPED`, `CORRECTION_CANCELLED`, `CORRECTION_PUBLISHED`, `CORRECTION_FAILED` | `CORRECTION_ARTIFACT_*`, `CORRECTION_PUBLISHED`, `RUN_FINALIZE_BEFORE_CUTOFF` | correction id, prior/current/candidate publication, artifact comparison, reseal status | correction table + run events | correction artifact/outcome reason codes missing from registry/seed; unchanged/published events had null reason | added registry/seed and reason-coded events | local targeted/full PHPUnit PASS |
| replay | verification result context and mismatch list | `REPLAY_*`, `REPLAY_MATCH` | fixture identity, expected/observed context, mismatch field/value/reason | replay result repository + evidence | `REPLAY_MATCH` missing from registry/seed | added registry/seed | local targeted/full PHPUnit PASS |
| evidence | manifest and completeness context | `EVIDENCE_COMPLETE`, `EVIDENCE_INCOMPLETE` | run/publication/pointer/source/coverage/correction/replay/lineage | exported JSON/CSV artifacts | evidence completeness codes missing from registry/seed | added registry/seed | local targeted/full PHPUnit PASS |
| session snapshot | capture/purge events and command summary | `SNAP_*`, `COMMAND_DRY_RUN_ONLY`, `COMMAND_APPLY_CONFIRMED` | output dir, partial scope, candidate/deleted rows, operation mode | snapshot service + command output | no new gap found | no code change | local targeted/full PHPUnit PASS |
| current publication repair | repair dry-run/apply command summary | `COMMAND_DRY_RUN_ONLY`, `COMMAND_APPLY_CONFIRMED`, `RUN_CURRENT_PUBLICATION_INTEGRITY_REPAIRED` | operation mode, candidate rows, affected pointer/run state | repair command output | none after local validation | reason-code sync included | local targeted/full PHPUnit PASS |
| command failure/blocked/skipped | command blocked helpers and failure output | `COMMAND_*` | operator input, status, reason code, next action | command output | no new gap found | static guard includes command reason-code sync | local targeted/full PHPUnit PASS |

## Gap status after this patch

- Container could not validate PHPUnit/artisan because `vendor/` is absent, but operator-local targeted and full MarketData PHPUnit validation was supplied and passed.
- Contract status is `LOCKED` for the current source-of-truth ZIP.
- Static proof prevents registry/seed drift for reason codes and protects the minimum lifecycle logging surface.
- Local proof: `LoggingTraceabilityReasonCodesStaticGuardTest.php` OK (7 tests, 134 assertions); targeted filters for Reason/Trace/Log/Event/Lifecycle/CommandSurface/Coverage/Finalize/Pointer/Publication/Correction/Replay/Evidence/Source/Provider/ManualFile/Integration all PASS; full `tests/Unit/MarketData` OK (319 tests, 4033 assertions).
