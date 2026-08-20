# Legacy Semantic Extract — LX-MD-0174-CTX-01

- Source ID: `LS-MD-0174`
- Original path: `ops/COMMAND_SURFACE_SAFETY_INVENTORY.md`
- Original SHA1: `4A1D5DF36286F6499A44A9A6E49E45976F3253D1`
- Extract role: `CONTEXT`
- Source range: `L57-L63`
- Extract body SHA1: `B203E091006669E3523B02A9D044ABE7EE07AD17`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Final policy

Destructive command behavior is allowed only for explicitly eligible non-authoritative retention artifacts or atomic publication-pointer lifecycle actions. No dry-run/apply/force/reason guard can authorize in-place observation, canonical, factor, snapshot, manifest, seal, or published-history mutation. Price repair apply and price-derived verification apply must be removed/disabled. Force-like promotion remains default-off and cannot bypass candidate completeness, lineage, config, hashes, seal, or coherent-product gates. Invalid input returns `BLOCKED` with registered reasons.

Commands with logically required arguments may expose those arguments as optional at the Symfony parser layer only when that is required to produce command-owned `status=BLOCKED` and `reason_code=COMMAND_MISSING_REQUIRED_INPUT` output. The operator contract remains required-input: docs/runbooks must continue to name the required values, and the command must not fall through to a raw framework missing-argument error.



<!-- LEGACY_EXTRACT_BODY_END -->
