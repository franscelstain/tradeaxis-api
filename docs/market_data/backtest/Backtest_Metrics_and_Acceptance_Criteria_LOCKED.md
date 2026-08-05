# Replay and Data-Quality Acceptance Criteria (STRATEGY LOCKED)

This file defines upstream replay acceptance, not alpha performance.

A release candidate requires:

- zero unexplained value, null-reason, lineage, config, factor, hash, seal, or publication mismatches in exact publication fixtures;
- deterministic output across supported runtime/locale/concurrency conditions;
- all anti-survivorship and as-known isolation fixtures passing;
- all degraded/negative fixtures producing their expected held/failed/unavailable states without silent repair or denominator shrinkage;
- long-chain ATR and corporate-action results matching independent oracles;
- corrected publications preserving their predecessors and switching atomically; and
- `BLOCKED` treated as missing proof, never converted to pass.

Pass rates or row-count similarity cannot compensate for a semantic mismatch in a required invariant.

## Capability boundary (LOCKED)

**What these metrics prove.** That a study's outputs were computed from a declared point-in-time input set under stated acceptance rules, so results are attributable to a specific data version rather than to an unspecified snapshot.

**What they cannot prove.**

- **That the strategy works.** This is a data contract. Metrics computed here describe a study over supplied inputs; they carry no claim about future performance, and market-data readiness never depends on them.
- **That a metric difference reflects a strategy difference.** Two runs over different data versions differ for reasons this contract cannot separate from strategy changes unless both bind their input versions.
- **That acceptance criteria being met makes the underlying data correct.** Every upstream capability boundary composes into these numbers; none is discharged by a study that accepts them.

Consequently a metric set may be cited as evidence that **a study ran over an identified data version**, never as evidence about **market-data quality or strategy merit**.
