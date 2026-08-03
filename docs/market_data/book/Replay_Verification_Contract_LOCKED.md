# Replay Verification Contract (STRATEGY LOCKED)

## Two replay modes

Replay mode is mandatory and explicit.

### Publication replay

Reproduces or verifies one historical immutable publication using exactly the observations, temporal master revisions, calendar/status revisions, event/factor revisions, configuration snapshot, formulas/registries, build/adapter versions, serialization rules, and publication manifest frozen with it.

It answers: “Can the published artifact and its decision be verified exactly?” It does not re-resolve historical truth from today's databases.

### As-known replay

Builds a labeled replay using only facts whose `recorded_at`/knowledge time was at or before a declared `knowledge_cutoff`, while effective-time rules determine where those facts apply. It answers: “What could the platform have known at that cutoff?”

It may differ from a historical publication when the selected cutoff, approved as-known configuration, or declared scenario differs. It creates new replay artifacts and never mutates or impersonates the original publication.

## Required bound inputs

Every fixture/manifest binds at minimum:

- replay mode, fixture ID/version, requested/effective date, and knowledge cutoff where applicable;
- intentional dataset boundary and temporal universe/listing/symbol/provider mappings;
- Regular-Market calendar/session and trading-status revisions;
- immutable source observation IDs/hashes and adapter/schema/normalization versions;
- canonical `RAW` publication/input set;
- corporate-action event revisions, verification states, factor-set revisions, and contamination decisions;
- full configuration snapshot ID/hash;
- formula, indicator registry, reason registry, price-product, coverage, eligibility, read-model, hash/serialization, and build versions;
- expected publication/pointer/seal state and deterministic output/hash assertions.

Missing input is `BLOCKED`, not permission to query current/latest state.

## Anti-future and anti-survivorship rules

Replay must not use today's `is_active`, current symbol, current sector, current suspension/status, latest calendar correction, later corporate-action revision, later factor, current config, or latest provider mapping unless that exact revision was frozen/known in the selected mode.

Required fixtures include:

1. a listing active at historical T but inactive today;
2. a symbol change and provider-symbol mapping transition;
3. symbol text reused by another listing;
4. a calendar/status fact corrected after T;
5. a corporate action learned or verified later;
6. a configuration/formula change after T;
7. an original and corrected immutable publication; and
8. a provider outage that cannot disappear through dormancy/current-universe filtering.

## Resolution rules

Publication replay starts from explicit publication identity, never latest/current. Current-read verification is a separate assertion that the pointer resolves a specific publication.

As-known replay performs bitemporal resolution with `effective_at <= target context` and `recorded_at <= knowledge_cutoff`; ties and corrections use versioned deterministic rules. Unresolved ambiguity fails closed.

## Result and evidence

- `PASS`: all expected values, null reasons, states, lineages, content hashes, manifest, and seal assertions match.
- `FAIL`: comparison executed and diverged.
- `BLOCKED`: required fixture/runtime/input proof was unavailable.

Evidence preserves fixture/manifest hashes, actual and expected contexts, mismatch paths, reason distributions, publication/pointer context, executable command/build identity, timestamps, and admission state without requiring a mutable database as the primary explanation.

“Command exited successfully” or matching row counts alone is not replay proof.

## Trading backtest boundary

This contract verifies the upstream data product; it does not measure alpha or portfolio performance. A downstream backtest consumes the versioned as-known read product defined by `../backtest/Point_In_Time_Backtest_Input_Contract_LOCKED.md` and owns its own decision/execution assumptions.

Production relock requires executed publication and as-known fixtures, including all anti-survivorship cases above, on the actual production path.
