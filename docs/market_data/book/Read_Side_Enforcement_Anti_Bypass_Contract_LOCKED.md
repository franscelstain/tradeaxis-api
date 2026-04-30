# Read-Side Enforcement Anti-Bypass Contract LOCKED

Status: LOCKED  
Scope: market-data read-side consumers only

## A. Authority Rule

This contract locks the market-data read-side consumer contract. Every consumer read path must resolve data through the current readable publication pointer. This contract does not change coverage gate behavior, correction lifecycle, force replace behavior, finalize lock behavior, publication replacement policy, manual-file publishability, or DB schema sync policy.

## B. Consumer Read Path Definition

Consumer read path includes every path that returns, exports, verifies, displays, or reports readable market-data outside the write pipeline:

- API responses and future controllers/resources.
- Dashboard/read services.
- Public repository methods used by read-only consumers.
- Commands that display current/readable market-data state.
- Evidence export.
- Replay verification and replay fixture comparison.
- Report/read-only tooling.

A path remains a consumer read path even when it is executed from CLI, tests, evidence tooling, or replay tooling.

## C. Pointer-Only Rule

A consumer read path must resolve readable data through the official pointer gateway:

- `eod_current_publication_pointer`
- join to `eod_publications`
- validation that `pub.is_current = 1`
- validation that `pub.seal_state = SEALED`
- validation that pointer/publication/run identifiers match
- validation that run mirror fields match the pointer (`run.publication_id`, `run.publication_version`)
- validation that `run.terminal_status = SUCCESS`
- validation that `run.publishability_state = READABLE`
- validation that `run.coverage_gate_state = PASS`
- validation that `run.is_current_publication = 1`
- validation that trade date/effective date matches the locked publication contract

The official repository gateway is:

- `EodPublicationRepository::resolveCurrentReadablePublicationForTradeDate($tradeDate)`

Compatibility wrappers may call the gateway, but they must not duplicate or weaken the pointer/readability predicate.

## D. Forbidden Bypass Rule

Consumer read paths must not:

- read raw/staging tables directly
- read `eod_bars`, `eod_eligibility`, or `eod_indicators` without resolving the current readable publication pointer
- use `MAX(date)`, `MAX(trade_date)`, `MAX(publication_id)`, or equivalent latest/current shortcut
- use `is_current` without the pointer row
- use `terminal_status = SUCCESS` without `publishability_state = READABLE`
- use `publishability_state = READABLE` without current pointer validation
- fallback to old publications without a current readable pointer
- fallback to raw/staging/latest-date data when pointer resolution fails
- normalize a bypass through test fixtures

## E. Allowed Internal Access Rule

Direct raw/artifact/staging access is allowed only for non-consumer paths:

- ingestion/write pipeline
- indicator computation
- eligibility computation
- seal/finalize/publish process
- dataset hash/seal internals
- admin repair command
- audit diagnostic command
- test setup/assertion fixtures
- `EodPublicationRepository::findLatestReadablePublicationBefore($tradeDate)` is an internal finalize/source-failure fallback resolver, not the official consumer gateway. It may resolve prior readable pointer rows for pipeline hold/degraded-mode decisions and must not be used by API/evidence/replay/consumer output as a latest shortcut.

These paths must be named and scoped as write/admin/test behavior. They must not be used by API/public read/evidence/replay consumer outputs unless the query is pointer-resolved and readable-current validated.

## F. Fail-Safe Rule

If no current readable publication exists:

- API/read services must return a controlled empty/error result according to their response contract.
- CLI read commands must display `NOT_READABLE` or `NO_CURRENT_READABLE_PUBLICATION`.
- Evidence export and replay verification must fail-safe with a reason code or empty controlled result.
- No consumer path may fallback to raw/staging/latest-date rows.

If a pointer exists but resolves to a non-readable, non-success, unsealed, stale, or invalid publication, the behavior is the same fail-safe behavior.

## G. Repository Gateway Rule

All consumer paths must use a repository gateway that resolves the current readable publication first. The gateway must return `null`/controlled empty output if the pointer is absent or invalid. It must not silently search older publications.

Current gateway:

- `EodPublicationRepository::resolveCurrentReadablePublicationForTradeDate($tradeDate)`

Current pointer-resolved read repositories:

- `EligibilitySnapshotScopeRepository::getScopeForTradeDate($tradeDate)`
- `EodEvidenceRepository::exportEligibilityRows($tradeDate, $publicationId = null)`
- `EodEvidenceRepository::dominantReasonCodes($runId, $tradeDate, $publicationId = null)`

Pointer-resolved read repositories must enforce `coverage_gate_state = PASS` and the run mirror match before returning rows, counts, or reason-code output.

## H. Static Enforcement Rule

A static anti-bypass test must guard consumer/read paths against:

- `MAX(trade_date)` / `max(trade_date)`
- `MAX(publication_id)` / `max(publication_id)`
- direct raw/staging read from consumer paths
- direct `eod_bars`, `eod_eligibility`, or `eod_indicators` read without pointer validation
- `is_current` shortcut without `eod_current_publication_pointer`
- unsafe latest/current repository methods exposed for consumer use

Grep/static findings are not automatically failures when they are write/admin/test setup paths. They must be classified as:

- `ALLOWED_WRITE_PATH`
- `ALLOWED_ADMIN_REPAIR`
- `ALLOWED_TEST_SETUP`
- `FORBIDDEN_CONSUMER_BYPASS`

No `FORBIDDEN_CONSUMER_BYPASS` may remain.

## I. Audit Rule

Every future read-side enforcement change must append a new audit session to:

- `docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md`
- `docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md`

Audit updates are append-only and must follow `AUDIT_UPDATE_GOVERNANCE.md`.

## J. Validation Evidence

Locked validation for this source-of-truth ZIP:

- `php artisan migrate:fresh --env=testing` → PASS.
- `vendor/bin/phpunit tests/Unit/MarketData --filter "readable"` → PASS; `OK (45 tests, 256 assertions)`.
- `vendor/bin/phpunit tests/Unit/MarketData --filter "pointer"` → PASS; `OK (51 tests, 551 assertions)`.
- `vendor/bin/phpunit tests/Unit/MarketData` → PASS; `OK (250 tests, 2355 assertions)`.
- `vendor/bin/phpunit tests/Unit/MarketData/ReadablePublicationReadContractIntegrationTest.php` → PASS; `OK (8 tests, 15 assertions)`.
- `vendor/bin/phpunit tests/Unit/MarketData/PublicationCurrentPointerReadinessStaticGuardTest.php` → PASS; `OK (3 tests, 23 assertions)`.

Regression reconciliation:

- Consumer gateway, eligibility scope, and evidence reads remain mirror-enforced.
- `EodPublicationRepository::findLatestReadablePublicationBefore($tradeDate)` remains internal-only fallback behavior for pipeline hold/degraded-mode/correction preservation and must not be used as a consumer latest shortcut.

