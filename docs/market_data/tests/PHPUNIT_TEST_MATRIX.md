# TEST MATRIX COVERAGE GATE

## PASS
- 900/900 → PASS
- 890/900 (>= threshold) → PASS

## FAIL
- 880/900 (< threshold) → FAIL

## PRIORITY FAIL
- overall PASS tapi priority FAIL → FAIL

## NOT EVALUABLE
- expected = 0 → NOT_EVALUABLE

## FINALIZE
- FAIL + fallback → HELD
- FAIL tanpa fallback → NOT_READABLE
- PASS → READABLE
