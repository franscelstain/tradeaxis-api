# Legacy Semantic Extract — LX-MD-0035-RES-02

- Source ID: `LS-MD-0035`
- Original path: `audit/MARKET_DATA_PRODUCTION_PROOF_PACK.md`
- Original SHA1: `9D1E95DE0523A6EFF6B7E31DF54056B51CB33F26`
- Extract role: `RESEARCH`
- Source range: `L608-L632`
- Extract body SHA1: `51737BE70200B52A0953477B12C1C5AA773DF120`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-22 - Audit Docs Static Guard Rate-Limit Reconciliation

[SESSION] AUDIT_DOCS_STATIC_GUARD_RATE_LIMIT_RECONCILIATION

[SESSION_STATUS] PATCHED_STATIC_GUARD_EXPECTATION_RERUN_REQUIRED

[FINAL_DECISION]
- `Decision: OPS_RUNTIME_PARITY_PASSED` remains the only valid proof-pack decision while the embedded provider smoke artifact is `provider_smoke_status=PASS` / `reason_code=PROVIDER_SMOKE_OK`.
- A passed ops-runtime-parity decision is now backed by the provider smoke artifact returning `provider_smoke_status=PASS` / `reason_code=PROVIDER_SMOKE_OK` / `http_status=200`.
- Source-state decision remains `MARKET_DATA_PRODUCTION_READY_LOCKED`.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.

[PATCH]
- Updated `AuditDocsSynchronizationStaticGuardTest` to preserve the then-current provider-passed decision and explicitly reject a false proof-pack PASS decision; this historical static-guard patch is superseded by the later final provider-smoke PASS and 2026-06-05 full global lock.
- Reconciled active audit-doc wording so the authoritative artifact is consistently recorded as HTTP 200 / `PROVIDER_SMOKE_OK` / PASS.

[LOCAL_OPERATOR_EVIDENCE]
- The operator rerun showed targeted guards already PASS: `ProductionSchedulerCronStaticGuardTest`, `ProductionValidationRuntimeProofStaticGuardTest`, and `ProviderSmokeSafeModeStaticGuardTest`.
- The only remaining full-suite failure was the stale audit-docs expectation at `AuditDocsSynchronizationStaticGuardTest.php:276`, which expected a proof-pack PASS decision inside `MARKET_DATA_PRODUCTION_PROOF_PACK.md`.

[REQUIRED_RERUN]
- `vendor\bin\phpunit tests/Unit/MarketData --filter "AuditDocsSynchronizationStaticGuardTest"`.
- `vendor\bin\phpunit tests/Unit/MarketData`.

---


<!-- LEGACY_EXTRACT_BODY_END -->
