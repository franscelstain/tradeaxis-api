# F-MD-B01-A001-001 — Required-rule sets are not satisfiable as assigned, across 17 of 18 stages

- Status: `OPEN`
- Severity: `P0`
- Stage / Attempt / Baseline / Epoch: `MD-B01` / `MD-B01-A001` / `MD-B01-A001-BL001` / `MD-REBASELINE-20260820-001`
- Owning stage for remediation: governance — requires a reviewed decision under `DOCUMENT_CHANGE_POLICY.md`
- Blocks: every stage except `MD-B13` reaching `DONE`, at any attempt, until resolved
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

## Related

- Does not supersede any record. Independent of `F-MD-B00-A001-001`.
- The five satisfied rules and their evidence are listed in `E-MD-B01-A001-001`.
