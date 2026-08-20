# Legacy Semantic Extract — LX-MD-0044-FND-01

- Source ID: `LS-MD-0044`
- Original path: `audit/reports/AUDIT_FINAL_STATE.md`
- Original SHA1: `4F88A2D91FB7215D7D64C2F3ACFED4764964CB20`
- Extract role: `FINDING`
- Source range: `L415-L478`
- Extract body SHA1: `85997BAB2E255D54AE36E3C00FB6E1E4FB501C98`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Prioritized findings

### P0 — must be corrected before decision-grade use

| ID | Finding | Evidence | Risk | Required correction |
|---|---|---|---|---|
| P0-01 | Historical price-scale repair mengubah data in-place | `app/Application/MarketData/Services/PriceScaleStretchRepairService.php` melakukan update terhadap `eod_bars` dan `eod_bars_history` | Sealed history, hashes, replay, dan prior watchlist dapat berubah tanpa publication revision | Hilangkan apply path langsung; detector hanya membuat anomaly; correction harus membuat publication baru — **`CLOSED` pada remediasi W21, 2026-08-06.** Nol pembaruan in-place `eod_bars` tersisa di seluruh `app/`; `PriceScaleStretchRepairService` dan `CorporateActionDerivationService` keduanya permukaan non-mutating ber-`capability_state = DETECTION_ONLY`; dan sejak W10 enam trigger database menolak mutasi snapshot yang sudah tersegel. **Residu terekam:** 18 dari 32 baris `market_data_price_scale_breaks` berstatus `REPAIRED` dengan `repaired_at` pada `2026-07-30 01:34`, yaitu perbaikan in-place yang benar-benar terjadi sebelum penjaga ini ada. Kolom repair-nya sengaja **tidak dihapus** — ia catatan bahwa operasi terlarang itu pernah dijalankan, dan menghapusnya akan memusnahkan bukti alih-alih memperbaikinya. Lihat `P1-14` |
| P0-02 | Historical universe memakai current active state | `TickerMasterRepository.php` memfilter `is_active=1` sebelum as-of listed/delisted dates | Survivorship bias dan replay universe salah | Universe resolver harus sepenuhnya as-of-date dan diuji dengan inactive-now-but-active-then fixture **Ditutup pada W05, 2026-08-03, dengan bukti terhadap data produksi.** Proyeksi temporal dijalankan atas 977 ticker menghasilkan 977 baris pada `md_issuers`, `md_instruments`, `md_listings`, dan `md_listing_symbols`. Uji survivorship memakai emiten nyata: `legacy_ticker_id 939`, listing 1995-05-16 dan delisting 2023-04-06, kini `is_active = 0`. Ia **muncul** pada `universeAsOf('2023-03-01')` yang berisi 846 listing dan **tidak muncul** pada `universeAsOf('2026-07-28')` yang berisi 962. Resolver tidak menyentuh `is_active` sama sekali. |
| P0-03 | Synthetic corporate action dan break linkage tidak fail-safe | Derivation dapat membuat action dari price anomaly tetapi `matched_corporate_action_id` tidak selalu terpasang; quarantine dapat tetap salah | False adjustment atau affected rows lolos tanpa factor yang benar | Price break hanya candidate; verified/manual/authoritative action wajib sebelum factor dipakai; linkage atomik — **`CLOSED` pada remediasi W21, 2026-08-06.** Dampaknya dinetralkan pada W11: faktor ber-`adjustment_source = DERIVED_FROM_PRICE_SERIES` tidak dapat mencapai jalur adjustment **maupun** menekan flag kontaminasi, terbukti pada produksi dengan 15 faktor terpakai menjadi 0. Penulisnya juga sudah pensiun menjadi permukaan non-mutating. **Residu terekam:** 28 dari 32 break tidak memiliki `matched_corporate_action_id`, tetapi linkage tidak lagi menggerbangi apa pun karena faktor turunan ditolak sepenuhnya, bukan disaring lewat linkage. Lihat `P1-31` |
| P0-04 | Analytical price product belum konsisten dengan selected strategy | Owner strategy memilih satu versioned `STRUCTURAL_ADJUSTED` product per run dan melarang provider `adj_close` sebagai basis. | Produk campuran atau identity yang tidak terikat membuat indikator/replay tidak koheren. | **`CLOSED 2026-08-14 FOR ADMITTED CORPUS`.** Tahap 8 menghasilkan 15/15 publication dengan `price_product_code=STRUCTURAL_ADJUSTED`, version `structural_adjusted_v1`, dan factor-set hash publication/run/artifact yang konsisten; tiap publication memiliki factor-set hash tersendiri dan oracle identity/lineage nol. Bar canonical tetap exact `RAW`. Replay proof independen tetap `F-024`/Tahap 10 dan tidak dihitung sebagai penutup P0-04. |

### P1 — required for strong and stable Weekly Swing data

| ID | Finding | Risk | Required correction |
|---|---|---|---|
| P1-01 | Raw provider observations belum menjadi immutable first-class layer | Correction dan source investigation sulit direproduksi | Simpan bounded raw observation envelope atau immutable payload reference/hash sebelum canonicalization |
| P1-02 | Config provenance tidak lengkap | `config_version` hanya mewakili indicator set dan `config_hash/config_snapshot_ref` masih kosong | Replay menghasilkan output berbeda tanpa jejak | Snapshot seluruh output-affecting config dan isi hash/reference pada run/publication |
| P1-03 | Dormancy bercampur dengan coverage | Missing provider bars dapat tersembunyi sebagai dormant exclusion | Simpan dormancy/zero-volume sebagai fakta activity/liquidity tanpa menjadikannya data-usability atau coverage exclusion; denominator hanya memakai verified bar expectation |
| P1-04 | ATR direseed dari loaded sliding window | Nilai ATR dapat bergeser ketika run date maju | Persist recursive state atau hitung dari stable historical seed; tambahkan long-chain oracle |
| P1-05 | Event-risk memakai `action_date` pada jalur tertentu | Quarantine window dapat salah tanggal | Gunakan `ex_date` sebagai primary effective anchor dengan fallback hierarchy eksplisit |
| P1-06 | `dv20` bukan actual traded value | Liquidity ranking dapat salah, terutama bila price adjusted dikali raw volume | Rename menjadi explicit proxy; ingest actual traded value saat source memungkinkan |
| P1-07 | Canonical EOD fields terlalu tipis | Reconciliation, liquidity, board/status, dan quality diagnosis terbatas | Tambahkan nullable source fields untuk previous, traded value, trade count, board, dan status dengan provenance |
| P1-08 | Corporate-action factor fields dapat dimutasi | Sealed analytical history berubah secara implisit | Gunakan event/factor revisions dan publication binding; jangan mutate factor yang telah dipakai |
| P1-09 | Zero-OHLC policy kontradiktif | Satu contract melarang canonical zero bars, contract lain mengizinkan placeholder | Pilih satu model: recommended missing/invalid observation terpisah, bukan zero canonical price |
| P1-10 | Tests mengunci beberapa semantic lama yang salah | Green suite memberi false confidence | Tambahkan regression untuk point-in-time universe, no-history-rewrite, coherent basis, exact ATR, dan coverage/eligibility separation |
| P1-11 | Promote/freshness operation belum sepenuhnya otomatis | Bukan current development blocker, tetapi akan membuat consumer menerima state stale setelah operational activation | Sebelum activation, catch up development gap, jadwalkan promote/readiness, monitor locks/retries, dan alert latest effective date |
| P1-12 | Testing migration state tidak sepenuhnya sinkron | Test environment dapat tidak merepresentasikan schema runtime | Terapkan pending testing migrations dan tambahkan environment parity gate |

### P2 — future-strength improvements after core correctness

| ID | Improvement | Timing rule |
|---|---|---|
| P2-01 | Full `AS_KNOWN` versus `LATEST_RESTATED` bitemporal product | Setelah minimum point-in-time universe/publication semantics stabil |
| P2-02 | Secondary-source bounded reconciliation | Setelah Yahoo bootstrap menghasilkan manfaat dan anomaly cost dapat diukur |
| P2-03 | Paid/licensed provider adapter | Hanya setelah value, SLA, licensing, correction, field coverage, atau commercial trigger terpenuhi |
| P2-04 | Automated broader corporate-action workflow | Setelah price-continuity actions dan manual verification flow stabil |
| P2-05 | Static analysis and mutation/property testing | Setelah P0 behavior diperbaiki agar automation tidak mengunci behavior salah |
| P2-06 | Disaster rebuild and provider migration rehearsal | Sebelum commercial SLA atau higher-risk automation |

---

## Known bugs and future bug risks

| Risk | Type | Likely symptom | Prevention |
|---|---|---|---|
| In-place scale repair | Existing bug | Historical bars dan prior outputs berubah | Immutable correction publication |
| Current-active historical filter | Existing bug | Delisted/inactive securities hilang dari backtest | Point-in-time universe resolver |
| Missing corporate-action linkage | Existing bug | Break tetap quarantined atau factor tidak ditemukan | Transactional event-break-factor linkage |
| Event window anchored to wrong date | Existing bug | False positive/negative event risk | Explicit ex-date hierarchy |
| Sliding-window ATR reseed | Existing semantic bug | ATR berubah antar rerun/window | Stable recursive seed/state |
| Mixed close/adj-close fallback | High future risk | Indicator spike dan ranking drift | One coherent basis per run |
| Volume rounding during repair | High future risk | Historical liquidity berubah dan tidak reversible | Never rewrite raw bar/volume |
| Dormant exclusion hides provider outage | High future risk | Coverage terlihat tinggi saat data hilang | Separate expectation, delivery, factual activity/liquidity, and data usability |
| Adjusted price multiplied by raw volume | High future risk | Nominal liquidity secara dimensional salah | Actual value or explicitly named proxy |
| Provider schema or rate-limit change | Expected operational risk | Partial/malformed/late acquisition | Adapter isolation, retry budget, schema guard, quarantine |
| Yahoo revision without captured lineage | High future risk | Re-fetch memberi history berbeda | Immutable observation hash/reference and new publication |
| Symbol reuse/change | High future risk | Data melekat ke instrument yang salah | Temporal listing and provider-symbol mapping |
| Late corporate action | High future risk | Signal window memakai false price gap | Event revision, contamination window, republish workflow |
| Config drift | High future risk | Rerun berbeda walau code sama | Full config snapshot/hash |
| Stale calendar/status | High future risk | Wrong requested date atau wrong coverage exclusion | Effective-dated authoritative calendar/status controls |
| Test fixtures mirror flawed rules | Existing process risk | Tests green namun semantic salah | Independent real-market oracles and negative cases |

---


<!-- LEGACY_EXTRACT_BODY_END -->
