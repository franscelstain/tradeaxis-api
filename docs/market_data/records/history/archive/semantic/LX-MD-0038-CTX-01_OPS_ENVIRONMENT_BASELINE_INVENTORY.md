# Legacy Semantic Extract — LX-MD-0038-CTX-01

- Source ID: `LS-MD-0038`
- Original path: `audit/OPS_ENVIRONMENT_BASELINE_INVENTORY.md`
- Original SHA1: `C4C8EC75AF93028E3F6AEFEF6E52E82B376969D5`
- Extract role: `CONTEXT`
- Source range: `L37-L51`
- Extract body SHA1: `B98B3839FB696E2930B0C6DFFD86FBFAD79099D8`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Runtime environment baseline matrix

| Item | Source | Current Value | Expected / Supported | Status |
|---|---|---|---|---|
| PHP version constraint | `composer.json` | `^7.3|^8.0` | Runtime evidence baseline: PHP `>= 7.3` and `< 8.4`; preferred operator/CI PHP 8.3.x | DOCUMENTED_AND_GUARDED |
| Composer platform PHP | `composer.json` / `composer.lock` | No `config.platform.php`; lock platform is `^7.3|^8.0` | `DEFER_WITH_REASON`; do not add platform without Composer lock regeneration | DEFER_WITH_REASON |
| Lumen version | `composer.lock` | `laravel/lumen-framework v8.3.4` | Compatible only with clean-output PHP below 8.4 for this dependency set | DOCUMENTED |
| PHPUnit version | `composer.json` / prior operator proof | `^9.5.10`; prior local proof used PHPUnit 9.6.34 | PHPUnit 9.6.x with required extensions | DOCUMENTED |
| Required PHP extensions | PHPUnit/container observation | Container missing `dom`, `mbstring`, `xml`, `xmlwriter` | `dom`, `json`, `libxml`, `mbstring`, `pdo_mysql`, `pdo_sqlite`, `tokenizer`, `xml`, `xmlreader`, `xmlwriter` | DOCUMENTED |
| Timezone | `.env.example`, `.env.testing`, config | `Asia/Jakarta` | `Asia/Jakarta` | OK |
| CI PHP version | CI workflow scan | No CI workflow found | Must use supported clean-output baseline if CI is added | DOCUMENTED_MANUAL |
| Local operator PHP version | User proof from current session | PHP 7.4.33 | `>= 7.3` and `< 8.4` | PASS_SUPPORTED_RUNTIME |
| Artisan clean output | Container PHP 8.4.16 + operator-local PHP 7.4.33 | Container before guard: noisy vendor deprecations; container after guard: clean unsupported-environment block; operator-local: clean command/help output | Clean output on supported PHP; clean fail-closed on unsupported PHP | OPERATOR_LOCAL_CLEAN_OUTPUT_CONFIRMED |
| PHPUnit clean output | Operator-local PHP 7.4.33 | Targeted OpsEnvironment/Evidence/Replay/Command filters cleanly passed; final StaticGuard and full MarketData suite passed after guard synchronization | Clean output on supported PHP with extensions | OPERATOR_LOCAL_FULL_MARKET_DATA_SUITE_PASS |


<!-- LEGACY_EXTRACT_BODY_END -->
