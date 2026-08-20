# Watchlist Implementation Audit Example

## Context

Contoh ini menunjukkan bentuk hasil audit implementasi Watchlist terhadap current Weekly Swing strategy. Dokumen ini bukan owner checklist; owner utama tetap `WATCHLIST_IMPLEMENTATION_CHECKLIST_FINAL.md` dan canonical strategy.

Current CONFIRM semantics mengikuti `../../strategy/WS_D1_CONFIRM_ACTIONABILITY.md` dan `../../../development/implementation/CONFIRM_OPTIONAL_NON_BLOCKING_IMPLEMENTATION_GUARD.md`: CONFIRM hanya final Top Picks, optional, dan non-blocking.

## Example A — Guidance Package Review

### PASS
- PLAN dan RECOMMENDATION/Top Picks dipisah jelas.
- Recommendation dibentuk dari PLAN immutable.
- Core runtime selesai tanpa CONFIRM artifact.
- CONFIRM eligibility berasal dari final Top Picks.
- Missing decision-time data menghasilkan `UNAVAILABLE_RETRYABLE`.
- API tidak menyiratkan buy/sell execution.

### FAIL Pattern
- Guidance membuat CONFIRM prerequisite untuk menyimpan atau mempublikasikan Top Picks.
- Guidance memakai PLAN candidate membership sebagai CONFIRM eligibility tanpa final recommendation membership.
- Missing/stale CONFIRM snapshot dipetakan menjadi `NOT_ACTIONABLE`.

## Example B — Code Review Finding Pattern

### PASS
- PLAN dan RECOMMENDATION dapat selesai ketika CONFIRM source belum tersedia.
- Empty recommendation tetap valid.
- CONFIRM hanya menerima ticker final Top Picks.
- `UNAVAILABLE_RETRYABLE` dapat dievaluasi ulang selama entry window.

### FAIL
- Satu service menganggap tidak adanya current snapshot sebagai whole-run failure.
- Satu service mengizinkan non-recommended PLAN candidate menjadi CONFIRM target.
- Satu serializer mengubah recommendation membership/rank berdasarkan CONFIRM.

### Patch Direction
1. pertahankan core completion pada PLAN + RECOMMENDATION/Top Picks;
2. pindahkan CONFIRM eligibility ke final Top Picks binding;
3. pisahkan availability state dari negative actionability decision;
4. pastikan retry tidak memutasi historical EOD recommendation.

## Example C — API / Persistence Review

### PASS
- Core response valid tanpa CONFIRM section.
- CONFIRM section/artifact conditional pada request/attempt/result.
- `NOT_ACTIONABLE` hanya muncul dari valid evaluated decision-time data.
- Technical CONFIRM error tidak menghapus atau mererank Top Picks.

### FAIL
- Database constraint mewajibkan CONFIRM row untuk setiap recommendation.
- API mengembalikan core failure hanya karena CONFIRM provider tidak memberi data.
- Persistence menyimpan synthetic `NOT_ACTIONABLE` ketika snapshot belum tersedia.

## Example D — Forward Proof Review

### PASS
- Core forward shadow menguji EOD final Top Picks independen dari CONFIRM availability.
- Optional CONFIRM observations hanya memakai valid causal decision-time data.
- Sample CONFIRM tidak cukup menghasilkan `CONFIRM_EVIDENCE_INSUFFICIENT`, bukan core shadow FAIL.

### FAIL
- Core production-use review diblokir hanya karena tidak ada cukup CONFIRM observations.
- Historical backtest memfabricate synthetic D+1 CONFIRM dari data yang tidak tersedia pada decision time.
