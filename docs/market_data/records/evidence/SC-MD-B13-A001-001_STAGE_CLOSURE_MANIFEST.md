# MD Stage Closure Manifest — SC-MD-B13-A001-001

- ID: `SC-MD-B13-A001-001`
- Stage / Attempt / Baseline / Epoch: `MD-B13` / `MD-B13-A001` / `MD-B13-A001-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260823-001`
- Change Impact Declaration: `CI-MD-B13-A001-001`
- Governed evidence: `E-MD-B13-A001-001`
- Predecessor stage closure: `SC-MD-B12-A001-001`
- Dependency: `MD-DEP-0004` B13 entry obligation complete; remains `OPEN_NON_BLOCKING` for **258 mixed-classification members across 7 unopened stages**
- Role: `EVIDENCE`, scope `STAGE_CLOSURE_MANIFEST`, immutable after issue
- Issued at: `2026-08-27T09:30:00+07:00`

## Terminal coverage

- Mandatory denominator: **33**
- Mandatory SATISFIED: **33/33**
- Conditional not applicable: **14** (each with an evidenced false condition)
- Conditional pending: **0**
- Reference/context: **13**
- Transitional applicability: **0**
- B13 mixed-classification debt: **0**
- Stage rows: **60**
- Evidence binding: all 33 current B13 mandatory predicates are atomically bound to `E-MD-B13-A001-001`

The stage opened on a provisional `0/19`. That figure was generator output, not a derived denominator, and it was wrong in both directions `F-MD-B01-A001-001` describes: eighteen rows stating obligations were filed as reference context — including one that reads "Provider unit identity and normalization evidence are mandatory" — while the section rule carrying the "only from a source field" restriction sat as context above required children. The corrected denominator increases the obligation.

No predicate credit is inherited from MD-B12 or from pre-epoch W13 work.

## Executed proof admitted by E-MD-B13-A001-001

Proof executed in-session against the deployed 10.4.27-MariaDB instance at applied-migration head `2026_08_27_000001_add_liquidity_metric_labelling_and_volume_unit_identity` (80 applied). Raw outputs retained under `storage/app/market-data/evidence/MD-B13-A001/` with manifest SHA-256 `516A0FAA9B9E1CC3F80DB65E78C6B1A8980F38A681688BA319B0D02AEAFAB259`.

- Targeted B13 runtime/fail-closed proof: **PASS — 55 tests / 198 assertions**, zero failures/errors, exit 0.
- Full PHPUnit regression: **PASS — 1946 tests / 18130 assertions**, zero failures/errors, exit 0.
- Proof mutation self-test: **PASS — 12/12** in `BOUND_CLOSURE`, `control_bound` and `control_traceability` both green before any mutation verdict was read.
- Static invariant gate: **PASS**, 230 files scanned, `aggregate_capability_state=NOT_REQUESTED_NO_SURFACE`.
- PHP syntax control: **PASS — 537/537**.

A schema-drift finding was raised and closed during the attempt: `tradeaxis_testing` was four migrations behind and was brought forward by applying the outstanding forward migrations. No deployed database was edited to match code, and no mirror was edited to match a stale deployment.

## Required semantics proven

- `RAW volume` stores source-reported traded share units only after verified unit normalization, with provider unit identity and normalization evidence recorded on the observation that carried the volume;
- no lot multiplier is applied when the source unit is shares, and an undeclared or lot-reporting source fails closed rather than being converted, because market-data owns no position-sizing configuration;
- zero volume is preserved as a real source-backed value, distinct from missing volume and from a missing bar;
- structural-adjusted volume remains a separate analytical field and never overwrites raw volume;
- `traded_value_idr_actual` is populated only from a source field representing actual Regular-Market traded value, with source, currency, market segment, observed date and quality state required **on the populated path**; an unavailable value is `NULL`, and a proxy-derived value is refused;
- trade count is source-backed and separately nullable from the traded value in both directions;
- the close-volume proxy is `RAW close * RAW volume` from the raw series only, and stays so when every other indicator runs on the structural-adjusted series;
- formula version, price basis, window length and an explicit actual-versus-proxy marker are queryable fields resolvable from the same publication context as the metric;
- a populated liquidity metric with no persisted marker is **unlabelled**, not presumed a proxy, and the seal stage refuses to publish it;
- the `dv20_idr` alias declares its target and an explicit retirement condition, the explicitly named proxy field exists in its own right, and no new artifact, column or field is named `dv*` or otherwise implies traded value without stating its basis;
- source precision is preserved with rounding only at the locked storage boundary, and no raw volume or historical value is rewritten to repair a proxy;
- every declared liquidity metric states its kind, units, market basis, window and quality-state pointer, and no metric surface owned here carries a market-timing, buy/sell, ranking or portfolio-recommendation semantic.

## Applicability outcomes

**Fourteen `MD-S042` aggregate rows: `CONDITIONAL_NOT_APPLICABLE`.** The condition — that the `market_daily_metrics` aggregate publication is requested by current strategy scope — is false and evidenced: `MD-S042` declares the aggregate context optional; `Downstream_Consumer_Read_Model_Contract_LOCKED.md` requires daily aggregate state only "where aggregates are requested" and states no unconditional aggregate obligation; `Domain_Boundary_Invariants_LOCKED.md` admits factual benchmark context solely as the consumer contract requires it; `LAYER_ACTIVATION_RULE.md` permits a frozen-strategy optional capability to remain not requested; and a 230-file scan found no aggregate table, model, repository, configuration key, read surface, `total_traded_value_idr_actual` or `total_close_volume_proxy_idr`.

`NOT_APPLICABLE` here is a terminal evidenced outcome, not a hidden pass: these rows enter neither numerator nor denominator, and nothing about them is claimed as proven. The condition is checked where it could become true rather than only where it is already false — `MarketDataLiquidityMetricStaticGate` reports `SURFACE_PRESENT` and names the file the moment an aggregate identifier appears in the scanned trees, and the gate's reaction to that mutation was verified before the resolution was recorded.

**`MD-S086-R0025` reassigned to `MD-B17`.** Its retirement executes "through a versioned read-model change", and demonstrating that no reader outside this package depends on the alias needs the same consumer read-surface versioning. B13 can own neither, so section 7 of the traceability standard places the predicate with the stage that can close it — consistently with `MD-S020-R0069`, the identical clause for the compatibility `eligible` alias meaning `data_usable`, already owned by MD-B17. `MD-S086-R0024`, `R0026`, `R0027` and `R0028` remain mandatory B13 alias governance, so the obligation is relocated, not reduced.

## Residue

`CONFORMANT_WITH_DECLARED_HISTORICAL_GAP`

- Indicator rows published before this attempt carry no `liquidity_formula_version` and resolve no label. They are not rewritten: `MD-S086-R0033` forbids rewriting historical values to repair a proxy, and back-stamping a marker onto rows whose formula identity was never recorded would assert a fact that was never true. The read product reports no label for them rather than an inferred one, so the gap is visible rather than disguised.
- The actual traded-value fields remain `NULL` because the current provider reports none. That is the contract's required value, and the required-field enforcement now runs on the populated path.
- `TOTAL_RETURN` and aggregate market metrics remain outside this stage. Neither is a hidden fallback.

## Findings and dependencies

- Blocking B13 finding: **none**. No new finding was opened.
- `MD-DEP-0004`: `OPEN_NON_BLOCKING`; B13 entry obligation complete, **258 / 7** downstream backlog remains.
- `MD-DEP-0003`: not owned by MD-B13.
- No predecessor closure, Baseline Lock, prior evidence or failed proof artifact was rewritten.

## Integrity / closure controls

- B13 bound traceability/applicability gate: **PASS — 33 mandatory / 14 conditional-not-applicable / 0 pending / 13 reference / 0 transitional**.
- B13 bound proof gate: **PASS — 33/33, 11 proof families, runtime pending 0**.
- B13 proof mutation self-test: **PASS — 12/12** in `BOUND_CLOSURE` with green controls.
- B13 liquidity static invariant gate: **PASS**.
- Classification consistency: **PASS — B13 debt 0; 258 mixed members across 7 unopened stages remain downstream**.
- Traceability applicability gate: **PASS**. Scope boundary completion gate: **PASS**.
- Strategy freeze / documentation integrity: **PASS — 942 physical / 942 role rows / 942 Document IDs / 942 current-verification rows; strategy freeze 91 / 0 mismatch; traceability 6495 rows**.
- Relationship integrity: **PASS — 143 work records / 249 relationships / 0 validity errors / 0 completeness gaps**.
- Relationship/document mutation self-test: **PASS**, controls and post-restore controls green.
- MD-B12 bound traceability and proof gates: **PASS**, unaffected by the B13 matrix changes.
- CURRENT_STATE deterministic generation: **PASS — repeated generations byte-identical**.

## Successor / exact resume

`MD-B13` is terminal **DONE / PASS**. `MD-B14` remains **NOT_STARTED** and is not opened by this closure work unit.

Single exact resume point after this closure: begin **MD-B14 stage-entry preflight**; rederive current B14 applicability/ownership/classification from current authority — including the 65 mixed-classification members and the six transferred horizon predicates of `F-MD-B01-A008-001` — and issue the first valid B14 Baseline Lock + Change Impact Declaration before any material B14 mutation.
