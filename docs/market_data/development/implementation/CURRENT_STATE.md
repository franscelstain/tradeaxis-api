# Market Data Current State

> GENERATED — DO NOT EDIT MANUALLY

## Verification identity and coverage

- Verification epoch: `MD-REBASELINE-20260820-001`
- Required active traceability rows: **4039**
- Coverage denominator: **4014** (FINAL)
- SATISFIED: **3068**
- NOT_ASSESSED inside denominator: **946**
- CONDITIONAL_NOT_APPLICABLE / NOT_APPLICABLE: **25 / 25**
- CONDITIONAL_PENDING / APPLICABILITY_PENDING: **0 / 0**
- Transitional MANDATORY_OR_CONDITIONAL: **0**
- Verified coverage: **76.43% FINAL**
- Optional capability rules: **63**

## Current executable stage

- Stage: `MD-B18`
- Latest attempt / baseline: `MD-B18-A002` / `MD-B18-A002-BL001`
- State / verdict: `IN_PROGRESS` / `WITHDRAWN — see F-MD-B19-A001-002`
- Residue/rework: `CONFORMANT`
- Dependency: **`MD-DEP-0009`** — `MD-B18` is the active remediation stage for it; `MD-DEP-0004` B18 entry obligation complete
- Open finding: `F-MD-B18-A001-001` (P1) — RESOLVED; `F-MD-B18-A002-001` (P1) — RESOLVED; `F-MD-B18-A002-002` (P2) — RESOLVED, both gaps closed by implementation: `md_listings.delisted_recorded_at` gives a delisting a knowledge time of its own, and `EodEvidenceRepository::resolvePublicationAsKnownAt()` resolves a publication as known at a cutoff by the declared supersession chain rather than by recency; **`F-MD-B18-A002-003` (P2) — OPEN**, the SQLite mirror disables foreign keys globally and mirrors nullability loosely, so referential integrity is enforced by production and by nothing in the mirror
- Change Impact Declaration: `CI-MD-B18-A002-001` — ISSUED
- Denominator: **121** (FINAL for every machine-checked criterion — no transitional applicability, no mixed-classification run, every reference row decided)
- SATISFIED / NOT_ASSESSED: **0 / 121**
- Mandatory / conditional-applicable: **117 / 4**
- Conditional-not-applicable / conditional-pending / transitional: **0 / 0 / 0**

## Stage state index

| Stage | Lifecycle | Verdict | Latest attempt | Baseline | Integrity gate |
|---|---|---|---|---|---|
| `MD-B00` | `DONE` | `PASS` | `MD-B00-A004` | `MD-B00-A004-BL001` | `PASS` (classification gate hardened with `UNEXPLAINED_REFERENCE` and `BINDING_COHERENCE`, both mutation-proven; self-test 12/46; full suite 1951/18238) |
| `MD-B01` | `DONE` | `PASS` | `MD-B01-A016` | `MD-B01-A016-BL001` | `PASS` (ownership-chain proof + promoted-predicate map + classification consistency + applicability + scope map, all mutation-proven) |
| `MD-B02` | `DONE` | `PASS` | `MD-B02-A001` | `MD-B02-A001-BL001` | `PASS` (provider-bootstrap traceability/proof gate + classification + relationship + documentation; mutation-proven) |
| `MD-B03` | `DONE` | `PASS` | `MD-B03-A003` | `MD-B03-A003-BL001` | `PASS` (drift detector, nullable-placeholder gate, test-path binding integrity, all mutation-proven) |
| `MD-B04` | `DONE` | `PASS` | `MD-B04-A002` | `MD-B04-A002-BL001` | `PASS` (config-foundation proof/traceability + classification + relationship + documentation; mutation-proven) |
| `MD-B05` | `DONE` | `PASS` | `MD-B05-A001` | `MD-B05-A001-BL001` | `PASS` (temporal-identity traceability/proof gates + classification + relationship + documentation; mutation-proven) |
| `MD-B06` | `DONE` | `PASS` | `MD-B06-A001` | `MD-B06-A001-BL001` | `PASS` (calendar/status traceability + exact proof + classification + relationship + documentation; mutation-proven) |
| `MD-B07` | `DONE` | `PASS` | `MD-B07-A002` | `MD-B07-A002-BL001` | `PASS` (exact B07 116/116 + all four governance gates + full suite 1946/18210; masking guard and normalization completeness assertion both mutation-proven) |
| `MD-B08` | `DONE` | `PASS` | `MD-B08-A002` | `MD-B08-A002-BL001` | `PASS` (exact B08 139/139 + failure-taxonomy surface 75/335 + all four governance gates + full suite; the new retention guard mutation-proven against the exact collapse that previously passed all 1946 tests) |
| `MD-B09` | `DONE` | `PASS` | `MD-B09-A003` | `MD-B09-A003-BL001` | `PASS` (B09 traceability + proof gates, all four governance gates, full suite 1951/18239; the source-JSON-path guard gap closed and mutation-proven) |
| `MD-B10` | `DONE` | `PASS` | `MD-B10-A001` | `MD-B10-A001-BL001` | `PASS` (exact 9-trigger deployed immutability + cumulative lifecycle/reconciliation/full-suite proof + rollback-safe deployed `REBUILT_AND_VERIFIED` repair + exact 1072 binding + post-binding controls) |
| `MD-B11` | `DONE` | `PASS` | `MD-B11-A003` | `MD-B11-A003-BL001` | `PASS` (B11 proof/traceability gates bound, all four governance gates, B11 surface 93/480, full suite 1953/18248 exit 0 against reachable MariaDB) |
| `MD-B12` | `DONE` | `PASS` | `MD-B12-A003` | `MD-B12-A003-BL001` | `PASS` (B12 proof/traceability/static gates bound, all four governance gates, B12 surface 78/247, full suite 1953/18247 exit 0 against reachable MariaDB) |
| `MD-B13` | `DONE` | `PASS` | `MD-B13-A001` | `MD-B13-A001-BL001` | `PASS` (in-session deployed-MariaDB targeted/full-suite proof + exact 33 binding + evidenced aggregate applicability + post-binding controls) |
| `MD-B14` | `DONE` | `PASS` | `MD-B14-A001` | `MD-B14-A001-BL001` | `PASS` — proof gate bound, self-test 11/11, 10 fail-closed probes and 8 closure-condition probes all caught |
| `MD-B15` | `DONE` | `PASS` | `MD-B15-A001` | `MD-B15-A001-BL001` | `PASS` — proof gate bound, self-test 11/11, 6 fail-closed probes and 8 closure-condition probes all caught |
| `MD-B16` | `DONE` | `PASS` | `MD-B16-A001` | `MD-B16-A001-BL001` | `PASS` — proof gate bound, self-test 11/11, 8 fail-closed and 8 closure-condition probes all caught |
| `MD-B17` | `DONE` | `PASS` | `MD-B17-A002` | `MD-B17-A002-BL001` | `PASS` — 246-entry proof map, atomic binding, self-test 11/11, 7 snapshot fail-closed guards, 8 closure-condition probes, affected B04 gates and post-binding full suite all pass |
| `MD-B18` | `IN_PROGRESS` | `WITHDRAWN — see F-MD-B19-A001-002` | `MD-B18-A002` | `MD-B18-A002-BL001` | `PARTIAL` — `MD-B18-A002` in progress. **118/121** predicates carry a reviewed per-predicate proof basis; the closure gate reports the other 3 by name and both remaining conditions (`denominator_fully_satisfied`, `every_predicate_has_a_reviewed_proof_basis`) are unmet. Full market-data suite green at each step; every guard class named in the proof basis passes. The `MD-B18-A001` `PASS` recorded here previously was the **withdrawn** closure and is not inheritable proof — it is retained only in the withdrawal note in this row |
| `MD-B19` | `IN_PROGRESS` | — | `MD-B19-A001` | `MD-B19-A001-BL001` | `PARTIAL` — proof map validated at 743/743 across 37 families, zero structural errors; **no family may now be called proven**: `F-MD-B19-A001-002` measured that a family-level guard assignment does not establish its members, and the three families previously recorded as proven carry one guard pair each for 52, 13 and 8 predicates. The gate now requires a reviewed per-predicate proof basis and reports **743/743 predicates without one**; 34/37 families also carry no guard at all |
| `MD-B20` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B21` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B22` | `NOT_STARTED` | — | — | — | `NOT_RUN` |

## Open dependencies and work records

- Open findings across every stage: `F-MD-B00-A001-001` — PARTIALLY_RESOLVED; `F-MD-B01-A001-001` — PARTIALLY_RESOLVED; `F-MD-B01-A014-001` — OPEN; `F-MD-B14-A001-001` — OPEN; `F-MD-B18-A002-003` — OPEN; `F-MD-B19-A001-002` — OPEN — total **6**
- Open dependencies: `MD-DEP-0003` — OPEN_NON_BLOCKING; owner `owning stages MD-B03/B15/B17/B19/B21/B22`; `MD-DEP-0004` — OPEN_NON_BLOCKING; owner `each stage at entry`
- Classification entry obligation (`MD-DEP-0004`), reference-only rows in mixed-classification runs by stage: `MD-B20` 9 — total **9**
- Registered current work records: **213** (BASELINE_LOCK=51, CHANGE_IMPACT_DECLARATION=46, DECISION=8, EVIDENCE=59, FINDING=20, STAGE_CLOSURE=5, STAGE_CLOSURE_MANIFEST=24)

## Exact resume

- Single exact next executable resume point: continue `MD-B18-A002` — **118 of 121** predicates carry a reviewed per-predicate proof basis, **3 remain**, named by `MarketDataReplayVerificationProofBasis::outstanding()` and counted by closure condition 7. All three now need an ownership decision rather than a guard, and none is blocked on a missing surface this attempt can build. `MD-S002-R0004` (deterministic output across runtime/locale/concurrency) needs more than one runtime; it is recorded as an explicit gap in `B18ReleaseCandidateCriteriaTest`, with a guard that fails if a corpus is quietly added for it. `MD-S050-R0040` is **bound**: `F-MD-B18-A002-002` is resolved by implementation rather than carried as a capability gap. Migration `AddListingDelistingKnowledgeTime` gives a delisting its own knowledge time on `md_listings` — one column rather than a listing revision series, because `listing_id` is the primary key and a superseding revision would fracture the very identity the anti-survivorship corpus exists to keep intact — and `EodEvidenceRepository::resolvePublicationAsKnownAt()` answers which publication a reader had at a cutoff. That selector resolves by the declared supersession chain, not by recency: a first version ordered by `publication_id` and `ReadPathShortcutProhibitionTest` refused it, so it was rewritten rather than exempted, and two sealed publications that do not name each other are refused with `EVIDENCE_AS_KNOWN_PUBLICATION_AMBIGUOUS`. The classification the row asserts is now checkable: each of the eight contract cases is bound to the guard that decides it by a cutoff and to the resolver that guard drives, with the cutoff's argument position asserted — every one of these resolvers answers the effective-time question too, so naming the method alone would have let a row be repointed at the effective-time fixture beside it and stay green. `MD-S050-R0056` (production relock on the actual production path) and `MD-S004-R0007` (simulated execution choosing realistic executable prices, which reads as backtest scope rather than MD-B18) both need a scope decision before anything is built. The MariaDB substrate added for `MD-S003-R0025` is available to `MD-S050-R0056` if that decision goes ahead: `UsesMarketDataMariaDb` runs the migrated `tradeaxis_testing` schema inside a rolled-back transaction and skips when MariaDB is unreachable. Phase 3 — bind the 117 to `E-MD-B18-A002-001`, build the self-test, probe the closure conditions, issue evidence and a closure manifest, discharge `MD-DEP-0009`, return to `MD-B19-A001` — cannot start until the denominator is complete, because closure condition `denominator_fully_satisfied` requires 121/121. Two governance decisions remain open and are recorded rather than taken: whether the now runtime-proven AS_KNOWN capability discharges the `CONDITIONAL_APPLICABLE` condition on `MD-S050-R0040` and `MD-S050-R0041` (which would reduce the denominator to 119), and how to resolve **`F-MD-B18-A002-003`** — the mirror disables foreign keys globally, so referential integrity across all 91 DB-backed guards is enforced by production only.
- Current stage source: `MD_IMPLEMENTATION_STAGE_REGISTER.md`
- Pre-epoch W00..W22 verdicts: **historical-only**
