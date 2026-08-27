# Change Impact Declaration — `MD-B08-A002`

- ID: `CI-MD-B08-A002-001`
- Stage / Attempt / Baseline / Epoch: `MD-B08` / `MD-B08-A002` / `MD-B08-A002-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260823-001`
- Stage precondition: `SC-MD-B08-A001-001`
- Dependencies: `MD-DEP-0004` stage-entry semantic normalization
- Status: `EXECUTED`
- Strategy meaning change: `NO`

## Objective

Correct a classification defect in the closed `MD-B08-A001` entry review: `MD-S067-R0010`
(`All reason codes are retained. A primary reason is routing compatibility only.`) states two
executable obligations and was filed `REFERENCE_ONLY` with empty notes. Bind it, and prove it with a
behavioural guard, because unlike the `MD-B07-A002` correction the obligation was genuinely
unproven.

## Why the prior classification was defective

`MD-S067-R0010` is the standalone paragraph closing the `Independent dimensions` section of the
Error Taxonomy contract. It carries no list marker, so `detectRuns` in the classification
consistency gate never groups it with the eight dimension bullets above it and `MIXED_RUN` cannot
fire. It is the same shape as `MD-S066-R0002` in `MD-B07-A002`: an obligation-bearing paragraph
invisible to every gate.

The B08 normalization scope was not at fault here — it examines all 233 rows the matrix assigns to
the stage. The rule was examined and classified `REFERENCE_ONLY`.

## Why this one differs from MD-B07-A002

`MD-B07-A002` bound proof that already existed. This one did not exist.

The only test naming `failure_reason_summary` asserted the string appears in the adapter source:

```php
$this->assertStringContainsString('failure_reason_summary', $source);
```

That stays green while the behaviour is gone. Collapsing the retained reason map to its single most
frequent entry passed the entire 1946-test suite. This is the third time in this package that a
static guard has failed to catch a reason-code defect.

## Authority and traceability scope

- Semantic owner: `MD-S067` Error Taxonomy and Run-Status Decision Table.
- Two obligations in one row: every distinct reason code survives, and a primary reason is a routing
  label that never becomes the sole record of why a run failed.
- A001 denominator 138 mandatory + 95 reference; A002 denominator **139 mandatory + 94 reference** (stage-owned rows; the normalization counter reports 96 because it spans rows owned by other stages too).
- No other row changes classification.

## Impact assessment

- Strategy: no strategy byte change is authorised or expected.
- Schema/data/configuration: none.
- Runtime/application: **none**. The retention behaviour is correct as implemented; what was missing
  was any test of it.
- Tests/gates: adds `PublicApiEodBarsAdapterTest::test_every_distinct_failure_reason_is_retained_and_the_primary_reason_does_not_replace_them`,
  a behavioural test driving two genuinely different provider failures through one run. The B08
  normalization also gains the two guards written for `MD-B07-A002`: a scope-completeness assertion,
  and preservation of proof state for rows owned by another stage.
- Compatibility: `E-MD-B08-A001-001` is not edited; only the newly bound predicate carries the A002
  pair.
- Evidence: issue `E-MD-B08-A002-001` after actual execution.

## Closure boundary

Closure requires the corrected predicate bound to executed proof at 139/139; the new behavioural
guard demonstrated to fail when retention is removed; no harmful residue; current evidence; complete
relationships; and all required integrity/governance gates passing.

## Actual impact and result

- **Traceability**: `MD-S067-R0010` promoted `REFERENCE_ONLY` → `REQUIRED`/`MANDATORY`/`SATISFIED`.
  B08 denominator 138 → **139**, stage-owned reference 95 → **94**. `b08_owned_examined` = **233 of 233**.
- **New guard proven falsifiable**: collapsing `summarizeYahooFailureReasons()` to its most frequent
  entry — the exact mutation that previously passed all 1946 tests — now fails the new test with
  `the transient-server cause was dropped from the retained set`.
- **Latent hazard closed**: `foreign_bindings_preserved` reports **41**. The B08 normalization
  carried the same unguarded clearing code that unbound 30 predicates during `MD-B07-A002`; it is
  now guarded here too.
- **A correction to my own reading**: the published primary reason for a partial run is the routing
  label `RUN_SOURCE_PARTIAL_RESPONSE`, which is not one of the two retained causes. The first draft
  of the test asserted it would be. The contract is stricter than that assumption, and the test now
  asserts what the contract actually says — the retained set is never reduced to the routing label.
- **Application source changed**: **NO**. **Strategy changed**: **NO**. **Storage**: not inspected,
  not mutated.
