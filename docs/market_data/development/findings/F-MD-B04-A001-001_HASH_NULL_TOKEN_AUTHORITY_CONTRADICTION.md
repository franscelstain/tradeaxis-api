# F-MD-B04-A001-001 — Platform config NULL token contradicts its semantic owner

- Status: `RESOLVED`
- Severity: `P1`
- Stage / Attempt / Baseline / Epoch: `MD-B04` / `MD-B04-A001` / `MD-B04-A001-BL001` / `MD-REBASELINE-20260820-001`
- Owning stage for remediation: strategy authority change control, then governed `MD-B04` re-entry
- Blocks: `MD-S082-R0062` and `MD-B04` closure
- Dependency: `MD-DEP-0007`

## Finding

Three current locked statements cannot all be true as executable semantics:

1. `Audit_Hash_and_Reproducibility_Contract_LOCKED.md` locks `NULL` to the empty token.
2. `Hash_Number_Formatting_LOCKED.md` locks `NULL` to the empty string.
3. `Platform_Config_Registry_LOCKED.md` resolves `market_data.hash.null_token` to the literal string `[empty]` and exposes `MARKET_DATA_HASH_NULL_TOKEN` as an environment input, while naming the audit contract as semantic owner.

The first two agree and have higher semantic specificity for actual serialization. The third is not merely a traceability label: it declares a resolved runtime value which is captured inside configuration identity. Treating `[empty]` as an undocumented notation for an empty string would rewrite locked content by convenience. Letting the resolved value control serialization would violate both owner contracts.

## Actual implementation state

`DeterministicHashService` follows the semantic owner: `NULL` serializes to `''`. Pipeline events, publication manifests, and command output now report that actual serializer token. The current platform configuration still resolves `[empty]` because changing it would contradict the locked resolved-key row. `PlatformConfigRegistry` proves runtime key/type parity against that row and fails closed for missing, unregistered, or wrongly typed keys.

This is intentionally not hidden by:

- changing strategy during normal implementation;
- changing the runtime default while claiming registry conformity;
- letting environment input weaken locked serialization;
- marking `MD-S082-R0062` satisfied because the serializer alone is correct.

## Proof impact

The executable serializer predicates in `MD-S005` and `MD-S034` are current-proven. The blocked predicate is `MD-S082-R0062`: the immutable configuration snapshot cannot claim an aligned binding for null normalization while its resolved value contradicts the owner semantics. `E-MD-B04-A001-001` therefore binds 113 of 114 B04 predicates and withholds this one.

No previously issued immutable evidence was edited. Strategy bytes changed: `0`.

## Required governed resolution

The preferred correction preserves the existing semantic owner and does not relax hashing:

1. issue a reviewed decision accepting that the resolved-key row is defective, not that `[empty]` changes the meaning of `NULL`;
2. obtain explicit user authorization required by `DOCUMENT_CHANGE_POLICY.md` section 2;
3. revise the `market_data.hash.null_token` resolved-key row so the default is explicitly the empty string and no environment override can select a non-empty token;
4. record the strategy change, issue a new freeze identity, and identify affected proof;
5. open a successor B04 attempt with a new pre-change baseline, make runtime config/env templates and registry validation conform, and revalidate snapshot/hash/metadata/traceability proof.

A reviewed decision that merely calls `[empty]` “equivalent” is insufficient: the value is a five-byte string and produces different serialized bytes.

## Resolution condition recorded at A001

At A001 issuance, `MD-DEP-0007` could resolve only after the authorised strategy correction and successor freeze were issued, followed by a governed successor attempt from the corrected freeze. That condition has now been completed by `MD-B04-A002`; `MD-B04-A001-BL001` and `E-MD-B04-A001-001` remain immutable.

## Lifecycle resolution — 2026-08-22

`D-MD-20260822-06` determined from the authority chain that `MD-S005`, specialized by `MD-S034`, owns the zero-byte empty-string NULL token and that `[empty]` was an incorrect literal resolved default, not a notation layer. The user explicitly authorised the bounded correction. `DOC-CHG-20260822-001` records the change; only the `market_data.hash.null_token` row of `MD-S082` changed, and successor freeze `MD-STRATEGY-FREEZE-20260822-001` passed the strategy-freeze integrity check across all 91 documents.

The authority contradiction tracked by this finding is therefore resolved. Runtime/config/test conformity remains a successor-attempt proof obligation at `MD-B04-A002`; resolving this finding does not grant `MD-S082-R0062` satisfaction and does not alter the immutable A001 baseline or evidence.
