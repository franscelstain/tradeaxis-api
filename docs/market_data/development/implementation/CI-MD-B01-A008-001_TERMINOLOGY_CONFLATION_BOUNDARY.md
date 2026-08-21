# MD Change Impact Declaration — CI-MD-B01-A008-001

- ID: `CI-MD-B01-A008-001`
- Stage / Attempt / Baseline / Epoch: `MD-B01` / `MD-B01-A008` / `MD-B01-A008-BL001` / `MD-REBASELINE-20260820-001`
- Record class: `MUTABLE_TRACEABLE`
- Issued: 2026-08-21, **before** the matrix mutation and after the guard was written and mutation-proven.

## Why this attempt is material

It changes `coverage_status` on traceability rows, adds an executable test, and opens a finding. All three are named material by `CHANGE_IMPACT_DECLARATION_STANDARD.md` section 1.

## Scope

Advance `MD-B01` coverage against the corrected 155-rule set. 14 rules move `NOT_ASSESSED` → `SATISFIED`:

| Rules | Proof |
|---|---|
| `MD-S056-R0113`, `R0115`, `R0116`, `R0117`, `R0119`, `R0120`, `R0128`, `R0131` (8) | The locked interpretation rules that forbid one term being described as another. Proven across 171 active documents and the `app/`, `config/`, `database/` trees. |
| `MD-S001-R0139`, `R0140`, `R0141` (3) | The forbidden-claims list in the platform baseline: what `market-data:daily` runs, what `market-data:backfill` publishes, and what coverage is counted from. |
| `MD-S001-R0154`, `R0156`, `R0157` (3) | Three of the five documentation-state readings. Each is proven by absence of contradiction **plus** a population check that the active corpus actually raises the subject. |

## Deliberately not claimed

| Rules | Why |
|---|---|
| `MD-S001-R0155`, `MD-S001-R0158` | Zero active documents raise either subject — "archived proof window", and the official-authority / commercial-SLA / redistribution-right disclaimer. An affirmative reading cannot be proven against a corpus that never states the thing being read; a clean contradiction check would be vacuous. Recorded here rather than claimed on empty evidence. |
| `MD-S056-R0019`–`R0022`, `MD-S056-R0129` | No dependency window declares a horizon role anywhere in the repository. Opened as `F-MD-B01-A008-001`, remediation owned by `MD-B14`. |
| `MD-S056-R0124`, `MD-S056-R0125` | Both have a behavioural half this stage cannot reach: price-basis coherence across an indicator run belongs to the indicator engine, and separate explainability of coverage and liquidity has no surface until `MD-B13`/`MD-B15`. |

## Affected areas

| Area | Impact |
|---|---|
| Traceability | **Material.** `MD-B01` `55/155` → `69/155`. Global `SATISFIED` 55 → 69; denominator unchanged at 2010. |
| Findings | **Material.** One finding opened: `F-MD-B01-A008-001` (P2), remediation owned by `MD-B14`. |
| Tests | **Material.** One test file added: 19 tests, 51 assertions. |
| Schema / config / runtime / provider / backfill / replay / ops | **None.** No file under `app/`, `database/`, or `config/` is modified. |
| Evidence | Additive. No prior evidence is restated or invalidated. |
| Runtime artifacts | **None.** Under `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md` section 5 this is document-and-test work claiming no externally-stored runtime output, so no `storage/**` inspection is required or performed and no raw-artifact linkage is claimed. |

## Compatibility risk

**Low.** Nothing existing changes behaviour; a test is added and 14 rows change state. The added guard is strictly stricter — it can only turn a future `PASS` into a `FAIL`.

## Residue / rework risk

**Low.** Six mutations each turned the guard red, each verified as landed first: four injected conflations, one injected contradiction of a required reading, and one removal of the subject that the anti-vacuity control depends on. Two controls confirmed the guard stays green on a document that correctly restates the prohibitions and on one that states the required reading.

The sixth mutation is the one worth naming. `MD-S001-R0157` is proven partly by a population check — the corpus must raise the subject — so the proof is only as good as that check. Removing every mention of operational activation from the four active files that carry it turned the guard red, which is what distinguishes this from a check that would pass on an empty corpus.

One guard defect was found and fixed during the attempt. The first revision flagged `E-MD-B01-A002-001` for containing the sentence "decision horizon must never be expressed in calendar days" — a correct restatement of the prohibition, not a violation of it. This is the second time a corpus guard in this stage has mistaken a quotation for an assertion. The gap between subject and verb in every pattern now cannot span a negation, and `test_a_quotation_of_the_prohibition_is_not_read_as_an_assertion` asserts that for all eleven rules against the prohibition wording itself. Two other defects were my own pattern errors, found by the adequacy tests before any rule was claimed: `MD-S056-R0117` could not match its own fixture, and the `MD-S001-R0154` subject pattern was tighter than the corpus wording.

Residual risk, stated rather than hidden: the guard proves that no document *states* a conflation. Conflation by implication is not mechanically detectable and is not claimed to be covered — the same limit recorded at `MD-B01-A006`.

## Affected dependencies and relationships

- `MD-DEP-0004` — unaffected, remains `OPEN_NON_BLOCKING`.
- `F-MD-B01-A008-001` opened by this attempt; recorded as an explicit relationship row.
- Continuity edge to `E-MD-B01-A007-001`: this attempt advances the same corrected denominator.

## Strategy semantic change

`NO`. `Terminology_and_Scope.md` and `MARKET_DATA_PLATFORM_EOD_BASELINE.md` are read as owner contracts and are not modified.
