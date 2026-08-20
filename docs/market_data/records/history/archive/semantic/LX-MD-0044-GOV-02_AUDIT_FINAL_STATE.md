# Legacy Semantic Extract — LX-MD-0044-GOV-02

- Source ID: `LS-MD-0044`
- Original path: `audit/reports/AUDIT_FINAL_STATE.md`
- Original SHA1: `4F88A2D91FB7215D7D64C2F3ACFED4764964CB20`
- Extract role: `GOVERNANCE`
- Source range: `L138-L172`
- Extract body SHA1: `12A84EB8B348CA0F5344FD9010B8E169D0B7FCB3`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Scope boundary

### In scope sekarang

- saham IDX;
- EOD Regular Market sebagai market scope utama;
- raw OHLCV dan field EOD relevan;
- ticker, instrument, listing, dan symbol lifecycle;
- market calendar dan point-in-time trading status;
- corporate action yang memengaruhi price continuity atau event risk;
- adjusted analytical series yang konsisten;
- versioned indicator profile yang dinyatakan sebagai bagian data product;
- coverage, quality, liquidity, event-risk, dan data-usability facts;
- publication, correction, replay, provenance, scheduling, dan operational evidence;
- bootstrap provider `yahoo_finance`.

### Explicitly deferred

- tick-by-tick data;
- full order book atau market depth;
- ultra-low-latency pipeline;
- execution routing;
- cash dan negotiated market analytics penuh;
- multi-exchange platform;
- ISO 20022 lifecycle penuh;
- full LEI, ISIN, dan MIC infrastructure;
- seluruh voluntary corporate-action automation;
- multi-provider majority voting;
- dual-feed production;
- pembelian atau integrasi vendor berbayar pada fase sekarang.

Deferred scope boleh dipertimbangkan kembali hanya bila kebutuhan nyata muncul. Ia tidak boleh mengalihkan perbaikan P0 dan P1 yang langsung menentukan kebenaran dan kestabilan data product EOD.

---


<!-- LEGACY_EXTRACT_BODY_END -->
