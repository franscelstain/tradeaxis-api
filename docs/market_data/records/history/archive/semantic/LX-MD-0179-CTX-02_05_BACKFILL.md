# Legacy Semantic Extract — LX-MD-0179-CTX-02

- Source ID: `LS-MD-0179`
- Original path: `ops/commands/05_BACKFILL.md`
- Original SHA1: `7D024D1A49999C8FD30899BF32AC78581D6AE221`
- Extract role: `CONTEXT`
- Source range: `L31-L38`
- Extract body SHA1: `7508912D9FD158F30A2C843FCB4B66CE0118E8B4`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Operator meaning
Selesainya `market-data:backfill` berarti data import range sudah dicoba/dicatat.
Itu **bukan** berarti semua tanggal di range tersebut sudah published/readable.

Invalid atau missing date harus gagal aman dengan `status=BLOCKED`; date format salah harus memakai `COMMAND_INVALID_DATE_FORMAT`, dan input wajib yang hilang harus memakai `COMMAND_MISSING_REQUIRED_INPUT`.

---


<!-- LEGACY_EXTRACT_BODY_END -->
