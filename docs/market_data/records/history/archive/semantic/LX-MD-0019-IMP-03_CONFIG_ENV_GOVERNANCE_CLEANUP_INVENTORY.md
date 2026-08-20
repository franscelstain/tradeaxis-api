# Legacy Semantic Extract — LX-MD-0019-IMP-03

- Source ID: `LS-MD-0019`
- Original path: `audit/CONFIG_ENV_GOVERNANCE_CLEANUP_INVENTORY.md`
- Original SHA1: `B522643CB68AFF2ECC9A8268A482C11CE2D61598`
- Extract role: `IMPLEMENTATION`
- Source range: `L124-L159`
- Extract body SHA1: `3B842CD2B8AEC0D7FAE61E958D88BBF5723E698C`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Pruning Matrix

| Key | Location | Problem | Runtime Caller Exists? | Decision | Patch |
|---|---|---|---:|---|---|
| `MARKET_DATA_TICKERS_ACTIVE_YES_VALUE` / `market_data.tickers.active_yes_value` | config/env/repository/tests | Misleading string `Yes` value against numeric schema | yes, but semantically wrong | RENAME_AND_UPDATE_CALLERS | Replaced with `MARKET_DATA_TICKERS_ACTIVE_VALUE=1` and `market_data.tickers.active_value` |
| `MARKET_DATA_MULTI_SOURCE_MODE` / `market_data.coverage_edge_cases.multi_source_mode` | config/book doc only | No runtime caller; could imply multi-source behavior exists | no | REMOVE | Removed from config; book doc marks it pruned |
| `MARKET_DATA_ALLOW_MIXED_SOURCES` / `market_data.coverage_edge_cases.allow_mixed_sources` | config/book doc only | No runtime caller; could imply row-level mixing can be enabled | no | REMOVE | Removed from config; book doc marks it pruned |
| `MARKET_DATA_COVERAGE_DELAY_WINDOW_MINUTES` | config/service | Used by delayed coverage behavior but missing from env templates | yes | KEEP_ACTIVE | Added to `.env.example` and `.env.testing` |
| `market_data.source.local_input_file` | command/runtime override | Runtime config key set by command/tests, intentionally not env-backed | yes | DEFER_WITH_REASON | Not added to env; remains command override to avoid persistent operator input file |

## Caller Trace Matrix

| Config Key | Caller File | Method / Command | Purpose | Required | Status |
|---|---|---|---|---:|---|
| `market_data.tickers.active_value` | `TickerMasterRepository.php` | `getUniverseForTradeDate()` | strict active ticker universe filter | yes | PATCHED |
| `market_data.tickers.table/id_column/code_column/active_column/listed_date_column/delisted_date_column` | `TickerMasterRepository.php` | `resolveTickerIdsByCodes()`, `getUniverseForTradeDate()` | ticker table/schema mapping | yes | ACTIVE_USED |
| `market_data.coverage_gate.*` | `CoverageGateEvaluator.php`, `MarketDataPipelineService.php`, command/test surfaces | coverage PASS/FAIL telemetry and publishability | yes | ACTIVE_USED |
| `market_data.coverage_edge_cases.delay_window_minutes` | `MarketDataPipelineService.php` | delayed data classification | yes | ACTIVE_USED |
| `market_data.source.*` | `LocalFileEodBarsAdapter.php`, commands, pipeline | local/manual source resolution | yes | ACTIVE_USED |
| `market_data.source.api.*` and `market_data.provider.*` | source adapters/pipeline telemetry | provider acquisition and retry telemetry | yes | ACTIVE_USED |
| `market_data.indicators.*` | `EodIndicatorsComputeService.php` | indicator computation | yes | ACTIVE_USED |
| `market_data.hash.*` | `DeterministicHashService.php`, pipeline | deterministic hash/seal metadata | yes | ACTIVE_USED |
| `market_data.platform.*` | services/commands/backfills | timezone, cutoff, lot size, price basis | yes | ACTIVE_USED |
| `market_data.evidence.*` | evidence/export surfaces | evidence output and invalid bars sample limit | yes | ACTIVE_USED |
| `market_data.session_snapshot.*` | session snapshot service/commands | snapshot retention/scope/tolerance | yes | ACTIVE_USED |

## Patch Matrix

| Gap | File | Change | Why Safe | Test Coverage | Status |
|---|---|---|---|---|---|
| ticker active config used `Yes` against numeric schema | `config/market_data.php`, `.env.example`, `.env.testing`, `TickerMasterRepository.php` | renamed to numeric `active_value`; repository uses strict `where(is_active, 1)` | Schema already numeric/boolean; stale string no longer accepted | static guard + repository test | PATCHED |
| stale multi-source env/config surface | `config/market_data.php`, `Coverage_Edge_Cases_Contract_LOCKED.md` | removed unused config keys and documented pruned state | No runtime caller existed; hard rule remains no source mixing | static guard | PATCHED |
| delay-window env missing from templates | `.env.example`, `.env.testing` | added `MARKET_DATA_COVERAGE_DELAY_WINDOW_MINUTES=60` | Caller already existed | static guard | PATCHED |
| generic ticker DB doc allowed ambiguous `Yes/No` | `docs/db/02_TICKERS_MASTER.md` | normalized to boolean/TINYINT `1/0` | Aligns with migration and locked SQL schema | static guard | PATCHED |
| audit docs missing active session | audit docs | active session and current working entries added | append-only; old history preserved | static guard | PATCHED |


<!-- LEGACY_EXTRACT_BODY_END -->
