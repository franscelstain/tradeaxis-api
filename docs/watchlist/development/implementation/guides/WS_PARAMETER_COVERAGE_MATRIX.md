# WS Parameter Coverage Matrix (Reference)

> **Current Conformance:** `NOT_ASSESSED_REVALIDATION_REQUIRED`  
> **Verification Epoch:** `WS-REBASELINE-20260819-001`  
> Existing content is a revalidation input. Historical PASS/READY/DONE wording does not grant current conformance.


## Reference status
Matriks ini adalah alat bantu audit untuk melihat sebaran parameter Weekly Swing di berbagai dokumen dan titik penggunaan. Matriks ini bukan owner default value, provenance final, atau kewajiban runtime.

## Purpose
Tujuan matriks ini adalah membantu reviewer dan implementer menelusuri apakah parameter yang relevan sudah muncul pada rumah normatif yang benar dan pada area implementasi yang tepat.

## Scope
Cakupan matriks ini terbatas pada pemetaan coverage. Bila ditemukan mismatch, keputusan final harus ditelusuri kembali ke dokumen normatif bernomor, lalu matriks ini diperbarui agar tetap konsisten.

## Inputs
Matriks ini dibaca bersama dokumen normatif bernomor, paramset contract, serta referensi implementasi yang relevan. Pembaca tidak boleh menetapkan aturan baru hanya dari isi matriks.

## Outputs
Keluaran dari matriks ini adalah gambaran coverage parameter dan pointer audit, bukan penetapan contract behavior, default value, atau provenance final.

## How to read this matrix
Gunakan matriks ini untuk menjawab empat pertanyaan:
1. parameter ini hidup di dokumen normatif mana,
2. parameter ini dipakai pada tahap PLAN atau CONFIRM,
3. parameter ini memengaruhi guard, scoring, grouping, atau overlay,
4. apakah ada area yang tampak belum tercakup secara dokumentasi atau implementasi.

## Coverage matrix
| Parameter | Domain stage | Dipakai untuk | Owner normatif utama | Referensi sekunder / implementasi | Catatan audit |
|---|---|---|---|---|---|
| `data_readiness.min_coverage_ratio` | PLAN guard | memastikan cakupan minimum EOD terpenuhi | `WS_RUNTIME_FLOW.md` | `WS_FAILURE_BEHAVIOR_MATRIX.md` | review bila hasil abort coverage sering muncul |
| `no_trade.min_eligible_count` | PLAN guard / dynamic selection | memutuskan `NO_TRADE` global bila kandidat eligible terlalu sedikit | `WS_CANDIDATE_CLASSIFICATION.md` | `WS_FAILURE_BEHAVIOR_MATRIX.md` | cek konsistensi dengan outcome `NO_TRADE` |
| `risk.min_rr` | PLAN level | memaksa kandidat menjadi `WATCH_ONLY` bila RR rendah | `WS_CANDIDATE_CLASSIFICATION.md` | `WS_WORKED_EXAMPLE_E2E.md` | pastikan tidak dipakai sebagai alasan ranking di tempat yang salah |
| `confirm_overlay.max_drift_from_entry_pct` | CONFIRM overlay | memberi label caution saat drift terlalu jauh | `WS_D1_CONFIRM_ACTIONABILITY.md` | `WS_MANUAL_INPUT_TEMPLATE.md` | cek hubungan dengan `last_price` pada input manual |
| `confirm_overlay.snapshot_max_age_sec` | CONFIRM snapshot validity | menentukan stale snapshot | `WS_D1_CONFIRM_ACTIONABILITY.md` | `WS_GLOSSARY_REFERENCE.md` | pastikan pembaca membedakan TTL dari timestamp storage |
| `grouping.top_picks_target` dan `grouping.top_min_score_q` | PLAN grouping | membentuk target dan cutoff kandidat utama | `WS_CANDIDATE_CLASSIFICATION.md` | `WS_WORKED_EXAMPLE_E2E.md` | cek konsistensi dengan hasil grouping |
| `grouping.secondary_target` dan `grouping.secondary_min_score_q` | PLAN grouping | membentuk target dan cutoff kandidat pendukung | `WS_CANDIDATE_CLASSIFICATION.md` | `WS_WORKED_EXAMPLE_E2E.md` | cek outcome saat pool sekunder kosong |

## Reading patterns
### Saat ingin audit PLAN
Lihat parameter guard, ranking, dan grouping terlebih dahulu. Setelah itu cocokkan dengan worked example atau failure matrix bila perilaku hasil sulit dipahami.

### Saat ingin audit CONFIRM
Fokus pada parameter stale, drift, dan kelengkapan input intraday. Cross-check dengan manual input template agar pembacaan field tidak keliru.

### Saat menemukan mismatch
Jangan putuskan dari matriks ini saja. Buka owner normatif utama, lalu perbarui matriks bila mismatch memang nyata dan bukan sekadar pembacaan yang salah.
