# Coverage Gate Enforcement Contract LOCKED

Status: LOCKED  
Contract owner: Market Data / Publication Safety  
Last updated: 2026-04-27

## Purpose

Coverage gate is the single source of truth for deciding whether an EOD publication candidate may become readable and current. Coverage is not metadata-only; it is an enforcing gate that controls finalize outcome, terminal status, publishability, current pointer ownership, evidence export, and replay verification.

## Coverage Inputs

Coverage MUST be evaluated from persisted canonical valid EOD bars for the requested trade date and the applicable publication candidate when one is supplied.

Required fields:

- `expected_universe_count`
- `available_eod_count`
- `missing_eod_count`
- `coverage_ratio`
- `coverage_threshold_value`
- `coverage_threshold_mode`
- `coverage_gate_status`
- `coverage_reason_code`
- `coverage_universe_basis`
- `coverage_contract_version`
- `coverage_missing_sample`

## Calculation Rules

`expected_universe_count` is the number of active ticker universe members for the trade date according to the locked universe definition.

`available_eod_count` is the count of unique canonical valid EOD bar ticker IDs for the trade date that also belong to the expected universe.

`missing_eod_count = expected_universe_count - available_eod_count`.

`coverage_ratio = available_eod_count / expected_universe_count`.

When `expected_universe_count = 0`, coverage MUST NOT be coerced to 0 or 1. The coverage ratio is `null` and the gate status is `NOT_EVALUABLE`.

## Gate Status

Allowed coverage gate statuses:

- `PASS`: `expected_universe_count > 0` and `coverage_ratio >= coverage_threshold_value`
- `FAIL`: `expected_universe_count > 0` and `coverage_ratio < coverage_threshold_value`
- `NOT_EVALUABLE`: `expected_universe_count = 0` or coverage cannot be evaluated safely

`BLOCKED` is retained only as a backward-compatible quality-gate/readiness state. New coverage gate evaluation MUST emit `NOT_EVALUABLE`, not `BLOCKED`, when coverage itself cannot be evaluated.

## Reason Code Mapping

- `PASS` → `COVERAGE_THRESHOLD_MET`
- `FAIL` → `COVERAGE_BELOW_THRESHOLD` / finalize `RUN_COVERAGE_LOW`
- `NOT_EVALUABLE` → `RUN_COVERAGE_NOT_EVALUABLE`

## Finalize Enforcement

Finalize MUST require a coverage result before promotion.

- `PASS` may continue to seal/publishability/pointer validation.
- `FAIL` MUST finalize as `NOT_READABLE`; terminal status is `HELD` only when an existing readable fallback remains authoritative, otherwise `FAILED`.
- `NOT_EVALUABLE` MUST finalize as `NOT_READABLE`; terminal status is `HELD` only when an existing readable fallback remains authoritative, otherwise `FAILED`.

Coverage failure is never allowed to be hidden by fallback. Fallback only preserves the previous readable publication as authoritative; it does not make the failed candidate readable.

## Publishability Enforcement

- `PASS` + seal success + pointer validation may become `READABLE`.
- `FAIL` must be `NOT_READABLE`.
- `NOT_EVALUABLE` must be `NOT_READABLE`.

No `READABLE_WITH_OVERRIDE`, partial-readable, or source-mode bypass is allowed without a new locked contract.

## Pointer Enforcement

Only a candidate with coverage `PASS`, sealed state, successful finalize decision, and strict pointer validation may become current.

A candidate with coverage `FAIL` or `NOT_EVALUABLE` MUST NOT become current. If a non-readable run is detected as current, the implementation must clear or restore current ownership to the prior readable publication.

## Evidence Enforcement

Run evidence MUST expose coverage as first-class data, including:

- `coverage_summary`
- `coverage_reason_code`
- expected / available / missing counts
- ratio
- threshold
- gate state
- threshold mode
- universe basis
- contract version
- missing sample

## Replay Enforcement

Replay verification MUST compare coverage fields when present in the fixture expectation. Coverage mismatch is a replay mismatch.

Required replay-comparable coverage fields:

- `coverage_universe_count`
- `coverage_available_count`
- `coverage_missing_count`
- `coverage_ratio`
- `coverage_min_threshold`
- `coverage_gate_state`
- `coverage_reason_code`
- `coverage_threshold_mode`
- `coverage_universe_basis`
- `coverage_contract_version`
- `coverage_missing_sample`

## Command Enforcement

Operator command output MUST render coverage gate state, coverage reason code, and coverage summary whenever coverage telemetry exists.

## Anti-Bypass Rules

The following are forbidden:

- treating coverage as metadata-only
- allowing current publication when coverage is `FAIL` or `NOT_EVALUABLE`
- allowing source mode, manual file, correction, repair, replay, or evidence export to bypass coverage enforcement
- using fallback to convert a failed candidate into readable
- changing threshold without an explicit locked policy update
