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

### Finalize safety
Sudah ditutup oleh finalize + coverage gate path.
- requested date tidak boleh menjadi `READABLE` bila ingest/source failure membuat coverage gagal atau blocked
- fallback publication lama boleh tetap menjadi effective readable date bila memang valid menurut finalize decision

### Manual rerun path
Sudah tersedia melalui command pipeline yang sudah ada.
- operator dapat menjalankan ulang `market-data:daily` untuk requested date tertentu
- correction / reseal path tetap memakai command correction yang sudah terpisah bila konteksnya historical correction

---

## Remaining Operational Gaps
Bagian berikut belum boleh dianggap selesai hanya karena adapter retry sudah ada:
- live-source runtime proof di environment nyata
- operator-visible logging/audit trail untuk attempt/backoff yang lebih kaya
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
