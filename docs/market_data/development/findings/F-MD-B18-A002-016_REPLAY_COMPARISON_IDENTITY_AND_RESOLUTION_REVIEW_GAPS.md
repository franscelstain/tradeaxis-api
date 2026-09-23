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

**This finding remains `OPEN — REMEDIATION_IN_CONSOLIDATED_PACKAGE`.** 4 of its own 14 predicates
remain `INCOMPLETE`: `MD-S003-R0009`/`MD-S003-R0010`/`MD-S003-R0005` (G09), `MD-S019-R0074` (F-013
carry-forward, package-labelled G01). G08 (`MD-S050-R0046`) closed in this unit.

## G08: capability boundary source observation (MD-S050-R0046) — 2026-09-23T14:15:30+07:00

**Exact requirement (Replay_Verification_Contract_LOCKED.md:91):** "That the source observation was faithful. Provider error inside an immutable observation is frozen by the same mechanism that guarantees reproducibility."

**Classification:** REBIND_ONLY — corpus pattern guard already exists in the code.

**Existing guard and corpus:** `B18ReplayAdmissibilityBoundaryTest` at lines 62-64 contains the pattern guard for MD-S050-R0046:

```php
'MD-S050-R0046' => [
    '/replay'.$this->gap(40).'(proves|confirms|establishes)'.$this->gap(30).'(source\s+)?observation'.$this->gap(20).'(was|is)\s+(faithful|accurate|correct)/i',
    'A successful replay proves the source observation was faithful.',
],
```

The pattern scans the active corpus to forbid any claim of the form ('replay proves the source observation was faithful, accurate, or correct'). Nothing in the active corpus may make this assertion. The test `test_no_active_surface_cites_a_replay_verdict_for_something_replay_cannot_establish` executes this guard against all files in `/docs/market_data` and `/app`.

**Discriminating probe:** Injected a temporary file `docs/market_data/records/evidence/PROBE_MD_S050_R0046_INJECTED.md` containing the forbidden claim text. The scan test turned red with correct pattern match (detecting the injection). Removed the file; test turned green again. Pattern is load-bearing and discriminating.

**Proof and validation:** 
- Control baseline: PASS
- Probe target red: PASS
- Probe restoration green: PASS
- Guard tests: `test_every_pattern_matches_the_claim_it_forbids_and_spares_the_denial` confirms the pattern fires on the claim and spares denials (PASS)
- Corpus clean: No active surface makes the forbidden assertion
- Governance: `PromotedPredicateProofGateTest` 8/8 PASS

**Production code impact:** None — zero production code changed.

**Evidence record:** E-MD-B18-A002-061, registered in DOCUMENT_ID_REGISTRY (MD-DOC-01194), DOCUMENT_ROLE_REGISTRY, CURRENT_VERIFICATION_REGISTRY, and WORK_RECORD_REGISTRY.

**Proof-basis state:** Promoted MD-S050-R0046 from INCOMPLETE to PROVEN. PROVEN 84→85, INCOMPLETE 30→29.

**This finding remains `OPEN — REMEDIATION_IN_CONSOLIDATED_PACKAGE`** with 4 of its own 14 predicates still `INCOMPLETE`.

## G09: survivorship identity and denominator preservation (MD-S003-R0009/MD-S003-R0010/MD-S003-R0005) — 2026-09-23T15:34:21+07:00

Three predicates, reviewed and proven independently rather than bulk-promoted. G09 closes because all
three legitimately earned `PROVEN` on their own; had any one failed, the other two would still be
independently promotable and G09 would remain `OPEN` pointing at the unresolved predicate.

### MD-S003-R0009 — inactive-now/active-then listing remains in the historical universe

**Requirement** (`Historical_Replay_and_Data_Quality_Backtest.md:24`, section "Temporal identity and
status"): "inactive-now/active-then listing remains in the historical universe."

**Classification:** `REBIND_ONLY`, confirmed after independent review — the prior basis
(`TemporalIdentityLayerContractTest` point-in-time/retraction pair) never seeds a delisted listing, so
it is structurally blind to the survivorship claim it was recorded against, exactly as the F-016 remedy
table states.

**Implementation path:** `TemporalIdentityRepository::baseIdentityQuery`'s `delisted_date > $tradeDate`
OR-branch (the same knowledge-time-gated clause probed as member 1 of `MD-S050-R0017` in G05, here
proven for its own distinct predicate).

**Rebind target:**
`B18AntiSurvivorshipFixtureCorpusTest::test_a_listing_active_at_T_but_delisted_today_stays_in_the_historical_universe`.
Seeds a listing delisted 2025-06-30 alongside a live control; asserts the historical read (2024-05-02,
pre-delisting) includes the delisted listing and the current read (2026-03-02) excludes it. Constructs
`TemporalIdentityRepository` directly with no `ProducerInputScope` active, so it exercises the real
DB-backed `baseIdentityQuery()` path, not a mocked/active-producer branch.

**Discriminating probe:** Removed the `delisted_date > $tradeDate` OR-branch, leaving only
`whereNull('l.delisted_date')`. Target test turned red (historical-date assertion failed — the delisted
listing vanished from the as-of-T universe). 9 of the file's other 9 tests stayed green. Byte-restored;
sha256 unchanged (`cdd98b5...84`). Control re-run: 10/10 green (47 assertions).

**Production code impact:** None.

### MD-S003-R0010 — symbol change and symbol reuse resolve through stable listing identity

**Requirement** (`Historical_Replay_and_Data_Quality_Backtest.md:25`, same section): "symbol change and
symbol reuse resolve through stable listing identity."

**Classification:** `REBIND_ONLY`, confirmed after independent review, not assumed from R0009's
outcome — the prior basis has one symbol and one mapping per listing, never a change or a reuse.

**Implementation path:** `TemporalIdentityRepository::resolveProviderContext`'s `pm.effective_to`
upper-bound clause on the provider-mapping join (the F-016 remedy table's own "mapping end",
`P11-R0010-MAP`).

**Rebind targets:**
`B18AntiSurvivorshipFixtureCorpusTest::test_a_symbol_change_resolves_to_the_symbol_and_mapping_effective_on_the_trade_date`
(one listing renamed OLDSYM→NEWSYM with a parallel provider-mapping rename; asserts pre-change,
post-change, and fail-closed-on-wrong-side) and
`test_reused_symbol_text_resolves_to_the_listing_that_held_it_on_the_trade_date` (two listings sharing
literal text "REUSED" in disjoint windows; asserts both the provider-mapping path and the
symbol-interval path independently).

**Discriminating probe:** Removed the `pm.effective_to` upper bound from the provider-mapping join.
Both target tests turned red — `PROVIDER_SYMBOL_MAPPING_AMBIGUOUS`, since the old and new mapping rows
both matched the post-change date once the upper bound was gone. 8 of the file's other 8 tests stayed
green (including the unrelated R0009 delisting fixture). Byte-restored; sha256 unchanged (`cdd98b5...84`).
Control re-run: 10/10 green (47 assertions).

**Production code impact:** None.

### MD-S003-R0005 — provider outage remains missing delivery and cannot shrink the denominator

**Requirement** (`Historical_Replay_and_Data_Quality_Backtest.md:17`, section "Degraded acquisition and
expectation"): "provider outage remains missing delivery and cannot shrink the denominator."

**Classification:** `PROOF_GUARD_GAP` — **not** `REBIND_ONLY`, verified rather than assumed per explicit
instruction. This predicate required adding a new guard, not merely rebinding to an existing one.

**Prior basis defect:** `SourceFailureResilienceTest::test_a_provider_failure_never_shrinks_the_denominator`
sets `expected_universe_count` directly in its own fixture array and asserts `FinalizeDecisionService`
echoes it back. Direct reading of `FinalizeDecisionService::evaluate()` (lines 16 and 278) confirmed this
field is a pure pass-through of the input array key — never computed. The fixture proves a pass-through
is a pass-through, not that the real universe computation survives a provider outage.

**Audit result — no production defect found.** `CoverageGateEvaluator::evaluateCaptured()` computes
`expected_universe_count = count($universeByTickerId)` from
`TickerMasterRepository::getUniverseForTradeDate()` (the ticker-master/temporal-identity universe),
filtered only by verified full-session suspension, computed strictly *before* delivered/available ticker
ids are even loaded from the artifact repository. This is architecturally independent of provider
delivery, consistent with `Coverage_Universe_Definition_LOCKED.md`'s denominator = EXPECTED + UNKNOWN
rule and its explicit prohibition on excluding dormant/quiet tickers (`COVERAGE_DORMANT_TICKERS_EXCLUDED`
is deprecated).

**Guard added:**
`CoverageGateEvaluatorTest::test_evaluator_keeps_the_full_universe_as_the_denominator_when_the_provider_delivers_nothing`.
Drives the real `CoverageGateEvaluator::evaluate()` (only the two repository collaborators are mocked,
matching the file's existing pattern) through a 900-ticker universe with zero delivered ticker ids (a
total outage). Asserts `expected_universe_count` stays 900, `available_eod_count` is 0,
`missing_eod_count` is 900 (every listing counted missing, none silently excluded), and
`coverage_gate_status` is `FAIL`.

**Discriminating probe:** Changed the evaluator's returned `expected_universe_count` from the real
universe count to the delivered-observation count. Both the new total-outage guard (900 expected vs. 0
observed) and the pre-existing partial-shortfall guard
`test_evaluator_returns_fail_when_available_is_below_threshold` (900 expected vs. 854 observed) turned
red, while the other 6 of 8 tests in the file stayed green. Byte-restored; sha256 unchanged
(`4a58b78...260`). Control re-run: 8/8 green (73 assertions).

**Production code impact:** None. One new test method added; no application code changed.

**Evidence record:** `E-MD-B18-A002-062`, registered in `DOCUMENT_ID_REGISTRY` (`MD-DOC-01195`),
`DOCUMENT_ROLE_REGISTRY`, `CURRENT_VERIFICATION_REGISTRY`, and `WORK_RECORD_REGISTRY`. E062 does not rely
on E061 for any proof.

**Proof-basis state:** Promoted `MD-S003-R0009`, `MD-S003-R0010`, `MD-S003-R0005` from `INCOMPLETE` to
`PROVEN`, each independently. `PROVEN` 85→88, `INCOMPLETE` 29→26.

**This finding remains `OPEN — REMEDIATION_IN_CONSOLIDATED_PACKAGE`** with exactly 1 of its own 14
predicates still `INCOMPLETE`: `MD-S019-R0074` (F-013 carry-forward, package-labelled G01). Not started
in this unit.

## Correction of issued E061 (E-MD-B18-A002-063) — 2026-09-23T15:57:33+07:00

`E-MD-B18-A002-061` (G08) was issued in commit `0caaf38` with four defects. It is
`IMMUTABLE_AFTER_ISSUE`, so it is not edited: `DOCUMENT_CHANGE_POLICY.md` §3 requires a new correlated
record, and `DOCUMENT_RECORDING_STANDARD.md` §1 says evidence correction creates new evidence. An earlier
working-tree edit that re-escaped its `pattern` field was reverted; E061 is byte-identical to the
committed blob (sha256 `3ad2e42e…8576`). `E-MD-B18-A002-063` corrects it in the same shape E037 used to
correct E036:

- **E061-D1:** the `pattern` field transcribes the regex with single backslashes, which is not valid
  JSON. The authoritative value is `B18ReplayAdmissibilityBoundaryTest::forbidden()['MD-S050-R0046']`;
  E063 points there rather than transcribing it again.
- **E061-D2:** its `issued_at` (14:15:30+07:00) was estimated; the clock-derived write time recorded
  with it is 14:51:51+07:00.
- **E061-D3:** its rows in `DOCUMENT_ROLE_REGISTRY.csv`, `CURRENT_VERIFICATION_REGISTRY.csv` and
  `WORK_RECORD_REGISTRY.csv` were malformed (2/6/8 fields against 8/10/15). These registries are
  `MUTABLE_TRACEABLE` and their standard requires the registration, so the rows were replaced with
  schema-complete ones; E063 is the trace.
- **E061-D4:** the same commit left `CURRENT_STATE.md` with its first bytes overwritten by the
  generator's echoed path, because the generator was run with stdout redirected into the file it
  writes. Regenerated by running it without redirection; two consecutive runs are byte-identical.

No proof impact: `MD-S050-R0046`'s basis names the executing guards, which pass.

**Unresolved — E061-U1.** `MarketDataDocumentationIntegrityGate` parses every JSON file under
`docs/market_data` with no exception path, and `DOCUMENT_INTEGRITY_EXCEPTION_REGISTRY.json` has no
defined semantics and is not read by the gate. With E061 immutable, `JSON_PARSE` stays red on E061
alone. Resolving that changes a gate requirement, so it needs an owner decision; it is not resolved
here.

## E061-U1 resolved (F-MD-B18-A002-022) — 2026-09-23

The documentation-gate conflict above was raised as `F-MD-B18-A002-022` and resolved by controlled
revision `DOC-CHG-20260923-001` under owner decision `D-MD-B18-A002-007`, proven in
`E-MD-B18-A002-064`. E061 is admitted through exception `MD-DOCEX-0001`, which binds it at its
retained sha256 to `E-MD-B18-A002-063` defect `E061-D1`; its raw `JSON_PARSE` failure is still
reported, and any unexcepted malformed JSON remains a hard `FAIL`. E061 is unchanged.

**This finding remains `OPEN — REMEDIATION_IN_CONSOLIDATED_PACKAGE`** with exactly 1 of its own 14
predicates still `INCOMPLETE`: `MD-S019-R0074` (F-013 carry-forward, package-labelled G01).
