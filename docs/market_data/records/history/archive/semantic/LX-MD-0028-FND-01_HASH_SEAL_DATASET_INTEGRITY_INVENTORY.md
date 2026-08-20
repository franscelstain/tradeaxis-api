# Legacy Semantic Extract — LX-MD-0028-FND-01

- Source ID: `LS-MD-0028`
- Original path: `audit/HASH_SEAL_DATASET_INTEGRITY_INVENTORY.md`
- Original SHA1: `C8D94C9D62FC23B2978DB75D31E851645BBF5CCF`
- Extract role: `FINDING`
- Source range: `L81-L120`
- Extract body SHA1: `FC40539191E1E424915D8B406F0FEA24DD02E166`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Recovery from operator-local PHPUnit failures — 2026-05-07

Local failures showed three implementation/fixture-contract mismatches: source/API timeout default had drifted to `15` while source/provider tests expect `20`; direct repository candidate sealing updated publication hashes but not the owning run mirror; and replacement candidates for a date that already has a sealed/current publication wrote derived artifacts/hash against live tables instead of candidate history, triggering `SEALED_DATASET_MUTATION_BLOCKED` before finalize could produce the expected held/force-replace behavior.

Recovery patch:

- restored `MARKET_DATA_SOURCE_API_TIMEOUT_SECONDS` and config fallback to `20`;
- mirrored `updateCandidateHashes()` into `eod_runs` so repository-level seal/promote tests use consistent run/publication hash context;
- moved current-pointer/operator replacement validation before final hash equality checks so existing current-pointer integrity errors are not masked by `FINALIZE_HASH_MISSING`;
- routed indicators, eligibility, and hash computation for superseding/replacement candidates through history artifacts;
- promoted history artifacts into live current tables only after pointer promotion is allowed, preserving sealed baseline immutability for non-force replacement attempts.

Historical transition marker `ENFORCED_PENDING_LOCAL_PHPUNIT` was active at this recovery point. It is closed by later targeted/full `tests/Unit/MarketData` proof recorded in Lumen and the production-ready proof pack.

## Recovery from operator-local PHPUnit failures — 2026-05-07 / round 2

Local retest after the first recovery showed these remaining failures: `Seal`, `Integrity`, `Pointer`, and `Publication` passed, while `Artifact`/`Evidence` still observed `timeout_seconds=15` instead of the source/provider contract baseline `20`; `Finalize`/`Integration` still hit `SEALED_DATASET_MUTATION_BLOCKED` during indicator recomputation for replacement candidates.

Recovery patch:

- test SQLite bootstrap now sets `market_data.source.api.timeout_seconds=20` to prevent local `.env` or prior test config drift from leaking into source/provider integration assertions;
- replacement candidate publications with `publication_version > 1` now route indicator computation, eligibility build, and hash generation through history artifacts from the beginning of the candidate lifecycle;
- sealed/current/readable baseline immutability remains enforced because replacement candidates no longer delete/reinsert live indicator/eligibility rows before finalize decides whether force replacement is allowed.

Historical transition marker `ENFORCED_PENDING_LOCAL_PHPUNIT` was still active at this recovery point. It is closed by later targeted/full `tests/Unit/MarketData` proof recorded in Lumen and the production-ready proof pack.

## Recovery from operator-local PHPUnit failures — 2026-05-07 / round 3

Local retest after round 2 showed `Artifact` and `Evidence` passed and timeout drift was resolved, leaving only replacement promote/finalize cases blocked by `Cannot seal dataset before all mandatory hashes exist`. The historical remaining gap at that moment was that replacement candidates created from an already completed/current seed run had no candidate-bound `eod_bars_history` rows before indicator/hash/seal stages; the later recovery round closed this by materializing candidate-bound bars history.

Recovery patch:

- replacement candidate indicator computation now ensures candidate-bound bars history exists before loading the indicator window;
- when no candidate bars history exists, current live bars for the trade date are copied into the candidate publication history using the replacement run id/publication id, preserving baseline immutability while giving the candidate its own hashable artifact scope;
- hash generation also ensures candidate bars history before hashing replacement candidates;
- live sealed/current/readable artifact mutation remains blocked because the recovery writes candidate history only and does not delete/reinsert live rows before finalize authorizes pointer promotion.

Historical transition marker `ENFORCED_PENDING_LOCAL_PHPUNIT` was still active at this recovery point. It is closed by later `Finalize`, `Integration`, and full `tests/Unit/MarketData` proof recorded in Lumen and the production-ready proof pack.



<!-- LEGACY_EXTRACT_BODY_END -->
