# Watchlist Document Authority

> **Status:** CANONICAL GOVERNANCE

## Purpose

Governance tertinggi untuk hierarchy authority, conflict resolution, ownership, dan document lifecycle domain Watchlist.

## Authority Hierarchy

1. `DOCUMENTATION_ARCHITECTURE.md` — layer/authority layout;
2. `DOCUMENT_RECORDING_STANDARD.md` — universal mutability/recording/correction/supersession rule;
3. `DOCUMENT_CHANGE_POLICY.md` — canonical strategy-change rule;
4. canonical strategy di `../strategy/`;
5. technical translation di `../implementation/`;
6. research/evidence/findings/decisions/history sesuai perannya.

`research/`, `evidence/`, `findings/`, `decisions/`, dan `history/` tidak menjadi owner business rule hanya karena lebih baru tanggal/campaign.

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
