# Platform Configuration Registry (STRATEGY LOCKED)

## Purpose

Every market-data run and publication must bind the exact resolved configuration that can affect acquisition, canonical facts, analytical products, eligibility, readability, serialization, or replay. A version label alone is insufficient.

This registry owns configuration identity and snapshot semantics. Domain contracts own the meaning of each configured rule.

## Snapshot object

Before a run writes output, the platform creates an immutable configuration snapshot containing:

- a stable `config_snapshot_id`;
- schema/serialization version;
- effective and recorded/known timestamps;
- canonical, sorted, typed key/value content;
- redacted secret references as described below;
- `SHA-256` content hash; and
- provenance identifying the registry revision, deployment/build, environment profile, and resolver that produced it.

The same non-null snapshot ID and hash bind the run, every output artifact, the publication manifest, and the dataset seal. The resolved snapshot—not the current environment or registry—is used for replay.

Any unresolved, unknown, or null required configuration binding prevents sealing and consumer readability.

## Output-affecting key families

The snapshot includes every applicable key in these families, including resolved defaults and feature states.

### Acquisition and normalization

- selected acquisition source and adapter version;
- provider endpoint/data class, request-window semantics, rate limit, retry/backoff, timeout, and circuit-breaker rules;
- immutable observation-envelope and payload-retention versions;
- provider-symbol mapping and provider schema/field-mapping versions;
- response schema, timestamp, timezone, stale-date, duplicate, and invalid-value validation rules;
- canonicalization and numeric/unit normalization versions.

### Temporal identity and market expectation

- universe/listing/provider-mapping snapshot or revision selectors;
- Regular-Market calendar and session-completion revision selectors;
- trading-status/suspension revision selectors;
- requested-date, latest-expected-date, cutoff, activation, grace-period, and freshness rules;
- intentional dataset start `2023-01-02` and operational activation date/state.

### Coverage, quality, and data usability

- bar-expectation and delivery definitions;
- coverage threshold such as `COVERAGE_MIN`;
- quality rules, accepted/blocked reason sets, and quarantine policy;
- liquidity and status/event-risk fact versions;
- data-usability decision/reason registry versions.

Dormancy, zero volume, current active state, and provider absence are not configurable denominator exclusions.

Watchlist thresholds for tradability, ranking, entry/exit, or portfolio preference are not market-data configuration and must not enter this snapshot. A factual metric's required-input validation may be configured, but a strategy preference cannot change data usability.

### Price products and corporate actions

- canonical `RAW` product version;
- selected analytical price product, defaulting to `STRUCTURAL_ADJUSTED` for the initial EOD indicator profile;
- corporate-action type, lifecycle, verification hierarchy, and effective/ex-date semantics;
- factor construction, factor-set revision, rounding, and volume-transformation rules;
- detector thresholds and quarantine behavior;
- contamination horizons and reason-code registry version.

No configuration may enable synthetic candidates, provider adjustment fields, or price jumps to become verified factors automatically. No force flag may enable in-place published-history repair.

### Indicators and daily metrics

- formula/indicator registry versions and required field set;
- exact window definitions, inclusive/exclusive endpoints, ATR seed and recursive-state version;
- warm-up/history dependency rules expressed in trading sessions;
- price basis, benchmark/sector dependency versions, precision, rounding, nullability, and reason rules;
- actual traded-value field/version and close-volume proxy label/formula;
- daily aggregate partialness/completeness rules.

Representative windows include MA20, MA50, ROC5/10/20, range20, volume ratio 20, liquidity 20, and Wilder ATR14. A configured acquisition warm-up does not redefine the stable ATR seed/state.

### Publication, correction, and reads

- immutable snapshot/history schema versions;
- candidate, validation, sealing, activation/pointer-switch, supersession, and rollback policies;
- content and manifest hash definitions;
- minimum market-data consumer read-model version;
- freshness/readiness state machine and requested-versus-effective-date behavior;
- correction impact-graph and replay-mode versions.

### Hash and serialization

- hash algorithms;
- canonical table/column ordering;
- row sort keys;
- null, boolean, timestamp, decimal, collection, and text normalization;
- character encoding, delimiter/escaping, and line separator;
- inclusion rules for values, annotations, reason sets, lineage, config, and manifest references.

Every published field and semantic annotation belongs in its artifact hash. The config snapshot hash and ID belong in the publication/seal manifest even when two configurations happen to produce equal numeric rows.

## Registry metadata

Each key definition records at minimum:

- canonical key and type;
- authoritative meaning and owner contract;
- allowed values, unit, and default behavior;
- required/optional classification by run type;
- effective interval and recorded/known interval;
- output/lineage/security impact classification;
- change reason/ticket, author, and approval evidence; and
- compatible schema/formula/adapter versions.

Overlapping effective revisions for the same scope are invalid. Silent runtime overrides and undocumented defaults are forbidden.

## Environment and secret handling

Environment variables are inputs to the resolver, not replay authority. Their resolved non-secret values and origin/profile are captured in the immutable snapshot.

Secret values and credentials are never stored or hashed in cleartext. The snapshot records only a sanitized credential-profile/key identifier, provider account scope, and secret revision/version sufficient to explain behavior without exposing the secret. Rotating a secret that cannot affect returned content may be provenance-only; changing provider account scope, permissions, or data entitlement is output-affecting.

`.env.example`, application config, registry definitions, resolver validation, and documentation must remain synchronized. A key present in runtime code but absent from the registry is a sealing error.

## Effective-time and replay rules

Operational production selects the approved configuration effective for the run context and records when it became known. Two replay modes are distinct:

- publication replay uses the exact snapshot frozen with the publication;
- as-known replay resolves only revisions known by the declared knowledge cutoff, then freezes a new replay snapshot.

Current registry state must never leak into historical replay. Alternate-scenario runs are explicitly labeled and cannot impersonate the historical publication.

## Validation and acceptance proof

Before seal, validation proves:

1. every required output-affecting key was resolved exactly once;
2. snapshot serialization and hash are deterministic across independent executions;
3. run, artifacts, publication, and seal reference the identical non-null snapshot ID/hash;
4. a one-key semantic change produces a distinct snapshot hash and publication context;
5. current environment drift cannot change publication replay;
6. as-known replay cannot see later revisions; and
7. secrets are absent from snapshots, logs, observations, and manifests.

Until schema constraints and executed fixtures prove these properties, this registry is strategy-locked but configuration governance is not production-relocked.
