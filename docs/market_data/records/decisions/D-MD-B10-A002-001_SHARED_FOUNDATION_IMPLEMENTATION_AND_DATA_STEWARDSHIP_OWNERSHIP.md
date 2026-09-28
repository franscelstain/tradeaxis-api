# Architecture decision — shared security-identity implementation and stewardship ownership

- ID: D-MD-B10-A002-001
- Status: ISSUED / Accepted (bounded implementation-ownership decision)
- Stage / Attempt / Work / Baseline: MD-B10 / MD-B10-A002 / MD-B10-A002 / MD-B10-A002-BL001
- Verification epoch: MD-REBASELINE-20260820-001
- Issued: 2026-09-28T23:54:25+07:00
- Finding / dependency: F-MD-B18-A002-023 / MD-DEP-0021
- Basis: E-MD-B10-A002-004; current docs/README.md shared foundation boundary; MD-S057/055; docs/api_architecture/arsitektur-decision-record.md
- Strategy meaning change: NONE
- Authorization: current project-owner request to determine and establish accountable logical implementation/data ownership where current governance permits. No independent human review, team acceptance, dataset completeness approval or identity-algorithm approval is claimed.

## Context

E004 established that existing authority is sufficient and same-repository delivery is permitted. Leaving all implementation accountability UNKNOWN perpetuates a deployment assumption that current architecture does not require. The shared security-identity foundation is already the logical dependency named by MD-S057; it is not a new Market Data semantic owner.

## Decision

1. TradeAxis Shared Security Identity Foundation is the logical implementation and canonical identity/lifecycle persistence owner inside tradeaxis-api, outside Market Data ownership. The TradeAxis project-owner role is accountable for its delivery and data stewardship. No new team/person, repository, deployment, namespace, physical table or stage ID is assigned.
2. The project owner, acting as shared-foundation data steward, is responsible for source custody/admission, provenance, corrections and evidence-backed continuity adjudication. Acquisition/import executors act for this role and cannot infer continuity from symbol/company strings.
3. Foundation delivery proof belongs to this shared implementation responsibility, correlated through the same MD-B10-A002/BL001/CI and MD-DEP-0021 dependency work. B05 retains bounded resolver proof; B10 retains allocation-independent artifact integration proof; B21 retains relevant external completeness obligations. No predicate ownership or status changes here.
4. Market Data consumes the foundation through explicit stable temporal binding. It retains bars/indicators/eligibility/publications and frozen consumer projections; global-root issuance remains outside Market Data. Watchlist/future consumers receive only separately approved intake, not a new raw-table shortcut.
5. Current source-readiness/admission is recorded by the correlated E-MD-B10-A002-005 extension. Partial factual admission does not resolve MD-DEP-0021, authorize hash remediation, establish a complete universe, or supply missing canonical roots.
6. No ID representation or issuance algorithm is selected. Any future implementation must satisfy immutable/retained import/rebuild identity, source-backed lifecycle and fail-closed ambiguity. A source ISIN is a factual input, not a decision to make it the root algorithm.

## Reasons and alternatives

This assigns actionable project/module roles using the existing shared owner boundary and the current task authorization. Keeping UNKNOWN despite permissible logical assignment would leave avoidable coordination debt. Requiring another service/repository has no authority basis. Assigning master ownership to Market Data conflicts with MD-S057. Inventing a named team or global-master algorithm would exceed this decision.

## Boundary and document impact

The current dependency, finding, Change Impact Declaration, orchestration and registries record these roles. Authority files remain unchanged. Prior issued E002/E003/E004 retain their factual historical UNKNOWN statements; this decision resolves logical accountability prospectively.

## Implementation and negative consequences

No code/schema/data backfill or consumer integration is performed. Physical placement, concrete persistence/import design and any material integration are future technical choices requiring the applicable early impact/baseline/correlation and architecture review. Role assignment does not make missing source history available, and no Market Data-consumable listing is certified here.

## Review again

Review on a concrete shared-module/intake/storage design, a change of accountable role, conflicting source/continuity evidence, or affected-current-verification integration. Preserve historical decisions and issue a successor if this ownership decision changes.
