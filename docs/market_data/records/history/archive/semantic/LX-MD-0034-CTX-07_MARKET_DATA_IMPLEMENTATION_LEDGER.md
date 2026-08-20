# Legacy Semantic Extract — LX-MD-0034-CTX-07

- Source ID: `LS-MD-0034`
- Original path: `audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md`
- Original SHA1: `53F8C0BDBA19E2AB4F630A31731FDB2210E19CEF`
- Extract role: `CONTEXT`
- Source range: `L1626-L1640`
- Extract body SHA1: `1F7EC9EE162FFBE0F50E96DD61BF67AC8199EAC5`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Ledger update transaction (LOCKED)

One command update must atomically keep these fields consistent:

1. active work order;
2. row status;
3. latest audit verdict;
4. assigned-document count;
5. evidence refs;
6. active findings;
7. implementation/operational claim;
8. exactly one next permitted command.

If an update would produce two active work orders, successor before predecessor, `PASS` without evidence, or a next command inconsistent with the protocol, the ledger update must be rejected.


<!-- LEGACY_EXTRACT_BODY_END -->
