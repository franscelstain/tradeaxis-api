# F-MD-B19-A001-020 — Commands and runbook execution gaps

- Status: `OPEN`. Attempt: MD-B19-A001. Baseline: MD-B19-A001-BL001. Source: MD-S064 and MD-S036-R0013. Evidence: E-MD-B19-A001-028. Change impact: CI-MD-B19-A001-001.

E028 executed `artisan list --raw` and JSON help for all 41 registered `market-data:*` commands. Three registered commands were missing from the operator index and are now listed. `B19RuntimeCommandCatalogTest` compares every registered name and command-specific option to `commands/README.md`; an invented option made it red, and the index was restored. This establishes only MD-S064-R0010, pending independent review. Existing 64 command-safety tests/371 assertions passed but are not substituted for live command-family workflows.

The other 16 commands_and_runbook predicates remain without an independently admitted basis: MD-S036-R0013 and MD-S064-R0001 through R0009, R0011 through R0016. Runtime help establishes syntax, not preflight dates, bounded backfill, validated promotion, historical rollback, replay/evidence integrity, retention, apply preview, force boundaries, anti-mutation, exit taxonomy, linked retry or the complete production-runtime proof gate. E029 registers and executes `market-data:status` and `market-data:scheduled`; a generic historical pointer rollback command is still not established by current runtime help. These are executable-surface and proof gaps; documentation examples are not treated as execution. No applicability or denominator changes are proposed.

The next runbook batch must independently execute each command family and its negative/refusal path on the supported runtime, identify any missing producer behavior, and prove mutation sensitivity before increasing the reviewed count. Known F018 escalation/closure, R0324 under D008/F016, MD-DEP-0025, alias-meaning and static coverage failures remain separate.


## E029 progress amendment

E029 adds proposed positive/negative bases for MD-S064-R0002 and R0003 using actual MariaDB registered-command runs: read-only status/frontier reporting and a scheduled import-to-promotion-to-verified-status chain with idempotent retry, due-date provider failure, preserved prior current publication, lock refusal, and missing-calendar refusal. The registered command catalog was rerun with 43 commands and 89 assertions; E028-accepted MD-S064-R0010 remains the same basis and is not double-counted. Fourteen command predicates remain without a complete real runtime command-specific proof: MD-S036-R0013 and MD-S064-R0001, R0004-R0009, R0011-R0016. Mock adapter tests and runtime help remain supporting evidence only.

## E034 progress amendment

E034 adds six proposed command bases: `MD-S036-R0013` and `MD-S064-R0004`, `R0005`, `R0008`, `R0014`, and `R0015`, using actual command execution, MariaDB run-evidence export, immutable event-trail refusal, telemetry checks, replay comparisons, and operator-facing refusal output. Eight command predicates remain without candidate bases: `MD-S064-R0001`, `R0006`, `R0007`, `R0009`, `R0011`, `R0012`, `R0013`, and `R0016`. The accepted total remains 595/750; the proposed total is 620/750 pending independent review. E034 does not close this finding or claim command-family completion.
