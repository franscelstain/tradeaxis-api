# MD Change Impact Declaration — CI-MD-B00-A003-001

- ID: `CI-MD-B00-A003-001`
- Stage / Attempt / Baseline / Epoch: `MD-B00` / `MD-B00-A003` / `MD-B00-A003-BL001` / `MD-REBASELINE-20260820-001`
- Record class: `MUTABLE_TRACEABLE`
- Issued: 2026-08-22, after the A003 baseline lock and before any A003 test, tooling, or record mutation.

## Why re-enter a stage that is already DONE

`MD-B01` and `MD-B03` are both held by governance decisions — `MD-DEP-0005` and `MD-DEP-0006` — with no implementation remediation path. Neither can advance.

`MD-B00` is `DONE`, and its closure declared one finding deferred rather than fixed: `F-MD-B00-A001-003`, a hole in the check that seals the legacy semantic extracts. The deferral was legitimate; the finding itself calls the risk narrow and acceptable to record rather than repair. What changed is not its severity but the alternative. Leaving a known, specified, executable hole open while there is nothing else to build is a choice, not a constraint.

Re-entering a `DONE` stage is precedented here: `MD-B00-A002` re-entered after `DOC-CHG-20260821-001` and issued a successor closure manifest without editing the prior one.

Two further defects were found while confirming the state and are declared here rather than absorbed silently:

- **Three `OPEN` findings are invisible in canonical current state.** `GenerateMarketDataCurrentState.php` reads findings from the current stage's register row, so any finding owned by another stage never reaches `CURRENT_STATE.md`. `F-MD-B00-A001-003` has been invisible since `MD-B00-A001`, and `F-MD-B03-A003-001` was invisible the moment it was raised. §20 requires canonical current state to stay synchronized.
- **Finding records use two lifecycle field names.** Most write `- Status:`; `F-MD-20260821-03` writes `- State:`. Any reader scanning one name gets an incomplete corpus.

## Affected strategy rules

None. `MD-B00` owns zero traceability rows and no strategy byte is read or written.

## Planned proof method

1. **Reproduce the seal hole before fixing it**, isolating the specific check rather than reading the gate's exit code — unrelated gate state can make an exit code say the wrong thing about one check.
2. Fix it in the form the finding prescribes: a structural assertion that an extract contains its header, its sealed body, and an optional trailing newline. **Not** a wider hash — extending the hash would break the reconstruction proof, which must stay bound to the exact original source range.
3. Cover every extract the split index references, not only one directory, and pair the assertion with a positive locator so a file that lost its markers cannot pass by having nothing left to check.
4. Make the current-state generator read the findings corpus, accepting both lifecycle field names rather than restructuring a `LIFECYCLE_UPDATE_ONLY` record to suit the reader.
5. Add a guard that fails when a finding's lifecycle is unreadable or an open finding is missing from the generated current state, so a stale document is caught rather than trusted.
6. Prove every guard fails closed under a landed mutation, and add a population floor to the extract scan.

## Affected areas

| Area | Impact |
|---|---|
| Strategy | None. |
| Governance | `MarketDataDocumentationIntegrityGate` gains a check. The gate is `TECHNICAL_TEST` / `MUTABLE_TRACEABLE`. |
| Schema / migrations / configuration / application code / runtime | No mutation. |
| Legacy extracts | **No mutation.** They are `HISTORICAL_ONLY`; the point of this attempt is that they cannot be edited unnoticed. Mutations used to prove the guard are restored and verified. |
| Tests / tooling | Material: one new gate check, one new guard suite, one generator change. |
| Traceability | None. `MD-B00` holds no rows. |
| Evidence | Additive A003 evidence and a successor closure manifest. `SC-MD-B00-A002-001` and all prior records remain unedited. |
| Raw artifacts / storage | None. |

## Compatibility and residue risk

The dominant risk is fixing the seal in a way that breaks the reconstruction proof. The body hash must keep covering exactly the original source range; a "stronger" hash over the whole file would make all 43 sources fail to reconstruct and would look like a regression in the thing the seal exists to prove.

Second risk: a mutation left applied to a `HISTORICAL_ONLY` extract. Every probe writes to a real sealed record, so each must be restored from a pre-mutation copy and the restoration verified by re-running the check, not assumed.

Third risk: reading a gate's exit code as a verdict on one check. The first reproduction attempt in this attempt did exactly that and was contaminated by an unregistered baseline lock — the exit code said `1` for a reason unrelated to the mutation.

## Dependencies and relationships

- Successor to `MD-B00-A002`; predecessor baseline `MD-B00-A002-BL001`.
- Closes `F-MD-B00-A001-003`, deferred at `MD-B00-A002` closure.
- `MD-DEP-0005` and `MD-DEP-0006` are unaffected and remain the blockers for `MD-B01` and `MD-B03`.

## Strategy semantic change

`NO`.

## Executed impact and result

- Strategy, schema, migrations, configuration, application code, runtime behaviour, and **legacy extract bytes** changed: `NO`. Every probe written to a sealed extract was restored and the restoration verified by re-running the check over all 428 files.
- **`F-MD-B00-A001-003` CLOSED by remediation.** The hole was reproduced first — text inside the sealed body gave `FAIL errors=2`, the same text after `LEGACY_EXTRACT_BODY_END` gave `PASS errors=0` — then closed by `LEGACY_EXTRACT_STRUCTURE` over **428 extracts across all three legacy directories**, not the 294 the finding measured. The body hash is untouched, so the 43-source reconstruction proof still binds each extract to its exact original source range.
- **Three previously invisible `OPEN` findings now reach canonical current state.** `CURRENT_STATE.md` went from naming 2 findings to naming all of them. `F-MD-B00-A001-003` had been invisible since `MD-B00-A001`; `F-MD-B03-A003-001` was invisible from the moment it was written.
- **Lifecycle field inconsistency handled in the reader, not the record.** `F-MD-20260821-03` is `LIFECYCLE_UPDATE_ONLY`, so it keeps its `State:` field and the reader accepts both names. An unreadable lifecycle counts as open.
- Tooling: `LEGACY_EXTRACT_STRUCTURE` added to the documentation integrity gate with a 400-extract population floor; `GenerateMarketDataCurrentState.php` now reads the findings corpus; `FindingRecordConsistencyTest` added (3 tests / 13 assertions).
- Test execution: **1680 tests, 11402 assertions, 0 failures** — 1677 before this attempt plus the 3 added. All six gates PASS.
- Negative proof: five mutations, each verified as landed, each turning its guard red — append after the sealed body, duplicated body-end marker, renamed header, an open finding removed from current state, and a finding stripped of its lifecycle field.
- **A verification error worth recording:** the first reproduction read the gate's process exit code, which was already `1` because this attempt's own baseline lock was not yet registered. The exit code was correct about the gate and wrong about the mutation. The check status was isolated and read directly instead.
- The relationship completeness gate caught two edges declared in record columns but not registered as rows; both were added and the gate re-run.
- Closure: `SC-MD-B00-A003-001` issued, superseding `SC-MD-B00-A002-001` for sufficiency only. The prior manifest is retained immutable and unedited.
- Raw artifacts/storage: none required, produced, or claimed.

## Current boundary

`MD-B00` remains `DONE` with its deferred finding now closed rather than declared. The resume point is unchanged and still not implementation-executable: `MD-B01` waits on `MD-DEP-0005` and `MD-B03` on `MD-DEP-0006`, both reviewed governance decisions.
