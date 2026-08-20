# Legacy Semantic Extract — LX-MD-0241-CTX-01

- Source ID: `LS-MD-0241`
- Original path: `tests/Contract_Tests_Specification.md`
- Original SHA1: `AC585FDD0F64F74D1BDDB9F511D8D24A57C87147`
- Extract role: `CONTEXT`
- Source range: `L7-L49`
- Extract body SHA1: `766E9B9644D3205F2794FB7368E94D03FDBD1EFE`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## What a source-text assertion proves (LOCKED)

A test that reads implementation source and asserts on its text establishes exactly one thing: **that the text is present**. It is a lint, and it must be counted as one.

- A source-text assertion proves neither old meaning nor new meaning. It proves a string exists in a file, which survives a rewrite that preserves the string and changes the behaviour, and fails on a rewrite that changes the string and preserves the behaviour.
- Such a test may guard against **reintroduction of a named forbidden construct**, which is a legitimate and narrow purpose. It may not stand as the proof that a contract rule holds.
- Every contract rule requires at least one assertion that **executes the path it governs**. Where only a source-text assertion exists for a rule, that rule is **unproven**, and the suite must not be described as covering it.
- A rule whose only feasible check is textual states that explicitly, so its weakness is visible rather than absorbed into an aggregate pass count.

Counting rules: a green suite is reported with its behavioural and source-text proportions separated. An aggregate figure that mixes them overstates coverage by exactly the share that never executed anything.

## Fixtures must exist as artifacts (LOCKED)

A fixture catalog, manifest, oracle, or vector document describes what a fixture must contain. It is not the fixture.

- A specification without a corresponding artifact proves nothing and must not be cited as coverage. The correct reading of a fully specified, unbuilt fixture set is **capability absent**, not **coverage pending**.
- Every fixture document names where its artifacts live, so their presence or absence is checkable rather than assumed.
- An acceptance criterion that depends on a golden fixture is **unmet** while the fixture does not exist. It is not partially met by the specification being thorough.

Each test identifies contract/version, fixture/manifest hash, runtime/database/build, frozen input revisions/config, assertion layers, and evidence admission state.

## Required test groups

1. **Acquisition/provenance:** immutable success/failure/stale/schema-invalid observations, secret sanitization, refetch linkage, provider-neutral mapping, wrong-date rejection.
2. **Temporal identity:** inactive-now/active-then, listing/symbol changes, symbol reuse, provider mapping intervals, as-known cutoff.
3. **Calendar/status/expectation:** completed IDX Regular-Market sessions, verified full-session no-bar state, unknown expectation, no dormancy/provider-absence exclusion.
4. **Canonical bars:** exact source mapping, positive/internal OHLC consistency, zero/null rejection, duplicate conflict quarantine, actual EOD field provenance.
5. **History/correction:** observation and snapshots immutable, corrected publication distinct, pointer switch atomic, rollback by pointer, direct repair impossible.
6. **Corporate actions/products:** synthetic detector never verifies, ex-date and versioned event/factor lifecycle, coherent structural OHLC/volume, RAW unchanged, total return separate.
7. **Coverage/eligibility:** expectation/delivery/quality/liquidity/status/event facts independent; unknown in denominator; complete reason sets.
8. **Metrics/indicators:** actual versus proxy units, stable Wilder ATR long chain, warm-up/gap/correction propagation, fixed windows, null reasons, formula/config/hash determinism.
9. **Readiness/consumer:** full minimum DTO, same publication/config/factor/formula, explicit fresh/stale/degraded/unavailable, no current/latest/internal bypass.
10. **Replay/backtest:** exact publication and as-known modes, anti-future/anti-survivorship fixtures, original/corrected contexts.
11. **Operations/schema:** scheduler target/lock fencing/idempotent retry, activation-aware stale alert, MariaDB clean install/upgrade, SQLite mirror, non-null enforcement and privileges.

## Assertion layers

Required scenarios assert row values/null reasons, observation and temporal lineage, run/gate/reason states, artifact/manifest/config hashes, publication/seal/pointer state, gateway response/freshness, and replay result. Row count or exit code alone is insufficient.

## Database requirement

Portable unit tests may use SQLite, but uniqueness/check/FK/transaction/isolation/locking and migration behavior are proven on MariaDB. Any unsupported SQLite behavior is an explicit non-pass until MariaDB proof exists.


<!-- LEGACY_EXTRACT_BODY_END -->
