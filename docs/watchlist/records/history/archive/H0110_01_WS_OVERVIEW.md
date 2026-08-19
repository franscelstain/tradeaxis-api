# 01 — WS Overview

## Purpose

Weekly Swing watchlist bertujuan mengubah Market Data EOD yang dapat dipercaya menjadi daftar saham yang benar-benar layak dipertimbangkan untuk pembelian swing, lalu mengurutkan kandidat yang lulus menjadi **Top Picks** dari kualitas tertinggi ke terendah.

Weekly Swing mempunyai tiga lapisan:

1. **PLAN** — membentuk dan memprioritaskan candidate setup;
2. **RECOMMENDATION** — menetapkan qualified recommendation dan final Top Picks ranking;
3. **CONFIRM** — memeriksa apakah Top Pick masih actionable ketika pengguna hendak mengambil keputusan beli.

## Product Objective

Strategy mengutamakan **quality over quantity**.

Keberhasilan Weekly Swing tidak diukur dari banyaknya saham yang muncul, tetapi dari kemampuan strategy untuk:
- menolak kandidat yang tidak cukup layak;
- menghasilkan recommendation dengan positive expected net return setelah realistic trading friction;
- menjaga downside dan period stability;
- membuat rank lebih tinggi merepresentasikan kualitas yang setidaknya tidak lebih buruk daripada rank lebih rendah;
- menghasilkan output deterministik dan dapat direplay;
- mengizinkan no-trade ketika market tidak menyediakan peluang yang cukup baik.

## Canonical Naming

Istilah **TOP PICKS** hanya digunakan untuk **final qualified RECOMMENDATION**.

PLAN menggunakan candidate states:
- `RECOMMENDATION_CANDIDATES`;
- `WATCH_ONLY`;
- `AVOID`.

PLAN candidate state tidak boleh dibaca sebagai final buy recommendation.

## Weekly Swing Architecture

Urutan konseptual:

1. PLAN dibentuk dari authoritative EOD input dan candidate eligibility/setup strategy;
2. PLAN menjadi immutable;
3. RECOMMENDATION mengevaluasi candidate PLAN dan menghasilkan seluruh qualified Top Picks;
4. Top Picks diurutkan secara deterministik berdasarkan canonical quality score;
5. pada intended next-trading-day entry session, CONFIRM dapat mengevaluasi current actionability dari Top Pick tanpa menulis ulang historical EOD recommendation.

## Recommendation Meaning

Sebuah ticker hanya disebut **Top Pick** bila ticker tersebut:
- berasal dari PLAN candidate yang sah;
- melewati seluruh hard eligibility/risk/data gate;
- melewati final recommendation quality floor;
- memiliki predeclared trade plan yang sah;
- memenuhi exit-policy-specific risk requirement;
- kemudian diurutkan menggunakan canonical recommendation ranking.

Jumlah Top Picks adalah jumlah aktual ticker yang lulus seluruh gate. Tidak ada kewajiban mengisi jumlah minimum atau maksimum hanya untuk kebutuhan tampilan.

## Confirm Meaning

CONFIRM tidak memilih saham baru dan tidak mengubah EOD Top Picks ranking.

CONFIRM hanya menjawab pertanyaan:

> apakah Top Pick yang sudah direkomendasikan masih layak ditindaklanjuti pada kondisi terbaru yang diizinkan strategy?

Top Pick yang gagal CONFIRM tetap tercatat sebagai historical recommendation, tetapi tidak boleh ditampilkan sebagai **actionable now**. Untuk intended real-use flow, keputusan beli baru hanya didukung ketika CONFIRM pada canonical next-trading-day entry window berstatus actionable.

## Final Rules

1. PLAN menghasilkan candidates, bukan final Top Picks.
2. RECOMMENDATION adalah owner final Top Picks.
3. Top Picks boleh kosong dan jumlahnya tidak dipaksa oleh quota.
4. Rank #1 adalah qualified recommendation dengan canonical quality ordering tertinggi, bukan sekadar item pertama dari PLAN.
5. Capital/affordability tidak menentukan kualitas, membership, atau rank Top Picks.
6. CONFIRM tidak mengubah recommendation history tetapi dapat menentukan current actionability pada canonical entry window.
7. Recommendation yang tidak dieksekusi pada canonical entry window tidak otomatis menjadi new-entry signal pada hari berikutnya.
