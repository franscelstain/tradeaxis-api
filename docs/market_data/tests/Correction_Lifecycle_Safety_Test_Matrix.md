# Correction Lifecycle Safety Test Matrix

Status: ENFORCED — pending operator-local PHPUnit evidence before LOCKED.

Required validation coverage:

- baseline resolver uses current readable pointer only and rejects latest/MAX-date shortcut
- invalid/incomplete correction hash comparison blocks pointer switch
- unchanged correction discards candidate publication, preserves pointer, and does not reseal
- changed correction requires deterministic artifact changed scope before reseal/promotion
- correction failure restores or preserves previous current readable publication
- correction-run-publication-artifact linkage is exposed in evidence
- replay stores and compares expected/actual correction lifecycle context
- correction command output displays unchanged/reseal/baseline/candidate/final outcome context
- static guard prevents baseline shortcut, invalid diff bypass, missing evidence/replay fields, and hidden command lifecycle state

Manual command set is owned by `LUMEN_IMPLEMENTATION_STATUS.md` active session evidence until local validation promotes this contract to LOCKED.
