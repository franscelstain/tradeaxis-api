# Decision — F-MD-B10-A002-002 static-guard dispositions: issued-evidence alias admission and foundation table ownership

- ID: `D-MD-B10-A002-004`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Work / Baseline: `MD-B10` / `MD-B10-A002` / `MD-B10-A002` / `MD-B10-A002-BL001`
- Finding / CI: `F-MD-B10-A002-002` / `CI-MD-B10-A002-001`
- Parent decisions: `D-MD-B10-A002-001` and `D-MD-B10-A002-002` (shared security-identity foundation ownership and persistence); `D-MD-20260822-04` (alias meaning ownership)
- Issued: `2026-10-01T13:07:47+07:00`
- Decision status: `ISSUED` — reviewed decision under the owner's direction of 2026-10-01 to resolve each `F-MD-B10-A002-002` member through its recorded owner, the alias member "→ governed decision karena E001 immutable"
- Strategy impact: `NONE` — no strategy byte changes; freeze `MD-STRATEGY-FREEZE-20260925-001` unchanged
- Governance impact: `NONE` — no `CONTROLLED_REVISION` document changes; no `DOCUMENT_CHANGE_LOG.md` entry

## Questions

`F-MD-B10-A002-002` records four static guards that fail on committed surfaces. It forbids adding a document, file or mock to a guard's allow-list without a governed decision and a probe showing the guard still fails on an unlisted violation. Two members need such a decision; two do not.

1. **Alias guard.** `AliasNamingAndMeaningBoundaryTest` pins the documents that use the `eligible` alias identifier without repeating that it means `data_usable`. `E-MD-B10-A002-001` now appears in that set. The record is `IMMUTABLE_AFTER_ISSUE` and cannot be edited.
2. **Domain ownership guard.** `DomainOwnershipSurfaceTest` treats every table the schema reader returns as a Market Data table, so the shared foundation repository's own tables count as Market Data tables touched from outside the domain tree.

## Authority review

### Alias member

1. `MD-S020-R0067` puts the repetition duty on "every contract that uses" the alias. `F-MD-B01-A003-001` measured the scan on the identifier and listed the one issued evidence record it found "for completeness rather than as an action item", because evidence cannot be edited. The pinned set has carried two issued evidence records since then.
2. `E-MD-B10-A002-001` uses the identifier on one line only. That line quotes an existing test assertion verbatim, inside a source-assertion review, and names the preserved `eod_eligibility.eligible` column. The record is not a contract, introduces no new surface (`MD-S020-R0071`), and restates nothing about the field's meaning.
3. `DOCUMENT_CHANGE_POLICY.md` §3 forbids editing the record. A correction record cannot satisfy a per-document scan, because the scan reads the original.
4. The meaning itself stays discharged by the governed ownership chain of `D-MD-20260822-04`, which this decision does not touch.

### Domain ownership member

1. `D-MD-B10-A002-001` places the shared security-identity foundation outside Market Data ownership. `D-MD-B10-A002-002` names its canonical persistence: `si_packages`, `si_entities`, `si_revisions` and `si_holds`, written through the foundation repository under `app/Infrastructure/Persistence/SecurityIdentity`.
2. The guard's population is the whole schema surface, base SQL plus every migration. The foundation migration therefore entered the population as if it created Market Data tables. The flagged references are the foundation repository reading its own tables.
3. The guard's purpose — no second owner of a Market Data table outside the domain tree — is unchanged by this decision.

### Members that need no decision

- `DateDrivenCapabilityAndProviderAbstractionTest`: the foundation reader carried the provider request URL as a literal. The shared foundation owner removes the literal; the reader now takes each source locator from its fingerprinted package. The guard is unchanged.
- `LifecycleProofIsNotMockedTest`: the guard is unchanged. MD-B18-A002 replaces the mocked repositories in its own DB-backed test with a persisted world. That work is recorded in `E-MD-B18-A002-087`.

## Decision

1. **Alias.** `E-MD-B10-A002-001` is admitted to the alias guard's pinned set as issued evidence, at its issued SHA-256 `4a371ba393c9a209f2f3dd5e70c84612116412186a20e88f740b576835921887` only. The guard keeps the admission in a constant keyed by that hash, and a separate test fails if the record no longer has those bytes or no longer uses the identifier. Any other document that uses the identifier without the meaning still fails the measured result.
2. **Foundation tables.** The four tables named by `D-MD-B10-A002-002` are excluded from the Market Data table population of `DomainOwnershipSurfaceTest`, by name and under three conditions the guard asserts: the foundation migration creates exactly those tables, no other migration creates or alters them, and the base Market Data schema defines none of them. A new test requires the foundation repository to be the only application surface that touches them, inside or outside the Market Data tree. The pinned legacy model set is unchanged.
3. Neither change is a general exception. No other evidence record, document, file, table or mock is admitted, and adding one needs its own governed decision and probe.

## Required proof

Each change must be proven green on the repaired surface and red on the violation it exists to catch: an unadmitted document using the alias, a changed admission hash, a Market Data table read from the foundation repository, a foundation table read from a Market Data service, and a widened exclusion set. The results are recorded in `E-MD-B10-A002-014`.

## Invalidation / revalidation impact

- No predicate changes status. B10 stays 1016 SATISFIED / 56 NOT_ASSESSED / 1072; B05 117/117; B18 100/113 reviewed basis, formal 0/113.
- `MD-S020-R0067`, `MD-S020-R0071` and the Market Data ownership rows keep their current bindings. Their guards are stricter than before the regressions: the alias admission is hash-pinned and foundation-table ownership is asserted in both directions.

## Scope limit

This decision governs the two guard changes above for `F-MD-B10-A002-002` only. It does not resolve `MD-DEP-0021`, `MD-DEP-0020` or `MD-DEP-0019`, and it does not change the B18 resume path. If `D-MD-B10-A002-002` is superseded and the foundation's persistence changes, the exclusion must be re-derived from the successor decision.
