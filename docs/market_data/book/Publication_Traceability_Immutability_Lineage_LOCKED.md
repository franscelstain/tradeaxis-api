# Publication Traceability, Immutability, and Lineage Contract — LOCKED

## Status

STRATEGY LOCKED / IMPLEMENTATION REQUIRES RE-AUDIT.

This contract does not change:
- coverage gate logic
- publishability decision
- current pointer logic
- manual-file policy
- correction policy
- read-side enforcement

## Scope

This contract governs:
1. immutable source observation identity for file and API acquisition
2. sealed publication immutability guard
3. publication versioning and lineage metadata

---

## 1. Source File Identity Contract

For file-backed source acquisition, the originating source file identity must be queryable from first-class database fields, not only from logs, events, or notes.

Required fields on `eod_runs` and `eod_publications`:
- `source_file_hash`
- `source_file_hash_algorithm`
- `source_file_size_bytes`
- `source_file_row_count`

Rules:
- `source_file_hash` must be calculated from the original source file bytes.
- `source_file_hash_algorithm` must identify the algorithm; locked value for this version is `SHA-256`.
- `source_file_size_bytes` must store the source file size in bytes.
- `source_file_row_count` must store the number of data rows from the source file, excluding the header row when present.
- Non-file/API runs may keep these fields `NULL`; they must not fake file identity.
- Publication-level source identity is a copy of the run-level source identity at seal time so the sealed publication remains independently auditable.

API/manual observations additionally require immutable observation id, provider/source, provider symbol mapping, requested boundary, observed/received timestamps, schema/adapter version, payload hash/reference, and linkage to publication-bound rows. API runs must not leave source identity empty merely because file-specific fields are `NULL`.

---

## 2. SEALED Publication Immutability Contract

A publication with `seal_state='SEALED'` is immutable for publication content identity and source identity.

Protected mutation scope:
- publication-bound bar/price-product/indicator/eligibility row snapshots
- observation and provenance references
- identity/universe, calendar/status, config, formula, and factor revision bindings
- batch hashes
- source file identity fields
- seal metadata
- lineage fields

Required reason code/message for blocked mutation:

```text
SEALED_PUBLICATION_IMMUTABLE
```

Rules:
- The guard must live in repository/service code, not only in command code.
- Mutation attempts after seal must fail before writing changes.
- The guard applies to repository, service, maintenance, repair, migration, and direct operator paths.
- A content change creates a new publication version and immutable snapshot set; a governed correction/current-replacement flow may switch a pointer only after that lifecycle succeeds; integrity repair is limited to pointer/mirror recovery.
- Existing consumer safety, publishability, coverage gate, and pointer decisions are not changed by this guard.

---

## 3. Publication Lineage Contract

Every publication must carry enough lineage to identify its version and replacement relation.

Required fields:
- `publication_version`
- `previous_publication_id`
- `replaced_publication_id`
- correction/revision identity and immutable manifest/config/factor references

Compatibility field:
- `supersedes_publication_id` remains supported as the existing historical replacement field.

Rules:
- First publication for a trade date starts at `publication_version=1`.
- Next publication for the same trade date increments `publication_version` by one from the existing maximum version.
- When a publication replaces a current publication, `previous_publication_id`, `replaced_publication_id`, and `supersedes_publication_id` must identify that prior publication.
- Non-current correction candidates may still carry lineage to the baseline/current publication without becoming reader-authoritative.

---

---

## 4. Projection Reconciliation Contract (LOCKED)

Sections 1 to 3 protect **history**. They say nothing about whether the live materialised projection still corresponds to it.

`EOD_Bars_Contract.md` states that a current projection such as `eod_bars` is non-authoritative and rebuildable from immutable publication-bound rows. That is a claim about the projection, and a claim with no verification obligation is an assumption.

This is **internal** reconciliation: it compares two platform artifacts against each other. It is distinct from the external reconciliation owned by global gate 13, which compares a root of expectation against an outside authority. Neither substitutes for the other — a projection can agree perfectly with a publication built on an incomplete universe.

Required reconciliation, on its own cadence and independent of the daily pipeline:

- **Coverage both ways.** Every projection row must be covered by the current publication for its trade date, and every current-publication row must appear in the projection. A row present on one side only is a finding, not a rounding difference.
- **Value agreement.** For rows present on both sides, every canonical field must match exactly. Divergence is direct evidence that something wrote to the projection outside the publication lifecycle.
- **Explicit result.** The reconciliation records counts for both directions and the trade dates involved. An unreconciled period is declared as such.

Rules:

- A projection row whose trade date has no current publication is an **orphan** and must be reported. It may not be silently deleted, because deletion destroys the evidence of how it arrived; it is resolved through the correction lifecycle like any other content decision.
- A superseded publication that never became current is working as designed. What must be caught is projection content that outlived it.
- Reconciliation failure never authorises editing sealed history to match the projection. The publication is authoritative; the projection is the side that gets rebuilt.

## 5. Capability Boundary (LOCKED)

**What the immutability guard proves.** That a mutation attempt reaching a guarded repository or service path against sealed content is rejected before writing, with `SEALED_PUBLICATION_IMMUTABLE`; that lineage fields identify version and replacement relations; that superseded snapshots remain queryable and hash-verifiable.

**What it cannot prove.**

- **That sealed content was never modified.** The guard is application-level. A direct database session, an out-of-band migration, a restore from an altered backup, or any path that does not pass through application code is invisible to it. Hash verification detects such a change only when someone runs it and compares against an independently retained hash.
- **That the sealed content is correct.** Immutability preserves whatever was sealed, including a wrong value sealed faithfully.
- **That lineage completeness equals decision completeness.** `previous_publication_id` records which publication was replaced, not whether replacing it was right.
- **That the projection agrees with history**, absent the reconciliation in section 4.

Consequently a passing immutability guard may be cited as evidence that **guarded write paths are safe**, never as evidence that **published content is intact or correct**.

## Done Criteria

This contract is satisfied only when:
- source observation identity persists to run, publication, and row lineage for every source mode
- sealed publication hash mutation is rejected with `SEALED_PUBLICATION_IMMUTABLE`
- sealed row/source/config/factor mutation is rejected before write
- version/previous/replaced lineage fields are persisted
- superseded immutable snapshot rows remain queryable and hash-verifiable
- current pointer behavior remains governed by the existing pointer contract
- coverage and publishability policy remain unchanged
- projection-versus-current-publication reconciliation runs on its own cadence, reports both directions, and leaves no undeclared unreconciled period
- the capability boundary in section 5 is stated wherever an immutability result is cited as evidence
