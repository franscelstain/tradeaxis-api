# Legacy Semantic Extract — LX-MD-0044-RES-01

- Source ID: `LS-MD-0044`
- Original path: `audit/reports/AUDIT_FINAL_STATE.md`
- Original SHA1: `4F88A2D91FB7215D7D64C2F3ACFED4764964CB20`
- Extract role: `RESEARCH`
- Source range: `L118-L137`
- Extract body SHA1: `1C66A0B5B114E947D89A65E122C62F7C22FFDD67`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Correct strategic target

Target yang disahkan untuk mengarahkan pembaruan dokumen adalah:

> Membangun EOD market-data platform yang menghasilkan data product saham IDX Regular Market yang valid, decision-grade, point-in-time, reproducible, auditable, stabil, dan aman dikonsumsi, dengan Yahoo Finance sebagai bootstrap source saat ini dan provider-neutral architecture untuk masa depan.

Istilah **kuat dan stabil** pada target ini berarti:

- hasil hari ini dapat ditelusuri sampai source observation, publication, adjustment, dan config yang membentuknya;
- hasil historis tidak berubah diam-diam;
- backtest atau replay memakai universe dan informasi yang benar-benar diketahui pada waktu tersebut;
- corporate action tidak menghasilkan false price movement;
- kegagalan source menghasilkan quarantine atau held state, bukan synthetic repair yang tidak terbukti;
- pergantian provider tidak memerlukan redesign domain, indikator, atau consumer contract;
- consumer menerima data yang konsisten dan alasan data usability yang dapat dijelaskan.

Target ini tidak menjanjikan profit. Market-data menjamin kualitas data product; kualitas alpha dan hasil trading tetap menjadi tanggung jawab policy watchlist dan evaluasi strategy di downstream domain, serta tidak memengaruhi status readiness market-data.

---


<!-- LEGACY_EXTRACT_BODY_END -->
