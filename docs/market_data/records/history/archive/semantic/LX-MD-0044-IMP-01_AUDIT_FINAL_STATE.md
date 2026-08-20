# Legacy Semantic Extract — LX-MD-0044-IMP-01

- Source ID: `LS-MD-0044`
- Original path: `audit/reports/AUDIT_FINAL_STATE.md`
- Original SHA1: `4F88A2D91FB7215D7D64C2F3ACFED4764964CB20`
- Extract role: `IMPLEMENTATION`
- Source range: `L1035-L1136`
- Extract body SHA1: `EABDA186FA28157E40492922A4C14F19A345EF7F`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Implementation roadmap

### Phase 0 — strategy correction

Tujuan: menyelesaikan keputusan semantik sebelum code remediation.

Status dokumentasi: **COMPLETE**. Daftar berikut menjadi baseline yang tidak boleh dibuka kembali hanya untuk menyesuaikan legacy code.

- tetapkan Regular-Market EOD scope;
- tetapkan intentional dataset start `2023-01-02` dan operational activation semantics;
- tetapkan temporal universe semantics;
- tetapkan `STRUCTURAL_ADJUSTED` signal basis;
- tetapkan corporate-action verification hierarchy;
- tetapkan coverage versus eligibility separation;
- tetapkan no-history-rewrite invariant;
- tetapkan Yahoo bootstrap boundary;
- perbarui owner dan supporting contracts stage 1 sampai 21, kunci work order `W00`–`W22`, dan assign seluruh dokumen/deliverable/proof pada conformance matrix.

Exit gate:

- tidak ada contradiction antar owner docs;
- semua istilah memiliki satu owner;
- tidak ada keputusan P0 yang masih ambigu.

Exit gate Phase 0 telah dipenuhi pada level dokumentasi. Phase 1 dan seterusnya adalah pekerjaan implementasi dan pembuktian.

### Phase 1 — stop unsafe behavior

Tujuan: mencegah kerusakan historis baru.

- disable/remove direct price-scale apply path;
- ubah price break menjadi anomaly-only;
- blokir unverified derived corporate action dari adjustment;
- perbaiki point-in-time universe resolver;
- tambahkan regression tests untuk keempat area tersebut.

Exit gate:

- zero code path dapat mengubah sealed bars/history in-place;
- inactive-now-but-active-then ticker muncul pada historical universe;
- unresolved break selalu menghasilkan quarantine/non-eligibility.

### Phase 2 — correct analytical data products

Tujuan: memastikan analytical data product coherent.

- buat raw, structural-adjusted, dan total-return product semantics;
- implement versioned factor application;
- perbaiki exact ATR seed/state;
- rename turnover proxy dan siapkan actual value fields;
- perbaiki ex-date event windows;
- hilangkan zero-price placeholder contradiction.

Exit gate:

- split/reverse/rights fixture menghasilkan continuous adjusted OHLC;
- indicator rerun menghasilkan byte/number-equivalent outputs untuk publication/config sama;
- liquidity field tidak misleading.

### Phase 3 — provenance and operations

Tujuan: menjadikan pipeline stabil untuk forward validation.

- isi config hash dan snapshot reference;
- tambahkan immutable source observation identity;
- aktifkan daily scheduling dan promote/readiness path;
- tambahkan freshness state dan alerts;
- sinkronkan testing migrations;
- jalankan backfill/replay determinism proof.

Exit gate:

- daily pipeline berjalan tanpa manual intervention normal;
- stale date tidak dapat dibaca sebagai fresh;
- setiap consumer-facing market-data row dapat dilacak ke source/publication/config/factor.

### Independent downstream proof period — outside market-data readiness

Tujuan downstream: membuktikan manfaat use case awal tanpa membeli data berbayar terlebih dahulu.

- jalankan forward paper watchlist;
- rekam quality incidents terpisah dari strategy outcomes;
- ukur coverage, freshness, anomaly, correction, dan replay stability;
- downstream watchlist mengukur expectancy, drawdown, turnover, dan usefulness secara out-of-sample setelah biaya asumsi yang realistis;
- kumpulkan user feedback dan operational cost.

Exit gate:

- manfaat cukup stabil untuk membuat keputusan produk;
- kualitas source limitation dapat diukur, bukan diasumsikan;
- keputusan paid provider dapat dibuat berdasarkan evidence bila kelak diperlukan.

Seluruh kegiatan dan exit gate pada bagian ini milik validasi produk/watchlist downstream. Kegiatan ini tidak diperlukan untuk menutup dokumentasi, implementation conformance, atau operational validation market-data dan tidak boleh mengubah fakta historis agar outcome strategi tampak lebih baik.

### Future provider decision

Bagian ini **bukan pekerjaan sekarang**.

Ia hanya dibuka bila value, SLA, licensing, correction authority, richer fields, redistribution, atau commercial use menjadi kebutuhan nyata. Jika dibuka, lakukan provider adapter evaluation, bounded parallel reconciliation, controlled priority switch, dan lineage-preserving publication transition.

---


<!-- LEGACY_EXTRACT_BODY_END -->
