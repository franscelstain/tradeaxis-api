# Publishability State Integrity Contract LOCKED

## Status

LOCKED — 2026-04-27

## Purpose

Kontrak ini mengunci integritas state publishability antara:

- `eod_runs`
- `eod_publications`
- `eod_current_publication_pointer`

Tidak boleh ada current publication yang dapat dikonsumsi jika state run, publication, dan pointer tidak membentuk kombinasi valid.

## Single Source of Truth

Penentu utama hasil publishability adalah:

1. `FinalizeDecisionService`
2. Coverage Gate result
3. `PublicationFinalizeOutcomeService` sebagai resolver akhir setelah pointer/promotion result diketahui

Repository, command, evidence, replay, dan consumer read path hanya boleh menegakkan kontrak dan membaca state yang sudah diputuskan. Komponen tersebut tidak boleh membuat outcome publishability baru secara independen.

## State Matrix

| terminal_status | publishability_state | boleh current |
| --- | --- | --- |
| SUCCESS | READABLE | YA |
| SUCCESS | NOT_READABLE | TIDAK |
| FAILED | NOT_READABLE | TIDAK |
| HELD | NOT_READABLE | TIDAK |

Kombinasi selain matrix di atas tidak valid untuk finalized run.

## Invariants

- `READABLE` selalu berarti `terminal_status = SUCCESS`.
- `FAILED` selalu berarti `publishability_state = NOT_READABLE`.
- `HELD` selalu berarti `publishability_state = NOT_READABLE`.
- `current` selalu berarti publication `SEALED`, run `SUCCESS`, dan run `READABLE`.
- `NOT_READABLE` tidak boleh menjadi current publication pointer.
- `is_current = 1` pada `eod_publications` tidak cukup; pointer dan run state harus valid juga.
- `eod_runs.is_current_publication = 1` tidak cukup; pointer dan publication state harus valid juga.

## Pointer Rule

`eod_current_publication_pointer` hanya boleh menunjuk publication yang memenuhi semua syarat berikut:

- pointer `trade_date` sama dengan publication `trade_date`
- pointer `run_id` sama dengan publication `run_id`
- pointer `publication_version` sama dengan publication `publication_version`
- publication `seal_state = SEALED`
- publication `sealed_at` tidak null
- publication `is_current = 1`
- run exists
- run `sealed_at` tidak null
- run `terminal_status = SUCCESS`
- run `publishability_state = READABLE`
- run `is_current_publication = 1`

Jika salah satu syarat gagal, publication tidak boleh dianggap current/readable.

## Publication Rule

`eod_publications.is_current = 1` hanya valid jika pointer untuk trade date yang sama menunjuk publication tersebut dan run pemiliknya memenuhi `SUCCESS + READABLE`.

Publication yang `SEALED` tetapi run-nya `SUCCESS + NOT_READABLE`, `FAILED + NOT_READABLE`, atau `HELD + NOT_READABLE` boleh ada sebagai historical/candidate record, tetapi tidak boleh menjadi current pointer.

## Fallback Rule

Fallback hanya boleh terjadi ke previous publication yang:

- berada pada trade date fallback yang deterministic
- publication `SEALED`
- run `SUCCESS`
- run `READABLE`
- memiliki linkage pointer/publication/run yang konsisten

Fallback tidak boleh:

- implicit tanpa reason code
- menunjuk publication `NOT_READABLE`
- menunjuk run `FAILED` atau `HELD`
- membuat pointer ke publication yang belum sealed
- mengandalkan `MAX(date)` atau shortcut tanpa resolver contract

## Enforcement Points

- `FinalizeDecisionService` wajib menjaga state matrix sebelum mengembalikan pre-decision.
- `PublicationFinalizeOutcomeService` wajib menjaga state matrix sebelum mengembalikan final outcome.
- `EodPublicationRepository::resolveCurrentReadablePublicationForTradeDate` adalah read-side resolver pointer-first.
- `EodPublicationRepository::restorePriorCurrentPublication` wajib menolak fallback prior publication jika run bukan `SUCCESS + READABLE`.
- `MarketDataPipelineService` wajib membersihkan/restore current state jika non-readable run sempat menjadi current kandidat dalam finalize transaction.
- `AbstractMarketDataCommand` hanya merender state dari DB/run, bukan menentukan state baru.

## Reason Code Requirement

Setiap state non-readable harus memiliki reason code operasional jika berasal dari failure, hold, coverage fail, source failure, lock conflict, atau integrity repair.

Contoh reason code valid:

- `RUN_COVERAGE_LOW`
- `RUN_COVERAGE_NOT_EVALUABLE`
- `RUN_PARTIAL_DATA`
- `RUN_DATA_DELAYED`
- `RUN_SOURCE_RATE_LIMIT`
- `RUN_SOURCE_TIMEOUT`
- `RUN_LOCK_CONFLICT`
- `RUN_CURRENT_PUBLICATION_INTEGRITY_REPAIRED`

## Done Criteria

- Tidak ada `READABLE` selain `SUCCESS + READABLE`.
- Tidak ada `FAILED/HELD` yang readable.
- Tidak ada pointer ke publication non-readable.
- Tidak ada current publication tanpa sealed publication dan sealed run.
- Fallback selalu deterministic dan hanya ke previous readable publication.
- Consumer read path tetap pointer-first dan fail-safe.
