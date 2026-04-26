# Force Replace Operator Control Policy LOCKED

Status: LOCKED  
Owner: Market Data Publication / Operator Control  
Scope: `market-data:promote` current publication replacement only.

---

## 1. Purpose

Force replace exists to remove unsafe manual SQL cleanup when an operator intentionally needs to replace an already-current readable publication for the same trade date.

The only supported mechanism is an explicit command-level operator action. Direct database cleanup is not the contract.

---

## 2. Definition

`force_replace` means:

> operator-authorized override of the normal current-publication lock conflict during promotion, while preserving all data-quality, coverage, seal, publishability, pointer, and audit requirements.

It is not a quality bypass.

---

## 3. Activation Rule

Force replace is inactive by default.

It is active only when the promote command receives:

`--force_replace=true`

When active, an audit reason is required:

`--force_replace_reason="operator reason"`

Compatible operator alias:

`--force_reason="operator reason"`

Without `--force_replace=true`, the existing deterministic lock behavior remains unchanged.

---

## 4. Replacement Rule

| Existing Current State | Force Flag | Result |
|---|---:|---|
| Valid readable sealed current exists | false | BLOCK / HELD with lock conflict |
| Valid readable sealed current exists | true | ALLOW controlled replacement |
| Invalid current pointer/current mirror exists | false | BLOCK and require repair |
| Invalid current pointer/current mirror exists | true | BLOCK and require repair |
| No current exists | false | ALLOW normal promotion |
| No current exists | true | ALLOW, but still audited as operator intent |

A valid current publication is defined as a current pointer/current publication mirror resolving to a sealed publication whose run is `SUCCESS` and `READABLE`.

Invalid integrity states must be repaired first. Force replace must not silently repair corrupted pointers.

---

## 5. Safety Rule

Force replace must still pass the same publishability path as a normal current promotion:

- coverage gate must pass;
- candidate publication must be sealed;
- candidate publication must have `sealed_at`;
- final outcome must be `terminal_status = SUCCESS`;
- final outcome must be `publishability_state = READABLE`;
- read-side current pointer resolution must pass strict post-finalize validation.

Force replace must not bypass:

- coverage gate;
- publishability decision;
- correction lifecycle;
- read-side pointer enforcement;
- invalid pointer/current integrity repair requirements.

---

## 6. Pointer Rule

When force replace is allowed:

- the previous publication remains stored;
- the previous publication is demoted with `is_current = 0`;
- the new publication is promoted with `is_current = 1`;
- `eod_current_publication_pointer` is switched to the new publication;
- `eod_runs.is_current_publication` mirrors are switched so only the new run is current;
- lineage fields on the new publication record the replaced publication:
  - `supersedes_publication_id`;
  - `previous_publication_id`;
  - `replaced_publication_id`.

Only one current publication and one current pointer may remain for the trade date.

---

## 7. Audit Trail Rule

Every executed force replace must create a run event:

`RUN_FORCE_REPLACE_EXECUTED`

The event payload must include:

- `force_replace = true`;
- `force_replace_reason`;
- `run_id`;
- `previous_publication_id`;
- `new_publication_id`;
- `new_publication_version`;
- `trade_date`.

The promote command summary must also expose `force_replace=true|false`.

---

## 8. Idempotency Rule

Force replace must not create duplicate pointer rows or double-current state.

Repeating the same already-finalized successful run must keep the existing idempotent finalize behavior and must not append a second finalization or duplicate force-replace event.

---

## 9. Operator Contract

Approved operator command shape:

`php artisan market-data:promote --requested_date=YYYY-MM-DD --source_mode=manual_file --run_id=<run_id> --force_replace=true --force_replace_reason="approved operator replacement reason"`

Compatible alias:

`php artisan market-data:promote --run_id=<run_id> --force_replace=true --force_reason="approved operator replacement reason"`

The operator must not manually delete pointer rows or reset `is_current` rows to force a replacement.

---

## 10. Done Criteria

This policy is satisfied only when:

- default promote still blocks uncontrolled replacement;
- explicit force replace can switch the current pointer safely;
- invalid current integrity is still blocked;
- audit event exists;
- command summary exposes force flag;
- tests cover uncontrolled block and controlled replace;
- audit files append the session result without deleting old history.
