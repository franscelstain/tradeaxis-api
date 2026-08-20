# Market Data Implementation Stage Register

> **Status:** CURRENT ORCHESTRATION INDEX
> **Verification Epoch:** `MD-REBASELINE-20260820-001`

Pre-epoch `W00..W22` PASS/FAIL/PARTIAL/DONE/PROVEN/CONFORMANT is historical-only. `NOT_STARTED` here does not mean code does not exist; it means current revalidation has not been opened.

| Stage | Maps to frozen scope | Lifecycle state | Verdict | Latest attempt / Work ID | Baseline ID | Strategy coverage | Residue state | Integrity gate | Dependency | Open finding | Closure manifest | Resume from |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| `MD-B00` | `W00` — Preflight dan implementation ledger | `DONE` | `PASS` | `MD-B00-A001` | `MD-B00-A001-BL001` | `0/0` | `CONFORMANT_NO_HARMFUL_RESIDUE_FOUND` | `PASS` | `MD-DEP-0003` | `F-MD-B00-A001-002`; `F-MD-B00-A001-003`; `F-MD-B00-A001-004` | `SC-MD-B00-A001-001` | proceed to `MD-B01` |
| `MD-B01` | `W01` — Kunci scope, boundary, dataset start, development frontier, activation semantics, dan non-goals | `IN_PROGRESS` | — | `MD-B01-A003` | `MD-B01-A003-BL001` | `21/127` | `CONFORMANT_NO_HARMFUL_RESIDUE_FOUND` | `PASS` | `MD-DEP-0004` | `F-MD-B01-A001-001` (P0, blocks `DONE`); `F-MD-B01-A003-001` (P2) | — | continue `MD-B01`; see the open-stage note below |
| `MD-B02` | `W02` — Kunci Yahoo bootstrap dan provider-neutral ports | `NOT_STARTED` | — | — | — | `0/35 mandatory + 0/4 optional` | `NOT_ASSESSED` | `NOT_RUN` | — | — | — | begin/revalidate stage |
| `MD-B03` | `W03` — Bangun migration framework, additive schema skeleton, repository interfaces, reason registry, dan test harness skeleton | `NOT_STARTED` | — | — | — | `0/0` | `NOT_ASSESSED` | `NOT_RUN` | `MD-DEP-0001`; `MD-DEP-0002`; `MD-DEP-0003` | `F-MD-B00-A001-001` (P0) | — | begin/revalidate stage; clean install and reason seed are broken |
| `MD-B04` | `W04` — Bangun immutable configuration snapshot dan semantic version bindings | `NOT_STARTED` | — | — | — | `0/167 mandatory + 0/1 optional` | `NOT_ASSESSED` | `NOT_RUN` | — | — | — | begin/revalidate stage |
| `MD-B05` | `W05` — Bangun temporal issuer/instrument/listing/symbol/provider mapping **serta temporal sector membership foundation** | `NOT_STARTED` | — | — | — | `0/55 mandatory + 0/1 optional` | `NOT_ASSESSED` | `NOT_RUN` | — | — | — | begin/revalidate stage |
| `MD-B06` | `W06` — Bangun calendar/session/status expectation | `NOT_STARTED` | — | — | — | `0/44 mandatory + 0/1 optional` | `NOT_ASSESSED` | `NOT_RUN` | — | — | — | begin/revalidate stage |
| `MD-B07` | `W07` — Bangun immutable source observations dan acquisition ports/adapters | `NOT_STARTED` | — | — | — | `0/78` | `NOT_ASSESSED` | `NOT_RUN` | — | — | — | begin/revalidate stage |
| `MD-B08` | `W08` — Bangun resilience, retry/backoff/rate limit, manual recovery, quarantine, dan failure taxonomy | `NOT_STARTED` | — | — | — | `0/62` | `NOT_ASSESSED` | `NOT_RUN` | — | — | — | begin/revalidate stage |
| `MD-B09` | `W09` — Bangun import-only, canonical `RAW`, invalid-row, dedup/conflict, dan candidate persistence | `NOT_STARTED` | — | — | — | `0/55 mandatory + 0/1 optional` | `NOT_ASSESSED` | `NOT_RUN` | — | — | — | begin/revalidate stage |
| `MD-B10` | `W10` — Bangun immutable publication state machine, manifest, seal, pointer, correction, supersession, dan no-in-place-rewrite | `NOT_STARTED` | — | — | — | `0/281 mandatory + 0/1 optional` | `NOT_ASSESSED` | `NOT_RUN` | — | — | — | begin/revalidate stage |
| `MD-B11` | `W11` — Bangun verified corporate-action event/factor lifecycle dan anomaly-only detector | `NOT_STARTED` | — | — | — | `0/80 mandatory + 0/1 optional` | `NOT_ASSESSED` | `NOT_RUN` | — | — | — | begin/revalidate stage |
| `MD-B12` | `W12` — Bangun coherent `RAW`/`STRUCTURAL_ADJUSTED`/`TOTAL_RETURN` product engine | `NOT_STARTED` | — | — | — | `0/23` | `NOT_ASSESSED` | `NOT_RUN` | — | — | — | begin/revalidate stage |
| `MD-B13` | `W13` — Bangun actual/proxy daily market metrics | `NOT_STARTED` | — | — | — | `0/19` | `NOT_ASSESSED` | `NOT_RUN` | — | — | — | begin/revalidate stage |
| `MD-B14` | `W14` — Bangun deterministic indicator engine dan correction dependency graph | `NOT_STARTED` | — | — | — | `0/66 mandatory + 0/6 optional` | `NOT_ASSESSED` | `NOT_RUN` | — | — | — | begin/revalidate stage |
| `MD-B15` | `W15` — Bangun temporal coverage expectation/delivery gate | `NOT_STARTED` | — | — | — | `0/76 mandatory + 0/2 optional` | `NOT_ASSESSED` | `NOT_RUN` | — | — | — | begin/revalidate stage |
| `MD-B16` | `W16` — Bangun explainable row-level data usability | `NOT_STARTED` | — | — | — | `0/22 mandatory + 0/2 optional` | `NOT_ASSESSED` | `NOT_RUN` | — | — | — | begin/revalidate stage |
| `MD-B17` | `W17` — Bangun atomic versioned market-data read product dan freshness/readiness gateway | `NOT_STARTED` | — | — | — | `0/64 mandatory + 0/1 optional` | `NOT_ASSESSED` | `NOT_RUN` | — | — | — | begin/revalidate stage |
| `MD-B18` | `W18` — Bangun exact-publication dan as-known replay | `NOT_STARTED` | — | — | — | `0/28 mandatory + 0/2 optional` | `NOT_ASSESSED` | `NOT_RUN` | — | — | — | begin/revalidate stage |
| `MD-B19` | `W19` — Bangun daily/backfill/correction/replay operations, locking, observability, evidence export, dan recovery | `NOT_STARTED` | — | — | — | `0/125 mandatory + 0/1 optional` | `NOT_ASSESSED` | `NOT_RUN` | `MD-DEP-0003` | `F-MD-B00-A001-001` (P0) | — | begin/revalidate stage; 8 test files bound to dead paths |
| `MD-B20` | `W20` — Implementasikan supplemental session snapshot hanya bila feature state dinyatakan aktif | `NOT_STARTED` | — | — | — | `0/0 mandatory + 0/30 optional` | `NOT_ASSESSED` | `NOT_RUN` | — | — | — | begin/revalidate stage |
| `MD-B21` | `W21` — Global schema/config/code/test/ops convergence, backfill, constraint hardening, dan full semantic proof | `NOT_STARTED` | — | — | — | `0/0` | `NOT_ASSESSED` | `NOT_RUN` | `MD-DEP-0001`; `MD-DEP-0002`; `MD-DEP-0003` | `F-MD-B00-A001-001` (P0) | — | blocked until `MD-DEP-0001` and `MD-DEP-0002` close |
| `MD-B22` | `W22` — Independent implementation audit, pre-activation catch-up, operational validation, dan relock | `NOT_STARTED` | — | — | — | `0/0` | `NOT_ASSESSED` | `NOT_RUN` | `MD-DEP-0003` | `F-MD-B00-A001-001` (P0) | — | begin/revalidate stage; audit instruments read removed composites |

## Closure rule

A stage may become `DONE` only with current Attempt/Baseline/Epoch evidence, required strategy coverage, tests, residue verdict, dependencies resolved or explicitly governed, integrity/relationship gates, and closure manifest. Old Wxx verdicts cannot populate these fields.

## Current open stage: MD-B01

Latest attempt `MD-B01-A003`, baseline `MD-B01-A003-BL001`, evidence `E-MD-B01-A003-001`. Prior attempts `MD-B01-A001` and `MD-B01-A002`.

Done at `MD-B01-A001`: the one `MD-B01`-owned dead documentation path was rebound and guarded, `TerminologyOwnerVocabularyTest` returned to 7/7, and 22 new executable checks were added covering scope drift, the four time boundaries, activation semantics, and policy-vocabulary leakage across tables, columns, status enums, reason codes, command signatures, and config keys. Seven mutations confirmed each guard fails closed. Five rules reached `SATISFIED`.

Done at `MD-B01-A002`: 16 further checks covering date-driven capability and provider limitation abstraction, plus boundary-direction and alias-containment guards. Provider transport shape is proven absent from `app/Domain` and `app/Application` and present in exactly one adapter; the acquisition port is provider-neutral and date-addressed; 123 market-data files carry zero policy-namespace imports; the bare `eligible` column exists on exactly two tables. Eight mutations confirmed each guard fails closed. Eleven more rules reached `SATISFIED`, bringing coverage to `16/127`.

Done at `MD-B01-A003`: 8 checks on the locked interpretation rules governing what the platform may claim about itself. Three are behavioural — they seed a genuinely readable publication and call `MarketDataReadinessService`, because a freshness claim is made by the readiness payload rather than by a document. A ready date with no governed marker reports `is_ready` true and `activation_state` `DEVELOPMENT`; the blocked path answers in the same vocabulary; setting the marker moves the state and only for dates at or after it. No executable surface makes a consecutive-SLO claim, no product term is aliased to another, the eligibility surface carries no approval or ranking vocabulary, and nothing asserts `decision-grade` across code, reason codes, command names, test names, or 50+ active documents. Eight mutations confirmed each guard fails closed, each verified as applied first. Five more rules reached `SATISFIED`, bringing coverage to `21/127`.

Also raised at `MD-B01-A003`: `F-MD-B01-A003-001` (P2). Ten frozen strategy contracts use the bare word `eligible` without repeating its data-usability meaning, which `Domain_Boundary_Invariants_LOCKED.md` calls the only thing preventing the misreading. `MD-S020-R0067` is therefore not satisfiable by implementation work — its subject is the wording of frozen strategy.

Not done, and why: `F-MD-B01-A001-001` establishes that 15 of the 127 required rules are bare colon-terminated list headers and 3 more are owner pointers, so `127/127` cannot be reached by evidence at any attempt. No matrix row was reclassified to work around this, because `DOCUMENT_CHANGE_POLICY.md` forbids weakening governance to make implementation pass. `MD-B01` therefore stays `IN_PROGRESS` pending a reviewed governance decision.

**This blocks the whole track, not just `MD-B01`.** The same scan across all stages found 210 of 1407 required rules are non-predicate list headers, affecting 17 of the 18 stages that carry required rules; only `MD-B13` has a clean set. Stages may continue accumulating `SATISFIED` rules, but none except `MD-B13` can legitimately reach `DONE` on coverage grounds until `MD-DEP-0004` closes.

Do-not-repeat, `MD-B01-A001`: the first policy vocabulary missed `ranked_picks`, one of the boundary contract's own forbidden examples, because a bare `rank` alternative does not match `ranked`. A naming guard is only as good as its word list, so the word list is now asserted against the contract's example lists in both directions rather than trusted.

Do-not-repeat, `MD-B01-A002`: two of them. First, a mutation that does not mutate is indistinguishable from a guard that does not guard — one mutation targeted a `use Illuminate` line the file did not contain, reported "guard did not react", and was a no-op; verify the mutation landed before concluding anything about the guard. Second, absence is only evidence when paired with a positive locator: every "X is not in this layer" check is paired with "X is in the adapter", so a rename cannot turn the suite green by making the search find nothing anywhere.

Do-not-repeat, `MD-B01-A003`: a scan is only as good as the pattern behind it. The alias-repetition scan first matched only snake_case `data_usable` and reported 18 non-conforming documents; seven of those state the meaning in prose as `data-usability`, which satisfies the rule as written, and the true figure is 11. A finding published from the first number would have named seven documents as defective when they are not. Check what a pattern misses before you count with it — and when a guard flags its own test method, rename the method rather than adding a self-exemption, because the carve-out weakens the guard for every other file.

## Scope of the MD-B00 closure

`MD-B00` is `DONE` for the baseline record only. It certifies that the current code/schema/test/evidence state is recorded and that every active document carries a conformance-matrix assignment. It certifies nothing about implementation correctness.

At that closure the PHPUnit suite is `RED` (1488 tests, 26 errors, 108 failures), clean install cannot pass migration 3 of 62, and the reason-code seeder throws. Those are recorded baseline facts owned by `MD-B03`, `MD-B19`, and `MD-B21`, not defects in `MD-B00`. All 1407 required strategy rules remain `NOT_ASSESSED` and every existing artifact remains `NOT_ASSESSED_REVALIDATION_REQUIRED`.

