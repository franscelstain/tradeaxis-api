# Legacy Semantic Extract — LX-MD-0044-EVD-01

- Source ID: `LS-MD-0044`
- Original path: `audit/reports/AUDIT_FINAL_STATE.md`
- Original SHA1: `4F88A2D91FB7215D7D64C2F3ACFED4764964CB20`
- Extract role: `EVIDENCE`
- Source range: `L1248-L1278`
- Extract body SHA1: `8B0BA692158BF43D43D7DB7592542BE7405643CF`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Previous implementation evidence snapshot — non-normative handoff

Read-only inspection sebelum documentation closure observed hal-hal berikut. Snapshot ini tidak menciptakan behavior dan dapat menjadi stale setelah implementasi dimulai:

- ticker master: `977` total, `962` current active, `33` rows with delisted date;
- latest canonical bar date: `2026-07-28`, `870` rows;
- calendar marked `2026-07-29`, `2026-07-30`, and `2026-07-31` as trading days without equivalent latest bars/runs at inspection time;
- latest inspected publication for `2026-07-28`: run `72062`, publication `72738`, version `8`, sealed;
- source provider: `yahoo_finance`;
- coverage reported `866/866 = 100%` after excluding `83` suspended and `13` dormant securities;
- output-affecting `config_hash` and `config_snapshot_ref` were null;
- daily scheduling config was disabled, consistent with the stated development phase but not sufficient after operational activation;
- `530` corporate-action rows existed, including `10` synthetic `derived_price_scale_break` rows;
- `18` price-scale break endpoint rows were marked repaired;
- several testing-environment migrations were pending while the default database had the structures;
- full MarketData PHPUnit sebelum koreksi strategi: `1152 tests / 8455 assertions`, pass.
- full MarketData rerun terakhir pada documentation-closure audit mengeksekusi `1153 tests / 8649 assertions` dan menghasilkan `3 errors / 11 failures`; seluruh outcome tersisa berada pada legacy semantic expectations di `DerivationFillsRecordedActionTest`, `PriceAdjustmentTest`, dan `PriceScaleStretchRepairTest` yang masih menuntut synthetic derivation/direct repair yang sekarang dilarang owner strategy.
- targeted re-verification terakhir juga lulus untuk audit synchronization (`4 tests / 253 assertions`), SQLite schema sync (`6 tests / 528 assertions`), dan published-column hash coverage (`16 tests / 24 assertions`).
- setelah strategy update order 20–21, targeted SQLite V2 schema/anti-repair guard lulus `6 tests / 528 assertions`; hasil ini hanya membuktikan mirror shape dan tidak menggantikan full semantic/runtime proof.

Interpretation:

- breadth dan automated coverage sudah kuat;
- reported `100%` coverage belum cukup membuktikan delivery completeness karena dormancy semantics perlu dikoreksi;
- the gap after `2026-07-28` is a development frontier, not a current production incident or architecture blocker;
- provenance, temporal universe, and correction safety remain material correctness gaps independent of the development frontier;
- green tests tidak membatalkan findings ketika tests mengunci behavior yang perlu dikoreksi.
- additive V2 schema mengurangi implementation gap, tetapi nullable rollout fields dan test-mirror pass tidak menutup P0 service/command behavior atau P1 writer/enforcement gaps.

---


<!-- LEGACY_EXTRACT_BODY_END -->
