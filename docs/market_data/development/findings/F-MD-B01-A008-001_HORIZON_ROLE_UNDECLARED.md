# F-MD-B01-A008-001 — No dependency window in the baseline field set declares its horizon role

- Status: `OPEN`
- Severity: `P2`
- Stage / Attempt / Baseline / Epoch: `MD-B01` / `MD-B01-A008` / `MD-B01-A008-BL001` / `MD-REBASELINE-20260820-001`
- Owning stage for remediation: `MD-B14` — the indicator dependency manifest is the surface that must carry the role
- Blocks at proof-owning stage `MD-B14`: `MD-S056-R0019`, `MD-S056-R0020`, `MD-S056-R0021`, `MD-S056-R0022`, `MD-S056-R0024`, `MD-S056-R0129` reaching `SATISFIED`

## Finding

`Terminology_and_Scope.md` locks three horizon roles and makes declaring one mandatory:

> A field's window is not arbitrary and must declare which role it serves relative to the horizon above

The three roles are **decision window** (spans at most the horizon), **context window** (spans beyond it deliberately), and **state window** (no fixed span, recursive state). The contract then states the consequence:

> A window whose role is undeclared cannot be justified by the horizon and must not be added to the baseline field set.

The baseline field set contains at least eleven dependency windows, and **none of them declares a role anywhere in this repository**:

| Window | Span | Role the contract's rule implies |
|---|---|---|
| `roc5` | 5 sessions | decision window — spans exactly the horizon |
| `roc10` | 10 sessions | context window |
| `roc_20`, `ma20`, `range_20_pct`, `range_position_20_pct`, `rs_20_vs_ihsg` | 20 sessions | context window |
| `hh20` / `close_to_hh20_pct`, `ll20` / `close_to_ll20_pct` | 20 sessions (`hh_window_days`) | context window |
| `dv20` | 20 sessions (`dv_window_days`) | context window |
| `ma50` / `close_vs_ma50_pct` | 50 sessions | context window |
| ATR | 14 sessions seed (`atr_window_days`), Wilder recursion | state window — the contract names Wilder ATR as its own example |

A search of the whole repository for the three role names, or for any `horizon_role` / `window_role` identifier, returns nothing outside the owner contract itself and one archived audit record.

This is not merely a documentation omission. `roc5` and `roc10` sit on opposite sides of the horizon, and rule 17 makes the distinction load-bearing:

> spanning beyond the horizon is legitimate for a context window and illegitimate for a decision window

Without a declared role, there is no way to tell whether a 10-session window is a legitimate context window or an illegitimate decision window. The rule that governs the difference cannot be evaluated.

## Why this attempt did not fix it

Two reasons, both governance rather than difficulty.

**The role belongs to a surface `MD-B01` does not own.** `Terminology_and_Scope.md` is explicit about the split:

> The concrete window lengths belong to the indicator owner contract; the obligation to state the role belongs here, because only this document owns the horizon they are measured against.

So `MD-B01` owns the horizon and the obligation, and the indicator owner contract owns the per-window assignment. `Indicator_Registry_Baseline_LOCKED.md` already requires the natural home for it — "The registry and implementation must publish a dependency manifest sufficient to compute correction impact" — which is `MD-B14` work. Building that manifest inside `MD-B01` would be a stage jump without a dependency basis.

**The strategy side cannot be edited here.** The indicator owner contracts are registered in `MARKET_DATA_STRATEGY_FREEZE_MANIFEST.json` and verified byte-for-byte by the documentation integrity gate. Adding role assignments to them is a strategy revision, which `DOCUMENT_CHANGE_POLICY.md` reserves for an authorised strategy change rather than an implementation attempt.

## Remediation

When `MD-B14` opens, the indicator dependency manifest must carry a `horizon_role` for every window in the published field set, drawn from the three locked roles, and a guard must assert that no field enters the baseline set without one. At that point `MD-S056-R0019`–`R0022`, `R0024`, and `R0129` become provable, and `MD-S056-R0129` in particular becomes enforceable rather than merely stated.

`MD-B01-A012` normalized all six predicates to `MD-B14` with `MD-B01` retained as a supporting stage. Until remediation, the six rules remain `NOT_ASSESSED`. They are not claimed on the strength of the roles being derivable — deriving a role is not declaring one, and the contract requires the declaration.
