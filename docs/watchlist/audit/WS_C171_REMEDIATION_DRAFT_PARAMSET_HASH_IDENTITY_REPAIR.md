# C171 Remediation DRAFT Paramset Hash Identity Repair

## Session identity

```text
C171_TOPIC=C171_REMEDIATION_DRAFT_PARAMSET_HASH_IDENTITY_REPAIR
C171_SOURCE_EVAL_ID=188
C171_SOURCE_PARAM_SET_ID=1
C171_SOURCE_PARAMS_HASH=b7f3c207b989c55c93f8f61b1fcceea2c343a151
C171_CATALOG_CODE=WS_BT_GRID_REAL_IS_REMEDIATION_C171_R1_2026_07
C171_CATALOG_HASH=82b0fcbf17823fda5ab59bd2dba3d947b4f9e233
C171_REPAIR_STATUS=IMPLEMENTED_PENDING_OPERATOR_RERUN
C171_OFFICIAL_IS_RUNTIME_INVOKED=0
C171_OOS_RUNTIME_INVOKED=0
C171_PRODUCTION_READY=0
C172_ALLOWED=0
```

## Operator evidence

Migration completed on `tradeaxis` and `tradeaxis_testing`. Focused C171 tests
passed with 33 tests and 327 assertions. Full Watchlist regression passed with
7,099 tests and 48,014 assertions.

The first persistence command then returned:

```text
status=BLOCKED
reason_code=C171_REMEDIATION_DRAFT_CATALOG_PERSISTENCE_FAILED
error=C171_REMEDIATION_DRAFT_PARAMSET_HASH_MISMATCH: C171_DRAFT_A_BROAD_MODERATE_SCORE_CAP
```

Persisted state at the failure boundary:

```text
CATALOG_ROW_COUNT=5
PARTIAL_DRAFT_ROW_COUNT=1
PARTIAL_DRAFT_PARAM_SET_ID=2
PARTIAL_DRAFT_STATUS=DRAFT
PARTIAL_DRAFT_PARAMS_HASH=ffd50a2cf482558d6a5582f8479accd9b3bf62c8
OFFICIAL_IS_RUNTIME_INVOKED=0
OOS_RUNTIME_INVOKED=0
PARAMSET_PROMOTED=0
PLAN_RUN_CREATED=0
PRODUCTION_READY=0
```

## Root cause

The five hardcoded expected candidate hashes were generated from
`docs/watchlist/system/policies/weekly_swing/fixtures/paramset_valid.json`.
That fixture is schema-valid and useful for unit tests, but its canonical hash
is not the immutable production source DRAFT hash. Candidate payloads preserve
all untouched source audit metadata, so a candidate derived from the fixture
cannot be assumed to have the same hash as a candidate derived from the
persisted source DRAFT.

The persistence path itself correctly generated and stored the candidate from
the exact database source. The stale expected value caused a false mismatch
after the first immutable DRAFT had already been inserted.

## Repair contract

Hardcoded fixture-derived production identities are removed. Before any new
catalog or DRAFT write, the service now:

1. verifies the exact source DRAFT canonical hash;
2. builds all five candidate payloads from the verified source canonical
   payload and immutable catalog rows;
3. validates every candidate;
4. derives five unique candidate hashes;
5. rejects missing, duplicate, source-equal, or invalid candidate hashes;
6. creates a stable candidate hash manifest;
7. compares each import validator hash and persisted database hash against the
   preflight-derived identity.

```text
CANDIDATE_HASH_CONTRACT=DERIVED_FROM_IMMUTABLE_SOURCE_CANONICAL_PAYLOAD_AND_CATALOG_ROW
PREWRITE_HASH_MANIFEST_COUNT=5
DOCUMENTATION_FIXTURE_HASH_AS_PRODUCTION_IDENTITY=FORBIDDEN
```

## Partial-state recovery

The repair intentionally preserves `param_set_id=2`. The provenance passed to
the immutable DRAFT repository is unchanged. Therefore a repaired rerun must
resolve the first DRAFT as `IDEMPOTENT`, then insert the remaining four DRAFTs.
A later exact rerun must resolve all five as idempotent.

Forbidden recovery actions:

```text
DELETE_PARAM_SET_ID_2=0
UPDATE_PARAM_SET_ID_2_PAYLOAD=0
RESET_AUTO_INCREMENT=0
DELETE_CATALOG_ROWS=0
MUTATE_CATALOG_HASH=0
```

No migration is required.

## Expected repaired result

```text
status=C171_IMMUTABLE_REMEDIATION_DRAFT_CATALOG_PERSISTED
catalog_row_count=5
draft_paramset_created_count=4
draft_paramset_idempotent_count=1
candidate_hash_contract=DERIVED_FROM_IMMUTABLE_SOURCE_CANONICAL_PAYLOAD_AND_CATALOG_ROW
candidate_hash_manifest_count=5
official_is_runtime_invoked=0
oos_runtime_invoked=0
paramset_promoted=0
plan_run_created=0
production_ready=0
next_recommendation=C171_RUN_VERSIONED_OFFICIAL_IS_FOR_EACH_IMMUTABLE_REMEDIATION_DRAFT
```

The inserted/idempotent split may be five idempotent rows on any later exact
rerun. Official IS remains a separate subsequent C171 stage.
