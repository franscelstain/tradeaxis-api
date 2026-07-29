# Weekly Swing New Strategy R01 Research Hypothesis Note

## Status

```text
SCOPE=WS_NEW_STRATEGY_R01
SCOPE_TYPE=SEPARATE_NEW_STRATEGY_RESEARCH
C171_STATUS=CLOSED
C171_MORE_REMEDIATION_ALLOWED=0
C172_OOS_ALLOWED=0
PRODUCTION_READY=0
```

Dokumen ini adalah catatan referensi riset. Dokumen ini tidak mengubah owner
canonical pada file 12, 16, 17, dan 20.

## Kesesuaian dengan owner contract

R01 mengikuti kontrak Weekly Swing karena:

- memakai window IS canonical `2023-01-02` sampai `2025-05-21`;
- memakai evidence anchor immutable `eval_id=204` / `param_set_id=11`;
- memverifikasi manifest official evidence sebelum analisis;
- membaca indikator equity dari immutable signal-publication history;
- memakai IHSG hanya pada signal date;
- memakai return, gap, dan exit outcome hanya untuk diagnosis setelah selection;
- tidak membaca OOS;
- tidak membuat atau mengubah paramset;
- tidak menjalankan official IS;
- tidak mengubah canonical gate;
- tidak membuat PLAN, RECOMMENDATION, atau CONFIRM.

## Tiga hipotesis praregistrasi

R01 membatasi penelitian pada tiga hipotesis:

1. `H1_BREAKOUT_QUALITY_CONFIRMATION`
   - field decision-time: `close_to_hh20_pct`, `range_position_20_pct`,
     `vol_ratio`;
   - tujuan: membedakan breakout berkualitas dari breakout jauh/extended.
2. `H2_MOMENTUM_PERSISTENCE`
   - field decision-time: `roc5`, `roc10`, `roc20`, `ma20_slope_pct`,
     `rs_20_vs_ihsg`;
   - tujuan: membedakan momentum persisten dari lonjakan sesaat atau momentum
     yang mendingin.
3. `H3_MARKET_REGIME_COMPATIBILITY`
   - field decision-time: `market_index_roc20`,
     `market_index_ma20_slope_pct`;
   - tujuan: menilai kompatibilitas strategi terhadap kondisi IHSG pada waktu
     keputusan.

Hipotesis praregistrasi tidak sama dengan aturan kandidat final. Threshold
kandidat harus ditetapkan eksplisit sebelum official IS dan tidak boleh dipilih
dari OOS.

## Diagnostic evidence

R01 wajib menganalisis:

- winning trade versus losing/flat trade;
- gap versus non-gap;
- saham harga rendah versus harga menengah/tinggi;
- tick-risk decision-time;
- breakout extension;
- ROC5/ROC10/ROC20 persistence;
- volume ratio;
- ATR;
- jarak close terhadap HH20;
- kondisi IHSG pada signal date;
- bulan dan tahun;
- exit reason sebagai post-selection attribution.

Return, actual next-open gap, fill rule, dan exit reason tidak boleh menjadi
selection input kandidat berikutnya.

## Advancement rule

```text
IF_R01_SUPPORTED_HYPOTHESIS_COUNT_GT_0:
  NEXT=WS_NEW_STRATEGY_R02_IMPLEMENT_MINIMAL_ONE_IDEA_CANDIDATES

IF_R01_SUPPORTED_HYPOTHESIS_COUNT_EQ_0:
  NEXT=WS_NEW_STRATEGY_R02_EXPAND_DECISION_TIME_DIAGNOSTIC_EVIDENCE

MAX_HYPOTHESES=3
MAX_FUTURE_CANDIDATES=3
MAX_REMEDIATION_ROUNDS=1
OOS_BEFORE_CANONICAL_IS_PASS=FORBIDDEN
```

Setiap kandidat R02 hanya boleh membawa satu ide utama. R01 sendiri tidak
memberi izin membuat DRAFT, menjalankan official IS, atau membuka OOS.
