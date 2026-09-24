# Decision — AS_KNOWN `read_model_version` binds the read-product contract that renders the artifact

- ID: `D-MD-B18-A002-008`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline reviewed: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Finding: `F-MD-B18-A002-017` (`MD-S050-R0016` residual Gap B1)
- Supporting evidence: `E-MD-B18-A002-068` (closure audit that found the AS_KNOWN read-model gap),
  `E-MD-B18-A002-069` (Gap A closure; Gap B restated as an owner decision); read-only Gap B
  authority reconstruction presented in this attempt before this record (no evidence issued)
- Rules whose AS_KNOWN read-model member this decision disambiguates: `MD-S050-R0016`,
  `MD-S050-R0014`, `MD-S004-R0004`
- Issued: 2026-09-24T11:51:50+07:00
- Decision status: `APPROVED`
- Strategy impact: `NONE` — no strategy byte changes; freeze `MD-STRATEGY-FREEZE-20260922-001` unchanged
- Governance impact: `NONE` — no `CONTROLLED_REVISION` document changes; no `DOCUMENT_CHANGE_LOG.md` entry

## Question

`Replay_Verification_Contract_LOCKED.md:30` requires every fixture/manifest, in both replay modes, to
bind a read-model version. `AsKnownReplaySnapshotService` reads `governance.read_model_version`, a
configuration key that has never existed, so every AS_KNOWN replay records it empty. The read-only
reconstruction found that current authority requires the value and forbids a missing one from passing,
but does not say which read-model version an **as-known** replay binds:

1. the read-product contract version that renders the artifact (the reading the publication path
   already uses); or
2. a configuration value resolved as known at the replay's knowledge cutoff.

Strategy does not choose between these readings, and the implementation may not choose on its own.

## Authority review

Reverified narrowly against the owner's choice before this record was written. No conflict was found.

1. `Downstream_Consumer_Read_Model_Contract_LOCKED.md` (`MD-S021`) defines one versioned, stable
   read product ("read-model V1"). Its version identifies the data shape, meaning, units, requiredness,
   null semantics and readiness behavior; changing any of these "require[s] a new version and
   compatibility plan". The version is a contract identity, not a tunable setting.
2. `Publication_Manifest_Contract_LOCKED.md` (`MD-S045`) defines "product/formula/read-model versions"
   as the "explicit interpretation of the artifact rows" — the identity of the contract the rows are
   read under.
3. `Replay_Verification_Contract_LOCKED.md` (`MD-S050`): `:17` as-known replay "creates new replay
   artifacts"; `:30` read-model version is a required bound input for every fixture/manifest; `:33`
   a missing input is `BLOCKED`; `:107` "A downstream backtest consumes the **versioned as-known read
   product**". `Point_In_Time_Backtest_Input_Contract_LOCKED.md` (`MD-S004`) likewise: backtests
   "consume only a versioned snapshot/export of the market-data read model", and every as-known
   row/export binds its "read-model version". Both describe the as-known output as a versioned read
   product, which is this decision's reading.
4. `Platform_Config_Registry_LOCKED.md` (`MD-S082`): `:289` as-known replay "resolves only revisions
   known by the declared knowledge cutoff" and `:291` "Current registry state must never leak into
   historical replay". These rules govern configuration and registry revisions. The only read-model
   item among `MD-S082`'s configuration families is the "minimum market-data consumer read-model
   version", a read-surface setting, not the interpretation identity of an artifact. The resolved-key
   register contains no read-model key. This decision does not make read-model version a configuration
   value, so it adds no key and changes no register row.
5. Precedent, not authority (`MARKET_DATA_DOCUMENT_AUTHORITY.md` rule 4): `F-MD-B18-A002-021` /
   `E-MD-B18-A002-048` bound the publication path to `market_data_read_product_v1`, the literal the read
   product itself emits (`MarketDataReadProductService`, `EodPublicationRepository`), classified as reuse
   of an established identity rather than invention. That finding also recorded that
   `market_data.governance.read_model_version` never existed.

This record selects one of two authority-compatible readings. It does not create or restate strategy
semantics, and it does not become strategy authority itself (rule 4). No strategy byte change is
needed, because Option 1 adds no configuration key and alters no locked clause.

## Decision

1. **AS_KNOWN `read_model_version` binds the replay/read-product contract version of the artifact
   being rendered.**
2. **Current canonical identity: `market_data_read_product_v1`.** This is the same identity the
   publication path already binds.
3. It does **not** mean:
   - the current runtime code version;
   - the build identity;
   - the configuration snapshot effective at the replay cutoff;
   - the minimum consumer read-model version resolved from `MD-S082` configuration;
   - a newly invented AS_KNOWN configuration value.
4. **Historical behavior.** If a historical artifact was rendered under V1, the AS_KNOWN replay stays
   bound to V1 even if a later read-product contract V2 exists. The binding follows the contract that
   rendered the artifact, not the knowledge cutoff and not whichever contract is the current default.
   Derived from `MD-S021` (a contract change is a new version): an artifact is never relabeled to a
   later version, and rendering under a later contract produces a different artifact bound to that
   later version.

## Authorization state

**Explicit owner authorization was given inline with this decision.** The owner's message selected
"OPTION 1 — REPLAY-CONTRACT IDENTITY" and stated that, for AS_KNOWN replay, `read_model_version`
"mengidentifikasi versioned read-product / replay contract yang merender artifact; ia bukan
configuration value yang di-resolve berdasarkan cutoff", with canonical identity
`market_data_read_product_v1`, that a historical replay artifact using V1 stays bound to V1 even if V2
later exists, and that the decision adds no configuration key to `MD-S082` and changes no frozen
strategy semantics. This record was written after that authorization, not before it.

## Invalidation / revalidation impact

- Strategy: none; all 91 strategy documents and freeze `MD-STRATEGY-FREEZE-20260922-001` unchanged.
- Proof basis: unchanged at 91 `PROVEN` / 23 `INCOMPLETE`. `MD-S050-R0016` remains `INCOMPLETE`.
- Formal traceability: unchanged at `0/114` `SATISFIED`.
- No prior evidence is rewritten. `E-MD-B18-A002-068` and `E-MD-B18-A002-069` described fail-closed
  AS_KNOWN versus binding a real identity as open alternatives; for the read-model member, this record
  settles which one applies.
- Implementation consequence (not started by this record): AS_KNOWN must bind the read-product contract
  identity instead of reading a nonexistent configuration key, and the direct-write boundary must
  require `read_model_version`. The sequence relative to Gap B2 and to `MD-S050-R0005`/`MD-S050-R0014`
  proof impact is set by the governed resume point, not by this record.

## Scope limit

This decision closes only the semantic ambiguity of Gap B1. It does not:

- declare `MD-S050-R0016` `PROVEN`;
- resolve Gap B2 (AS_KNOWN reason-registry identity). Current authority already determines Gap B2 as
  fail-closed because no historical reason-registry identity exists;
- change AS_KNOWN admission behavior yet;
- change any formal `SATISFIED` state;
- add, rename or default any configuration key, or modify `MD-S082`, `MD-S021` or any other strategy
  document;
- authorize treating any predicate as `PROVEN` without its own reviewed proof basis.
