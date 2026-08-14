# Historical Replay and Data-Quality Verification (STRATEGY LOCKED)

## Purpose

This suite proves deterministic market-data meaning, not trading-strategy performance. It exercises both publication replay and as-known replay as defined by `../book/Replay_Verification_Contract_LOCKED.md`.

## Required scenario families

### Exact publication verification

- resolve an explicit immutable publication, not latest/current;
- verify frozen observations, temporal revisions, config, factors, formulas, artifacts, hashes, manifest, seal, reasons, and terminal state;
- prove an unchanged rerun is byte-identical and does not create a fake correction.

### Degraded acquisition and expectation

- provider outage remains missing delivery and cannot shrink the denominator;
- unknown expectation does not become holiday/dormancy;
- stale/schema-invalid/wrong-date/zero-price observations quarantine or hold;
- no prior-date result masquerades as requested-date fresh data.

### Temporal identity and status

- inactive-now/active-then listing remains in the historical universe;
- symbol change and symbol reuse resolve through stable listing identity;
- calendar/session/status revisions respect effective and knowledge time.

### Corporate actions and indicators

- synthetic price-break candidates never activate factors;
- verified event/factor revision produces coherent structural OHLC/volume;
- provider adjusted-close fallback is impossible;
- long-chain Wilder ATR matches an independent oracle, including a correction whose impact continues beyond fourteen sessions;
- actual traded value and close-volume proxy never share meaning or field identity.

### Correction and read path

- prior immutable publication remains auditable;
- a distinct corrected candidate becomes active only after complete validation and reseal;
- concurrent consumers read exactly one publication;
- explicit fallback retains prior effective date and stale/degraded state.

### As-known isolation

- later master, event, status, calendar, config, formula, and factor revisions are invisible before their recorded/known times;
- a declared later cutoff can expose them without rewriting earlier replay evidence.

## Per-run evidence

Record replay mode, fixture/manifest hash, requested/effective dates, knowledge cutoff, all frozen revision/snapshot IDs, expected/actual readiness and reason sets, field-level mismatch paths, artifact/manifest/seal hashes, executable build identity, and `PASS`/`FAIL`/`BLOCKED`.

Fixtures must be independently reviewed semantic oracles. Copying current implementation output into “expected” files without independent derivation is not acceptable proof.

## Acceptance

All required scenario families pass on MariaDB production semantics and the supported test mirror. Any missing family remains an open proof gap; historical green results for superseded rules do not close it.

## Capability boundary (LOCKED)

**What historical quality replay proves.** That the quality decisions recorded for past dates reproduce under their bound rules, and that quarantine, rejection, and hold outcomes were not applied retroactively.

**What it cannot prove.**

- **That past quality decisions were the right decisions.** Reproducing a verdict confirms the rule was applied consistently; a rule that was wrong then reproduces wrongly now, with full agreement.
- **That the defects it replays are the defects that occurred.** Only recorded defects can be replayed. A fault nobody detected at the time leaves no verdict to reproduce and no trace to find.
- **That improving rules improves history.** A rule strengthened today does not reclassify yesterday's publications; it changes what happens next, which is the intended behaviour and also the limit.

Consequently a clean historical quality replay may be cited as evidence that **recorded decisions were stable and rule-bound**, never as evidence that **historical data was sound**.
