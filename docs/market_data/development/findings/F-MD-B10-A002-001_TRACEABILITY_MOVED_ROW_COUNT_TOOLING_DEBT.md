# Finding - B10 traceability moved-row count is stale

- ID: F-MD-B10-A002-001
- Stage / Attempt / Baseline / Epoch: MD-B10 / MD-B10-A002 / MD-B10-A002-BL001 / MD-REBASELINE-20260820-001
- Raised: 2026-09-28T14:51:16+07:00
- Severity: P2 - tooling/current closure assessment
- Status: RESOLVED - the expectation is reconciled to the measured value with fail-closed proof (E-MD-B10-A002-018)
- Class: TOOLING_DEFECT
- Related: E-MD-B10-A002-002, E-MD-B10-A002-001, CI-MD-B10-A002-001

## Observed discrepancy

MarketDataPublicationLifecycleTraceabilitySpec::EXPECTED_MOVED is 1. The gate selects active rows whose primary stage differs from B10 and whose supporting stages contain B10; both committed HEAD 800774357215eb86489a3750bc25c26abe26fe1b and the current canonical matrix produce 0. This pre-existing false failure is separate from B10's 56 intentionally NOT_ASSESSED predicates.

No current finding/dependency specifically recorded this tooling discrepancy before this record. The prior scope review reported it without correcting the gate. The mutable Stage Register coverage cell is now synchronized to actual zero moved/supporting rows; historical evidence and ownership remain intact.

## Remediation and closure

Reconcile the gate's count with current canonical ownership/traceability through a separate bounded tooling unit; prove genuine unexpected moved membership still fails closed. Do not add a fictitious matrix row, change strategy, inherit a historical count, or weaken intentionally unbound-rule checks. Scope: traceability tooling only; no additional predicate demotion or artifact fix is credited.

## Orchestration

Record this debt separately. Current artifact remediation waits for MD-DEP-0021 foundation delivery under the same A002 attempt; this finding creates no parallel executable resume point and is not that external dependency. No gate/source/test change made here.

## Resolution - 2026-10-02 - E-MD-B10-A002-018

Relationship to `F-MD-B10-A002-006`: a separate pre-existing defect that did not prevent a valid successor binding, but the B10 traceability gate cannot pass in bound mode while it stands. It was resolved on its own criteria and not folded into the binder finding.

`MarketDataPublicationLifecycleTraceabilitySpec::EXPECTED_MOVED` is now 0, the measured number of active rows owned by another stage that name `MD-B10` as supporting, at HEAD `800774357215eb86489a3750bc25c26abe26fe1b` and on the current matrix. The other expectations (denominator 1072, optional 1, reference 239, pending 0, mandatory states) are still enforced. A genuinely moved row still fails closed: self-test cases `f001_*` and mutation probes G04 and G05 prove it. No matrix row was added or changed, no strategy changed and no historical count was inherited.
