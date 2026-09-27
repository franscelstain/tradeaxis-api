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

### First independent R0025 fixture

For the first admissible package, freeze the source inputs and derive the expected semantics independently of the target run output. A manual calculation, reviewed spreadsheet/calculation artifact, or separate reference implementation is suitable only with identified method/version and derivation evidence traceable to those inputs. Another `run_id` alone does not establish independence; a copied or relabelled same-target output remains inadmissible. A governed review must cover the authority-relevant expected semantics, input lineage, derivation evidence, oracle identity/version, reviewer, owner approval and package hashes before the package is used for a proof claim. An already executed target may be checked later against the independently approved package; its execution need not follow fixture approval, but the governed verification claim must. The target output may be inspected after independent derivation for comparison and must never supply expected truth. See `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md` section 6A for record boundaries.

## Change rule

A semantic change creates a new fixture/contract version. Do not update expected files merely to make a changed implementation green. The review records why the old oracle was wrong or why the new version intentionally differs.

For an approved package, even a correction of an erroneous expected value creates a new fixture version with renewed derivation, independent review and approval; the old approved package remains immutable.

## Admission

`PASS` requires the package and actual path to be executable and the evidence to identify runtime/build/database/config. Missing inputs or runner support is `BLOCKED`; a manifest-only example is not executed proof.
