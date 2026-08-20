# Resumable Backfill Contract (LOCKED)

Backfill must be restartable by date range without corrupting canonical output.

## V2 immutable-observation / candidate-projection boundary (LOCKED)

Every retry/refetch first appends a new immutable source observation/acquisition attempt. The term **partial-upsert** retained in the historical amendment below applies only to an **unsealed mutable candidate/workspace projection** used to assemble a publication candidate; it never means overwriting the immutable observation, a sealed publication/history snapshot, or a revision already bound to readable output. Target row identity is stable `listing_id`; legacy ticker/date keys are compatibility projection only.

If a selected observation changes for any already-readable date, correction/republication is mandatory. If a not-yet-readable candidate is rebuilt, the replacement must retain old/new observation lineage so the candidate remains reproducible.

## Locked rules
- each date is processed idempotently
- completed dates do not need to be recomputed unless explicitly requested
- historical corrections must create auditable new run artifacts
- partially processed ranges must be resumable from the first incomplete date

---

## Amendment 2026-05-27 - Failed checkpoint recovery apply

When `--resume --only-failed` retries a failed source checkpoint:

- retry still failed -> source state remains blocked, recovered apply is `NOOP`, no derived reprocess, no fake readable
- retry succeeded with rows -> append the recovered source observation, then merge it into the **unsealed candidate projection** before the command returns; historical wording calls this a partial-upsert
- retry succeeded with unchanged rows -> recovered apply is `UNCHANGED` and derived reprocess is `NOOP_UNCHANGED_BARS`
- retry succeeded with changed rows -> affected dates are resolved and non-readable affected dates execute indicator/eligibility reprocess

The resume checkpoint identity remains window/ticker scoped. Recovered candidate-projection merge must not use full-date replacement and must key stable `listing_id`; it must never mutate immutable observation or sealed/history content.

---

## Amendment 2026-05-27 - Publication reprocess after recovered rows

If recovered rows change canonical EOD bars and affected dates are not already readable/current, lifecycle resume may continue past indicator/eligibility execution and call the normal promote path for affected dates. The promote path is required because it owns coverage, hash, seal, finalize, and pointer-readability guards.

If an affected date is already readable/current, resume must not silently update it. It remains blocked with `AFFECTED_PUBLICATION_REQUIRES_CORRECTION` and must be handled by correction/republication lifecycle.
