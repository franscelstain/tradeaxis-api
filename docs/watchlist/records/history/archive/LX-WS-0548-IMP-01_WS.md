# Legacy Role Extract — WS — IMPLEMENTATION

> **Document Type:** IMPLEMENTATION
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0548-IMP-01`
> **Legacy Source ID:** `LS-WS-0548`
> **Legacy Work Key:** `WS`
> **Original Path:** `docs/watchlist/system/policies/weekly_swing/10_WS_CONFIRM_OVERLAY.md`
> **Original SHA1:** `87E95DC166C97B420D3EC4354809DAD5EEF0FC94`
> **Source Sections:** L73-L86 Confirm Overlay Foundation Output Reason Codes
> **Extract Body SHA1:** `CB5D0C4B273BE1350C451F24D71D5015F2F19B79`
> **Current Authority:** NO

The body below is an exact copy of the identified original sections. No semantic rewriting was applied.

---

## Confirm Overlay Foundation Output Reason Codes

Reason code foundation berikut boleh dipakai oleh implementation layer untuk menjelaskan binding dan hasil overlay CONFIRM:

- `WS_CONFIRM_ELIGIBLE_RECOMMENDED` — ticker recommended masih merupakan candidate PLAN yang sah untuk CONFIRM.
- `WS_CONFIRM_ELIGIBLE_NON_RECOMMENDED` — ticker non-recommended masih merupakan candidate PLAN yang sah untuk CONFIRM.
- `WS_CONFIRM_APPLIED` — evidence CONFIRM valid diterapkan sebagai overlay metadata.
- `WS_CONFIRM_NOT_APPLIED` — evidence tersedia tetapi tidak menjadi confirmed overlay.
- `WS_CONFIRM_REJECTED_UNKNOWN_CANDIDATE` — evidence ditolak karena ticker tidak ditemukan pada candidate PLAN aktif.
- `WS_CONFIRM_REJECTED_NOT_PLAN_CANDIDATE` — evidence ditolak karena ticker bukan candidate PLAN aktif.
- `WS_CONFIRM_NO_DATA` — tidak ada evidence CONFIRM untuk candidate PLAN tersebut.

Kode di atas tidak boleh dipakai untuk membentuk recommendation baru dan tidak boleh mengubah recommendation membership, rank, score, label, atau hash.
