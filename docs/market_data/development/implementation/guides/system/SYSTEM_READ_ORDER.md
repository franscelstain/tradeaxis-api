# System Read Order

> **Role:** supporting implementation navigation only. Canonical start/resume authority is `docs/market_data/START_HERE.md` plus current governance/canonical records.

## Current mandatory outer read order

1. `docs/market_data/START_HERE.md`
2. `docs/market_data/authority/strategy/README.md`
3. `docs/market_data/authority/governance/README.md`
4. `docs/market_data/authority/governance/MARKET_DATA_DOCUMENT_AUTHORITY.md`
5. `docs/market_data/authority/governance/CURRENT_VERIFICATION_REBASELINE_STANDARD.md`
6. `docs/market_data/authority/governance/RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md`
7. `docs/market_data/development/implementation/MD_IMPLEMENTATION_STAGE_REGISTER.md`
8. current canonical records needed to resolve the active Stage/Attempt/Baseline/Epoch, blockers, evidence, and single exact resume point
9. the strategy subset and traceability rows owned by the selected proof obligation
10. referenced/required raw runtime artifacts and actual code/tests only as needed to execute or verify that proof.

This guide MUST NOT restore the pre-normalization `book/`, `audit/`, or W00..W22 read order as current authority. Those paths/records may still exist as current strategy subtrees or historical material, but `START_HERE.md` and the current authority/record architecture control navigation.

## Storage/runtime artifact rule

Do not begin normal work by recursively scanning `storage/**`.

First determine the current proof obligation from canonical docs. Then:

- read the relevant current governed evidence;
- inspect the specific raw artifact/manifest referenced by that evidence or required by the runtime proof;
- verify required execution/path/hash/manifest integrity;
- rerun only when proof is missing, invalid, stale, or the current attempt requires a new execution.

Pure document-governance work does not require broad storage inspection.

## Historical material

Historical W00..W22 verdicts, legacy audits, inventories, and old proof remain supporting context only. They do not determine current stage/verdict and their storage artifacts do not become current evidence without current revalidation.

## Downstream consumer intake

Downstream consumers should use owner-facing Market Data contracts/read models from current strategy authority. They must not infer consumer semantics from raw bars, raw indicators, storage artifacts, session-snapshot internals, or historical technical evidence.
