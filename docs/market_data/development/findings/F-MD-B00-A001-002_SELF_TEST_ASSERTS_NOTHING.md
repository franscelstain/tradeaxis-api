# F-MD-B00-A001-002 — Relationship integrity gate self-test reports PASS without executing anything

- Status: `OPEN`
- Severity: `P1`
- Stage / Attempt / Baseline / Epoch: `MD-B00` / `MD-B00-A001` / `MD-B00-A001-BL001` / `MD-REBASELINE-20260820-001`
- Owning stage for remediation: `MD-B00`
- Artifact: `development/implementation/tests/MarketDataRelationshipIntegrityGateSelfTest.php`

## Finding

The file is three lines. It holds an array of nine invariant descriptions, loops over it, prints `PASS <description>` for each, then prints `PASS 9/9 relationship invariants specified`. It never loads the gate, never constructs a violating fixture, and never asserts anything. Its exit code is always 0.

An operator reading its output sees nine green lines and concludes the relationship invariants are proven. Nothing was proven. The word `specified` in the final line is the only hint, and it is doing more work than any reader will give it.

This is the same failure shape recorded historically for the reason-code seed guard, where a text-parsing check passed for an extended period while `MarketDataReasonCodesSeeder` failed on every execution because the SQL carried a trailing comma. That defect was found only when `ReasonCodeSeedExecutionTest` was written to execute the statement instead of reading it. The lesson did not propagate to the governance tooling.

## Verified counter-evidence

`MD-B00-A001` executed the real gate against eight deliberate mutations on an isolated copy of the tree. The gate itself is sound and fails closed on seven of them, including the three relationship invariants the self-test claims to cover:

| Mutation | Gate | Result |
|---|---|---|
| `work_id` differs from `attempt_id` | relationship | `FAIL` — work identity mismatch |
| malformed attempt ID shape | relationship | `FAIL` — bad attempt |
| relationship references a non-existent record | relationship | `FAIL` — unresolved relationship |
| unregistered physical document added | documentation | `FAIL` — role + verification registry |
| frozen strategy byte mutated | documentation | `FAIL` — strategy freeze |
| legacy evidence flipped to `current_proof_eligible=YES` | documentation | `FAIL` — verification rebaseline |
| legacy split extract body tampered | documentation | `FAIL` — body hash and reconstruction hash |
| text appended after extract body-end marker | documentation | `PASS` — see `F-MD-B00-A001-003` |

So the gate deserves current PASS and the self-test does not. The self-test adds no assurance the gate does not already provide, and subtracts assurance by looking like proof.

## Required outcome

Either delete the file, or replace its body with the mutation matrix above executed against a temporary copy of the tree, asserting a non-zero exit per mutation and a zero exit for the unmutated control. A self-test that cannot fail is worse than no self-test, because it consumes the reviewer's attention budget while returning nothing.

Until one of those happens, no closure manifest may cite `MarketDataRelationshipIntegrityGateSelfTest.php` as evidence.
