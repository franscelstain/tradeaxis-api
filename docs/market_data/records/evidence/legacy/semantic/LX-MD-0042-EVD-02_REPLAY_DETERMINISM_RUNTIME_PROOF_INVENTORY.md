# Legacy Semantic Extract — LX-MD-0042-EVD-02

- Source ID: `LS-MD-0042`
- Original path: `audit/REPLAY_DETERMINISM_RUNTIME_PROOF_INVENTORY.md`
- Original SHA1: `7E5FB7DE9A03E174497EC8911DE7215EE2F3EEEC`
- Extract role: `EVIDENCE`
- Source range: `L14-L27`
- Extract body SHA1: `1D412D36A03DBC405BD29949F88907E3A94E7344`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Runtime Proof

| Proof | Command | Result |
|---|---|---|
| Command surface | `php artisan list market-data`; replay/evidence help commands | PASS; 20 market-data commands listed and replay/evidence command options exposed |
| Migration | `php artisan migrate --env=testing --force` | PASS; `2026_05_19_000002_add_replay_status_to_replay_daily_metrics` migrated |
| Fixture generation | `php artisan market-data:replay:fixture:generate 2 --case=valid_case --output_dir=storage/app/market-data/replay-determinism-runtime-proof/fixtures/generated-valid-run-2` | PASS; fixture generated from `run_id=2`, `publication_id=2`, `trade_date=2026-02-18` |
| PASS verification | `php artisan market-data:replay:verify 2 storage/app/market-data/replay-determinism-runtime-proof/fixtures/generated-valid-run-2 --output_dir=storage/app/market-data/replay-determinism-runtime-proof/verify-pass` | PASS; `replay_id=2`, `comparison_result=MATCH`, `replay_status=PASS`, `mismatch_count=0` |
| FAIL verification | `php artisan market-data:replay:verify 2 storage/app/market-data/replay-determinism-runtime-proof/fixtures/generated-run-2-reason-code-mismatch --output_dir=storage/app/market-data/replay-determinism-runtime-proof/verify-fail` | FAIL as expected; `replay_id=3`, `comparison_result=MISMATCH`, `replay_status=FAIL`, `mismatch_count=1`, `REPLAY_FINAL_REASON_CODE_MISMATCH` |
| BLOCKED verification | invalid-BOM derived fixture and smoke broken/missing fixtures | BLOCKED as expected; `REPLAY_EXPECTED_PROOF_INCOMPLETE`, `REPLAY_FIXTURE_SCHEMA_MISMATCH`, and `replay_status=BLOCKED` were surfaced |
| Smoke suite | `php artisan market-data:replay:smoke 2 --fixture_root=storage/app/market-data/replay-fixtures --output_dir=storage/app/market-data/replay-determinism-runtime-proof/smoke --generate_runtime_valid_case` | PASS; `all_passed=1`, generated valid case `PASS`, reason-code mismatch `FAIL`, broken/missing fixture cases `BLOCKED` |
| Evidence export linkage | `php artisan market-data:evidence:export --replay_id=2 --trade_date=2026-02-18 --output_dir=storage/app/market-data/replay-determinism-runtime-proof/evidence-export-replay-2` | PASS; `replay_status=PASS`, `evidence_admission_state=ADMITTED_COMPLETE`, `file_count=6` |
| Historical non-current runtime proof | explicit fixture with `--publication_id=<historical_publication_id>` after pointer has moved to a newer readable publication | LOCKED in this source ZIP; artifacts are present under `storage/app/market-data/full-production-ready/runtime/historical-replay/` and prove `replay_status=PASS`, `comparison_result=MATCH`, and `evidence_admission_state=ADMITTED_COMPLETE` |


<!-- LEGACY_EXTRACT_BODY_END -->
