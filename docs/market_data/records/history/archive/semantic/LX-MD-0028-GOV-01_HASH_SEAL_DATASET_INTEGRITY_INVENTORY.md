# Legacy Semantic Extract — LX-MD-0028-GOV-01

- Source ID: `LS-MD-0028`
- Original path: `audit/HASH_SEAL_DATASET_INTEGRITY_INVENTORY.md`
- Original SHA1: `C8D94C9D62FC23B2978DB75D31E851645BBF5CCF`
- Extract role: `GOVERNANCE`
- Source range: `L10-L48`
- Extract body SHA1: `10C9E78762E46BF264AD44686514B3A84092F9C2`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Scope
Inventory ini mencatat proof surface untuk hash, seal, manifest, immutability, replay, evidence, correction, command output, dan read-side dependency. Catatan container ZIP tanpa `vendor/` di bawah dipertahankan sebagai histori awal; status aktif scope ini sudah LOCKED melalui Lumen tracker dan validasi full PHPUnit berikutnya.

| Area | Hash Deterministic | Seal Validated | Manifest Complete | Immutable After Seal | Replay/Evidence Proof | Gap | Patch | Test Result |
|---|---|---|---|---|---|---|---|---|
| EOD bars | Yes, `DeterministicHashService` + explicit bars columns | Yes, mandatory hash before seal | Yes, manifest includes bars hash/row/column scope | Live mutation blocked when sealed/current/readable exists | Evidence/replay carries `bars_batch_hash` | Runtime recompute-on-seal is not full row-by-row re-read yet | Config-driven hash + mutation guard | `php -l` PASS; closed by later local PHPUnit proof |
| Indicators | Yes, explicit indicator columns | Yes, mandatory hash before seal | Yes, manifest includes indicator hash/row/column scope | Live mutation blocked when sealed/current/readable exists | Evidence/replay carries `indicators_batch_hash` | Runtime recompute-on-seal is not full row-by-row re-read yet | Config-driven hash + mutation guard | `php -l` PASS; closed by later local PHPUnit proof |
| Eligibility | Yes, explicit eligibility columns | Yes, mandatory hash before seal | Yes, manifest includes eligibility hash/row/column scope | Live mutation blocked when sealed/current/readable exists | Evidence/replay carries `eligibility_batch_hash` | Runtime recompute-on-seal is not full row-by-row re-read yet | Config-driven hash + mutation guard | `php -l` PASS; closed by later local PHPUnit proof |
| Artifact hash | Yes, config-driven algorithm/delimiter/line/null token and canonical sort | Required by seal | Included as `component_hashes` | Publication hash update blocked after SEALED | Replay expected/actual hash comparison exists | N/A | Hash service hardened | `DeterministicHashServiceTest` expanded; closed by later local proof |
| Artifact manifest | Virtual manifest from publication + run + config | Manifest context checked before seal | Includes source, coverage, hash contract, columns, ordering | N/A | Evidence exports `publication_manifest.json` | No physical manifest table/column yet | Manifest builder enriched | Static guard added |
| Dataset seal | Requires hashes + rows for normal publication | SEALED only after hash/context checks | Manifest exposes verification status/reason | Publication update blocked after SEALED | Evidence/replay include seal state | Seal lifecycle enum still only `SEALED/UNSEALED` in current schema | Reason-coded failures + verify context | Static guard added |
| Finalize | Candidate/run hashes must exist/match | Blocks missing/invalid seal | Uses sealed candidate + pointer guard | N/A | Replay/evidence include final state | No full recompute during finalize yet | `FINALIZE_HASH_*` and `FINALIZE_SEAL_*` guards | Static guard added |
| Publication | Hash fields mirrored from run | `seal_state + sealed_at` required | Manifest by publication id | `assertPublicationMutable` blocks sealed publication update | Evidence manifest output | N/A | Manifest/context hardening | `php -l` PASS |
| Pointer | Pointer resolves only SEALED/SUCCESS/READABLE/PASS | Required for readable current | Pointer context in evidence/replay | N/A | Existing pointer proof retained | N/A | Hash mismatch guard before promotion | `php -l` PASS |
| Correction | Publication diff compares hash triplet | Changed correction reseals; unchanged skips | Manifest contains prior/new lineage when available through evidence | Baseline live artifact mutation blocked | Correction evidence/replay context exists | Baseline seal recompute not full row-by-row yet | Guards preserve baseline + hash-aware unchanged decision | Static guard added |
| Replay | Expected/actual artifact hashes compared | Expected/actual seal state compared | Fixture manifest required | N/A | Mismatch reason codes persisted | Manifest field-level hash contract can be expanded later | Registry/seed synced | Existing replay tests closed by later local proof |
| Evidence | Hash/seal/source/coverage/publication context exported | Manifest includes verification summary | Yes | N/A | Proof enough for audit without direct DB lookup | No physical manifest snapshot table yet | Manifest enriched | Evidence tests closed by later local proof |
| Session snapshot | Depends on pointer-resolved readable publication | Inherits publication seal requirement | N/A | N/A | Snapshot command output includes reason codes | N/A | No direct change | Existing tests closed by later local proof |
| Read-side consumer | Pointer-first only | SEALED/SUCCESS/READABLE/PASS required | N/A | N/A | Evidence/replay trace dependency | N/A | No raw/staging bypass added | Static scan closed by later local proof |
| Command output | Summary prints hash/seal config and batch hashes | Prints seal status from run sealed_at | Evidence command exports manifest | Mutation commands reason-coded | Output includes reason_code | Publication-level seal_state not fetched in generic run summary | `renderDatasetIntegritySummary` added | Static guard added |
| Static guard | New hash/seal integrity static guard | Yes | Yes | Yes | Yes | Full PHPUnit closed by later local proof | `HashSealDatasetIntegrityStaticGuardTest` | closed by later local proof |

## Canonical serialization rule
`DeterministicHashService` serializes only the explicit column list passed by the artifact caller, normalizes each value deterministically, length/JSON-encodes scalar values to avoid delimiter ambiguity, joins columns with `MARKET_DATA_HASH_DELIMITER`, sorts canonical row lines using `SORT_STRING`, and joins rows with `MARKET_DATA_HASH_LINE_SEPARATOR`. Logical row order therefore does not change the hash.

## Hash config rule
Hash settings are config/env-driven:

- `MARKET_DATA_HASH_ALGORITHM`, default `SHA-256`
- `MARKET_DATA_HASH_DELIMITER`, default `|`
- `MARKET_DATA_HASH_LINE_SEPARATOR`, default `\n`
- `MARKET_DATA_HASH_NULL_TOKEN`, default `[empty]`

## Seal rule
Normal publication seal is blocked when mandatory component hashes or row-count context are missing. Partial repair candidates remain allowed only through the explicit partial repair path and must not become normal current readable data.

## Immutability rule
Normal live artifact replacement for `eod_bars`, `eod_indicators`, or `eod_eligibility` is blocked when the target trade date already has a different sealed/current/readable publication in the live artifact table. Correction must use history/candidate flow and must not mutate the baseline publication directly.


<!-- LEGACY_EXTRACT_BODY_END -->
