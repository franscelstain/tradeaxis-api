# MD Change Impact Declaration — CI-MD-B01-A015-001

- ID: `CI-MD-B01-A015-001`
- Stage / Attempt / Baseline / Epoch: `MD-B01` / `MD-B01-A015` / `MD-B01-A015-BL001` / `MD-REBASELINE-20260820-001`
- Record class: `MUTABLE_TRACEABLE`
- Issued: 2026-08-21, after the A015 baseline lock and before any A015 test, implementation, or traceability mutation.

## Why this attempt is material

`MD-B01-A014` corrected the stage denominator from a false `FINAL` 143 to 207 and left 63 rows `NOT_ASSESSED`. Exactly one, `MD-S020-R0067`, is governed-blocked by `F-MD-B01-A003-001`. The other **62 are locally executable and carry a normalized predicate with no proof** — that is the entire A015 scope.

Promotion states that proof is owed. A promoted row that stays unproven is worse than an excluded one, because the denominator now counts it while nothing tests it.

## Affected strategy rules

62 rows, grouped by the semantic family that owns their proof:

| Family | Rules | Count |
|---|---|---|
| Domain ownership of canonical artifacts | `MD-S001-R0101`–`R0109`, `MD-S020-R0019`–`R0022`, `R0024`–`R0028`, `MD-S056-R0055`–`R0061` | 25 |
| Phase ownership | `MD-S056-R0114` | 1 |
| Canonical scope, dataset start, development frontier, decision-grade conditions | `MD-S001-R0006`, `R0007`, `R0009`, `R0062`, `MD-S056-R0006`, `R0007`, `R0009`, `R0012`, `R0014`, `R0031`, `R0032`, `R0034`, `R0035`, `R0039`, `R0040`, `R0041` | 16 |
| Boundary invariants stated declaratively | `MD-S020-R0160`, `R0162`–`R0169`, `R0172` | 10 |
| Anti-assumption claims | `MD-S001-R0142`–`R0145` | 4 |
| Provider limitation abstraction | `MD-S001-R0075`, `R0076`, `R0078`, `R0082` | 4 |
| Term ownership register | `MD-S056-R0143`, `R0144` | 2 |

`MD-S020-R0067` is explicitly excluded and remains `NOT_ASSESSED`.

## Planned proof method

1. Read the actual implementation for each predicate before deciding how to prove it. A predicate about behaviour gets behavioural proof; only a predicate that is genuinely static gets static proof.
2. Place each family with the suite that already owns it rather than creating parallel coverage — the anti-assumption family extends `AntiAssumptionClaimBoundaryTest`, provider limitation extends `DateDrivenCapabilityAndProviderAbstractionTest`, term ownership extends `TerminologyOwnerVocabularyTest`.
3. Prove ownership positively. "This domain owns X" is not shown by X's absence elsewhere; it needs the canonical surface located, and located inside market-data.
4. Prove the declarative boundary invariants the way the modal ones were proven: absence of the forbidden downstream sense **paired with a positive locator of the legitimate upstream sense**, so a rename cannot turn the suite green by making both searches find nothing.
5. Use the negation-safe within-sentence gap for every document-claim pattern, and assert each pattern both fires on the claim and stays silent on a quotation of the prohibition.
6. Verify every mutation landed before judging any guard.
7. Bind traceability only where the executed proof establishes the whole normalized predicate.

## Affected areas

| Area | Impact |
|---|---|
| Strategy | None; immutable authority remains byte-identical. |
| Schema / configuration / provider behaviour | No mutation planned. Existing surfaces are read as implementation proof. |
| Runtime / application code | No mutation planned. If a predicate turns out not to hold against actual code, the code is the thing that moves, not the predicate — that would be a material change and is declared here as possible rather than expected. |
| Backfill / replay / operations | No execution or behavioural mutation planned. |
| Tests / gates | Material: three new proof suites and three extended ones. |
| Traceability | Material: up to 62 rows may advance. `MD-S020-R0067` remains unchanged. |
| Evidence | Additive A015 evidence. A012–A014 evidence remains unedited. |
| Raw artifacts / storage | None expected. These predicates are source, schema-DDL, configuration, and document proof under §5 of the runtime-artifact standard. |

## Compatibility and residue risk

The dominant risk is proving a weaker predicate than the row states. Twenty-five of the 62 are ownership fragments whose obligation comes from a parent — "Domain ini tetap menjadi owner untuk canonical EOD bars" is not satisfied by a table named `eod_bars` existing. `STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` §3 is explicit that existence proof may not stand in for the stronger relation, and `MD-B01-A012` already invalidated two rows for exactly that.

Second risk: vacuous absence. Ten of the 62 are declarative prohibitions where a scan that matches nothing looks identical to a clean corpus.

Third risk: a guard flagging a document that is obeying the rule. This has recurred four times in this stage, and `MD-S020-R0172` — itself one of the rules under proof — states that a guard may not flag `candidate`, `target`, or `policy` on the word alone.

Harmful residue includes any row bound on partial predicate proof, an unlanded mutation, a guard whose population is unasserted, or a suite that duplicates a family another suite already owns.

## Dependencies and relationships

- Successor to `MD-B01-A014`; predecessor baseline `MD-B01-A014-BL001`.
- `F-MD-B01-A003-001` remains `OPEN` and continues to block `MD-S020-R0067`.
- `F-MD-B01-A014-001` remains `OPEN` and owned by `MD-B19`; it is not in this attempt's scope.
- `F-MD-B01-A001-001` and `MD-DEP-0004` remain `OPEN` for the 630 reference-only rows in unopened stages.

## Strategy semantic change

`NO`.

## Executed impact and result

- Strategy, schema, configuration, provider behaviour, backfill/replay behaviour, operator behaviour, and **runtime code** changed: `NO`. `git status` reports no residual change under `app/`, `config/`, or `authority/strategy/`, and sha1 recomputed across all 6490 matrix rows returns 0 fingerprint mismatches.
- Traceability effect: **all 62 scoped rows advanced from `NOT_ASSESSED` to `SATISFIED`** with `E-MD-B01-A015-001`. `MD-B01` moves from `144/207` to **`206/207`**. The denominator is unchanged — coverage moved because rules were proven, not because the set was adjusted. `MD-S020-R0067` remains `NOT_ASSESSED` with no evidence id, enforced by the binder and by its own mutation test.
- Test execution: **249 tests / 1779 assertions across 18 suites, zero failures and zero errors.** Four new suites (`DomainOwnershipSurfaceTest` 27/238, `CanonicalScopeFrontierAndDecisionGradeTest` 12/88, `BoundaryInvariantSemanticsTest` 11/81, `PromotedPredicateProofGateTest` 8/21) and four extended ones. The full `tests/Unit/MarketData` directory returns 1740 tests, 4 errors, 67 failures — identical to the pre-existing `MD-DEP-0003` baseline.
- Negative proof: nine on-disk mutations and six in-suite mutations, each asserted to have landed before the guard was judged. One did not land — the import stage-sequence needle assumed LF where the file is CRLF — which without the landing assertion would have read as a guard that does not guard. Third instance of that shape in this stage.
- **Application code changed: none, and that is the correct result.** Every predicate in scope was found to hold against the implementation as it stands. Section 24 forbids editing conforming code to manufacture implementation work.
- **Three test-tooling defects were found and fixed**, each discovered by writing the proof rather than by inspection:
  1. `ReadsMarketDataSchema::schemaColumnMap()` matched any `->name('x')` call, so `$this->dropExisting('md_listings', [...])` registered a table as a column of whichever table was open — from a helper whose purpose is to *remove* columns — and `->on('eod_runs')` leaked the same way. A guard asserting a column exists could be satisfied by a namesake table or by a column just dropped. Five suites share this trait; all re-executed green after the fix.
  2. `MarketDataOrdersOneToFourArchitectureTest` matched bare `Candidate` against class file names. `MD-S020-R0172` — itself one of the rules under proof — names that shape as non-conformant, and it would have flagged a `CandidatePublicationRepository`. `Candidate` now requires a downstream-sense compound, with a two-way fixture.
  3. `TermOwnershipAndPriceProductTest::ownedTerms()` was a hardcoded copy of the register naming 20 of its 33 terms, leaving 13 owned terms unguarded and creating a second place a term could be added — the arrangement `MD-S056-R0143` exists to prevent. The list is now parsed from the register; extending to all 33 produced zero new findings.
- Guard self-correction: the new `MD-S020-R0172` guard flagged its own adequacy probe, because a literal `/candidate/i` written to prove the scan can detect a violation is itself a bare-token pattern inside the scanned corpus. The probe is assembled at runtime; no self-exemption was added.
- Tooling added: `MarketDataPromotedPredicateProofGate` — atomic binder that refuses a partial binding, verifies every named test method and implementation surface exists, and keeps the finding-blocked rule out of the map. The A013 gate's satisfied-count lock moved from 144 to 206.
- Raw artifacts/storage: no `storage/**` artifact was required, inspected, mutated, exported, or claimed.
- Findings/dependencies: `F-MD-B01-A003-001` remains `OPEN` and is now the sole block on stage closure; `F-MD-B01-A014-001` remains `OPEN` and owned by `MD-B19`; `F-MD-B01-A001-001` and `MD-DEP-0004` remain open for the 630 reference-only rows in unopened stages.
- Downstream-stage effect: entry to `MD-B02` remains prohibited. `MD-B01` cannot close while one required row is unproven.

## Current boundary

The A015 executable scope is exhausted. `MD-B01` has **no locally executable proof work left** — the one remaining row is blocked by the wording of a frozen strategy contract, which no implementation attempt may change. The single re-entry point is `MD-B01-A016`, after an authorised strategy-change process resolves `F-MD-B01-A003-001`.
