# Watchlist Stage Execution, Re-entry, Remediation, and Closure Standard

> **Status:** CANONICAL GOVERNANCE  
> **Scope:** current Weekly Swing implementation stages (`WS-Bxx`) and any formally declared successor/decomposition stage  
> **Purpose:** memastikan stage yang sedang dikerjakan tidak kehilangan histori, tidak mengulang kegagalan yang sama, tidak dibiarkan menggantung tanpa arah, dan tidak ditutup hanya karena percobaan berulang gagal.

## 1. Core Principle

Setiap stage yang **sudah dimulai** harus selalu mempunyai keadaan yang dapat dijelaskan dengan bukti:

- apa objective stage;
- apa yang sudah dicoba;
- apa hasil attempt terakhir;
- apakah masalah mengerucut atau tidak;
- apa blocker/dependency yang terverifikasi;
- apa remediation berikutnya;
- dari titik mana pekerjaan harus dilanjutkan;
- kapan stage sah dinyatakan selesai atau ditutup.

Tidak boleh ada stage aktif yang hanya berstatus `PARTIAL`, `BLOCKED`, `FAILED`, atau `TBD` tanpa alasan, evidence, owner, dan next action yang pasti.

**Failure count is never a closure criterion.**  
Jumlah percobaan, lamanya waktu, rasa lelah, atau pendapat pribadi tidak pernah cukup untuk menutup stage.

---

## 2. Separate Three Things: Stage State, Stage Completion, and Evaluation Verdict

Tiga hal ini **tidak boleh dicampur**.

### A. Stage lifecycle state

Menjawab: *pekerjaan stage sedang berada di mana?*

Active states:

- `NOT_STARTED`
- `IN_PROGRESS`
- `REMEDIATION_IN_PROGRESS`
- `WAITING_VERIFIED_DEPENDENCY`
- `VALIDATION`

Optional branch state:

- `NOT_REQUESTED_OPTIONAL` — hanya untuk capability yang memang optional, seperti `WS-B07 CONFIRM`.

Terminal states:

- `DONE`
- `CLOSED_UNRESOLVED_WITH_EVIDENCE`
- `SUPERSEDED_BY_SUCCESSOR`
- `SUPERSEDED_BY_DECOMPOSITION`

### B. Stage completion meaning

`DONE` hanya sah bila **objective dan exit criteria stage benar-benar tercapai**.

`DONE` tidak boleh dipakai sebagai sinonim untuk:

- sudah dicoba berkali-kali;
- tidak tahu mau mencoba apa lagi;
- deadline habis;
- dependency belum tersedia;
- test masih gagal;
- “cukup untuk sekarang”.

### C. Evaluation/proof verdict

Stage evaluasi dapat selesai dengan verdict negatif **tanpa berarti stage execution gagal**.

Contoh:

```text
WS-B10 objective:
jalankan untouched OOS secara sah dan hasilkan verdict exact frozen identity.

Stage state = DONE
OOS verdict = FAIL
Production proof path = STOP
```

Ini sah karena objective WS-B10 adalah menghasilkan **valid OOS verdict**, bukan memaksa strategi lulus OOS.

Sebaliknya:

```text
WS-B03 objective:
implement deterministic eligibility/classification conformant.

test masih gagal
```

WS-B03 **belum DONE**. Ia tetap `REMEDIATION_IN_PROGRESS` atau state aktif lain yang sesuai.

---

## 3. Attempt Lifecycle

Satu stage boleh mempunyai banyak attempt. **Setiap attempt wajib ditutup**, walaupun stage masih aktif.

Attempt minimum record:

```text
Attempt ID:
Stage ID:
Started:
Closed:
Objective / hypothesis for this attempt:
Change versus previous attempt:
Commands/tests/run performed:
Evidence:
Attempt outcome:
Convergence:
Root-cause state:
What was learned:
Do not repeat:
Remaining gap:
Next action:
Resume from:
```

Allowed `Attempt outcome`:

- `PASS`
- `FAILED`
- `PARTIAL_RESULT`
- `DEPENDENCY_MISSING`
- `INCONCLUSIVE`

Allowed `Convergence`:

- `IMPROVING`
- `STABLE`
- `REGRESSING`
- `INCONCLUSIVE`

`ROOT_CAUSE_ISOLATED` boleh dicatat sebagai root-cause state, tetapi bukan alasan otomatis menutup stage.

Attempt final evidence berada di `evidence/` dan menjadi immutable setelah issued. Stage register hanya menyimpan pointer/ringkasannya.

---

## 4. Mandatory Re-entry Protocol

Jika stage pernah mempunyai attempt sebelumnya, atau current state bukan `NOT_STARTED`, pekerjaan **tidak boleh langsung dimulai dari code**.

Wajib membaca, dalam urutan ini:

1. current canonical strategy owner untuk stage;
2. current implementation contract/guard yang berlaku;
3. [`../implementation/WS_IMPLEMENTATION_STAGE_REGISTER.md`](../implementation/WS_IMPLEMENTATION_STAGE_REGISTER.md);
4. latest closed attempt evidence;
5. semua **OPEN/ACCEPTED unresolved findings** yang terkait langsung;
6. current decision/remediation yang masih berlaku;
7. `DOCUMENT_CHANGE_LOG.md` sejak attempt terakhir;
8. historical/superseded record **hanya jika direferensikan** oleh evidence/finding/decision current.

Sebelum attempt baru dibuka, re-entry summary harus dapat menjawab:

```text
Current objective:
Current unresolved gap:
Latest known root cause:
Latest convergence:
Verified dependency if any:
Prior attempts that must not be repeated:
New hypothesis / materially different action:
Resume point:
Evidence required to close this attempt:
```

Jika jawaban ini tidak dapat dibuat, stage belum siap untuk rerun; lakukan diagnostic/read-only review terlebih dahulu.

---

## 5. Convergence Rule — Failure Can Be Positive Information

Stage yang terus menghasilkan **material diagnostic convergence** wajib tetap terbuka untuk remediation.

Contoh convergence positif:

- suspect set berkurang;
- failing gate berkurang atau menjadi lebih spesifik;
- root cause berpindah dari symptom umum ke dependency/logic tertentu;
- failure berhasil direproduksi secara deterministic;
- satu hipotesis dieliminasi dengan evidence;
- required missing fact berhasil diidentifikasi secara pasti;
- remediation berikutnya mempunyai testable hypothesis yang lebih sempit.

Selama `Convergence = IMPROVING` dan masih ada reasonable remediation yang belum diuji, `CLOSED_UNRESOLVED_WITH_EVIDENCE` **dilarang**.

Repeated failure tanpa pembelajaran baru harus ditandai `STABLE`, `REGRESSING`, atau `INCONCLUSIVE`, lalu dievaluasi apakah pendekatan perlu diganti—bukan langsung ditutup.

---

## 6. Verified Dependency Rule

Jika stage tidak dapat bergerak karena dependency yang benar-benar berada di luar stage:

```text
Stage state = WAITING_VERIFIED_DEPENDENCY
```

Wajib dicatat:

- dependency yang dibutuhkan;
- owner/dependency domain;
- evidence bahwa dependency memang belum tersedia;
- mengapa local workaround/fallback dilarang atau tidak sah;
- resume trigger yang objektif;
- titik resume setelah dependency terpenuhi.

`WAITING_VERIFIED_DEPENDENCY` adalah **active state**, bukan terminal closure.

Tidak boleh menutup stage hanya karena dependency belum tersedia bila dependency tersebut masih mempunyai jalur penyelesaian yang nyata.

---

## 7. High Burden of Proof for Terminal Unresolved Closure

`CLOSED_UNRESOLVED_WITH_EVIDENCE` hanya boleh digunakan jika objective stage tidak tercapai dan ada bukti kuat bahwa stage **tidak dapat diselesaikan secara sah dalam current approach/scope**.

Minimum mandatory closure packet:

1. root cause atau terminal constraint telah diisolasi cukup untuk menjelaskan ketidakmampuan menyelesaikan objective;
2. blocker dibuktikan oleh immutable evidence, bukan asumsi;
3. reasonable remediation alternatives telah diinventarisasi;
4. alternatives yang masih layak telah diuji, atau ada bukti objektif mengapa tidak feasible;
5. tidak ada active attempt dengan `Convergence = IMPROVING`;
6. tidak ada testable remediation penting yang sengaja dilewati;
7. kondisi bukan sekadar `WAITING_VERIFIED_DEPENDENCY`;
8. residual objective dan dampaknya terhadap core Weekly Swing dijelaskan;
9. ada explicit reviewed decision di `decisions/` yang menyetujui terminal closure;
10. jika tidak ada successor, decision menjelaskan mengapa tidak ada legitimate successor.

Personal judgement, elapsed time, deadline pressure, atau repeated failure **tidak memenuhi** burden of proof ini.

---

## 8. Successor / Decomposition Rule

Stage boleh diganti atau dipecah hanya bila evidence menunjukkan pendekatan atau scope baru memang berbeda secara material.

### `SUPERSEDED_BY_SUCCESSOR`

Gunakan jika objective core masih perlu dicapai, tetapi pendekatan stage lama terbukti tidak layak dan successor memakai materially different resolution path.

Wajib:

- explicit decision;
- predecessor residual objective;
- successor objective;
- apa yang berbeda secara material;
- evidence yang menyebabkan perpindahan;
- mapping seluruh unresolved scope agar tidak ada pekerjaan hilang;
- entry criteria successor.

### `SUPERSEDED_BY_DECOMPOSITION`

Gunakan jika satu stage terbukti terdiri dari beberapa objective independen yang lebih aman diselesaikan terpisah.

Contoh:

```text
WS-B06
  -> WS-B06A persistence alignment
  -> WS-B06B API contract alignment
  -> WS-B06C integration validation
```

Predecessor **bukan `DONE`** hanya karena sudah dipecah.

Split/successor dilarang jika hanya mengganti nomor atau nama stage tanpa perubahan objective/approach yang material.

---

## 9. No Escape by Strategy Weakening

Implementation difficulty tidak boleh diselesaikan dengan mengubah canonical strategy agar test menjadi PASS.

Jika evidence menunjukkan strategy sendiri mempunyai masalah material:

```text
stage evidence
  -> finding
  -> decision
  -> controlled strategy revision
  -> new/successor implementation stage
```

Sampai strategy revision sah, current strategy tetap authority.

---

## 10. Stage Register Is the Current Resume Index

Current stage pointer berada di:

[`../implementation/WS_IMPLEMENTATION_STAGE_REGISTER.md`](../implementation/WS_IMPLEMENTATION_STAGE_REGISTER.md)

Register harus menunjukkan minimal:

- stage ID dan objective;
- lifecycle state;
- stage/evaluation verdict bila applicable;
- latest attempt;
- convergence;
- open finding;
- current remediation/decision;
- verified dependency/resume trigger;
- successor jika ada;
- resume point;
- last update.

Register adalah `MUTABLE_TRACEABLE` current index. Ia **tidak menggantikan evidence/finding/decision**.

Historical attempt details tidak boleh ditumpuk penuh di register; gunakan pointer agar register tetap mudah dibaca.

---

## 11. Recording Matrix for Every Attempt / Stage Event

| Event | Mandatory record |
|---|---|
| stage opened | stage register update + implementation status append |
| attempt executed | evidence record + stage register latest-attempt pointer |
| new problem discovered | finding, jika memang new material issue |
| remediation chosen materially | finding/decision sesuai authority + implementation change log bila contract berubah |
| verified external dependency | evidence + stage register `WAITING_VERIFIED_DEPENDENCY` |
| attempt failed but converging | close attempt evidence; stage remains active; record convergence + next action |
| stage objective achieved | stage register `DONE` + evidence + status/tracker sync |
| evaluation stage returns valid FAIL | stage may be `DONE`; keep verdict `FAIL`; downstream gate follows strategy |
| terminal infeasibility | evidence + reviewed decision + `CLOSED_UNRESOLVED_WITH_EVIDENCE` |
| successor/decomposition | evidence + decision + predecessor/successor register update |

Jangan menduplikasi seluruh isi evidence/finding/decision di banyak dokumen. Tracker/register cukup menyimpan summary + pointer.

---

## 12. Downstream Advancement Rule

Downstream step boleh dimulai hanya jika dependency stage sebelumnya mempunyai keadaan yang **secara eksplisit mengizinkan handoff**.

- `DONE` dengan required positive gate → lanjut normal.
- `DONE` dengan valid evaluation verdict `FAIL` → stage execution selesai, tetapi proof/production path berhenti sesuai strategy.
- `WAITING_VERIFIED_DEPENDENCY` → jangan melewati dependency dengan fallback ilegal.
- `CLOSED_UNRESOLVED_WITH_EVIDENCE` → downstream hanya boleh lanjut jika decision secara eksplisit menunjukkan core dependency sudah dialihkan atau stage tersebut tidak lagi required.
- `SUPERSEDED_BY_SUCCESSOR/DECOMPOSITION` → lanjut ke declared successor, bukan melompati residual objective.
- `NOT_REQUESTED_OPTIONAL` pada CONFIRM → core path boleh lanjut karena strategy memang menyatakan optional.

---

## 13. Historical Status Compatibility

Historical C/R/B/P campaign tracker dapat memuat status lama seperti:

- `PARTIAL`
- `BLOCKED`
- `FAILED_NOT_READY_CLOSED`
- `DONE_OPERATOR_VALIDATED`
- status campaign-specific lain.

Status tersebut tetap immutable sebagai historical evidence. **Mereka bukan lifecycle taxonomy untuk stage `WS-Bxx` baru.**

Setiap current alignment/rework stage harus memakai standard ini dan current stage register.

---

## 14. Hard Prohibitions

Dilarang:

1. menutup stage karena jumlah attempt tertentu;
2. menutup stage hanya karena lama tidak selesai;
3. memakai `DONE` ketika objective/exit criteria stage belum terpenuhi;
4. menghapus failed attempt yang ternyata membantu mengerucutkan masalah;
5. rerun tanpa membaca latest evidence/findings/decision;
6. mengulang exact failed approach tanpa new hypothesis/evidence reason;
7. memecah stage hanya untuk menghilangkan status gagal;
8. membuat successor tanpa memindahkan seluruh residual objective;
9. menyebut dependency “tidak tersedia” tanpa evidence;
10. menganggap `INSUFFICIENT EVIDENCE` sebagai terminal bila evidence masih dapat dikumpulkan secara wajar;
11. melemahkan strategy untuk membuat implementation stage terlihat selesai;
12. memakai historical tracker status sebagai current `WS-Bxx` lifecycle authority.

---

## 15. Audit Enforcement

Setiap implementation/audit session yang menyentuh stage aktif wajib memeriksa:

- re-entry protocol sudah dijalankan;
- latest attempt evidence dibaca;
- open finding dan active decision/remediation dibaca;
- attempt baru mempunyai perbedaan/hypothesis yang jelas;
- convergence dicatat;
- stage register diperbarui;
- `DONE` hanya digunakan sesuai objective/exit criteria;
- unresolved terminal closure memenuhi burden of proof;
- successor/decomposition tidak menjadi escape hatch;
- downstream handoff sah.

Jika salah satu tidak dapat dibuktikan, stage progression harus ditolak atau dikembalikan ke state aktif yang sesuai.
