# Hash Number Formatting Rules (LOCKED)

These rules apply only to hash serialization, not necessarily to storage precision.

These rules are part of the locked reproducibility contract and must stay aligned with schema precision and the indicator oracle/test vectors.

## Fixed formats
- prices (`open`, `high`, `low`, `close`, `previous_close`, `hh20`, `ll20`, `ma20`, `ma50`): 4 decimal places
- `traded_value_idr_actual`, `adv20_traded_value_idr_actual`, and `adv20_close_volume_proxy_idr`: 2 decimal places
- legacy `dv20_idr`, while retained as a declared proxy alias: 2 decimal places
- `atr14`: 10 decimal places
- `atr14_pct`, `vol_ratio`, `roc5`, `roc10`, `roc20`, range/MA distance percentages, and relative-strength fields: 10 decimal places
- price/volume adjustment factors: 12 decimal places or the stricter precision owned by the factor contract
- `coverage_ratio`: 4 decimal places
- integer counts and `volume`: base-10 integer with no separators
- booleans / flags: `0` or `1`
- NULL: empty string
- dates: `YYYY-MM-DD`
- timestamps: `YYYY-MM-DD HH:MM:SS` in platform timezone used by the run
- content hashes: lowercase 64-character hexadecimal
- sets/objects: canonical sorted JSON; numeric values inside JSON use their owned field format

## Examples
- `123.4` => `123.4000`
- `7` in an IDR actual/proxy metric => `7.00`
- NULL => ``

Provider `adj_close` is not part of canonical RAW or analytical artifact number formatting. If retained inside immutable source-observation evidence, it follows the provider-field normalization/version recorded by the observation contract and cannot enter a structural-adjusted vector.

## Locked rule
Locale must never affect formatting. No thousands separator, no scientific notation, no trimmed trailing zeros.

## Cross-contract alignment
This file must remain aligned with:
- `Audit_Hash_and_Reproducibility_Contract_LOCKED.md`
- `../indicators/EOD_Indicators_Formula_Spec.md`
- `../tests/Indicator_Test_Vectors_LOCKED.md`
- `../tests/Indicator_Expected_Output_Oracle_LOCKED.md`
- `../db/Database_Schema_MariaDB.sql`
