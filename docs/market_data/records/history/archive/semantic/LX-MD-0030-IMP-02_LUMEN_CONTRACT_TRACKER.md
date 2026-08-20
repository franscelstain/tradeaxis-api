# Legacy Semantic Extract — LX-MD-0030-IMP-02

- Source ID: `LS-MD-0030`
- Original path: `audit/LUMEN_CONTRACT_TRACKER.md`
- Original SHA1: `88A4CD4C9D12A578A2837DCB275B315C91D1492A`
- Extract role: `IMPLEMENTATION`
- Source range: `L3508-L3529`
- Extract body SHA1: `EC3243C6EEFE10F5D7636FAF775CA3EC35F70054`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-20 Final Lock Patch — Contract Update

- Contract status for current patched ZIP: `LOCKED`.
- Source ZIP/session: `tradeaxis-api-correction-lifecycle-hardening-202605200904.zip`.
- Historical note: prior `LOCKED_RUNTIME_REPLAY_AND_FAILED_CORRECTION_PROOF` evidence is retained as historical proof, but it is superseded for the current source state because the final audit found unchanged correction evidence aliasing preserved baseline publication `5` as candidate/new publication.
- Contract rule added/clarified:
  - For unchanged / consumed-current corrections, evidence must never fallback candidate or new publication identity to the preserved baseline/current publication.
  - `baseline_publication_id` / `preserved_publication_id` identify the current publication kept readable.
  - `discarded_candidate_publication_id` identifies the candidate produced by the correction run and discarded as unchanged.
  - `replacement_publication_id` must be `null` and `publication_switch=false` for unchanged corrections.
  - If discarded candidate publication cannot be resolved from traceable runtime source, evidence must fail closed with `CORRECTION_DISCARDED_CANDIDATE_PUBLICATION_MISSING` instead of inventing baseline-as-candidate.
- Current source evidence patched:
  - Correction `3` evidence now matches replay run `8`: baseline/preserved publication `5`, discarded candidate publication `7`, replacement publication `null`, publication switch `false`, and unchanged outcome.
  - Failed correction `4` proof remains unchanged and valid as preserved-baseline/no-replacement evidence.
- Current blocker to relock:
  - Artisan/PHPUnit cannot be executed in this container because PHP `8.4.16` is outside the clean-output baseline and required PHPUnit extensions are missing.
  - Relock requires supported local proof with targeted Correction/Evidence/Replay/StaticGuard/AuditDocs filters and full `tests/Unit/MarketData` PASS.


---



<!-- LEGACY_EXTRACT_BODY_END -->
