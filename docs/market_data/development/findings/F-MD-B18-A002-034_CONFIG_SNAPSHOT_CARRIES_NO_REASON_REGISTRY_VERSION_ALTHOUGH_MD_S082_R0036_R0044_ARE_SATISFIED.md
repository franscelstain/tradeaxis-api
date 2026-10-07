# Finding — the configuration snapshot carries no reason-registry version although MD-S082-R0036 and MD-S082-R0044 are SATISFIED

- ID: `F-MD-B18-A002-034`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001` (discovered here; the affected rows belong to `MD-B04`)
- Raised: 2026-10-07T09:48:10+07:00
- Severity: `P1` for `MD-S050-R0005`, `MD-S050-R0014`, `MD-S019-R0071` (AS_KNOWN cannot bind a reason-registry version); `P2` for the `MD-B04` closure claim
- Status: `RESOLVED` — the `MD-B04` part by `MD-B04-A003` (`SC-MD-B04-A003-001`) and the `MD-B18` bindings (`MD-S050-R0005`, `MD-S050-R0014`, `MD-S019-R0071`) by `E-MD-B18-A002-110`
- Class: `PROOF_BASIS_VACUOUS_OR_MISTARGETED` (family proof standing for two predicates) and `IMPLEMENTATION_GAP` (the member does not exist)
- Related: `E-MD-B18-A002-106`, `E-MD-B04-A002-001`, `F-MD-B18-A002-017`, `E-MD-B18-A002-071`, `E-MD-B18-A002-073`
- Remediation owner: decided by owner decision Q9 (`E-MD-B18-A002-106`); recommended a successor attempt of `MD-B04`

## Observed

`Platform_Config_Registry_LOCKED.md` (`MD-S082`) requires the immutable configuration snapshot to include every applicable key of its output-affecting families, among them "data-usability decision/reason registry versions" (`:66`, `MD-S082-R0036`) and "contamination horizons and reason-code registry version" (`:79`, `MD-S082-R0044`). Both rows are `SATISFIED` for `MD-B04` on `E-MD-B04-A002-001`. No configuration snapshot carries a reason-registry version: the resolved-key register has no such key, `MarketDataSemanticBindings::snapshot()` has none, and `AsKnownReplaySnapshotService` reads `governance.reason_registry_revision`, which never existed. `MarketDataConfigFoundationProofGate` maps every `MD-S082` row owned by `MD-B04` to the same generic configuration guard pair, so the two rows were bound on a family proof that does not exercise a reason-registry member.

## Consequence

The AS_KNOWN reason-registry identity is unbindable (every as-known replay is `BLOCKED` on `REASON_REGISTRY_IDENTITY_UNAVAILABLE`), which makes `MD-S050-R0005`'s bounded-difference clause unreachable and leaves `MD-S050-R0014` and `MD-S019-R0071` without their AS_KNOWN ingredient. The earlier reading (MD-S085 defines no revision concept, so there is nothing to bind) missed that `MD-S082` places the version in the snapshot.

## Closure

Resolved when the owner decision Q9 is recorded, the governed route it selects makes the configuration snapshot carry the reason-registry identity (or records an authorized alternative), and `MD-S082-R0036` / `MD-S082-R0044` are bound on guards that would fail if the member were absent.

## 2026-10-07 owner decision Q9 = A1 — D-MD-B18-A002-018, E-MD-B18-A002-107

The owner chose the derived configuration-snapshot member (A1). The remediation is an `MD-B04` successor attempt: `MD-S082-R0036` and `MD-S082-R0044` are to be demoted and re-proven on guards that fail when the member is absent; `MD-S082-R0006`, `R0007`, `R0009`, `R0011`, `R0214`, `R0220`, `R0221` and `R0223` are to be re-executed. The change is payload-only (no schema), and existing snapshots stay immutable. The finding stays `OPEN`.

## 2026-10-07 MD-B04 part resolved — E-MD-B04-A003-003

`MD-B04-A003` made the configuration snapshot carry the reason-registry identity as a derived member (`E-MD-B04-A003-002`), re-proved `MD-S082-R0036` and `MD-S082-R0044`, re-confirmed eight related rows and closed `MD-B04` at 114/114 (`SC-MD-B04-A003-001`). Historical snapshots remain immutable and without the member. Remaining under this finding: the `MD-B18` as-known binding and proof; candidate-v5 later.

## 2026-10-07 MD-B18 part resolved — E-MD-B18-A002-110

As-known replay binds the reason-registry identity of the configuration snapshot resolved at the cutoff; the live-configuration guard was examined and kept for execution (`E-MD-B18-A002-109`, verdict A). The three predicates are `PROVEN` with discriminating probes. The finding is resolved.
