# EOD COVERAGE GATE CONTRACT (LOCKED)

## Tujuan
Menentukan apakah dataset EOD layak dinyatakan READABLE berdasarkan coverage terhadap universe ticker valid.

## Definisi Universe
Ticker valid jika:
- is_active = Yes
- listed_date <= trade_date
- delisted_date IS NULL OR delisted_date >= trade_date

## Rumus
coverage_ratio = available_eod_count / expected_universe_count

## Valid EOD
Ticker dianggap available jika:
- OHLCV lengkap & valid
- lolos canonicalization

## Status Gate
- PASS
- FAIL
- NOT_EVALUABLE

## Outcome
- PASS + gate lain PASS → READABLE
- FAIL + fallback ada → HELD
- FAIL + tidak ada fallback → NOT_READABLE

## Threshold
- coverage_threshold_value (explicit)
- coverage_threshold_mode:
  - PROVISIONAL_BOOTSTRAP
  - CALIBRATED_LOCKED

## Larangan
- tidak boleh implicit threshold
- tidak boleh publish jika coverage FAIL
