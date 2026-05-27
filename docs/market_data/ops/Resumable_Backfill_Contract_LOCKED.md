# Resumable Backfill Contract (LOCKED)

Backfill must be restartable by date range without corrupting canonical output.

## Locked rules
- each date is processed idempotently
- completed dates do not need to be recomputed unless explicitly requested
- historical corrections must create auditable new run artifacts
- partially processed ranges must be resumable from the first incomplete date

---

## Amendment 2026-05-27 - Failed checkpoint recovery apply

When `--resume --only-failed` retries a failed source checkpoint:

- retry still failed -> source state remains blocked, recovered apply is `NOOP`, no derived reprocess, no fake readable
- retry succeeded with rows -> recovered rows must be partial-upserted before the command returns
- retry succeeded with unchanged rows -> recovered apply is `UNCHANGED` and derived reprocess is `NOOP_UNCHANGED_BARS`
- retry succeeded with changed rows -> affected dates are resolved and non-readable affected dates execute indicator/eligibility reprocess

The resume checkpoint identity remains window/ticker scoped. Recovered row apply must not use full-date replacement.

---

## Amendment 2026-05-27 - Publication reprocess after recovered rows

If recovered rows change canonical EOD bars and affected dates are not already readable/current, lifecycle resume may continue past indicator/eligibility execution and call the normal promote path for affected dates. The promote path is required because it owns coverage, hash, seal, finalize, and pointer-readability guards.

If an affected date is already readable/current, resume must not silently update it. It remains blocked with `AFFECTED_PUBLICATION_REQUIRES_CORRECTION` and must be handled by correction/republication lifecycle.
