# Legacy Semantic Extract — LX-MD-0040-CTX-01

- Source ID: `LS-MD-0040`
- Original path: `audit/READ_SIDE_CONSUMER_SURFACE_FINAL_SWEEP_INVENTORY.md`
- Original SHA1: `89C4B1A3A221C2A0F9B52AA735566CF5C0F8B107`
- Extract role: `CONTEXT`
- Source range: `L13-L34`
- Extract body SHA1: `759C7B4B8E1D579056D5ED27EBFFA71498904B20`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Pre-check summary

| Check | Result | Notes |
|---|---|---|
| ZIP extraction | PASS | Project structure was present: `artisan`, `composer.json`, `routes`, `app/Application/MarketData`, `app/Infrastructure/Persistence/MarketData`, `tests/Unit/MarketData`, `docs/market_data/audit`, and locked read-side docs. |
| Governance files read | PASS | `AUDIT_UPDATE_GOVERNANCE.md`, `LUMEN_IMPLEMENTATION_STATUS.md`, and `LUMEN_CONTRACT_TRACKER.md` were reviewed before docs/test updates. |
| Existing contract identified | PASS | `READ_SIDE_POINTER_ENFORCEMENT_CONTRACT` and `Read_Side_Enforcement_Anti_Bypass_Contract_LOCKED.md` are the existing owner. |
| Vendor availability | CONTAINER_BLOCKED_LOCAL_AVAILABLE | `vendor/` is present. Container PHPUnit is blocked because PHP extensions `dom`, `mbstring`, `xml`, and `xmlwriter` are unavailable, but operator-local PHPUnit proof is supplied and passed. |
| Runtime proof | DONE_LOCAL_PHPUNIT_PASS | Operator-local ReadSide/Readable/Pointer/Publication/Consumer/CommandSurface/Replay/Evidence/StaticGuard, direct final-sweep guard, and full MarketData suite all passed. Evidence and StaticGuard initially failed only on the Production Validation audit-phrase compatibility marker, then passed after the audit wording patch. |

## Runtime Environment Baseline

This environment block is intentionally duplicated in the always-read audit materials so future sessions can distinguish operator-local runtime proof from container-only static proof.

| Environment Field | Value | Status |
|---|---|---|
| Operator-local PHP version | PHP 7.4.33 | `RUNTIME_AUTHORITY` |
| Operator-local PHPUnit version | PHPUnit 9.6.34 | `RUNTIME_AUTHORITY` |
| Required PHP extensions available locally | dom, mbstring, pdo_mysql, pdo_sqlite, xml, xmlreader, xmlwriter | `PASS_LOCAL` |
| Container PHPUnit status | Missing dom, mbstring, xml, xmlwriter | `BLOCKED_CONTAINER_RUNTIME_ENV` |
| Runtime authority for DONE/LOCKED | Operator-local PHPUnit output | `LOCKED_LOCAL_PHPUNIT_PASS` |


<!-- LEGACY_EXTRACT_BODY_END -->
