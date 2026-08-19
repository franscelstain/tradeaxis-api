# 02 — WS Module Mapping

> **Current Conformance:** `NOT_ASSESSED_REVALIDATION_REQUIRED`  
> **Verification Epoch:** `WS-REBASELINE-20260819-001`  
> Existing content is a revalidation input. Historical PASS/READY/DONE wording does not grant current conformance.


## Purpose

Dokumen ini memetakan baseline Weekly Swing ke modul implementasi aplikasi watchlist.

Dokumen ini bersifat **implementation translation only**.  
Dokumen ini **tidak** boleh dipakai untuk membuat rule bisnis baru atau memperluas domain di luar baseline freeze.

## Scope Lock

Scope aktif:
- watchlist only
- weekly_swing only
- bukan portfolio
- bukan execution
- bukan market-data internals

## Governance Rule

Jika terjadi konflik:
1. `docs/watchlist/authority/governance/WATCHLIST_DOCUMENT_AUTHORITY.md` menang
2. owner docs di `docs/watchlist/authority/strategy/*.md` menang
3. dokumen ini wajib tunduk

CONFIRM-specific alignment wajib mengikuti `../CONFIRM_OPTIONAL_NON_BLOCKING_IMPLEMENTATION_GUARD.md`; older wording yang membuat CONFIRM mandatory/non-blocking conflict dianggap stale.

## Recommended Module Set

### Shared
- `PolicyResolver`
- `RuntimeArtifactRepository`
- `ReasonCodeResolver`
- `ManualInputValidator`
- `TradeDateResolver`
- `ArtifactHashResolver`
- `SchemaContractValidator`

### PLAN
- `WsPlanInputProvider`
- `WsPlanEngine`
- `WsPlanAssembler`
- `WsPlanSerializer`
- `WsPlanPublisher`

### RECOMMENDATION
- `WsRecommendationEngine`
- `WsRecommendationAssembler`
- `WsRecommendationSerializer`
- `WsRecommendationPublisher`

### CONFIRM
- `WsConfirmBinder`
- `WsConfirmOverlayEngine`
- `WsConfirmAssembler`
- `WsConfirmSerializer`
- `WsConfirmPublisher`

### Consumer / Delivery
- `WsWatchlistReadService`
- `WsWatchlistApiPresenter`
- `WsWatchlistCompositeViewBuilder`

## PLAN intake anchor for module translation
`WsPlanInputProvider` tidak boleh bebas menentukan source upstream sendiri. Modul ini harus mengikat bacaannya ke intake producer-facing yang sah dari `market-data`, terutama kontrak consumer-readable / publication-aware yang sudah ditunjuk owner producer.

Anchor minimum yang wajib dibaca dan dijadikan referensi implementasi adalah:
- `docs/market_data/book/CONSUMER_READ_CONTRACT_LOCKED.md`
- `docs/market_data/book/Downstream_Consumer_Read_Model_Contract_LOCKED.md`
- `docs/market_data/book/EOD_Eligibility_Snapshot_Contract_LOCKED.md`
- `docs/market_data/book/Downstream_Data_Readiness_Guarantee_LOCKED.md`
- `docs/watchlist/authority/strategy/WS_MARKET_DATA_INPUT_REQUIREMENTS.md`
- `docs/watchlist/development/implementation/MARKET_DATA_INTAKE_IMPLEMENTATION_CONTRACT.md`

Arti praktisnya:
- adapter boleh berbeda secara teknis
- tetapi meaning intake harus tunggal
- dan tidak boleh diganti dengan raw internals, session snapshot internals, atau technical switch artifacts sebagai source of truth baru

## Artifact-to-Module Mapping

| Artifact | Producer module(s) | Consumer module(s) | May mutate source artifact? | Persistence required? | Notes |
|---|---|---|---:|---:|---|
| PLAN | `WsPlanInputProvider`, `WsPlanEngine`, `WsPlanAssembler`, `WsPlanSerializer`, `WsPlanPublisher` | recommendation, confirm, composite read | No | Yes | source artifact utama |
| RECOMMENDATION | `WsRecommendationEngine`, `WsRecommendationAssembler`, `WsRecommendationSerializer`, `WsRecommendationPublisher` | composite read, API read | No | Yes | derived only from PLAN |
| CONFIRM | `WsConfirmBinder`, `WsConfirmOverlayEngine`, `WsConfirmAssembler`, `WsConfirmSerializer`, `WsConfirmPublisher` | composite read, API read | No | Conditional | optional non-blocking capability; persist an attempt/result when present, but absence of CONFIRM is valid and must not make core artifacts incomplete |

## Per-Module Contract Boundary

### PLAN Module
- reads from:
  - producer-facing, publication-aware upstream intake contract dari `market-data`
  - resolved PLAN intake adapter yang tunduk pada kontrak tersebut
- writes to:
  - PLAN artifact
- must not touch:
  - recommendation output
  - confirm output
  - execution/order state
  - holdings/portfolio state

### RECOMMENDATION Module
- reads from:
  - immutable PLAN artifact
  - capital input yang sah bila mode capital-aware aktif
- writes to:
  - RECOMMENDATION artifact
- must not touch:
  - confirm mutation
  - recommendation source di luar PLAN
  - execution/order state
  - holdings/portfolio state

### CONFIRM Module
- reads from:
  - final Top Pick + immutable PLAN/recommendation binding
  - current snapshot/manual input bila tersedia dan sah sesuai contract
- writes to:
  - CONFIRM artifact
- must not touch:
  - recommendation membership
  - recommendation rank
  - recommendation score
  - recommendation grouping
  - execution/order state
  - holdings/portfolio state

### Composite / Consumer Module
- reads from:
  - PLAN
  - RECOMMENDATION
  - CONFIRM
- writes to:
  - read-model / view-model only
- must not touch:
  - source artifact mutation
  - execution/order state
  - holdings/portfolio state

## Trigger Matrix

| Module area | Trigger | Minimum input | Minimum output | Failure mode |
|---|---|---|---|---|
| PLAN | build trade-date run | valid policy + valid PLAN inputs | PLAN artifact | contract fail / validation fail |
| RECOMMENDATION | PLAN published | immutable PLAN artifact | RECOMMENDATION artifact | source PLAN missing / contract fail |
| CONFIRM | optional confirm request/current-data attempt | final Top Pick binding; current data if available | CONFIRM state/artifact when attempted | missing/stale/incomplete data => `UNAVAILABLE_RETRYABLE` (non-blocking); technical contract errors remain local to CONFIRM |
| Composite read | consumer read | PLAN and optionally RECOMMENDATION/CONFIRM | composite view | missing source artifact / inconsistent source refs |


## Suggested Build Order (Translation Layer)

### Phase 1 — Lock Core Contracts
- baca `docs/watchlist/authority/governance/WATCHLIST_DOCUMENT_AUTHORITY.md`
- baca owner docs Weekly Swing yang relevan
- kunci invariants core `PLAN -> RECOMMENDATION/TOP PICKS` dan optional branch `TOP PICKS -> CONFIRM`
- kunci acceptance minimum dari `WS_CONTRACT_TEST_CHECKLIST.md`

### Phase 2 — Build PLAN Foundation
1. bind input yang sah untuk `PLAN`
2. build candidate pool
3. compute PLAN score
4. resolve ranking dan group semantics
5. assemble `PLAN` runtime output
6. freeze `PLAN` sebagai immutable artifact

### Phase 3 — Build RECOMMENDATION Layer
1. bind immutable `PLAN` artifact
2. form recommendation source universe from `PLAN` only
3. compute recommendation logic
4. resolve dynamic recommendation count
5. resolve capital-free / capital-aware mode sesuai policy
6. assemble `RECOMMENDATION` runtime output
7. persist/publish `RECOMMENDATION` tanpa menunggu `CONFIRM`

### Phase 4 — Build Optional CONFIRM Overlay
1. core PLAN + RECOMMENDATION/TOP PICKS harus sudah dapat selesai tanpa CONFIRM
2. bind final Top Pick + immutable PLAN/recommendation identity
3. jika valid current input belum tersedia, return/record `UNAVAILABLE_RETRYABLE` tanpa menggagalkan core run
4. bila input tersedia, evaluate confirm per item menjadi `ACTIONABLE` atau `NOT_ACTIONABLE`
5. assemble/persist `CONFIRM` hanya bila ada request/attempt/result
6. jangan pernah memutasi atau menggagalkan `RECOMMENDATION` karena CONFIRM

### Phase 5 — Build Composite Read Layer
Gabungkan `PLAN`, `RECOMMENDATION`, dan `CONFIRM` hanya di read/view layer tanpa mencampur source semantics.

## Mapping to Source of Truth

- `WS_PRODUCT_OBJECTIVE_AND_LAYERS.md` = business orientation
- `WS_RUNTIME_FLOW.md` = runtime precedence
- `WS_DATA_MODEL_MARIADB.md` = runtime data shape baseline
- `WS_PLAN_SCORING_AND_TRADE_PLAN.md` = PLAN owner behavior
- `WS_D1_CONFIRM_ACTIONABILITY.md` = CONFIRM owner behavior
- `WS_IMPLEMENTATION_BLUEPRINT.md` = build order
- `22–25` = RECOMMENDATION owner behavior
- `WS_CONTRACT_TEST_CHECKLIST.md` = acceptance minimum

Setiap module implementasi harus bisa ditelusuri balik ke owner doc yang relevan.

## Forbidden Coupling (LOCKED)

1. modul confirm membaca recommendation untuk eligibility candidate
2. modul recommendation membaca confirm untuk source scoring atau source membership
3. modul recommendation membentuk ticker dari luar candidate PLAN
4. modul watchlist langsung membaca market-data internals di luar input contract yang sudah tersedia
5. modul watchlist menulis holdings/portfolio state
6. modul watchlist menulis order/execution/broker state
7. confirm ditulis sebagai mutasi atas row recommendation sehingga recommendation terlihat berubah

## Persistence Alignment Note (LOCKED)

Status persistence untuk `CONFIRM` pada baseline ini adalah **Yes**.

Artinya:
- `CONFIRM` diperlakukan sebagai artifact yang dapat direplay
- `CONFIRM` tersedia untuk consumer reads
- `CONFIRM` tersedia untuk audit trail
- implementasi transient-only untuk `CONFIRM` tidak dianggap setara tanpa audit dan perubahan guidance yang eksplisit

Dokumen ini wajib sinkron dengan `WS_PERSISTENCE_GUIDANCE.md`.

## Final Rule

Module mapping yang sah adalah:
- build PLAN dahulu
- derive RECOMMENDATION hanya dari PLAN
- derive optional CONFIRM hanya dari final Top Pick binding + current data bila tersedia; absence/missing data tidak memblokir core
- gabungkan hanya di layer read/view tanpa mengubah source semantics

## Canonical Layer Mapping

Dokumen ini harus dibaca bersama `docs/system_audit/SYSTEM_TRANSLATION_BASELINE.md` dan vocabulary di `docs/api_architecture/`.

| Module | Canonical layer | Allowed input | Forbidden responsibility | Expected output form |
|---|---|---|---|---|
| `WsPlanInputProvider` | producer-facing read adapter / intake repository | publication-aware consumer-facing upstream output | scoring PLAN, recommendation logic, confirm logic, response shaping | upstream intake DTO / normalized result object |
| `WsPlanEngine` | domain compute | clean PLAN input DTO | query upstream source langsung, persistence write, transport formatting | PLAN decision/result object |
| `WsPlanAssembler` | application-side assembler / artifact shaper | PLAN result object + resolved runtime metadata | rule scoring baru, upstream query | PLAN artifact payload |
| `WsRecommendationEngine` | domain compute | immutable PLAN-derived compute input | membaca CONFIRM, response shaping, persistence write | recommendation result object |
| `WsRecommendationAssembler` | application-side assembler / artifact shaper | recommendation result object + metadata | mengubah source PLAN meaning | recommendation artifact payload |
| `WsConfirmBinder` | application orchestration helper / binder | final Top Pick binding + optional current input | rule confirm final, response transport final | confirm availability/compute input object |
| `WsConfirmOverlayEngine` | domain compute | clean confirm compute input | membaca upstream producer langsung, memutasi recommendation | confirm result object |
| `WsConfirmAssembler` | application-side assembler / artifact shaper | confirm result object + metadata | rule confirm baru | confirm artifact payload |
| `RuntimeArtifactRepository` | persistence adapter / repository | artifact payload yang sudah selesai diputuskan | eligibility/scoring/policy decision | persisted artifact / artifact read result |
| `WsWatchlistReadService` | application service / orchestration | request intent yang sudah lolos boundary | query mentah di transport, policy baru | read/composite result object |
| `WsWatchlistApiPresenter` | transport-facing presenter | read/composite result object | policy, upstream intake, persistence write | response DTO |

## What Each Module May Read / Decide / Return

### Read
- `WsPlanInputProvider` hanya membaca source upstream producer-facing yang sah.
- `RuntimeArtifactRepository` membaca/menulis artifact watchlist milik consumer.
- `WsWatchlistReadService` boleh membaca artifact melalui adapter/repository yang sah, bukan langsung ke internals producer.

### Decide
- `WsPlanEngine`, `WsRecommendationEngine`, dan `WsConfirmOverlayEngine` adalah rumah keputusan internal weekly_swing.
- Application service, binder, assembler, dan presenter tidak boleh menjadi rumah keputusan policy baru.

### Return
- read adapter mengembalikan intake DTO / normalized result object;
- domain compute mengembalikan decision/result object;
- assembler mengembalikan artifact payload;
- presenter mengembalikan response DTO.
