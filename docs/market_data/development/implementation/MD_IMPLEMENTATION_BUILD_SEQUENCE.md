# Market Data Implementation Build Sequence

> Current verification epoch: `MD-REBASELINE-20260820-001`.

Current stage IDs are intentionally distinct from historical work-order verdicts. Each `MD-Bxx` maps to frozen legacy strategy work-order `Wxx` only as scope/orientation; old Wxx status is not inherited.

| Current stage | Frozen scope source | Title | Contract areas | Frozen exit intent |
|---|---|---|---|---|
| `MD-B00` | `W00` | Preflight dan implementation ledger | all | current code/schema/test/evidence baseline direkam; setiap dokumen aktif memiliki assignment di conformance matrix |
| `MD-B01` | `W01` | Kunci scope, boundary, dataset start, development frontier, activation semantics, dan non-goals | 1–2 | tidak ada ambiguity market, product, time boundary, atau kebocoran policy watchlist |
| `MD-B02` | `W02` | Kunci Yahoo bootstrap dan provider-neutral ports | 3 | Yahoo-specific behavior berhenti di adapter; current/future source decision eksplisit |
| `MD-B03` | `W03` | Bangun migration framework, additive schema skeleton, repository interfaces, reason registry, dan test harness skeleton | 4–21 foundations | clean-install/upgrade path tersedia untuk setiap feature berikut; belum ada nullable placeholder yang dianggap conformant |
| `MD-B04` | `W04` | Bangun immutable configuration snapshot dan semantic version bindings | 16 | semua writer berikut dapat menerima non-null config/reason/build identity sejak pertama kali dibuat |
| `MD-B05` | `W05` | Bangun temporal issuer/instrument/listing/symbol/provider mapping **serta temporal sector membership foundation** | 6 + 13 prerequisite | as-of/as-known identity, inactive-now-active-then, dan sector-reclassification fixture lulus sebelum indicator sector-relative dibangun |
| `MD-B06` | `W06` | Bangun calendar/session/status expectation | 7 | requested date dan expected-bar decision tidak memakai current-state guessing |
| `MD-B07` | `W07` | Bangun immutable source observations dan acquisition ports/adapters | 4 | setiap source outcome, termasuk empty/failure, memiliki immutable provenance |
| `MD-B08` | `W08` | Bangun resilience, retry/backoff/rate limit, manual recovery, quarantine, dan failure taxonomy | 5 | provider failure tidak menghasilkan synthetic data atau silent readable state |
| `MD-B09` | `W09` | Bangun import-only, canonical `RAW`, invalid-row, dedup/conflict, dan candidate persistence | 8 | source payload tidak langsung menjadi readable; canonical invariants dan lineage terbukti |
| `MD-B10` | `W10` | Bangun immutable publication state machine, manifest, seal, pointer, correction, supersession, dan no-in-place-rewrite | 9 | candidate/sealed/readable/superseded terpisah; failed correction tidak mengubah pointer |
| `MD-B11` | `W11` | Bangun verified corporate-action event/factor lifecycle dan anomaly-only detector | 10 | price break tidak dapat menjadi verified action/factor otomatis |
| `MD-B12` | `W12` | Bangun coherent `RAW`/`STRUCTURAL_ADJUSTED`/`TOTAL_RETURN` product engine | 11 | satu run memakai satu explicit factor-bound basis tanpa per-row fallback |
| `MD-B13` | `W13` | Bangun actual/proxy daily market metrics | 14 | actual field dan proxy berbeda nama, unit, basis, null state, lineage, dan hash |
| `MD-B14` | `W14` | Bangun deterministic indicator engine dan correction dependency graph | 15 | formula/seed/warm-up/nullability/precision exact; long-chain ATR dan correction propagation lulus |
| `MD-B15` | `W15` | Bangun temporal coverage expectation/delivery gate | 12 | denominator tidak menyusut karena provider absence, dormancy, zero volume, atau current active state |
| `MD-B16` | `W16` | Bangun explainable row-level data usability | 13 | quality/liquidity/status/event facts terpisah; compatibility `eligible` tidak memuat policy tradability/watchlist |
| `MD-B17` | `W17` | Bangun atomic versioned market-data read product dan freshness/readiness gateway | 17 | satu response terikat satu publication/config/factor/formula identity; no `MAX(date)`/mixed fallback |
| `MD-B18` | `W18` | Bangun exact-publication dan as-known replay | 18 | no future leakage; exact replay mereproduksi values, reasons, lineage, dan hashes |
| `MD-B19` | `W19` | Bangun daily/backfill/correction/replay operations, locking, observability, evidence export, dan recovery | 19 | command surface fail-safe, idempotent, resumable, observable, dan activation-aware |
| `MD-B20` | `W20` | Implementasikan supplemental session snapshot hanya bila feature state dinyatakan aktif | 17/19 optional | bila disabled, state/non-scope eksplisit; bila enabled, seluruh snapshot contract dan proof lulus |
| `MD-B21` | `W21` | Global schema/config/code/test/ops convergence, backfill, constraint hardening, dan full semantic proof | 20–21 | clean install + supported upgrade + backfill + MariaDB/test mirror + positive/negative suites lulus tanpa superseded oracle |
| `MD-B22` | `W22` | Independent implementation audit, pre-activation catch-up, operational validation, dan relock | 22 | tidak ada P0/P1 material; claim sesuai evidence dan activation state; watchlist performance bukan gate |
