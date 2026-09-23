# Finding — replay comparison, identity and publication-resolution review gaps

- ID: `F-MD-B18-A002-016`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-09-14T16:03:58+07:00 (system clock)
- Severity: `P1` for closure. It contains an executable defect and proof bases that overclaim.
- Status: `OPEN — REMEDIATION_IN_CONSOLIDATED_PACKAGE`
- Class: `EXECUTABLE_DEFECT_AND_PROOF_BASIS_MISTARGETED`
- Found by: per-predicate review of PAIRS 09, 10, 11, 12, 13, 16, 17, 18 and 19 (14 predicates)
- Probe evidence: `E-MD-B18-A002-011`
- Dependency: `MD-DEP-0017` (consolidated remediation review)

## Executable defect

**Replay backfill starts publication replay from the current pointer.**
`ReplayBackfillService::execute` (`:54`) chooses each date's publication with
`findCurrentPublicationForTradeDate($tradeDate)`, then replays it with that id as though the id had
been declared. `ReplayBackfillServiceTest` expects exactly that call.

`MD-S050-R0027` says two things:
- publication replay "starts from explicit publication identity, never latest/current";
- current-read verification is a *separate* assertion that the pointer resolves a specific
  publication.

The backfill merges the two. Today's pointer decides which publication gets verified, and no separate
pointer assertion is recorded. After a correction moves the pointer, the same command verifies a
different publication.

Remedy — a user decision, one of:
- the backfill takes explicit identities, for example a declared `(trade_date, publication_id)`
  manifest;
- the backfill is reclassified as current-read verification, which then needs the separate pointer
  assertion `R0027` names.

`ReplayVerificationService::verifyRunAgainstFixture` itself already refuses a readable
`PUBLICATION_EXACT` replay that has no explicit id (`REPLAY_EXPLICIT_PUBLICATION_REQUIRED`,
`:81-83`). That refusal is guarded only by a string check, which is recorded against `MD-S003-R0002` below and also bears on R0027.

## Guard gaps — fixable in tests, no application change

| Predicate | Gap | Probe that showed it | Remedy |
|---|---|---|---|
| MD-S036-R0007 | The basis credits "import status" to the import-only fixture, but the fixture and the policy never touch `import_status`. The generic comparison exists (`ReplayVerificationService:1103`), yet no test perturbs it. The "record" half is unguarded for evidence: the run-summary export of `request_mode`, `import_status`, `promote_status` and `promoted` can be blanked and nothing goes red. | `G09-IMPORT-CMP`, `G09-EXP-STATUS`, `G09-EXP-MODE`, `G09-EXP-PROMOTED`: whole-directory runs, failure set identical to control | A divergence case for `import_status`; an export guard over an import-only run and a promoted run, plus probes |
| MD-S036-R0031 | Clause 1 — the export shows import-only versus promoted without DB inspection — has no guard (same three export probes). Clause 2 has the same `import_status` gap. Clause 3 — unexpected promotion is a mismatch — is proven. | as above; `P09-POS` and `P09-NEG` caught | As for R0007 |
| MD-S040-R0080 | The replay half is proven. The evidence half rests on the coverage clause of `MarketDataEvidenceExportService::isReadableRun`, which can be removed without any test going red. | `P10-POS` and `P10-NEG` caught; `G10-EVID-READABLE` not caught | An export guard: a `manual_file` run claiming READABLE without coverage PASS is not exported as readable, plus a probe |
| MD-S003-R0005 | The basis checks the observation manifest and never the denominator. The obvious candidate, `SourceFailureResilienceTest::test_a_provider_failure_never_shrinks_the_denominator`, passes `expected_universe_count` into `FinalizeDecisionService`, which echoes it back (`:16`, `:278`) — a pass-through, not a computation. | inspection | An evaluator-level guard in which a provider outage with zero delivered bars keeps the expected count at the universe and counts every listing missing, plus a probe |
| MD-S003-R0002 | The explicit-resolution path is proven (P13-POS, P13-NEG). The refusal that keeps a readable `PUBLICATION_EXACT` replay with no explicit id off the current publication (`ReplayVerificationService:81-83`) is covered only by `B18ReplayContractStaticGuardTest`, a string check. With the condition disabled and the string left in place, nothing goes red, and the replay would fall back to `findReadableCurrentPublicationForRun` (`:1820`). | `G13-REFUSE`: whole directory, failure set identical to control | A behavioural guard: a readable run replayed without an explicit id is refused with `REPLAY_EXPLICIT_PUBLICATION_REQUIRED` before any publication lookup, plus a probe |

## Mistargeted bases — rebind to an existing executing guard

| Predicate | Recorded basis | Probe result | Rebind to |
|---|---|---|---|
| MD-S003-R0009 | `TemporalIdentityLayerContractTest` point-in-time and retraction tests; neither seeds a delisted listing | Making every delisted listing vanish: basis green, target red (`P11-R0009`) | `B18AntiSurvivorshipFixtureCorpusTest::test_a_listing_active_at_T_but_delisted_today_stays_in_the_historical_universe` |
| MD-S003-R0010 | Same pair; one symbol and one mapping per listing | Ignoring the symbol end, and separately the mapping end: basis green, targets red (`P11-R0010-SYM`, `P11-R0010-MAP`) | `test_a_symbol_change_resolves_to_the_symbol_and_mapping_effective_on_the_trade_date` and `test_reused_symbol_text_resolves_to_the_listing_that_held_it_on_the_trade_date` |
| MD-S050-R0046 | `SourceObservationAsKnownBoundaryTest` pair; a capability boundary cannot be proven by repository tests | An injected claim document: basis green; the corpus guard named `MD-S050-R0046` (`P12-R0046`) | `B18ReplayAdmissibilityBoundaryTest` scan and pattern tests (its own pattern at `:62`) |
| MD-S050-R0017 | Map-to-contract parse and method-existence check; both structural, as in PAIR 06 | Unbounding the sector knowledge-time filter: basis green, executing guard red (`P17-R0017-SECTOR`) | The nine executing guards the map names, one probe per item |

## Carried forward

`MD-S019-R0074` (Determinism invariant 14):
- **Clause 1, publication replay freezes the exact identities.** Its positive is
  `test_a_divergence_in_any_frozen_input_denies_pass`. `F-MD-B18-A002-013` already found that this
  test perturbs run-row identities production never persists, and moved `MD-S050-R0002` for that
  reason. The same applies here.
- **The as-known clause.** It is shown for the status root only, through
  `TemporalTradingStatusRepository`, not through the as-known replay.

It moves to INCOMPLETE under F-013; the remedy is the F-013 package.

## Kept `PROVEN` after review — probe evidence in E011

- **`MD-S085-R0452`.** A publication write was injected into the replay path. The positive and the
  negative both went red (`P10-R0452`), so a replay reason code cannot sit beside a publication
  mutation.
- **`MD-S050-R0032`.** Each of the eight preserved items was nulled once in the exporter, and the
  positive went red each time. Blanking the mismatch block on a MATCH turned the negative red.
- **`MD-S050-R0005`.** Four probes were caught:
  - an as-known metric carrying the run's publication id (impersonation);
  - the snapshot-hash comparison disabled (negative);
  - `seal_state` added to the compared fields (comparison surface);
  - a publication write injected into the as-known path (mutation).

## Also observed

Every test run adds one assertion because
`ProductionValidationRuntimeProofStaticGuardTest` asserts the encoding of every `.txt` under
`storage/app/market-data`, and the probe logs are such files. The failure sets are unaffected.

## G01: explicit-publication-identity backfill (`MD-S050-R0027`/`MD-S003-R0002`) — first bounded unit

Re-verified against current authority before any change: `Replay_Verification_Contract_LOCKED.md`
line 52 ("Resolution rules" — publication replay starts from explicit publication identity, never
latest/current; current-read verification is a separate assertion); `Historical_Replay_and_Data_Quality_Backtest.md`
line 11 (exact publication verification — resolve an explicit immutable publication, not
latest/current); `D-MD-B18-A002-005` Q3 (historical/backfill verification uses explicit
publication/fixture identity, no latest/current substitution; current-read is a separate operation,
not a fallback). Both predicates and the executable defect this finding names were confirmed against
current code before any change: `ReplayBackfillService::execute` (`:77`, `:86` at the time of review)
chose each date's publication with `findCurrentPublicationForTradeDate($tradeDate)` and passed that
pointer-derived id to `ReplayVerificationService::verifyRunAgainstFixture()`'s 4th argument, which
treats any non-null caller-supplied id as "explicit" (`:78`) with no check against what the fixture
manifest itself declares — the exact laundering channel this finding describes.

**Fix.** `ReplayBackfillService::execute` no longer calls `findCurrentPublicationForTradeDate` at
all. The fixture directory is now the declared manifest: `{fixtureRoot}/{tradeDate}/publication_{N}`
names the immutable publication that date's replay targets, discovered by scanning the date's
directory (not computed from any live lookup) and resolved via
`EodPublicationRepository::buildManifestByPublicationId()` — a pure identity-keyed lookup with no
pointer/current concept, already existing and unmodified. Zero declared `publication_<id>`
directories, more than one, a declared id that does not exist, and a declared id whose real
`trade_date` disagrees with the directory it was found under are each rejected before any replay work
(`REPLAY_BACKFILL_EXPLICIT_PUBLICATION_UNDECLARED`/`_AMBIGUOUS`/`_NOT_FOUND`/`_TRADE_DATE_MISMATCH`),
the same "reject outright, never guess" boundary this class already enforces for an unknown fixture
case. `ReplayVerificationService`'s own `REPLAY_EXPLICIT_PUBLICATION_REQUIRED` refusal (line 81-82 at
review time) needed no change — it already refuses a `READABLE`-expected `PUBLICATION_EXACT` replay
with no explicit id; only its guard was weak (a static source-text check).

**`FullRangeCurrentEvidenceReplayService` disposition — audited, not modified.** This service also
derives `publicationId` from `findCurrentPublicationForTradeDate`, matching the surface pattern this
finding describes. It is not the same defect. Its own summary declares
`'assertion_scope' => 'current_readable_publication_per_trading_date'`, and its suite name is
`market_data_full_range_current_evidence_replay` — a self-declared, explicit "verify whatever is
current, per date" operation, not a claim of historical exact identity. This is precisely the second
disposition this finding's own remedy names ("the backfill is reclassified as current-read
verification, which then needs the separate pointer assertion R0027 names") — except this service was
already built that way and already carries that separate, explicit classification, unlike
`ReplayBackfillService`, which mixed the two without any classification at all. Left unmodified;
raised here as the documented disposition, not implemented in this bounded unit.

**Tests.** `ReplayBackfillServiceTest` rewritten: the two tests that pinned pointer-derived selection
(`findCurrentPublicationForTradeDate` mocked and asserted-called) are rewritten against the corrected
contract; four new tests added covering the undeclared/ambiguous/not-found/trade-date-mismatch
refusal paths, plus a dedicated test proving a pointer move after fixture creation does not retarget
the replay (`test_pointer_moving_after_fixture_creation_does_not_retarget_the_replay`) — every test in
the file now asserts `$publications->shouldNotReceive('findCurrentPublicationForTradeDate')`.
`ReplayVerificationServiceTest` gains two new tests replacing `MD-S003-R0002`'s prior static-text-only
guard: a negative proving the refusal fires with no explicit id anywhere, before any
pointer/current-lookup method is even stubbed (so an unexpected call itself fails the test), and a
positive control proving a genuinely explicit id resolves successfully — the boundary is not
reject-everything.

**Falsifiability.** Three live mutation/restore probes, each byte-restored from a pre-mutation copy
and sha256-verified identical before/after, each control re-run green: reintroducing the removed
pointer-derived-identity code into `ReplayBackfillService` turned 7 of the file's 8 tests red (the
eighth, the unknown-fixture-case guard, fires earlier in the method and is correctly unaffected);
removing the `trade_date` consistency check turned its dedicated test red, falling through to an
unmocked `verifyRunAgainstFixture` call rather than refusing; removing
`ReplayVerificationService`'s `REPLAY_EXPLICIT_PUBLICATION_REQUIRED` refusal turned the new R0002
negative test red on an unstubbed downstream call (`ReplayResultRepository::nextReplayId()`) rather
than silently resolving a pointer-substituted publication.

`MD-S050-R0027`/`MD-S003-R0002` moved `INCOMPLETE` → `PROVEN`, reviewed and bound independently.
Proof basis: `PROVEN` 78 → 80, `INCOMPLETE` 36 → 34, confirmed via PHP parse. No traceability-matrix
`coverage_status`/`SATISFIED`/denominator change. `MD-DEP-0017` remains `BLOCKING`.

**This finding remained `OPEN — REMEDIATION_IN_CONSOLIDATED_PACKAGE`** after G01, with 9 of its own
14 predicates `INCOMPLETE`: `MD-S036-R0007`/`MD-S036-R0031`/`MD-S040-R0080`/`MD-S003-R0005` (guard
gaps), `MD-S050-R0017` (G05 full-parent aggregate), `MD-S003-R0009`/`MD-S003-R0010`/`MD-S050-R0046`
(rebind-only), `MD-S019-R0074` (F-013 carry-forward).

## G04: import/promote record and compare (MD-S036-R0007/MD-S036-R0031/MD-S040-R0080) — 2026-09-23T12:34:25+07:00

Bounded to exactly three predicates, per the consolidated remediation package's own G-label taxonomy
(the same numeric order this attempt already used for F-015: G03→G04). Reviewed independently
against current implementation before any guard was written — the prior reconstruction's
`PROOF_GUARD_GAP` classification for all three was confirmed, not trusted.

**`MD-S036-R0007`.** "Record and compare request mode, import status, promote status, source mode,
pointer switch status, and publication state." `request_mode`/`source_mode`/`publishability_state`
were already proven load-bearing by `B18ReplayComparisonExhaustivenessTest`'s perturbation table. The
real `compareField()` calls for `import_status`/`promote_status`/`promoted`/`pointer_switched` in
`ReplayVerificationService::compareExpectedAndActual` already existed and were confirmed correctly
implemented — each genuinely derived from the real run row, not the expected side — but
`expectedReplayResult()`'s `expected_run_context` array literal never included these three keys at
all, so `compareField()`'s null-expectation skip left real code permanently unexercised by every
existing test. **Fixed with three new perturbation entries**, paired with matching non-divergent
baseline defaults so all 13 pre-existing perturbations remain provably unaffected.

**`MD-S036-R0031`.** Three clauses. Clause 3 ("unexpected import promotion must be a replay
mismatch") was already proven prior to this unit. Clause 2 (replay compares 7 named fields) shares
R0007's exact fix for its unproven fields. **Clause 1** — "Evidence export must show whether a run
is import-only or promoted without requiring direct DB inspection" — was genuinely unguarded:
`MarketDataEvidenceExportService::buildRunSummary`/`deriveImportStatus`/`derivePromoteStatus`
confirmed by direct reading to be pure functions of the already-fetched `$run` row (neither issues a
query nor takes a repository); grep confirmed zero existing test ever referenced
`import_promote_boundary`, `deriveImportStatus`, or `derivePromoteStatus` at all. **New test** exports
both an import-only run and a promoted run through the same real, unmocked path, with neither
collaborator mock stubbing anything import/promote-specific beyond what every export already
structurally requires, proving `request_mode`/`import_status`/`promote_status`/`promoted`/
`import_promote_boundary.boundary_rule` all differ correctly between the two runs.

**`MD-S040-R0080`.** The replay half was already proven. `MarketDataEvidenceExportService::isReadableRun()`
confirmed by direct reading to already require `coverage_gate_state=PASS` alongside
`terminal_status=SUCCESS` and `publishability_state=READABLE` — but every existing test declaring
`coverage_gate_state=FAIL` also declared `terminal_status=HELD`, so the coverage clause's own
necessity was never isolated: a broken coverage clause and a correct one would have produced
byte-identical outcomes on every existing fixture. **New test** holds `terminal_status=SUCCESS` and
`publishability_state=READABLE` constant (import genuinely succeeded, run superficially looks
`READABLE`) and varies only `coverage_gate_state` to `FAIL`, proving the coverage clause
independently load-bearing, paired with a positive control.

**No production code changed for any of the three predicates** — every implementation was already
correct; only test coverage was added, across `B18ReplayComparisonExhaustivenessTest.php` and
`MarketDataEvidenceExportServiceTest.php`.

**Falsifiability.** Four live mutation/restore probes, all caught on their own distinct assertion, all
byte-restored via `git checkout` (each file confirmed byte-identical to committed HEAD both before and
after, since this session's F-016 work had not otherwise touched either file), all sha256-verified,
all controls green: removing the four `compareField()` calls for
`import_status`/`promote_status`/`promoted`/`pointer_switched` turned exactly the 3 new perturbation
cases red out of 17 run under that dataProvider filter; removing the `boundary_rule` ternary's
`request_mode` branch turned the import/promote-distinguishing test red on that exact field;
broadening the `$promoted` computation to ignore `publishability_state`/pointer state turned the same
test red on `promote_status`; removing the `coverage_gate_state=PASS` clause from `isReadableRun()`
turned exactly the R0080 positive test red while its negative control stayed green. **A fifth
mutation attempt was discarded rather than counted as a false proof**: disabling one dead-code branch
inside `derivePromoteStatus` specific to the import-only fixture's `terminal_status=SUCCESS` was found
non-discriminating — both branches converge to the same value for that terminal status — so it proved
nothing about that line; the line remains covered by R0007's own generic field-comparison proof
instead.

`MD-S036-R0007`/`MD-S036-R0031`/`MD-S040-R0080` moved `INCOMPLETE` → `PROVEN`, reviewed and bound
independently. Proof basis: `PROVEN` 80 → 83, `INCOMPLETE` 34 → 31, confirmed via PHP parse. No
traceability-matrix `coverage_status`/`SATISFIED`/denominator change. `MD-DEP-0017` remains
`BLOCKING`.

Targeted suites green: `B18ReplayComparisonExhaustivenessTest` 42/42 (186 assertions),
`MarketDataEvidenceExportServiceTest` 8/8 (222 assertions). Governance self-tests green:
`GovernanceGateReadOnlyExecutionTest`+`ScopeBoundaryAndOrchestrationCompletionTest` 9/9,
`FindingRecordConsistencyTest` 3/3. Full application suite not run, not required — production code is
byte-identical to before this unit.

**This finding remains `OPEN — REMEDIATION_IN_CONSOLIDATED_PACKAGE`.** 6 of its own 14 predicates
remain `INCOMPLETE`: `MD-S050-R0017` (G05 full-parent aggregate), `MD-S050-R0046` (G08),
`MD-S003-R0009`/`MD-S003-R0010`/`MD-S003-R0005` (G09 rebind-only), `MD-S019-R0074` (F-013
carry-forward, package-labelled G01). Not started in this unit.

---

## G05 remediation (E-MD-B18-A002-060, 2026-09-23) — `MD-S050-R0017`, full-parent aggregate

Third bounded unit, scoped to exactly one predicate: `MD-S050-R0017`, the anti-future full-parent
aggregate. Treated as a genuine nine-member aggregate, not a simple rebind — the parent is proven
only if all nine named members are independently, executably guarded, one member's failure fails
only that member, and the other eight (plus the map-completeness check itself) stay green.

**Reconstruction.** The nine members were reconstructed from `Replay_Verification_Contract_LOCKED.md`
line 37 (Anti-future and anti-survivorship rules) directly — today's `is_active`, current symbol,
current sector, current suspension/status, latest calendar correction, later corporate-action
revision, later factor, current config, latest provider mapping — parsed the same way the guard test
itself parses it, rather than trusted from this finding's own gap-table row or the pre-existing draft
proof-basis narrative for this predicate. `B18AntiFutureResolutionTest.php` was found to already
exist, fully committed under a commit that predates this remediation session entirely, and to already
implement the correct structure: `antiFutureMap()` binds all nine items to guards, and
`test_the_anti_future_map_names_exactly_what_the_contract_names` asserts the map and the live-parsed
contract sentence name exactly the same set.

**Audit before any guard was trusted.** Seven members already had executing, DB-backed guards spread
across `B18AntiSurvivorshipFixtureCorpusTest`, `AsKnownReplayBoundaryTest` and
`B18AsKnownSnapshotIsolationTest`. Two — current sector and latest provider mapping — were previously
covered only by `AsKnownReplayBoundaryTest::test_every_temporal_root_accepts_a_knowledge_cutoff`, a
reflection check that a cutoff parameter exists and is accepted, which an ignored parameter also
passes; both are proven inside `B18AntiFutureResolutionTest` itself against real, unmocked
repositories (`SectorClassificationRepository::resolveSectorContextForTickerIds`,
`TemporalIdentityRepository::resolveProviderContext`). Every one of the nine members' production
implementation was read and confirmed already correct before any probe was run. **No production code
was changed for this predicate.**

**Nine required discriminating probes, one per member, all caught:**

| # | Member | Implementation path | Probe |
|---|---|---|---|
| 1 | today's `is_active` | `TemporalIdentityRepository::baseIdentityQuery` — `delisted_date`/`delisted_recorded_at` knowledge-time OR-clause | Removed the `delisted_recorded_at` branch |
| 2 | current symbol | same method — `md_listing_symbols` join `effective_to`/`retracted_at` conditions | Removed those conditions |
| 3 | current sector | `SectorClassificationRepository::resolveSectorContextForTickerIds` — `recorded_at <= $knownAt` | Removed the clause |
| 4 | current suspension/status | `TemporalTradingStatusRepository::resolveStatus` — `knownAt`-gated branch | Disabled the condition |
| 5 | latest calendar correction | `MarketCalendarRepository::terminalRevisionRowsForDate` — `recorded_at <= knownAt` (disambiguated from an unrelated duplicate-looking line elsewhere in the file) | Removed the correct clause |
| 6 | later corporate-action revision | `EventRiskSourceRepository::applyKnowledgeCutoff` — the legacy-table path the fixture actually exercises (confirmed by inspection, not the unexercised V2 path) | Made it a no-op |
| 7 | later factor | `AsKnownReplaySnapshotService::eventFactorContext` — `md_adjustment_factor_sets` `recorded_at <= $knowledgeCutoff` | Hardcoded the cutoff to a future date |
| 8 | current config | `MarketDataConfigSnapshotRepository::governingSnapshot` — `knownAt`-gated `recorded_at` clause | Disabled the condition |
| 9 | latest provider mapping | `TemporalIdentityRepository::resolveProviderContext` — conditional `pm.recorded_at`/`pm.retracted_at` block | Replaced with unconditional `whereNull('pm.retracted_at')` |

Each probe was byte-restored via `git checkout` and sha256-verified identical to its pre-probe
baseline both before and after, each turned exactly its own target test red while sibling members
stayed green, and the combined control suite across all four touched files (28 tests, 175 assertions)
was confirmed green before the probe series began and after every restore. Zero probes discarded as
non-discriminating.

**Aggregate semantics.** A tenth, supplementary probe (not counted toward the nine) re-verified the
map-completeness scaffold test itself by mutating `Replay_Verification_Contract_LOCKED.md` to add a
tenth, unmapped anti-future item; the test correctly turned red on the resulting array-diff, byte-
restored and sha256-verified. A direct attempt to probe the same scaffold from the test side —
temporarily removing one mapped entry from `antiFutureMap()` — was blocked by the Claude Code
auto-mode security classifier (reason: "Security Test Removal") before any test ran against the
mutation; the file was immediately restored and confirmed sha256-identical to its pre-edit state, so
no coverage was ever weakened, and the authority-document-side probe above independently demonstrates
the same class of falsifiability. Full-parent aggregate semantics reused the repository's existing
map-completeness-plus-per-member-execution pattern (the same shape already used for `MD-S040`'s
ten-member list and `MD-S050-R0040`'s eight-fixture classification), not a new parallel mechanism.

**No current/latest substitution**, audited per member using exact repository semantics: each of the
nine guarding clauses is conditioned on the caller-supplied `knownAt`/`knowledgeCutoff` argument
rather than defaulting to an unfiltered current read, which is exactly what disabling that one
condition in each probe demonstrated by exposing the later fact while the other eight stayed silent.

`MD-S050-R0017` moved `INCOMPLETE` → `PROVEN`, reviewed independently. Proof basis: `PROVEN` 83 → 84,
`INCOMPLETE` 31 → 30, confirmed via PHP parse. (The promotion edit was initially misplaced into the
`WITHDRAWN_NOT_APPLICABLE` array — an audit-only array never read as current basis — during
authoring; caught immediately by re-verifying the PHP-parsed count and key membership, corrected
before evidence was issued, re-verified clean.) No traceability-matrix `coverage_status`/`SATISFIED`/
denominator change. `MD-DEP-0017` remains `BLOCKING`.

Targeted suites green: `B18AntiFutureResolutionTest` 4/4 (10 assertions),
`B18AntiSurvivorshipFixtureCorpusTest` 10/10 (47 assertions), `AsKnownReplayBoundaryTest` 11/11 (91
assertions), `B18AsKnownSnapshotIsolationTest` 3/3 (27 assertions). Governance self-tests green:
`GovernanceGateReadOnlyExecutionTest` 9/9, `ScopeBoundaryAndOrchestrationCompletionTest` 8/8,
`FindingRecordConsistencyTest` 3/3, `ClassificationConsistencyGateTest` 20/20,
`TraceabilityApplicabilityGateTest` 11/11, `PromotedPredicateProofGateTest` 8/8. Proof self-test
(`MarketDataReplayVerificationProofSelfTest.php`) re-run: overall status remains `FAIL`, driven
entirely by the pre-existing baseline scenario (unrelated, still-open findings' predicates only); all
nine injected-mutation scenarios still pass, and `MD-S050-R0017` no longer appears in the baseline's
`PREDICATE_WITHOUT_REVIEWED_BASIS` list — confirmed not a regression. Full application suite not run,
not required — zero production code changed.

**This finding remains `OPEN — REMEDIATION_IN_CONSOLIDATED_PACKAGE`.** 5 of its own 14 predicates
remain `INCOMPLETE`: `MD-S050-R0046` (G08), `MD-S003-R0009`/`MD-S003-R0010`/`MD-S003-R0005` (G09
rebind-only), `MD-S019-R0074` (F-013 carry-forward, package-labelled G01). Not started in this unit.
