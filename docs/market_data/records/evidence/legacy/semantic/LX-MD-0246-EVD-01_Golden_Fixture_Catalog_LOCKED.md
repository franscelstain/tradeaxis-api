# Legacy Semantic Extract — LX-MD-0246-EVD-01

- Source ID: `LS-MD-0246`
- Original path: `tests/Golden_Fixture_Catalog_LOCKED.md`
- Original SHA1: `A8B066831AF122E436D973046A48DE4166C5D642`
- Extract role: `EVIDENCE`
- Source range: `L26-L38`
- Extract body SHA1: `D16B43D28DFC24C94D9DB9A68A9E90403994AAC5`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Artifact existence status (LOCKED)

This catalog specifies fixtures. It does not create them, and a specified fixture is not a built one.

**Current state:** no golden fixture artifacts exist in the repository. A search across the project outside `docs/` returns only a replay-fixture generator command, two seeding helpers, and an ops runtime-matrix manifest in storage — none of which is a golden indicator fixture, expected-output oracle, or test vector set.

Consequences that bind until artifacts exist:

- Every acceptance criterion depending on a golden fixture is **unmet**, not partially met. Thoroughness of this catalog does not substitute for the artifacts it describes.
- The long-chain deterministic calculation required by the indicator owner contract has **no executing test**. No test in the suite exercises a Wilder ATR chain against an external reference, so that criterion is unproven rather than proven.
- A conformance claim may not cite this catalog as coverage. It may cite it as a plan.

Closing this requires building the artifacts, naming their location here, and adding tests that consume them. Extending the specification does not move it closer to closed.

<!-- LEGACY_EXTRACT_BODY_END -->
