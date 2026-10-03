# Finding — the candidate-v1 wrong-fingerprint control could catch its own failure

- ID: `F-MD-B18-A002-029`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-10-03T08:45:44+07:00
- Severity: `P3` — a control that could not fail; no production effect
- Status: `RESOLVED` — fixed and proven in the same unit (`E-MD-B18-A002-093`)
- Class: `PROOF_BASIS_VACUOUS_OR_MISTARGETED`
- Related: `E-MD-B18-A002-093`, `E-MD-B18-A002-092`, `F-MD-B18-A002-025`

## Observed

`R0025SyntheticV2CandidateFixtureTest::test_the_synthetic_retained_identity_resolves_only_through_the_foundation_restore_path` ended with `try { restore($path, str_repeat('0', 64)); $this->fail(...); } catch (\Throwable $e) { assertNotSame('', $e->getMessage()); }`. `PHPUnit\Framework\AssertionFailedError` is a `Throwable`, so when the production rejection is absent `fail()` throws, the `catch` takes it, and its non-empty message satisfies the assertion. Probe: with the production check in `FrozenSourcePackageReader::verifiedBytes` disabled, the control as found stays green (`probes/P1_old_structure_under_mutation.txt`, `OK (1 test, 2 assertions)`).

Four more sites used the same try/fail/catch shape with narrower catches (`\RuntimeException`, which also covers PHPUnit's own failure). Their message assertions would have turned a swallowed failure red, so they were not vacuous, but they depended on that.

## Resolution

A helper (`thrownBy`) wraps only the production call and returns what it threw; every assertion is made outside it. The control now asserts that something was thrown, that it is a `\DomainException`, and that its message is exactly `REGISTRY_FINGERPRINT_MISMATCH`; a malformed fingerprint is asserted to give `REGISTRY_APPROVED_FINGERPRINT_REQUIRED`. The other four sites use the same structure. Probe P1 (before `1b66b902…ad6c4`, mutated `cbddbad0…148b1`, restored `1b66b902…ad6c4`, byte-identical): the new control is red at `a wrong fingerprint was accepted` and green after restore. Candidate controls: 15 tests, 152 assertions, green.
