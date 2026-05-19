# HASH / SEAL / DATASET INTEGRITY INVENTORY

[RELATED_CONTRACT] HASH_SEAL_DATASET_INTEGRITY_CONTRACT
[LAST_UPDATED] 2026-05-07
[STATUS] DONE / LOCKED_LOCAL_PHPUNIT_PASS

## Scope
Inventory ini mencatat proof surface untuk hash, seal, manifest, immutability, replay, evidence, correction, command output, dan read-side dependency. Container ZIP tidak menyertakan `vendor/`, sehingga status final tetap pending validasi lokal targeted/full PHPUnit.

| Area | Hash Deterministic | Seal Validated | Manifest Complete | Immutable After Seal | Replay/Evidence Proof | Gap | Patch | Test Result |
|---|---|---|---|---|---|---|---|---|
| EOD bars | Yes, `DeterministicHashService` + explicit bars columns | Yes, mandatory hash before seal | Yes, manifest includes bars hash/row/column scope | Live mutation blocked when sealed/current/readable exists | Evidence/replay carries `bars_batch_hash` | Runtime recompute-on-seal is not full row-by-row re-read yet | Config-driven hash + mutation guard | `php -l` PASS; local PHPUnit pending |
| Indicators | Yes, explicit indicator columns | Yes, mandatory hash before seal | Yes, manifest includes indicator hash/row/column scope | Live mutation blocked when sealed/current/readable exists | Evidence/replay carries `indicators_batch_hash` | Runtime recompute-on-seal is not full row-by-row re-read yet | Config-driven hash + mutation guard | `php -l` PASS; local PHPUnit pending |
| Eligibility | Yes, explicit eligibility columns | Yes, mandatory hash before seal | Yes, manifest includes eligibility hash/row/column scope | Live mutation blocked when sealed/current/readable exists | Evidence/replay carries `eligibility_batch_hash` | Runtime recompute-on-seal is not full row-by-row re-read yet | Config-driven hash + mutation guard | `php -l` PASS; local PHPUnit pending |
| Artifact hash | Yes, config-driven algorithm/delimiter/line/null token and canonical sort | Required by seal | Included as `component_hashes` | Publication hash update blocked after SEALED | Replay expected/actual hash comparison exists | N/A | Hash service hardened | `DeterministicHashServiceTest` expanded; pending local |
| Artifact manifest | Virtual manifest from publication + run + config | Manifest context checked before seal | Includes source, coverage, hash contract, columns, ordering | N/A | Evidence exports `publication_manifest.json` | No physical manifest table/column yet | Manifest builder enriched | Static guard added |
| Dataset seal | Requires hashes + rows for normal publication | SEALED only after hash/context checks | Manifest exposes verification status/reason | Publication update blocked after SEALED | Evidence/replay include seal state | Seal lifecycle enum still only `SEALED/UNSEALED` in current schema | Reason-coded failures + verify context | Static guard added |
| Finalize | Candidate/run hashes must exist/match | Blocks missing/invalid seal | Uses sealed candidate + pointer guard | N/A | Replay/evidence include final state | No full recompute during finalize yet | `FINALIZE_HASH_*` and `FINALIZE_SEAL_*` guards | Static guard added |
| Publication | Hash fields mirrored from run | `seal_state + sealed_at` required | Manifest by publication id | `assertPublicationMutable` blocks sealed publication update | Evidence manifest output | N/A | Manifest/context hardening | `php -l` PASS |
| Pointer | Pointer resolves only SEALED/SUCCESS/READABLE/PASS | Required for readable current | Pointer context in evidence/replay | N/A | Existing pointer proof retained | N/A | Hash mismatch guard before promotion | `php -l` PASS |
| Correction | Publication diff compares hash triplet | Changed correction reseals; unchanged skips | Manifest contains prior/new lineage when available through evidence | Baseline live artifact mutation blocked | Correction evidence/replay context exists | Baseline seal recompute not full row-by-row yet | Guards preserve baseline + hash-aware unchanged decision | Static guard added |
| Replay | Expected/actual artifact hashes compared | Expected/actual seal state compared | Fixture manifest required | N/A | Mismatch reason codes persisted | Manifest field-level hash contract can be expanded later | Registry/seed synced | Existing replay tests pending local |
| Evidence | Hash/seal/source/coverage/publication context exported | Manifest includes verification summary | Yes | N/A | Proof enough for audit without direct DB lookup | No physical manifest snapshot table yet | Manifest enriched | Evidence tests pending local |
| Session snapshot | Depends on pointer-resolved readable publication | Inherits publication seal requirement | N/A | N/A | Snapshot command output includes reason codes | N/A | No direct change | Existing tests pending local |
| Read-side consumer | Pointer-first only | SEALED/SUCCESS/READABLE/PASS required | N/A | N/A | Evidence/replay trace dependency | N/A | No raw/staging bypass added | Static scan pending local |
| Command output | Summary prints hash/seal config and batch hashes | Prints seal status from run sealed_at | Evidence command exports manifest | Mutation commands reason-coded | Output includes reason_code | Publication-level seal_state not fetched in generic run summary | `renderDatasetIntegritySummary` added | Static guard added |
| Static guard | New hash/seal integrity static guard | Yes | Yes | Yes | Yes | Full PHPUnit pending local | `HashSealDatasetIntegrityStaticGuardTest` | pending local |

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

## Reason codes added
- `DATASET_HASH_CREATED`
- `DATASET_HASH_VERIFIED`
- `DATASET_HASH_MISSING`
- `DATASET_HASH_MISMATCH`
- `DATASET_MANIFEST_INVALID`
- `DATASET_SEAL_INVALID`
- `SEALED_DATASET_MUTATION_BLOCKED`
- `FINALIZE_HASH_MISSING`
- `FINALIZE_HASH_MISMATCH`
- `FINALIZE_SEAL_MISSING`
- `FINALIZE_SEAL_INVALID`

## Manual validation pending
Run locally:

```bash
vendor/bin/phpunit tests/Unit/MarketData/HashSealDatasetIntegrityStaticGuardTest.php
vendor/bin/phpunit tests/Unit/MarketData/DeterministicHashServiceTest.php
vendor/bin/phpunit tests/Unit/MarketData --filter "Hash"
vendor/bin/phpunit tests/Unit/MarketData --filter "Seal"
vendor/bin/phpunit tests/Unit/MarketData --filter "Integrity"
vendor/bin/phpunit tests/Unit/MarketData --filter "Finalize"
vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"
vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"
vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"
vendor/bin/phpunit tests/Unit/MarketData --filter "CommandSurface"
vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"
vendor/bin/phpunit tests/Unit/MarketData
```


## Recovery from operator-local PHPUnit failures — 2026-05-07

Local failures showed three implementation/fixture-contract mismatches: source/API timeout default had drifted to `15` while source/provider tests expect `20`; direct repository candidate sealing updated publication hashes but not the owning run mirror; and replacement candidates for a date that already has a sealed/current publication wrote derived artifacts/hash against live tables instead of candidate history, triggering `SEALED_DATASET_MUTATION_BLOCKED` before finalize could produce the expected held/force-replace behavior.

Recovery patch:

- restored `MARKET_DATA_SOURCE_API_TIMEOUT_SECONDS` and config fallback to `20`;
- mirrored `updateCandidateHashes()` into `eod_runs` so repository-level seal/promote tests use consistent run/publication hash context;
- moved current-pointer/operator replacement validation before final hash equality checks so existing current-pointer integrity errors are not masked by `FINALIZE_HASH_MISSING`;
- routed indicators, eligibility, and hash computation for superseding/replacement candidates through history artifacts;
- promoted history artifacts into live current tables only after pointer promotion is allowed, preserving sealed baseline immutability for non-force replacement attempts.

Status remains `ENFORCED_PENDING_LOCAL_PHPUNIT` until the recovered targeted filters and full `tests/Unit/MarketData` pass locally.

## Recovery from operator-local PHPUnit failures — 2026-05-07 / round 2

Local retest after the first recovery showed these remaining failures: `Seal`, `Integrity`, `Pointer`, and `Publication` passed, while `Artifact`/`Evidence` still observed `timeout_seconds=15` instead of the source/provider contract baseline `20`; `Finalize`/`Integration` still hit `SEALED_DATASET_MUTATION_BLOCKED` during indicator recomputation for replacement candidates.

Recovery patch:

- test SQLite bootstrap now sets `market_data.source.api.timeout_seconds=20` to prevent local `.env` or prior test config drift from leaking into source/provider integration assertions;
- replacement candidate publications with `publication_version > 1` now route indicator computation, eligibility build, and hash generation through history artifacts from the beginning of the candidate lifecycle;
- sealed/current/readable baseline immutability remains enforced because replacement candidates no longer delete/reinsert live indicator/eligibility rows before finalize decides whether force replacement is allowed.

Status remains `ENFORCED_PENDING_LOCAL_PHPUNIT` until the recovered targeted filters and full `tests/Unit/MarketData` pass locally.

## Recovery from operator-local PHPUnit failures — 2026-05-07 / round 3

Local retest after round 2 showed `Artifact` and `Evidence` passed and timeout drift was resolved, leaving only replacement promote/finalize cases blocked by `Cannot seal dataset before all mandatory hashes exist`. The remaining gap was that replacement candidates which were created from an already completed/current seed run had no candidate-bound `eod_bars_history` rows before indicator/hash/seal stages, so mandatory history hashes could not represent a complete candidate artifact set.

Recovery patch:

- replacement candidate indicator computation now ensures candidate-bound bars history exists before loading the indicator window;
- when no candidate bars history exists, current live bars for the trade date are copied into the candidate publication history using the replacement run id/publication id, preserving baseline immutability while giving the candidate its own hashable artifact scope;
- hash generation also ensures candidate bars history before hashing replacement candidates;
- live sealed/current/readable artifact mutation remains blocked because the recovery writes candidate history only and does not delete/reinsert live rows before finalize authorizes pointer promotion.

Status remains `ENFORCED_PENDING_LOCAL_PHPUNIT` until the recovered `Finalize`, `Integration`, and full `tests/Unit/MarketData` pass locally.


## Production-Ready Reconciliation Addendum

Current canonical status for this scope is LOCKED in `LUMEN_CONTRACT_TRACKER.md`. Historical pending/local-validation wording above is retained as session history only. The full production-ready proof pack records final operator-local validation: AuditDocs OK (10 tests, 363 assertions), Replay OK (57 tests, 904 assertions), StaticGuard OK (170 tests, 3950 assertions), and full `tests/Unit/MarketData` OK (453 tests, 6671 assertions).
