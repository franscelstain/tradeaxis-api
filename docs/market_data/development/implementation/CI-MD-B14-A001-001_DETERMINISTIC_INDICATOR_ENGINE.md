# Change Impact Declaration — `MD-B14-A001`

- ID: `CI-MD-B14-A001-001`
- Stage / Attempt / Baseline / Epoch: `MD-B14` / `MD-B14-A001` / `MD-B14-A001-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260823-001`
- Stage precondition: `SC-MD-B13-A001-001`
- Dependencies: `MD-DEP-0004` stage-entry semantic normalization
- Open finding owned here: `F-MD-B01-A008-001` (P2) — six horizon-role predicates
- Status: `IN_PROGRESS`
- Strategy meaning change: `NO`

## Objective

Open `MD-B14` — the deterministic indicator engine and correction dependency graph — and prove its
147 mandatory predicates against current authority, including the horizon-role declaration that
`F-MD-B01-A008-001` has been waiting on this stage to build.

## 1. Affected strategy IDs and rules

Fourteen documents, 383 active rows:

| Document | Owner | Rows |
|---|---|---|
| `MD-S081` | Indicator Registry Baseline (LOCKED) | 67 |
| `MD-S060` | EOD Indicators Formula Spec | 75 |
| `MD-S017` | Current Indicator Recompute Command Contract | 73 |
| `MD-S038` | Indicator Recompute Source Scope Contract | 42 |
| `MD-S028` | EOD Indicators Contract | 40 |
| `MD-S061` | Indicator Computation Specification | 40 |
| `MD-S037` | Indicator Nullability and OHLCV Gap Contract | 19 |
| `MD-S019`, `MD-S020`, `MD-S023`, `MD-S034`, `MD-S041`, `MD-S052`, `MD-S056` | supporting invariants | 27 |

Stage-entry normalization resolved every transitional row before this declaration was issued: **147
mandatory**, 1 conditional-pending, 6 optional-not-requested, 229 reference. Zero transitional
applicability, zero mixed-classification debt, zero unexplained reference rows.

## 2. Affected areas

- **Schema / migration**: inspect whether the indicator dependency manifest needs a persisted
  `horizon_role` surface. Any gap is remediated additively; no issued publication is rewritten.
- **Configuration**: inspect indicator window/precision/warm-up configuration identity. Configuration
  cannot weaken a locked formula or window rule.
- **Runtime behaviour**: inspect the recompute command boundary, computation order, window loading,
  ATR recursive state, per-field nullability and reason emission, and the publication-bound candidate
  flow.
- **Provider / source behaviour**: none directly; the recompute path is forbidden to acquire source.
- **Backfill / replay**: deterministic replay must reproduce byte-identical values, reasons, lineage
  and hash.
- **Tests / gates / generators**: build the `MD-B14` traceability spec, normalization, proof spec,
  proof gate and binder; add the horizon-role guard the finding requires.
- **Operator / ops behaviour**: the recompute command must not run source or import commands.
- **Evidence / proof mechanics**: issue new `MD-B14-A001` governed evidence after actual execution.
  Historical `W14` material is supporting context only and is not inherited.

## 3. Raw-artifact storage, path, manifest, hash and retention mechanics

Golden long-chain fixtures and deterministic replay are named by `MD-S060-R0069` and `MD-S081-R0066`
as acceptance proof. Where their material output is external, the governed evidence must bind
execution identity, artifact or manifest path, and hash per
`RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md` §6 before that proof supports closure. Storage is
not scanned as a resume step; only artifacts the selected proof requires are inspected.

## 4. Compatibility risk

Preserve the `STRUCTURAL_ADJUSTED` technical default bound in `MD-B12`, the actual-versus-proxy
separation bound in `MD-B13`, and the contamination semantics bound in `MD-B11`. Reject any change
that reintroduces a zero-OHLC placeholder, a forward fill, a sliding-window ATR reseed, or a
coalesced actual/proxy output.

## 5. Residue and rework risk

Search scope is the indicator computation, recompute command, nullability/reason, dependency-manifest
and registry surface. The specific residues to look for: a window that loads N available rows rather
than exact trading-date dependencies, an ATR seeded from the first row of the loaded window, an
intermediate rounding step, a compatibility primary reason that erases field-level reason sets, and a
recompute path that reaches a source or master table.

## 6. Affected dependencies and relationships

`MD-DEP-0004` is discharged for `MD-B14` by the stage-entry normalization recorded above.
`F-MD-B01-A008-001` is owned here for remediation: the dependency manifest must carry a `horizon_role`
for every window in the published field set, drawn from the three roles locked in
`Terminology_and_Scope.md`, with a guard asserting no field enters the baseline set without one. Until
then `MD-S056-R0019`–`R0022`, `R0024` and `R0129` stay `NOT_ASSESSED`; they are not claimed on the
strength of the roles being derivable, because deriving a role is not declaring one.

## 7. Strategy meaning change

**NO.** No strategy byte is changed. The indicator owner contracts are in the strategy freeze and
verified byte-for-byte; adding role assignments to them would be a strategy revision reserved for
`DOCUMENT_CHANGE_POLICY.md`. The role assignment belongs in the implementation-owned dependency
manifest, which is why the finding routed it here.

## Closure boundary

Closure requires the six conditions in `STAGE_CLOSURE_MANIFEST_STANDARD.md`, the conditional-pending
row resolved with evidence rather than assumption, positive and fail-closed proof for every mandatory
predicate, the horizon-role guard demonstrated to fail when a role is removed, no harmful residue,
current evidence, complete relationships, and all integrity gates passing.

## Actual impact and result

- **Stage-entry normalization**: complete and recorded before this declaration. 383 rows examined,
  147 mandatory, zero transitional, zero mixed-run debt, zero unexplained reference. The
  normalization asserts full-scope coverage, so a row it never examined is fatal rather than silent —
  the defect that produced `MD-B07-A002`.
- **Remaining work**: the `MD-B14` proof surface and the horizon-role remediation. Not yet claimed.
