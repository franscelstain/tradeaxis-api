# Terminology and Scope

## Purpose
Define locked terminology for the Market Data Platform (EOD) after the architecture split into **Import** and **Promote** phases.

This module remains upstream-only.
It does not define watchlist scoring, ranking, trading signals, portfolio action, or broker execution.

## Scope of Market Data Platform (LOCKED)
Market Data Platform is responsible for producing upstream artifacts that are:
- canonical
- validated
- deterministic
- auditable
- sealable
- safe for downstream consumption

Its minimum outputs remain:
- canonical EOD bars
- EOD indicators
- eligibility snapshot
- run metadata
- content hashes
- seal/publication metadata

## New phase terminology (LOCKED)

### Import
Import is the upstream acquisition and bar-persistence phase.

Import includes:
- acquisition
- ticker-level processing
- source mapping
- dedup
- validation
- bars write
- invalid-row write
- bars coverage evidence
- telemetry

Import does **not** include:
- indicators
- eligibility
- hash
- seal
- finalize

### Promote
Promote is the phase that turns persisted import results into a consumer-readable candidate.

Promote includes:
- validation
- bars coverage gate
- indicators
- eligibility
- hash
- seal
- finalize

Promote is the only phase allowed to create requested-date readable success.

### Date-driven capability
The locked capability that the platform accepts explicit requested dates as domain input.

It means:
- one specific requested trade date can be imported explicitly
- one explicit trading-date range can be imported explicitly
- historical and recent dates are both first-class inputs
- provider transport defaults do not define the domain boundary

### Provider limitation abstraction
The rule that provider quirks such as `range=10d`, rate limit, per-ticker request fan-out, or transport-specific parameter shapes must be absorbed by the acquisition strategy rather than inherited as domain limits.

### Fatal failure
Failure that may stop the whole requested-date run immediately.
Examples: invalid config, broken endpoint contract globally, broken global parser, DB/storage failure.

### Per-ticker failure
Failure that affects one ticker request/import unit only.
Examples: rate limit, timeout, empty result, malformed single payload.

Per-ticker failure must be recorded and tolerated during import.
It is evaluated later through coverage/readiness, not as automatic full-run stop.

### Bars coverage
Coverage based on canonical valid bars after mapping, dedup, and validation.

Bars coverage is **not** request-success coverage.

## Existing terminology retained

### Requested trade date
The date originally requested for processing by a run.

### Effective trade date
The consumer-readable date resolved by readability rules after finalization.

### Canonical bars
Validated EOD OHLCV rows that represent the authoritative upstream bar dataset for one trade date and one ticker.

### Invalid bars
Source-derived rows rejected from canonical publication because they violate locked validation rules.

### Indicators
Deterministic derived metrics computed from canonical bars under locked formula and window rules.

### Eligibility snapshot
One row per coverage-universe ticker for trade date D stating whether the ticker is upstream-readable and, if blocked, why.

### Seal
The act and metadata state that freeze a coherent consumer-readable dataset publication.

### Publication
The current sealed consumer-readable dataset state for one effective trade date.

### Replay
Controlled historical re-execution used to verify determinism and readiness behavior.
Replay is not trading-strategy backtesting.

### Session snapshot
Optional non-streaming supplemental artifact aligned to the effective readable trade date and captured at a real wall-clock time.

## Locked interpretation rules
1. Import completion must never be described as readable publication.
2. Promote owns indicators, eligibility, hash, seal, and finalize.
3. Per-ticker failure must never be described as automatic full-run fatality unless it is truly a global failure.
4. Coverage must never mean successful HTTP request count.
5. Publication must never mean “latest raw row by timestamp”; it means the current sealed readable state.
6. Date-driven capability must never be reduced to provider default recent window behavior.
7. Provider limitation abstraction must never be described as domain limitation.

## Anti-domain-leak rule (LOCKED)
Any wording that implies:
- pick selection
- ranking preference
- strategy approval
- broker action
- portfolio action

is outside this module unless explicitly documented as downstream consumer behavior.
