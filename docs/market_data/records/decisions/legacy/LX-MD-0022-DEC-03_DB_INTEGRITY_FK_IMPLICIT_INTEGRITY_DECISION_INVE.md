# Legacy Semantic Extract — LX-MD-0022-DEC-03

- Source ID: `LS-MD-0022`
- Original path: `audit/DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_INVENTORY.md`
- Original SHA1: `BA5CB0819D76C0ADAEA2600174DA40EF3CFF16A3`
- Extract role: `DECISION`
- Source range: `L63-L78`
- Extract body SHA1: `7265F26361A699E3BC68B60F62D7EDC0D74F368E`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Live Artifact Relation Decision Matrix

| Relation | Current Enforcement | Failure Impact | FK Safe? | Guard Safe? | Decision | Reason |
|---|---|---|---:|---:|---|---|
| live artifact → ticker | PK on `tickers`, source mapper, coverage universe, artifact static guards | Orphan ticker row could pollute current artifact | No for this session | Yes | `IMPLICIT_GUARD_ACCEPTED` | Adding FK now risks broad migration/data cleanup; ticker validity is governed before artifact publication and must be tested |
| live artifact → trade date / calendar | calendar table + command/source date validation | Non-trading or wrong date rows | No for this session | Yes | `IMPLICIT_GUARD_ACCEPTED` | Calendar relation is operational/date validation, not stable FK on every artifact row |
| live artifact → run | NOT NULL + index + run/publication mirror guard | Wrong run context in current data | No | Yes | `IMPLICIT_GUARD_ACCEPTED` | Run identity is phase-dependent until finalize/publish |
| live artifact → publication | NOT NULL + index + publication-scoped artifact lookup | Stale/current row mismatch | No | Yes | `IMPLICIT_GUARD_ACCEPTED` | Current live table is replaceable current surface; history is FK-backed immutable proof |
| live artifact → current pointer | Read-side resolver + pointer guard | Read stale/non-current artifacts | No | Yes | `IMPLICIT_GUARD_ACCEPTED` | Current pointer validity is cross-table invariant, not row FK |
| history artifact → publication | PK + FK to publication | Historical orphan proof | Yes | Yes | `EXPLICIT_FK_REQUIRED` | Stable immutable relation; already enforced |
| publication → run | run id index + mirror guard | Publication not tied to successful readable run | No | Yes | `IMPLICIT_GUARD_ACCEPTED` | Avoid circular lifecycle block; repository validates mirror/final states |
| pointer → publication | FK + unique + PK | Broken current pointer | Yes | Yes | `EXPLICIT_FK_REQUIRED` | Stable pointer target relation; already enforced |
| pointer → run/version | index + `whereColumn` mirror checks | Pointer mismatch | No | Yes | `IMPLICIT_GUARD_ACCEPTED` | Must compare with publication/run state and coverage gate, not plain FK only |
| correction → prior/new run/publication | nullable indexed columns + correction repository/evidence guards | Broken correction lineage | No | Yes | `IMPLICIT_GUARD_ACCEPTED` | Valid lifecycle includes requested/approved states before all link fields exist |
| replay/evidence → historical publication | selector-scoped resolver + publication-scoped artifact export | Historical proof resolves current instead of selected publication | No | Yes | `IMPLICIT_GUARD_ACCEPTED` | Audit resolver must remain explicit-selector and reason-coded |


<!-- LEGACY_EXTRACT_BODY_END -->
