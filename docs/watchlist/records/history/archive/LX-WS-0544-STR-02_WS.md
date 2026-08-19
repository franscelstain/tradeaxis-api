# Legacy Role Extract — WS — STRATEGY

> **Document Type:** STRATEGY
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0544-STR-02`
> **Legacy Source ID:** `LS-WS-0544`
> **Legacy Work Key:** `WS`
> **Original Path:** `docs/watchlist/system/policies/weekly_swing/06_WS_PARAMSET_VALIDATOR_SPEC.md`
> **Original SHA1:** `A72760D7ECCEDB8ED10511710BEFB8F603BC75D6`
> **Source Sections:** L3-L6 Purpose; L7-L10 Scope; L11-L14 Ownership Rule; L15-L28 A. Required Top-Level Presence; L29-L42 B. Unknown Root Key Rejection; L43-L62 C. Audit Object Completeness; L63-L76 D. Type Validation; L77-L90 E. Origin Enum Validation; L91-L107 F. Hash Contract Lock Validation; L108-L122 G. Canonical Sort-Key Validation; L123-L130 H. Contract Cohesion Rule; L145-L148 Final Validator Rule; L149-L165 J. R2 Entry-Quality Catalog Validation; L166-L195 K. C171-R1 Optional Upper-Bound Validation
> **Extract Body SHA1:** `9FDBB912D6A065CDC9EE93CB8DD2C180255E3962`
> **Current Authority:** NO

The body below is an exact copy of the identified original sections. No semantic rewriting was applied.

---

## Purpose

Dokumen ini adalah owner normatif untuk penentuan valid atau tidak validnya paramset Weekly Swing.

## Scope

Dokumen ini mengunci failure conditions dan expected validator outcomes untuk kontrak paramset aktif Weekly Swing.

## Ownership Rule

Fixtures negatif hanya membuktikan rule yang ditetapkan di dokumen ini. `_refs` tidak boleh lebih authoritative daripada dokumen ini.

## A. Required Top-Level Presence

### Rule
Paramset wajib memiliki seluruh required top-level keys yang ditetapkan di `04_WS_PARAMSET_JSON_CONTRACT.md`.

### Failure Condition
Jika satu required top-level key hilang, validator wajib fail.

### Concrete Fixture Mapping
- `fixtures/paramset_missing_required_key.json` menghilangkan top-level key `risk` dan harus gagal.

### Expected Result
Paramset dengan top-level required key yang hilang tidak boleh dianggap valid.

## B. Unknown Root Key Rejection

### Rule
Unknown root key dilarang.

### Failure Condition
Jika terdapat key root yang tidak termasuk kontrak aktif Weekly Swing, validator wajib fail.

### Concrete Fixture Mapping
- `fixtures/paramset_unknown_key.json` menambahkan `unknown_root_key` dan harus gagal.

### Expected Result
Validator menolak payload dengan unknown root key.

## C. Audit Object Completeness

### Rule
Setiap audit object wajib memiliki field:
- `value`
- `origin`
- `status`
- `bt_target`
- `rationale`
- `change_triggers`

### Failure Condition
Jika salah satu field audit wajib hilang, validator wajib fail.

### Concrete Fixture Mapping
- `fixtures/paramset_missing_audit_field.json` menghilangkan `liquidity.min_dv20_idr.rationale` dan harus gagal.

### Expected Result
Audit object yang tidak lengkap dianggap invalid.

## D. Type Validation

### Rule
Leaf value harus mengikuti type contract Weekly Swing.

### Failure Condition
String numerik, object yang salah bentuk, atau type drift lain pada leaf aktif menyebabkan validator fail.

### Concrete Fixture Mapping
- `fixtures/paramset_type_drift.json` mengubah `liquidity.min_dv20_idr.value` dari numerik menjadi string numerik dan harus gagal.

### Expected Result
Validator menolak type drift walaupun nilai string tampak dapat di-cast.

## E. Origin Enum Validation

### Rule
`origin` wajib memakai enum yang diizinkan kontrak Weekly Swing.

### Failure Condition
Nilai enum di luar set yang diizinkan menyebabkan validator fail.

### Concrete Fixture Mapping
- `fixtures/paramset_bad_enum.json` mengubah `liquidity.min_dv20_idr.origin` menjadi `NOT_A_VALID_ORIGIN` dan harus gagal.

### Expected Result
Validator menolak origin yang tidak terdaftar pada kontrak aktif.

## F. Hash Contract Lock Validation

### Rule
Hash contract pada paramset aktif wajib cocok dengan contract owner untuk:
- `order_by`
- `null_handling`
- `scales`

### Failure Condition
Perubahan pada nilai locked hash contract menyebabkan validator fail.

### Concrete Fixture Mapping
- `fixtures/paramset_bad_hash_contract.json` mengubah `hash_contract.null_handling.value` menjadi `INCLUDE_IN_HASH_PAYLOAD` dan harus gagal.

### Expected Result
Validator menolak drift pada hash contract yang locked.

## G. Canonical Sort-Key Validation

### Rule
`grouping.sort_keys.value` wajib mengikuti urutan canonical Weekly Swing:

- `score_total_desc`
- `score_breakout_desc`
- `score_momentum_desc`
- `dv20_idr_desc`
- `atr14_pct_asc`
- `ticker_id_asc`

### Failure Condition
Perubahan order atau isi sort keys menyebabkan validator fail, kecuali ownership normatifnya diubah secara eksplisit.

## H. Contract Cohesion Rule

### Rule
Validator tidak boleh menerima payload yang shape-nya lolos secara teknis tetapi melanggar fixed-value contract atau audit-object completeness.

### Expected Result
Payload hanya valid jika lolos seluruh rule shape, type, audit, enum, unknown-key, dan locked-contract validation yang relevan.

## Final Validator Rule

Tidak ada invalid state Weekly Swing yang boleh hidup hanya di fixtures atau di file referensial tanpa rule normatif yang dapat ditelusuri ke dokumen ini.

## J. R2 Entry-Quality Catalog Validation

For catalog `WS_BT_GRID_ENTRY_QUALITY_R2_2026_06`, validation must fail closed unless all rules below pass:

```text
liquidity.min_dv20_idr >= 0
liquidity.dv20_strong_idr >= liquidity.min_dv20_idr
volume.min_vol_ratio >= 0
volume.strong_vol_ratio >= volume.min_vol_ratio
risk.min_atr14_pct <= risk.atr_ideal_low <= risk.atr_ideal_high <= risk.max_atr14_pct
setup.roc_lo < setup.roc_hi
0 <= grouping.secondary_min_score_q <= grouping.top_min_score_q <= 1
sum(scoring.weights.value.*) = 1
```

Every persisted R2 field is required, numeric in its declared unit, and mapped explicitly into the runtime paramset. Missing fields, duplicate canonical parameter combinations, invalid catalog identity/hash, or a catalog row that differs from an already persisted immutable row must fail closed. No silent fallback is permitted for an R2 field.

## K. C171-R1 Optional Upper-Bound Validation

Legacy paramsets remain valid when the three C171 fields are absent. For catalog
`WS_BT_GRID_REAL_IS_REMEDIATION_C171_R1_2026_07`, all three fields are required
by catalog/factory/binding validation and must be hashed as canonical audit
objects.

```text
liquidity.min_dv20_idr <= liquidity.dv20_strong_idr <= liquidity.max_dv20_idr
volume.min_vol_ratio <= volume.strong_vol_ratio <= volume.max_vol_ratio
0 <= grouping.top_max_score_total <= 1
```

Failure conditions:

- present optional field is not a complete audit object;
- numeric value is encoded as a string;
- max DV20 is lower than the minimum or strong threshold;
- max volume ratio is lower than the minimum, or lower than the strong threshold
  for a C171-R1 catalog row;
- TOP maximum score is outside `0..1`;
- persisted grid value and canonical paramset value differ;
- any of the five immutable catalog rows has a duplicate parameter combination,
  wrong row/catalog hash, or changed payload.

Runtime semantics must also be tested: upper liquidity/volume rejects happen
before scoring, and TOP cap is applied before daily TOP quantile calculation.
No validator or adapter may silently reuse `dv20_strong_idr` or
`strong_vol_ratio` as a maximum for legacy payloads.
