# CI-MD-B13-A001-001 — Actual and Proxy Daily Market Metrics

## Identity

- Stage: `MD-B13`
- Attempt: `MD-B13-A001`
- Baseline: `MD-B13-A001-BL001`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260823-001`
- Predecessor closure: `SC-MD-B12-A001-001`
- Status: `ISSUED — PROOF COMPLETE, ADMITTED BY E-MD-B13-A001-001`

## Stage-entry normalization

Current B13 traceability was re-derived across all `61` active rows before any material executable mutation. The stage owns two strategy documents in full — `MD-S042` (16 rows) and `MD-S086` (45 rows) — with no cross-stage split of either.

Entry state:

- `33` mandatory predicates;
- `15` `CONDITIONAL_PENDING` rows (`APPLICABILITY_PENDING`, closure-blocking until each condition is evidenced);
- `13` reference/context rows;
- `0` `MANDATORY_OR_CONDITIONAL` rows;
- `0` B13 mixed-classification debt;
- `0` current B13 predicate evidence bindings.

The prior provisional figure was `0/19`. It was not a denominator that anyone had derived; it was generator output, and it was wrong in both directions that `F-MD-B01-A001-001` describes.

**Rows promoted out of `REFERENCE_ONLY` because they state obligations (18).** The clearest cases are not marginal. `MD-S086-R0003` reads "Provider unit identity and normalization evidence are mandatory" and was classified as reference context — a row asserting its own mandatory status, filed as non-requirement. `MD-S086-R0007` carries the "only from a source field whose semantics and units represent actual Regular-Market traded value" restriction that is the operative rule of its section, while its own child bullets `R0008`/`R0009` were already required; the parent was reference and the children were obligations. `MD-S086-R0020` states the queryable-field requirement that its own required section rule `R0019` exists to enforce, and `MD-S086-R0022` states an explicit publication prohibition — "an unlabelled liquidity metric may not be published" — both filed as context. The remainder are indistinguishable siblings inside mixed enumerated runs: `MD-S086-R0002`, `R0004`, `R0005`, `R0010`, `R0012`, `R0016`, `R0024`, `R0026`, `R0027`, `R0030`, `R0032`, and the acceptance criterion `R0041`.

**Rows resolved to `CONDITIONAL_PENDING` (15).** Fourteen `MD-S042` rows construct or constrain the `market_daily_metrics` aggregate dataset, which `MD-S042` itself declares optional and which `Downstream_Consumer_Read_Model_Contract_LOCKED.md` requires only "where aggregates are requested". Whether current strategy scope requests aggregates is a condition this attempt must evaluate with evidence; it is not assumed in either direction here. `MD-S086-R0025` is the `dv20_idr` retirement condition, the same shape as `MD-S020-R0069` for the compatibility `eligible` alias, which carries only the upstream `data_usable` meaning, and it is normalized the same way.

**Rows that stay reference context (13).** One list introducer (`MD-S086-R0011`), one bare label (`MD-S086-R0035`), four bare cross-contract document references (`MD-S086-R0042..R0045`), two purpose statements, one explanatory passage, and the four capability-limitation bullets `MD-S086-R0036..R0039`, whose single enforceable consequence is owned by the required `MD-S086-R0040`.

`MD-S042-R0015` is normalized **mandatory** rather than conditional. Its subject is "`risk_flag` or equivalent", and the equivalent field exists today on the live row surface; a prohibition on publishing a market-timing, ranking or recommendation semantic is provable now and does not wait on the optional aggregate dataset.

`MD-DEP-0004` is discharged for B13 entry and remains `OPEN_NON_BLOCKING` globally, reduced from `268 / 8` to `258 / 7`.

## Strategy scope affected

- `MD-S042` — `Market_Daily_Metrics_Contract.md`: optional publication-bound aggregate context, actual/proxy total separation, aggregate declaration requirements, aggregate temporal/publication rules, metric domain boundary.
- `MD-S086` — `Volume_and_Turnover_Normalization_LOCKED.md`: canonical raw volume and unit normalization, source-backed actual traded value, close-times-volume proxy construction, rolling actual/proxy metrics, persisted proxy labelling, `dv20_idr` alias governance and retirement, lot-size boundary, precision/correction, and the capability boundary limiting what a liquidity metric may be cited for.

Cross-contract context read but not owned here: `Downstream_Consumer_Read_Model_Contract_LOCKED.md` (which fields the consumer requires), `Domain_Boundary_Invariants_LOCKED.md` (market-data ownership list), `Platform_Config_Registry_LOCKED.md` (actual traded-value field/version and close-volume proxy label/formula as configuration), and `Downstream_Data_Readiness_Guarantee_LOCKED.md` condition 5.

Strategy meaning changed: **NO**. No strategy byte is modified by this attempt.

## Executable impact surface

Expected/allowed material B13 changes:

- persisting the actual-versus-proxy marker, price basis, window length and formula version as queryable fields resolvable from the same publication context as the metric they label, so the distinction survives outside documentation and outside column naming;
- refusing publication of a liquidity metric whose actual-versus-proxy marker is absent, treating it as unlabelled rather than defaulting it to proxy;
- enforcing the required source/currency/market-segment/observed-date/quality-state set on an actual traded-value fact at the read path as well as the write path;
- keeping unavailable actual traded value at `NULL` and proving no silent derivation path exists from proxy to actual;
- proving the proxy is computed as `RAW close * RAW volume` from the raw series only, and that structural-adjusted price can never combine with raw volume;
- proving raw volume stores source-reported share units after verified unit normalization, that no lot multiplier is applied when the source unit is shares, and that zero volume is preserved as a source-backed value distinct from missing;
- guarding that no new artifact, column, contract or API field is named `dv*` or otherwise implies traded value without stating its basis, including the new labelling fields this stage introduces;
- proving the `dv20_idr` alias does not stand in for the explicitly named proxy field, and evaluating its retirement condition with evidence;
- proving no metric surface owned here carries a market-timing, buy/sell, ranking or portfolio-recommendation semantic;
- evaluating, with evidence, whether the optional `market_daily_metrics` aggregate dataset is requested by current strategy scope, and building the guard that detects a future aggregate surface rather than leaving the condition checked only where it is already false;
- positive and negative/fail-closed tests plus stage-scoped proof tooling.

## Database / schema impact

`eod_indicators` already carries `dv20_idr`, `adv20_close_volume_proxy_idr`, `adv20_traded_value_idr_actual`, `formula_version` and `price_product_code`; the bars table already carries `traded_value_idr_actual` and `trade_count_actual`. `market_daily_metrics` does not exist in any migration, model, repository or configuration.

Additive persistence is permitted only where the current row cannot otherwise store the labelling identity that `MD-S086-R0019..R0022` require. No migration may rewrite canonical `RAW` volume, sealed publication history, or historical metric values in order to attach labelling metadata — `MD-S086-R0033` forbids exactly that repair. Any new column introduced here is subject to `MD-S086-R0026`: it states its basis in its own name and does not imply traded value.

No aggregate table is created by this attempt unless the applicability evidence establishes that current strategy scope requests aggregates.

## Runtime / provider / backfill impact

- The current adapter declares `provides_actual_traded_value => false`, so `traded_value_idr_actual` and `adv20_traded_value_idr_actual` are written `NULL` on every path. That is the contract-required value, and it is also why the required-field enforcement of `MD-S086-R0008` has never been exercised by a real write. A condition enforced only on a branch nothing reaches is the defect shape this package has already recorded three times; the proof must exercise the populated path, not only the null one.
- No proxy value may be promoted into an actual field, and no provider field may become an actual traded-value fallback.
- Historical rows are not relabelled to acquire the new labelling fields; correction ownership stays with the governed publication lifecycle.
- No broad `storage/**` scan is required. Runtime artifacts are admitted only through current B13 governed evidence after local execution.

## Tests / tooling / evidence impact

B13 requires:

- targeted unit/integration tests for labelling persistence, actual/proxy separation, unit normalization, zero-volume preservation, lot-size prohibition, precision/rounding boundaries and every fail-closed path;
- static guards for the `dv*` naming prohibition, the lot-multiplier prohibition, the adjusted-price-times-raw-volume prohibition, and the absence of an aggregate surface while the aggregate condition is evidenced false;
- a stage-scoped traceability specification/gate and proof specification/readiness gate;
- an atomic binder with validate-only mode;
- a fail-closed proof mutation/self-test whose control run is read before any verdict is trusted;
- final documentation, classification, relationship and current-state checks.

A test file or source inspection is not execution evidence. All `33` B13 mandatory predicates remain `NOT_ASSESSED` until current proof is returned and admitted, and the `15` conditional rows remain `APPLICABILITY_PENDING` until their conditions are evidenced.

## Compatibility / residue risk

Existing liquidity implementation is `EXISTING_UNVERIFIED` for B13 and is preserved where conformant rather than rewritten for appearance. Known risk areas at attempt entry:

- no persisted actual-versus-proxy marker, price basis or window length exists anywhere, so `MD-S086-R0019..R0022` are currently unmet in substance, not merely unproven;
- `dv20_idr` and `adv20_close_volume_proxy_idr` carry the identical value, and the read product still exposes the alias; `MD-S086-R0027` requires proof that the alias is not standing in for the field it aliases;
- the actual traded-value required-field set has no exercised write path, so its enforcement is unproven in the direction that matters;
- `formula_version` and `price_product_code` are row-level identity, not per-metric labelling, so reusing them as the marker would satisfy the letter of the contract while leaving two liquidity metrics on one row indistinguishable;
- `market_daily_metrics` is absent entirely, so the fourteen aggregate predicates cannot be proven by construction and must be resolved as applicability, with evidence, in the direction the evidence actually supports.

These are candidate implementation gaps under current authority, not strategy defects.

## Dependency / relationship impact

- predecessor closure `SC-MD-B12-A001-001` is a stage precondition; it supplies the coherent product/factor foundation through explicit current implementation use only and contributes no inherited B13 predicate proof;
- `MD-DEP-0004` B13 entry obligation is complete; the global dependency remains `OPEN_NON_BLOCKING` at `258 / 7`;
- `MD-DEP-0003` is not owned by B13;
- no B13 blocking dependency exists at attempt opening;
- no open finding is owned by B13 at entry.

## Raw-artifact / governed-evidence mechanics

Executed proof for this attempt is expected to arrive as a returned local archive under `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md`. Each returned artifact is admitted only with its execution identity, path and hash correlated to this attempt and baseline. A returned artifact that is absent or that fails its hash ledger is recorded `INCOMPLETE` and never represented as `PASS`. No existing current evidence or prior closure is re-linked, rewritten or invalidated by this attempt's proof mechanics.

## Closure boundary

`MD-B13` cannot close until all `33` mandatory predicates are proven under `MD-B13-A001-BL001`, every one of the `15` conditional rows is resolved to `CONDITIONAL_APPLICABLE` and proven or to `CONDITIONAL_NOT_APPLICABLE` with evidence of the false condition, required runtime artifacts are admitted, residue is conformant or explicitly qualified, relationships are complete, and post-binding traceability/proof/classification/documentation/relationship/current-state gates pass.


## Executed proof outcome

Proof ran in-session against the deployed 10.4.27-MariaDB instance rather than arriving as a returned archive, so the raw outputs are retained under `storage/app/market-data/evidence/MD-B13-A001/` with a hashed manifest and correlated to this attempt by `E-MD-B13-A001-001`.

- Targeted B13 runtime/fail-closed proof: **PASS — 55 tests / 198 assertions**, exit 0.
- Full PHPUnit regression: **PASS — 1946 tests / 18130 assertions**, exit 0, against a predecessor baseline of 1891/17908.
- Proof mutation self-test: **PASS — 12/12**, both control entries green before any mutation verdict was read.
- Static invariant gate: **PASS** over 230 scanned files, `aggregate_capability_state=NOT_REQUESTED_NO_SURFACE`.
- PHP syntax control: **PASS — 537/537**.

Three defects were found in this attempt's own work and remediated before proof was admitted: labels keyed to an operator-configured version that would never have resolved, a seal guard scoped to the trade date rather than the sealing candidate, and a read product that resolved labels from the declaration rather than from the row. Each is recorded in `E-MD-B13-A001-001`.

## Applicability outcome

The fourteen `MD-S042` aggregate rows resolved to `CONDITIONAL_NOT_APPLICABLE` on an evidenced false condition, not on an assumption: the consumer contract's only aggregate clause is expressly conditional, the domain boundary admits benchmark context solely as the consumer contract requires it, and a 230-file scan found no aggregate surface of any kind. The condition is re-evaluated wherever it could become true — the static gate reports `SURFACE_PRESENT` and names the file the moment an aggregate identifier appears — rather than being checked only where it is already false.

`MD-S086-R0025` moved to `MD-B17` under section 7 of the traceability standard. Its retirement runs through a versioned read-model change B13 does not own, and the identical clause for the compatibility `eligible` alias, which carries only the upstream `data_usable` meaning, is already owned there. B13 keeps `R0024`, `R0026`, `R0027` and `R0028` as mandatory alias governance, so the obligation is relocated rather than reduced.

## Scope change during the attempt

Two additions beyond the entry declaration, both required by predicates already in scope: a `liquidity_formula_version` column on `eod_indicators` and its history table, without which no label could resolve deterministically; and `ActualTradedValueFactService`, which makes the required-field set of `MD-S086-R0008` enforceable on a populated value rather than latent behind a field nothing writes. Both are additive. No canonical `RAW` value, sealed publication, historical metric or immutable record was rewritten.


## Defect found by the closure controls — CURRENT_STATE stage resolution

Running the closure controls surfaced a fourth defect, this one in governance tooling rather than in B13 code, and it is worth recording plainly because it produced a wrong answer without producing a failure.

`GenerateMarketDataCurrentState.php` resolved the current executable stage by searching the resume sentence for the first stage-attempt-shaped token. B13's resume point cites `F-MD-B01-A008-001`, the finding MD-B14 inherits — and that identifier embeds the substring `MD-B01-A008`. The generator matched the finding instead of the stage the sentence names, and emitted a CURRENT_STATE reporting `MD-B01` as the current executable stage while the Stage Register said MD-B13 was closed and MD-B14 was next.

Nothing failed. The generation was deterministic and byte-identical across repeats, every gate passed, and the document was internally well-formed. It was simply wrong, and it would have stayed wrong for as long as a resume sentence happened to cite a finding — which the B14 resume point must, because B14 inherits one.

The fix anchors the stage token on a left-hand boundary, so a token preceded by anything identifier-like is not read as a stage. Five cases were checked, covering the attempt form, the unopened-successor form, unbackticked text, a resume citing two finding identifiers, and text naming no stage at all. Rewording the resume sentence to avoid the collision was available and was deliberately not used on its own: the collision is a property of the identifier scheme, not of one sentence, and the next stage to cite a finding would have reintroduced it.

This changed no B13 predicate, denominator, applicability decision, binding or evidence claim. `SC-MD-B13-A001-001` remains accurate as issued — its control line states that repeated generations were byte-identical, which was true then and is true now.


## Suite assertion-count reconciliation

`E-MD-B13-A001-001` records the full suite at **1946 tests / 18130 assertions**. Re-running it after the post-proof governance records were written gives **1946 tests / 18132 assertions**, still green. The test count is identical and no test was added, removed or changed after the proof run.

Three candidate causes were excluded by measurement rather than by argument, each with a full JUnit-instrumented suite run and a per-class assertion diff:

- the two new governed evidence records — removed and re-run: no change;
- the matrix binding and conditional resolution — reverted to the pre-binding matrix and re-run: no change;
- the appended sections of this declaration — truncated and re-run: no change.

The remaining +2 was not isolated. It is recorded here as unattributed rather than assigned to a plausible-sounding cause that was not measured. It is benign on the evidence available: the suite passes at 1946/1946 in every state tested, and the figure in `E-MD-B13-A001-001` is accurate as executed — an evidence record states what one execution observed, not what every future execution will observe.
