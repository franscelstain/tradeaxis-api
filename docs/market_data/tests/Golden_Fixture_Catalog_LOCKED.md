# Golden Fixture Catalog (STRATEGY LOCKED; EXECUTION GAPS OPEN)

Documentation specification status: **`DOCUMENTATION_READY`**. `required` means the semantic oracle is fully specified but still must be implemented and executed; it does not mean the strategy document is incomplete.

| Fixture ID | Semantic oracle | Required result | Current state |
|---|---|---|---|
| `obs_yahoo_valid_regular_eod_v2` | frozen valid Regular-Market provider payload | immutable envelope→RAW mapping with hashes/timestamps | required |
| `obs_stale_wrong_date_schema_v2` | stale, wrong-date, and schema-changed responses | separate quarantined observations; no canonical row | required |
| `identity_inactive_now_active_then_v2` | listing active at T, inactive today | included at T; current state invisible | required |
| `identity_symbol_change_reuse_v2` | rename plus reused symbol | stable listing IDs and correct provider mapping | required |
| `status_full_session_vs_unknown_v2` | verified full-session status and missing status evidence | only verified case is not-expected; unknown stays denominator | required |
| `provider_outage_not_dormancy_v2` | expected listings missing from provider | delivery/coverage fail without denominator shrink | required |
| `bars_zero_duplicate_invalid_v2` | zero OHLC and conflicting duplicates | reject/quarantine; no latest-wins or placeholder | required |
| `action_verified_structural_v2` | authoritative frozen structural-action terms | versioned event/factor, coherent OHLC/volume, RAW unchanged | required |
| `action_synthetic_break_v2` | RMKE/SCCO/PYFA-style observed discontinuity without verified terms | detector candidate/contamination only; no factor/repair | required |
| `liquidity_actual_proxy_v2` | source-backed traded value plus RAW close/volume | distinct actual and proxy fields/units | required |
| `atr_wilder_long_chain_v2` | independently calculated >100-session chain | exact stable seed/recurrence and hash | required |
| `atr_old_correction_unbounded_v2` | changed historical TR far before D | all affected later ATR versions rebuilt; predecessor preserved | required |
| `config_as_known_drift_v2` | later config/formula revision | exact replay frozen; as-known cutoff isolates later revision | required |
| `read_fresh_stale_atomic_v2` | current/held/corrected publications and concurrent pointer switch | truthful dates/states; never mixed | required |
| `replay_anti_survivorship_v2` | combined temporal master/action/calendar/config changes | no future/current leakage | required |
| `schema_v2_foundation` | migration and SQLite mirror | target tables/columns present; direct repair fields/type absent | **executed locally: schema mirror only** |

“Required” means no admitted executed V2 evidence is currently recorded. Legacy fixture IDs containing adjusted-close fallback, dormancy exclusion, sliding ATR reseed, direct scale repair, or price-derived verified actions are superseded and cannot count toward closure.

## Artifact existence status (LOCKED)

This catalog specifies fixtures. It does not create them, and a specified fixture is not a built one.

**Current state:** no golden fixture artifacts exist in the repository. A search across the project outside `docs/` returns only a replay-fixture generator command, two seeding helpers, and an ops runtime-matrix manifest in storage — none of which is a golden indicator fixture, expected-output oracle, or test vector set.

Consequences that bind until artifacts exist:

- Every acceptance criterion depending on a golden fixture is **unmet**, not partially met. Thoroughness of this catalog does not substitute for the artifacts it describes.
- The long-chain deterministic calculation required by the indicator owner contract has **no executing test**. No test in the suite exercises a Wilder ATR chain against an external reference, so that criterion is unproven rather than proven.
- A conformance claim may not cite this catalog as coverage. It may cite it as a plan.

Closing this requires building the artifacts, naming their location here, and adding tests that consume them. Extending the specification does not move it closer to closed.
