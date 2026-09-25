# Decision — the `MD-S065-R0003` override mechanism (A1 + C1 + D2), and transfer of its primary proof to `MD-B21`

- ID: `D-MD-B18-A002-010`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline reviewed: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Finding: `F-MD-B18-A002-017` (`G09`, `MD-S065-R0003`)
- Supporting evidence: `E-MD-B18-A002-077` (G09 reconstruction, `AUTHORITY_AMBIGUITY`); `E-MD-B18-A002-078`
  (correction of one unsupported statement in `E-077`, issued with this unit)
- Prior decision this completes: `D-MD-B18-A002-009` (Option 2: historical configuration by default, explicit
  governed override, no re-stamping). This record decides the items `D-009` listed as "Not decided".
- Rule: `MD-S065-R0003` (`Config_Change_Protocol_LOCKED.md:7`)
- Issued: 2026-09-25T14:07:39+07:00
- Decision status: `APPROVED`
- Strategy impact: `CONTROLLED_REVISION` — `MD-S065` line 7 only, through `DOC-CHG-20260925-001`; successor
  freeze `MD-STRATEGY-FREEZE-20260925-001`
- Ownership impact: `MD-S065-R0003` primary `MD-B18` -> `MD-B21`; supporting `MD-B04` -> `MD-B18;MD-B04`

## Question

`D-009` settled the reading of `MD-S065-R0003` and left four questions open:

1. what the explicit governed override is, and who may authorize it;
2. what happens when the configuration effective for `D` cannot be executed and there is no override;
3. how a new configuration version gets its effective date without a rerun or backfill stamping one;
4. where the resulting rule lives, given that `MD-S065` is frozen strategy.

## Authority review (fit reverified before this record was written)

No contradiction was found.

1. **A1 fits.** `Historical_Correction_and_Reseal_Contract_LOCKED.md:218`: automated republication runs "only
   through the correction-current lifecycle. The system must create/approve a correction". So a `system`
   approval is already legitimate authority. `Correction_Lifecycle_Safety_Contract.md:121`: approval "records that
   a governed actor authorised the correction, not that the replacement values are correct". An approval, then,
   is authority to correct. It does not itself choose a configuration, which is why the override has to be
   requested and recorded explicitly on top of the approval. `Platform_Config_Registry_LOCKED.md:274` ("Silent
   runtime overrides and undocumented defaults are forbidden") rules out treating an approval with no override
   metadata as an override.
2. **C1 fits.** `MD-S065-R0003` makes any configuration other than the one effective for `D` conditional on the
   override. `RunInputCaptureRepository::assertConsumedConfiguration()` already refuses a run whose bound snapshot
   differs from live configuration (`INPUT_CAPTURE_LIVE_CONFIG_DIVERGENCE`), so the platform can execute only
   live configuration. When the version effective for `D` is not live and there is no override, the run can
   honour the rule only by not running. `BLOCK` is that outcome. It needs no new execution capability.
3. **D2 fits.** Effective-for-`D` selection needs a registry that declares a version's effective interval
   independently of the runs that use it. `Platform_Config_Registry_LOCKED.md:262-271` requires every key
   definition to record an "effective interval and recorded/known interval" (`MD-S082-R0207`) and "change
   reason/ticket, author, and approval evidence" (`MD-S082-R0209`). Both rows are `MANDATORY`, primary `MD-B21`,
   `NOT_ASSESSED`, and unimplemented: today the resolver stamps the requested date of whichever run first resolves
   a new version (`E-077`). `R0003` cannot be implemented or proven honestly before `R0207`/`R0209` exist. `MD-B21`
   (Stage Register: "Global schema/config/code/test/ops convergence, backfill, constraint hardening, dan full
   semantic proof")
   already owns them. The canonical mechanism for moving an unproven predicate's primary owner is the one
   `D-MD-B18-A002-005` (Q5) / `E-MD-B18-A002-018` applied to `MD-S020-R0014`: the matrix owner changes, the
   supporting stages record the origin, the proof basis moves the entry to `TRANSFERRED_OWNERSHIP` (audit only),
   and no proof is inherited.
4. **Where the rule lives.** `records/decisions/README.md` requires a resulting current rule to be reflected in
   authority. `MARKET_DATA_DOCUMENT_AUTHORITY.md` rule 4 says a decision does not become strategy authority.
   `DOCUMENT_CHANGE_POLICY.md` section 2 allows frozen strategy bytes to change with a finding, evidence, a reviewed
   decision, explicit authorization, a change-log entry, a successor freeze and a verification-impact review.
   `F-017`, `E-077`/`E-078`, this record, the authorization below and `DOC-CHG-20260925-001` supply them.

## Decision

**A1 — system authorization allowed.**

1. The explicitly documented override for `MD-S065-R0003` is a governed correction that explicitly requests
   execution under a named configuration and records that configuration's identity and the reason.
2. That correction may be approved by `system`.
3. An approval, whether by `system` or a human, of a correction that carries no such request and identity is
   **not** an override. It gives no permission to use live, current or latest configuration. Without explicit
   override provenance, substituting live/current/latest configuration stays forbidden.
4. The override is scoped to the governed correction that records it.

**C1 — fail closed.**

5. By default a rerun of requested trade date `D` binds the configuration version effective for `D`.
6. If that version differs from the live executable configuration and no override exists, the rerun is
   `BLOCKED`. It is not executed under any other configuration.
7. `assertConsumedConfiguration()` stays fail-closed. No ability to execute a historical configuration is
   added by this decision.

**D2 — primary proof transfers to `MD-B21`.**

8. Primary implementation and proof ownership of `MD-S065-R0003` moves to `MD-B21`, alongside `MD-S082-R0207`
   and `MD-S082-R0209`. `MD-B18` stays supporting (the replay/rerun surfaces and its `E-077` reconstruction are
   prerequisite context); `MD-B04` stays supporting.
9. The requirement stays `MANDATORY` and `NOT_ASSESSED`. It is not withdrawn, and it is not `PROVEN`: `MD-B18`
   records no proof of it and `MD-B21` inherits none. `MD-B21` must prove it once `R0207`/`R0209` exist.

**Effective-date consequence (rationale for D2).**

10. A configuration version's effective date is declared through the effective-dated registry that
    `MD-S082-R0207`/`R0209` require. It is never derived from a rerun's or a backfill's requested date, and an
    override never records or re-stamps its configuration as effective for `D` (`D-009` item 4).
11. **Not adopted — D3** (declare one baseline effective interval for the current configuration now). It would
    be true only if that configuration had governed every date in the corpus. That is not established, and
    asserting it would be the false history `D-009` forbids.
12. **Not adopted — D1** (build the whole dated registry inside `MD-B18` as a prerequisite of `R0003`). The
    registry is `MD-B21`'s obligation (`R0207`/`R0209`). Building it in `MD-B18` would pull global configuration
    governance into the replay stage.

**Distinguishability.** `D-009` item 5 stands unchanged: default and override runs must be distinguishable in
persisted provenance and in replay/evidence. This decision fixes where the override is recorded (the governed
correction). It does not choose storage for the run-side facts.

## Controlled revision this decision authorizes

`Config_Change_Protocol_LOCKED.md` line 7 is extended in place. The original sentence stays as its prefix and is
followed by the default, override, `system`-approval, distinguishability, no-re-stamping, `BLOCK` and
declared-effective-date rules above. Only line 7 changes, so no row is added and no rule id moves.

Stage ownership (items 8-9) is recorded in the traceability matrix, not in the strategy text. Strategy documents
own behaviour, the matrix owns stage assignment, and no strategy document names an owning stage.

The change is recorded as `DOC-CHG-20260925-001`, with successor freeze `MD-STRATEGY-FREEZE-20260925-001` in which
only the `MD-S065` fingerprint changes.

## Still not decided

- Whether a **first** execution of a historical date (backfill, Stage 8 reconstruction) is a "rerun" that needs
  an override under C1 when live configuration is not the version effective for that date. Item 10 already
  forbids such runs from *dating* a configuration, but whether they may *execute* without an override is not
  decided here. It must be decided before `MD-B21` implements `R0003` for those paths.
- Whether `BLOCKED` is reported through a newly registered reason code. No existing code has that meaning
  (`CONFIG_SNAPSHOT_REQUIRED` means a missing snapshot). Adding one would be a separate, explicitly authorized
  bounded revision of `MD-S085`.

## Authorization

The owner's message selected A1 + C1 + D2 for the `MD-S065-R0003` mechanism, and stated: "Owner now
explicitly authorizes the minimum bounded controlled revision necessary to encode A1+C1+D2." It also stated: no
runtime implementation, no test change, no `R0207`/`R0209` build, and `R0003` must not become `PROVEN`. This record
was written after that authorization.

## Invalidation / revalidation impact

- Strategy: `MD-S065` line 7 only; every other strategy document byte-identical.
- Formal traceability: `0/114` `SATISFIED` in `MD-B18` is unchanged by this decision. `MD-B18`'s required set
  loses one row by the ownership change; the resulting counts are derived by the gates, not asserted here
  (`E-MD-B18-A002-079`).
- `MD-S065-R0003` was `NOT_ASSESSED` with no bound evidence and an `INCOMPLETE` basis, so no current `PASS` or
  `SATISFIED` depends on its old text.
- `MD-S065-R0002` (`SATISFIED` under `MD-B04`, `E-MD-B04-A002-001`): its line and meaning are unchanged, and the
  revision states effective-date rules as a constraint on reruns, backfills and overrides. The concern `D-009`
  reported stands: the resolver records a run's requested date as a new version's effective date, and
  `MD-B04`'s proof of `R0002` is family-level and does not examine that date. It is reported again for `MD-B04`'s
  review, not acted on here.
- No immutable record is edited. `E-077` stays byte-identical; its "843 recompute runs" statement is corrected by
  `E-078`.

## Scope limit

This decision does not:

- change `resolveForRun`, `getOrCreateOwningRun`, `createPromoteRunFromSeed`, `assertConsumedConfiguration`,
  the correction commands, schema, migrations or runtime configuration;
- add or change any PHPUnit test, or pin future semantics in a test;
- build `MD-S082-R0207`/`R0209`;
- declare `MD-S065-R0003` `PROVEN` or change any formal `SATISFIED` state;
- change `MD-S050-R0005`/`R0014`, `MD-S019-R0071`, the `F-013` status or the `G05`/`G07` work;
- register a reason code.

The platform stays non-conformant with `MD-S065-R0003` (a rerun binds live configuration) until `MD-B21`
implements it. That gap is recorded here, not closed.
