# Decision — register the eligibility decision contract's own version identity

- ID: `D-MD-B18-A002-006`
- Verification epoch: `MD-STRATEGY-FREEZE-20260903-001` (predecessor)
- Stage / Attempt / Baseline reviewed: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Finding: `F-MD-B18-A002-021`
- Supporting evidence: `E-MD-B18-A002-045` (per-predicate proof-basis review), `E-MD-B18-A002-046`
  through `E-MD-B18-A002-048` (prior F-021 remediation), owner-decision package presented in this
  attempt's C1 §6 step 5 (Admission) closing review
- Blocked rules: `MD-S050-R0002`, `MD-S050-R0014`, `MD-S019-R0071`, `MD-S003-R0003`
- Issued: 2026-09-22
- Decision status: `APPROVED`
- Strategy impact: `CONTROLLED_CORRECTION` limited to one additive resolved-key row in `MD-S082`

## Question

`F-MD-B18-A002-021`'s per-predicate proof-basis review found that "eligibility version" — one of
the nine bound-input kinds `MD-S050`/`MD-S019` require every publication replay to freeze and
compare — has no canonical version identity anywhere in the repository: no config key, no code
constant, no prior capture. `ProducerRegistrySnapshot::capture()` already captures
`indicator_set_version` and `coverage_contract_version` the same way; eligibility had no equivalent
to capture at all.

The owner-decision package bounded two contract-compatible options (a fixed literal constant, or a
config-driven contract version symmetric with `coverage_contract_version`) and recommended the
config-driven option, but did not record a decision or implement it — the canonical value has no
existing authority basis and required an explicit owner choice.

## Authority review

1. `EOD_Eligibility_Snapshot_Contract_LOCKED.md` governs a broader upstream data-usability decision
   that `coverage_gate` is only one input to; its own version identity is therefore a distinct
   concept from `coverage_contract_version`, not a restatement of it.
2. `MD-S082` (`Platform_Config_Registry_LOCKED.md`) requires every resolved `market_data.*`
   configuration key to have a matching registry row, enforced at runtime by
   `PlatformConfigRegistry`; a config-driven eligibility version accordingly requires an additive
   row here, mirroring the existing `market_data.coverage_gate.contract_version` /
   `market_data.price_scale_break.contract_version` rows.
3. No other strategy document names or constrains this identity's value; a config-driven identity
   with an explicit default is a bounded, contract-compatible correction rather than a semantic
   change to any locked contract.

## Decision

1. Preserve `EOD_Eligibility_Snapshot_Contract_LOCKED.md`, `Reason_Codes_Registry.md`, and every
   other strategy document byte-for-byte.
2. Add exactly this one row to the `MD-S082` resolved-key register:

   | Key | Type | Default | Environment input |
   |---|---|---|---|
   | `market_data.eligibility.contract_version` | string | `eod_eligibility_snapshot_v1` | `MARKET_DATA_ELIGIBILITY_CONTRACT_VERSION` |

   Owned by `../book/EOD_Eligibility_Snapshot_Contract_LOCKED.md`, mirroring the existing
   `coverage_gate`/`price_scale_break` contract-version rows.
3. No other key, default, threshold, finding behavior, readiness rule, publishability rule, or
   Admission semantic is authorised to change. `eligibility_contract_version` is captured through
   the existing `ProducerRegistrySnapshot::capture()` / `registry_versions` component only; no new
   decode path or bound-input field is authorised, consistent with the owner-decision package's own
   finding that `formula_registry_hash`/`reason_registry_hash` already read the whole
   `registry_versions` component's combined `payload_hash`.
4. Issue a change-log entry and successor strategy freeze; retain the predecessor freeze and every
   prior `MD-B18-A002` evidence byte unchanged.

## Authorization state

**Explicit user authorization was given inline with this decision**, unlike the two-phase
issue-then-authorize sequence `D-MD-B17-A001-001` used. The user's own message stated: "OWNER
DECISION untuk F-MD-B18-A002-021 / `eligibility_version`: Pilih Opsi B — config-driven eligibility
contract version. Tetapkan: canonical config key: `market_data.eligibility.contract_version`;
initial value: `eod_eligibility_snapshot_v1`; environment variable:
`MARKET_DATA_ELIGIBILITY_CONTRACT_VERSION`," together with an explicit implementation instruction
bounded to the existing capture/Binding/Seal/Reader path and an explicit prohibition on any other
semantic extension. This decision record and the strategy-document/freeze-manifest edits it
authorises were written after that authorization, not before it.

## Invalidation / revalidation impact

- Strategy byte scope: one additive resolved-key row in `MD-S082`; the other 90 strategy documents
  remain byte-identical.
- Freeze: successor `MD-STRATEGY-FREEZE-20260922-001`; only the registered `MD-S082` fingerprint
  changes from the predecessor freeze.
- Affected predecessor proof: `MD-B18-A002`'s prior evidence (`E-MD-B18-A002-001` through `-048`) is
  unaffected — none of it depended on the `MD-S082` exhaustive key population changing, unlike
  `D-MD-B17-A001-001`'s six-key correction which the B04 configuration-registry invariants directly
  enumerate. No B00-B17 closure and no prior `MD-B18-A002` evidence is rewritten.
- The four blocked rules above are reviewed on their own merits against the successor freeze in this
  same work unit's evidence record, not promoted automatically because this registration exists.

## Scope limit

This decision does not authorise tuning the default value, adding another configuration key,
changing `EOD_Eligibility_Snapshot_Contract_LOCKED.md` or any other strategy document, bypassing
`PlatformConfigRegistry`, or treating any predicate as `PROVEN` without its own reviewed proof basis.
