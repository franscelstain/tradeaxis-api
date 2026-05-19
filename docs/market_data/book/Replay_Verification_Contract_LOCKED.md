# Replay Verification Contract (LOCKED)

## Scope

Replay verification proves that an executed market-data run can be compared against an explicit fixture package without resolving data through latest-date, latest-run, raw, or staging shortcuts.

This contract covers current-readable replay, explicit historical replay, comparison semantics, result persistence, command output, and evidence export linkage.

## Fixture Rule

Replay fixtures must include `manifest.json` and expected files listed by the manifest. A valid replay manifest must identify:

- `fixture_id`
- `fixture_family`
- `fixture_version`
- `fixture_schema_version`
- `fixture_created_at`
- `fixture_source`
- `contract_areas`
- `files`
- `assertion_layers`

Expected replay context must include run, source/provider, coverage, artifact hash, seal, publication, pointer, fallback, correction, final state, reason-code, and lineage context when available.

## Current Publication Rule

Current replay verification must resolve the actual publication through the current-readable pointer path:

`eod_current_publication_pointer` -> `eod_publications` -> sealed/success/readable/coverage-pass publication and run checks.

Current replay must not resolve the actual state through latest run, latest date, raw, staging, or unscoped artifact tables.

## Historical Publication Rule

Historical replay is allowed only when the fixture declares explicit historical publication context. Historical replay must carry explicit `run_id`, `publication_id`, and `trade_date` selectors and must not masquerade as current replay.

Historical publications are audit evidence only. They do not satisfy current consumer reads and must expose `historical_publication_allowed=true`, `current_pointer_required=false`, and a non-current pointer status.

## Comparison Rule

Replay verification must compare, when present:

- coverage gate state, ratio, minimum threshold, expected/available/missing counts
- terminal status and publishability state
- final reason code and reason-code count distribution
- source mode, source name, provider context, source file identity, and manual-file policy context
- artifact hashes for bars, indicators, and eligibility
- seal state and seal metadata
- publication identity, version, current flag, and pointer resolution context
- fallback context
- correction lifecycle and lineage context
- deterministic field list and ignored volatile fields

Missing expected or actual proof is a mismatch or blocked prerequisite, not a silent success.

## Result Rule

Replay result status is explicit:

- `PASS`: comparison is deterministic (`MATCH`) or an expected degraded outcome matches (`EXPECTED_DEGRADE`)
- `FAIL`: comparison ran and found divergence (`MISMATCH` or `UNEXPECTED`)
- `BLOCKED`: replay could not run because fixture, context, or runtime prerequisites were missing

`comparison_result` remains the detailed replay comparison class. `replay_status` is the operator-facing result class used by command output, persistence, smoke/backfill summaries, and evidence export.

## Evidence Linkage Rule

Replay evidence export must be able to reference:

- `replay_id`
- `replay_status`
- `comparison_result`
- expected context
- actual context
- comparison summary
- reason-code counts
- publication and pointer context
- coverage comparison
- hash/seal comparison

Evidence export must preserve admission state and must not require an external database query as the primary proof of replay status.

## Locked Validation Rule

This contract may be treated as LOCKED only when replay command surface, generated fixture proof, PASS/FAIL/BLOCKED runtime outcomes, evidence export linkage, targeted replay tests, replay static guards, audit docs guard, and full MarketData unit scope pass in a Lumen-compatible local environment.
