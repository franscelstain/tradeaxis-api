# Change Impact Declaration — `MD-B04-A002`

- ID: `CI-MD-B04-A002-001`
- Stage / Attempt / Baseline / Epoch: `MD-B04` / `MD-B04-A002` / `MD-B04-A002-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260822-001`
- Governing decision/change: `D-MD-20260822-06` / `DOC-CHG-20260822-001`
- Predecessor evidence: `E-MD-B04-A001-001` (immutable 113/114 boundary)
- Status: `COMPLETE`
- Strategy meaning change in this attempt: `NO` — the separately authorised authority correction was completed and refrozen before this attempt baseline

## Objective

Make runtime configuration, environment templates, resolver validation, canonical serializer metadata, and current proof conform to the corrected authority: `market_data.hash.null_token` is the zero-byte empty string and has no environment override. Revalidate `MD-S082-R0062` and every affected B04 proof under the successor freeze without editing A001 records.

## Affected strategy predicates

Direct semantic predicates: `MD-S005-R0015`, `MD-S034-R0012`, `MD-S082-R0062`, `MD-S082-R0066`, `MD-S082-R0067`, `MD-S082-R0214`, `MD-S082-R0220`, `MD-S082-R0221`, and `MD-S082-R0223`.

Affected payload/identity/owner-link proof includes `MD-S019-R0076`, `MD-S019-R0077`, `MD-S065-R0001`, `MD-S065-R0002`, `MD-S065-R0005`, `MD-S082-R0001`, `MD-S082-R0002`, `MD-S082-R0020`, `MD-S082-R0055`, `MD-S082-R0059`, `MD-S082-R0064`, `MD-S082-R0065`, and `MD-S082-R0212`. Because the active freeze identity changed, the current B04 proof gate and evidence will rebind all 114 mandatory B04 predicates to A002 after executing their existing proof methods; unchanged behavior is carried forward only through revalidation, not inheritance.

`MD-S082-R0121` is the corrected source-integrity/reference row. It remains `REFERENCE_ONLY`; it is not substituted for the executable normalization predicate `MD-S082-R0062`.

## Impact assessment

- Strategy: no additional strategy bytes may change in A002. `MD-S005` and `MD-S034` remain canonical semantic owners.
- Schema/data: none; no migration, persisted-row rewrite, replay, or backfill.
- Configuration: change `config/market_data.php` to literal `''`; remove `MARKET_DATA_HASH_NULL_TOKEN` from both env templates; fail closed if resolved config supplies a non-empty token.
- Runtime/API/contracts: canonical serializer output remains zero-byte empty string; this attempt aligns the snapshot/config surface with behavior already owned by strategy. No API shape changes.
- Serializer metadata: publication/run/manifest token metadata must remain identical to the canonical serializer constant and must not report `[empty]`.
- Tests/gates: add positive default proof and negative non-empty override proof; update the current B04 binder/proof gate from the A001 blocker model to exact A002 114/114 evidence binding; rerun the affected tests, full Market Data suite, mutation self-tests, traceability/classification, relationship, and documentation gates.
- Operations: remove the obsolete environment knob. Existing deployments carrying it must not be able to alter canonical bytes; residue scan targets config and env surfaces only.
- Compatibility: digest serialization remains the owner-correct behavior already implemented. The config snapshot identity changes because the resolved config changes from `[empty]` to `''`; this is intentional and must be visible.
- Residue/rework: reject any remaining executable `MARKET_DATA_HASH_NULL_TOKEN` or `[empty]` binding in current code/config/tests. Historical records and the old strategy value in immutable evidence may remain as history and are not executable residue.
- Evidence: issue new A002 governed evidence; do not edit A001 baseline/evidence. No external raw runtime artifact is required for this deterministic local config/hash proof.
- Dependencies/relationships: `MD-DEP-0007` is resolved by `D-MD-20260822-06` and the successor freeze. A002 baseline/evidence/closure must be explicitly related to the decision and A001 lineage.
- Downstream stages: none opened. `MD-B05` remains out of scope until valid B04 closure and canonical orchestration.

## Closure boundary

A002 may close only after the runtime configuration cannot resolve a non-empty NULL token, env/config/registry/serializer metadata are synchronized, all 114 mandatory B04 predicates carry current A002 evidence, no harmful executable residue remains, and all required integrity/relationship/closure checks pass.

## Closure confirmation

The declared scope did not expand. Runtime/config/env/test/tooling changes, negative proof, residue verification, all 114 traceability bindings, governed evidence, and required relationships are complete. No schema/data/API/backfill/replay/operations/raw-artifact impact occurred. Final suite: 1715 tests / 11526 assertions; all B04 closure-bearing gates passed.
