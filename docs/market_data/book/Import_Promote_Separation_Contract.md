# Import vs Promote Separation Contract

Status: ENFORCED — local PHPUnit validation pending before LOCKED.

Import/ingest is the process of accepting source data into traceable run/candidate context. Promote/publish is the process of making a dataset consumer-readable. These two paths must remain separate even when they share the same requested trade date, source mode, run lineage, or candidate publication context.

## Final rule

`request_mode=import_only` may write imported/canonical/candidate artifact context, source identity, row counts, notes, and run events, but it must not set `publishability_state=READABLE`, must not set `is_current_publication=1`, must not switch `eod_current_publication_pointer`, must not mark a correction as published, and must not imply coverage PASS or final publishability. `request_mode=promote` is the only normal path allowed to move a candidate toward publication/current pointer, and it must pass coverage, hash, seal, finalize decision, run-publication mirror validation, pointer target validation, and post-switch resolver validation.

## Runtime ownership

- `eod_runs.request_mode` is the first-class run intent field.
- `source` / `source_mode` remains source identity, not publish intent.
- `promote_mode` and `publish_target` refine promote behavior but do not replace `request_mode`.
- Evidence and replay must record and compare request mode, import status, promote status, source mode, pointer switch status, and publication state.

## Allowed request modes

- `import_only`
- `promote`
- `full_publish`
- `correction`
- `repair_candidate`
- `replay_verify`
- `evidence_export`

## Import-only side-effect boundary

Allowed:

- create or reuse an owning run context
- persist source identity and source telemetry
- write imported/canonical/candidate artifact context
- create non-current candidate publication context when required by existing artifact design
- append reason-coded events
- return `import_status=COMPLETED` and `promote_status=NOT_PROMOTED`

Forbidden:

- current pointer write
- `READABLE` run state
- `SUCCESS + READABLE` publish-ready state without finalize
- `is_current_publication=1`
- current publication replacement
- correction publish / consumed-for-current marker
- coverage bypass during promote
- raw/staging/latest/MAX(date) read-side bypass

## Promote gate boundary

Promote must be explicit and must validate, in order of runtime proof, source/import context, coverage gate, deterministic hashes, dataset seal, finalize decision, run/publication mirror, pointer target, pointer switch, and post-switch pointer resolver. A failed gate must return HELD/BLOCKED/FAILED/NOT_READABLE with reason code and must preserve the existing current pointer.

## Evidence and replay boundary

Evidence export must show whether a run is import-only or promoted without requiring direct DB inspection. Replay must compare expected vs actual request mode, source mode, import status, promote status, publication state, pointer state, and reason code. Unexpected import promotion must be a replay mismatch, not a silent pass.
