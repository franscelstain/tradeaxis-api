# Watchlist Change Impact Declaration Standard

> **Status:** CANONICAL GOVERNANCE
> **Scope:** current/future attempt yang mengubah code, contract, schema semantic, API, persistence, tests/fixtures, Market Data binding, or proof behavior

## 1. Core Rule

Sebelum material implementation change, attempt wajib membuat **Change Impact Declaration** yang menyatakan area yang diperkirakan terdampak. Setelah change, declaration yang sama diverifikasi terhadap actual impact.

Template: [`../../development/implementation/examples/WS_CHANGE_IMPACT_DECLARATION_TEMPLATE.md`](../../development/implementation/examples/WS_CHANGE_IMPACT_DECLARATION_TEMPLATE.md).

## 2. Minimum Impact Surface

- strategy rule IDs;
- build stage;
- implementation modules/services;
- contracts/DTO/API;
- DB/schema/migrations;
- persistence/query behavior;
- tests/fixtures;
- Market Data consumer contract;
- backward compatibility;
- potential residue/reachability;
- proof/evidence that may become stale;
- documentation that must be updated.

## 3. Non-material Changes

Pure formatting/comment-only change dapat ditandai `NON_MATERIAL` dengan reason. Jangan memakai label non-material untuk menghindari traceability/test/residue gate.

## 4. Pre/Post Comparison

Attempt closure wajib mencatat:

- planned impact;
- actual impact;
- unexpected impact;
- additional residue discovered;
- strategy/governance drift if any.

Unexpected material impact yang belum dievaluasi memblokir closure.

## 5. Storage / Registry

Draft declaration may live in the implementation working area while attempt is active. Verified/final declaration is issued as immutable attempt evidence under `records/evidence/runs/` and registered in `records/WORK_RECORD_REGISTRY.csv`.
