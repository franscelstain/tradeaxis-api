# WS Downside Stability C07 Design Note

Status: IMPLEMENTED / SEEDED / IS_QUALITY_FAILED / REJECTED_AS_STRATEGY_CATALOG
Last updated: 2026-06-12

## Catalog identity

```text
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06
catalog_version=C07
catalog_count=12
catalog_hash=233b45b06cbf34da221d5d7de2d9725fdf4d3441
OOS=NOT_RUN
production_ready=0
```

## Design intent

C07 is a new catalog identity, not a mutation of C06. It was created after C06 failed IS quality with a split failure pattern: strict rows were too sparse, while broader rows still failed robust return, downside, and stability.

C07 used C01/C04/C05/C06 forensic evidence plus a runtime feature audit:

- C04 improved some return/downside direction but collapsed sample and monthly stability;
- C05 restored sample size but kept negative median return and downside below the locked p25 gate;
- C06 showed that moderate caps and stricter setup filters did not create monthly stability;
- runtime audit found additional feature axes already available in the repository/data path, so C07 tested those axes instead of further tightening old parameters.

## Runtime-supported axes

C07 uses only fields available in the runtime candidate/scoring/grouping payload after explicit pass-through:

- short-term momentum: `roc_5`, `roc_10`;
- range and not-overextended entry quality: `close_to_ll20_pct`, `range_20_pct`, `range_position_20_pct`;
- sector-relative confirmation metrics: `sector_roc20`, `rs_20_vs_sector`, `sector_rs_20_vs_ihsg`;
- event/risk flags: `corporate_action_flag`, `is_suspended`, `is_uma`, `event_risk_flag`;
- existing score component pass-count and average floors;
- existing trend metric pass-count floor;
- existing raw setup guard tolerances for ROC and close-to-HH20.

Sector information is used only as continuous confirmation metrics. C07 does not add a sector whitelist, sector exclusion list, or unsupported sector filter.

## Final IS result

```text
status=C07_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C07_NO_VALID_IS_CANDIDATE
is_valid_param_count=0
is_failed_param_count=12
artifact_hash_run_1=c562d0a37ec7911c17c50072413fbbae25bb6114
artifact_hash_run_2=c562d0a37ec7911c17c50072413fbbae25bb6114
oos_executed=0
production_ready=0
```

Forensic summary:

```text
picks_count=728..1355
median_ret_net_top=-1.0279%..-0.6993%
p25_ret_net_top=-4.0156%..-3.4276%
month_win_rate_min=17.86%..25.00%
```

C07 recovered sample size and improved monthly-win minimum versus C06, but all rows still failed robust return, p25 downside, and stability. C07 is rejected as a strategy-quality catalog and is not eligible for OOS.
