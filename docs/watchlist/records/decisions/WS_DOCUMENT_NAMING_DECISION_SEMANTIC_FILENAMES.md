# Weekly Swing Documentation Naming Decision — Semantic Canonical Filenames

## Decision

Current canonical Weekly Swing strategy files use semantic filenames without legacy numeric prefixes.

Authoritative build/execution order is represented only by lifecycle stages `WS-S00..WS-S11` in `../../authority/strategy/WS_END_TO_END_STRATEGY_LIFECYCLE.md`.

## Reason

Legacy prefixes such as `00_`, `08_`, `22_`, and `24_` originated from document evolution and no longer matched lifecycle order. Keeping them risked making implementers infer a false sequence from filenames.

## Scope

This is a documentation naming/path decision only. It does not change Weekly Swing strategy behavior, thresholds, gates, ranking semantics, or proof requirements.

## Historical Preservation

The immediately preceding filenames are preserved under `../history/documentation_migrations/2026-08-17_semantic_strategy_filename_rename/`.
