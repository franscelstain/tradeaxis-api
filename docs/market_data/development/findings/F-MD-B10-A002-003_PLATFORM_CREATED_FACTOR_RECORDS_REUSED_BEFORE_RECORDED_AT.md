# Finding - A later run can bind platform-created factor records recorded after its knowledge cutoff

- ID: F-MD-B10-A002-003
- Stage / Attempt / Baseline / Epoch: MD-B10 / MD-B10-A002 / MD-B10-A002-BL001 / MD-REBASELINE-20260820-001
- Raised: 2026-10-01T11:19:20+07:00
- Severity: P2 - as-known visibility of platform-created records; published content is unaffected
- Status: OPEN
- Class: KNOWLEDGE_CUTOFF_VISIBILITY_EXPOSURE
- Related: D-MD-B10-A002-003 (decision 1A), E-MD-B10-A002-013, F-MD-B18-A002-023, CI-MD-B10-A002-001
- Remediation owner: MD-B18 (MD-B18-A002), owner of the as-known predicates below; not resumed by this record

## Observed

D-MD-B10-A002-003 decision 1A makes `recorded_at` of platform-created source-scale assessments, factor
sets and factor decisions provenance and visibility time: it is not semantic content, it still controls
when later runs may observe the record, and it does not authorize bypassing a knowledge cutoff. The
owner asked that an existing fallback contradicting this be recorded, not normalized into V2.

Two producer paths in `AdjustmentFactorSetService::produceForPublication` observe such records without
their visibility time:

1. `latestAssessment()` selects assessments with `recorded_at <= knowledge_cutoff_at`. When it finds
   none, `recordUnknownAssessment()` derives the UNKNOWN assessment's `assessment_uid` and, if a row with
   that uid exists, returns it whatever its `recorded_at`.
2. An existing factor set with the same `factor_set_uid` is reused whatever its `recorded_at`.

The B18 C1 capture verifier encodes the same exemption: `ProducerEventFactorCapture` admits the fallback
assessment by uid and state with no cutoff check, while it checks the cutoff for every other assessment.

## Reproduction (executed)

Isolated MariaDB database cloned from the migrated test schema, retained IKPM foundation identity,
production producer invoked directly (raw `diagnostics/fallback_exposure.json` of E-MD-B10-A002-013):

- Run 1, knowledge cutoff `2026-09-29 00:38:15`, executed at `2026-09-29 01:00:00`, recorded two UNKNOWN
  assessments at `01:00:00` and one factor set.
- Run 2, `replay_verify` with the same cutoff, executed at `2026-09-30 09:05:00` over the same evidence
  observations, bound both assessments and the same factor set. Each was recorded after run 2's cutoff.
  It created no assessment of its own.

## Actual affected scope

- Affected: any run other than the producing run whose knowledge cutoff precedes the `recorded_at` of an
  existing UNKNOWN assessment or factor set with the same content-derived uid. That covers as-known replay
  runs created with an explicit historical cutoff (`EodRunRepository::createAsKnownReplayRun`) and any run
  whose cutoff was stamped at creation but which reached the factor stage after another run recorded the
  row.
- Not affected: the producing run consuming what it derived itself, and every run whose cutoff is after
  the row's `recorded_at`; `latestAssessment()` already selects those rows.
- Content: the reused row has the content the run would derive from its own evidence, because the uid is
  content-derived from the same event revision and evidence observations. What leaks is visibility and
  provenance, not values: the run's lineage binds a record the platform had not recorded at its cutoff,
  together with that record's local ids.
- V2 identity: unaffected by construction. Under decision 1A, `recorded_at` and local ids are not members,
  and the V2 contract does not state that consuming a record before its `recorded_at` is valid.
- V1 identity: `source_scale_assessment_set_hash` and the V1 factor-set content bind the reused rows'
  local ids.
- Predicates whose proof would have to cover this case (primary MD-B18): MD-S004-R0002 (inputs contain
  only revisions recorded/known by the cutoff), MD-S050-R0028 (as-known resolution with
  `recorded_at <= knowledge_cutoff`), MD-S019-R0074 (current state must not leak). Their current B18
  proof-basis status is not changed by this record.
- Not measured: persisted rows in the normal `tradeaxis` database, which stays untouched.

## Disposition

Not repaired in MD-B10-A002. A repair changes factor production and C1 capture verification, which are
B11/B18 surfaces outside the B10 V2 identity scope, and would change V1 lineage of reruns. It is not a
B10 closure blocker: the V2 nested identities do not depend on the exempted fields. Re-review the three
predicates above when MD-B18-A002 resumes after MD-DEP-0020, and decide the producer and verifier change
there.
