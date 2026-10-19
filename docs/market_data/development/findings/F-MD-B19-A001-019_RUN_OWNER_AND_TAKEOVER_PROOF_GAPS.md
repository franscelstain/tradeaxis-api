# Finding — F-MD-B19-A001-019

- ID: `F-MD-B19-A001-019`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Raised at: 2026-10-19T17:13:15+07:00
- Severity: `P2`
- Status: `OPEN`

## Measured runtime gaps

`EodRunRepository::getOrCreateOwningRun()` selects active runs by requested date **and** source/request mode. The executed SQLite preflight probe obtains two different active run IDs for one requested date under different source modes. This does not establish the locked one-active-owner rule, even though publication promotion has later pointer safeguards. `cancelStaleActiveRuns()` cancels on elapsed time and records `STALE_ACTIVE_RUN_CANCELLED`; before allowing another owner it does not explicitly verify candidate trust, rule out ambiguous partial publication, or record a decision that covers those safety facts. Thus the takeover and anti-double-owner claims remain unproved.

The locked taxonomy calls an already verified duplicate target `SKIPPED` and a lock held by another fenced worker `BLOCKED`/`SKIPPED_LOCKED`; the observed unforced current-publication replacement is `HELD` with `RUN_LOCK_CONFLICT`. `MD-S067-R0016` and `MD-S067-R0017` stay unproved pending a correction that reconciles those exact meanings. This finding does not change the locked taxonomy or relabel the observed run.

## Scoped proof retained

Fourteen `ownership_and_recovery` predicates have bounded executed bases: a normal owner run produces coherent artifacts and hashes, seals its own candidate and finalizes; approved correction and force-replacement paths persist their resulting publication state; an unforced competing promotion and an injected promotion failure preserve the prior pointer. The unforced case also preserves the complete prior owner publication row and records the distinct competing run ID and its lock-conflict event. Six focused existing tests passed with 65 assertions, with further normal, low-coverage and correction tests. A force-interlock bypass mutant returned unexpected `SUCCESS`, then exact source bytes were restored and the control passed. Those scenarios do not prove general concurrent owner exclusion or a safe stale-owner takeover.

The other 13 ownership/recovery predicates remain `NOT_ASSESSED`: `MD-S067-R0016`, `MD-S067-R0017`, `MD-S077-R0003`, `MD-S077-R0004`, `MD-S077-R0013`, `MD-S077-R0025`, `MD-S077-R0027`, `MD-S077-R0028`, `MD-S077-R0031`, `MD-S077-R0033`, `MD-S077-R0034`, `MD-S077-R0035`, `MD-S077-R0039`. No owner decision is required to establish the already locked single-owner and takeover rules; implementation and independent runtime proof remain.

## E027 successor implementation and residual gap

E027 corrects the measured owner scope. A unique requested-date fence serializes publish-critical owner selection across source and request modes; a two-process SQLite race and cross-mode/promote competition leave one active owner. Hash, readable finalize, direct seal and pointer switch recheck ownership. A stale owner can be abandoned only after expiry and explicit assessment of candidate hashes, seal, publication and prior current-pointer integrity; ambiguous partial publication or unverified prior current state blocks takeover. Persisted cancellation, blocked decision and new-owner takeover events retain both run identities and the resulting fence. A partial candidate test preserves the complete prior readable pointer and publication row. Focused mutations of cross-mode scope, trust bypass and seal-fence bypass turn their guards red and are restored.

Eleven of the thirteen formerly unassessed owner predicates now have proposed E027 per-predicate runtime bases. `MD-S067-R0016` and `MD-S067-R0017` remain `NOT_ASSESSED`: the locked table requires `SKIPPED` for an already verified duplicate target and `BLOCKED`/`SKIPPED_LOCKED` for another fenced worker. Current run terminal schema and finalize routing still encode `SUCCESS`/`HELD`/`FAILED`; an unforced replacement of a different current publication remains `HELD`/`RUN_LOCK_CONFLICT`, which is neither automatically a verified duplicate nor a fenced worker status. The E027 owner-conflict event records a `BLOCKED` decision, but no run-level `BLOCKED`/`SKIPPED_LOCKED` terminal status is persisted for the rejected request. These two exact taxonomy obligations need an implementation correction and a separately verified duplicate-target fixture before admission. This is implementation work under existing authority, not a Project Owner decision or a reason to relabel the earlier HELD result.

## E028 successor resolution pending independent review

E027 independent review accepted six bounded owner bases and held MD-S077-R0003/R0004/R0013/R0034/R0035 for MariaDB and missing-fence proof. E028 uses an isolated migrated MariaDB InnoDB schema and two independent PHP processes behind a held date key: one active owner survives across source/request modes, the contender persists a non-readable BLOCKED run, and the owner fence names the surviving run. The actual deadlock observed without transaction retry is converted by a bounded retry into an audited owner conflict. Direct repository seal and promotion with no fence now refuse before candidate work; an injected non-owner cannot finalize readable, seal or promote. A prior current pointer/publication remains unchanged, and its historical raw read still works. The five held bases have successor proof, not five additional counted bases.

The locked taxonomy is now exercised on separate exact cases. A verified same-target candidate is SKIPPED while the current pointer and readable publication remain unchanged; a changed unforced replacement remains HELD/RUN_LOCK_CONFLICT. A cross-mode fenced worker persists COMPLETED/BLOCKED/NOT_READABLE with registered RUN_LOCK_CONFLICT and both run IDs, without becoming active. The MariaDB status enum accepts SKIPPED/BLOCKED. Both formerly unproved taxonomy predicates have new proposed bases, subject to independent review. No frozen status text was changed.
