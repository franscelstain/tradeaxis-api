# Change Impact Declaration — `MD-B09-A003`

- ID: `CI-MD-B09-A003-001`
- Stage / Attempt / Baseline / Epoch: `MD-B09` / `MD-B09-A003` / `MD-B09-A003-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260823-001`
- Stage precondition: `SC-MD-B09-A002-001`
- Dependencies: `MD-DEP-0004` stage-entry semantic normalization
- Status: `EXECUTED`
- Strategy meaning change: `NO`

## Objective

Complete the `MD-B09` reference-population re-check: promote `MD-S008-R0018`, record the
classification decision on the four reference rows the hardened gate reported, and admit `MD-B09` to
`DECISION_RECORDED_STAGES`.

This is the first stage re-check driven by `UNEXPLAINED_REFERENCE` instead of a hand search. The gate
named five rows; reading them took minutes rather than reading two whole contracts line by line.

## Why the prior classification was defective

`MD-S008-R0018` — *"Future adapters map their payloads into these same normalized semantics and may
not leak source JSON paths, suffix rules, or proprietary status codes into consumer contracts."* — is
the paragraph immediately after `MD-S008-R0017`, which is `REQUIRED` and `SATISFIED`. Both are
prohibitions on how an adapter may present itself downstream, and they are indistinguishable in
form. R0018 sat `REFERENCE_ONLY` with empty notes.

Third instance of the same shape, after `MD-S066-R0002` and `MD-S067-R0010`: a standalone paragraph
that no enumerated-run invariant can reach.

## The four rows that stay reference, now with the reason recorded

- `MD-S023-R0045` — explanatory prose contrasting the zero-volume rule with the zero-price rule. The
  executable predicate is `MD-S023-R0044` (rule 10), `REQUIRED` and `SATISFIED`.
- `MD-S036-R0001` — document status and historical guard marker.
- `MD-S036-R0032`, `MD-S039-R0005` — capability boundary disclaimers of the *"produces no verdict,
  state, flag or signal"* form, the class correctly kept as reference in `MD-B13` and `MD-B07`.

## Impact assessment

- Strategy / schema / configuration / application source: **no change**.
- Storage: not inspected, not mutated.
- Tests/gates: `ProviderNeutralBoundaryTest::test_downstream_contracts_never_name_the_concrete_adapter_or_provider`
  is extended to cover the leak class it did not check. A new
  `MarketDataCanonicalRawImportReferenceReview.php` carries the two guards B07 and B08 needed. The
  `MarketDataCanonicalRawImportProofBinder` becomes idempotent and attempt-aware.
- Compatibility: `E-MD-B09-A002-001` is not edited; its 139 bindings stand.

## Actual impact and result

- **Traceability**: `MD-S008-R0018` promoted; denominator 139 → **140**; B09 non-structural reference
  rows with no recorded decision **5 → 0**.
- **Guard gap closed, not assumed**: the existing test checked the provider name, the adapter class
  and the `.JK` suffix — three leak classes named by the rule are *source JSON paths*, suffix rules
  and proprietary status codes, and the first was unguarded. The test now also rejects `adjclose`,
  `exchangeTimezoneName`, `chart.result`, `period1` and `period2` in the six downstream files.
  Injecting a payload path into `MarketDataReadProductService` turns it red.
- **Both review guards proven falsifiable**: dropping a rule from `REFERENCE_DECISIONS` aborts with
  the row unaccounted for; making the pass touch `MD-S011-R0023` aborts with a foreign-row alteration.
- **Binder correctness**: the previous binder appended a binding note on every run without stripping
  the old one, so a re-run stacked duplicates. It now replaces its own line.
- **Application source changed**: **NO**. **Strategy changed**: **NO**.
