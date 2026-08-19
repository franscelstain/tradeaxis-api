Saya akan upload ZIP berisi implementation guidance, artefak implementasi, atau hasil review code watchlist.

Tugas Anda adalah mengaudit implementasi secara ketat terhadap canonical `../../strategy/`.

Aturan aktivasi layer:
- guidance/examples/fixtures/SQL/schema tanpa code/runtime nyata = **Layer B**
- code/app/runtime/persistence nyata yang cukup = **Layer C**

Canonical behavior yang wajib diterjemahkan:
- weekly_swing only
- flow `PLAN -> qualified RECOMMENDATION/TOP_PICKS -> CONFIRM actionability`
- PLAN states: RECOMMENDATION_CANDIDATES / WATCH_ONLY / AVOID
- TOP_PICKS hanya final recommendation
- recommendation all-and-only qualification pass, zero allowed, no quota
- recommendation_score = PLAN score_total baseline
- capital tidak mengubah membership/rank
- CONFIRM hanya final Top Picks dan tidak mutate recommendation
- backtest mengevaluasi final Top Picks
- realistic costs + non-zero slippage + adverse-friction stress
- ranking-quality IS/OOS proof
- no OOS retuning
- forward shadow sebelum production-use approval

Jika technical docs/code masih memakai semantics lama, tandai strategy alignment pending; jangan menganggap keberadaan docs sebagai proof implementasi.

Yang wajib diaudit:
1. implementation scope and boundary
2. module mapping
3. runtime artifact flow
4. API/consumer guidance atau payload review
5. persistence guidance atau persistence review
6. test implementation guidance atau bukti test coverage
7. delivery checklist atau delivery readiness
8. service / repository / serializer / validator boundaries jika code/app nyata sudah tersedia

Output yang saya mau:
- nilai akhir numerik
- verdict
- tabel audit singkat PASS/PARTIAL/FAIL/N/A
- temuan utama
- patch prioritas berikutnya

Jangan mengaudit sebagai portfolio atau execution system. Audit ini khusus implementasi watchlist.


Jika code/app nyata tersedia, audit harus memprioritaskan bukti nyata tersebut di atas contoh guidance. Jika code/app nyata belum tersedia, nyatakan keterbatasan itu dengan jujur dan audit sebagai implementation guidance baseline Layer B, bukan Layer C.


Aturan scoring penting:
- jika Layer C tidak aktif, seluruh item audit yang khusus code/app/runtime nyata harus diberi `N/A`, bukan `PARTIAL`;
- jangan menurunkan nilai hanya karena service/controller/repository/payload runtime nyata memang belum ada pada ZIP guidance;
- `PARTIAL` untuk real-app evidence hanya sah bila Layer C aktif tetapi buktinya belum lengkap atau belum sinkron.

## Market Data Intake + Watchlist Persistence Requirement for Implementation Prompts

Any implementation prompt that touches Market Data input must require the implementer to read and apply:

```text
docs/market_data/book/CONSUMER_READ_CONTRACT_LOCKED.md
docs/market_data/book/Downstream_Consumer_Read_Model_Contract_LOCKED.md
docs/market_data/book/Downstream_Data_Readiness_Guarantee_LOCKED.md
docs/watchlist/authority/strategy/WS_MARKET_DATA_INPUT_REQUIREMENTS.md
docs/watchlist/development/implementation/MARKET_DATA_INTAKE_IMPLEMENTATION_CONTRACT.md
```

The implementation must confirm requested/effective date semantics, readiness/freshness, publication/read-model identity, `data_usable`, active required features, liquidity basis, and no-direct-producer-table rule before coding.

If Watchlist-owned persistence is touched, also read:

```text
docs/db/DATABASE_DICTIONARY_USAGE_RULE.md
docs/watchlist/development/implementation/db/WATCHLIST_DB_DICTIONARY.md
```

Do not require normal Watchlist implementation to reconstruct producer meaning from `docs/market_data/db/MARKET_DATA_DICTIONARY.md`. That dictionary is producer implementation reference, not the downstream intake contract. Missing producer-facing field/semantic coverage is a contract gap or blocker; it is not permission to query an internal table directly.

## Documentation Recording / No-Silent-Update Requirement

Setiap implementation session wajib membaca dan menerapkan:

```text
docs/watchlist/authority/governance/DOCUMENT_RECORDING_STANDARD.md
docs/watchlist/authority/governance/DOCUMENT_CHANGE_LOG.md
```

Audit/implementation harus memastikan:

- final evidence tidak diubah in-place; correction memakai evidence record baru;
- issued decision tidak diubah; perubahan memakai superseding decision;
- locked research/preregistration tidak di-retune; perubahan memakai identity/version baru;
- historical ledger/session entry tidak dihapus atau ditulis ulang; correction dibuat append-only;
- material implementation-contract/API/DTO/schema/reason/validation change dicatat di `DOCUMENT_CHANGE_LOG.md` dan memiliki test/evidence;
- README/index hanya navigation dan tidak menjadi tempat business rule baru;
- strategy/governance material revision mengikuti controlled-revision process.

Jangan menyebut dokumentasi `DONE` bila update material tidak mempunyai trace yang diwajibkan standard.


## Stage Re-entry / Remediation / Closure Requirement

Setiap implementation session yang mengerjakan `WS-Bxx` wajib membaca:

```text
docs/watchlist/authority/governance/STAGE_EXECUTION_AND_REWORK_STANDARD.md
docs/watchlist/development/implementation/WS_IMPLEMENTATION_STAGE_REGISTER.md
```

Jika stage pernah dicoba, jangan mulai dari code. Baca latest attempt evidence, unresolved finding, active remediation/decision, dan change-log lineage terlebih dahulu.

Wajib menilai:

- attempt outcome;
- diagnostic convergence;
- root-cause state;
- what-not-to-repeat;
- remaining gap;
- next testable action;
- resume point.

Repeated failure/time/fatigue bukan alasan `DONE` atau terminal closure. Selama masalah mengerucut (`Convergence=IMPROVING`) atau reasonable remediation masih ada, stage tetap active. `WAITING_VERIFIED_DEPENDENCY` juga active dan harus punya verified dependency + resume trigger.

`DONE` hanya bila declared stage objective/exit criteria tercapai. Pada evaluation/proof stage, valid verdict `FAIL` boleh coexist dengan `DONE` bila menghasilkan verdict sah adalah objective stage; downstream proof gate kemudian stop. Untuk terminal unresolved, minta objective evidence + exhausted/infeasible reasonable remedies + reviewed decision. Successor/decomposition harus materially different dan memindahkan seluruh residual objective.

## Recurring Implementation Residue / Conformance Requirement

Setiap session yang membuat, mengubah, meremediasi, atau memvalidasi current implementation/proof wajib membaca:

```text
docs/watchlist/authority/governance/IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md
```

Wajib:

- scan impacted code/config/schema/API/DTO/reason/fixture/test/runtime/evaluator/documentation surfaces;
- nilai reachability dan semantic impact, bukan nama lama saja;
- classify residue sebagai `HARMFUL_RESIDUE`, `CONTROLLED_COMPATIBILITY_RESIDUE`, `HISTORICAL_ONLY_RESIDUE`, atau `DEAD_RESIDUE_CONFIRMED`;
- harmful residue material dibuat finding/remediation dan memblokir implementation-stage `DONE`;
- compatibility residue hanya boleh tetap dengan exact mapping + isolation + tests + evidence;
- jangan klaim dead hanya dari grep/search;
- attempt evidence wajib menyimpan residue scope + conformance verdict;
- rerun membaca residue evidence/finding sebelumnya dan tidak menemukan masalah yang sama dari nol.

Valid residue verdict:

- `CONFORMANT_NO_HARMFUL_RESIDUE_FOUND`
- `CONFORMANT_WITH_CONTROLLED_COMPATIBILITY`
- `NON_CONFORMANT_HARMFUL_RESIDUE_OPEN`
- `INCONCLUSIVE_RESIDUE_EVIDENCE`


## Canonical Strategy Traceability Requirement

Setiap implementation prompt wajib meminta pembacaan dan update current `../STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv` untuk `WS-Bxx` yang sedang dikerjakan. Jangan nyatakan stage `DONE` atau implementation alignment complete jika masih ada mandatory row belum `SATISFIED`. Isi implementation/test/evidence/residue pointer berdasarkan hasil aktual; jangan mengisi `SATISFIED` dari asumsi.

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

## Correlation/registry rule

For every formal `WS-Bxx` attempt, use Attempt ID as Work ID; update Work Record Registry, Change Impact Declaration, Dependency Registry when applicable, run relationship integrity gate, and issue Stage Closure Manifest only for terminal stage state.
