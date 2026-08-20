# Legacy Semantic Extract — LX-MD-0043-IMP-03

- Source ID: `LS-MD-0043`
- Original path: `audit/REPLAY_HISTORICAL_DETERMINISM_HARDENING_INVENTORY.md`
- Original SHA1: `6831E28FEFD55DC99E3BEA0B303AC2A439016C86`
- Extract role: `IMPLEMENTATION`
- Source range: `L109-L119`
- Extract body SHA1: `FCE401200A29D9F0D2F234EF56E5834BFD34A84C`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Local Feedback Follow-up Patch - 2026-05-17

| Finding | Source | Patch | Status |
|---|---|---|---|
| `ReplayHistoricalDeterminismHardeningStaticGuardTest::test_historical_replay_does_not_weaken_consumer_current_pointer_resolver` asserted the service-call token against `EodPublicationRepository.php` instead of checking the repository method signature. | Operator-local PHPUnit feedback | Guard now checks `public function findReadableCurrentPublicationForRun($runId, $tradeDate)` in the repository and keeps the service-call assertion inside `ReplayVerificationService.php`. | PATCHED |
| `AuditDocsSynchronizationStaticGuardTest::test_reason_code_registry_and_seed_are_synchronized` still expected 315 reason codes after replay historical hardening added 9 synchronized codes. | Operator-local PHPUnit feedback | Expected synchronized reason-code count updated to 324. Registry and seed sets remain identical. | PATCHED |

[POST_PATCH_STATIC_VALIDATION]
- `php -l tests/Unit/MarketData/ReplayHistoricalDeterminismHardeningStaticGuardTest.php` -> No syntax errors detected.
- `php -l tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` -> No syntax errors detected.
- Full operator-local PHPUnit rerun was later completed; this line is retained as post-patch history, not active work.

<!-- LEGACY_EXTRACT_BODY_END -->
