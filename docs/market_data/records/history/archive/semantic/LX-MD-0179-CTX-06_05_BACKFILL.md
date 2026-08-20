# Legacy Semantic Extract — LX-MD-0179-CTX-06

- Source ID: `LS-MD-0179`
- Original path: `ops/commands/05_BACKFILL.md`
- Original SHA1: `7D024D1A49999C8FD30899BF32AC78581D6AE221`
- Extract role: `CONTEXT`
- Source range: `L289-L300`
- Extract body SHA1: `0A86ED371A15AB7B3205DA31E963DF600685A528`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Amendment 2026-06-05 - Market-calendar lifecycle warmup
`market-data:backfill:lifecycle` must treat source-acquisition warmup as a trading-day dependency.

Required behavior:
- read requested trading dates from `market_calendar`
- resolve warmup start through the configured trading-day window, not wall-clock days
- keep requested-date lifecycle boundaries per trading date
- block invalid or insufficient calendar dependency with an explicit reason instead of silently producing NULL rolling indicators

Operational implication:
- if MA50 or ROC20-based outputs are unexpectedly NULL while OHLC history exists, verify `market_calendar` coverage and lifecycle warmup-window resolution before changing indicator formulas
- sector rotation remains source-backed; `sector_roc20` for a date still requires sector-index bars and benchmark indicators for that same date

<!-- LEGACY_EXTRACT_BODY_END -->
