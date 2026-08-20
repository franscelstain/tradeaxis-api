# Legacy Semantic Extract — LX-MD-0037-CTX-01

- Source ID: `LS-MD-0037`
- Original path: `audit/OPS_COMMAND_SURFACE_RUNTIME_MATRIX_INVENTORY.md`
- Original SHA1: `D6E40A3FC4141C4D0798627BD21A5F34418206F8`
- Extract role: `CONTEXT`
- Source range: `L13-L24`
- Extract body SHA1: `0F8873CB8A67CD1D9007598E8745A3B2D90255A4`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Runtime Environment

- PHP CLI: PHP 7.4.33.
- PHPUnit: PHPUnit 9.6.34.
- Required extensions available: dom, mbstring, pdo_mysql, pdo_sqlite, xml, xmlreader, xmlwriter.
- `vendor/` and `.env.testing` are present in this source ZIP.
- Local artisan runtime DB: `tradeaxis` for this proof run; commands were invoked with `--env=testing` for parity with the existing matrix command convention.
- Migration status: available/applied for the market-data runtime database.
- Runtime proof artifact roots:
  - `storage/app/market-data/ops-command-surface-runtime-matrix/**` for the prior enforced matrix.
  - `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/**` for the lock matrix and command output artifacts.


<!-- LEGACY_EXTRACT_BODY_END -->
