# Market-Data Implementation Conformance Matrix (LOCKED)

## Status and role

Status dokumentasi: **`DOCUMENTATION_STRATEGY_READY`**.

Documentation-strategy synchronization: **`PASS` (2026-08-07)**. Strategy coverage `22/22`; owner assignment completeness tetap wajib dibuktikan melalui ledger dan tidak menyiratkan implementation/runtime conformance.

Dokumen ini adalah owner untuk **traceability kelengkapan implementasi**. `Market_Data_Strategy_Implementation_Blueprint_LOCKED.md` memiliki urutan kerja `W00`–`W22`; owner contract paling spesifik memiliki behavior; matrix ini memastikan seluruh dokumen strategi aktif memperoleh contract-area assignment, deliverable, proof, dan status sehingga tidak ada yang terlewat.

Matrix ini tidak membuat logic watchlist. Weekly Swing hanya initial consumer profile pada read boundary. Screening, tradability threshold, ranking, signal, entry/exit, portfolio, dan performance proof berada di luar conformance market-data.

## How to use this matrix (LOCKED)

Sebelum coding dimulai, gunakan current-state ledger `../audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md` dengan satu row untuk setiap assignment di dokumen ini. Command lifecycle dan result format wajib mengikuti `Market_Data_Implementation_Command_Protocol_LOCKED.md`. Minimum kolom ledger/detail assignment-nya:

| Field | Meaning |
|---|---|
| `work_order` | `W00`–`W22` dari blueprint |
| `contract_area` | stage 1–22 yang dilayani |
| `document` | owner/support document yang harus dipenuhi |
| `schema_config_impact` | migration, constraint, seed, config, registry, atau `NONE` |
| `runtime_impact` | adapter/service/repository/command/read path yang berubah |
| `backfill_impact` | `NONE`, required range, resume key, dan verification query |
| `test_ids` | positive/negative/failure/correction/replay/concurrency tests |
| `runbook_evidence` | operator path dan evidence bundle yang membuktikan behavior |
| `status` | vocabulary terkunci di bawah |
| `reviewer_evidence` | commit/build/DB/config/evidence identity dan reviewer decision |

Satu dokumen boleh mempunyai primary stage dan cross-cutting impact. Primary stage menentukan pemilik penutupan; cross-cutting stage tetap wajib mencatat impact.

## Status vocabulary (LOCKED)

- `NOT_STARTED`: belum ada implementation work yang dapat dibuktikan.
- `IN_PROGRESS`: sebagian artifact telah dibuat, tetapi exit gate belum lengkap.
- `IMPLEMENTED_NOT_PROVEN`: code/schema tersedia, tetapi semantic/executed proof belum memenuhi contract.
- `PROVEN`: targeted contract proof lulus dan evidence admissible, tetapi global convergence/audit belum selesai.
- `CONFORMANT`: targeted proof, full affected suite, schema/config/code/ops alignment, dan cross-contract review lulus.
- `SUPERSEDED`: legacy artifact/test tidak lagi mewakili owner strategy dan tidak boleh dihitung sebagai proof. **Pemicu wajib:** ketika sebuah stage menolak suatu behavior, setiap test, fixture, atau oracle yang masih menguncinya wajib ditandai `SUPERSEDED` **pada stage yang sama**, sesuai langkah 3 dan 7 pada blueprint loop. Status ini tidak opsional dan tidak boleh ditunda ke stage 21.
- `BLOCKED`: hanya dipakai sesuai goal/audit governance; blocker dan missing authority/evidence harus eksplisit.

`table exists`, `column exists`, command exit code `0`, HTTP `200`, non-empty rows, atau green legacy test tidak pernah cukup untuk `PROVEN`/`CONFORMANT`.

## Global no-omission gates (LOCKED)

Sebelum `W22`:

1. setiap dokumen normative core/support di bawah memiliki ledger row;
2. setiap field/config/reason/status memiliki satu semantic owner;
3. setiap output-affecting config terikat non-null snapshot ID/hash;
4. setiap table/column/constraint memiliki writer, reader, migration, backfill, dictionary, dan test decision;
5. setiap command memiliki ownership, lock, retry/resume, failure, logging, dan evidence behavior;
6. setiap contract memiliki positive dan negative proof; correction/concurrency/replay proof ditambahkan bila relevan;
7. tidak ada `TODO`, `TBD`, transitional null, force bypass, synthetic repair, atau superseded oracle yang disamarkan sebagai closure;
8. system/audit/runbook docs disinkronkan setelah behavior nyata terbukti;
9. archived evidence dan examples tidak dihitung sebagai current executed proof tanpa admission metadata;
10. watchlist outcomes tidak digunakan sebagai market-data acceptance evidence;
11. setiap source, detector, resolver, dan validator menyatakan capability boundary-nya pada owner contract: apa yang dapat dibuktikan, apa yang hanya diagnostic, apa yang tidak dapat dilihat, dan fail-safe state ketika evidence tidak tersedia. **Cakupan gate ini adalah dokumen yang memiliki mekanisme penghasil verdict, state, flag, atau signal** — bukan setiap dokumen yang ter-assign pada sebuah stage. Kontrak yang hanya menetapkan pemisahan tanggung jawab, lokasi penyimpanan, kewenangan operator, atau bentuk artefak tidak menghasilkan verdict dan berada di luar cakupan; menambahkan boundary generik ke dokumen semacam itu memenuhi pemeriksaan mekanis tanpa mengajarkan apa pun, dan justru melemahkan gate ini. Bila ragu, tanyakan apakah dokumen itu memiliki keluaran yang dapat dikutip sebagai bukti — bila ya, ia wajib menyatakan batasnya. Pernyataan itu wajib berada di bawah heading yang judulnya dimulai dengan **`Capability boundary`**, setelah nomor section opsional, agar gate ini dapat diperiksa secara mekanis. Kualifikasi boleh mengikuti setelahnya. Bentuk yang sah mencakup `## Capability boundary (LOCKED)`, `## 5. Capability Boundary (LOCKED)`, dan `## Capability boundary — detection sensitivity (LOCKED)`;
12. tidak ada output, verdict, atau release keputusan yang memakai **ketiadaan sinyal dari komponen berbatas** sebagai bukti ketiadaan peristiwa.

13. setiap **root of expectation** — kalender, universe/identitas, corporate action, trading status, serta **sector classification/membership untuk setiap produk sector-relative** — memiliki rekonsiliasi eksternal, karena tidak ada gate internal yang dapat mendeteksi ketidaklengkapannya. Aturan bersama berikut dimiliki gate ini dan **tidak boleh diulang** di owner contract; owner contract hanya menyatakan parameter domainnya sendiri, yaitu sumber otoritatif yang dipakai, cadence rekonsiliasinya, cakupan periodenya, dan qualification bila periodenya belum direkonsiliasi:
    - **dua arah** — tercatat tetapi tidak pernah terjadi, dan terjadi tetapi tidak pernah tercatat. Arah kedua adalah yang tidak dapat dijangkau gate internal mana pun;
    - **independen dari pipeline harian**, dengan cadence sendiri, karena pipeline mengukur kesesuaian terhadap sumber ekspektasi, bukan sumber ekspektasi itu sendiri;
    - **periode yang belum direkonsiliasi dinyatakan eksplisit**, dan klaim apa pun yang mencakupnya dikualifikasi sesuai;
    - **pipeline hijau, gate yang lulus, atau ketiadaan keluhan bukan bukti rekonsiliasi**.

Gate 13 adalah penawar dari gate 11 dan 12: keduanya menamai wilayah buta, gate ini menetapkan satu-satunya cara mengisinya. **Lima domain rekonsiliasi** kini tunduk pada aturan bersama ini: calendar, universe/identity, trading status, corporate action, dan sector classification/membership untuk sector-relative products. Owner contract hanya membawa parameter domainnya agar aturan bersama tidak menyimpang antar domain.

Gate 11 dan 12 berbeda dari gate 6. Gate 6 membuktikan komponen menolak input buruk dengan benar. Gate 11 menyatakan wilayah tempat komponen tidak menghasilkan sinyal sama sekali, dan gate 12 melarang wilayah itu dibaca sebagai bukti. Sebuah komponen dapat lulus seluruh negative proof dan tetap menyesatkan bila wilayah butanya tidak dinyatakan.

## Contract-area assignments and exit gates

### Stage 1 — scope, terminology, and time boundary

Work order: `W01`.

Documents:

- `../README.md`
- `Terminology_and_Scope.md`

Required implementation outcome:

- canonical product hanya IDX Regular-Market EOD;
- timezone/session/date semantics eksplisit;
- `2023-01-02` menjadi intentional dataset boundary yang tervalidasi config;
- development frontier, operational activation, requested/effective date, dan proof window tidak tercampur;
- RAW source observation, canonical `RAW`, `STRUCTURAL_ADJUSTED`, `TOTAL_RETURN`, coverage, quality, data usability, readiness, dan freshness memiliki satu arti.

Exit gate: constants/config/API vocabulary/schema dictionary/test names tidak menentang terminology owner dan tidak membuat pre-2023/freshness/watchlist-performance claim yang salah.

### Stage 2 — domain boundary

Work order: `W01`.

Documents:

- `Domain_Boundary_Invariants_LOCKED.md`

Required implementation outcome: namespace, table, config, command, DTO, reason code, dan API market-data tidak menghasilkan screening, tradability threshold, ranking, signal, entry/exit, sizing, portfolio, atau execution policy.

Exit gate: boundary static/architecture tests lulus dan compatibility `eligible` hanya berarti upstream `data_usable`.

### Stage 3 — Yahoo bootstrap and provider-neutral boundary

Work order: `W02`.

Documents:

- `Yahoo_Finance_Bootstrap_Source_Strategy.md`

Required implementation outcome: provider port netral, Yahoo adapter terisolasi, limitation/provenance/schema/date/licensing disclosure eksplisit, manual file hanya controlled **one-date operational rescue** (bukan multi-day continuity), dan paid-provider work tetap deferred.

Exit gate: mengganti adapter tidak mengubah canonical/product/indicator/read contracts dan Yahoo tidak pernah dilabel official IDX source.

### Stage 4 — immutable source observations

Work orders: `W03`, `W04`, `W07`.

Documents:

- `Source_Data_Acquisition_Contract_LOCKED.md`
- `Source_Mapping_Contract_LOCKED.md`
- `../ops/Credentials_and_Secrets_Contract.md`

Required implementation outcome: setiap request/file/response/empty/failure memiliki immutable observation identity, requested scope, provider/mapping/adapter/schema identity, source/acquired timestamps, sanitized request, payload hash/reference, outcome, reasons, dan supersession lineage.

Exit gate: rerun tidak menimpa observation; secret tidak bocor; canonical rows dapat ditelusuri ke observation yang tepat.

### Stage 5 — source resilience, controlled recovery, and failure states

Work order: `W08`.

Documents:

- `EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`
- `Manual_File_Publishability_Policy_LOCKED.md`
- `../db/Optional_Fetch_Failures_Table.md`
- `../db/Optional_Fetch_Failures_Table.sql`
- `../ops/Error_Taxonomy_and_Run_Status_Decision_Table_LOCKED.md`
- `../ops/Failure_Playbook_LOCKED.md`

Required implementation outcome: retry/backoff/rate-limit/circuit-breaker, wrong-date/stale/schema quarantine, per-ticker versus global failure, manual import-only/promote, degraded/held/failed transitions, dan no-synthetic-repair behavior.

Exit gate: outage/partial/empty/wrong-date/schema-change fixtures tidak menghasilkan silent readable publication atau denominator shrink.

### Stage 6 — temporal identity, symbol/provider mapping, and sector-reference foundation

Work order: `W05`.

Documents:

- `Tickers_and_Identity_Dependency_Contract_LOCKED.md`
- `Symbol_Lifecycle_and_Mapping_Contract.md`
- `Sector_Classification_Contract_LOCKED.md` — cross-cutting prerequisite; sector-relative analytical products may not precede this foundation

Required implementation outcome: issuer, instrument, listing, exchange symbol, provider symbol, dan temporal `IDX-IC` membership berbeda governed facts dan bitemporal/as-known resolvable; current `is_active` maupun current sector bukan historical truth.

Exit gate: listing/delisting, rename, symbol reuse, provider mapping revision, inactive-now-active-then, sector reclassification, missing-membership, dan current-sector-leakage fixtures lulus tanpa survivorship/future leakage.

### Stage 7 — calendar, session, and temporal trading status

Work order: `W06`.

Documents:

- `Market_Calendar_Requirements_Contract.md`
- `Trading_Status_Source_Contract_LOCKED.md`

Required implementation outcome: requested/latest expected completed session, board/session, holiday, suspension/status event, effective/known time, unknown state, dan expected-bar policy berasal dari governed evidence.

Exit gate: unknown tidak menjadi holiday/normal; current status tidak bocor ke historical date; long suspension tidak diubah menjadi dormancy exclusion.

### Stage 8 — import-only and canonical RAW

Work order: `W09`.

Documents:

- `Import_Promote_Separation_Contract.md`
- `Canonicalization_Contract_EOD_Bars.md`
- `EOD_Bars_Contract.md`
- `Invalid_Bar_Storage_Policy_LOCKED.md`

Required implementation outcome: deterministic mapping/dedup/conflict winner, positive OHLC invariants, volume/unit/date validation, invalid-versus-missing separation, observation/config lineage, candidate storage, dan import tidak melakukan indicator/seal/pointer switch.

Exit gate: zero placeholder, provider `adj_close` sebagai RAW close, direct publish, dan untraceable row tidak mungkin masuk canonical readable path.

### Stage 9 — immutable publication, correction, seal, and pointer lifecycle

Work order: `W10`; cross-cutting `W19` and `W21`.

Documents:

- `Canonical_Row_History_and_Versioning_Policy_LOCKED.md`
- `EOD_Data_Retention_and_History_Rewrite_Policy_LOCKED.md`
- `Publication_Traceability_Immutability_Lineage_LOCKED.md`
- `Publication_Manifest_Contract_LOCKED.md`
- `Dataset_Seal_and_Freeze_Contract_LOCKED.md`
- `Publication_Current_Pointer_Integrity_Contract_LOCKED.md`
- `Publication_Lock_And_Replacement_Policy_LOCKED.md`
- `Finalize_Lock_And_Pointer_Behavior_LOCKED.md`
- `EOD_Cutoff_and_Finalization_Contract_LOCKED.md`
- `Historical_Correction_and_Reseal_Contract_LOCKED.md`
- `Correction_Lifecycle_Safety_Contract.md`
- `Force_Replace_Operator_Control_Policy_LOCKED.md`
- `Publishability_State_Integrity_Contract_LOCKED.md`
- `Publishability_Coverage_Fallback_Cross_Consistency_Contract_LOCKED.md`
- `../ops/Historical_Correction_Runbook_LOCKED.md`
- `../ops/History_Table_Immutability_Guards_LOCKED.sql`

Required implementation outcome: immutable candidate/publication/artifact/history, explicit state machine, cutoff/finalization, manifest/seal, atomic pointer switch, correction/supersession/rollback lineage, fail-safe force controls, dan no in-place rewrite.

Exit gate: failed build/reseal/correction tidak mengubah current pointer; prior publication tetap repeatable; concurrent read melihat tepat satu publication.

### Stage 10 — corporate-action event and factor lifecycle

Work order: `W11`.

Documents:

- `Corporate_Action_and_Adjustment_Policy.md`
- `Corporate_Action_Impact_Flags_Contract.md`
- `../registry/Corporate_Action_Type_Registry_LOCKED.md`
- `../registry/Price_Scale_Break_Detection_LOCKED.md`
- `../registry/Exchange_Market_Structure_Facts_LOCKED.md`

Required implementation outcome: source-backed event identity/revision, verification hierarchy, effective/known/ex/cum/record/payment dates, factor candidate/verified state, contamination window, and anomaly-only detector. Exchange price band, minimum price, dan tick ladder diresolusi dari tiered effective-dated rows, bukan konstanta dalam kode.

Exit gate: price jump/proximity/provider adjusted field tidak dapat membuat verified action/factor atau mengubah history. Tidak ada keputusan yang mencapai published output memakai band/floor/tick tanpa sumber dan effective date.

### Stage 11 — coherent analytical price products

Work order: `W12`.

Documents:

- `Corporate_Action_and_Adjustment_Policy_Selected_Defaults_LOCKED.md`
- `../registry/Price_Adjustment_Contract_LOCKED.md`

Required implementation outcome: immutable `RAW`, factor-bound coherent `STRUCTURAL_ADJUSTED` OHLC plus inverse volume where required, separate `TOTAL_RETURN`, explicit revision/basis/precision/null behavior.

Exit gate: one run/field vector cannot mix `close`, provider `adj_close`, factor sets, or RAW/adjusted scales; unresolved factor contaminates/nulls rather than falls back.

### Stage 12 — temporal coverage expectation and delivery

Work order: `W15`.

Documents:

- `Coverage_Universe_Definition_LOCKED.md`
- `Coverage_Gate_Enforcement_Contract_LOCKED.md`
- `EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`
- `Coverage_Edge_Cases_Contract_LOCKED.md`

Required implementation outcome: point-in-time numerator/denominator, expectation/delivery evidence, 98% prerequisite, unknown state, bounded missing reasons, and independent quality/provenance/product/seal gates.

Exit gate: provider absence, dormancy, zero volume, illiquidity, current active state, or missing status cannot silently improve coverage.

### Stage 13 — explainable data usability

Work order: `W16`.

Documents:

- `EOD_Eligibility_Snapshot_Contract_LOCKED.md`
- `Eligibility_Partial_Data_Behavior_LOCKED.md`

Cross-cutting prerequisite/input:

- `Sector_Classification_Contract_LOCKED.md` — primary prerequisite closure berada di Stage 6/`W05`; Stage 13 hanya mempersist/mengekspose sector-reference state yang sudah point-in-time dan publication-bound bila read product membutuhkannya.

Required implementation outcome: one publication-bound row per temporal listing with separately persisted expectation, delivery, quality, metric validity, status, event/contamination, indicator state, sector-reference state bila relevan, data-usability decision, and complete ordered reasons.

Exit gate: blocked row tidak hilang; true tidak berarti tradable/selected; liquidity/event preference watchlist tidak masuk upstream decision; missing/unknown sector tidak diganti current membership dan hanya men-null-kan dependent sector-relative fields sesuai owner indicator contract.

### Stage 14 — actual and proxy daily market metrics

Work order: `W13`.

Documents:

- `Market_Daily_Metrics_Contract.md`
- `../registry/Volume_and_Turnover_Normalization_LOCKED.md`

Required implementation outcome: source-backed traded value/trade count/frequency bila tersedia; explicit `RAW close × RAW volume` proxy; unit/basis/window/completeness/null/reason lineage terpisah.

Exit gate: actual dan proxy tidak dapat berbagi misleading name/meaning; adjusted price × raw volume tidak dipakai sebagai proxy.

### Stage 15 — deterministic indicators and dependency graph

Work order: `W14`.

Documents:

- `EOD_Indicators_Contract.md`
- `Indicator_Nullability_And_OHLCV_Gap_Contract.md`
- `Indicator_Recompute_Source_Scope_Contract.md`
- `Current_Indicator_Recompute_Command_Contract.md`
- `../indicators/EOD_Indicators_Formula_Spec.md`
- `../indicators/Indicator_Computation_Specification.md`
- `../registry/Indicator_Registry_Baseline_LOCKED.md`

Required implementation outcome: exact formula/window/endpoint/seed/recursive state/warm-up/gap/null/precision/rounding/dependency graph, one coherent product basis, immutable indicator publication, and bounded recompute command.

Exit gate: independent short/long/gap/action/correction oracles lulus; ATR state stabil sejak dataset/listing boundary dan correction impact tidak dipotong secara salah.

### Stage 16 — immutable config, reasons, hashes, and determinism

Work orders: foundation `W04`; closure `W21`.

Documents:

- `Audit_Hash_and_Reproducibility_Contract_LOCKED.md`
- `Hash_Number_Formatting_LOCKED.md`
- `Determinism_Invariants_LOCKED.md`
- `../registry/Platform_Config_Registry_LOCKED.md`
- `../registry/Reason_Codes_Registry.md`
- `../registry/Reason_Codes_Seed.sql`
- `../ops/Config_Change_Protocol_LOCKED.md`

Required implementation outcome: canonical typed config snapshot, output-affecting key completeness, reason registry governance, canonical serialization, observation/artifact/publication manifest hashes, source/temporal/factor/product/formula/read-model identity, and as-known config resolution.

Exit gate: run/artifacts/publication/manifest/seal/replay bind identical non-null config identity; one semantic change changes the correct identity/hash; secret remains redacted.

### Stage 17 — versioned market-data read product and optional snapshot boundary

Work order: core `W17`; optional `W20`.

Documents:

- `Downstream_Consumer_Read_Model_Contract_LOCKED.md`
- `Downstream_Data_Readiness_Guarantee_LOCKED.md`
- `CONSUMER_READ_CONTRACT_LOCKED.md`
- `Consumer_Readability_Decision_Table_LOCKED.md`
- `Read_Side_Enforcement_Anti_Bypass_Contract_LOCKED.md`
- `Effective_Trade_Date_Contract_LOCKED.md`
- `Run_Status_and_Quality_Gates_LOCKED.md`
- `../session_snapshot/Session_Snapshot_Contract_LOCKED.md`
- `../session_snapshot/Session_Snapshot_Date_Alignment_with_Effective_Date_LOCKED.md`
- `../session_snapshot/Session_Snapshot_Retention_Defaults_LOCKED.md`
- `../session_snapshot/Session_Snapshot_Scope_Selection_and_Dependencies_LOCKED.md`
- `../session_snapshot/Snapshot_Slot_Tolerances_and_Session_Rules_LOCKED.md`

Required implementation outcome: atomic publication-bound DTO/gateway, requested/effective dates, readiness/freshness/fallback, identity/RAW/product/indicator/data-usability/lineage fields, anti-bypass permissions, and explicit snapshot feature state.

Exit gate: no raw/current/master/`MAX(date)`/mixed-publication read; optional snapshot cannot become strategy engine and, when disabled, does not create an implied missing feature.

### Stage 18 — exact and as-known replay

Work order: `W18`.

Documents:

- `Replay_Verification_Contract_LOCKED.md`
- `../backtest/Point_In_Time_Backtest_Input_Contract_LOCKED.md`
- `../backtest/Historical_Replay_and_Data_Quality_Backtest.md`
- `../backtest/Backtest_Metrics_and_Acceptance_Criteria_LOCKED.md`
- `../backtest/Replay_Results_Schema_MariaDB.sql`

Required implementation outcome: exact-publication reproduction, as-known knowledge cutoff, frozen revision/config/factor/formula/product/read-model identity, mismatch paths, evidence schema, and anti-survivorship/future-leak protection.

Exit gate: exact replay matches values/nulls/reasons/lineage/hashes; as-known replay cannot see later identity/status/event/config/factor revisions; no strategy P&L metric menjadi market-data acceptance.

### Stage 19 — operational lifecycle, commands, observability, and evidence

Work order: `W19`; optional snapshot command follows `W20`.

Normative operational documents:

- `../ops/Archived_Actual_Execution_Evidence_Contract_LOCKED.md`
- `../ops/Audit_Evidence_Pack_Contract_LOCKED.md`
- `../ops/Audit_Query_Cookbook_LOCKED.md`
- `../ops/Bootstrap_and_Backfill_Runbook_LOCKED.md`
- `../ops/Commands_and_Runbook_LOCKED.md`
- `../ops/Daily_Pipeline_Execution_and_Sealing_Runbook_LOCKED.md`
- `../ops/Executed_Run_Admission_Criteria_LOCKED.md`
- `../ops/Incident_Classification_and_Response_Matrix_LOCKED.md`
- `../ops/Locking_Implementation_Standard_LOCKED.md`
- `../ops/Logging_Schema_JSON_LOCKED.md`
- `../ops/Observability_Minimum_Contract_LOCKED.md`
- `../ops/Operator_Decision_Trees_LOCKED.md`
- `../ops/Performance_SLO_and_Limits_LOCKED.md`
- `../ops/Release_Gates_LOCKED.md`
- `../ops/Resumable_Backfill_Contract_LOCKED.md`
- `../ops/Run_Artifacts_Format_LOCKED.md`
- `../ops/Run_Execution_Evidence_Pack_Contract_LOCKED.md`
- `../ops/Run_Ownership_and_Recovery_LOCKED.md`
- `../ops/Scheduling_and_Locking_Contract_LOCKED.md`

Operational command/implementation companions that must conform:

- `../ops/IMPLEMENTATION_GUIDE.md`
- `../ops/OPERATIONAL_RUNBOOK.md`
- `../ops/OPS_ENVIRONMENT_BASELINE.md`
- `../ops/commands/README.md`
- `../ops/commands/01_DAILY_PIPELINE.md`
- `../ops/commands/02_IMPORT_DATA.md`
- `../ops/commands/03_PROCESS_DATASET.md`
- `../ops/commands/04_FINALIZE_AND_PUBLISH.md`
- `../ops/commands/05_BACKFILL.md`
- `../ops/commands/06_CORRECTION.md`
- `../ops/commands/07_REPLAY_AND_EVIDENCE.md`
- `../ops/commands/08_SESSION_SNAPSHOT.md`

Audit/support inventories assigned to operational closure, not behavioral authority:

- `../ops/COMMAND_SURFACE_SAFETY_INVENTORY.md`
- `../ops/LOGGING_TRACEABILITY_REASON_CODES_INVENTORY.md`
- `../ops/RUN_PUBLICATION_POINTER_LINKAGE_INVENTORY.md`

Required implementation outcome: safe command ownership, import/promote/correction/replay/backfill flow, locks/idempotency/resume, structured logs/reasons/metrics/alerts, incident/recovery, evidence export/admission, release gates, and pre/post-activation SLO behavior.

Exit gate: every command has success/failure/concurrency/retry proof; operator cannot bypass publication safety; development frontier is not misreported as activated freshness.

### Stage 20 — global schema and migration convergence

Work orders: additive foundation `W03`; per-feature migrations `W04`–`W20`; global closure `W21`.

Documents:

- `../db/README.md`
- `../db/Database_Schema_Contracts_MariaDB.md`
- `../db/Database_Schema_MariaDB.sql`
- `../db/DB_FIELDS_AND_METADATA.md`
- `../db/DB_Schema_And_Migration_Sync_Contract_LOCKED.md`
- `../db/Indices_and_Constraints_Contract_LOCKED.md`
- `../db/MARKET_DATA_DICTIONARY.md`
- `../db/Migration_Policy_LOCKED.md`
- `../db/Schema_Enforcement_Notes_LOCKED.md`
- `../db/EOD_Publications_Table.sql`
- `../db/EOD_Current_Publication_Pointer_Table.sql`
- `../db/Publication_Current_Pointer_Switch_Procedure_LOCKED.sql`
- `../db/Publication_Switch_Procedure_LOCKED.sql`

Required implementation outcome: clean install, forward upgrade, rollback/recovery decision, idempotent backfill, temporal/immutable constraints, indexes/FKs or documented implicit integrity, stored procedures where applicable, production repositories, test mirror parity, and dictionary synchronization.

Exit gate: no required semantic field remains nullable/unwritten without reason; base SQL + migrations equal supported runtime shape; clean DB and upgraded DB pass the same semantic suite.

### Stage 21 — semantic proof implementation and closure

Work orders: harness `W03`; tests per stage; global closure `W21`.

Normative proof documents:

- `../tests/README.md`
- `../tests/Contract_Test_Matrix_LOCKED.md`
- `../tests/Contract_Tests_Specification.md`
- `../tests/Golden_Fixture_Catalog_LOCKED.md`
- `../tests/Negative_Test_Catalog_LOCKED.md`
- `../tests/Golden_Fixtures_Specification.md`
- `../tests/Golden_Fixture_Examples_LOCKED.md`
- `../tests/Indicator_Test_Vectors_LOCKED.md`
- `../tests/Indicator_Expected_Output_Oracle_LOCKED.md`
- `../tests/Fixture_Package_Manifest_LOCKED.md`
- `../tests/Test_Implementation_Guidance_LOCKED.md`
- `../tests/Executed_Proof_Admission_Criteria_LOCKED.md`
- `../tests/Test_Coverage_Closure_Contract_LOCKED.md`

Historical/narrow companions that must be classified, not accepted as closure by themselves:

- `../tests/Behavioral_Test_Coverage_Inventory.md`
- `../tests/Db_Integrity_Constraint_Inventory.md`
- `../tests/Correction_Lifecycle_Safety_Test_Matrix.md`
- `../tests/PHPUNIT_TEST_MATRIX.md`

Required implementation outcome: positive/negative/golden/failure/correction/concurrency/replay/migration proof through actual production paths, independent expected oracle, fixture manifests, exact environment/build/DB/config identity, and supported full-suite pass.

Exit gate: every P0/P1 invariant is `PROVEN`; no test expects provider-adjusted fallback, direct repair, synthetic verified factors, current-active historical filtering, dormancy denominator exclusion, sliding ATR reseed, or other superseded behavior.

### Stage 22 — audit, documentation synchronization, activation, and relock

Work order: `W22`.

Sequencing/traceability anchors:

- `Market_Data_Strategy_Implementation_Blueprint_LOCKED.md`
- `Market_Data_Implementation_Conformance_Matrix_LOCKED.md`
- `Market_Data_Implementation_Command_Protocol_LOCKED.md`
- `INDEX.md`
- `../system/README.md`
- `../system/SYSTEM_ARTIFACT_MAP.md`
- `../system/SYSTEM_BOUNDARY.md`
- `../system/SYSTEM_CONTEXT_AND_DEPENDENCIES.md`
- `../system/SYSTEM_DATA_PRODUCT_MAP.md`
- `../system/SYSTEM_OVERVIEW.md`
- `../system/SYSTEM_OWNERSHIP_MAP.md`
- `../system/SYSTEM_PUBLICATION_AND_CORRECTION_OVERVIEW.md`
- `../system/SYSTEM_READ_ORDER.md`
- `../system/SYSTEM_RUNTIME_FLOW.md`

Audit framework assigned here:

- `../audit/README.md`
- `../audit/AUDIT_BASELINE.md`
- `../audit/AUDIT_CLAIM_CONTROL.md`
- `../audit/AUDIT_DOMAIN_BOUNDARY.md`
- `../audit/AUDIT_EVIDENCE_STRENGTH_MODEL.md`
- `../audit/AUDIT_LAYER_CLASSIFICATION_RULES.md`
- `../audit/AUDIT_METHOD.md`
- `../audit/AUDIT_NORMATIVE_VS_COMPANION_RULES.md`
- `../audit/AUDIT_SCOPE_AND_EXCLUSIONS.md`
- `../audit/AUDIT_SCORING_AND_VERDICT.md`
- `../audit/AUDIT_TRACEABILITY_RULES.md`
- `../audit/AUDIT_UPDATE_GOVERNANCE.md`
- `../audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md`
- `../audit/reports/README.md`
- `../audit/reports/AUDIT_FINAL_STATE.md`

All `audit/*INVENTORY*.md`, trackers, status files, proof packs, templates, checklists, and `audit/histories/` are evaluation/evidence companions. They are reviewed and updated or explicitly retained as historical, but never override owner contracts.

Required implementation outcome: independent code/schema/config/test/runtime audit, finding closure, full traceability, docs synchronization, accurate claim level, pre-activation catch-up, activated-session evidence when applicable, and rollback of any stale production-ready wording.

Exit gate:

- `IMPLEMENTATION_CONFORMANT` requires no material P0/P1 implementation gap and complete current evidence;
- `OPERATIONALLY_VALIDATED` requires activated operational proof appropriate to the declared state;
- production relock requires both plus claim governance;
- watchlist implementation/performance and paid-provider purchase remain outside the gate.

## Non-authoritative folder disposition

The following folders are intentionally not implementation strategy owners:

- `../examples/`: illustrative shapes only; may support Stage 21 understanding but never prove runtime execution.
- `../evidence/`: archived actual evidence only when admission metadata is genuine; consumed by Stages 19, 21, and 22.
- `../patches/`: historical/proposed patches; no item is implemented merely because it exists. Promote its final meaning into an owner contract first or mark it historical/superseded.
- `../audit/histories/`: dated state snapshots only.

An unassigned new file under `book/`, `db/`, `registry/`, `indicators/`, `session_snapshot/`, `ops/`, `tests/`, or `backtest/` is a documentation synchronization failure until this matrix assigns or explicitly classifies it.

## Pull-request and release usage

Every implementation pull request must state:

1. `Wxx` work order and contract-area IDs;
2. exact documents fulfilled;
3. schema/config/runtime/backfill impact;
4. tests added/replaced and superseded tests removed;
5. runbook/evidence changes;
6. ledger rows advanced and why;
7. remaining open rows/dependencies.

A release candidate must aggregate all ledger rows, not only changed files. One missing owner assignment, unproven P0/P1 invariant, or hidden legacy bypass blocks relock.

## Change control

When a strategy document is added, removed, renamed, or changes meaning:

1. update its owner contract;
2. assign/update contract area and work order here;
3. update blueprint dependency/order if necessary;
4. update schema/config/test/ops/evidence impact;
5. invalidate prior conformance for affected rows until re-proven.

This rule keeps the build order complete as documentation evolves, without turning audit/history/example material into behavioral authority.
