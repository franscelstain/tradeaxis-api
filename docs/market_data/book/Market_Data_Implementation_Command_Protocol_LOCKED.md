# Market-Data Implementation Command Protocol (LOCKED)

## Purpose

Dokumen ini menetapkan perintah kontrol yang dipakai untuk menjalankan work order `W00`–`W22`, mengaudit hasilnya, melakukan remediation, mengaudit ulang, dan maju secara aman. Ia menjawab empat pertanyaan untuk setiap urutan:

1. perintah apa yang harus diberikan;
2. scope apa yang boleh dikerjakan;
3. bentuk hasil apa yang wajib dilaporkan;
4. perintah apa yang boleh diberikan berikutnya berdasarkan verdict.

Perintah `MD-*` di dokumen ini adalah **task directives untuk agent/developer workflow**, bukan shell, Artisan, atau database commands. Exact terminal commands harus ditemukan/dibuat sesuai repository pada work order terkait, dijalankan, dan dicatat sebagai evidence; perintah terminal tidak boleh dikarang sebelum implementation surface tersedia.

## Authorities

- urutan kerja: `Market_Data_Strategy_Implementation_Blueprint_LOCKED.md`;
- assignment dan exit gate: `Market_Data_Implementation_Conformance_Matrix_LOCKED.md`;
- behavior: owner contract paling spesifik;
- current execution state: `../audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md`;
- current documentation verdict: `../audit/reports/AUDIT_FINAL_STATE.md`.

Command protocol tidak boleh mengalahkan authority tersebut dan tidak boleh membawa policy watchlist ke market-data.

## Command vocabulary (LOCKED)

### `MD-RUN Wxx`

Tujuan: menjalankan atau melanjutkan **tepat satu** work order sampai `PASS`, lalu memberikan exact command untuk work order berikutnya. Ini adalah command utama yang direkomendasikan.

Copy-paste directive:

```text
MD-RUN Wxx market-data.
Jalankan hanya work order Wxx dari current-state ledger sampai PASS sesuai Market_Data_Strategy_Implementation_Blueprint_LOCKED.md, Market_Data_Implementation_Conformance_Matrix_LOCKED.md, Market_Data_Implementation_Command_Protocol_LOCKED.md, seluruh owner contracts yang assigned, dan MARKET_DATA_IMPLEMENTATION_LEDGER.md. Verifikasi predecessor; implementasikan seluruh assignment schema/config/code/migration/backfill/test/ops/evidence; jalankan targeted tests dan affected full suite; lakukan audit read-only; bila PARTIAL/FAIL, remediasi seluruh finding aktif dan re-audit berulang sampai PASS. Jangan maju atau mengimplementasikan work order berikutnya, jangan melemahkan contract agar legacy behavior lulus, dan jangan memasukkan policy watchlist. Tandai Wxx CONFORMANT hanya dengan evidence admissible, perbarui ledger, lalu keluarkan WORK_ORDER_RESULT dan tepat satu next command MD-RUN Wyy untuk successor. Berhenti sebelum PASS hanya bila diperlukan keputusan/otoritas user yang benar-benar material, credential/external state tanpa safe local/test alternative, atau tindakan production/destructive/external yang belum diizinkan; laporkan exact unblock requirement. Jangan commit, push, deploy, membeli provider, mengaktifkan production, atau memutasi production data tanpa instruksi terpisah.
```

Behavior stage command:

1. baca ledger dan pastikan `Wxx` adalah next admitted work order atau active resumable work order;
2. jangan mengulang predecessor yang sudah `CONFORMANT` kecuali dependency evidence menjadi stale;
3. jalankan lifecycle internal `EXEC -> AUDIT -> REMEDIATE -> REAUDIT -> CLOSE` hanya untuk `Wxx`;
4. kegagalan tests/findings bukan alasan berhenti; lakukan remediation dalam scope sampai pass;
5. setelah `PASS`, update ledger dan berikan exact successor command tanpa menjalankannya;
6. bila context terkompaksi atau proses terinterupsi, resume `Wxx` dari ledger/evidence;
7. target setiap command adalah satu row `Wxx CONFORMANT`, bukan seluruh program;
8. pada `W22`, output final membedakan `IMPLEMENTATION_CONFORMANT` dari activation-dependent `OPERATIONALLY_VALIDATED`.

Pada material blocker, user menyelesaikan input/authority yang diminta lalu mengirim ulang command `MD-RUN Wxx market-data.` yang sama. Pada `PASS`, `next_permitted_command` wajib `MD-RUN W(next) market-data.`; pada `W22 PASS`, nilainya `NONE — IMPLEMENTATION_SEQUENCE_COMPLETE`.

### `MD-STATUS`

Tujuan: membaca ledger dan melaporkan satu next permitted command tanpa mengubah code/schema/status.

Copy-paste directive:

```text
MD-STATUS market-data.
Baca blueprint, conformance matrix, command protocol, dan current implementation ledger. Laporkan current active work order, predecessor state, open findings, evidence yang masih kurang, dan tepat satu next permitted command. Jangan mengubah file atau menjalankan remediation.
```

### `MD-EXEC Wxx`

Tujuan: mengimplementasikan tepat satu work order.

Copy-paste directive:

```text
MD-EXEC Wxx market-data.
Jalankan hanya work order Wxx sesuai Market_Data_Strategy_Implementation_Blueprint_LOCKED.md dan seluruh assignment/exit gate pada Market_Data_Implementation_Conformance_Matrix_LOCKED.md. Verifikasi predecessor pada current implementation ledger terlebih dahulu. Inspect current code, schema, config, tests, ops, dan evidence; jangan menganggap legacy behavior benar. Implementasikan seluruh perubahan in-scope, migrations/backfill/repository/service/adapter/command/tests/runbook/evidence yang diperlukan. Jangan maju ke work order berikutnya dan jangan memasukkan policy watchlist. Jalankan targeted tests serta affected full suite. Perbarui ledger hanya dengan fakta yang telah terbukti dan keluarkan WORK_ORDER_RESULT sesuai command protocol. Bila belum memenuhi exit gate, jangan klaim PASS; keluarkan exact findings dan next permitted remediation command.
```

`MD-EXEC` menghasilkan implementation result dengan `audit_verdict: NOT_AUDITED`. Pelaksana tidak boleh memberi dirinya sendiri final `PASS`; penutupan memerlukan `MD-AUDIT`.

### `MD-AUDIT Wxx`

Tujuan: audit read-only terhadap work order yang telah diimplementasikan.

Copy-paste directive:

```text
MD-AUDIT Wxx market-data.
Audit secara read-only hasil work order Wxx terhadap blueprint, conformance matrix, seluruh owner contracts, schema/config/code/tests/ops/evidence, dan current ledger. Jangan memperbaiki temuan pada perintah ini. Jalankan safe diagnostics dan seluruh verification commands yang diperlukan. Beri finding ID, severity, evidence, violated contract/exit gate, dan exact remediation target. Keluarkan verdict hanya PASS, PARTIAL, FAIL, atau BLOCKED serta tepat satu next permitted command sesuai command protocol. PASS hanya bila seluruh assigned rows CONFORMANT dan evidence admissible.
```

### `MD-REMEDIATE Wxx F-...`

Tujuan: memperbaiki hanya findings dari audit terakhir untuk work order yang sama.

Copy-paste directive:

```text
MD-REMEDIATE Wxx findings F-001,F-002,... market-data.
Perbaiki hanya findings aktif yang disebut untuk work order Wxx berdasarkan evidence dan remediation target pada audit terakhir. Jangan memperluas scope, jangan maju ke work order berikutnya, jangan melemahkan owner contract agar legacy code lulus, dan jangan menambahkan policy watchlist. Jalankan targeted tests serta affected full suite, perbarui evidence/ledger secara faktual, lalu keluarkan WORK_ORDER_RESULT dengan audit_verdict NOT_AUDITED dan next permitted command MD-REAUDIT Wxx.
```

### `MD-REAUDIT Wxx`

Tujuan: mengaudit ulang seluruh exit gate work order setelah remediation, bukan hanya mengecek baris yang diubah.

Copy-paste directive:

```text
MD-REAUDIT Wxx market-data.
Audit ulang secara read-only seluruh scope dan exit gate Wxx setelah remediation. Verifikasi juga regression dan cross-contract impact, bukan hanya finding sebelumnya. Jangan melakukan fix. Keluarkan current findings, verdict PASS/PARTIAL/FAIL/BLOCKED, ledger decision, dan tepat satu next permitted command.
```

### `MD-CLOSE Wxx`

Tujuan: menutup work order yang audit terakhirnya sudah `PASS` dan menyiapkan successor tanpa mengimplementasikannya.

Copy-paste directive:

```text
MD-CLOSE Wxx market-data.
Pastikan audit terakhir Wxx berverdict PASS, seluruh assigned ledger rows CONFORMANT, evidence references tersedia, dan tidak ada open blocking finding. Ubah current-state ledger untuk menutup Wxx, hitung successor yang sah dari blueprint, dan keluarkan tepat satu next permitted command MD-EXEC Wyy. Jangan mengubah implementation dan jangan mulai Wyy.
```

`MD-CLOSE` tidak diperlukan bila `MD-AUDIT`/`MD-REAUDIT` telah memperbarui ledger ke `CONFORMANT` secara atomik dan jelas. Dalam hal itu next command langsung `MD-EXEC Wyy`.

### `MD-REPORT Wxx`

Tujuan: menampilkan current-state result/evidence tanpa mutasi.

```text
MD-REPORT Wxx market-data.
Laporkan current-state ledger, latest implementation result, latest audit verdict, open findings, test/evidence identities, dan next permitted command untuk Wxx. Jangan mengubah file atau menjalankan remediation.
```

## Command admission rules (LOCKED)

Sebelum menjalankan command:

- `MD-EXEC W00` dan `MD-RUN W00` hanya memerlukan documentation strategy ready;
- `MD-EXEC W01`–`W22` dan `MD-RUN W01`–`W22` memerlukan predecessor work order `CONFORMANT`;
- `MD-RUN Wxx` selalu resume work order yang sama bila ledger menunjukkan partial progress/blocker yang sudah di-unblock;
- hanya satu work order boleh `IN_PROGRESS`;
- `MD-AUDIT Wxx` memerlukan implementation result untuk `Wxx`;
- `MD-REMEDIATE Wxx` memerlukan active finding IDs dari latest audit;
- `MD-REAUDIT Wxx` memerlukan remediation result baru;
- `MD-CLOSE Wxx` memerlukan latest audit `PASS`;
- stale audit terhadap code/schema/config/evidence yang telah berubah tidak dapat dipakai untuk close;
- command yang tidak admitted harus ditolak dengan verdict `BLOCKED_COMMAND`, tanpa mutasi, dan mengembalikan `MD-STATUS` atau command predecessor yang tepat.

Pekerjaan additive untuk dependency teknis boleh dibuat hanya bila work order aktif memang mengizinkannya. Ia tetap dicatat `IMPLEMENTED_NOT_PROVEN` dan tidak menutup successor.

## Required `WORK_ORDER_RESULT` format (LOCKED)

Setiap `MD-RUN`, `MD-EXEC`, `MD-REMEDIATE`, `MD-AUDIT`, dan `MD-REAUDIT` wajib mengeluarkan blok hasil dengan field berikut:

```yaml
WORK_ORDER_RESULT:
  work_order: Wxx
  command: MD-RUN | MD-EXEC | MD-REMEDIATE | MD-AUDIT | MD-REAUDIT
  execution_mode: STAGE_RUN | MANUAL
  scope_title: "..."
  predecessor_verified: true | false
  status_before: NOT_STARTED | IN_PROGRESS | IMPLEMENTED_NOT_PROVEN | PROVEN | CONFORMANT | BLOCKED
  status_after: NOT_STARTED | IN_PROGRESS | IMPLEMENTED_NOT_PROVEN | PROVEN | CONFORMANT | BLOCKED
  audit_verdict: NOT_AUDITED | PASS | PARTIAL | FAIL | BLOCKED
  assigned_documents_checked: 0
  assigned_documents_total: 0
  changed_files: []
  schema_config_changes: []
  backfill_or_data_changes: []
  terminal_commands_executed:
    - command: "exact command"
      exit_code: 0
      result_summary: "tests/assertions/rows/hash/evidence identity"
  tests:
    targeted: "PASS | FAIL | NOT_RUN with counts/reason"
    affected_full_suite: "PASS | FAIL | NOT_RUN with counts/reason"
  evidence_refs: []
  findings:
    - id: F-001
      severity: P0 | P1 | P2 | P3
      status: OPEN | CLOSED
      contract: "owner file and section"
      evidence: "exact file/line/query/test/output"
      remediation_target: "concrete required outcome"
  exit_gates:
    passed: []
    failed: []
    not_evaluable: []
  ledger_updated: true | false
  next_permitted_command: "exactly one MD-* command"
```

Zero findings ditulis sebagai `findings: []`; field tidak boleh dihilangkan. Command yang tidak dijalankan harus `NOT_RUN` beserta alasan, bukan diasumsikan pass.

## Verdict rules (LOCKED)

### `PASS`

Hanya sah bila:

- predecessor valid;
- seluruh assigned documents diperiksa;
- seluruh exit gates lulus;
- schema/config/runtime/backfill/test/ops/evidence impact lengkap;
- targeted tests dan affected full suite lulus;
- tidak ada superseded expectation yang dihitung;
- tidak ada open contract variance atau P0/P1 finding;
- ledger dapat ditandai `CONFORMANT` dengan evidence refs.

Informational P3 boleh dicatat hanya bila tidak merupakan contract deviation. Tidak ada `PASS_WITH_WARNINGS` untuk menyembunyikan pekerjaan wajib.

### `PARTIAL`

Dipakai bila pekerjaan benar telah dilakukan tetapi satu atau lebih exit gate/evidence/test/backfill/enforcement belum lengkap, tanpa unsafe active behavior yang memerlukan `FAIL`.

Next permitted command wajib:

```text
MD-REMEDIATE Wxx findings F-...
```

Dalam `MD-RUN Wxx`, `PARTIAL` adalah internal audit state: controller langsung melakukan remediation dan re-audit pada `Wxx`, bukan meminta user mengirim command baru.

### `FAIL`

Dipakai bila ditemukan contract violation, unsafe mutation/bypass, semantic mismatch, test failure material, false proof, future leakage, synthetic repair, mixed publication/product, atau behavior lain yang harus dihentikan.

Next permitted command wajib `MD-REMEDIATE Wxx findings ...`; bila unsafe behavior aktif, remediation pertama harus menonaktifkan/contain behavior tersebut.

Dalam `MD-RUN Wxx`, `FAIL` adalah internal audit state yang wajib diremediasi pada work order yang sama. User hanya menerima terminal result sebelum `PASS` bila remediation memerlukan material authority/input.

### `BLOCKED`

Dipakai hanya bila dependency/authority/environment/evidence yang diperlukan benar-benar tidak tersedia dan safe in-scope alternatives telah habis. Blocking condition dan unblock requirement harus konkret.

Next permitted command adalah salah satu:

- predecessor command yang belum selesai;
- `MD-STATUS` setelah external state berubah;
- explicit user/owner decision yang benar-benar diperlukan.

`BLOCKED` tidak boleh dipakai karena pekerjaan sulit atau banyak.

## Deterministic next-command table — manual mode

| Latest state | Next permitted command |
|---|---|
| no work order started | `MD-EXEC W00` |
| `MD-EXEC Wxx` selesai, belum diaudit | `MD-AUDIT Wxx` |
| audit `PASS` dan ledger belum ditutup | `MD-CLOSE Wxx` |
| audit `PASS` dan ledger atomically `CONFORMANT` | `MD-EXEC W(next)` |
| audit `PARTIAL` | `MD-REMEDIATE Wxx findings F-...` |
| audit `FAIL` | `MD-REMEDIATE Wxx findings F-...` |
| remediation selesai | `MD-REAUDIT Wxx` |
| re-audit tetap `PARTIAL/FAIL` | `MD-REMEDIATE Wxx findings <current-open-IDs>` |
| command tidak admitted | predecessor command atau `MD-STATUS` |
| `W22` audit `PASS` | `MD-CLOSE W22`, lalu status akhir sesuai evidence (`IMPLEMENTATION_CONFORMANT`/`OPERATIONALLY_VALIDATED`) |

Agent tidak boleh mengembalikan beberapa alternatif next command. Ia harus memilih tepat satu berdasarkan current ledger. Dalam mode `STAGE_RUN`, lifecycle internal tidak meminta command tambahan; setelah `PASS`, hasil wajib menunjuk exact `MD-RUN W(next) market-data.` tanpa menjalankannya.

## Sequential stage command catalog (LOCKED)

Kirim tepat satu command pada satu waktu. Command berikut hanya dikirim setelah command sebelumnya menghasilkan `PASS`/`CONFORMANT`.

| Order | Exact user command | Scope |
|---|---|---|
| 1 | `MD-RUN W00 market-data.` | preflight dan implementation ledger baseline |
| 2 | `MD-RUN W01 market-data.` | scope, boundary, dataset/activation semantics |
| 3 | `MD-RUN W02 market-data.` | Yahoo bootstrap dan provider-neutral ports |
| 4 | `MD-RUN W03 market-data.` | migration/schema/repository/reason/test skeleton |
| 5 | `MD-RUN W04 market-data.` | immutable config snapshot dan semantic bindings |
| 6 | `MD-RUN W05 market-data.` | temporal identity dan mappings |
| 7 | `MD-RUN W06 market-data.` | calendar/session/trading status |
| 8 | `MD-RUN W07 market-data.` | immutable observations dan source adapters |
| 9 | `MD-RUN W08 market-data.` | resilience/manual recovery/failure taxonomy |
| 10 | `MD-RUN W09 market-data.` | import-only dan canonical RAW |
| 11 | `MD-RUN W10 market-data.` | publication/seal/pointer/correction lifecycle |
| 12 | `MD-RUN W11 market-data.` | corporate-action event/factor lifecycle |
| 13 | `MD-RUN W12 market-data.` | coherent analytical price products |
| 14 | `MD-RUN W13 market-data.` | actual/proxy daily metrics |
| 15 | `MD-RUN W14 market-data.` | deterministic indicators/dependency graph |
| 16 | `MD-RUN W15 market-data.` | temporal coverage gate |
| 17 | `MD-RUN W16 market-data.` | explainable data usability |
| 18 | `MD-RUN W17 market-data.` | versioned atomic read product |
| 19 | `MD-RUN W18 market-data.` | exact/as-known replay |
| 20 | `MD-RUN W19 market-data.` | operational lifecycle/commands/observability/evidence |
| 21 | `MD-RUN W20 market-data.` | optional session snapshot decision/implementation |
| 22 | `MD-RUN W21 market-data.` | global convergence/backfill/full semantic proof |
| 23 | `MD-RUN W22 market-data.` | independent audit/activation-aware validation/relock |

## Standard stage-by-stage lifecycle example

```text
MD-RUN W00 market-data.
-> internal: implement -> audit -> remediate/re-audit bila perlu
-> terminal result: W00 PASS / CONFORMANT
-> next_permitted_command: MD-RUN W01 market-data.

MD-RUN W01 market-data.
-> internal: implement -> audit -> remediate/re-audit bila perlu
-> terminal result: W01 PASS / CONFORMANT
-> next_permitted_command: MD-RUN W02 market-data.
```

Siklus internal remediation dan re-audit diulang pada work order yang sama sampai `PASS`; user kemudian mengirim command successor yang diberikan oleh hasil. Nomor berikut tidak boleh dikirim lebih awal.

## Terminal command and evidence rule

Setiap work order harus menemukan dan merekam exact repository commands untuk:

- static/contract checks;
- targeted unit/integration tests;
- affected full suite;
- migration clean install dan supported upgrade bila relevan;
- backfill/dry-run/resume verification bila relevan;
- artifact/hash/replay/evidence verification bila relevan;
- command help/safety/runtime proof bila relevan.

Expected result harus kuantitatif ketika tersedia: exit code, test/assertion count, row counts, hash identity, publication/run/config IDs, migration state, dan evidence path. Output “berhasil” tanpa exact evidence tidak dapat menghasilkan `PASS`.

## Final completion rule

Pembangunan market-data selesai hanya bila:

1. `W00`–`W22` semuanya `CONFORMANT` pada ledger;
2. latest `W22` audit `PASS`;
3. final claim membedakan `IMPLEMENTATION_CONFORMANT` dari `OPERATIONALLY_VALIDATED`;
4. activation-dependent evidence tidak diklaim sebelum activation;
5. documentation, implementation, schema, tests, ops, dan evidence tetap sinkron;
6. hasil watchlist atau pembelian provider berbayar tidak dijadikan syarat market-data completion.
