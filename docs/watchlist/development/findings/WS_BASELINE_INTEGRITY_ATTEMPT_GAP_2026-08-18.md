# F-WS-20260818-06 — Missing Work Baseline, Executable Integrity Gate, and Canonical Attempt Record

- **Document Type:** FINDING
- **Status:** RESOLVED
- **Scope:** watchlist / weekly_swing implementation governance
- **Record ID:** F-WS-20260818-06
- **Created:** 2026-08-18

## Original Observation

Current architecture already controls strategy/governance, stage re-entry, residue, and rule-level traceability. However, an implementation attempt still lacked three enforceable controls:

1. no immutable baseline bound the attempt to exact strategy/governance/Market Data contracts and starting source revision;
2. many integrity rules were validated manually or by one-off scripts rather than a canonical executable gate;
3. attempt minimum fields existed in prose but there was no single mandatory reusable attempt-record template.

During review, two examples showed why executable enforcement matters:

- `DOCUMENT_RECORDING_STANDARD.md` contained a duplicate numbered section heading after prior additions;
- `DOCUMENT_CHANGE_LOG.md` already contained a duplicated historical `DOC-CHG-20260818-003` block. Because the log is append-only, this legacy duplication cannot simply be deleted to make a scanner green; it needs an explicit narrow exception/correction lineage.

Additional stale semantic filenames remained in current implementation guidance even when Markdown links resolved to renamed targets, proving that broken-link scanning alone is insufficient.

## Impact

Without these controls:

- evidence can become ambiguous about which authority/source revision produced it;
- a rerun can accidentally validate against a changed strategy/governance baseline;
- structural drift can survive because links still resolve;
- programmers can format attempt evidence inconsistently and omit do-not-repeat/resume/convergence fields;
- stage `DONE` can be harder to reproduce independently.

## Resolution

Accepted by `D-WS-20260818-06`: adopt Work Baseline Lock, executable integrity gate, canonical stage attempt record template, and a narrow integrity-exception registry for immutable legacy defects.
