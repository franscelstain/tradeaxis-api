# Fixture Package Manifest (LOCKED)

## Purpose
Define the manifest shape for one fixture package so every fixture family is explicit about:
- what files it contains
- what contracts it covers
- what assertion layers it supports

## Minimum manifest shape

    {
      "fixture_family": "fixture_controlled_correction",
      "version": "v2",
      "market_scope": "IDX_REGULAR_EOD",
      "intentional_dataset_start": "2023-01-02",
      "replay_mode": "PUBLICATION_EXACT",
      "knowledge_cutoff": null,
      "input_manifest_hash": "INPUT_HASH",
      "expected_manifest_hash": "EXPECTED_HASH",
      "source_observation_manifest_hash": "OBS_HASH",
      "config_snapshot_hash": "CONFIG_HASH",
      "temporal_revision_set_hash": "TEMPORAL_HASH",
      "factor_set_hash": "FACTOR_HASH",
      "price_product_code": "STRUCTURAL_ADJUSTED",
      "formula_version": "weekly_swing_eod_v2",
      "read_model_version": "weekly_swing_read_v2",
      "contract_areas": [
        "historical_correction_integrity",
        "publication_resolution",
        "hash_determinism"
      ],
      "files": [
        "prior_publication.json",
        "correction_request.json",
        "bars_before.csv",
        "bars_after.csv",
        "expected_hashes_before.json",
        "expected_hashes_after.json",
        "expected_publication_state.json",
        "expected_run_summary.json"
      ],
      "assertion_layers": [
        "observation",
        "temporal_identity",
        "row",
        "factor_product",
        "run",
        "hash",
        "publication",
        "read_model",
        "replay"
      ],
      "source_and_license_notes": "sanitized frozen fixture; redistribution constraints recorded",
      "independent_oracle_ref": "reviewed expected artifact or calculation source"
    }

## Mandatory manifest rules

- All package files have SHA-256 entries in a canonical file manifest.
- Required semantic bindings cannot be replaced by current environment/master state.
- `AS_KNOWN` packages require a non-null knowledge cutoff and recorded-time revisions.
- Real-market cases record source, retrieval/recorded times, sanitation, licensing, and verification authority.
- Synthetic mathematics fixtures are labeled synthetic and cannot replace required real-market semantic cases.
- Expected artifacts are independently derived and are not regenerated from the implementation being tested.

## Required manifest fields
- fixture_family
- version
- contract_areas
- files
- assertion_layers

## Assertion layer values
Allowed values:
- `row`
- `run`
- `hash`
- `publication`
- `replay`

## Locked rule
Every real fixture package should be describable by one manifest like this.

## Package filesystem minimum (LOCKED)
A real fixture package must be shippable as one stable directory or archive with this minimum shape:
- `manifest.json`
- `inputs/`
- `expected/`
- optional `notes/` for explanatory, non-normative context

Rules:
- files listed in `manifest.json` must resolve to actual package contents
- expected outputs must live under `expected/`
- input source material must live under `inputs/`
- explanatory notes must never replace missing expected outputs
