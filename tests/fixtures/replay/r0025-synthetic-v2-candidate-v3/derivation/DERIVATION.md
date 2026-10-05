# Derivation record — R0025 synthetic V2 golden fixture, candidate 3

Status: `CANDIDATE_AWAITING_INDEPENDENT_REVIEW`. Not approved, not proof, not a canonical golden.
World: `SYNTHETIC_TEST_WORLD_NOT_A_REAL_INSTRUMENT` (`r0025-synthetic-v2/3`). Test only; never master-data truth.
Supersedes: candidate-2 (`../r0025-synthetic-v2-candidate-v2`, fingerprint `bbd8c73953eb1397a49ba651e8b66b5b6cf914dd0790142d2a706bb8a49edb9f`) and candidate-1 (`../r0025-synthetic-v2-candidate-v1`, fingerprint `05b717c63ef1f5f96a759ed6d2160b46e4eb9d1c9c946e2a03d373229abf26c0`). Both were reviewed CHANGES REQUIRED, are unchanged and were never approved.
Authority: `D-MD-B18-A002-011`, `D-MD-B18-A002-013`, `D-MD-B18-A002-014` (owner selections Q4 = A, Q5 = B, Q6 = B, Q7 = A), `E-MD-B18-A002-095` (the review of candidate-2), `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md` section 6A, `Fixture_Package_Manifest_LOCKED.md`, `Golden_Fixtures_Specification.md`, `Replay_Verification_Contract_LOCKED.md`, `Publication_Manifest_Contract_LOCKED.md`.

## 1. What changed from candidate 2, and why

Candidate 3 corrects exactly the two blocking findings of the independent review of candidate 2 and keeps every other semantic of candidate 2 (all eleven bound inputs, the synthetic V2 world, the Q4 registry identities, the Q5 frozen build literal, the Q6 provider and acquisition timestamps, the Q7 contamination projection). The frozen inputs other than the build identity are byte-identical to candidate 2.

| Review finding | Candidate 3 |
|---|---|
| `assertion_layers` outside the locked vocabulary (`F-MD-B18-A002-030`) | The manifest declares `run, hash, publication, replay`, all locked values (`row`, `run`, `hash`, `publication`, `replay`). The package asserts run-level state and counts (`run`), content hashes and identities (`hash`), publication, pointer, seal, fallback and lineage state with the manifest hash (`publication`) and the replay result (`replay`); it asserts no row-level value directly, so `row` is not declared. No semantics were changed to fit a name. |
| `publication_manifest_hash` exact coverage not established (`F-MD-B18-A002-031`) | The oracle derives the frozen publication manifest hash (section 4) and `expected_publication_context.publication_manifest_hash` carries it as a literal. The verifier compares it with the manifest hash of the target, which it reports only when the repository can re-derive it from the target's rows; a package that omits it is refused. |

The executable build changed because the verifier changed (a new check), so the frozen build identity and manifest are new (section 6).

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
| `inputs/frozen_config_content.txt` | the frozen configuration content (unchanged from candidate 2) |
| `inputs/frozen_reason_registry.json` | the frozen governed reason registry: 437 entries with the cross-check against the authority document |
| `inputs/frozen_registry_literals.json` | the two registry literals compiled into the implementation: `registry_contract`, `read_model_version` |
| `inputs/frozen_build_identity.json`, `inputs/frozen_build_manifest.txt` | the frozen executable build identity and its manifest |
| `inputs/foundation_registry.json` | the retained-identity registry the actual side restores (the oracle reads the roots from the world declaration) |

`derivation/author_inputs.php`, `author_registry_inputs.php` and `author_build_identity.php` are AUTHORING TOOLS that produced the frozen inputs once; the oracle never runs them.

## 3. The eleven bound inputs

All preimages are in `oracle_output.json`. Algorithm for every hash: SHA-256, lowercase hex. "Composite" is the replay verifier's composite serialization (keys ordered at every level, lists in the order stated, JSON without escaped slashes or unicode, zero fractions preserved).

| # | Input | Class | Derivation | Literal |
|---|---|---|---|---|
| 1 | `source_observation_manifest_hash` | independently derived semantic hash | observation manifest document over the accepted entry (parent entry = captured entry); both entries bind the provider instant and the acquisition clock | `92ecc9f3fb02adec53093e3836129e7c4713549297e09ffe62f8619fa46bf0b5` |
| 2 | `canonical_raw_input_hash` | independently derived semantic hash | V2 bars domain hash of the one bar row | `6c874753323c4b750b253039444c73e6c4f9a0f0ddff19bbb4828ec07272c415` |
| 3 | `temporal_identity_hash` | independently derived semantic hash | composite of the identity revision set hash | `a9233bcee067ad6f1715494c0bd5b587e49fa69e08b2a1085bc5509dc1b2e51b` |
| 4 | `calendar_status_hash` | independently derived semantic hash | composite of the calendar and status revision set hashes | `2c7dc9f896cee8e1233ac0899d63316b5f2ccfb779cd6553f2fe3fa52e801dcf` |
| 5 | `event_factor_hash` | independently derived semantic hash | composite of the four semantic nested members and the contamination decision set hash (section 5) | `4b3221309a4d1f6661c6b593920d20feeb5f03d674251aff11d7b04a32fd6f55` |
| 6 | `config_snapshot_hash` | source frozen fact | sha256 of `inputs/frozen_config_content.txt` | `8d0dd54f449b119c4691426612512e152c7dd22c151c358a4acb320f2160a4c0` |
| 7 | `formula_registry_hash` | independently derived semantic hash/version | formula registry document (section 5) | `2d1f53e19c2995ab5f9e0282e898e06a5f755ad4d9248f6c7cdac629e8ace4bf` |
| 8 | `reason_registry_hash` | independently derived semantic hash/version | reason registry document over the frozen entries (section 5) | `0dda9efd27338ad354bca8175aeb2a84d127a259ba5d26dfe84712c33c027a89` |
| 9 | `read_model_version` | source frozen fact | `inputs/frozen_registry_literals.json` | `market_data_read_product_v1` |
| 10 | `serialization_version` | source frozen fact | frozen configuration `governance.config_serialization_version` | `canonical_json_v1` |
| 11 | `executable_build_identity` | source frozen fact (frozen build, owner decision Q5 = B) | `inputs/frozen_build_identity.json` `build_id` | `sha256:aa4aa7d1e9b1c4c15fe58d2d172b395af630679bce834633c2758cda91dc1e22` |

Other literals: indicators `45a03750e321889251c94e59b56e2032e54becf5d94a5dda2560e53c0c4e85ec`, eligibility `42722d5ddeaa78896b81921e25d1c7d8d72de1e914a7a1ee24f8ae5ff2bf9269`, factor set `f76a54ae5251685f2bcff60bce22fc66f250408350583b86c57bde953baf74e0`.

## 4. publication_manifest_hash (review finding 2)

Requirement: `Replay_Verification_Contract_LOCKED.md` makes the manifest assertion part of `PASS`; `Publication_Manifest_Contract_LOCKED.md` makes the manifest the identity object of one publication. Literal: `56e44a75683bf3a1734c333c10855be3f3b123d6ba5ac2c1e192cb537a11c2f2`.

Derivation (oracle section 4e, full payload and preimage in `oracle_output.json` `publication_manifest`): the V2 manifest is the canonical document `{semantic_profile: market-data-publication-semantic/v2, domain: publication_manifest, payload}` hashed with SHA-256. The payload is the manifest-shaped object of the publication without any operational id (no run, publication, correction, configuration-snapshot, factor-set or observation id; those are excluded from the semantic identity). Its members and where each comes from:

| Member | Source |
|---|---|
| `market_scope` `IDX_REGULAR_EOD` | the manifest contract and fixture manifest spec |
| `trade_date`, `trade_date_requested`, `trade_date_effective` | the frozen world's trade date |
| `publication_version` `"1"`; `lineage` three NULL manifest hashes | the first and only publication of the trade date, no supersession, no previous or replaced publication |
| `artifact_hash_profile`, `nested_identity_version` | the V2 profile literals (vocabulary) |
| `observation_manifest_hash`, the seven nested set hashes, `factor_set_hash` | derived by the oracle in section 2 of its output |
| `config_content_hash`, `config_registry_revision` | the frozen configuration |
| `semantic_reasons` `["COVERAGE_THRESHOLD_MET"]` | the publication-scope reason set: the coverage reason, which agrees with the frozen coverage state `PASS` |
| `price_product_code`, `price_product_version`, `canonicalization_version`, `formula_version`, `read_model_version` | the frozen scenario, the bars and indicators rows, the frozen configuration and the registry literals |
| `artifacts` (bars, indicators, eligibility) | the three artifact hashes derived by the oracle |
| `row_counts` 1, 1, 1 | one bar, one indicator row, one eligibility row |
| `quality_state` `PASS`, `coverage_gate_state` `PASS`, `coverage_ratio` `1.0000` | the frozen coverage outcome; coverage ratio has 4 decimal places (`Hash_Number_Formatting_LOCKED.md`) |
| `readiness_state` `READABLE`, `freshness_state` `NOT_AVAILABLE` | the frozen expected run state; the freshness label is the one the eligibility row already carries |
| `correction_lineage` (no correction identity, no promote mode, no publish target) | the run is not a correction |
| `seal_contract_version` `dataset_seal_v2`, `seal_hash_algorithm` `SHA-256` | the V2 seal contract label (vocabulary) and the Audit contract |

Vocabulary points for the reviewer (no authority text defines them; the package fixes which label applies to the frozen scenario): `quality_state` = `PASS`, `freshness_state` = `NOT_AVAILABLE`, `seal_contract_version` = `dataset_seal_v2`, `nested_identity_version`.

Comparison: `ReplayVerificationService` compares `expected_publication_context.publication_manifest_hash` with the manifest hash of the target publication. The target value is reported only when `EodPublicationRepository::assertPublicationManifestHashValid` re-derives the stored hash from the target's own rows; otherwise it is empty and the comparison fails. An independent package that omits the literal, or declares it malformed or target-bound, is refused.

## 5. event_factor_hash, formula and reason registry identities (unchanged from candidate 2)

* `event_factor_hash` (Q7 = A): composite of the four semantic nested members and the contamination decision set hash. The contamination decision set is `{schema_version: market-data-contamination-decision-set/v2, contamination, price_scale_breaks}` (semantic fields only, grouped by retained listing root; `corporate_action_revision_id` and `candidate_uid` excluded by name). The frozen world has no corporate action, factor or price-scale break: both lists are empty, literal `b61b39fa2641038c54740d92f862fe82ea2c4d3c5387ed99d1a685e8048f94b6`. The identity does not contain `event_risk_contexts` or the market-structure set (interpretation recorded for review).
* `formula_registry_hash` (Q4 = A) = composite of `schema_version = market-data-formula-registry/v2`, `registry_contract`, `semantic_versions`, `indicator_set_version`, `coverage_contract_version`, `eligibility_contract_version`, `config_registry_revision`, `config_resolver_version`, `serialization_version`, `read_model_version`. No build, no implementation file hash, no reason entry.
* `reason_registry_hash` (Q4 = A) = composite of `schema_version = market-data-reason-registry/v2` and the 437 frozen entries ordered by code, each `{code, category, description, severity, is_active}`. Disclosure: 24 persisted descriptions differ from `Reason_Codes_Registry.md`; codes, categories and severities agree for all 437.

## 6. Executable build identity (owner decision Q5 = B)

Method `php_source_build_v1` (the governed `ProducerRegistrySnapshot::build`): SHA-256 of the canonical JSON of every `*.php` file of app, config and bootstrap, every file of vendor and the composer files, base64-encoded and ordered by path. Frozen: `sha256:aa4aa7d1e9b1c4c15fe58d2d172b395af630679bce834633c2758cda91dc1e22` over 5915 files (`inputs/frozen_build_manifest.txt`, sha256 `95bd9b10fd306312a37b9a9ba1b5275e10bcf630b6d46a4261a61bfc472e80b7`). Candidate 3 is frozen again because the verifier changed. The oracle consumes the frozen literal and checks only the internal consistency of the frozen input. This candidate is valid only for this build.

## 7. Observation timestamp (owner decision Q6 = B)

The provider response is a chart series with one instant per bar (`1774231200` = `2026-03-23 09:00:00` Asia/Jakarta) and no response-level observation time. For a request of one trade date the oracle takes the series element whose exchange-local date is the trade date; it is the envelope's source timestamp when exactly one element matches, rendered in the platform timezone. The acquisition clock `2026-03-25 10:30:00` stays in `acquired_at`. The bar's source timestamp is NULL.

## 8. Reviewer points

* Transcription points of candidates 1 and 2 still apply (column order, nested document members, vocabulary labels, formula and reason member sets, the contamination whitelist, the single-date timestamp rule).
* New: the publication manifest payload members and the four vocabulary labels in section 4; the declared assertion layers.
* The frozen configuration content and the frozen reason registry are snapshots of the platform's own sources, not independently authored values; drift changes the corresponding identity and fails the candidate, which is intended.

## 9. Independence statement

The oracle is a separate implementation. It does not call, include or read the output of the services that produce the value under test, and it reads no run, publication or replay. The author had read the production services while transcribing the member lists above. The oracle output was produced from the frozen inputs alone; the comparison with a production run was made afterwards, and no production hash (including the stored publication manifest hash) was used as an input.
