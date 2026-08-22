# Decision — `MD-B03-A001` accepted as a historical process deviation

- ID: `D-MD-20260822-05`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline: `MD-B03` / `MD-B03-A003` / `MD-B03-A003-BL001`
- Finding: `F-MD-B03-A003-001`
- Dependency: `MD-DEP-0006`
- Issued: 2026-08-22
- Decision status: `ISSUED`
- Strategy impact: `NONE`
- Historical records modified: `NONE`

## Question

`MD-B03-A001` was a material attempt with no correlated Change Impact Declaration. `CHANGE_IMPACT_DECLARATION_STANDARD.md` §3 is unqualified: "For a material attempt, a missing/unregistered Change Impact Declaration blocks `DONE`, even if tests and other gates pass."

Every other `MD-B03` closure criterion is met. The question is whether this deviation can be accepted as historical without fabricating retroactive compliance.

## Review

**Does the missing declaration change the technical truth of A001's proof?**

No. A Change Impact Declaration is a pre-change impact assessment that constrains scope and names what could break. It is not a proof instrument. It neither validates nor invalidates an executed result. A001's results were produced by execution and recorded in `E-MD-B03-A001-001` and `E-MD-B03-A001-002`.

**Are A001's seven defects currently proven under properly declared attempts?**

Each was re-established, not inherited:

| | Defect | Current proof |
|---|---|---|
| D1 | clean-install migration bound to a removed schema path | `MD-B03-A003` re-executed clean install on an isolated database: 62/62 migrations, one batch, 51 tables, exit 0 |
| D2 | reason-code seeder bound to a removed seed path | `MD-B03-A003` re-executed the seeder on that clean install: `eod_reason_codes` 43 → 436 |
| D3 | `hasIndex()` swallowed every `Throwable` and always answered "index absent" | the A003 clean install applied `2026_06_04_000001` from empty, which is the case that previously died on a duplicate key |
| D4 | four unguarded index adds on `ticker_sector_memberships` | the same run applied `2026_08_08_000001`, previously fatal on `uq_sector_membership_listing_effective_known` |
| D5 | 79 dead documentation paths across 38 files in `app/`, `database/`, `config/`, `tests/` | measured at this review: 5 documentation-path references remain across `app/`, `config/`, `database/` and **all resolve**; the suite is green at 1683 tests |
| D6 | relationship gate self-test asserted nothing | `MarketDataRelationshipIntegrityGateSelfTest` executes and exits 0, re-run at this review |
| D7 | baseline lock tool omitted four required fields | every baseline lock issued since — A012 through A016, `MD-B03-A002`/`A003`, `MD-B00-A003` — was produced by the corrected tool |

`MD-B03-A002` and `MD-B03-A003` both issued Change Impact Declarations before their material changes, and A003 explicitly re-executed rather than cited the A001 run.

**Was there material risk the timing deviation left unassessed?**

One, and it is worth being precise rather than reassuring.

The function a pre-change declaration performs is naming what a migration fix could break *before* it is attempted. That risk materialised twice inside A001: fixing D3 exposed D4. It was caught by execution rather than by declaration — the outcome was correct, but it was correct by discovery, which is what the declaration exists to reduce.

The residual concern is D5's surface. It touched four executable trees, and until this review only `tests/` was guarded against a documentation path going dead again — three of the four surfaces A001 repaired could have regressed unnoticed. The paths were measured clean, but "clean today" is not "cannot regress".

**That gap is closed as part of this decision rather than accepted alongside it.** `TestPathBindingIntegrityTest` now scans `app/`, `config/`, and `database/` in addition to `tests/`, covering exactly what A001 changed. A mutation binding an `app/` class to a removed inventory was verified as landed and turns the guard red.

No other unassessed material risk was identified. A001 changed migrations, a seeder, a test generator, and path strings; it changed no strategy, no schema shape, no runtime behaviour, and no configuration value.

**Can current closure rely on subsequent governed proof?**

Yes. Every A001 defect has current executed proof under an attempt that declared its impact, and the one surface whose regression risk was unguarded is now guarded.

## Decision

1. The `MD-B03-A001` timing deviation is **accepted as a historical process deviation**. It is recorded permanently, in `F-MD-B03-A003-001` and here, and is not erased.
2. **No Change Impact Declaration is written for `MD-B03-A001`.** §3 requires the declaration to exist early enough to guide the attempt's validation scope; a record written three attempts later would satisfy §1 by falsifying §3. No baseline, evidence, or registry row of A001 is edited.
3. The deviation **does not block `MD-B03` closure**, because the technical proof it failed to precede has since been re-established under attempts that did declare their impact, and the residual regression risk it created is now under a mutation-proven guard.
4. `MD-DEP-0006` resolves. `F-MD-B03-A003-001` closes as accepted-with-deviation-recorded.
5. `SC-MD-B03-A003-001` may be issued if, and only if, every remaining closure criterion in `E-MD-B03-A003-001` is independently confirmed at issuance.

## What this decision is not

It is not a general waiver. A future material attempt without a declaration is blocked by §3 exactly as before, and this decision is not precedent for accepting one whose technical proof has *not* since been re-established under a declared attempt. The acceptance rests on the revalidation having actually happened, not on the deviation being old.

## Related

- Independent of `D-MD-20260822-04`, which concerns semantic ownership in `MD-B01`.
- `CHANGE_IMPACT_DECLARATION_STANDARD.md` is unchanged; no governance text was revised to reach this outcome.
