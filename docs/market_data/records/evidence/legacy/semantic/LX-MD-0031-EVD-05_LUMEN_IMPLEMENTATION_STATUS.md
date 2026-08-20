# Legacy Semantic Extract — LX-MD-0031-EVD-05

- Source ID: `LS-MD-0031`
- Original path: `audit/LUMEN_IMPLEMENTATION_STATUS.md`
- Original SHA1: `3FDDFFF0B53D431DAA71A8545F87173445A616F7`
- Extract role: `EVIDENCE`
- Source range: `L4453-L4472`
- Extract body SHA1: `3A53007B750AB341CEF8DCFCDDDBBA7467569BEB`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-27 - OUT-OF-ORDER IMPORT IMPACT EXECUTION FINAL FULL-SUITE PROOF

[STATUS]
- `DONE` for recovered row apply plus actual non-readable affected-date indicator/eligibility reprocess execution.
- `DONE` for correction-safe candidate handling of already-readable affected publications.
- Superseded by the later correction-current patch: automated readable-publication republication is now implemented through correction-current mode instead of normal full-publish.

[FINAL_PROOF]
- Command: `vendor\bin\phpunit tests\Unit\MarketData`.
- Result: OK (576 tests, 8624 assertions).
- Runtime: Time 00:20.787, Memory 42.00 MB.
- Post-doc rerun: OK (576 tests, 8624 assertions), Time 00:19.910, Memory 42.00 MB.

[LOCKED_ASSERTION]
- Resume-only-failed retry success can apply recovered rows through partial upsert and does not delete unrelated same-date tickers.
- Changed recovered/historical bars execute indicator recompute and eligibility rebuild for affected non-readable dates.
- Already-readable affected dates must carry correction lineage before any replacement is published; no fake readable, unvalidated pointer switch, replay verification, or silent live mutation is claimed.

---


<!-- LEGACY_EXTRACT_BODY_END -->
