# C171 Real-IS Remediation Immutable DRAFT Catalog Implementation

## Session identity

```text
C171_TOPIC=C171_IMPLEMENT_AND_PERSIST_IMMUTABLE_REAL_IS_REMEDIATION_DRAFT_CATALOG
C171_SOURCE_EVAL_ID=188
C171_SOURCE_PARAM_SET_ID=1
C171_SOURCE_PARAMS_HASH=b7f3c207b989c55c93f8f61b1fcceea2c343a151
C171_DIAGNOSTIC_ARTIFACT_HASH=768b4e47d4a9e497fda29ca6541be9a8f3a63c9d
C171_CANDIDATE_DESIGN_ARTIFACT_HASH=2a1345857e2ecf62b2d64fcaa46ed06f6015e9a6
C171_CATALOG_CODE=WS_BT_GRID_REAL_IS_REMEDIATION_C171_R1_2026_07
C171_CATALOG_VERSION=C171-R1
C171_CATALOG_COUNT=5
C171_CATALOG_HASH=82b0fcbf17823fda5ab59bd2dba3d947b4f9e233
C171_IMPLEMENTATION_STATUS=OPERATOR_MIGRATION_AND_TESTS_PASSED_HASH_IDENTITY_REPAIR_PENDING_RERUN
C171_PRODUCTION_READY=0
C172_ALLOWED=0
```

## Owner decision

The immutable `eval_id=188` diagnostic proved that general data readiness and
one flagged discontinuity are not the primary cause of failure. This stage
implements the finite five-candidate design without changing the failed
baseline, canonical evaluation gates, or execution model.

The implementation is DRAFT-persistence only. It does not run official IS or
OOS and does not select a winner.

## Canonical contract extension

Three audit-object fields were added as optional for legacy paramset
compatibility and mandatory for C171-R1 rows:

```text
liquidity.max_dv20_idr
volume.max_vol_ratio
grouping.top_max_score_total
```

When present, all three fields participate in canonical JSON normalization and
`params_hash`.

Runtime behavior:

```text
dv20_idr > max_dv20_idr          => reject before scoring as WS_LIQ_HIGH
vol_ratio > max_vol_ratio         => reject before scoring as WS_VOLR_HIGH
score_total > top_max_score_total => cannot enter TOP_PICKS
```

The TOP daily quantile is recalculated from the decision-time score pool that
is at or below the TOP cap. A score above the cap may still enter SECONDARY when
it satisfies the secondary cutoff and capacity. No future return is consulted.

## Immutable catalog

The code-owned catalog contains exactly these rows:

| Row | Min/Max DV20 | Min/Max volume ratio | ATR range | TOP score cap |
|---|---:|---:|---:|---:|
| `C171_DRAFT_A_BROAD_MODERATE_SCORE_CAP` | 1B / 50B | 1.2 / 5.0 | 0.02 / 0.075 | 0.98 |
| `C171_DRAFT_B_BROAD_SAMPLE_RECOVERY` | 1B / 50B | 1.2 / 5.0 | 0.02 / 0.075 | 1.0 |
| `C171_DRAFT_C_MID_LIQ_LOW_ATR_SCORE_CAP` | 2.5B / 50B | 1.5 / 5.0 | 0.02 / 0.06 | 0.999999 |
| `C171_DRAFT_D_LOW_ATR_BALANCED` | 1B / 50B | 1.2 / 5.0 | 0.02 / 0.06 | 0.999999 |
| `C171_DRAFT_E_LOWER_VOLUME_BALANCED` | 1B / 50B | 1.2 / 3.0 | 0.02 / 0.075 | 0.98 |

Weights, stop ATR multiple, minimum RR, group targets, quantiles, canonical IS
window, and all canonical gates remain unchanged.

Candidate DRAFT hashes are derived before persistence from the exact immutable
source canonical payload (`param_set_id=1`, hash
`b7f3c207b989c55c93f8f61b1fcceea2c343a151`) plus each immutable catalog row.
The five derived hashes must be non-empty, unique, different from the source
hash, and must match both validator output and the persisted database identity.

```text
CANDIDATE_HASH_CONTRACT=DERIVED_FROM_IMMUTABLE_SOURCE_CANONICAL_PAYLOAD_AND_CATALOG_ROW
HARDCODED_DOCUMENTATION_FIXTURE_HASHES_ALLOWED=0
PREWRITE_FIVE_CANDIDATE_HASH_MANIFEST_REQUIRED=1
```

The operator artifact records the five actual hashes and one
`candidate_hash_manifest_hash`. This avoids treating hashes produced from the
standalone documentation fixture as production identities. The documentation
fixture is schema-valid but is not byte/canonical-identical to the persisted
source DRAFT.

## Persistence contract

Command:

```text
watchlist:backtest-c171-persist-remediation-draft-catalog
```

Before any write, the service verifies:

- exact operator approval reference;
- exact source IDs and failed baseline hash;
- source DRAFT/evaluation identity and canonical IS window;
- schema/table/column readiness;
- diagnostic file SHA1, artifact hash, status, classification, and exact pick
  parity;
- design file SHA1, artifact hash, catalog code, and ordered row codes.

The catalog repository then seeds the exact rows idempotently. A persisted row
with the same identity but a different payload fails closed. Each row is mapped
to a complete canonical paramset, validated, exact-bound to its `param_id`, and
imported through the existing immutable DRAFT import service.

Outputs:

```text
storage/app/watchlist/backtest/c171-remediation-draft-catalog.json
storage/app/watchlist/backtest/c171-remediation-draft-catalog/c171_draft_*.json
```

A rerun with identical catalog and DRAFT payloads is idempotent.

## Database schema

Migration:

```text
2026_07_27_000002_add_c171_real_is_remediation_catalog_bounds
```

Adds nullable columns to `watchlist_bt_param_grid`:

```text
max_dv20_idr BIGINT UNSIGNED NULL
max_vol_ratio DECIMAL(20,6) NULL
top_max_score_total DECIMAL(10,6) NULL
```

Nullable columns preserve all prior immutable catalogs. Rollback is blocked if
C171-R1 rows already exist.

## Operator hash-identity repair

The first main-database persistence attempt seeded all five catalog rows and
inserted the first DRAFT (`param_set_id=2`) with canonical hash
`ffd50a2cf482558d6a5582f8479accd9b3bf62c8`, then correctly stopped because the
service compared it with a stale hash calculated from
`fixtures/paramset_valid.json`.

The repair does not delete or mutate that DRAFT. It performs the five-hash
preflight from the verified database source payload before any catalog/DRAFT
write, then reuses the same provenance and persistence call. Therefore the
existing first DRAFT must return `IDEMPOTENT`, while the remaining four may be
inserted. A differing existing immutable payload still fails closed.

No new migration is required for this repair.

## Fail-closed boundary

```text
OFFICIAL_IS_RUNTIME_INVOKED=0
OOS_RUNTIME_INVOKED=0
OOS_REPOSITORY_INVOKED=0
PARAMSET_PROMOTED=0
ACTIVE_PARAMSET_CREATED=0
PLAN_RUN_CREATED=0
RECOMMENDATION_PERSISTED=0
CONFIRM_MUTATED=0
PRODUCTION_ACTIVATION_EXECUTED=0
CONTROLLED_ROLLOUT_EXECUTED=0
PRODUCTION_READY=0
```

The service compares ACTIVE-paramset, OOS-row, and PLAN-run counts before and
after persistence. Any observed forbidden mutation fails closed.

## Validation scope

Source-level validation supplied by this implementation covers:

- catalog version/count/hash and row immutability;
- legacy paramset backward compatibility;
- new canonical hashes and runtime adapter mapping;
- decision-time upper-bound rejection reason codes;
- TOP score-pool cap and replacement behavior;
- database seeding and five-DRAFT idempotency on SQLite;
- exact binding mismatch rejection;
- command registration and static no-OOS/no-promotion/no-PLAN guards.

Local operator PHPUnit remains required because deployment dependencies are not
included in the source ZIP used for offline patch construction.

## Next stage

After migration, regression, and successful five-DRAFT persistence:

```text
C171_RUN_VERSIONED_OFFICIAL_IS_FOR_EACH_IMMUTABLE_REMEDIATION_DRAFT
```

Each DRAFT must be run unchanged on `2023-01-02` through `2025-05-21` and
persist its own versioned official evidence. Failed candidates remain evidence.
C172 is permitted only after one candidate passes every canonical IS gate.
### Candidate-design artifact hash compatibility

The immutable design artifact hash was created from recursively key-sorted compact JSON with unescaped Unicode and preserved zero fractions. Runtime verification therefore uses `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION` after recursive key sorting. This is intentionally distinct from the official C171 evidence hash helper and preserves the already-released design identity `2a1345857e2ecf62b2d64fcaa46ed06f6015e9a6`.

