# Watchlist Weekly Swing — D+1 CONFIRM Actionability

## Purpose

CONFIRM adalah **optional non-blocking capability** untuk menilai current actionability dari final Weekly Swing Top Picks ketika pengguna hendak mempertimbangkan entry. CONFIRM bukan selection engine, bukan sumber recommendation baru, dan bukan prerequisite penyelesaian core Weekly Swing.

## Lifecycle Position

- **Stage:** `WS-S05` — Optional D+1 CONFIRM Actionability.
- **Branch point:** setelah final Top Picks `WS-S04` tersedia.
- **Consumes:** final Top Pick dan decision-time snapshot **bila tersedia** dalam canonical entry window.
- **Produces:** availability/actionability state tanpa mengubah EOD recommendation history.
- **Core dependency:** NONE — `WS-S06+` core proof tidak menunggu `WS-S05`.

## Eligibility

CONFIRM hanya berlaku pada ticker yang berada pada final `TOP_PICKS` untuk PLAN/recommendation binding yang sah.

Ticker non-recommended tidak boleh dipromosikan menjadi buy recommendation melalui CONFIRM.

## Canonical Entry Timing

Baseline Weekly Swing recommendation dibentuk setelah EOD `D` dan canonical initial-entry session adalah trading day `D+1`.

CONFIRM dapat dipakai selama canonical entry window untuk memeriksa actionability. Bila valid data belum tersedia pada percobaan awal, state harus tetap retryable selama window masih terbuka.

Bila entry window berakhir tanpa valid CONFIRM evaluation, recommendation tidak otomatis dibawa sebagai new-entry signal ke hari berikutnya. Carry-forward hanya sah bila active strategy identity secara eksplisit mendefinisikannya dan mempunyai proof terpisah.

## Decision-Time Source Boundary

Current Market Data core contract adalah EOD consumer read product dan **tidak otomatis menjamin D+1 intraday/current decision-time snapshot**. CONFIRM hanya boleh memakai decision-time source yang mempunyai producer-facing contract, timestamp/freshness semantics, dan field validity yang cukup.

Jika source tersebut belum ada, belum ready, atau tidak menyediakan valid data pada saat check, CONFIRM tetap `UNAVAILABLE_RETRYABLE` selama entry window masih terbuka. Watchlist tidak boleh mengubah EOD Market Data row menjadi synthetic current snapshot atau membaca session/internal Market Data artifacts yang belum consumer-facing.

## Binding

Ketika CONFIRM dievaluasi, ia harus terikat pada:
- immutable PLAN yang membentuk recommendation;
- final Top Pick yang sama;
- strategy identity yang sama;
- valid current-market snapshot dengan timestamp yang dapat divalidasi.

CONFIRM tidak boleh menambah ticker dari luar Top Picks.

## EOD / Current-Snapshot Separation

EOD recommendation facts dan D+1 confirmation facts mempunyai time role berbeda:
- EOD snapshot membentuk PLAN, score, recommendation membership, dan rank;
- D+1 current snapshot hanya menilai actionability dari Top Pick yang sudah terbentuk;
- D+1 information tidak boleh dipakai untuk menulis ulang atau memperbaiki EOD ranking secara retroaktif.

Tidak tersedianya valid current snapshot **bukan negative evidence** terhadap Top Pick.

## Canonical CONFIRM States

### NOT_REQUESTED

CONFIRM belum diminta/dijalankan. Top Pick tetap sah sebagai EOD recommendation.

### UNAVAILABLE_RETRYABLE

CONFIRM diminta, tetapi decision-time data yang cukup dan valid belum tersedia. Kondisi ini:
- bukan strategy failure;
- bukan `NOT_ACTIONABLE`;
- tidak memengaruhi Top Pick membership/rank;
- boleh dievaluasi ulang jika valid data tersedia sebelum entry window berakhir.

Missing, stale, incomplete, delayed, atau temporarily unavailable data masuk state ini selama evaluation belum dapat dilakukan secara sah.

### ACTIONABLE

Valid current data tersedia dan seluruh active CONFIRM gate lulus.

### NOT_ACTIONABLE

Valid current data tersedia, active CONFIRM rule dapat dievaluasi secara sah, dan sedikitnya satu actionability gate gagal. User-facing decision support dapat menyatakan **do not enter now** untuk ticker tersebut.

### EXPIRED_UNCONFIRMED

Canonical entry window berakhir sebelum valid CONFIRM evaluation tersedia. Top Pick tetap tercatat sebagai historical EOD recommendation; current actionability untuk opportunity tersebut tidak pernah terbukti.

## Current-Actionability Checks

`ACTIONABLE` hanya dapat diberikan bila seluruh active CONFIRM gate dapat dievaluasi dari valid current data dan semuanya lulus.

Minimum canonical gates:

1. current snapshot tersedia dan fresh menurut active confirmation freshness limit;
2. ticker tidak memiliki current disqualifying trading/data state yang diketahui;
3. current executable/indicative price masih berada dalam allowed entry band dan tidak melampaui maximum adverse drift/chase limit dari PLAN entry reference;
4. proposed current entry tidak membuat active trade-plan risk geometry invalid;
5. seluruh field yang diwajibkan active CONFIRM rule valid.

Jika gate tidak dapat dievaluasi karena required data belum valid/tersedia, hasilnya `UNAVAILABLE_RETRYABLE`, bukan `NOT_ACTIONABLE`.

Exact freshness, entry-band, drift, dan exit-policy-specific validity thresholds adalah versioned strategy parameters yang harus dibekukan sebelum capability proof dibaca.

## Retry and Late-Data Rule

`UNAVAILABLE_RETRYABLE` adalah non-terminal selama canonical entry window masih terbuka.

Ketika valid decision-time data kemudian tersedia:
1. bind kembali Top Pick/PLAN/strategy identity yang sama;
2. gunakan snapshot terbaru yang sah pada waktu evaluation;
3. jalankan active CONFIRM gates;
4. hasil dapat berubah dari `UNAVAILABLE_RETRYABLE` menjadi `ACTIONABLE` atau `NOT_ACTIONABLE`.

Tidak boleh membuat synthetic/default PASS hanya agar CONFIRM menghasilkan output.

## Technical Error Boundary

Kesalahan teknis internal seperti schema corruption, persistence failure, atau exception boleh dicatat sebagai **technical error** oleh implementation, tetapi:
- bukan business `NOT_ACTIONABLE`;
- tidak boleh menghapus/menggagalkan Top Picks;
- tidak boleh membuat core PLAN/RECOMMENDATION run gagal setelah Top Picks sudah sah;
- harus dapat diretry sesuai operational policy bila entry window masih terbuka.

Strategy tidak mensyaratkan CONFIRM technical success untuk menyatakan core Weekly Swing runtime complete.

## Decision-Support Rule

Strongest Watchlist state adalah:

`TOP PICK + ACTIONABLE CONFIRM`

Tetapi Top Pick tanpa CONFIRM tetap valid sebagai qualified EOD decision-support. Sistem hanya harus transparan bahwa current actionability belum terbukti.

Top Pick dengan valid `NOT_ACTIONABLE` tidak boleh disajikan sebagai actionable entry saat itu.

## Strictness Boundary

1. CONFIRM hanya membaca final Top Picks dan binding PLAN-nya.
2. CONFIRM tidak membuat recommendation baru.
3. CONFIRM tidak mengubah recommendation membership, score, atau rank.
4. CONFIRM optional dan non-blocking terhadap core Weekly Swing.
5. Missing/stale/incomplete current data tidak boleh menjadi `NOT_ACTIONABLE` atau core failure.
6. CONFIRM hanya mengubah current-actionability interpretation bila valid data cukup untuk evaluation.
7. CONFIRM tidak melakukan order placement atau execution.
8. CONFIRM tidak menghidupkan kembali recommendation yang sudah melewati canonical entry window.


## No Orderbook Requirement

CONFIRM tetap optional dan **tidak identik dengan orderbook**.

- Bila CONFIRM diminta, active actionability rule hanya memerlukan governed decision-time facts yang memang dibutuhkan oleh rule tersebut; full orderbook/queue-depth feed **MUST NOT** diasumsikan mandatory kecuali future CONFIRM identity secara eksplisit mengadopsinya.
- Tidak adanya orderbook tidak membuat core Top Pick invalid dan tidak membuat core Weekly Swing incomplete.
- Bila tidak ada decision-time source yang cukup untuk active CONFIRM rule, state tetap `UNAVAILABLE_RETRYABLE` atau `EXPIRED_UNCONFIRMED` sesuai lifecycle; sistem tidak boleh mengarang current snapshot.
- CONFIRM proof, bila dilakukan, adalah capability-specific proof dan tidak boleh mengubah historical EOD modeled execution menjadi klaim actual investor fill.
- Perubahan CONFIRM source dari simple governed current-price/status facts ke richer intraday/orderbook data adalah capability identity change yang harus dilacak dan dibuktikan terpisah.

## Decision-Time Fact Ownership

Optional CONFIRM juga tetap consumer capability dan tidak berubah menjadi data-acquisition subsystem.

- Jika CONFIRM diminta, seluruh decision-time market facts yang dipakai active CONFIRM rule **MUST** berasal dari separately governed authoritative source/contract; Watchlist tidak boleh memperoleh atau membangun substitute fact sendiri.
- Missing decision-time fact yang diperlukan active CONFIRM rule tetap menghasilkan `UNAVAILABLE_RETRYABLE`/`EXPIRED_UNCONFIRMED` sesuai lifecycle dan tidak boleh diisi dengan estimated/recomputed/current-provider fallback.
- Future decision-time source availability tidak otomatis mengubah EOD core atau CONFIRM semantics; source/capability tersebut harus dibind secara explicit dan dibuktikan di identity yang sesuai.
