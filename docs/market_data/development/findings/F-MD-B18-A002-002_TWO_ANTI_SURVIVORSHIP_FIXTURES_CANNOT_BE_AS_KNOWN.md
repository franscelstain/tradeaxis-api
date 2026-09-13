# Finding — `F-MD-B18-A002-002`

- ID: `F-MD-B18-A002-002`
- Raised by: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Owner stage: **`MD-B18`**
- Raised at: 2026-09-10T14:20:00+07:00
- Severity: `P2`
- Status: `RESOLVED`
- Resolved at: 2026-09-10T18:30:00+07:00
- Resolved by: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Class: `CAPABILITY_GAP`
- Blocks: `MD-S050-R0040` only
- Blocks strategy change: `NO`

## Statement

`MD-S050-R0040` opens with a classification, not a conditional:

> The eight anti-survivorship fixtures required below **are as-known fixtures**.

An as-known fixture is one whose answer is decided by a declared knowledge cutoff. Six of the eight
now are. **Two cannot be**, because the facts they turn on carry no knowledge time in the schema.

| # | Required fixture | As-known? | Why |
|---|---|---|---|
| 1 | a listing active at historical T but inactive today | **now yes** | was: `md_listings.delisted_date` is a mutable column on a single listing row. There is no listing revision, retraction or supersession anywhere, so a delisting has no `recorded_at` of its own and a cutoff cannot distinguish "delisted" from "delisted, but not yet known at T". Closed below. |
| 2 | a symbol change and provider-symbol mapping transition | yes | fixed in this attempt |
| 3 | symbol text reused by another listing | yes | fixed in this attempt |
| 4 | a calendar/status fact corrected after T | yes | |
| 5 | a corporate action learned or verified later | yes | |
| 6 | a configuration/formula change after T | yes | |
| 7 | an original and corrected immutable publication | **now yes** | was: `EodEvidenceRepository::resolvePublicationForEvidenceAudit()` takes a selector with no knowledge-cutoff parameter. Publication resolution is by explicit id or by current pointer; there is no cutoff-bounded resolution, so "the publication as it stood as known at T" is not expressible. Closed below. |
| 8 | a provider outage that cannot disappear through dormancy/current-universe filtering | yes | |

## What was fixed here

Fixtures 2 and 3 were effective-time fixtures: they called `readProjectedUniverseAsOf($tradeDate)`
and `resolveProviderContext($symbol, $provider, $tradeDate)` with no cutoff, so they proved which
symbol was *effective* on a trade date and nothing about what the platform could have *known*.

Both now separate the two. The rename takes effect on 1 July and is recorded on 1 September; a read
of trade date 2 August as known on 1 August still resolves the old symbol, and the same read without
a cutoff resolves the new one.

Modelling it required the retraction the schema actually provides. Writing the closed interval
directly — `effective_to = 2024-07-01` on the original row — backdates the knowledge: it makes the
platform look as though it had always known the interval would close. The correct shape is an open
interval recorded in 2023 and retracted on 1 September, plus the closed replacement recorded on 1
September. `md_listing_symbols` and `md_provider_symbol_mappings` both carry `retracted_at` and
`TemporalIdentityRepository::baseIdentityQuery()` already honours it against the cutoff.

**Correction to this section's original probe claim.** It read: "removing the `retracted_at >
knownAt` clause from the symbol join turns both new fixtures red, and leaves the rest of the corpus
green." That was re-run while building the production-path corpus for `MD-S050-R0056` and **it is
false as stated**. Neutering the as-known retraction clause on either `md_listing_symbols` or
`md_provider_symbol_mappings` left both fixtures green.

The reason is that both fixtures read only cutoffs *before* the rename was recorded, and at those
cutoffs the answer is already decided by `ls.recorded_at <= knownAt` — the replacement revision
simply did not exist yet. The retraction decides a different read: one taken *after* the platform
learned, where the interval that was believed open must stop resolving beside the revision recorded
in its place. Neither fixture took that read, so neither could see the clause at all. The mapping
half was weaker still: `md_provider_symbol_mappings` rows were seeded and never read.

Both fixtures now take three reads — before the record, after it, and uncut — and case 2 resolves
the provider mapping as well as the symbol, which is what the contract case actually says ("a symbol
change **and provider-symbol mapping transition**").

Probes, re-run against the corrected fixtures and reverted by byte copy with the files verified
identical by `md5sum`: neutering `ls.retracted_at > knownAt` turns the symbol-change fixture red on
both substrates; neutering the uncut `whereNull('ls.retracted_at')` branch does the same; neutering
`pm.retracted_at > knownAt` makes the resolver refuse with `PROVIDER_SYMBOL_MAPPING_AMBIGUOUS`,
because a retracted mapping answering beside its replacement makes two provider symbols claim one
listing on one date. The rest of the corpus stays green under each.

This is the sixteenth-and-then-some instance of the same shape: a guard that is correct, that
executes the right code, and whose red state the fixture cannot reach.

## Consequence for `MD-S050-R0040`

The row is not satisfied and is not recorded as satisfied. Six of eight is not the predicate.

The two gaps are unequal in cost:

- **Fixture 1** needs a knowledge-time dimension on the listing lifecycle — a `retracted_at` on
  `md_listings` plus a superseding revision, mirroring what `md_listing_symbols` already has. That
  is a schema change with migration and backfill implications for every consumer of
  `delisted_date`.
- **Fixture 7** needs a cutoff-bounded publication resolution — a selector that answers "which
  publication was current for this trade date as known at T". `eod_publications` already carries
  `sealed_at` and `created_at`, so the fact exists; nothing reads it as a knowledge boundary.

Both were first recorded as stage-scope decisions rather than defects to be fixed inside this
attempt. That call was reversed: neither gap turned out to need a scope decision, both are executable
work, and documentation is proof rather than a substitute for implementation. See **Resolution**.

## Resolution

Both gaps were closed by implementation in `MD-B18-A002`. `MD-S050-R0040` is bound.

### Fixture 1 — the listing lifecycle now carries a knowledge time

`database/migrations/2026_09_10_000001_add_listing_delisting_knowledge_time.php` adds a nullable
`delisted_recorded_at` to `md_listings` with the index `idx_md_listing_delisted_known`, and backfills
existing delisted rows from their own `recorded_at`. `TemporalIdentityRepository::baseIdentityQuery()`
now applies the delisting only when it was recorded at or before the cutoff.

One column, not the revision series this finding first proposed. `md_listings.listing_id` is the
primary key and `listing_uid` is unique, so a superseding listing revision would fracture listing
identity — which is precisely what the anti-survivorship corpus exists to keep intact. The knowledge
time belongs on the delisting fact, and that is where it went.

`NULL` means "not learned", so no cutoff can see it. Treating a null as "learned at the dawn of
time" would reintroduce the bias the column removes, and
`test_a_delisting_with_no_recorded_time_is_never_visible_to_a_cutoff` holds that closed.

Probe: removing the knowledge-time clause from the delisting filter turns both new fixtures red and
leaves the rest of the corpus green. The repository was restored by byte copy and verified identical
by `md5sum`.

### Fixture 7 — publication resolution now takes a cutoff

`EodEvidenceRepository::resolvePublicationAsKnownAt($tradeDate, $knowledgeCutoff)` was added. A
publication's knowledge time is its seal: before it was sealed it was a candidate and no reader could
resolve it. The candidates are therefore the publications for the date sealed at or before the
cutoff, and the answer among them is the one nothing sealed by then had yet superseded.

**It is not ordered by recency.** The first version resolved by `publication_version` with
`orderByDesc('publication_id')` as a tiebreaker, and `ReadPathShortcutProhibitionTest` refused it:
that test bans recency ordering from the consumer read repositories because "the newest row wins" is
a guess dressed as an answer. The guard was right and the method was rewritten to follow the declared
supersession chain — `supersedes_publication_id` / `previous_publication_id` /
`replaced_publication_id`, all three written together by `createCandidatePublication()`. Two sealed
publications that do not name each other are an unresolved supersession and are refused with
`EVIDENCE_AS_KNOWN_PUBLICATION_AMBIGUOUS`: a replay reading the wrong half of a correction pair is
worse than a replay that stops.

Probes, each reverted by byte copy with the file verified identical by `md5sum`: dropping the
`sealed_at <= cutoff` bound makes the correction visible to the earlier cutoff and makes a pre-seal
cutoff resolve a row; dropping the seal-state filter admits an unsealed candidate; dropping the
mandatory-cutoff half of the argument guard removes the `EVIDENCE_SELECTOR_MISSING` refusal; and
replacing the ambiguity refusal with a fallback turns the negative guard red.

### The classification itself is now checkable

`B18AntiSurvivorshipFixtureCorpusTest::test_every_required_fixture_is_also_an_as_known_fixture` binds
each of the eight contract cases — parsed from `MD-S050` rather than transcribed — to the guard that
decides it by a cutoff and to the cutoff-bounded runtime resolver that guard drives, and asserts the
guard exists, the resolver exists, and the guard calls it with the argument the cutoff occupies.

The argument count is load-bearing. Every one of these resolvers answers both questions —
`readProjectedUniverseAsOf($tradeDate)` is the effective-time read and
`readProjectedUniverseAsOf($tradeDate, $knownAt)` the as-known one — so naming the method would have
let a row be repointed at the effective-time fixture sitting beside it and stay green. Probe:
repointing case 1 at that fixture turns the guard red naming the missing cutoff argument.

## What this resolution does not touch

The note below on recorded applicability stands unchanged. Whether the `CONDITIONAL_APPLICABLE`
condition on `MD-S050-R0040` and `MD-S050-R0041` is discharged — and the denominator reduced from 121
to 119 — remains a governance decision for the owner. This resolution proves the row rather than
removing it, which is the conservative direction.

`MD-S050-R0056` inherits this finding for the sentence "all anti-survivorship cases" and is **not**
established by this resolution: it additionally requires a production-path scope decision and remains
outstanding.

`F-MD-B18-A002-003` stands: the SQLite mirror creates its schema with foreign key constraints
disabled and mirrors nullability loosely, so the mirror is weaker than production for the pointer and
publication chain fixture 7 reads.

## Note on the recorded applicability

`MD-S050-R0040` and `MD-S050-R0041` are both `CONDITIONAL_APPLICABLE` in
`STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv`, with the condition recorded at `MD-B18-A001`
stage entry as:

> conditional remains applicable **until integrated AS_KNOWN capability is runtime-proven**; no
> inherited evidence.

Both sit under the contract heading "While only one mode exists (LOCKED)", whose preamble is "Where
as-known replay is not implemented, the following hold without exception."

That antecedent now looks false. `MD-S050-R0005` was established in this attempt by
`B18AsKnownModeIsolationTest`, which drives `ReplayVerificationService::verifyAsKnownAgainstFixture`
end to end through the real `AsKnownReplaySnapshotService`, `AsKnownReplayExecutionService` and
persistence, and asserts the `AS_KNOWN` result it writes.

Whether that discharges the condition — moving both rows to `CONDITIONAL_NOT_APPLICABLE` and
reducing the denominator from 121 to 119 — is a governance decision and is **not** taken here.
Reducing a denominator is coverage-affecting and belongs to the owner, not to the attempt that would
benefit from it. It is recorded so the decision is made deliberately rather than by omission.

Note that the classification sentence quoted at the top of this finding is not part of the
conditional: "the eight are as-known fixtures" holds whichever way the condition resolves, and it is
that sentence this finding reports against.
