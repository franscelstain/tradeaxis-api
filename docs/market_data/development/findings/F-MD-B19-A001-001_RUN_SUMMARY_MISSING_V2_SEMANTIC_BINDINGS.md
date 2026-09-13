# Finding — `F-MD-B19-A001-001`

- ID: `F-MD-B19-A001-001`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Raised at: 2026-09-09T14:00:00+07:00
- Severity: `P2`
- Status: `RESOLVED`
- Class: `ARTIFACT_CONTRACT_NOT_SATISFIED`
- Blocked: the `artifact_run_summary` proof family (52 predicates). Remediated in the attempt that
  raised it.
- Blocks strategy change: `NO`

## Statement

`run_summary.json` did not carry the V2 semantic bindings its own contract requires.

`Run_Artifacts_Format_LOCKED.md` (`MD-S075`) states the minimum shape under *"A conforming summary
should contain at minimum:"*, and its corrected-strategy binding rule requires every artifact to
expose the V2 semantic bindings applicable to its scope — the immutable observation-manifest hash,
the full config snapshot ID/hash, the temporal revision-set identity, the factor-set ID/hash, the
price product, and the formula/registry/read-model versions.

Measured by exporting a run through the real `MarketDataEvidenceExportService` and reading the file
it writes, **12 of the 40 minimum top-level fields were absent**:

```
canonicalization_version, config_snapshot_hash, config_snapshot_id, factor_set_hash, factor_set_id,
formula_version, freshness_state, observation_manifest_hash, price_product_code,
publication_manifest_hash, read_model_version, temporal_revision_set_hash
```

## Remediation

All twelve are now emitted, from the two places that already held them.

**Five were persisted on `eod_runs` and simply not emitted.** `buildRunSummary()` now emits them
under their persisted names, as `MD-S075-R0074` requires of run-summary fields that mirror run
state: `observation_manifest_hash`, `config_snapshot_id`, `factor_set_hash`, `price_product_code`,
`freshness_state`.

**Seven were already assembled by `EodPublicationRepository::buildManifestByPublicationId()`** and
simply not carried across into the summary, although `buildRunSummary()` already receives that
manifest as a parameter: `publication_manifest_hash`, `config_snapshot_hash`, `factor_set_id`,
`read_model_version`, `formula_version`, `canonicalization_version`, and
`temporal_revision_set_hash`.

Nothing is computed in the exporter. A run with no resolved publication exports the manifest-sourced
bindings as `null` rather than as a stand-in, which is what `MD-S075`'s prohibition on inventing
source facts requires.

## Correction to this finding's own first reading

The first version of this record claimed seven fields were unreachable, and said of
`temporal_revision_set_hash` that composing it *"would create a value the platform never recorded"*.

That was wrong. The repository composes exactly that value, from the identity, calendar and status
revision-set hashes, through `DeterministicHashService::hashCanonicalDocument` — a governed
canonical-document hash, not an ad-hoc concatenation. It also derives `canonicalization_version`
from `eod_bars_history` and reads the remaining five straight from `eod_publications` and its
lineage.

The error came from treating two searches as a survey of where a value can come from: a column
search over the schema, and a literal grep of the export service. Neither looks at what a
collaborator the export path already calls computes on the way. A field absent from a column list is
not thereby absent from the system.

## Proof

`B19RunSummaryArtifactContractTest` parses the expected field list from `MD-S075` itself rather than
carrying a copy, runs the real exporter, and reads the file it writes:

- positive — every minimum field the contract names is present;
- negative — a held requested date is not summarised as readable, promoted, pointer-switched, or
  sealed.

Three fail-closed probes, controls green either side: removing a field persisted on `eod_runs`,
removing a field carried from the manifest, and renaming the config snapshot identity. All three
caught.

## Not done

No predicate is bound. `artifact_run_summary` now carries guards and the readiness gate no longer
lists it as unproven, but binding happens once for the whole stage against issued evidence, not
family by family.
