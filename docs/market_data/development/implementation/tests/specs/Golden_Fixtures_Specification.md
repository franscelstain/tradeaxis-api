# Golden Fixtures Specification (STRATEGY LOCKED)

## Package

Each fixture package contains a versioned manifest, immutable input observations/master revisions/config snapshot, independently derived expected artifacts/states/hashes, source/reference notes, and runner/evidence instructions. Package files have hashes; volatile runtime fields are explicitly excluded.

Real-market cases are stored as dated frozen evidence with source/licensing metadata. Synthetic minimal cases may isolate mathematics or negative invariants but must be labeled synthetic and cannot replace required real-market semantics.

## Mandatory families

- observation success, stale date, schema drift, provider outage, invalid/zero/conflicting bars;
- temporal listing membership, symbol transition/reuse, mapping changes, calendar/status corrections;
- verified no-bar-expected versus unknown expectation;
- verified split/reverse split/rights/bonus cases plus unverified discontinuity candidates;
- coherent RAW/structural-adjusted/total-return products and actual/proxy liquidity;
- ATR seed, long recursive chain, missing session, later listing, and old correction beyond fourteen sessions;
- coverage and multi-reason eligibility edge cases;
- full-config drift and deterministic serialization;
- current, held, failed, explicit stale fallback, correction concurrency, and bypass rejection;
- exact publication and as-known replay with late-known revisions.

## Oracle discipline

Expected values are calculated independently (for example, reviewed spreadsheet/reference implementation and manual lineage derivation) and include precision/rounding. For real events, official/authoritative event terms establish verification; price behavior may confirm a test scenario but cannot be the verifying source.

Provider payloads are sanitized and frozen; provider `adj_close` is never the expected structural product oracle.

## Change rule

A semantic change creates a new fixture/contract version. Do not update expected files merely to make a changed implementation green. The review records why the old oracle was wrong or why the new version intentionally differs.

## Admission

`PASS` requires the package and actual path to be executable and the evidence to identify runtime/build/database/config. Missing inputs or runner support is `BLOCKED`; a manifest-only example is not executed proof.
