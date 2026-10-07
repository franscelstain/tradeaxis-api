# Decision — owner decision: the R0025 supported test mirror requires semantic-equivalent proof; the E102 blanket exclusions are not approved

- ID: `D-MD-B18-A002-017`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Findings / predicate: `F-MD-B18-A002-017` / `MD-S003-R0025`
- Applies to: `E-MD-B18-A002-102` (R0025 proof basis PROVEN, issued before this decision); `E-MD-B18-A002-101` (admission, unchanged)
- Decisions relied on: `D-MD-B18-A002-001` (item 4), `D-MD-B18-A002-011`, `D-MD-B18-A002-013`, `D-MD-B18-A002-016`
- Change impact: `CI-MD-B18-A002-001`
- Issued: 2026-10-07T07:27:24+07:00
- Status: `APPROVED` — project owner, in the 2026-10-07 instruction "R0025 — RECORD MIRROR OWNER DECISION AND COMPLETE BOUNDED MIRROR-PROOF CORRECTION"
- Mutability: `IMMUTABLE_AFTER_ISSUE`
- Strategy impact: `NONE`; `MD-STRATEGY-FREEZE-20261005-001` unchanged

## What prompted it

The independent review of `E-MD-B18-A002-102`, as relayed in the owner instruction (the review text and the reviewer identity were not supplied to the repository; none is invented), found that the five engine-only exclusions introduced by that record for the supported-mirror half of `MD-S003-R0025` are unsupported. `E-MD-B18-A002-102` records them as named, pinned exclusions (sealed snapshot-row triggers, `information_schema` column types, the `GET_LOCK` retained-identity restore, concurrent-connection isolation, enforced schema constraints). The authority does not enumerate any such exclusion.

## Owner decision (verbatim, Indonesian, as supplied by the project owner)

> 1. R0025 supported test mirror wajib membuktikan semantic-equivalent behavior untuk seluruh required scenario bullets.
>
> 2. Perbedaan engine TIDAK otomatis membolehkan exclusion.
>
> 3. Hanya exception yang sudah secara eksplisit diizinkan governance yang boleh diperlakukan sebagai mirror exception, termasuk bounded limitations yang memang sudah diputuskan melalui D-MD-B18-A002-001.
>
> 4. Jika SQLite tidak mempunyai production-specific primitive, mirror harus menggunakan mekanisme ekuivalen untuk membuktikan semantic obligation yang sama.
>
> 5. Jika tidak ada semantic-equivalent mechanism yang feasible, MariaDB-only proof baru boleh dianggap cukup setelah explicit authority/governance authorization.
>
> 6. Lima blanket exclusions yang diperkenalkan dalam E102 TIDAK DISETUJUI sebagai blanket exclusions.
>
> 7. Candidate-v4 admission chain melalui E101 tetap valid.
>
> 8. E102 dapat tetap sebagai immutable execution evidence, tetapi conclusion PROVEN-nya memerlukan successor correction/annotation.
>
> 9. R0025 reviewed-basis count tidak boleh dianggap settled 101/113 sampai mirror proof diperbaiki dan divalidasi.

## Scope reading (what this record binds, stated without adding owner language)

* A difference between the two engines does not by itself authorize an exclusion. A mirror assertion is omitted only where governance explicitly allows it.
* `D-MD-B18-A002-001` item 4 is the existing bounded limitation: "Until then SQLite may support behavior but never establish referential integrity or production nullability. Any B18 constraint-bearing predicate still needs actual MariaDB proof; absence of it blocks that predicate despite this deferral." It is bounded to exactly what it says (referential integrity and production nullability); it does not reach other constraints, locks, triggers, metadata or isolation.
* Where SQLite lacks a production-specific primitive, the mirror uses an equivalent mechanism that proves the same semantic obligation. Only where no equivalent mechanism is feasible, and only after explicit authority or governance authorization for that specific obligation, may MariaDB-only proof be treated as sufficient.
* The admission chain `E-MD-B18-A002-100` (independent review PASS), `D-MD-B18-A002-016` (owner approval) and `E-MD-B18-A002-101` (admission) stays valid. Candidate-v4 is not touched.
* `E-MD-B18-A002-102` stays as immutable execution evidence and is not edited. Its PROVEN conclusion needs a successor correction or annotation. The reviewed-basis count of `MD-S003-R0025` is not settled at 101/113 until the mirror proof is corrected and validated.

## What this decision does not do

It does not approve any exclusion, does not decide the mirror proof is complete, does not promote or demote any predicate by itself, does not touch `MD-S050-R0005` or `F-018`, and does not change `B10` (`1072/1072`) or `B17` (`244/257`). It asks the owner nothing further about the mirror policy.

## Next

MD-B18-A002: implement the semantic-equivalent R0025 mirror proof required by D-MD-B18-A002-017 (replace each of the five E102 blanket exclusions independently; only the bounded D001 limitations on referential integrity and production nullability may remain as explicit governed exceptions; keep the MariaDB proof unchanged; re-run the G05 probes with new probes for each mirror-equivalent protection), then stop at an independent-review boundary with the author-produced corrected basis separated from any reviewed basis. MD-S003-R0025 stays INCOMPLETE and the reviewed basis is 100/113 until then. Do not start MD-S050-R0005 or F-018; do not edit candidate-v4, E100, D016, E101 or E102.
