# Legacy Semantic Extract — LX-MD-0031-IMP-02

- Source ID: `LS-MD-0031`
- Original path: `audit/LUMEN_IMPLEMENTATION_STATUS.md`
- Original SHA1: `3FDDFFF0B53D431DAA71A8545F87173445A616F7`
- Extract role: `IMPLEMENTATION`
- Source range: `L3619-L3652`
- Extract body SHA1: `F681DC30652AE98DFDFB988304E5024558122F3D`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Recovery-3 malformed fallback pointer fix — Coverage Gate Enforcement / No Coverage Bypass

- Status: SUPERSEDED_BY_FINAL_LOCK.
- Local evidence received: static guard, Coverage, Publication, readable, Evidence, Replay, and Command suites passed; one integration/pointer failure remained for malformed fallback pointer effective-date handling.
- Recovery-3 fix: when correction pointer mismatch occurs and no contract-valid readable fallback exists, `trade_date_effective` is explicitly cleared to null instead of retaining the requested candidate date.
- Final result: superseded by Recovery-5 final local validation; `MarketDataPipelineIntegrationTest`, pointer filter, and full `tests/Unit/MarketData` all PASS.

## Recovery-4 fallback mirror fixture alignment — Coverage Gate Enforcement / No Coverage Bypass

- Status: SUPERSEDED_BY_FINAL_LOCK.
- Local evidence received after Recovery-3: static guard, Coverage, Publication, readable, Evidence, Replay, and Command suites passed; remaining failures were isolated to correction fallback/effective-date and low-coverage fallback preservation.
- Recovery-4 fix: `seedReadableFallbackPublication()` now mirrors `eod_runs.publication_id` to the seeded fallback publication id instead of hard-coding publication `1`, so strict pointer/publication/run mirror validation can resolve valid fallback baselines while still rejecting malformed fallback pointers.
- Recovery-4 fix: correction baseline pointer mismatch messages are classified as pointer-integrity failures, so failed correction promotion preserves prior current state and uses the contract-valid fallback date when one resolves.
- Final result: superseded by Recovery-5 final local validation; `MarketDataPipelineIntegrationTest`, pointer filter, and full `tests/Unit/MarketData` all PASS.

## Recovery-5 baseline pointer mismatch message preservation — Coverage Gate Enforcement / No Coverage Bypass

- Status: DONE / LOCKED by final local validation.
- Local evidence after Recovery-5: `MarketDataPipelineIntegrationTest`, pointer filter, targeted coverage/finalize/publication/readable/evidence/replay/command suites, core service tests, static guard, and full `tests/Unit/MarketData` all PASS.
- Recovery-5 fix: pointer-integrity handling now preserves the explicit `Correction baseline no longer matches current publication pointer` note instead of collapsing it to the generic post-finalize pointer mismatch message.
- Final lock completed for Coverage Gate Enforcement / No Coverage Bypass.

## Hash / Seal / Dataset Integrity — Recovery round 3

- Status: DONE / LOCKED by final local validation.
- Local evidence received before final recovery: `Artifact` and `Evidence` filters PASS; remaining failures isolated to replacement promote/finalize seal precondition because mandatory candidate hashes were incomplete.
- Recovery: replacement candidates now create candidate-bound bars history from current live bars when no candidate bars history exists, then compute/hash/seal against history scope without mutating sealed baseline live rows.
- Final validation: `vendor/bin/phpunit tests/Unit/MarketData --filter "Finalize"` PASS with `OK (46 tests, 355 assertions)`; `vendor/bin/phpunit tests/Unit/MarketData --filter "Integration"` PASS with `OK (91 tests, 1443 assertions)`; full `vendor/bin/phpunit tests/Unit/MarketData` PASS with `OK (329 tests, 4110 assertions)`.
- Final lock completed for Hash / Seal / Dataset Integrity.


---



<!-- LEGACY_EXTRACT_BODY_END -->
