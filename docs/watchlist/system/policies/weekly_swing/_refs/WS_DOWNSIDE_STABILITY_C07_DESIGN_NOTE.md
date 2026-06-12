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

## Scoped post-failure drilldown

After the C07 final IS quality failure, scoped IS-only drilldown was executed for:

```text
param_id=102 / 05_ANTI_REVERSAL_NOT_OVEREXTENDED
param_id=106 / 09_LOW_ATR_RANGE_SECTOR
```

Both scoped rows remained invalid:

```text
param_102 median=-0.6993% / p25=-3.4831% / month_win_min=25.00%
param_106 median=-0.7569% / p25=-3.4276% / month_win_min=20.59%
```

The scoped drilldown found risk and volume score components directionally healthier than momentum, while `corporate_action_flag` remained missing from scoped runtime evidence. C08 was not created from this evidence. The next step is runtime payload enrichment or a distinct strategy family/exit model, not another same-shape C07 threshold retune.

## C08 runtime diagnostic follow-up

C08 did not create a strategy catalog. It enriched the diagnostic payload for source-backed nullable event context and added a batched IS-only C07 drilldown command.

Executed C08 batch result:

```text
command=php artisan watchlist:backtest-is-diagnose-batch --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06 --from=2023-01-02 --to=2025-05-21 --output-dir=storage/app/watchlist/backtest/c08-batched-c07-drilldown --summary=storage/app/watchlist/backtest/c08-batched-c07-drilldown-summary.csv --overwrite
status=PASS
reason_code=WS_BT_IS_FAILURE_DRILLDOWN_BATCH_READY
diagnostic_param_count=12
ready_count=12
blocked_count=0
summary_sha1=49101D6AA702A898A3F691A7553823A8DFB2F125
oos_executed=0
production_ready=0
```

C08 batch findings:

```text
picks_count=728..1355
median_ret_net_top=-1.0279%..-0.6993%
p25_ret_net_top=-4.0156%..-3.4276%
month_win_rate_min=17.86%..25.00%
available_event_context=trading_status_code,event_risk_flag,is_suspended,is_uma
missing_runtime_evidence_fields=corporate_action_flag,corporate_action_types,event_risk_reasons
next_decision=NEXT_CATALOG_NOT_DESIGNED
```

C07 remains rejected as a strategy-quality catalog and remains ineligible for OOS. The C08 runtime evidence does not justify a same-shape C08/C09 threshold retune.
