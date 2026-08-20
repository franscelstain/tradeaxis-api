# Audit Hash and Reproducibility Contract (LOCKED)

## Purpose

Define deterministic content identity for publication-bound market-data artifacts. Hashes must detect a change in consumer-visible values **or their semantic meaning/lineage**, while ignoring volatile execution metadata that cannot change interpretation.

## Required hashes

For one effective trade date and publication context, persist at minimum:

- `bars_batch_hash`;
- `indicators_batch_hash`;
- `eligibility_batch_hash`;
- `observation_manifest_hash`;
- `publication_manifest_hash`.

The publication manifest binds the artifact hashes to configuration, factor, formula, read-model, temporal revision, seal, and correction lineage.

## Algorithm and canonical serialization

- algorithm: `SHA-256`;
- output: lowercase hexadecimal;
- encoding: UTF-8;
- field delimiter: `|`;
- row separator: `\n`;
- no trailing delimiter or trailing newline;
- `NULL`: empty token;
- booleans: `0` or `1`;
- dates/timestamps/numbers use `Hash_Number_Formatting_LOCKED.md`;
- object/set fields use sorted canonical JSON with stable escaping;
- rows sort by the complete stable artifact key.

Locale, insertion order, database query plan, runtime version, and map iteration order must not affect serialization.

## Stable identity rule

Artifact hashes use stable domain/revision/content identities, not volatile database surrogate IDs when those IDs can differ across equivalent rebuilds.

Examples:

- stable `listing_id` is included;
- config snapshot **content hash** is included;
- factor-set content hash/revision identity is included;
- formula/registry/read-model version is included;
- source observation or observation-manifest content hash is included;
- volatile `run_id`, auto-increment publication ID, and execution timestamps are excluded from artifact content hashes.

The manifest may record surrogate IDs for navigation, but reproducible semantic comparison uses the stable hashes/revisions.

## Bars artifact payload

One canonical `RAW` Regular-Market bar row includes, in locked schema order:

1. trade date and stable listing identity;
2. source observation/manifest content identity;
3. RAW open, high, low, close, and volume;
4. nullable source-backed previous close, actual traded value, and trade count;
5. board/session/status facts when part of the canonical row;
6. source timestamp and acquisition timestamp when consumer-visible availability semantics require them;
7. canonicalization version;
8. `RAW` price-product code;
9. quality state and sorted reason/annotation set;
10. configuration snapshot content hash.

Provider `adj_close` is source-observation lineage only. It is not canonical RAW close or a structural product and is not serialized as an analytical bar field.

## Indicators artifact payload

One indicator row includes, in locked registry/schema order:

1. trade date and stable listing identity;
2. price-product code/version and factor-set content hash;
3. formula/indicator registry version and configuration snapshot content hash;
4. indicator values including separately named actual traded-value and RAW close-volume proxy metrics;
5. Wilder ATR value/percentage and stable recursive-state reference;
6. benchmark/sector revision identities and dependent values when present;
7. validity/warm-up/contamination states;
8. complete sorted per-field null/reason annotations;
9. event/status context revisions exposed by the indicator contract.

Legacy `dv20_idr`, if temporarily present, hashes only as the declared compatibility alias of `adv20_close_volume_proxy_idr`; it cannot replace or impersonate actual traded value.

## Eligibility artifact payload

One eligibility row includes, in locked schema order:

1. trade date and stable listing identity;
2. temporal universe membership state;
3. bar expectation and delivery states;
4. canonical quality, liquidity, temporal status, event-risk, and freshness states;
5. explicit eligibility decision;
6. complete sorted reason-code/annotation set, including field-specific reasons where exposed;
7. configuration, publication/read-model, and relevant revision content identities.

A primary compatibility `reason_code` never replaces the complete reason set in the hash.

## Observation manifest

The observation manifest deterministically lists every accepted, rejected, stale, schema-invalid, missing, and superseding observation outcome relevant to the run/publication. Each entry uses payload hash/reference, provider/mapping revision, requested/selected date, timestamps, adapter/schema version, outcome, and reason without secrets.

Refetching creates a new observation identity. A changed accepted observation changes the observation manifest and every dependent publication context even when rounded output values happen to remain equal.

## Publication manifest binding

`publication_manifest_hash` binds at minimum:

- market/product scope and requested/effective trade dates;
- publication version and supersession relation;
- artifact and observation-manifest hashes;
- full config snapshot hash;
- temporal identity/calendar/status revision-set hashes;
- corporate-action event and factor-set hashes;
- formula/registry/canonicalization/read-model versions;
- row counts, quality/readiness/freshness states, and sorted reasons;
- seal algorithm/version and correction lineage.

The manifest records operational IDs and seal timestamps for audit navigation, but volatile values must be separated from the canonical semantic-hash payload when they would prevent identical replay comparison.

## Included versus excluded data

Included in semantic identity:

- every consumer-visible value;
- every annotation, reason, null state, basis, unit, revision, and config choice that can change interpretation;
- row membership and stable ordering;
- lineage content hashes required to reproduce the product.

Excluded from artifact content hashes unless they alter semantics:

- `run_id` and auto-increment navigation IDs;
- worker/host/process identity;
- retry log and operator note;
- `created_at`, `updated_at`, execution start/end, and seal wall-clock timestamps;
- credential secrets.

Exclusion from artifact content hash does not permit omission from audit records or the manifest.

## Correction and rerun rules

- An unchanged rebuild with identical stable inputs/versions produces identical artifact hashes and must not create a fake corrected publication.
- Any changed consumer value, reason, row set, observation manifest, config, factor, formula, product, or semantic revision creates a distinct publication context and relevant hash change.
- A correction preserves the prior manifest/hashes and publishes new immutable artifacts through explicit supersession and atomic pointer switch.
- Sealed artifacts are never updated to make hashes match.

## Required proof

Fixtures must prove:

1. identical semantic content with a different run ID yields identical artifact hashes;
2. a changed value, reason, row membership, config key, observation, factor, formula, or revision changes the expected hash/context;
3. unordered reason/JSON input canonicalizes identically;
4. locale/runtime differences do not alter output;
5. exact publication replay reproduces hashes;
6. as-known replay excludes later revisions;
7. corrections preserve prior hashes and create a new manifest;
8. provider `adj_close`, direct repair metadata, and omitted annotations cannot silently enter/escape hash coverage.

## Cross-contract alignment

- `Publication_Manifest_Contract_LOCKED.md`
- `Dataset_Seal_and_Freeze_Contract_LOCKED.md`
- `Determinism_Invariants_LOCKED.md`
- `../registry/Platform_Config_Registry_LOCKED.md`
- `../registry/Price_Adjustment_Contract_LOCKED.md`
- `../tests/Golden_Fixtures_Specification.md`

## Acceptance criterion

A candidate cannot seal when required semantic bindings are null, inconsistent across artifact families, or absent from the manifest/hash contract. Green proof under a narrower legacy field list is superseded.
