# MD Stage Closure Manifest — SC-MD-B18-A001-001

- ID: `SC-MD-B18-A001-001`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A001` / `MD-B18-A001-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260903-001`
- Change Impact Declaration: `CI-MD-B18-A001-001`
- Governed evidence: `E-MD-B18-A001-001`
- Finding raised and discharged in this attempt: `F-MD-B18-A001-001` (P1)
- Predecessor stage closure: `SC-MD-B17-A002-001`
- Dependency: `MD-DEP-0004` discharged for `MD-B18`
- Role: `EVIDENCE`, scope `STAGE_CLOSURE_MANIFEST`, immutable after issue
- Issued at: 2026-09-09T10:00:00+07:00

## Terminal coverage

- Required denominator: **121**
- `SATISFIED`: **121/121**, atomically bound to `E-MD-B18-A001-001`
- Mandatory / conditional-applicable: **117 / 4**
- Conditional pending / transitional: **0 / 0**
- Optional capability: **2**
- Reference/context: **32**, each carrying a recorded stage-entry decision
- Foreign rows altered by binding: **0**

No `MD-B17` verdict is inherited. Every one of the 121 predicates was proven by a guard
executed in this attempt, and each bound row records its proof family and both guard method names,
not merely the evidence id.

## What this attempt had to rebuild before it could bind

The proof surface that existed at stage entry reported `121/121` map coverage with a green readiness
gate and a green self-test, and it could not carry a binding. `F-MD-B18-A001-001` measured two
defects:

- families were assigned by a `strpos()` chain over rule text ending in an unconditional catch-all,
  so **23 of 121 predicates reached a family with no keyword match at all** and earlier branches
  stole predicates from later ones — `MD-S050-R0028`, a bitemporal as-known rule, was filed under
  `evidence` because its sentence contains "pass";
- **103 of 121 positive proofs were source-text greps.** A probe removed the real `DB::rollBack()`
  from `AsKnownReplayExecutionService` and left the identical text in a comment: the static guard
  stayed green, the runtime guard went red.

The map is now an explicit reviewed 121-row table across eleven families with declared per-family
counts. Ten families name guards that execute; `admissibility_boundary` remains a corpus assertion
because its fifteen predicates are about what may be *claimed*, and the gate records that as a
declared proof kind rather than leaving it implicit.

Three further defects were found while proving that work and fixed rather than absorbed: the
bound-input refusal in `ReplayResultRepository` was enforced by code no test reached; the binder
wrote the matrix on invocation with no dry run, pristine check, atomicity assertion or foreign-row
isolation; and its first applied write re-quoted 6226 rows it did not own, which the semantic
isolation check could not see. That write was reverted byte-for-byte and the writer now emits
untouched lines verbatim — the applied binding is exactly 121 changed lines, all `MD-B18`.

## Closure conditions

| Condition | Result |
|---|---|
| Zero transitional required rows | **MET — 0** |
| Zero pending applicability rows | **MET — 0** |
| Complete applicable denominator | **MET — 121/121** |
| Every non-required row carries a recorded decision | **MET — 0 undecided** |
| Every bound row names its proof family and both guards | **MET — 0 unattributed** |
| No foreign row carries this stage's evidence | **MET — 0** |
| Raw artifact existence and manifest hash integrity | **MET — 6 proof + 3 closure artifacts** |
| Governed evidence reachable and correlated | **MET** |

`MarketDataReplayVerificationClosureGate` did not exist for this stage and was built here. It was
then proven fail-closed through eight isolated mutations: each condition made false independently,
all eight caught, controls `PASS` before and after.

## Executed proof

| Execution | Result |
|---|---|
| `B18-LP-003-R2` targeted family guards | PASS — 32 tests / 191 assertions |
| `B18-LP-004-R2` full suite (pre-binding) | PASS — 2057 tests / 20592 assertions |
| `B18-LP-005-R2` family fail-closed probes | 11/11 caught, controls green either side |
| `B18-LP-006-R2` readiness gate and self-test | PASS — self-test 11/11 |
| `B18-LP-007-R2` governance gates | PASS — 5/5 exit zero |
| `B18-LP-008-R2` binder fail-closed probes | 5/5 caught, each for the stated reason |
| `B18-LP-009-R2` closure-condition probes | 8/8 caught, controls PASS either side |
| `B18-LP-010-R2` post-binding gate sweep | PASS — 8 exit zero, plus the deliberately fatal `--pre-binding` self-test run |
| `B18-LP-011-R2` post-binding full suite | PASS — 2057 tests / 20596 assertions |

Raw artifact manifests: `MANIFEST.json` sha256 `1E2E452C7CD0612E1A74C78C2550C10EB3C97CBA5D3BCD83FFA12B8B51812B2D` (6 artifacts);
`MANIFEST-CLOSURE.json` sha256 `F3B70F9FC4ECF4F6D9D039089BC2D612342121883F34E825929881675749FFC7` (3 artifacts).

## Environment note

The first post-binding regression could not execute: MariaDB was down, and 6 errors and 16 skips all
reported `[2002] target machine actively refused it`. No finding or dependency was raised for a
temporarily unavailable environment. The server was restarted and the regression above is the real
execution, not a reinterpretation of the blocked one.

Restarting it also exposed a defect the blocked run had hidden. With the matrix now bound, the
self-test's baseline still ran the gate in pre-binding mode, so its control was red and every
mutation after it was meaningless — the fifth instance of that silent-fallback shape in this package
after `MD-B08`, `MD-B11`, `MD-B12` and `MD-B17`. The self-test now detects the mode, honours an
explicit `--bound` or `--pre-binding` flag, and treats a requested mode that contradicts the matrix
as fatal. Both directions are recorded in `B18-LP-010-R2`.

## Residue and successor

- Residue: `CONFORMANT`. `B18ReplayContractStaticGuardTest` remains in the suite as a cheap wiring
  check and is no longer proof for any predicate; the readiness gate rejects re-pointing a
  behavioural family at it.
- `F-MD-B01-A014-001` remains open and is owned by `MD-B19`. It is not owned by `MD-B18`.
- Successor: `MD-B19` may open. This closure is its stage precondition only; no `MD-B18` predicate
  proof is inherited by it.
