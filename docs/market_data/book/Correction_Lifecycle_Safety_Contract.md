# Correction Lifecycle Safety Contract

Status: LOCKED for the 2026-05-20 correction lifecycle hardening scope. Request baseline proof, unchanged publication-switch semantics, failed-correction pointer preservation, evidence linkage, replay MATCH linkage, and repair reason guards are enforced and validated by runtime proof.

## Scope

This contract owns market-data correction lifecycle safety across baseline resolution, request/approval/execution eligibility, unchanged artifact handling, changed artifact reseal, correction-run-publication-artifact linkage, pointer switch, fallback preservation, evidence export, replay verification, command output, repository persistence, and static regression guards.

## Final Rule

A correction may only publish a replacement when the baseline is resolved from the current readable pointer contract, the baseline publication is `SUCCESS + READABLE + SEALED + coverage PASS`, candidate artifacts are complete and deterministically different from the baseline, reseal is valid, candidate publication linkage is valid, and the post-switch pointer resolver returns the same candidate publication/run/version. If any check fails, the correction must not switch pointer and must preserve the previous current readable publication.

## Request And Baseline Rule

Correction baseline resolution must use `EodPublicationRepository::findCorrectionBaselinePublicationForTradeDate()` and must not use `MAX(trade_date)`, `latest('trade_date')`, `orderByDesc('trade_date')`, raw/staging shortcuts, sealed-only fallback, or latest successful run fallback. The baseline row must be pointer-resolved through `eod_current_publication_pointer`, joined to `eod_publications` and `eod_runs`, and must satisfy `SUCCESS + READABLE + SEALED + coverage PASS` plus run-publication mirror checks.

Correction request commands must resolve this same baseline before creating a request. If no baseline exists for the target trade date, the request command must stop with `CORRECTION_BASELINE_LINK_MISSING` and must not create a correction row. Repository-level callers may pass explicit baseline context for tests or controlled internal flows, but operator-created correction requests are baseline-gated.

## Approval And Execution Rule

A correction request is not executable until it is approved. Execution must reject missing, mismatched, terminal, consumed, unapproved, or mode-ineligible correction rows. Pipeline execution must re-resolve the baseline at runtime before creating or using a candidate publication.

## Unchanged Artifact Rule

Artifact comparison is mandatory before correction pointer switch. If baseline and candidate batch hashes are identical across bars, indicators, and eligibility, the correction outcome is unchanged: discard the candidate publication, preserve the existing current pointer, do not reseal, do not create a new current version, mark the correction consumed/current-preserved, render `candidate_publication_switch=false`, and expose the unchanged context in events, evidence, replay, and command output.

## Changed / Reseal Rule

A changed correction must have complete non-empty baseline and candidate hashes, deterministic changed scope, valid manifest/hash context, valid candidate publication, valid run-correction-publication linkage, and valid post-switch pointer resolution. Reseal is only valid for changed artifacts. A changed correction cannot become readable until the candidate publication is sealed, current, pointer-resolved, and mirrored by the run.

## Linkage Rule

Correction lifecycle state must preserve these links: correction id to prior/new run id, correction id to baseline/candidate publication id, run id to publication id/version, publication id to artifact hashes/manifest/seal state, current pointer to publication id/version/run id, evidence to correction/run/publication/artifact context, and replay expected/actual state to correction lifecycle context.

## Replay / Evidence Rule

Evidence and replay must carry correction lifecycle fields: correction id/status/outcome/reseal status, baseline publication id/version/run id, candidate publication id/version/run id when applicable, publication switch state, final outcome note, changed/unchanged decision, publication seal/current context, run terminal/publishability/coverage context, and mismatch comparison. Unchanged correction replay must resolve the preserved baseline publication even when the correction run does not own that publication, and must record preserved-baseline lineage rather than treating the discarded candidate as current. Replay must fail or block with a registered reason when expected correction lifecycle state cannot be compared to actual state.

## Repair / Force Guard Rule

Force replacement and current-publication repair are operator-intent paths. `market-data:promote --force_replace=true` must include `--force_replace_reason` or `--force_reason`; `market-data:current-publication:repair --apply` must include `--reason` or `--force_reason`. Repair output must show the reason, pointer before, pointer after, operation mode, and registered command reason code.

## Fail-safe Rule

Invalid correction lifecycle state must fail safe: no pointer switch, no candidate current, no fake success, no readable run without pointer/linkage proof, previous current readable publication preserved where available, candidate remains non-current, explicit reason/final outcome note recorded, and command/evidence/replay show the conflict.

## Locked Runtime Proof

- Unchanged correction proof: `correction_id=3`, run `8`, baseline publication `5` / run `6`, discarded candidate publication `7`, `candidate_publication_switch=false`, replay `10` `MATCH` / `PASS`.
- Failed correction proof: `correction_id=4`, candidate run `11`, status `FAILED`, failure reason `RUN_SOURCE_MANUAL_FILE_NOT_FOUND`, no replacement publication, baseline pointer publication `5` preserved.

---

## Amendment 2026-05-26 - Affected readable publication impact

Historical EOD bar mutations can affect later rolling indicators, eligibility, hashes, seals, and publication proof.

If mutation impact resolution finds that an affected downstream date already has a current readable publication, the system must not update that publication's live artifacts silently. It must expose the impact as `publication_impact_state=REQUIRES_REPUBLICATION` with reason `AFFECTED_PUBLICATION_REQUIRES_CORRECTION`, then require the correction/reseal/republication lifecycle before any pointer switch or consumer-visible replacement.

If recomputed derived artifacts are unchanged, the correction lifecycle may preserve the current publication according to the unchanged-artifact rule. If they changed, the changed/reseal rule applies.

---

## Amendment 2026-05-27 - Safe block during impact execution

Affected-date execution may recompute indicators and eligibility only for dates that are not already current readable publications. If an affected date is readable, execution must stop for that date and emit:

- `publication_reprocess_summary.execution_state=BLOCKED_REQUIRES_CORRECTION`
- `blocked_reason_code=AFFECTED_PUBLICATION_REQUIRES_CORRECTION`
- affected blocked trade dates

This blocked state is a valid safety outcome, not a publication success. It must not switch pointers, mutate readable live artifacts, export replacement replay proof, or claim automatic republication.

---

## Amendment 2026-05-27 - Automated impact correction for already-readable affected dates

Out-of-order import impact reprocess may automatically orchestrate correction for an affected downstream date that is already current/readable, but only through the existing correction-current lifecycle.

Required rules:

- Resolve the baseline via `findCorrectionBaselinePublicationForTradeDate()`.
- Create a correction request with reason `AFFECTED_PUBLICATION_REQUIRES_CORRECTION`.
- Approve the correction before execution.
- Promote through correction-current mode, not normal full-publish.
- Preserve baseline lineage and apply all existing coverage, hash, seal, finalize, pointer, evidence, and replay guards.
- If baseline resolution, correction approval, promotion, or pointer validation fails, report the failure and do not fake readable/current state.

This amendment upgrades the previous safe-block-only impact behavior into an automated correction orchestration path while preserving all correction lifecycle safety requirements.


---

## Amendment 2026-05-27 - Final lock for automated impact correction

The automated impact correction path for already-readable affected dates is now validated by targeted and full MarketData PHPUnit proof:

- `BackfillLifecyclePublicationReprocess` -> OK (3 tests, 12 assertions).
- `OutOfOrderImportImpact` -> OK (7 tests, 96 assertions).
- Full MarketData suite -> OK (582 tests, 8678 assertions).

This confirms that out-of-order import publication reprocess may automate correction only by creating/approving a correction request and promoting through correction-current mode. The existing correction lifecycle remains the authority for baseline resolution, candidate validation, seal/finalize, current-pointer switching, evidence, and replay proof.

The following remains forbidden:

- replacing an already-readable affected date through normal full-publish;
- switching current pointer without correction lineage;
- claiming readable/current state when correction-current promotion or pointer validation fails.
