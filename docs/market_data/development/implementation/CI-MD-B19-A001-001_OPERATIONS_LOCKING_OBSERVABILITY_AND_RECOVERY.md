# Change Impact Declaration — `MD-B19-A001`

- ID: `CI-MD-B19-A001-001`
- Stage / Attempt / Baseline / Epoch: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260903-001`
- Predecessor stage closure: `SC-MD-B18-A001-001`
- Dependencies open at entry: `MD-DEP-0003`, `MD-DEP-0004`
- Status: `IN_PROGRESS — STAGE ENTRY NORMALIZATION`
- Strategy meaning change: `NO`
- Governance authority change: `NO`

Issued at stage entry, after `MD-B19-A001-BL001` and before any material `MD-B19` mutation, so that
it directs the attempt rather than describing it afterwards.

## Objective

Open `MD-B19` — daily/backfill/correction/replay operations, locking, observability, evidence
export and recovery — and establish what the stage actually owes before designing any proof for it.

## 1. Measured stage-entry state

This is the largest stage in the package. Measured against the matrix the baseline locked:

| | |
|---|---|
| Active rows | **936** |
| `REQUIRED` | 290 |
| — `MANDATORY` | 67 |
| — **`MANDATORY_OR_CONDITIONAL` (transitional)** | **222** |
| — `OPTIONAL_CAPABILITY` | 1 |
| `REFERENCE_ONLY` | 646, of which the classification gate reports **538 unexplained** |
| Coverage status | 289 `NOT_ASSESSED`, 646 `REFERENCE_ONLY`, 1 `OPTIONAL_NOT_REQUESTED` |

Twenty-seven owner documents, concentrated in `MD-S075` (324), `MD-S063` (168), `MD-S062` (61),
`MD-S076` (53), `MD-S071` (48), `MD-S053` (43), `MD-S077` (40), `MD-S072` (37), `MD-S068` (36).

**The denominator is not yet knowable and this declaration does not state one.** The register's
`0/224 provisional executable` figure is provisional and is not carried forward as fact. 222 required
rows still carry transitional applicability; each must be resolved against its own predicate before
any figure is quotable.

## 2. Stage-entry normalization, and how it will be done

Every one of the 222 transitional rows was read before this declaration was written. They fall into
seven semantic classes, and the classification is recorded as an **explicit reviewed
`rule_id => decision` map with a stated basis per row** — not a keyword heuristic.

That constraint is not stylistic. `F-MD-B18-A001-001` measured what happens without it: a
`strpos()` chain over rule text put 23 of 121 predicates in a family with no keyword match at all
and let earlier branches steal predicates from later ones. `MD-B19` has 222 transitional rows and
646 reference rows, so the same shortcut here would be nine times the damage.

The classes:

| Class | Disposition | Example |
|---|---|---|
| Enumerated member of a "minimum contents / minimum artifact set / minimum evidence" list | `MANDATORY` with parent/context binding and a normalized predicate | `MD-S063-R0024` "- terminal status" |
| Question a pack must answer | `MANDATORY` with parent/context binding | `MD-S063-R0070` "1. Why was correction requested?" |
| Executable locked rule | `MANDATORY` | `MD-S070-R0018` "`eod_run_events` must be append-only" |
| Capability/citation boundary | `MANDATORY`, provable by corpus assertion | `MD-S078-R0019` "…never as evidence that operations ran correctly" |
| Cross-contract alignment pointer | `REFERENCE_ONLY` with recorded basis | `MD-S075-R0305` "- `Failure_Playbook_LOCKED.md`" |
| Purpose/scope header | `REFERENCE_ONLY` with recorded basis | `MD-S076-R0001` "Define the minimum evidence pack…" |
| Conditional obligation | `CONDITIONAL_APPLICABLE` or `CONDITIONAL_NOT_APPLICABLE`, condition named and evidenced | resolved per row |

A bullet that reads as a bare noun phrase is not thereby reference material. `- terminal status`
under "Minimum contents" is an obligation stated in list form, and
`STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` §3 requires it to carry its parent context and a
normalized predicate rather than be dropped.

The 646 `REFERENCE_ONLY` rows are examined, not accepted. This is the largest reference population
in the package and `MD-S066-R0002` survived two attempts inside one exactly like it.

## 3. Affected areas

- **Runtime behaviour**: scheduler and due-date discovery, lock ownership and fencing, backfill
  resume and checkpoint recovery, correction and reseal operations, replay operations, evidence
  export, artifact generation, incident classification, freshness/SLO reporting, release gating.
- **Schema / migration**: not yet determined. No migration is declared until normalization shows one
  is owed.
- **Configuration**: not yet determined.
- **Evidence / proof mechanics**: a `MD-B19` proof surface will be built only after the denominator
  is knowable. It will use an explicit reviewed map with behavioural guards, and a corpus assertion
  only where the predicate is itself about what may be claimed.
- **Tests / gates / tooling**: the register records that seven test files remain bound to
  split-sealed composites; that is inside this stage's scope and will be resolved on its own
  evidence rather than assumed stale.

## 4. Known entry-state facts this attempt inherits and does not assume away

- `MD-DEP-0003` — `OPEN_NON_BLOCKING`, owned by stages `MD-B03/B15/B17/B19/B21/B22`. The registry
  names the `MD-B19` share exactly: **production-validation, ops-command-surface, scheduler, and
  environment-baseline contracts**. That is what is owed here, and it is not discharged by the
  normalization above.
- `MD-DEP-0004` — stage-entry semantic normalization. Discharged for `MD-B19` by the work above and
  recorded in `MD_DEPENDENCY_REGISTRY.csv`, whose per-stage chain now runs through
  `MD-B19-A001-BL001`. The global dependency stays `OPEN_NON_BLOCKING` for `MD-B20`.
- `F-MD-B01-A014-001` — the eligibility-export non-conformance raised in `MD-B01-A014` and handed to
  its owning stage. `MD-B19` owns it and it is discharged here or it is not discharged.
- `F-MD-B00-A001-001` (Class S half) is carried in the register against this stage and is
  re-measured rather than inherited as stated.

## 5. Compatibility risk

Preserve every closed predecessor boundary: the replay mode admission, exact-publication resolution
and as-known cutoff isolation bound in `MD-B18`; the atomic read product and readiness gateway in
`MD-B17`; the first-class eligibility dimensions in `MD-B16`; the coverage threshold in `MD-B15`.
Reject any operations path that finalizes a date through a non-owner run, resumes a backfill over an
already-readable date without correction, or exports evidence that implies readability for an
unsealed publication.

## 6. Residue and rework risk

Search scope is the operations surface named in §3. The specific residues to look for: a scheduler
that discovers work from wall-clock rather than governed dates, a lock whose expiry is treated as
proof the previous owner stopped, a resume that replaces a full date instead of merging on stable
identity, an artifact set whose members contradict each other about status or seal state, and an
evidence export that reconstructs history the append-only trail does not contain.

## 7. Strategy meaning change

**NO.** No strategy byte is changed. Normalization records how an existing predicate is classified
and proven; it does not restate the predicate. If a predicate turns out to be unprovable under
current authority, that is recorded as a finding rather than resolved by reinterpretation.

## Closure boundary

`MD-B19-A001` remains `IN_PROGRESS / PARTIAL` until the denominator is established by normalization,
every required predicate is proven by an executed guard shown able to fail, the proof surface passes
its readiness gate and self-test, governed evidence is issued, the binder promotes atomically, the
post-binding controls run, and the closure gate meets every condition. `MD-B20` must remain unopened
until legitimate `MD-B19` closure.

## Actual impact and result

- **Baseline**: `MD-B19-A001-BL001` issued before any material mutation, recording the measured entry
  state above and the fingerprints it was measured against.
- **Normalization**: complete. All 936 active rows carry a recorded decision traced to their parent
  section in the owner contract. 682 rows changed applicability:

  | Transition | Rows |
  |---|---|
  | `REFERENCE_ONLY` → `MANDATORY` | **424** |
  | `MANDATORY_OR_CONDITIONAL` → `MANDATORY` | 216 |
  | `REFERENCE_ONLY` → `CONDITIONAL_APPLICABLE` | 36 |
  | `MANDATORY_OR_CONDITIONAL` → `REFERENCE_ONLY` | 6 |

  **424 obligations had been filed as reference** and were invisible to every gate, because the
  mixed-run detector only fires where a section holds required and reference rows together and these
  sat in sections classified entirely as reference.

- **Denominator**: **743** — 707 `MANDATORY` plus 36 `CONDITIONAL_APPLICABLE`, with 192
  `REFERENCE_ONLY`, 1 `OPTIONAL_CAPABILITY`, zero transitional and zero undecided reference rows.
  The register carried `0/224 provisional` at entry; the real figure is 3.3 times that.

- **Three over-claims caught and corrected during the pass**, each by tabulating every applicability
  transition after applying rather than by re-reading the corpus:
  1. the first pass scoped normalization to the transitional subset, leaving `MD-S063-R0070..R0071`
     `MANDATORY` while `R0072..R0079` — the same numbered list under the same parent — stayed
     `REFERENCE_ONLY`. Re-scoped to whole sections.
  2. the section rule promoted `MD-S075-R0139` out of `OPTIONAL_CAPABILITY` although its own text
     declares the projection optional. Restored with a recorded basis.
  3. the section rule read `MD-S075`'s artifact-list header "At minimum … must be reconstructable"
     across items 7–11, which actually sit under "Where applicable, the following should also be
     available". Those five became `CONDITIONAL_APPLICABLE` with the condition named.

- **A reporting defect fixed**: `GenerateMarketDataCurrentState.php` printed "FINAL for every
  machine-checked criterion" while 515 reference rows had never been examined. Both of its signals
  are blind to a section classified entirely as reference. A third signal now counts reference rows
  carrying no recorded stage-entry decision, and reports `PROVISIONAL` while any remain.

## 2026-10-08T07:08:21+07:00 Actual impact -- first family established per predicate (E-MD-B19-A001-002)

Same attempt, baseline and declaration. **No file of the frozen R0025 build was changed.** What changed: one new behavioural guard (`B19RangeWindowWarmupWiringTest`), the per-predicate proof basis (`MarketDataOperationsProofBasis`, 8 entries) and the proof gate, which now reads that basis and fails when a named guard does not exist.

`range_window_warmup_calendar` (`MD-S053-R0218..R0225`) is established per predicate: the resolver guard that already existed proved what the resolver returns, not that the lifecycle backfill hands it to the acquisition service; the new guard runs the real orchestrator over the real calendar and observes the arguments, the recorded telemetry and the absence of any acquisition or publication when the calendar cannot establish the boundary. 17 single-protection mutations (orchestrator wiring, resolver, calendar repository, indicator service) turned the guards red for the intended reason with the files restored byte for byte; 7 scenarios proved the gate's new checks can fail.

The 37-family inventory recorded in `E-MD-B19-A001-002` classifies 1 family complete, 15 proof-gap, 14 harness-gap, 7 production-gap; 9 of the 37 are measured against current output or an executed guard and the rest are structural. **A cross-stage consequence was found and is not decided here:** every production defect owned by this stage sits in a file of the frozen candidate-v5 build, so the first production edit invalidates the R0025 proof for the current build (`D-MD-B18-A002-018` item 13). `F-MD-B19-A001-003` and `MD-DEP-0025` carry the owner decision; `MD-B18` is not reopened.

Strategy meaning change: `NO`.

## 2026-10-08T08:04:28+07:00 Owner decision O1 (`D-MD-B19-A001-001`) and the checkpoint/resume family (`E-MD-B19-A001-003`)

Same attempt, baseline and declaration. **Owner decision:** O1 for `F-MD-B19-A001-003` -- production-changing `MD-B19` work may be batched; candidate-v5 stays current until the first executable-build change, then historical-only for the current build with the build-identity failure visible as a governed successor-pending failure (`MD-DEP-0025`); one candidate-v6 is authored against the final build before `MD-B19` closure. `MD-B18` is not reopened. The runtime-behaviour, schema and configuration scope of section 3 is unchanged.

**Actual impact of this unit:** no file of the frozen build was changed (5916 checked, 0 differ). Two behavioural guards were added (`B19RangeWindowCheckpointIdentityTest`, `B19RangeWindowCheckpointPersistenceTest`) and 12 entries to the proof basis. `range_window_checkpoint_resume` is established per predicate for 12 of 13: 42 single-protection mutations turned a guard red for the intended reason, two documented mutants are equivalent. `MD-S053-R0210` is held: a successful checkpoint row carries the window-level HTTP status and attempt total of a different ticker, and the contract does not say what a success row should carry (`F-MD-B19-A001-004`). Reviewed per-predicate bases: 20/743.

Strategy meaning change: `NO`.

## 2026-10-08T10:53:24+07:00 R0210 under Option A (`D-MD-B19-A001-002`) -- first executable-build change (`E-MD-B19-A001-004`)

Same attempt, baseline and declaration. **Actual impact: runtime behaviour.** `PublicApiEodBarsAdapter::buildYahooRangeAggregateTelemetry()` now exposes each ticker's own request result (`ticker_request_telemetry`), and `ApiBackfillRangeAcquisitionService::buildWindowCheckpoints()` takes a checkpoint row's `http_status` (success) and its fallback `attempt_count` from it instead of from the window aggregate. Window aggregates are unchanged on the window telemetry. No schema, configuration or migration change; no other family or surface touched.

This is the **first executable-build change** under O1: from 2026-10-08T08:57:11+07:00 candidate-v5 is historical-only for the current build and `MD-DEP-0025` is the active successor-pending tracker; `MD-B18` is not reopened and candidate-v6 is not created. The expected failure of the R0025 build-identity control is recorded, not skipped. `range_window_checkpoint_resume` is complete at 13/13; reviewed bases 21/743.

Strategy meaning change: `NO`.

## 2026-10-08T12:02:54+07:00 artifact_run_summary (`E-MD-B19-A001-005`)

Same attempt, baseline and declaration. **Actual impact: runtime behaviour, one line.** `MarketDataEvidenceExportService::buildSourceContext()` exported `retry_attempt_count` as 0 for a run that recorded no retry telemetry -- a source fact the run never recorded, which `MD-S075-R0076` forbids; it is now null when unrecorded. This is the **third executable-build file** (after `PublicApiEodBarsAdapter.php` and `ApiBackfillRangeAcquisitionService.php`); `MD-DEP-0025` lists it, candidate-v5 stays historical-only, candidate-v6 is not created, and the governed successor-pending set is the same 13 tests. No schema or configuration change.

The family's existing guard proved that each minimum field is a key of the file; it passed with the seven manifest-derived fields holding null. Two new guards prove values (a run record whose every column differs, the manifest of the run's own publication against decoys, and a real sealed run read directly from the tables). 49 of 52 predicates are established; `MD-S075-R0047`, `R0074` and `R0075` are held on owner questions (`F-MD-B19-A001-005`, `F-MD-B19-A001-006`). Reviewed per-predicate bases 70/743.

Strategy meaning change: `NO`.

## 2026-10-08T14:15:01+07:00 run-summary owner decisions (`D-MD-B19-A001-003`, `E-MD-B19-A001-006`)

Same attempt, baseline and declaration. **Actual impact: runtime behaviour of one artifact.** `MarketDataEvidenceExportService::buildRunSummary()` now exports `final_reason_code` as the persisted `eod_runs.final_reason_code` only; the reason it used to export under that name (persisted, else source, else coverage reason) is `effective_final_reason_code`, with `effective_final_reason_code_derived_from` and `effective_final_reason_message`; `derived_companion_fields` lists every derived field with its derivation and origin. The export result and `lineage.json` carry both reasons; the completeness check and the outcome note read the effective reason. `warning_count` is unchanged (already the faithful mirror) and its limitation is recorded. This edits an executable-build file that already differed from candidate-v5 (`MarketDataEvidenceExportService.php`); no file is added to the set, candidate-v5 stays historical-only, candidate-v6 is not created. No schema or configuration change.

`artifact_run_summary` is complete at 52/52; reviewed per-predicate bases 73/743. Strategy meaning change: `NO`.

## 2026-10-08T17:42:49+07:00 artifact_publication_manifest (`E-MD-B19-A001-007`)

Same attempt, baseline and declaration. **Actual impact: none on runtime behaviour.** The family had no guard (`GUARD_NOT_ASSIGNED`). Three guards were added (mapping on a history where every source differs and neighbours and run mirrors are decoys; the repository's own seal and promotion run twice on one trade date; a real sealed MariaDB publication read against the tables and the governed hash verifier) and the family-level guard pair was assigned in `MarketDataOperationsProofSpec`. Every one of the 29 minimum fields and the 4 locked rules of `MD-S075` section 2 was found conforming; **no production file changed**, so `MD-DEP-0025`, the executable-build file set and the 13-test successor-pending set are unchanged and candidate-v6 is not created. 48 single-defect mutations of the builder, the writer and the promotion path are red for the intended reason. Reviewed per-predicate bases 106/743.

Strategy meaning change: `NO`.

## 2026-10-09T00:50:03+07:00 artifact_run_event_summary (`E-MD-B19-A001-008`, `F-MD-B19-A001-007`)

Same attempt, baseline and declaration. **Actual impact: one executable-build file edited, inside the set already differing from candidate-v5** (`MarketDataEvidenceExportService.php`, +5 lines): an empty `stage_counts` / `reason_code_counts` is now written as a JSON object (`{}`), as the locked shape shows, not as `[]`. Two guards were added (the summarizer on a seeded `eod_run_events` trail compared with an independent derivation; a real MariaDB world exported and compared with the raw trail, the run row and cloned runs) and the family-level guard pair was assigned in `MarketDataOperationsProofSpec`. The other 16 predicates were found conforming. 27 single-defect mutations red. `MD-DEP-0025` stays ACTIVE and candidate-v6 is not created. Reviewed per-predicate bases 124/743.

Strategy meaning change: `NO`.

## 2026-10-09T07:57:53+07:00 artifact_run_event_summary review follow-up (`E-MD-B19-A001-009`, `F-MD-B19-A001-008`)

Same attempt, baseline and declaration. **Actual impact: none on runtime behaviour; no production file changed.** The event-id tie break of `summarizeRunEvents()` is now guarded by the executed SQL and by an index-order test (`M02` is red, no longer treated as equivalent). The severity of a run with no events is an authority gap (`F-MD-B19-A001-008`, OPEN): `MD-S075-R0121` and `MD-S075-R0128` are held without a reviewed basis until the Project Owner decides. `MD-DEP-0025` stays ACTIVE; candidate-v6 is not created. Reviewed per-predicate bases 122/743.

Strategy meaning change: `NO`.

## 2026-10-09T08:44:28+07:00 artifact_run_event_summary empty-trail severity (`E-MD-B19-A001-010`, `D-MD-B19-A001-004`, `F-MD-B19-A001-008`)

Same attempt, baseline and declaration. **Actual impact: one executable-build file edited by one line** — `EodEvidenceRepository::summarizeRunEvents()` starts `highest_severity` at `null`, so a run with zero events writes JSON `null` instead of `"INFO"`, as the Project Owner decided (`D-MD-B19-A001-004`, Option A). The file is in the candidate-v5 frozen build manifest, so **four** executable-build files now differ from candidate-v5 (permitted by O1; `MD-DEP-0025` updated and still ACTIVE); the same 13 build-identity tests fail and nothing else; candidate-v6 is not created. Consumers were checked (only the exported summary reads the field). `MD-S075-R0121` and `MD-S075-R0128` are re-admitted; `F-MD-B19-A001-008` is RESOLVED. Reviewed per-predicate bases 124/743.

Strategy meaning change: `NO`.

## 2026-10-09T12:55:37+07:00 artifact_eligibility_export (`E-MD-B19-A001-011`, `F-MD-B01-A014-001`)

Same attempt, baseline and declaration. **Actual impact: two executable-build files edited, both already in the set differing from candidate-v5** (`EodEvidenceRepository.php`, `MarketDataEvidenceExportService.php`): `eligibility_export.csv` now ships the mandatory V2 columns `listing_id`, `publication_id`, `data_usable` and the complete persisted reason set, with the legacy `eligible` / `reason_code` projection last (`F-MD-B01-A014-001`, RESOLVED). The only reader of the repository rows (replay) reads `eligible` and is unaffected. The set differing from candidate-v5 stays at four files; the full suite shows exactly the 7 oracle failures and the same 13 build-identity failures; `MD-DEP-0025` updated and still ACTIVE; candidate-v6 not created. Reviewed per-predicate bases 133/743.

Strategy meaning change: `NO`.

## 2026-10-09T13:48:09+07:00 artifact_eligibility_export review follow-up (`E-MD-B19-A001-012`, `F-MD-B19-A001-009`)

Same attempt, baseline and declaration. **Actual impact: one executable-build file edited** (`EodArtifactRepository.php`, in the candidate-v5 frozen build manifest): `replaceEligibility()` now refuses, before touching the stored rows, a blocked (`eligible = 0`) row whose reason set is empty, not a list or holds a blank member - the locked rule "no `eligible=false` row may have an empty reason set" was enforced only by construction in the producer (`F-MD-B19-A001-009`, RESOLVED). No pipeline run produces such a row; snapshot and promote copies are not tightened. **Five** executable-build files now differ from candidate-v5 (permitted by O1; `MD-DEP-0025` updated and still ACTIVE); the full suite shows the 7 oracle failures and the same 13 build-identity failures; candidate-v6 is not created. `R0144` is re-based on producer + persistence + real run; `R0134` and `R0138` boundary cases are proven; `artifact_eligibility_export` stays 9/9. Reviewed bases 133/743.

Strategy meaning change: `NO`.

## 2026-10-09T15:10:24+07:00 blocked-row write guard: numeric-key JSON object (`E-MD-B19-A001-013`, `F-MD-B19-A001-009`)

Same attempt, baseline and declaration. **Actual impact: one already-listed executable-build file edited again, no new build file** (`EodArtifactRepository.php`): the guard added under `E-MD-B19-A001-012` decoded the reason set with the associative flag and checked for sequential keys, so an independent reviewer's JSON object with numeric keys passed as a list; the guard now reads the JSON root type from a decode without the associative flag. Red before / green after; seven mutations red; the real MariaDB write re-run green. Registry membership is still not enforced at the write (stated, with a tripwire test). Five executable-build files still differ from candidate-v5; `MD-DEP-0025` updated and still ACTIVE; candidate-v6 not created. `artifact_eligibility_export` stays 9/9; reviewed bases 133/743.

Strategy meaning change: `NO`.

## 2026-10-09T16:12:13+07:00 artifact_eligibility_export R0144 registered-reason boundary (`E-MD-B19-A001-014`)

Same attempt, baseline and declaration. **Actual impact: none on runtime behaviour; no production file changed.** The registered-reason boundary of `MD-S075-R0144` was traced: the public `replaceEligibility()` accepts an unregistered member and the export of a sealed publication carries it unchanged (executed), so the boundary is the producer, which is closed (one caller, one builder, two member expressions, registered literals, a closed set of table writers) and enforced by a census tripwire. Registry enforcement at persistence or sealing is recorded as an optional hardening question. Five executable-build files still differ from candidate-v5; `MD-DEP-0025` unchanged and ACTIVE; candidate-v6 not created. `artifact_eligibility_export` stays 9/9; reviewed bases 133/743.

Strategy meaning change: `NO`.

## 2026-10-09T19:36:32+07:00 artifact_invalid_bars_export partial checkpoint (`E-MD-B19-A001-015`, `F-MD-B19-A001-010`)

Same attempt, baseline and declaration. **Actual impact: two executable-build files edited again, neither new to the candidate-v5 difference set**: `EodEvidenceRepository.php` selects persisted `listing_id` and `source_observation_id` for the audit export and counts the unbounded run/date population; `MarketDataEvidenceExportService.php` writes those required CSV columns and states `sampled`, exported count, total count and configured limit in `run_summary.json`. The interrupted untracked producer test was recovered and corrected without resetting it. On a seeded database and a real run, the file reflects persisted rejected rows while canonical rows stay separate; bounded sampling is disclosed. Eight of the nine family predicates have reviewed bases. `MD-S075-R0152` is held: the real conflict branch emits `BAR_CONFLICTING_SOURCE_ROWS`, absent from the locked registry and seed (`F-MD-B19-A001-010`, owner decision required). The family remains partial; reviewed bases 141/743, six families complete, formal SATISFIED 0/743. The same five build files differ from candidate-v5; `MD-DEP-0025` remains ACTIVE; candidate-v6 not created. No change to candidate-v5, MD-B18, or eligibility-export proof. Fifteen isolated mutations were red and byte-restored; targeted producer, repository, writer and real-run tests pass. The full MarketData suite returned 32 failures: seven corpus-oracle and 13 candidate-v5 build-identity governed failures, 11 transient governance failures from the then-missing E015 record and ungenerated CURRENT_STATE (the corresponding settled-state targeted controls now pass), and one alias-meaning failure on unchanged HEAD eligibility records. This is not a full-suite PASS.

Strategy meaning change: `NO`.
## 2026-10-09T21:31:30+07:00 proof-throughput pilot (`E-MD-B19-A001-016`)

Same attempt, Baseline Lock and implementation declaration. No production, schema, strategy, candidate-v5 or MD-B18 change. The read-only `MarketDataOperationsProofCoverageDiagnostic.php` reports exact missing rule IDs, family denominators, guard assignment and shared implementation paths; it never binds a predicate. A three-row persisted SQLite fixture and real replay evidence exporter establish only `MD-S075-R0231`–`R0233` in `artifact_replay_result`: two dates for replay 4101, a same-date replay 4102 decoy, three distinct effective dates, and explicit refusal of missing pairs. Four isolated mutations (wrong replay ID, fixed trade date, fixed effective date, wrong-date repository lookup) each turn a targeted guard red and are byte-restored. New test 2/28 and existing exporter test 2/55 pass. Operations proof gate remains FAIL by design at **144/743 reviewed bases**, **599 missing**, **29 families without a family guard**; six families complete, `artifact_invalid_bars_export` 8/9 with R0152 HELD by F-010, `artifact_replay_result` 3/32, formal SATISFIED 0/743. `MD-DEP-0025` remains ACTIVE with the same five build files differing; candidate-v6 was not created. Full suite was not rerun because this pilot changes no production or schema code.

### Throughput diagnostic and reusable boundaries

- Existing `UsesMarketDataSqlite`, `ReplayResultRepositoryIntegrationTest`, `ReplayEvidenceExportServiceTest`, B19 provenance tests, `MarketDataOperationsProofSpec/Basis/Gate`, and the evidence SHA-256 inventories already supply schema, replay persistence, exporter construction, per-predicate admission and integrity checks. The new diagnostic exposes their next missing IDs without treating one family guard as proof of all members. The previously used mutation scripts and review-package generator live outside the repository; the pilot retains the small byte-restoring mutation script as raw evidence. Reuse their mechanism for subsequent predicates only when each mutation reaches the claimed behavior.
- Repeated family discovery, copying large metric fixtures, rerunning passed mutations, rerunning the full MarketData suite for test-only edits, and rebuilding an entire historical evidence package for every field add effort without strengthening an individual basis. One fixture lifecycle, exact pair selector and single exporter run can serve multiple fields, but each field needs an independent expected value, a discriminating negative case and a sensitive mutation. The pilot measures **three admitted bases from one fixture setup and four red mutations**, not an elapsed-time saving; future time savings remain speculative.
- Shared implementation clusters in the canonical map: run-summary, publication, run-event, eligibility, invalid-bars, replay-result, anomaly, source-attempt, set/category, cross-cutting and audit-pack families use `MarketDataEvidenceExportService`; eligibility/invalid-bars/run-event also use `EodEvidenceRepository`; replay-result uses replay metric storage and export; replay-state-pair uses `ReplayVerificationService`. Existing B19 real-run fixture patterns may be reused, but expected values must be derived independently from each producer/store. Potential later remediation is most likely around replay-result field-name projection, replay-state comparison semantics and status mapping; inspect exact locked text and real producer before classifying any as defect or authority gap. F-010 remains the only confirmed owner decision in this pilot.

### Next bounded execution batch — `artifact_replay_result`, 32 predicates

The 32-member family alone fits the 20–35 target; `artifact_replay_state_pair` (another 32) is a separate later batch. Canonical inventory: `MD-S075-R0231`–`R0262`, inclusive. Pilot reviewed `R0231`–`R0233`; **29 remain**. `R0152` stays HELD in the preceding family and is not silently advanced.

| Predicate inventory | Positive / source proof | Negative / mutation and real-run requirement |
| --- | --- | --- |
| R0231–R0233 (3 identity fields) | Done: persisted exact-pair rows to real JSON writer, independent literal expectations | Done: absent pair refused; four red projection/selector mutations |
| R0234–R0239 (6 run/replay outcome fields) | Trace real replay producer to `md_replay_daily_metrics` and JSON for match, mismatch and blocked cases | Distinguish run `status` from `replay_status`, comparison result, note, scope and mode; invert each branch or swap fields one at a time |
| R0240–R0249 (10 temporal/config/product identity fields) | Resolve each locked field name against persisted replay schema and bound-input context, then assert independently seeded non-equal identities | Preflight exact names (`knowledge_cutoff` versus `_at`, observation/temporal/factor fields) before coding; mutate each projection or swap two identity roots; obtain owner decision only if locked meanings cannot be reconciled |
| R0250–R0258 (9 version/hash/expectation fields) | Persist distinct actual and expected values, export via real writer, and compare with source rows and fixture declaration | Swap actual/expected, null, or use a neighboring replay/date; exercise mismatch details in a real replay outcome |
| R0259–R0262 (4 locked rules) | Verify persisted names, PASS/FAIL/BLOCKED semantics, explicit expected context, and detailed mismatch evidence on executed replay cases | Mutate fallback/default status, drop expected context, or replace detail with summary; a mere matching NULL or key existence cannot admit these |

Preflight each subgroup against locked section 8 and the current replay producer/repository before extending the fixture. Batch evidence must retain per-rule guard references, independent expected values, raw green/red execution, source restoration hashes, governed evidence correlation and an explicit count; the coverage diagnostic cannot promote it. Use a real replay producer run for the semantic groups, not only seeded exporter rows. Stop an affected predicate on a genuine authority choice while continuing independent members only when governance allows it. The exact next executable predicate is **R0234**; F-010 remains an independent owner hold on R0152.

## 2026-10-10T15:21:32+07:00 replay-result stored-field and mismatch-detail checkpoint (`E-MD-B19-A001-017`, `F-MD-B19-A001-011`)

This successor checkpoint supersedes the pilot's R0234 resume point. Same MD-B19-A001 attempt, baseline and declaration. **No production, schema, strategy, or candidate-v5 change**. The real repository/exporter reads three independently seeded replay metric rows with two dates for one replay and a same-date decoy. Actual and expected values, batch hashes, publication versions, modes, outcome fields and NULLs differ. Sixteen additional rule-specific bases are reviewed: R0234–R0239, R0250–R0258 and R0262. The first fifteen establish exact stored-field export only; R0262 combines real persisted mismatch detail/export with the actual replay comparator's reason-code divergence path. Fifteen field mutations plus export-detail and producer-detail mutations red, all source byte-restored. New field guard 2/105, retained identity guard 2/28, repository integration 26/89, existing exporter 2/55, new detail guard 2/11 and producer detail guard 1/3 green. No full-suite rerun because executable production and schema are unchanged. The 32-row exact preflight matrix and raw execution are in E017. The E017 filename says “FIFTEEN_MORE” but its immutable body and canonical map correctly enumerate **sixteen** newly reviewed predicates; the filename is a non-authoritative label and is not rewritten.

`F-MD-B19-A001-011` is a genuine authority mapping hold for R0240–R0249 and R0259: locked top-level example names disagree with the locked persisted-name rule and the metric/export schema; factor/product source is not established. No alias, NULL or invented default is admitted. R0260 (semantic PASS/FAIL/BLOCKED classification) and R0261 (versioned expected-fixture independence) remain proof gaps, not satisfied or owner-decided. The family is **19/32**; B19 reviewed bases **160/743**, **583 missing**, six complete families, formal SATISFIED **0/743**. Prior invalid-bars R0152 remains HELD by F-010. `MD-DEP-0025` remains successor-pending ACTIVE with the same five build files differing from candidate-v5; candidate-v6 not created. **Single exact next executable resume point: R0260**, requiring a real producer comparison/admission outcome through persisted metric and exported JSON, including contradictory/invalid input discrimination; maintain the eleven-predicate F-011 hold and the independent R0152 F-010 hold.

## 2026-10-17 replay semantic proof and F011 owner readiness (`E-MD-B19-A001-018`)

Same attempt, Baseline Lock and owner O1 authority. R0260/R0261 now have executable reviewed bases through the real comparator, real persistence and real JSON exporter. The independent literal fixture definitions b19-v1/b19-v2 are loaded before the actual world; they are contract probes, not R0025 candidate admission. MATCH/EXPECTED_DEGRADE map to PASS, MISMATCH/UNEXPECTED to FAIL, missing fixture/runtime input/self-generated oracle to NOT_ADMISSIBLE/BLOCKED. Run terminal status is separate. The expected context preserves fixture id/version/hash, expected status/effective date/seal and publication/lineage identities; missing or NULL context is not rebuilt from actual results. Seventeen new mutations red and byte-restored.

Actual impact: ReplayResultRepository and MarketDataEvidenceExportService reject a contradictory explicit replay_status or unknown comparison_result before storing/updating/exporting (ten focused red-before cases; F-MD-B19-A001-012 resolved). A valid historical NULL status derives from the existing five-value mapping without rewriting the historical row on export. An accepted synthetic test row using comparison_result=BLOCKED (not in the production enum) is boundedly corrected to NOT_ADMISSIBLE and its test rerun; prior immutable records are unchanged. ReplayResultRepository.php is the sixth candidate-v5-differing build file; the exporter was already in the set. MD-DEP-0025 remains ACTIVE.

Targeted producer, persistence, exporter, fixture/admission and retained real-publication-binding regressions pass. The representative candidate-v5 frozen-build test still fails only on the governed build identity; the single bounded alias-meaning check repeats the prior F009/E013 documentation omissions, whose bytes and guard are unchanged from takeover HEAD. Neither failure is called PASS or a new production regression. No full suite: impact is bounded to two replay storage/export boundaries and targeted consumers/admission guards cover it; full-suite state is not asserted.

F-MD-B19-A001-011 now contains the exact eleven-rule source/path/applicability mapping and proposed single owner choice B. Temporal universe identity is explicitly distinct from the publication temporal revision hash, event/factor composite from factor-set hash, and registry hash from formula version. Two missing publication-manifest values need a persisted versioned envelope after approval; all other values use existing frozen sources. No authority or mapping decision is invented.

Reviewed bases 162/743 (581 missing), replay result 21/32, six complete families, invalid bars 8/9, formal SATISFIED 0/743. Eleven replay predicates remain HELD under F011 and R0152 remains HELD under F010. Candidate-v5 immutable, candidate-v6 not created, MD-B18 stays closed. Single exact resume point: MD-B19-A001: Project Owner to decide F-MD-B19-A001-011 using its exact eleven-predicate mapping (Option B recommended); then execute the controlled authority revision and only its approved implementation/proof. Replay result 21/32; R0260/R0261 reviewed, R0240-R0249 and R0259 still HELD. Invalid-bars R0152 remains HELD under F-MD-B19-A001-010. B19 162/743 reviewed, six complete families, formal SATISFIED 0/743. MD-DEP-0025 ACTIVE with six build files differing; candidate-v5 immutable, candidate-v6 not created.

## 2026-10-18 owner choices, controlled-revision review and independent state-pair proof (`E-MD-B19-A001-019`)

Owner choices F010=A/F011=B recorded; exact three-document proposal awaits independent strategy review; no freeze/source change. Sixteen stable state-pair bases established, B19 178/743, state pair 16/32, six complete families, formal 0/743; 28 of 44 targets remain held.

Same A001 / BL001; scope is 44 exact predicates in preflight_44.json. Proposed strategy IDs MD-S085, MD-S075 and MD-S063 are copied as non-authoritative raw review material, not edited at their registered paths. D005 records owner authorization without inventing independent exact-text review. Independent review retained by the owner is pending; no change-log application entry, freeze, authority fingerprint or strategy matrix mutation occurs. verification_impact.json and B19_F010_F011_CONTROLLED_REVISION_REVIEW.md identify future rebindings and revalidation, including exact identity meanings and PUBLICATION_EXACT RAW review. No locked requirement is weakened.

Actual changes: one test class and the existing spec/basis guards, mutable governance/navigation, decision and evidence. No schema/config/runtime/provider/backfill/ops behavior or executable-build identity input changes. Existing accepted literal fixtures and all E015-E018 raw files are retained and hash-verified. Raw proof is isolated in authority-and-stable-pair-20261018 with producer/persisted/export snapshots, execution commands and mutation restoration hashes; no retention mechanics change. Version-zero current state remains explicit for formal SATISFIED (0/743).

The sixteen stable state-pair members keep exact meanings and paths through the proposed revision. Their future table-format source rebinding needs explicit semantic-equivalence handling; generated maps cannot admit proof. Do not execute the sixteen contract-dependent identities before the new freeze. No broader suite or MariaDB execution is justified by a test/map-only change; SQLite real producer/persistence/export behavior is proved and no engine-specific claim is made. Thirteen new mutations are restored; three prior unchanged expected-export probes are retained. Targeted replay regression passes; known alias documentation failure is retained, never hidden. MD-DEP-0025 remains ACTIVE with six inherited build differences, candidate-v5 immutable, candidate-v6 absent, B18 historical validity preserved.

Single exact resume point: MD-B19-A001: independently review the exact proposed F010/F011 strategy bytes and verification-impact map in B19_F010_F011_CONTROLLED_REVISION_REVIEW.md (D-MD-B19-A001-005 choices are binding); resolve PUBLICATION_EXACT RAW applicability without invented values, then apply one reviewed correction/freeze with affected-proof handling before R0152, R0240-R0249/R0259 and the sixteen remaining state-pair mode/identity predicates. Retain E015-E019 and the sixteen stable state-pair bases. Current B19 178/743 reviewed, six complete families, formal SATISFIED 0/743; invalid bars 8/9, replay result 21/32, state pair 16/32. MD-DEP-0025 ACTIVE, six build files differing; candidate-v5 immutable, candidate-v6 not created.

## 2026-10-18 D006 controlled application and remaining 28 proof

D006 reviewed corrections and owner RAW authorization applied; freeze 20261018-001, B19 denominator 750 including seven new unproved normative groups; 178 earlier bases retained, formal zero; remaining 28 targets require executable proof.

Source scope: three corrected strategy files, current authority/freeze and source matrix, seven new proof-map rows, reason seed and normal/recovered ingest stable-listing/date key; ReplayVerificationService capture/admission and persisted actual/expected context; exporter mode/context preservation. No schema migration is planned. New publication envelope must bind explicit verified publication and independent fixture; missing mandatory context fails closed including RAW. Current config reference is not a digest. Real SQLite/MariaDB, targeted negative mutations and affected replay/registry regressions required. Raw proof is isolated under authority-completion-20261018; E015–E019 remain immutable. Actual build changes must update MD-DEP-0025. No B18 reopening, candidate-v5 edits or candidate-v6 creation. Seven new compound normative groups remain unproved until each constituent is established. Existing source rebinding is syntactic equivalence, not automatic new proof.

## E021 completion / actual verification impact

MD-B19-A001: stop for independent review of E021 / D006 freeze MD-STRATEGY-FREEZE-20261018-001, 28 target bases plus affected revalidation (206/750 reviewed, formal 0/750). After review, resume canonical remaining B19 scope with the seven newly classified compound obligations explicitly unproved; no additional family entered here. MD-DEP-0025 ACTIVE, candidate-v6 deferred to final-build trigger; owner controls all Git mutations.

Three strategy files applied under D006/E020; 28 new bases, 18 earlier semantic/stable bases explicitly revalidated by verified MariaDB publication and independent counterfactual fixture. Other existing stored-field bases retain exact meanings and are covered by targeted exporter/repository regressions. Seven new compound rows remain unproved. Eight differing build files audited; no SQL migration/new RAW capability. New reason seed exercised on MariaDB rollback; normal/recovered ingest/storage/export on SQLite. Independent source-build input used to avoid circular or vacuous build matching; no new golden admission. Legacy incomplete synthetic publication tests now assert BLOCKED without weakening applicable identity requirements. Immutable fixture literals retained. Known alias-meaning failure visible; no full-suite PASS claimed.

## E022 actual verification impact / D007 companion direction

MD-B19-A001: stop for independent review of E022/D007, 50 new bases in artifact_cross_cutting_rules, audit_pack_replay and artifact_set_and_categories (256/750 reviewed, formal 0/750, 12/37 complete). Resume remaining canonical B19 scope only after review; MD-DEP-0025 ACTIVE, candidate-v6 deferred to final-build trigger, Project Owner controls Git.

Small runtime changes in exporter, replay producer and existing evidence repository; no schema migration or strategy-byte revision. Replay producer persists own verified V2 semantic supplemental snapshot before metric write; expected snapshot independently supplied by versioned fixture, never target-derived. Export never queries live state to repair replay history. New common immutable companion exposes exact member hashes, explicit timezone, applicability and self-contained own context. Missing identities/coverage/diagnostics remain incomplete, sensitive content refused, stale mixed outputs refused, existing bytes preserved. All 64 prior replay result/state bases revalidated by focused guards/real verified producer; 142 other prior bases retained with shared writer regressions. Known F009/E013 alias failure and historical successor-build failures remain visible; MD-DEP-0025 ACTIVE eight differing source files. No full suite claim.

## E023 verification impact / accepted E022 continuation

MD-B19-A001: stop for independent review of E023, 66 new run-evidence bases and 81 scoped impact revalidations (322/750 reviewed, formal 0/750, 15/37 complete). After review resume remaining canonical B19 scope; MD-DEP-0025 ACTIVE, candidate-v6 deferred to final-build trigger, Project Owner controls all Git mutations.

No strategy-byte revision. Three runtime files edited: actual request capture retained through Yahoo aggregate; strict append-only own acquisition selection and typed/availability projection; exporter own-context enrichment, current pointer distinct from selected historical publication, source/trail admission and anomaly facts. One test-support fetcher override reuses actual producer, no new generic framework. Independently authored HTTP outcomes/frozen input expectations establish real SUCCESS/retry/corrected-current/superseded/HELD/FAILED; errors do not force admission. 81 affected bases explicitly revalidated,175 retained including accepted replay proof; no immutable record rewritten or unaffected mutation repeated. MD-DEP-0025 remains ACTIVE at eight differing executable-build files. Known alias-meaning failure retained, no full suite run.

## E024 correction and archive verification impact

MD-B19-A001: stop for independent review of E024:82 new reviewed bases (48 primary +34 actual archive),7 scoped revalidations;404/750 reviewed,18/37 complete families,formal0. R0324 HELD under D008/F016, historical raw provenance absent. Await review and Project Owner Git instructions; MD-DEP-0025 ACTIVE, no candidate-v6.

Only correction export and its comparison helpers changed in the existing source-build exporter. Seven prior correction-applicable companion/category/timestamp/consistency bases revalidated;315 unaffected retained including accepted E023. No run/replay producer/fixture/frozen strategy changes. Actual MariaDB correction and per-case impact lifecycle (explicit reflection seam with real dependencies) plus persisted notes/export and independent archives prove82 new bases; historical R0324 cannot be inferred from current successes. No broad/full suite executed or claimed. MD-DEP-0025 remains ACTIVE/eight differing files.

## E025 executed admission and audit core impact

MD-B19-A001: stop for independent review of E025:72 new reviewed bases (executed pack32,admission21,audit core19),7 scoped revalidations;476/750 reviewed,21/37 complete families,formal0. Conditional cross-contract25 not entered;R0324 HELD D008/F016. Await external review and Project Owner Git instructions;MD-DEP-0025 ACTIVE,no candidate-v6.

Only exporter admission/final completeness changed within existing eight source-build differences. Seven prior serialized companion/category/timestamp/consistency bases revalidated;397 unaffected retained. Current real MariaDB world plus exact accepted E022-E024 sources and independent fixture preimages establish72 mapped bases. Criteria metadata implements existing capability boundaries, no normative/freeze revision. Conditional25 also spans incident/recovery/playbook semantics beyond selected proof. Unchanged coverage static guard failure and alias F009/E013 retained. No full suite;MD-DEP-0025 ACTIVE.

## E026 cross-contract and bounded ownership impact

MD-B19-A001: stop for independent external review of E026:39 new reviewed bases (cross-contract policy25, bounded ownership14),0 earlier revalidations;515/750 reviewed,22/37 complete families,formal0. Incident18 unproved under F018;owner13 unproved under F019;commands17 conditional not entered. R0324 HELD D008/F016;MD-DEP-0025 ACTIVE,no candidate-v6. Project Owner alone controls Git.

No production, schema, app/config/bootstrap/vendor or frozen-strategy source changed. Cross-contract policy test and B19 proof spec/basis add39 scoped bases; accepted476 earlier bases are unaffected and retained without rerunning historical mutations. Real SQLite owner/correction/held/failed/pointer cases and one restored force-interlock bypass establish only14 bounded owner predicates. The executed two-active-run probe and code inspection expose F019; incident classification has no executable decision path and F018 records the exact severity/closure authority question. Static contract alignment does not imply runtime execution. MD-DEP-0025 stays ACTIVE with the same eight candidate-v5-differing files. No candidate-v6 or full suite.

## E027 cross-reference repair and owner/takeover impact

MD-B19-A001: stop for independent external review of E027:25 E026-held cross-contract bases repaired without double count,11 new F019 owner bases,526/750 proposed reviewed,22/37 complete families,formal0. F019 taxonomy R0016/R0017 and F018 incident18 remain unproved;commands17 conditional not entered. R0324 HELD D008/F016;MD-DEP-0025 ACTIVE,no candidate-v6;Project Owner controls Git.

The cross-contract proof guard now checks exact governing source line and source document ID against the frozen matrix, resolves explicit filenames uniquely through both document registries, and resolves generic references by registered role and document family. Existing independently selected source/target semantic clauses remain checked. The E026 evidence record and raw files are unchanged. E027 records 75/75 tests and on-disk removal/retarget mutants for exact and generic references. The affected E026 cross bases are revalidated, not newly counted.

Runtime changes add a requested-date owner fence table, cross-mode owner selection, blocked diagnostic events, conservative stale trust and prior-current integrity checks, takeover identity events and ownership rechecks at hash, readable finalize, direct seal and pointer promotion. Eight adversarial tests include two independent SQLite processes released under one database lock; nine focused earlier runtime regressions revalidate normal, low-coverage, correction, unforced/authorized replacement and ownership-binding paths. Three runtime mutants turn targeted tests red and restore. E015-E025 unaffected proof is retained without historical mutation reruns. MariaDB row-lock behavior is a deployment dependency of the new fence; no claim of a full MariaDB owner race is made. Known alias-meaning F009/E013, unchanged coverage static-guard failure and historical candidate-v5 build-identity failures stay visible. Formal SATISFIED remains zero.

## E028 MariaDB owner, taxonomy and runtime-command impact

MD-B19-A001: E027 external review accepted the repaired cross-contract25 and six owner bases; five owner bases were held for MariaDB/missing-fence proof. E028 supplies targeted production-engine successor proof for those five, two new locked-taxonomy bases, and one new commands/runbook catalog basis: **529/750 proposed reviewed**, 23/37 complete families, formal0, pending independent review. E026/E027 remain immutable. F018 R0011/R0012 owner decision and R0324 D008/F016 remain held; MD-DEP-0025 ACTIVE; no candidate-v6 or formal promotion.

Production changes require a matching requested-date fence for direct seal/promotion, retry actual InnoDB deadlock during owner acquisition, persist a separate BLOCKED fenced-worker run, and persist SKIPPED only when a separately verified current publication and same-target artifact/config/product identity agree. Changed unforced replacement remains HELD. Migration 000002 extends the terminal enum without converting old rows. The test database ran migrations 000001/000002; an isolated migrated MariaDB schema with two actual PHP processes passed one-owner, non-owner finalize/seal/promotion, no-fence, prior-pointer and historical-read checks. Four earlier direct-publication fixture classes needed explicit owner-fence setup; all 78 tests/531 assertions revalidated after correction. The 12 focused owner/taxonomy tests/98 assertions and three publication regressions/4751 assertions pass. Five new mutants (missing fence, blocked status, duplicate skip, MariaDB retry and command catalog option) turn red and are byte-restored. Earlier valid E027 cross proof and unaffected historical mutations were not rerun.

The runtime catalog now matches 41 registered commands and command-specific options from actual JSON help. Three previously omitted names and obsolete detection-only notes were corrected; the command catalog test passes 1/85. One runbook predicate is proposed, while F020 records the other 16 as executable-surface/proof gaps. Existing 64 command-safety tests/371 assertions passed but are not treated as complete command-family proof. Operations readiness remains FAIL at 221 missing bases and 12 families without a guard. No full-suite PASS or production deployment claim.


## E029 operational commands, observability, calendar and locking successor

External independent review accepted E028's five repaired ownership bases and three new taxonomy/runtime-catalog bases; accepted baseline is 529/750, formal SATISFIED 0. E029 adds 34 predicate-level proof bases pending external review: commands_and_runbook 2, observability_minimum 18, scheduling_and_locking/calendar 14. The per-predicate map and held list are `storage/app/market-data/evidence/MD-B19-A001/E029_per_predicate_map.json`; no basis is double-counted and E026-E028 remain immutable.

The binding `D-MD-B19-A001-003` decision was checked before code correction. Persisted `warning_count` remains NULL; event WARN counting and writes were removed. The status consumer marks the persisted field NULL with decision provenance. Its missing/contradictory fact cases, event append-only MariaDB triggers, scheduler healthy/idempotent/held/failed/locked outcomes, prior pointer preservation, versioned-calendar cutoff, and owner timestamp migration were exercised. Observability now includes persisted `source_final_reason_code` and the coverage-held stage only when a matching persisted reason/event proves it.

Focused final runs: operational commands 4/106 MariaDB; observability 3/70 MariaDB; calendar 2/12; owner fence/migration 2/44 MariaDB; schedule registration 11/29; runbook scheduler guard 1/13; runtime catalog 1/89 over 43 commands; warning authority 21/317; operational/destructive/command-surface guards 79/422; three affected pipeline regressions 3/4765. No full suite. Mutation inputs were restored by transaction/finally paths; no temporary source mutant remains.

E029's basis gate reports 563/750 basis rows and remains FAIL: 187 predicates lack a basis, 12 families lack an assigned family guard, and formal SATISFIED remains zero. The accepted count remains 529 pending external review. Twenty-four predicates remain held with exact reasons in the map and F021. F018, R0324/D008/F016, MD-DEP-0025, candidate-v5, and historical B18 validity remain as governed. No candidate-v6.

The frozen candidate-v5 manifest comparison identifies 15 differing listed files and three new application source files outside that frozen manifest; exact expected/current hashes are in `E029_build_manifest_comparison.json`. MD-DEP-0025 remains ACTIVE.

MD-B19-A001: stop for independent external review of E029's proposed 34 new bases (commands2, observability18, scheduling/calendar14); accepted baseline 529/750, E029 pending review, formal0. After review continue the 24 exact selected-scope predicates listed in E029. F018 R0011/R0012 remain held for Project Owner authority; R0324 HELD D008/F016; MD-DEP-0025 ACTIVE; candidate-v5 unchanged; no candidate-v6 or Git mutation.

## E031 acquisition, recovered-row provenance, and bounded maintenance successor

External review accepted E029's 34 new bases, making the accepted baseline 563/750. E030's recovered-manifest production correction was accepted with zero new predicate bases; E030 remains immutable. E031 proposes 31 new bases: range_window_acquisition 19, resumable_backfill 10, allowed_source_modes 1, retention_and_maintenance 1. The 12 selected holds and exact blockers are in F-MD-B19-A001-022 and E031_per_predicate_map.json. Candidate reviewed count is 594/750, formal SATISFIED 0/750.

Real MariaDB lifecycle runs cover a chronological two-date API acquisition with a distinct run and readable current publication per requested date; warmup pointers remain unchanged. The resumed provider-503 case proves a still-failed retry has NOOP apply, no indicators or eligibility and no fake readable pointer, then a successful retry appends a new immutable accepted observation, binds exact V1 and semantic V2 manifest hashes independently computed from persisted inputs, reprocesses affected nonreadable dates, and promotes through seal and pointer guards. Six missing, substituted or contradictory observation identities refuse before readable publication. The registered manual-file command executes an explicit one-date historical CSV fill only under the supported legacy profile; current default V2 local-only provider attribution remains held and is not claimed as passing. Snapshot TTL dry-run/apply leaves runs, bars, publications and current pointer unchanged.

The E031 focused MariaDB, affected regression and mutation transcripts and exact source hashes are retained with the successor package. The source correction to RAW lineage validates the referenced trade-date/listing row inside a multi-date accepted observation, allowing chronological publication while still refusing ambiguous or unbound references. Windows absolute local-file path recognition is corrected for the executed manual-file command. Earlier correction, source-observation, RAW input and local-file proof is revalidated only where these code changes affect it.

Operations gate remains FAIL at 156 predicates without basis and eight families without an assigned guard. Formal SATISFIED remains zero. The F009/E013 alias-meaning guard, coverage static guard, historical build-identity failures and MD-DEP-0025 remain visible. F018 severity/closure and R0324 D008/F016 remain held. No production provider activation, partition deployment, full-suite PASS, candidate-v6 or Git mutation is claimed. Exact next point: independent external review of E031's 31 proposed bases and 12 held selected predicates; continue only after disposition.

## E032 bounded warmup, interrupted-range resume, and retention impact

E032 continues the same frozen attempt after independent review accepted 27 E031 bases and held four proof gaps. It does not change strategy, authority, applicability, denominator, or D003 `warning_count` semantics. The four held E031 proposals are re-proved with actual pre-request MariaDB warmup rows/source identity and an explicit direct-promotion refusal (R0183/R0200), a genuinely interrupted two-date lifecycle and resume from the first incomplete date (R0001), and protected canonical-family snapshots over TTL dry-run and apply (R0085). A fifth proposed basis addresses the previously held first-incomplete-date rule R0007; it is one additional proposal, not a duplicate of any E031 basis.

Production impact is limited to `BackfillLifecycleOrchestrator` ordinary resume source-row reacquisition and `MarketDataPipelineService::promoteSingleDay` range-window requested-date fencing. Only-failed checkpoint retries, historical reads, prior readable publication and immutable history are covered by targeted MariaDB regression. Both source changes passed their direct behavioral checks and targeted mutation probes; mutation restoration was SHA-256 verified. `B19RangeBackfillMariaDbProofTest.php` passed 12/187. Resume-state/checkpoint persistence, warmup wiring and checkpoint persistence controls passed. No full suite, provider activation, deployment, candidate-v6, formal SATISFIED promotion or denominator change is claimed.

The last independent acceptance remains 590/750. Four repaired E031 proposals are re-submitted without double counting; one newly mapped R0007 makes the proposed position 595/750 pending independent review. The proof gate remains FAIL with 155 predicates lacking a reviewed basis and eight families lacking a guard. Eleven residual E031 holds remain, listed with exact limitations in E032 evidence. F018 authority, R0324 D008/F016, MD-DEP-0025 ACTIVE, candidate-v5 and historical B18 validity remain unchanged.

Stop for independent external review of E032. Exact continuation after review: MD-B19-A001 — continue the eleven residual acquisition/backfill/mode/maintenance holds listed in E032 evidence and its per-predicate map; preserve the four repaired E031 proposals without duplicating them or repeating their proof.
