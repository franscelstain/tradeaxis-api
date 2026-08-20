# Market Data — Current Documentation

> **Role:** navigation only. Current Market Data authority and implementation proof are separated by architecture.

## Mental model

`AUTHORITY menentukan → DEVELOPMENT mengerjakan → RECORDS membuktikan/merekam`

## Start

Mulai dari [`START_HERE.md`](START_HERE.md).

## Current architecture

- `authority/strategy/` — current Market Data behavior/product/operational strategy authority. Source strategy content from the architecture baseline is preserved byte-for-byte; IDs and authority disposition live in governance registries.
- `authority/governance/` — documentation, verification, traceability, stage/rework, residue, baseline, integrity, and record governance.
- `development/implementation/` — existing technical realization and the current `MD-B00..MD-B22` revalidation track.
- `development/research/` — current research only.
- `development/findings/` — current finding lifecycle only.
- `records/evidence/` — evidence records; pre-rebaseline evidence is historical-only for current implementation verification.
- `records/decisions/` — issued decisions.
- `records/history/` — historical/superseded documentation, prior W00–W22 status/audit corpus, and provenance indexes.

Old `PASS/DONE/READY/CONFORMANT` remains a historical fact but has **zero current-verification effect** until revalidated under the active epoch.
