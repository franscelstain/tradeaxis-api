# Watchlist Lumen Contract Tracker

## Document Purpose

Dokumen ini melacak kontrak perilaku yang harus dipenuhi selama implementasi watchlist di Lumen.

Dokumen ini bukan owner business rule. Kontrak di sini harus ditelusuri ke:

- `docs/watchlist/system/policy.md`;
- `docs/watchlist/system/policies/weekly_swing/**`;
- `docs/watchlist/system/implementation/weekly_swing/**` sebagai translation guidance;
- owner upstream market-data untuk producer-facing consumer read contract.

## ACTIVE SESSION

Session:
`WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF EXECUTION SESSION`

Status:
`DONE for published price series runtime proof scope / LOCAL_RUNTIME_PROOF_PASS / NOT_PRODUCTION_READY`.

Historical baselines remain valid and are not downgraded:

- `PHASE_6_CONFIRM_OVERLAY_FOUNDATION_DONE / NOT_PRODUCTION_READY`;
- `PHASE_7_BACKTEST_STRATEGY_ENGINE_FOUNDATION_DONE / NOT_PRODUCTION_READY`;
- `DONE for runtime artifact and metrics foundation unit/static scope / LOCAL_PHPUNIT_PASS / NOT_PRODUCTION_READY`.

Final operator proof exists for the closure build: all requested Watchlist/MarketData PHPUnit scopes passed, the command ran twice against current readable publications, zero-volume execution semantics and dynamic 70% coverage behaved correctly, metric thresholds resolved, and canonical artifact hash equality passed. Published-price runtime proof scope is complete; walk-forward/OOS and production operating proof remain outstanding.

Coverage correction evidence:

- operator PublishedPrice `17/146` PASS;
- operator Backtest `47/490` and full Watchlist `138/1372` each had the same single dynamic-coverage failure;
- MarketData PublishedEodSeries `6/29`, TradingCalendar `4/16`, and Watchlist read model `3/41` PASS;
- corrected coverage semantics pass controlled `1/10 FAIL`, `7/10 PASS`, and explicit empty-recommendation coverage checks.

Priority contract status:

- `WL-CONTRACT-007`: DONE for published-price runtime paramset traceability scope; remains not `LOCKED`;
- `WL-CONTRACT-008`: DONE for published-price runtime explainability scope; remains not `LOCKED`;
- `WL-CONTRACT-009`: DONE for published-price no-lookahead runtime proof scope; remains not `LOCKED`;
- `WL-CONTRACT-010`: DONE for published-price deterministic runtime reproducibility scope; remains not `LOCKED`;
- `WL-CONTRACT-013`: DONE for deterministic JSON runtime artifact evidence scope; remains not `LOCKED`;
- `WL-CONTRACT-014`: DONE for current docs synchronization scope;
- `WL-CONTRACT-015`: `PARTIAL / NOT_READY`.

No contract is `LOCKED`. Watchlist Production Ready remains `NO`.

## Status Rules

- `NOT_STARTED`: no implementation yet.
- `FOUNDATION_STARTED`: governance/docs baseline exists, but runtime implementation is not complete.
- `IN_PROGRESS`: implementation started but not finished.
- `PARTIAL`: some acceptance criteria met but not enough for lock.
- `DONE`: scope-specific work completed, not necessarily production readiness.
- `LOCKED`: implementation, tests, runtime proof, artifact evidence, and docs sync all valid.
- `BLOCKED`: cannot progress due to missing dependency or decision.
- `SUPERSEDED`: replaced by newer contract.

No contract may move to `LOCKED` only because documentation exists.

## Contract Summary

| Contract ID | Title | Status |
|---|---|---|
| WL-CONTRACT-001 | MARKET-DATA PUBLICATION READ CONTRACT | `PARTIAL` |
| WL-CONTRACT-002 | NO RAW MARKET-DATA BYPASS | `PARTIAL` |
| WL-CONTRACT-003 | NO MAX-DATE / LATEST SHORTCUT | `PARTIAL` |
| WL-CONTRACT-004 | INDICATOR VALIDITY CONTRACT | `PARTIAL` |
| WL-CONTRACT-005 | ELIGIBILITY CONTRACT | `PARTIAL` |
| WL-CONTRACT-006 | SCORING DETERMINISM CONTRACT | `PARTIAL` |
| WL-CONTRACT-007 | PARAMSET TRACEABILITY CONTRACT | `DONE for published-price runtime scope / NOT LOCKED` |
| WL-CONTRACT-008 | SIGNAL EXPLAINABILITY CONTRACT | `DONE for published-price runtime scope / NOT LOCKED` |
| WL-CONTRACT-009 | BACKTEST NO-LOOKAHEAD CONTRACT | `DONE for published-price runtime scope / NOT LOCKED` |
| WL-CONTRACT-010 | BACKTEST REPRODUCIBILITY CONTRACT | `DONE for published-price runtime scope / NOT LOCKED` |
| WL-CONTRACT-011 | RISK GATE CONTRACT | `PARTIAL` |
| WL-CONTRACT-012 | PORTFOLIO AWARENESS BOUNDARY | `NOT_STARTED` |
| WL-CONTRACT-013 | AUDIT ARTIFACT CONTRACT | `DONE for deterministic JSON runtime evidence scope / NOT LOCKED` |
| WL-CONTRACT-014 | DOCS SYNC CONTRACT | `DONE for final published-price runtime proof docs sync scope` |
| WL-CONTRACT-015 | PRODUCTION READINESS CONTRACT | `PARTIAL / NOT_READY` |
| WL-CONTRACT-016 | PLAN GROUPING DETERMINISM CONTRACT | `PARTIAL` |
| WL-CONTRACT-017 | PLAN GROUP BOUNDARY CONTRACT | `PARTIAL` |
| WL-CONTRACT-018 | RECOMMENDATION PLAN-SOURCE CONTRACT | `PARTIAL` |
| WL-CONTRACT-019 | RECOMMENDATION DETERMINISM AND EMPTY-SET CONTRACT | `PARTIAL` |

---

## WL-CONTRACT-001 — MARKET-DATA PUBLICATION READ CONTRACT

Contract ID:
`WL-CONTRACT-001`

Title:
`MARKET-DATA PUBLICATION READ CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policy.md`
- `docs/watchlist/system/README.md`
- `docs/watchlist/system/implementation/weekly_swing/01_WS_IMPLEMENTATION_SCOPE_AND_BOUNDARY.md`
- `docs/watchlist/system/implementation/weekly_swing/02_WS_MODULE_MAPPING.md`
- `docs/market_data/book/Downstream_Consumer_Read_Model_Contract_LOCKED.md`
- `docs/market_data/book/EOD_Eligibility_Snapshot_Contract_LOCKED.md`
- `docs/market_data/book/Downstream_Data_Readiness_Guarantee_LOCKED.md`
- `docs/market_data/book/Publication_Current_Pointer_Integrity_Contract_LOCKED.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php`
- `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php` downstream gated universe consumer
- `app/Application/MarketData/Services/MarketDataWatchlistReadService.php` upstream consumer read gateway
- `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php` upstream publication-scoped row source

Tests:

- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadServiceTest.php`
- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistCandidateUniverseServiceTest.php`
- `tests/Unit/Watchlist/WatchlistCandidateUniverseStaticGuardTest.php`
- upstream reference: `tests/Unit/MarketData/MarketDataWatchlistReadModelTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Watchlist read model exists and consumes the upstream market-data watchlist read surface.
- Candidate universe service consumes the Phase 1 read model and preserves pointer/publication metadata in gated rows.
- Contract is not `LOCKED` because there is no watchlist command/API runtime proof and no artifact/log output yet.
- Backtest foundation consumes upstream PLAN/recommendation/confirm services rather than raw market-data. Runtime command/API consumers have not been added yet.

Acceptance criteria:

- Watchlist reads market-data only from current readable publication pointer.
- Consumed publication is sealed, `SUCCESS`, `READABLE`, coverage `PASS`, and mirror-valid through upstream market-data readiness.
- Failure to resolve valid publication fails safe.
- No raw/staging/latest fallback exists in watchlist application code.
- Static guard covers the no-bypass constraint.

Last update:
`2026-05-28 — WATCHLIST — CANDIDATE UNIVERSE + LIQUIDITY/RISK FILTER EXECUTION SESSION`

---

## WL-CONTRACT-002 — NO RAW MARKET-DATA BYPASS

Contract ID:
`WL-CONTRACT-002`

Title:
`NO RAW MARKET-DATA BYPASS`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/README.md`
- `docs/watchlist/system/policy.md`
- `docs/watchlist/system/implementation/weekly_swing/01_WS_IMPLEMENTATION_SCOPE_AND_BOUNDARY.md`
- `docs/watchlist/system/implementation/weekly_swing/02_WS_MODULE_MAPPING.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php`
- `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php`
- `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php` upstream hardened query boundary

Tests:

- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadServiceTest.php`
- `tests/Unit/Watchlist/WatchlistCandidateUniverseStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistCandidateUniverseServiceTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Watchlist application code currently has no direct DB read. Phase 2 candidate universe consumes `WatchlistMarketDataConsumerReadService` only.
- Static guard blocks `DB::table`, raw market-data table names, staging/latest/MAX(date) shortcuts in watchlist application code, including the candidate universe service.
- Contract is not `LOCKED` until future watchlist consumers are added and guarded by runtime proof.

Acceptance criteria:

- Watchlist does not directly consume raw provider response, staging tables, unsealed bars, unsealed indicators, or unsealed eligibility rows.
- Static guard rejects raw market-data bypass patterns in watchlist code.
- Any future repository/API/command must preserve this boundary or update the guard.

Last update:
`2026-05-28 — WATCHLIST — CANDIDATE UNIVERSE + LIQUIDITY/RISK FILTER EXECUTION SESSION`

---

## WL-CONTRACT-003 — NO MAX-DATE / LATEST SHORTCUT

Contract ID:
`WL-CONTRACT-003`

Title:
`NO MAX-DATE / LATEST SHORTCUT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policy.md`
- `docs/watchlist/system/implementation/weekly_swing/01_WS_IMPLEMENTATION_SCOPE_AND_BOUNDARY.md`
- `docs/watchlist/system/implementation/weekly_swing/03_WS_RUNTIME_ARTIFACT_FLOW.md`
- `docs/market_data/book/Publication_Current_Pointer_Integrity_Contract_LOCKED.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php`
- `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php`

Tests:

- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistCandidateUniverseStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Watchlist read model delegates date/publication resolution to market-data.
- Candidate universe service keeps the already-resolved `trade_date_effective`, `publication_id`, `publication_version`, and `run_id` from Phase 1 output.
- Static guard forbids `MAX(trade_date)`, `max('trade_date')`, `latest()`, `orderByDesc('trade_date')`, and descending date fallback in watchlist application code.
- Contract is not `LOCKED` until all future watchlist read consumers are added and covered by runtime proof.

Acceptance criteria:

- Date/effective publication resolution is owned by market-data current readable publication pointer.
- Watchlist code does not infer data freshness via `MAX(trade_date)`, `latest()`, descending date limit, or fallback to newest available raw row.
- Any future backtest/recommendation/API code must use the same resolved publication/effective date contract.

Last update:
`2026-05-28 — WATCHLIST — CANDIDATE UNIVERSE + LIQUIDITY/RISK FILTER EXECUTION SESSION`

---

## WL-CONTRACT-004 — INDICATOR VALIDITY CONTRACT

Contract ID:
`WL-CONTRACT-004`

Title:
`INDICATOR VALIDITY CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/03_WS_DATA_MODEL_MARIADB.md`
- `docs/watchlist/system/policies/weekly_swing/08_WS_PLAN_ALGORITHM.md`
- `docs/watchlist/system/implementation/weekly_swing/05A_WS_CANONICAL_FIELD_MATRIX.md`
- market-data indicator/readiness owner docs

Implementation files:

- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php`
- `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php`

Tests:

- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadServiceTest.php`
- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Required indicator fields are checked in watchlist service.
- Upstream market-data watchlist repository now filters `ind.is_valid = 1`, `invalid_reason_code IS NULL`, `indicator_set_version IS NOT NULL`, and required indicator fields non-null.
- Watchlist service still revalidates rows and excludes invalid/incomplete rows if they ever appear in the upstream payload.
- Contract is not `LOCKED` because no runtime command/API proof exists.

Acceptance criteria:

- A ticker cannot become a watchlist candidate if required indicator values are null, missing, invalid, or flagged by invalid reason code.
- Required indicator list is explicit and guarded by tests.
- Invalid candidate rows are excluded with reason-coded evidence.

Last update:
`2026-05-28 — WATCHLIST — CANDIDATE UNIVERSE + LIQUIDITY/RISK FILTER EXECUTION SESSION`

---

## WL-CONTRACT-005 — ELIGIBILITY CONTRACT

Contract ID:
`WL-CONTRACT-005`

Title:
`ELIGIBILITY CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policy.md`
- `docs/watchlist/system/policies/weekly_swing/03_WS_DATA_MODEL_MARIADB.md`
- `docs/watchlist/system/implementation/weekly_swing/05A_WS_CANONICAL_FIELD_MATRIX.md`
- `docs/market_data/book/EOD_Eligibility_Snapshot_Contract_LOCKED.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php`
- `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php`

Tests:

- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadServiceTest.php`
- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Upstream market-data watchlist repository returns `elig.eligible = 1` rows only and publication/run scopes eligibility to the resolved readable publication.
- Watchlist service rechecks `eligibility_state` and excludes any non-eligible row if the upstream payload is malformed.
- Contract is not `LOCKED` because no runtime command/API proof exists.

Acceptance criteria:

- Watchlist candidate universe contains only eligible tickers from the resolved market-data publication.
- Non-eligible rows are not silently accepted.
- Eligibility reason state remains traceable for downstream scoring/recommendation work.

Last update:
`2026-05-28 — WATCHLIST — MARKET-DATA CONSUMER READ MODEL EXECUTION SESSION`

---

## WL-CONTRACT-006 — SCORING DETERMINISM CONTRACT

Contract ID:
`WL-CONTRACT-006`

Title:
`SCORING DETERMINISM CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/08_WS_PLAN_ALGORITHM.md`
- `docs/watchlist/system/policies/weekly_swing/09_WS_DYNAMIC_SELECTION_DETERMINISTIC.md`
- `docs/watchlist/system/policies/weekly_swing/04_WS_PARAMSET_JSON_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/05_WS_PARAMETER_REGISTRY_COMPLETE.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistScoringService.php`
- `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php`
- `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php` scoring input metric pass-through
- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php` ticker id pass-through
- `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php` ticker id read-surface pass-through

Tests:

- `tests/Unit/Watchlist/WatchlistScoringServiceTest.php`
- `tests/Unit/Watchlist/WatchlistScoringStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingServiceTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Scoring engine foundation is baseline local PASS for Phase 3 unit/static scope.
- PLAN grouping foundation consumes scoring output and preserves deterministic sort keys for Phase 4 unit/static scope.
- `score_total` is deterministic `WEIGHTED_MEAN` over momentum, breakout, volume, and risk components.
- Component scores and total score are clamped to `0..1`.
- Ranking sort keys are deterministic: `score_total_desc`, `score_breakout_desc`, `score_momentum_desc`, `dv20_idr_desc`, `atr14_pct_asc`, `ticker_id_asc`.
- PLAN grouping deduplicates duplicate `ticker_id` by deterministic best item before active group assignment.
- Contract is not `LOCKED` because there is no command/API runtime proof and no persisted artifact/log output yet.

Acceptance criteria:

- Same publication input + same paramset + same universe produces the same score and ranking.
- Tie-breaking is deterministic.
- Tests cover deterministic scoring output and deterministic PLAN grouping output.

Last update:
`2026-06-05 — WATCHLIST — PLAN GROUPING + TOP_PICKS / SECONDARY SELECTION EXECUTION SESSION`

---

## WL-CONTRACT-007 — PARAMSET TRACEABILITY CONTRACT

Contract ID:
`WL-CONTRACT-007`

Title:
`PARAMSET TRACEABILITY CONTRACT`

Status:
`DONE for published-price runtime paramset traceability scope / NOT LOCKED`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/04_WS_PARAMSET_JSON_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/05_WS_PARAMETER_REGISTRY_COMPLETE.md`
- `docs/watchlist/system/policies/_shared/02_PARAMSET_CONTRACT_GLOBAL.md`
- `docs/watchlist/system/policies/weekly_swing/20_WS_CANONICAL_PARAMSET_PROCEDURES.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php`
- `app/Application/Watchlist/Services/WatchlistScoringService.php`
- `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php`
- `app/Application/Watchlist/Services/WatchlistRecommendationService.php`

Tests:

- `tests/Unit/Watchlist/WatchlistCandidateUniverseServiceTest.php`
- `tests/Unit/Watchlist/WatchlistCandidateUniverseStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistScoringServiceTest.php`
- `tests/Unit/Watchlist/WatchlistScoringStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingServiceTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistRecommendationServiceTest.php`
- `tests/Unit/Watchlist/WatchlistRecommendationStaticGuardTest.php`

Runtime proof:
`LOCAL_RUNTIME_PROOF_PASS — final command artifacts carry resolved canonical eval thresholds, effective dynamic coverage threshold, policy/paramset snapshot, and deterministic hash; broader promotion/persistence governance remains outside this scope.`

Current gaps:

- Final closure note: final operator command proof resolved all required eval thresholds (`min_trades=120`, effective `min_days_covered=4` for the five-day window) and recorded them in the artifact; no unresolved-threshold export occurred.

- Candidate universe records canonical policy/paramset labels: `policy_code`, `policy_version`, and `paramset_code`.
- Scoring output records canonical policy/paramset labels plus `paramset_snapshot`.
- PLAN grouping output records canonical policy/paramset labels plus `paramset_snapshot.grouping`.
- Recommendation output records canonical policy/paramset labels plus `paramset_snapshot.recommendation` and `source_plan_reference`.
- Current bootstrap labels intentionally do not use `_V1` suffix because the watchlist application does not have formal app/runtime versioning yet.
- Candidate universe accepts nested `{ value: ... }` paramset shape for the gate fields it owns.
- Scoring accepts nested `{ value: ... }` weight shape for the fields it owns and rejects invalid weights.
- PLAN grouping accepts nested `{ value: ... }` grouping threshold/limit shape for the fields it owns and rejects invalid threshold/limit contracts.
- Recommendation accepts nested `{ value: ... }` recommendation threshold/limit shape for the fields it owns and rejects invalid recommendation threshold/limit contracts.
- Candidate universe rejects invalid ATR percent-point units above `1.0`.
- Scoring rejects candidate ATR unit drift above `1.0`.
- Full runtime paramset loader/validator, persistence, hash, promotion, and artifact recording are still not implemented.

Acceptance criteria:

- Every scoring/recommendation/backtest execution has traceable policy/paramset identity.
- Paramset validation rejects missing, unknown, invalid, or type-drifted fields.
- Artifact output records policy/paramset identity and hash when runtime artifacts are introduced.

Last update:
`2026-06-09 — WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF FINAL CLOSURE`

---

## WL-CONTRACT-008 — SIGNAL EXPLAINABILITY CONTRACT

Contract ID:
`WL-CONTRACT-008`

Title:
`SIGNAL EXPLAINABILITY CONTRACT`

Status:
`DONE for published-price runtime explainability scope / NOT LOCKED`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/07_WS_REASON_CODES_AND_HASH.md`
- `docs/watchlist/system/policies/weekly_swing/08_WS_PLAN_ALGORITHM.md`
- `docs/watchlist/system/policies/weekly_swing/22_WS_RECOMMENDATION_OVERVIEW.md`
- `docs/watchlist/system/policies/weekly_swing/23_WS_RECOMMENDATION_INPUT_OUTPUT_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/25_WS_RECOMMENDATION_REASON_CODES_AND_TESTS.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistScoringService.php`
- `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php`
- `app/Application/Watchlist/Services/WatchlistRecommendationService.php`
- `app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestRuntimeArtifactService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestPublishedPriceRuntimeService.php`
- `app/Application/MarketData/Services/MarketDataTradingCalendarReadService.php`
- `app/Application/MarketData/Services/MarketDataPublishedEodSeriesReadService.php`
- `app/Infrastructure/Persistence/MarketData/MarketDataPublishedEodSeriesReadRepository.php`
- `app/Console/Commands/Watchlist/RunBacktestPublishedPriceProofCommand.php`

Tests:

- `tests/Unit/Watchlist/WatchlistScoringServiceTest.php`
- `tests/Unit/Watchlist/WatchlistScoringStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingServiceTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistRecommendationServiceTest.php`
- `tests/Unit/Watchlist/WatchlistRecommendationStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistConfirmOverlayServiceTest.php`
- `tests/Unit/Watchlist/WatchlistConfirmOverlayStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestMetricsServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceProofCommandTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeStaticGuardTest.php`
- `tests/Unit/MarketData/MarketDataTradingCalendarReadModelTest.php`
- `tests/Unit/MarketData/MarketDataPublishedEodSeriesReadModelTest.php`

Runtime proof:
`LOCAL_RUNTIME_PROOF_PASS — official command diagnostics and artifacts explain publication lineage, zero-volume non-tradable entry/exit behavior, skipped evaluations, metrics, and validation state.`

Current gaps:

- Final closure note: final diagnostics proved BKDP `BT_SKIP_NO_TRADABLE_ENTRY` with `entry_volume=0` and KING `BT_SKIP_MISSING_OHLC_EXIT` with ignored zero-volume dates; no synthetic fill or zero return was created.

- PLAN scoring explainability exists via `score_components`, `score_weights`, `factor_breakdown`, and `reason_codes`.
- PLAN grouping explainability exists via `group_reason_code`, augmented `reason_codes`, `group_contract`, `paramset_snapshot.grouping`, and summary counts.
- Recommendation explainability exists via `recommendation_label`, `recommended_flag`, `recommendation_score`, `recommendation_rank`, `reason_codes`, `recommendation_contract`, `source_plan_reference`, and summary counts.
- Explainability reason codes used by scoring are traceable to Weekly Swing owner docs / reason seed.
- PLAN grouping reason codes `WS_PLAN_TOP_PICK`, `WS_PLAN_SECONDARY`, `WS_PLAN_WATCH_ONLY`, `WS_PLAN_AVOID_LOW_SCORE`, and `WS_PLAN_AVOID_EXCLUDED` are traceable to Weekly Swing reason-code docs / support seed.
- Recommendation reason codes `WS_REC_SELECTED`, `WS_REC_NOT_SELECTED`, `WS_REC_BORDERLINE`, `WS_REC_EMPTY_SET`, `WS_REC_RANK_OUTSIDE_DYNAMIC_TARGET`, `WS_REC_CAPITAL_AWARE`, `WS_REC_CAPITAL_INSUFFICIENT`, and `WS_REC_MIN_LOT_NOT_AFFORDABLE` are traceable to Weekly Swing owner docs / support seed.
- Contract is not `LOCKED` because official command/database runtime proof, current-patch PHPUnit execution, and production persisted artifact/log evidence remain incomplete.
- Confirm overlay output adds reason-coded `confirm_reason_codes` and preserves recommendation reason-code separation at unit/static scope.
- Backtest foundation output adds reason-coded diagnostics/evaluations, `source_contract`, `backtest_contract`, `paramset_snapshot`, `replay_window`, `summary`, and `artifact_manifest` at service + unit/static scope.
- Historical local PHPUnit baseline remains green: `WatchlistBacktest` 25/286, full Watchlist 116/1168, and `MarketDataWatchlistReadModelTest` 3/41. Current published-price tests are authored and lint-clean but were not executed because sandbox PHPUnit lacks required extensions.
- Published-price evidence now carries exact-date publication/run lineage, calendar/price manifests, evaluation reason codes, and deterministic artifact hash.

Acceptance criteria:

- Every signal/recommendation has explainable reason/factor output.
- Output includes enough factor breakdown to audit why a ticker is included, watched, avoided, or rejected.

Last update:
`2026-06-09 — WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF FINAL CLOSURE`

---

## WL-CONTRACT-009 — BACKTEST NO-LOOKAHEAD CONTRACT

Contract ID:
`WL-CONTRACT-009`

Title:
`BACKTEST NO-LOOKAHEAD CONTRACT`

Status:
`DONE for published-price no-lookahead runtime proof scope / NOT LOCKED`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/12_WS_BACKTEST_SCHEMA_AND_CALIBRATION.md`
- `docs/watchlist/system/policies/weekly_swing/14_WS_BT_COVERAGE_MATRIX_LOCKED.md`
- `docs/watchlist/system/policies/weekly_swing/17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md`
- `docs/watchlist/system/policies/weekly_swing/18_WS_BACKTEST_ARTIFACT_MANIFEST_LOCKED.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestRuntimeArtifactService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestPublishedPriceRuntimeService.php`
- `app/Application/MarketData/Services/MarketDataTradingCalendarReadService.php`
- `app/Application/MarketData/Services/MarketDataPublishedEodSeriesReadService.php`
- `app/Infrastructure/Persistence/MarketData/MarketDataPublishedEodSeriesReadRepository.php`
- `app/Console/Commands/Watchlist/RunBacktestPublishedPriceProofCommand.php`

Tests:

- `tests/Unit/Watchlist/WatchlistBacktestStrategyServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestMetricsServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceProofCommandTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeStaticGuardTest.php`
- `tests/Unit/MarketData/MarketDataTradingCalendarReadModelTest.php`
- `tests/Unit/MarketData/MarketDataPublishedEodSeriesReadModelTest.php`

Runtime proof:
`LOCAL_RUNTIME_PROOF_PASS — strategy output is frozen before future-price reads, exact-date readable publications are used, future prices remain evaluation-only, and the final operator replay passed.`

Current gaps:

- Final closure note: final two-run proof used explicit replay dates and publication/calendar lineage; zero-volume handling remained evaluation-only and did not mutate PLAN, RECOMMENDATION, or CONFIRM.

- Backtest foundation service exists at unit/static scope and runtime artifact/metrics foundation now exists at service scope.
- Historical local PHPUnit baseline remains green. Current published-price regression tests were attempted but could not start because sandbox PHPUnit lacks `dom`, `mbstring`, `xml`, and `xmlwriter`.
- No-lookahead guard exists for future-effective source output.
- Controlled proof freezes and hashes PLAN/recommendation trade candidates before any D+1..D+5 price read; the post-read hash remains identical and future-effective strategy input fails closed.
- Service consumes existing PLAN/recommendation/confirm output layers and does not read raw market-data.
- Contract is not `LOCKED` because official command/database proof, current-patch PHPUnit regression, owner exit-model conflict resolution, production operating evidence, and OOS proof remain incomplete.

Acceptance criteria:

- Backtest never uses future publication, future indicator, future eligibility, future price, or future outcome to make historical decisions.
- Tests include lookahead guard cases.

Last update:
`2026-06-09 — WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF FINAL CLOSURE`

---

## WL-CONTRACT-010 — BACKTEST REPRODUCIBILITY CONTRACT

Contract ID:
`WL-CONTRACT-010`

Title:
`BACKTEST REPRODUCIBILITY CONTRACT`

Status:
`DONE for published-price deterministic runtime reproducibility scope / NOT LOCKED`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/12_WS_BACKTEST_SCHEMA_AND_CALIBRATION.md`
- `docs/watchlist/system/policies/weekly_swing/18_WS_BACKTEST_ARTIFACT_MANIFEST_LOCKED.md`
- `docs/watchlist/system/policies/weekly_swing/20_WS_CANONICAL_PARAMSET_PROCEDURES.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestRuntimeArtifactService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestPublishedPriceRuntimeService.php`
- `app/Application/MarketData/Services/MarketDataTradingCalendarReadService.php`
- `app/Application/MarketData/Services/MarketDataPublishedEodSeriesReadService.php`
- `app/Infrastructure/Persistence/MarketData/MarketDataPublishedEodSeriesReadRepository.php`
- `app/Console/Commands/Watchlist/RunBacktestPublishedPriceProofCommand.php`

Tests:

- `tests/Unit/Watchlist/WatchlistBacktestStrategyServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestMetricsServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceProofCommandTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeStaticGuardTest.php`
- `tests/Unit/MarketData/MarketDataTradingCalendarReadModelTest.php`
- `tests/Unit/MarketData/MarketDataPublishedEodSeriesReadModelTest.php`

Runtime proof:
`LOCAL_RUNTIME_PROOF_PASS — two final official command runs with identical canonical inputs produced canonical artifact hash `0eaa353d20df901c4f372c0000951408578bf302` in both runs.`

Current gaps:

- Final closure note: file SHA-1 differed only because output path/execution metadata are intentionally non-hashed; canonical hash equality passed.

- Explicit replay-window normalization and deterministic output ordering exist at service + unit/static scope.
- Source publication/run metadata is preserved in foundation output.
- Official artifact-manifest references are present.
- Runtime artifact service adds deterministic `input_manifest`, `validation.artifact_hash`, and JSON export foundation.
- Metrics service is deterministic for identical payload + explicit price/calendar input and fails safe when official inputs are missing.
- Historical local PHPUnit baseline remains green; current-patch PHPUnit is blocked before discovery by missing sandbox extensions.
- Controlled canonical hash equality is proven as `bb2268bbc053d7aa85fd5a400e834c519cfd3429` across two runs. Contract is not `LOCKED` because official command/database replay, current-patch PHPUnit, production persisted evidence, and OOS proof are not complete.

Acceptance criteria:

- Backtest can be replayed with the same dataset identity, publication scope, paramset identity, universe, date range, and artifact manifest.
- Replayed result matches expected metrics and output contract.

Last update:
`2026-06-09 — WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF FINAL CLOSURE`

---

## WL-CONTRACT-011 — RISK GATE CONTRACT

Contract ID:
`WL-CONTRACT-011`

Title:
`RISK GATE CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/04_WS_PARAMSET_JSON_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/05_WS_PARAMETER_REGISTRY_COMPLETE.md`
- `docs/watchlist/system/policies/weekly_swing/08_WS_PLAN_ALGORITHM.md`
- `docs/watchlist/system/policies/weekly_swing/09_WS_DYNAMIC_SELECTION_DETERMINISTIC.md`
- `docs/watchlist/system/policies/weekly_swing/15_WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md`
- `docs/watchlist/system/policies/weekly_swing/22_WS_RECOMMENDATION_OVERVIEW.md`
- `docs/watchlist/system/policies/weekly_swing/24_WS_RECOMMENDATION_ALGORITHM.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php`
- `app/Application/Watchlist/Services/WatchlistScoringService.php`

Tests:

- `tests/Unit/Watchlist/WatchlistCandidateUniverseServiceTest.php`
- `tests/Unit/Watchlist/WatchlistCandidateUniverseStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistScoringServiceTest.php`
- `tests/Unit/Watchlist/WatchlistScoringStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Runtime risk/liquidity/volume gate exists at service + unit/static test level.
- Scoring risk/volume quality components now exist at service + unit/static scope.
- PLAN grouping consumes scored risk/volume-aware output without rewriting risk/liquidity formulas.
- Guards implemented: `dv20_idr >= min_dv20_idr`, `atr14_pct >= min_atr14_pct`, `atr14_pct <= max_atr14_pct`, `vol_ratio >= min_vol_ratio`.
- Canonical rejection reason priority implemented: `WS_DATA_MISSING`, `WS_LIQ_FAIL`, `WS_ATR_LOW`, `WS_ATR_HIGH`, `WS_VOLR_FAIL`.
- Explainable row output includes `required_ok`, `guard_ok`, `eligible_plan`, `canonical_fail_reason_code`, `reason_codes`, `missing_fields`, `gate_metrics`, and `gate_thresholds`.
- Scoring output includes risk factor breakdown and rejects ATR unit drift above `1.0`.
- PLAN grouping keeps low-score candidates in diagnostics `AVOID` and prevents scoring exclusions from entering active PLAN groups.
- Contract is not `LOCKED` because no command/API runtime proof, artifact output, backtest equivalence proof, or persisted universe snapshot exists yet.

Acceptance criteria:

- Watchlist does not rank only potential return.
- Candidate selection includes risk, liquidity, volatility, and guard failure handling.
- Risk gate output is explainable.
- Production PLAN universe and future backtest universe can compare pass/fail + reason using canonical fields.

Last update:
`2026-06-05 — WATCHLIST — PLAN GROUPING + TOP_PICKS / SECONDARY SELECTION EXECUTION SESSION`

---

## WL-CONTRACT-012 — PORTFOLIO AWARENESS BOUNDARY

Contract ID:
`WL-CONTRACT-012`

Title:
`PORTFOLIO AWARENESS BOUNDARY`

Status:
`NOT_STARTED`

Owner docs:

- `docs/watchlist/README.md`
- `docs/watchlist/audit/WATCHLIST_SCOPE_LOCK.md`
- `docs/watchlist/system/policy.md`
- `docs/watchlist/system/policies/weekly_swing/22_WS_RECOMMENDATION_OVERVIEW.md`
- `docs/watchlist/system/policies/weekly_swing/23_WS_RECOMMENDATION_INPUT_OUTPUT_CONTRACT.md`

Implementation files:
`NOT_STARTED`

Tests:
`NOT_STARTED`

Runtime proof:
`NOT_STARTED`

Current gaps:

- No portfolio-aware integration exists.

Acceptance criteria:

- Portfolio integration does not alter market-data.
- Clear boundary exists between signal, position awareness, and execution decision.
- Watchlist remains suggestion-only and does not execute orders.

Last update:
`2026-05-28`

---

## WL-CONTRACT-013 — AUDIT ARTIFACT CONTRACT

Contract ID:
`WL-CONTRACT-013`

Title:
`AUDIT ARTIFACT CONTRACT`

Status:
`DONE for deterministic JSON runtime artifact evidence scope / NOT LOCKED`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/07_WS_REASON_CODES_AND_HASH.md`
- `docs/watchlist/system/policies/weekly_swing/18_WS_BACKTEST_ARTIFACT_MANIFEST_LOCKED.md`
- `docs/watchlist/system/implementation/weekly_swing/03_WS_RUNTIME_ARTIFACT_FLOW.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestRuntimeArtifactService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestPublishedPriceRuntimeService.php`
- `app/Application/MarketData/Services/MarketDataTradingCalendarReadService.php`
- `app/Application/MarketData/Services/MarketDataPublishedEodSeriesReadService.php`
- `app/Infrastructure/Persistence/MarketData/MarketDataPublishedEodSeriesReadRepository.php`
- `app/Console/Commands/Watchlist/RunBacktestPublishedPriceProofCommand.php`

Tests:

- `tests/Unit/Watchlist/WatchlistBacktestStrategyServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestMetricsServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceProofCommandTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeStaticGuardTest.php`
- `tests/Unit/MarketData/MarketDataTradingCalendarReadModelTest.php`
- `tests/Unit/MarketData/MarketDataPublishedEodSeriesReadModelTest.php`

Runtime proof:
`LOCAL_RUNTIME_PROOF_PASS — two official JSON artifacts were exported with calendar, price, publication, paramset, metrics, diagnostics, validation, and deterministic canonical hash evidence.`

Current gaps:

- Final closure note: production persisted artifact tables and production operating retention remain outside this proof scope; JSON evidence does not create a shadow official artifact.

- Backtest foundation output includes `artifact_manifest` with official Weekly Swing artifact names.
- Historical local PHPUnit baseline remains green; current-patch PHPUnit execution is blocked by missing sandbox extensions.
- Runtime artifact service now creates deterministic artifact shape, `input_manifest`, `metrics`, `validation`, `artifact_hash`, and JSON export foundation.
- Runtime production persistence remains explicitly `false`. A command surface now exists and is registered, but Artisan startup is blocked by the project PHP-version guard in this sandbox; controlled service artifacts are evidence only and do not become new official manifest artifacts.
- Contract is not `LOCKED` because official command/database runtime proof, current-patch PHPUnit, and persisted production runtime artifact/log evidence are not available.

Acceptance criteria:

- Every important watchlist run produces traceable artifact/log.
- Artifact records publication, paramset, universe, result, reason code/factor output, and validation status.

Last update:
`2026-06-09 — WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF FINAL CLOSURE`

---

## WL-CONTRACT-014 — DOCS SYNC CONTRACT

Contract ID:
`WL-CONTRACT-014`

Title:
`DOCS SYNC CONTRACT`

Status:
`DONE for final published-price runtime proof docs sync scope`

Owner docs:

- `docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md`
- `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md`
- `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md`
- `docs/watchlist/audit/README.md`
- `docs/watchlist/audit/WATCHLIST_OWNER_MATRIX.md`

Implementation files:

- `tests/Unit/Watchlist/WatchlistAuditGovernanceStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistCandidateUniverseStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistScoringStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistRecommendationStaticGuardTest.php`
- `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md`
- `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md`
- `docs/watchlist/system/policies/weekly_swing/07_WS_REASON_CODES_AND_HASH.md`
- `docs/watchlist/system/policies/weekly_swing/25_WS_RECOMMENDATION_REASON_CODES_AND_TESTS.md`
- `docs/watchlist/system/policies/weekly_swing/db/REASON_CODES_SEED.sql`
- `app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestRuntimeArtifactService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestPublishedPriceRuntimeService.php`
- `app/Application/MarketData/Services/MarketDataTradingCalendarReadService.php`
- `app/Application/MarketData/Services/MarketDataPublishedEodSeriesReadService.php`
- `app/Infrastructure/Persistence/MarketData/MarketDataPublishedEodSeriesReadRepository.php`
- `app/Console/Commands/Watchlist/RunBacktestPublishedPriceProofCommand.php`

Tests:

- `WatchlistAuditGovernanceStaticGuardTest` added for initial docs guard.
- `WatchlistMarketDataConsumerReadModelStaticGuardTest` added for Phase 1 docs/code synchronization guard.
- `WatchlistScoringStaticGuardTest` added for Phase 3 docs/code synchronization guard.
- `WatchlistPlanGroupingStaticGuardTest` added for Phase 4 docs/code synchronization guard.
- `WatchlistRecommendationStaticGuardTest` added for Phase 5 docs/code synchronization guard.
- `tests/Unit/Watchlist/WatchlistConfirmOverlayServiceTest.php`
- `tests/Unit/Watchlist/WatchlistConfirmOverlayStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestMetricsServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceProofCommandTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeStaticGuardTest.php`
- `tests/Unit/MarketData/MarketDataTradingCalendarReadModelTest.php`
- `tests/Unit/MarketData/MarketDataPublishedEodSeriesReadModelTest.php`

Runtime proof:
`PASS — implementation status and contract tracker now record final PHPUnit, command, canonical hash, dynamic coverage, threshold, zero-volume diagnostic, remaining OOS gap, and `NOT_PRODUCTION_READY` status.`

Current gaps:

- Final closure note: earlier references to a required closure/coverage rerun are historical and superseded by the final closure update appended to both trackers.

- Phase 1 code/test/docs sync completed for market-data consumer read model.
- Phase 2 code/test/docs sync completed for candidate universe.
- Phase 3 code/test/docs sync completed for scoring foundation.
- Phase 4 code/test/docs sync completed for PLAN grouping foundation.
- Phase 5 code/test/docs sync completed for final recommendation foundation.
- Current docs synchronization scope is DONE. The contract remains not `LOCKED` because official command/database proof, current-patch PHPUnit, and production persistence/operating evidence remain incomplete.

- Docs sync foundation exists, but future code/config/schema/test/runtime changes still need ongoing enforcement.
- The command surface now exists and is documented; no API or production persistence surface was added. Official command execution is blocked by sandbox PHP `8.4.16`.
- Phase 6 confirm overlay service, tests, reason-code docs, and Lumen tracker/status docs are synchronized for unit/static scope.
- Phase 7 backtest strategy service, tests, static guard, and Lumen tracker/status docs are synchronized for unit/static scope.
- Runtime artifact/metrics docs, tests, and Lumen audit trackers are synchronized for unit/static scope.
- Historical local PHPUnit baseline remains green. Current patch has 17 lint-clean PHP files and zero grouped static validation failures; new PHPUnit tests remain unexecuted in this sandbox.

Acceptance criteria:

- Every watchlist code/config/schema/test/behavior change updates implementation status and contract tracker.
- Active session name is aligned between status and tracker.
- Tracker contracts reflect actual code/test/runtime status without overclaim.

Last update:
`2026-06-09 — WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF FINAL CLOSURE`

---

## WL-CONTRACT-015 — PRODUCTION READINESS CONTRACT

Contract ID:
`WL-CONTRACT-015`

Title:
`PRODUCTION READINESS CONTRACT`

Status:
`PARTIAL / NOT_READY`

Owner docs:

- `docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md`
- `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md`
- `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md`
- `docs/watchlist/system/policy.md`
- `docs/watchlist/system/policies/weekly_swing/**`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php`
- `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php`
- `app/Application/Watchlist/Services/WatchlistScoringService.php`
- `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php`
- `app/Application/Watchlist/Services/WatchlistRecommendationService.php`
- `app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestRuntimeArtifactService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestPublishedPriceRuntimeService.php`
- `app/Application/MarketData/Services/MarketDataTradingCalendarReadService.php`
- `app/Application/MarketData/Services/MarketDataPublishedEodSeriesReadService.php`
- `app/Infrastructure/Persistence/MarketData/MarketDataPublishedEodSeriesReadRepository.php`
- `app/Console/Commands/Watchlist/RunBacktestPublishedPriceProofCommand.php`

Tests:

- watchlist read model unit/static tests
- watchlist candidate universe unit/static tests
- watchlist scoring unit/static tests
- watchlist PLAN grouping unit/static tests
- watchlist recommendation unit/static tests
- `tests/Unit/Watchlist/WatchlistConfirmOverlayServiceTest.php`
- `tests/Unit/Watchlist/WatchlistConfirmOverlayStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestMetricsServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceProofCommandTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeStaticGuardTest.php`
- `tests/Unit/MarketData/MarketDataTradingCalendarReadModelTest.php`
- `tests/Unit/MarketData/MarketDataPublishedEodSeriesReadModelTest.php`

Runtime proof:
`PARTIAL — published-price runtime proof, final PHPUnit, deterministic JSON artifacts, threshold binding, coverage, and zero-volume diagnostics pass; walk-forward/OOS and production operating proof remain unavailable.`

Current gaps:

- Final closure note: published-price runtime proof no longer blocks the next session, but no production-ready claim is allowed because OOS, production operations, and remaining contract lock evidence are incomplete.

- Read model, candidate universe, scoring foundation, PLAN grouping foundation, recommendation foundation, confirm overlay foundation, and backtest strategy foundation exist at unit/static/smoke scope.
- The proof command is implemented and registered without scheduler, but official Artisan/database execution is blocked in this sandbox; no API endpoint exists.
- Runtime-safe artifact shaping and JSON export foundation exist, but production persisted artifact/log evidence is not available.
- No API endpoint exists.
- Watchlist command surface `watchlist:backtest-published-price-proof` exists; no successful official command evidence is claimed.
- No production watchlist schema/migration exists.
- Backtest strategy, runtime artifact, and metrics foundations retain historical local PHPUnit proof. Published-price orchestration and controlled deterministic evidence now exist, but official integration-database command proof, production runtime persistence, and OOS proof do not.
- Core contracts are not `LOCKED` because official command/database runtime proof, current-patch PHPUnit, OOS proof, and production persisted artifact/log evidence are missing.
- Historical local validation remains PASS at 25/286, 116/1168, and 3/41. Current patch validation is limited to lint/static and controlled service smokes because PHPUnit/Artisan cannot start in this sandbox.
- Production readiness remains `NO`; no successful official command/database proof, API, OOS proof, production persistence, or production operating proof exists.

Acceptance criteria:

- Market-data consumer read model locked.
- No raw/latest/`MAX(date)` bypass.
- Required indicator and eligibility guards locked.
- Scoring deterministic and explainable.
- PLAN grouping deterministic and explainable.
- Paramset identity traceable.
- Recommendation output tested.
- Recommendation source is PLAN-only and empty recommendation is valid.
- Backtest no-lookahead and reproducibility have unit/static proof; runtime replay/artifact proof is still required before lock.
- Risk gates present.
- Artifact/log proof present.
- Full watchlist test suite passes.
- Runtime command/API proof passes.
- Docs sync complete.

Last update:
`2026-06-09 — WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF FINAL CLOSURE`

---

## WL-CONTRACT-016 — PLAN GROUPING DETERMINISM CONTRACT

Contract ID:
`WL-CONTRACT-016`

Title:
`PLAN GROUPING DETERMINISM CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/08_WS_PLAN_ALGORITHM.md`
- `docs/watchlist/system/policies/weekly_swing/09_WS_DYNAMIC_SELECTION_DETERMINISTIC.md`
- `docs/watchlist/system/policies/weekly_swing/04_WS_PARAMSET_JSON_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/05_WS_PARAMETER_REGISTRY_COMPLETE.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php`
- upstream source: `app/Application/Watchlist/Services/WatchlistScoringService.php`

Tests:

- `tests/Unit/Watchlist/WatchlistPlanGroupingServiceTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- PLAN grouping service exists for Phase 4 unit/static scope.
- `TOP_PICKS`, `SECONDARY`, `WATCH_ONLY`, and diagnostics `AVOID` are formed deterministically from Phase 3 scored output.
- Default bootstrap thresholds and limits are validated: top `0.70/5`, secondary `0.55/10`, watch-only `0.40/20`, avoid below `0.40`.
- Sort keys follow Phase 3 scoring contract: `score_total_desc`, `score_breakout_desc`, `score_momentum_desc`, `dv20_idr_desc`, `atr14_pct_asc`, `ticker_id_asc`.
- Duplicate `ticker_id` is resolved by deterministic best item.
- Contract is not `LOCKED` because there is no command/API runtime proof and no artifact/log output yet.

Acceptance criteria:

- Same scored input + same grouping paramset produces identical PLAN groups.
- Active PLAN groups do not depend on input array order.
- Duplicate ticker IDs do not enter more than one active PLAN group.
- Overflow from TOP_PICKS and SECONDARY follows deterministic threshold/limit rules.

Last update:
`2026-06-05 — WATCHLIST — PLAN GROUPING + TOP_PICKS / SECONDARY SELECTION EXECUTION SESSION`

---

## WL-CONTRACT-017 — PLAN GROUP BOUNDARY CONTRACT

Contract ID:
`WL-CONTRACT-017`

Title:
`PLAN GROUP BOUNDARY CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/01_WS_OVERVIEW.md`
- `docs/watchlist/system/policies/weekly_swing/02_WS_CANONICAL_RUNTIME_FLOW.md`
- `docs/watchlist/system/policies/weekly_swing/08_WS_PLAN_ALGORITHM.md`
- `docs/watchlist/system/policies/weekly_swing/09_WS_DYNAMIC_SELECTION_DETERMINISTIC.md`
- `docs/watchlist/system/policies/weekly_swing/10_WS_CONFIRM_OVERLAY.md`
- `docs/watchlist/system/policies/weekly_swing/22_WS_RECOMMENDATION_OVERVIEW.md`
- `docs/watchlist/system/policies/weekly_swing/23_WS_RECOMMENDATION_INPUT_OUTPUT_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/24_WS_RECOMMENDATION_ALGORITHM.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php`
- `app/Application/Watchlist/Services/WatchlistRecommendationService.php`
- `app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php`

Tests:

- `tests/Unit/Watchlist/WatchlistPlanGroupingServiceTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistRecommendationServiceTest.php`
- `tests/Unit/Watchlist/WatchlistRecommendationStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistConfirmOverlayServiceTest.php`
- `tests/Unit/Watchlist/WatchlistConfirmOverlayStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Confirm overlay foundation now consumes active PLAN candidate binding from `WatchlistPlanGroupingService`.
- Recommended PLAN candidates and non-recommended active PLAN candidates can receive CONFIRM overlay.
- Unknown/non-active candidate evidence is rejected into diagnostics/excluded output.
- Confirm overlay does not mutate recommendation membership, rank, score, label, or hash at unit/static scope.
- Contract is not `LOCKED` because there is no command/API runtime proof and no artifact/log output yet.

Acceptance criteria:

- PLAN grouping consumes `WatchlistScoringService` only.
- PLAN grouping does not create recommendation labels, confirm state, order/execution actions, portfolio allocation, or backtest metrics.
- `AVOID` remains diagnostics and must not be interpreted as sell recommendation or execution instruction.
- Recommendation layer must consume PLAN grouping output without mutating PLAN group membership.
- Confirm overlay binds to candidate PLAN without mutating recommendation membership, rank, score, label, or hash.

Last update:
`2026-06-05 — WATCHLIST — CONFIRM OVERLAY FOUNDATION EXECUTION SESSION`

---

## WL-CONTRACT-018 — RECOMMENDATION PLAN-SOURCE CONTRACT

Contract ID:
`WL-CONTRACT-018`

Title:
`RECOMMENDATION PLAN-SOURCE CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/01_WS_OVERVIEW.md`
- `docs/watchlist/system/policies/weekly_swing/02_WS_CANONICAL_RUNTIME_FLOW.md`
- `docs/watchlist/system/policies/weekly_swing/22_WS_RECOMMENDATION_OVERVIEW.md`
- `docs/watchlist/system/policies/weekly_swing/23_WS_RECOMMENDATION_INPUT_OUTPUT_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/24_WS_RECOMMENDATION_ALGORITHM.md`
- `docs/watchlist/system/policies/weekly_swing/25_WS_RECOMMENDATION_REASON_CODES_AND_TESTS.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistRecommendationService.php`
- upstream source: `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php`
- `app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php`

Tests:

- `tests/Unit/Watchlist/WatchlistRecommendationServiceTest.php`
- `tests/Unit/Watchlist/WatchlistRecommendationStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistConfirmOverlayServiceTest.php`
- `tests/Unit/Watchlist/WatchlistConfirmOverlayStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Recommendation service still does not read CONFIRM output.
- Confirm overlay consumes recommendation output only as immutable membership snapshot after recommendation has already been produced from PLAN.
- Confirm overlay does not add ticker into recommendation membership and does not remove ticker from recommendation membership.
- Confirm overlay preserves source PLAN trade date, publication, run, policy, and paramset identity in output.
- Contract is not `LOCKED` because there is no command/API runtime proof and no persisted artifact/log output yet.

Acceptance criteria:

- Recommendation output never adds ticker from outside PLAN source groups.
- Recommendation can be produced without CONFIRM.
- CONFIRM fields do not become source inputs for recommendation.
- Recommendation metadata preserves source PLAN trade date, publication, run, policy, and paramset identity.

Last update:
`2026-06-05 — WATCHLIST — CONFIRM OVERLAY FOUNDATION EXECUTION SESSION`

---

## WL-CONTRACT-019 — RECOMMENDATION DETERMINISM AND EMPTY-SET CONTRACT

Contract ID:
`WL-CONTRACT-019`

Title:
`RECOMMENDATION DETERMINISM AND EMPTY-SET CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/13_WS_CONTRACT_TEST_CHECKLIST.md`
- `docs/watchlist/system/policies/weekly_swing/22_WS_RECOMMENDATION_OVERVIEW.md`
- `docs/watchlist/system/policies/weekly_swing/23_WS_RECOMMENDATION_INPUT_OUTPUT_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/24_WS_RECOMMENDATION_ALGORITHM.md`
- `docs/watchlist/system/policies/weekly_swing/25_WS_RECOMMENDATION_REASON_CODES_AND_TESTS.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistRecommendationService.php`
- `app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php`

Tests:

- `tests/Unit/Watchlist/WatchlistRecommendationServiceTest.php`
- `tests/Unit/Watchlist/WatchlistRecommendationStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistConfirmOverlayServiceTest.php`
- `tests/Unit/Watchlist/WatchlistConfirmOverlayStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Recommendation deterministic and empty-set behavior remains owned by `WatchlistRecommendationService`.
- Confirm overlay foundation proves confirm evidence does not mutate `recommended_flag`, `recommendation_rank`, `recommendation_score`, `recommendation_label`, or available hash fields.
- Empty recommendation does not block CONFIRM eligibility for active PLAN candidates when PLAN candidates exist.
- Contract is not `LOCKED` because there is no command/API runtime proof, artifact hash, or persisted replay evidence yet.

Acceptance criteria:

- Same PLAN output + same recommendation paramset + same capital input produces identical recommendation output.
- Empty recommendation is a valid output, not an error.
- Dynamic recommendation count is algorithmic and may be zero.
- Capital-aware replay is deterministic for identical explicit capital input.

Last update:
`2026-06-05 — WATCHLIST — CONFIRM OVERLAY FOUNDATION EXECUTION SESSION`


## Phase 7 Local Validation Update — 2026-06-08

Session:
`WATCHLIST — BACKTEST STRATEGY ENGINE FOUNDATION EXECUTION SESSION`

Status:
`PHASE_7_BACKTEST_STRATEGY_ENGINE_FOUNDATION_DONE / NOT_PRODUCTION_READY`.

Evidence:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestStrategy"
OK (13 tests, 152 assertions)

vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataWatchlistReadModelTest"
OK (3 tests, 41 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (104 tests, 1034 assertions)
```

Contract impact:

- `WL-CONTRACT-008` is DONE for unit/static explainability scope.
- `WL-CONTRACT-009` is DONE for unit/static no-lookahead foundation scope.
- `WL-CONTRACT-010` is DONE for unit/static reproducibility foundation scope.
- `WL-CONTRACT-013` is DONE for unit/static artifact-manifest foundation scope.
- `WL-CONTRACT-014` is DONE for Phase 7 docs sync unit/static scope.
- `WL-CONTRACT-015` remains `PARTIAL / NOT_READY`.

No Phase 7 contract is moved to `LOCKED` because command/API runtime proof, persisted artifact/log evidence, completed pricing metric engine, production schema, and walk-forward/OOS proof do not exist yet.



## Runtime Artifact and Metrics Contract Update — 2026-06-08

Session:
`WATCHLIST — BACKTEST RUNTIME ARTIFACT AND METRICS EXECUTION SESSION`

Status:
`DONE for runtime artifact and metrics foundation unit/static scope / NOT_PRODUCTION_READY`.

Priority contract impact:

- `WL-CONTRACT-008`: explainability extended through artifact diagnostics, metric diagnostics, and reason-code distribution.
- `WL-CONTRACT-009`: no-lookahead boundary preserved; metrics only uses explicit replay trade dates, explicit calendar input, and published EOD price series input.
- `WL-CONTRACT-010`: reproducibility improved with deterministic artifact hash, source payload hash, stable JSON encoding, and deterministic metrics aggregation.
- `WL-CONTRACT-013`: runtime artifact shape now exists at service level with official manifest references and JSON export foundation.
- `WL-CONTRACT-014`: audit docs synchronized for runtime artifact and metrics foundation.
- `WL-CONTRACT-015`: remains `PARTIAL / NOT_READY`; no production-ready claim.

Implementation files:

- `app/Application/Watchlist/Services/WatchlistBacktestRuntimeArtifactService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php`

Tests:

- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestMetricsServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactStaticGuardTest.php`

Internal diagnostics added for backtest artifact/metrics scope only:

- `WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE`
- `WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE`
- `WATCHLIST_BACKTEST_EVALUATION_SKIPPED_NO_PUBLISHED_PRICE`
- `WATCHLIST_BACKTEST_RUNTIME_ARTIFACT_READY_WITH_EVALUATION_SKIPPED`

No contract is moved to `LOCKED` because command/API runtime proof, production persisted artifact/log evidence, production schema, and walk-forward/OOS proof are still missing.

## Runtime Artifact and Metrics Local Validation Update — 2026-06-09

Status:
`DONE for runtime artifact and metrics foundation unit/static scope / LOCAL_PHPUNIT_PASS / NOT_PRODUCTION_READY`.

Evidence:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktest"
OK (25 tests, 286 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (116 tests, 1168 assertions)

vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataWatchlistReadModelTest"
OK (3 tests, 41 assertions)
```

Contract impact:

- `WL-CONTRACT-008`, `WL-CONTRACT-009`, `WL-CONTRACT-010`, `WL-CONTRACT-013`, and `WL-CONTRACT-014` retain DONE status for the current unit/static foundation scope with completed local PHPUnit proof.
- `WL-CONTRACT-015` remains `PARTIAL / NOT_READY`.
- The current local test requirement is satisfied; remaining blockers are command/API runtime proof, published-price production replay evidence, production persisted artifact/log evidence, production schema where required, and walk-forward/OOS proof.
- No contract is promoted to `LOCKED`.

## Next Required Contract Work

Next session should target:

`WATCHLIST — WALK-FORWARD AND OUT-OF-SAMPLE PROOF EXECUTION SESSION`

Priority contracts:

1. `WL-CONTRACT-009`
2. `WL-CONTRACT-010`
3. `WL-CONTRACT-013`
4. `WL-CONTRACT-007`
5. `WL-CONTRACT-008`
6. `WL-CONTRACT-014`
7. `WL-CONTRACT-015`

Required proof:

- build deterministic chronological train/calibration/test folds without future leakage;
- resolve exact-date current-readable publication, calendar, paramset, and input lineage independently for every fold;
- prove OOS-only evaluation and prevent in-sample/calibration rows from entering OOS metrics;
- preserve positive-volume tradability and no-synthetic-fill semantics;
- generate deterministic per-fold and aggregate OOS artifacts with reproducible hashes;
- keep portfolio allocation, execution, broker integration, scheduler, and production API out of scope;
- retain `WL-CONTRACT-015` as `PARTIAL / NOT_READY` until OOS and production operating evidence are complete.
## Published Price Runtime Contract Update — 2026-06-09

Session:
`WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF EXECUTION SESSION`

Evidence:

- official calendar read surface: `MarketDataTradingCalendarReadService` over `MarketCalendarRepository`;
- official exact-date price surface: `MarketDataPublishedEodSeriesReadService` over `MarketDataReadinessService` and `MarketDataPublishedEodSeriesReadRepository`;
- watchlist orchestration: `WatchlistBacktestPublishedPriceRuntimeService`;
- command: `RunBacktestPublishedPriceProofCommand`, registered in `app/Console/Kernel.php` without scheduler;
- runtime artifact adds `calendar_manifest`, `price_series_manifest`, `publication_manifest`, and `runtime_execution` while retaining official manifest names;
- canonical metric fields from file 16 are mapped and separated from derived/report metrics and diagnostic counters;
- controlled service runtime proof passed 25 assertions and produced equal canonical hashes `bb2268bbc053d7aa85fd5a400e834c519cfd3429` across two runs;
- controlled market-data read-surface proof passed 21 assertions; strategy paramset snapshot and command argument fail-safe smokes each passed 4 assertions;
- all 17 changed/new PHP files pass lint and grouped static validation has 0 failures;
- official command/database proof is blocked by sandbox PHP `8.4.16` versus project requirement PHP `< 8.4`; command attempt exits `2` and writes no artifact;
- all requested PHPUnit commands were attempted but exit `1` before discovery because `dom`, `mbstring`, `xml`, and `xmlwriter` are missing; no current-patch PHPUnit PASS is claimed.

Contract impact:

- `WL-CONTRACT-008`: published-price evaluations and diagnostics now include price/publication lineage; official command proof remains missing.
- `WL-CONTRACT-009`: strategy output is hashed/frozen before future-price reads; future price is evaluation-only; missing/future-effective inputs fail closed in controlled proof.
- `WL-CONTRACT-010`: canonical artifact hash excludes volatile execution timestamp/path and is reproducible across identical inputs.
- `WL-CONTRACT-013`: deterministic JSON evidence is exported at service level with official artifact references; official command/database evidence remains blocked.
- `WL-CONTRACT-014`: active session, implementation status, contract tracker, files, validation, blockers, and next work are synchronized.
- `WL-CONTRACT-015`: remains `PARTIAL / NOT_READY`; no OOS, production operating proof, production schema/persistence claim, or production-ready claim exists.

Historical pre-closure blockers (superseded by the closure update below):

- official command and PHPUnit evidence are now available for the pre-closure build;
- file 12/file 16 wording conflict is resolved by the closure patch;
- current closure-patch PHPUnit and two-run artifact proof remain required;
- walk-forward/OOS proof and production operating proof remain outstanding.

No contract is promoted to `LOCKED`.



## Published Price Runtime Proof and Closure Contract Update — 2026-06-09

Session:
`WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF EXECUTION SESSION`

Operator evidence:

- `WatchlistBacktestPublishedPrice`: PASS, 13 tests / 87 assertions;
- `WatchlistBacktest`: PASS, 39 tests / 375 assertions;
- full Watchlist: PASS, 130 tests / 1257 assertions;
- `MarketDataPublishedEodSeries`: PASS, 6 tests / 29 assertions after correcting historical-row fixture placement;
- `MarketDataTradingCalendar`: PASS, 4 tests / 16 assertions;
- existing MarketData watchlist read model: PASS, 3 tests / 41 assertions;
- command replay `2026-05-21` through `2026-05-29`: PASS twice;
- calendar coverage 10 dates, required/resolved published-price dates 9/9, evaluated trades 13;
- canonical artifact hash both runs: `03dce5cbd7176a6065dc711e0d9907a2279f9cc3`;
- publication lineage: 10/10 current readable sealed dates through `2026-06-08`.

Observed diagnostics:

- KING: no executable exit after positive-volume entry;
- BKDP: D+1 published row had equal OHLC and zero volume and therefore must be treated as no tradable entry.

Closure-patch controlled validation:

- all 9 changed PHP source/test files pass lint;
- grouped safety/parity validation passes 20 assertions;
- zero-volume and effective-threshold metrics harness passes 12 assertions;
- controlled runtime orchestration passes 10 assertions with equal canonical hash `e2d725378e6df67ffa579017fdbb2399e8bdc322` across two runs;
- these controlled results do not replace the required operator PHPUnit/database command rerun.

Closure impact:

- `WL-CONTRACT-007`: paramset traceability improved; required eval thresholds are carried to `paramset_snapshot`, configured/effective coverage thresholds are explicit, and unresolved thresholds block export.
- `WL-CONTRACT-008`: explainability improved with `BT_SKIP_NO_TRADABLE_ENTRY`, `BT_SKIP_NO_TRADABLE_EXIT`, volumes, and ignored non-tradable dates.
- `WL-CONTRACT-009`: future price remains evaluation-only after immutable trade-candidate freeze; zero-volume handling does not feed PLAN/RECOMMENDATION/CONFIRM.
- `WL-CONTRACT-010`: prior official canonical hash equality passed; closure-patch deterministic rerun remains required because semantics and hashed paramset metadata changed.
- `WL-CONTRACT-013`: official pre-closure command artifacts exist; closure-patch artifact export must be regenerated.
- `WL-CONTRACT-014`: owner docs, reason dictionary, SQL seed, audit status, and contract tracker are synchronized; file 12/file 16 exit-model wording conflict is resolved in favor of file 12 canonical rule-based execution.
- `WL-CONTRACT-015`: remains `PARTIAL / NOT_READY`; no OOS or production operating proof exists.

No contract is promoted to `LOCKED`.

Next required work inside the same session:

1. rerun closure-patch PHPUnit scopes;
2. run the command twice on `2026-05-21` through `2026-05-29` using new output files;
3. prove `metric_thresholds_resolved=1`;
4. verify BKDP becomes `BT_SKIP_NO_TRADABLE_ENTRY` and KING records zero-volume dates without synthetic exit;
5. prove the two new canonical artifact hashes are equal;
6. only then close this session and select walk-forward/OOS as the next session.

## Published Price Runtime Proof Final Contract Closure — 2026-06-09

Session:
`WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF EXECUTION SESSION`

Final session status:
`DONE for published price series runtime proof scope / LOCAL_RUNTIME_PROOF_PASS / NOT_PRODUCTION_READY`.

### Final evidence

```text
PublishedPrice PHPUnit: 17 tests / 146 assertions / PASS
MetricsService PHPUnit: 8 tests / 63 assertions / PASS
Backtest PHPUnit: 48 tests / 497 assertions / PASS
Full Watchlist PHPUnit: 139 tests / 1379 assertions / PASS
PublishedEodSeries PHPUnit: 6 tests / 29 assertions / PASS
TradingCalendar PHPUnit: 4 tests / 16 assertions / PASS
MarketDataWatchlistReadModel PHPUnit: 3 tests / 41 assertions / PASS

replay range: 2026-05-21 through 2026-05-29
command runs: 2 / PASS
calendar dates: 10
required/resolved price dates: 9/9
evaluated trades: 13
diagnostics: 2
thresholds resolved: true
min_trades: 120
effective min_days_covered: 4
days_covered / total window: 5/5
minimum coverage gate: true
metric calibration valid: false (expected: 13 < 120)
canonical artifact hash run 1: 0eaa353d20df901c4f372c0000951408578bf302
canonical artifact hash run 2: 0eaa353d20df901c4f372c0000951408578bf302
canonical hash equality: true
```

Final diagnostics:

- KING: `BT_SKIP_MISSING_OHLC_EXIT`; zero-volume dates `2026-05-25`, `2026-05-26`, and `2026-05-29` were ignored and recorded; no synthetic exit.
- BKDP: `BT_SKIP_NO_TRADABLE_ENTRY`; `entry_volume=0`; no position was opened.

### Final contract impact

- `WL-CONTRACT-007`: DONE for published-price runtime paramset traceability scope; not `LOCKED`.
- `WL-CONTRACT-008`: DONE for published-price runtime explainability scope; not `LOCKED`.
- `WL-CONTRACT-009`: DONE for published-price no-lookahead runtime proof scope; not `LOCKED`.
- `WL-CONTRACT-010`: DONE for published-price deterministic runtime reproducibility scope; not `LOCKED`.
- `WL-CONTRACT-013`: DONE for deterministic JSON runtime artifact evidence scope; not `LOCKED`.
- `WL-CONTRACT-014`: DONE for final docs synchronization scope.
- `WL-CONTRACT-015`: remains `PARTIAL / NOT_READY`.

No contract is promoted to `LOCKED`. The completed published-price runtime proof is sufficient to begin the next backtest-proof session, but it is not sufficient for production readiness.

Earlier statements in this tracker that current closure/coverage PHPUnit or command reruns are still required are historical and superseded by this final closure section.

Next required session:
`WATCHLIST — WALK-FORWARD AND OUT-OF-SAMPLE PROOF EXECUTION SESSION`.

