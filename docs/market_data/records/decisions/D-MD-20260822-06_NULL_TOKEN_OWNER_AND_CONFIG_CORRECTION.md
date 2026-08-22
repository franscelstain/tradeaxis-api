# Decision — canonical NULL token is the empty string; the Platform Config lock is corrected

- ID: `D-MD-20260822-06`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline reviewed: `MD-B04` / `MD-B04-A001` / `MD-B04-A001-BL001`
- Finding: `F-MD-B04-A001-001`
- Dependency: `MD-DEP-0007`
- Blocked rule: `MD-S082-R0062`
- Issued: 2026-08-22
- Decision status: `ISSUED`
- Strategy impact: `CONTROLLED_CORRECTION` limited to the `market_data.hash.null_token` resolved-key row in `MD-S082`

## Question

The Platform Config resolved-key register locks `market_data.hash.null_token` to `[empty]` and exposes `MARKET_DATA_HASH_NULL_TOKEN`, while its named owner contracts lock canonical `NULL` serialization to the empty token/empty string. The review must decide whether `[empty]` is merely documentation notation, a canonical five-byte token, or a defective configuration lock.

## Authority review

1. `MD-S005` (`Audit_Hash_and_Reproducibility_Contract_LOCKED.md`) owns the canonical row serialization and states: `NULL`: empty token.
2. `MD-S034` (`Hash_Number_Formatting_LOCKED.md`) owns the specialized canonical formatting and states: `NULL: empty string`, with the example `NULL => ```. These two semantic owners agree.
3. `MD-S082` states that the resolved-key register records resolver-verifiable key, type, resolved default, and environment input, while meaning and allowed values remain with the owner contract named in the final column. The `market_data.hash.null_token` row names `MD-S005` as that owner.
4. `[empty]` appears in the resolved-default column in the same literal formatting used for other runtime defaults. Nothing in `MD-S082` declares square brackets to be a notation layer or maps `[empty]` to a zero-byte value. Treating it as notation would add semantics absent from the frozen bytes.
5. The five-byte string `[empty]` cannot satisfy the owner contracts: it changes serialized bytes and therefore changes every affected digest. Conversely, an environment input capable of selecting a non-empty value cannot preserve a locked zero-byte token.

## Decision

1. The canonical semantic owner of NULL serialization is `MD-S005`, specialized by the consistent number-format rule in `MD-S034`.
2. The canonical token is the **empty string (zero bytes)**. `[empty]` is not a governed notation for that value and is not the canonical literal token.
3. `MD-S082` delegates meaning and allowed values to `MD-S005`; its resolved-key row is therefore an actual authority defect. The correction is limited to declaring an explicit empty-string default and no environment input for `market_data.hash.null_token`.
4. `MD-S005` and `MD-S034` remain byte-identical. No semantic-owner revision is authorised or required.
5. `MD-DEP-0007` resolves after the corrected `MD-S082` bytes are logged and refrozen. `MD-S082-R0062` is not satisfied by this decision; a successor `MD-B04-A002` must rebaseline and prove runtime config, registry enforcement, serializer metadata, negative override behavior, residue, and current traceability under the successor freeze.
6. `MD-B04-A001-BL001` and `E-MD-B04-A001-001` remain immutable historical records of the 113/114 boundary. They are not edited or relabeled.

## Explicit strategy-revision authorization

The user instruction `RESOLVE MD-DEP-0007 — NULL TOKEN AUTHORITY CONFLICT`, received 2026-08-22, explicitly authorises the strategy revision if authority review establishes that the canonical token is the empty string and the Platform Config row is wrong. This review establishes exactly that condition.

The authorised byte scope is only the `market_data.hash.null_token` row of `authority/strategy/registry/Platform_Config_Registry_LOCKED.md`: default `empty string (``)` and environment input `—`. It does not authorise any other strategy edit, semantic relaxation, or unrelated correction.

## Invalidation and revalidation impact

- Invalidated for current closure: the A001 proof mapping for `MD-S082-R0062` and every config/hash/serializer-metadata assertion whose current sufficiency depends on the old strategy freeze.
- Preserved as immutable history: A001 baseline and evidence, including the blocked verdict.
- Required successor proof: `MD-B04-A002` with a new pre-change Baseline Lock and early Change Impact Declaration, followed by current executed positive and fail-closed proof, residue checks, governed evidence, relationship registration, traceability rebinding, integrity gates, and closure checks.
- Raw runtime artifacts: none are required by this authority decision; any executed proof later relying on external artifacts remains governed by the runtime-artifact evidence standard.

## Scope limit

This decision does not decide any other Platform Config default or environment input, does not reopen `MD-S005`/`MD-S034`, and does not authorise entry into `MD-B05`.
