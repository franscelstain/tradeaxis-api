# MD Change Impact Declaration — CI-MD-B05-A001-001

- ID: `CI-MD-B05-A001-001`
- Stage / Attempt / Baseline / Epoch: `MD-B05` / `MD-B05-A001` / `MD-B05-A001-BL001` / `MD-REBASELINE-20260820-001`
- Record class: `MUTABLE_TRACEABLE`
- Issued: 2026-08-22, after `MD-B05-A001-BL001` and before any A001 matrix, schema, implementation, test, or current-state mutation.

## Material scope

`MD-B05` opens with a provisional 78-row denominator: two `MANDATORY` rows carried in from other documents, 76 transitional `MANDATORY_OR_CONDITIONAL` rows, one `OPTIONAL_CAPABILITY` row, and 89 `REFERENCE_ONLY` rows, of which the classification gate reports 27 as reference members sitting inside mixed-classification runs. The `MD-DEP-0004` entry obligation must be discharged before any denominator is treated as final.

Authority scope is three owner contracts held entirely by this stage — `MD-S052` `Sector_Classification_Contract_LOCKED.md`, `MD-S055` `Symbol_Lifecycle_and_Mapping_Contract.md`, `MD-S057` `Tickers_and_Identity_Dependency_Contract_LOCKED.md` — plus two predicates already assigned here by earlier attempts: `MD-S020-R0011` (temporal identity, universe, calendar and status correctness) and `MD-S082-R0163` (the `market_data.tickers.*` legacy projection boundary).

Initial row identity is discovery input, not a promised denominator. Normalization may promote a reference row to executable semantics or move a proof obligation to the stage that can actually execute it, without changing a strategy byte.

## Planned proof and implementation method

1. Derive whole predicates from the three owner contracts, binding each list member to the introducer that carries its obligation rather than counting fragments as independent requirements.
2. Classify strictly by structure. A row is reference only when it is a heading, a list introducer, a bare label, a table header, or a bare document reference. Grammatical mood is not a classification input; encoding it would rebuild the defect `F-MD-B01-A001-001` names.
3. Assign each required predicate to the stage that can execute its proof. Observation binding, publication snapshots, coverage exposure, replay, and sector-derived measures are named in these contracts but are not provable at `MD-B05`; those move, with `MD-B05` retained as supporting stage.
4. Inspect the actual temporal identity and sector membership implementation — `md_issuers`, `md_instruments`, `md_listings`, `md_listing_symbols`, `md_provider_symbol_mappings`, `ticker_sector_memberships`, `TemporalIdentityRepository`, `SectorClassificationRepository`, `TickerMasterRepository`, `EquityProviderSymbolResolver` — against those predicates before writing any record.
5. Where inspection exposes an executable defect, fix the implementation first and prove the fix, then bind records. Two candidates are already identified below and are the reason this attempt expects schema and application-code mutation rather than revalidation alone.
6. Prove behaviorally wherever the predicate governs resolution, interval arithmetic, ambiguity, or failure. Static source proof is reserved for genuinely structural predicates such as layer boundaries and the absence of a forbidden key.
7. Bind traceability only after the stage-scoped normalization and proof gates pass with their mutation tests, then evaluate residue, evidence, relationships, and closure.

## Executable defects this attempt expects to remediate

Both were found by reading the implementation against the contract during scope derivation, before any mutation. Each is stated as a defect to be reproduced and fixed, not as a conclusion.

**1. Board and market segment are not effective-dated.** `md_listings.board_code` and `md_listings.market_segment` are single mutable columns. `MD-S057-R0031` requires board or market-segment movement to be effective-dated and to leave the prior listing context intact; `MD-S057-R0039` requires point-in-time resolution to return the market segment and board valid on `T`; `MD-S055-R0017` requires Regular-Market observations to retain the listing/board context valid on their trade date. The current schema cannot express a board move except by overwriting the column, which rewrites every historical resolution silently. Expected remediation is an additive effective-dated board/segment record, resolution that reads it, and a projection that seeds the opening interval — with the existing columns demoted to the cached current-state projection `MD-S057-R0017` permits.

**2. The conditional `OPERATOR_ENTERED` authority class is unconditional on the read side.** `SectorClassificationRepository::AUTHORITATIVE_CLASSES` admits `OPERATOR_ENTERED` at resolution without checking the condition `MD-S052-R0012` attaches to it — an explicit authoritative reference, a named operator, and a governed reason code. The write path enforces the triple; the read path does not, and `operator_name` and `reason_code` are nullable with no database constraint tying them to the class. A row reaching the table by any other route resolves as authoritative membership. Expected remediation is read-side fail-closed resolution with an explicit reason code, so an incompletely governed operator row resolves `UNKNOWN` rather than establishing a sector.

## Affected areas

| Area | Expected impact |
|---|---|
| Strategy | No byte or semantic change. The three owner contracts are the target of proof, not of revision. |
| Schema/data/migrations | **Material.** An additive effective-dated board/segment record is expected. No destructive change, no column drop, no backfill that invents a value. |
| Configuration | Inspect `market_data.tickers.*` and `market_data.sectors.*` against `MD-S082-R0163`; mutate only if non-conformant. |
| Runtime/API/contracts | **Material.** Identity resolution and sector resolution are expected to change where the two defects above are confirmed. |
| Backfill/replay/operations | No execution expected. Replay-mode predicates in these contracts are owned by `MD-B18`; the as-known coordinate is proven here only as a resolution input. |
| Tests/gates | **Material.** A stage-scoped normalization tool and traceability/proof gate, plus behavioral suites for identity, mapping, and membership resolution. |
| Compatibility/residue | Check legacy `ticker_id` propagation, current-state leakage into historical resolution, silent fallback to the current mapping or current sector, and fabricated identity. |
| Traceability/evidence | **Material.** Classification, applicability, parent context, proof ownership, evidence binding, coverage, and current state will change. |
| Dependencies/relationships | Performs the `MD-B05` entry portion of `MD-DEP-0004`. Stage entry depends on `SC-MD-B04-A002-001` and the closures preceding it. |
| Downstream stages | A moved obligation stays open at its actual proof-owning stage. No downstream proof is inherited and none is claimed. |

## Raw artifact/storage boundary

No `storage/**` artifact is referenced or expected. Behavioral proof runs against the in-repository SQLite mirror used by the existing market-data suites, which is repository state rather than a runtime artifact. If a live database, replay, or backfill probe becomes necessary this declaration must be updated before execution and the artifact linked under `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md`. No recursive storage scan is authorised.

## Risk and failure boundary

The dominant risk in this stage is proving the mechanism and claiming the guarantee. These contracts say so explicitly: a survivorship-free resolver is not a survivorship-free universe, an internally consistent mapping set is not a complete one, and a resolved sector is evidence that a recorded classification covered the date and never that the exchange classified it that way. Any proof that blurs those two must be rejected in review.

Secondary risks are counting a bare field name as a predicate without its introducer, promoting a row this stage cannot execute, letting a schema change rewrite historical context while adding temporality, and adding a fail-closed branch without proving it can fire. Every mutation used for negative proof must be asserted to have landed before the guard is judged.

## Strategy semantic change

`NO`.

## Current status

`EXECUTED — READY FOR CLOSURE EVALUATION`.

## Actual impact and result

- **Schema**: one additive migration, `2026_08_22_000001_add_temporal_listing_board_intervals.php`. It creates `md_listing_boards` and opens one interval per existing listing from dates that listing already records. Nothing dropped, nothing rewritten, no value invented — a listing whose segment is null is skipped rather than defaulted. Applied to the reachable development database: 977 listings, 977 intervals, 0 listings left unresolvable, 0 intervals disagreeing with their listing dates.
- **Application code**: three files. `TemporalIdentityRepository` resolves board and segment from the temporal record, opens the interval during projection, and fails closed on two intervals covering one date. `SectorClassificationRepository` applies the `OPERATOR_ENTERED` condition at resolution. `MarketDataPipelineService` passes acquisition context unconditionally.
- **Both defects named in this declaration were confirmed**, and a third was found during the work: the pipeline's context-free acquisition branch. It falls inside the declared runtime area rather than outside the declared scope.
- **Tooling**: a reviewed classification spec, a normalization tool, a stage traceability gate, a proof gate, and an atomic binder. `MD-B05` joined the classification gate's normalized-stage set. The relationship gate self-test was repaired: its completeness probe removed the last registry row, which only worked while the newest edge happened to be a required one.
- **Tests**: seven new suites and one gate self-test, 64 tests. Four existing suites had fixtures completed for the new temporal record, and five `MarketDataPipelineServiceTest` expectations now pin the four-argument acquisition call including the enforcement flag — a stronger assertion than the shape they replaced.
- **Traceability**: the provisional 78-row denominator was replaced by **117 mandatory** rows, all `SATISFIED` and bound to `E-MD-B05-A001-001`. 58 reference rows were promoted, 71 bound to a governing parent, 20 moved to their actual proof-owning stage, and 31 remain structurally reference-only. The mixed-classification backlog fell from 579 to 552.
- **Strategy changed**: **NO**. No byte of the three owner contracts changed.
- **Storage**: not inspected, not mutated. One database mutation, declared above.
- **Residue**: `CONFORMANT_NO_HARMFUL_RESIDUE_FOUND`, with 36 pre-refactor documentation pointers named as belonging to `F-MD-B00-A001-001` rather than absorbed.
- **Governed evidence**: `E-MD-B05-A001-001`. **Closure**: `SC-MD-B05-A001-001`.
