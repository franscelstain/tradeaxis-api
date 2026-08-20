# Legacy Semantic Extract — LX-MD-0045-CTX-01

- Source ID: `LS-MD-0045`
- Original path: `audit/reports/ORDERS_01_04_IMPLEMENTATION_EVIDENCE_2026-08-03.md`
- Original SHA1: `9EC579890FB2FB4256FE012CF3BD1CF68D55E90E`
- Extract role: `CONTEXT`
- Source range: `L65-L73`
- Extract body SHA1: `DB72F779546747EED937DB1DE98A3CE2C6DEF3B6`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Fixed obligations carried by orders 1-4

| Obligation | Evidence |
|---|---|
| Config keys registered | `config/market_data.php`, `.env.example`, and `.env.testing` are synchronized by executable governance test. |
| Schema targets declared | additive migration `2026_08_03_000001_harden_market_data_orders_1_to_4.php` plus the SQLite production-shape mirror. |
| Rejected tests retired in the same implementation | price-derived corporate-action synthesis and in-place price/history repair tests were superseded by non-mutation/fail-closed proofs. |
| Capability limits explicit | provider, corporate-action detector, in-place repair compatibility surface, calendar, identity, and status paths expose or enforce their blind spots/fail-safe states. |


<!-- LEGACY_EXTRACT_BODY_END -->
