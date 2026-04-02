# DB Fields & Metadata (Coverage Gate)

## Purpose
Define the minimum DB/runtime metadata that must be persisted or emitted so the locked coverage-gate contract is audit-visible and implementable.

This document does not own the coverage formula.
Formula and outcome semantics are owned by `../book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`.

## Required eod_runs fields (LOCKED minimum)
The `eod_runs` record for a requested trade date must make these values audit-visible:

- `coverage_universe_count` INT NULL  
  Official denominator resolved from the coverage universe as-of `trade_date_requested`.
- `coverage_available_count` INT NULL  
  Canonical valid-bar numerator used for coverage evaluation.
- `coverage_missing_count` INT NULL  
  Derived missing count for the resolved universe. Expected formula: `coverage_universe_count - coverage_available_count`, never below zero.
- `coverage_ratio` DECIMAL(8,6) NULL  
  Evaluated ratio before any UI-only rounding.
- `coverage_min_threshold` DECIMAL(8,6) NULL  
  Threshold actually used by the run.
- `coverage_gate_state` ENUM/VARCHAR NULL  
  Final allowed values: `PASS`, `FAIL`, `BLOCKED`.
- `coverage_threshold_mode` VARCHAR(32) NULL  
  Initial locked value: `MIN_RATIO`.
- `coverage_universe_basis` VARCHAR(64) NULL  
  Initial locked value: `ACTIVE_LISTED_EQUITY_AS_OF_DATE`.
- `coverage_contract_version` VARCHAR(64) NULL  
  Contract/config identity for audit and replay clarity.
- `coverage_missing_sample_json` JSON/TEXT NULL  
  Optional but recommended sample of missing ticker codes for operator evidence. Sampling must not replace the official counts.

## Why these fields are required
They close the ambiguity that previously allowed coverage to be discussed without proving:
- what denominator was used
- what numerator was used
- what threshold was used
- why the gate ended in `PASS`, `FAIL`, or `BLOCKED`

## Required config metadata linkage
The persisted coverage values must stay explainable from runtime config. At minimum, config must expose:
- `MARKET_DATA_COVERAGE_MIN`
- `MARKET_DATA_COVERAGE_THRESHOLD_MODE`
- `MARKET_DATA_COVERAGE_UNIVERSE_BASIS`
- `MARKET_DATA_COVERAGE_CONTRACT_VERSION`

## Replay / evidence mirror
If replay or evidence tables mirror run metrics, they should also mirror the same coverage metadata fields so proof artifacts can explain the original gate decision without re-deriving it.

## Anti-ambiguity notes
- `coverage_ratio` alone is not sufficient.
- `coverage_gate_state` alone is not sufficient.
- provider row count must never substitute for `coverage_universe_count`.
- eligibility row count must never substitute for `coverage_available_count`.
