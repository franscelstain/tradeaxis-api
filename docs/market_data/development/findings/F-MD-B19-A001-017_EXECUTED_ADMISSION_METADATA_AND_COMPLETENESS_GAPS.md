# Finding — F-MD-B19-A001-017

- ID: `F-MD-B19-A001-017`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Raised at: 2026-10-19T14:12:45+07:00
- Severity: `P2`
- Status: `RESOLVED`

## Demonstrated gaps

Actual MariaDB run with own mandatory trail removed was ADMITTED_INCOMPLETE but final completeness still COMPLETE. Private admission accepted missing/zero identity and missing/symbolic/invalid calendar timestamps. Clean assertion reds retained. Initial property collision was a test-harness diagnostic, not a production defect.

## Bounded correction

Final run completeness includes final admission gaps. Positive typed identity and strict persisted timestamp required; invalid source value remains readable, marked not_available without current-time fallback. Explicit implementation criteria version binds source bytes/declared sections; claims distinguish evidence completeness from output correctness, cross-version equivalence and governed verification. Existing authority implemented, no strategy/freeze revision.

## Proof and limitations

Actual producer/persistence/export SUCCESS,401 FAILED,approved106 correction,superseded publication,MATCH and independent MISMATCH. A fully diagnosed MISMATCH/FAIL can remain complete evidence. Eleven runtime mutations and one proof-refusal bypass restored exact bytes. All72 obligations independently mapped, no aggregate automatic admission.

Two incomplete mock exporter fixtures now expect INCOMPLETE; historical identity assertions preserved. An existing coverage static guard fails on unchanged persistedCoverageExpectedCount helper; bounded HEAD source comparison retained, not a new production regression. Alias F009/E013 retained. No full-suite PASS. R0324 HELD D008/F016;MD-DEP-0025 ACTIVE.
