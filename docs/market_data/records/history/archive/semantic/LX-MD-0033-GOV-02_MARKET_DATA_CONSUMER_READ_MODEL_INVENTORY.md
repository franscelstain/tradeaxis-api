# Legacy Semantic Extract — LX-MD-0033-GOV-02

- Source ID: `LS-MD-0033`
- Original path: `audit/MARKET_DATA_CONSUMER_READ_MODEL_INVENTORY.md`
- Original SHA1: `A63ADB11787063B5198FC2AB1A3E1DA244D95EC8`
- Extract role: `GOVERNANCE`
- Source range: `L34-L118`
- Extract body SHA1: `385FA14E53356FA5ADC2228C0B06E42AF16F050B`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## CONSUMER READ BOUNDARY

Market-data supplies official prices, indicators, benchmark context, publication proof metadata, and readiness status.

Downstream ownership boundary:
- watchlist owns ranking and candidate selection.
- portfolio owns holding valuation, allocation, and P/L.
- signal/strategy owns buy/sell decisions and recommendations.

The consumer read surface returns rows only after the official current readable publication pointer resolves.

## WATCHLIST READ MODEL CONTRACT

WATCHLIST_READ_SURFACE: PASS

Owner classes:
- `MarketDataWatchlistReadService`
- `MarketDataWatchlistReadRepository`

Contract:
- Input is an explicit requested `trade_date`.
- Data must be resolved through `EodPublicationRepository::resolveCurrentReadablePublicationForTradeDate($tradeDate)`.
- Publication must be current, SEALED, owned by a SUCCESS run, publishability READABLE, and coverage PASS.
- Rows are scoped by exact `trade_date + publication_id`.
- Returned rows include ticker identity, close/volume, liquidity and momentum indicators, benchmark-relative strength, indicator set version, source name, `publication_id`, `publication_version`, and `run_id`.
- No watchlist ranking or buy/sell output is produced.
- If no readable current publication exists, the result is blocked with an explicit reason code and empty rows.

## PORTFOLIO PRICE READ MODEL CONTRACT

PORTFOLIO_PRICE_SURFACE: PASS

Owner classes:
- `MarketDataPortfolioPriceService`
- `MarketDataPortfolioPriceRepository`

Contract:
- Input is an explicit requested `trade_date` and explicit ticker list.
- Official current-day prices are scoped by exact `trade_date + publication_id` from the resolved current readable publication.
- Returned rows include close, adjusted close, previous close when a previous readable publication exists, change amount, change percent, source name, `publication_id`, `publication_version`, and `run_id`.
- Missing requested tickers are returned in `missing_tickers`.
- No fallback to another requested date is allowed.
- Portfolio market value, unrealized P/L, allocation, and recommendation output are not computed in market-data.
- If previous close is unavailable, `previous_close_price` is null with a reason code.

## BENCHMARK READ MODEL CONTRACT

BENCHMARK_READ_SURFACE: PASS

Owner classes:
- `MarketBenchmarkReadService`
- `MarketBenchmarkReadRepository`

Contract:
- Benchmark context is readable only after market-data readiness for the requested date is proven.
- IHSG is read from `market_benchmarks`, `market_benchmark_bars`, and `market_benchmark_indicators`.
- IHSG is not treated as an equity ticker.
- Provider symbol `^JKSE` is preserved and is not suffixed as `^JKSE.JK`.
- Insufficient benchmark history returns nullable indicators and `IND_INSUFFICIENT_HISTORY`, never fabricated benchmark values.

## FRESHNESS / READINESS CONTRACT

READINESS_SURFACE: PASS

Owner class:
- `MarketDataReadinessService`

`is_ready=true` only when all conditions hold:
- current pointer resolves.
- publication is current.
- publication is SEALED.
- run terminal status is SUCCESS.
- publishability state is READABLE.
- coverage gate state is PASS.

Ready output uses `READABLE_PUBLICATION_RESOLVED` and `RESOLVED_READABLE_CURRENT`.

Blocked output is fail-closed with explicit reason codes, including:
- NO_READABLE_PUBLICATION
- PUBLICATION_NOT_SEALED
- RUN_TERMINAL_STATUS_NOT_SUCCESS
- RUN_PUBLISHABILITY_NOT_READABLE
- RUN_COVERAGE_GATE_NOT_PASS
- POINTER_NOT_RESOLVED / NOT_RESOLVED_READABLE_CURRENT semantics through the blocked payload.


<!-- LEGACY_EXTRACT_BODY_END -->
