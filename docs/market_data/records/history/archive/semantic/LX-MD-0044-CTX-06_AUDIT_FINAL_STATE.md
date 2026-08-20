# Legacy Semantic Extract — LX-MD-0044-CTX-06

- Source ID: `LS-MD-0044`
- Original path: `audit/reports/AUDIT_FINAL_STATE.md`
- Original SHA1: `4F88A2D91FB7215D7D64C2F3ACFED4764964CB20`
- Extract role: `CONTEXT`
- Source range: `L1137-L1185`
- Extract body SHA1: `B798D6BA8376D551D1FF7CBE9C41E0952EB4FDCC`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Minimum versioned market-data read product (initial consumer profile)

Consumer-facing publication minimal perlu menyediakan:

Naming/ownership rule: schema yang dimiliki package ini harus bernama strategy-neutral, misalnya `Market_Data_Read_Product_Schema_V1_LOCKED.md`. Dokumen bernama `Weekly_Swing_Read_DTO_Schema_V1_LOCKED.md` tidak boleh menjadi owner di `docs/market_data`; DTO policy semacam itu harus berada pada domain watchlist dan hanya mengonsumsi read product market-data.

### Identity and publication

- `instrument_id` atau stable identity equivalent;
- ticker/listing identity as-of trade date;
- requested dan effective trade date;
- run/publication/version identity;
- freshness/readability state.

### Market observations

- raw open, high, low, close, volume;
- traded value dan trade count bila source menyediakan;
- board/trading status bila tersedia;
- source provider dan observed/ingested timestamp;
- data-quality state.

### Analytical price product

- price-basis identity;
- coherent structural-adjusted OHLC/volume;
- adjustment/factor version;
- contamination or unresolved-event flag.

### Indicators

- indicator-set version;
- ATR and registered indicators required by the declared read-product version;
- nullability/warm-up state;
- market benchmark context yang dideklarasikan sebagai factual field pada read-product version.

### Eligibility facts

- coverage state;
- quality state;
- liquidity measures and proxy labels;
- suspension/trading-status state;
- event-risk state;
- eligibility boolean and reason codes.

Market-data tidak boleh mengeluarkan buy/sell signal, ranking alpha, entry price, stop loss, target, position sizing, ataupun threshold tradability. Consumer profile hanya memengaruhi factual field contract, bukan market-data readiness criteria.

---


<!-- LEGACY_EXTRACT_BODY_END -->
