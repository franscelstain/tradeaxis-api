# Market Data Audit README

## Purpose
Folder ini adalah pintu masuk audit untuk paket `docs/market_data`. Audit dipakai agar pembahasan paket market-data tetap konsisten saat chat baru dimulai, saat ZIP baru diunggah, atau saat revisi dokumen dilakukan.

## What this audit does
Audit market-data menilai:
- klasifikasi layer paket
- ketepatan domain boundary market-data
- kejelasan source of truth
- kekuatan implementation guidance
- kekuatan evidence runtime nyata
- konsistensi lintas folder utama

## Layers
- **Layer A — System Docs**: source-of-truth docs, contract docs, owner docs, dan DB/schema support docs.
- **Layer B — Implementation Guidance**: module mapping, runtime flow translation, API/persistence/testing guidance, runbook, dan delivery checklist.
- **Layer C — Real Implementation / App Runtime**: bukti nyata aplikasi, runtime payload, schema runtime, executed test, log, atau archived execution evidence yang bisa ditelusuri.

## Expected output of every audit
Setiap audit minimal harus menghasilkan:
1. layer classification
2. scope statement
3. PASS / PARTIAL / FAIL per dimensi
4. findings per file atau per area
5. remediation items
6. final verdict

## Audit-output state rule
Output audit pada paket aktif harus **state-based**, bukan **revision-based**.

Artinya:
- audit aktif harus berdiri pada state dokumen saat ini
- audit aktif tidak boleh bergantung pada penamaan ronde seperti `R1`, `R2`, `R3`, dst.
- audit aktif tidak boleh memakai history audit lama sebagai penopang agar verdict saat ini terlihat utuh
- bila paket sudah bersih, output report aktif harus diringkas menjadi satu final-state report kanonik
- remediation report hanya boleh hidup selama masalahnya masih terbuka

## How to use this folder
Urutan minimum:
1. baca `AUDIT_BASELINE.md`
2. baca `AUDIT_LAYER_CLASSIFICATION_RULES.md`
3. baca `AUDIT_DOMAIN_BOUNDARY.md`
4. baca checkpoint aktif implementasi market-data:
   - `LUMEN_IMPLEMENTATION_STATUS.md`
   - `LUMEN_CONTRACT_TRACKER.md`
5. jika audit menyentuh source live / operational health, baca juga:
   - `../book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`
   - `../../system_audit/CODEBASE_BUILD_AND_AUDIT_GUIDE.md`
6. jalankan checklist yang sesuai
7. baca `audit/reports/AUDIT_FINAL_STATE.md` untuk state audit aktif saat ini
8. pakai templates bila perlu membuat findings, remediation, atau verdict baru

## Relationship to other folders
- `system/` = peta besar sistem tingkat atas
- `book/` = kontrak domain utama
- `db/` = schema / persistence support contract
- `ops/` = operational rules dan guidance
- `tests/` = proof specification dan admission criteria
- `examples/` = illustrative examples
- `evidence/` = archived actual evidence bila isinya memang nyata dan traceable

## Important rule
Audit ini menilai market-data sebagai **producer/data-platform domain**, bukan watchlist strategy, bukan execution engine, dan bukan portfolio behavior.


## Checkpoint rule
Dua file berikut adalah checkpoint aktif utama untuk build dan closure implementasi market-data:
- `LUMEN_IMPLEMENTATION_STATUS.md`
- `LUMEN_CONTRACT_TRACKER.md`

Keduanya wajib sinkron dengan owner docs dan tidak boleh dipakai untuk membuat kontrak paralel.

## Runtime-state separation
Audit market-data wajib memisahkan:
- `contract / implementation state`, dan
- `operational live-source state`.

Test suite hijau tidak otomatis berarti live source sehat harian.
Sebaliknya, gangguan provider eksternal tidak otomatis membatalkan closure contract internal, selama checkpoint menuliskannya dengan jujur.
