# Change Impact Declaration — `MD-B06-A001`

- ID: `CI-MD-B06-A001-001`
- Stage / Attempt / Baseline / Epoch: `MD-B06` / `MD-B06-A001` / `MD-B06-A001-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260822-001`
- Stage precondition: `SC-MD-B05-A001-001`
- Dependencies: `MD-DEP-0004` stage-entry normalization; `MD-DEP-0003` current-authority replacement-guard review for the trading-status/operator surface
- Status: `EXECUTED_COMPLETE`
- Strategy meaning change: `NO`

## Objective

Revalidate and, where required, remediate the calendar/session/trading-status expectation layer against `MD-S041` and `MD-S058`. The stage must prove that requested-date and expected-bar decisions use governed point-in-time calendar, listing, and status facts; current-state guessing, provider absence, wall-clock guessing, projected-calendar promotion, ungoverned source authority, and ambiguous/conflicting status must fail closed.

## Authority and traceability scope

- `MD-S041` — Market Calendar Requirements Contract: all 84 extracted rows, including the provisional 34 required plus one optional row, require semantic review rather than inheritance.
- `MD-S058` — Trading Status Source Contract: all 74 extracted rows, including the provisional 31 required rows, require semantic review.
- `MD-DEP-0004`: resolve every transitional applicability value, bind parent/child context, correct the 25 mixed-classification reference members, confirm proof ownership, recompute the denominator, and revalidate all resulting MD-B06 predicates.
- Rows whose executable owner is another stage will move with `MD-B06` supporting linkage; headings, introducers, examples, capability limitations, and bare cross-contract pointers remain reference-only unless they express an independently testable predicate.

## Impact assessment

- Strategy: no strategy byte changes are authorised or expected.
- Schema/data: inspect calendar, calendar-revision, status observation/event, source-authority, and expectation persistence. Any material schema gap requires additive migration and SQLite mirror proof; no historical row rewrite or invented default is permitted.
- Configuration: inspect timezone, cutoff, source registry, and calendar/status version bindings. No undocumented resolver key or current-environment substitution is permitted.
- Runtime: calendar provenance tiering, completed-session resolution, shortened-session visibility, trading-window traversal, temporal/as-known status resolution, source authority/priority/conflict handling, explicit `EXPECTED`/`NOT_EXPECTED`/`UNKNOWN`, and latest expected-date separation.
- API/contracts: requested non-trading dates and unknown/inconsistent completion must not finalize a trading-date success; output must retain reason and source/version identity.
- Backfill/replay: no bulk backfill is planned. Replay/as-known behavior is verified where MD-B06 owns the temporal primitive; full replay lifecycle remains with its registered downstream owner.
- Tests/gates: add or repair behavioral positive and negative tests, exact stage-scoped normalization/proof gates, mutation self-tests, and current evidence binding.
- Operations: inspect the trading-status import/authority command guard required by `MD-DEP-0003`; do not claim operational completeness from a removed historical inventory.
- Compatibility: preserve stable listing identity and the closed MD-B05 temporal boundary; no current ticker, `is_active`, dormancy, volume, or provider-response compatibility fallback may establish historical expectation.
- Residue/rework: target current-date/calendar subtraction, current-status lookup, missing-bar-as-suspension inference, projected-as-verified calendar rows, ungoverned manual source authority, majority/recency conflict resolution, and stale command guards.
- Evidence: new A001 governed evidence only; historical evidence is supporting context and is not edited or inherited.
- Relationships: baseline-to-MD-B05 precondition, CI-to-baseline, evidence-to-baseline/CI, and closure relationships must be explicit.
- Downstream stages: moved predicates remain unproven at their owners; no later stage is opened in this attempt.
- Raw artifacts/storage: no broad `storage/**` scan. Deterministic repository/database/test proof is expected; if a retained external execution artifact becomes material, its path/hash/manifest will be correlated before use.

## Closure boundary

Closure requires a final MD-B06 semantic denominator with no transitional applicability or mixed-classification debt, conformant actual implementation, positive and fail-closed proof for every owned predicate, resolved applicable portions of `MD-DEP-0003`/`MD-DEP-0004`, conformant residue, current evidence, complete relationships, and all required gates.

## Executed impact result

- Strategy: no byte changed and no semantic owner moved for closure convenience.
- Schema/data: additive authority metadata/source registry migration applied; immutable historical calendar/status evidence was not edited. Unsupported legacy calendar claims receive appended non-authoritative successors.
- Runtime/API: all calendar consumers now resolve the canonical temporal revision set; status/expectation binds stable identity, temporal board, governed type/source/priority, accepted observation hash and as-known cutoff.
- Backfill/replay: temporal roots and full pipeline were revalidated; no storage replay/backfill artifact was required or claimed.
- Tests: the final Market Data unit suite passes at 1800 tests / 15567 assertions, including positive, negative, fail-closed, migration and gate mutation proof.
- Compatibility/residue: compatibility tables remain only as non-authoritative storage; targeted search finds no executable application/test-support read from `market_calendar`.
- Dependencies: the MD-B06 portion of `MD-DEP-0003` and the MD-B06 entry obligation of `MD-DEP-0004` are discharged. Both dependencies remain open non-blocking for their explicitly separate downstream scopes.
- Evidence/relationships: current evidence is `E-MD-B06-A001-001`; baseline, CI, precondition, dependency and closure relationships are registered before closure.
