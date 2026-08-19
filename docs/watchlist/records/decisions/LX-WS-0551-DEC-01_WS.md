# Legacy Role Extract — WS — DECISION

> **Document Type:** DECISION
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0551-DEC-01`
> **Legacy Source ID:** `LS-WS-0551`
> **Legacy Work Key:** `WS`
> **Original Path:** `docs/watchlist/system/policies/weekly_swing/13_WS_CONTRACT_TEST_CHECKLIST.md`
> **Original SHA1:** `99AA10494EB908109701F152E21402943FEF0B63`
> **Source Sections:** L165-L175 C171 Final Closure and Separate New-Strategy Research; L176-L189 WS New Strategy R02 Closure; L190-L211 WS Tail Risk S01 Closure; L212-L231 WS Price Quality P01 Closure
> **Extract Body SHA1:** `824437BE0F1A22BB7C37D1293492243145E23D01`
> **Current Authority:** NO

The body below is an exact copy of the identified original sections. No semantic rewriting was applied.

---

## C171 Final Closure and Separate New-Strategy Research

- [ ] `C171_FAILED_NOT_READY_NO_FURTHER_REMEDIATION` remains immutable;
- [ ] no additional C171 catalog, DRAFT, official IS, or C172 OOS is created;
- [ ] separate strategy research uses a new scope identity and does not present itself as C171 remediation;
- [ ] failed C171 evidence is read-only diagnostic input, not a best-of-failed binding;
- [ ] equity research features are bound to exact signal-date publication lineage;
- [ ] realized return, actual entry gap, fill rule, and exit result remain post-selection diagnostic fields;
- [ ] no OOS table is read until a candidate in the separate scope passes every unchanged canonical IS gate;
- [ ] tracker `ACTIVE SESSION` values remain synchronized after the scope transition.

## WS New Strategy R02 Closure

- [ ] exactly three initial one-idea candidates are bound before Official IS;
- [ ] initial Official IS uses only the canonical `2023-01-02..2025-05-21` window;
- [ ] at most one remediation is persisted and evaluated;
- [ ] remediation selection remains identical to its declared source candidate;
- [ ] remediation exit routes are fixed before entry and evaluated chronologically;
- [ ] a Dn close signal cannot exit before D(n+1) open;
- [ ] raw tradable OHLCV and IDX tick normalization remain mandatory;
- [ ] future target outcomes and future-derived buckets cannot select an exit route;
- [ ] every canonical IS threshold remains unchanged;
- [ ] failure of any gate after the one remediation closes R02 with OOS row count zero;
- [ ] closed R02 cannot be rescued by another remediation, blacklist, promotion, PLAN, or rollout.

## WS Tail Risk S01 Closure

- [ ] S01 uses a separate scope identity and does not reopen R02;
- [ ] immutable source eval `211`, paramset `19`, artifact hash, and evidence
  manifest are verified before diagnostic interpretation;
- [ ] the diagnostic is read-only and never reads OOS;
- [ ] exactly three one-idea candidates are locked before DRAFT persistence and
  Official IS;
- [ ] H1 benchmark and H2 tick-risk features use exact signal-date lineage and
  fail closed when unavailable;
- [ ] H3 close-loss signal and remediation close-loss signal execute only at
  the next trading-day open;
- [ ] all target, close-signal, and time-exit routes are evaluated
  chronologically from raw tradable OHLCV;
- [ ] no realized return, future path, ticker blacklist, month blacklist, or
  OOS result routes candidate selection or execution;
- [ ] initial evals `212-214` use unchanged canonical IS gates;
- [ ] at most one S01 remediation is persisted and evaluated;
- [ ] remediation eval `215` failure closes S01 with no further remediation;
- [ ] OOS row count remains zero and no promotion, ACTIVE paramset, PLAN,
  CONFIRM, activation, or rollout occurs.

## WS Price Quality P01 Closure

- [x] P01 uses a separate scope and does not reopen C171, R02, or S01.
- [x] thresholds 50, 100, and 200 were locked before diagnostic runtime.
- [x] only diagnostic-authorized thresholds 50 and 100 were persisted.
- [x] exact signal-date close, ROC20, and IHSG regime fail closed when absent.
- [x] initial evals 216-217 used unchanged canonical IS gates and both failed.
- [x] exactly one remediation retained C1 selection and introduced no new
  signal-price threshold.
- [x] the close-loss signal executes only at the next trading-day open.
- [x] eval 218 generic model-label drift was detected and not used as final
  identity evidence.
- [x] identity repair changed no strategy semantics and did not increment the
  remediation round.
- [x] authoritative eval 219 stores `SEQ_TP05_PCL1NO_TIME` identity and
  reproduced eval 218 metrics.
- [x] eval 219 failed four unchanged canonical gates and closed P01.
- [x] no OOS service, repository, or table read; no promotion, ACTIVE
  paramset, PLAN, CONFIRM, activation, or rollout occurred.
- [x] focused P01 and full Watchlist PHPUnit suites pass.
