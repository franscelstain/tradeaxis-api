# Legacy Semantic Extract — LX-MD-0037-EVD-01

- Source ID: `LS-MD-0037`
- Original path: `audit/OPS_COMMAND_SURFACE_RUNTIME_MATRIX_INVENTORY.md`
- Original SHA1: `D6E40A3FC4141C4D0798627BD21A5F34418206F8`
- Extract role: `EVIDENCE`
- Source range: `L36-L73`
- Extract body SHA1: `4A8136B48EA798982B2067B1F62BF7401E6886DD`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Command Registry Proof

Command: `php artisan --env=testing list market-data`.
Result: exit 0, current source reports 30 public market-data commands registered. The 2026-05-20 matrix remains the historical 20-command runtime fixture proof; the lifecycle backfill command is public in the current source, the provider-smoke overlay added the safe live-provider command surface and final provider PASS proof, the 2026-06-03 extensions add proof-only full-range current evidence/replay orchestration plus dry-run/apply guarded sector membership, sector-index CSV bar import, and sector-index API bar import, the 2026-06-04 event-risk extension adds dry-run/apply guarded corporate-action plus trading-status source imports, and the invalid 2026-06-05 indicator-only republish command was removed after operator runtime proved it failed seal/hash lifecycle, and `market-data:eod-indicators:recompute-current` is the replacement current-bars recompute command using correction-current lifecycle without source acquisition/bar ingest/source-master writes. Source/master read-only recompute means no source/import/master writes; it does not freeze publication-bound context fields unless a future explicit technical-only mode is implemented and proven.

| Command | Registered | Help Proof | Signature/Docs Sync | Guard Decision |
|---|---:|---:|---|---|
| `market-data:daily` | PASS | PASS | `--requested_date`, `--source_mode`, optional pipeline flags | command-owned date validation |
| `market-data:backfill` | PASS | PASS | parser args optional only for command-owned missing-input output; operator contract still requires start/end dates | `COMMAND_MISSING_REQUIRED_INPUT`, date validation |
| `market-data:backfill:lifecycle` | PASS | PASS | start/end range, source mode, plan/diagnose/resume/evidence/replay options | lifecycle orchestrator owns date/source/checkpoint validation |
| `market-data:backfill:missing-tickers` | PASS | PASS | start/end range, `source_mode=api`, `--ticker_codes`, plan/resume/error-policy/evidence/replay options | ticker-master/current-bars gap scan; plan is non-mutating; lifecycle orchestrator owns promote/evidence/replay |
| `market-data:promote` | PASS | PASS | `--requested_date` or `--run_id`, force replace guarded | date validation, force reason guard |
| `market-data:run:finalize` | PASS | PASS | `--requested_date`, `--source_mode`, `--run_id` | finalize/pointer contract |
| `market-data:eod-bars:ingest` | PASS | PASS | date/source options plus explicit `--request_mode` for stage-by-stage publish proof | command-owned request-mode validation + pipeline input validation |
| `market-data:eod-eligibility:build` | PASS | PASS | date/source options | pipeline input validation |
| `market-data:eod-indicators:compute` | PASS | PASS | date/source options | pipeline input validation |
| `market-data:eod-indicators:recompute-current` | PASS | FULL_RANGE_RUNTIME_AND_REPLAY_PASS_807_OF_807 | start/end date, force reason, dry-run, evidence/replay, continue-on-error | current readable publication required; correction-current lifecycle; no source/bar/master/`eod_bars` writes; unchanged correction exports correction evidence |
| `market-data:audit:hash` | PASS | PASS | date/source options | pipeline input validation |
| `market-data:dataset:seal` | PASS | PASS | date/source options | seal precondition validation |
| `market-data:evidence:export` | PASS | PASS | exactly-one selector required by command | `COMMAND_MISSING_REQUIRED_INPUT` |
| `market-data:evidence-replay:full-range-current` | PASS | PASS | optional start/end date range; omitted range uses current publication pointer min/max; fixture/output/error-continuation options | proof-only current pointer resolver; no import/promote/finalize; `NO_READABLE_PUBLICATION` on missing current readable date |
| `market-data:sector-indexes:ingest-api` | PASS | PASS | start/end date range, provider, symbol suffix/map, dry-run/apply guard | command-owned date/provider validation; default `COMMAND_DRY_RUN_ONLY`; explicit `COMMAND_APPLY_CONFIRMED` on apply; fail-closed on incomplete provider response |
| `market-data:sector-indexes:import-bars` | PASS | PASS | input CSV, source name, dry-run/apply guard | command-owned CSV validation; default `COMMAND_DRY_RUN_ONLY`; explicit `COMMAND_APPLY_CONFIRMED` on apply |
| `market-data:sectors:import-memberships` | PASS | PASS | input CSV, classification system, source name, dry-run/apply guard | command-owned CSV validation; default `COMMAND_DRY_RUN_ONLY`; explicit `COMMAND_APPLY_CONFIRMED` on apply |
| `market-data:events:import-corporate-actions` | PASS | PASS | input CSV, source name, dry-run/apply guard | command-owned CSV validation; default `COMMAND_DRY_RUN_ONLY`; explicit `COMMAND_APPLY_CONFIRMED` on apply |
| `market-data:events:import-trading-status` | PASS | PASS | input CSV, source name, dry-run/apply guard | command-owned CSV validation; default `COMMAND_DRY_RUN_ONLY`; explicit `COMMAND_APPLY_CONFIRMED` on apply |
| `market-data:replay:verify` | PASS | PASS | parser args optional only for command-owned missing-input output; operator contract still requires run id and fixture path | `COMMAND_MISSING_REQUIRED_INPUT`, `replay_status=BLOCKED` |
| `market-data:replay:smoke` | PASS | PASS | parser run id optional only for command-owned missing-input output | `COMMAND_MISSING_REQUIRED_INPUT`, service failure catch |
| `market-data:replay:backfill` | PASS | PASS | parser dates optional only for command-owned missing-input output | `COMMAND_MISSING_REQUIRED_INPUT` |
| `market-data:replay:fixture:generate` | PASS | PASS | parser run id optional only for command-owned missing-input output | `COMMAND_MISSING_REQUIRED_INPUT` |
| `market-data:correction:request` | PASS | PASS | trade date/reason required by command validation | correction baseline guard |
| `market-data:correction:approve` | PASS | PASS | parser id optional only for command-owned missing-input output | `COMMAND_MISSING_REQUIRED_INPUT`, `COMMAND_CORRECTION_NOT_FOUND` |
| `market-data:correction:run` | PASS | PASS | parser id optional only for command-owned missing-input output | `COMMAND_MISSING_REQUIRED_INPUT`, lifecycle status guard |
| `market-data:current-publication:repair` | PASS | PASS | dry-run default, `--apply` guarded by reason | `COMMAND_DESTRUCTIVE_GUARD_REQUIRED` |
| `market-data:session-snapshot` | PASS | PASS | parser args optional only for command-owned missing-input output | `COMMAND_MISSING_REQUIRED_INPUT`, readable publication guard |
| `market-data:session-snapshot:purge` | PASS | PASS | dry-run default, `--apply` explicit | `COMMAND_DRY_RUN_ONLY`, `COMMAND_APPLY_CONFIRMED` |
| `market-data:provider:smoke` | PASS | PASS | safe single-ticker dry-run provider smoke; `--json` emits JSON stdout; `--provider` overrides API provider config | `PROVIDER_SMOKE_TICKER_REQUIRED`, `PROVIDER_SMOKE_FULL_UNIVERSE_BLOCKED`, final live proof `provider_smoke_status=PASS` / `reason_code=PROVIDER_SMOKE_OK` / `http_status=200` |


<!-- LEGACY_EXTRACT_BODY_END -->
