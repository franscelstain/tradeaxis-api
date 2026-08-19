# Legacy Document Normalization Standard

> **Status:** CANONICAL GOVERNANCE
> **Scope:** original pre-reorganization Watchlist corpus imported from `docs.zip`
> **Purpose:** prevent filename-only migration, preserve semantic provenance, enforce one-document-one-authoritative-role, and avoid retaining duplicate composite files after verified decomposition.
> **Role purity owner:** [`ONE_DOCUMENT_ONE_AUTHORITATIVE_ROLE_STANDARD.md`](ONE_DOCUMENT_ONE_AUTHORITATIVE_ROLE_STANDARD.md).

## 1. Read Before Placement

Every legacy file must be read in full before role placement. Markdown must additionally be audited section-by-section. JSON and CSV must parse; SQL and text must be readable. Filename and former folder are hints only, never sufficient placement evidence.

## 2. Stable Legacy Source Identity

Every legacy file receives a stable `LS-WS-NNNN` source ID, original path, original SHA1, parse/read status, semantic role audit, and storage disposition in `records/history/LEGACY_SOURCE_INDEX.csv`.

`LS-*` is provenance identity, not current authority and not a requirement to retain a duplicate physical original forever.

## 3. Fully Split Composite Sources Must Not Be Duplicated

When a composite Markdown source has been decomposed into role-pure `LX-*` records, the physical original may be removed only after all of the following are proven in one sealing validation:

1. every original line is represented by exactly one registered extract range;
2. line coverage is `100%` with zero overlap;
3. every extract file exists and its registered body hash matches;
4. original source SHA1 matched the registered `LS-*` identity immediately before deletion;
5. `LEGACY_SPLIT_RECONSTRUCTION_INDEX.csv` and `LEGACY_SPLIT_SOURCE_CATALOG.md` have been generated;
6. the source is marked `FULL_100_PERCENT_SEALED` and `REMOVED_AFTER_FULL_SPLIT` in `LEGACY_SOURCE_INDEX.csv`.

After those conditions pass, retaining the same composite in `original_sources/` or as another historical/evidence/decision copy is prohibited duplication. Current clean strategy/implementation derivatives are **not** deleted because they are current authority, not legacy-original copies.

## 4. Role-Pure Historical Placement

Historical extracts use `LX-WS-<source>-<role>-NN` identities and exact registered source ranges.

- evidence -> `records/evidence/runs/`;
- issued decision -> `records/decisions/`;
- historical research, finding, implementation, strategy, governance, or preservation context -> `records/history/archive/`.

Completed/superseded legacy research/findings must not appear as current development work.

## 5. Sources That Remain Physical

A physical legacy source may remain only when its semantic audit resolves to **one authoritative role** or to a single container role such as navigation/registry/status/source-container.

A source with more than one semantic authority **must be fully decomposed and sealed**. `LEGACY_BUNDLE_EXCEPTION_INDEX.csv` cannot waive one-document-one-role. Any retained bundle exception must itself be single-role and must state why the container role is legitimate.

Retention is not current authority. It only preserves a role-pure historical/source container that does not need destructive decomposition.

## 6. Current Development Purity

`development/research/` and `development/findings/` are current working areas. Completed/superseded C/R/P/B/S/Q campaign records belong in records/history/evidence/decisions according to semantic role.

`development/implementation/` may retain current technical contracts and current clean derivatives from legacy documents. Campaign-specific historical addenda do not belong there as current behavior.

## 7. Stable Legacy Correlation

Historical campaign correlation uses `legacy_work_key`, `LS-*`, and `LX-*`. Historical keys such as C171/B01/R2 are not retrofitted into current `WS-Bxx-Axxx` Attempt IDs.

## 8. Required Indexes

- `LEGACY_SOURCE_INDEX.csv`
- `LEGACY_DOCUMENT_ROLE_AUDIT.csv`
- `LEGACY_SECTION_ROLE_AUDIT.csv`
- `LEGACY_SPLIT_INDEX.csv`
- `LEGACY_SPLIT_RECONSTRUCTION_INDEX.csv`
- `LEGACY_SPLIT_SOURCE_CATALOG.md`
- `LEGACY_BUNDLE_EXCEPTION_INDEX.csv`
- `LEGACY_WORK_CORRELATION_INDEX.csv`

The executable documentation integrity gate must verify unique source/extract identities, retained-source hashes where a source is intentionally retained, sealed 100% split coverage where a source is removed, extract-body hashes, split-mapping hashes, and absence of historical `LX-*` records from active development areas.
