# Publication Traceability, Immutability, and Lineage Contract — LOCKED

## Status

LOCKED extension for audit-safety hardening.

This contract does not change:
- coverage gate logic
- publishability decision
- current pointer logic
- manual-file policy
- correction policy
- read-side enforcement

## Scope

This contract only governs:
1. source file identity persistence
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

---

## 2. SEALED Publication Immutability Contract

A publication with `seal_state='SEALED'` is immutable for publication content identity and source identity.

Protected mutation scope:
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
- Existing consumer safety, publishability, coverage gate, and pointer decisions are not changed by this guard.

---

## 3. Publication Lineage Contract

Every publication must carry enough lineage to identify its version and replacement relation.

Required fields:
- `publication_version`
- `previous_publication_id`
- `replaced_publication_id`

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
- source file identity persists to run and publication rows
- sealed publication hash mutation is rejected with `SEALED_PUBLICATION_IMMUTABLE`
- version/previous/replaced lineage fields are persisted
- current pointer behavior remains governed by the existing pointer contract
- coverage and publishability policy remain unchanged
