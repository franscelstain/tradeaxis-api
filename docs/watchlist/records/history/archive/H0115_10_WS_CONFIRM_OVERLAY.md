# 10 — WS Confirm Overlay

## Purpose

CONFIRM menentukan **current actionability** dari final Weekly Swing Top Picks ketika pengguna hendak mempertimbangkan entry. CONFIRM bukan selection engine dan bukan sumber recommendation baru.

## Eligibility

CONFIRM hanya berlaku pada ticker yang berada pada final `TOP_PICKS` untuk PLAN/recommendation binding yang sah.

Ticker non-recommended tidak boleh dipromosikan menjadi buy recommendation melalui CONFIRM.

## Canonical Entry Timing

Baseline Weekly Swing recommendation dibentuk setelah EOD `D` dan canonical initial-entry session adalah trading day `D+1`.

CONFIRM dipakai untuk memeriksa actionability pada intended entry session tersebut. Bila entry tidak dilakukan dalam canonical entry window, recommendation tidak otomatis dibawa sebagai new-entry signal ke hari berikutnya. Carry-forward hanya sah bila active strategy identity secara eksplisit mendefinisikannya dan mempunyai proof terpisah.

## Binding

CONFIRM harus terikat pada:
- immutable PLAN yang membentuk recommendation;
- final recommendation item yang sama;
- strategy/evaluation identity yang sama;
- approved current-market snapshot dengan timestamp yang dapat divalidasi.

CONFIRM tidak boleh menambah ticker dari luar Top Picks.

## Current-Actionability Checks

Top Pick hanya dapat berstatus **actionable** bila seluruh active CONFIRM gate lulus.

Minimum canonical gates:

1. current snapshot tersedia dan tidak stale menurut active confirmation freshness limit;
2. ticker masih tidak memiliki current disqualifying trading/data state yang diketahui;
3. current executable/indicative price masih berada dalam allowed entry band dan tidak melampaui maximum adverse drift/chase limit dari PLAN entry reference;
4. proposed current entry tidak membuat active trade-plan risk geometry invalid;
5. seluruh field yang diwajibkan active CONFIRM rule valid.

Exact freshness, entry-band, drift, dan exit-policy-specific validity thresholds adalah versioned strategy parameters yang harus dibekukan sebelum outcome evaluation/shadow dibaca.

## Evaluation Meaning

### Actionable

Top Pick masih berada dalam kondisi yang diizinkan untuk dipertimbangkan entry pada canonical entry session.

### Not Actionable

Top Pick tetap tercatat sebagai historical EOD recommendation tetapi current conditions tidak lagi memenuhi entry requirement. User-facing decision support harus memperlakukannya sebagai **do not enter now**.

Tidak ada outcome CONFIRM yang boleh menulis ulang rank atau score EOD.

## Decision-Support Rule

Untuk keputusan beli manual, strongest Watchlist state adalah:

`TOP PICK + ACTIONABLE CONFIRM`

Top Pick tanpa CONFIRM tetap merupakan EOD recommendation tetapi belum mempunyai current-actionability proof.

Top Pick dengan CONFIRM not-actionable tidak boleh disajikan sebagai actionable buy saat itu.

## Strictness Boundary

1. CONFIRM hanya membaca final Top Picks dan binding PLAN-nya.
2. CONFIRM tidak membuat recommendation baru.
3. CONFIRM tidak mengubah recommendation membership, score, atau rank.
4. CONFIRM hanya mengubah current-actionability interpretation.
5. CONFIRM tidak melakukan order placement atau execution.
6. CONFIRM tidak menghidupkan kembali recommendation yang sudah melewati canonical entry window.
