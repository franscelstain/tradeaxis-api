# Finding — the frozen publication manifest hash is not verified exactly and independently by the exact-publication path

- ID: `F-MD-B18-A002-031`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-10-04T23:42:39+07:00
- Severity: `P1` for `MD-S003-R0025`; the replay contract's PASS rule names the manifest
- Status: `OPEN` — coverage established in candidate-v3 (`E-MD-B18-A002-096`, mechanics reviewed PASS) and preserved in candidate-v4 (`E-MD-B18-A002-099`); closes when a corrected package passes independent review
- Class: `PROOF_BASIS_STRUCTURAL_AND_EXECUTABLE_GAP`
- Related: `E-MD-B18-A002-095`, `E-MD-B18-A002-094`, `F-MD-B18-A002-025`, `F-MD-B18-A002-017`, `D-MD-B18-A002-011`
- Remediation owner: `MD-B18-A002`

## Observed

`Replay_Verification_Contract_LOCKED.md` ("Result and evidence"): `PASS` requires that "all expected values, null reasons, states, lineages, content hashes, manifest, and seal assertions match". `Publication_Manifest_Contract_LOCKED.md` makes `publication_manifest_hash` the identity of one publication. The independent review of candidate-v2 could not establish that the frozen `publication_manifest_hash` is checked exactly and independently. Tracing requirement, implementation, executing comparison and discriminating probe shows why:

| Link | State in candidate-v2 and the unchanged exact-publication path |
|---|---|
| Independently derived expected value | Absent. Neither the oracle nor `expected_replay_result.json` carries a publication manifest hash. The oracle derives the manifest's inputs (nested identities, artifact hashes) but not the manifest. |
| Executing comparison against the target | Absent. `ReplayVerificationService` never reads `publication_manifest_hash`; `actual_publication_context` has no such field and `compareExpectedAndActual` has no such comparison. |
| Unchanged exact-publication family (`B18ScenarioFamiliesOnMariaDbTest`, `s_f1_frozen_inputs_and_seal`) | It calls `EodPublicationRepository::assertPublicationManifestHashValid`, which recomputes the hash from the database rows with the production serializer and compares it with the stored hash: a self-consistency check of the producer, not an independent expectation. The same scenario then verifies a fixture generated from the run under test, which `replayAdmissibility` refuses as self-generated (the governed expected failure). |
| Sensitivity probe | The family damages one artifact hash and expects `assertPublicationManifestHashValid` to refuse. That shows the self-recompute notices damage; it does not show that a wrong manifest content is detected against an independent value. |

Classification: coverage was **incomplete**, not merely unproven.

## Closure

Resolved when a candidate package carries an independently derived expected `publication_manifest_hash`, the verifier compares it with the actual publication manifest hash of the target (fail closed when the manifest cannot be verified), a package that omits it is refused, and a probe that changes a manifest input turns the comparison red (`E-MD-B18-A002-096`), and the corrected package has passed independent review.

## 2026-10-05 coverage established — E-MD-B18-A002-096

Case B was confirmed: coverage was incomplete. Candidate-v3 carries `expected_publication_context.publication_manifest_hash` `56e44a75683bf3a1734c333c10855be3f3b123d6ba5ac2c1e192cb537a11c2f2`, derived by the standalone oracle (section 4e, payload and preimage in `oracle_output.json` and `expected/expected_publication_manifest.json`) from the frozen inputs and values the oracle derived; no production value was an input. The verifier compares it with the manifest hash of the target publication, reported only when `assertPublicationManifestHashValid` re-derives it from the target's rows (empty otherwise); an independent package that omits, empties, mis-cases, shortens or target-binds the literal is refused. Proof: a wrong literal causes exactly the manifest mismatch; damage to a manifest input of the run or to the stored hash after the seal gives an empty actual value and a mismatch; each input that authority makes a manifest member moves the expected hash; the manifest hash is equal across two allocation layouts; probes M1 to M6 (including the manifest compared with itself) are red. The finding stays `OPEN` until candidate-v3 passes independent review.
