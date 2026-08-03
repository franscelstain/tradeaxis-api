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
