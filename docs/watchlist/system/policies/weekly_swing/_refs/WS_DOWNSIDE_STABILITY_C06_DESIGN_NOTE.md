# WS Downside Stability C06 Design Note

Status: IMPLEMENTED / SEEDED / IS_QUALITY_FAILED / REJECTED_AS_STRATEGY_CATALOG
Last updated: 2026-06-12

## Catalog identity

```text
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C06_2026_06
catalog_version=C06
catalog_count=12
catalog_hash=6c93d67fb77319a02cecc3d96fd99bb0e139a1ac
OOS=NOT_RUN
production_ready=0
```

## Design intent

C06 is a new catalog identity, not a mutation of C05. It was created after C05 failed IS quality with enough sample but weak median, downside, and monthly stability.

C06 used C01/C04/C05 forensic evidence:

- C01 drilldown favored moderate DV20 and moderate volume participation over high-liquidity/high-volume chase;
- C04 showed strict entry-quality floors improved downside direction but became too sparse;
- C05 restored sample size but did not fix negative median, p25 downside below `-3%`, or monthly stability;
- C06 therefore tested moderate caps and existing raw runtime bounds rather than loosening evaluation gates.

## Runtime-supported axes

C06 uses only fields already present in runtime candidate/scoring/grouping payloads:

- `score_metrics.dv20_idr` bounded by catalog `min_dv20_idr..dv20_strong_idr`;
- `score_metrics.vol_ratio` bounded by catalog `min_vol_ratio..strong_vol_ratio`;
- `score_metrics.atr14_pct` bounded by catalog `min_atr14_pct..max_atr14_pct`;
- `factor_breakdown.momentum.roc20` bounded by catalog `roc_lo..roc_hi`;
- `factor_breakdown.breakout.close_to_hh20_pct` bounded by `-bo_near_below_pct..bo_max_ext_pct`;
- score component pass-count and average floors;
- trend metric pass-count floor.

No sector filter, sector code, OOS data, or post-result best-of-failed selection was used.

## Final IS result

```text
status=C06_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C06_NO_VALID_IS_CANDIDATE
is_valid_param_count=0
is_failed_param_count=12
artifact_hash_run_1=ede8ca6f53ea49141a5e047e6094b7a282cdb232
artifact_hash_run_2=ede8ca6f53ea49141a5e047e6094b7a282cdb232
oos_executed=0
production_ready=0
```

Forensic summary:

```text
picks_count=9..214
median_ret_net_top=-1.6757%..1.6637%
p25_ret_net_top=-3.4390%..-0.6101%
month_win_rate_min=0.00%..0.00%
```

C06 improved some strict-row median/p25 metrics but did not produce a valid IS candidate. Strict rows were too sparse; broader rows failed robust return, downside, and stability. C06 is rejected as a strategy-quality catalog and is not eligible for OOS.
