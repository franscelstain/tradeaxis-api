# 20 — Weekly Swing Canonical Paramset Procedures

## Purpose

Dokumen ini adalah owner normatif untuk prosedur canonical pemilihan, validasi, promosi, dan aktivasi paramset Weekly Swing.

## Scope

Dokumen ini mengunci:

- preconditions promote / activate,
- validation gates yang wajib lolos,
- status transition canonical,
- postconditions yang wajib benar setelah promote,
- dan relationship ke SQL artifact yang merealisasikan prosedur teknis.

## Ownership Rule

`db/PROMOTE_PARAMSET.sql` merealisasikan prosedur yang ditetapkan di dokumen ini. SQL artifact tidak menjadi owner preconditions, gate semantics, atau postconditions.

## A. Promotion Preconditions

Sebelum promote dijalankan, kondisi berikut wajib benar:

1. target paramset berada pada `policy_code = WS` sebagai canonical internal policy code untuk Weekly Swing,
2. target paramset memakai `policy_version` yang relevan untuk run Weekly Swing,
3. target row ada,
4. target status adalah `DRAFT`.

Jika salah satu kondisi ini tidak terpenuhi, promote wajib gagal.

## B. Concurrency Lock

Promote canonical wajib memakai policy-scoped lock untuk mencegah promote paralel yang saling menimpa.

SQL artifact saat ini merealisasikan lock tersebut dengan primitive `GET_LOCK` dan `RELEASE_LOCK`, memakai lock name:
- `WS:PARAMSET`

dan timeout lock:
- `10` detik.

Jika lock tidak diperoleh, promote wajib gagal dan tidak boleh lanjut ke status transition.

## C. Required Validation Gate Before Promote

Promote canonical Weekly Swing mensyaratkan OOS proof yang lolos acceptance gate berikut:

- `picks_count_oos >= ws.eval.min_trades_oos` (canonical default `40`)
- `avg_ret_net_top_oos > 0`
- `median_ret_net_top_oos >= 0`
- `month_win_rate_min_oos >= 0.45`
- `p25_ret_net_top_oos >= -0.03`

Selain itu, OOS proof yang dicek harus cocok terhadap:

- `policy_code = WS`
- `policy_version` target
- `param_id_best_is = target BT param id`
- `oos_id = target OOS id`

Jika gate ini tidak lolos, promote wajib gagal dan target tidak boleh menjadi `ACTIVE`.

## D. Canonical Status Transition

Status transition canonical Weekly Swing saat promote adalah:

1. existing paramset `ACTIVE` untuk `policy_code = WS` diubah menjadi `DEPRECATED`,
2. target paramset `DRAFT` diubah menjadi `ACTIVE`.

Tidak boleh ada hasil akhir di mana lebih dari satu paramset untuk `policy_code = WS` tetap `ACTIVE` akibat promote yang sama.

## E. Postconditions

Setelah promote sukses, kondisi berikut wajib benar:

- tepat satu target row berhasil dipromote menjadi `ACTIVE`,
- tidak ada lebih dari satu row `ACTIVE` yang tersisa untuk `policy_code = WS`,
- existing `ACTIVE` lama telah diturunkan menjadi `DEPRECATED`,
- transaction committed,
- lock dilepas melalui `RELEASE_LOCK`.

Jika target row tidak benar-benar terpromote, prosedur wajib dianggap gagal.

## F. Failure Handling

Jika terjadi exception SQL atau salah satu gate gagal:

- transaction wajib rollback,
- lock wajib dilepas melalui `RELEASE_LOCK`,
- target tidak boleh dianggap promoted.

## G. Relationship to JSON Contract and Validator

Promote canonical harus dibaca bersama:

- `04_WS_PARAMSET_JSON_CONTRACT.md`
- `06_WS_PARAMSET_VALIDATOR_SPEC.md`

Shape paramset dan validitas paramset adalah precondition normatif bagi lifecycle promote / activate ini.

## H. Policy Identifier Parity

Untuk parity lintas dokumen dan artifacts, `policy_code = WS` adalah canonical internal policy code yang dipakai oleh paramset dan procedure artifacts. Runtime outputs dapat memakai `meta.policy = WEEKLY_SWING` sebagai runtime / display label untuk strategy yang sama. Perbedaan ini tidak boleh dianggap drift kontrak selama semantics strategy-nya tetap sama.

## I. Relationship to SQL Artifact

`db/PROMOTE_PARAMSET.sql` adalah implementation artifact yang saat ini merealisasikan:

- lock acquisition,
- DRAFT-only target validation,
- OOS acceptance gate,
- ACTIVE -> DEPRECATED transition,
- DRAFT -> ACTIVE promotion,
- rollback / release-lock failure handling.

Jika SQL artifact berbeda dari dokumen ini, dokumen ini selalu menang dan SQL artifact harus diperbarui.

## Final Procedure Rule

Tidak ada paramset yang boleh dianggap canonically promoted atau canonically active bila status tersebut tidak dapat ditelusuri ke preconditions, validation gates, status transitions, dan postconditions yang ditetapkan secara normatif di dokumen ini.

## Versioned identity requirement before OOS and promotion

Before an IS candidate may be handed to OOS, the following identity must be exact:

```text
watchlist_param_sets.params_hash
=
watchlist_bt_eval.paramset_hash
```

The execution contract must also match:

```text
watchlist_param_sets.eval_model_hash
=
watchlist_bt_eval.eval_model_hash

watchlist_param_sets.implementation_hash
=
watchlist_bt_eval.implementation_hash
```

Before DRAFT-to-ACTIVE promotion, official OOS evidence for the exact
IS-passing strategy scope must carry those same hashes plus the exact IS
`evidence_manifest_hash`. Any mismatch is fail-closed and cannot be repaired by
changing only an artifact or tracker entry.

The historical C171-to-C172 label does not override a sealed C171 failure.
After `C171_FAILED_NOT_READY_NO_FURTHER_REMEDIATION`, C172 remains forbidden for
that closed topic. A separately approved new-strategy scope must use its own
versioned IS/OOS identity and still satisfy every invariant in this section.

The R02 scope demonstrates this separation. Its optional
`research_selection` and `research_execution` sections are part of the
canonical params hash, while the distinct exit model is part of
`eval_model/eval_model_hash`. Eval `211` failed one canonical IS gate after the
only allowed remediation, so its DRAFT remains non-promotable and no official
OOS identity may be created from it.

The separate S01 scope also remains non-promotable. Its initial evals `212-214`
failed unchanged canonical IS gates, and its only remediation eval `215`
failed both monthly win-rate and monthly-average floors. Therefore no S01 IS
binding may be handed to OOS and paramsets `20-22` or `24` cannot become
`ACTIVE`. A tracker or artifact entry cannot override this failed gate result.

The separate P01 scope is also non-promotable. Initial evals `216-217` failed
canonical stability gates. Eval `218` is not a valid final identity because
its generic model label omitted the P01 remediation exit semantics.
Identity-corrected eval `219` carries exact
`EXIT=SEQ_TP05_PCL1NO_TIME` identity and failed median, P25, monthly win-rate,
and monthly-average gates. Therefore paramsets `25-28` cannot be handed to
OOS or promoted to `ACTIVE`; no further P01 remediation is allowed.


### Paramset provenance identity

An existing `params_hash` is idempotent only when `params_json`, `hash_contract`, `provenance_json`, `eval_model`, `eval_model_hash`, `implementation_version`, and `implementation_hash` are all identical. The same parameter payload may not silently adopt a different backtest binding or source provenance.
