# MD Change Impact Declaration — CI-MD-B01-A006-001

- ID: `CI-MD-B01-A006-001`
- Stage / Attempt / Baseline / Epoch: `MD-B01` / `MD-B01-A006` / `MD-B01-A006-BL001` / `MD-REBASELINE-20260820-001`
- Record class: `MUTABLE_TRACEABLE`
- Issued: 2026-08-21, **before** the matrix mutation and after the guard was written and mutation-proven. `MD-B01-A005` recorded the opposite order as a deviation; this attempt follows its do-not-repeat.

## Why this attempt is material

It changes `coverage_status` on traceability rows and adds an executable test. Both are named material by `CHANGE_IMPACT_DECLARATION_STANDARD.md` section 1.

## Scope

Advance `MD-B01` coverage against the corrected 155-rule set. 14 rules move `NOT_ASSESSED` → `SATISFIED`:

| Rules | Proof |
|---|---|
| `MD-S001-R0033`, `MD-S001-R0034` | `DateDrivenCapabilityAndProviderAbstractionTest` — arbitrary single requested date and arbitrary requested range. Proven at `MD-B01-A002`, re-executed at this baseline. These were `REFERENCE_ONLY` until `A004` promoted them, so they could not be claimed before. |
| `MD-S001-R0127`–`MD-S001-R0138` (12) | `AntiAssumptionClaimBoundaryTest` — new. 171 active documents plus the `app/` and `config/` trees carry none of the twelve assumptions the baseline contract forbids. |

## Affected areas

| Area | Impact |
|---|---|
| Traceability | **Material.** `MD-B01` `21/155` → `35/155`. Global `SATISFIED` 21 → 35; denominator unchanged at 2010. |
| Tests | **Material.** One test file added: 15 tests, 40 assertions. |
| Schema / config / runtime / provider / backfill / replay / ops | **None.** No file under `app/`, `database/`, or `config/` is touched. |
| Evidence | Additive. No prior evidence is restated or invalidated. |
| Runtime artifacts | **None.** Under `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md` section 5 this is document-and-test work claiming no externally-stored runtime output, so no `storage/**` inspection is required or performed and no raw-artifact linkage is claimed. The two carried-forward rules rest on in-repository test execution, not on an external artifact. |

## Compatibility risk

**Low.** Nothing existing changes behaviour; a test is added and 14 rows change state. The added guard is strictly stricter — it can only turn a future `PASS` into a `FAIL`.

## Residue / rework risk

**Low.** The guard's twelve patterns are each asserted to match the exact statement they forbid, and asserted not to match each other, so a clean corpus result cannot come from a pattern that is merely too narrow or from one rule's pattern standing in for another's. Four mutations — three injected document claims and one injected code comment — were each verified as landed and each turned the guard red.

Residual risk: the patterns match stated assumptions, not implied ones. The contract says "menyatakan atau menyiratkan"; implication is not mechanically detectable and is not claimed to be covered. That limit is stated rather than hidden.

## Affected dependencies and relationships

- `MD-DEP-0004` — unaffected, remains `OPEN_NON_BLOCKING`.
- Carries forward `E-MD-B01-A002-001` for two rules; recorded as an explicit relationship row.

## Strategy semantic change

`NO`.

## Repair performed under this attempt

The `MD-B01-A005` rows had been lost from all four registries between sessions while the record files and their 15 matrix ownership moves stayed intact. Both gates detected it on entry. The three records were re-registered rather than re-derived; no `A005` work was repeated.
