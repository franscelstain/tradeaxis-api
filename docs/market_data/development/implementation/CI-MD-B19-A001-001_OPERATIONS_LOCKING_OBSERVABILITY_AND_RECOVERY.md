# Change Impact Declaration — `MD-B19-A001`

- ID: `CI-MD-B19-A001-001`
- Stage / Attempt / Baseline / Epoch: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260903-001`
- Predecessor stage closure: `SC-MD-B18-A001-001`
- Dependencies open at entry: `MD-DEP-0003`, `MD-DEP-0004`
- Status: `IN_PROGRESS — STAGE ENTRY NORMALIZATION`
- Strategy meaning change: `NO`
- Governance authority change: `NO`

Issued at stage entry, after `MD-B19-A001-BL001` and before any material `MD-B19` mutation, so that
it directs the attempt rather than describing it afterwards.

## Objective

Open `MD-B19` — daily/backfill/correction/replay operations, locking, observability, evidence
export and recovery — and establish what the stage actually owes before designing any proof for it.

## 1. Measured stage-entry state

This is the largest stage in the package. Measured against the matrix the baseline locked:

| | |
|---|---|
| Active rows | **936** |
| `REQUIRED` | 290 |
| — `MANDATORY` | 67 |
| — **`MANDATORY_OR_CONDITIONAL` (transitional)** | **222** |
| — `OPTIONAL_CAPABILITY` | 1 |
| `REFERENCE_ONLY` | 646, of which the classification gate reports **538 unexplained** |
| Coverage status | 289 `NOT_ASSESSED`, 646 `REFERENCE_ONLY`, 1 `OPTIONAL_NOT_REQUESTED` |

Twenty-seven owner documents, concentrated in `MD-S075` (324), `MD-S063` (168), `MD-S062` (61),
`MD-S076` (53), `MD-S071` (48), `MD-S053` (43), `MD-S077` (40), `MD-S072` (37), `MD-S068` (36).

**The denominator is not yet knowable and this declaration does not state one.** The register's
`0/224 provisional executable` figure is provisional and is not carried forward as fact. 222 required
rows still carry transitional applicability; each must be resolved against its own predicate before
any figure is quotable.

## 2. Stage-entry normalization, and how it will be done

Every one of the 222 transitional rows was read before this declaration was written. They fall into
seven semantic classes, and the classification is recorded as an **explicit reviewed
`rule_id => decision` map with a stated basis per row** — not a keyword heuristic.

That constraint is not stylistic. `F-MD-B18-A001-001` measured what happens without it: a
`strpos()` chain over rule text put 23 of 121 predicates in a family with no keyword match at all
and let earlier branches steal predicates from later ones. `MD-B19` has 222 transitional rows and
646 reference rows, so the same shortcut here would be nine times the damage.

The classes:

| Class | Disposition | Example |
|---|---|---|
| Enumerated member of a "minimum contents / minimum artifact set / minimum evidence" list | `MANDATORY` with parent/context binding and a normalized predicate | `MD-S063-R0024` "- terminal status" |
| Question a pack must answer | `MANDATORY` with parent/context binding | `MD-S063-R0070` "1. Why was correction requested?" |
| Executable locked rule | `MANDATORY` | `MD-S070-R0018` "`eod_run_events` must be append-only" |
| Capability/citation boundary | `MANDATORY`, provable by corpus assertion | `MD-S078-R0019` "…never as evidence that operations ran correctly" |
| Cross-contract alignment pointer | `REFERENCE_ONLY` with recorded basis | `MD-S075-R0305` "- `Failure_Playbook_LOCKED.md`" |
| Purpose/scope header | `REFERENCE_ONLY` with recorded basis | `MD-S076-R0001` "Define the minimum evidence pack…" |
| Conditional obligation | `CONDITIONAL_APPLICABLE` or `CONDITIONAL_NOT_APPLICABLE`, condition named and evidenced | resolved per row |

A bullet that reads as a bare noun phrase is not thereby reference material. `- terminal status`
under "Minimum contents" is an obligation stated in list form, and
`STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` §3 requires it to carry its parent context and a
normalized predicate rather than be dropped.

The 646 `REFERENCE_ONLY` rows are examined, not accepted. This is the largest reference population
in the package and `MD-S066-R0002` survived two attempts inside one exactly like it.

## 3. Affected areas

- **Runtime behaviour**: scheduler and due-date discovery, lock ownership and fencing, backfill
  resume and checkpoint recovery, correction and reseal operations, replay operations, evidence
  export, artifact generation, incident classification, freshness/SLO reporting, release gating.
- **Schema / migration**: not yet determined. No migration is declared until normalization shows one
  is owed.
- **Configuration**: not yet determined.
- **Evidence / proof mechanics**: a `MD-B19` proof surface will be built only after the denominator
  is knowable. It will use an explicit reviewed map with behavioural guards, and a corpus assertion
  only where the predicate is itself about what may be claimed.
- **Tests / gates / tooling**: the register records that seven test files remain bound to
  split-sealed composites; that is inside this stage's scope and will be resolved on its own
  evidence rather than assumed stale.

## 4. Known entry-state facts this attempt inherits and does not assume away

- `MD-DEP-0003` — `OPEN_NON_BLOCKING`, owned by stages `MD-B03/B15/B17/B19/B21/B22`. The registry
  names the `MD-B19` share exactly: **production-validation, ops-command-surface, scheduler, and
  environment-baseline contracts**. That is what is owed here, and it is not discharged by the
  normalization above.
- `MD-DEP-0004` — stage-entry semantic normalization. Discharged for `MD-B19` by the work above and
  recorded in `MD_DEPENDENCY_REGISTRY.csv`, whose per-stage chain now runs through
  `MD-B19-A001-BL001`. The global dependency stays `OPEN_NON_BLOCKING` for `MD-B20`.
- `F-MD-B01-A014-001` — the eligibility-export non-conformance raised in `MD-B01-A014` and handed to
  its owning stage. `MD-B19` owns it and it is discharged here or it is not discharged.
- `F-MD-B00-A001-001` (Class S half) is carried in the register against this stage and is
  re-measured rather than inherited as stated.

## 5. Compatibility risk

Preserve every closed predecessor boundary: the replay mode admission, exact-publication resolution
and as-known cutoff isolation bound in `MD-B18`; the atomic read product and readiness gateway in
`MD-B17`; the first-class eligibility dimensions in `MD-B16`; the coverage threshold in `MD-B15`.
Reject any operations path that finalizes a date through a non-owner run, resumes a backfill over an
already-readable date without correction, or exports evidence that implies readability for an
unsealed publication.

## 6. Residue and rework risk

Search scope is the operations surface named in §3. The specific residues to look for: a scheduler
that discovers work from wall-clock rather than governed dates, a lock whose expiry is treated as
proof the previous owner stopped, a resume that replaces a full date instead of merging on stable
identity, an artifact set whose members contradict each other about status or seal state, and an
evidence export that reconstructs history the append-only trail does not contain.

## 7. Strategy meaning change

**NO.** No strategy byte is changed. Normalization records how an existing predicate is classified
and proven; it does not restate the predicate. If a predicate turns out to be unprovable under
current authority, that is recorded as a finding rather than resolved by reinterpretation.

## Closure boundary

`MD-B19-A001` remains `IN_PROGRESS / PARTIAL` until the denominator is established by normalization,
every required predicate is proven by an executed guard shown able to fail, the proof surface passes
its readiness gate and self-test, governed evidence is issued, the binder promotes atomically, the
post-binding controls run, and the closure gate meets every condition. `MD-B20` must remain unopened
until legitimate `MD-B19` closure.

## Actual impact and result

- **Baseline**: `MD-B19-A001-BL001` issued before any material mutation, recording the measured entry
  state above and the fingerprints it was measured against.
- **Normalization**: complete. All 936 active rows carry a recorded decision traced to their parent
  section in the owner contract. 682 rows changed applicability:

  | Transition | Rows |
  |---|---|
  | `REFERENCE_ONLY` → `MANDATORY` | **424** |
  | `MANDATORY_OR_CONDITIONAL` → `MANDATORY` | 216 |
  | `REFERENCE_ONLY` → `CONDITIONAL_APPLICABLE` | 36 |
  | `MANDATORY_OR_CONDITIONAL` → `REFERENCE_ONLY` | 6 |

  **424 obligations had been filed as reference** and were invisible to every gate, because the
  mixed-run detector only fires where a section holds required and reference rows together and these
  sat in sections classified entirely as reference.

- **Denominator**: **743** — 707 `MANDATORY` plus 36 `CONDITIONAL_APPLICABLE`, with 192
  `REFERENCE_ONLY`, 1 `OPTIONAL_CAPABILITY`, zero transitional and zero undecided reference rows.
  The register carried `0/224 provisional` at entry; the real figure is 3.3 times that.

- **Three over-claims caught and corrected during the pass**, each by tabulating every applicability
  transition after applying rather than by re-reading the corpus:
  1. the first pass scoped normalization to the transitional subset, leaving `MD-S063-R0070..R0071`
     `MANDATORY` while `R0072..R0079` — the same numbered list under the same parent — stayed
     `REFERENCE_ONLY`. Re-scoped to whole sections.
  2. the section rule promoted `MD-S075-R0139` out of `OPTIONAL_CAPABILITY` although its own text
     declares the projection optional. Restored with a recorded basis.
  3. the section rule read `MD-S075`'s artifact-list header "At minimum … must be reconstructable"
     across items 7–11, which actually sit under "Where applicable, the following should also be
     available". Those five became `CONDITIONAL_APPLICABLE` with the condition named.

- **A reporting defect fixed**: `GenerateMarketDataCurrentState.php` printed "FINAL for every
  machine-checked criterion" while 515 reference rows had never been examined. Both of its signals
  are blind to a section classified entirely as reference. A third signal now counts reference rows
  carrying no recorded stage-entry decision, and reports `PROVISIONAL` while any remain.
