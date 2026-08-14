# Canonical Row History and Versioning Policy (LOCKED)

## Purpose
Define the official row-history strategy for preserving historical canonical artifact state across corrections and publication changes.

This policy applies to:
- raw observation identity/reference
- canonical bars
- analytical price products/factor bindings
- indicators
- eligibility

## Core principle (LOCKED)
A corrected publication for trade date D must never make prior consumer-visible row state disappear silently from auditability.

## Official production-grade strategy (LOCKED)
The official required strategy for every sealed publication is:

### Strategy A — Immutable publication-bound row snapshots

Under Strategy A:
- each publication for D has its own immutable row snapshot set
- row snapshots are preserved in history tables:
  - `eod_bars_history`
  - `eod_indicators_history`
  - `eod_eligibility_history`
- current readable state may be served from rebuildable current projections resolved through the publication pointer
- historical row-level audit must be reconstructable exactly per publication

## Strategy B status
Strategy B (publication trail + hash trail + correction evidence only) is not the default production-grade strategy.

It may exist only as:
- legacy note
- simplified non-production deployment note
- explicitly weaker fallback model

It must not be presented as equal in strength to Strategy A.

## Strategy A rules (LOCKED)
All of the following must hold:
1. each sealed publication must have one immutable snapshot set
2. history rows must be keyed by `publication_id` plus row identity
3. history rows must never be updated in place
4. corrected publication produces a new snapshot set
5. prior snapshot set remains queryable after supersession
6. history snapshot rows must link to `eod_publications`
7. history snapshot rows are appended/frozen atomically with the seal/publication transition
8. raw observation, identity/calendar/status snapshot, config, factor, and formula/version bindings remain reconstructable for every snapshot
9. no repair, recompute, migration, or operator command may update/delete sealed snapshot content

## Required history-table semantics
History tables must support:
- exact row-state recovery for one publication
- clear publication linkage
- append-only / immutable behavior
- no ambiguity between current-state tables and historical snapshot tables

## Current-state vs history-state distinction
Optional current projection tables:
- `eod_bars`
- `eod_indicators`
- `eod_eligibility`

may serve the current readable state only after pointer resolution. They are non-authoritative, rebuildable projections and must never be the only preserved representation of published content.

History tables:
- `eod_bars_history`
- `eod_indicators_history`
- `eod_eligibility_history`

serve immutable publication-bound audit state.

These roles must never be confused.

A projection replacement may change which immutable snapshot is exposed as current, but it may not mutate the snapshot itself. Direct reads from projections without publication context remain forbidden.

## Correction rule
On correction for D:
- prior publication snapshot remains preserved
- corrected publication creates a new snapshot set
- corrected snapshot becomes associated with the new current publication
- prior snapshot remains audit-only but fully queryable

## Minimum audit questions this policy must support
For any corrected date D, the system must answer:
1. what was the prior current publication?
2. what exact rows belonged to that prior publication?
3. what exact rows belong to the new publication?
4. which publication is current now?
5. which history-table snapshot corresponds to each publication?

## Required schema alignment
This policy must be reflected in:
- `../db/Database_Schema_MariaDB.sql`
- `../db/Database_Schema_Contracts_MariaDB.md`
- `../ops/History_Table_Immutability_Guards_LOCKED.sql`
- publication/correction contracts

## Required evidence alignment
Executed evidence examples should demonstrate:
- prior publication snapshot
- corrected publication snapshot
- publication manifest
- correction diff artifact

## Anti-ambiguity rule (LOCKED)
If the platform claims production-grade auditability but cannot point to immutable publication-bound history rows, then row-history integrity is overstated.

Strategy B is not acceptable for decision-grade relock. Until immutable publication-bound snapshots and mutation guards are implemented and proven, this contract is strategy-locked but its production behavior remains unproven.

## Capability boundary scope (LOCKED)

**Gate 11: not applicable.** Kontrak ini menetapkan kapan sebuah versi baris baru terbentuk dan bagaimana versi lama dipertahankan. Ia tidak menghasilkan verdict, state, flag, atau signal yang dapat dikutip sebagai bukti tentang data, sehingga tidak memiliki wilayah buta untuk dinyatakan. Mekanisme yang memang menghasilkan keluaran semacam itu menyatakan batasnya pada owner contract-nya masing-masing.
