# Legacy Semantic Extract — LX-MD-0044-CTX-03

- Source ID: `LS-MD-0044`
- Original path: `audit/reports/AUDIT_FINAL_STATE.md`
- Original SHA1: `4F88A2D91FB7215D7D64C2F3ACFED4764964CB20`
- Extract role: `CONTEXT`
- Source range: `L222-L267`
- Extract body SHA1: `89A9D4E49B37273DCA7183304975BFEEAA6F2E63`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Strengths that must be preserved

Hal berikut sudah menjadi aset penting dan tidak boleh hilang saat remediation:

1. **Import dan promote separation**

   Acquisition tidak otomatis berarti publication readable.

2. **Publication-aware consumer model**

   Consumer diarahkan membaca sealed/current/readable publication, bukan `MAX(date)` atau raw table secara bebas.

3. **Coverage and publishability gates**

   Partial provider response tidak otomatis menjadi readable dataset.

4. **Correction, seal, pointer, replay, dan evidence concepts**

   Fondasi ini tepat untuk deterministic rebuild dan audit trail.

5. **Date-driven acquisition dan backfill**

   Provider window tidak dijadikan capability limit domain.

6. **Retry, throttle, partial-tolerant acquisition, dan failure taxonomy**

   Ini sesuai dengan risiko bootstrap provider yang dapat rate limit atau berubah.

7. **Provider abstraction direction**

   Yahoo-specific mapping berada pada adapter dan tidak seharusnya masuk ke indicator/watchlist contract.

8. **Broad automated test coverage**

   Full MarketData suite terakhir yang dijalankan pada audit ini lulus `1152 tests / 8455 assertions`. Bukti ini menunjukkan breadth implementasi, walaupun belum membuktikan seluruh semantic correctness.

9. **Current universe acquisition breadth**

   Snapshot database menunjukkan master dan latest bars mencakup sebagian besar saham aktif. Kekurangannya berada pada temporal semantics dan freshness, bukan sekadar jumlah ticker.

10. **Yahoo bootstrap decision**

    Pemakaian Yahoo Finance adalah keputusan biaya dan fase produk yang sah, selama quality bar tidak diturunkan dan provider tidak menjadi domain truth.

---


<!-- LEGACY_EXTRACT_BODY_END -->
