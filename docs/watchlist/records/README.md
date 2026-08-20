# Watchlist Records

> **Current Verification Epoch:** `WS-REBASELINE-20260819-001`  
> Pre-epoch evidence/decisions remain immutable historical facts but are **not current implementation/proof evidence**.


`docs/watchlist/records/` menyimpan apa yang **benar-benar terjadi, diputuskan, atau sudah menjadi histori**. Ini bukan working area untuk mengubah current behavior.

## Contents

- [`evidence/`](evidence/README.md) — actual results, validation, baseline/attempt runs, artifacts; final evidence immutable/correction-by-new-record.
- [`decisions/`](decisions/README.md) — issued decisions; perubahan melalui superseding decision.
- [`history/`](history/README.md) — superseded/migration/archive; immutable dan bukan fallback current authority.

Jika sebuah record menunjukkan masalah yang masih harus dikerjakan, arah pekerjaan kembali ke [`../development/`](../development/README.md), sedangkan authority tetap berasal dari [`../authority/`](../authority/README.md).

## Current Work Registry

Current/future `WS-Bxx` record relationships are indexed by [`WORK_RECORD_REGISTRY.csv`](WORK_RECORD_REGISTRY.csv). This is a current index, not an evidence owner and not a replacement for immutable records.
