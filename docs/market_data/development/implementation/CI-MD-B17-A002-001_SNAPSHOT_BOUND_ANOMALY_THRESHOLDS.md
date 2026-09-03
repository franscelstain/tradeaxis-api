# Change Impact Declaration — `MD-B17-A002`

- ID: `CI-MD-B17-A002-001`
- Stage / Attempt / Baseline / Epoch: `MD-B17` / `MD-B17-A002` / `MD-B17-A002-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260903-001`
- Predecessor attempt: `MD-B17-A001` — `PARTIAL_REBASELINE_REQUIRED`; immutable A001 evidence retained, no verdict inherited
- Predecessor stage closure: `SC-MD-B16-A001-001`
- Reviewed strategy decision / controlled revision: `D-MD-B17-A001-001` / `DOC-CHG-20260903-001`
- Authorization/refreeze evidence: `E-MD-B17-A001-002`
- Finding: `F-MD-B17-A001-001` resolved at the authority layer; runtime remediation remains A002 work
- Dependencies: `MD-DEP-0004` discharged at B17 entry; no open blocking dependency
- Status: `COMPLETED — E-MD-B17-A002-001 / E-MD-B17-A002-002 / SC-MD-B17-A002-001`
- Strategy meaning change within A002: `NO`; the separately authorised controlled correction completed before this attempt baseline

## Objective

Revalidate MD-B17 under the successor strategy freeze and close the one withheld semantic predicate
without inheriting A001 proof. Bind all six date-level anomaly thresholds to the immutable run
configuration snapshot, preserve the already-proven non-destructive anomaly measurements and atomic
read-product semantics, and freshly prove the complete 246-predicate denominator.

## Controlled-correction and traceability impact

`DOC-CHG-20260903-001` adds six structural resolved-key rows to `MD-S082`; all are
`REFERENCE_ONLY`. The executable owner remains mandatory `MD-S051-R0070`, so the behavioral
denominator stays **246**. B17 now has 66 primary-stage reference rows, 2
conditional-not-applicable rows behind their existing guards, 1 optional capability, zero
transitional rows, and zero conditional-pending rows.

Before runtime proof, all 246 mandatory predicates must be invalidated to `NOT_ASSESSED` for A002.
The A001 evidence and its 245 bindings remain immutable facts of the predecessor attempt, not
current A002 satisfaction.

## Material executable impact

- **Configuration:** add exactly six typed keys under
  `market_data.quality_gates.date_level_anomaly` with the defaults and environment inputs authorised
  by `D-MD-B17-A001-001`. Keep `.env.example`, `.env.testing`, the locked registry, and the runtime
  config synchronized.
- **Runtime:** run creation must resolve the six values through `config()` into the immutable
  snapshot; `DateLevelAnomalyCheckService` must then read only that run-bound snapshot, report
  `CONFIG_SNAPSHOT_BOUND`, and fail closed on missing, non-numeric, non-finite, out-of-range, or
  internally incoherent values. It must not fall back to current config or declared constants.
- **Snapshot binding:** a promote run's stored configuration snapshot must contain the exact six
  resolved values used by the anomaly evaluation. A current config value that differs from the
  run-bound snapshot must not silently alter historical/replayed evaluation.
- **Pipeline:** preserve the existing promote hook and audit-visible stage-event payload; no import,
  canonical-row, publication-pointer, or consumer-read behavior changes are expected.
- **Schema/migrations/backfill:** none expected. Existing immutable config snapshot storage supports
  the additional keys. No historical snapshot is rewritten and no backfill is authorised.
- **Tests:** convert the A001 honest-deferral guard into positive snapshot-binding proof; add missing,
  wrong-type/range, and snapshot/current-config divergence guards; retain every A001 anomaly,
  alias-retirement, read-product, readiness, pointer and fail-closed guard.
- **Proof tooling:** update the B17 proof spec/gate/binder/self-test from the 245+1 blocked shape to a
  complete 246-predicate A002 shape; the binder must refuse stale A001 evidence and bind all rows only
  to fresh A002 evidence.

## Affected predecessor verification

The additive keys change the exhaustive `MD-S082` configuration population. Fresh proof must cover
at least:

- `MD-S082-R0001` — every output-affecting configuration is bound to a run/publication;
- `MD-S082-R0020` and `MD-S082-R0034` — every applicable quality key is included;
- `MD-S082-R0066` and `MD-S082-R0214` — runtime/config/registry synchronization and rejection of an
  unregistered key;
- `MD-S082-R0220` and `MD-S082-R0223` — exact-once resolution and hash change on semantic change.

Run the current B04 config-registry/snapshot guards under the successor freeze and add a B17 guard
for the exact six-key payload. B04 is not reopened unless current execution exposes a distinct
predecessor defect or a gate requires formal re-entry.

## Raw-artifact storage and admission

Executed proof will be retained under
`storage/app/market-data/evidence/MD-B17-A002/`. A deterministic `MANIFEST.json` will enumerate the
material transcripts and SHA-256 hashes. Governed A002 evidence will bind execution IDs, commands,
environment/database identity, exit states, artifact paths, hashes, and the manifest hash. Historical
A001 artifacts are not current A002 proof and will not be copied forward.

## Compatibility and residue risk

- Preserve the A001 read-product response, pointer-only authority resolution, readiness/freshness
  states, effective-versus-requested dates, and both compatibility alias retirement conditions.
- Configuration defaults must reproduce the prior declared A001 values; an environment override is
  permitted only through typed resolved config captured in the run snapshot.
- An anomaly finding remains evidence only: it does not delete, rewrite, quarantine, or relabel a
  canonical row by itself.
- Search for fallback to constants, direct `env()` reads outside configuration, current-config reads
  during replay, partial six-key snapshots, and tests satisfied merely by key presence.

## Closure boundary

A002 may close B17 only after all 246 mandatory predicates have fresh current proof; both
conditional-not-applicable conditions remain currently proven false; affected B04 invariants pass;
positive and fail-closed mutation proof passes; full suite passes; no harmful residue remains; exact
A002 binding is complete; governed evidence/relationships are issued; documentation, relationship,
classification, applicability, scope-boundary, proof, self-test, and closure gates all pass. `MD-B18`
remains unopened until that closure exists.
