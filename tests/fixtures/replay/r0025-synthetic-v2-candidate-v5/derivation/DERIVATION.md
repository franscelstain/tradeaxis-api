# Derivation record — R0025 synthetic V2 golden fixture, candidate 5

Status: `CANDIDATE_AWAITING_INDEPENDENT_REVIEW`. Not reviewed, not approved, not admitted, not proof, not a canonical golden.
World: `SYNTHETIC_TEST_WORLD_NOT_A_REAL_INSTRUMENT` (`r0025-synthetic-v2/5`), explicitly **PRE-ACTIVATION**. Test only; never master-data truth.
Supersedes: candidate-4 (`../r0025-synthetic-v2-candidate-v4`, fingerprint `d0a61b36d99682c9e165560a9e89ad83ee5a363d08f4f3f58701b06cc7ac9f00`; reviewed PASS `E-MD-B18-A002-100`, owner-approved `D-MD-B18-A002-016`, admitted `E-MD-B18-A002-101`, invalidated for the current build `E-MD-B18-A002-108`; immutable, untouched), candidate-3, candidate-2 and candidate-1 (reviewed CHANGES REQUIRED, untouched).
Authority: everything candidate 4 cited, plus `D-MD-B18-A002-018` (Q9 = A1: the reason-registry version is a derived member of every new configuration snapshot) and the governed record `E-MD-B04-A003-002` (the member shape `reason_registry = {identity_contract, semantic_identity}`, the canonical content `{reason_registry, resolved_config, semantic_bindings}` and `config_content_hash` = SHA-256 of that content), and `D-MD-B18-A002-014` Q4 = A (the reason-registry semantic identity).

## 1. What changed from candidate 4, and why

Candidate 4 was valid for the build it froze. The final build differs from it in three ways that matter to the candidate: (a) the configuration snapshot content gained the `reason_registry` member (MD-B04-A003), so `config_content_hash` moved; (b) every artifact row binds `config_content_hash`, so the bars, indicators and eligibility hashes, the factor-set document, the event-factor composite and the publication manifest moved; (c) the executable build changed (MD-B04-A003, the as-known binding and F-MD-B18-A002-018 touched `app/`), so the frozen build identity moved. A candidate is valid for exactly one build and one configuration content, so a new candidate is required; the old approval does not carry over.

| Item | Candidate 5 |
|---|---|
| Frozen configuration content | three members; `reason_registry` is **derived by the oracle** from `inputs/frozen_reason_registry.json` (437 entries) and the content is **assembled by the oracle**; the frozen file must equal the assembly byte for byte, otherwise the oracle flags it and `build_package.php` refuses to build |
| `config_snapshot_hash` | derived: `2075a960f2d065877244435ed38ac9715c61a21479470c1935642d89eed3bf6f` (5768 bytes). Candidate 4: `8d0dd54f449b119c4691426612512e152c7dd22c151c358a4acb320f2160a4c0`. Nothing was copied |
| `reason_registry_hash` / member `semantic_identity` | derived: `0dda9efd27338ad354bca8175aeb2a84d127a259ba5d26dfe84712c33c027a89` — the value is **unchanged** against candidate 4 because the registry **content** is unchanged; what changed is that the snapshot now binds it |
| Frozen executable build | re-frozen at repository commit `7eeaf89`: `sha256:7ea1956a36fe3ad773479bb249f1409b6fa04df8725576080284294ed8016c4a`, 5916 files |

Preserved unchanged (settled): locked assertion layers `run, hash, publication, replay` (no `row`), the 35-member manifest inventory, the independent canonicalisation, the mandatory literal `publication_manifest_hash`, actual-side manifest re-verification, allocation independence, NOT_APPLICABLE pre-operational freshness, exact-date behaviour, provider timestamp semantics, event-factor projection, admissibility rules, the eleven bound-input derivations, the package/fingerprint discipline, and no target output as an oracle.

## 2. The reason-registry member and the configuration snapshot content (new)

1. `inputs/frozen_reason_registry.json` is the frozen governed registry (437 entries; byte-identical to candidate 4's).
2. Document: `{"schema_version":"market-data-reason-registry/v2","entries":[{code,category,description,severity,is_active}, ...]}`, entries ordered by `code` bytewise, no row id, no audit column, keys ordered at every level, JSON with unescaped slashes and unicode. Its SHA-256 is the semantic identity (`reason_registry_hash`).
3. Member: `reason_registry = {"identity_contract":"market-data-reason-registry/v2","semantic_identity":<2>}` (record `E-MD-B04-A003-002`).
4. Content: canonical JSON of `{reason_registry, resolved_config, semantic_bindings}`; `resolved_config` and `semantic_bindings` are the frozen members (identical to candidate 4's and to the configuration source at the final build); `config_content_hash` = SHA-256 of the content (`2075a960…bf6f`).
5. Everything that binds the config hash is then derived by the oracle: bars, indicators and eligibility rows (`config_content_hash` column), the factor-set document, the event-factor composite, the manifest preimage and `publication_manifest_hash`.

Sensitivity (proved by the test and the probes): a **content** change of the registry (one description, a code, an `is_active` flag) moves `semantic_identity`, therefore the config content and `config_snapshot_hash`, therefore every value listed in 5. A change of **row order or allocation only** (row ids, audit columns, table order) moves none of them.

## 3. The frozen pre-activation world and the independent derivation of `NOT_APPLICABLE` (settled, carried over unchanged from candidate 4)

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

## 4. Reproduce every expected value

```
php derivation/author_inputs.php        # world declaration, provider response, foundation registry (AUTHORING TOOLS: they ran once)
php derivation/author_registry_inputs.php <database>
php -d memory_limit=1G derivation/author_build_identity.php
php derivation/author_member_representation.php
php derivation/author_config_content.php   # frozen configuration content incl. the derived reason_registry member
php derivation/reference_oracle.php     # rewrites expected/* and derivation/oracle_output.json from inputs/ alone
php derivation/build_package.php        # classification, manifest, package fingerprint
```

The oracle includes no application code, opens no database, reads no run, publication or replay output, and calls no production hash implementation. Its inputs are `inputs/synthetic_world.json` (activation context and publication facts), `provider_response.json`, `frozen_config_content.txt`, `frozen_reason_registry.json`, `frozen_registry_literals.json`, `frozen_build_identity.json`, `frozen_build_manifest.txt` and `frozen_manifest_member_representation.json`.

## 5. Values that moved against candidate 4 (all derived)

| Value | Candidate 5 |
|---|---|
| `bars_batch_hash` = `canonical_raw_input_hash` | `fa316df7c72d74a0c4e2b28154d7d773ad23c086aeb7342caf7c95c13589e219` |
| `indicators_batch_hash` | `d38185be55ae6c1334f1025e4e489a384ea90eea17da4c43d93cead31766c995` |
| `eligibility_batch_hash` | `a30d81022aae24012d7fee3b9aa9a1c5a8bab98ed66c013ce786eaf892218c56` |
| `factor_set_hash` | `63dda396fc01519248abf19518cde3deef588edc72893d45f60ab11134c46611` |
| `event_factor_hash` | `747227a60551f5b9f6e2c468255d95ca3a3c07abb2f90374c6c29ff8d4f42488` |
| `config_snapshot_hash` | `2075a960f2d065877244435ed38ac9715c61a21479470c1935642d89eed3bf6f` |
| `publication_manifest_hash` | `6162e5fcd1d327fa39d1e43b2a3911ca74807816d36d8ef8ea506a10a977c8bd` |
| `executable_build_identity` | `sha256:7ea1956a36fe3ad773479bb249f1409b6fa04df8725576080284294ed8016c4a` |

Unchanged in value (derived again, equal): `source_observation_manifest_hash`, `temporal_identity_hash`, `calendar_status_hash`, `formula_registry_hash`, `reason_registry_hash`, `read_model_version`, `serialization_version`, and the eight other nested set documents. The full list with preimages is in `oracle_output.json`.

## 6. The eleven bound inputs

| # | Input | Literal |
|---|---|---|
| 1 | `source_observation_manifest_hash` | `92ecc9f3fb02adec53093e3836129e7c4713549297e09ffe62f8619fa46bf0b5` |
| 2 | `canonical_raw_input_hash` | `fa316df7c72d74a0c4e2b28154d7d773ad23c086aeb7342caf7c95c13589e219` |
| 3 | `temporal_identity_hash` | `a9233bcee067ad6f1715494c0bd5b587e49fa69e08b2a1085bc5509dc1b2e51b` |
| 4 | `calendar_status_hash` | `2c7dc9f896cee8e1233ac0899d63316b5f2ccfb779cd6553f2fe3fa52e801dcf` |
| 5 | `event_factor_hash` | `747227a60551f5b9f6e2c468255d95ca3a3c07abb2f90374c6c29ff8d4f42488` |
| 6 | `config_snapshot_hash` | `2075a960f2d065877244435ed38ac9715c61a21479470c1935642d89eed3bf6f` |
| 7 | `formula_registry_hash` | `2d1f53e19c2995ab5f9e0282e898e06a5f755ad4d9248f6c7cdac629e8ace4bf` |
| 8 | `reason_registry_hash` | `0dda9efd27338ad354bca8175aeb2a84d127a259ba5d26dfe84712c33c027a89` |
| 9 | `read_model_version` | `market_data_read_product_v1` |
| 10 | `serialization_version` | `canonical_json_v1` |
| 11 | `executable_build_identity` | `sha256:7ea1956a36fe3ad773479bb249f1409b6fa04df8725576080284294ed8016c4a` |

## 7. Frozen inputs against candidate 4

Byte-identical to candidate 4: `provider_response.json`, `foundation_registry.json`, `frozen_reason_registry.json`, `frozen_registry_literals.json`, `frozen_manifest_member_representation.json`.
Different: `synthetic_world.json` (`world_version`, `purpose` only), `frozen_config_content.txt` (the `reason_registry` member added), `frozen_build_identity.json` and `frozen_build_manifest.txt` (the final build).

## 8. Executable build identity

Method `php_source_build_v1` (the governed `ProducerRegistrySnapshot::build`). Frozen: `sha256:7ea1956a36fe3ad773479bb249f1409b6fa04df8725576080284294ed8016c4a` over 5916 files (`inputs/frozen_build_manifest.txt`, sha256 `8464514949826b5a2a7cd686cd3d2dc33a57bba58e43824aad3667bc0971146e`), the tree of repository commit `7eeaf89` (MD-B18-A002 F-018 complete) with no uncommitted change to `app`, `config`, `bootstrap`, `vendor`, `composer.json` or `composer.lock`. Retained archive: `storage/app/market-data/input-builds/7ea1956a36fe3ad773479bb249f1409b6fa04df8725576080284294ed8016c4a.json.gz` (sha256 `0f65faae9c725532fc3758cd024ae9fdedc5e0377a3997f1b95077a9a7cd35e2`, 11406320 bytes, decompresses to exactly this content). Any later change to a file of the build invalidates this candidate.

## 9. Reviewer points

* The reason-registry member shape, `identity_contract` literal and canonical content are transcribed by the author from the governed record `E-MD-B04-A003-002` and `D-MD-B18-A002-018`; the reviewer should confirm the transcription and that the oracle's canonical encoding (keys ordered at every level, unescaped slashes/unicode) is the content canonicalisation those records name.
* `resolved_config` and `semantic_bindings` are snapshots of the platform's own configuration source (unchanged from candidate 4), not independently authored values; drift changes the config identity and fails the candidate, which is intended.
* The activation context and publication facts are author declarations of the synthetic world; the oracle's ordered freshness table is the text of `Downstream_Data_Readiness_Guarantee_LOCKED.md`. The oracle refuses every combination it does not represent: candidate 5 proves no activated-world evaluation.
* `publication_version`: the representation rule was transcribed from the governed producer source and is bound to the frozen build by hash.
* Transcription points of candidates 1–4 still apply (column order, nested document members, vocabulary labels, formula and reason member sets, the contamination whitelist, the single-date timestamp rule).
* This package has not been reviewed: author-side validation is not independent review.

## 10. Independence statement

The oracle is a separate implementation. It does not call, include or read the output of the services that produce the value under test, and it reads no run, publication or replay. The author read the production services while transcribing member lists. The oracle output was produced from the frozen inputs before any target run; the target (the replay verifier) appears only as the ACTUAL side in the author-side validation test. Before authoring, the author had dumped the production snapshot content once to learn its shape (outside the package; not an input of any tool or of the oracle). The oracle derives the member, the content and the config hash from the frozen inputs alone; the derived value equals that dump, which is a cross-check recorded here, not a source.