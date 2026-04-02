# DB FIELDS & METADATA

## Fields wajib
- expected_universe_count INT
- available_eod_count INT
- missing_eod_count INT
- coverage_ratio DECIMAL(8,6)
- coverage_gate_status VARCHAR
- coverage_threshold_value DECIMAL(8,6)
- coverage_threshold_mode VARCHAR
- coverage_calibration_version VARCHAR

## Optional
- priority_expected_count INT
- priority_available_count INT
- priority_coverage_ratio DECIMAL(8,6)
- missing_ticker_sample_json JSON

## Penempatan
Disarankan:
- t_eod_runs
- t_eod_publications
