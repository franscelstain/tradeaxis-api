# Market Data — START HERE

## 1. Read current authority

1. [`authority/strategy/README.md`](authority/strategy/README.md)
2. [`authority/governance/README.md`](authority/governance/README.md)
3. [`authority/governance/MARKET_DATA_DOCUMENT_AUTHORITY.md`](authority/governance/MARKET_DATA_DOCUMENT_AUTHORITY.md)
4. [`authority/governance/CURRENT_VERIFICATION_REBASELINE_STANDARD.md`](authority/governance/CURRENT_VERIFICATION_REBASELINE_STANDARD.md)
5. [`authority/governance/RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md`](authority/governance/RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md)

## 2. Current implementation/revalidation

- [`development/implementation/MD_IMPLEMENTATION_BUILD_SEQUENCE.md`](development/implementation/MD_IMPLEMENTATION_BUILD_SEQUENCE.md)
- [`development/implementation/MD_IMPLEMENTATION_STAGE_REGISTER.md`](development/implementation/MD_IMPLEMENTATION_STAGE_REGISTER.md)
- [`authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv`](authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv)

Determine the current Stage/Attempt/Baseline/Epoch, blockers, current evidence, and single exact resume point from canonical records before inspecting runtime artifacts.

## 3. Executed proof / storage read order

`docs` drives navigation; `storage` supplies raw execution material.

After the current proof obligation is known:

1. read the relevant current governed evidence record;
2. inspect only the raw `storage/**` artifact referenced by that evidence or required by the selected runtime/test/replay/backfill proof;
3. verify required execution identity/path/hash/manifest integrity;
4. run/re-run implementation commands/tests only when current proof requires it.

Do not recursively scan all of `storage/**` as a normal start/resume step. A historical raw artifact does not become current proof without current governed evidence correlation.

## 4. Historical material

Prior `W00..W22`, old audit verdicts, implementation ledgers, LUMEN status, inventories, and patches are preserved under `records/history/`. They are supporting history only and never current proof.

## Non-negotiable

- Market Data remains the owner of market facts; Watchlist policy does not belong here.
- Current strategy semantics are frozen by `MARKET_DATA_STRATEGY_FREEZE_MANIFEST.json` during this architecture refactor.
- One physical semantic document has one authoritative role.
- Existing implementation is `NOT_ASSESSED_REVALIDATION_REQUIRED` until current evidence proves conformance.
- `records/evidence/**` is governed proof record; raw `storage/**` artifacts are supporting execution material and are admitted only through current correlation/integrity rules.
