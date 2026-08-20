# Legacy Semantic Extract — LX-MD-0042-EVD-04

- Source ID: `LS-MD-0042`
- Original path: `audit/REPLAY_DETERMINISM_RUNTIME_PROOF_INVENTORY.md`
- Original SHA1: `7E5FB7DE9A03E174497EC8911DE7215EE2F3EEEC`
- Extract role: `EVIDENCE`
- Source range: `L94-L124`
- Extract body SHA1: `464A9D97D30DB39BEDD3046F75382A5AB3AE4DEE`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Final Historical Non-Current Replay Runtime Proof Closure

Historical non-current replay proof is now LOCKED for this source ZIP.

Artifact paths:

- `storage/app/market-data/full-production-ready/runtime/historical-replay/fixtures/run-2-publication-2/manifest.json`
- `storage/app/market-data/full-production-ready/runtime/historical-replay/verify-run-2-publication-2/replay_result.json`
- `storage/app/market-data/full-production-ready/runtime/historical-replay/evidence-export-replay-8/evidence_admission.json`
- `storage/app/market-data/full-production-ready/runtime/historical-replay/evidence-export-replay-8/replay_result.json`

Required semantic proof:

- `publication_id=2`
- `publication_run_id=2`
- `publication_is_current=false`
- `historical_publication_allowed=true`
- `current_pointer_required=false`
- `current_pointer_status=NOT_CURRENT_POINTER`
- `replay_actual_resolution_mode=HISTORICAL_PUBLICATION_AUDIT`
- `replay_publication_scope=HISTORICAL_SEALED_PUBLICATION`
- `comparison_result=MATCH`
- `replay_status=PASS`
- `mismatch_count=0`
- `evidence_admission_state=ADMITTED_COMPLETE`

Final validation supplied:

- `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` -> OK (57 tests, 904 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (170 tests, 3950 assertions).
- Full `vendor/bin/phpunit tests/Unit/MarketData` -> OK (453 tests, 6671 assertions).

<!-- LEGACY_EXTRACT_BODY_END -->
