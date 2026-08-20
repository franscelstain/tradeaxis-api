# Legacy Semantic Extract — LX-MD-0044-GOV-01

- Source ID: `LS-MD-0044`
- Original path: `audit/reports/AUDIT_FINAL_STATE.md`
- Original SHA1: `4F88A2D91FB7215D7D64C2F3ACFED4764964CB20`
- Extract role: `GOVERNANCE`
- Source range: `L41-L83`
- Extract body SHA1: `CB8457C1B653326A826B3094DF9865214F32213E`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Dataset time boundary and development-phase interpretation

### Intentional dataset start

`2023-01-02` adalah **intentional dataset start** untuk baseline awal aplikasi ini.

Artinya:

- tanggal tersebut dipilih sebagai awal historical data yang dibangun, bukan akibat source gagal menyediakan seluruh sejarah sebelum 2023;
- tidak adanya data sebelum `2023-01-02` bukan missing-data bug pada scope aktif;
- pipeline, replay, indicator, dan backtest hanya boleh mengklaim coverage mulai dari dataset boundary tersebut;
- indicator yang membutuhkan warm-up harus menghasilkan deterministic `NULL` pada tanggal-tanggal awal sampai history masing-masing cukup;
- ticker yang listing setelah dataset start mengumpulkan warm-up dari listed date-nya sendiri;
- historical expansion ke tanggal sebelum `2023-01-02` boleh dilakukan kelak bila ada manfaat nyata, tetapi bukan pekerjaan atau blocker sekarang.

Dataset start harus dibedakan dari archived proof window. Proof window `2023-01-02` sampai `2025-10-31` adalah rentang yang pernah dipakai untuk executed audit evidence, sedangkan `2023-01-02` sendiri adalah baseline awal data yang sengaja dipilih untuk pembangunan aplikasi.

### Current last-data date during development

Tanggal terakhir yang tersedia saat ini adalah **development data frontier**, bukan batas capability platform dan bukan bukti aplikasi gagal menjaga production freshness.

Aplikasi masih dibangun dari awal dan belum menjadi operational watchlist yang dipakai secara rutin. Karena itu:

- tidak wajib mempertahankan data selalu sampai trading date terbaru selama fase pembangunan;
- gap setelah last ingested date bukan current production incident;
- `daily_enabled=false` dapat diterima sebagai development-state choice;
- gap tersebut tidak menghalangi koreksi contracts, schema, historical integrity, corporate action, indicator, atau replay;
- last-data date tidak boleh dipakai sebagai alasan menurunkan penilaian correctness komponen yang tidak bergantung pada live freshness.

Freshness baru menjadi hard operational requirement ketika project menetapkan **operational activation date** untuk forward paper watchlist, user-facing watchlist, atau penggunaan rutin lainnya.

Sebelum activation:

1. tentukan `OPERATIONAL_START_DATE` atau governance marker ekuivalen;
2. catch up seluruh trading dates dari development frontier sampai activation boundary melalui controlled backfill;
3. aktifkan dan buktikan daily import/promote scheduling;
4. aktifkan freshness alerts dan stale-consumer protection;
5. mulai menghitung consecutive operational SLO hanya sejak activation boundary.

Dengan interpretasi ini, historical start dan current last-data date tidak menjadi penghalang fase pembangunan. Keduanya tetap harus transparan agar kelak tidak disalahartikan sebagai full-history claim atau production-fresh claim.

---


<!-- LEGACY_EXTRACT_BODY_END -->
