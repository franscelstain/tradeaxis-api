# Derivation record — R0025 synthetic V2 golden fixture, candidate 1

Status: `CANDIDATE_AWAITING_INDEPENDENT_REVIEW`. Not approved, not proof, not a canonical golden.
World: `SYNTHETIC_TEST_WORLD_NOT_A_REAL_INSTRUMENT` (`r0025-synthetic-v2/1`). Test only; never master-data truth.
Authority: `D-MD-B18-A002-011`, `D-MD-B18-A002-013`, `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md` section 6A, `Fixture_Package_Manifest_LOCKED.md`, `Golden_Fixtures_Specification.md`.

## 1. Reproduce every expected value

```
php derivation/reference_oracle.php     # rewrites expected/* and derivation/oracle_output.json from inputs/ alone
php derivation/build_package.php        # classification, manifest, package fingerprint
```

The oracle includes no application code and opens no database. Its only inputs:

| Input | Content |
|---|---|
| `inputs/synthetic_world.json` | the synthetic world declaration: fixed retained roots, listing, one bar, calendar rule, run clock |
| `inputs/provider_response.json` | the exact provider response bytes (223 bytes, no trailing newline) |
| `inputs/frozen_config_content.txt` | the frozen configuration content (an input snapshot of the configuration source, captured in the golden test's environment) |
| `inputs/foundation_registry.json` | the retained-identity registry the actual side restores (the oracle reads the roots from the world declaration) |

The outputs `oracle_output.json` hold every intermediate document, every row token, and the full preimage of every hash, so a reviewer can recompute any value with `sha256sum`.

## 2. Specification used, and where it was transcribed

The V2 profile is specified by governed records. The oracle implements them and the reviewer checks the transcription points:

* **Framing.** `E-MD-B10-A002-010` `semantic_hash_contract`: UTF-8 bytes `market-data-semantic-hash/v2|<domain>`, then LF and the rows for a non-empty set only; no trailing delimiter or LF; ordering trade date, retained listing root, then row bytes; NULL is the empty token.
* **Serialization.** `Audit_Hash_and_Reproducibility_Contract_LOCKED.md`: SHA-256, lowercase hex, `|` delimiter, LF row separator, locked schema order, sorted canonical JSON for object and set fields.
* **Number formats.** `Hash_Number_Formatting_LOCKED.md`: prices 4 decimals (also `hh20`, `ll20`, `ma20`, `ma50`), traded values and ADV proxies 2, ATR and ratio fields 10, integers without separators, flags `0`/`1`, dates `YYYY-MM-DD`, timestamps `YYYY-MM-DD HH:MM:SS`.
* **Member lists and order (transcription point 1).** The members of each V2 artifact are the ones `E-MD-B10-A002-010` enumerates (retained roots, provider namespace and symbol, observation manifest hash, OHLCV, previous close, actual traded value, trade count, board, session, canonicalization version, price product, quality state and reasons, configuration content hash, source-scale state and set hash; for indicators the nested set hashes, validity, values, versions, ATR state, contexts; for eligibility the decision, all states, reasons, configuration content hash, read-model version). The column ORDER is transcribed in `reference_oracle.php` (`$barsColumns`, `$indColumns`, `$eliColumns`). The reviewer compares it with the profile's declared member order.
* **Nested set documents (transcription point 2).** Each nested identity is a canonical JSON document `{schema_version, content}` of the rows below, hashed with SHA-256: `market-data-observation-manifest/v2` (entries), `identity-board-resolution-set/v2`, `calendar-revision-set/v2`, `status-resolution-set/v2`, `event-revision-set/v2`, `source-scale-assessment-set/v2`, `market-structure-resolution-set/v2`, `factor-decision-set/v2`, `market-data-adjustment-factor-set/v2`. Documents carry no local key (an `*_id` member is forbidden), NULL is the empty token and integers stay integers.
* **Vocabulary (transcription point 3).** No authority text defines labels such as `CURRENT_READABLE_PUBLICATION_AUDIT`, `RESOLVED_READABLE_CURRENT`, `MIRROR_VALID`, `LINEAGE_VERIFIED`, `API_FREE`, `FAIL_CLOSED_BOARD_UNKNOWN`, `MARKET_STRUCTURE_BOARD_UNKNOWN`, `SOURCE_TRACEABLE`, `NO_CONTAMINATION_DETECTED` or `temporal_provider_mapping_v1`. They are the implementation's vocabulary. The package fixes which label applies to the frozen scenario; the spelling comes from the implementation.

## 3. Hash derivations (input fields, ordering, normalization, serialization, algorithm, literal)

All preimages are in `oracle_output.json`. Algorithm for every hash: SHA-256, lowercase hex.

| Value | Inputs | Derivation | Literal |
|---|---|---|---|
| provider payload hash | `provider_response.json` bytes | sha256 of the bytes | `8ab35be69d1223c7bcb6220d8937fbd703ce3f44c1a860489f865d5b31019fd3` |
| response schema fingerprint | the response structure | one line per JSON path `path:type` (arrays `[]`), sorted bytewise, joined by LF, sha256 | `oracle_output.json` `frozen_input_facts` |
| config content hash | `frozen_config_content.txt` | sha256 of the file bytes | `8d0dd54f449b119c4691426612512e152c7dd22c151c358a4acb320f2160a4c0` |
| observation manifest | one accepted entry whose parent entry is the captured entry | entry = payload hash and ref, provider, provider symbol, mapping revision, requested trade date, acquired-at (the run clock), adapter and provider schema versions (from the frozen config), schema fingerprint, outcome and validation state, parent entry hash; manifest document hashed | `fa1a57ddea92bc605dcb4537341c7f2a8db99c52f42f2384b058eb50b69c9ef3` |
| identity set | the retained roots, no board code, board identity recorded at the listing record time, `FAIL_CLOSED_BOARD_UNKNOWN` | set document | `fc581f41d529036c9886bdac8b21e700503321e5b6f3661f19771ade97fc586b` |
| calendar set | the frozen calendar rule for the trade date | one revision row | `ef256ac9af24d297d5fbe6fd3c97219ea5565181050b69f95050fff3c6a9065e` |
| status set | one listing, bar expectation unknown, status unknown, no revision | set document | `083483df0e9a825eb0b7fd7cf9cea9f9606c9d28f04f4d249442681ad01af81e` |
| event / source-scale / factor-decision sets | no corporate action is frozen | empty set documents | `e26c3c22…`, `ebb520fa…`, `648a6377…` |
| market-structure set | one listing, board unknown, no rule revision | set document | `d313d45ada9589003ac3a8af2f962d5fea02850d35bb2e5fc14120bd4fc4f8e6` |
| factor set | structural-adjusted product, bound, config content hash, window from dataset start to the trade date, no decisions or factors | factor-set document | `f76a54ae5251685f2bcff60bce22fc66f250408350583b86c57bde953baf74e0` |
| `bars_batch_hash` | one bar row | V2 framing over the bars columns | `6a981c3a417a19b59f401690c53585a06dc0a9156c252fa82975333309ccb1e9` |
| `indicators_batch_hash` | one row: invalid, `IND_INSUFFICIENT_HISTORY`, every windowed value null with a per-field reason | V2 framing over the indicator columns | `0668bcc2c7ae9b30536ab5f069856c65688722c6b6cdc08938b2f3c827238019` |
| `eligibility_batch_hash` | one row: not eligible, `ELIG_INSUFFICIENT_HISTORY`, states from the scenario | V2 framing over the eligibility columns | `42722d5ddeaa78896b81921e25d1c7d8d72de1e914a7a1ee24f8ae5ff2bf9269` |
| `temporal_identity_hash` (replay identity) | the identity set hash | sha256 of the JSON of `{component_key: universe_identity, identity_revision_set_hash, profile}` sorted by key | `oracle_output.json` `replay_composites` |
| `calendar_status_hash` (replay identity) | calendar and status set hashes | the same composite rule | `oracle_output.json` `replay_composites` |

The composite rule is the V2 rule of the replay verifier (`E-MD-B18-A002-092`); the oracle implements it from that description.

## 4. Why the scenario's semantic values are what they are

One listing, one bar (`open 100`, `high 110`, `low 95`, `close 105`, `volume 1000`), no history. Therefore: the coverage universe is one listing and one bar is available (ratio 1 against the frozen threshold 0.98, gate `PASS`, `COVERAGE_THRESHOLD_MET`); the run succeeds and is readable, promoted and current; the seal is `SEALED`; the price product is `STRUCTURAL_ADJUSTED` with no factor event; no window of 5, 10, 20 or 50 sessions is satisfiable, so the indicator row is invalid with `IND_INSUFFICIENT_HISTORY` and the eligibility row is not eligible with `ELIG_INSUFFICIENT_HISTORY` (invalid indicator count 1, hard reject count 1, eligible count 0); no board identity exists, so market structure is board-unknown and fails closed; no corporate action, status revision or sector context exists. The reason-code counts are one `DATASET_HASH_CREATED` and one `ELIG_INSUFFICIENT_HISTORY`.

Run-level values that are configuration: acquisition timeout, retry maximum, coverage threshold, mode, basis and contract version, and the adapter and schema versions come from `frozen_config_content.txt`.

## 5. What is not asserted, and reviewer points

* `event_factor_hash` is not asserted: it still binds the ancillary captures, which carry materialized rows with local ids (the open part of `F-MD-B10-A002-004`). `formula_registry_hash`, `reason_registry_hash` and `executable_build_identity` are not asserted: they bind the executable build.
* The bar's `source_timestamp` is NULL and its `acquired_at` is the run clock. The Audit contract asks for the source timestamp only "when consumer-visible availability semantics require them", so NULL is a reading, not a quotation.
* The set of fields with a null reason (21) is the set of windowed fields the indicator formula spec makes unavailable with one bar. Authority does not name `null_reasons_json` members.
* `board_identity_recorded_at` is the listing master record time of the frozen world (`2020-01-01 00:00:00`).
* The frozen configuration content is a snapshot of the configuration source, not an independently authored configuration. Drift in the registry or in the code-owned semantic bindings changes the config hash and fails the candidate, which is the intended behaviour for a frozen configuration.

## 6. Independence statement

The oracle was written as a separate implementation of the profile. It does not call, include or read the output of the services that produce the value under test. The author had read the production services while transcribing the member lists and vocabulary above; no hash of any production run was used as an input to the oracle, and the candidate-versus-actual comparison was made after the oracle output existed. That transcription step is exactly what an independent reviewer should re-check.
