# MD-B18-A002 — Paket remediasi untuk review

**Status: DRAFT — AWAITING USER REVIEW / MD-DEP-0017.** Paket ini merupakan usulan
kontrak implementasi dan rencana pembuktian, bukan authority baru atau izin implementasi.

| Identitas | Nilai |
|---|---|
| Stage / attempt | MD-B18 / MD-B18-A002 |
| Baseline / epoch | MD-B18-A002-BL001 / MD-REBASELINE-20260820-001 |
| Freeze | MD-STRATEGY-FREEZE-20260903-001 |
| CI | CI-MD-B18-A002-001, amendment 2026-09-15 08:00 +07:00 sebelum mutasi paket |
| Evidence paket | E-MD-B18-A002-014, inspeksi dan rekonsiliasi; bukan runtime PASS |
| Findings | F-MD-B18-A002-013, -014, -015, -016, -017, -018 |
| Resume saat masuk | Susun paket konsolidasi, lalu review user sebelum perubahan aplikasi/test |
| Return-to | MD-B19-A001 hanya setelah closure B18 yang valid dan MD-DEP-0009 resolved |

## 1. Keputusan yang diminta

Setiap pilihan masih **PENDING**. Tidak ada D baru sampai user memilih. Persetujuan paket tidak
mencakup recovery MD-DEP-0016, perubahan strategy, production relock, atau kebijakan watchlist.

| Pilihan | Rekomendasi | Alternatif dan dampak |
|---|---|---|
| Q1 — scope dan kontrak identitas | B18 memiliki remediasi replay yang terbatas; perluas binding lineage publication yang sudah ada dengan bundle input versi baru, lengkap dan immutable. Revalidasi konsumen B04/B10/B11/B17 yang terkena, dengan hubungan impact tercatat. | Dependency-driven re-entry pada owner publication sebelum B18 diteruskan. Memerlukan baseline/CI dan perubahan orkestrasi resmi; jangan pindah stage secara implisit. |
| Q2 — publication lama tanpa bundle lengkap | PUBLICATION_EXACT mengembalikan BLOCKED dengan daftar input hilang. Pertahankan publication lama dan pointer; jangan memperkaya seal lama dari keadaan hari ini. Jalur admission bukti juga menolak klaim exact yang tidak lengkap. | Rekonstruksi hanya jika seluruh input asli dapat dibuktikan dari artefak immutable yang terikat pada publication tersebut; perlu paket evidence/keputusan tersendiri. Rekonstruksi AS_KNOWN bukan pengganti bukti exact. |
| Q3 — replay backfill | Wajibkan manifest input eksplisit: setiap trade_date mengikat publication_id dan hash fixture; mode PUBLICATION_EXACT diberikan eksplisit. Validasi semua entri sebelum eksekusi. | Pisahkan operasi current-read verification dengan kontrak/pointer assertion dan nama klaim berbeda. Scope lebih luas; tidak boleh tetap mengaku exact replay. |
| Q4 — byte deterministik | Bandingkan byte payload domain yang diserialisasi menurut S005, serta semua hash output. Simpan envelope audit terpisah. Volatil hanya surrogate execution IDs (run_id, replay_id, auto-increment publication ID) dan execution created_at/updated_at/generated_at yang tidak menentukan interpretasi. | Menuntut byte seluruh envelope termasuk identitas eksekusi berarti perlu format artifact baru atau identity reuse. Jangan menghapus timestamp secara umum hanya agar test hijau. |
| Q5 — pemilik MD-S020-R0014 | B22 primary, B18 dan B17 supporting: admission dokumentasi, implementasi, dan operational readiness membutuhkan bukti gabungan. Tetap MANDATORY/NOT_ASSESSED. | Tetap B18 primary dengan guard admission lintas seluruh permukaan klaim dan bukti B17/B22 yang lengkap; B18 belum dapat ditutup selama proof readiness itu tidak tersedia. |

Rekomendasi Q5 berasal dari scope parent S020, bukan dari kebutuhan mengurangi denominator.
Jika disetujui, perubahan tepat satu row: B18 115 → 114 dan B22 bertambah satu kewajiban; applicability
tetap MANDATORY, SATISFIED tetap nol untuk row itu. Periksa seluruh section parent, catat justifikasi,
relationship dan invalidasi basis terkait sebelum mengubah matrix. Q1 tidak membuka ulang stage
yang sudah ditutup tanpa orkestrasi. Bila implementasi menemukan kebutuhan strategy berubah,
berhenti di prosedur DOCUMENT_CHANGE_POLICY §2 dan minta otorisasi terpisah.

Q4 tidak mengecualikan trade_date, knowledge_cutoff_at, effective/recorded/learned/verified time,
source/acquisition time yang menentukan availability, revision/content identity, alasan, nilai,
atau hubungan predecessor/correction. Daftar field konkret pada serializer harus ditabulasi dan
direview terhadap klausul ini; field baru secara default masuk payload domain, bukan volatil.

## 2. Kondisi saat review

Matrix current: **154 active, 115 required, 0 SATISFIED, 115 NOT_ASSESSED, 4 conditional N/A,
33 reference, 2 optional; 0 transitional/pending**. Proof basis: **65 INCOMPLETE, 50 retained PROVEN**.
Semua 69 pasangan lama telah direview. PROVEN pada basis belum merupakan binding evidence A002.
Lampiran §7 memuat tepat 65 row dengan context/normalized predicate current, perubahan atau
rebind, assertion yang membuktikan predikat itu, dan probe tunggal yang direncanakan.

Evidence E008–E013 memberi basis review; inspeksi paket memverifikasi ulang 12 manifest E009–E013
dan 423 entri artefaknya, semuanya hash-equal. Itu memverifikasi integritas hasil review yang
dirujuk, bukan mengeksekusi ulang seluruh probe. Semua probe baru di paket berstatus **PLANNED**.

## 3. Root cause dan refinement F013

1. `ReplayVerificationService.php:1029-1030` membaca temporal_identity_hash/calendar_status_hash
   pada publication/run yang tidak menyediakan field itu. Tetapi `PublicationGovernanceBindingService`
   sudah menyimpan identity/calendar/status/event revision-set hashes di md_publication_lineage_bindings;
   `EodPublicationRepository::publicationManifestContext` dan `buildManifestByPublicationId` membacanya.
   Karena itu klaim lama “tidak ada frozen identity” terlalu luas. Defek tetap ada: proyeksi replay
   salah, serta isi identity/event/version/dataset binding belum memenuhi seluruh S050/S019.
2. `actualBoundInputContext` menghitung registry/version dari konfigurasi saat replay dan reason hash
   dari daftar state nominal. Hash nonkosong saja tidak membuktikan registry yang dipakai publication.
   E012 mendemonstrasikan temporal/calendar/observation kosong tetap menghasilkan PASS/MATCH/ADMISSIBLE.
3. `B18ReplayRerunDeterminismTest` mengekspor metric mock dua kali. Tidak menjalankan rebuild atau
   ReplayVerificationService; ia membuktikan serializer deterministik, bukan rerun end-to-end.
4. `ReplayBackfillService` memilih current pointer dan memberi passed=true bila expected outcome
   tidak dikenal. Cabang expected ERROR juga harus dipisahkan dari replay PASS.
5. Aggregate lama hanya memeriksa daftar/method existence. Beberapa guard temporal tetap hijau
   ketika semua data di balik cutoff disembunyikan. Comparison test juga dapat hijau karena
   mismatch lain muncul, walau comparison field yang diklaim tidak pernah dijalankan.

Refinement ini dicatat pada F013 yang masih terbuka. E lama tidak diedit, dan tidak ada basis yang
dinaikkan. Draf F013 yang menyarankan kolom paralel di eod_publications dan backfill AS_KNOWN
digantikan untuk keperluan review oleh Q1/Q2 di paket ini, menunggu keputusan user.

## 4. Kontrak implementasi yang diusulkan

### C1 — bundle input exact dan seal

Gunakan md_publication_lineage_bindings sebagai titik binding yang sudah ada. Simpan konteks
canonical versi baru beserta hash dan referensi input immutable yang dapat diverifikasi; nama
kolom/migration mengikuti schema yang disetujui, bukan membuat sumber authority paralel.
Konteks mencakup setiap anggota S050 §Bound input identity dan antecedent S019 Invariant 14:

- mode, fixture hash, trade_date; universe/dataset boundary; issuer/instrument/listing/symbol dan
  provider mapping revisions; calendar/session/status facts beserta effective dan knowledge identity;
- observation IDs/content hashes serta adapter/schema/normalization versions; canonical RAW set;
- event revisions/terms, learned/verified states, factor set, source-scale dan contamination decisions;
- config snapshot ID/content hash; formula/indicator/reason registry content atau immutable revision;
  price-product, coverage, eligibility, read-model, serialization/hash dan build identities;
- expected publication/version/pointer/seal, ketiga output batch hashes, serta correction lineage.

Expected-state assertions tetap berada pada fixture/manifest pembanding, terpisah dari input
producer; jangan meng-hash publication_manifest_hash ke dalam dirinya sendiri. Susun hash input
dan output lebih dahulu, kemudian manifest dengan aturan S005. Versi baru tidak mengubah hash
publication lama. Jangan mengisi version dari default konfigurasi jika frozen value tidak tersedia.

Catat **input yang benar-benar dikonsumsi run**, bukan resolusi ulang saat seal. Seal memvalidasi
bundle lengkap, koheren dan tersedia sebelum publication menjadi readable/current, dalam transaksi
yang sama dengan lifecycle yang diatur owner. Validasi gagal meninggalkan predecessor/pointer utuh.
Set kosong sah hanya dengan domain tag, alasan dan bukti populasi nol yang benar; field hilang
tidak boleh diganti hash konstanta/empty-set. Hash valid harus cocok dengan konten/revisi aslinya.

Semua jalur exact (service, direct repository persistence, replay/export/admission/backfill) memakai
identitas frozen yang sama. Non-BLOCKED ditolak jika bundle wajib hilang atau tidak dapat dibuktikan.
AS_KNOWN tetap berbeda: resolusi efektif dan known-at di setiap root; perbaiki registry identity
nominal pada kedua mode tanpa menjadikan exact bergantung pada resolver current/as-known.

Audit writer, updater, deleter dan inverse operation: binding/upsert, seal, correction/supersession,
pointer transition, manifest read, replay persistence, export/admission, registry edit. Lindungi
immutability di aplikasi dan schema yang diklaim. Guard scan mencakup **base SQL dan seluruh
migrations**, dengan population assertion dan satu instance rusak per probe. Migrasi dijalankan pada
tradeaxis dan tradeaxis_testing. D001 deferral SQLite ke B21 tidak boleh diam-diam diperluas atau dicabut.

### C2 — status, reason, backfill

| Keadaan | Hasil kontrak |
|---|---|
| Input/proof wajib hilang, fixture section tidak lengkap, identitas exact tidak tersedia | BLOCKED / NOT_ADMISSIBLE; missing field paths; tidak pernah PASS |
| Semua input tersedia dan hasil eksekusi berbeda dari expected | FAIL / MISMATCH; named field + expected + actual + reason |
| Semua input tersedia dan hasil memenuhi expected | PASS hanya pada kontrak replay yang sah |
| Fixture negatif memang mengharapkan ERROR/BLOCKED | Catat fixture expectation matched terpisah; status replay tetap ERROR/FAIL/BLOCKED yang sebenarnya |
| Case/fixture tidak dikenal, duplicate trade_date, hash fixture salah, publication/date tidak cocok | Tolak sebelum bekerja; tidak memilih pointer atau menebak expected outcome |

Manifest Q3 mencakup seluruh rentang tanggal, urutan stabil dan mapping satu tanggal ke satu konteks
exact. Retain hash manifest pada evidence. CLI/service harus meminta mode eksplisit; default atau
pointer lookup tidak boleh menyamarkan input yang hilang. Audit pula FullRangeCurrentEvidenceReplayService
dan BackfillLifecycleOrchestrator. Temuan B19 tentang SKIPPED→SUCCESS tetap return-to obligation,
tidak dieksekusi diam-diam di B18.

coverage_gate_reason_code berasal dari CoverageGateEvaluator dan dipersist tanpa sintesis ulang
di EodRunRepository/finalization; ReplayVerificationService, ReplayResultRepository, export run/replay,
backfill summary dan CLI memakai reason itu. Telusuri FinalizeDecisionService, MarketDataPipelineService,
MarketDataBackfillService, MarketDataEvidenceExportService dan AbstractMarketDataCommand. Nilai reason
historis yang tidak diketahui harus tampil sebagai kekurangan bukti, bukan alasan tebakan dari state.
Test memakai dua reason berbeda dengan state yang sama agar fallback sintetis tidak lolos.

### C3 — rerun dan proof execution

Rebuild pipeline dua kali dengan input identik melalui production repositories pada schema migrated,
serta replay dua kali untuk publication+fixture tetap. Periksa byte payload domain, ketiga batch hashes,
publication manifest/lineage, jumlah publication dan correction sebelum/sesudah. Perubahan satu input
harus mengubah output/identity yang tepat. Export-only guards tetap berguna untuk pass-through,
tetapi tidak menggantikan eksekusi ini.

Aggregate mengambil semua anggota dari **seluruh section parent**, lalu menjalankan body test melalui
mekanisme PHPUnit yang menangkap assertion, exception, failure, skipped/incomplete dan lifecycle.
Setiap anggota mempunyai >0 assertion; missing, duplicate, skipped, no-assert, exception atau failure
membuat aggregate merah. Gunakan pola sembilan anggota R0056 yang telah disetujui D003; jangan hanya
memanggil method tanpa setup/teardown atau menghitung exit suite global sebagai bukti anggota.

## 5. Urutan pekerjaan setelah keputusan dicatat

1. Terbitkan D dan relationship keputusan Q1–Q5; CI tetap current sebelum scope baru. Bila Q1 memilih
   re-entry, orkestrasi dependency dan baseline baru dilakukan lebih dahulu. Jika scope berbeda dari
   rekomendasi, revisi paket terbuka secara traceable sebelum aplikasi/test dimutasi.
2. Finalkan kontrak/schema C1–C3 sesuai pilihan, termasuk seluruh read/write/inverse surface; review
   context S019 Invariant 1/14 dan seluruh parent terkait. Koreksi context hanya bila perlu mengembalikan
   makna authority; tabulasi semua transisi, invalidate basis terdampak. Jangan mengubah applicability
   untuk membuat PASS. Q5 hanya diterapkan setelah keputusan ownership eksplisit.
3. C1 capture→binding→seal→reader→admission; C2 status/reason→backfill; kemudian C3 rebuild/rerun.
   Unit perbaikan saling bergantung dalam attempt yang sama, bukan attempt baru per test gagal.
4. Jalankan guard dan probe §7; perbaiki guard-only/rebind; jalankan aggregate setelah anggota hijau.
   Perbarui basis per predicate hanya setelah assertion yang tepat dan landed probe terbukti.
5. Revalidasi 8 retained PAIR01 dan 42 retained lainnya (§8), serta 4 conditional N/A E008 bila
   checked-source hash berubah. D001/D002/D003/D004 tetap berlaku. Selesaikan residue/relationship.
6. Validation order: targeted PHPUnit → normalization / proof pre-binding / closure / self-test →
   governance gates/self-test → full PHPUnit. Untuk penutupan, full suite harus hijau tanpa skip
   sebelum dan sesudah binding. MD-DEP-0015 tetap blocker sampai criterion D004 terpenuhi.
7. Setelah proof lengkap: E-MD-B18-A002-001 + A002 MANIFEST, binding yang atomic dan row delta tepat,
   gate bound + closure dan mutation proof, SC terdaftar, resolve MD-DEP-0009; perbarui register dan
   generate CURRENT_STATE; baru return-to B19. Paket ini tidak menjalankan langkah 1–7 tersebut.

## 6. Protokol probe dan command

Untuk setiap row §7, target perubahan disebut spesifik. Sebelum probe: guard control hijau dan
populasi input/member >0 serta tepat jumlah yang diturunkan dari parent. Simpan file sebagai **byte**;
periksa EOL; anchor count harus **1**. Setelah replacement, buktikan satu diff tepat pada target dan
hash berubah. Jalankan guard, pastikan assertion/pesan yang relevan merah; kegagalan lain tidak cukup.
Pulihkan byte dari salinan dalam finally; hash harus identik; control sesudah harus hijau. Tidak
menggunakan git checkout. Jalankan satu instance/member/field rusak pada satu waktu; jangan gabungkan
beberapa kerusakan sehingga satu comparison menutupi comparison lainnya. Control merah berarti
probe tidak sah dan dependency control diperbaiki dulu.

Command dari repository root (PHP lokal D:/xampp/php/php.exe; tidak ada mode implisit):

```text
php vendor/bin/phpunit --filter 'B18|ReplayBackfillServiceTest|FindingRecordConsistencyTest'
php docs/market_data/development/implementation/tests/MarketDataReplayVerificationNormalization.php
php docs/market_data/development/implementation/tests/MarketDataReplayVerificationProofBinder.php --validate-only
php docs/market_data/development/implementation/tests/MarketDataReplayVerificationProofGate.php --pre-binding
php docs/market_data/development/implementation/tests/MarketDataReplayVerificationProofReadinessGate.php
php docs/market_data/development/implementation/tests/MarketDataReplayVerificationClosureGate.php
php docs/market_data/development/implementation/tests/MarketDataReplayVerificationProofSelfTest.php --pre-binding
php docs/market_data/development/implementation/tests/MarketDataDocumentationIntegrityGate.php
php docs/market_data/development/implementation/tests/MarketDataRelationshipIntegrityGate.php
php docs/market_data/development/implementation/tests/MarketDataRelationshipIntegrityGateSelfTest.php
php docs/market_data/development/implementation/tests/MarketDataClassificationConsistencyGate.php
php docs/market_data/development/implementation/tests/MarketDataTraceabilityApplicabilityGate.php
php vendor/bin/phpunit
php docs/market_data/development/implementation/tests/GenerateMarketDataCurrentState.php
```

Normalization script adalah verifier fixed-mode. ProofReadinessGate fixed pre-binding;
ClosureGate fixed closure. Setelah binding gunakan --bound pada proof gate/self-test; jangan
menebak mode. Kondisi saat draft: proof gate/closure/control self-test memang belum hijau; jangan
menandai probe gate valid dari red baseline. Governance self-test berjalan pada salinan sementara;
pastikan control sebelum/sesudah hijau dan setiap mutation applied. Ulang seluruh current checks
setelah record baru diterbitkan, sesuai batas evidence yang dinyatakan.

## 7. Rencana per predikat — 65 INCOMPLETE

Seluruh guard/probe berikut **PLANNED / NOT YET PROVEN**. Label G01–G09 menunjuk kontrak guard
yang akan dibuat atau diperluas, bukan klaim method sudah tersedia:

- G01: persisted bound-input composition + replay/export/admission (C1).
- G02: real pipeline rebuild dan exact replay rerun (C3).
- G03: replay/backfill status, missing proof dan eligibility to cite (C2).
- G04: reason/import/manual-source preservation di seluruh jalur baca.
- G05: executing full-parent aggregate dan semua anggota wajib.
- G06: bitemporal fixtures dengan known-before, learned-after, cutoff boundary dan counterpart positif.
- G07: field-specific comparison assertions (termasuk null-reason counts).
- G08: consumer/admission claim scan seluruh declared surface + positive population.
- G09: guard executing yang sudah ada, rebind setelah probe discriminating diulang.

Context dan normalized predicate di bawah merupakan snapshot matrix current, bukan koreksi authority.
Setiap row memiliki assertion yang akan ditulis sebagai basis sendiri; kesamaan guard tidak berarti
kesamaan predikat. S019R0009 wajib membaca seluruh Invariant 1, S019R0073 seluruh Invariant 14;
frasa rujukan dalam snapshot tidak boleh menjadi alasan membuktikan kalimat terlepas dari parent.


### F-MD-B18-A002-013 — 10 predicates

#### MD-S050-R0008 — G01 / Q1,Q2
- Source: `authority/strategy/book/Replay_Verification_Contract_LOCKED.md:24`; parent: Required bound inputs.
- Current context: MD-S050-R0006.
- Current normalized predicate: Every fixture/manifest binds at minimum: intentional dataset boundary and temporal universe/listing/symbol/provider mappings;.
- Proposed change/rebind and predicate proof: Persist universe, dataset, issuer/instrument/listing/symbol/provider revisions consumed; exact replay reads the same set after later registry edits.
- Planned single-defect probe: Drop one provider-map revision from the persisted bundle; assert that named component blocks seal/admission or mismatches exact identity. Protocol §6 applies to each independent variant.

#### MD-S050-R0009 — G01 / Q1,Q2
- Source: `authority/strategy/book/Replay_Verification_Contract_LOCKED.md:25`; parent: Required bound inputs.
- Current context: MD-S050-R0006.
- Current normalized predicate: Every fixture/manifest binds at minimum: Regular-Market calendar/session and trading-status revisions;.
- Proposed change/rebind and predicate proof: Persist full calendar/session and status revision context; assert frozen effective/known facts survive exact replay.
- Planned single-defect probe: Omit one session field while retaining other hashes; the session-specific composition assertion must fail. Protocol §6 applies to each independent variant.

#### MD-S050-R0012 — G01 / Q1,Q2
- Source: `authority/strategy/book/Replay_Verification_Contract_LOCKED.md:28`; parent: Required bound inputs.
- Current context: MD-S050-R0006.
- Current normalized predicate: Every fixture/manifest binds at minimum: corporate-action event revisions, verification states, factor-set revisions, and contamination decisions;.
- Proposed change/rebind and predicate proof: Bind event terms, verification state, factor revisions and contamination decisions together, not factor hash alone.
- Planned single-defect probe: Change one verification or contamination decision while preserving factor values; assert event_factor_hash changes and exact comparison names it. Protocol §6 applies to each independent variant.

#### MD-S050-R0014 — G01 / Q1,Q2
- Source: `authority/strategy/book/Replay_Verification_Contract_LOCKED.md:30`; parent: Required bound inputs.
- Current context: MD-S050-R0006.
- Current normalized predicate: Every fixture/manifest binds at minimum: formula, indicator registry, reason registry, price-product, coverage, eligibility, read-model, hash/serialization, and build versions;.
- Proposed change/rebind and predicate proof: Each formula/indicator/reason/price-product/coverage/eligibility/read-model/serialization/build version resolves to the frozen content, including real registry rows.
- Planned single-defect probe: Change one reason registry row while holding nominal states fixed; force a constant reason hash and catch that specific identity assertion; repeat independently for every listed version. Protocol §6 applies to each independent variant.

#### MD-S019-R0067 — G01 / Q1,Q2
- Source: `authority/strategy/book/Determinism_Invariants_LOCKED.md:113`; parent: Invariant 14 — Replay must reproduce unchanged historical inputs.
- Current context: MD-S019-R0065.
- Current normalized predicate: If replay uses identical: temporal issuer/instrument/listing/symbol and provider-mapping revisions.
- Proposed change/rebind and predicate proof: The temporal-master antecedent of Invariant 14 binds the actual consumed revisions, not fabricated metric fields.
- Planned single-defect probe: Replace persisted temporal input with current revision for one listing; exact replay must retain old identity or report that exact mismatch. Protocol §6 applies to each independent variant.

#### MD-S019-R0068 — G01 / Q1,Q2
- Source: `authority/strategy/book/Determinism_Invariants_LOCKED.md:114`; parent: Invariant 14 — Replay must reproduce unchanged historical inputs.
- Current context: MD-S019-R0065.
- Current normalized predicate: If replay uses identical: calendar/session/status revisions.
- Proposed change/rebind and predicate proof: Invariant 14 calendar/status antecedent is a stable hash of frozen facts across later corrections.
- Planned single-defect probe: Swap one frozen calendar or status revision for a later revision; assert the corresponding identity mismatch, separately for each root. Protocol §6 applies to each independent variant.

#### MD-S019-R0069 — G01 / Q1,Q2
- Source: `authority/strategy/book/Determinism_Invariants_LOCKED.md:115`; parent: Invariant 14 — Replay must reproduce unchanged historical inputs.
- Current context: MD-S019-R0065.
- Current normalized predicate: If replay uses identical: corporate-action event/factor-set revisions.
- Proposed change/rebind and predicate proof: Invariant 14 event/factor antecedent includes event revision and verified state independently of numeric factor.
- Planned single-defect probe: Discard one event revision with unchanged factor; the event-specific binding assertion must turn red. Protocol §6 applies to each independent variant.

#### MD-S019-R0071 — G01 / Q1,Q2
- Source: `authority/strategy/book/Determinism_Invariants_LOCKED.md:117`; parent: Invariant 14 — Replay must reproduce unchanged historical inputs.
- Current context: MD-S019-R0065.
- Current normalized predicate: If replay uses identical: price-product and formula/registry versions.
- Proposed change/rebind and predicate proof: Invariant 14 formula/registry/read-model antecedent is identical only when each named version/content is identical.
- Planned single-defect probe: Force replay to read the current formula registry after publication freeze; frozen identity assertion fails even when exported prices match. Protocol §6 applies to each independent variant.

#### MD-S050-R0002 — G01 / Q1,Q2
- Source: `authority/strategy/book/Replay_Verification_Contract_LOCKED.md:9`; parent: Publication replay.
- Current context: SECTION:Publication replay.
- Current normalized predicate: Two replay modes / Publication replay: Reproduces or verifies one historical immutable publication using exactly the observations, temporal master revisions, calendar/status revisions, event/factor revisions, configuration snapshot, formulas/registries, build/adapter versions, serialization rules, and publication manifest frozen with it..
- Proposed change/rebind and predicate proof: Real persisted publication inputs, outputs and lineage replay exactly; every frozen input is compared, with no as-known/current substitution.
- Planned single-defect probe: Replace one bound publication input with a later input; named exact-input mismatch/refusal must occur; repeat each root independently. Protocol §6 applies to each independent variant.

#### MD-S003-R0003 — G01 / Q1,Q2
- Source: `authority/strategy/backtest/Historical_Replay_and_Data_Quality_Backtest.md:12`; parent: Exact publication verification.
- Current context: SECTION:Exact publication verification.
- Current normalized predicate: Required scenario families / Exact publication verification: verify frozen observations, temporal revisions, config, factors, formulas, artifacts, hashes, manifest, seal, reasons, and terminal state;.
- Proposed change/rebind and predicate proof: A production-repository publication fixture covers hashes, revisions and reasons from persisted data through exact replay, without injected fake run properties.
- Planned single-defect probe: Remove temporal input persistence from the producer while leaving replay comparison intact; fixture must fail before a PASS can be admitted. Protocol §6 applies to each independent variant.

### F-MD-B18-A002-014 — 4 predicates

#### MD-S019-R0073 — G02 / Q1,Q4
- Source: `authority/strategy/book/Determinism_Invariants_LOCKED.md:120`; parent: Invariant 14 — Replay must reproduce unchanged historical inputs.
- Current context: MD-S019-R0065.
- Current normalized predicate: If replay uses identical: then replay must reproduce identical outputs and identical hashes..
- Proposed change/rebind and predicate proof: Execute replay twice with all Invariant 14 antecedents fixed and compare identical domain bytes plus output hashes.
- Planned single-defect probe: Introduce execution-dependent value into one domain result on the second replay; equality assertion must fail. Protocol §6 applies to each independent variant.

#### MD-S003-R0004 — G02 / Q1,Q4
- Source: `authority/strategy/backtest/Historical_Replay_and_Data_Quality_Backtest.md:13`; parent: Exact publication verification.
- Current context: SECTION:Exact publication verification.
- Current normalized predicate: Required scenario families / Exact publication verification: prove an unchanged rerun is byte-identical and does not create a fake correction..
- Proposed change/rebind and predicate proof: Rebuild the same date twice through pipeline; bytes are identical and publication/correction counts and pointer stay unchanged on unchanged content.
- Planned single-defect probe: Force one extra correction or publication on the unchanged second run; the exact count/identity assertion fails. Protocol §6 applies to each independent variant.

#### MD-S005-R0095 — G02 / Q1,Q4
- Source: `authority/strategy/book/Audit_Hash_and_Reproducibility_Contract_LOCKED.md:153`; parent: Required proof.
- Current context: MD-S005-R0090.
- Current normalized predicate: Fixtures must prove: exact publication replay reproduces hashes;.
- Proposed change/rebind and predicate proof: Exact replay computes and reproduces every required frozen hash rather than exporting a supplied metric hash.
- Planned single-defect probe: Perturb a computed indicator hash while fixture expectation stays frozen; indicator-specific assertion fails. Protocol §6 applies to each independent variant.

#### MD-S019-R0009 — G02 / Q1,Q4
- Source: `authority/strategy/book/Determinism_Invariants_LOCKED.md:18`; parent: Invariant 1 — Same semantic content and bindings imply same content hashes.
- Current context: SELF_CONTAINED.
- Current normalized predicate: This must hold across reruns and replay..
- Proposed change/rebind and predicate proof: Read whole Invariant 1: bars, indicators and eligibility semantic inputs/bindings determine identical batch hashes across real reruns and replay.
- Planned single-defect probe: Inject drift into only eligibility output on rerun, then separately bars and indicators; the named batch assertion catches each. Protocol §6 applies to each independent variant.

### F-MD-B18-A002-015 — 15 predicates

#### MD-S050-R0053 — G03 / Q3
- Source: `authority/strategy/book/Replay_Verification_Contract_LOCKED.md:101`; parent: Admissibility of a PASS (LOCKED).
- Current context: SECTION:Admissibility of a PASS (LOCKED).
- Current normalized predicate: Capability boundary (LOCKED) / Admissibility of a PASS (LOCKED): `BLOCKED` is not a weaker `PASS`. It states that the comparison did not execute..
- Proposed change/rebind and predicate proof: A missing proof or unknown fixture never becomes replay PASS; expected negative fixture outcome is separate from replay verdict.
- Planned single-defect probe: Restore unknown-case passed=true or convert expected ERROR to replay PASS; the status assertion fails for each branch. Protocol §6 applies to each independent variant.

#### MD-S002-R0009 — G03 / Q1,Q3
- Source: `authority/strategy/backtest/Backtest_Metrics_and_Acceptance_Criteria_LOCKED.md:13`; parent: Replay and Data-Quality Acceptance Criteria (STRATEGY LOCKED).
- Current context: MD-S002-R0002.
- Current normalized predicate: A release candidate requires: `BLOCKED` treated as missing proof, never converted to pass..
- Proposed change/rebind and predicate proof: Every release-criterion BLOCKED path remains missing proof in service, persisted result, export and backfill summary.
- Planned single-defect probe: Convert one blocked child replay to a passed backfill entry; aggregate must reject that entry despite other successful entries. Protocol §6 applies to each independent variant.

#### MD-S002-R0010 — G03+G08 / Q3
- Source: `authority/strategy/backtest/Backtest_Metrics_and_Acceptance_Criteria_LOCKED.md:15`; parent: Replay and Data-Quality Acceptance Criteria (STRATEGY LOCKED).
- Current context: SELF_CONTAINED.
- Current normalized predicate: Pass rates or row-count similarity cannot compensate for a semantic mismatch in a required invariant..
- Proposed change/rebind and predicate proof: Assert that pass percentage and similar row counts cannot override even one required semantic mismatch in backfill/release/citation consumers; valid all-matching control remains accepted.
- Planned single-defect probe: Use 99 matching rows and one semantic mismatch with identical total counts, then force passed=true or a pass-rate override; exact failed-case and forbidden-claim assertions must turn red. Protocol §6 applies to each independent variant.

#### MD-S040-R0071 — G04 / Q1
- Source: `authority/strategy/book/Manual_File_Publishability_Policy_LOCKED.md:157`; parent: 7. Evidence and Replay Contract.
- Current context: MD-S040-R0069.
- Current normalized predicate: Evidence export and replay verification must preserve: coverage reason code.
- Proposed change/rebind and predicate proof: Persist evaluator coverage reason and preserve exactly the same reason in run/replay/export/CLI; two reasons share one coverage state.
- Planned single-defect probe: Replace persisted reason with a state-derived reason at one consumer; that consumer's exact reason assertion fails. Protocol §6 applies to each independent variant.

#### MD-S050-R0031 — G03 / Q1
- Source: `authority/strategy/book/Replay_Verification_Contract_LOCKED.md:60`; parent: Result and evidence.
- Current context: SECTION:Result and evidence.
- Current normalized predicate: Result and evidence: `BLOCKED`: required fixture/runtime/input proof was unavailable..
- Proposed change/rebind and predicate proof: Missing required fixture section or unavailable input is BLOCKED, with precise missing-section paths; executed mismatch remains FAIL.
- Planned single-defect probe: Change one missing-section branch to appendMismatch/FAIL; assert BLOCKED and its named missing path. Protocol §6 applies to each independent variant.

#### MD-S050-R0051 — G08 / Q1
- Source: `authority/strategy/book/Replay_Verification_Contract_LOCKED.md:99`; parent: Admissibility of a PASS (LOCKED).
- Current context: SECTION:Admissibility of a PASS (LOCKED).
- Current normalized predicate: A replay `PASS` may not close a data-quality finding, release a quarantine, dismiss a corporate-action candidate, or satisfy a continuity check..
- Proposed change/rebind and predicate proof: Scan all replay verdict consumers and exercise writes; PASS cannot override quality, quarantine, candidate admission or continuity state.
- Planned single-defect probe: Inject one consumer write promoting quarantined data solely from replay PASS; guard rejects this one write while a harmless read stays allowed. Protocol §6 applies to each independent variant.

#### MD-S050-R0052 — G08 / Q1
- Source: `authority/strategy/book/Replay_Verification_Contract_LOCKED.md:100`; parent: Admissibility of a PASS (LOCKED).
- Current context: SECTION:Admissibility of a PASS (LOCKED).
- Current normalized predicate: Where an audit claim requires correctness, the admissible evidence is independent — verified event terms, source reconciliation, or exchange-published facts — not a replay verdict..
- Proposed change/rebind and predicate proof: Add the dedicated correctness-claim pattern: an audit correctness claim requires independent verified event terms, source reconciliation or exchange facts, rather than replay verdict; preserve a properly supported counterexample.
- Planned single-defect probe: Insert one audit correctness claim justified only by replay PASS into the full declared citation surface; the dedicated rule rejects that claim while allowing the independent-evidence claim. Protocol §6 applies to each independent variant.

#### MD-S036-R0012 — G09 / Q3
- Source: `authority/strategy/book/Import_Promote_Separation_Contract.md:25`; parent: Allowed request modes.
- Current context: MD-S036-R0002.
- Current normalized predicate: Allowed request modes: `replay_verify` \| MD-B18-A001: proof_family=mode_admission.
- Proposed change/rebind and predicate proof: Assert request replay_verify intent independently of ReplayMode at command, request construction, run persistence and ingest routing.
- Planned single-defect probe: Drop replay_verify from one request path while keeping mode valid; path-specific request/branch assertion fails. Protocol §6 applies to each independent variant.

#### MD-S050-R0036 — G09 / Q1
- Source: `authority/strategy/book/Replay_Verification_Contract_LOCKED.md:71`; parent: Mode recording and single-mode implementation (LOCKED).
- Current context: MD-S050-R0034.
- Current normalized predicate: A result carrying no mode is not a publication-replay result by default. It is **unclassified**, and an unclassified result may not be cited as either..
- Proposed change/rebind and predicate proof: Rebind B18ReplayEvidenceSelfExplanationTest::test_an_unmoded_result_is_not_admitted_as_citable_evidence; it asserts incomplete admission and missing-mode reason on the read side.
- Planned single-defect probe: Remove the read-side replay_mode_invalid_or_historical_unclassified check; unmoded export must fail its exact admission assertion. Protocol §6 applies to each independent variant.

#### MD-S003-R0021 — G09 / Q1
- Source: `authority/strategy/backtest/Historical_Replay_and_Data_Quality_Backtest.md:45`; parent: As-known isolation.
- Current context: SECTION:As-known isolation.
- Current normalized predicate: Required scenario families / As-known isolation: later master, event, status, calendar, config, formula, and factor revisions are invisible before their recorded/known times;.
- Proposed change/rebind and predicate proof: Rebind B18AsKnownSnapshotIsolationTest::test_a_later_cutoff_exposes_later_revisions_without_rewriting_the_earlier_snapshot; each of seven roots proves later data cannot enter the earlier snapshot.
- Planned single-defect probe: Remove known-at constraint for one root only; earlier snapshot equality and later positive visibility assertions catch it; repeat seven roots. Protocol §6 applies to each independent variant.

#### MD-S005-R0096 — G09 / Q1
- Source: `authority/strategy/book/Audit_Hash_and_Reproducibility_Contract_LOCKED.md:154`; parent: Required proof.
- Current context: MD-S005-R0090.
- Current normalized predicate: Fixtures must prove: as-known replay excludes later revisions;.
- Proposed change/rebind and predicate proof: The same executing two-cutoff fixture establishes point-in-time replay identity, with independently asserted earlier and later hashes for every root.
- Planned single-defect probe: Use a current-only resolver for one root; earlier hash must drift and the named identity assertion must fail. Protocol §6 applies to each independent variant.

#### MD-S050-R0030 — G07 / Q1
- Source: `authority/strategy/book/Replay_Verification_Contract_LOCKED.md:59`; parent: Result and evidence.
- Current context: SECTION:Result and evidence.
- Current normalized predicate: Result and evidence: `FAIL`: comparison executed and diverged..
- Proposed change/rebind and predicate proof: Complete proof plus executed divergence is FAIL/MISMATCH, with expected/actual context, not BLOCKED.
- Planned single-defect probe: Convert an executed numeric divergence to BLOCKED; exact FAIL and numeric mismatch assertions fail. Protocol §6 applies to each independent variant.

#### MD-S050-R0033 — G07 / Q1
- Source: `authority/strategy/book/Replay_Verification_Contract_LOCKED.md:64`; parent: Result and evidence.
- Current context: SELF_CONTAINED.
- Current normalized predicate: “Command exited successfully” or matching row counts alone is not replay proof..
- Proposed change/rebind and predicate proof: Equal row counts cannot establish equivalence; a content/hash divergence at unchanged population produces its own mismatch.
- Planned single-defect probe: Disable only hash comparison with equal counts and a single changed hash; assert the exact missing mismatch path. Protocol §6 applies to each independent variant.

#### MD-S040-R0077 — G04 / Q1
- Source: `authority/strategy/book/Manual_File_Publishability_Policy_LOCKED.md:163`; parent: 7. Evidence and Replay Contract.
- Current context: MD-S040-R0069.
- Current normalized predicate: Evidence export and replay verification must preserve: final reason code.
- Proposed change/rebind and predicate proof: The evaluator's final reason survives in actual_context even on replay mismatch and evidence export.
- Planned single-defect probe: Replace actual final reason with expected reason in one export path; assert actual_context.final_reason_code unchanged. Protocol §6 applies to each independent variant.

#### MD-S020-R0014 — G08 / Q5
- Source: `authority/strategy/book/Domain_Boundary_Invariants_LOCKED.md:30`; parent: Data-readiness admission rule (LOCKED).
- Current context: MD-S020-R0008.
- Current normalized predicate: Market-data readiness may be admitted only from market-data evidence establishing immutable publication, lineage, reproducibility, and replay;.
- Proposed change/rebind and predicate proof: Read whole admission parent; prove documentation, implementation and readiness claims use immutable publication, lineage, reproducibility and replay evidence with proper owner relationships.
- Planned single-defect probe: Admit one readiness claim with replay-only evidence and no other required basis; owning-stage gate must reject that specific claim; no B18 basis until ownership decided. Protocol §6 applies to each independent variant.

### F-MD-B18-A002-016 — 11 predicates

#### MD-S050-R0027 — G03 / Q3
- Source: `authority/strategy/book/Replay_Verification_Contract_LOCKED.md:52`; parent: Resolution rules.
- Current context: SELF_CONTAINED.
- Current normalized predicate: Publication replay starts from explicit publication identity, never latest/current. Current-read verification is a separate assertion that the pointer resolves a specific publication..
- Proposed change/rebind and predicate proof: Backfill consumes explicit publication IDs from the fixture manifest and never resolves current pointer for exact replay.
- Planned single-defect probe: Switch current pointer between fixture creation and execution; force current-pointer lookup and assert replay still targets the manifest ID or fails. Protocol §6 applies to each independent variant.

#### MD-S036-R0007 — G04 / Q1
- Source: `authority/strategy/book/Import_Promote_Separation_Contract.md:16`; parent: Runtime ownership.
- Current context: SECTION:Runtime ownership.
- Current normalized predicate: Evidence and replay must record and compare request mode, import status, promote status, source mode, pointer switch status, and publication state..
- Proposed change/rebind and predicate proof: Preserve and compare import_status through actual service/repository/export for both replay modes; execute the comparison.
- Planned single-defect probe: Remove import_status comparison while changing only import_status; its own mismatch assertion fails. Protocol §6 applies to each independent variant.

#### MD-S036-R0031 — G04 / Q1
- Source: `authority/strategy/book/Import_Promote_Separation_Contract.md:59`; parent: Evidence and replay boundary.
- Current context: SELF_CONTAINED.
- Current normalized predicate: Evidence export must show whether a run is import-only or promoted without requiring direct DB inspection. Replay must compare expected vs actual request mode, source mode, import status, promote status, publication state, pointer state, and reason code. Unexpected import promotion must be a replay mismatch, not a silent pass..
- Proposed change/rebind and predicate proof: Export preserves request/import/promote/promoted facts as distinct fields in both modes, including negative states.
- Planned single-defect probe: Blank one of the four fields at one exporter assignment; field-specific equality fails; repeat each field and mode. Protocol §6 applies to each independent variant.

#### MD-S040-R0080 — G04 / Q1
- Source: `authority/strategy/book/Manual_File_Publishability_Policy_LOCKED.md:167`; parent: 7. Evidence and Replay Contract.
- Current context: SELF_CONTAINED.
- Current normalized predicate: No evidence or replay flow may treat `manual_file` as readable merely because import succeeded..
- Proposed change/rebind and predicate proof: Manual-file evidence is readable only with the required coverage proof; test complete covered input and missing/failed coverage separately.
- Planned single-defect probe: Admit one manual_file export lacking coverage evidence; admission assertion fails; rejecting all manual_file is caught by the positive fixture. Protocol §6 applies to each independent variant.

#### MD-S003-R0005 — G09 / Q1
- Source: `authority/strategy/backtest/Historical_Replay_and_Data_Quality_Backtest.md:17`; parent: Degraded acquisition and expectation.
- Current context: SECTION:Degraded acquisition and expectation.
- Current normalized predicate: Required scenario families / Degraded acquisition and expectation: provider outage remains missing delivery and cannot shrink the denominator;.
- Proposed change/rebind and predicate proof: Invoke CoverageGateEvaluator with expected universe and zero provider observations; expected denominator remains intact and missing counts rise.
- Planned single-defect probe: Shrink expected universe to observed rows during outage; denominator and missing-count assertions fail before finalization. Protocol §6 applies to each independent variant.

#### MD-S003-R0002 — G03 / Q3
- Source: `authority/strategy/backtest/Historical_Replay_and_Data_Quality_Backtest.md:11`; parent: Exact publication verification.
- Current context: SECTION:Exact publication verification.
- Current normalized predicate: Required scenario families / Exact publication verification: resolve an explicit immutable publication, not latest/current;.
- Proposed change/rebind and predicate proof: PUBLICATION_EXACT without explicit publication_id is refused before pointer/repository resolution; valid explicit ID still executes.
- Planned single-defect probe: Remove early missing-ID refusal and allow pointer fallback; assert no pointer calls and refusal reason; positive ensures not reject-all. Protocol §6 applies to each independent variant.

#### MD-S003-R0009 — G09 / Q1
- Source: `authority/strategy/backtest/Historical_Replay_and_Data_Quality_Backtest.md:24`; parent: Temporal identity and status.
- Current context: SECTION:Temporal identity and status.
- Current normalized predicate: Required scenario families / Temporal identity and status: inactive-now/active-then listing remains in the historical universe;.
- Proposed change/rebind and predicate proof: Rebind the executing delisted-listing fixture; listing active at historical date remains in historical universe despite current delisting.
- Planned single-defect probe: Filter historical universe by current listing status; specific delisted listing must disappear and fail membership assertion. Protocol §6 applies to each independent variant.

#### MD-S003-R0010 — G09 / Q1
- Source: `authority/strategy/backtest/Historical_Replay_and_Data_Quality_Backtest.md:25`; parent: Temporal identity and status.
- Current context: SECTION:Temporal identity and status.
- Current normalized predicate: Required scenario families / Temporal identity and status: symbol change and symbol reuse resolve through stable listing identity;.
- Proposed change/rebind and predicate proof: Rebind executing symbol-change, symbol-reuse and provider-map fixtures; historical listing identity never follows today's symbol owner.
- Planned single-defect probe: Resolve one historical symbol through latest map; assert original listing and provider mapping, independently across the three cases. Protocol §6 applies to each independent variant.

#### MD-S050-R0046 — G08 / Q1
- Source: `authority/strategy/book/Replay_Verification_Contract_LOCKED.md:91`; parent: Capability boundary (LOCKED).
- Current context: MD-S050-R0044.
- Current normalized predicate: What replay cannot prove: **That the source observation was faithful.** Provider error inside an immutable observation is frozen by the same mechanism that guarantees reproducibility..
- Proposed change/rebind and predicate proof: Corpus claims distinguish source-faithfulness from point-in-time knowledge; inspect all declared claim surfaces with a positive supported claim.
- Planned single-defect probe: Inject one source-faithfulness claim inferred only from publication replay; exact citation-boundary guard rejects it. Protocol §6 applies to each independent variant.

#### MD-S050-R0017 — G05 / Q1
- Source: `authority/strategy/book/Replay_Verification_Contract_LOCKED.md:37`; parent: Anti-future and anti-survivorship rules.
- Current context: SELF_CONTAINED.
- Current normalized predicate: Replay must not use today's `is_active`, current symbol, current sector, current suspension/status, latest calendar correction, later corporate-action revision, later factor, current config, or latest provider mapping unless that exact revision was frozen/known in the selected mode..
- Proposed change/rebind and predicate proof: Execute every anti-current rule under the nine-member parent; membership and each member assertion are required, with positive visibility controls.
- Planned single-defect probe: Disable one member body or current-isolation predicate at a time; aggregate must fail for that member including no-assert, skip and exception. Protocol §6 applies to each independent variant.

#### MD-S019-R0074 — G01 / Q1,Q2
- Source: `authority/strategy/book/Determinism_Invariants_LOCKED.md:122`; parent: Invariant 14 — Replay must reproduce unchanged historical inputs.
- Current context: SELF_CONTAINED.
- Current normalized predicate: Publication replay freezes the exact identities above. As-known replay resolves only revisions known by the declared knowledge cutoff. Current state must not leak into either mode..
- Proposed change/rebind and predicate proof: Invariant 14 no-current-registry clause uses frozen identity for exact and bound known-at identity for AS_KNOWN.
- Planned single-defect probe: Change today's registry after a valid fixture; route one replay lookup to it and assert frozen result/identity fails. Protocol §6 applies to each independent variant.

### F-MD-B18-A002-017 — 16 predicates

#### MD-S050-R0016 — G03 / Q1,Q2
- Source: `authority/strategy/book/Replay_Verification_Contract_LOCKED.md:33`; parent: Required bound inputs.
- Current context: SELF_CONTAINED.
- Current normalized predicate: Missing input is `BLOCKED`, not permission to query current/latest state..
- Proposed change/rebind and predicate proof: All mandatory input identities, not just config, gate replay admission in both modes and repository direct writes.
- Planned single-defect probe: Blank only temporal, calendar or observation identity in turn; assert BLOCKED/NOT_ADMISSIBLE and exact missing field, plus complete-input PASS control. Protocol §6 applies to each independent variant.

#### MD-S004-R0003 — G05 / Q1
- Source: `authority/strategy/backtest/Point_In_Time_Backtest_Input_Contract_LOCKED.md:11`; parent: Knowledge-time rule.
- Current context: SELF_CONTAINED.
- Current normalized predicate: Today's universe, symbol, sector, action verification, or current publication may not be backfilled into an earlier decision..
- Proposed change/rebind and predicate proof: Execute all five no-future-backfill clauses from the whole parent, including their read-side resolvers and member assertion population.
- Planned single-defect probe: Make one of five members consult future/current input while retaining all method names; aggregate must fail that member. Protocol §6 applies to each independent variant.

#### MD-S004-R0005 — G05 / Q1
- Source: `authority/strategy/backtest/Point_In_Time_Backtest_Input_Contract_LOCKED.md:19`; parent: Survivorship and revisions.
- Current context: SELF_CONTAINED.
- Current normalized predicate: Inactive/delisted securities remain present when they were in the temporal universe. Symbol changes/reuse use listing IDs. Late corrections/actions produce a distinct later-known dataset and do not rewrite the earlier-known dataset..
- Proposed change/rebind and predicate proof: Execute all three anti-survivorship cases with exact historical universe assertions, not method-existence checks.
- Planned single-defect probe: Use today's survivor set in one member; named historical membership assertion and aggregate both fail. Protocol §6 applies to each independent variant.

#### MD-S004-R0008 — G05 / Q1
- Source: `authority/strategy/backtest/Point_In_Time_Backtest_Input_Contract_LOCKED.md:29`; parent: Acceptance fixtures.
- Current context: SELF_CONTAINED.
- Current normalized predicate: At minimum prove inactive-now/active-then membership, symbol transition/reuse, late action verification, late config/calendar/status correction, unavailable same-day data, explicit stale fallback, and original-versus-corrected as-known datasets..
- Proposed change/rebind and predicate proof: Execute all seven point-in-time acceptance members, each with nonzero assertions and no skipped/incomplete members.
- Planned single-defect probe: Replace one member with a no-op while preserving declaration; aggregate rejects assertion count zero; repeat all seven. Protocol §6 applies to each independent variant.

#### MD-S004-R0002 — G05 / Q1
- Source: `authority/strategy/backtest/Point_In_Time_Backtest_Input_Contract_LOCKED.md:9`; parent: Knowledge-time rule.
- Current context: SELF_CONTAINED.
- Current normalized predicate: Each decision timestamp declares a `knowledge_cutoff`. Inputs contain only observations and identity, calendar, status, event, factor, config, and formula revisions recorded/known by that cutoff and effective for the evaluated context..
- Proposed change/rebind and predicate proof: Execute all eight cutoff-bound inputs from the entire parent; each uses its own known/effective boundary and positive counterpart.
- Planned single-defect probe: Remove one known-at filter while the other seven remain correct; the specific member and aggregate fail. Protocol §6 applies to each independent variant.

#### MD-S002-R0005 — G05 / Q1
- Source: `authority/strategy/backtest/Backtest_Metrics_and_Acceptance_Criteria_LOCKED.md:9`; parent: Replay and Data-Quality Acceptance Criteria (STRATEGY LOCKED).
- Current context: MD-S002-R0002.
- Current normalized predicate: A release candidate requires: all anti-survivorship and as-known isolation fixtures passing;.
- Proposed change/rebind and predicate proof: Release acceptance runs all anti-survivorship and as-known isolation fixtures, including nested members, not merely outer static list checks.
- Planned single-defect probe: Make one nested executing member fail while structural lists remain unchanged; acceptance aggregate fails. Protocol §6 applies to each independent variant.

#### MD-S002-R0008 — G05 / Q1
- Source: `authority/strategy/backtest/Backtest_Metrics_and_Acceptance_Criteria_LOCKED.md:12`; parent: Replay and Data-Quality Acceptance Criteria (STRATEGY LOCKED).
- Current context: MD-S002-R0002.
- Current normalized predicate: A release candidate requires: corrected publications preserving their predecessors and switching atomically; and.
- Proposed change/rebind and predicate proof: Execute correction predecessor preservation and atomic pointer switch/read coherence fixtures; retain rows and reject partial visibility.
- Planned single-defect probe: Allow one incoherent pointer/projection read or remove predecessor rows during correction; member and acceptance aggregate fail independently. Protocol §6 applies to each independent variant.

#### MD-S002-R0007 — G05 / Q1
- Source: `authority/strategy/backtest/Backtest_Metrics_and_Acceptance_Criteria_LOCKED.md:11`; parent: Replay and Data-Quality Acceptance Criteria (STRATEGY LOCKED).
- Current context: MD-S002-R0002.
- Current normalized predicate: A release candidate requires: long-chain ATR and corporate-action results matching independent oracles;.
- Proposed change/rebind and predicate proof: Execute independent long-chain Wilder ATR and corporate-action oracles; compare results against independently derived numeric/state expectations.
- Planned single-defect probe: Perturb one ATR coefficient or one corporate-action factor/verification rule; relevant oracle and aggregate fail separately. Protocol §6 applies to each independent variant.

#### MD-S002-R0003 — G07 / Q1
- Source: `authority/strategy/backtest/Backtest_Metrics_and_Acceptance_Criteria_LOCKED.md:7`; parent: Replay and Data-Quality Acceptance Criteria (STRATEGY LOCKED).
- Current context: MD-S002-R0002.
- Current normalized predicate: A release candidate requires: zero unexplained value, null-reason, lineage, config, factor, hash, seal, or publication mismatches in exact publication fixtures;.
- Proposed change/rebind and predicate proof: Mismatch corpus executes every named value/null-reason/lineage/config/factor/hash/seal/publication class, each with own mismatch assertion.
- Planned single-defect probe: Disable config comparison then factor comparison independently; own-path assertions fail even if a secondary mismatch still exists. Protocol §6 applies to each independent variant.

#### MD-S002-R0006 — G05 / Q1
- Source: `authority/strategy/backtest/Backtest_Metrics_and_Acceptance_Criteria_LOCKED.md:10`; parent: Replay and Data-Quality Acceptance Criteria (STRATEGY LOCKED).
- Current context: MD-S002-R0002.
- Current normalized predicate: A release candidate requires: all degraded/negative fixtures producing their expected held/failed/unavailable states without silent repair or denominator shrinkage;.
- Proposed change/rebind and predicate proof: Execute all degraded/negative expected held/failed/unavailable outcomes, including evaluator-level outage without denominator shrinkage.
- Planned single-defect probe: Permit zero-row outage to shrink denominator; evaluator member and acceptance aggregate fail; negative expected outcome remains distinct from replay PASS. Protocol §6 applies to each independent variant.

#### MD-S065-R0003 — G09 / Q1
- Source: `authority/strategy/ops/Config_Change_Protocol_LOCKED.md:7`; parent: Locked rules.
- Current context: MD-S065-R0001.
- Current normalized predicate: Any output-affecting config change must be treated as a contract change. reruns must use the registry version effective for the requested trade date or explicitly documented override.
- Proposed change/rebind and predicate proof: Rebind effective-time config selection plus actual rerun caller in EodRunRepository; all three resolveForRun call sites use requested historical date.
- Planned single-defect probe: Replace requested date at the rerun caller only with current date; historical config identity assertion fails while normal creation still passes. Protocol §6 applies to each independent variant.

#### MD-S003-R0025 — G05 / Q1
- Source: `authority/strategy/backtest/Historical_Replay_and_Data_Quality_Backtest.md:56`; parent: Acceptance.
- Current context: SELF_CONTAINED.
- Current normalized predicate: All required scenario families pass on MariaDB production semantics and the supported test mirror. Any missing family remains an open proof gap; historical green results for superseded rules do not close it..
- Proposed change/rebind and predicate proof: Execute all six scenario families on production MariaDB repositories/schema and mirror; use real exact/degraded/correction readers, not handwritten SQL substitutes.
- Planned single-defect probe: Replace one production read path with current-only lookup or skip one family/driver; aggregate reports that missing/failing member; zero DB skips permitted. Protocol §6 applies to each independent variant.

#### MD-S003-R0023 — G01 / Q1,Q2
- Source: `authority/strategy/backtest/Historical_Replay_and_Data_Quality_Backtest.md:50`; parent: Per-run evidence.
- Current context: SELF_CONTAINED.
- Current normalized predicate: Record replay mode, fixture/manifest hash, requested/effective dates, knowledge cutoff, all frozen revision/snapshot IDs, expected/actual readiness and reason sets, field-level mismatch paths, artifact/manifest/seal hashes, executable build identity, and `PASS`/`FAIL`/`BLOCKED`..
- Proposed change/rebind and predicate proof: Export all frozen input identities and versions from persisted context; later environment drift cannot change exact evidence.
- Planned single-defect probe: Omit one exported frozen version at a time; per-key persisted-to-export equality fails, with complete identity-map population. Protocol §6 applies to each independent variant.

#### MD-S004-R0004 — G01 / Q1,Q2
- Source: `authority/strategy/backtest/Point_In_Time_Backtest_Input_Contract_LOCKED.md:15`; parent: Required input identity.
- Current context: SELF_CONTAINED.
- Current normalized predicate: Every row/export binds listing identity, requested/effective trade date, knowledge cutoff, as-known replay ID/publication-like artifact ID, read-model version, full config hash, factor/formula versions, and lineage. Availability timestamp is distinct from market trade date..
- Proposed change/rebind and predicate proof: Bind listing, version and availability semantics used by the point-in-time dataset, including knowledge times and lineage.
- Planned single-defect probe: Drop one listing revision or availability timestamp while values match; semantic identity/boundary assertion fails. Protocol §6 applies to each independent variant.

#### MD-S082-R0218 — G01 / Q1,Q2
- Source: `authority/strategy/registry/Platform_Config_Registry_LOCKED.md:290`; parent: Effective-time and replay rules.
- Current context: SELF_CONTAINED.
- Current normalized predicate: Current registry state must never leak into historical replay. Alternate-scenario runs are explicitly labeled and cannot impersonate the historical publication..
- Proposed change/rebind and predicate proof: Exact verification does not leak current registry state; any alternative reconstruction is explicitly labeled and not admitted as exact.
- Planned single-defect probe: Route one lookup to current registry after freeze, or mislabel reconstructed result exact; assert identity/admission violation separately. Protocol §6 applies to each independent variant.

#### MD-S082-R0224 — G01 / Q1,Q2
- Source: `authority/strategy/registry/Platform_Config_Registry_LOCKED.md:300`; parent: Validation and acceptance proof.
- Current context: MD-S082-R0219.
- Current normalized predicate: Before seal, validation proves: current environment drift cannot change publication replay;.
- Proposed change/rebind and predicate proof: Executable build, formula, read-model and serialization drift cannot silently redefine historical exact verification.
- Planned single-defect probe: Change each environment version separately after publication, force current fallback and assert corresponding frozen-version comparison fails. Protocol §6 applies to each independent variant.

### F-MD-B18-A002-018 — 9 predicates

#### MD-S050-R0022 — G06 / Q1
- Source: `authority/strategy/book/Replay_Verification_Contract_LOCKED.md:44`; parent: Anti-future and anti-survivorship rules.
- Current context: MD-S050-R0018.
- Current normalized predicate: Required fixtures include: a calendar/status fact corrected after T;.
- Proposed change/rebind and predicate proof: Calendar/status fixtures include a known-before revision and learned-later correction; earlier cutoff keeps old facts, later cutoff sees correction.
- Planned single-defect probe: Install cutoff wall hiding all revisions; positive known-before assertion fails; remove knowledge filter and earlier assertion fails separately. Protocol §6 applies to each independent variant.

#### MD-S041-R0032 — G06+G01 / Q1,Q2
- Source: `authority/strategy/book/Market_Calendar_Requirements_Contract.md:64`; parent: Session-completion rule (LOCKED).
- Current context: SELF_CONTAINED.
- Current normalized predicate: Historical processing uses the calendar revision/evidence governed for the replay mode. As-known replay must not use a future calendar correction that was unknown at its cutoff..
- Proposed change/rebind and predicate proof: Calendar revision identity and session facts are cutoff-correct and frozen for exact replay; both known-old and later-correction paths execute.
- Planned single-defect probe: Hide all calendar revisions at cutoff, then independently substitute latest revision in exact binding; relevant positive/frozen identity assertion fails. Protocol §6 applies to each independent variant.

#### MD-S050-R0023 — G06 / Q1
- Source: `authority/strategy/book/Replay_Verification_Contract_LOCKED.md:45`; parent: Anti-future and anti-survivorship rules.
- Current context: MD-S050-R0018.
- Current normalized predicate: Required fixtures include: a corporate action learned or verified later;.
- Proposed change/rebind and predicate proof: Event learned and verified boundaries use independent revisions/times with an already-known positive; later knowledge never enters earlier snapshot.
- Planned single-defect probe: Replace event resolver with cutoff wall or drop learned/verified constraint separately; known-positive and earlier-exclusion assertions catch each. Protocol §6 applies to each independent variant.

#### MD-S003-R0011 — G06 / Q1
- Source: `authority/strategy/backtest/Historical_Replay_and_Data_Quality_Backtest.md:26`; parent: Temporal identity and status.
- Current context: SECTION:Temporal identity and status.
- Current normalized predicate: Required scenario families / Temporal identity and status: calendar/session/status revisions respect effective and knowledge time..
- Proposed change/rebind and predicate proof: Calendar/status correctness requires effective date AND knowledge cutoff, including inclusive boundary and revision supersession.
- Planned single-defect probe: Keep effective date filter but remove knowledge filter on one root; later-recorded effective-old correction must fail earlier snapshot assertion. Protocol §6 applies to each independent variant.

#### MD-S050-R0028 — G06 / Q1
- Source: `authority/strategy/book/Replay_Verification_Contract_LOCKED.md:54`; parent: Resolution rules.
- Current context: SELF_CONTAINED.
- Current normalized predicate: As-known replay performs bitemporal resolution with `effective_at <= target context` and `recorded_at <= knowledge_cutoff`; ties and corrections use versioned deterministic rules. Unresolved ambiguity fails closed..
- Proposed change/rebind and predicate proof: Every root (identity, status, calendar, event, config, factor, and source/formula bindings through their roots) is effective-and-known bounded; ties/corrections follow owner contract or fail ambiguous.
- Planned single-defect probe: Remove knowledge predicate on exactly one root; its earlier-negative/later-positive assertions fail; repeat roots and reject-all wall probes. Protocol §6 applies to each independent variant.

#### MD-S003-R0014 — G09 / Q1
- Source: `authority/strategy/backtest/Historical_Replay_and_Data_Quality_Backtest.md:32`; parent: Corporate actions and indicators.
- Current context: SECTION:Corporate actions and indicators.
- Current normalized predicate: Required scenario families / Corporate actions and indicators: provider adjusted-close fallback is impossible;.
- Proposed change/rebind and predicate proof: Rebind CanonicalRawImportBoundaryTest::test_provider_adjusted_close_never_reaches_the_canonical_row and CoherentPriceProductBoundaryTest::test_provider_adjusted_close_is_not_scaled_by_a_platform_factor.
- Planned single-defect probe: Copy provider adj_close into RAW close then separately apply platform factor to adj_close; respective actual canonical/product assertions fail. Protocol §6 applies to each independent variant.

#### MD-S050-R0025 — G09 / Q1
- Source: `authority/strategy/book/Replay_Verification_Contract_LOCKED.md:47`; parent: Anti-future and anti-survivorship rules.
- Current context: MD-S050-R0018.
- Current normalized predicate: Required fixtures include: an original and corrected immutable publication; and.
- Proposed change/rebind and predicate proof: Rebind B18CorrectionReadPathScenarioTest::test_a_correction_sealed_later_is_invisible_to_an_earlier_cutoff; execute predecessor/correction and ambiguous-selection negative.
- Planned single-defect probe: Return later correction at earlier cutoff or select arbitrary ambiguous candidate; exact publication identity/refusal assertion fails. Protocol §6 applies to each independent variant.

#### MD-S055-R0025 — G06+G01 / Q1,Q2
- Source: `authority/strategy/book/Symbol_Lifecycle_and_Mapping_Contract.md:50`; parent: Historical replay rule (LOCKED).
- Current context: SELF_CONTAINED.
- Current normalized predicate: Replay uses the mapping effective on trade date T and, for as-known mode, only the revision known by replay cutoff. It must not use a future rename, relisting, provider correction, or current symbol to resolve historical data..
- Proposed change/rebind and predicate proof: Symbol/provider remapping resolves listing at cutoff and the same mapping revisions are frozen into exact identity.
- Planned single-defect probe: Substitute latest mapping for one historical symbol/provider; assert historical listing and mapping revision mismatch separately. Protocol §6 applies to each independent variant.

#### MD-S050-R0029 — G07 / Q1
- Source: `authority/strategy/book/Replay_Verification_Contract_LOCKED.md:58`; parent: Result and evidence.
- Current context: SECTION:Result and evidence.
- Current normalized predicate: Result and evidence: `PASS`: all expected values, null reasons, states, lineages, content hashes, manifest, and seal assertions match..
- Proposed change/rebind and predicate proof: Compare each assertion class independently, including null-reason distribution and seal; own field/path must be present, not just any mismatch.
- Planned single-defect probe: Disable compareReasonCodeCounts with only reason counts changed; then disable each seal comparison independently and assert the exact absent path despite other mismatches. Protocol §6 applies to each independent variant.

## 8. Retained proof dan dependency yang tetap berlaku

Delapan retained PAIR01 tetap wajib diperkuat saat C1 berubah: MD-S050-R0007 (mode/fixture/date),
R0010 (observation dan adapter/schema/normalization), R0011 (RAW input), R0013 (config ID/hash),
R0015 (publication ID/version/pointer/seal dan tiga output hashes), MD-S019-R0066 (observations),
R0070 (config), R0072 (build). Setiap field diuji dari producer tersimpan sampai export, kemudian
di-drop/substitusi satu per satu. Perluas expected_eligibility_batch_hash, expected_publication_id
dan canonicalization/normalization_version yang belum dijaga PAIR01. Tidak ada automatic carry.

42 retained lainnya direvalidasi menurut predicate basis dan affected-source fingerprint, termasuk
R0056 aggregate sembilan anggota. Negative stub EventRiskSourceRepository::isAdjustable yang tidak
terpanggil tidak dijadikan bukti R0012; pertahankan executing positive dan probe admit-none sebagai
control, lalu rebind negative jika memang perlu. Jangan mengubah aplikasi yang sudah conform.

Empat conditional N/A tetap memerlukan proof E008 current dan re-execution ketika source yang
diuji berubah. 33 reference dan 2 optional tidak menjadi PASS. D001 deferral ke B21/B22 tetap
dicatat dengan relationship dan tidak disamakan dengan dependency resolved.

MD-DEP-0017 memblokir implementasi sampai review paket. MD-DEP-0015 memblokir closure full-suite
criterion; D004 mengaitkannya ke recovery terpisah MD-DEP-0016, yang belum diotorisasi. Status
MD-DEP-0016 tetap OPEN_NON_BLOCKING sebagai track terpisah; efek terhadap closure dinyatakan
melalui MD-DEP-0015, sehingga frasa lama “does not block B18 closure” tidak dipakai lagi.
MD-DEP-0009 tetap memblokir return B19. Tidak ada recovery atau load data besar dalam paket ini.

## 9. Acceptance dan titik berhenti

Paket siap direview bila tepat 65 IDs cocok dengan INCOMPLETE current, context/source tersedia,
semua F013–F018 tercakup, setiap row mempunyai assertion/probe, keputusan dan urutan jelas,
registries/relationship valid dan CURRENT_STATE dihasilkan dari register. Ini bukan closure B18.

Closure tetap mengikuti register dan STAGE_CLOSURE_MANIFEST_STANDARD: denominator final;
seluruh required SATISFIED dengan basis current per predicate; CI current; residue conformant;
dependency resolved/diatur resmi; semua relationship; gate hijau dengan landed-red probes;
E001 dan SC terbit/terdaftar; full suite hijau tanpa skip; satu resume dan return-to sah.

**Single next resume: review Q1–Q5 paket ini di bawah MD-DEP-0017.** Setelah keputusan diterima,
rekam D/relationship dan scope CI lebih dahulu, lalu eksekusi §5 sesuai keputusan. Tidak ada
perubahan aplikasi/test, binding, atau klaim closure sebelum titik review ini dilewati.
