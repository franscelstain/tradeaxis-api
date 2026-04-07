# EOD SOURCE OPERATIONAL RESILIENCE CONTRACT (LOCKED)

## Current State
PARTIALLY IMPLEMENTED

---

## Scope
Dokumen ini mengunci perilaku resilience minimum untuk akuisisi source EOD eksternal.
Fokusnya hanya pada source acquisition upstream, bukan realtime/live trading system.

---

## Implemented Behavior in Active Codebase

### Retry
Sudah diimplementasikan pada `PublicApiEodBarsAdapter`.
- retry hanya untuk `RUN_SOURCE_TIMEOUT` dan `RUN_SOURCE_RATE_LIMIT`
- jumlah retry dikendalikan oleh `market_data.provider.api_retry_max`
- retry tidak dipakai untuk auth/config error

### Backoff + throttle
Sudah diimplementasikan pada `PublicApiEodBarsAdapter`.
- backoff bersifat exponential berbasis `market_data.provider.api_backoff_ms`
- throttle + jitter request menggunakan `market_data.provider.api_throttle_qps`
- detail schedule operasional tetap mengacu ke `ops/Performance_SLO_and_Limits_LOCKED.md` sebagai guidance operator, bukan hardcoded contract sequence

### Timeout
Sudah diimplementasikan.
- HTTP request membaca `market_data.source.api.timeout_seconds`
- timeout / HTTP 408 / HTTP 5xx / transport failure diklasifikasikan sebagai `RUN_SOURCE_TIMEOUT`

### Error classification
Sudah diimplementasikan minimal untuk source adapter default.
- `RUN_SOURCE_AUTH_ERROR` untuk 401/403 atau endpoint/auth config yang tidak valid
- `RUN_SOURCE_RATE_LIMIT` untuk 429
- `RUN_SOURCE_TIMEOUT` untuk timeout / transport failure / 408 / 5xx
- `RUN_SOURCE_MALFORMED_PAYLOAD` untuk unexpected non-2xx response atau payload yang tidak bisa dipercaya
- `RUN_SOURCE_RESPONSE_CHANGED` untuk provider payload yang berubah bentuk pada adapter provider-specific path

### Failure event telemetry
Sudah diimplementasikan minimal pada failure path ingest API.
- exhaustion / non-retry source failure membawa `exception_context` ke `eod_run_events.event_payload_json`
- context minimum berisi provider, retry_max, attempt_count, final_reason_code, dan daftar attempt
- setiap attempt menyimpan minimal: `attempt_number`, `reason_code`, `http_status`, `throttle_delay_ms`, `backoff_delay_ms`, dan `will_retry`
- enrichment ini adalah audit trail minimum untuk operator; ini tidak mengubah contract retry/fallback yang sudah ada

### Finalize safety
Sudah ditutup oleh finalize + coverage gate path.
- requested date tidak boleh menjadi `READABLE` bila ingest/source failure membuat coverage gagal atau blocked
- fallback publication lama boleh tetap menjadi effective readable date bila memang valid menurut finalize decision

### Success-after-retry telemetry
Sudah diimplementasikan minimal pada success path ingest API.
- adapter menyimpan ringkasan acquisition terakhir untuk run ingest API
- ringkasan minimum berisi `provider`, `source_name`, `timeout_seconds`, `retry_max`, `attempt_count`, `attempts`, `success_after_retry`, `final_http_status`, `final_reason_code`, dan `captured_at`
- `EodBarsIngestService` meneruskan ringkasan ini ke hasil ingest
- `MarketDataPipelineService` menulis ringkasan tersebut ke payload `STAGE_COMPLETED` dan ke `eod_runs.notes` minimum (`source_provider`, `source_timeout_seconds`, `source_retry_max`, `source_attempt_count`, `source_success_after_retry`, `source_final_http_status`)
- `AbstractMarketDataCommand` sekarang mengekstrak ringkasan minimum itu ke output operator (`source_name`, `source_summary`) agar operator tidak harus membaca `notes` mentah; bila context tersedia, `source_summary` juga harus membawa `provider`, `timeout_seconds`, dan `retry_max`
- `MarketDataEvidenceExportService` sekarang juga menurunkan source context minimum itu ke `run_summary.json`, `evidence_pack.json`, dan ringkasan `market-data:evidence:export` agar proof ops minimum tidak berhenti di notes mentah atau command harian saja
- bila run gagal pada jalur source-acquisition, minimum failure-side source context (`source_name`, `source_provider`, `source_timeout_seconds`, `source_retry_max`, `source_attempt_count`, `source_final_http_status` bila ada, dan `source_final_reason_code`) juga harus ikut dipersist ke `eod_runs.notes` agar operator summary/backfill/evidence export tetap bisa menjelaskan kegagalan tanpa membaca raw event payload saja
- tujuan batch ini tetap audit trail minimum, bukan operator dashboard penuh

### Manual fallback operator path
Sudah diimplementasikan minimum pada command harian utama.
- operator dapat menjalankan `market-data:daily --source_mode=manual_file --input_file=...` saat API mode tidak aman dipakai
- explicit input file `.json` / `.csv` mengoverride lookup direktori default hanya untuk eksekusi command tersebut
- payload event stage ingest dan `eod_runs.notes` sekarang dapat membawa jejak minimum `input_file` / `source_input_file` untuk fallback manual yang dipicu operator
- output command operator sekarang juga menampilkan `source_input_file` bila jejak itu tersedia
- bila `market-data:daily` gagal setelah run failure-side notes sempat dipersist, command harus mencoba merender ulang ringkasan run terakhir untuk tanggal+source tersebut sebelum mengembalikan exit non-zero, agar operator tetap melihat `source_name` / `source_summary` minimum tanpa inspeksi manual ke tabel

### Manual rerun path
Sudah tersedia minimum melalui command pipeline yang sudah ada.
- operator dapat menjalankan ulang `market-data:daily` untuk requested date tertentu
- operator dapat menjalankan `market-data:backfill {start_date} {end_date}` untuk rerun date-range yang mengikuti `market_calendar`
- summary backfill sekarang membawa source context minimum per tanggal (`source_name`, `source_input_file`, `source_summary`) bila run notes memilikinya; untuk API path, `source_summary` harus ikut menurunkan `provider`, `timeout_seconds`, dan `retry_max` bila context itu dipersist
- bila `market-data:backfill` menerima exception setelah run gagal sudah tercatat, summary kasus `ERROR` harus mencoba memuat ulang run terakhir untuk tanggal+source tersebut dan tetap menurunkan `run_id`, `terminal_status`, `publishability_state`, serta source context minimum dari notes yang sudah dipersist
- correction / reseal path tetap memakai command correction yang sudah terpisah bila konteksnya historical correction

---

## Remaining Operational Gaps
Bagian berikut belum boleh dianggap selesai hanya karena adapter retry sudah ada:
- live-source runtime proof di environment nyata
- logging/audit trail saat ini sudah mencakup failure path, success-after-retry minimum, dan ringkasan backfill minimum, tetapi belum punya operator dashboard/export khusus
- failure handling yang lebih granular bila nanti source acquisition dibuat concurrent atau multi-provider
- fallback exercise proof berbasis run nyata, bukan hanya contract/unit path

---

## Test Requirement
Minimal proof yang harus ada:
- retry test untuk rate limit
- retry test untuk timeout/transient failure
- non-retry test untuk auth/config failure
- finalize/fallback safety proof tetap ditutup di integration path publication outcome

---

## Final State
LOCKED
PARTIAL IMPLEMENTATION ACKNOWLEDGED
