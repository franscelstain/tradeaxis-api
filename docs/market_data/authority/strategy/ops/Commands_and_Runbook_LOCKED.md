# Commands and Operator Surface (STRATEGY LOCKED)

## Rule

Commands are adapters to domain workflows, not alternate authority. They obey the same immutable observations, temporal revisions, config snapshot, validation, seal/pointer, consumer-read, and evidence rules as scheduled execution.

## Required command families

- non-mutating preflight/status showing requested/latest expected/acquired/canonicalized/readable dates;
- daily acquisition-to-publication workflow;
- bounded resumable bootstrap/backfill;
- candidate validation/seal/promotion;
- explicit historical correction/reseal and pointer rollback;
- publication and as-known replay verification;
- evidence export and integrity verification;
- safe retention/purge of eligible non-authoritative artifacts.

Actual command names/options are catalogued in `commands/README.md` and must be generated/verified against registered runtime help. Documentation must not invent a command or imply an option is safe because its name includes `force`.

## Mutating-command controls

Before apply, commands display scope, resolved immutable IDs, expected row/artifact effects, config hash, pointer impact, and reasons. Destructive or externally visible actions require explicit mode/confirmation according to deployment policy and emit auditable evidence.

`--force` may bypass an operator interlock only where its owner policy permits; it never permits content mutation, unverified factor activation, gate bypass, history rewrite, or mixed-publication output.

Price-scale “repair apply,” direct canonical/history update, seal mutation, and direct current-row publication are prohibited surfaces. Detection remains read-only and may create a quarantined candidate/event observation.

## Exit and retry semantics

Exit codes distinguish readable success, held/non-readable completion, invalid input, retryable failure, integrity failure, lock conflict, and blocked proof. Machine-readable output includes full reason sets and run/publication IDs. A zero process exit cannot be the sole readability signal.

Retries preserve the original attempt/observation, create linked attempts where needed, use bounded backoff, and remain idempotent under lock fencing.

## Proof gate

Help rendering, dry-run, invalid input, lock conflict, idempotent retry, held/failed, correction, promotion, replay, evidence, and anti-mutation tests must execute in the supported production runtime. Historical command-matrix proof for superseded semantics is retained as history but does not relock this surface.
