# Derivation record — R0025 synthetic V2 golden fixture, candidate 4

Status: `CANDIDATE_AWAITING_INDEPENDENT_REVIEW`. Not approved, not proof, not a canonical golden.
World: `SYNTHETIC_TEST_WORLD_NOT_A_REAL_INSTRUMENT` (`r0025-synthetic-v2/4`), explicitly **PRE-ACTIVATION**. Test only; never master-data truth.
Supersedes: candidate-3 (`../r0025-synthetic-v2-candidate-v3`, fingerprint `8a218f5befc0b1c6f278d20ee86a798ca29185ad4522e8b8a00ee069cec9b85e`), candidate-2 (`bbd8c739…edb9f`) and candidate-1 (`05b717c6…26c0`). All three were reviewed CHANGES REQUIRED, are unchanged and were never approved.
Authority: `D-MD-B18-A002-011`, `D-MD-B18-A002-013`, `D-MD-B18-A002-014` (Q4 = A, Q5 = B, Q6 = B, Q7 = A), `D-MD-B18-A002-015` (owner Direction D) with the controlled correction `DOC-CHG-20261005-001` (`Downstream_Data_Readiness_Guarantee_LOCKED.md`, section "Readability and operational freshness are independent"), `E-MD-B18-A002-097` (the review of candidate-3), `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md` section 6A, `Fixture_Package_Manifest_LOCKED.md`, `Golden_Fixtures_Specification.md`, `Replay_Verification_Contract_LOCKED.md`, `Publication_Manifest_Contract_LOCKED.md`.

## 1. What changed from candidate 3, and why

The independent review of candidate 3 passed its mechanics and found one blocker: `freshness_state = NOT_AVAILABLE` was inherited from production fallback normalisation, not derived from authority and frozen facts. Authority then did not determine the state (owner decision `D-MD-B18-A002-015`); the strategy was corrected (`DOC-CHG-20261005-001`) and the producers were made to conform (`MD-B10-A003`).

| Review finding / point | Candidate 4 |
|---|---|
| `freshness_state` not independently justified | The world freezes its **activation context** and **publication facts** (section 2). The oracle derives the state with its own implementation of the authority's ordered table: `NOT_APPLICABLE`. It carries that value into the eligibility row and the manifest member. |
| `canonicalization_version` hard-coded in the oracle (non-blocking) | Read from the frozen configuration, `resolved_config.source.canonicalization_version` (`idx_regular_raw_v2`), for the bars row and the manifest. |
| `publication_version` representation undeclared (non-blocking) | Declared as a frozen input with its governed-producer source (section 5); the oracle reads it and checks it against the frozen build manifest. |
| E096 / DERIVATION said "all other frozen inputs byte-identical" while `synthetic_world.json` had changed | Stated exactly (section 7): which inputs are byte-identical to candidate 3 and which differ. |

Preserved unchanged (reviewed PASS): the locked assertion-layer vocabulary (`run, hash, publication, replay`; no `row`), the 35-member manifest inventory, the independent canonicalisation, the mandatory literal `publication_manifest_hash`, the actual-side manifest re-verification, allocation independence, V1, the eleven bound-input derivations, and the package/fingerprint discipline. The frozen build is new (section 8).

## 2. The frozen pre-activation world and the independent derivation of `NOT_APPLICABLE`

Frozen in `inputs/synthetic_world.json` (declarations, read from no run and no publication):

| Fact | Value | Why |
|---|---|---|
| `trade_date` (requested date) | `2026-03-23` | the one frozen bar |
| `operational_activation.operational_start_date` | `null`; `marker_state` `NO_MARKER_EFFECTIVE` | a development world: no explicit governed marker is effective. The frozen configuration carries `scope.operational_start_date = null`; the oracle **refuses** the inputs if the two disagree. |
| `publication_facts.requested_publication_is_returned` | `true` | the active sealed publication for the requested date |
| `publication_facts.all_readable_conditions_hold` | `true` | the bounded replay purpose: the publication is `READABLE` |
| `publication_facts.prior_date_fallback_returned` | `false` | the requested date is served, not a prior one |
| `publication_facts.activated_degraded_condition_declared` | `false` | none is declared by the world or by `DOC-CHG-20261005-001` |
| `publication_facts.activated_operational_freshness_gates_pass` | `null` | not applicable: operational freshness is not in force, so no gate is assessed |

Authority applied (the oracle's own implementation of the text, `reference_oracle.php` section 0): operational freshness is in force for a requested trade date only when an explicit governed marker is effective on or before that date; it is never backdated; it is decided from the requested date and the marker alone. The first row of the ordered table that applies decides the state:

| Row | Condition | State |
|---|---|---|
| 1 | no readable publication is returned and no allowed fallback applies | `NOT_AVAILABLE` |
| 2 | an allowed prior-date fallback is returned | `STALE` or `DEGRADED` (the oracle refuses: no such facts are declared) |
| 3 | `READABLE` and an explicit activated degraded condition applies | `DEGRADED` |
| 4 | `READABLE`, no such condition, operational freshness **not** in force | **`NOT_APPLICABLE`** |
| 5 | `READABLE`, no such condition, in force, all activated gates pass | `FRESH` |
| — | any other combination | the fail-safe default: the oracle refuses |

Derivation for this world: the requested publication is returned and readable; no fallback; no degraded condition; no marker is effective, so freshness is not in force for `2026-03-23`; **row 4 decides `NOT_APPLICABLE`** (`oracle_output.json` `freshness_derivation`). The value is not read from, compared with, or checked against production before it is written; production appears only afterwards, in the comparison tests. It is also not the production fallback: `NOT_AVAILABLE` means "no consumer-safe result" and cannot describe a `READABLE` publication.

## 3. Reproduce every expected value

```
php derivation/author_inputs.php        # world declaration, provider response, foundation registry (AUTHORING TOOLS: they ran once)
php derivation/author_registry_inputs.php
php -d memory_limit=1G derivation/author_build_identity.php
php derivation/author_member_representation.php
php derivation/reference_oracle.php     # rewrites expected/* and derivation/oracle_output.json from inputs/ alone
php derivation/build_package.php        # classification, manifest, package fingerprint
```

The oracle includes no application code, opens no database, reads no run, publication or replay output, and calls no production hash implementation. Inputs: `inputs/synthetic_world.json` (with the activation context and publication facts), `provider_response.json`, `frozen_config_content.txt`, `frozen_reason_registry.json`, `frozen_registry_literals.json`, `frozen_build_identity.json` and `frozen_build_manifest.txt`, `frozen_manifest_member_representation.json`. `foundation_registry.json` is the registry the actual side restores.

## 4. Eligibility, nested identities and the manifest

* **Eligibility row.** `freshness_state` is the derived `NOT_APPLICABLE` (it was a literal `NOT_AVAILABLE` in candidate 3). Eligibility domain hash (V2, `|` framing): `eligibility_batch_hash` = `a8b3ee0e062c51578287d6d3f23bca65ad51d8dfc8dea3f3fdda6ebd0f545d57` (candidate 3: `42722d5ddeaa78896b81921e25d1c7d8d72de1e914a7a1ee24f8ae5ff2bf9269`).
* **Nested identities.** None of the nine nested members binds a freshness state (they are identity, calendar, status, event, source-scale, market-structure, factor-decision, factor-set and observation documents), so all nine are unchanged by this correction. The oracle recomputes them; they equal candidate 3's. `bars_batch_hash` and `indicators_batch_hash` do not bind freshness and are unchanged.
* **Manifest.** The 35-member inventory is unchanged. Members that changed against candidate 3: `freshness_state` (`NOT_APPLICABLE`), `artifacts.eligibility` (the new eligibility hash), and nothing else — `publication_version` keeps its text representation, now declared (section 5), and `canonicalization_version` keeps its value, now read from the frozen configuration. `readiness_state` is derived from the frozen publication facts (`READABLE`).
* **`publication_manifest_hash`** (`expected_publication_context`, a literal): `17f6a5177f18b8e811052aa9eb11abec62c9d0d193e10b7e198b6ed668848e55` (candidate 3: `56e44a75683bf3a1734c333c10855be3f3b123d6ba5ac2c1e192cb537a11c2f2`). Full payload and preimage: `oracle_output.json` `publication_manifest` and `expected/expected_publication_manifest.json`.

## 5. The two review points handled as frozen inputs

* `canonicalization_version` — frozen configuration `source.canonicalization_version` = `idx_regular_raw_v2`; the oracle fails (exit 2) if it is absent.
* `publication_version` — no authority text fixes its JSON type inside the hashed semantic payload (the manifest contract shows an integer in the operational view; the database column is `INT`). The type is a property of the **frozen governed producer**: `inputs/frozen_manifest_member_representation.json` declares `TEXT_BASE10` (the value `1` is hashed as the text `"1"`), cites the producer source (`EodPublicationRepository::publicationManifestSemanticPayloadV2`, the rendering line and its line number) and the sha256 of that file recorded by the frozen build (`72dc69a1d1dbf2702c5ef4d7f8b7084af17f284027d6ee323f0d5af44fcb190e`). The oracle reads the declaration and refuses it unless the sha256 equals the frozen build manifest's line for that file. The declaration was authored by reading the producer source (disclosed in section 9), not by reading any target output. The control tests prove (a) the declaration agrees with the frozen build manifest and with the source text of that file, and (b) the other representation (`1`) would give a different preimage, so the declaration is load-bearing.

## 6. The eleven bound inputs

Unchanged from candidate 3 except input 11 (the frozen build). All preimages are in `oracle_output.json`. Algorithm for every hash: SHA-256, lowercase hex.

| # | Input | Literal |
|---|---|---|
| 1 | `source_observation_manifest_hash` | `92ecc9f3fb02adec53093e3836129e7c4713549297e09ffe62f8619fa46bf0b5` |
| 2 | `canonical_raw_input_hash` | `6c874753323c4b750b253039444c73e6c4f9a0f0ddff19bbb4828ec07272c415` |
| 3 | `temporal_identity_hash` | `a9233bcee067ad6f1715494c0bd5b587e49fa69e08b2a1085bc5509dc1b2e51b` |
| 4 | `calendar_status_hash` | `2c7dc9f896cee8e1233ac0899d63316b5f2ccfb779cd6553f2fe3fa52e801dcf` |
| 5 | `event_factor_hash` | `4b3221309a4d1f6661c6b593920d20feeb5f03d674251aff11d7b04a32fd6f55` |
| 6 | `config_snapshot_hash` | `8d0dd54f449b119c4691426612512e152c7dd22c151c358a4acb320f2160a4c0` |
| 7 | `formula_registry_hash` | `2d1f53e19c2995ab5f9e0282e898e06a5f755ad4d9248f6c7cdac629e8ace4bf` |
| 8 | `reason_registry_hash` | `0dda9efd27338ad354bca8175aeb2a84d127a259ba5d26dfe84712c33c027a89` |
| 9 | `read_model_version` | `market_data_read_product_v1` |
| 10 | `serialization_version` | `canonical_json_v1` |
| 11 | `executable_build_identity` | `sha256:7a1ed5c78cc37a978df0441b565815f3e435fdceafd2045eb202d716ce16d8cf` (frozen build, owner decision Q5 = B) |

## 7. Frozen inputs against candidate 3

Byte-identical to candidate 3: `provider_response.json`, `foundation_registry.json`, `frozen_config_content.txt`, `frozen_reason_registry.json`, `frozen_registry_literals.json`.
Different: `synthetic_world.json` (`world_version`, `purpose`, and the new `operational_activation` and `publication_facts`), `frozen_build_identity.json` and `frozen_build_manifest.txt` (new build). New: `frozen_manifest_member_representation.json`.

## 8. Executable build identity (owner decision Q5 = B)

Method `php_source_build_v1` (the governed `ProducerRegistrySnapshot::build`). Frozen: `sha256:7a1ed5c78cc37a978df0441b565815f3e435fdceafd2045eb202d716ce16d8cf` over 5916 files (`inputs/frozen_build_manifest.txt`, sha256 `e8ed25135f52697d0bb072e3ac06dbf91b499bb73ea3b3f1fe5003ba92126eed`), the tree of repository commit `71f9f3d` (`MD-B10-A003` closed). The previous candidates' build changed because the MD-B10-A003 producers changed the build. The oracle consumes the frozen literal and checks only the internal consistency of the frozen input. This candidate is valid only for this build: any change to a file of the build requires a new candidate version.

## 9. Reviewer points

* The activation context and publication facts are author declarations of the synthetic world; the review should confirm they say what the authority needs (marker not effective, readable, no fallback, no degraded condition) and that the oracle's ordered table is the text of `Downstream_Data_Readiness_Guarantee_LOCKED.md`.
* The oracle's table implementation is the author's transcription of that text. It refuses (exit 3) every combination it does not represent, including every activated one: candidate 4 is a pre-activation proof candidate and proves no activated-world evaluation.
* `publication_version`: the representation rule was transcribed by the author from the governed producer source and is bound to the frozen build by hash; no authority text defines it.
* Transcription points of candidates 1–3 still apply (column order, nested document members, vocabulary labels, formula and reason member sets, the contamination whitelist, the single-date timestamp rule).
* The frozen configuration content and the frozen reason registry are snapshots of the platform's own sources, not independently authored values; drift changes the corresponding identity and fails the candidate, which is intended.

## 10. Independence statement

The oracle is a separate implementation. It does not call, include or read the output of the services that produce the value under test (it does not name them), and it reads no run, publication or replay. The author had read the production services while transcribing member lists, and read the producer source line for `publication_version` (section 5). The oracle output was produced from the frozen inputs alone; the comparison with a production run was made afterwards, and no production hash (including the stored publication manifest hash) and no production freshness value was used as an input.
