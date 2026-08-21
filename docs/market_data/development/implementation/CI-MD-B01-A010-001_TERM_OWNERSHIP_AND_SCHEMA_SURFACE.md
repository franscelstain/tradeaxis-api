# MD Change Impact Declaration — CI-MD-B01-A010-001

- ID: `CI-MD-B01-A010-001`
- Stage / Attempt / Baseline / Epoch: `MD-B01` / `MD-B01-A010` / `MD-B01-A010-BL001` / `MD-REBASELINE-20260820-001`
- Record class: `MUTABLE_TRACEABLE`
- Issued: 2026-08-21, **before** the matrix mutation and after the guard was written and mutation-proven.

## Why this attempt is material

It changes `coverage_status` on traceability rows, adds an executable test, and **modifies the schema-reading instrument used by two already-closed attempts**. The third is the reason this declaration is longer than its predecessors.

## Scope

13 rules move `NOT_ASSESSED` → `SATISFIED`, all owned by `Terminology_and_Scope.md`:

| Rules | Proof |
|---|---|
| `MD-S056-R0141` | Term ownership. No active document carries a competing definition of a term the register owns. |
| `MD-S056-R0054`, `R0062` | The raw source observation surface carries provenance, source and acquisition timestamps, and observation identity, and is a distinct surface from canonical bars. |
| `MD-S056-R0063`, `R0064` | `RAW` and `STRUCTURAL_ADJUSTED` are declared price products carried by a product/basis column, with `STRUCTURAL_ADJUSTED` resting on versioned corporate-action factor sets. |
| `MD-S056-R0065` | One indicator run binds one explicit basis through a single selector, and provider adjusted close never participates in it nor acts as a per-row fallback. |
| `MD-S056-R0067` | An unresolved or unverified factor blocks through eligibility rather than authorising a mutation of history. |
| `MD-S056-R0100`, `R0107`, `R0108` | Liquidity is published as a named measure with unit and explicit proxy label; rejected rows have their own surface and record why; indicators are versioned. |
| `MD-S056-R0033`, `R0096`, `R0098` | Every windowed computation returns a deterministic `NULL` until its warm-up history exists; per-ticker failure is expressible as partial rather than fatal; denominator exclusion rests on point-in-time trading-status evidence while the legacy dormancy exclusion is registered inactive and emitted by no runtime path. |

## Deliberately not claimed

`MD-S056-R0142` requires that a summary of these terms in another document carry a pointer back to the register and the precedence rule. No active document summarises an owned term's meaning — the corpus uses and refers to the terms, which the register explicitly permits. An affirmative obligation with no subject cannot be proven, the same reasoning applied to `MD-S001-R0155` and `R0158` at `MD-B01-A008`. It becomes provable the moment a document does summarise one of these terms.

## The instrument correction

The deployed schema for this domain is **not** the migrations alone. `2026_03_22_000003_create_market_data_core_schema.php` executes `db/Database_Schema_MariaDB.sql`, which creates 28 tables; the migrations then extend it. The guards written at `MD-B01-A007` and `MD-B01-A009` read only `database/migrations/**`, so each proved its claim over a subset of the real surface — 456 identifiers rather than 582.

**No prior claim turned out to be wrong.** Before changing anything, the base SQL was searched directly for every forbidden downstream concept and every downstream dual-use half. The only matches were three words on a single SQL comment line stating the disownment itself — "`eligible` is not ranking, alpha, tradability approval, or watchlist policy" — which is the contract being honoured. Zero identifiers breach either rule. `MD-S020-R0054`, `R0088`, `R0096`, `R0101`, and `R0171` therefore stand, and now stand over the full surface.

The correction lives in one place, `tests/Support/MarketData/ReadsMarketDataSchema.php`, so that what counts as "the schema" has a single definition rather than three drifting copies. It strips comments from both sources, because a comment stating an obligation is not a surface breaching it.

Two mutations were run specifically to prove the widening is real: a forbidden `alpha_score` column and a `lot_size` column, both placed in the base SQL where neither guard could previously see them. Both now turn their guard red.

## Affected areas

| Area | Impact |
|---|---|
| Traceability | **Material.** `MD-B01` `85/155` → `98/155`. Global `SATISFIED` 85 → 98; denominator unchanged at 2010. |
| Tests | **Material.** One test file added (13 tests, 41 assertions) and one shared support trait added. Two previously-closed guards had their schema reader widened; both still pass, with `MD-B01-A007` now at 582 identifiers and `MD-B01-A009` over the full surface. |
| Prior evidence | `E-MD-B01-A007-001` and `E-MD-B01-A009-001` remain valid and are not edited. Their proofs are re-executed at this baseline over the widened surface and recorded here, with explicit relationship rows. |
| Schema / config / runtime / strategy | **None.** Nine files were mutated transiently during the negative proof and each was restored and verified byte-identical. |
| Runtime artifacts | **None.** Section 5 of `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md`; no `storage/**` inspection required or performed. |

## Compatibility risk

**Low.** Nothing existing changes behaviour. The widened guards are strictly stricter — they can only turn a future `PASS` into a `FAIL`.

## Residue / rework risk

**Low.** Ten mutations each turned their guard red, every one verified as landed first; two controls confirmed the guards stay green on a base-SQL comment stating the disownment and on a permitted role statement about an owned term.

One guard defect was found by the negative proof and fixed before any rule was claimed. The first `MD-S056-R0033` assertion checked that *a* warm-up guard existed somewhere in the indicator engine. There are three windowed computations — `averageTurnover`, `windowExtreme`, `movingAverage` — each with its own guard, so removing one left the test green: a guard satisfied by any survivor. It now enumerates every function taking a `$window` parameter and requires each to compare available history against its window and return `NULL`. Re-run per function, all three now fail closed.

The mutation that exposed it first reported `NO_OP` twice because the pattern could not span the inner parentheses in `($index + 1) < $window`. Judging the guard on either run would have been wrong in opposite directions; the standing rule that a mutation must be verified as landed before its verdict is read is what caught it.

An inverted proof was caught by an existing guard. The first `MD-S056-R0098` proof asserted that the dormancy exclusion reason code must be **present**, which is the opposite of the rule. `GlobalConvergenceClosureTest::test_no_test_expects_dormancy_to_shrink_the_denominator` failed in the full suite — it permits a test to mention the deprecated code only in an assertion that it never appears. The code is registered inactive and described as a legacy reason whose emission blocks V2 relock; the rule reads in full that denominator exclusion requires point-in-time evidence that a bar was not expected, such as verified suspension or market status, and dormancy is precisely not such evidence. The rule is now proven by the evidence columns, by exclusion resting on verified trading-status events, and by no runtime path emitting the deprecated code. The claim had already been applied to the matrix when this surfaced, which is what the full-suite regression control is for.

Residual risk, stated rather than hidden: `MD-S056-R0141` proves no document carries a *competing definition*, using the contract's own test of two substantive definitions in two documents. A definition that drifts by implication rather than by statement is not mechanically detectable and is not claimed.

## Affected dependencies and relationships

- `MD-DEP-0004` — unaffected, remains `OPEN_NON_BLOCKING`.
- `F-MD-B01-A008-001` — unaffected; no rule it blocks is claimed here.
- Continuity edge to `E-MD-B01-A009-001`, and re-execution edges to `E-MD-B01-A007-001` and `E-MD-B01-A009-001` for the widened surface.

## Strategy semantic change

`NO`. `Terminology_and_Scope.md` is read as the owner contract and is not modified.
