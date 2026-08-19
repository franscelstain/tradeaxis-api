# F-WS-20260818-05 — Root Documentation Architecture Ambiguity

- **Document Type:** FINDING
- **Status:** RESOLVED
- **Scope:** watchlist / weekly_swing documentation architecture
- **Record ID:** F-WS-20260818-05
- **Created:** 2026-08-18

## Original Observation

`docs/watchlist/` menampilkan `../../authority/strategy`, `../../authority/governance`, `../implementation`, `../research`, `.`, `../../records/evidence`, `../../records/decisions`, dan `../../records/history` sebagai sibling yang secara visual terlihat setara. Struktur ini benar secara kategori tetapi tidak langsung menjelaskan kepada programmer baru mana current authority yang harus dipatuhi, mana working area yang aktif berubah selama implementation, dan mana immutable/append-oriented records.

## Impact

- programmer dapat menganggap strategy/governance sebagai working documents biasa;
- evidence/decision/history dapat terlihat setara dengan area kerja aktif;
- root navigation membutuhkan pembacaan README sebelum sifat mutability terlihat;
- semakin banyak document type, semakin besar risiko file baru ditempatkan pada layer yang salah.

## Finding

Root Watchlist perlu mengelompokkan dokumen berdasarkan fungsi permanen tanpa menambah kedalaman berlebihan:

- `authority/` — strategy + governance;
- `development/` — implementation + research + findings;
- `records/` — evidence + decisions + history.

Pengelompokan harus bersifat navigational/architectural saja dan tidak mengubah canonical Weekly Swing behavior.

## Resolution

Accepted by `D-WS-20260818-05`; root architecture direorganisasi dan seluruh current references divalidasi ulang.
