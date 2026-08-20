# Legacy Semantic Extract — LX-MD-0034-GOV-02

- Source ID: `LS-MD-0034`
- Original path: `audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md`
- Original SHA1: `53F8C0BDBA19E2AB4F630A31731FDB2210E19CEF`
- Extract role: `GOVERNANCE`
- Source range: `L1641-L1650`
- Extract body SHA1: `D513922C65E55ACE68DD43F45C0563DF742C1FEE`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Pass and advance rule

- `MD-EXEC Wxx` may advance the row only to `IN_PROGRESS`, `IMPLEMENTED_NOT_PROVEN`, or `PROVEN`; it cannot independently create final `CONFORMANT`.
- `MD-RUN Wxx` menjalankan lifecycle implement/audit/remediate/re-audit untuk satu row sampai `PASS`, lalu berhenti dan memberikan successor command. Ia tidak boleh melompati predecessor atau mengurangi audit/evidence gate.
- `MD-AUDIT Wxx`/`MD-REAUDIT Wxx` may set `CONFORMANT` only with verdict `PASS` and admissible evidence.
- `PARTIAL`/`FAIL` keeps the same work order active and sets next command to `MD-REMEDIATE Wxx findings ...`.
- remediation sets next command to `MD-REAUDIT Wxx`.
- successor becomes permitted only after predecessor is `CONFORMANT`.
- `W22 PASS` updates final claim only to the level actually proven; pre-activation evidence cannot become `OPERATIONALLY_VALIDATED` by wording alone.


<!-- LEGACY_EXTRACT_BODY_END -->
