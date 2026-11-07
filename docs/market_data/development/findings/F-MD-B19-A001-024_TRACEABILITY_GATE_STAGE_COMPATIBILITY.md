# Finding — F-MD-B19-A001-024

- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Status: `OPEN`
- Severity: `P2`
- Raised by: `E-MD-B19-A001-048`

## Exact current-build gap

The standalone `MarketDataTraceabilityApplicabilityGate.php` entry point validates `MD-B01` by default and labels its output B01/A012. Prior standalone PASS output is not a B19 traceability PASS. E048 explicitly called `validate(rows, 'MD-B19')`: the result is FAIL with 748 missing B01/A012-or-successor structured-note messages and 36 conditional-applicability mismatches against that gate's semantic review map. The B19 rows carry the existing B19 stage-entry review notes and the governed 714 mandatory plus 36 conditional-applicable scope.

Both the validator and canonical traceability CSV are byte-identical to current Git HEAD. This is a pre-existing gate/stage compatibility gap, not evidence that B19 applicability should be rewritten to B01 semantics. No matrix, denominator, frozen authority, accepted basis or historical B18 disposition is changed.

## Required resolution

Provide an applicable B19 traceability validation path under the existing stage-entry and BL001 authority. Validate the B19 750-row identity, current applicability and predicate context without substituting the B01 conditional map or silently normalizing the matrix. Retain the explicit FAIL until that path executes. Current B19 operations proof separately reports 750 applicable, 647 repository bases, 103 missing and formal SATISFIED zero; it remains FAIL for its actual missing proof/guards.

## Retained evidence

`E048_runtime/gate_traceability_B19.stdout.txt`, `traceability_baseline_comparison.json` and `E048_traceability_check.php` retain the exact read-only call, errors and HEAD/current hashes. This finding does not independently invalidate reviewed runtime evidence or reopen B18.

## E049 internal continuation checkpoint

E049 locally corrects B19 validation to its own750-row registered canonical scope and independently verified applicability/source identities; explicit B19 CLI and negative tamper/mutation-option cases pass in current targeted execution. The matrix and denominator are unchanged. Local PASS is proposed implementation/proof of F024 compatibility, not formal finding closure or B19 admission. Status remains OPEN pending independent review.
