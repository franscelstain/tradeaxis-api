# Change Impact Declaration — `MD-B17-A001`

- ID: `CI-MD-B17-A001-001`
- Stage / Attempt / Baseline / Epoch: `MD-B17` / `MD-B17-A001` / `MD-B17-A001-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260823-001`
- Stage precondition: `SC-MD-B16-A001-001`
- Dependencies: `MD-DEP-0004` stage-entry semantic normalization
- Open finding owned here: `F-MD-B17-A001-001` (P2) — blocks stage closure
- Status: `COMPLETE_WITHIN_ATTEMPT_SCOPE` — closure blocked, not deferred
- Strategy meaning change: `NO`

## Objective

Open `MD-B17` — the atomic versioned market-data read product and freshness/readiness gateway — and
prove as much of its 246-predicate denominator as the current authority permits, while stating
plainly which part it does not permit and why.

## 1. Affected strategy IDs and rules

Twenty documents, 309 active rows. The owner contracts:

| Document | Owner | Mandatory rows |
|---|---|---|
| `MD-S051` | Run Status and Quality Gates (LOCKED) | 69 |
| `MD-S021` | Downstream Consumer Read Model Contract (LOCKED) | 40 |
| `MD-S022` | Downstream Data Readiness Guarantee (LOCKED) | 33 |
| `MD-S006` | Consumer Read Contract (LOCKED) | 24 |
| `MD-S049` | Read-Side Enforcement Anti-Bypass Contract (LOCKED) | 16 |
| `MD-S030` | Effective Trade Date Contract (LOCKED) | 13 |
| `MD-S019`, `MD-S001`, `MD-S009`, and eleven others | supporting invariants | 51 |

Stage-entry normalization resolved every transitional row before this declaration: **246 mandatory**,
2 conditional-not-applicable, 1 optional, 60 reference. Zero transitional, zero conditional-pending,
zero mixed-classification debt, zero unexplained reference.

The Stage Register carried `0/104`. The real denominator is 246, because 116 predicates arrived
filed as reference context or as mixed-run siblings while carrying obligations.

## 2. Affected areas

- **Runtime behaviour**: `MarketDataPipelineService`, `MarketDataReadinessService`,
  `MarketDataReadProductRepository`, `MarketDataReadProductService`, `EodPublicationRepository`,
  `FinalizeDecisionService`. Inspect the import/promote split, terminal status and publishability,
  the seven promote preconditions, readiness and freshness states, pointer-only resolution, and the
  forbidden read shortcuts.
- **Quality gates**: the date-level anomaly checks `MD-S051` owns. See the confirmed gap below.
- **Schema / migration**: none required. The remediation adds a service and a promote hook.
- **Configuration**: the anomaly thresholds need six keys the strategy-owned registry does not carry.
- **Evidence / proof mechanics**: issue `MD-B17-A001` governed evidence for the predicates that can
  be proven; the finding-blocked predicate stays unbound.
- **Tests / gates / generators**: build the `MD-B17` proof spec, gate, binder, self-test and closure
  gate, with the finding-blocked rule carried explicitly rather than dropped.

## Confirmed executable gap, and the part of it that is closed

`Run_Status_and_Quality_Gates_LOCKED.md` owns date-level anomaly checks and explains why:

> Row-level validation cannot, by construction, see a pattern across rows. A defect affecting many
> instruments on one acquisition date presents as many individually admissible rows, and every
> per-row rule passes.

It names three measures — zero-volume share, flat-bar share, cross-field contradiction count.
**None of the three existed anywhere in the platform.** `DateLevelAnomalyCheckService` now computes
all three, compares against neighbouring dates resolved through the governed market calendar rather
than by date arithmetic, records a state and finding set on the promote stage event, and alters no
row. Nine guards cover it; eight fail-closed probes all fire.

The contract also says:

> Thresholds are configured values bound to the run's configuration snapshot, never implicit
> judgement.

That clause is **not** satisfied and cannot be by this attempt. The six configuration keys must be
registered in `Platform_Config_Registry_LOCKED.md`, which is `MD-S082`, current strategy authority
verified byte-for-byte, changed only by controlled correction. `PlatformConfigRegistry` rejected the
keys as unregistered the moment they were added, and it was right to. Registering them so this
attempt's own output would pass is what the governed workflow forbids.

The thresholds are therefore declared constants, every result carries
`date_level_anomaly_threshold_binding = DECLARED_PENDING_CONFIG_REGISTRATION`, and a guard asserts
both that state and that the keys remain unregistered — so the deferral ends loudly rather than
quietly. Recorded as `F-MD-B17-A001-001`.

## 3. Raw-artifact storage, path, manifest, hash and retention mechanics

Proof is executed locally and its material output is a test transcript. Where a selected proof
depends on material output external to docs, the governed evidence binds execution identity,
artifact or manifest path, and hash per `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md` §6.
Storage is not scanned as a resume step.

## 4. Compatibility risk

Preserve every closed predecessor boundary: the `0.98` coverage threshold bound in `MD-B15`, the
first-class eligibility dimensions bound in `MD-B16`, the indicator field registry and per-field
reason sets bound in `MD-B14`, and the two compatibility aliases whose retirement conditions remain
false. Reject any change that resolves a publication by recency, mixes publications in one response,
or relabels a prior effective date as the requested date.

## 5. Residue and rework risk

Search scope is the promote decision path, readiness resolution, read product repository and
service, pointer lifecycle and the quality-gate surface. The specific residues to look for: a
readiness state inferred rather than derived, a prior-date result labelled fresh, a read that
resolves authority from the newest row, a response that mixes publications, and an anomaly
measurement computed and discarded.

## 6. Affected dependencies and relationships

`MD-DEP-0004` is discharged for `MD-B17` by the stage-entry normalization. `F-MD-B17-A001-001` is
owned here and blocks closure. `F-MD-B01-A014-001` remains open and is owned by `MD-B19`;
`F-MD-B14-A001-001` remains open and is a reason-code vocabulary matter outside this stage.

## 7. Strategy meaning change

**NO.** No strategy byte is changed — which is precisely why the threshold clause remains unmet
rather than being made to pass.

## Closure boundary

`MD-B17` cannot close on this attempt. `STAGE_CLOSURE_MANIFEST_STANDARD.md` requires every
`MANDATORY` denominator row to be `SATISFIED`, and `MD-S051-R0070` cannot be. Closure additionally
requires a controlled correction registering the six
`market_data.quality_gates.date_level_anomaly.*` keys, which is outside an implementation attempt.

Reviewed decision `D-MD-B17-A001-001` now bounds that correction and its affected-proof analysis,
but is pending explicit user authorization. Issuing the decision does not alter A001 evidence,
strategy bytes, the active freeze, or this attempt's `PARTIAL` verdict.

## Actual impact and result

- **Stage-entry normalization**: complete. 309 rows examined, 246 mandatory, 2 conditional-not-applicable behind a standing guard, zero transitional, zero pending, zero unexplained reference, zero foreign rows altered.
- **Date-level anomaly checks**: implemented, wired into promote, guarded, mutation-proven — nine guards, nine fail-closed probes, all caught. Threshold binding deferred to `F-MD-B17-A001-001`.
- **Proof surface**: 245 of 246 predicates mapped across 28 families; `MD-S051-R0070` carried as the finding-blocked rule, kept in the denominator and asserted unbound.
- **Evidence**: `E-MD-B17-A001-001` issued from nine executions — targeted guards (94 tests, 1046 assertions), remediation guards (11 / 50), a full suite before binding (2006 / 20331) and after (2006 / 20334), the governance gate sweep, the proof gate and self-test, nine service probes and four binder probes. Two manifests: seven artifacts for the proof, two for the post-binding verification, kept apart because one post-binding artifact records a closure-gate run that reads the proof manifest's own artifact count.
- **Binding**: 245 predicates bound atomically. The diff is exactly 245 matrix lines, every one `MD-B17`, and `MD-S051-R0070` untouched. The binder refuses to run if the blocked predicate is already bound, is downgraded or deactivated out of the denominator, or is given a family and bound as though provable; four probes confirm each refusal, all four caught for the stated reason.
- **A defect the binding exposed**: the stage proof self-test labelled a fully bound matrix `PRE_RUNTIME`, because its mode detector required every mandatory row to be bound and one is deliberately not. The red control it produced was the safe direction, but the label was false, and had the blocked row ever been bound the detector would have flipped modes silently — the same shape found earlier in the `MD-B11` and `MD-B12` self-tests. The detector now excludes the blocked rule as the gate does, honours an explicit `--bound` or `--pre-binding` flag, and treats a requested mode that contradicts the matrix as fatal. Its two matrix mutations also now select an ordinary predicate by identity rather than by position, so neither can land on the blocked row and report a catch it never made.
- **Closure**: not claimed. The closure gate meets seven of its eight conditions and fails `denominator_fully_satisfied` at 245/246, naming `MD-S051-R0070`. That is the correct reading of the stage, and no part of this attempt was shaped to make it read otherwise.
- **Remaining work**: none inside this attempt. Closure waits on the controlled correction to `MD-S082`.
