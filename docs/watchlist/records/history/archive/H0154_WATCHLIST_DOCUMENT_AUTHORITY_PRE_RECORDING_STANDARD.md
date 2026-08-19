# Watchlist Document Authority

> **Status:** CANONICAL GOVERNANCE

## Purpose

Dokumen ini adalah governance tertinggi untuk dokumentasi domain Watchlist. Ia menentukan hierarchy authority, conflict resolution, ownership, dan perubahan dokumen setelah pemisahan architecture.

## Authority Hierarchy

1. `governance/DOCUMENTATION_ARCHITECTURE.md` dan `governance/DOCUMENT_CHANGE_POLICY.md`;
2. canonical strategy di `strategy/`;
3. technical translation di `implementation/`;
4. research/evidence/findings/decisions/history sesuai perannya.

`research/`, `evidence/`, `findings/`, `decisions/`, dan `history/` tidak menjadi owner business rule hanya karena memiliki tanggal atau campaign number yang lebih baru.

## Active Strategy

Current active strategy hanya `weekly_swing`. Strategy lain tidak boleh diperkenalkan sebagai active product scope sebelum ada keputusan eksplisit yang mengubah scope.

## Conflict Resolution

- Governance menentukan cara membaca dan mengubah dokumen.
- Canonical Weekly Swing strategy menang atas implementation/reference/example/fixture.
- Implementation harus diperbaiki bila menyimpang dari strategy tanpa ada approved strategy-change decision.
- Research yang PASS tetap non-canonical sampai ada decision yang mengadopsinya.
- Evidence mencatat fakta, bukan rule.
- Historical/superseded records tidak boleh dipakai sebagai fallback current behavior.

## Upstream Ownership

Watchlist tidak mendefinisikan ulang fakta yang dimiliki `market_data`, termasuk OHLCV, indicators, publication/read model, readiness, corporate-action semantics, atau producer-side temporal correctness. Watchlist hanya memiliki consumer behavior setelah menerima upstream contract yang sah.

## Implementation Boundary

Schema, API, DTO, repository, command, SQL, test, fixture, hash transport, dan artifact format berada pada implementation layer kecuali suatu semantics memang dinyatakan sebagai canonical strategy behavior.

## Change Rule

Perubahan implementation tidak otomatis mengubah strategy. Strategy hanya boleh direvisi melalui material finding + traceable evidence + explicit decision sesuai `DOCUMENT_CHANGE_POLICY.md`.
