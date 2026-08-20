# Legacy Semantic Extract — LX-MD-0034-FND-01

- Source ID: `LS-MD-0034`
- Original path: `audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md`
- Original SHA1: `53F8C0BDBA19E2AB4F630A31731FDB2210E19CEF`
- Extract role: `FINDING`
- Source range: `L840-L873`
- Extract body SHA1: `2E5BD67242E3ADB1733713D8DEC263A0291DC4AD`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## W08 record — resilience, manual recovery, dan failure taxonomy stage 5, ditutup 2026-08-06

Exit gate: *outage/partial/empty/wrong-date/schema-change fixtures tidak menghasilkan silent readable publication atau denominator shrink.*

### Yang sudah ada

Taksonomi kegagalan sudah lengkap dan terpakai: 15 reason code sumber di adapter, dan matriks state di `FinalizeDecisionService::enforceStateMatrix()` menolak kombinasi mustahil dengan `LogicException` — `READABLE` menuntut `SUCCESS` **dan** `coverage_gate_status` `PASS`, sedangkan `FAILED`/`HELD` dipaksa `NOT_READABLE`. Retry, backoff, dan throttle terkonfigurasi dan terpakai.

### `F-005b` — circuit breaker terkonfigurasi tanpa implementasi

`market_data.provider.circuit_breaker_error_rate` bernilai `0.5` sejak awal, dan bagian **Source access self-protection (LOCKED)** pada `EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md` mewajibkannya. Pencarian di `app/` menghasilkan **nol** kemunculan: konfigurasi itu tidak pernah dibaca siapa pun.

Yang dilindunginya bukan run, melainkan akses. Aturan retry sudah melindungi run; tidak ada yang melindungi sumber gratis non-resmi dari volume retry platform sendiri. Universe sebesar ~950 menerbitkan ratusan request per tanggal sebelum retry apa pun, sehingga berjalan terus menembus kegagalan menyeluruh justru melipatgandakan beban tepat ketika sumber sedang menolak.

Diremediasi di `PublicApiEodBarsAdapter`: breaker membuka pada rasio kegagalan melewati ambang, dengan lantai sampel `max(5, 5% universe)` agar satu galat transien di awal run tidak menghentikan tanggal yang sebenarnya sehat. Penghentian itu **wajib terlihat** — breaker menulis `RUN_SOURCE_CIRCUIT_BREAKER_OPEN` beserta ambang, jumlah percobaan, dan jumlah baris diterima ke telemetry, karena berhenti diam-diam akan tampak persis seperti universe yang memang kecil.

### Bukti exit gate

`tests/Unit/MarketData/SourceFailureResilienceTest.php` menjalankan kelima fixture kegagalan melalui `FinalizeDecisionService` dan menuntut tiga hal dari masing-masing: tidak pernah `READABLE`, tidak pernah tanpa reason code, dan tidak pernah menggeser denominator. Kontrol positif disertakan — tanpa itu seluruh assertion di atas akan lulus pada service yang menolak tanpa syarat, yang tidak membuktikan apa pun tentang penanganan kegagalan.

Denominator adalah besaran yang menanggung beban di sini. Coverage adalah available/expected, sehingga kegagalan provider yang **juga** mengecilkan expected akan menaikkan rasio persis ketika lebih sedikit instrumen berhasil diambil: gate akan terbaca paling sehat pada saat terburuknya.

Bukti produksi, 68.411 run:

| Probe | Hasil |
|---|---|
| run `FAILED`/`HELD` dengan `publishability_state = READABLE` | `0` |
| run `FAILED`/`HELD` tanpa `final_reason_code` | `0` |
| run `READABLE` dengan `coverage_available_count = 0` | `0` |
| tanggal dengan denominator provider-failure lebih kecil dari denominator sukses | `0` |
| rentang denominator, run gagal vs run sukses | `807–951` vs `807–951` |

Perbandingan pertama yang saya jalankan membandingkan `MIN(gagal)` dengan `MAX(sukses)` dan menghasilkan 750 tanggal "menyusut". Itu artefak kueri: mengambil agregat berlawanan dari dua kelompok memproduksi arah yang dicari. Setelah dikontrol terhadap hari eksekusi dan dibandingkan setara, arahnya berbalik — 104 tanggal justru berdenominator **lebih besar** saat gagal (arah yang aman, karena memperketat gate) melawan 1 tanggal lebih kecil, dan tanggal tunggal itu ber-reason `RUN_LOCK_CONFLICT`, sebuah hasil konkurensi internal, bukan kegagalan provider. Dicatat terpisah sebagai `F-006`.


<!-- LEGACY_EXTRACT_BODY_END -->
