# Legacy Semantic Extract — LX-MD-0034-CTX-02

- Source ID: `LS-MD-0034`
- Original path: `audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md`
- Original SHA1: `53F8C0BDBA19E2AB4F630A31731FDB2210E19CEF`
- Extract role: `CONTEXT`
- Source range: `L51-L79`
- Extract body SHA1: `FCAEB7EFD23F09186BF1176D2762031E9A48B9DF`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## W18 remediation `F-028`, `F-029`, `F-030` — 2026-08-11

`MD-REMEDIATE W18 findings F-028,F-029,F-030` setelah `MD-REAUDIT W18` memberi `FAIL`.

**`F-028` — akar event/factor kini punya koordinat as-known.** Migrasi `2026_08_11_000002` menambah `recorded_at` pada `market_data_corporate_actions` **dan** `market_data_trading_status_events`. Tabel kedua ikut karena `EventRiskSourceRepository` membacanya langsung tanpa cutoff, sementara `TemporalTradingStatusRepository` membaca `md_trading_status_revisions` dengan cutoff — memperbaiki separuh akan meninggalkan gate tetap dilanggar.

Backfill dari `created_at` bukan karangan: `created_at` adalah saat baris masuk ke platform, yang persis makna `recorded_at`. Buktinya **0 baris berbeda dari `created_at`** setelah backfill. Yang sengaja **tidak** diklaim: bahwa stempel itu adalah saat bursa mengumumkan aksinya. Itu saat platform mengetahuinya — koordinat yang tepat untuk replay as-known, tetapi bukan bukti waktu pengumuman.

Empat metode resolusi kini menerima `$knownAt`, difilter lewat satu helper. Baris ber-`recorded_at` NULL **dikecualikan** saat cutoff diberikan, bukan dianggap cukup tua — baris tak bertanggal tidak dapat ditempatkan pada garis waktu pengetahuan, dan menganggapnya lama akan membocorkannya ke setiap replay. `upsertCorporateAction` dan `upsertTradingStatusEvent` kini menulis koordinat itu agar baris baru tidak lahir tak terlihat.

Efeknya pada korpus produksi:

| Skenario | Aksi terlihat |
|---|---:|
| tanpa cutoff (perilaku lama) | 530 |
| cutoff 2024-01-01 | **0** |
| cutoff 2026-06-30 | 428 |
| cutoff 2026-08-11 | 530 |

**`F-029` — guard as-known mendaftar seluruh akar, dan akar baru gagal secara default.** `temporalRoots()` kini memuat **13 metode** di 6 repository, termasuk `SectorClassificationRepository` yang menjadi temporal pada 2026-08-10 dan tidak pernah didaftarkan. Test kedua menyapu seluruh `app/Infrastructure/Persistence/MarketData/` dengan refleksi: setiap metode publik ber-parameter `knownAt` yang tidak terdaftar membuat test gagal. Penambahan akar baru tanpa mendaftarkannya kini pecah, bukan lolos diam.

Dua test perilaku ditambahkan — aksi korporasi dan faktor penyesuaian yang dicatat setelah cutoff harus tak terlihat. Masing-masing membawa asersi pembanding tanpa cutoff, sehingga filter yang menyembunyikan segalanya tidak dapat menyamar sebagai lulus.

**`F-030` — admissibility memeriksa fakta, bukan label.** Aturan lama menolak fixture ber-`fixture_family` persis `runtime_generated_valid_case`, yang dapat dilewati hanya dengan menamainya lain. Kini `fixture_source` juga diperiksa: bila ia menyebut run yang sedang diverifikasi, fixture ditolak apa pun labelnya. Guard-nya memuat kasus ketiga yang sama pentingnya — fixture dari run **berbeda** tetap admissible, sehingga aturan ini tidak dapat dipenuhi dengan menolak segalanya.

Yang **tidak** ditutup oleh remediasi ini: `F-030` menghapus lubang pada aturannya, tetapi **fixture ber-ekspektasi independen tetap belum ada**. Gate exact-replay masih belum dapat disertifikasi, dan `F-024` masih menunggu hal yang sama.

**Koreksi dari `MD-REAUDIT W18` kedua, hari yang sama.** Remediasi di atas **tidak menutup future leakage pada jalur produksi**. Kolom, cutoff, dan guard-nya memang ada dan terbukti pada tingkat repository; yang tidak dikerjakan adalah menyambungkannya. `EodIndicatorsComputeService.php:87` sudah memegang `$knownAt` dan meneruskannya ke akar sektor pada baris 89, sementara ketiga pemanggil akar event/factor pada baris 91, 216, dan 260 tetap memanggil tanpa cutoff — dengan variabelnya berada dua baris di atas. Dicatat sebagai `F-031`; penutupan `F-028` karena itu berlaku untuk kapabilitasnya, bukan untuk hilangnya kebocoran. Audit kedua juga menemukan `md_config_snapshots` kosong dengan seluruh run dan publikasi ber-`config_snapshot_id` NULL (`F-032`), yang membuat klausa config pada exit gate tidak terpenuhi sekaligus memblokir `F-030` di belakangnya.


<!-- LEGACY_EXTRACT_BODY_END -->
