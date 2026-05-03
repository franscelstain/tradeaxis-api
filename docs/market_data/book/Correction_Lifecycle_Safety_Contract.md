# Correction Lifecycle Safety Contract

Status: ENFORCED — pending operator-local targeted and full PHPUnit evidence before LOCKED.

## Scope

This contract owns market-data correction lifecycle safety across baseline resolution, unchanged artifact handling, changed artifact reseal, correction-run-publication-artifact linkage, pointer switch, fallback preservation, evidence export, replay verification, command output, repository persistence, and static regression guards.

## Final Rule

A correction may only publish a replacement when the baseline is resolved from the current readable pointer contract, the baseline publication is `SUCCESS + READABLE + SEALED + coverage PASS`, candidate artifacts are complete and deterministically different from the baseline, reseal is valid, candidate publication linkage is valid, and the post-switch pointer resolver returns the same candidate publication/run/version. If any check fails, the correction must not switch pointer and must preserve the previous current readable publication.

## Baseline Rule

Correction baseline resolution must use `EodPublicationRepository::findCorrectionBaselinePublicationForTradeDate()` and must not use `MAX(trade_date)`, `latest('trade_date')`, `orderByDesc('trade_date')`, raw/staging shortcuts, sealed-only fallback, or latest successful run fallback. The baseline row must be pointer-resolved through `eod_current_publication_pointer`, joined to `eod_publications` and `eod_runs`, and must satisfy `SUCCESS + READABLE + SEALED + coverage PASS` plus run-publication mirror checks.

## Unchanged Artifact Rule

Artifact comparison is mandatory before correction pointer switch. If baseline and candidate batch hashes are identical across bars, indicators, and eligibility, the correction outcome is unchanged: discard the candidate publication, preserve the existing current pointer, do not reseal, do not create a new current version, mark the correction consumed/current-preserved, and expose the unchanged context in events, evidence, replay, and command output.

## Changed / Reseal Rule

A changed correction must have complete non-empty baseline and candidate hashes, deterministic changed scope, valid manifest/hash context, valid candidate publication, valid run-correction-publication linkage, and valid post-switch pointer resolution. Reseal is only valid for changed artifacts. A changed correction cannot become readable until the candidate publication is sealed, current, pointer-resolved, and mirrored by the run.

## Linkage Rule

Correction lifecycle state must preserve these links: correction id to prior/new run id, correction id to baseline/candidate publication id, run id to publication id/version, publication id to artifact hashes/manifest/seal state, current pointer to publication id/version/run id, evidence to correction/run/publication/artifact context, and replay expected/actual state to correction lifecycle context.

## Replay / Evidence Rule

Evidence and replay must carry correction lifecycle fields: correction id/status/outcome/reseal status, baseline publication id/version/run id, candidate publication id/version/run id, publication switch state, final outcome note, changed/unchanged decision, publication seal/current context, run terminal/publishability/coverage context, and mismatch comparison. Replay must fail when expected correction lifecycle state differs from actual state.

## Fail-safe Rule

Invalid correction lifecycle state must fail safe: no pointer switch, no candidate current, no fake success, no readable run without pointer/linkage proof, previous current readable publication preserved where available, candidate remains non-current, explicit reason/final outcome note recorded, and command/evidence/replay show the conflict.
