# Legacy Semantic Extract — LX-MD-0038-DEC-01

- Source ID: `LS-MD-0038`
- Original path: `audit/OPS_ENVIRONMENT_BASELINE_INVENTORY.md`
- Original SHA1: `C4C8EC75AF93028E3F6AEFEF6E52E82B376969D5`
- Extract role: `DECISION`
- Source range: `L77-L85`
- Extract body SHA1: `9D52D153B0C13286E574A1871871DB3F923B3F74`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Composer / platform decision matrix

| Decision Point | Current State | Decision | Reason |
|---|---|---|---|
| Change `composer.json` PHP constraint | Current constraint allows PHP 8.4 by `^8.0` | DEFER_WITH_REASON | No Composer binary is available in container to regenerate `composer.lock` safely; avoid creating lock drift. |
| Add `config.platform.php` | Not present | DO_NOT_ADD_IN_THIS_PATCH | Platform override can hide real runtime PHP mismatch and still allow PHP 8.4 execution. Runtime guard is safer for evidence. |
| Runtime block unsupported PHP | Not present before patch | ADD | Blocks PHP 8.4 before Lumen vendor autoload and keeps command output clean. |
| Future CI baseline | No workflow found | DOCUMENT_MANUAL | If CI is added, use supported clean-output PHP, preferably PHP 8.3.x, and required extensions. |


<!-- LEGACY_EXTRACT_BODY_END -->
