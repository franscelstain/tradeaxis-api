# F-WS-20260819-04 — Exact Duplicate Legacy Source Storage Gap

> **Document Type:** FINDING
> **Status:** RESOLVED
> **Scope:** retained `LS-*` physical sources under `records/history/original_sources/`

## Observation

A retained legacy source can be semantically single-role yet still be a byte-identical duplicate of its normalized role-specific `final_primary_path`. Keeping both physical copies adds storage noise without adding provenance value because `LS-*`, original path, original SHA1, role, and normalized primary path are already indexed.

## Evidence Criterion

A physical `LS-*` copy is redundant only when all of the following are true:

1. `archived_source_path` exists;
2. `final_primary_path` exists;
3. both files have exactly the same SHA1;
4. that SHA1 equals the registered `original_sha1`; and
5. the primary path is already role-appropriate and registered.

Similarity, filename resemblance, normalized-equivalent content, or manual judgement is insufficient.

## Resolution

Remove only byte-identical duplicate `LS-*` copies, retain the role-specific primary file, preserve the `LS-*` identity and original metadata in `LEGACY_SOURCE_INDEX.csv`, and register the removal in `LEGACY_EXACT_DUPLICATE_COMPACTION_INDEX.csv`. Non-identical retained sources remain physical until separately proven safe to compact.
