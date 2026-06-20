# Watchlist Audit Update Governance

## Purpose

Dokumen ini mengunci cara update audit, status, dan contract tracker untuk domain `watchlist` agar pengembangan tidak berjalan tanpa jejak, tidak overclaim, dan tetap tunduk pada owner docs yang benar.

Dokumen ini adalah governance audit/update. Dokumen ini **bukan** owner business rule watchlist. Behavioral owner tetap berada pada `docs/watchlist/system/policy.md` dan dokumen normatif `docs/watchlist/system/policies/weekly_swing/**`.

## Source of Truth Rule

- ZIP terbaru yang diberikan operator adalah source of truth sesi.
- Semua keputusan update harus membaca file aktual di ZIP, bukan asumsi dari sesi sebelumnya.
- Jika file target sudah ada, baca dulu lalu update append-only kecuali koreksi minor diperlukan untuk menghapus kontradiksi eksplisit.
- Dokumen audit tidak boleh menggantikan policy owner.
- Dokumen system/policy tetap menjadi behavioral owner.
- Dokumen `LUMEN_IMPLEMENTATION_STATUS.md` hanya mencatat progress, bukan membuat kontrak baru tanpa referensi.
- Dokumen `LUMEN_CONTRACT_TRACKER.md` mengunci kontrak berdasarkan policy/system docs dan dependency upstream yang sah.

## Append-Only Update Rule

Setiap sesi baru harus menambahkan catatan baru dengan:

- nama sesi;
- tanggal update;
- status sesi;
- file dibuat/diubah;
- bukti validasi;
- gap tersisa;
- next session recommendation.

Update boleh mengubah ringkasan paling atas hanya untuk mencerminkan status terbaru, tetapi riwayat sesi lama tidak boleh dihapus.

## Active Session Rule

Harus selalu ada satu section `ACTIVE SESSION` pada:

- `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md`;
- `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md`.

Nama active session pada kedua file harus sama. Jika tidak sama, docs sync dianggap gagal.

## Status Taxonomy

| Status | Meaning |
|---|---|
| `NOT_STARTED` | Belum ada implementasi, test, atau runtime proof. |
| `IN_PROGRESS` | Sedang dikerjakan dan belum selesai. |
| `PARTIAL` | Sebagian terpenuhi tetapi masih ada gap substantif. |
| `REVIEW_REQUIRED` | Perlu review ulang karena ada risiko, konflik, atau bukti belum cukup. |
| `BLOCKED` | Tidak bisa dilanjutkan karena dependency, file, runtime, atau keputusan belum tersedia. |
| `DONE` | Scope sesi spesifik selesai sesuai acceptance criteria. Bukan berarti seluruh watchlist siap produksi. |
| `LOCKED` | Kontrak sudah dikunci oleh code, test, runtime proof, artifact, dan docs sync. |
| `SUPERSEDED` | Digantikan oleh kontrak atau sesi yang lebih baru. |
| `FOUNDATION_STARTED` | Baseline dokumen/tracker dibuat, tetapi implementasi utama belum dimulai. |
| `NOT_IMPLEMENTED` | Fitur utama belum ada pada codebase. |
| `NOT_READY` | Belum layak diklaim siap produksi. |

## Severity Taxonomy

| Severity | Meaning |
|---|---|
| `BLOCKER` | Menghalangi klaim readiness atau next implementation. |
| `HIGH_RISK` | Bisa menyebabkan behavior salah, data salah, atau kontrak terbypass. |
| `MEDIUM_RISK` | Risiko nyata tetapi tidak langsung merusak invariant utama. |
| `MINOR` | Perbaikan kecil, wording, atau struktur non-kritis. |
| `DOCS_ONLY` | Perubahan dokumentasi tanpa perubahan runtime. |
| `RUNTIME_PROOF_MISSING` | Code/test mungkin ada, tetapi bukti runtime belum tersedia. |
| `ACCEPTABLE_LIMITATION` | Batasan sadar yang boleh diterima dan harus dicatat. |
| `EXTERNAL_DEPENDENCY` | Bergantung pada provider, upstream, environment, atau keputusan di luar modul watchlist. |

## Evidence Rule

Setiap klaim status wajib punya evidence yang jelas:

- file path aktual;
- ringkasan perubahan;
- test command dan hasil;
- runtime command dan hasil jika sudah ada runtime;
- artifact/log path jika ada;
- alasan jika validasi hanya static/docs.

Tanpa evidence, klaim maksimal hanya `PARTIAL` atau `REVIEW_REQUIRED`.

## Code / Test / Docs / Runtime Proof Rule

- Docs selesai tidak sama dengan fitur selesai.
- Test static guard tidak sama dengan runtime proof.
- PHPUnit PASS untuk unit test tidak sama dengan production readiness jika runtime command belum dibuktikan.
- Runtime proof harus berasal dari command/API nyata milik watchlist saat fitur tersebut sudah dibuat.
- Jika hanya docs yang berubah, validasi minimal adalah file existence check, grep/check anti-overclaim, dan static guard docs bila tersedia.

## C29 OOS Proof Governance Addendum

C29 is governed as an OOS proof session only.

Source artifact lock:

```text
INPUT_C28_ARTIFACT=storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json
EXPECTED_C28_HASH=64ec3e48fa3c6beb4b1175cc8f0cc277f22d20fd
ACTUAL_C28_HASH=64ec3e48fa3c6beb4b1175cc8f0cc277f22d20fd
CANDIDATE_PROFILE_CODE=C28_G05_BUCKET_TIEBREAK_R09_STABLE_G21_NO_SIGNAL_G16_DELAY
```

Validation command:

```text
watchlist:backtest-c29-oos-proof
```

Output artifact:

```text
storage/app/watchlist/backtest/c29-oos-proof-c28-g05.json
artifact_hash=c02add8f2cc8af53bdb3f0cf9d0c7d90d63e1dd9
status=C29_OOS_PROOF_FAILED
```

Final C29 evidence rules:

- C29 may claim OOS failure because the operator ran the runtime command against the locked C28 artifact and produced the C29 artifact.
- C29 may not claim production readiness because the runtime result is `C29_OOS_PROOF_FAILED` and `production_ready=0`.
- C29 may not use OOS metrics to retune, reselect a profile, or create a best-of-OOS binding.
- C29 may not create or promote a production catalog.
- C29 may not mutate C01-C28, R1/R2, or PLAN/CONFIRM behavior.
- `production_ready` must remain `false/0` in source, artifact, docs, and final response.
- The four C29 rows counted by the lookahead gate are documented as missing D1-D5 raw OHLC path rows; they must not be overclaimed as proven future-return selection leakage unless C30 proves it.

Operator validation evidence:

```text
PHPUNIT_C29=PASS: OK (13 tests, 132 assertions)
FULL_WATCHLIST_PHPUNIT=PASS: OK (448 tests, 10900 assertions)
C29_RUNTIME=FAIL: status=C29_OOS_PROOF_FAILED
C29_ARTIFACT_HASH=c02add8f2cc8af53bdb3f0cf9d0c7d90d63e1dd9
```

C30 governance requirement:

```text
NEXT_STEP=C30_OOS_FAILURE_ATTRIBUTION_AND_DATA_COMPLETENESS_DIAGNOSTIC
DO_NOT_TUNE_FROM_OOS=true
DO_NOT_CREATE_BEST_OF_OOS=true
DO_NOT_PROMOTE_PRODUCTION_CATALOG=true
SPLIT_ACTUAL_LOOKAHEAD_LEAK_FROM_MISSING_PATH_ROWS=true
SPLIT_EVALUABLE_FROM_NON_EVALUABLE_ROWS=true
```

Operator validation rule:

```text
PHPUNIT_C29_PASS_REQUIRES_ACTUAL_COMMAND_OUTPUT=true
FULL_WATCHLIST_PASS_REQUIRES_ACTUAL_COMMAND_OUTPUT=true
C29_RUNTIME_PASS_REQUIRES_ACTUAL_ARTIFACT=true
C29_RUNTIME_FAIL_REQUIRES_ACTUAL_ARTIFACT=true
C29_RUNTIME_BLOCKED_REQUIRES_EXPLICIT_DIAGNOSTIC=true
```

## C30 OOS Failure Attribution Governance Addendum

C30 is governed as failure attribution only. It must not retune, reselect, promote, create best-of-OOS, or change PLAN/CONFIRM production behavior.

Source artifact lock:

```text
INPUT_C29_ARTIFACT=storage/app/watchlist/backtest/c29-oos-proof-c28-g05.json
EXPECTED_C29_HASH=c02add8f2cc8af53bdb3f0cf9d0c7d90d63e1dd9
EXPECTED_C29_STATUS=C29_OOS_PROOF_FAILED
```

Validation command:

```text
watchlist:backtest-c30-oos-failure-attribution
```

Output artifact:

```text
storage/app/watchlist/backtest/c30-oos-failure-attribution.json
```

Evidence rules:

- C30 may claim completed attribution only from the official C30 command output or a committed artifact generated by that command.
- C30 may not claim PHPUnit PASS unless the C30 PHPUnit command is actually run.
- C30 may not claim full Watchlist PHPUnit PASS unless the full command is actually run.
- C30 may not infer artifact hash; hash must be read from the created C30 artifact or file hash command.
- C30 may not use C29 OOS returns to tune or select a profile.
- `production_ready` must remain `false/0` in source, artifact, docs, and final response.

Operator validation rule:

```text
PHPUNIT_C30_PASS_REQUIRES_ACTUAL_COMMAND_OUTPUT=true
FULL_WATCHLIST_PASS_REQUIRES_ACTUAL_COMMAND_OUTPUT=true
C30_RUNTIME_COMPLETED_REQUIRES_ACTUAL_ARTIFACT=true
C30_RUNTIME_BLOCKED_REQUIRES_EXPLICIT_DIAGNOSTIC=true
```

Missing-path vs actual lookahead classification rule:

```text
MISSING_PATH_ROWS=missing_path_data_flag=true OR raw_ohlc_validated_flag=false OR missing_path_reason_code is not null
SELECTION_LEAK_ROWS=future_path_price_used_for_selection=true OR profile_ret_net_used_for_selection=true OR derived_mfe_mae_used_for_execution=true
ACTUAL_LOOKAHEAD_ROWS=lookahead_safe=false AND NOT missing_path OR explicit future-data leak reason
MISSING_PATH_ROWS_MUST_NOT_BE_OVERCLAIMED_AS_ACTUAL_LOOKAHEAD_LEAK=true
```

C30 final operator status:

```text
PHPUNIT_C30=PASS
PHPUNIT_C30_RESULT=OK (16 tests, 104 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (464 tests, 11004 assertions)
C30_RUNTIME=COMPLETED
C30_FINAL_STATUS=C30_ATTRIBUTION_COMPLETED
C30_ARTIFACT_PATH=storage/app/watchlist/backtest/c30-oos-failure-attribution.json
C30_ARTIFACT_HASH=667b639951d6b566cc9b0fa6cf7dc278db92a8f0
C30_ATTRIBUTION_VERDICT=MIXED_DATA_AND_STRATEGY_FAILURE
ACTUAL_LOOKAHEAD_VIOLATION_COUNT=0
SELECTION_LEAK_COUNT=0
MISSING_PATH_COUNT=4
NON_EVALUABLE_PICK_COUNT=4
CLEAN_EVALUABLE_PICK_COUNT=128
production_ready=0
```

C30 governance decision: C29's reported lookahead violations must be treated as missing-path/data-completeness rows unless a later controlled proof shows an explicit actual leak. C31 must split lookahead gate from data-completeness gate and must not tune from OOS.


## C31 Controlled Gate Reclassification Governance Addendum

C31 is governed as controlled gate reclassification only. It must not retune, reselect, promote, create best-of-OOS, create a production catalog, or change PLAN/CONFIRM production behavior.

Source artifact lock:

```text
INPUT_C29_ARTIFACT=storage/app/watchlist/backtest/c29-oos-proof-c28-g05.json
EXPECTED_C29_HASH=c02add8f2cc8af53bdb3f0cf9d0c7d90d63e1dd9
EXPECTED_C29_STATUS=C29_OOS_PROOF_FAILED

INPUT_C30_ARTIFACT=storage/app/watchlist/backtest/c30-oos-failure-attribution.json
EXPECTED_C30_HASH=667b639951d6b566cc9b0fa6cf7dc278db92a8f0
EXPECTED_C30_STATUS=C30_ATTRIBUTION_COMPLETED
```

Validation command:

```text
watchlist:backtest-c31-controlled-gate-reclassification
```

Output artifact:

```text
storage/app/watchlist/backtest/c31-controlled-gate-reclassification.json
```

Evidence rules:

- C31 may claim completed reclassification only from the official C31 command output or an artifact generated by that command.
- C31 may not claim PHPUnit PASS unless the C31 PHPUnit command is actually run.
- C31 may not claim full Watchlist PHPUnit PASS unless the full command is actually run.
- C31 may not infer artifact hash; hash must be read from the created C31 artifact or file hash command.
- C31 may not use C29/C30 OOS returns to tune or select a profile.
- C31 may not mutate C01-C30 artifacts.
- `production_ready` must remain `false/0` in source, artifact, docs, and final response.

Operator validation rule:

```text
PHPUNIT_C31_PASS_REQUIRES_ACTUAL_COMMAND_OUTPUT=true
FULL_WATCHLIST_PASS_REQUIRES_ACTUAL_COMMAND_OUTPUT=true
C31_RUNTIME_COMPLETED_REQUIRES_ACTUAL_ARTIFACT=true
C31_RUNTIME_BLOCKED_REQUIRES_EXPLICIT_DIAGNOSTIC=true
```

Missing-path vs actual lookahead classification rule:

```text
REPORTED_LOOKAHEAD_GATE_SOURCE=C29 reported lookahead count
ACTUAL_LOOKAHEAD_GATE_SOURCE=C30 actual_lookahead_violation_count
SELECTION_LEAK_GATE_SOURCE=C30 selection_leak_count
DATA_COMPLETENESS_GATE_SOURCE=C30 missing_path_count and non_evaluable_pick_count
MISSING_PATH_ROWS_MUST_NOT_BE_OVERCLAIMED_AS_ACTUAL_LOOKAHEAD_LEAK=true
```

Separated gate model rule:

```text
reported_lookahead_gate=FAIL if reported_lookahead_violation_count > 0
actual_lookahead_gate=PASS if actual_lookahead_violation_count == 0
selection_leak_gate=PASS if selection_leak_count == 0
data_completeness_gate=FAIL if missing_path_count > 0 or non_evaluable_pick_count > 0
month_win_rate_gate=FAIL if source month_win_rate_min == 0
clean_month_win_rate_gate=FAIL if clean_month_win_rate_min == 0
overall_controlled_oos_gate=FAIL if any required controlled gate fails
```

No production-readiness rule:

```text
C31_CAN_RECLASSIFY_GATE_FAILURE=true
C31_CAN_NOT_DECLARE_OOS_PASS=true
C31_CAN_NOT_PROMOTE_PRODUCTION=true
production_ready=0
```

C31 final operator status:

```text
PHPUNIT_C31=PASS
PHPUNIT_C31_RESULT=OK (14 tests, 126 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (478 tests, 11130 assertions)
C31_RUNTIME=COMPLETED
C31_FINAL_STATUS=C31_CONTROLLED_GATE_RECLASSIFICATION_COMPLETED
C31_ARTIFACT_PATH=storage/app/watchlist/backtest/c31-controlled-gate-reclassification.json
C31_ARTIFACT_HASH=4c6203621ed53ade368328a3aad567cbfc12f3a0
C31_FILE_SHA1=B9EC57659113EFED3B99E9DC22235E44398A5DA2
RECLASSIFICATION_CONCLUSION=C31_RECLASSIFICATION_CONFIRMED_MISSING_PATH_NOT_LOOKAHEAD_LEAK
CONTROLLED_PROOF_STATUS=C31_CONTROLLED_OOS_PROOF_FAILED_DATA_COMPLETENESS_AND_ROBUSTNESS
ACTUAL_LOOKAHEAD_GATE=PASS
SELECTION_LEAK_GATE=PASS
DATA_COMPLETENESS_GATE=FAIL
MONTH_WIN_RATE_GATE=FAIL
CLEAN_MONTH_WIN_RATE_GATE=FAIL
production_ready=0
```

C31 governance decision: C29's reported lookahead failure is reclassified as missing-path/data-completeness failure, not actual lookahead leakage. The controlled proof still fails because data completeness and bad-month robustness gates fail. The next step must split data-path remediation proof from bad-month robustness diagnostic and must not tune from OOS.


## C32 Data Path And Bad Month Diagnostic Governance Addendum

C32 is governed as data-path and bad-month diagnostic only. It must not retune, reselect, promote, create best-of-OOS, create a production catalog, or change PLAN/CONFIRM production behavior.

Source artifact lock:

```text
INPUT_C31_ARTIFACT=storage/app/watchlist/backtest/c31-controlled-gate-reclassification.json
EXPECTED_C31_HASH=4c6203621ed53ade368328a3aad567cbfc12f3a0
EXPECTED_C31_STATUS=C31_CONTROLLED_GATE_RECLASSIFICATION_COMPLETED
EXPECTED_C31_CONCLUSION=C31_RECLASSIFICATION_CONFIRMED_MISSING_PATH_NOT_LOOKAHEAD_LEAK
EXPECTED_C31_PROOF_STATUS=C31_CONTROLLED_OOS_PROOF_FAILED_DATA_COMPLETENESS_AND_ROBUSTNESS
```

Validation command:

```text
watchlist:backtest-c32-data-path-and-bad-month-diagnostic
```

Output artifact:

```text
storage/app/watchlist/backtest/c32-data-path-and-bad-month-diagnostic.json
```

Evidence rules:

- C32 may claim completed diagnostic split only from the official C32 command output or an artifact generated by that command.
- C32 may not claim PHPUnit PASS unless the C32 PHPUnit command is actually run.
- C32 may not claim full Watchlist PHPUnit PASS unless the full command is actually run.
- C32 may not infer artifact hash; hash must be read from the created C32 artifact or file hash command.
- C32 may not use C31/C29/C30 OOS returns to tune or select a profile.
- C32 may not mutate C01-C31 artifacts.
- `production_ready` must remain `false/0` in source, artifact, docs, and final response.

Operator validation rule:

```text
PHPUNIT_C32_PASS_REQUIRES_ACTUAL_COMMAND_OUTPUT=true
FULL_WATCHLIST_PASS_REQUIRES_ACTUAL_COMMAND_OUTPUT=true
C32_RUNTIME_COMPLETED_REQUIRES_ACTUAL_ARTIFACT=true
C32_RUNTIME_BLOCKED_REQUIRES_EXPLICIT_DIAGNOSTIC=true
```

Data-path remediation rule:

```text
C32_MUST_REPORT_MISSING_PATH_ROWS=true
C32_MUST_REPORT_AFFECTED_TICKERS=true
C32_MUST_REPORT_AFFECTED_TRADE_AND_ENTRY_DATES=true
C32_MUST_REPORT_REASON_COUNTS=true
C32_CAN_NOT_CLAIM_DATA_COMPLETENESS_PASS_UNTIL_REPLAY_PROOF=true
```

Bad-month robustness rule:

```text
C32_MUST_KEEP_DATA_PATH_AFFECTED_MONTHS_SEPARATE_FROM_CLEAN_ROBUSTNESS_FAILURE=true
C32_MUST_KEEP_DATA_PATH_AFFECTED_BRANCHES_SEPARATE_FROM_CLEAN_BRANCH_ROBUSTNESS_REVIEW=true
C32_CAN_NOT_TUNE_FROM_BAD_MONTH_EVIDENCE=true
```

No production-readiness rule:

```text
C32_CAN_SPLIT_NEXT_WORK=true
C32_CAN_NOT_DECLARE_OOS_PASS=true
C32_CAN_NOT_PROMOTE_PRODUCTION=true
production_ready=0
```

C32 final operator status:

```text
PHPUNIT_C32=PASS
PHPUNIT_C32_RESULT=OK (12 tests, 107 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (490 tests, 11237 assertions)
C32_RUNTIME=COMPLETED
C32_FINAL_STATUS=C32_DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_COMPLETED
C32_ARTIFACT_PATH=storage/app/watchlist/backtest/c32-data-path-and-bad-month-diagnostic.json
C32_ARTIFACT_HASH=4bd92dfcf70dd0b02398d3ecf62d08c0356292ab
C32_FILE_SHA1=49F4A138BEF5B18841119F255F39ACDC2F97445B
DATA_PATH_REMEDIATION_STATUS=C32_DATA_PATH_REMEDIATION_REQUIRED
BAD_MONTH_ROBUSTNESS_STATUS=C32_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED
DIAGNOSTIC_CONCLUSION=C32_SPLIT_CONFIRMED_DATA_PATH_REMEDIATION_AND_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED
NEXT_STEP=C33_DATA_PATH_REPLAY_PROOF_THEN_C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_NO_OOS_TUNING
production_ready=0
```

C32 governance decision: the next work is split into C33 data-path replay proof and C34 bad-month robustness diagnostic. C33/C34 must not tune from OOS, create best-of-OOS, promote a catalog, or set production readiness.


## C33 Data Path Replay Proof Governance Addendum

C33 is governed as data-path replay proof only. It must not retune, reselect, promote, create best-of-OOS, create a production catalog, acquire source data, ingest bars, mutate `eod_bars`, or change PLAN/CONFIRM production behavior.

Source artifact lock:

```text
INPUT_C32_ARTIFACT=storage/app/watchlist/backtest/c32-data-path-and-bad-month-diagnostic.json
EXPECTED_C32_HASH=4bd92dfcf70dd0b02398d3ecf62d08c0356292ab
EXPECTED_C32_STATUS=C32_DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_COMPLETED
EXPECTED_C32_CONCLUSION=C32_SPLIT_CONFIRMED_DATA_PATH_REMEDIATION_AND_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED
EXPECTED_C32_DATA_PATH_STATUS=C32_DATA_PATH_REMEDIATION_REQUIRED
```

Validation command:

```text
watchlist:backtest-c33-data-path-replay-proof
```

Output artifact:

```text
storage/app/watchlist/backtest/c33-data-path-replay-proof.json
```

Evidence rules:

- C33 may claim completed replay proof only from the official C33 command output or an artifact generated by that command.
- C33 may not claim PHPUnit PASS unless the C33 PHPUnit command is actually run.
- C33 may not claim full Watchlist PHPUnit PASS unless the full command is actually run.
- C33 may not infer artifact hash; hash must be read from the created C33 artifact or file hash command.
- C33 may read only the exact C32 replay scope and exact D1-D5 market-calendar dates required for that scope.
- C33 may not acquire provider/source data, ingest bars, write source/master tables, or write `eod_bars`.
- C33 may not use C32/C31/C29/C30 OOS returns to tune or select a profile.
- C33 may not mutate C01-C32 artifacts.
- `production_ready` must remain `false/0` in source, artifact, docs, and final response.

Operator validation rule:

```text
PHPUNIT_C33_PASS_REQUIRES_ACTUAL_COMMAND_OUTPUT=true
FULL_WATCHLIST_PASS_REQUIRES_ACTUAL_COMMAND_OUTPUT=true
C33_RUNTIME_COMPLETED_REQUIRES_ACTUAL_ARTIFACT=true
C33_RUNTIME_BLOCKED_REQUIRES_EXPLICIT_DIAGNOSTIC=true
```

Data-path replay rule:

```text
C33_MUST_REPLAY_C32_MISSING_PATH_ROWS_ONLY=true
C33_MUST_RESOLVE_D1_TO_D5_FROM_MARKET_CALENDAR=true
C33_MUST_REPORT_REQUIRED_PATH_DATES=true
C33_MUST_REPORT_AVAILABLE_MISSING_AND_INVALID_PATH_DATES=true
C33_MUST_REPORT_PUBLICATION_AND_RUN_CONTEXT=true
C33_CAN_NOT_CLAIM_FULL_OOS_PASS=true
```

Read-only proof rule:

```text
NO_SOURCE_ACQUISITION=true
NO_BAR_INGEST=true
NO_SOURCE_MASTER_WRITE=true
NO_EOD_BARS_WRITE=true
READ_ONLY_CURRENT_EOD_BARS_REPLAY_PROOF=true
```

No production-readiness rule:

```text
C33_CAN_CLEAR_DATA_PATH_REPLAY_PROOF=true
C33_CAN_NOT_DECLARE_FULL_CONTROLLED_OOS_PASS=true
C33_CAN_NOT_PROMOTE_PRODUCTION=true
production_ready=0
```

C33 final operator status:

```text
PHPUNIT_C33=PASS
PHPUNIT_C33_RESULT=OK (15 tests, 145 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (505 tests, 11382 assertions)
C33_RUNTIME=COMPLETED
C33_FINAL_STATUS=C33_DATA_PATH_REPLAY_PROOF_COMPLETED
C33_ARTIFACT_PATH=storage/app/watchlist/backtest/c33-data-path-replay-proof.json
C33_ARTIFACT_HASH=84bb77871515643b203de644fd34b4c748d1b2af
C33_FILE_SHA1=1B0558C823732649DC7487154E5045BE86A160CC
DATA_PATH_REPLAY_STATUS=C33_DATA_PATH_REPLAY_PASS
DATA_COMPLETENESS_GATE_AFTER_REPLAY=PASS
REPLAY_PASS_COUNT=4
REPLAY_FAIL_COUNT=0
REPLAY_BLOCKED_COUNT=0
DIAGNOSTIC_CONCLUSION=C33_DATA_PATH_REPLAY_CONFIRMED_D1_TO_D5_RAW_OHLC_AVAILABLE
NEXT_STEP=C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_AFTER_C33_NO_OOS_TUNING
production_ready=0
```

C33 governance decision: the C32 data-path replay blocker is cleared for the four missing D1-D5 raw OHLC rows, but C33 does not declare full controlled OOS pass and does not unlock production readiness. The next work is C34 bad-month robustness diagnostic after clean data evidence, with no OOS tuning, best-of-OOS, catalog promotion, or production readiness.


## C34 Bad Month Robustness Diagnostic Governance Addendum

C34 is governed as bad-month robustness diagnostic only. It must not retune, reselect, promote, create best-of-OOS, create a production catalog, replay market data, query DB state, or change PLAN/CONFIRM production behavior.

Source artifact locks:

```text
INPUT_C33_ARTIFACT=storage/app/watchlist/backtest/c33-data-path-replay-proof.json
EXPECTED_C33_HASH=84bb77871515643b203de644fd34b4c748d1b2af
EXPECTED_C33_STATUS=C33_DATA_PATH_REPLAY_PROOF_COMPLETED
EXPECTED_C33_CONCLUSION=C33_DATA_PATH_REPLAY_CONFIRMED_D1_TO_D5_RAW_OHLC_AVAILABLE
EXPECTED_C33_REPLAY_STATUS=C33_DATA_PATH_REPLAY_PASS

INPUT_C32_ARTIFACT=storage/app/watchlist/backtest/c32-data-path-and-bad-month-diagnostic.json
EXPECTED_C32_HASH=4bd92dfcf70dd0b02398d3ecf62d08c0356292ab
EXPECTED_C32_STATUS=C32_DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_COMPLETED
EXPECTED_C32_BAD_MONTH_STATUS=C32_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED
```

Validation command:

```text
watchlist:backtest-c34-bad-month-robustness-diagnostic
```

Output artifact:

```text
storage/app/watchlist/backtest/c34-bad-month-robustness-diagnostic.json
```

Evidence rules:

- C34 may claim completed bad-month robustness diagnostic only from the official C34 command output or an artifact generated by that command.
- C34 may not claim PHPUnit PASS unless the C34 PHPUnit command is actually run.
- C34 may not claim full Watchlist PHPUnit PASS unless the full command is actually run.
- C34 may not infer artifact hash; hash must be read from the created C34 artifact or file hash command.
- C34 may not query DB state, replay market data, acquire provider/source data, ingest bars, or write `eod_bars`.
- C34 may not use C32/C33/C31/C29/C30 OOS returns to tune or select a profile.
- C34 may not mutate C01-C33 artifacts.
- `production_ready` must remain `false/0` in source, artifact, docs, and final response.

Operator validation rule:

```text
PHPUNIT_C34_PASS_REQUIRES_ACTUAL_COMMAND_OUTPUT=true
FULL_WATCHLIST_PASS_REQUIRES_ACTUAL_COMMAND_OUTPUT=true
C34_RUNTIME_COMPLETED_REQUIRES_ACTUAL_ARTIFACT=true
C34_RUNTIME_BLOCKED_REQUIRES_EXPLICIT_DIAGNOSTIC=true
```

Bad-month robustness rule:

```text
C34_MUST_REQUIRE_C33_DATA_PATH_PASS=true
C34_MUST_USE_C32_BAD_MONTH_SCOPE=true
C34_MUST_REPORT_BAD_MONTH_ROWS=true
C34_MUST_REPORT_BRANCH_ROBUSTNESS_ROWS=true
C34_MUST_KEEP_R09_DATA_PATH_CLEARED_SEPARATE_FROM_G16_G21_ROBUSTNESS_REVIEW=true
C34_CAN_NOT_CLAIM_FULL_OOS_PASS=true
```

No production-readiness rule:

```text
C34_CAN_CONFIRM_ROBUSTNESS_FAILURE=true
C34_CAN_NOT_DECLARE_FULL_CONTROLLED_OOS_PASS=true
C34_CAN_NOT_PROMOTE_PRODUCTION=true
production_ready=0
```

C34 final operator status:

```text
PHPUNIT_C34=PASS
PHPUNIT_C34_RESULT=OK (13 tests, 119 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (518 tests, 11501 assertions)
C34_RUNTIME=COMPLETED
C34_FINAL_STATUS=C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_COMPLETED
C34_ARTIFACT_PATH=storage/app/watchlist/backtest/c34-bad-month-robustness-diagnostic.json
C34_ARTIFACT_HASH=1dcf355095334796c2f4558823a1882e71e3ed30
C34_FILE_SHA1=71897A94B665CAF2C5A632915FE5B48AE99726A2
BAD_MONTH_ROBUSTNESS_STATUS=C34_BAD_MONTH_ROBUSTNESS_FAILURE_CONFIRMED_AFTER_C33_DATA_PATH_PASS
BAD_MONTH_FAILURE_COUNT=3
BRANCH_ROBUSTNESS_FLAG_COUNT=2
STRATEGY_ROBUSTNESS_REDESIGN_REQUIRED=1
DIAGNOSTIC_CONCLUSION=C34_BAD_MONTH_ROBUSTNESS_FAILURE_CONFIRMED_AFTER_C33_DATA_PATH_PASS
NEXT_STEP=C35_IS_ONLY_ROBUSTNESS_REDESIGN_DIAGNOSTIC_NO_OOS_TUNING
production_ready=0
```

C34 governance decision: the data-path blocker is cleared, but clean bad-month/branch robustness failure remains. The next work is C35 IS-only robustness redesign diagnostic, with no OOS tuning, best-of-OOS, catalog promotion, or production readiness.


## Anti-Overclaim Rule

Watchlist tidak boleh diklaim production-ready hanya karena dokumen selesai.

Watchlist tidak boleh diklaim production-ready tanpa seluruh bukti berikut:

- code utama tersedia;
- database/schema runtime tersedia bila diperlukan;
- test unit/integration/static guard tersedia;
- runtime command/API proof tersedia;
- artifact/log audit tersedia;
- docs sync selesai;
- dependency market-data terbukti valid melalui consumer read contract.

Klaim `DONE` hanya boleh merujuk pada scope sesi spesifik. Klaim `LOCKED` hanya boleh dipakai untuk kontrak yang benar-benar sudah punya code, test, runtime proof, dan docs sync.

## Docs Synchronization Rule

Setiap perubahan pada code, config, schema, test, behavior, command, API, atau artifact watchlist wajib disinkronkan ke:

- `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md`;
- `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md`;
- owner docs terkait di `docs/watchlist/system/**` bila behavior normatif berubah;
- audit docs/checklist terkait bila acceptance atau governance berubah.

Jika perubahan hanya docs foundation, status harus tetap jujur sebagai foundation, bukan implementation readiness.

## Market-Data Dependency Rule

Watchlist depends on market-data as its official data source.

Watchlist must consume:

- sealed publication;
- `SUCCESS` run;
- `READABLE` publication;
- coverage `PASS`;
- valid current publication pointer;
- valid publication/run mirror;
- valid indicator rows;
- valid eligibility rows.

Watchlist must not consume:

- raw provider response;
- raw staging table;
- unsealed `eod_bars`;
- unsealed `eod_indicators`;
- unsealed `eod_eligibility`;
- `MAX(trade_date)` shortcut;
- latest available row without publication pointer;
- indicator rows with required null values;
- invalid indicator rows.

Market-data production-ready does not automatically make watchlist production-ready. Watchlist must prove its own read contract, scoring contract, backtest contract, artifact contract, and runtime behavior.

## Consumer Read Safety Rule

Watchlist read-side code must be publication-aware and fail-safe.

Forbidden patterns for watchlist consumer reads:

- raw/latest/MIN/MAX shortcuts for trade date selection;
- direct raw table reads as an authoritative source;
- fallback to staging/provider rows;
- silently accepting missing required indicators;
- silently accepting invalid eligibility;
- inventing an effective date when pointer resolution fails;
- using market-data internals as a replacement for producer-facing consumer contract.

## Backtest Integrity Rule

Backtest must be deterministic, reproducible, and free from lookahead bias.

Forbidden backtest behavior:

- using future publication;
- using future indicator;
- using future price;
- using future eligibility;
- using final known dataset as if it existed historically;
- mixing paramset/code labels without traceable artifact;
- omitting universe, date range, or dataset identity from evidence.

## Watchlist Implementation Update Rule

Every watchlist implementation session must update:

1. implementation status;
2. contract tracker;
3. relevant owner docs if behavior changed;
4. validation log;
5. gap/next-session section.

Implementation work must not start by creating scoring/recommendation/backtest logic before the market-data consumer read model contract is created and guarded.

## `LUMEN_IMPLEMENTATION_STATUS.md` Update Rule

This file must record:

- current active session;
- current implementation status;
- existing docs discovered;
- file/code/test/runtime changes;
- validation evidence;
- active gaps;
- next required session;
- production readiness status.

It must never claim the whole watchlist is ready while core code remains `NOT_STARTED` or runtime proof is missing.

## `LUMEN_CONTRACT_TRACKER.md` Update Rule

This file must record for each contract:

- contract ID;
- title;
- status;
- owner docs;
- implementation files;
- tests;
- runtime proof;
- current gaps;
- acceptance criteria;
- last update.

Contract status may only move to `LOCKED` after implementation, tests, runtime proof, artifacts, and docs sync are all valid.

## Final Readiness Claim Rule

Final readiness may only be claimed after all of the following are true:

- market-data consumer read contract is locked;
- no raw/latest/`MAX(date)` bypass exists;
- required indicators and eligibility guards are enforced;
- scoring is deterministic and explainable;
- paramset/code traceability is recorded;
- recommendation output has acceptance tests;
- backtest is no-lookahead and reproducible;
- risk/liquidity/volatility gates exist;
- artifacts/logs are generated;
- full watchlist test suite passes;
- runtime command/API proof passes;
- docs are synchronized;
- contract tracker marks all readiness-critical contracts as `LOCKED`.

## Initial Governance Baseline

Session: `WATCHLIST — AUDIT GOVERNANCE + LUMEN TRACKER FOUNDATION`

Status: `DONE` for governance foundation only.

Scope completed:

- audit update governance created;
- Lumen implementation status tracker created;
- Lumen contract tracker created;
- owner hierarchy baseline recorded;
- market-data dependency rule recorded;
- anti-overclaim rule recorded.

Watchlist implementation status remains `FOUNDATION_STARTED / NOT_IMPLEMENTED / NOT_READY`.

---

## C35 Governance — IS-Only Robustness Redesign Diagnostic

C35 must begin by locking the C34 artifact hash. If the C34 artifact is missing, unreadable, hash-mismatched, status-unexpected, conclusion-unexpected, or production-ready, C35 must write a blocked artifact and stop.

Source artifact lock:

```text
input_c34_artifact=storage/app/watchlist/backtest/c34-bad-month-robustness-diagnostic.json
expected_c34_hash=1dcf355095334796c2f4558823a1882e71e3ed30
actual_c34_hash=1dcf355095334796c2f4558823a1882e71e3ed30
c34_hash_match=true
c34_status=C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_COMPLETED
c34_final_conclusion=C34_BAD_MONTH_ROBUSTNESS_FAILURE_CONFIRMED_AFTER_C33_DATA_PATH_PASS
```

Validation command:

```powershell
php artisan watchlist:backtest-c35-is-robustness-redesign-diagnostic `
  --c34-artifact=storage/app/watchlist/backtest/c34-bad-month-robustness-diagnostic.json `
  --expected-c34-hash=1dcf355095334796c2f4558823a1882e71e3ed30 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c35-is-robustness-redesign-diagnostic.json `
  --progress
```

Output artifact:

```text
storage/app/watchlist/backtest/c35-is-robustness-redesign-diagnostic.json
artifact_hash=1ab43b0dcee6d41d11b2ab0ed904721836dee3b1
file_sha1=733BE61DF96DBA0ECA450ECCF30A8C0CE8329A4B
```

Evidence rules:

```text
Use only IS rows for G21/G16 diagnostic.
Return may be used only after pick selection as diagnostic/evaluation evidence.
Do not use OOS returns, OOS bad months, or C34 OOS rows for tuning.
C34 bad months are context-only.
```

Operator validation rule:

```text
Do not claim PHPUnit PASS, full Watchlist PASS, or Artisan runtime PASS unless run in the operator environment.
C35 operator validation has been completed.
PHPUNIT_C35=PASS
PHPUNIT_C35_RESULT=OK (11 tests, 106 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (529 tests, 11607 assertions)
ARTISAN_C35_RUNTIME=COMPLETED
```

IS-only diagnostic rule:

```text
from=2023-01-02
to=2025-05-21
oos_reserved_from=2025-05-22
oos_reserved_to=2026-05-29
oos_data_used_for_tuning=false
```

Final C35 IS evidence:

```text
source=storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json
total_rows=15750
g21_rows=1770
g16_rows=1320
months_covered=27
evidence_available=true
```

No production-readiness rule:

```text
production_ready=false
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
```

Redesign hypothesis rule:

```text
C35 may emit redesign hypotheses only. It must not declare a final production candidate and must not unlock OOS proof by itself.
```

Final C35 hypotheses:

```text
C35_HYP_G21_NO_PROFIT_SIGNAL_BRANCH_WEAK=STRONG_IS_SUPPORT
C35_HYP_G21_FALLBACK_EXIT_TOO_LATE=STRONG_IS_SUPPORT
C35_HYP_G16_NEXT_OPEN_DELAY_GAP_DAMAGE=MODERATE_IS_SUPPORT
C35_HYP_BRANCH_CONCENTRATION_REQUIRES_IS_REGIME_FILTER=MODERATE_IS_SUPPORT
```

Final C35 governance decision:

```text
C35_FINAL_STATUS=C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC_COMPLETED
DIAGNOSTIC_CONCLUSION=C35_IS_G21_AND_G16_WEAKNESS_CONFIRMED
NEXT_STEP=C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION
PRODUCTION_READY=false
OOS_PROOF_UNLOCKED=false
```

C35 confirms the robustness weakness is visible in IS evidence. G21 is the primary C36 redesign target, G16 is the secondary controlled redesign target, and bad-month-like regime filtering may be tested only from IS evidence. C36 must not run OOS proof until an IS-controlled candidate is valid.
