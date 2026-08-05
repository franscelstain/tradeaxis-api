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

## Mode recording and single-mode implementation (LOCKED)

Replay mode is declared mandatory and explicit above. That declaration binds the **result** as much as the request:

- Every replay result records its mode as a first-class field. A result set in which publication and as-known outcomes are indistinguishable does not satisfy the mandatory-mode rule, regardless of what the invoking command intended.
- A result carrying no mode is not a publication-replay result by default. It is **unclassified**, and an unclassified result may not be cited as either.

### While only one mode exists (LOCKED)

Publication replay and as-known replay answer different questions, and only the second bears on future-state leakage. Where as-known replay is not implemented, the following hold without exception:

- **Publication-replay results carry no information about anti-survivorship or future-state leakage.** They compare an artifact against inputs frozen with it; a future master, a later action revision, or a later configuration is absent from both sides of that comparison and therefore cannot produce a mismatch.
- No volume of publication-replay `PASS` results substitutes for a single as-known fixture. Accumulating them raises confidence in determinism only.
- The eight anti-survivorship fixtures required below are as-known fixtures. Until as-known replay exists, their absence is not a gap in coverage — it is a gap in capability, and any claim resting on them is unavailable rather than untested.
- A conformance or activation claim that cites replay evidence names which mode produced it. Citing an unmoded or publication-only corpus in support of a point-in-time property is a governance violation.

## Capability boundary (LOCKED)

Replay is the strongest proof this platform has, which makes an unstated limit here more dangerous than anywhere else.

**What replay proves.** That a published artifact is reproducible from the inputs frozen with it; that the pipeline is deterministic across runs, builds, and environments; that the tested paths do not read future or current state; that a divergence, when it appears, is real.

**What replay cannot prove.**

- **That the values are correct.** Replay compares an output against itself under fixed inputs. A publication computed from a wrong observation, a missing corporate action, or an absent factor reproduces exactly and returns `PASS`. The verdict means "the pipeline agreed with itself", never "the market data is right".
- **That the source observation was faithful.** Provider error inside an immutable observation is frozen by the same mechanism that guarantees reproducibility.
- **That an event was not missed.** A corporate action nobody recorded is absent from both sides of the comparison, so it cannot create a mismatch.
- **That the semantic rules are right.** Replay verifies the rules were applied consistently, not that they express the intended meaning. A rule that is wrong in the same way on both sides replays green.
- **That untested paths are safe.** Only the bound inputs and asserted fields are covered. A field absent from the assertion set is unverified regardless of the verdict.

### Admissibility of a PASS (LOCKED)

- A replay `PASS` may be cited as evidence of **reproducibility and determinism**. It may never be cited as evidence of **data correctness, event completeness, or factor validity**.
- A replay `PASS` may not close a data-quality finding, release a quarantine, dismiss a corporate-action candidate, or satisfy a continuity check.
- Where an audit claim requires correctness, the admissible evidence is independent — verified event terms, source reconciliation, or exchange-published facts — not a replay verdict.
- `BLOCKED` is not a weaker `PASS`. It states that the comparison did not execute.

This is the same principle the detection and adjustment contracts already carry: a bounded mechanism agreeing with itself is not evidence about the world.

## Trading backtest boundary

This contract verifies the upstream data product; it does not measure alpha or portfolio performance. A downstream backtest consumes the versioned as-known read product defined by `../backtest/Point_In_Time_Backtest_Input_Contract_LOCKED.md` and owns its own decision/execution assumptions.

Production relock requires executed publication and as-known fixtures, including all anti-survivorship cases above, on the actual production path.
