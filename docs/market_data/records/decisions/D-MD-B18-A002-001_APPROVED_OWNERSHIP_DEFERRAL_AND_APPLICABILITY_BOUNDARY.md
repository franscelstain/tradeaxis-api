# Decision — approved ownership and deferral package

- ID: `D-MD-B18-A002-001`
- Stage / Attempt / Work / Baseline: `MD-B18` / `MD-B18-A002` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Epoch: `MD-REBASELINE-20260820-001`
- Issued: 2026-09-13T11:00:00+07:00
- Status: `ISSUED — USER_APPROVED_BOUNDED_PACKAGE`
- Mutability: `IMMUTABLE_AFTER_ISSUE`
- Findings: `F-MD-B18-A002-003`, `F-MD-B18-A002-004`, `F-MD-B18-A002-006`
- Evidence: `E-MD-B18-A002-002` (document-scope audit, not runtime acceptance)
- Dependencies: `MD-DEP-0010`, `MD-DEP-0011`, `MD-DEP-0012`; orchestration remains under `MD-DEP-0009`

## Authorization and decisions

The user stated, "Saya menyetujui paket rekomendasi yang anda sarankan", approving the immediately
preceding four-item recommendation. This records that authorization now, without backdating it
or treating it as authority to change frozen strategy.

1. The recommendation to classify MD-S050-R0040/R0041 as CONDITIONAL_NOT_APPLICABLE was approved
   on the stated basis that the parent's absent-AS_KNOWN condition is false. Execution still
   requires current evidence of that false condition. Whole-parent review on this continuation
   also found R0038/R0039 under the same parent. Their final applicability was omitted from the
   recommendation and is NOT silently decided here. All four siblings remain pending together
   until MD-DEP-0012 is decided; no NOT_APPLICABLE or SATISFIED evidence binding is issued here.
2. MD-S002-R0004 is assigned to MD-B22 as proof-owning stage, MD-B18 supporting, under traceability
   standard section 7. The governing parent is MD-S002-R0002, "A release candidate requires".
   The complete supported-runtime/locale/concurrency predicate remains MANDATORY / NOT_ASSESSED.
   MD-DEP-0011 carries F-MD-B18-A002-004 into release validation. A second interpreter is not
   installed, supported PHP versions are not narrowed, and single-process order independence is
   not represented as proof of all concurrency conditions. MD-B22 must define and execute the
   complete supported-condition corpus before release; B18 locale/order guards are support only.
3. MD-S004-R0007 is REFERENCE_ONLY in the Market Data matrix, with a recorded reference decision.
   MD-S004 Boundary explicitly assigns signal, execution, cost and portfolio policy to the
   downstream backtest. Its price/execution paragraph governs that consumer's choice of analytical
   inputs, simulated executable prices/times and cashflow treatment. The paragraph remains frozen
   and binding on its owner; no Market Data data-product or availability fact obligation is removed.
4. F-MD-B18-A002-003 is deferred to MD-B21 constraint hardening under MD-DEP-0010. It remains OPEN,
   not fixed or waived. Until then SQLite may support behavior but never establish referential
   integrity or production nullability. Any B18 constraint-bearing predicate still needs actual
   MariaDB proof; absence of it blocks that predicate despite this deferral.

## Impact and limit

Only the two approved ownership/reference rows are finalized. Four sibling applicability rows are
uniformly pending, with the governing condition composed into their normalized predicates.
The earlier predicted final denominator 117 is withdrawn as an incomplete scope calculation.
If the same false condition is approved and evidenced for all four children, the projected B18
denominator is 115, not 117. It is not final or satisfied now.

MD-B21 and MD-B22 are not opened by this decision. Their obligations are recorded in the existing
stage sequence, with this decision/finding/evidence chain available for explicit future baseline
relationships. The single next executable resume point remains MD-B18-A002; return-to MD-B19-A001
requires actual B18 closure and resolution of MD-DEP-0009. No strategy byte, issued BL/E/D/SC,
runtime support declaration, application behavior, or proof gate threshold is changed here.

## Decision still required

Recommended: apply MD-S050-R0037's same external condition to the omitted R0038/R0039 as well,
then prove that condition false with fresh integrated AS_KNOWN execution before terminal N/A
binding of all four siblings. Impact: projected denominator 115 and four separately evidenced
conditional N/A rows. Alternative: keep the four pending and re-review applicability; closure
remains blocked. No option permits dropping the parent or keeping 117 merely to match a forecast.
