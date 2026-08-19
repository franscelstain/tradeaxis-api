# Shared Technical Support — Weekly Swing Current Scope

> **Doc Role:** IMPLEMENTATION SUPPORT INDEX
> **Business-rule owner:** NO

Folder ini berisi technical contracts yang dipakai untuk mengurangi duplikasi implementation concern. **Current product scope tetap hanya Weekly Swing.** Istilah `GLOBAL` pada beberapa legacy filename berarti technical reuse boundary, bukan active multi-strategy scope.

## Current Reading Order

1. `../../governance/WEEKLY_SWING_POLICY_FRAMEWORK.md`
2. `02_PARAMSET_CONTRACT_GLOBAL.md`
3. `03_VALIDATOR_SPEC_GLOBAL.md`
4. `04_CONTRACT_TESTS_GLOBAL.md`
5. `05_EXECUTION_CANONICAL_GLOBAL.md`
6. `06_SCHEMA_PARITY_RULES.md`
7. `07_CONTRACT_FAILURE_CODES_LOCKED.md`

## Hard Boundary

Shared technical support tidak boleh menjadi owner untuk:
- Weekly Swing scoring/ranking/selection;
- recommendation / Top Picks behavior;
- CONFIRM semantics;
- Weekly Swing entry/exit/risk;
- strategy acceptance/OOS gates;
- strategy scope.

Jika terjadi konflik, `../../strategy/weekly_swing/` menang.
