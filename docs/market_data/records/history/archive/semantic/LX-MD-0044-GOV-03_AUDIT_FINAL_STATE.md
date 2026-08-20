# Legacy Semantic Extract — LX-MD-0044-GOV-03

- Source ID: `LS-MD-0044`
- Original path: `audit/reports/AUDIT_FINAL_STATE.md`
- Original SHA1: `4F88A2D91FB7215D7D64C2F3ACFED4764964CB20`
- Extract role: `GOVERNANCE`
- Source range: `L298-L408`
- Extract body SHA1: `795518A8E44EC23E5174B9FA427C9D49E3E23A1C`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Strategic invariants to lock in owner documents

### 1. Explicit market scope

- Canonical equity EOD mewakili IDX Regular Market.
- Cash/negotiated market tidak boleh diam-diam tercampur.
- `trade_date`, exchange timezone, session completion, dan board/status semantics harus eksplisit.

### 2. Temporal instrument identity

- Issuer, instrument, listing, dan provider symbol adalah konsep terpisah.
- Universe untuk tanggal T ditentukan berdasarkan state pada T.
- Current `is_active` tidak boleh menghapus saham yang secara historis aktif dari replay/backtest.
- Symbol change, listing, delisting, suspension, relisting, dan board movement harus effective-dated.

### 3. Source strategy

- Yahoo Finance sah sebagai bootstrap primary source sekarang.
- Yahoo tidak disebut official IDX source dan tidak diberi commercial SLA yang tidak ada.
- Provider limitations berhenti di adapter/import strategy.
- Paid-provider selection dan migration bukan current scope.
- Source upgrade kelak dilakukan dengan adapter baru dan publication lineage, bukan rewrite domain.

### 4. Immutable observation and publication history

- Raw observations dan sealed publication tidak boleh diubah in-place.
- Correction harus menghasilkan revision/publication baru.
- Anomaly detection tidak boleh menjadi izin untuk mengubah history.
- Seluruh repair harus fail-safe, traceable, reversible melalui lineage, dan publication-aware.

### 5. Corporate-action correctness

- Price discontinuity hanya menghasilkan anomaly candidate.
- Synthetic price break tidak boleh otomatis menjadi verified corporate action.
- Event membutuhkan source, event identity, type, status, dates, factor, revision, dan verification state.
- Adjustment factor yang sudah dipakai sealed publication tidak boleh dimutasi diam-diam.
- `ex_date` menjadi anchor price continuity dan event-risk ketika tersedia.

### 6. Separate price products

Canonical strategy harus membedakan:

- `RAW`: market-observed OHLCV;
- `STRUCTURAL_ADJUSTED`: coherent OHLC dan volume adjustment untuk split, reverse split, bonus, rights, atau structural action yang disahkan;
- `TOTAL_RETURN`: product terpisah untuk performance evaluation termasuk distribution effects bila datanya tersedia.

Default yang direkomendasikan untuk indicator teknikal Weekly Swing adalah `STRUCTURAL_ADJUSTED`, bukan per-row `adj_close` fallback.

Aturan keras:

- satu indicator run memakai satu basis yang dikunci;
- seluruh OHLC disesuaikan secara coherent;
- volume disesuaikan inversely bila action semantics mengharuskannya;
- tidak ada campuran `adj_close` dan `close` antar tanggal dalam satu vector;
- bila factor penting belum terverifikasi, affected range di-quarantine atau eligibility diblokir.

### 7. Coverage is not eligibility

- Coverage menjawab apakah expected market observations tersedia.
- Quality menjawab apakah observations dapat dipercaya.
- Liquidity menjawab apakah saham layak untuk policy Weekly Swing.
- Eligibility menyatukan facts yang dibutuhkan downstream tanpa menyatakan alpha approval.
- Dormancy dan zero-volume history tidak boleh menyembunyikan provider failure dari denominator coverage.
- Exclusion dari denominator hanya boleh berdasarkan point-in-time evidence bahwa bar memang tidak diharapkan, misalnya verified suspension/market status.

### 8. Exact indicators

- Formula, price basis, seed, warm-up, rounding, nullability, dan version harus terkunci.
- Wilder ATR harus menggunakan recursive state atau historical chain dengan seed stabil.
- Sliding load window tidak boleh diam-diam me-seed ulang ATR setiap run.
- Indicator tidak boleh memakai zero-placeholder atau invalid row sebagai harga nyata.
- Perubahan formula harus membuat indicator/config version baru.

### 9. Honest liquidity fields

- Provider-reported traded value adalah canonical turnover bila tersedia dan tervalidasi.
- `price × volume` hanya boleh diberi nama `turnover_proxy_idr` atau nama lain yang jujur.
- Proxy tidak boleh disebut actual traded value.
- Jika adjusted price dipakai, jangan mengalikannya dengan raw volume untuk mengklaim nominal market turnover.

### 10. Full reproducibility context

Setiap consumer-visible result minimal harus dapat ditelusuri ke:

- requested dan effective trade date;
- run dan publication identity;
- source mode, provider, symbol, dan observed/ingested time;
- raw payload/request identity atau content hash yang aman;
- canonicalization version;
- corporate-action/factor version;
- price-basis version;
- indicator set/version;
- complete output-affecting config hash dan snapshot;
- eligibility/reason-code version.

### 11. Daily operational truth

- Trading calendar menentukan tanggal yang wajib diproses.
- Daily pipeline harus otomatis, idempotent, retryable, dan observable.
- Import sukses bukan publish sukses.
- Late or missing date harus terlihat sebagai incident/degraded state.
- Watchlist tidak boleh mengonsumsi stale publication tanpa explicit freshness state.

### 12. Point-in-time replay

- Replay harus dapat memilih `AS_KNOWN` dan, bila kelak dibutuhkan, `LATEST_RESTATED` view.
- Minimum current requirement adalah survivorship-free universe dan publication-as-known input.
- Backtest tidak boleh melihat current ticker status, correction, corporate action, atau config yang belum diketahui pada tanggal evaluasi.

---


<!-- LEGACY_EXTRACT_BODY_END -->
