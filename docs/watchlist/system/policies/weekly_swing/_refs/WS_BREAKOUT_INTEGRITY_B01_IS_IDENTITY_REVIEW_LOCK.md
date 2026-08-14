# WS Breakout Integrity B01 — IS Winner Identity Review Lock

## Scope

This is a separate new-strategy scope after the sealed C171, R02, S01, and P01
failures. It does not reopen or remediate those scopes.

The B01 Official IS winner is locked before any OOS access:

```text
candidate_code=B01_C1_CLOSE_TO_HH20_FLOOR_NEG5
param_set_id=29
bt_param_id=181
is_eval_id=220
is_window=2023-01-02..2025-05-21
params_hash=ff14df49c1a5b3da997dafbea163a51e008314fd
eval_model_hash=d0e6f180b85edda2c3785460cd958581684102f1
implementation_hash=9f9ac615f2c9506bbebee5fb60a2038aa3a42c25
evidence_pipeline_hash=9e9933b363026623b7ab5629f3281fa680a53a2e
evidence_manifest_hash=e413a21f8951722e113a99cb6c60691d8b289750
official_is_artifact_hash=adf7ec1ba705a4823f4c8590967ffba08fcbd5d8
official_is_file_sha1=9d36e816b5b2ed31c7c3d087954d7cf47b476ef3
```

## Passing IS evidence

```text
picks_count=146
days_covered=500
avg_ret_net_top=0.0036870822158956863
median_ret_net_top=0.006905178884163123
p25_ret_net_top=0.005220092973476967
month_win_rate_min=0.625
month_avg_ret_net_min=-0.008998265585691277
period_fail_count=0
all_seven_canonical_is_gates_pass=1
```

## Required review

The review must recompute the source artifact hash, verify the exact DRAFT,
grid binding, evaluation identity, database support-evidence content hashes,
and unchanged ACTIVE/PLAN boundaries. It must fail closed on any mismatch.

The review itself:

```text
OOS_RUNTIME_INVOKED=0
OOS_REPOSITORY_INVOKED=0
OOS_TABLE_READ=0
PARAMSET_PROMOTED=0
PLAN_RUN_CREATED=0
PRODUCTION_READY=0
```

Only a passing review may authorize one immutable Official OOS evaluation over
the already-reserved window `2025-05-22..2026-05-29`. No OOS result may be used
to retune, add a blacklist, change a gate, or create another B01 candidate.
