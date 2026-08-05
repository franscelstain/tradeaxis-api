# YAHOO FINANCE BOOTSTRAP SOURCE STRATEGY

## Status
CURRENT-PHASE STRATEGY / NORMATIVE COMPANION

Dokumen ini adalah owner keputusan strategis pemakaian `api_free/yahoo_finance` pada fase aktif dan owner batas keputusan provider berbayar pada masa depan. Perilaku runtime yang mengikat tetap dimiliki oleh:
- `Source_Data_Acquisition_Contract_LOCKED.md`
- `EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`

Jika terdapat perbedaan perilaku teknis, kedua owner contract tersebut tetap menjadi authority.

---

## Decision statement

`api_free/yahoo_finance` dipilih dan diterima sebagai **bootstrap primary EOD source** untuk membuktikan manfaat platform market-data bagi watchlist Weekly Swing sebelum pengeluaran data berbayar dibenarkan oleh evidence.

Yahoo Finance adalah keputusan fase produk yang disengaja, bukan provider yang sekadar ditoleransi karena belum ada pengganti.

Pemilihan ini:
- merupakan keputusan fase awal yang disengaja;
- bukan pernyataan bahwa Yahoo Finance adalah sumber resmi bursa atau sumber final platform;
- bukan penurunan standar integritas canonical market-data;
- bukan kesalahan strategi selama batas provider diisolasi di source adapter dan seluruh quality gate tetap berlaku;
- memungkinkan biaya awal difokuskan pada pembuktian bahwa sistem dapat menghasilkan watchlist yang berguna sebelum membeli data berbayar.

Market-data harus dibangun dengan arsitektur yang kuat sejak awal. Investasi provider berbayar hanya boleh dipertimbangkan kemudian bila evidence nilai produk atau kebutuhan operasional nyata membenarkannya; investasi tersebut bukan kelanjutan yang sudah diputuskan.

Tidak adanya provider berbayar pada fase sekarang bukan defect, audit gap, technical debt, atau blocker dengan sendirinya. Temuan integritas, provenance, historical correctness, dan reproducibility tetap harus diperbaiki tanpa menunggu pergantian provider.

---

## Current source roles (LOCKED)

- primary bootstrap EOD source: `api_free/yahoo_finance`
- controlled recovery source: `manual_file`
- canonical domain truth: governed, validated, versioned publication; bukan provider payload atau nama provider

`manual_file` adalah jalur recovery yang dikendalikan, bukan second live feed, consensus member, atau alasan melakukan merge field-by-field. Satu publication candidate tetap harus memiliki source mode dan provenance yang eksplisit.

---

## Why Yahoo Finance is appropriate for the current phase

Fokus aktif adalah EOD market-data untuk watchlist dengan policy Weekly Swing, bukan tick data, order book, execution routing, atau low-latency trading.

Untuk tujuan pembuktian awal tersebut, Yahoo Finance memberi jalur yang pragmatis karena:
- data EOD dapat diperoleh tanpa biaya subscription provider komersial pada fase awal;
- cakupannya dapat digunakan untuk research, pembangunan, dan forward validation awal selama setiap output tetap melewati quality dan readability gates;
- integrasi yang sudah tersedia mempercepat forward validation dan pengumpulan evidence operasional;
- modal dapat diprioritaskan untuk membuktikan manfaat sistem, bukan dihabiskan sebelum product value terbukti;
- kelemahannya dapat dibatasi melalui provenance, validation, coverage gate, quarantine, correction, dan publication lifecycle.

Keputusan ini tidak mengklaim bahwa data gratis memiliki authority, SLA, field richness, revision service, atau jaminan distribusi yang setara dengan data resmi IDX atau vendor berlisensi.

Dokumen ini juga tidak memberi atau menyiratkan hak redistribusi data. Penggunaan provider tetap harus mematuhi access terms dan usage terms yang berlaku pada saat data diambil atau digunakan.

---

## Current-phase objective

Tujuan fase aktif adalah membuktikan bahwa sistem dapat:
- memperoleh dan mempublikasikan EOD bars secara terkendali dan terukur;
- menjaga historical integrity dan reproducibility;
- menghasilkan indicator dan eligibility yang deterministic;
- mendukung watchlist Weekly Swing yang dapat dijelaskan dan diuji;
- menunjukkan manfaat melalui forward observation, paper evaluation, dan feedback pengguna;
- menghasilkan evidence yang cukup untuk memutuskan apakah investasi data berbayar layak dilakukan kemudian.

Keberhasilan watchlist tidak boleh dinilai hanya dari backtest atau beberapa trade yang menguntungkan. Evidence harus memisahkan kualitas market-data, kualitas policy Weekly Swing, dan manfaat bagi pengguna.

Keputusan bootstrap tidak sama dengan klaim bahwa source atau operasi saat ini sudah decision-grade production ready. Relock tersebut tetap mengikuti acceptance gates pada audit final dan membutuhkan executed proof yang terpisah.

---

## Non-negotiable safeguards while Yahoo Finance is active

Pemakaian bootstrap source tidak mengubah quality bar platform.

Selama `api_free/yahoo_finance` menjadi primary active source:
- source identity dan acquisition timestamp harus traceable;
- provider-specific mapping harus tetap berada di adapter/import strategy;
- requested trade date tidak boleh ditentukan oleh default query window provider;
- canonical row tetap harus lolos row validation, dedup, dan coverage contract;
- partial, missing, malformed, stale, atau suspicious observations harus menghasilkan evidence dan fail-safe outcome;
- anomaly tidak boleh otomatis dianggap corporate action yang terverifikasi;
- historical publication tidak boleh diubah in-place untuk menutupi anomaly provider;
- indicator dan eligibility tidak boleh menghitung data invalid sebagai harga pasar yang sah;
- consumer hanya boleh membaca publication yang memenuhi readability contract;
- penggunaan Yahoo Finance tidak boleh disembunyikan atau diberi label seolah-olah data resmi IDX.
- access terms, usage terms, dan batas redistribusi provider tetap harus dipatuhi.

Dengan safeguards ini, risiko provider diperlakukan sebagai risiko yang dikelola, bukan diwariskan sebagai kelemahan domain model.

---

## What is explicitly not required now

Fase aktif tidak mewajibkan pekerjaan berikut:
- membeli subscription market-data;
- memilih atau melakukan tender vendor berbayar;
- mengintegrasikan feed resmi IDX;
- membangun multi-provider consensus atau majority voting;
- menjalankan dual-feed production;
- menegosiasikan redistribution rights;
- membuat proyek migrasi provider atau menetapkan tanggal migrasi;
- menambah field institusional yang belum dibutuhkan Weekly Swing.

Hal-hal tersebut tidak boleh mengalihkan fokus dari perbaikan integritas data, stabilitas pipeline, dan pembuktian manfaat watchlist saat ini.

Karena bukan current requirement, pekerjaan paid-provider tidak boleh dibuat seolah-olah merupakan backlog remediation yang tertunda. Fase sekarang tidak memerlukan vendor shortlist, tender, purchase order, reserved budget, migration milestone, atau target date pergantian source.

---

## Source continuity exposure (LOCKED)

Dokumen ini memiliki keputusan menerima risiko source. Karena itu ia juga harus menyatakan besarnya risiko itu, bukan hanya manfaatnya.

`EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md` memiliki perilaku kegagalan **per-run** — retry, quarantine, degraded state, alert. Yang dinyatakan di sini adalah kegagalan sebagai **kondisi berkelanjutan**, yang tidak dimiliki kontrak mana pun sebelumnya.

### Paparan yang diterima secara sadar

- **Tidak ada SLA, support channel, atau jalur eskalasi.** Bila akses berubah, rate limit mengetat, endpoint bergeser, atau permintaan diblokir, tidak ada pihak yang dapat dimintai pemulihan. Ini konsekuensi wajar dari sumber gratis tak resmi dan bukan cacat yang muncul belakangan.
- **Tidak ada pemberitahuan perubahan.** Perubahan schema atau perilaku diketahui dari kegagalan validasi, bukan dari pengumuman.
- **Tidak ada authoritative correction.** Bila provider merevisi nilai historis, tidak ada notifikasi revisi yang dapat diandalkan.

### Batas jalur pemulihan (LOCKED)

`manual_file` adalah **rescue satu tanggal**, bukan jalur kelangsungan operasi.

Universe aktif berjumlah sekitar sembilan ratus emiten. Memulihkan satu hari perdagangan secara manual berarti menyiapkan bar untuk seluruh universe itu, dengan provenance dan validasi yang sama ketatnya. Itu dapat dilakukan sesekali untuk satu tanggal; ia tidak dapat menggantikan akuisisi harian selama periode yang panjang.

Menyebut `manual_file` sebagai jalur pemulihan tanpa menyatakan batas ini akan membuat rencana kelangsungan terlihat ada padahal tidak.

### Risiko kehilangan permanen (LOCKED)

Intentional dataset start adalah `2023-01-02`, sementara development frontier bergerak maju. Rentang di antara keduanya yang **belum di-backfill** hanya ada di sisi provider.

Bila akses hilang sebelum rentang itu diambil, sejarah tersebut tidak dapat dipulihkan dari sumber ini, dengan biaya berapa pun. Observation yang sudah diakuisisi tetap milik platform dan immutable; yang belum diakuisisi tidak dijamin masih akan tersedia.

Konsekuensinya satu, dan ia adalah pekerjaan fase sekarang:

> **Kelengkapan backfill terhadap intentional dataset start adalah mitigasi risiko, bukan sekadar kelengkapan data.** Menundanya berarti membiarkan paparan yang tidak dapat dibalik.

Ini **bukan** pekerjaan provider berbayar dan tidak boleh dibaca sebagai backlog vendor. Ia adalah akuisisi memakai sumber yang aktif sekarang.

### Ambang bagi trigger availability

Trigger *availability* pada daftar di bawah tidak boleh dibiarkan tanpa ukuran. Kontrak operasi wajib menetapkan berapa hari perdagangan berturut-turut kegagalan akuisisi yang mengubah status dari insiden operasional menjadi bukti bahwa kapabilitas source menjadi penghambat. Tanpa ambang, trigger itu tidak pernah terpicu — selalu ada alasan menunggu satu hari lagi.

---

## Licensing basis (LOCKED)

Keputusan bootstrap ini dibenarkan oleh penghematan biaya. Penghematan itu hanya sah bila penggunaannya memang diizinkan, sehingga dasar lisensi adalah bagian dari keputusan, bukan catatan kaki.

Dokumen ini sebelumnya menyatakan empat kali bahwa terms provider "harus dipatuhi", tanpa satu kali pun menyatakan **penggunaan seperti apa yang sedang dilakukan**. Kewajiban tanpa deklarasi tidak dapat diaudit.

Karena itu wajib dicatat dan dipelihara:

- **Penggunaan yang dinyatakan saat ini** — cakupan aktual pemakaian data pada fase ini, termasuk apakah ia bersifat internal/non-komersial, dan siapa yang mengaksesnya.
- **Terms yang berlaku saat pengambilan**, disimpan sebagai evidence beserta tanggal pembacaannya. Terms provider dapat berubah tanpa pemberitahuan; klaim kepatuhan tanpa tanggal tidak dapat diverifikasi.
- **Batas yang diketahui** — khususnya redistribusi, penyimpanan, dan pemakaian otomatis.
- **Peristiwa yang mengubah dasar ini** — pemakaian komersial, akses publik, redistribusi keluaran, atau perubahan terms.

Bila penggunaan bergerak melampaui yang dinyatakan, dasar lisensi berubah lebih dulu dari kebutuhan teknis mana pun. Ini juga yang menghubungkan bagian ini dengan trigger *penggunaan komersial* di bawah: pemicu itu bukan soal kualitas data, melainkan soal izin.

Menyatakan dasar lisensi bukan pekerjaan provider berbayar dan tidak menyiratkan migrasi.

---

## Evidence that may justify a later paid-data decision

Evaluasi sumber berbayar baru menjadi relevan bila satu atau lebih kondisi berikut muncul:
- watchlist telah menunjukkan manfaat yang stabil melalui forward/out-of-sample evaluation;
- penggunaan dan kebutuhan pengguna membenarkan biaya data;
- kualitas, freshness, availability, corporate-action accuracy, atau field coverage Yahoo menjadi penghambat manfaat yang sudah terbukti;
- diperlukan SLA, support, authoritative correction, licensing, atau redistribution rights;
- aplikasi bergerak menuju penggunaan komersial atau automation dengan risiko yang lebih tinggi;
- tersedia modal atau pendapatan yang memang dialokasikan untuk peningkatan sumber data.

Daftar ini adalah **future decision trigger**, bukan backlog yang harus dikerjakan pada fase sekarang.

Tidak ada satu trigger pun yang otomatis memilih vendor atau mewajibkan migrasi. Hasil evaluasi di masa depan boleh tetap mempertahankan Yahoo bila evidence, terms, risk, dan kebutuhan aktual masih membenarkannya. Bila evaluasi dibuka, keputusan provider harus membandingkan capability, kualitas, biaya, lisensi, dan kebutuhan Weekly Swing pada saat itu.

---

## Development and operational-activation boundary

Selama aplikasi masih dalam development phase, gap setelah development data frontier bukan alasan untuk membeli provider dan bukan evidence bahwa Yahoo harus diganti.

Operational activation juga tidak otomatis mewajibkan paid provider. Sebelum activation, source aktif harus menjalani controlled catch-up dan pembuktian freshness, retry, quarantine, stale protection, serta daily import/promote. Jika executed evidence kemudian menunjukkan bootstrap source tidak dapat memenuhi kebutuhan operasional yang benar-benar ditetapkan, hasil tersebut menjadi future decision trigger—bukan migration decision yang sudah dibuat sebelumnya.

---

## Future continuation direction, not current implementation scope

Jika keputusan memakai sumber berbayar sudah dibenarkan, arah kelanjutannya adalah:
1. menambahkan provider adapter baru tanpa mengubah canonical domain contract;
2. menjalankan bounded parallel validation terhadap publication Yahoo dan candidate provider;
3. merekonsiliasi sample dan anomaly tanpa merge-field-per-field atau majority voting;
4. menetapkan provider priority melalui perubahan config/contract yang dapat diaudit;
5. mempublikasikan perubahan active provider priority melalui lifecycle yang berversi;
6. mempertahankan historical lineage dan tidak menulis ulang publication lama secara diam-diam.

Arah ini memastikan pekerjaan fase sekarang tetap dapat digunakan kemudian. Arah ini tidak memberi otorisasi atau kewajiban untuk memulai migrasi sekarang.

Pergantian provider hanya mengubah acquisition implementation dan publication lineage. Ia tidak boleh mengubah arti canonical fields, price products, indicators, eligibility facts, atau consumer contract secara diam-diam.

---

## Source capability matrix (LOCKED)

Strategi ini sebelumnya hanya menyatakan apa yang **boleh** dilakukan dengan bootstrap source. Ia tidak pernah menyatakan apa yang source tersebut **tidak sediakan**. Akibatnya kontrak hilir dapat menulis spesifikasi untuk data yang tidak akan pernah tiba.

Matrix ini menyatakan fakta kapabilitas provider. Ia **tidak** membuat aturan pemakaian — konsekuensi setiap kapabilitas tetap milik owner contract yang ditunjuk pada kolom terakhir. Menyalin aturan owner ke sini dilarang, karena akan menciptakan pemilik kedua yang dapat berbeda saat revisi.

### Binding revision

Matrix ini berlaku untuk **satu kombinasi**: phase bootstrap sekarang, endpoint chart yang aktif, revisi adapter `PublicApiEodBarsAdapter`, dan mapping revision yang berlaku. Ia **tidak permanen**. Kapabilitas baru dapat dibuktikan kapan saja; hingga dibuktikan dan matrix direvisi, perubahan schema provider tidak boleh mengubah semantics hilir secara diam-diam.

### Capability rows

| Capability | State | Bukti penentuan | Owner konsekuensi |
|---|---|---|---|
| EOD OHLCV Regular-Market | `SUPPORTED` | dipetakan dari `indicators.quote.0` pada `PublicApiEodBarsAdapter.php:978`–`:982` | `Canonicalization_Contract_EOD_Bars.md` — tetap melalui validasi penuh |
| Actual Regular-Market traded value | `UNSUPPORTED` | tidak ada field yang dipetakan dari payload aktif | `../registry/Volume_and_Turnover_Normalization_LOCKED.md` — actual `NULL`, proxy tetap bernama proxy |
| Trade count / frequency | `UNSUPPORTED` | tidak ada field yang dipetakan dari payload aktif | `../registry/Volume_and_Turnover_Normalization_LOCKED.md` — nullable dan terpisah |
| Provider `adj_close` | `DIAGNOSTIC_METADATA_ONLY` | dipetakan dari `indicators.adjclose.0.adjclose` pada `PublicApiEodBarsAdapter.php:983` | `Source_Mapping_Contract_LOCKED.md` dan `../registry/Price_Adjustment_Contract_LOCKED.md` |
| Board / market segment | `UNSUPPORTED_BY_BAR_SOURCE` | tidak ada field yang dipetakan dari payload aktif | `Trading_Status_Source_Contract_LOCKED.md` — perlu governed reference source |
| Suspension / UMA / trading status | `UNSUPPORTED_BY_BAR_SOURCE` | tidak ada field yang dipetakan dari payload aktif | `Trading_Status_Source_Contract_LOCKED.md` — perlu governed status source |
| Corporate-action truth, tipe, atau rasio | `UNSUPPORTED_AS_AUTHORITY` | tidak ada field yang dipetakan dari payload aktif | `../registry/Price_Adjustment_Contract_LOCKED.md` — factor hanya dari verified event evidence |
| Exchange price band, minimum price, tick ladder | `UNSUPPORTED_BY_BAR_SOURCE` | tidak ada field yang dipetakan dari payload aktif | `../registry/Exchange_Market_Structure_Facts_LOCKED.md` |

### Batas kekuatan bukti (LOCKED)

Kolom bukti di atas membuktikan bahwa **adapter aktif tidak mengambil** field tersebut. Ia tidak membuktikan bahwa provider tidak pernah menyediakannya di endpoint lain.

Konsekuensinya dua arah dan keduanya mengikat:

- kontrak hilir tidak boleh menuliskan requirement seolah data itu akan tiba pada fase ini;
- klaim bahwa provider secara mutlak tidak memiliki kapabilitas tersebut memerlukan hasil probe endpoint yang disimpan sebagai evidence, bukan pembacaan mapping.

### Known adapter defect

`PublicApiEodBarsAdapter.php:983` memakai `close` sebagai fallback ketika `adjclose` absen. `Source_Mapping_Contract_LOCKED.md` menyatakan provider `adj_close` "has no close fallback semantics". Nilai `adj_close` yang tersimpan karena itu belum tentu benar-benar `adj_close` provider.

Ini defect implementasi, bukan kekurangan dokumen. Ia dicatat di sini agar matrix tidak dibaca seolah kolom `adj_close` sudah berperilaku sesuai kontrak, dan harus ditutup pada work order yang memiliki source mapping.

---

## Strategy acceptance criteria (LOCKED)

Strategi ini dianggap dipatuhi hanya jika seluruh kondisi berikut benar:

- Yahoo dinyatakan sebagai bootstrap source yang diterima secara sadar, bukan official IDX source atau provider final
- quality bar tidak diturunkan karena source gratis
- provider-specific behavior berhenti di adapter/import strategy
- canonical publication tetap governed domain product, bukan salinan provider yang dianggap authoritative
- paid-provider selection, procurement, integration, dan migration tidak dicatat sebagai current remediation backlog
- future trigger hanya membuka evaluasi baru dan tidak menetapkan hasil evaluasi sebelumnya
- source transition apa pun kelak mempertahankan versioned publication lineage dan tidak menulis ulang history
- paparan kelangsungan source dinyatakan terbuka, termasuk ketiadaan SLA dan batas `manual_file` sebagai rescue satu tanggal
- kelengkapan backfill terhadap intentional dataset start diperlakukan sebagai mitigasi risiko kehilangan permanen, bukan sebagai kelengkapan opsional
- trigger availability memiliki ambang terukur pada kontrak operasi, bukan penilaian bebas
- dasar lisensi dinyatakan beserta penggunaan aktual, terms bertanggal, batas yang diketahui, dan peristiwa yang mengubahnya

---

## Interpretation boundary

Interpretasi final:
- **sekarang:** `api_free/yahoo_finance` adalah bootstrap primary EOD source yang sah untuk pembuktian manfaat Weekly Swing;
- **recovery:** `manual_file` adalah controlled recovery, bukan dual-feed atau consensus source;
- **arsitektur:** domain dan canonical contracts tetap provider-neutral;
- **quality:** sumber gratis tidak pernah menjadi alasan untuk menurunkan validation, coverage, provenance, correction, atau readability safety;
- **nanti:** sumber berbayar dievaluasi ketika manfaat atau kebutuhan nyata membenarkannya;
- **bukan scope sekarang:** procurement, pemilihan, integrasi, atau migrasi provider berbayar;
- **bukan backlog sekarang:** tidak ada vendor project yang dianggap tertunda hanya karena future triggers telah didokumentasikan;
- **licensing boundary:** keputusan bootstrap ini tidak memberikan hak komersialisasi atau redistribusi data di luar terms provider.
