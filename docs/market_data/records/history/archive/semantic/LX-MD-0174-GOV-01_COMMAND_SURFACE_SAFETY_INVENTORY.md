# Legacy Semantic Extract — LX-MD-0174-GOV-01

- Source ID: `LS-MD-0174`
- Original path: `ops/COMMAND_SURFACE_SAFETY_INVENTORY.md`
- Original SHA1: `4A1D5DF36286F6499A44A9A6E49E45976F3253D1`
- Extract role: `GOVERNANCE`
- Source range: `L112-L116`
- Extract body SHA1: `313CC41A5E0A3D2B7B0287083ACD3D274900558C`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Source/master read-only recompute boundary
`market-data:eod-indicators:recompute-current` is the implemented current-bars recompute command for this boundary. It does not perform source acquisition, bar ingest, source/master writes, or `eod_bars` writes. It creates a correction-current publication from existing current readable bars and recomputes publication-bound indicator and eligibility artifacts.

"Without updating sector/corporate-action/trading-status/master data" means no writes to source/master tables and no source import commands. It does not mean publication-bound `eod_indicators` context columns are frozen; a new publication may recalculate those fields from existing source rows. A context-freezing technical-only mode must be explicit and separately proven.


<!-- LEGACY_EXTRACT_BODY_END -->
