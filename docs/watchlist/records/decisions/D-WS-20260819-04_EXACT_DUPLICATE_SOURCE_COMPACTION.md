# D-WS-20260819-04 — Adopt Exact-Duplicate Legacy Source Compaction

> **Document Type:** DECISION
> **Status:** ISSUED / IMMUTABLE
> **Verification Epoch:** `WS-REBASELINE-20260819-001`

## Decision

A retained legacy `LS-*` physical source MUST NOT be stored a second time when its normalized role-specific `final_primary_path` is byte-identical to the source and matches the registered `original_sha1`.

For that case:

1. remove only the duplicate `records/history/original_sources/LS-*` physical copy;
2. keep the role-specific `final_primary_path`;
3. preserve `source_id`, `original_path`, `original_sha1`, role, and primary path in `LEGACY_SOURCE_INDEX.csv`;
4. set `source_storage_policy=DEDUPLICATED_TO_ROLE_PRIMARY`;
5. register the exact removal mapping in `LEGACY_EXACT_DUPLICATE_COMPACTION_INDEX.csv`; and
6. make the executable document gate verify that the retained primary path still exists and still matches `original_sha1`.

No non-identical source may be deleted under this decision.

## Basis

- Finding: `F-WS-20260819-04`
- Current normalization authority: `../../authority/governance/LEGACY_DOCUMENT_NORMALIZATION_STANDARD.md`

## Effect

Provenance remains complete while byte-identical duplicate storage is prohibited. This decision does not alter Weekly Swing strategy behavior or current verification coverage.
