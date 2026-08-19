# Legacy Role Extract — WS — DECISION

> **Document Type:** DECISION
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0550-DEC-01`
> **Legacy Source ID:** `LS-WS-0550`
> **Legacy Work Key:** `WS`
> **Original Path:** `docs/watchlist/system/policies/weekly_swing/12_WS_BACKTEST_SCHEMA_AND_CALIBRATION.md`
> **Original SHA1:** `F36B415CA47D448CF0C5EA5AEDE987D497FFEF42`
> **Source Sections:** L1642-L1658 C171 final closure supersession addendum; L1659-L1704 WS New Strategy R02 official-IS closure addendum; L1705-L1746 WS Tail Risk S01 official-IS closure addendum; L1764-L1799 WS Price Quality P01 official-IS closure addendum
> **Extract Body SHA1:** `A61D1B64E571933F46F21164BF61E36C0D409ACB`
> **Current Authority:** NO

The body below is an exact copy of the identified original sections. No semantic rewriting was applied.

---

## C171 final closure supersession addendum

The historical conditional wording that allowed a later C171 candidate to open
C172 is superseded by the sealed result:

```text
C171_FINAL_DECISION=C171_FAILED_NOT_READY_NO_FURTHER_REMEDIATION
C171_ADDITIONAL_CANDIDATE_CATALOG_ALLOWED=0
C172_ALLOWED=0
```

No additional C171 calibration row or catalog may be created. A separately
approved new-strategy research scope may reuse immutable failed evidence for
diagnostic attribution, but it must use a new scope identity and must still
follow every schema, IS gate, no-lookahead, OOS, and promotion rule owned by
this document.

## WS New Strategy R02 official-IS closure addendum

The separate R02 scope used three predeclared selection candidates and exactly
one remediation. Initial evals `208-210` failed. The remediation retained H2
selection and used this fixed execution model:

```text
ENTRY=NEXT_OPEN
EXIT=SEQ_TP05_OR_PCNO_OR_TIME
HOLD=5
FEE=IDR_FIXED
SLIP=0
GAP=OPEN
PX=IDX_BANDS
```

`SEQ_TP05_OR_PCNO_OR_TIME` means:

1. a normalized IDX target at entry plus `0.5%` is fixed before entry;
2. target fills are evaluated chronologically from raw tradable OHLCV;
3. if target has not filled, a profitable D1-D3 close may only exit at the
   next trading-day open;
4. if neither route fills, exit at D5 close;
5. no future target result, future-path bucket, OOS result, month, or ticker
   chooses the route.

Remediation eval `211` passed six gates but failed the unchanged monthly-average
floor:

```text
picks_count=323
days_covered=490
avg_ret_net_top=0.0062324113104205376
median_ret_net_top=0.00725
p25_ret_net_top=0.005278567059322118
win_rate_top=0.8699690402476781
month_win_rate_min=0.5
month_avg_ret_net_min=-0.04507202296434394
period_fail_count=4
canonical_is_gates_pass=0
```

R02 is therefore failed/not-ready and closed. A second remediation, gate
weakening, blacklist rescue, Official OOS, promotion, PLAN, or production
activation is forbidden for this scope.

## WS Tail Risk S01 official-IS closure addendum

S01 is a separate strategy scope sourced from immutable failed R02 eval `211`.
It does not reopen R02. Three one-idea candidates were locked before Official
IS under catalog `WS_BT_GRID_TAIL_RISK_S01_2026_07`:

```text
S01_H1_IHSG_NON_WEAK_GUARD
S01_H2_TICK_RISK_LT_1P5_GUARD
S01_H3_DAILY_CLOSE_LOSS_CONTAINMENT
```

H1/H2 use exact signal-date context and fail closed when required context is
missing. H3 uses a fixed sequential target plus a D1-D3 `-3%` close-loss
signal executed at the next trading-day open. Evals `212-214` all failed at
least one unchanged canonical IS gate.

The only allowed remediation retained H1 selection and changed one execution
idea: a D1-D3 close at or below `-1%` exits at the next trading-day open. The
threshold and route were fixed before eval `215`; raw tradable OHLCV, IDX tick
normalization, chronological evaluation, and D5-close fallback remain
mandatory. No future target result may select an earlier route.

Eval `215` result:

```text
picks_count=205
days_covered=496
avg_ret_net_top=0.00791698058673335
median_ret_net_top=0.006514657980456026
p25_ret_net_top=0.00482897384305835
win_rate_top=0.8048780487804879
month_win_rate_min=0.4
month_avg_ret_net_min=-0.01807863294738592
period_fail_count=2
canonical_is_gates_pass=0
```

Monthly win-rate and monthly-average floors failed. S01 is failed/not-ready
and closed. A second S01 remediation, best-of-failed binding, OOS, promotion,
PLAN, blacklist rescue, gate weakening, or production activation is forbidden.

## WS Price Quality P01 official-IS closure addendum

The preregistered thresholds were `50`, `100`, and `200`. Diagnostic evidence
authorized only 50 and 100, so the immutable initial catalog contains exactly
two rows. Official IS evals `216` and `217` both failed unchanged stability
gates; neither opened OOS.

The only remediation retained floor 50 and added a fixed D1-D3 `-1%`
close-loss signal executed at the next trading-day open. Eval `218` executed
that contract but stored a generic execution-model label. It is immutable
historical evidence but invalid for final identity use.

An identity-only repair created paramset `28` and authoritative eval `219`
without changing selection, execution, gates, or remediation count:

```text
EVAL_MODEL=ENTRY=NEXT_OPEN;EXIT=SEQ_TP05_PCL1NO_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS
PARAMSET_HASH=b3a61e825751fa007f9fcfed8d30ecbbfa78c171
EVAL_ID=219
EVIDENCE_MANIFEST_HASH=2110f4fec4984446b599f9e3b1fd6c7b5fb40ac1
picks_count=187
days_covered=497
avg_ret_net_top=0.008006009018199029
median_ret_net_top=-0.0005000750112516877
p25_ret_net_top=-0.04232922821700027
win_rate_top=0.48663101604278075
month_win_rate_min=0
month_avg_ret_net_min=-0.08743718592964825
period_fail_count=11
canonical_is_gates_pass=0
```

Median, P25, monthly win-rate, and monthly-average gates failed. P01 is
failed/not-ready and closed. No further P01 remediation, OOS access,
promotion, PLAN, blacklist rescue, gate weakening, or production activation
is allowed.
