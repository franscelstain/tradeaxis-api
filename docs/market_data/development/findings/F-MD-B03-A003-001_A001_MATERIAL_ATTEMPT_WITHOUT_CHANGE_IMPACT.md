# F-MD-B03-A003-001 — `MD-B03-A001` was a material attempt with no Change Impact Declaration

- Status: `CLOSED`
- Severity: `P2`
- Stage / Attempt / Baseline / Epoch: `MD-B03` / `MD-B03-A003` / `MD-B03-A003-BL001` / `MD-REBASELINE-20260820-001`
- Owning stage for remediation: governance — the deviation is historical and cannot be repaired by an implementation attempt
- Blocks: `MD-B03` reaching `DONE`
- Dependency: `MD-DEP-0006`

## Finding

`CHANGE_IMPACT_DECLARATION_STANDARD.md` §1 requires every material current attempt to issue a correlated `CI-*` record using the attempt Work ID. §3 states the consequence without qualification:

> For a material attempt, a missing/unregistered Change Impact Declaration blocks `DONE`, even if tests and other gates pass.

`MD-B03-A001` was material by the standard's own list. It changed migrations, a seeder, and a test generator, fixing seven defects — including two `hasIndex()` guards that swallowed every `Throwable` and always answered "index absent", which broke clean install at migration 3 of 62. The work-record registry holds `MD-B03-A001-BL001`, `E-MD-B03-A001-001`, and `E-MD-B03-A001-002` for that attempt. It holds no `CI-MD-B03-A001-001`.

`MD-B03-A002` and `MD-B03-A003` each issued theirs. The gap is A001's alone.

## Why this attempt did not fix it by writing one now

§3 requires the declaration to "exist early enough to guide the attempt's validation scope". A record written two attempts later guides nothing; it would assert a timing that did not happen.

The execution contract is explicit on the same point: a Change Impact Declaration must not be created only after the changes are complete when governance asks for a pre-change assessment, and a historical timing deviation must not be hidden or repaired by editing old evidence. Producing `CI-MD-B03-A001-001` today would satisfy the letter of §1 by falsifying §3, and would leave the registry looking clean while recording something untrue.

So the deviation stands as a fact, and this finding is the record of it.

## What the deviation did and did not cost

It did not cost proof. A001's seven fixes were each proven by execution and are recorded in `E-MD-B03-A001-001` and `E-MD-B03-A001-002`; `MD-B03-A003` re-executed the stage's exit criteria against a live database rather than inheriting them, and both hold. Nothing in the stage's technical state depends on the missing record.

What it cost is the control the declaration exists to provide: an impact assessment written *before* the change, naming what could break. A001 fixed a migration guard and, in doing so, exposed two further `P0` defects that only execution revealed. That is exactly the class of surprise a pre-change impact assessment is meant to anticipate.

## Required outcome

A reviewed governance decision, of the same class the `MD-DEP-0005` resolution needs. Either:

1. record that the A001 deviation is accepted, with the reasoning, so `MD-B03` may close with the deviation declared rather than concealed; or
2. record that a material attempt without a declaration can never contribute to a `DONE` stage, in which case `MD-B03` stays `IN_PROGRESS` permanently and its deliverables are carried by the stages that consume them.

Both are defensible. What is not defensible is closing the stage while the criterion is unmet and unmentioned, or writing the missing record and calling the criterion met.

## Related

- Independent of `F-MD-B01-A003-001` / `MD-DEP-0005`, which blocks `MD-B01` on frozen strategy wording. The two are the same shape — an implementation stage held by a decision only governance can make — and are the reason no stage in this track can currently reach `DONE`.
- `MD-B03`'s technical closure criteria are otherwise met; see `E-MD-B03-A003-001`.

## Closed — D-MD-20260822-05

Accepted as a historical process deviation. **No Change Impact Declaration was written for `MD-B03-A001`, and no A001 baseline, evidence, or registry row was edited.**

The acceptance rests on revalidation having actually happened, not on the deviation being old. All seven A001 defects carry current executed proof under attempts that did declare their impact: D1 and D2 by the A003 clean install (62/62 migrations, exit 0) and seeder run (43 → 436); D3 and D4 because that run applied from empty the two migrations that previously died on duplicate keys; D5 by measurement — 5 documentation-path references across `app/`, `config/`, `database/`, all resolving; D6 by the relationship gate self-test executing; D7 because every baseline lock since was produced by the corrected tool.

### The one risk that was closed rather than accepted

A pre-change declaration exists to name what could break before a change is attempted. That risk materialised inside A001 — fixing D3 exposed D4 — and was caught by execution rather than by declaration.

The residual concern was D5's surface: it touched four executable trees, and only `tests/` was guarded against a documentation path going dead again. The paths measured clean, but "clean today" is not "cannot regress". `TestPathBindingIntegrityTest` now scans `app/`, `config/`, and `database/` as well, covering exactly what A001 changed, mutation-proven against an `app/` class binding to a removed inventory.

### Why the record was still not written

`CHANGE_IMPACT_DECLARATION_STANDARD.md` §3 requires the declaration to exist early enough to guide the attempt's validation scope. A record produced three attempts later guides nothing; it would satisfy §1 by falsifying §3. The deviation stands as a fact in this finding and in `D-MD-20260822-05`.

### Not a precedent

A future material attempt without a declaration is blocked by §3 exactly as before. The decision is not authority for accepting one whose technical proof has **not** since been re-established under a declared attempt.

`MD-B03` closed with `SC-MD-B03-A003-001`.
