# C171 Final Failed/Not-Ready Closure and Evidence Seal

## Final decision

The exact final official-IS set is `eval_id=205,206,207`, compared with the valid V3 anchor `eval_id=204`.
Every final candidate passed trade-count, coverage, and positive-average gates, but failed all four quality gates:

```text
median_return_non_negative=0
p25_downside_bound=0
monthly_win_rate_floor=0
monthly_average_floor=0
```

The final candidates did not displace anchor paramset 11. Candidate 12 came closest but had lower average return, lower win rate, and two more failed periods. Candidates 13 and 14 degraded overall quality further. Therefore the previously locked no-pass rule is executed:

```text
FINAL_DECISION=C171_FAILED_NOT_READY_NO_FURTHER_REMEDIATION
C171_TOPIC_CLOSED=1
ADDITIONAL_C171_CANDIDATE_CATALOG_ALLOWED=0
OOS_ALLOWED=0
C172_ALLOWED=0
PROMOTION_ALLOWED=0
PLAN_ALLOWED=0
PRODUCTION_READY=0
```

## Exact evidence identities

```text
ANCHOR_PARAM_SET_ID=11
ANCHOR_EVAL_ID=204
ANCHOR_FILE_SHA1=D811D80CBE3677835CB3BBB6F3F87462A1744EAF

FINAL_PARAM_SET_ID=12
FINAL_EVAL_ID=205
FINAL_FILE_SHA1=F251D73175400DD36920D68D4E02E5AFC59DAE90

FINAL_PARAM_SET_ID=13
FINAL_EVAL_ID=206
FINAL_FILE_SHA1=5C764255457820F1321CB7FADC5DDF21556895E5

FINAL_PARAM_SET_ID=14
FINAL_EVAL_ID=207
FINAL_FILE_SHA1=7FEDD1436305B351FA9E3E846FC3B4ABCA42C26E

FINAL_SUMMARY_FILE_SHA1=53356CA429CF7AA47EFC45ACFB5511F9DC92ED50
DECISION_ARTIFACT_HASH=b7fcc8d7aae089cd6fb518ddb390de0d3122318d
DECISION_FILE_SHA1=CDAAA8271EBDF6711B6A3CFBD6732E1A6A70992B
```

The closure command reads and verifies existing immutable evidence only. It does not run official IS, query OOS, mutate evals or paramsets, promote a paramset, create PLAN, persist recommendations, mutate CONFIRM, or activate production.

The generic `next_recommendation=C171_TARGETED_EXECUTABLE_IS_STRATEGY_REMEDIATION` contained in the already immutable official-IS artifacts is superseded by this explicit closure rule for catalog `FINAL-C01`.

## PowerShell UTF-8 BOM summary parser repair

The first operator seal attempt was correctly blocked before database verification with:

```text
status=BLOCKED
reason_code=C171_FINAL_CLOSURE_SUMMARY_IDENTITY_MISMATCH
```

Root cause was limited to CSV parsing. Windows PowerShell `Export-Csv -Encoding UTF8` placed the UTF-8 BOM before the opening quote of the first header. Calling `fgetcsv()` before removing that BOM caused the first key to be parsed as the literal `"param_set_id"` instead of `param_set_id`, so every row resolved to `param_set_id=0` and failed closed.

The repair strips the BOM from the raw first line before calling `str_getcsv()`, validates required headers, and leaves the exact summary SHA1, artifact identities, evals, paramsets, gates, database rows, and closure decision unchanged. The same seal command must be rerun. No official IS, OOS, promotion, PLAN, CONFIRM, or production operation is introduced.

