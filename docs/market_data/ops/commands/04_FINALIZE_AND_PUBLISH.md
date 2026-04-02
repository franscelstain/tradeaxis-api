# FINALIZE DAN PUBLISH

## Commands
- `market-data:dataset:seal`
- `market-data:run:finalize`

## Fokus operator
`market-data:run:finalize` adalah surface utama untuk membaca outcome requested date. Karena itu output finalize harus menampilkan coverage secara eksplisit bila telemetry coverage tersedia pada run.

## Surface output coverage minimum
Minimal operator harus bisa langsung membaca:
- `terminal_status`
- `publishability_state`
- `reason_code`
- `coverage_gate_state`
- `coverage_reason_code`
- `coverage_summary`
- `coverage_missing_sample` bila ada

## Contoh outcome held karena coverage gagal
```text
run_id=55
requested_date=2026-03-24
stage=FINALIZE
lifecycle_state=COMPLETED
terminal_status=HELD
publishability_state=NOT_READABLE
coverage_gate_state=FAIL
coverage_reason_code=RUN_COVERAGE_LOW
coverage_summary=available=854/900 | missing=46 | ratio=0.9489 | threshold=0.9800 | basis=ticker_master_active_on_trade_date | contract=coverage_gate_v1
coverage_missing_sample=AALI,ACES,ADRO
reason_code=RUN_COVERAGE_LOW
```

## Interpretasi operator
- `coverage_gate_state=FAIL` + `terminal_status=HELD` berarti run tidak meloloskan requested date, tetapi sistem masih punya fallback readable yang sah.
- `coverage_gate_state=FAIL` + `terminal_status=FAILED` berarti requested date gagal dan fallback readable tidak tersedia.
- `coverage_gate_state=NOT_EVALUABLE` + `coverage_reason_code=RUN_COVERAGE_NOT_EVALUABLE` berarti coverage tidak bermakna untuk dinilai; requested date tetap harus dianggap not readable.
- `coverage_missing_sample` hanya sampel cepat untuk operator, bukan daftar lengkap missing universe.
