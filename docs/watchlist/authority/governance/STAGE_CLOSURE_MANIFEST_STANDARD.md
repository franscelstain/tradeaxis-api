# Watchlist Stage Closure Manifest Standard

> **Status:** CANONICAL GOVERNANCE
> **Scope:** setiap `WS-Bxx` atau declared successor/decomposition stage yang mencapai terminal lifecycle state

## 1. Purpose

Stage terminal harus memiliki **satu immutable closure manifest** yang menjawab mengapa stage berakhir tanpa memaksa pembaca menggabungkan banyak evidence secara manual.

Template: [`../../development/implementation/examples/WS_STAGE_CLOSURE_MANIFEST_TEMPLATE.md`](../../development/implementation/examples/WS_STAGE_CLOSURE_MANIFEST_TEMPLATE.md).

## 2. When Required

Closure manifest wajib untuk terminal state:

- `DONE`
- `CLOSED_UNRESOLVED_WITH_EVIDENCE`
- `SUPERSEDED_BY_SUCCESSOR`
- `SUPERSEDED_BY_DECOMPOSITION`

Active state tidak boleh mempunyai final closure manifest.

## 3. Mandatory Content

Manifest minimum mencatat:

- Stage ID;
- Closure Record ID;
- final Attempt/Work ID;
- Baseline ID;
- declared objective dan exit criteria;
- strategy coverage summary;
- functional/negative test evidence;
- residue verdict/evidence;
- documentation + relationship integrity verdict;
- open/closed findings;
- dependency disposition;
- evaluation/proof verdict bila berbeda dari lifecycle state;
- terminal lifecycle state;
- supporting evidence yang juga diregister sebagai `related_evidence_ids` pada closure Work Record;
- required reviewed decision untuk non-`DONE` terminal closure;
- successor/next stage;
- remaining known risk.

## 4. `DONE` Rule

Closure manifest tidak menciptakan `DONE`. Ia hanya merangkum bukti bahwa objective/exit criteria memang telah terpenuhi sesuai stage standard.

Implementation stage `DONE` tetap memerlukan:

- required strategy coverage complete;
- tests/negative paths complete;
- no unresolved harmful residue;
- baseline binding valid;
- integrity gates pass;
- no unresolved required dependency.

## 5. Non-DONE Terminal Rule

`CLOSED_UNRESOLVED_WITH_EVIDENCE`, `SUPERSEDED_BY_SUCCESSOR`, dan `SUPERSEDED_BY_DECOMPOSITION` wajib menunjuk reviewed decision. Repeated failure, elapsed time, atau fatigue tidak cukup.

## 6. Storage / Mutability

Final closure manifest adalah **EVIDENCE / IMMUTABLE_AFTER_ISSUE** dan normalnya disimpan di `records/evidence/runs/`.

Stage Register hanya menyimpan Closure Record ID + summary state.

## 7. Baseline and Relationship Binding

Closure Record ID current wajib correlation-first: `SC-WS-Bxx-Ayyy-NNN`. Closure row pada Work Record Registry wajib memakai final Stage ID + Attempt ID/Work ID + Baseline ID yang sama dengan Stage Register.

Semua **closure-critical current evidence** wajib tercantum pada closure `related_evidence_ids` dan pada bagian `Supporting Records / Evidence` di manifest. Keduanya harus match.

Default rule: closure-critical evidence harus berasal dari Baseline ID final Attempt yang sama. Evidence current dari baseline/attempt lain hanya boleh digunakan jika:

1. explicit row `CROSS_BASELINE_CLOSURE_EVIDENCE` ada di `WORK_RELATIONSHIP_REGISTRY.csv`;
2. justification non-empty;
3. relationship menunjuk reviewed Decision yang nyata;
4. Decision tersebut juga tercantum pada closure `related_decision_ids`;
5. evidence target tetap immutable dan registered.

Historical/legacy evidence tanpa current Baseline ID boleh disebut sebagai context, tetapi tidak boleh mengisi closure-critical `related_evidence_ids` atau menggantikan current baseline evidence.
