# Publishability Coverage Fallback Cross-Consistency Contract (LOCKED)

## Purpose

Dokumen ini mengunci hubungan satu arah antara coverage gate, finalize decision, publishability state, fallback behavior, dan current publication pointer.

Contract ini mencegah state ambigu seperti coverage gagal tetapi publication tetap readable, fallback ke publication tidak readable, atau pointer pindah ke run yang tidak memenuhi coverage.

---

## Authority

Contract ini menjadi owner untuk cross-consistency antar layer berikut:

1. `CoverageGateEvaluator`
2. `FinalizeDecisionService`
3. `PublicationFinalizeOutcomeService`
4. `MarketDataPipelineService`
5. `EodPublicationRepository`
6. `eod_current_publication_pointer`
7. command output yang menampilkan coverage, publishability, fallback, dan pointer state

Contract khusus yang tetap berlaku dan tidak digantikan:

- `EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`
- `Coverage_Gate_Enforcement_Contract_LOCKED.md`
- `Coverage_Edge_Cases_Contract_LOCKED.md`
- `Publishability_State_Integrity_Contract_LOCKED.md`
- `Publication_Current_Pointer_Integrity_Contract_LOCKED.md`
- `Finalize_Lock_And_Pointer_Behavior_LOCKED.md`
- `Read_Side_Enforcement_Anti_Bypass_Contract_LOCKED.md`

---

## Locked hierarchy

Flow wajib:

```mermaid
flowchart LR
    A[Coverage Gate] --> B[Finalize Decision]
    B --> C[Publishability State]
    C --> D[Fallback Resolution]
    D --> E[Current Pointer]
```

Tidak boleh ada layer yang melompati layer sebelumnya.

Artinya:

- coverage tidak langsung menulis pointer;
- pointer tidak boleh menentukan publishability;
- fallback tidak boleh mengevaluasi raw coverage candidate secara langsung;
- publishability hanya boleh berasal dari finalize/outcome decision;
- command output hanya melaporkan state, bukan membuat keputusan state.

---

## Coverage rule

Coverage gate adalah input wajib untuk finalize decision.

Allowed input state:

- `PASS`
- `FAIL`
- `NOT_EVALUABLE`
- `BLOCKED`

Rules:

- `PASS` adalah syarat minimum agar candidate boleh menjadi `READABLE`.
- `FAIL` tidak boleh menghasilkan `READABLE`.
- `NOT_EVALUABLE` tidak boleh menghasilkan `READABLE`.
- `BLOCKED` tidak boleh menghasilkan `READABLE`.
- coverage ratio saja tidak cukup; state akhir coverage gate harus eksplisit.

---

## Finalize decision rule

Finalize decision adalah satu-satunya layer yang menerjemahkan coverage menjadi terminal/publishability candidate state sebelum publication outcome.

Rules:

- `coverage_gate_status=PASS` + sealed candidate + cutoff satisfied dapat menghasilkan:
  - `terminal_status=SUCCESS`
  - `publishability_state=READABLE`
  - `promotion_allowed=true`
- `coverage_gate_status=FAIL` harus menghasilkan:
  - `publishability_state=NOT_READABLE`
  - `terminal_status=HELD` bila readable fallback tersedia
  - `terminal_status=FAILED` bila readable fallback tidak tersedia
- `coverage_gate_status=NOT_EVALUABLE` harus menghasilkan:
  - `publishability_state=NOT_READABLE`
  - `terminal_status=HELD` bila readable fallback tersedia
  - `terminal_status=FAILED` bila readable fallback tidak tersedia
- operational blocker seperti cutoff/seal precondition tidak boleh membuat `READABLE` walaupun coverage PASS.

---

## Publishability rule

Publishability adalah state hasil finalize/outcome, bukan hasil langsung dari coverage repository atau pointer repository.

Rules:

- `READABLE` hanya valid bila:
  - `terminal_status=SUCCESS`
  - `coverage_gate_status=PASS`
  - sealed publication tersedia
  - pointer sync berhasil bila publish target adalah current publication
- `NOT_READABLE` wajib untuk:
  - `terminal_status=HELD`
  - `terminal_status=FAILED`
  - coverage `FAIL`
  - coverage `NOT_EVALUABLE`
  - coverage `BLOCKED`
- tidak ada state override seperti `READABLE_WITH_OVERRIDE`.
- publishability tidak boleh diubah oleh read-side repository, evidence export, replay, atau command output.

---

## Fallback rule

Fallback hanya boleh menggunakan publication yang sudah valid secara publishability, bukan menghitung coverage candidate secara langsung.

Fallback target wajib memenuhi semua syarat:

- pointer-resolved publication;
- `run.terminal_status=SUCCESS`;
- `run.publishability_state=READABLE`;
- `run.coverage_gate_state=PASS`;
- `publication.seal_state=SEALED`;
- `publication.sealed_at` tidak null;
- `run.sealed_at` tidak null;
- pointer `run_id`, `publication_id`, dan `publication_version` sinkron dengan publication row.

Fallback tidak boleh:

- ke publication coverage `FAIL`;
- ke publication coverage `NOT_EVALUABLE`;
- ke `NOT_READABLE`;
- recursive;
- random;
- implicit tanpa reason code;
- mengubah candidate gagal menjadi readable.

Fallback chain dikunci satu level: current failed/held run hanya boleh kembali ke prior readable current publication yang sudah ada.

---

## Pointer rule

`eod_current_publication_pointer` hanya boleh menunjuk publication yang memenuhi:

- `run.terminal_status=SUCCESS`
- `run.publishability_state=READABLE`
- `run.coverage_gate_state=PASS`
- `pub.seal_state=SEALED`
- `pub.is_current=1`
- `run.is_current_publication=1`
- pointer metadata sinkron dengan publication metadata

Pointer tidak boleh pindah karena:

- coverage `FAIL`;
- coverage `NOT_EVALUABLE`;
- fallback candidate yang tidak readable;
- force replace tanpa candidate `SUCCESS + READABLE + coverage PASS`;
- non-current repair candidate.

---

## Consistency invariants

Invariant wajib:

| Invariant | Meaning |
|---|---|
| `coverage FAIL => publishability NOT_READABLE` | coverage gagal tidak bisa readable |
| `coverage NOT_EVALUABLE => publishability NOT_READABLE` | source/coverage tidak jelas tidak bisa readable |
| `publishability READABLE => coverage PASS` | readable selalu butuh coverage pass |
| `publishability READABLE => terminal SUCCESS` | readable tidak boleh HELD/FAILED |
| `fallback => target READABLE` | fallback selalu ke publication readable |
| `fallback => target coverage PASS` | fallback tidak boleh ke prior bad coverage |
| `pointer => READABLE` | pointer hanya consumer-safe |
| `pointer => coverage PASS` | pointer tidak boleh menutupi coverage failure |

---

## Edge-case decision table

| Case | Expected run state | Expected publishability | Expected pointer behavior |
|---|---|---|---|
| Coverage PASS + sealed + cutoff ok | `SUCCESS` | `READABLE` | pointer may move to candidate |
| Coverage PASS + stale/hard quality blocker | `FAILED` or `HELD` according blocker/fallback | `NOT_READABLE` | pointer does not move to candidate |
| Coverage FAIL + previous readable exists | `HELD` | `NOT_READABLE` | pointer remains/restores previous readable |
| Coverage FAIL + no previous readable | `FAILED` | `NOT_READABLE` | pointer empty or unchanged; no fallback |
| Coverage NOT_EVALUABLE + previous readable exists | `HELD` | `NOT_READABLE` | pointer remains/restores previous readable |
| Coverage NOT_EVALUABLE + no previous readable | `FAILED` | `NOT_READABLE` | pointer empty or unchanged; no fallback |
| Fallback target NOT_READABLE | reject fallback | current run remains non-readable | pointer must not point to fallback target |
| Fallback target coverage FAIL | reject fallback | current run remains non-readable | pointer must not point to fallback target |
| Non-current repair candidate | `SUCCESS` allowed only as non-readable repair artifact | `NOT_READABLE` | pointer not moved to repair candidate |
| Mixed source data without explicit contract | blocked/fail-safe | `NOT_READABLE` unless coverage contract authorizes basis | pointer not moved |

---

## Command/evidence visibility rule

Operator-facing output must expose enough state to see cross-consistency:

- `coverage_gate_state`
- `coverage_ratio`
- `coverage_threshold`
- `terminal_status`
- `publishability_state`
- `trade_date_effective`
- `reason_code` / `final_reason_code`
- fallback/effective-date note when fallback is used
- current pointer publication/run when command scope resolves it

Command output must not create or override any state.

---

## Implementation lock

Implementation wajib menjaga contract ini melalui:

- finalize state matrix guard;
- publication finalize outcome guard;
- pointer repository read filter;
- pointer promotion guard;
- fallback restore guard;
- invalid current pointer diagnosis including coverage state.

Any future change that allows partial coverage, manual override, mixed source coverage, or non-current publication behavior must update this contract first and must not silently weaken these invariants.
