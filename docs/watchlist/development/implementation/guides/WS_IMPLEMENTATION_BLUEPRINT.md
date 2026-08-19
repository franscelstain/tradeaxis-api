# 21 — WS Implementation Bridge Note

> **Current Conformance:** `NOT_ASSESSED_REVALIDATION_REQUIRED`  
> **Verification Epoch:** `WS-REBASELINE-20260819-001`  
> Existing content is a revalidation input. Historical PASS/READY/DONE wording does not grant current conformance.


## Purpose

Dokumen ini adalah **bridge note** antara owner docs Weekly Swing dan implementation guidance di `docs/watchlist/development/implementation/guides/`.
Tujuannya menjaga agar penerjemahan implementasi tidak melanggar boundary core `PLAN → RECOMMENDATION/TOP PICKS` dan optional non-blocking `CONFIRM`.

Dokumen ini **bukan owner rule bisnis baru** dan **bukan** tempat utama untuk detail build/module/API/persistence/testing.
Detail translation implementasi harus dibaca di folder implementation guidance.

## Blueprint Authority Boundary (LOCKED)

Jika terjadi konflik:
1. `docs/watchlist/authority/governance/WATCHLIST_DOCUMENT_AUTHORITY.md` menang
2. owner docs Weekly Swing yang relevan menang
3. dokumen ini wajib tunduk

Dokumen ini hanya menjelaskan bentuk translasi implementasi yang kompatibel dengan policy.

## Placement Guard (LOCKED)

Dokumen ini sengaja dipertahankan di folder policy hanya sebagai **jembatan baca** agar implementer tidak loncat langsung ke guidance tanpa membaca owner rules.
Namun isi dokumen ini harus tetap tipis, boundary-first, dan tidak boleh berkembang menjadi owner normatif baru atau blueprint implementasi rinci.

Jika butuh detail penerjemahan implementasi, rujuk ke:
- `WS_IMPLEMENTATION_SCOPE_AND_BOUNDARY.md`
- `WS_MODULE_MAPPING.md`
- `WS_RUNTIME_ARTIFACT_FLOW.md`
- `WS_API_GUIDANCE.md`
- `WS_PERSISTENCE_GUIDANCE.md`
- `WS_TEST_IMPLEMENTATION_GUIDANCE.md`
- `WS_DELIVERY_CHECKLIST.md`

## Scope Lock

- watchlist only
- weekly_swing only
- bukan portfolio
- bukan execution
- bukan market-data internals

## Mandatory Invariants

1. `RECOMMENDATION` derived from `PLAN` only
2. `CONFIRM` optional authority tied to final Top Picks + immutable PLAN/recommendation binding
3. `CONFIRM` does not mutate `RECOMMENDATION`
4. watchlist artifacts remain separate from execution concerns
5. watchlist artifacts remain separate from portfolio concerns
6. persistence/API shape must preserve artifact separation
7. empty recommendation is valid
8. non-Top-Pick cannot receive valid business CONFIRM; missing CONFIRM data cannot fail core output

## Forbidden Blueprint Interpretations

1. “confirm boleh meng-upgrade recommendation”
2. “recommendation boleh memakai data di luar PLAN asal masih relevan”
3. “confirm cukup ditempel sebagai status di recommendation row”
4. “watchlist API boleh sekalian membawa order/execution intent”
5. “portfolio enrichment boleh dimasukkan agar output lebih praktis”
6. “recommendation membership boleh direvisi setelah confirm”
7. “CONFIRM harus selesai agar PLAN/RECOMMENDATION dianggap sukses”
8. “missing CONFIRM data berarti NOT_ACTIONABLE”

## Reading Order Pointer

Urutan baca implementer yang benar:
1. pahami owner docs Weekly Swing yang relevan
2. kunci invariant artifact dan contract acceptance
3. baru baca implementation guidance untuk module mapping, runtime flow, API, persistence, tests, dan delivery gate

Detail yang **tidak boleh** diperluas di dokumen ini dan harus hidup di implementation guidance:
- module mapping
- build order per fase
- runtime delivery flow rinci
- delivery / merge checklist
- traceability implementasi yang detail

## Final Rules

1. urutan translasi core Weekly Swing adalah: `PLAN`, lalu `RECOMMENDATION/TOP PICKS`; core selesai tanpa CONFIRM
2. `RECOMMENDATION` dibangun sebagai lapisan terpisah dari `PLAN`
3. `CONFIRM` dibangun kemudian sebagai optional overlay hanya pada final Top Picks
4. missing/stale/incomplete CONFIRM input menghasilkan availability state non-blocking dan retryable
5. empty recommendation adalah hasil valid dan tidak membutuhkan CONFIRM
6. artifact separation tidak boleh dikorbankan demi convenience view/API

## Final Boundary Reminder

Dokumen ini hanya merangkum boundary translasi yang kompatibel dengan owner docs.
Jika ada kebutuhan detail implementasi yang lebih teknis, tambahkan atau revisi di folder `docs/watchlist/development/implementation/`, bukan dengan memperluas authority dokumen ini.
