# Finding — B10 semantic hashes depend on database allocation

- ID: `F-MD-B18-A002-023`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-09-27T11:37:01+07:00
- Severity: `P1` for current verification and the blocked R0025 independent fixture
- Status: `OPEN`
- Class: `IMPLEMENTATION_AND_PROOF_DEFECT` in the `MD-B10` audit-hash surface
- Related: `E-MD-B18-A002-086`, `E-MD-B10-A001-001`, `SC-MD-B10-A001-001`, `F-MD-B18-A002-017`, `MD-DEP-0020`

## Authority and observed defect

`Tickers_and_Identity_Dependency_Contract_LOCKED.md` requires immutable issuer, instrument and listing identities, with `ticker_id` only as a documented invariant compatibility alias. Market Data consumes the shared security-identity foundation; it does not own the global master. `Audit_Hash_and_Reproducibility_Contract_LOCKED.md` separately requires stable semantic identities in artifact hashes, excluding database surrogate IDs when equivalent rebuilds can allocate them differently, and separates operational publication IDs from semantic manifest content.

The current legacy projection in `TemporalIdentityRepository::projectTicker` derives `issuer_uid`, `instrument_uid`, and `listing_uid` from `ticker_id` and obtains numeric issuer, instrument and listing IDs by `insertGetId`. These UIDs are migration projections, not an independently allocated domain root. The B10 pipeline hashes numeric `ticker_id` and `listing_id` in all three artifact families, and its row key uses those fields. The publication manifest semantic payload hashes numeric supersession/previous/replaced publication IDs and `correction_id`. Therefore equivalent semantic content with different row allocation can produce different artifact or manifest hashes. No production, schema or persistent dataset was changed in this review.

The B10 `audit_manifest` proof family maps the affected `MD-S005` rows to a static manifest guard and a canonical-document order-sensitivity test. Those guards do not vary database allocation, do not check the named numeric relationship fields, and do not establish cross-rebuild hash equality. `E-MD-B10-A001-001` and the B10 closure remain immutable historical records and continue to support their narrower lifecycle/immutability claims; their broad 1072/1072 current sufficiency claim no longer supports the eleven affected predicates recorded in `E-MD-B18-A002-086`.

## B05 boundary

The MD-B05 identity tests prove distinct layers, temporal resolution, symbol reuse/relisting and one-database alias binding. They do not claim that a fresh database allocation preserves hash bytes. `MD-S057-R0028` and `R0029` are `MD-B21` primary and already `NOT_ASSESSED`. This finding does not invalidate B05's 117/117 bounded proof or move global-master issuance ownership into MD-B18.

## Required remediation and closure

Re-enter `MD-B10` under a successor attempt and current baseline. Obtain the shared foundation's stable issuer/instrument/listing identities or declare its external dependency; correct semantic artifact and manifest hashing without rewriting sealed historical digests; provide explicit legacy/successor interpretation where required; prove equality under different numeric allocations and sensitivity to changed semantic content; bind each affected predicate to a discriminating guard; revalidate the eleven `MD-S005` rows and the B10 closure state. `MD-B18-A002` then returns to the first independent R0025 fixture. No authority change or owner-defined UID issuance algorithm is authorized by this finding.

## Re-entry status — 2026-09-27

`MD-B10-A002` is active for remediation with immutable baseline `MD-B10-A002-BL001` and early `CI-MD-B10-A002-001`. No B10 runtime/schema mutation or hash proof has begun. `MD-DEP-0020` remains BLOCKING until the eleven predicates and B10 closure are revalidated; `MD-B18-A002` remains the blocked return-to stage.

## Final affected-current-verification scope — 2026-09-28T11:55:13+07:00

Current controlling correction: `E-MD-B10-A002-001` supersedes E086 only for affected-scope sufficiency. E086 eleven-rule determination was incomplete; all eleven remain affected and 45 additional predicates require revalidation. The earlier eleven/1061 count is historical and superseded. Full mandatory denominator 1072 across 31 proof families reviewed: current 1016 SATISFIED / 56 NOT_ASSESSED.

Final affected rules: `MD-S005-R0001`, `MD-S005-R0019`, `MD-S005-R0021`, `MD-S005-R0022`, `MD-S005-R0023`, `MD-S005-R0024`, `MD-S005-R0025`, `MD-S005-R0026`, `MD-S005-R0028`, `MD-S005-R0030`, `MD-S005-R0031`, `MD-S005-R0035`, `MD-S005-R0038`, `MD-S005-R0039`, `MD-S005-R0040`, `MD-S005-R0042`, `MD-S005-R0043`, `MD-S005-R0044`, `MD-S005-R0046`, `MD-S005-R0047`, `MD-S005-R0050`, `MD-S005-R0053`, `MD-S005-R0056`, `MD-S005-R0059`, `MD-S005-R0061`, `MD-S005-R0062`, `MD-S005-R0068`, `MD-S005-R0069`, `MD-S005-R0071`, `MD-S005-R0073`, `MD-S005-R0075`, `MD-S005-R0076`, `MD-S005-R0077`, `MD-S005-R0078`, `MD-S005-R0080`, `MD-S005-R0086`, `MD-S005-R0087`, `MD-S005-R0092`, `MD-S005-R0098`, `MD-S005-R0105`, `MD-S013-R0003`, `MD-S013-R0009`, `MD-S018-R0009`, `MD-S019-R0006`, `MD-S019-R0007`, `MD-S019-R0008`, `MD-S019-R0014`, `MD-S019-R0015`, `MD-S035-R0032`, `MD-S035-R0062`, `MD-S035-R0069`, `MD-S035-R0127`, `MD-S045-R0001`, `MD-S045-R0054`, `MD-S045-R0056`, `MD-S045-R0059`.

Classification remains IMPLEMENTATION_AND_PROOF_DEFECT. Configuration content hash appears in the manifest, but artifact preimages use snapshot numeric IDs; required source/reason/revision/ATR meaning is missing or ID-bearing; bars explicitly hash provider adj_close; correction/seal proof relies on the narrowed preimages. The issued successor has each rule causal chain and all unaffected-row rationale; no adjacent-family blanket demotion. B05 remains 117/117 on its valid scope.

Remediation continues in MD-B10-A002 / MD-B10-A002-BL001 / CI-MD-B10-A002-001. Reprove all final 56 affected predicates and valid successor closure before resolving MD-DEP-0020 and returning to MD-B18-A002 F-017/R0025. MD-DEP-0019 remains separately BLOCKING. First bounded unit is artifact semantic-identity binding: shared-foundation stable listing identity and config content-hash membership with historical hash compatibility. No identity issuance/root redesign is authorized; unavailable foundation identity must be a declared dependency. Runtime remediation not started.

## Artifact boundary prerequisite - 2026-09-28T14:51:16+07:00

E-MD-B10-A002-002 reverified the foundation consumer boundary. The current repository exposes legacy ticker projections and allocated navigation IDs, not a governed external immutable identity delivery for semantic artifacts. MD-DEP-0021 is BLOCKING for MD-B10-A002: shared security-identity foundation owner must supply retained stable issuer/instrument/listing identity, local temporal linkage and provenance/continuity evidence. No UID/root issuance policy is invented; this is an unavailable dependency, not missing strategy semantics.

All three artifact families and their stable ordering share this prerequisite. No production or schema patch, new artifact hasher, test mutation, or predicate promotion was made. Configuration content hash is available, and adj_close exclusion is already required; active legacy preimage edits are deferred until complete identity integration and historical/successor interpretation can be delivered coherently. Current 1016/1072 and final 56-rule scope remain unchanged.

MD-DEP-0020 remains BLOCKING for B18 and now records its upstream MD-DEP-0021; MD-DEP-0019 remains separate. F-MD-B10-A002-001 records the unrelated pre-existing traceability moved-count tooling debt without expanding this artifact unit. Exact next state: obtain and validate foundation delivery under MD-DEP-0021, then continue the same B10-A002 artifact unit.

## Foundation ownership and delivery handoff - 2026-09-28T15:30:36+07:00

E-MD-B10-A002-003 extends E002 through repository-wide owner/source review. Shared contracts belong in docs/db under docs/README.md; the legacy ticker master is present, but no governed issuer/instrument/listing master producer/import is available. Actual implementation owner and repository/API/export locator are UNKNOWN. The external foundation role remains recorded; no team, issuance algorithm or semantic authority is fabricated. This missing delivery/accountability is retained in MD-DEP-0021 and this OPEN finding, with no separate attempt or finding.

Required upstream delivery: retained immutable identities and issuer-to-instrument-to-listing relationships; authoritative effective/known-time symbol/provider/board mappings, continuity/relisting/reuse decisions, provenance/revision and frozen delivery identity; explicit crosswalk to local temporal records. Supply an identified responsible upstream implementation owner and actual governed repository/API contract or retained versioned master export. B10 must prove identical frozen facts imported under different local PK allocations retain identical supplied semantic identities, and missing/conflicting history fails closed. E003 contains the authority-to-delivery acceptance inventory; it is dependency evidence, not a new identity contract or runtime proof.

B05 remains DONE/PASS 117/117 on its bounded resolver scope; global identity delivery and external completeness are separate guarantees. B10 remains 1016 SATISFIED / 56 NOT_ASSESSED / 1072; MD-DEP-0020/0019/0021 remain BLOCKING. F-MD-B10-A002-001 is unchanged separate tooling debt. No code, test, schema or database change.

Single current next state: MD-B10-A002: wait for an identified upstream shared security-identity implementation owner to supply a governed repository/API or retained versioned master export with immutable issuer/instrument/listing identities, relationships, temporal mappings, continuity/relisting decisions and provenance under MD-DEP-0021; validate import/rebuild allocation independence and fail-closed linkage before returning to the same artifact semantic-identity unit. No useful hash implementation can resume before that delivery. MD-DEP-0020/0019 remain BLOCKING; no MD-B18/R0025 or F-018 work.

## Same-repository foundation authority and source dependency - 2026-09-28T16:06:44+07:00

E-MD-B10-A002-004 materially clarifies the preceding delivery handoff: outside Market Data is logical ownership, not a separate repository or service. Current architecture permits the shared identity foundation inside tradeaxis-api. No external UID issuer/API is mandatory; a shared owner may assign once, retain and export/import technical domain IDs grounded in admitted entity facts. The identity invariants are already authoritative; no issuance representation is selected.

Verdict B: AUTHORITY SUFFICIENT WITH EXTERNAL DATA DEPENDENCY. docs/db provides legacy ticker/display/audit minima; MD-S057/055 provide distinct entity layers, immutable identity, temporal mapping and explicit continuity/reuse/relisting with fail-closed ambiguity. The missing data are source-backed issuer-to-instrument-to-listing relationships, real effective/known-time history and documented continuity decisions. Neither company name nor current ticker projection establishes those facts. Accountable implementation/data owner and admitted dataset remain UNKNOWN. External data means evidence outside code derivation, not an external application.

Current MD temporal tables/resolver retain their valid consumer/projection scope; physical structure reuse is a later controlled migration/ownership choice. No authority conflict or semantic decision is needed for a fail-closed foundation. Future shared delivery must be correlated to its actual owner/source and early impact/baseline/ADR requirements before implementation; no arbitrary stage or B05 re-entry is created by this review.

Same A002/BL001/CI; B05 PASS 117/117, B10 1016 SATISFIED / 56 NOT_ASSESSED / 1072, B18 100 PROVEN / 13 INCOMPLETE / 113 and formal 0 remain unchanged. No code/schema/DB/test/hash/fixture change. F-MD-B10-A002-001 remains separate and byte-identical; MD-DEP-0021/0020/0019 remain BLOCKING.

Single current next state: MD-B10-A002 / MD-DEP-0021: identify and admit a provenance-backed identity/lifecycle master dataset and accountable shared-foundation implementation/data owner for delivery inside tradeaxis-api. Required facts: distinct issuer/instrument/listing relationships, effective and known-time symbol/provider/board history, and evidenced continuity/relisting/reuse decisions. Existing authority permits the shared module; no separate repository/service or external ID issuer is required. Freeze the source/retained identity basis and govern the delivery work before implementation; validate allocation independence before returning to the same B10 artifact unit. MD-DEP-0021/0020/0019 remain BLOCKING; no B18/R0025 or F-018 work.

## Master data inventory, bounded admission and accountable shared ownership - 2026-09-28T23:56:14+07:00

E-MD-B10-A002-005 extends E004 with actual read-only database/source-file inventory. The configured tradeaxis tickers and six md identity/mapping/board tables contain zero rows; the same seven tables are empty in tradeaxis_testing. Test source observations are not actual master evidence. The retained import directories contain 44 source files; membership CSVs carry 971 rows and 29 new-listing reference leads, not a complete admitted master. Current source labels, price/symbol rows, suspension/potential-delisting notes and generic corporate-action references do not establish issuer/instrument/listing continuity. The restricted uncertified data_260914 recovery was not accessed.

Data readiness B: CURRENT DATA PARTIALLY SUFFICIENT. Frozen foundation-source-basis-20260928-v1 admits only explicitly observed IKPM registered issuer/common-share facts and ATLA issuer/offering/announced listing facts. KSEI listing date is corroborating; eIPO schedule is not executed admission. Capture/revision limits are explicit, no historical known-time is invented. Zero roots assigned; zero complete Market Data listing deliveries. Package manifest and per-file hashes are linked in E005; missing/changed material invalidates source admission.

D-MD-B10-A002-001 assigns implementation/persistence to the same-repository TradeAxis Shared Security Identity Foundation logical domain and accountability/data stewardship to the TradeAxis project-owner role. This is a bounded architecture ownership decision under the current request, not strategy revision or a fabricated named team/person approval. B05/B10/B21 proof boundaries remain. No identity algorithm, physical table/module path or new stage/attempt is selected.

Current authority permits per-instrument partial failure/held identity with exposed coverage gaps. Therefore bounded fail-closed shared implementation can begin against real admitted facts while unsupported instrument/listing/mapping/continuity/history stays HELD. This does not permit B10 artifact hashing or R0025 to resume. MD-DEP-0021 remains BLOCKING until implemented provenance-backed consumed-scope linkage, retained stable import/rebuild and allocation-independent/fail-closed acceptance pass; 0020/0019 remain separate BLOCKING. Current B05 117/117, B10 1016/1072 with final 56 NOT_ASSESSED, B18 100/113 basis and formal 0 unchanged.

Single current next state: MD-B10-A002 / MD-DEP-0021: implement the bounded fail-closed Shared Security Identity Foundation inside tradeaxis-api using foundation-source-basis-20260928-v1 under D-MD-B10-A002-001 ownership. Admit only the frozen source facts, retain unresolved listing/mapping/continuity/history as HELD, and choose no ticker-derived roots. Govern the concrete shared implementation/intake and its acceptance in the same valid A002/BL001/CI; no new stage/attempt presumed. No Market Data identity is usable until complete evidenced listing linkage and allocation-independent delivery pass. MD-DEP-0021/0020/0019 stay BLOCKING; hash remediation, R0025 and F-018 remain paused.

## 2026-09-29 shared foundation core checkpoint — E-MD-B10-A002-006

D-MD-B10-A002-002 records the conformant technical retained-registry mechanism and shared module boundaries under D001. Core implemented and proven on isolated MariaDB: 16 tests / 223 assertions, zero errors/skips; seven distinct production clauses turned RED at their assertions and restored byte-identically with GREEN controls (nine negative executions, two repeated after shape hardening). FK migration and empty roundtrip, refusal of populated destructive rollback, idempotence, allocation/rebuild, provenance, rename/reuse/termination/as-known/held behavior proven. Synthetic listing fixtures are mechanism evidence, not historical master admission.

E005 bootstrap assigns two issuer and one instrument roots in a retained versioned registry; zero listing roots/MD-usable listings. Normal tradeaxis/testing schemas/data not activated or changed; all test-owned databases removed. Existing ticker/md projection and artifact hashing unchanged, R0025 preserved. MD-DEP-0021 remains BLOCKING on complete consumed-scope factual listing linkage, controlled deployment and consumer acceptance; MD-DEP-0020/0019 remain BLOCKING. B05 117/117 retained; B10 1016/56/1072 and B18 100/13/113 formal0 unchanged. Finding stays OPEN.

Single next bounded work: MD-B10-A002 / MD-DEP-0021: acquire and governedly admit authoritative executed listing, venue/board and effective symbol/provider mapping evidence for the already evidenced IKPM instrument into the implemented shared foundation. Retain unsupported continuity and historical knowledge as HELD. Core implementation is complete; do not repeat it. Same A002/BL001/CI. Production deployment and Market Data intake acceptance remain required before dependency resolution or hash remediation. MD-DEP-0021/0020/0019 remain BLOCKING; R0025 and F-018 stay paused.

## 2026-09-29 first real IKPM listing admission — E-MD-B10-A002-007

The immutable successor source package `foundation-source-basis-20260929-ikpm-listing-v2` extends, and does not rewrite, E005's v1 package. The official IDX announcement admits the executed 2023-11-08 IKPM listing, IDX venue, IKPM exchange symbol and Development Board for the bounded initial-listing interval. The exact Yahoo Finance chart response admits `IKPM.JK` only from its 2026-09-29 capture. No current observation is backdated to the listing date.

The existing issuer and instrument identities are retained and one listing root is assigned once. Isolated MariaDB controls pass 7/96 for the IKPM path and 16/223 for the foundation core, both with zero skips. Different local allocation histories, repeat admission and export/import/rebuild preserve the same issuer, instrument and listing identities. Three production mechanisms were mutated and turned RED at the intended provider-interval, lifecycle-coverage and entity-parent assertions; all sources were restored byte-identically and controls returned GREEN. Temporary databases and normal shared-foundation tables remaining: zero.

Resolution level is B: the canonical IKPM listing is admitted, but Market Data consumer resolution remains HELD. Historical Yahoo resolution lacks a supported provider interval; capture-time Yahoo resolution lacks verified listing lifecycle/board/symbol continuity after the first listing day. MD-DEP-0021 remains BLOCKING on complete consumed-scope factual coverage, controlled deployment and consumer intake acceptance. No B10 predicate is promoted; B05 117/117, B10 1016/56/1072 and B18 100/13/113 formal 0 are unchanged.

Single next bounded work: MD-B10-A002 / MD-DEP-0021: acquire and governedly admit authoritative IKPM lifecycle, symbol and board continuity/termination evidence covering the interval after 2023-11-08 through the admitted Yahoo capture boundary. Preserve the capture-time provider mapping and retained listing root; do not repeat listing admission. Only after one instant has complete lifecycle plus provider coverage may bounded Market Data consumer integration begin. MD-DEP-0021/0020/0019 remain BLOCKING; R0025 and F-018 stay paused.

## 2026-09-29 first consumer-ready IKPM temporal instant — E-MD-B10-A002-008

The official IDX current-profile response captured at `2026-09-29 00:38:15 UTC` confirms IKPM, IDX listing context and Development Board at that snapshot. It overlaps the retained Yahoo `IKPM.JK` mapping for exactly the admitted half-open interval `[2026-09-29 00:38:15, 2026-09-29 00:38:16) UTC`. The shared foundation resolves the retained IKPM listing at the target instant and remains HELD before/after the admitted interval and under an earlier knowledge cutoff. No continuity across the 2023–2026 gap or future validity is claimed.

The issuer, instrument and listing roots remain unchanged; v3 adds only LISTING, SYMBOL and BOARD successor revisions. Isolated MariaDB controls pass 5/79 for the consumer window, 7/96 for the listing path and 16/223 for the core, all with zero skips. Three production mechanisms were mutated and turned RED at the intended half-open interval, knowledge-cutoff and official-board source-extraction guards; production sources were restored byte-identically and controls returned GREEN.

MD-DEP-0021 remains BLOCKING: foundation consumer readiness is proven for one instant, while controlled Market Data consumption and allocation-independent intake are not implemented or proven. No B10 predicate is promoted; B05 117/117, B10 1016/56/1072 and B18 100/13/113 formal 0 remain unchanged. The next bounded unit is Market Data consumer integration for the exact admitted instant, without changing artifact hashes.

## 2026-09-29 bounded Market Data foundation intake — E-MD-B10-A002-009

Market Data now has an explicit foundation-required read path through the shared `IdentityResolver` contract and `FoundationService`. At the E008 instant it propagates the retained issuer, instrument and listing UUID roots plus IDX/IKPM and Yahoo/IKPM.JK context. HELD, AMBIGUOUS, missing-root and missing-provider results block without consulting `tickers` or `md_*`, synthesizing `.JK`, or fabricating identity. The legacy projection path remains separate for B05 compatibility.

Isolated MariaDB proof varies ticker IDs, Market Data surrogate rows and foundation surrogate allocations while producing identical consumer results. Four restored mutations prove allocation identity, fallback, temporal and propagation guards. Controls pass 5/71 integration, 16/223 core, 7/96 listing, 5/79 window and 9/104 B05 regression with zero skips and database residue.

MD-DEP-0021 remains BLOCKING on artifact-side consumption and allocation-independent semantic hash proof. No artifact hash, manifest, seal, correction or predicate binding changed; B05 117/117, B10 1016/56/1072 and B18 100/13/113 formal 0 remain unchanged. Next bounded unit is bars/indicators/eligibility semantic artifact identity using the now-proven consumer result.

## 2026-09-29 bounded artifact semantic identity checkpoint — E-MD-B10-A002-010

The bars, indicators and eligibility artifact primitive is now implemented and locally proven under explicit `market-data-semantic-hash/v2`. It consumes the retained foundation issuer/instrument/listing roots exposed by E009, binds immutable configuration content, excludes allocation IDs and provider `adj_close`, and orders by stable semantic identity and canonical bytes. Historical NULL-profile rows remain `market-data-row-hash/v1`; no sealed history is rewritten.

Equivalent facts under different ticker, Market Data, foundation and config allocations produce identical hashes for all three artifacts; changed bar, indicator, eligibility, config or retained listing semantics change the owning hash. Four production mutations were caught and restored byte-identically. The inherited R0025 files still match the E001 fingerprints.

The finding remains OPEN. E010 proves bounded artifact clauses only. Publication-manifest, nested lineage production, correction, seal and full current-verification review remain outstanding; all 56 affected predicates stay NOT_ASSESSED. MD-DEP-0021 remains BLOCKING on controlled normal-database deployment and end-to-end successor-profile acceptance. MD-DEP-0020 continues to block B18/R0025 until complete B10 reproof and successor closure.

Single next bounded work: same MD-B10-A002 / BL001 / CI, remediate publication-manifest, correction and seal semantic identity using the versioned V2 artifact hashes while preserving legacy V1 interpretation. Do not promote predicates until complete governed reproof.

## 2026-09-30 publication/correction/seal successor-profile progress — E-MD-B10-A002-011

V2 publication, correction/republication and seal semantic identity is implemented and locally proven. The publication preimage uses V2 bars/indicators/eligibility roots, stable predecessor manifest hashes and configuration/nested content hashes; correction uses semantic baseline/replacement/reason material; seal binds their semantic fingerprints. Numeric publication/correction/run/config row identities do not enter V2 identity. Explicit profile dispatch preserves the V1 payload and fails closed on unknown, missing or mixed V2 material; historical hashes are not reinterpreted or rewritten.

Two isolated MariaDB histories with different publication/run/correction/local allocations converge on identical V2 publication, correction and seal values. Meaningful artifact, configuration, lineage and correction changes diverge. Four probes removed critical semantic members or profile enforcement, each turned the intended assertion RED, and the production service was restored byte-identically. Additive migration compatibility and focused shared-repository regression are green.

The finding remains OPEN and its final 56-rule scope is unchanged. E011 proves the bounded publication/correction/seal clauses, but nested observation/factor/temporal revision-set producer identity, sorted semantic reason coverage, controlled deployment and complete-denominator reproof remain. No predicate is promoted; B05 117/117, B10 1016/56/1072 and B18 100/13/113 formal 0 remain unchanged. MD-DEP-0021 and MD-DEP-0020 remain BLOCKING.

## 2026-09-30 E011 correction, env-reader fix and production-path proof — E-MD-B10-A002-012

E-MD-B10-A002-012 supersedes E011 without editing it. Verifying E011 showed that its full-suite classification called one remediation regression pre-existing: `EodRunRepository` read `env('MARKET_DATA_ARTIFACT_HASH_PROFILE')` directly, the read entered with the E010 sub-unit, and E011's profile selection calls it. The switch now lives in `config/market_data_runtime.php`, outside the snapshot-hashed `market_data` tree; run creation reads it through `config()` and fails closed on a missing or unknown value.

E011's allocation-independence test hashed documents that contain no allocated key, so it could not detect a leak in the repository assembly. `PublicationSemanticIdentityProductionPathTest` now drives the production repository path from candidate to promotion in two histories with different local keys and insertion order, for a correction republication and a plain first publication. All three identities converge, a changed predecessor diverges, and fifteen out-of-band tampering cases stop promotion with the check that owns the material. Probes P1-P3 leak an allocation through the repository: each leaves every E011 V2 proof file green and turns the new test red.

The 16 recovered failures are 2 A (both fixed), 8 D, 2 F and 4 E. The four E rows are static-guard regressions from committed units after the E-MD-B18-A002-074 baseline and are recorded as F-MD-B10-A002-002.

The finding remains OPEN and its 56-rule scope is unchanged. Nested observation/factor/temporal revision-set producer identity, sorted semantic reasons, controlled deployment and complete reproof remain. No predicate is promoted; B05 117/117, B10 1016/56/1072 and B18 100/13/113 formal 0 are unchanged. MD-DEP-0021 and MD-DEP-0020 remain BLOCKING.

## 2026-10-01 owner membership decision — D-MD-B10-A002-003

The remaining producers stopped on two membership questions that authority leaves open: whether a pipeline-derived assessment's `recorded_at` belongs in V2 identity, and which reasons the manifest-level sorted reason set holds. The owner decided both in D-MD-B10-A002-003. Platform-created assessments, factor sets and decisions hash their content, and `recorded_at` stays provenance and visibility time. Source-fact revisions keep governed knowledge time in the semantic revision tuple. The manifest binds a deduplicated, canonically sorted publication-scope reason set that holds the coverage reason and duplicates nothing already bound in a nested or artifact hash.

The finding remains OPEN and its 56-rule scope is unchanged. The nested producers are not implemented yet. No predicate is promoted; MD-DEP-0021 and MD-DEP-0020 remain BLOCKING.

## 2026-10-01 V2 nested semantic producers — E-MD-B10-A002-013

Under D-MD-B10-A002-003, E-MD-B10-A002-013 implements the remaining semantic producers additively: a V2 observation manifest bound at ingest beside V1, and V2 identity-board, calendar, trading-status, market-structure, event, source-scale, factor-decision and factor-set identities derived at the hash stage. Per-listing sets carry retained foundation roots, source-fact revisions carry their governed knowledge time, and platform-created records carry content only. V2 artifacts, the V2 manifest and the V2 correction identity read only these identities, and the manifest binds the publication-scope reason set (the coverage reason). V1 columns and their consumers are unchanged.

On isolated MariaDB databases with the retained IKPM root, the real producers give identical V2 identities, V2 bars artifact and V2 manifest across histories that differ in every local key, key order, insertion order and producer clock, while every V1 nested hash differs. Genuine semantic changes move exactly their owning identities, and thirteen mutation probes turned the intended assertions red. F-MD-B10-A002-003 records the cutoff exposure decision 1A asked for.

The finding remains OPEN and its 56-rule scope is unchanged. Controlled deployment, F-MD-B10-A002-002 and the complete governed reproof remain. No predicate is promoted; MD-DEP-0021 and MD-DEP-0020 remain BLOCKING.

## 2026-10-01 controlled deployment — E-MD-B10-A002-015

The B10 successor schema and the Shared Security Identity Foundation are deployed on the normal `tradeaxis` database: four additive migrations (batch 5) and the three governed IKPM packages bootstrapped with exact retained roots, idempotent on re-run, with foundation and Market Data identity-intake acceptance passed there. Existing rows are preserved. The finding remains OPEN with its 56-rule scope unchanged: the complete governed reproof and the successor full-suite control remain. No predicate is promoted; MD-DEP-0021 and MD-DEP-0020 remain BLOCKING.

## 2026-10-01 complete reproof — E-MD-B10-A002-016

The complete reproof of the 56 affected predicates ran against the deployed implementation: 31 are `PROVEN_CURRENT` and 25 are `INCOMPLETE`. `F-MD-B10-A002-005` records five V2 coverage gaps (stored bar timestamps, the ATR recursive-state reference, sector membership revision identity, eligibility freshness, the correction comparison's calendar binding) and the missing negative proof. This finding remains OPEN with its 56-rule scope unchanged. Nothing is promoted; `MD-DEP-0021` and `MD-DEP-0020` stay BLOCKING, and the return to B18 stays blocked until the remediation, the successor full-suite re-run and a reproof that proves all 56.
