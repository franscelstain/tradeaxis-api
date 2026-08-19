# Weekly Swing Policy / Technical Support Framework

> **Status:** CANONICAL GOVERNANCE
> **Active product scope:** Weekly Swing only

## Purpose

Dokumen ini menjelaskan hubungan antara canonical Weekly Swing strategy dan technical support contracts yang digunakan implementasinya. Dokumen ini **tidak** membuka atau merencanakan multi-strategy product scope.

## Active Authority

Current behavioral owner hanya:
- `../strategy/`.

Canonical end-to-end stage/dependency/handoff owner adalah:
- `../strategy/WS_END_TO_END_STRATEGY_LIFECYCLE.md`.

Technical support Weekly Swing berada di current implementation areas seperti `../../development/implementation/contracts/`, `../../development/implementation/guides/`, dan `../../development/implementation/tests/`. Kata `shared` atau `global` pada legacy filename **tidak memberikan authority untuk memperkenalkan strategy lain**.

## Technical Support Areas

Technical support dapat mencakup:
- paramset shape/provenance mechanics;
- validator mechanics;
- contract-test mechanics;
- runtime artifact immutability mechanics;
- schema parity;
- technical failure-code dictionary.

Semua area tersebut adalah implementation concerns dan harus tunduk pada canonical Weekly Swing strategy bila menyentuh behavior Weekly Swing.

## What Must Remain in Weekly Swing Strategy

Behavior berikut tidak boleh dipindahkan ke implementation/shared sebagai owner:
- candidate eligibility behavior;
- scoring/ranking/grouping semantics;
- recommendation selection/Top Picks behavior;
- CONFIRM semantics;
- entry/exit/risk/horizon strategy;
- calibration acceptance and OOS gates;
- product scope and out-of-scope boundary.

## Conflict Rule

Jika implementation technical contract bertentangan dengan canonical Weekly Swing behavior, canonical strategy menang. Implementation harus diperbaiki kecuali ada material finding + evidence + explicit decision yang sah untuk merevisi strategy.

## Future Scope Guard

Tidak ada strategy selain Weekly Swing yang dianggap active, planned-by-default, atau implied oleh dokumen shared. Penambahan strategy lain adalah perubahan product scope terpisah dan berada di luar baseline ini.
