# Watchlist Development

`docs/watchlist/development/` adalah working area yang aktif berkembang selama pembangunan Weekly Swing. Perubahan tetap harus traceable dan tunduk pada authority.

## Contents

- [`implementation/`](implementation/README.md) — technical translation, build sequence, stage register, contracts, tests, DB, guides, examples.
- [`research/`](research/README.md) — hypothesis/experiment; draft mutable, locked research immutable.
- [`findings/`](findings/README.md) — masalah/insight yang ditemukan; original observation dipertahankan.

## Working Rule

Development **tidak boleh membuat business rule baru**. Jika implementation menemukan masalah strategy/governance yang material, catat finding/evidence lalu gunakan controlled decision path.

Current implementation resume point: [`implementation/WS_IMPLEMENTATION_STAGE_REGISTER.md`](implementation/WS_IMPLEMENTATION_STAGE_REGISTER.md).

## Baseline / attempt control

Formal `WS-Bxx` implementation work dimulai melalui current stage register, immutable Work Baseline Lock, canonical Stage Attempt Record, dan executable integrity preflight. Detail berada di [`implementation/README.md`](implementation/README.md).

