# Finding - Replay frozen identities depend on local allocation carried by producer captures

- ID: F-MD-B10-A002-004
- Stage / Attempt / Baseline / Epoch: MD-B10 / MD-B10-A002 / MD-B10-A002-BL001 / MD-REBASELINE-20260820-001
- Raised: 2026-10-01T16:39:53+07:00
- Severity: P2 - replay identity determinism across allocation histories
- Status: `PARTIALLY_RESOLVED — RUN_SCOPE_SELECTION_KEYS_REMOVED_E-MD-B18-A002-088; V1_ENTITY_IDS_IN_CAPTURED_CONTENT_REMAIN`
- Class: VOLATILE_IDENTIFIER_IN_SEMANTIC_HASH
- Related: D-MD-B10-A002-005, E-MD-B18-A002-088, E-MD-B18-A002-087, F-MD-B10-A002-002, F-MD-B18-A002-017, F-MD-B18-A002-023, MD-DEP-0020
- Remediation owner: MD-B18 (MD-B18-A002), owner of the replay verification surface; the residual is not resumed by this record

## Observed

`D-MD-B10-A002-005` requires that a dependent hash move only because its governed semantic input changes. Its review of `E-MD-B18-A002-087` ran the real production path. A run bound to configuration snapshot id 7002 instead of 7001, with identical configuration content and identical captured rows, moved `temporal_identity_hash` and `event_factor_hash` as well as the recorded `config_snapshot_id`.

The cause is in how the replay verifier builds those identities. `ReplayVerificationService::componentGroupHash` hashed each bound component's `slot_hash` and `payload_hash`. Both cover the capture's full `selection_context`, and `RunInputCaptureRepository::captureForProducer` puts the run's local `config_snapshot_id` into every producer selection. Some producers also add `publication_id`; the event-factor capture adds `run_id`. A slot is a per-run storage identity (C1 contract: unique per run, stage, component and slot), so these keys belong there. They do not belong in an identity recorded as frozen replay evidence.

Raw dependency records: `diagnostics/dependencies_before_fix.json` and `dependencies_after_fix.json` in `storage/app/market_data/evidence/MD-B10-A002/b18-expectation-semantic-review-20261001-v1`.

## Resolved part — E-MD-B18-A002-088

Reader projects a `semantic_payload_hash` for every verified component: the payload's selection, rows and empty basis, without the run-scope keys in `RunInputCaptureRepository::RUN_SCOPE_SELECTION_KEYS` (`config_snapshot_id`, `publication_id`, `run_id`). The verifier groups `stage_code` and that hash. A component without one leaves the identity unavailable; it never falls back to the allocation-bearing hashes. A re-allocated configuration snapshot, publication or run with identical content now moves no replay identity. Captures, slots, Seal and Binding are unchanged, and historical captures need no rewrite because the hash is computed at read time.

## Residual — open

Captured content still carries V1 local entity ids:

- universe rows name `ticker_id`, `issuer_id`, `instrument_id` and `listing_id`;
- status-expectation selections name `listing_id`.

Two captures that differ only in those ids have different semantic payload hashes (`diagnostics/residual_entity_ids.json`), so `temporal_identity_hash` and the ancillary member of `event_factor_hash` still move when listings are re-allocated with identical semantic content. The as-known path (`AsKnownReplaySnapshotService`) hashes the same universe rows.

Removing them is not a key filter. Listings need a retained identity, and the shared foundation provides one: retained listing roots that MD-B10-A002 already uses for the V2 artifacts. Consuming those roots in replay identities is B18 work on the MD-DEP-0020 return path, after B10 successor closure.

## Affected scope

- Identities: `temporal_identity_hash`; `event_factor_hash` through its ancillary member.
- B18 predicates whose recorded basis uses them: `MD-S050-R0008`, `MD-S050-R0012`, `MD-S050-R0002`, `MD-S050-R0016`, `MD-S019-R0067`, `MD-S019-R0069`, `MD-S019-R0074`, `MD-S003-R0023`, `MD-S004-R0004`.
- Their bound guards pass after the fix with the same test names (`E-MD-B18-A002-088`). This finding does not change their reviewed basis or status. Whether allocation independence across histories is part of each predicate is for the B18 review on the MD-DEP-0020 return path.

## Closure

Resolved when replay frozen identities are built from retained roots and content, and an allocation-history probe shows identical identities for identical semantic inputs.

## 2026-10-02 R0025 impact — E-MD-B18-A002-091

Affected, as a prerequisite of the first independent exact-publication fixture. The family bullet verifies frozen temporal revisions and factors, so an independent expectation of `temporal_identity_hash` or `event_factor_hash` cannot move with allocation, and captured replay content still carries V1 entity ids. The current member does not assert those identities (the bound-input fields are checked for presence only), which is why it was not exposed earlier. The direction the owner is asked to confirm (`E-MD-B18-A002-091`, Q3): retained-root replay identities for V2-profile publications, failing closed (`BLOCKED`) when a listing has no retained identity, V1 interpretation unchanged, no key dropping, no ticker or listing id substitution. Not implemented in this unit: it can only be validated on the allocation-independent world the owner chooses. The finding stays `PARTIALLY_RESOLVED`.
