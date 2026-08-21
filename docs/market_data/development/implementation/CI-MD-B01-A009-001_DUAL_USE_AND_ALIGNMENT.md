# MD Change Impact Declaration — CI-MD-B01-A009-001

- ID: `CI-MD-B01-A009-001`
- Stage / Attempt / Baseline / Epoch: `MD-B01` / `MD-B01-A009` / `MD-B01-A009-BL001` / `MD-REBASELINE-20260820-001`
- Record class: `MUTABLE_TRACEABLE`
- Issued: 2026-08-21, **before** the matrix mutation and after the guard was written and mutation-proven.

## Why this attempt is material

It changes `coverage_status` on traceability rows and adds an executable test. Both are named material by `CHANGE_IMPACT_DECLARATION_STANDARD.md` section 1.

## Scope

Advance `MD-B01` coverage against the corrected 155-rule set. 16 rules move `NOT_ASSESSED` → `SATISFIED`, all owned by `Domain_Boundary_Invariants_LOCKED.md`:

| Rules | Proof |
|---|---|
| `MD-S020-R0182`–`R0188` (7) | The cross-contract alignment list. Each named document must exist at the path the contract writes — resolved relative to `book/`, the way a reader standing in that directory would — and must be registered as current authority in at least two governance registries, not merely present on disk. |
| `MD-S020-R0189` | The precedence rule. No active document may redefine the compatibility alias against the owner boundary. |
| `MD-S020-R0088`, `R0096` | The dual-use fact rule. Each of the five declared facts carries its market-data half here and none carries its downstream half. |
| `MD-S020-R0101`, `R0171` | Every row of the dual-use table states both halves, and every fact this guard constrains appears in that table. |
| `MD-S020-R0014`, `R0015`, `R0018`, `R0023` | Four ownership assertions, each resolved to a real schema surface rather than to prose. |

## The load-bearing case

Exchange lot size is the sharpest rule in this family: the contract records its market-data half as **"None — explicitly disowned by the volume contract"**. A disownment is only real if the surface is absent, so absence is what is checked — and `MD-S020-R0187` separately requires that the volume contract actually carry the disownment it is credited with owning. Both hold.

## Affected areas

| Area | Impact |
|---|---|
| Traceability | **Material.** `MD-B01` `69/155` → `85/155`. Global `SATISFIED` 69 → 85; denominator unchanged at 2010. |
| Tests | **Material.** One test file added: 18 tests, 55 assertions. |
| Schema / config / runtime / strategy | **None.** Nine files were mutated transiently during the negative proof and each was restored and verified byte-identical. |
| Evidence | Additive. No prior evidence is restated or invalidated. |
| Runtime artifacts | **None.** Under `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md` section 5 this is document-and-test work claiming no externally-stored runtime output, so no `storage/**` inspection is required or performed. |

## Compatibility risk

**Low.** Nothing existing changes behaviour; a test is added and 16 rows change state. The added guard is strictly stricter — it can only turn a future `PASS` into a `FAIL`.

## Residue / rework risk

**Low.** Six mutations each turned the guard red, each verified as landed before the verdict was read: a lot-size column, a downstream entry/exit column, a downstream liquidity threshold in configuration, an alignment target de-registered from all three governance registries, the lot-size disownment removed from the contract credited with owning it, and the immutable source-observation table renamed out of the schema. Two controls confirmed the guard stays green on a legitimate tick-size revision column and on a comment mentioning the lot-size disownment.

One of those mutations edited a frozen strategy contract. It was restored and the restoration verified twice — by byte comparison in the mutation driver, and by the documentation integrity gate's `STRATEGY_FREEZE` check, which re-verified the freeze manifest byte-for-byte and passed.

One guard defect was found and fixed during the attempt. The first revision flagged `IndicatorVectorService` for the phrase "lot size", which appears in a docblock explaining that turnover applies **no** lot multiplier and why the alternative overstates the market by two orders of magnitude. That comment is the disownment being honoured, and reading it as a violation is the same mistake recorded at `MD-B01-A008` — a description of an obligation mistaken for a breach of it, this time on the code surface rather than the document corpus. The dual-use scan now strips PHP comments before reading identifiers, and `test_a_comment_about_the_disownment_is_not_read_as_a_surface` asserts the separation in both directions: the comment must not be flagged, a real `lot_size` identifier must still be.

Two further defects were my own and were caught by the tests before any rule was claimed: `run_status` is not the readiness identifier in this schema (`freshness_state` and `coverage_gate_state` are), and `assertRegExp` is deprecated in PHPUnit 9.

Residual risk, stated rather than hidden:

- Alignment is proven as existence, registration, and — for the two contracts credited with owning a specific split — presence of that split. Full semantic alignment between seven contracts is not mechanically decidable and is not claimed.
- The dual-use check proves no downstream half is present under the names this domain would use for it. A downstream threshold hidden under an innocent name would not be caught, the same inherent limit recorded for the naming boundary at `MD-B01-A007`.

## Affected dependencies and relationships

- `MD-DEP-0004` — unaffected, remains `OPEN_NON_BLOCKING`.
- `F-MD-B01-A008-001` — unaffected; this attempt claims no rule it blocks.
- Continuity edge to `E-MD-B01-A008-001`: this attempt advances the same corrected denominator.

## Strategy semantic change

`NO`. `Domain_Boundary_Invariants_LOCKED.md` is read as the owner contract and is not modified; the freeze was verified intact after the negative proof.
