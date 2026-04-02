# Daily Pipeline Execution and Sealing Runbook (LOCKED)

## Purpose
Ensure daily runs produce a frozen upstream dataset that downstream consumers can read safely.

## Daily order (LOCKED)
1) acquire and publish canonical EOD bars for requested date `T`
2) compute indicators for `T` using locked semantics and version
3) build eligibility for requested date `T`
4) evaluate coverage gate for `T` using resolved universe, canonical valid-bar count, and explicit `COVERAGE_MIN`
5) compute audit hashes for the candidate consumer-visible date
6) write seal metadata for that date only if all seal preconditions pass
7) finalize run status and resolve `trade_date_effective`
8) generate run artifacts after final status is committed

## Coverage gate operator rule (LOCKED)
Before requested date `T` can become readable, operators and code must be able to show:
- resolved coverage universe for `T`
- `coverage_universe_count`
- `coverage_available_count`
- `coverage_ratio`
- explicit threshold `COVERAGE_MIN`
- final `coverage_gate_state`

Coverage denominator must come from resolved universe membership as-of `T`.
It must not come from provider-return count or eligibility row count.

## Readiness rule (LOCKED)
Downstream consumers may only use:
- `trade_date_effective = D` that resolves to a finalized safe date
- and a dataset that is `SEALED`

If requested date is `HELD` / `FAILED` or unsealed:
- effective date falls back when a prior readable publication exists
- downstream readers must use fallback `D`
- requested date itself remains non-readable

## Coverage-specific outcome guidance
- coverage `PASS` is necessary but not sufficient for readable success
- coverage `FAIL` means requested date must not be sealed/readable for requested-date publication
- coverage `BLOCKED` means requested date must not be treated as publishable because the evaluation basis itself is not trustworthy or not meaningfully evaluable

## Sealing storage (LOCKED default)
Recommended storage is on `eod_runs`:
- `sealed_at`
- `sealed_by`
- `seal_note`

## Controlled correction
If provider correction occurs for a sealed date:
- create new run
- recompute artifacts
- recompute hashes
- re-evaluate coverage gate
- reseal only if requested date again satisfies all locked readability preconditions
- preserve old run for audit

## Operator checklist
- final status appropriate?
- coverage denominator based on resolved universe?
- coverage numerator based on canonical valid bars?
- coverage ratio passes explicit threshold?
- hashes non-null?
- `sealed_at` non-null for consumable date?
- fallback resolution recorded honestly when requested date is not readable?

If any answer above is no, requested date is not ready.
