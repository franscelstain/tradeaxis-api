# Read-Side Enforcement Anti-Bypass Contract (STRATEGY LOCKED)

Status: LOCKED at strategy level; implementation and production relock remain unproven.

## Authority

The versioned market-data read gateway is the only normal consumer authority. It resolves an active immutable publication and returns the DTO owned by `Downstream_Consumer_Read_Model_Contract_LOCKED.md`. Weekly Swing is an initial consumer profile, not gateway authority or readiness policy.

## Pointer-only resolution

Normal reads start from an active publication pointer scoped by market, product, requested/effective trade date, and read-model version. They do not discover authority from the newest row, job, run, seal, history record, or timestamp.

Every downstream join is constrained to the resolved publication and its frozen config, identity/calendar/status/event/factor/formula versions. A mutable current table may exist as a replaceable projection but cannot provide historical authority.

Current compatibility entry point `resolveCurrentReadablePublicationForTradeDate` must delegate to this pointer-only decision and will be replaced/versioned with the minimum market-data product gateway; its presence does not waive V2 fields or freshness rules.

## Forbidden Bypass Rule

The shortcuts listed below and in the owner consumer contract are forbidden in every consumer surface.

## Defense in depth

- application repositories expose only the read-gateway interface to consumer modules;
- database consumer roles can execute approved views/procedures but cannot select internal fact/candidate/history tables directly;
- exports, queues, caches, notebooks, and scheduled jobs use the same gateway or a publication-bound bulk surface;
- static checks flag raw table names, `MAX(trade_date)`, client-side formulas, and direct current-row access in consumer namespaces;
- integration tests prove unsealed, superseded, mixed-publication, configless, and ambiguous-pointer data are unreachable.

## Audit access

Privileged audit/reconciliation paths may inspect internal and superseded artifacts. They require explicit mode/authorization, expose publication state and integrity warnings, and cannot return the normal consumer DTO as if the artifact were current/readable.

## Failure behavior

Pointer ambiguity, missing publication binding, seal/hash mismatch, cross-publication rows, or unknown readiness fails closed. The gateway does not fall back around an integrity error. Policy-allowed prior-date fallback occurs only after a valid requested-date state decision and retains its true effective date.

## Fail-Safe Rule

An unrecognized or incompletely bound state returns unavailable/blocked evidence; it never attempts a best-effort internal-table read.

## Acceptance evidence

Production relock requires code search/static enforcement, database privilege evidence, gateway integration tests, concurrency tests during pointer replacement, and runtime query/audit evidence showing no consumer bypass. A policy document or repository convention alone is insufficient.

Contract changes follow `docs/market_data/audit/AUDIT_UPDATE_GOVERNANCE.md` and the canonical current-state verdict in `docs/market_data/audit/reports/AUDIT_FINAL_STATE.md`.

## Capability boundary (LOCKED)

**What read-side enforcement proves.** That governed read paths resolve through publication context, reject recency shortcuts, and refuse raw or invalid storage as a price source.

**What it cannot prove.**

- **That no bypass occurred.** Enforcement lives in application code. A consumer with direct database access, a reporting tool pointed at the schema, or an ad-hoc query reaches the same tables without passing through any of it. This is the same limit the sealed-publication immutability guard carries, and for the same reason.
- **That the absence of violations means the rules were exercised.** No consumer surface is currently exposed. A rule with no traffic has not been tested by traffic; it has merely not been contradicted.
- **That a governed path is a correct path.** Enforcement checks how data is reached, not whether the resolved publication is the one that should be current.

Consequently clean enforcement may be cited as evidence that **governed paths behave**, never as evidence that **all access was governed**.
