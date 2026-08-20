# Legacy Semantic Extract — LX-MD-0038-GOV-01

- Source ID: `LS-MD-0038`
- Original path: `audit/OPS_ENVIRONMENT_BASELINE_INVENTORY.md`
- Original SHA1: `C4C8EC75AF93028E3F6AEFEF6E52E82B376969D5`
- Extract role: `GOVERNANCE`
- Source range: `L18-L36`
- Extract body SHA1: `ADB0BE2AD446D65B285DE953898DC05656F664E7`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Scope

This inventory records the environment/operator/CI hardening needed so `market-data:*` command output can be trusted as runtime evidence.

This scope does not change market-data domain behavior, pipeline semantics, publication rules, coverage rules, read-side pointer rules, replay semantics, or evidence payload contracts.

## Existing contract / test / doc matrix

| Existing Contract / Test / Doc | Role | Current Status | Relevance to Ops Environment Baseline | Reuse / Extend / Do Not Touch |
|---|---|---|---|---|
| `AUDIT_UPDATE_GOVERNANCE.md` | Audit update rule owner | ACTIVE | Already requires runtime environment baseline for DONE/LOCKED claims. | EXTEND_BY_REFERENCE |
| `LUMEN_IMPLEMENTATION_STATUS.md` | Implementation status source | ACTIVE | Must record current environment status and avoid DONE without clean runtime proof. | EXTEND |
| `LUMEN_CONTRACT_TRACKER.md` | Contract status source | ACTIVE | Must record environment baseline contract and final rule. | EXTEND |
| `docs/market_data/ops/OPERATIONAL_RUNBOOK.md` | Operator runbook surface | ACTIVE | Must point operators to clean-output baseline before command evidence. | EXTEND |
| `docs/market_data/ops/OPS_ENVIRONMENT_BASELINE.md` | New baseline doc | ADDED | Locks version, extension, command-output, and manual validation expectations. | ADD |
| `tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php` | Static guard | ADDED | Prevents missing baseline docs, PHP 8.4 bypass, and noisy evidence policy regression. | ADD |
| `artisan` | Command entrypoint | PATCHED | Blocks unsupported PHP before vendor autoload to avoid PHP 8.4 deprecation noise. | EXTEND |
| `phpunit.xml` + `tests/bootstrap.php` | PHPUnit proof bootstrap | PATCHED | Ensures test proof uses the same unsupported PHP guard before project autoload. | EXTEND |


<!-- LEGACY_EXTRACT_BODY_END -->
