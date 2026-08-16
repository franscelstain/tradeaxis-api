# C171 Paramset Optional-Bound Contract — extracted campaign contract

> **Doc Role:** HISTORICAL / RESEARCH ADDENDA
> **Authority:** NON-CANONICAL. Preserved verbatim from the previous mixed document during architecture separation.

## H. C171 Real-IS Remediation Optional-Bound Contract

Catalog `WS_BT_GRID_REAL_IS_REMEDIATION_C171_R1_2026_07` memperluas kontrak
canonical dengan tiga audit object optional:

```text
liquidity.max_dv20_idr
volume.max_vol_ratio
grouping.top_max_score_total
```

Ketiga field bersifat optional hanya untuk menjaga paramset legacy yang sudah
memiliki hash/evidence tetap valid. Setiap row catalog C171-R1 wajib membawa
ketiganya. Saat hadir, field wajib mengikuti audit-object contract penuh dan
menjadi bagian canonical JSON/hash. Omitted legacy value berarti tidak ada upper
bound (`null` pada runtime); runtime tidak boleh menebak nilai dari threshold
`strong`.

Semantics yang dikunci:

- `liquidity.max_dv20_idr`: kandidat ditolak sebelum scoring/grouping jika
  `dv20_idr` melebihi nilai ini;
- `volume.max_vol_ratio`: kandidat ditolak sebelum scoring/grouping jika
  `vol_ratio` melebihi nilai ini;
- `grouping.top_max_score_total`: score di atas cap dilarang masuk TOP_PICKS.
  Daily TOP quantile dihitung dari qualified score pool yang sudah dibatasi cap;
  item score tinggi tetap dapat masuk SECONDARY bila memenuhi cutoff dan slot;
- seluruh checks memakai field decision-time saja dan tidak boleh membaca return
  D+1..D+5, hasil OOS, ticker/sector/month blacklist, atau future-derived route;
- catalog baru atau perubahan nilai menghasilkan paramset/hash baru. Paramset
  lama dan `eval_id=188` tidak boleh diedit.


