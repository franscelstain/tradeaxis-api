# Legacy Semantic Extract — LX-MD-0004-EVD-01

- Source ID: `LS-MD-0004`
- Original path: `audit/AUDIT_DOCS_SYNCHRONIZATION_POST_SESSION_1_8_INVENTORY.md`
- Original SHA1: `1EB4399E5C2239980FD50CC73AF543D8125FA55A`
- Extract role: `EVIDENCE`
- Source range: `L93-L121`
- Extract body SHA1: `9293F8815E9AF49935FE928C3EEDF378C90BBEF1`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Proof matrix

| Proof | Source | Result | Status |
|---|---|---|---|
| Historical Fail-Safe proof | Fail-Safe entry | full `vendor/bin/phpunit tests/Unit/MarketData` OK (349 tests, 4558 assertions) | CARRIED_HISTORY |
| Historical Audit Docs Synchronization proof | 2026-05-08 audit-docs entry/inventory | `AuditDocsSynchronizationStaticGuardTest.php` OK (9 tests, 153 assertions); `AuditDocs` OK (9 tests, 153 assertions); `StaticGuard` OK (93 tests, 2160 assertions); full MarketData OK (358 tests, 4711 assertions) | CARRIED_HISTORY |
| Historical Operational Readiness proof | Operational Readiness entry | full `vendor/bin/phpunit tests/Unit/MarketData` OK (368 tests, 4927 assertions) | CARRIED_HISTORY |
| Latest Ops Environment proof | Ops Environment entry/inventory | `StaticGuard` OK (164 tests, 3702 assertions); full MarketData OK (435 tests, 6299 assertions) | CARRIED_HISTORY |
| Current container artisan | Current container | `php artisan list` -> `ENV_UNSUPPORTED_PHP_VERSION` | EXPECTED_FAIL_CLOSED |
| Current container PHPUnit | Current container | Missing `dom`, `mbstring`, `xml`, `xmlwriter` | BLOCKED_CONTAINER_RUNTIME_ENV |
| First post-session operator-local rerun | Operator-local PHP 7.4.33 environment | `php artisan list` clean; direct AuditDocs guard OK (9 tests, 261 assertions); `AuditDocs` filter OK (9 tests, 261 assertions); `StaticGuard` failed 1 assertion in stale OpsEnvironment guard scoping | PARTIAL_LOCAL_PROOF_STATICGUARD_FAILED |
| Current post-guard-scope patch operator-local PHPUnit | Operator-local final rerun | `StaticGuard` OK (164 tests, 3721 assertions); full MarketData OK (435 tests, 6318 assertions) | PASS_LOCKED |

All carried historical test counts above are not a new container PHPUnit run.

---

## Runtime proof matrix

| Runtime Surface | Current Container Result | Evidence Meaning | Status |
|---|---|---|---|
| PHP runtime | PHP 8.4.16 | Unsupported for clean Lumen 8.3.4 evidence output | BLOCKED_CONTAINER_RUNTIME_ENV |
| Artisan | Clean fail-closed `ENV_UNSUPPORTED_PHP_VERSION` before vendor autoload | Expected environment gate behavior; not runtime pass | EXPECTED_FAIL_CLOSED |
| PHPUnit | Cannot start because `dom`, `mbstring`, `xml`, `xmlwriter` are missing | No current container test proof | BLOCKED_CONTAINER_RUNTIME_ENV |
| Operator-local | Final post-guard-scope proof supplied on supported local runtime | Required proof recorded | PASS |
| First post-session local rerun | `php artisan list` and AuditDocs tests passed, StaticGuard failed one stale guard-scope assertion | Useful partial proof, not enough for DONE/LOCKED | PARTIAL_LOCAL_PROOF_STATICGUARD_FAILED |

---


<!-- LEGACY_EXTRACT_BODY_END -->
