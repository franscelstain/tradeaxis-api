# Watchlist Document Authority

> **Status:** CANONICAL GOVERNANCE

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
2. `DOCUMENT_RECORDING_STANDARD.md` — universal mutability/recording/correction/supersession rule;
3. `STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` — strategy-to-implementation coverage/closure rule;
4. `STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv` — current coverage index, not business-rule owner;
5. `DOCUMENT_CHANGE_POLICY.md` — canonical strategy-change rule;
6. canonical strategy di `../strategy/`;
7. technical translation di `../../development/implementation/`;
8. research/evidence/findings/decisions/history sesuai perannya.

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
