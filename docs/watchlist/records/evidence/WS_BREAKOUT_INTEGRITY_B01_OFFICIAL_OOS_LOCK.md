# WS Breakout Integrity B01 — Single Official OOS Lock

The exact IS winner identity was independently verified before this lock:

```text
identity_review_artifact_hash=ca65ca2e25db2929f047f7baec6fc0891d90e7c0
identity_review_file_sha1=462c8dd9e1fe21ae624b78fafb0aaea14f8437d0
param_set_id=29
bt_param_id=181
is_eval_id=220
is_evidence_manifest_hash=e413a21f8951722e113a99cb6c60691d8b289750
```

Exactly one OOS evaluation is authorized:

```text
OOS_FROM=2025-05-22
OOS_TO=2026-05-29
RETUNING_ALLOWED=0
OOS_USED_FOR_SELECTION=0
TICKER_BLACKLIST_ALLOWED=0
MONTH_BLACKLIST_ALLOWED=0
GATE_CHANGE_ALLOWED=0
```

Locked acceptance gates:

```text
picks_count_oos >= 40
avg_ret_net_top_oos > 0
median_ret_net_top_oos >= 0
month_win_rate_min_oos >= 0.45
p25_ret_net_top_oos >= -0.03
```

The OOS row must carry the exact IS `params_hash`, `eval_model_hash`,
`implementation_version`, `implementation_hash`, and
`is_evidence_manifest_hash`. The OOS command may insert that one official row
but must not promote the paramset, create ACTIVE state, create PLAN, or claim
production readiness. A failed OOS result closes B01 without tuning from OOS.
