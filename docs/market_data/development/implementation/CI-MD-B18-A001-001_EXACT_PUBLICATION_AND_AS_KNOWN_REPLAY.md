# Change Impact Declaration — `MD-B18-A001`

- ID: `CI-MD-B18-A001-001`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A001` / `MD-B18-A001-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260903-001`
- Predecessor stage closure: `SC-MD-B17-A002-001`
- Dependencies: `MD-DEP-0004` entry portion discharged by B18 normalization; global dependency remains `OPEN_NON_BLOCKING`
- Status: `IN_PROGRESS — R2 FULL-SUITE REMEDIATION COMPLETE; CORRECTED LOCAL RUNTIME PROOF PENDING`
- Strategy meaning change: `NO`
- Governance authority change: `NO`

## Objective

Revalidate and complete the current exact-publication and as-known replay contract without inheriting
historical replay success. `PUBLICATION_EXACT` must bind an explicit immutable publication and never
fall back to current/latest state. `AS_KNOWN` must bind an explicit knowledge cutoff and reconstruct
only inputs knowable at that cutoff. Both modes must retain deterministic bound-input identity,
independent-oracle admission, fail-closed outcomes, and execution-correlated evidence.

## Affected strategy predicates

The normalized B18 denominator is **121**: 117 `MANDATORY` plus 4
`CONDITIONAL_APPLICABLE`. It spans current predicates owned by `MD-S002`, `MD-S003`, `MD-S004`,
`MD-S005`, `MD-S019`, `MD-S020`, `MD-S036`, `MD-S040`, `MD-S041`, `MD-S050`, `MD-S055`,
`MD-S058`, `MD-S065`, `MD-S082`, and `MD-S085`. Two optional capabilities remain outside the
required denominator and 32 reviewed rows remain `REFERENCE_ONLY`. No B17 predicate verdict is
inherited.

The four conditional-applicable predicates remain applicable while only one integrated replay mode
would otherwise exist. They may move to `CONDITIONAL_NOT_APPLICABLE` only after the integrated
`AS_KNOWN` path is proven by current runtime evidence; static implementation presence is insufficient.

## Material executable impact

### Replay contract and admission

- Add first-class replay modes `PUBLICATION_EXACT` and `AS_KNOWN`.
- Reject unknown or missing mode at the operator/service boundary.
- `PUBLICATION_EXACT` requires an explicit immutable publication identifier and must not resolve
  `current`, `latest`, or a mutable pointer as substitute proof input.
- `AS_KNOWN` requires an explicit immutable knowledge cutoff and must not impersonate a historical
  publication.
- A fixture generated from the same run is diagnostic only and `NOT_ADMISSIBLE` as positive proof.
  Positive replay proof requires an independently supplied fixture/oracle lineage.

### Temporal and source-input reconstruction

- Temporal listing/symbol/board/provider mapping reads are cutoff-aware for both creation and later
  retraction. A fact retracted after the cutoff remains visible when reconstructing the earlier state.
- Indicator, eligibility, ingest, adjustment-factor, and publication-governance paths use the run's
  immutable knowledge cutoff rather than `started_at`, `created_at`, or current state.
- Source-observation replay binds the immutable observations and normalized rows knowable at the
  cutoff, including provider-outage observations; current universe changes must not erase historical
  observations.
- `AS_KNOWN` does not stop at metadata reconstruction: it executes the production canonical RAW
  ingest/canonicalization path from those immutable rows inside an isolated outer transaction,
  compares deterministic canonical output assertions against an independent fixture, and rolls the
  transient run/publication/projection mutation back before durable replay-result persistence. The
  assertion scope is explicit `CANONICAL_RAW`; this stage does not mislabel that isolated replay as a
  sealed historical publication.
- Calendar/status, corporate-action/factor, source-scale, config, formula/reason, serialization, and
  read-model identities are carried as deterministic replay bound inputs.

### Schema and persistence

- Add an additive replay-v2 migration for first-class mode/cutoff, publication identity, fixture
  manifest, source-observation manifest, canonical RAW input hash, temporal/calendar/event-factor/
  config/formula/read-model identities, executable build identity, admission state, and bound-input
  context.
- Keep the MariaDB development schema and SQLite test mirror synchronized with the additive migration.
- Historical replay rows are not backfilled with invented mode identity. Unclassified historical rows
  are therefore not admissible as current B18 proof.

### Operator/backfill behavior

- Full-range replay, replay backfill, replay smoke, lifecycle backfill, and missing-ticker lifecycle
  positive proof paths require independent fixture input where replay proof is requested.
- No lifecycle/backfill path may generate expected output from the run under test and then call that a
  positive replay result.
- Diagnostic fixture generation remains available only so the system can prove the self-generated
  oracle is rejected.

## Tests and proof-tool impact

- Reconcile existing replay tests only where their expectation represented the obsolete unmoded or
  self-generated-oracle contract.
- Add direct regression coverage for first-class mode admission, exact-publication binding,
  AS_KNOWN cutoff isolation, post-cutoff retraction, immutable source-observation visibility,
  provider-outage retention, replay-v2 persistence, and evidence completeness.
- Add B18 static contract guard coverage plus a complete 121-predicate semantic proof map.
- The pre-proof gate must require all 121 current rows to remain `NOT_ASSESSED` with no current B18
  evidence. The binder must refuse to bind until fresh current-attempt governed runtime evidence exists.
- Mutation/self-test must fail on denominator loss, premature binding, duplicate/orphan mapping,
  missing family, and wrong proof ownership.

## Evidence and raw-artifact impact

Fresh local execution proof belongs under:

`storage/app/market-data/evidence/MD-B18-A001/`

Only material transcripts/manifests referenced by the eventual governed evidence are proof-bearing.
The runtime package must capture exact commands, exit state, environment/database identity, execution
or run/publication IDs, replay mode/cutoff, independent fixture identity, result/admission state, and
SHA-256 linkage. No historical B18-like artifact or B17 artifact is inherited as current B18 proof.

## Compatibility and residue risk

- Existing replay diagnostic capability may remain, but must not be mistaken for an independent oracle.
- Existing historical replay rows remain readable where legacy compatibility requires it, but lack of
  first-class mode/bound-input identity prevents their admission as B18 proof.
- Current publication pointer behavior for normal consumers is not changed by this stage.
- No strategy semantics are revised. A discovered authority contradiction would stop the attempt rather
  than be resolved by implementation reinterpretation.
- Main residue risks are current/latest fallback, survivorship through later retraction, same-run oracle
  leakage, partial bound-input persistence/export, and operator paths that bypass mode/fixture admission.

## Closure boundary

`MD-B18-A001` remains `IN_PROGRESS / PARTIAL` until fresh local runtime proof validates both replay
modes, negative/fail-closed behavior, deployed schema, affected integration paths, complete evidence
admission, and final regression. Only then may current evidence be issued, the binder promote the 121
required predicates, post-binding controls run, and stage closure be evaluated. `MD-B19` must remain
unopened until legitimate B18 closure.


## Returned local proof and R2 remediation

The first local proof package established two current-attempt facts that remain valid:

- `B18-LP-001` — PASS on local PHP 7.4.33: B18 normalization `117 + 4 = 121`, proof-readiness `121/121`, mutation self-test and classification consistency all exited zero.
- `B18-LP-002` — PASS: migration `2026_09_04_000001_add_replay_v2_bound_input_context` applied successfully and appeared applied in `migrate:status`.

The R1 proof cycle then returned a genuine regression result. `B18-LP-004` executed the full PHPUnit suite and returned **2040 tests / 20258 assertions / 37 errors / 13 failures / exit 2**. That failure is retained as execution evidence and is not reinterpreted as an attempt boundary. Root-cause grouping separated executable defects from stale predecessor fixtures/expectations and proof-tool defects.

R2 remediation preserves the B18 authority invariants rather than weakening them:

- immutable `knowledge_cutoff_at` remains mandatory; predecessor fixtures that omitted it are corrected instead of restoring `started_at`/`created_at` fallback;
- exact publication remains explicit and immutable; affected replay tests provide the explicit selector instead of restoring current/latest fallback;
- AS_KNOWN replay artifact reads use the persistence gateway, the canonicalizer fixture carries a valid CAPTURED → ACCEPTED source-observation lineage, and pure-unit config reads are fail-safe without requiring a Lumen config binding;
- same-run/self-generated fixture families remain inadmissible, including the legacy `runtime_generated_valid_case` label;
- lifecycle independent-fixture paths are platform-neutral on Windows/Linux;
- replay result optional-null persistence remains guarded against erasing stored values;
- emitted replay reasons reuse registered canonical reason codes; informational AS_KNOWN run creation no longer invents an unregistered reason code;
- stale evidence-export expectations retain historical unmoded replay as `ADMITTED_INCOMPLETE`; and
- B18 temporal/static guards are updated for the new cutoff-aware source-observation roots and PHP 7-compatible literal checks.

R2 does **not** alter the normalized denominator, proof ownership, strategy semantics, migration shape already proven by LP-002, or the immutable Baseline Lock. `B18-LP-001` and `B18-LP-002` therefore remain valid. Corrected runtime proof must execute `B18-LP-003-R2` first and a fresh full suite only after that targeted proof passes. No B18 predicate is bound before returned corrected proof is verified.
