# Watchlist Audit Prompt Standard

Gunakan prompt berikut saat audit ZIP baru.

```text
Audit ZIP ini secara ketat sebagai Weekly Swing Watchlist.

Canonical product direction:
core: trusted Market Data -> PLAN candidates -> qualified RECOMMENDATION -> ranked TOP PICKS -> manual buy decision support
optional branch: ranked TOP PICKS -> D+1 CONFIRM when valid decision-time data is available -> ACTIONABLE / NOT_ACTIONABLE

Boundary:
- watchlist only
- weekly_swing only
- bukan portfolio
- bukan execution
- bukan position management
- bukan market-data internals

Rule inti:
- PLAN memakai RECOMMENDATION_CANDIDATES / WATCH_ONLY / AVOID
- TOP_PICKS hanya final qualified recommendation
- recommendation berasal dari PLAN immutable
- seluruh dan hanya candidate yang lulus qualification menjadi Top Picks
- Top Picks count boleh nol dan tidak dipaksa quota
- recommendation_score memakai canonical PLAN score_total
- capital/affordability tidak memengaruhi membership/rank
- CONFIRM hanya final Top Picks dan hanya current actionability
- CONFIRM optional/non-blocking; core runtime dan core proof tidak bergantung pada availability CONFIRM
- missing/stale/incomplete/delayed CONFIRM data -> UNAVAILABLE_RETRYABLE, bukan NOT_ACTIONABLE dan bukan core failure
- NOT_ACTIONABLE hanya sah bila valid decision-time data tersedia dan gate dapat dievaluasi
- CONFIRM boleh diretry selama canonical entry window; bila window berakhir tanpa valid evaluation -> EXPIRED_UNCONFIRMED
- backtest/IS/OOS mengevaluasi final Top Picks
- production proof memakai realistic cost + non-zero slippage + adverse-friction stress
- ranking usefulness ikut diuji
- OOS tidak boleh retuning
- core forward shadow diperlukan sebelum core production-use approval; CONFIRM capability proof terpisah dan tidak memblokir core approval

Bedakan:
1. strategy conformance
2. implementation conformance
3. runtime/evidence proof
4. documentation recording/lifecycle integrity

Documentation recording rule:
- apply `docs/watchlist/authority/governance/DOCUMENT_RECORDING_STANDARD.md`;
- final evidence, issued decision, locked research, dan history tidak boleh rewritten;
- finding harus mempertahankan original observation;
- historical ledger/session correction harus append-only;
- material implementation/documentation contract change harus traceable di `DOCUMENT_CHANGE_LOG.md`;
- README/index tidak boleh menjadi owner rule baru.

Jika strategy sudah berubah tetapi technical contracts/code/tests masih lama, jangan beri implementation PASS.

Output wajib:
1. nilai akhir
2. verdict
3. PASS/PARTIAL/FAIL/N/A matrix
4. material findings
5. strategy-to-implementation drift
6. evidence gap
7. next patch order
```


## Baseline Rule

Gunakan audit baseline ini sebagai baseline tetap. Penguatan ZIP baru hanya boleh memperjelas, memperkaya, atau menutup gap kecil. Penguatan tidak boleh mengubah scope inti watchlist, boundary PLAN/RECOMMENDATION/CONFIRM, atau owner map aktif tanpa perubahan eksplisit pada audit foundation.




## Runtime Validation Claim Guardrail

Saat membuat prompt audit/implementation atau menutup hasil sesi, validasi runtime harus diperlakukan sebagai evidence nyata, bukan asumsi.

Rules:

- DILARANG klaim PHPUnit, Artisan command, migration, seed, calibration, backfill, replay, atau runtime proof sudah dijalankan bila memang belum dijalankan atau tidak bisa dijalankan pada environment sesi tersebut.
- Jika tool/environment tidak dapat menjalankan PHPUnit/Artisan, tulis status apa adanya sebagai `BLOCKED`, `NOT_RUN`, atau `OPERATOR_VALIDATION_REQUIRED`; jangan mengganti dengan klaim PASS.
- Jika ada test/command yang dibutuhkan tetapi harus dijalankan manual oleh operator, output wajib mencantumkan:
  1. manual test command lengkap;
  2. expected output atau minimal expected marker yang harus muncul;
  3. pass/fail criteria yang tegas;
  4. exit code yang diharapkan bila relevan;
  5. larangan klaim final PASS sampai output operator diberikan.
- Jika operator kemudian memberikan output runtime, gunakan hanya output tersebut sebagai evidence. Jangan membuat ulang angka, assertion count, artifact hash, atau database count yang tidak muncul di output/operator evidence.
- Jika runtime gagal karena environment, dependency, database, permission, atau missing extension, bedakan dengan jelas antara implementation gap dan validation blocked.
- Jika command menghasilkan exit code non-zero yang memang domain-valid, misalnya grid quality failed, jelaskan pass/fail criteria berdasarkan `status` dan `reason_code`, bukan hanya berdasarkan exit code.
- Semua klaim seperti `LOCAL_RUNTIME_PROOF_PASS`, `LOCAL_R2_IS_CALIBRATION_EXECUTED`, `OOS_NOT_READ`, `R2_GRID_FAILED_IS_QUALITY`, atau `PRODUCTION_READY` hanya boleh ditulis jika evidence command/artifact/DB mendukung secara eksplisit.

Minimum manual validation format:

```text
Manual command:
<command>

Expected output:
<expected lines / markers>

Pass criteria:
- <criteria 1>
- <criteria 2>

Fail criteria:
- <criteria 1>
- <criteria 2>
```


## Calibration Catalog Naming Guardrail

For Weekly Swing calibration/backtest prompt generation, do not continue numeric R-series naming for future catalogs.

Rules:

- `R1` and `R2` may be referenced only as historical aliases/backward-compatible evidence labels.
- Do not create or recommend `R3`, `R4`, `R5`, or later catalog identity.
- Future catalog identity must use semantic focus + catalog attempt:
  `WS_BT_GRID_<FOCUS>_C##_YYYY_MM`.
- `C##` means Catalog attempt within a named focus/campaign, not system revision. It must never stand alone without the focus/campaign name.
- Future run evidence may use:
  `WS_BT_IS_<FOCUS>_C##_RUN_##` and `WS_BT_OOS_<FOCUS>_C##_RUN_##`.
- If a previous catalog already has runtime evidence, do not rename, mutate, or reinterpret it to improve the result.
- If no IS row passes canonical gates, OOS is not eligible and the next session must be diagnostic/design-first, not OOS.

Current historical aliases/evidence labels:

```text
R1 = WS_BT_GRID_BOOTSTRAP_2026_06
R2 = WS_BT_GRID_ENTRY_QUALITY_R2_2026_06
C01 = WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06
```

Current post-C01 next-session naming pattern:

```text
WATCHLIST — WEEKLY SWING C01 FAILURE DIAGNOSTIC AND NEXT SEMANTIC CATALOG DESIGN SESSION
```

Next catalog naming rule:

```text
If the same DOWNSIDE_STABILITY focus continues:
WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06

If diagnosis chooses a new focus:
WS_BT_GRID_<NEW_FOCUS>_C01_YYYY_MM
```

Never mutate `WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06` after its failed-IS runtime evidence.

## Market Data Intake and DB-Connected Work Requirement

For any Watchlist audit/implementation that consumes Market Data, enforce this clause:

```text
Before implementation, read the Market Data producer-facing consumer read contracts, `docs/watchlist/authority/strategy/WS_MARKET_DATA_INPUT_REQUIREMENTS.md`, and `docs/watchlist/development/implementation/MARKET_DATA_INTAKE_IMPLEMENTATION_CONTRACT.md`. Confirm requested/effective date, readiness/freshness, publication/read-model identity, `data_usable`, required active fields, and liquidity basis. Do not reconstruct producer meaning from internal Market Data tables or infer transport aliases.
```

For Watchlist-owned persistence, also read `docs/db/DATABASE_DICTIONARY_USAGE_RULE.md`, `docs/watchlist/development/implementation/db/WATCHLIST_DB_DICTIONARY.md`, and relevant Watchlist migrations/schema.

`docs/market_data/db/MARKET_DATA_DICTIONARY.md` may be used when explicitly auditing producer implementation, but direct producer table mappings are not a valid replacement for the downstream read contract. This applies to regime/benchmark context, sector metadata, data usability, PLAN reads, backtests, diagnostics, and future Market Data-backed features.

## Recurring Residue / Conformance Audit Requirement

Audit implementation/proof harus membaca `docs/watchlist/authority/governance/IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md` dan tidak menerima stage `DONE` hanya karena happy-path baru PASS.

Periksa apakah legacy code/config/schema/API/fixture/test/evaluator path masih reachable dan dapat mengubah current behavior/proof. `HARMFUL_RESIDUE` yang unresolved berarti implementation belum conformant. Controlled compatibility boleh diterima hanya dengan exact semantic mapping, isolation, tests, dan evidence. Grep/search saja bukan proof absence.


## Strategy Traceability / Coverage Audit Requirement

Audit wajib memeriksa `../STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv` dan membedakan stage completion dari rule-level strategy coverage. Klaim 100% mandatory strategy coverage hanya boleh diterima bila seluruh active mandatory/conditional row `SATISFIED`, open mandatory gap = 0, dan harmful residue open = 0. Optional CONFIRM `OPTIONAL_NOT_REQUESTED` tidak memblokir core coverage.

## Work Baseline Lock / Attempt / Executable Integrity Requirement

Current implementation/proof work must follow:

- `docs/watchlist/authority/governance/WORK_BASELINE_LOCK_STANDARD.md`;
- `docs/watchlist/authority/governance/DOCUMENT_INTEGRITY_GATE_STANDARD.md`;
- `docs/watchlist/development/implementation/examples/WS_STAGE_ATTEMPT_RECORD_TEMPLATE.md`.

Audit/implementation must verify:

- baseline was issued before material code/contract change;
- Stage ID, Attempt ID, and Baseline ID are linked;
- source revision and locked authority fingerprints are recorded;
- pre-attempt and pre-close executable integrity gate results exist;
- attempt evidence records coverage/residue/convergence/root-cause/do-not-repeat/resume point;
- no stage `DONE` or 100% coverage claim relies on evidence detached from a valid baseline;
- a gate failure is not waived by prose; only exact registered legacy exception may be used where governance permits.

## Relationship/closure verification

Audit current work relationships using Work Record Registry + Dependency Registry + relationship integrity gate. Do not accept terminal stage status without a valid closure manifest and supporting evidence.
## Mandatory Role-Purity Audit

Audit wajib memeriksa `ONE_DOCUMENT_ONE_AUTHORITATIVE_ROLE_STANDARD.md` dan `DOCUMENT_ROLE_REGISTRY.csv`. Satu file hanya boleh memiliki satu authoritative role. Supporting cross-role references boleh; embedded second authority harus dinilai sebagai gap dan dipecah ke record sesuai role.

