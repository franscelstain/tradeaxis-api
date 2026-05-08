# RUN / PUBLICATION / POINTER LINKAGE INVENTORY

Status: `LOCKED_LOCAL_PHPUNIT_PASS`
Updated: 2026-05-08

This inventory records the enforced lineage chain for market-data publication lifecycle:

`run -> publication -> current pointer -> correction baseline/replacement -> replay/evidence/command proof`

| Area | Run Link | Publication Link | Pointer Link | Correction Link | Validation | Gap | Patch | Test Result |
|---|---|---|---|---|---|---|---|---|
| run creation | `eod_runs.run_id` created by owning run repository | candidate publication later binds to `run_id` | none at creation | correction stores `prior_run_id/new_run_id` once execution starts | lifecycle event context | none found | no behavior change | local PHPUnit PASS |
| run terminal state | `terminal_status/publishability_state/coverage_gate_state` | publication promotion requires `SUCCESS + READABLE + PASS` | pointer target requires same run state | correction cannot publish replacement unless final run succeeds | `MarketDataInvariantGuard::assertNoBypassState` + repository promotion guard | none found | reason-coded linkage failures | local PHPUnit PASS |
| publication candidate | candidate has `run_id`, `trade_date`, `publication_version` | `getOrCreateCandidatePublication` creates lineage | not current until switch | candidate can become correction replacement | hash/seal/manifest validation | baseline/replacement publication ids were not explicit in correction table | added correction publication lineage columns | local PHPUnit PASS |
| publication sealed | run and publication hash/seal mirror | candidate publication gets `SEALED` only after hash context | pointer rejects unsealed target | replacement must be resealed before publication | seal/finalize guards | none found | preserved hash/seal validation | local PHPUnit PASS |
| publication promoted | run mirrors `publication_id/publication_version` | publication `is_current=1` | pointer row stores `publication_id/run_id/publication_version/sealed_at` | correction published stores baseline and replacement ids | post-switch resolver check | reason-code vocabulary incomplete | registry/seed expanded | local PHPUnit PASS |
| current pointer | run/publication/pointer mirror enforced | pointer target joined to publication | `eod_current_publication_pointer` is authoritative | correction baseline is pointer-resolved | `resolveCurrentReadablePublicationForTradeDate` | none found | mirror guard strengthened | local PHPUnit PASS |
| pointer resolver | validates `SUCCESS + READABLE + PASS + SEALED` | rejects stale/orphan target | resolver returns only valid readable current | baseline resolver uses same contract | repository and invariant guard | none found | run-publication mirror helper added | local PHPUnit PASS |
| pointer switch | run is prepared before switch | candidate validated before update | demote/promote/pointer update/post-verify inside transaction | correction switch uses baseline id as expected prior | repository transaction + post-switch validation | reason-code vocabulary incomplete | added reason-coded failure strings | local PHPUnit PASS |
| publication demotion | old run current mirror cleared | old publication `is_current=0` | pointer moved atomically | baseline preserved on failed/unchanged correction | transaction guarded | none found | documented final rule | local PHPUnit PASS |
| force replace | replacement run is validated | replacement publication must be sealed/readable | pointer replacement blocked unless explicit force | not a correction shortcut | operator flag + audit reason | force event had no dedicated reason code | event now uses `CURRENT_PUBLICATION_FORCE_REPLACED` | local PHPUnit PASS |
| correction request | no replacement yet | no replacement yet | no pointer mutation | correction row starts requested lifecycle | correction repository status guard | correction publication ids absent | added nullable baseline/replacement fields | local PHPUnit PASS |
| correction approval | prior/current not mutated | no replacement yet | no pointer mutation | approved status only | status guard | none found | no behavior change | local PHPUnit PASS |
| correction run | `prior_run_id/new_run_id` stored | baseline/replacement publication ids stored as they become known | baseline resolved from current pointer | explicit baseline publication id persisted | repository + pipeline | publication lineage incomplete | pipeline passes baseline/replacement ids | local PHPUnit PASS |
| correction unchanged | final run points to preserved baseline publication | candidate discarded when no change | pointer remains baseline | `baseline_publication_id` preserved, replacement null | diff service + preserve baseline branch | publication id not persisted before | persisted baseline id | local PHPUnit PASS |
| correction failed | final state fail-safe | invalid candidate not current | previous pointer restored/preserved where possible | baseline publication id stays traceable | pointer recovery branch | none found | reason-code matching expanded | local PHPUnit PASS |
| correction published | new run succeeds | replacement publication id traceable | pointer target becomes replacement after validation | baseline and replacement ids persisted | post-switch validation | publication id not persisted before | persisted baseline/replacement ids | local PHPUnit PASS |
| replay | expected/actual run context compared | expected/actual publication context compared | pointer target compared | correction baseline/candidate compared | replay deterministic comparison | needed new table fields fallback support | replay reads `baseline_publication_id/replacement_publication_id` | local PHPUnit PASS |
| evidence | run context exported | publication context exported | pointer context exported | correction baseline/candidate exported | lineage context builder | needed new table fields fallback support | evidence reads `baseline_publication_id/replacement_publication_id` | local PHPUnit PASS |
| session snapshot | read-side depends on current readable pointer | publication scoped | pointer-resolved only | no direct correction mutation | read-side contract | none found | no behavior change | local PHPUnit PASS |
| read-side consumer | run must be readable/current | publication must be sealed/current | pointer is authoritative | no raw correction shortcut | read-side static guards | none found | no behavior change | local PHPUnit PASS |
| command output | run id emitted | publication id/version emitted | lineage summary emitted | correction commands show lifecycle status | command summary payload | lineage summary incomplete | added `renderLineageSummary` | local PHPUnit PASS |
| static guard | run linkage guarded | publication linkage guarded | pointer linkage guarded | correction linkage guarded | `RunPublicationPointerLinkageStaticGuardTest` | missing dedicated guard | added static guard | container syntax PASS only |

## Final enforced rules

1. Every readable/current publication must be traceable to a valid originating run.
2. Run-publication mirror must agree on `run_id`, `publication_id`, `publication_version`, and trade date when those fields are present.
3. Current pointer must resolve to a publication whose run is `SUCCESS + READABLE + coverage PASS` and whose publication is `SEALED`.
4. Pointer switch must validate candidate state before mutation and verify resolver output after mutation.
5. Correction baseline must be pointer-resolved from an existing current readable publication.
6. Correction replacement must be represented by a valid replacement publication before pointer switch.
7. Failed or unchanged correction must preserve baseline pointer and record baseline lineage.
8. Evidence and replay must carry enough lineage context to prove run/publication/pointer/correction state without ad-hoc database queries.
9. Command output must expose run-publication lineage summary for operator traceability.
10. No current/read-side/fallback resolver may use raw/staging/latest/`MAX(trade_date)` shortcuts.

## Validation status

Container validation is static only because uploaded ZIP has no `vendor/`.

- `php -l` PASS for changed PHP files.
- Operator-local targeted and full PHPUnit validation passed.
- Contract status is `LOCKED_LOCAL_PHPUNIT_PASS` for this source-of-truth ZIP.
