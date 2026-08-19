# Watchlist Implementation Change Impact Matrix

## Purpose

Dokumen ini memetakan dampak perubahan file implementasi watchlist agar revisi tidak setengah jadi.

## Matrix

| Jika berubah | Minimal cek ulang | Catatan |
|---|---|---|
| `../../../development/implementation/guides/WS_IMPLEMENTATION_SCOPE_AND_BOUNDARY.md` | `02`, `03`, `04`, `05`, audit foundation | boundary implementasi jangan drift |
| `WS_MODULE_MAPPING.md` | `03`, `06`, checklist final, service/repository boundary | module drift cepat merusak runtime flow |
| `WS_RUNTIME_ARTIFACT_FLOW.md` | `04`, `05`, `06`, checklist final | cek payload, persistence, dan tests |
| `WS_API_GUIDANCE.md` | `03`, `06`, checklist final, serializer/presenter boundary | jaga agar API tetap watchlist only |
| `WS_PERSISTENCE_GUIDANCE.md` | `03`, `06`, db support di system, repository boundary | jaga agar tidak bocor ke portfolio |
| `WS_TEST_IMPLEMENTATION_GUIDANCE.md` | checklist final | pastikan test coverage masih cukup |
| `WS_DELIVERY_CHECKLIST.md` | checklist final, implementation README | jaga delivery tetap tunduk ke baseline |
| review code service layer | checklist final, module mapping, runtime flow | cek business rule tidak bercampur |
| review serializer/presenter layer | checklist final, API guidance | cek serializer tidak mengambil alih rule |
| review input validator | checklist final, API guidance, delivery checklist | jaga input validation terpisah dari recommendation logic dan tunduk pada Market Data contract |

| review real API payloads | checklist final, API guidance, serializer/presenter boundary | cek payload nyata tetap watchlist only |
| review real persistence tables/artifacts | checklist final, persistence guidance, db support | cek artefak nyata tetap terpisah |
| review real input flow | checklist final, API guidance, validator boundary | cek input path tidak mencampur recommendation rule atau mengambil alih Market Data semantics |

## Final Rule

Setiap perubahan implementation guidance atau review code/app yang menyentuh boundary, payload, persistence, atau input validator wajib diaudit ulang dengan `WATCHLIST_IMPLEMENTATION_CHECKLIST_FINAL.md`.
