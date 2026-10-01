# Decision — owner approval of D-MD-B10-A002-004 and the semantic-dependency rule for derived hashes

- ID: `D-MD-B10-A002-005`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Work / Baseline: `MD-B10` / `MD-B10-A002` / `MD-B10-A002` / `MD-B10-A002-BL001`
- Finding / CI: `F-MD-B10-A002-002`, `F-MD-B10-A002-004` / `CI-MD-B10-A002-001`
- Reviewed records: `D-MD-B10-A002-004` (issued as a reviewed decision), `E-MD-B18-A002-087` (four corrected B18 perturbation expectations)
- Issued: `2026-10-01T16:39:17+07:00`
- Decision status: `APPROVED` — owner review received in this attempt on 2026-10-01 ("I explicitly APPROVE D-MD-B10-A002-004 with the bounded interpretation already implemented.")
- Strategy impact: `NONE` — no strategy byte changes; freeze `MD-STRATEGY-FREEZE-20260925-001` unchanged
- Governance impact: `NONE` — no `CONTROLLED_REVISION` document changes; no `DOCUMENT_CHANGE_LOG.md` entry

## Decision

### 1. D-MD-B10-A002-004 approved, bounded

`D-MD-B10-A002-004` stays as issued. The owner approves it with the interpretation already implemented:

- The alias exception covers only the immutable, byte-pinned content of `E-MD-B10-A002-001`. It is not a general exception for historical evidence.
- Treating the `si_*` tables as foundation-owned holds only while the governed migration-ownership checks and the reverse-access guard keep proving Shared Security Identity Foundation ownership.

### 2. Rule for the B18 expectation corrections

The owner approves the move of `B18ReplayPersistedEvidenceBindingTest` from the mock world to the persisted production world, subject to one rule:

**A dependent hash may move only because its governed semantic input changes.**

- Registry-version coupling is valid only if the affected hashes bind the same semantic registry capture.
- Configuration-snapshot coupling is valid only when the perturbation changes semantic configuration content or another governed semantic selection input.
- A change to a local `config_snapshot_id`, a database allocation or another volatile identifier with identical semantic content is never a valid reason for a semantic hash to move.

Each of the four corrected expectations must be verified against the real production path, with its exact semantic dependency recorded. An expectation that moves only because of a volatile or local identifier is not approved; the test or the implementation is fixed instead and the fix is reported.

## Application

`E-MD-B18-A002-088` records the verification:

| Expectation | Input that actually changes on the production path | Result |
|---|---|---|
| read-model version | content of the `registry_versions` capture, whose payload hash is the formula and reason registry identity | approved |
| serialization version | same capture content | approved |
| executable build | same capture content | approved |
| configuration snapshot id | only the local `config_snapshot_id` in the producer capture selections; rows and configuration content identical | **not approved** |

For the fourth, the implementation is fixed. Reader projects a semantic payload hash for each verified capture, leaving out the run-scope selection keys `config_snapshot_id`, `publication_id` and `run_id`. The replay verifier builds the temporal and event/factor identities from that hash, and a component without one leaves the identity unavailable. The test expectation is restored to `[config_snapshot_id]`.

The same rule exposes a residual that this decision does not resolve. Captured universe and ancillary content still carries V1 local entity ids, so those identities still move when listings are re-allocated with identical semantic content. It is recorded as `F-MD-B10-A002-004`, owned by MD-B18-A002, and is resolved when B18 replay identities consume the retained foundation roots on the MD-DEP-0020 return path.

## Scope limit

This decision changes no predicate status: B10 stays 1016 SATISFIED / 56 NOT_ASSESSED / 1072, B05 117/117, B18 100/113 reviewed basis, formal 0/113. It does not authorize MD-DEP-0021 controlled deployment. It does not resume R0025 or F-018, and it changes no dependency status.
