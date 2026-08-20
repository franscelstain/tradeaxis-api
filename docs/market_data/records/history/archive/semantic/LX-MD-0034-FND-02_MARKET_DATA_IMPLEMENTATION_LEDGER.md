# Legacy Semantic Extract — LX-MD-0034-FND-02

- Source ID: `LS-MD-0034`
- Original path: `audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md`
- Original SHA1: `53F8C0BDBA19E2AB4F630A31731FDB2210E19CEF`
- Extract role: `FINDING`
- Source range: `L1611-L1625`
- Extract body SHA1: `58C9C804B23C12FB426FA33C9EF2534CC4871A4E`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Active findings

| Finding ID | Work order | Severity | Status | Owner contract | Current evidence | Required remediation |
|---|---|---|---|---|---|---|
| `F-030` | `W18` | P1 | OPEN | `Replay_Verification_Contract_LOCKED.md` | Lubang aturan self-generated sudah ditutup, tetapi fixture dengan expected values ber-author independen belum ada | Tahap 9 meng-author fixture tanpa mengambil expected output dari run target |
| `F-024` | `W12` | P0 | OPEN — replay proof only | `Price_Adjustment_Contract` + `Indicator_Registry_Baseline` | Tahap 8 sudah membuktikan selected product/factor/config binding dan fresh admitted reconstruction; proof replay independen belum dijalankan | Tahap 10 mengeksekusi fixture Tahap 9 dan menyimpan verdict admissible |
| `F-023` | `W21` | P0 | OPEN | `Test_Coverage_Closure_Contract` | Gate implementasi dan bukti consecutive activated sessions masih dipisah | Tahap 11 menilai `F-023a`; `F-023b` tetap gate operasi `O3` |
| `F-021` | `W19` | P1 | OPEN / `PRE_ACTIVATION_DEFERRED` | `Release_Gates` | Project masih pembangunan; activation date dan sesi operasional belum ada | `O1`/`O2` hanya setelah activation prerequisites terbukti |
| `F-020` | `W18` | P1 | OPEN | `Replay_Verification_Contract` | Replay lama self-generated/non-admissible; Tahap 8 tidak menjalankan replay | Tahap 9–10 menghasilkan fixture dan proof independen |
| `F-019` | `W17` | P1 | OPEN | `Read_Side_Enforcement_Anti_Bypass_Contract` | Belum ada konsumen domain nyata | Buktikan consumer nyata melalui gateway; tidak termasuk Tahap 8 |
| `F-010` | `W11` | P1 | PARTIAL / OPEN | `Corporate_Action_and_Adjustment_Policy` | Tiga event KSEI dalam scope Tahap 6/8 selesai, tetapi itu bukan authority event-complete/full-range | Rekonsiliasi external corporate action full-range sebelum klaim action-complete |

Hanya tujuh row di atas yang membentuk roster current. Detail finding tertutup tetap berada pada
register historis dan evidence tahapnya, bukan sebagai row aktif.


<!-- LEGACY_EXTRACT_BODY_END -->
