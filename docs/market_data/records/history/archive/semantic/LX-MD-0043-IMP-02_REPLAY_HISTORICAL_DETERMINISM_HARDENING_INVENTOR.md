# Legacy Semantic Extract — LX-MD-0043-IMP-02

- Source ID: `LS-MD-0043`
- Original path: `audit/REPLAY_HISTORICAL_DETERMINISM_HARDENING_INVENTORY.md`
- Original SHA1: `6831E28FEFD55DC99E3BEA0B303AC2A439016C86`
- Extract role: `IMPLEMENTATION`
- Source range: `L86-L94`
- Extract body SHA1: `D3EEBAC08BA5792AB173826EDA737EEDDE92780C`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Patch Matrix

| Gap | File | Change | Why Safe | Test Coverage | Status |
|---|---|---|---|---|---|
| Replay verify was current-pointer dependent | `ReplayVerificationService.php` | Added `resolvePublicationForReplayActualState()` with historical selector branch | Consumer resolver unchanged; historical proof uses evidence audit resolver | `ReplayVerificationServiceTest`, static guard | PATCHED |
| Replay actual output did not clearly label historical mode | `ReplayVerificationService.php` | Added expected/actual replay resolution contexts | Deterministic comparison extended, not loosened | `ReplayHistoricalDeterminismHardeningStaticGuardTest` | PATCHED |
| Historical artifacts needed publication-scoped path | `ReplayVerificationService.php` | Uses evidence publication-scoped reason/eligibility methods | No raw/current/latest fallback | static guard | PATCHED |
| Missing historical reason code registry | registry docs/seed | Added replay historical reason codes | Registry/seed synchronized | static guard | PATCHED |


<!-- LEGACY_EXTRACT_BODY_END -->
