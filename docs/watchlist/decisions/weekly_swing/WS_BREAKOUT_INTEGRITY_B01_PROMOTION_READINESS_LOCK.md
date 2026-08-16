# WS Breakout Integrity B01 — Promotion Readiness Lock

Official OOS passed the locked acceptance gates without retuning:

```text
oos_id=1
oos_window=2025-05-22..2026-05-29
picks_count_oos=84
days_covered_oos=242
avg_ret_net_top_oos=0.002326377918853518
median_ret_net_top_oos=0.0070446965286297515
p25_ret_net_top_oos=0.005048780318109579
month_win_rate_min_oos=0.7142857142857143
all_five_oos_gates_pass=1
official_oos_artifact_hash=0be1ef09abfb4ba332dc3f0605af90a5d3a565df
official_oos_file_sha1=e6caa3390104b36598e97a5dd4ceaf740edc14fa
```

Before promotion, a read-only review must verify the complete chain:

```text
param_set_id=29
bt_param_id=181
is_eval_id=220
oos_id=1
params_hash=ff14df49c1a5b3da997dafbea163a51e008314fd
eval_model_hash=d0e6f180b85edda2c3785460cd958581684102f1
implementation_hash=9f9ac615f2c9506bbebee5fb60a2038aa3a42c25
is_evidence_manifest_hash=e413a21f8951722e113a99cb6c60691d8b289750
```

The review does not promote or create PLAN. A passing review authorizes only
the existing canonical promotion procedure. Controlled runtime remains a
later, separate stage.
