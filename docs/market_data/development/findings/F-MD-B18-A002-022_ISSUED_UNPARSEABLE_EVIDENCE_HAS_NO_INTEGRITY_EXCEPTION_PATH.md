# Finding — an issued, immutable evidence record that does not parse has no governed path through the documentation integrity gate

- ID: `F-MD-B18-A002-022`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-09-23T16:22:40+07:00
- Severity: `P1` for closure — the documentation integrity gate cannot pass while the artifact exists, and no governed mechanism existed to reconcile the two rules
- Status: `RESOLVED` (`DOC-CHG-20260923-001`, `E-MD-B18-A002-064`)
- Class: `GOVERNANCE_AUTHORITY_CONFLICT`
- Origin: `E-MD-B18-A002-063`, unresolved item `E061-U1`
- Related: `F-MD-B18-A002-016` (G08 produced the artifact); `CI-MD-B18-A002-001`

## Defect

`E-MD-B18-A002-061` was issued in commit `0caaf38` with a `pattern` field that is not valid JSON.
Two governance rules then conflict for that one file:

- `DOCUMENT_CHANGE_POLICY.md` §3 and `DOCUMENT_RECORDING_STANDARD.md` §1: evidence is
  `IMMUTABLE_AFTER_ISSUE`; corrections create new evidence and never rewrite the original.
- `DOCUMENT_INTEGRITY_GATE_STANDARD.md`: the gate must verify parseable JSON.
  `MarketDataDocumentationIntegrityGate.php` decodes every `.json` file and fails `JSON_PARSE` on any
  error, with no exception path.

`DOCUMENT_INTEGRITY_EXCEPTION_REGISTRY.json` exists (`MUTABLE_TRACEABLE`, `{"exceptions": []}` since
creation), but no standard defines its semantics and the gate never reads it.

`E-MD-B18-A002-063` corrects E061 through new evidence, as policy requires, but a correction record
cannot make the original parse. The gate therefore stays red on E061 alone, and with it
`GovernanceGateReadOnlyExecutionTest`'s documentation-pass case and the relationship self-test's two
documentation-gate controls.

## Why neither obvious shortcut is admissible

- Editing E061 to make it parse violates `IMMUTABLE_AFTER_ISSUE`; that edit was made in the working
  tree and reverted.
- Treating E061 as never issued would rewrite history: it is committed, registered, and it is the
  record behind `MD-S050-R0046`'s promotion.

## Remedy

A governance controlled revision (`DOCUMENT_CHANGE_POLICY.md` §3, §5) authorised by the owner
decision recorded as `D-MD-B18-A002-007`: define the exception registry in
`DOCUMENT_INTEGRITY_GATE_STANDARD.md`, restricted to issued immutable evidence whose original bytes
are retained and whose structural defect is identified by an issued correction; validate the
registry fail-closed in the gate; keep every unexcepted malformed file a hard `FAIL`; prove each
rejection path with a mutation.

## Resolution — 2026-09-23

Resolved by controlled revision `DOC-CHG-20260923-001` under `D-MD-B18-A002-007`, proven in
`E-MD-B18-A002-064`:

- `DOCUMENT_INTEGRITY_GATE_STANDARD.md` gains an additive section defining the exception registry
  as the only admission path, with eligibility, registry contract and fail-closed gate behaviour.
  Only `JSON_PARSE` may be excepted.
- `DOCUMENT_INTEGRITY_EXCEPTION_REGISTRY.json` holds one `ACTIVE` entry, `MD-DOCEX-0001`, binding
  E061 at sha256 `3ad2e42e…8576` to correction `E-MD-B18-A002-063`, defect `E061-D1`.
- `MarketDataDocumentationIntegrityGate` validates the registry as its own check
  (`INTEGRITY_EXCEPTION_REGISTRY`) and admits a `JSON_PARSE` failure only through a valid entry for
  that exact path. E061's raw failure is still reported.
- The self-test gains 15 probes, each required to fail exactly the checks it names. Weakening the
  gate on a scratch copy showed the suite can fail. One weakening (applying exceptions from an
  invalid registry) first escaped, because every probe used a single entry; two whole-registry
  probes were added and now catch it.

E061 is unchanged and is not declared unissued.
