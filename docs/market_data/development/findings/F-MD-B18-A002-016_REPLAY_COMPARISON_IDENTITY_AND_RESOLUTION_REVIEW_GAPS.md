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

**This finding remains `OPEN — REMEDIATION_IN_CONSOLIDATED_PACKAGE`.** 9 of its own 14 predicates
remain `INCOMPLETE`: `MD-S036-R0007`/`MD-S036-R0031`/`MD-S040-R0080`/`MD-S003-R0005` (guard gaps),
`MD-S050-R0017` (G05 full-parent aggregate), `MD-S003-R0009`/`MD-S003-R0010`/`MD-S050-R0046`
(rebind-only), `MD-S019-R0074` (F-013 carry-forward). Not started in this unit.
