# Coverage Gate Enforcement Contract LOCKED

Status: LOCKED  
Contract owner: Market Data / Publication Safety  
Last updated: 2026-08-12

## Purpose

Coverage gate is the enforcing owner for delivery completeness, but not the sole readability decision. Independent quality, provenance, event-risk, eligibility, seal, and pointer gates must also pass.

## Coverage Inputs

Coverage MUST be evaluated from persisted temporal expectation plus immutable requested-date delivery evidence for the applicable candidate. Canonical-valid rows are a separate quality/readability count and are never substituted for delivery evidence.

Required fields:

- `expected_universe_count`
- `coverage_universe_count` raw temporal-universe evidence
- `coverage_universe_hash`
- `verified_not_expected_count`
- `coverage_bar_not_expected_count` persisted/evidence field
- `expectation_unknown_count`
- `coverage_expectation_unknown_count` persisted/evidence field
- `delivered_observation_count`
- `coverage_delivered_count` persisted/evidence field
- `canonical_valid_count`
- `coverage_delivered_valid_count` persisted/evidence field
- `quality_blocked_count`
- `available_eod_count`
- `missing_eod_count`
- `expected_bar_count` / `coverage_expected_count` evidence alias
- `available_bar_count` / `coverage_available_count` evidence alias
- `missing_bar_count` / `coverage_missing_count` evidence alias
- `coverage_ratio`
- `coverage_threshold_value`
- `coverage_min_threshold` persisted/evidence alias
- `coverage_threshold_mode`
- `coverage_gate_status`
- `coverage_gate_state` persisted/evidence alias
- `coverage_reason_code`
- `coverage_universe_basis`
- `coverage_contract_version`
- `coverage_missing_sample`
- `coverage_missing_sample_json` exact stored-field export
- `coverage_excluded_sample_json`

## Calculation Rules

`expected_universe_count` is the temporal as-of-D universe count minus only verified `NOT_EXPECTED` listing/date states. `UNKNOWN` remains included.

`available_eod_count`/`delivered_observation_count` is the count of unique traceably delivered requested-date market observations in the expected universe. Canonical-valid and quality-blocked counts remain separate.

`missing_eod_count = expected_universe_count - delivered_observation_count`.

`coverage_available_count` remains a canonical-availability compatibility metric. It must never be
substituted for `coverage_delivered_count`, just as raw `coverage_universe_count` must never be
substituted for `coverage_expected_count`.

`coverage_ratio = delivered_observation_count / expected_universe_count`.

Dormancy, zero volume, illiquidity, provider failure, or current activity/status may not reduce `expected_universe_count`. Delivered-but-invalid observations can contribute to delivery measurement but independently block quality/eligibility/readability.

The single locked platform threshold is `MARKET_DATA_COVERAGE_MIN = 0.98`. Runtime config key `market_data.coverage_gate.min_ratio` and legacy alias `market_data.platform.coverage_min` must resolve to the same 0.98 default unless a future locked policy update changes it explicitly.

When `expected_universe_count = 0`, coverage MUST NOT be coerced to 0 or 1. The coverage ratio is `null` and the gate status is `NOT_EVALUABLE`.

## Gate Status

Allowed coverage gate statuses:

- `PASS`: `expected_universe_count > 0` and `coverage_ratio >= coverage_threshold_value`; this does not imply quality/readability pass
- `FAIL`: `expected_universe_count > 0` and `coverage_ratio < coverage_threshold_value`
- `NOT_EVALUABLE`: `expected_universe_count = 0` or coverage cannot be evaluated safely

`BLOCKED` is retained only as a backward-compatible quality-gate/readiness state and legacy input marker. New coverage gate evaluation MUST emit `NOT_EVALUABLE`, not `BLOCKED`, when coverage itself cannot be evaluated. If evidence, replay, command, or repository boundaries read legacy `BLOCKED`, they MUST emit final `coverage_gate_state=NOT_EVALUABLE` and may expose the raw value only as explicit `legacy_coverage_gate_state_raw=BLOCKED`.

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
- `coverage_universe_hash`
- `coverage_expected_count`
- `coverage_bar_not_expected_count`
- `coverage_expectation_unknown_count`
- `coverage_delivered_count`
- `coverage_delivered_valid_count`
- `coverage_available_count`
- `coverage_missing_count`
- `expected_bar_count`
- `available_bar_count`
- `missing_bar_count`
- `coverage_ratio`
- `coverage_min_threshold`
- `coverage_gate_state`
- `legacy_coverage_gate_state_raw` when legacy input was normalized
- `coverage_reason_code`
- `coverage_threshold_mode`
- `coverage_universe_basis`
- `coverage_contract_version`
- `coverage_missing_sample`
- `coverage_missing_sample_json`
- `coverage_excluded_sample_json`

## Command Enforcement

Operator command output MUST render coverage gate state, coverage reason code, and coverage summary whenever coverage telemetry exists.

## Anti-Bypass Rules

The following are forbidden:

- treating coverage as metadata-only
- allowing current publication when coverage is `FAIL` or `NOT_EVALUABLE`
- allowing source mode, manual file, correction/republication, pointer-integrity repair, replay, or evidence export to bypass coverage enforcement
- excluding dormant, zero-volume, illiquid, provider-missing, or current-inactive listings without verified point-in-time `NOT_EXPECTED` evidence
- treating delivery coverage as quality or eligibility
- using fallback to convert a failed candidate into readable
- changing threshold without an explicit locked policy update

## Capability boundary (LOCKED)

**What gate enforcement proves.** That the coverage decision was computed from governed counts, compared against the bound threshold, and that a failing or non-evaluable result blocked readability rather than degrading quietly.

**What it cannot prove.**

- **That a passing ratio means the delivered data is right.** The gate counts delivered observations against expected ones. Correctness of the values is a different dimension with its own gates, and passing here says nothing about it.
- **That the threshold is the right threshold.** A configured boundary expresses a chosen tolerance. A date just above it is not thereby sound, and a date just below it is not thereby unusable.
- **That the counts it compared were themselves correct.** Numerator and denominator arrive from delivery and expectation resolution. If either is wrong, the gate computes a precise ratio of wrong numbers and reports it confidently.

Consequently a coverage `PASS` may be cited as evidence that **the delivered share met the declared threshold**, never as evidence that **the date is complete or correct**.
