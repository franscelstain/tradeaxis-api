# Decision — project owner selects F-011 Option 2: the deployed-corpus oracle is deployed-environment validation, transferred to MD-B22

- ID: `D-MD-B18-A002-022`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Decides: `F-MD-B18-A002-011` part (b) (Option 2)
- Applies to: `MD-DEP-0015`, `MD-DEP-0016`, new `MD-DEP-0024`
- Decisions relied on: `D-MD-B18-A002-004` (Option 1), `D-MD-B18-A002-020`, `D-MD-B18-A002-021`, `D-MD-B18-A002-003`
- Evidence relied on: `E-MD-B18-A002-120` (byte-copy verified), `E-MD-B18-A002-121` (normal startup failed), `E-MD-B18-A002-001`, `E-MD-B18-A002-117`
- Change impact: `CI-MD-B18-A002-001` (successor amendment issued with this decision)
- Issued: 2026-10-08T04:01:26+07:00
- Status: `APPROVED` — project owner, in the 2026-10-08 instruction "CONTINUE MD-B18-A002 — RECORD D022 AND COMPLETE REMAINING CLOSURE CONTROLS"
- Mutability: `IMMUTABLE_AFTER_ISSUE`
- Strategy impact: `NONE`; `MD-STRATEGY-FREEZE-20261005-001` unchanged

## Owner decision (as supplied)

Project owner accepts F-MD-B18-A002-011 Option 2 and approves D-MD-B18-A002-022.

Normative semantics:

1. Option 1 recovery under D-004 was attempted only to its authorized boundary: E-MD-B18-A002-120: byte-copy verified; E-MD-B18-A002-121: normal MariaDB startup failed.
2. innodb_force_recovery is NOT authorized and is NOT required for B18 closure.
3. ProductionCorpusInvariantOracleTest remains unchanged: no test edit; no skip; no weakened assertion.
4. Its seven controls are deployed-environment/corpus validation, not B18 semantic/predicate closure acceptance.
5. Historical suite evidence including E-MD-B18-A002-001 and E-117 remains exactly truthful and must never be described as full-suite PASS.
6. For B18 closure acceptance: MarketData suite executes with 0 errors; 0 skips; every test outside ProductionCorpusInvariantOracleTest must PASS; the seven oracle failures remain recorded as real failures.
7. The seven oracle obligations are transferred to a new governed dependency owned by MD-B22.
8. MD-DEP-0015 may resolve after fresh execution proves item 6.
9. MD-DEP-0016 remains OPEN_NON_BLOCKING as historical old-data recovery. Any future innodb_force_recovery requires a new owner decision.
10. F-MD-B18-A002-011(b) becomes: OPEN — GOVERNED_DEFERRAL_TO_MD-B22.
11. The MD-B22 dependency does NOT open MD-B22 now.
12. B18 may proceed with remaining closure controls: F-005 mutation proof; F-017 / MD-DEP-0017 reconciliation; closure manifest; MD-DEP-0009 resolution; Stage Register completion.

## Boundary

* This decision does not edit, re-describe or supersede any issued record: `D-MD-B18-A002-004`, `D-MD-B18-A002-020`, `D-MD-B18-A002-021`, `E-MD-B18-A002-001`, `E-MD-B18-A002-117`, `E-MD-B18-A002-120` and `E-MD-B18-A002-121` stay as issued. It does not make any suite result a "PASS": a run with the seven oracle failures is a factual failure of the full suite that satisfies B18 closure acceptance only under item 6.
* It does not resolve `MD-DEP-0015` (item 8 needs a fresh execution), does not close B18, and changes no candidate, build, test, application file or matrix row.
* The old data directory `data_260914` and the byte-copy `D:\tradeaxis_recovery\data_260914_copy\` are retained untouched; no recovery MariaDB is running.
