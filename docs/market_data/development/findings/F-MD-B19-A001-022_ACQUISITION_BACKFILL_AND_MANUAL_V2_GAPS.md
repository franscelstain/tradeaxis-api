# Finding — F-MD-B19-A001-022

- ID: `F-MD-B19-A001-022`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Raised at: 2026-10-20T12:30:00+07:00
- Severity: `P2`
- Status: `OPEN`
- Evidence: `E-MD-B19-A001-031`
- Change impact: `CI-MD-B19-A001-001`

## Exact held obligations

The E031 selected scope has 43 predicates. The current proposal establishes 31 new bounded reviewed bases and holds these 12; the per-predicate record `E031_per_predicate_map.json` supplies the governing text, source line and raw-proof gap for each.

| Predicate | Missing authority or execution |
| --- | --- |
| `MD-S053-R0029` | Manual-file correction/replay ingestion is unproved under current V2. A real local-file candidate under the default V2 profile reaches import/reprocess but promotion refuses `ARTIFACT_PROVIDER_SOURCE_REQUIRED`; assigning a provider to a local file without an actual source fact would invent provenance. The legacy V1 historical-fill success supports R0030 only. |
| `MD-S053-R0182` | The two-date command proves distinct publication and readability, but independent evidence and replay for each newly requested date were not executed. |
| `MD-S059-R0072` | Acquired-observation immutability is proved, but the free source's future historical availability and full intentional-start coverage are not observable in this local test world. |
| `MD-S059-R0074`, `MD-S059-R0140` | No complete acquisition from the intentional dataset start establishes the stated irreversible-risk mitigation. |
| `MD-S074-R0003` | Readable correction is real and auditable; nonreadable candidate rebuild preserving both old and new observation lineage remains unexecuted. |
| `MD-S074-R0007` | Single-date checkpoint resume passes. No partially completed multi-date lifecycle demonstrates selection of the first incomplete date. `max_dates_per_run=1` correctly refuses a two-date request, so it cannot be used as a fake partial-range proof. |
| `MD-S074-R0011` | No real unchanged-value retry proves persisted `UNCHANGED` and `NOOP_UNCHANGED_BARS` together. |
| `MD-S074-R0013` | The single-ticker recovered-date world cannot detect an erroneous full-date replacement that drops a second listing from an unsealed candidate. Stable listing identity and historical immutability pass, but the complete merge clause needs a two-listing adversarial MariaDB guard. |
| `MD-S001-R0087` | No executable evidence-retention path proves the official minimum audit-evidence window. |
| `MD-S001-R0088` | No trade-date partition-maintenance execution or deployed partition proof. |
| `MD-S082-R0018` | No executed producer reports the `CONFIG_UNBOUND` publication count until zero. |

## Defaults and compatibility

The registered `market-data:backfill:lifecycle` command successfully executes an explicit one-date `manual_file` historical fill from CSV on MariaDB under the supported `market-data-row-hash/v1` profile, with a missing-file refusal and invalid-mode refusal. The default `market-data-semantic-hash/v2` path cannot resolve provider-source semantic identity from a local-only observation whose provider is intentionally NULL. The test retains both results. V2 is not relabeled as passing, and no provider fact is manufactured.

The `MD-S053-R0199` source line's insufficient-prior-window language is read with the later binding dataset-start addendum: missing or contradictory requested calendar blocks before acquisition; a valid intentional dataset-start boundary caps the warmup rather than failing solely for too few earlier dates. No frozen source text was changed.

## Continuing boundaries

Accepted baseline is 563/750; E031 proposes 594/750 pending independent review; formal SATISFIED remains 0/750. F018 incident escalation/closure authority, R0324 under D008/F016, MD-DEP-0025, the alias-meaning and coverage static-guard failures, and the frozen candidate-v5 build identity remain open as previously recorded. No production deployment, full-suite PASS, candidate-v6, or B19 closure is claimed.

## E032 continuation status — 2026-10-20

Independent review accepted 27 of E031's 31 proposals and held four. E032 supplies successor proof for those four without rewriting or double-counting E031: R0183 and R0200 now use actual warmup observations and test direct-promotion refusal; R0001 now uses an interrupted two-date MariaDB range and verified resume; R0085 snapshots every applicable protected canonical family across dry-run and apply. The previously held R0007 first-incomplete-date predicate is separately proposed from the same interrupted-range world. These are proposals pending independent admission; the last accepted baseline remains 590/750 and formal SATISFIED remains zero.

R0007 is removed from the current residual hold list subject to review of E032. The other eleven holds remain: R0029, R0182, R0072, R0074, R0140, R0003, R0011, R0013, R0087, R0088 and R0018 (with their full `MD-S...` identifiers in E032's map). R0182 still lacks an independently authored per-date replay fixture and execution. R0011 lacks a real unchanged recovered retry with both persisted `UNCHANGED` and derived `NOOP_UNCHANGED_BARS`; R0013 lacks an adversarial two-listing recovered-candidate merge. External history/dataset-start completeness, audit-evidence retention, trade-date partition maintenance, CONFIG_UNBOUND counting, and default V2 manual-file attribution remain unavailable or unsupported as stated above. The proof gate remains FAIL, 155 predicates without basis and eight families without guards. E032 raw evidence and the consolidated review package are registered separately.

## E044 successor disposition

E044 proposes `MD-S074-R0011` and `MD-S074-R0013` from two separate migrated-MariaDB retry worlds. The first verifies a new accepted source observation with unchanged canonical values, persisted `UNCHANGED` and both `NOOP_UNCHANGED_BARS` decisions. The second verifies that recovery of one failed listing preserves a different listing and prior sealed history; publication of the synthetic second listing is correctly held for missing frozen foundation identity. Comparator and full-date replacement mutations failed at intended assertions and were byte restored. These two rows are proposals pending external review, not revisions to E031/E032. The remaining recovery `R0003` compound old/new lineage obligation and the other unavailable holds continue; the current 114-row queue is retained with E044.

## E045 independent recovery continuation

Independent review accepted E044 R0011/R0013. E045 proposes the distinct `MD-S074-R0003` compound row using two real MariaDB worlds: an already readable 105 observation changes through an approved 106 correction and a new sealed publication, while a previously unreadable 105 candidate survives source failure and selects a separate immutable 106 observation on retry. Old/new source observation rows are asserted independently in both paths. The comparator counterfactual fails `APPLIED` versus `UNCHANGED`; source restores byte-for-byte and both changed/unchanged controls pass 2/47. The accepted E044 two-listing method passes on the current test source 1/33. Other acquisition, external-history, audit-retention, partition, CONFIG_UNBOUND and manual-file V2 holds remain unchanged. E045 is a proposal pending independent review, not formal SATISFIED.

## E049 internal continuation checkpoint

E049 proposes MD-S082-R0018 from the real sealed-corpus report, including historical/unbound and explicit-zero controls. This is repository proposal accounting only. Audit-retention, trade-date-partition maintenance, unsupported manual_file V2 attribution and other incomplete clauses remain unproved; existing issued evidence is unchanged.
