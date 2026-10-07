# Decision — owner selected Q9 = A1: the AS_KNOWN reason-registry identity is a derived member of the immutable configuration snapshot

- ID: `D-MD-B18-A002-018`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Answers: owner decision Q9 of `E-MD-B18-A002-106`
- Findings / predicate: `F-MD-B18-A002-034`, `F-MD-B18-A002-017` / `MD-S050-R0005` (with `MD-S050-R0014`, `MD-S019-R0071`)
- Authority relied on: `Platform_Config_Registry_LOCKED.md` (`MD-S082`, `:13-21`, `:46`, `:66`, `:79`, `:289-291`), `Replay_Verification_Contract_LOCKED.md` (`MD-S050`, `:30`, `:33`), `D-MD-B18-A002-014` (Q4 = A), precedent `D-MD-B18-A002-008`
- Change impact: `CI-MD-B18-A002-001`
- Issued: 2026-10-07T10:03:34+07:00
- Status: `APPROVED` — project owner, in the 2026-10-07 instruction "RECORD Q9 OWNER DECISION A1 AND CLASSIFY CROSS-STAGE IMPACT"
- Mutability: `IMMUTABLE_AFTER_ISSUE`
- Strategy impact: `NONE`; `MD-STRATEGY-FREEZE-20261005-001` unchanged; no locked resolved-key register row is added

## Owner decision (verbatim, Indonesian, as supplied by the project owner)

> 1. Reason-registry semantic identity untuk as-known replay harus menjadi derived member dari immutable configuration snapshot.
>
> 2. Model ini mengikuti: MD-S082 configuration snapshot semantics; dan D-MD-B18-A002-014 Q4=A semantic-content identity.
>
> 3. Setiap configuration snapshot BARU harus membawa reason-registry semantic content identity yang berlaku untuk snapshot tersebut.
>
> 4. As-known replay harus menggunakan reason-registry identity dari configuration snapshot yang resolved sebagai known at cutoff.
>
> 5. As-known replay tidak boleh menggunakan: current reason registry; latest run capture; placeholder/constant identity; inferred current value.
>
> 6. Historical configuration snapshots yang tidak memiliki member tersebut TIDAK BOLEH: dibackfill; dimutasi; direwrite. Replay terhadap snapshot tersebut tetap fail-closed / BLOCKED sesuai current authority.
>
> 7. Tidak dibuat lifecycle versioning reason registry baru di MD-S085.
>
> 8. Tidak perlu memperluas locked registered-key set hanya untuk memenuhi kebutuhan ini.
>
> 9. Implementation harus dilakukan melalui governed successor remediation untuk MD-B04.
>
> 10. Sebelum downstream/runtime mutation dilakukan, wajib ada explicit impact analysis terhadap: MD-B04; B10; B18; R0025; configuration/content hashes; frozen builds; proof/golden packages yang bergantung pada configuration snapshot.
>
> 11. Candidate-v4 tetap immutable.
>
> 12. Approval candidate-v4 tidak otomatis berlaku untuk build/configuration semantics baru.
>
> 13. Jika implementation change membuat current R0025 proof tidak lagi valid untuk current build/config semantics, successor candidate harus dibuat, independently reviewed, dan owner-approved kembali.

## What the decision binds (restated without adding owner language)

* **Derived-member semantics.** The reason-registry semantic content identity (the `D-MD-B18-A002-014` Q4 = A content: the governed reason-registry entries) is a member of every new immutable configuration snapshot, derived at snapshot creation, under the `MD-S082` snapshot semantics (immutable, canonical content, content hash, effective and recorded times). It is not a registered runtime key; the locked resolved-key register is not extended.
* **AS_KNOWN binding.** An as-known replay binds the member of the configuration snapshot resolved as known at its cutoff, and nothing else: never the current reason registry, the latest run capture, a placeholder or constant, or an inferred current value.
* **Historical snapshots.** Snapshots that do not carry the member are never back-filled, mutated or rewritten. An as-known replay that resolves such a snapshot stays fail-closed (`BLOCKED`, `Replay_Verification_Contract_LOCKED.md:33`).
* **No `MD-S085` versioning lifecycle** is created.
* **Route.** Implementation goes through a governed successor remediation of `MD-B04`. Before any downstream or runtime mutation, the impact on `MD-B04`, `MD-B10`, `MD-B18`, R0025, configuration and content hashes, frozen builds, and proof or golden packages that depend on the configuration snapshot is classified explicitly (`E-MD-B18-A002-107`).
* **Candidate-v4** stays immutable; its approval (`D-MD-B18-A002-016`) does not carry over to new build or configuration semantics; if the implementation makes the current R0025 proof invalid for the current build or configuration, a successor candidate is built, independently reviewed and owner-approved again.

## What this decision does not do

It implements nothing, mutates no snapshot, opens no attempt, changes no proof basis and promotes nothing. `MD-S050-R0005`, `MD-S050-R0014` and `MD-S019-R0071` stay without a reviewed basis; `MD-S003-R0025` stays `PROVEN` until a change actually invalidates it.

## Next

MD-B04 successor remediation entry (MD-B04-A003) under D-MD-B18-A002-018 and E-MD-B18-A002-107: record the dependency (MD-B18-A002 blocked on MD-B04-A003), the MD-B04-A003 baseline and change-impact declaration naming the predicates of E-MD-B18-A002-107 (remediate MD-S082-R0036/R0044; revalidate MD-S082-R0006/R0007/R0009/R0011/R0214/R0220/R0221/R0223), the R0025 lifecycle consequence (basis PROVEN -> INCOMPLETE in the same unit that first mutates the build or snapshot content; candidate-v4 retained as historical evidence) and the B10 regression scope; then implement the derived member and prove it. No B18 AS_KNOWN binding and no candidate-v5 before the MD-B04-A003 closure; candidate-v5 only after the B18 binding code has landed, so that its frozen build is final.
