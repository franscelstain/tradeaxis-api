# Watchlist Evidence

> **Current Verification Epoch:** `WS-REBASELINE-20260819-001`  
> Every pre-epoch result is historical-only for current strategy verification. Current proof requires a registered `WS-Bxx` Work Record + current-epoch Baseline.


> **Physical role:** `docs/watchlist/records/evidence/` — factual proof/results; final evidence immutable.

Hasil aktual: backtest, IS/OOS, runtime, shadow, operator validation, artifacts, baseline lock, stage-attempt evidence, dan ledger. `runs/` menyimpan result/review/operator run evidence termasuk immutable Work Baseline Lock dan final Stage Attempt Record; `artifacts/` bukti data; `backtest/` proof khusus backtest.

## Recording / Mutability Rule

Final evidence adalah **IMMUTABLE_AFTER_ISSUE**.

- jangan mengubah angka/result lama agar cocok dengan pengetahuan terbaru;
- jika evidence salah, buat **evidence correction record baru** yang menunjuk original evidence, menjelaskan corrected value dan impact;
- ledger/status bersifat append-oriented: current summary boleh berubah, historical entry tidak boleh dihapus/ditulis ulang;
- evidence tidak menjadi canonical strategy authority.

Universal rule: [`../../authority/governance/DOCUMENT_RECORDING_STANDARD.md`](../../authority/governance/DOCUMENT_RECORDING_STANDARD.md).

## Work Baseline / Attempt Evidence

- Work Baseline Lock diterbitkan sebelum material implementation attempt sesuai [`../../authority/governance/WORK_BASELINE_LOCK_STANDARD.md`](../../authority/governance/WORK_BASELINE_LOCK_STANDARD.md).
- Final Stage Attempt Record disimpan sebagai immutable evidence di `runs/` menggunakan [`../../development/implementation/examples/WS_STAGE_ATTEMPT_RECORD_TEMPLATE.md`](../../development/implementation/examples/WS_STAGE_ATTEMPT_RECORD_TEMPLATE.md).
- Rerun membuat attempt evidence baru; record attempt lama tidak ditulis ulang.

## Legacy evidence extracts

`LX-*` evidence files are immutable exact section extracts from original composite sources. They remain evidence records but never become current strategy/governance authority.
