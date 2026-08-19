# Weekly Swing Worked Example E2E

> **Current Conformance:** `NOT_ASSESSED_REVALIDATION_REQUIRED`  
> **Verification Epoch:** `WS-REBASELINE-20260819-001`  
> Existing content is a revalidation input. Historical PASS/READY/DONE wording does not grant current conformance.


## Purpose

Dokumen ini menyajikan worked example untuk membantu pembaca memahami core Weekly Swing dan optional non-blocking CONFIRM. Dokumen ini bukan source of truth; canonical strategy selalu menang.

## Owner Reminder

- `WS_RUNTIME_FLOW.md`
- `WS_CANDIDATE_CLASSIFICATION.md`
- `WS_PLAN_SCORING_AND_TRADE_PLAN.md`
- `WS_TOP_PICKS_QUALIFICATION_AND_RANKING.md`
- `WS_D1_CONFIRM_ACTIONABILITY.md`
- `WS_DATA_MODEL_MARIADB.md`
- `WS_CONTRACT_TEST_CHECKLIST.md`

---

## Scenario 1 — Core Weekly Swing Completes Without CONFIRM

### Step 1 — PLAN

Misalkan setelah eligibility/classification hanya AAA, BBB, dan CCC masuk `RECOMMENDATION_CANDIDATES`.

| ticker | score_total | entry reference | risk/exit plan |
|---|---:|---|---|
| AAA | 0.91 | valid | valid |
| BBB | 0.87 | valid | valid |
| CCC | 0.74 | valid | valid |

PLAN dibekukan dan immutable.

### Step 2 — Final RECOMMENDATION / TOP PICKS

Final qualification menghasilkan:

| ticker | qualified | recommendation_rank |
|---|---:|---:|
| AAA | true | 1 |
| BBB | true | 2 |
| CCC | false | — |

Core output untuk trade date tersebut sudah sah:

`TOP PICKS = [AAA, BBB]`

Tidak ada CONFIRM request. Consumer dapat menampilkan:
- AAA — Top Pick #1 — `CONFIRM: NOT_REQUESTED`
- BBB — Top Pick #2 — `CONFIRM: NOT_REQUESTED`

**Rule proven:** PLAN + RECOMMENDATION/Top Picks selesai tanpa CONFIRM artifact atau current-entry data.

---

## Scenario 2 — CONFIRM Data Belum Tersedia, Lalu Datang

AAA adalah final Top Pick #1.

### First Attempt

Pada awal entry window, current snapshot belum lengkap.

Output:

`AAA → UNAVAILABLE_RETRYABLE`

Interpretasi:
- AAA tetap Top Pick #1;
- recommendation tidak gagal;
- tidak boleh mengeluarkan `NOT_ACTIONABLE` karena belum ada data yang cukup;
- retry diperbolehkan selama entry window masih terbuka.

### Later Attempt

Beberapa waktu kemudian valid current snapshot tersedia dan seluruh active gate lulus.

Output:

`AAA → ACTIONABLE`

Recommendation membership/rank AAA tetap sama sebelum dan sesudah CONFIRM.

**Rule proven:** availability dapat berubah menjadi evaluated actionability tanpa membangun ulang recommendation.

---

## Scenario 3 — Valid Data Menunjukkan NOT_ACTIONABLE

BBB adalah final Top Pick #2. Valid current data tersedia, tetapi harga sudah melewati maximum allowed adverse drift dari PLAN entry reference.

Output:

`BBB → NOT_ACTIONABLE`

Interpretasi:
- BBB tetap historical EOD Top Pick #2;
- current-entry decision support menyatakan `DO NOT ENTER NOW`;
- AAA/CCC atau ticker lain tidak otomatis dipromosikan sebagai replacement;
- recommendation ranking tidak ditulis ulang.

**Rule proven:** `NOT_ACTIONABLE` hanya berasal dari valid evaluated data, bukan missing data.

---

## Scenario 4 — CONFIRM Tidak Pernah Tersedia Sampai Entry Window Selesai

AAA adalah Top Pick, tetapi valid current data tidak pernah tersedia sampai canonical entry window berakhir.

Final CONFIRM state:

`AAA → EXPIRED_UNCONFIRMED`

Interpretasi:
- core Weekly Swing run tetap sukses;
- AAA tetap tercatat sebagai EOD Top Pick;
- current actionability tidak pernah terbukti;
- recommendation tidak otomatis dibawa sebagai new-entry signal ke hari berikutnya.

---

## Scenario 5 — No Qualified Top Picks

PLAN dapat terbentuk, tetapi tidak ada candidate yang melewati final recommendation quality floor.

Output:

`NO QUALIFIED TOP PICKS`

CONFIRM tidak dijalankan karena tidak ada final Top Pick.

Ini adalah valid strategy outcome, bukan error.

---

## Final Reminder

Core Weekly Swing adalah:

`Market Data → PLAN → RECOMMENDATION/TOP PICKS`

CONFIRM adalah optional branch:

`TOP PICK → NOT_REQUESTED / UNAVAILABLE_RETRYABLE / ACTIONABLE / NOT_ACTIONABLE / EXPIRED_UNCONFIRMED`

Tidak ada CONFIRM state yang boleh menambah, menghapus, atau mererank EOD Top Picks.
