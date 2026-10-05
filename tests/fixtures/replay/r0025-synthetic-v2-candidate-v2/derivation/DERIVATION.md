# Derivation record — R0025 synthetic V2 golden fixture, candidate 2

Status: `CANDIDATE_AWAITING_INDEPENDENT_REVIEW`. Not approved, not proof, not a canonical golden.
World: `SYNTHETIC_TEST_WORLD_NOT_A_REAL_INSTRUMENT` (`r0025-synthetic-v2/2`). Test only; never master-data truth.
Supersedes: candidate-1 (`../r0025-synthetic-v2-candidate-v1`, fingerprint `05b717c63ef1f5f96a759ed6d2160b46e4eb9d1c9c946e2a03d373229abf26c0`), which the independent review returned as CHANGES REQUIRED. Candidate-1 is unchanged and never approved.
Authority: `D-MD-B18-A002-011`, `D-MD-B18-A002-013`, `D-MD-B18-A002-014` (owner selections Q4 = A, Q5 = B, Q6 = B, Q7 = A), `E-MD-B18-A002-093`, `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md` section 6A, `Fixture_Package_Manifest_LOCKED.md`, `Golden_Fixtures_Specification.md`, `Replay_Verification_Contract_LOCKED.md` (bound inputs).

## 1. What changed from candidate 1, and why

| Review finding | Candidate 2 |
|---|---|
| IR-1: four of the eleven bound inputs were not asserted | All eleven are asserted as literals: none omitted, none NULL, none a target marker. |
| IR-2: the provider timestamp was NULL in the observation entries | The envelope binds the provider instant `2026-03-23 09:00:00` (frozen unix `1774231200`, platform timezone `Asia/Jakarta`); the acquisition clock `2026-03-25 10:30:00` is a separate field; the canonical bar keeps a NULL source timestamp (owner decision Q6 = B). |
| IR-3: the wrong-fingerprint control could catch its own failure | Fixed in the controls (capture-then-assert helper; exact exception class and reason); not a package concern. |

## 2. Reproduce every expected value

```
php derivation/reference_oracle.php     # rewrites expected/* and derivation/oracle_output.json from inputs/ alone
php derivation/build_package.php        # classification, manifest, package fingerprint
```

The oracle includes no application code, opens no database, reads no run, publication or replay output, and calls no production hash implementation. Its inputs:

| Input | Content |
|---|---|
| `inputs/synthetic_world.json` | the synthetic world declaration: fixed retained roots, listing, one bar and its frozen provider instant, calendar rule, run clock |
| `inputs/provider_response.json` | the exact provider response bytes (223 bytes, no trailing newline) |
| `inputs/frozen_config_content.txt` | the frozen configuration content (unchanged from candidate 1) |
| `inputs/frozen_reason_registry.json` | the frozen governed reason registry: 437 entries with the cross-check against the authority document |
| `inputs/frozen_registry_literals.json` | the two registry literals compiled into the implementation: `registry_contract`, `read_model_version` |
| `inputs/frozen_build_identity.json`, `inputs/frozen_build_manifest.txt` | the frozen executable build identity and its manifest |
| `inputs/foundation_registry.json` | the retained-identity registry the actual side restores (the oracle reads the roots from the world declaration) |

`derivation/author_inputs.php`, `author_registry_inputs.php` and `author_build_identity.php` are AUTHORING TOOLS that produced the frozen inputs once; the oracle never runs them. `author_registry_inputs.php` read the registry table, `author_build_identity.php` scanned the tree once; both are re-runnable by a reviewer to compare with the frozen files.

## 3. The eleven bound inputs

All preimages are in `oracle_output.json`. Algorithm for every hash: SHA-256, lowercase hex. "Composite" below is the replay verifier's composite serialization (keys ordered at every level, lists in the order stated, JSON without escaped slashes or unicode, zero fractions preserved).

| # | Input | Class | Derivation | Literal |
|---|---|---|---|---|
| 1 | `source_observation_manifest_hash` | independently derived semantic hash | observation manifest document over the accepted entry (parent entry = captured entry); both entries bind the provider instant and the acquisition clock | `92ecc9f3fb02adec53093e3836129e7c4713549297e09ffe62f8619fa46bf0b5` |
| 2 | `canonical_raw_input_hash` | independently derived semantic hash | V2 bars domain hash of the one bar row | `6c874753323c4b750b253039444c73e6c4f9a0f0ddff19bbb4828ec07272c415` |
| 3 | `temporal_identity_hash` | independently derived semantic hash | composite of the identity revision set hash | `a9233bcee067ad6f1715494c0bd5b587e49fa69e08b2a1085bc5509dc1b2e51b` |
| 4 | `calendar_status_hash` | independently derived semantic hash | composite of the calendar and status revision set hashes | `2c7dc9f896cee8e1233ac0899d63316b5f2ccfb779cd6553f2fe3fa52e801dcf` |
| 5 | `event_factor_hash` | independently derived semantic hash | composite of the event revision set, source-scale assessment set, factor decision set and factor set hashes and the contamination decision set hash (section 4) | `4b3221309a4d1f6661c6b593920d20feeb5f03d674251aff11d7b04a32fd6f55` |
| 6 | `config_snapshot_hash` | source frozen fact | sha256 of `inputs/frozen_config_content.txt` | `8d0dd54f449b119c4691426612512e152c7dd22c151c358a4acb320f2160a4c0` |
| 7 | `formula_registry_hash` | independently derived semantic hash/version | formula registry document (section 5) | `2d1f53e19c2995ab5f9e0282e898e06a5f755ad4d9248f6c7cdac629e8ace4bf` |
| 8 | `reason_registry_hash` | independently derived semantic hash/version | reason registry document over the frozen entries (section 5) | `0dda9efd27338ad354bca8175aeb2a84d127a259ba5d26dfe84712c33c027a89` |
| 9 | `read_model_version` | source frozen fact | `inputs/frozen_registry_literals.json` | `market_data_read_product_v1` |
| 10 | `serialization_version` | source frozen fact | frozen configuration `governance.config_serialization_version` | `canonical_json_v1` |
| 11 | `executable_build_identity` | source frozen fact (frozen build, owner decision Q5 = B) | `inputs/frozen_build_identity.json` `build_id` | `sha256:d7e008b973f400189259fbe27ddbc273c769a0328827bec877fb3899cbb16c16` |

Other literals: indicators `45a03750e321889251c94e59b56e2032e54becf5d94a5dda2560e53c0c4e85ec`, eligibility `42722d5ddeaa78896b81921e25d1c7d8d72de1e914a7a1ee24f8ae5ff2bf9269`, factor set `f76a54ae5251685f2bcff60bce22fc66f250408350583b86c57bde953baf74e0`.

## 4. event_factor_hash (owner decision Q7 = A)

The V2 identity is the composite of five members: the four semantic nested sets and the contamination decision set. The earlier fifth member, the whole ancillary capture group, carried local ids (ticker ids, listing ids, run ids, publication ids, row ids) and is not part of the V2 identity. The contamination decision set is `{schema_version: market-data-contamination-decision-set/v2, contamination: [...], price_scale_breaks: [...]}`; each list holds one group per retained listing root with the decisions of that listing, semantic fields only (contamination: action type, verification state, ex date, action date, anchor state, depth, price and volume continuity flags, unmapped-type flag, factor hold reason; price-scale break: break type, trade date, depth, implied and inferred ratio, match status, matched action type, continuity verdict). `corporate_action_revision_id` and `candidate_uid` are row identities and are excluded by name; a field outside the lists makes the identity unavailable. The frozen world has no corporate action, no factor and no price-scale break: both lists are empty and the literal of that empty state is `b61b39fa2641038c54740d92f862fe82ea2c4d3c5387ed99d1a685e8048f94b6`.

Interpretation to review: the identity does not contain `event_risk_contexts` (derived from event and status revisions that the event revision set and the status revision set already bind, and carrying revision ids in its reason strings) and does not contain the market-structure set (no authority text puts it in the event/factor domain).

## 5. Registry identities (owner decision Q4 = A)

* `formula_registry_hash` = composite of `schema_version = market-data-formula-registry/v2`, `registry_contract`, `semantic_versions` (the frozen configuration's `semantic_bindings`), `indicator_set_version`, `coverage_contract_version`, `eligibility_contract_version`, `config_registry_revision`, `config_resolver_version`, `serialization_version`, `read_model_version`. No build, no implementation file hash, no reason entry.
* `reason_registry_hash` = composite of `schema_version = market-data-reason-registry/v2` and `entries`, the 437 frozen entries ordered by code (bytewise), each `{code, category, description, severity, is_active}`.
* Disclosure: 24 entries carry description text that differs from `Reason_Codes_Registry.md` (the persisted text is older seed wording; codes, categories and severities agree for all 437). The identity binds the persisted text; rule 3 of the registry allows description clarification, so this is disclosed, not hidden. See `inputs/frozen_reason_registry.json` `authority_crosscheck`.

## 6. Executable build identity (owner decision Q5 = B)

Method `php_source_build_v1` (the governed `ProducerRegistrySnapshot::build`): SHA-256 of the canonical JSON of every `*.php` file of app, config and bootstrap, every file of vendor and the composer files, base64-encoded and ordered by path. Frozen: `sha256:d7e008b973f400189259fbe27ddbc273c769a0328827bec877fb3899cbb16c16` over 5915 files (`inputs/frozen_build_manifest.txt`, sha256 `70c421ab3d374d1155a281785ac679a3a6886030a42479b86aa808e52ed01143`). The retained archive and a reviewer's verification steps are in `inputs/frozen_build_identity.json`. The oracle consumes the frozen literal and checks only the internal consistency of the frozen input. This candidate is valid only for this build: any change to a file of the build requires a new candidate version.

## 7. Observation timestamp (owner decision Q6 = B)

The provider response is a chart series with one instant per bar (`1774231200` = `2026-03-23 09:00:00` Asia/Jakarta) and no response-level observation time. For a request of one trade date the oracle takes the series element whose exchange-local date is the trade date; it is the envelope's source timestamp when exactly one element matches, rendered in the platform timezone. Range requests have several observed dates and no single instant (NULL; the instants stay bound through the payload hash). The acquisition clock stays in `acquired_at`. The bar's source timestamp is NULL.

## 8. Reviewer points

* Transcription points of candidate 1 still apply (column order, nested document members, vocabulary labels): see candidate 1's record.
* New transcription points: the formula member set, the reason entry member set and ordering, the contamination whitelist, the exclusion of `event_risk_contexts` and market structure from the event/factor identity, the single-date rule for the envelope timestamp.
* The frozen configuration content and the frozen reason registry are snapshots of the platform's own sources, not independently authored values; drift changes the corresponding identity and fails the candidate, which is intended.

## 9. Independence statement

The oracle is a separate implementation of the profile and of the candidate-2 identities. It does not call, include or read the output of the services that produce the value under test. The author had read the production services while transcribing the member lists above. The oracle output was produced from the frozen inputs alone; the comparison with a production run was made afterwards, and no production hash was used as an input.
