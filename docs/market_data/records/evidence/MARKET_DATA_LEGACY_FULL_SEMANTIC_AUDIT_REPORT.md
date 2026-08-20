# Market Data Legacy Full Semantic Audit & Normalization Report

## Verdict

**PASS — FULL LEGACY COMPOSITE SEMANTIC NORMALIZATION SEALED**

Audit dilakukan terhadap seluruh source Market Data pre-refactor yang terdaftar. Penempatan tidak ditentukan hanya dari nama file; Markdown diperiksa section-by-section untuk menentukan apakah dokumen benar-benar memiliki lebih dari satu historical semantic responsibility.

## Coverage

- Registered legacy sources: **255 / 255**
- Original bytes inspected: **3,522,890**
- Markdown files read: **220**
- Markdown lines processed: **42,213**
- Markdown section audit records: **2,253**
- JSON sources parsed/read: **24**
- SQL sources read: **9**
- CSV sources parsed/read: **1**
- Empty migration placeholder read: **1**
- Source SHA mismatches: **0**

## Composite result

- Material composite legacy documents: **43**
- Exact role-pure extracts created: **428**
- Composite originals retained: **0**
- Composite originals still physically present at former primary paths: **0**
- Reconstruction failures: **0**
- Missing source lines: **0**
- Overlapping source lines: **0**
- Retained high-precision composite candidates after normalization: **0**

Extract role distribution:

```text
CONTEXT=109
DECISION=34
EVIDENCE=100
FINDING=37
GOVERNANCE=50
IMPLEMENTATION=79
RESEARCH=19
```

`CONTEXT` is deliberately non-authoritative preservation material. Historical finding/research/implementation/governance fragments also remain non-current and cannot become current authority merely because they were extracted.

## Strategy safety

- Registered strategy authority documents: **91**
- Strategy byte/hash mismatches versus the source-of-truth baseline: **0**
- Strategy semantic rewrites caused by this audit: **0**

The audit does not split or rewrite current strategy authority. Strategy IDs/freeze authority continue to be supplied externally by governance registries/manifests.

## Placement

- evidence extracts → `records/evidence/legacy/semantic/`
- issued-decision extracts → `records/decisions/legacy/`
- historical context/finding/research/implementation/governance fragments → `records/history/archive/semantic/`

All old verdicts remain `HISTORICAL_ONLY`. No legacy extract is current implementation proof.

## Canonical indexes

- `records/history/LEGACY_SOURCE_INDEX.csv`
- `records/history/LEGACY_DOCUMENT_ROLE_AUDIT.csv`
- `records/history/LEGACY_SECTION_ROLE_AUDIT.csv`
- `records/history/LEGACY_SPLIT_INDEX.csv`
- `records/history/LEGACY_SPLIT_RECONSTRUCTION_INDEX.csv`
- `records/history/LEGACY_SPLIT_SOURCE_CATALOG.md`
- `records/history/LEGACY_WORK_CORRELATION_INDEX.csv`

## Machine enforcement

`MarketDataDocumentationIntegrityGate.php` now validates `LEGACY_SEMANTIC_SPLIT_INTEGRITY`: every fully split source must have no retained primary composite, contiguous exact source ranges, valid extract body hashes, and a reconstructed SHA1 equal to the registered original SHA1.

Final gate results:

- Root architecture: **PASS**
- One Document One Role registry completeness: **PASS**
- Strategy Freeze: **PASS — 91/91**
- Current Verification Rebaseline: **PASS**
- Traceability Matrix: **PASS — 6,490 rows**
- Legacy Semantic Split Integrity: **PASS — 43/43 reconstructed**
- Exact duplicate files: **PASS — 0**
- Active Markdown broken links: **PASS — 0**
- Relationship Integrity: **PASS**
- Relationship self-test: **PASS — 9/9**

## Governance trace

`F-MD-20260820-02 → D-MD-20260820-02 → E-MD-20260820-02 → DOC-CHG-20260820-002`
