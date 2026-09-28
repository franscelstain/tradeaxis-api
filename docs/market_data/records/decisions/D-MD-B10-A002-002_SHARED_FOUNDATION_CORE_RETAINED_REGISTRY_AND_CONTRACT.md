# Shared Security Identity Foundation core architecture decision

- ID: D-MD-B10-A002-002
- Status: ISSUED / Accepted (technical implementation decision under current authorized core task)
- Issued: 2026-09-29T00:45:22+07:00
- Stage / Attempt / Work / Baseline: MD-B10 / MD-B10-A002 / MD-B10-A002 / MD-B10-A002-BL001
- Verification epoch: MD-REBASELINE-20260820-001
- Parent decision: D-MD-B10-A002-001
- Related finding / CI / dependency: F-MD-B18-A002-023 / CI-MD-B10-A002-001 / MD-DEP-0021
- Basis evidence: E-MD-B10-A002-005
- Executed acceptance: E-MD-B10-A002-006
- Strategy meaning change: NONE
- Authorization: current owner-authorized bounded implementation task permits conformant technical mechanism selection. No independent human approval, master completeness or historical listing truth is claimed.

## Context

D001 assigns the shared foundation canonical writer and project-owner stewardship inside tradeaxis-api. E005 admits two bounded source contexts with no complete exchange listing. MD-S057/055 require retained immutable separate identity layers, explicit temporal mappings and fail-closed ambiguity; MD-S005 excludes volatile allocation from semantic identity. They do not prescribe an ID representation.

## Decision

1. Place orchestration and inert handoff contracts in app/Application/SecurityIdentity, pure admission/resolution in app/Domain/SecurityIdentity and filesystem/database access in app/Infrastructure/Persistence/SecurityIdentity. The CLI boundary is app/Console/Commands/SecurityIdentity. No shared master lives under MarketData ownership.
2. Assign opaque UUIDv4 identities once in resources/security_identity/foundation-source-basis-20260928-v1.registry.json. Its E005-bound fingerprint is dc86d2a9453b3edaaa32736e4ea5022152916bd97c62c13a84c8e4b29953b4aa. Retain these assignments on every import/export/rebuild; never generate IDs during source import. Source URLs are crosswalk/provenance locators, not UID derivation inputs. ISIN, symbols and numeric navigation IDs are not roots. First-assignment registry bytes are versioned input; changes require a successor retained registry/admission rather than regeneration of v1.
3. Canonical persistence is additive si_packages, si_entities, si_revisions and si_holds, InnoDB with explicit uniqueness and foreign keys. row_id is local navigation only and is excluded from registry export. Immutable root/parent assignment and revision documents are append-only through the application contract. A conflicting retained key rejects the whole admission. Corrections use new revision IDs and explicit supersession, preserving prior records. Non-empty foundation history blocks destructive migration rollback.
4. Store profiles, listing validity, symbols, boards, provider mappings and continuity as explicit revision records. Instants are normalized UTC Y-m-d H:i:s; intervals are half-open [valid_from,valid_to). Unknown source revision/known-time remain null. Capture time is stored separately and never substituted for historical knowledge. Listing evidence carries state/reason and a verified-through boundary; null termination does not certify unbounded future history.
5. Bootstrap validates the exact E005 manifest/version, all eight material member hashes and retained assignments before any write. It admits only the observed issuer profiles and KSEI instrument relationship/registered fields. CSD listing dates, exchange corroboration and announced IPO schedules remain package provenance; no listing is created. Missing histories remain explicit hold records.
6. Restore accepts a retained registry only with an externally supplied governed exact-file fingerprint. This is a trusted application/operator admission boundary, not a public upload API. A self-computed caller hash is not governance approval. Independently declared synthetic fixture fingerprints are test-only mechanism authorization and never production master evidence.
7. FoundationService orchestrates admission; the repository owns queries and transaction execution. A database-scoped advisory lock serializes writers. An InnoDB transaction makes admission atomic. Snapshot/export reads use one transaction and bounded lists (10,000 records per group); exceeding that limit raises instead of truncating coverage. This v1 core is not an unrestricted bulk master importer.
8. The consumer contract resolve(namespace,symbol,effectiveAtUtc,knowledgeCutoffUtc) returns IdentityResolution. RESOLVED carries issuerId/instrumentId/listingId, effective symbol, venue/segment/board, provider mapping and complete selected revision provenance. HELD and AMBIGUOUS expose reason and no identity. Visible revisions require source knowledge and actual recording by cutoff. Future or unknown knowledge, gaps, superseded revisions, unresolved continuity, expired validity and scope beyond verified history cannot fall back to current symbols.
9. Retain legacy tickers/md_* structures unchanged as existing compatibility/projection/migration paths. No equivalence alias is invented, no foundation root is wired into Market Data and no historical artifact digest is reinterpreted. All 56 B10 rows remain NOT_ASSESSED.

## Alternatives and reasons

Database sequences and ticker/name/provider-derived roots violate allocation or lifecycle invariants. Deterministic semantic root issuance from incomplete source facts would add unnecessary issuance meaning. Retained once-assigned opaque IDs are the smallest conformant registry mechanism. A separate repository/runtime service has no authority basis. An unused adapter/interface, raw-table shortcut or new global master under MarketData is unnecessary.

## Architecture review (automated implementation review, not independent human approval)

- Placement: transport parses the package path; application orchestrates; filesystem/database IO stays in persistence; admission/resolution is pure domain logic.
- Handoff: FoundationRegistry is a versioned inert document DTO with fixed group/key shapes, not ORM/query-builder state; IdentityResolution exposes a fixed read outcome. JSON technical canonicalization is confined to retained registry encoding, not Market Data artifact hashing.
- Persistence: immutable-key checks, FK parent/package/revision links, deterministic ordering, application transaction and write lock. No writes to normal tradeaxis; isolated identified MariaDB proof only.
- Compatibility: additive migration; empty schema rollback tested; populated rollback refuses; old applied migrations unchanged. Production bootstrap requires controlled migration/deployment; this patch does not activate it on normal development data.
- Failure: rejected source/provenance/registry never becomes partial admitted success; holds remain explicit, and ambiguous read returns no identity.
- Scope/performance: two source contexts, three root assignments; explicit bounded snapshot limit. Large-scale intake requires the applicable later access-pattern/performance review.
- Validation: E006 records real MariaDB controls, synthetic lifecycle mechanism cases, production negative probes, source restoration and residue checks. Full Market Data suite is unnecessary for isolated new tables/domain code and one CLI registration; existing Market Data behavior is not changed.
- Putusan: Accepted for this bounded core checkpoint. Production consumer readiness and dependency resolution are not approved.

## Consequences / review again

The retained registry must travel with export/import and source evidence; random reassignment would lose identity continuity. Zero Market Data-consumable listings is intentional. Review on new source admission, concrete Market Data intake, another consumer, large populations, schema/representation change or conflicting continuity facts. Use a successor decision if this technical boundary changes. Existing D001 and E005 remain immutable.
