# MD Change Impact Declaration — CI-MD-B01-A007-001

- ID: `CI-MD-B01-A007-001`
- Stage / Attempt / Baseline / Epoch: `MD-B01` / `MD-B01-A007` / `MD-B01-A007-BL001` / `MD-REBASELINE-20260820-001`
- Record class: `MUTABLE_TRACEABLE`
- Issued: 2026-08-21, **before** the matrix mutation and after the guard was written and mutation-proven, following the order established at `MD-B01-A006`.

## Why this attempt is material

It changes `coverage_status` on traceability rows and adds an executable test. Both are named material by `CHANGE_IMPACT_DECLARATION_STANDARD.md` section 1.

## Scope

Advance `MD-B01` coverage against the corrected 155-rule set. 20 rules move `NOT_ASSESSED` → `SATISFIED`, all owned by `Domain_Boundary_Invariants_LOCKED.md`:

| Rules | Proof |
|---|---|
| `MD-S020-R0054` | The enforcement sentence for the forbidden downstream concepts. Checked on every surface it names that exists here: 456 schema table and column names, 468 reason codes, 153 configuration keys, 38 command signatures, and the HTTP route surface. |
| `MD-S020-R0075`–`MD-S020-R0079` (5) | Indicators must not become strategy signals, ranked scores or candidate ordering, recommendation engines, hidden entry/exit rules, or position-sizing inputs. Checked against the indicator columns and the indicator service methods. |
| `MD-S020-R0110`–`MD-S020-R0113` (4) | Eligibility must never become a screening engine, a watchlist replacement, a proxy ranking layer, or entry/exit timing infrastructure. Checked against the eligibility methods, result keys, and ordering behaviour. |
| `MD-S020-R0115`–`MD-S020-R0123` (9) | The nine surfaces that must not silently embed consumer policy. Each is resolved to its own file set and checked separately, so no surface's result stands in for another's. |
| `MD-S020-R0158` | A guard enforcing the forbidden list must distinguish the overloaded upstream senses. Proven directly against this guard. |

## Affected areas

| Area | Impact |
|---|---|
| Traceability | **Material.** `MD-B01` `35/155` → `55/155`. Global `SATISFIED` 35 → 55; denominator unchanged at 2010. |
| Tests | **Material.** One test file added: 19 tests, 55 assertions. |
| Schema / config / runtime / provider / backfill / replay / ops | **None.** No file under `app/`, `database/`, or `config/` is modified. Six of these files were mutated transiently during the negative proof and each was restored and verified byte-identical. |
| Evidence | Additive. No prior evidence is restated or invalidated. |
| Runtime artifacts | **None.** Under `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md` section 5 this is document-and-test work claiming no externally-stored runtime output, so no `storage/**` inspection is required or performed and no raw-artifact linkage is claimed. |

## Compatibility risk

**Low.** Nothing existing changes behaviour; a test is added and 20 rows change state. The added guard is strictly stricter — it can only turn a future `PASS` into a `FAIL`.

## Residue / rework risk

**Low.** Six mutations were each verified as landed before the verdict was read, and each turned the guard red: a forbidden schema column, a forbidden reason code, a forbidden configuration key, a forbidden command option, a downstream-policy reference inside the indicator surface, and preference ordering inside the eligibility repository. Two controls confirmed the guard stays green on legitimate additions — a `candidate_publication_target_date` column and a `retention_policy` config key.

`MD-S020-R0158` is the reason this guard is built on compound concepts rather than tokens, and the reason it was corrected once during this attempt. The first ordering check flagged `orderBy('elig.ticker_id')` in `EligibilitySnapshotScopeRepository`, which is deterministic retrieval order required for reproducible reads, not preference ordering. The check now separates ordering by a stable identity or date key from ordering by a measure, and that separation is itself asserted in both directions. Correcting it made the guard more precise, not weaker: the six mutations above were all proven against the corrected version.

Residual risk, stated rather than hidden:

- The guard proves that no market-data surface **names** a downstream concept and that no named surface **references** downstream policy. It does not prove that no downstream semantic could ever be expressed under an innocent name. That limit is inherent to a naming boundary.
- `MD-S020-R0054` names APIs among its surfaces. This application publishes no market-data HTTP endpoint, so that surface is currently empty. The test asserts the emptiness rather than assuming it, and begins checking route names the moment one is added.
- `MD-S020-R0054` also names "positive feature definitions", which is document prose. That half rests on the document-corpus guards from `MD-B01-A003` and `MD-B01-A006`, not on this one.

## Affected dependencies and relationships

- `MD-DEP-0004` — unaffected, remains `OPEN_NON_BLOCKING`.
- No prior evidence is carried forward; every rule claimed here is proven by the test file added under this attempt.

## Strategy semantic change

`NO`. `Domain_Boundary_Invariants_LOCKED.md` is read as the owner contract and is not modified.
