# Legacy Semantic Extract — LX-MD-0034-DEC-01

- Source ID: `LS-MD-0034`
- Original path: `audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md`
- Original SHA1: `53F8C0BDBA19E2AB4F630A31731FDB2210E19CEF`
- Extract role: `DECISION`
- Source range: `L80-L96`
- Extract body SHA1: `E63B5A342B7614B8983B2226184354BB7B95F16D`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## `MD-REAUDIT W18` keenam — 2026-08-11, verdict `BLOCKED`

Pass ini memeriksa bagian required outcome yang belum pernah diaudit: **anti-survivorship**. Hasilnya positif dan perlu dicatat sebagai bukti, bukan hanya sebagai ketiadaan temuan.

`TemporalIdentityRepository::baseIdentityQuery` memasukkan listing selama `listed_date <= tanggal` dan `delisted_date IS NULL OR > tanggal`, sehingga instrumen yang delisting belakangan tetap ada pada tanggal lampau. Mekanismenya **terpakai pada data nyata**: 33 listing pernah delisting, 15 di antaranya di dalam rentang dataset.

Pemeriksaan lanjutan menemukan instrumen-instrumen itu kehilangan bar jauh sebelum tanggal delisting — sembilan di antaranya berhenti pada tanggal yang sama, 2024-05-14 — dan suspensi **tidak** menjelaskannya: FREN baru tercatat disuspensi April 2025, HDTX Juli 2025, MFIN September 2024, sedangkan KRAH/KPAS/MYRX/JKSW/TURI/RMBA tidak punya catatan status sama sekali. Sebelum menyebutnya survivorship tersembunyi, alternatifnya diuji: ternyata coverage **menghitung** mereka sebagai expected-and-missing dan evidence **menyebut namanya** — `["HDTX","MYRX","JKSW","MFIN","FREN","KRAH","KP…]` pada 2024-08-01 dan 2025-01-02 — dan **nol dari 844 run current** melaporkan `coverage_missing_count > 0` dengan sampel kosong. Kekurangan datanya nyata, tetapi terlihat dan berteratribusi, bukan senyap.

Sisa yang tidak dibuka sebagai temuan W18: alasan hilangnya bar tersebut tidak tercatat sebagai suspensi. Itu kondisi data pada lingkup coverage/eligibility, bukan replay, dan membuka finding W18 untuknya adalah perluasan scope. Dicatat di sini sebagai dampak lintas-kontrak.

**Kenapa `BLOCKED`, bukan `PARTIAL` lagi.** Sepuluh temuan W18 diremediasi dan diaudit ulang hari ini (`F-025`, `F-028`, `F-029`, `F-031`, `F-032`, `F-034`, `F-035`, `F-036`, `F-037`, ditambah `F-033` yang lahir dari salah satunya). Tidak ada lagi alternatif in-scope yang aman dan belum dicoba. Dua yang tersisa menuntut otoritas atau data pemilik:

- **`F-033`** — seal terblokir sampai diputuskan antara memproduksi observation manifest (akuisisi ulang sumber, lihat `P1-29`) atau membatasi klaim seal secara tertulis. Melonggarkan gerbangnya bukan alternatif yang sah: itu mengembalikannya ke keadaan tidur yang membuat 64.939 publikasi lolos tanpa diperiksa.
- **`F-030`** — menuntut fixture yang ekspektasinya disusun terpisah dari run yang diuji. Seluruh 20.635 perbandingan yang ada self-generated, dan menyusun fixture dari run yang sama akan melanggar aturan yang baru saja ditegakkan.

Unblock requirement karena itu konkret: satu keputusan pemilik untuk `F-033`, dan satu fixture ber-ekspektasi independen untuk `F-030`. Iterasi audit berikutnya tidak akan menggerakkan keduanya.


<!-- LEGACY_EXTRACT_BODY_END -->
