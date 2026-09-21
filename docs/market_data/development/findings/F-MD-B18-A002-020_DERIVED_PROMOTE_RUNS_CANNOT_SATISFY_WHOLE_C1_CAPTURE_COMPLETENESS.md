# Finding — derived promote runs (repair_candidate/incremental) cannot satisfy whole-C1 capture completeness

- ID: `F-MD-B18-A002-020`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-09-21T07:10:00+07:00
- Severity: `P1` — an active regression against LOCKED strategy authority (§ Authority/regression
  audit below), not merely an internal implementation gap
- Status: `OPEN — LOCKED_AUTHORITY_REGRESSION_CONFIRMED_CAPTURE_COMPLETENESS_INCOMPLETE_NOT_A_NEW_DECISION`
- Discovered during: wiring `PublicationInputBindingService::bind()` into
  `MarketDataPipelineService::completeHash()` (Binding V2 pipeline/orchestration integration,
  E035's `IMPLEMENTATION_DERIVABLE` finding, executed E038)
- Audited (no code changed): authority/regression audit below, E-MD-B18-A002-039

## Observed defect

`MarketDataPipelineService::promoteSingleDay()` derives a brand-new run row from a seed run via
`EodRunRepository::createPromoteRunFromSeed()` (`MarketDataPipelineService.php:2321`,
`EodRunRepository.php:238`) for `repair_candidate`/`incremental` promote flows. The derived run
gets its own `run_id` and re-enters the stage sequence starting at `COMPUTE_INDICATORS`
(`MarketDataPipelineService.php:2354`) — it never re-executes `INGEST_BARS`/`ACQUISITION`, because
its whole purpose is to recompute indicators/eligibility/hash over canonical bars a *different*,
earlier run already ingested.

`RunInputCaptureRepository::forRun()` scopes every C1 producer-bound input capture strictly by
`run_id`. Since the derived run never runs `INGEST_BARS`/`ACQUISITION`, it never captures
`provider_mapping.provider-mapping-revisions/v1`, `provider_mapping.provider-source-row-link/v1`,
or `source_observations.source-observation-outcomes/v1` under its own `run_id` — those captures
exist only under the *seed* run's `run_id`, which nothing currently reads.

Now that `PublicationInputBindingService::bind()` is wired into `completeHash()` on every pipeline
run (this evidence turn), `ProducerInputCompletionManifest::inspect()` correctly reports these
components missing for every derived promote run, and `completeHash()` fails closed with
`INPUT_CAPTURE_BINDING_MANIFEST_INCOMPLETE`. This is not a fixture defect and not something a test
assertion tweak can honestly resolve: it is a genuine architectural gap between C1's per-run
capture scoping and the pre-existing derived-run promote design, surfaced for the first time by
wiring Binding into the live call path (the manifest check itself has always been able to report
this; nothing enforced it automatically before this turn).

Reproduced directly by
`MarketDataPipelineIntegrationTest::test_promote_single_day_repair_candidate_first_execution_marks_metadata_and_keeps_current_publication_non_current`
and
`MarketDataPipelineIntegrationTest::test_promote_single_day_repair_candidate_rerun_increments_execution_count_and_preserves_current_pointer`,
both of which exercised a real `repair_candidate`/`incremental` promote end-to-end before this
turn and now fail closed at `HASH` with the exact message above.

## Why this is not resolved in this evidence turn

The user's authorization for this turn was scoped to wiring the already-implemented
`PublicationInputBindingService` into `completeHash()` and adding a precondition (never a
computation) to `sealCandidatePublication()` — explicitly *not* Seal semantics, and explicitly not
a license to invent new approval requirements or patch without existing authority. Closing this
gap requires a real design decision this session has no authority to make unilaterally, for
example (not a recommendation, an enumeration of the shape of the decision):

1. Derived promote runs record their seed `run_id` and capture completeness is checked against the
   *union* of the derived run's own captures and its seed run's captures for the components a
   derived run structurally cannot produce itself; or
2. Binding V2 is deliberately out of scope for non-`full_publish` promote modes for now, with an
   explicit contract statement to that effect (the C1 contract as currently written names no such
   exemption — `grep` for `repair_candidate`/`promote_mode`/`request_mode` in
   `MD_B18_A002_C1_PRODUCER_BOUND_INPUT_CONTRACT.md` returns nothing); **corrected below — this
   search missed the contract's actual reference, which names the underlying repository method
   rather than the promote-mode string; see § Authority/regression audit**; or
3. Some other mechanism entirely.

None of these has existing-authority basis today; inventing one here would be exactly the kind of
undocumented scope expansion this attempt has repeatedly been corrected away from.

## Disposition of the two affected tests

Both are marked `markTestSkipped()`, referencing this finding by ID, rather than either weakening
the assertions to expect failure (misrepresenting an undecided question as a settled one) or
silently deleting/rewriting them to hide the regression. Neither test's original narrative
(a successful repair-candidate promotion) is currently reachable; the underlying `promoteSingleDay`
production code path is unchanged from before this finding and remains available to fix once a
decision is made.

## Scope confirmation

The `full_publish`/`manual_file` mainline flow this evidence turn's eight proof points target is
unaffected: `MarketDataStageInput`'s `request_mode` for that flow is `full_publish` throughout
(`runSingleDay`/`importSingleDay`+`runPipelineThroughHashUnsealed`), and every stage — including
`INGEST_BARS`/`ACQUISITION` — runs under the one owning `run_id`, so whole-C1 capture completeness
(proven E031–E033) is unaffected. This finding is scoped precisely to derived/seed-based promote
runs (`repair_candidate`, `incremental`), a narrower and separate code path.

## Authority/regression audit (E-MD-B18-A002-039, no code changed)

This section corrects the framing above after re-searching authority the first pass missed, per an
explicit user audit request. Nothing below changes production code, tests, or predicate/coverage
numbers; it reclassifies what F-020 *is* against the authority that actually governs it.

### `repair_candidate`/`incremental` are not a fringe or legacy-only concept — they are LOCKED

Two frozen strategy documents, both outranking the C1 contract itself (which is explicitly marked
`Role: IMPLEMENTATION_GUIDANCE, MUTABLE_UNTIL_CLOSURE; no strategy authority`), name this scenario
directly:

- `Import_Promote_Separation_Contract.md` §"Allowed request modes": `correction_candidate` is a
  first-class allowed request mode; `` `repair_candidate` may be accepted only for backward
  compatibility and must never authorize in-place repair `` — i.e. the *function* (a non-current
  correction candidate) is required; only the *legacy name* is a compatibility alias, and only
  in-place repair (not the candidate mechanism itself) is forbidden.
- `Finalize_Lock_And_Pointer_Behavior_LOCKED.md` §3 Lock Behavior Contract, mapping table: "
  Correction candidate / non-current publish target (legacy runtime may label this
  `repair_candidate`) | `SUCCESS` or `HELD` according to existing policy | `NOT_READABLE` unless
  existing current is preserved | Current pointer preserved". This is a LOCKED requirement that
  this scenario be able to reach `SUCCESS` (via the normal seal path) or `HELD`, not "always blocked."

`incremental` is not a separately-authorized concept at all — it is a literal internal alias for
`repair_candidate`: `MarketDataPipelineService::resolvePromoteContext()` maps
`'incremental' => 'repair_candidate'` (`MarketDataPipelineService.php:3436`), and
`PromoteMarketDataCommand.php:147` does the same at the operator/command layer. Every governance
question about one applies identically to the other; there are not two lifecycles here, one.

### The C1 contract already names this entry path in scope — the first pass's grep missed it

The original disposition above searched for the literal strings `repair_candidate`/`promote_mode`/
`request_mode` in the C1 contract and found nothing, and concluded no exemption *or* requirement was
stated either way. That search was too narrow: the contract names the *producer/repository surface*,
not the promote-mode string. `MD_B18_A002_C1_PRODUCER_BOUND_INPUT_CONTRACT.md` §5 (C01 row) lists
`EodRunRepository::getOrCreateOwningRun, createAsKnownReplayRun, createPromoteRunFromSeed` as a
producer surface C1 must capture, with the explicit note "A seed/promote run cannot silently inherit
one snapshot while consuming another." The same §5, "Alternate entry paths and operation inverses"
(a cross-cutting list applying to *every* C01–C12 slot, not just C01), states: "The implementation
slice must account for all of these before its coverage claim: ... import-only to promote through
`createPromoteRunFromSeed` ..." — `createPromoteRunFromSeed` is the exact method
`promoteSingleDay()` calls to derive a `repair_candidate`/`incremental` run
(`MarketDataPipelineService.php:2321`, `EodRunRepository.php:238`). The same section also states the
correct interim behavior directly: "Existing flows without complete producer evidence fail closed."

Neither D-MD-B18-A002-005 nor CI-MD-B18-A002-001 nor the consolidated remediation package mention
`repair_candidate`/`incremental`/`createPromoteRunFromSeed` anywhere (confirmed by direct grep of
all three); the C1 contract is the only, and a sufficient, source naming this entry path in scope.

### Reclassification

None of the four offered categories fits alone; the accurate statement combines three of them:

- **Not** an implementation defect in Binding orchestration itself — `completeHash`'s wiring and
  `sealCandidatePublication`'s precondition behave exactly as specified and as proven for every
  entry path whole-C1 capture actually covers.
- **Is** authorized fail-closed behavior in the narrow, correct sense that refusing to bind/seal
  without complete producer evidence is exactly what C1 §5 requires *while the gap is open* — this
  is not a bug to route around.
- **Is** an explicit, already-named dependency/blocker: C1 §5's "alternate entry paths" list already
  put `createPromoteRunFromSeed` in scope for whole-C1 capture completeness, and E031–E033's
  "whole-C1 completeness" proof — accurate for what it tested — never actually exercised this named
  entry path (`runPipelineThroughHashUnsealed`/`runDaily` only ever drove the normal
  `INGEST_BARS`-through-`HASH` sequence under one `run_id`). The gap therefore **predates E038** and
  is a leftover incompleteness in the same C02–C09 capture work the contract already authorized and
  scoped, not a new problem E038 introduced. E038 did not create this gap; it was the first thing to
  make the pre-existing gap observable, by making the manifest check load-bearing on every real run.
- **Is not** a genuinely unresolved semantic decision about *whether* `repair_candidate`/`incremental`
  should keep working — LOCKED authority already answers that: yes, it must be able to reach
  `SUCCESS`/`HELD`. What remains open is purely the *mechanism* (capture carry-forward from the seed
  run, independent re-capture on the derived run, or something else) — an implementation question,
  not an approval question, and one this audit turn still does not decide or implement.

**Net effect on E-MD-B18-A002-038's own claim:** E038 stated "Binding (C1 contract §6 step 2) is now
fully complete." That is corrected, not retracted in substance: the orchestration *mechanism* E038
built (the `completeHash` call site and the `sealCandidatePublication` precondition) is complete and
correct. But C1 §5's own coverage-claim condition ("must account for all of these before its coverage
claim") was not met — the `createPromoteRunFromSeed` entry path was never proven, and is now proven
*not* to satisfy whole-C1 capture completeness. "Binding fully complete" therefore overstated scope;
the accurate claim is "Binding orchestration complete for every entry path whole-C1 capture already
covers (`full_publish`/`manual_file`); incomplete against C1's own full entry-path list."

### Are the two `markTestSkipped()` calls legitimate closure? No.

`markTestSkipped()` was used to mean "blocked pending a decision this attempt has no authority to
make." That premise is now shown to be wrong on both halves: there is no undecided *whether*
(LOCKED authority already requires it to work), and the current fail-closed behavior is not an
open question either (C1 §5 already requires it while the gap stands). SKIP therefore did not record
an authorized, understood interim state — it suppressed visibility of an active regression against
LOCKED strategy authority, which is exactly what this project's evidence discipline exists to
prevent. The correct disposition (not performed in this audit-only turn, since the user's instruction
for this turn is explicitly audit/evidence-only, no new coding) is to replace both skips with
assertions that positively prove the current, correct, contractually-required fail-closed outcome —
the same treatment already given to
`test_binding_rejects_an_incomplete_capture_manifest` earlier in E038 — so the suite documents the
known-incomplete state instead of hiding it, until whole-C1 capture is actually extended to cover
`createPromoteRunFromSeed`.

### SQLite canonical reason-code seed — authority classification

Audited separately (full detail in E-MD-B18-A002-039): classified as a legitimate test-fixture
change, not a new/second authority and not merely convenient. E032 (SQLite-only audit) explicitly
declined to seed the SQLite mirror at the time, stating doing so "has no existing C1 capture/manifest
authority behind it and was out of this audit's scope" — correct *then*, because Binding was not yet
wired into any live call path, so an empty `eod_reason_codes` in the disposable SQLite mirror was
cosmetic. Once E038 wired Binding into `completeHash` (already authorized by E035 under D005/CI-001),
the same empty fixture stopped being cosmetic and became a blocking regression across nearly the
entire pre-existing SQLite-backed suite — a necessary, derived consequence of an already-authorized
change, not an independent new decision. The C1 contract's own §4.3 anticipates exactly this: "SQLite
mirror receives the required fields and behavioral fixture data" for C1 verification. Content
provenance was independently re-verified this audit turn field-by-field (`code`, `category`,
`description`, `severity`, `is_active`) against the same canonical
`docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql`
`WholeC1RegistryVersionsMariaDbAuditTest`/`ReasonCodeSeedExecutionTest` already treat as authoritative:
437/437 rows, zero mismatches, zero extras, zero missing on every field — not a coincidental count
match, an exact set-and-content match. No second/competing canonical source was created.

## FASE 1 disposition (E-MD-B18-A002-040): the two skips replaced

Both `markTestSkipped()` calls in `MarketDataPipelineIntegrationTest.php` are replaced with
assertions that positively prove the current, correct, C1-Sec5-required fail-closed outcome:

- `test_promote_single_day_repair_candidate_fails_closed_on_incomplete_producer_evidence` (renamed
  from `..._first_execution_marks_metadata_and_keeps_current_publication_non_current`, whose original
  narrative — a successful repair candidate — is no longer reachable): asserts
  `promoteSingleDay(..., 'repair_candidate')` throws `\RuntimeException` naming
  `INPUT_CAPTURE_BINDING_MANIFEST_INCOMPLETE` and specifically `provider_mapping`/
  `source_observations` (not a generic failure); the derived run reaches
  `terminal_status=FAILED`/`final_reason_code=RUN_HASH_FAILED`/`publishability_state=NOT_READABLE`;
  the correction is not recorded `REPAIR_EXECUTED`; the pre-existing current pointer is untouched.
- `test_promote_single_day_incremental_fails_closed_identically_to_repair_candidate` (renamed from
  `..._rerun_increments_execution_count_and_preserves_current_pointer`, same reason): runs the
  scenario once with `'repair_candidate'` and once with `'incremental'`, proving both throw the
  identical reason and both derived runs read back with `promote_mode='repair_candidate'`
  (`resolvePromoteContext()`'s alias normalization applies before persistence either way) — the
  alias is not merely assumed, it is proven at the persisted-state level.

Production code is unchanged in this fix; both tests now document the known-incomplete state
instead of hiding it, matching every other place in this attempt where an authorized fail-closed
outcome is proven directly rather than skipped.

## FASE 2 — capture ownership/provenance model for `createPromoteRunFromSeed` (design only, not implemented)

### Confirmed exact scope: only two of twelve C1 domains are affected

The full `INPUT_CAPTURE_BINDING_MANIFEST_INCOMPLETE` exception message for a derived promote run
under current whole-C1 requirements is exactly:
`provider_mapping.provider-mapping-revisions/v1,provider_mapping.provider-source-row-link/v1,source_observations.no_producer_ingress_population,source_observations.source-observation-outcomes/v1`
— four paths, all under **C03 (provider_mapping)** and **C06 (source_observations)**. Every other
domain was independently re-traced this turn and confirmed already correct for this entry path,
with an existing authority basis for each:

| Domain | Status for a derived promote run | Why |
|---|---|---|
| C01 run/config | **Already correct** | `EodRunRepository::createPromoteRunFromSeedWithinTransaction` explicitly re-resolves a *fresh* `config_snapshot_id` (`EodRunRepository.php:337-340`), with an existing code comment stating exactly why: "claiming the seed's identity would attribute a configuration this run never used." It then calls `RunInputCaptureRepository::captureRunConfiguration($run, $snapshot)` (`EodRunRepository.php:369`) immediately, capturing `run_config` under the derived run's own `run_id`. This is an existing, already-implemented precedent: domains tied to *this specific execution's own resolved state* are re-derived fresh, not inherited. |
| C02 universe/identity, C04 calendar, C05 status/expectation, C08 event/factor, C09 ancillary, C10 registries, C11 market structure, C12 completion | **Already correct** | All are captured by `COMPUTE_INDICATORS`/`BUILD_ELIGIBILITY`, which the derived run *does* execute under its own `run_id`, reading continuously-maintained reference state (calendar, sector/event-risk sources, factor decisions, registries) that is correctly re-derived "as of now" for every run, mainline or derived alike — there is nothing acquisition-specific about these domains. |
| C07 RAW/history | **Already correct** | `EodIndicatorsComputeService::computeCaptured()` sets `$useHistory = $correctionMode || ...` (`EodIndicatorsComputeService.php:56-60`) and, when true, calls `EodArtifactRepository::ensureBarsHistoryFromCurrentTradeDate()` then reads the requested date via `loadBarsForTradeDate`/`loadBarsWindow` scoped to the derived candidate's own `publication_id` against the immutable `eod_bars_history` table (`EodArtifactRepository.php:656-661`), while lookback/dependency dates read the shared canonical `eod_bars` table by explicit, already-documented design ("Earlier dates remain current canonical inputs", `EodArtifactRepository.php:463-464`). This is not reading current/latest state to fabricate historical identity — the *current* canonical bars are exactly what a correction candidate is supposed to recompute over; only the *requested date's own* snapshot is frozen per-publication. |
| **C03 provider_mapping** | **Broken** | `EquityProviderSymbolResolver::resolveContext`/`TemporalIdentityRepository::resolveProviderContext`/`SourceObservationRepository::bindResolvedIdentity` are only invoked from within `INGEST_BARS`/`ACQUISITION`-stage code, which the derived run never executes. |
| **C06 source_observations** | **Broken** | `EodBarsIngestService::acquireSourceRows`/`ingestAcquiredRows` are the sole callers of the C06 capture producers; same reason as C03. |

### Existing, already-established provenance link (nothing to invent)

`createPromoteRunFromSeedWithinTransaction` already records the link from a derived run back to its
seed run, immutably, today: its `RUN_CREATED` event (`EodRunRepository.php:348-367`) carries
`'seed_run_id' => (int) $seedRun->run_id` in `event_payload_json`, appended via the same
append-only `eod_run_events` mechanism every other stage event uses. This is not something that
needs to be built — it already exists for every historical and future promote-from-seed run. The
open implementation question is only whether the capture/manifest layer should read this existing
event-payload link directly, or whether a first-class `seed_run_id` column on `eod_runs` should be
added for more direct/robust querying — a genuine but narrow and low-stakes implementation choice,
not a policy question (either satisfies "reference the seed run," which is what authority requires).

### C1 contract text directly authorizes referencing, not duplicating

`MD_B18_A002_C1_PRODUCER_BOUND_INPUT_CONTRACT.md` Sec3.1 states the general principle already used
throughout C1: component entries carry "a stable slot, exact domain payload, content hash,
selection context, row population **and immutable source references sufficient to verify the
content**" — referencing is the established pattern, not an exception. Sec5's own C06 row is even
more direct: capture must "carry contents **or verifiable immutable references**, not an
unexplained digest." Referencing the seed run's own already-captured, already-immutable C03/C06
rows is a literal reading of this text, not an invented allowance. `ProducerInputCompletionManifest`
already implements exactly this reference-and-verify shape for a same-run case today — the
`provider-mapping-revisions/v1`/`temporal-identity-revisions/v1` handling (`ProducerInputCompletionManifest.php:68-85`)
looks up a referenced "population" capture by `(stage_code, slot_hash)`, and rejects the reference
unless `(int) ($reference['run_id'] ?? 0) === (int) $run->run_id` and the referenced payload hash
matches exactly. The *shape* (locate by reference, verify hash equality, reject on any mismatch) is
directly reusable; the one deliberate change needed is widening that single equality check from
"reference must belong to this exact run" to "reference must belong to this run **or its recorded
seed run**" — a narrow, well-understood, well-precedented widening, not new architecture.

### Classification

**IMPLEMENTATION_DERIVABLE** for both C03 and C06 — not `GENUINELY_UNRESOLVED`, and not something
requiring a fresh owner decision:

- *Whether* `repair_candidate`/`incremental` must keep working: settled by LOCKED authority
  (`Finalize_Lock_And_Pointer_Behavior_LOCKED.md`, `Import_Promote_Separation_Contract.md`).
- *Whether* the interim fail-closed behavior is correct while the gap stands: settled by C1 Sec5
  ("existing flows without complete producer evidence fail closed").
- *Whether* referencing (not duplicating) is the right shape: settled by C1 Sec3.1/Sec5's own text
  and by the existing `population_ref` pattern already implementing this shape for a same-run case.
- *What remains genuinely open* is narrow implementation detail only: whether the seed-run link is
  read from the existing `RUN_CREATED` event payload or promoted to a first-class column, and the
  exact reference-record shape for C06 (which currently has no `population_ref`-style cross-check at
  all — C03 can extend an existing check; C06 needs an analogous new one, following the same
  established shape, not a new one).

### Coherent implementation plan (not performed this turn)

1. Expose the derived run's seed `run_id` reliably to the capture/manifest layer (read the existing
   `RUN_CREATED` event payload, or add a first-class column — pick one; both satisfy authority
   equally, this is the one open implementation choice named above).
2. Where `ProducerInputCompletionManifest::inspect()` gathers captures for completeness checking,
   for a run with a resolvable seed run, include the seed run's own `INGEST_BARS`/`ACQUISITION`-stage
   C03/C06 capture rows *by reference, never by duplication* — their `md_run_input_captures.run_id`
   stays the seed run's, permanently; nothing is re-inserted under the derived run's `run_id`.
3. Extend the existing `provider-mapping-revisions/v1` reference check
   (`ProducerInputCompletionManifest.php:74`) to accept a reference whose `run_id` is the run's own
   *or* its recorded seed run's, keeping the existing hash-equality/structural checks unchanged.
   Add an analogous reference-and-verify check for `source-observation-outcomes/v1`/the C06 ingress
   chain, modeled on the same shape, since none exists today for that domain.
4. `PublicationInputBindingService::bind()`'s assembled `md_publication_inputs_v2` bundle must tag
   every such referenced component with its true origin (e.g. a `source_run_id` distinct from the
   owning run/publication) so a reader can always distinguish "captured by this run" from "verified
   immutable reference inherited from run N" — satisfying the requirement that inherited evidence
   is never presented as freshly produced.
5. New targeted tests proving: a derived run with a genuinely absent/tampered seed-run C03/C06
   capture still fails closed (the reference must be *verified*, not merely *present*); a derived
   run whose seed capture is intact now completes; the bundle's provenance tagging is correct and
   readable; direct-SQL tampering with a referenced seed-run capture is still caught by
   `md_run_input_captures`'s own existing immutability triggers (no new gap introduced there).

This plan is not implemented in this turn. Seal must not begin until it is.
