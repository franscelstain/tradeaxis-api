# Finding — `F-MD-B19-A001-003`

- ID: `F-MD-B19-A001-003`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Raised at: 2026-10-08T07:08:21+07:00
- Severity: `P2`
- Status: `OPEN — O1 SELECTED (D-MD-B19-A001-001); SUCCESSOR-PENDING UNTIL CANDIDATE-V6 IS ADMITTED`
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

## 2026-10-08T08:04:28+07:00 Owner decision recorded: O1 (`D-MD-B19-A001-001`)

The project owner selected **O1**. Consequences, as decided (not widened here):

- Production-changing `MD-B19` units may proceed and may be batched; `MD-DEP-0025` no longer blocks them.
- Candidate-v5 stays current for the approved frozen build until the first executable-build change. At that change it becomes historical-only for current-build purposes; `MD-B18` is not reopened.
- From that point the build-identity failure of `R0025SyntheticV2CandidateFixtureTest` is a governed **successor-pending** failure under `MD-DEP-0025`: never skipped, never called PASS, never hidden, never repaired by editing candidate-v5. Any other R0025 failure is a regression.
- One candidate-v6 is authored against the final build after the last executable-build change and before `MD-B19` closure; it needs independent review, owner approval, admission and the current-build R0025 revalidation. A build change after it requires another freeze.

State at the time of recording: the frozen build equals the candidate-v5 manifest (5916 files, 0 differ). This finding stays open until candidate-v6 is admitted; it is the record of the successor-pending state.

## 2026-10-08T10:53:24+07:00 First executable-build change recorded (`E-MD-B19-A001-004`)

Under O1 (`D-MD-B19-A001-001`) the first executable-build change was made by the implementation of `MD-S053-R0210` (`D-MD-B19-A001-002`): `app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php` (written 2026-10-08T08:57:11+07:00), followed by `app/Application/MarketData/Services/ApiBackfillRangeAcquisitionService.php`. Before it all 5916 frozen-build files equalled the candidate-v5 manifest; now exactly those two differ.

From this point candidate-v5 is immutable historical proof and is no longer current-build R0025 proof; `MD-B18` stays `DONE` / `PASS`; `MD-DEP-0025` is the active successor-pending tracker. `R0025SyntheticV2CandidateFixtureTest::test_the_publication_bound_build_identity_is_the_frozen_build_identity` fails together with 12 derived R0025 / B18 scenario tests, all with the single mismatch field `bound_input_executable_build_identity` (13 tests in the final full suite, besides the 7 governed oracle failures; nothing else fails); none is skipped or reported as PASS and candidate-v5 is untouched. Candidate-v6 is not created: it is owed after the last `MD-B19` executable-build change and before closure.

## 2026-10-08T12:02:54+07:00 Third executable-build file (`E-MD-B19-A001-005`)

The correction of `MD-S075-R0076` (`source_context.retry_attempt_count` is null, not 0, when the run recorded no retry telemetry) changed `app/Application/MarketData/Services/MarketDataEvidenceExportService.php` (written 2026-10-08T11:26:42+07:00). Files now differing from the candidate-v5 manifest: `ApiBackfillRangeAcquisitionService.php`, `MarketDataEvidenceExportService.php`, `PublicApiEodBarsAdapter.php`. The governed successor-pending set is unchanged: the same 13 tests, each with the single mismatch field `bound_input_executable_build_identity`.

## 2026-10-08T14:15:01+07:00 Third executable-build file changed again (`E-MD-B19-A001-006`)

Implementing `D-MD-B19-A001-003` (strict mirror of `final_reason_code`, the separate effective field, the derived-companion marker and the consumer updates) changed `app/Application/MarketData/Services/MarketDataEvidenceExportService.php` again. It was already one of the three files differing from the candidate-v5 manifest, so the set is unchanged: `ApiBackfillRangeAcquisitionService.php`, `MarketDataEvidenceExportService.php`, `PublicApiEodBarsAdapter.php`. The governed successor-pending set is unchanged: the same 13 tests, each with the single mismatch field `bound_input_executable_build_identity`.

## 2026-10-09T08:44:28+07:00 Fourth executable-build file (`E-MD-B19-A001-010`)

Under O1, `app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php` (in the candidate-v5 frozen build manifest) was edited by one line to implement `D-MD-B19-A001-004`. Four files now differ from candidate-v5. The four test classes that carry the governed failures were run: exactly the same 13 tests fail. Nothing was skipped, candidate-v5 is untouched and candidate-v6 is not created.

## 2026-10-09T13:48:09+07:00 Fifth executable-build file (`E-MD-B19-A001-012`)

Under O1, `app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php` (in the candidate-v5 frozen build manifest) was edited to enforce, at the eligibility write, the locked rule that a blocked row carries a reason set (`F-MD-B19-A001-009`). Five files now differ from candidate-v5. The full suite shows exactly the 7 oracle failures and the same 13 build-identity failures. Nothing was skipped, candidate-v5 is untouched and candidate-v6 is not created.
