# MD Change Impact Declaration — CI-MD-B01-A011-001

- ID: `CI-MD-B01-A011-001`
- Stage / Attempt / Baseline / Epoch: `MD-B01` / `MD-B01-A011` / `MD-B01-A011-BL001` / `MD-REBASELINE-20260820-001`
- Record class: `MUTABLE_TRACEABLE`
- Issued: 2026-08-21, **before** the matrix mutation and after the guard was written and mutation-proven.

## Why this attempt is material

It changes `coverage_status` on traceability rows and adds an executable test. Both are named material by `CHANGE_IMPACT_DECLARATION_STANDARD.md` section 1.

## Scope

12 rules move `NOT_ASSESSED` → `SATISFIED`, across all three owner contracts:

| Rules | Proof |
|---|---|
| `MD-S001-R0056`, `MD-S020-R0066`, `MD-S056-R0125` | The five fact dimensions — coverage, quality, liquidity, event risk, data usability — are each populated and share **no column at all**. Each owner states the obligation in its own words, so each is asserted separately, with the boundary contract additionally requiring the eligibility result to carry its own explanatory field and the terminology register additionally requiring each dimension to own reason codes. |
| `MD-S001-R0051` | A raw source observation has its own immutable surface and is not column-identical to a canonical bar. |
| `MD-S001-R0053` | `STRUCTURAL_ADJUSTED` is declared and rests on adjustment factors that carry a volume factor alongside the price factor, which is what makes inverse volume adjustment possible. |
| `MD-S001-R0055`, `MD-S056-R0124` | Exactly three price products are declared and they are exactly the three the contract names; no declaration names a provider adjusted close, and the indicator engine reads none anywhere in its vector. |
| `MD-S001-R0035` | Import is driven by a requested date, and no import command defaults that date to today or latest, which would make the historical path unreachable. |
| `MD-S001-R0061` | Every partial-import reason is registered as blocking or gate-deciding, never as an ordinary readable outcome. |
| `MD-S001-R0077` | The provider default query window is represented explicitly in the acquisition layer and does not reach the read or publication layer. |
| `MD-S001-R0098` | The read path never resolves a date by taking the latest, proven by the existing `ConsumerReadProductAntiBypassTest`, re-executed at this baseline. |
| `MD-S001-R0100` | Immutable source observations carry provenance in four separate aspects: the source, the payload identity, the provider symbol, and the provider schema version. |

## Two rules un-deferred, and why

`MD-S056-R0124` and `MD-S056-R0125` were recorded at `MD-B01-A008` as not provable, on the grounds that coverage and liquidity had no implementation surface until `MD-B13`/`MD-B15`. That was read from a schema view covering only `database/migrations/**`. `MD-B01-A010` corrected the reader to include the base SQL that the core migration executes, and with the full surface visible all five dimensions are present — coverage 30 columns, quality 14, liquidity 9, event risk 40, data usability 16 — with zero overlap between any two.

The deferral was an artefact of the instrument, not of the codebase. Recording that plainly matters more than the two rules: a deferral made on a wrong reading is indistinguishable from a real blocker until someone re-checks it, and the resume point had been carrying both as blocked for three attempts.

The remaining eight blocked rules are unaffected and stay blocked.

## Affected areas

| Area | Impact |
|---|---|
| Traceability | **Material.** `MD-B01` `98/155` → `110/155`. Global `SATISFIED` 98 → 110; denominator unchanged at 2010. |
| Tests | **Material.** One test file added: 11 tests, 36 assertions. One existing test (`ConsumerReadProductAntiBypassTest`, 7 tests, 38 assertions) re-executed at this baseline and cited for `MD-S001-R0098`; it previously backed no rule. |
| Schema / config / runtime / strategy | **None.** Files were mutated transiently during the negative proof and each was restored and verified byte-identical. |
| Evidence | Additive. No prior evidence is restated or invalidated. |
| Runtime artifacts | **None.** Section 5 of `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md`; no `storage/**` inspection required or performed. |

## Compatibility risk

**Low.** Nothing existing changes behaviour; a test is added and 12 rows change state. The added guard is strictly stricter.

## Residue / rework risk

**Low.** Ten mutations each turned the guard red, every one verified as landed first: a column made to belong to two dimensions at once, each of the four provenance aspects removed separately, a fourth price product declared for the provider adjusted close, the indicator vector made to read one, a partial-import reason downgraded to an ordinary outcome, the volume adjustment factor removed, and the provider transport window leaked into the read layer. Two controls confirmed the guard stays green on another provider field-map entry and on a legitimate additional coverage column.

One guard defect was found and fixed before any rule was claimed. The `MD-S001-R0100` check originally asked whether *any* provenance-like column existed, which a single survivor satisfies — the failure shape recorded at `MD-B01-A010`. It now requires four aspects separately, and re-run per aspect all four fail closed. The mutation that exposed it first reported `GUARD_DID_NOT_REACT` because it renamed four of the six provenance columns and left two standing; the guard was right and the mutation was incomplete, which is only distinguishable by looking at the real columns rather than trusting either result.

A second correction: the first `MD-S001-R0055` check flagged `adj_close` in `config/market_data.php`. That entry is the provider **field map** — the name of the field in the provider payload — which is exactly what reading a raw source observation requires under `MD-S001-R0051`. Treating it as a product declaration would have flagged the domain for doing what another rule obliges it to do. The check now reads `*_PRODUCT` declarations, and `test_a_provider_field_map_entry_is_not_read_as_a_product_declaration` asserts the separation in both directions.

Residual risk, stated rather than hidden: dimension separation is proven at the column level. Two dimensions could still be conflated in prose or in a downstream read model without sharing a column; that is a different obligation, owned by the read-model contract.

## Affected dependencies and relationships

- `MD-DEP-0004` — unaffected, remains `OPEN_NON_BLOCKING`.
- `F-MD-B01-A008-001` — unaffected; no rule it blocks is claimed here.
- Continuity edge to `E-MD-B01-A010-001`, whose instrument correction is what made two of these rules provable.

## Strategy semantic change

`NO`. All three owner contracts are read as authority and none is modified.
