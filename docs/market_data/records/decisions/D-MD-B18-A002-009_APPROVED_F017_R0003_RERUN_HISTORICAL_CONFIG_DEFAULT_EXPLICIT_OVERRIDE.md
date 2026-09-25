# Decision — a rerun binds the configuration effective for its requested trade date; anything else needs an explicit governed override

- ID: `D-MD-B18-A002-009`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline reviewed: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Finding: `F-MD-B18-A002-017` (`G09`, `MD-S065-R0003`)
- Supporting evidence: `E-MD-B18-A002-077` (G09 reconstruction: the real rerun/promote path binds the
  later live configuration; classification `AUTHORITY_AMBIGUITY`; three options presented)
- Rule this decision disambiguates: `MD-S065-R0003`
- Issued: 2026-09-25T09:37:43+07:00
- Decision status: `APPROVED`
- Strategy impact: `NONE` — no strategy byte changes; freeze `MD-STRATEGY-FREEZE-20260922-001` unchanged
- Governance impact: `NONE` — no `CONTROLLED_REVISION` document changes; no `DOCUMENT_CHANGE_LOG.md` entry

## Question

`Config_Change_Protocol_LOCKED.md:7` (`MD-S065-R0003`): "reruns must use the registry version effective
for the requested trade date or explicitly documented override". `E-MD-B18-A002-077` found that the real
rerun/promote path (`EodRunRepository::createPromoteRunFromSeed`) binds whatever configuration is live,
and mints it as a snapshot stamped effective from the requested date; that authority does not define the
"explicitly documented override"; and that the recompute and correction contracts rerun historical dates
after formula/configuration changes. It presented three readings and chose none.

## Authority review

Reverified against the owner's choice before this record was written. No contradiction was found.

1. `MD-S065-R0003` names the requested trade date as the default and makes any other configuration
   conditional on an explicitly documented override. Option 2 is that sentence read literally: it adds
   no new default and removes none.
2. `Platform_Config_Registry_LOCKED.md` (`MD-S082`): `:286` operational production "selects the approved
   configuration effective for the run context and records when it became known"; `:274` "Silent runtime
   overrides and undocumented defaults are forbidden"; `:15`/`:269` a snapshot and a key definition carry
   an effective interval and a recorded/known interval. Binding live configuration by default, and
   stamping it effective from a date it did not govern, is the silent override `:274` forbids. Option 2
   removes it.
3. `Historical_Correction_and_Reseal_Contract_LOCKED.md`: a rerun that changes a "formula, or
   configuration binding that affects interpretation" is a correction, which requires an approved
   request with reason and approval metadata, a new run context and an explicit supersession trail.
   `Audit_Hash_and_Reproducibility_Contract_LOCKED.md:138-141`: a changed config in a rerun creates a
   distinct publication context. These contracts say a rerun under different configuration must be
   labelled and traceable; they do not say it may be implicit. Option 2 is consistent with them: such a
   rerun is exactly the intentional, recorded case the override covers.
4. `Current_Indicator_Recompute_Command_Contract.md:140-142` ("rerun affected dates after a change to ...
   indicator formula") and its correction-current lifecycle (`:93`): the recompute exists to apply a
   changed formula/configuration to historical dates. Under Option 2 that is an intentional use of a
   configuration not effective for those dates, so it proceeds only through the override. This is an
   obligation on the recompute workflow, not a contradiction with it.
5. `MARKET_DATA_DOCUMENT_AUTHORITY.md` rule 4: this record selects one reading of existing text; it does
   not become strategy authority itself. The override *mechanism* is not defined by existing authority and
   is not defined by this record (see "Not decided").

## Decision

1. **Default.** A rerun for requested trade date `D` resolves and binds the configuration/registry version
   effective for `D`. Historical rerun is point-in-time by default.
2. **No implicit substitution.** Live, current or latest configuration is never used implicitly for a
   rerun of `D`. That it is live, newer or already loaded in the runtime is not a reason to bind it.
3. **Explicit governed override.** A rerun may use a different configuration, including a current or
   newer one, only when an explicit governed override is present. The override is intentional
   provenance, not historical truth: it records that someone chose to run `D` under a configuration that
   did not govern `D`.
4. **No re-stamping.** An override configuration is never recorded, re-stamped or selected as if it had
   been effective at `D`. A configuration's effective interval is a fact about the configuration, not about
   the run that first used it. Using configuration B for `D` under an override must not make B the
   version a later lookup returns as effective for `D`.
5. **Distinguishable provenance.** Historical-default runs and override runs must be distinguishable in
   persisted provenance and in replay/evidence. At minimum an observer must be able to tell, for any
   rerun: whether it was historical-default or override; the configuration effective for `D`; the
   configuration actually bound; what authorized the override and why; and which prior or seed run it
   relates to. This lists facts that must be observable; it does not choose their storage.

## Not decided (owner decisions still required)

Existing authority does not define these, and this record does not choose them:

- **Invocation and authorization** — what constitutes the explicit governed override and who may grant
  it. The correction request (`REQUESTED` -> `APPROVED`, approval metadata, reason) is the closest existing
  vehicle, but four automated workflows create and approve their own correction requests as `system`
  (`MarketDataPipelineService:3193-3201`, `BackfillLifecycleOrchestrator:1228-1237`,
  `RecomputeCurrentIndicatorsCommand:228-236`, `StageEightCorpusReconstructionService:491-499`), so a
  correction approval is not, today, an explicit governed decision about configuration.
- **Execution when the default cannot run** — the platform executes only live configuration:
  `RunInputCaptureRepository::assertConsumedConfiguration()` rejects a run whose bound snapshot differs
  from live config. When live configuration is not the version effective for `D` and no override is
  present, the rerun must either be `BLOCKED` or the platform must gain the ability to execute a
  historical snapshot. This record does not choose, and does not relax the guard.
- **Scope of "rerun" and of an override** — whether a first execution of a historical date (backfill,
  Stage 8 reconstruction) is a rerun for this rule; how a new configuration version legitimately acquires
  its effective date (today the resolver stamps the requested date of whichever run first resolves it);
  and whether an override covers one run, one trade date, one correction or a declared range.
- **Where the resulting rule lives** — `records/decisions/README.md` requires a resulting current rule to be
  reflected in authority, and `MD-S065` is frozen strategy.

## Authorization state

**Explicit owner authorization was given inline with this decision.** The owner's message selected
"OPTION 2 — HISTORICAL CONFIG BY DEFAULT, EXPLICIT GOVERNED OVERRIDE FOR INTENTIONAL RERUNS" and stated
that a default rerun of `D` binds the configuration/registry version effective for `D`; that
current/live/latest configuration may not be used implicitly; that a different configuration, including a
current or newer one, is allowed only through an explicit governed override that is recorded and
distinguishable from historical-default execution; and that the implementation may not change or stamp a
historical effective date so that live configuration looks effective since `D`. This record was written
after that authorization, not before it.

## Invalidation / revalidation impact

- Strategy: none; freeze `MD-STRATEGY-FREEZE-20260922-001` unchanged.
- Proof basis: unchanged at 91 `PROVEN` / 23 `INCOMPLETE`. `MD-S065-R0003` remains `INCOMPLETE` until the
  mechanism is decided, implemented and proven.
- Formal traceability: unchanged at `0/114` `SATISFIED`.
- No prior evidence is rewritten. `E-MD-B18-A002-077` stands as issued; this record settles its semantic
  ambiguity and leaves its mechanism questions open.
- **Reported, not acted on:** `MD-S065-R0002` ("change must be recorded in the configuration registry with
  effective date") is `SATISFIED` under `MD-B04` (`E-MD-B04-A002-001`). `E-077`'s executed run showed the
  recorded effective date of a configuration can be the requested date of a rerun it never governed.
  Under item 4 that recording is incorrect. Whether this affects `MD-S065-R0002`'s `SATISFIED` state is
  outside `MD-B18` and this unit, and is recorded for the owning stage's review.

## Scope limit

This decision closes only the semantic ambiguity `E-MD-B18-A002-077` recorded. It does not:

- declare `MD-S065-R0003` `PROVEN`;
- define or implement the override mechanism;
- change configuration resolution, `assertConsumedConfiguration()`, rerun, correction, recompute or
  backfill behavior;
- rewrite any existing configuration snapshot or run binding;
- change any formal `SATISFIED` state;
- modify `MD-S065`, `MD-S082` or any other strategy document.
