# DAILY PIPELINE

## Command
market-data:daily

## Signature
php artisan market-data:daily   {--requested_date=}   {--source_mode=}   {--correction_id=}   {--latest}

## Fungsi
Menjalankan seluruh pipeline EOD dalam satu entry point: ingest, indicators, eligibility, hash, seal, lalu finalize.

## Opsi
- `--requested_date=`
- `--source_mode=`
- `--correction_id=`
- `--latest`

## Surface output operator minimum
Command ini wajib menampilkan ringkasan run dan coverage gate bila field coverage sudah tersedia pada `eod_runs`.

Field minimum yang harus terlihat bila coverage tersedia:
- `coverage_gate_state`
- `coverage_reason_code`
- `coverage_summary`
- `coverage_missing_sample` bila sampel ticker missing tersedia

## Contoh output penting
```text
run_id=55
requested_date=2026-03-24
stage=FINALIZE
lifecycle_state=COMPLETED
terminal_status=SUCCESS
publishability_state=READABLE
coverage_gate_state=PASS
coverage_reason_code=COVERAGE_THRESHOLD_MET
coverage_summary=available=900/900 | missing=0 | ratio=1.0000 | threshold=0.9800 | basis=ticker_master_active_on_trade_date | contract=coverage_gate_v1
```

## Interpretasi operator
- `coverage_gate_state=PASS` berarti coverage lolos, tetapi operator tetap harus membaca `terminal_status` dan `publishability_state` untuk outcome final.
- `coverage_gate_state=FAIL` berarti requested date tidak boleh dianggap readable, walaupun fallback publication mungkin masih membuat run berakhir `HELD` alih-alih `FAILED`.
- `coverage_reason_code` adalah shortcut yang menjelaskan outcome coverage tanpa operator harus menebak dari angka rasio.
