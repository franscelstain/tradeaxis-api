# WS OOS Evidence — Reference Evidence Note

## Reference status
Dokumen ini adalah catatan referensial untuk membantu pembaca memahami bentuk evidence pack OOS yang biasa dipakai saat review. Dokumen ini bukan owner proof sufficiency, bukan owner acceptance OOS, dan bukan tempat menetapkan syarat bukti final.

## Purpose
Tujuan dokumen ini adalah menjelaskan bentuk evidence yang lazim direview dalam konteks OOS, agar reviewer dan implementer memiliki peta baca yang konsisten. Penetapan bukti yang dianggap cukup tetap mengikuti dokumen normatif bernomor.

## Scope
Dokumen ini dipakai sebagai catatan referensi untuk guard promote dan audit kualitas riset tanpa mengambil alih owner normatif untuk proof sufficiency.

## Inputs
- Evidence OOS yang dikumpulkan dari proses riset/backtest.

## Outputs
- Ringkasan bentuk evidence OOS yang lazim direview secara referensial.

## Reference Inputs
Bagian ini merangkum input yang biasanya dibutuhkan saat menyusun atau membaca evidence OOS. Daftar ini bersifat referensial dan tidak menetapkan syarat final yang mengikat.

- hasil walk-forward atau OOS run,
- parameter set yang dipakai,
- window tanggal atau periode yang diuji,
- ringkasan universe / coverage yang relevan,
- artefak yang menjelaskan hasil utama dan pengecualian penting.

## Reference Artifacts
Bagian ini merangkum artefak yang biasanya muncul dalam review evidence OOS. Owner normatif untuk bukti, proof, dan artifact governance tetap berada pada dokumen bernomor yang relevan.

| Artifact | Kegunaan praktis | Kenapa penting |
|---|---|---|
| summary hasil OOS | membaca hasil tanpa membuka semua artefak | memberi gambaran cepat apakah riset layak diteruskan |
| parameter snapshot | memastikan hasil dibaca dengan konfigurasi yang benar | mencegah salah tafsir antar run |
| coverage / universe note | melihat apakah hasil dicapai pada coverage yang cukup | mencegah klaim performa dari subset sempit |
| daftar exception / anomaly | mencatat kondisi yang perlu dicermati | membantu audit sebelum promote |
| link ke artefak detail | memungkinkan penelusuran ulang | menjaga jejak review tetap auditable |

## Query Reference
Query pada bagian ini adalah contoh acuan yang membantu pembaca memahami cara menelusuri evidence OOS. Query ini tidak boleh diperlakukan sebagai satu-satunya bentuk query resmi bila dokumen normatif atau implementasi menyediakan bentuk yang lain namun tetap patuh kontrak.

### Contoh pertanyaan review
- periode OOS mana yang dipakai untuk hasil ini?
- parameter mana yang aktif pada run tersebut?
- universe atau coverage apa yang mendasari hasil?
- artefak mana yang menjelaskan anomali atau pengecualian penting?

### Contoh bentuk query kerja
- cari artefak berdasarkan `run_id`, `trade_date`, atau nama parameter set,
- tampilkan summary hasil OOS beserta link ke artefak detail,
- filter run yang coverage-nya di bawah batas review internal,
- tarik daftar exception yang perlu dibaca reviewer sebelum promote.

## OOS Summary (Reference)
Ringkasan pada bagian ini membantu pembaca melihat bentuk umum summary OOS yang biasa direview. Ringkasan ini tidak berdiri sendiri sebagai bukti final kelulusan atau kegagalan proof.

### Bentuk summary yang berguna
- identitas run,
- periode atau window OOS,
- parameter set yang dipakai,
- hasil utama yang ingin ditinjau,
- coverage ringkas,
- exception penting,
- pointer ke artefak detail.

### Cara membaca summary
Mulailah dari identitas run dan window, lalu cek parameter set, baru lihat hasil utama. Setelah itu baca coverage dan exception sebelum mengambil kesimpulan. Pola baca ini membantu reviewer menghindari kesimpulan terlalu cepat dari angka headline saja.

## Evidence Attachment Notes
Catatan attachment pada bagian ini membantu pembaca memahami jejak dokumen atau artefak yang biasa dilampirkan. Kewajiban attachment final tetap ditentukan oleh dokumen normatif proof dan governance artefak.

### Lampiran yang biasanya membantu
- summary sheet atau summary markdown,
- file konfigurasi parameter yang dipakai,
- query atau extract yang membuktikan coverage,
- daftar exception atau anomaly note,
- pointer ke artefak detail yang dapat dibuka ulang saat audit.

## Recorded R1 IS Evidence — 2026-06-10

Bagian ini mencatat hasil final supported-runtime dari chronological holdout R1. Catatan ini hanya evidence referensial faktual dan tidak mengubah kontrak normatif acceptance OOS.

### Identitas run

```text
requested range: 2023-01-02 sampai 2026-05-29
split rule: FLOOR_70_PERCENT_IS_REMAINDER_OOS
IS: 2023-01-02 sampai 2025-05-21 / 562 trading dates
reserved OOS: 2025-05-22 sampai 2026-05-29 / 242 trading dates
canonical grid count: 24
eval model: ENTRY=NEXT_OPEN;EXIT=STOP_TP_OR_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS
canonical artifact hash: f4ec8464f08515b31d7d26636851acea930307d6
```

### Hasil

```text
valid IS params: 0
failed IS params: 24
maximum picks count: 1445
maximum days covered: 513
failure classes: robust return, downside, stability
per-evaluation runtime/source diagnostics: tidak ada
best-IS binding: tidak ada
OOS execution: tidak dimulai
OOS acceptance: tidak dievaluasi
promotion eligibility: NOT_ELIGIBLE — OOS proof missing
production ready: false
```

### Pointer evidence

- `storage/app/watchlist/backtest/oos-proof-run-1.json`
- `storage/app/watchlist/backtest/oos-proof-execution-price-corrected-is-failed.json`
- `storage/app/watchlist/backtest/oos-is-evaluation-matrix-execution-corrected.csv`

### Interpretasi review

Infrastruktur proof menyelesaikan seluruh fase IS dan fail closed karena tidak ada row yang lolos semua canonical gate. Ini bukan OOS acceptance failure karena OOS tidak pernah dibaca atau dievaluasi. R1 tetap menjadi immutable failed-IS evidence. Setiap riset R2 wajib memakai catalog/version baru, hanya menggunakan IS, dan mempertahankan acceptance gates serta corrected execution-price semantics yang sama.
