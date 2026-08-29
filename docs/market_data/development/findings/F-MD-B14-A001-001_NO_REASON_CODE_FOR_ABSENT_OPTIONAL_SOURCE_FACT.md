# F-MD-B14-A001-001 — The registered reason vocabulary has no code for a field nulled by an absent optional source fact

- Status: `OPEN`
- Severity: `P3`
- Stage / Attempt / Baseline / Epoch: `MD-B14` / `MD-B14-A001` / `MD-B14-A001-BL001` / `MD-REBASELINE-20260820-001`
- Owning surface for remediation: `Reason_Codes_Registry.md` — a strategy authority document, so the change is reserved for `DOCUMENT_CHANGE_POLICY.md` and cannot be made by an implementation attempt
- Blocks nothing at `MD-B14`: no predicate in the stage denominator requires a code that does not exist

## Finding

`MD-B14-A001` implemented per-field null reason sets, which `Indicator_Nullability_And_OHLCV_Gap_Contract.md` requires:

> Indicator nullability is per field and reason-coded.

Every field the engine nulls for a reason the registry can name now carries that reason in `null_reasons_json`, and the compatibility primary reason no longer stands alone. Four registered `INDICATOR` codes carry the four causes the contract requires to stay distinct:

| Cause | Registered code |
|---|---|
| Warm-up not yet met | `IND_INSUFFICIENT_HISTORY` |
| A required trading-date dependency absent or invalid | `IND_MISSING_DEPENDENCY_BAR` |
| An unresolved corporate action in the window | `IND_CORPORATE_ACTION_DISCONTINUITY` |
| An unexplained price-scale break in the window | `IND_PRICE_SCALE_DISCONTINUITY` |

There is a fifth state the contracts describe and the vocabulary cannot express. `Current_Indicator_Recompute_Command_Contract.md` states it directly:

> If sector benchmark bars are missing for a date, sector-rotation fields remain `NULL`.

and `Indicator_Computation_Specification.md` restates it as a gap rule for a missing optional benchmark and for a missing actual traded value. In each case the field is null because an **optional source fact was never supplied** — not because history is short, not because a required bar is missing, and not because anything contaminated the window.

The `INDICATOR` category owns six codes in `Reason_Codes_Registry.md`. None of them describes that state:

- `IND_INSUFFICIENT_HISTORY` — "Required trading-day history is not yet sufficient". False: the history is complete.
- `IND_MISSING_DEPENDENCY_BAR` — "A required **canonical bar** in the trading-day dependency chain is missing". False twice over: the fact is not a canonical bar, and it is not required.
- `IND_INVALID_BAR_INPUT`, `IND_COMPUTE_ERROR` — neither is true; nothing is invalid and nothing failed.
- The two discontinuity codes describe a detected event, and there is none.

## Consequence, and why it is `P3`

Five fields are affected: `sector_roc20`, `rs_20_vs_sector`, `sector_rs_20_vs_ihsg`, `rs_20_vs_ihsg` when its benchmark is unresolved, and `adv20_traded_value_idr_actual`, which is permanently null because the provider supplies no actual traded value at all.

These fields are null and **absent from the reason map**, rather than null with a wrong reason. That is the conservative failure: a reader learns nothing, instead of learning something untrue. Writing `IND_MISSING_DEPENDENCY_BAR` onto an optional sector field would be worse than silence — it would report a canonical bar gap that did not happen, and an operator filtering on that code to find data holes would collect rows that have none.

It is `P3` rather than `P2` because no predicate in the `MD-B14` denominator is left unprovable by it. The contracts that describe these fields require them to be `NULL` and to null only their own dependents; both are implemented and proven by `IndicatorFieldRegistryAndNullReasonsTest` and `BenchmarkRoc20ResolutionTest`. What is missing is the vocabulary to say *why*, and no rule in the stage scope demands a code the registry does not own.

## Why this attempt did not fix it

Adding a reason code means editing `Reason_Codes_Registry.md`, which lives under `authority/strategy/registry/` and is verified byte-for-byte by the documentation integrity gate against the strategy freeze manifest. `DOCUMENT_CHANGE_POLICY.md` reserves that to an authorised strategy change.

Inventing the code only in `Reason_Codes_Seed.sql` would fail
`LoggingTraceabilityReasonCodesStaticGuardTest::test_reason_code_registry_and_seed_are_synchronized`, and rightly: the seed is a projection of the registry, not a second place to define codes.

An implementation attempt changing a strategy document so that its own output looks complete is precisely what the governed workflow forbids, and it is the shape of the defect this package has already recorded elsewhere. The honest outcome is a narrower implementation and an open finding, not a wider vocabulary invented here.

## Remediation

An authorised strategy change adds one `INDICATOR` code — a name such as `IND_OPTIONAL_SOURCE_FACT_ABSENT`, severity `WARN` — to `Reason_Codes_Registry.md`, mirrored into `Reason_Codes_Seed.sql` and seeded into deployed databases by a migration in the pattern of the existing reason-code seed migrations.

`IndicatorVectorService::fieldNullReasons()` then extends to emit it for the five fields above, and
`IndicatorVectorService::fieldRegistry()` lists it in their `null_reason_codes`. The existing guard
`IndicatorFieldRegistryAndNullReasonsTest::test_every_declared_null_reason_code_is_a_registered_code` already asserts that every code a field declares is one the registry owns, so it will hold the extension to the same standard without modification.
