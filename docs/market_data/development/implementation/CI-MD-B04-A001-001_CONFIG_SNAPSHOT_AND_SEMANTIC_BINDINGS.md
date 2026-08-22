# MD Change Impact Declaration — CI-MD-B04-A001-001

- ID: `CI-MD-B04-A001-001`
- Stage / Attempt / Baseline / Epoch: `MD-B04` / `MD-B04-A001` / `MD-B04-A001-BL001` / `MD-REBASELINE-20260820-001`
- Record class: `MUTABLE_TRACEABLE`
- Issued: 2026-08-22, after `MD-B04-A001-BL001` and before any A001 matrix, implementation, test, evidence, or current-state mutation.

## Material scope

`MD-B04` implements and revalidates immutable configuration snapshots and semantic version/build/reason bindings so every later writer can receive non-null identity when its row is first created. The provisional entry set contains 184 `MANDATORY_OR_CONDITIONAL` rows, one optional-capability row, and 50 reference-only members of mixed-classification runs across six strategy owners:

- `Audit_Hash_and_Reproducibility_Contract_LOCKED.md`;
- `Determinism_Invariants_LOCKED.md`;
- `Hash_Number_Formatting_LOCKED.md`;
- `Config_Change_Protocol_LOCKED.md`;
- `Platform_Config_Registry_LOCKED.md`;
- `Reason_Codes_Registry.md`.

Before coverage can be claimed, this attempt must perform MD-B04's `MD-DEP-0004` entry obligation: resolve full semantic predicates and applicability, correct reference/required classification, validate proof ownership, move obligations whose behavior belongs to later writers, and invalidate/revalidate affected proof. The resulting denominator is not pre-committed to the provisional count.

## Planned implementation and proof method

1. Derive exact B04-owned behavior from the six authority owners, including immutable snapshot content/identity, normalized hashing, version bindings, append-only change protocol, required reason-code vocabulary, and rejection behavior.
2. Inspect actual migrations/schema, configuration snapshot repository/service, reason-code resolver/seed, hashing/normalization code, writer interfaces, and current tests.
3. Fix executable code/schema/test/tooling gaps first. Existing conformant behavior is reused only after current positive and negative proof.
4. Add a stage-scoped traceability/proof gate that rejects wrong classification, unresolved applicability, incorrect proof ownership, partial proof binding, and structural/header coverage.
5. Bind only MD-B04-owned predicates after behavioral/static proof and residue checks pass; moved predicates remain unproven at their owning stages.

## Affected areas

| Area | Expected impact |
|---|---|
| Strategy | No byte or semantic change; frozen authority is the target. |
| Schema/migrations | Inspect snapshot/reason/version columns and constraints; remediate only actual non-null/immutability gaps owned by B04. |
| Configuration/data | Inspect platform config registry, default snapshot material, reason registry, canonical serialization, and semantic version identities. |
| Runtime/API/contracts | Inspect snapshot creation/resolution and writer binding surfaces; no unrelated writer-stage implementation. |
| Hashing/determinism | Revalidate normalized number/string/NULL encoding and stable content identity; reject volatile/run-time-only identity where authority forbids it. |
| Tests/gates | Material: normalization/proof gate and focused positive/negative tests may be added or corrected. |
| Compatibility/residue | Check mutable snapshots, nullable identity fallbacks, legacy ad-hoc config reads, duplicate registries, reason strings outside the registry, and obsolete hash formatting. |
| Traceability/evidence | Material: classification, applicability, parent context, ownership, denominator, evidence bindings, and current state will change. |
| Dependencies/relationships | Performs MD-B04's portion of `MD-DEP-0004`; entry follows both closed enabling tracks `SC-MD-B02-A001-001` and `SC-MD-B03-A003-001`. |
| Downstream stages | Moved writer-specific obligations remain open; B04 must supply reusable non-null identities but cannot manufacture later-stage proof. |

## Raw artifact/storage boundary

No `storage/**` artifact is currently referenced. Initial work is authority, source/schema/config, deterministic unit/integration proof, and canonical traceability. If database execution becomes necessary, it will be executed directly and governed evidence will record the command/results; raw storage will be inspected only if the proof creates or references a material retained artifact.

## Risk and fail-closed boundary

Primary risks are treating config-key/reason-code existence as semantic proof, hashing display formatting instead of canonical values, allowing snapshot mutation or NULL bindings, including volatile IDs/timestamps in content identity, binding a child fragment without its governing contract, or advancing later writer obligations from B04. Negative proof must demonstrate that mutation, missing identity, non-canonical serialization/number formatting, unknown reason codes, and partial evidence bindings are rejected wherever current authority makes them invalid.

## Strategy semantic change

`NO`.

## Actual implementation impact

- Added `PlatformConfigRegistry`, which derives the resolved-key set from the canonical strategy register and blocks snapshot creation on an unknown, missing, or wrongly typed key.
- Changed the snapshot payload to bind canonical resolved config plus compiled semantic identities, while retaining stable hashing, provenance, secret redaction, and as-known resolution.
- Moved price-product version, factor-formula version, and session-snapshot feature state out of undocumented runtime config into `MarketDataSemanticBindings` / `SessionSnapshotFeatureState`.
- Moved per-command manual file input out of global configuration into `ManualSourceInputContext`; request-specific input no longer contaminates platform config identity.
- Rebuilt `DeterministicHashService` around the locked SHA-256 serializer: fixed decimal text without binary-float formatting, canonical JSON, stable ordering, UTF-8/date/timestamp/hash validation, locked empty NULL token, and fail-closed algorithm/delimiter/separator/unowned-number behavior.
- Made pipeline events, publication manifests, and command summaries report the serializer's actual NULL token rather than the contradictory platform-config literal.
- Corrected the dormancy runtime owner to `market_data.activity.dormant_absence_trading_days`; the deprecated coverage namespace remains snapshot-only compatibility residue and cannot alter the denominator.

No schema migration was required. Current additive columns and writer bindings already supported non-null snapshot identity, and executed integration proof revalidated them.

## Final traceability and downstream impact

All 933 rows from the six provisional B04 strategy owners were semantically reviewed. The governed result is:

- `114` mandatory predicates owned by `MD-B04`;
- `181` executable predicates moved to their actual downstream proof owners with `MD-B04` supporting linkage;
- `638` headings, introducers, dictionary entries, explanatory rows, or other non-independent context rows classified `REFERENCE_ONLY`.

`MD-S085-R0001` is a list introducer, not a separate requirement. `MD-S082-R0203..R0211` remain executable but move to `MD-B21`, where per-key governance metadata and revision-overlap validation can be proved. `MD-S005-R0019` moves to `MD-B10`, where artifact-specific complete stable keys exist.

One B04 predicate is withheld: `MD-S082-R0062`. The resolved-key register locks `market_data.hash.null_token` to literal `[empty]`, while its named semantic owner and the number-format contract both lock actual NULL serialization to the empty token. Implementation follows the higher-precedence semantic owner, but a configuration snapshot cannot honestly claim aligned NULL-normalization semantics while retaining the contradictory resolved default. `F-MD-B04-A001-001` / `MD-DEP-0007` records this authority defect; strategy bytes were not changed.

## Compatibility, residue, and revalidation

- Removed executable reads of the deprecated dormancy alias, unregistered semantic-version keys, unregistered session activation, and global manual-input config.
- Retained the deprecated dormancy alias only because current strategy explicitly requires it to remain snapshotted during compatibility migration.
- No old `[empty]` metadata-emission path remains; a negative guard fails if pipeline, manifest, or command surfaces return to config-driven NULL metadata.
- A future authorised correction to the null-token strategy row invalidates `MD-S082-R0062`, config-registry conformance, config snapshot identity, hash metadata proof, and any closure evaluation derived from this attempt. It requires governed re-entry/rebaseline/revalidation; historical evidence must not be edited.

## Current status

`PARTIAL_BLOCKED — IMPLEMENTATION_AND_TEST_PROOF_COMPLETE`. Current proof is `113/114`; `MD-S082-R0062` is blocked only by `MD-DEP-0007`. No raw `storage/**` artifact was inspected or claimed.
