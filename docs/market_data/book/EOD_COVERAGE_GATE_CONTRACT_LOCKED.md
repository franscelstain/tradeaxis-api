# EOD Coverage Gate Contract (LOCKED)

## Purpose
Define the official owner contract for the EOD coverage gate that controls whether a requested trade date may become readable.

This contract is binding for:
- coverage universe definition used by market-data finalization
- numerator and denominator semantics
- coverage-ratio calculation
- coverage gate states
- how coverage contributes to final terminal status and publishability

This document owns the coverage formula and outcome mapping.
Universe-membership rules remain aligned with `Coverage_Universe_Definition_LOCKED.md`.

---

## 1. Coverage universe (LOCKED)
For requested trade date `D`, the coverage denominator must be based on the resolved coverage universe as-of `D`.

Default resolved coverage universe for `D` is:
- all ticker identities that are active members of the upstream equity universe as-of `D`

Minimum membership rules:
- `is_active = Yes`
- `listed_date <= D`
- `delisted_date IS NULL OR delisted_date >= D`

If richer temporal master-data exists, the implementation may apply a stricter upstream-equity filter, but it must stay deterministic and audit-visible.

Coverage denominator must never be derived from:
- how many rows the provider happened to return
- how many eligibility rows were successfully built
- how many rows survived downstream consumer filters
- current wall-clock membership state when historical as-of membership is available

---

## 2. Coverage numerator (LOCKED)
A ticker counts toward the numerator only if the ticker has a canonical valid EOD bar for `D`.

A canonical valid EOD bar means at minimum:
- the row exists in canonical EOD bar storage for `D`
- the row passed canonicalization / validation
- the row is not marked unusable for consumer publication

Indicator availability does not define coverage numerator.
Eligibility availability does not define coverage numerator.
Coverage measures bar availability over the resolved universe.

---

## 3. Coverage denominator (LOCKED)
`expected_universe_count` = count of all tickers in the resolved coverage universe for `D`

This denominator is the official denominator for coverage.
No alternate denominator is allowed for readable publication decisions.

Examples of forbidden denominator substitutions:
- `count(provider_rows_for_D)`
- `count(eligibility_rows_for_D)`
- `count(indicator_rows_for_D)`
- `count(valid_bars_for_D) + count(missing_known_subset)`

---

## 4. Coverage formula (LOCKED)
The official formula is:

`coverage_ratio = available_canonical_eod_count / expected_universe_count`

Where:
- `available_canonical_eod_count` = numerator from Section 2
- `expected_universe_count` = denominator from Section 3

### Precision rule
The stored/displayed ratio may be rounded for presentation, but gate evaluation must use the full internal numeric value or a documented fixed precision rule.

### Threshold rule
Coverage passes only if:

`coverage_ratio >= COVERAGE_MIN`

`COVERAGE_MIN` must be explicit, config-visible, and audit-visible per run.
There must be no implicit or guessed threshold.

---

## 5. Special cases (LOCKED)

### 5.1 Zero-universe case
If `expected_universe_count = 0`, the gate state must be:
- `BLOCKED`

Reason:
- coverage is not meaningfully evaluable for readable publication
- the system must not treat an empty denominator as automatic success

### 5.2 Missing universe resolution inputs
If the system cannot resolve the universe for `D` because required master-data prerequisites are missing, inconsistent, or unusable, the gate state must be:
- `BLOCKED`

### 5.3 Missing numerator evidence
If the system cannot reliably determine canonical valid-bar availability for `D`, the gate state must be:
- `BLOCKED`

`BLOCKED` is used when evaluation prerequisites are missing or invalid.
`FAIL` is used when evaluation completed and ratio is below threshold.

---

## 6. Coverage gate states (LOCKED)
Allowed states for the coverage gate are:
- `PASS`
- `FAIL`
- `BLOCKED`

Interpretation:
- `PASS`: coverage was evaluated and `coverage_ratio >= COVERAGE_MIN`
- `FAIL`: coverage was evaluated and `coverage_ratio < COVERAGE_MIN`
- `BLOCKED`: coverage could not be evaluated safely because prerequisite universe/bar evidence is missing, invalid, or empty in a way that makes evaluation non-meaningful

`NOT_EVALUABLE` is not an allowed final gate state in this contract.
Use `BLOCKED` instead.

---

## 7. Coverage outcome matrix vs finalization (LOCKED)
Coverage gate alone does not create readability.
Readable success still requires all other locked gates, seal, hash, publication resolution, and finalization rules.

### Coverage PASS
If coverage gate = `PASS` and all other required gates pass:
- quality gate state may resolve to `PASS`
- terminal status may resolve to `SUCCESS`
- publishability state may resolve to `READABLE`

If coverage gate = `PASS` but another required gate fails or is blocked:
- requested date must remain non-readable
- final outcome follows the failing/blocking gate family

### Coverage FAIL
If coverage gate = `FAIL`:
- requested date must not become `READABLE`
- final publishability for requested date = `NOT_READABLE`
- terminal outcome should resolve to:
  - `HELD` if a prior readable fallback exists
  - `FAILED` if no safe fallback exists and consumers cannot be served a valid readable date

### Coverage BLOCKED
If coverage gate = `BLOCKED`:
- requested date must not become `READABLE`
- final publishability for requested date = `NOT_READABLE`
- terminal outcome should resolve to:
  - `FAILED` when the blocked condition is a prerequisite/integrity failure that prevents trusted publication evaluation
  - `HELD` only if implementation explicitly classifies the requested date as non-readable while a previously sealed readable fallback still remains authoritative for consumers

When in doubt, `BLOCKED` must never be collapsed into readable success.

---

## 8. Required stored/audit-visible fields (LOCKED minimum)
At minimum, the run/finalization evidence must make the following values audit-visible:
- `requested_trade_date`
- `coverage_universe_count`
- `coverage_available_count`
- `coverage_ratio`
- `coverage_min_threshold`
- `coverage_gate_state`
- `quality_gate_state`
- `terminal_status`
- `publishability_state`
- fallback readability resolution outcome when applicable

Field names may vary, but meanings must stay intact.

---

## 9. Anti-ambiguity rules (LOCKED)
The implementation must not:
- use provider-return count as the denominator
- treat zero-universe as automatic pass
- treat coverage numerator as indicator count or eligibility count
- publish requested date as readable when coverage gate is `FAIL` or `BLOCKED`
- silently switch coverage semantics between runs without audit-visible contract change

---

## 10. Cross-contract alignment
This contract must remain aligned with:
- `Coverage_Universe_Definition_LOCKED.md`
- `Run_Status_and_Quality_Gates_LOCKED.md`
- `EOD_Cutoff_and_Finalization_Contract_LOCKED.md`
- `../ops/Daily_Pipeline_Execution_and_Sealing_Runbook_LOCKED.md`
- `../tests/Contract_Test_Matrix_LOCKED.md`
