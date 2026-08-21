# F-MD-B01-A001-001 — Required-rule sets are not satisfiable as assigned, across 17 of 18 stages

- Status: `PARTIALLY_RESOLVED`
- Severity: `P0` (both halves remediated for `MD-B01`; other stages validate ownership at entry)
- Stage / Attempt / Baseline / Epoch: `MD-B01` / `MD-B01-A001` / `MD-B01-A001-BL001` / `MD-REBASELINE-20260820-001`
- Owning stage for remediation: governance / `MD-B01` revalidation — governance clarification issued as `DOC-CHG-20260821-001`; semantic matrix correction still required
- Blocks: **nothing currently open.** All 18 stages can reach full required coverage, and `MD-B01` no longer holds rules it cannot prove. Ownership validation becomes an entry obligation for each stage as it opens.
- Dependency: `MD-DEP-0004`

> Raised at `MD-B01` and initially scoped to it. A follow-on scan executed at the same attempt showed the same defect across the whole matrix, so the severity is `P0` and the scope is the `MD-B01..MD-B22` track rather than one stage. `E-MD-B01-A001-001` records the stage-scoped measurement it was issued with; the global measurement below supersedes its scope statement.

## Finding

`MD-B01` requires 127 mandatory-or-conditional rules to reach `SATISFIED`. Fifteen of those 127 are bare colon-terminated list headers carrying no independently verifiable predicate, and three more are owner pointers or reading-order items. No executed evidence can satisfy `Artinya sistem harus mampu:` or `Consumer downstream wajib:`, because neither states a checkable condition — each merely introduces the bullets beneath it.

`127/127` is therefore unreachable by construction, and `MD-B01` cannot close as `DONE` no matter how conformant the implementation is.

## The assignment is arbitrary within a single list

The substantive content sits in the bullets, and those are mostly excluded from the required set. Measured across the `MD-B01` rows at this baseline:

| | count |
|---|---|
| REQUIRED rules that are bare colon-terminated list headers | 15 |
| bullet rows directly beneath those headers | 83 |
| of those bullets, REQUIRED | 5 |
| of those bullets, REFERENCE_ONLY | 78 |

In five cases the split runs through the middle of one homogeneous list:

- `MD-S001-R0032` "Artinya sistem harus mampu:" — bullet `R0036` is REQUIRED; siblings `R0033`, `R0034`, `R0035` are REFERENCE_ONLY. The three excluded bullets are the actual date-driven capabilities: accept any single requested trade date, accept any date range, run import for historical and latest dates.
- `MD-S001-R0099` "Domain ini tetap menjadi owner untuk:" — bullet `R0100` is REQUIRED; the nine sibling ownership bullets `R0101`–`R0109` are REFERENCE_ONLY.
- `MD-S001-R0126` "Dokumen dalam domain ini tidak boleh lagi menyatakan atau menyiratkan bahwa:" — bullet `R0136` is REQUIRED; eleven siblings are REFERENCE_ONLY.
- `MD-S056-R0053` "Its target minimum outputs are:" — bullet `R0054` is REQUIRED; seven siblings are REFERENCE_ONLY.
- `MD-S001-R0074` "Untuk jalur default aktif `yahoo_finance`:" — bullet `R0077` is REQUIRED; three siblings are REFERENCE_ONLY.

There is no semantic difference between the included and excluded bullets. `- immutable source observations dan provenance` is REQUIRED while `- canonical validated Regular-Market EOD bars` and eight other ownership statements from the same list are REFERENCE_ONLY. The pattern is consistent with a generator that marked the header plus one positionally-selected bullet, rather than an assignment made by meaning.

The consequence runs both ways: the required set contains rules that cannot be proved, and excludes rules that both can and should be.

## The defect is global, not local to MD-B01

Counting bare colon-terminated list headers among each stage's required mandatory-or-conditional rules at this baseline:

| Stage | Required | Non-predicate headers | Share |
|---|---|---|---|
| `MD-B01` | 127 | 15 | 11.8% |
| `MD-B02` | 35 | 1 | 2.9% |
| `MD-B04` | 167 | 15 | 9.0% |
| `MD-B05` | 55 | 6 | 10.9% |
| `MD-B06` | 44 | 5 | 11.4% |
| `MD-B07` | 78 | 17 | 21.8% |
| `MD-B08` | 62 | 14 | 22.6% |
| `MD-B09` | 55 | 5 | 9.1% |
| `MD-B10` | 281 | 58 | 20.6% |
| `MD-B11` | 80 | 7 | 8.8% |
| `MD-B12` | 23 | 3 | 13.0% |
| `MD-B13` | 19 | 0 | 0.0% |
| `MD-B14` | 66 | 6 | 9.1% |
| `MD-B15` | 76 | 9 | 11.8% |
| `MD-B16` | 22 | 2 | 9.1% |
| `MD-B17` | 64 | 12 | 18.8% |
| `MD-B18` | 28 | 2 | 7.1% |
| `MD-B19` | 125 | 33 | 26.4% |
| **Total** | **1407** | **210** | **14.9%** |

210 of the 1407 required rules cannot be satisfied by evidence. `MD-B13` is the only stage with a clean required set; every other stage is unable to reach full required coverage regardless of implementation quality. `MD-B10` and `MD-B19`, the two largest stages, are the worst affected in absolute terms with 58 and 33 unsatisfiable rules.

This is not a reason to lower the bar. It is a reason to fix the classification before the track proceeds, because a coverage target that cannot be met teaches everyone downstream to treat the target as advisory — which is precisely how a governance control stops working.

## Secondary concern: primary-stage assignment

Every one of the 127 names `MD-B01` as `primary_stage`, including rules whose behaviour is implemented and provable only in a later stage — for example `MD-S001-R0065` (consumers may read only sealed/current/readable publications through the effective-date pointer contract) and `MD-S001-R0066` (no `MAX(date)`, no consumer-side indicator recomputation), both of which are `MD-B17` read-side enforcement. `MD-B01` can lock the *semantics* of those boundaries; it cannot produce runtime evidence for a read path that `MD-B17` owns.

This is recorded as an observation rather than a measurement. Unlike the header defect above, deciding which rules genuinely belong to `MD-B01` requires a judgement per rule and is properly part of the remediation decision, not of this finding.

## What this attempt did instead

`MD-B01-A001` proved and marked `SATISFIED` only the five rules whose full text is covered by executed evidence, and left the remaining 122 `NOT_ASSESSED`. It did not reclassify a single matrix row to make the target reachable. `DOCUMENT_CHANGE_POLICY.md` is explicit that strategy and governance must not be weakened merely to make implementation pass, and the traceability matrix is governance.

## Required outcome

A reviewed governance decision that re-derives the requirement classification by meaning rather than position, covering at minimum:

1. Bare list headers and owner pointers become `REFERENCE_ONLY`; the predicate-bearing bullets beneath them become `REQUIRED` where the content is mandatory.
2. `primary_stage` is re-derived from the build-sequence contract areas so a rule is owned by the stage that can actually produce evidence for it.
3. Because the matrix is governance and the epoch binds to its fingerprints, the decision states which affected current verification is invalidated.

Until then `MD-B01` stays open and no stage except `MD-B13` can legitimately close. The rules already marked `SATISFIED` are unaffected by the remediation: each is a predicate-bearing rule whose evidence stands on its own.


## Governance revision impact — 2026-08-21

`DOC-CHG-20260821-001` removes the procedural deadlock that previously treated this matrix defect as unfixable merely because the matrix lives under `authority/`. The current governance now states explicitly that `STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv` is `MUTABLE_TRACEABLE`, that only predicate-bearing rows may own executable proof obligations, and that `primary_stage` must follow proof ownership rather than extraction position.

This finding therefore remains **OPEN**, but its remediation is now executable governance work. This synchronization does **not** reclassify the 210 rows, does not change any strategy byte, and does not carry forward existing `SATISFIED` states automatically. The matrix must still be re-derived semantically, affected counts recalculated, and any affected attempts/baselines/evidence invalidated or revalidated according to the revised standard.

Because `DOC-CHG-20260821-001` also changes closure/gate invariants, the exact repository resume point is first the `MD-B00` governance re-entry recorded in `MD_IMPLEMENTATION_STAGE_REGISTER.md`; after that re-entry succeeds, execution returns to `MD-B01` for this matrix remediation.

## Related

- Does not supersede any record. Independent of `F-MD-B00-A001-001`.
- The five satisfied rules and their evidence are listed in `E-MD-B01-A001-001`.

## Partial resolution — MD-B01-A004

The predicate-classification half is closed, remediated under `CI-MD-B01-A004-001` with evidence `E-MD-B01-A004-001`.

Both directions of the defect were corrected in one governed mutation. 214 non-predicates were demoted to reference context: 210 colon-terminated list introducers, 2 owner pointers, 1 bare bold label, 1 reading-order item. 817 predicate-bearing children of those introducers were promoted to required — the clauses that carry the actual obligation, such as `- baca hanya dari publication (sealed + readable + current)` and `- pakai MAX(date)`.

**The required set grew from 1407 to 2010, a net increase of 603.** Section 6 forbids editing the matrix merely to reduce required coverage or make a stage pass; this correction moved the number the other way by 43%. Zero of the 21 `SATISFIED` rows were demoted, so no proven work was hidden and no coverage was manufactured.

Measured effect on the table above: non-predicate required rows went from 210 to **0**, and stages able to reach full required coverage went from **1 of 18** to **18 of 18**. The claim that this blocks the whole track no longer holds for the predicate reason.

Strategy bytes unchanged; `rule_text`, `rule_fingerprint_sha1`, `strategy_owner`, and `source_line` untouched on every row, verified by recomputing sha1 across all 6490 rows with 0 mismatches and by the documentation gate TRACEABILITY_MATRIX check passing with 0 errors.

### What remains open

The secondary concern recorded above — proof-owning stage assignment under section 4 — is **not** addressed. `primary_stage` is still derived from the physical document a rule was extracted from rather than from the stage that can close its proof. `MD-DEP-0004` stays `OPEN_BLOCKING`, narrowed to this half, because a stage holding a rule it cannot prove still cannot reach full coverage honestly.

## Secondary concern resolved for MD-B01 — MD-B01-A005

The proof-ownership concern recorded above is addressed for `MD-B01` under `CI-MD-B01-A005-001` with evidence `E-MD-B01-A005-001`.

The defect was measured before it was corrected: **all 91 strategy documents map to exactly one `primary_stage`, and none maps to more than one**, so the field was a pure function of the extraction document with no per-rule differentiation — exactly what section 4 forbids.

15 of `MD-B01`'s 170 required rules moved to the stages that can evidence them: 9 to `MD-B17` (consumer read-path enforcement, including the `MAX(date)` and raw-table prohibitions named in this finding), 3 to `MD-B19` (retention and maintenance), 2 to `MD-B10` (publication immutability and the correction/reseal trail), 1 to `MD-B15` (the 98% delivery-coverage prerequisite). Each keeps `MD-B01` as a supporting stage. `MD-B01` is now `21/155`.

A keyword classifier was written first and **rejected**: it proposed 41 moves, roughly a third of them wrong — it pulled the boundary-ownership statement and the Weekly Swing horizon to `MD-B17` on the words "read model" and "consumer", and would have moved two rules that already hold valid current proof. Substituting a pattern for the judgement section 4 asks for is the same defect this finding exists to correct, so an explicit hand-verified table was used instead.

`MD-DEP-0004` is downgraded to `OPEN_NON_BLOCKING` and restructured into a per-stage entry obligation: each stage validates its own rule ownership when it opens. It no longer blocks `MD-B01`.
