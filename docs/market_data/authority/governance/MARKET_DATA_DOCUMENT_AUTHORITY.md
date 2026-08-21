# Market Data Document Authority

1. Current Market Data behavior is owned only by documents registered in `MARKET_DATA_STRATEGY_AUTHORITY_REGISTRY.csv`.
2. Governance owns how work is verified, recorded, correlated, orchestrated, and closed, not market-data business semantics.
3. Development cannot create or override strategy/governance authority silently.
4. Evidence/decisions/history never become implicit strategy authority.
5. Prior W00–W22 results are historical input. Current verification uses `MD-B00..MD-B22` under the active verification epoch.
6. Watchlist outcomes/policies are outside Market Data acceptance authority.
7. Authority role does not imply universal immutability. Mutability is defined by `DOCUMENT_ROLE_REGISTRY.csv` and `DOCUMENT_CHANGE_POLICY.md`.
8. During normal implementation/revalidation, strategy authority remains frozen. Governance `MUTABLE_TRACEABLE` registries/matrices may be updated as required by their standards; governance `CONTROLLED_REVISION` documents require controlled change.
9. A governance or registry update MUST NOT silently change Market Data strategy meaning or be used to manufacture `PASS/DONE/SATISFIED`.
10. Raw runtime artifacts under configured `storage/**` paths are supporting execution material, not independent strategy/governance/current-verdict authority; their admission into current proof is controlled by `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md`.
