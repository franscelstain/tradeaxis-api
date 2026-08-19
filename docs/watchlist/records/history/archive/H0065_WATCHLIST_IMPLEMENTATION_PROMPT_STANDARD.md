Saya akan upload ZIP berisi implementation guidance, artefak implementasi, atau hasil review code watchlist.

Tugas Anda adalah mengaudit implementasi secara ketat terhadap canonical `../../../strategy/weekly_swing/`.

Aturan aktivasi layer:
- guidance/examples/fixtures/SQL/schema tanpa code/runtime nyata = **Layer B**
- code/app/runtime/persistence nyata yang cukup = **Layer C**

Canonical behavior yang wajib diterjemahkan:
- weekly_swing only
- flow `PLAN -> qualified RECOMMENDATION/TOP_PICKS -> CONFIRM actionability`
- PLAN states: RECOMMENDATION_CANDIDATES / WATCH_ONLY / AVOID
- TOP_PICKS hanya final recommendation
- recommendation all-and-only qualification pass, zero allowed, no quota
- recommendation_score = PLAN score_total baseline
- capital tidak mengubah membership/rank
- CONFIRM hanya final Top Picks dan tidak mutate recommendation
- backtest mengevaluasi final Top Picks
- realistic costs + non-zero slippage + adverse-friction stress
- ranking-quality IS/OOS proof
- no OOS retuning
- forward shadow sebelum production-use approval

Jika technical docs/code masih memakai semantics lama, tandai strategy alignment pending; jangan menganggap keberadaan docs sebagai proof implementasi.

Yang wajib diaudit:
1. implementation scope and boundary
2. module mapping
3. runtime artifact flow
4. API/consumer guidance atau payload review
5. persistence guidance atau persistence review
6. test implementation guidance atau bukti test coverage
7. delivery checklist atau delivery readiness
8. service / repository / serializer / validator boundaries jika code/app nyata sudah tersedia

Output yang saya mau:
- nilai akhir numerik
- verdict
- tabel audit singkat PASS/PARTIAL/FAIL/N/A
- temuan utama
- patch prioritas berikutnya

Jangan mengaudit sebagai portfolio atau execution system. Audit ini khusus implementasi watchlist.


Jika code/app nyata tersedia, audit harus memprioritaskan bukti nyata tersebut di atas contoh guidance. Jika code/app nyata belum tersedia, nyatakan keterbatasan itu dengan jujur dan audit sebagai implementation guidance baseline Layer B, bukan Layer C.


Aturan scoring penting:
- jika Layer C tidak aktif, seluruh item audit yang khusus code/app/runtime nyata harus diberi `N/A`, bukan `PARTIAL`;
- jangan menurunkan nilai hanya karena service/controller/repository/payload runtime nyata memang belum ada pada ZIP guidance;
- `PARTIAL` untuk real-app evidence hanya sah bila Layer C aktif tetapi buktinya belum lengkap atau belum sinkron.

## Database Dictionary Requirement for Implementation Prompts

Any implementation prompt that touches database-connected data must require the implementer to read and apply:

```text
docs/market_data/db/MARKET_DATA_DICTIONARY.md
docs/db/DATABASE_DICTIONARY_USAGE_RULE.md
docs/watchlist/implementation/persistence/WATCHLIST_DB_DICTIONARY.md
```

The implementation must explicitly confirm touched tables, date keys, identifier keys, field roles, as-of safety, and selection/evaluation boundaries before coding. Do not infer database field names from memory. Missing dictionary coverage is a blocker or a required docs update, not something to guess around.
