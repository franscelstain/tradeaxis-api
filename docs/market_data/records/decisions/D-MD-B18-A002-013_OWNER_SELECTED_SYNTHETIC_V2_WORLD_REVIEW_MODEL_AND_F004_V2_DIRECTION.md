# Decision — first independent R0025 golden fixture: world, review model and F-MD-B10-A002-004 direction

- ID: `D-MD-B18-A002-013`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Finding / predicate: `F-MD-B18-A002-025` / `MD-S003-R0025`
- Answers: the three owner questions of `E-MD-B18-A002-091` (Q1, Q2, Q3)
- Change impact: `CI-MD-B18-A002-001`
- Issued: 2026-10-02T13:16:45+07:00
- Status: `APPROVED` — project owner, in the 2026-10-02 instruction that resumes the first independent R0025 fixture
- Mutability: `IMMUTABLE_AFTER_ISSUE`
- Strategy impact: `NONE`; `MD-STRATEGY-FREEZE-20260925-001` unchanged

## Decisions

**Q1 — the world of the first golden: option A.** A synthetic, explicitly labelled V2 world with fixed, independently declared retained roots and literal semantic expectations. Real-market IKPM is not used for the first golden, and the V1 recomputation approach is not retained. The synthetic world is test-only, must never be confused with a real instrument, and is never master-data truth. (`Golden_Fixtures_Specification.md` keeps its rule that a synthetic case does not replace a required real-market semantic case; this decision applies to the first `MD-S003-R0025` exact-publication package only and leaves the real-market families untouched.)

**Q2 — review and approval model.**

* Fixture author: the coding Agent performing the authoring unit.
* Independent reviewer: a separate TradeAxis reviewer or coordinator, never the authoring Agent.
* Owner approver: the project owner.
* The authoring unit stops after the candidate package, its derivation evidence and its immutable fingerprint. The author does not self-review or approve it. No `MD-S003-R0025` proof claim is allowed before independent review and owner approval, and the approval binds the package fingerprint (runtime evidence standard section 6A).

**Q3 — `F-MD-B10-A002-004` direction: confirmed.** Retained-root replay identity is introduced for V2-profile publications only. V2 uses stable retained issuer, instrument and listing roots wherever authority requires semantic identity; it fails closed when a required retained identity is unavailable; it never substitutes `ticker_id`, `listing_id`, `issuer_id`, `instrument_id` or another local allocation identifier, never merely deletes identity keys, and V1 historical behavior stays unchanged.

## What this decision does not do

It does not approve any package, does not decide `MD-S003-R0025`, does not resolve `F-MD-B18-A002-025`, `F-MD-B18-A002-017` or `F-MD-B10-A002-004`, and does not change `D-MD-B18-A002-011`, `D-MD-B18-A002-012` or the runtime evidence standard. `F-MD-B10-A002-003` stays out of scope unless the new world exercises its future-`recorded_at` cutoff issue.
