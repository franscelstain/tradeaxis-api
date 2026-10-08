# Finding — `F-MD-B19-A001-003`

- ID: `F-MD-B19-A001-003`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Raised at: 2026-10-08T07:08:21+07:00
- Severity: `P2`
- Status: `OPEN — OWNER DECISION REQUIRED BEFORE THE FIRST MD-B19 PRODUCTION CHANGE`
- Class: `CROSS_STAGE_BUILD_IDENTITY_DEPENDENCY`
- Blocked: every `MD-B19` unit that edits a file of the frozen R0025 build (`app/`, `config/`,
  `bootstrap/`, `composer.json`, `composer.lock`, `vendor/`). Does **not** block test-only, tooling or
  documentation units, and does not reopen `MD-B18`.
- Blocks strategy change: `NO`
- Dependency: `MD-DEP-0025`

## Statement

The R0025 golden fixture admitted at `MD-B18` closure — candidate-v5 — is valid for exactly one build.
Its own frozen identity says so:

> This candidate is valid only for this build. A change to any file of the build changes the identity
> and requires a new candidate version, review and approval.
> — `tests/fixtures/replay/r0025-synthetic-v2-candidate-v5/inputs/frozen_build_identity.json`

and `D-MD-B18-A002-018` item 13 makes the consequence binding:

> Jika implementation change membuat current R0025 proof tidak lagi valid untuk current build/config
> semantics, successor candidate harus dibuat, independently reviewed, dan owner-approved kembali.

Measured on the repository at `eaf9dbe` (clean tree), the build is **5916 files**:

| Part of the build | Files |
|---|---|
| `app/` | 195 |
| `config/` | 4 |
| `bootstrap/` | 1 |
| `composer.json`, `composer.lock` | 2 |
| `vendor/` | 5714 |
| `tests/`, `docs/`, `database/`, `routes/` | **0** — not part of the build |

`R0025SyntheticV2CandidateFixtureTest::test_the_publication_bound_build_identity_is_the_frozen_build_identity`
re-hashes every one of those files against `inputs/frozen_build_manifest.txt` and asserts the executing
build equals the frozen literal. It is green today (the file ran 46 tests / 6885 assertions in a clean
single-process run during this attempt).

## Why this matters to `MD-B19`

`MD-B19` owns operations, evidence export and recovery, and several of its measured non-conformances
are production defects:

| Finding / family | Files that would change | In the frozen build |
|---|---|---|
| `F-MD-B01-A014-001` — `eligibility_export.csv` omits `listing_id`, `publication_id`, `data_usable`, the reason set | `MarketDataEvidenceExportService.php`, `EodEvidenceRepository.php` | yes — manifest lines 44 and 150 |
| `invalid_bars_export.csv` omits `listing_id` and the source-observation reference (measured on a real sealed publication in this attempt) | same two files | yes |
| `anomaly_report.md` carries no run-identity line (measured, same run) | `MarketDataEvidenceExportService.php` | yes |
| scheduler outcomes `SUCCESS_HELD` / `SKIPPED_LOCKED`, takeover/fencing, incident classification, alert states — the contract vocabulary does not occur in `app/` (presence/absence search, not yet a reviewed finding) | `MarketDataPipelineService.php` and others | yes |

The first byte changed in any of those files:

1. turns the control above red, because the executing build is no longer the frozen build;
2. makes every R0025 publication produced afterwards carry a different `executable_build_identity`,
   so candidate-v5 no longer applies to the current build (`MD-S003-R0025`'s proof is, in effect,
   for the previous build);
3. under `D-MD-B18-A002-018` item 13, requires candidate-v6: authored, independently reviewed by a
   non-author, and owner-approved.

None of this reopens `MD-B18`. `SC-MD-B18-A002-001` is a true statement about the frozen build and stays
so. What changes is whether the R0025 proof is current for the *new* build, which is a build-identity
re-bind, not a semantic regression of a `MD-B18` predicate.

## What this attempt did and did not do

No file of the frozen build was changed in this attempt. Every probe that edited one restored its bytes
and verified the sha256 (the frozen-build control ran green afterwards). The units chosen so far —
`range_window_warmup_calendar` proof — are test-and-tooling only and are unaffected.

## Options for the project owner

- **O1 — batch, then re-freeze once (recommended).** Finish all `MD-B19` production changes first, each
  under its own finding and proof, then author candidate-v6 against the final `MD-B19` build before
  `MD-B19` closure. Until then the build-identity control is a known, tracked red from the first
  production commit. Cost: one candidate cycle (author, review, approval, admission), not one per change.
- **O2 — re-freeze per change.** Satisfies item 13 literally after every production edit. Cost: a full
  candidate cycle each time; not recommended.
- **O3 — redefine what the build identity covers** (for example, only the files the replay path
  executes). That changes `php_source_build_v1` semantics and `D-MD-B18-A002-018`'s premise, so it is a
  strategy-level decision and would need its own review; it also risks weakening what the identity proves.
- **O4 — defer production-changing families.** Leaves `F-MD-B01-A014-001` (owned by `MD-B19`:
  "discharged here or it is not discharged") undischarged, so `MD-B19` could not close.

The owner also has to say how the interim red is treated: the `D-MD-B18-A002-022` acceptance rule
(0 errors, 0 skips, every test outside the oracle class passing) was written for `MD-B18` closure and
does not carry over to `MD-B19` interim units.

## Required outcome

An owner decision selecting O1–O4 (or another path) before any `MD-B19` unit edits a frozen-build file;
then, under O1, candidate-v6 as the last `MD-B19` production-proof step and a `MD-B19` closure condition.
`MD-B19` units that touch only `tests/`, `docs/…/tests/` and records continue meanwhile.

## Related

- `D-MD-B18-A002-018`, `D-MD-B18-A002-019`, `E-MD-B18-A002-115`, `SC-MD-B18-A002-001`
- `F-MD-B01-A014-001` (owned by `MD-B19`), `F-MD-B19-A001-002`
- `E-MD-B19-A001-002` (where this was measured), `MD-DEP-0025`
