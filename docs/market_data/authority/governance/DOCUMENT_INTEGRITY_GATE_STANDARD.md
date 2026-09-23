# Document Integrity Gate Standard

The executable gate must verify: root architecture; active links; parseable JSON/CSV; Windows-safe paths; complete/unique role and ID registries; one-document-one-role; strategy freeze hashes; verification rebaseline; traceability source fingerprints; stage-register shape; no exact duplicate physical files; and absence of unclassified physical documents.

## Immutable historical integrity exception

Added by `DOC-CHG-20260923-001` under `D-MD-B18-A002-007`.

An issued evidence record is `IMMUTABLE_AFTER_ISSUE` and is never edited to make it pass a structural check. When such a record was issued structurally invalid, `authority/governance/DOCUMENT_INTEGRITY_EXCEPTION_REGISTRY.json` is the only mechanism by which the gate may admit it. There is no other exception path.

### Eligibility

An exception may be registered only when all of the following hold:

1. the artifact is registered `EVIDENCE` / `IMMUTABLE_AFTER_ISSUE`, has a Document ID, and is an `ISSUED` `EVIDENCE` work record;
2. the artifact is byte-identical to its issued form, bound by its sha256;
3. an issued correction evidence record exists, is itself parseable and registered, has verdict `CORRECTION`, names the artifact's record ID in `related_evidence`, identifies the artifact by record ID, path and retained sha256 with disposition `RETAINED_UNMODIFIED`, and identifies the exact defect as an entry in its `defects` whose `integrity_check` equals the excepted check;
4. the correction does not replace the original: it does not supersede the artifact, and the artifact remains a current issued record;
5. the exception names an `ISSUED` `DECISION` work record that authorises it.

The only integrity check that may be excepted is `JSON_PARSE`. Adding a check or an eligible artifact class requires a new controlled revision.

### Registry contract

The registry is a JSON object with exactly these keys:

- `schema_version`: `document_integrity_exception_registry_v1`;
- `governing_standard`: `authority/governance/DOCUMENT_INTEGRITY_GATE_STANDARD.md`;
- `exceptions`: a list of entries.

Each entry has exactly these keys, each a non-empty string:

| Key | Meaning |
|---|---|
| `exception_id` | `MD-DOCEX-` followed by four digits; unique |
| `status` | `ACTIVE` (applied) or `WITHDRAWN` (retained for history, never applied) |
| `integrity_check` | the excepted check; only `JSON_PARSE` |
| `artifact_path` | path relative to `docs/market_data` |
| `artifact_document_id` | the artifact's `MD-DOC-*` identifier |
| `artifact_record_id` | the artifact's work record ID |
| `artifact_sha256` | lowercase sha256 of the artifact's issued bytes |
| `correction_record_id` | the correction's work record ID |
| `correction_path` | path relative to `docs/market_data` |
| `correction_defect_id` | the `id` of the correction's defect entry for this check |
| `authorizing_decision_id` | the authorising decision's work record ID |
| `reason` | why the artifact cannot be fixed and what corrects it |

At most one `ACTIVE` entry may exist per artifact path.

### Gate behaviour

- The gate validates the registry before applying it, and reports the result as its own check, `INTEGRITY_EXCEPTION_REGISTRY`. Any violation of the contract or of the eligibility conditions for an `ACTIVE` entry fails that check, and then no exception is applied at all.
- An `ACTIVE` entry whose artifact parses is stale and fails the registry check.
- `JSON_PARSE` still parses every JSON file and reports every raw failure. A failure is admitted only when a valid `ACTIVE` entry names exactly that path, and the admission is reported alongside the raw failures. Every other failure keeps `JSON_PARSE` at `FAIL`.
- The registry file is itself parsed like any other JSON file and can never except itself.
