# Watchlist Document Authority

> **Status:** CANONICAL GOVERNANCE

> **Role purity:** setiap dokumen tunduk pada [`ONE_DOCUMENT_ONE_AUTHORITATIVE_ROLE_STANDARD.md`](ONE_DOCUMENT_ONE_AUTHORITATIVE_ROLE_STANDARD.md). Satu file hanya boleh mempunyai satu authoritative role; references ke role lain tidak memindahkan ownership.

## Purpose

Governance tertinggi untuk hierarchy authority, conflict resolution, ownership, dan document lifecycle domain Watchlist.

## Physical Root Grouping

Current paths are grouped by permanent role:

- `../strategy/` + current governance folder `./` are under `docs/watchlist/authority/`;
- `../../development/implementation/`, `../../development/research/`, and `../../development/findings/` are active working layers;
- `../../records/evidence/`, `../../records/decisions/`, and `../../records/history/` are factual/issued/historical records.

Physical grouping improves discoverability; it does not change document-level mutability or authority precedence.


## Authority Hierarchy

1. `DOCUMENTATION_ARCHITECTURE.md` — layer/authority layout;
2. `CURRENT_VERIFICATION_REBASELINE_STANDARD.md` — current verification epoch; no inheritance of pre-epoch PASS/DONE/READY;
3. `DOCUMENT_RECORDING_STANDARD.md` — universal mutability/record lifecycle;
3. `WORK_BASELINE_LOCK_STANDARD.md` — immutable starting authority/source baseline;
4. `WORK_CORRELATION_AND_RECORD_REGISTRY_STANDARD.md` — Work ID + current record relationships;
5. `CHANGE_IMPACT_DECLARATION_STANDARD.md` — planned/actual material change impact;
6. `STAGE_EXECUTION_AND_REWORK_STANDARD.md` — attempt/re-entry/closure lifecycle;
7. `DEPENDENCY_REGISTRY_STANDARD.md` — verified dependency + resume trigger;
8. `IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md` — recurring legacy/conformance gate;
9. `STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` — rule-level strategy coverage;
10. `DOCUMENT_INTEGRITY_GATE_STANDARD.md` — executable structural + relationship integrity enforcement;
11. `STAGE_CLOSURE_MANIFEST_STANDARD.md` — terminal stage evidence summary;
12. `CURRENT_STATE_SUMMARY_STANDARD.md` — generated status view;
13. `STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv` — current coverage index, not business-rule owner;
14. `DOCUMENT_CHANGE_POLICY.md` — canonical strategy-change rule;
15. canonical strategy di `../strategy/`;
16. technical translation di `../../development/implementation/`;
17. research/evidence/findings/decisions/history sesuai perannya.

`../../development/research/`, `../../records/evidence/`, `../../development/findings/`, `../../records/decisions/`, dan `../../records/history/` tidak menjadi owner business rule hanya karena lebih baru tanggal/campaign.

## Active Strategy

Current active strategy hanya `weekly_swing`. Strategy lain tidak boleh diperkenalkan sebagai active product scope tanpa keputusan eksplisit yang mengubah scope.

## Conflict Resolution

- Governance menentukan cara membaca/mencatat/mengubah dokumen.
- Canonical Weekly Swing strategy menang atas implementation/reference/example/fixture.
- Implementation harus diperbaiki bila menyimpang dari strategy tanpa approved strategy-change decision.
- Research yang PASS tetap non-canonical sampai decision mengadopsinya.
- Evidence mencatat fakta, bukan rule, dan final evidence tidak boleh rewritten.
- Issued decision tidak boleh rewritten; gunakan supersession.
- Locked research tidak boleh retuned in-place.
- Historical/superseded records tidak boleh dipakai sebagai fallback current behavior.

## Upstream Ownership

Watchlist tidak mendefinisikan ulang fakta yang dimiliki `market_data`, termasuk OHLCV, indicators, publication/read model, readiness, corporate-action semantics, atau producer-side temporal correctness. Watchlist hanya memiliki consumer behavior setelah menerima upstream contract yang sah.

## Implementation Boundary

Schema, API, DTO, repository, command, SQL, test, fixture, hash transport, dan artifact format berada pada implementation layer kecuali suatu semantics memang dinyatakan sebagai canonical strategy behavior.

Implementation material contract change boleh terjadi, tetapi wajib mengikuti `DOCUMENT_RECORDING_STANDARD.md` dan tidak boleh menjadi implicit strategy revision.

## Change Rule

- Universal document recording/lifecycle: `DOCUMENT_RECORDING_STANDARD.md`.
- Strategy semantic revision: `DOCUMENT_CHANGE_POLICY.md`.
- Material documentation event: append ke `DOCUMENT_CHANGE_LOG.md`.


## Coverage Authority

Canonical strategy tetap pemilik meaning. Traceability matrix hanya membuktikan apakah meaning tersebut sudah dipetakan dan dipenuhi oleh implementation/proof. Jika matrix conflict dengan strategy, strategy menang dan matrix wajib direvalidasi.

## Baseline and Integrity Enforcement

Current implementation/proof evidence must be attributable to an immutable Work Baseline Lock. Executable documentation integrity checks are mandatory at attempt/stage/package gates according to `DOCUMENT_INTEGRITY_GATE_STANDARD.md`. Neither mechanism owns Weekly Swing business behavior; they prove which authority was followed and whether the documentation/traceability structure remains internally valid.

## Work Relationship / Closure Enforcement

Current/future implementation attempt records are correlated by Attempt/Work ID and indexed in `../../records/WORK_RECORD_REGISTRY.csv`. Verified dependencies use `../../development/implementation/WS_DEPENDENCY_REGISTRY.csv`. Terminal stage claims require Stage Closure Manifest plus structural/relationship integrity evidence. Generated `CURRENT_STATE.md` is navigation/status only and cannot override source authorities.


## Current Verification Rebaseline

Current implementation/proof status is governed by `CURRENT_VERIFICATION_REBASELINE_STANDARD.md` and `CURRENT_VERIFICATION_EPOCH.json`. Historical evidence/decisions/results retain factual history but have no current verification effect. Existing behavior-bearing implementation documents must be revalidated before becoming current technical conformance evidence.
