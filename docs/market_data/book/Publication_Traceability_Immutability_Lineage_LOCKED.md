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
- A content change creates a new publication version and immutable snapshot set; force/repair may switch a pointer only after that lifecycle succeeds.
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
- Non-current repair candidates may still carry lineage to the baseline/current publication without becoming reader-authoritative.

---

## Done Criteria

This contract is satisfied only when:
- source observation identity persists to run, publication, and row lineage for every source mode
- sealed publication hash mutation is rejected with `SEALED_PUBLICATION_IMMUTABLE`
- sealed row/source/config/factor mutation is rejected before write
- version/previous/replaced lineage fields are persisted
- superseded immutable snapshot rows remain queryable and hash-verifiable
- current pointer behavior remains governed by the existing pointer contract
- coverage and publishability policy remain unchanged
