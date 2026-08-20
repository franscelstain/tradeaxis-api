# Watchlist Decisions

> **Current Verification Epoch:** `WS-REBASELINE-20260819-001`  
> Pre-epoch decisions preserve historical decisions but do not grant current strategy/implementation PASS. Current authority lives in `authority/`; current proof is re-earned under the new epoch.


> **Physical role:** `docs/watchlist/records/decisions/` — issued decisions; changes use supersession.

Keputusan eksplisit GO/NO-GO, approval, promotion, closure, atau perubahan strategy/governance.

## Recording / Mutability Rule

Decision lifecycle:

`DRAFT -> ISSUED -> SUPERSEDED`

- `ISSUED` adalah **IMMUTABLE_AFTER_ISSUE**;
- jangan mengubah decision lama dari GO menjadi NO-GO atau sebaliknya;
- perubahan keputusan dibuat sebagai **decision baru** yang menyebut `Supersedes`;
- decision baru harus mempunyai stable Record ID;
- decision yang mengubah canonical strategy tetap harus menjalankan strategy change policy.

Universal rule: [`../../authority/governance/DOCUMENT_RECORDING_STANDARD.md`](../../authority/governance/DOCUMENT_RECORDING_STANDARD.md).

## Legacy decision extracts

`LX-*` decision files are immutable exact section extracts or historical issued decisions. Superseding a legacy decision requires a new current decision; never rewrite the old record.
