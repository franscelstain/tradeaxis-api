# Decision — owner selects Direction D: authority correction first, for the freshness state of a readable pre-activation publication

- ID: `D-MD-B18-A002-015`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Findings / predicate: `F-MD-B18-A002-032`, `F-MD-B18-A002-025` / `MD-S003-R0025`
- Answers: owner decision `Q8` of `E-MD-B18-A002-097`, alternative `D` ("Authority change first")
- Change impact: `CI-MD-B18-A002-001`
- Issued: 2026-10-05T13:21:08+07:00
- Status: `APPROVED` — project owner, in the 2026-10-05 instruction "CONTROLLED AUTHORITY CORRECTION AFTER OWNER DECISION Q8"
- Mutability: `IMMUTABLE_AFTER_ISSUE`
- Strategy impact: authorises a controlled strategy revision under `DOCUMENT_CHANGE_POLICY.md` section 2; the revision, its change-log entry and its freeze are recorded separately (`E-MD-B18-A002-098`, `DOC-CHG-20261005-001`)

## Decision

The project owner selected **Direction D**: correct the authority first, then let the fixture follow the corrected authority. The owner's own statement of the semantic intent, as supplied in the instruction (quoted, not paraphrased):

1. "Pertahankan kemampuan publication pada pre-operational/development world untuk tetap data-readable ketika seluruh read-safety requirements yang relevan terpenuhi, termasuk untuk bounded historical/replay proof."
2. "Sebelum operational activation, operational freshness belum boleh dianggap telah dinilai/diaktifkan hanya karena publication dapat dibaca."
3. "Pre-activation readable publication TIDAK BOLEH dipaksa menjadi: FRESH; STALE; DEGRADED; atau NOT_AVAILABLE hanya untuk membuat fixture/proof cocok."
4. "Khususnya, NOT_AVAILABLE tidak boleh digunakan hanya karena current production fallback mengeluarkan nilai tersebut apabila authority mendefinisikannya sebagai tidak adanya consumer-safe result."
5. "Authority harus membedakan secara eksplisit: data/readability availability; dan applicability/assessment dari operational freshness."
6. "Current production behavior bukan source of truth untuk menetapkan expected semantics."
7. "Candidate-v3 tetap immutable reviewed history dengan verdict: REVIEWED — CHANGES REQUIRED."
8. "Setelah authority correction selesai dan direlock secara governed, lakukan explicit B10/B18 impact classification SEBELUM candidate-v4 dibuat."

The instruction also states that the owner should not be asked to choose among `A`/`B`/`C`/`D` again, and that a literal or name may be chosen by the Agent, documented, when all candidate names express the same already-authorised semantics.

## What this decision authorises

- A bounded controlled correction of the strategy documents that own the freshness vocabulary and the readable-versus-freshness relation, following `DOCUMENT_CHANGE_POLICY.md` section 2 (finding, evidence, reviewed decision, explicit authorisation, change-log entry, successor freeze, invalidation and governed revalidation of affected current verification).
- Reuse of an existing governed state where one exactly represents the condition; otherwise the smallest explicit representation that follows existing naming and contract conventions, with the reason documented.
- A stop with a narrowly scoped owner question only if the corrected authority still leaves materially different semantic models that current authority and this decision cannot resolve.

## What this decision does not do

It creates no candidate-v4 and changes no runtime code, migration, production configuration, replay fixture, expected hash or publication serializer. It approves no package and promotes no `MD-S003-R0025` predicate. It does not reopen `MD-B10` or any other stage by assumption: the impact is traced and classified in the correction record. It adds no reviewer identity, review artifact or historical evidence. `MD-S003-R0025` stays `INCOMPLETE`; `F-MD-B18-A002-032` and `F-MD-B18-A002-025` stay open until their own closure conditions are met.
