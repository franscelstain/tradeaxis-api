# C171 Weekly Swing Versioned Official Backtest Evidence and Executable IS Strategy Remediation

## Session identity

```text
C171_TOPIC=C171_VERSIONED_OFFICIAL_BACKTEST_EVIDENCE_AND_EXECUTABLE_IS_STRATEGY_REMEDIATION
C171_PHASE=IS_ONLY_OFFICIAL_EVIDENCE_REMEDIATION
C171_EXECUTION_MODE=IMPLEMENTATION_FIRST_IS_ONLY
C171_SOURCE_OF_TRUTH=REPOSITORY_OWNER_DOCUMENTS_AND_RUNTIME_DATABASE
C171_PRODUCTION_READY=0
```

## Reason for the session

C170 proved that the former C28 G05 route was selected using the evaluated D1-D5 future path. C29 therefore stopped before OOS. C170 also proved that the production support tables did not yet carry exact `eval_id` identity.

C171 implements the remediation already planned by the owner documents. It does not introduce a replacement Watchlist pipeline. It versions and persists evidence from the existing canonical PLAN → RECOMMENDATION → CONFIRM backtest replay.

## Implemented scope

```text
C171_PRODUCTION_EVIDENCE_MIGRATION_ADDED=1
C171_PICKS_EVAL_ID_VERSIONED=1
C171_UNIVERSE_EVAL_ID_VERSIONED=1
C171_CUTOFFS_EVAL_ID_VERSIONED=1
C171_EVAL_MANIFEST_HASHES_ADDED=1
C171_OOS_FUTURE_IDENTITY_COLUMNS_ADDED=1
C171_PARAMSET_PARAMS_HASH_ADDED=1
C171_PARAMSET_EVAL_MODEL_IDENTITY_ADDED=1
C171_PARAMSET_IMPLEMENTATION_IDENTITY_ADDED=1
C171_PARAMSET_PAYLOAD_DB_IMMUTABILITY_ADDED=1
C171_DRAFT_IMPORT_CONCURRENCY_IDENTITY_ADDED=1
C171_CANONICAL_PARAMSET_RUNTIME_ADAPTER_ADDED=1
C171_OFFICIAL_EVIDENCE_REPOSITORY_ADDED=1
C171_OFFICIAL_EVIDENCE_TRANSACTIONAL_BUNDLE_ADDED=1
C171_EVALUATION_HASH_FIELDS_STRING_SAFE=1
C171_CANONICAL_TOP_PICK_POPULATION_LOCKED=1
C171_MARKET_DATA_LINEAGE_REQUIRED=1
C171_UNIVERSE_CUTOFF_DATE_COVERAGE_LOCKED=1
C171_PARAMSET_PROVENANCE_IDENTITY_LOCKED=1
C171_CANONICAL_IS_SERVICE_ADDED=1
C171_CANONICAL_IS_COMMAND_ADDED=1
```

## Exact identity contract

The following must be equal before official OOS is allowed:

```text
DRAFT_PARAMS_HASH=IS_PARAMSET_HASH
DRAFT_EVAL_MODEL_HASH=IS_EVAL_MODEL_HASH
DRAFT_IMPLEMENTATION_HASH=IS_IMPLEMENTATION_HASH
```

The following support evidence belongs exclusively to one `eval_id`:

```text
PICKS_HASH
UNIVERSE_HASH
CUTOFFS_HASH
MARKET_DATA_LINEAGE_HASH
EVIDENCE_MANIFEST_HASH
```

Promotion additionally requires future C172 OOS evidence to carry the same paramset, evaluation-model, implementation, and IS manifest hashes. Row-count parity alone is rejected.

## Executable IS strategy boundary

The C171 command adapts the canonical audited DRAFT payload to execution values without changing the canonical source JSON. It then runs the existing canonical backtest replay for exactly one bound `bt_param_id`.

```text
STRATEGY_INPUT_TIME=DECISION_TIME_ONLY
FUTURE_D1_D5_ROUTE_SELECTION=FORBIDDEN
CANONICAL_IS_WINDOW=2023-01-02_TO_2025-05-21
LAST_HOLDING_DAYS_ENTRY_CENSORED=1
TRADE_CANDIDATES_FROZEN_BEFORE_PRICE_READ=REQUIRED
FUTURE_PRICE_USED_FOR_EVALUATION_ONLY=REQUIRED
STRATEGY_PAYLOAD_IMMUTABLE_AFTER_PRICE_READ=REQUIRED
CANONICAL_PLAN_REPLAY=USED
CANONICAL_RECOMMENDATION_REPLAY=USED
CONFIRM_OVERLAY_DIAGNOSTIC_REPLAY=USED
PUBLISHED_MARKET_DATA=USED
```

C171 does not guarantee that the current DRAFT passes IS. A failed canonical IS gate is a valid fail-closed result and keeps C171 in targeted strategy remediation.

## Forbidden actions

```text
OOS_RUNTIME_INVOKED=0
OOS_REPOSITORY_INVOKED=0
PARAMSET_PROMOTED=0
ACTIVE_PARAMSET_CREATED=0
PLAN_RUN_CREATED=0
RECOMMENDATION_PERSISTED=0
CONFIRM_MUTATED=0
PRODUCTION_ACTIVATION_EXECUTED=0
CONTROLLED_ROLLOUT_EXECUTED=0
OFFICIAL_PUBLICATION_EXECUTED=0
```

## Command

```powershell
php artisan watchlist:backtest-c171-versioned-official-is-evidence `
  --param-set-id=1 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --approval-reference=C171_OPERATOR_APPROVED_OFFICIAL_IS_EVIDENCE_ONLY `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c171-versioned-official-is-evidence.json `
  --overwrite `
  --progress
```

## Result interpretation

Passing canonical IS:

```text
STATUS=C171_VERSIONED_OFFICIAL_IS_EVIDENCE_PERSISTED_CANONICAL_GATES_PASSED_OOS_NOT_RUN
NEXT_RECOMMENDATION=C172_WEEKLY_SWING_OFFICIAL_OOS_EXECUTION_AND_PERSISTENCE
```

Failing canonical IS:

```text
STATUS=C171_VERSIONED_OFFICIAL_IS_EVIDENCE_PERSISTED_CANONICAL_GATES_FAILED_OOS_NOT_RUN
NEXT_RECOMMENDATION=C171_TARGETED_EXECUTABLE_IS_STRATEGY_REMEDIATION
```

Neither result activates production. OOS is allowed only after a single immutable candidate passes every canonical IS and evidence gate. Official picks are exactly the metrics-ready TOP/TOP_PICKS population used by the canonical `picks_count`; SECONDARY and non-metrics-ready trades are not inserted as official picks. Every official pick, universe row, and cutoff row must carry positive Market Data publication/version/run lineage, and every universe date must have exactly one cutoff row.

## Sandbox implementation validation

```text
PHP_LINT_C171_CHANGED_PHP=PASS
COMMAND_REGISTRATION_STATIC_CHECK=PASS
OWNER_DOCUMENT_SYNC=PASS
PHPUNIT_SANDBOX=BLOCKED_BY_MISSING_DOM_MBSTRING_XML_XMLWRITER
DIRECT_NON_DATABASE_IDENTITY_CHECK=PASS
JSON_CASE_INSENSITIVE_DUPLICATE_KEYS_INTRODUCED_BY_C171=0
LOCAL_DATABASE_RUNTIME_VALIDATION_REQUIRED=1
```


## C171 MySQL/MariaDB foreign-key compatibility repair

The first local migration attempt exposed a real historical schema divergence:

```text
HISTORICAL_OWNER_DDL_EVAL_ID=SIGNED_BIGINT
OLDER_LARAVEL_MIGRATION_EVAL_ID=UNSIGNED_BIGINT
MYSQL_FOREIGN_KEY_REQUIRES_EXACT_SIGNEDNESS=1
FIRST_LOCAL_RESULT=FK_BT_PICKS_EVAL_INCORRECTLY_FORMED
```

The C171 migration now reads the actual parent `COLUMN_TYPE` and aligns each
child foreign-key column before adding the constraint. It also verifies/repairs
the InnoDB engine, skips constraints and indexes already created by a partial
DDL run, and emits explicit child/parent type and engine diagnostics if a
foreign key still cannot be created.

Because MySQL/MariaDB DDL may auto-commit before a later statement fails, the
migration is intentionally resumable. The failed first attempt must not be
rolled back destructively; apply the repaired source and rerun `php artisan
migrate`.

## C171 streaming official-evidence memory remediation

The first complete local canonical-IS run proved that the former in-memory
evidence model was not operationally viable:

```text
MEMORY_LIMIT_512_MB=EXHAUSTED
MEMORY_LIMIT_2048_MB=EXHAUSTED
FAILURE_POINT=WeeklySwingBacktestEvidenceIdentityService::stableHash
ROOT_CAUSE=FULL_WINDOW_UNIVERSE_AND_REPLAY_ITEMS_MATERIALIZED_AND_COPIED_FOR_CANONICAL_HASHING
```

This is an implementation-resource failure, not an IS gate result. C171 now
keeps the same strategy, canonical parameter payload, evaluation model, hash
contract, date boundary, and official evidence schema while changing only the
operational evidence transport:

```text
C171_OFFICIAL_EVIDENCE_STORAGE_MODE=JSONL_SPOOL
C171_REPLAY_ITEMS_MODE=COMPACT_TRADES_ONLY
C171_UNIVERSE_HASH_MODE=INCREMENTAL_CANONICAL_JSON_ARRAY_SHA1
C171_CUTOFF_HASH_MODE=INCREMENTAL_CANONICAL_JSON_ARRAY_SHA1
C171_DATABASE_INSERT_MODE=CHUNKED_TRANSACTIONAL
C171_DATABASE_MANIFEST_READ_MODE=CHUNKED_INCREMENTAL_HASH
C171_EVIDENCE_HASH_COMPATIBILITY_WITH_IN_MEMORY_CONTRACT=REQUIRED
```

The JSONL spool is temporary operational storage only. The persisted rows and
manifest hashes remain authoritative. Streaming and in-memory manifests must be
byte-contract equivalent for the same ordered evidence rows. The repository
removes the raw and canonical spool files after a successful first persistence.
A failed run leaves its spool files available for diagnosis and the next run
clears only the matching deterministic C171 spool prefix.

This remediation does not permit OOS, promotion, PLAN persistence,
recommendation persistence, CONFIRM mutation, activation, or rollout.

## C171 universe vol-ratio precision remediation

The first streaming run reached transactional official-evidence persistence under
`memory_limit=512M`, then MySQL rejected a legitimate historical universe
snapshot:

```text
FAILURE=SQLSTATE[22003] 1264
TABLE=watchlist_bt_universe_ws
COLUMN=vol_ratio
OLD_TYPE=DECIMAL(10,6)
OLD_MAX=9999.999999
OBSERVED_VALUE=10382.317073
```

This is a schema-capacity defect, not a strategy gate failure. The evidence value
must not be clamped or discarded because doing so would break audit fidelity and
row-hash reproducibility. C171 widens only the integer capacity while preserving
the established six-decimal official-evidence hash scale:

```text
NEW_TYPE=DECIMAL(20,6)
VALUE_TRANSFORMATION=NONE
PARAMSET_CHANGED=0
STRATEGY_CHANGED=0
THRESHOLD_CHANGED=0
OOS_RUNTIME_INVOKED=0
```

Fresh schemas use the widened type directly. Existing databases use migration
`2026_07_27_000001_widen_watchlist_backtest_universe_vol_ratio_precision`.
The migration is intentionally non-destructive and its down path does not narrow
immutable evidence back to a range that may reject valid historical rows.


## Final operator-validated official IS result

The implementation-resource remediations were validated on the operator
workstation and the canonical command completed under `memory_limit=512M`.

```text
C171_IMPLEMENTATION_STATUS=IMPLEMENTED_AND_OPERATOR_VALIDATED
C171_FULL_WATCHLIST_PHPUNIT=OK (7085 tests, 47794 assertions)
C171_OFFICIAL_EVAL_ID=188
C171_OFFICIAL_EVIDENCE_PERSISTENCE_STATUS=INSERTED
C171_PICKS_COUNT=1425
C171_UNIVERSE_COUNT=401982
C171_CUTOFF_COUNT=508
C171_ARTIFACT_HASH=fef05d2849746e233290224ca3c18018a44bbd81
C171_ARTIFACT_FILE_SHA1=B9A3E74466F05FB7A1504CAFF4C7B06F86DD3F62
C171_CANONICAL_IS_GATES_PASS=0
C171_STATUS=C171_VERSIONED_OFFICIAL_IS_EVIDENCE_PERSISTED_CANONICAL_GATES_FAILED_OOS_NOT_RUN
C171_REASON_CODE=C171_NO_EXECUTABLE_IS_CANDIDATE_CANONICAL_GATES_FAILED
C171_NEXT=C171_TARGETED_EXECUTABLE_IS_STRATEGY_REMEDIATION_AND_TRADE_EVIDENCE_DIAGNOSTIC
```

The result is an implementation/runtime PASS and a strategy-quality FAIL. It
must not be represented as production readiness or an OOS unlock. `param_set_id
1` and `eval_id 188` are immutable baseline evidence.
