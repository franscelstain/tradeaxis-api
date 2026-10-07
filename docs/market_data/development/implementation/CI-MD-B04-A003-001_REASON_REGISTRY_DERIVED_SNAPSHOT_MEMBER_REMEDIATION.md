# Change Impact Declaration — `MD-B04-A003`

- ID: `CI-MD-B04-A003-001`
- Stage / Attempt / Baseline / Epoch: `MD-B04` / `MD-B04-A003` / `MD-B04-A003-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20261005-001`
- Predecessor: `MD-B04-A002`, closure `SC-MD-B04-A002-001` (`114/114`) — **retained as immutable history, not edited**; its claim is not rewritten as if it had failed
- Remediates: `F-MD-B18-A002-034` (the configuration snapshot carries no reason-registry identity although `MD-S082-R0036` and `MD-S082-R0044` were `SATISFIED`)
- Authority: owner decision `D-MD-B18-A002-018` (Q9 = A1) and impact classification `E-MD-B18-A002-107`, both issued under `MD-B18-A002` before this attempt
- Blocking dependency: `MD-DEP-0023` — blocked logical stage `MD-B18` (`MD-B18-A002`); active remediation stage `MD-B04` attempt `MD-B04-A003`; return-to `MD-B18-A002`
- Status: `CLOSED — REMEDIATION COMPLETE` (`SC-MD-B04-A003-001`)
- Strategy meaning change: `NO`. The owner decision selects how an existing rule is realised; no rule text changes.
- Governance authority change: `NO`. A governed tooling change is declared below (the B04 proof gate and a successor binder).

Issued after `MD-B04-A003-BL001` and before any runtime, test, gate or traceability mutation of this attempt.

## Objective and exact rule impact

Make the configuration-snapshot producer carry the reason-registry semantic identity as a **derived member of every new immutable configuration snapshot** (Q9 = A1), so that the registry version required by `MD-S082-R0036` and `MD-S082-R0044` is actually part of the canonical snapshot content and of its `SHA-256` content hash.

Binding semantics:

- The identity is derived from the semantic content of the reason registry (`D-MD-B18-A002-014` Q4 = A), never from row ids or allocation ids. It is computed once, when a snapshot is created, with the existing projection `ReplayV2IdentityProjection::reasonRegistryIdentity`.
- It is part of the canonical snapshot content, hence of `config_content_hash`. A snapshot is reused only on hash match.
- A historical snapshot without the member is immutable and stays blocked for as-known use. It is never modified and never back-filled.
- An empty, unreadable or malformed registry blocks creation of a new snapshot. No placeholder, constant or "unavailable" marker may stand in for the identity.
- No new registered platform-config key is introduced (the identity is derived, not configured).
- A run bound to a pre-change snapshot does not silently switch: the existing refusal `INPUT_CAPTURE_LIVE_CONFIG_DIVERGENCE` is preserved, tested explicitly, and no run is migrated automatically.

Rules revalidated:

| Scope | Rules |
|---|---|
| REMEDIATE / REPROVE (`NOT_ASSESSED` at entry, scope `E-MD-B04-A003-001`) | `MD-S082-R0036`, `MD-S082-R0044` |
| RE-EXECUTE / RECONFIRM with the member present (kept `SATISFIED`, proof re-run) | `MD-S082-R0006`, `R0007`, `R0009`, `R0011`, `R0214`, `R0220`, `R0221`, `R0223` |

The other `104` mandatory B04 predicates keep their binding; each has a recorded review in the scope evidence. They are not demoted mechanically. A predicate claimed unaffected that fails because of this attempt is new impact evidence, recorded and not forced green.

## Affected surfaces and proof boundary

- Strategy: none changed.
- Runtime (`app/`): `MarketDataConfigSnapshotRepository::currentContent()` gains the derived member (reading `eod_reason_codes` through the existing projection); a fail-closed refusal for an empty or malformed registry. No migration, no new column: the member lives inside the canonical `resolved_config_json`, which already stores the snapshot content.
- Not changed: `AsKnownReplaySnapshotService` and `ReplayVerificationService` (as-known verification is `MD-B18` work and is not implemented here); the V1 profile; sealed history; every historical `md_config_snapshots` row.
- Tests: new focused tests for the member, determinism and order/allocation independence, hash movement on content change, reuse on unchanged content, fail-closed creation, historical immutability, and run-bound refusal; fixtures that create snapshots without a seeded registry are corrected only where they are the production-state fabrication the guard must not allow.
- Governed tooling: `MarketDataConfigFoundationProofGate` is hard-wired to `E-MD-B04-A002-001` for all rows; this attempt re-owns two rows under a later evidence record. The gate gains per-rule expected evidence (A002 by default) and a successor binder rebinds only the two rows. The A002 binder and its existing gate tests stay unchanged and green.
- Backfill/replay: none. No historical snapshot is rewritten, re-hashed or relabelled.
- Operations/config: none.
- Evidence mechanics: a raw proof package under `storage/app/market_data/evidence/MD-B04-A003/`, linked from governed evidence.

## R0025 proof basis (mandatory transition)

Changing the snapshot content semantics changes the executable build and the configuration hash that enter the bars, indicators, eligibility and factor-set hashes, the manifest preimage and the frozen build of the `MD-S003-R0025` candidate-v4 package. At the first implementation mutation the `MD-S003-R0025` proof basis therefore moves `PROVEN → INCOMPLETE` through the governed mechanism (reason: current-build/configuration semantics changed; candidate-v5 is required later). Candidate-v4, `E-MD-B18-A002-100`, `D-MD-B18-A002-016`, `E-MD-B18-A002-101` and `E-MD-B18-A002-105` stay as immutable historical evidence. Formal `SATISFIED` coverage is unaffected. The nine R0025 fixture tests and the R0025 aggregate member are expected to turn red by design after the first mutation; that is recorded and classified, not repaired here.

## Compatibility and residue risk

- A snapshot created before this attempt has no member. It remains valid for the runs it governs, remains immutable and remains unusable for as-known reason-registry verification.
- After this attempt the first run that resolves a snapshot creates a new snapshot (content differs from every historical one); that is a correction of identity, not a fake version.
- `MD-B10` (`1072/1072`): the semantic-hash producers read the stored snapshot hash and content; the 21 covering B10 files are re-run to confirm no impact, and no B10 golden changes without a governed basis.
- `MD-B17` (`244/257`) is a separate bounded work unit; no `MD-B17-A003` is opened here.

## Dependencies and relationships

`MD-DEP-0023` BLOCKING for `MD-B18`. B18 bindings `MD-S050-R0005`, `MD-S050-R0014` and `MD-S019-R0071` are not implemented here; only the dependency is recorded. No candidate-v5 is built here.

## Result (2026-10-07)

The two predicates are `PROVEN_CURRENT` (`E-MD-B04-A003-002`) and promoted through the governed successor binder (`E-MD-B04-A003-003`); `MD-B04` is closed `114/114` under `MD-B04-A003` (`SC-MD-B04-A003-001`). Full MarketData suite on the final tree: 2875 tests, 41075 assertions, 26 failures, all classified in `E-MD-B04-A003-003`. One test fixture that modelled an empty registry as a late manifest defect was re-pointed at the earlier, stricter refusal. No B10 impact; R0025 basis `INCOMPLETE` since `E-MD-B18-A002-108`.
