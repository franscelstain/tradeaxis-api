# Finding — `F-MD-B18-A002-001`

- ID: `F-MD-B18-A002-001`
- Raised by: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Owner stage: **`MD-B18`** (the guard was written inside this attempt)
- Raised at: 2026-09-10T00:10:00+07:00
- Severity: `P1`
- Status: `RESOLVED`
- Class: `PROOF_DOES_NOT_ESTABLISH_THE_PREDICATE`
- Blocks: nothing further; it blocked closure of `MD-B18-A002` and made the market-data suite red until the remediation below
- Blocks strategy change: `NO`

## Statement

`B18AsKnownSnapshotIsolationTest` boots the market-data SQLite database and then replaces five
persistence repositories with mocks:

| Mocked | Consequence |
|---|---|
| `TemporalIdentityRepository` | `readProjectedUniverseAsOf()` returns `EARLY`/`LATE` chosen by the mock's own comparison of the cutoff |
| `MarketCalendarRepository` | `sessionContext()` returns `calendar-early`/`calendar-late` the same way |
| `TemporalTradingStatusRepository` | `ACTIVE_EARLY`/`ACTIVE_LATE` the same way |
| `MarketDataConfigSnapshotRepository` | mocked in the second test |
| `SourceObservationRepository` | mocked |

The assertions

```
assertSame('EARLY',        $earlyFirst['temporal_universe'][0]['ticker_code']);
assertSame('ACTIVE_EARLY', $earlyFirst['trading_status_contexts'][701]['status_code']);
assertSame('calendar-early', $earlyFirst['calendar_context']['calendar_revision']);
```

read back values the mock was instructed to return **on the basis of the cutoff it was handed**. The
knowledge-time decision the predicate is about is made inside the mock, not inside
`AsKnownReplaySnapshotService`. The service could ignore the cutoff entirely for identity, calendar
and status and every one of those assertions would still pass, because the mock would still have
branched on the cutoff it received.

This is the repository's own rule, and its own guard catches it.
`LifecycleProofIsNotMockedTest::test_db_backed_tests_only_mock_the_external_source_boundary` states
it directly:

> A DB-backed test may only stand in for the outside world. Mocking an application service or a
> persistence repository means the persisted state being asserted was produced by the mock, not by
> the code under test.

The full market-data suite was **2132 tests, 1 failure**, and this was the failure.

## What is and is not affected

Not all of the test is circular. These parts execute real code and remain sound:

- `MarketDataConfigSnapshotRepository` is constructed for real in the first test, so
  `config_snapshot_id` selection and `roc_lookback_days` 20 versus 21 are genuine;
- corporate-action revisions, factor sets and factors are real rows in the SQLite database, so the
  `assertCount(1, …)` / `assertCount(2, …)` pairs are genuine;
- `snapshot_hash` stability across two captures at the same cutoff, its difference at a later
  cutoff, and `boundRowCounts()` proving capture writes nothing, are all genuine.

The circular part is exactly the identity, calendar and status triple — which is the part
`MD-S003-R0021` ("later master/event/status/calendar/config/formula/factor revisions are invisible")
and `MD-S005-R0096` ("as-known replay excludes later revisions") are most about.

## Affected predicates

| Row | Slot | Guard |
|---|---|---|
| `MD-S003-R0021` | positive, negative | `test_every_later_revision_kind_is_bound_to_an_executing_guard`, `test_an_incomplete_historical_config_snapshot_is_refused_instead_of_using_live_config` |
| `MD-S003-R0022` | positive, negative | `test_a_later_cutoff_exposes_later_revisions_without_rewriting_the_earlier_snapshot`, `test_every_later_revision_kind_is_bound_to_an_executing_guard` |
| `MD-S005-R0096` | positive, negative | as `MD-S003-R0021` |

`test_every_later_revision_kind_is_bound_to_an_executing_guard` is a mapping test: it delegates five
of its seven revision kinds to `AsKnownReplayBoundaryTest`, which is not mocked and is sound. The two
kinds it keeps for itself — `formula` and `factor` — point at
`test_a_later_cutoff_exposes_later_revisions_without_rewriting_the_earlier_snapshot`, and those two
are among the genuine assertions listed above. So the mapping guard is weakened rather than void.

## Remediation

Replace the five repository mocks with seeded rows, keeping every existing assertion. The fixtures
already exist and are known to work: `AsKnownReplayBoundaryTest` seeds identity, calendar and status
against these same repositories with a cutoff, and `B18AsKnownTemporalSequenceTest` seeds a
knowledge-time status sequence against `TemporalTradingStatusRepository`. Nothing new has to be
invented; the mocks have to be removed.

## Remediation applied

All five mocks were removed. `B18AsKnownSnapshotIsolationTest` now constructs
`TemporalIdentityRepository`, `MarketCalendarRepository`, `TemporalTradingStatusRepository`,
`MarketDataConfigSnapshotRepository` and `SourceObservationRepository` for real, and the
early/late difference is produced by seeded rows whose only distinguishing property is
`recorded_at`:

- **master** two listings, one recorded 2023-01-02 and one recorded 2026-05-01; the early
  universe is asserted to be exactly `['EARLY']` and the later one to contain both;
- **calendar** two revisions for the trade date, the later superseding the earlier, asserted
  by `revision_uid` -- note the mock had returned a `calendar_revision` key the real
  repository never produces, which was itself evidence the assertion was circular;
- **status** a suspension recorded 2023-01-05 and the revision recorded 2026-05-01 that closes
  its interval before the trade date, so the early cutoff still sees `SUSPENSION` and the
  later one does not;
- **config** the unusable payload is written into the real `md_config_snapshots` row rather
  than returned by a mocked repository, so `REPLAY_CONFIG_SNAPSHOT_PAYLOAD_INVALID` is the
  service refusing a row it genuinely cannot use.

The config, formula, factor, snapshot-hash and no-write assertions were already genuine and are
unchanged.

### Proof the remediation is load-bearing

| Probe | Result |
|---|---|
| `readProjectedUniverseAsOf` called without the cutoff | fails: "a listing recorded in May cannot be in a universe read as known in April" |
| `sessionContext` and `resolveForListing` called without the cutoff | fails on the calendar `revision_uid` |

None of these could fail before. `LifecycleProofIsNotMockedTest` now passes, and the affected
as-known suite is 25/25.

## How it was found

Not by reading the guard. By running the full market-data suite as a control after unrelated work.
The guard passes in isolation and passes in every filtered run that does not also include
`LifecycleProofIsNotMockedTest`, which is why it was written, committed to the proof basis and
reported as covering three predicates without anything objecting.

That is the same shape as `F-MD-B19-A001-002`: a guard that is green, that names the right subject,
and that does not establish the predicate it is filed against.

## Concurrency note

`B18AsKnownSnapshotIsolationTest`, the `AsKnownReplaySnapshotService` changes it exercises, and the
`MD-S003-R0021` / `R0022` / `MD-S005-R0096` proof-basis entries were written into this working tree
by a writer other than the session raising this finding, while that session was working. The git
index was also reset during the same window with `HEAD` unmoved. Two writers on one attempt is the
condition under which this kind of defect reaches the canonical record unreviewed, and it should be
resolved before either continues.
