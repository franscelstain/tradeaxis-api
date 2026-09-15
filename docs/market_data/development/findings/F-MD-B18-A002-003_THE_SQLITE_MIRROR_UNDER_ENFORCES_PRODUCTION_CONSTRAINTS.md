# Finding — `F-MD-B18-A002-003`

- ID: `F-MD-B18-A002-003`
- Raised by: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Owner stage: **`MD-B21`** (approved deferral; originating attempt remains `MD-B18-A002`)
- Raised at: 2026-09-10T17:40:00+07:00
- Severity: `P2`
- Status: `OPEN — GOVERNED_DEFERRAL_TO_MD-B21`
- Class: `PROOF_SURFACE_WEAKER_THAN_PRODUCTION`
- Blocks: nothing currently bound; it bounds what the mirror can be cited for
- Blocks strategy change: `NO`

## Statement

`MD-S003` acceptance requires the scenario families to pass "on MariaDB production semantics **and**
the supported test mirror". Building the MariaDB half for `MD-S003-R0025` surfaced two constraints
that production enforces and the mirror does not. Both were found by fixtures failing on MariaDB
that pass on the mirror — which is the mirror being weaker, not the fixtures being wrong.

| Constraint | Production (MariaDB) | Mirror (SQLite) |
|---|---|---|
| `eod_current_publication_pointer.updated_at` | `NOT NULL`, no default — an insert omitting it is refused with `1364` | nullable in the mirror definition; the same insert succeeds |
| `eod_current_publication_pointer.publication_id` → `eod_publications.publication_id` | foreign key `fk_current_publication_pointer_publication`, enforced | `UsesMarketDataSqlite` creates every connection with `'foreign_key_constraints' => false` |

The second is the material one. The mirror disables foreign keys **globally**, so every referential
constraint in the market-data schema is unenforced across all 91 DB-backed guards. A change that
orphaned a pointer, a lineage binding, or a history row from its publication would pass the whole
mirror suite and fail in production.

This does not invalidate the mirror guards: each asserts a behaviour, and the behaviour is what they
establish. It bounds what the mirror can be *cited* for. A claim that referential integrity holds
cannot rest on a substrate that does not enforce it, in the same way `MD-S050-R0041` says a
publication-only corpus cannot support a point-in-time property.

## How it was found

Not by reading the mirror definition. By writing fixtures for `B18ScenarioFamiliesOnMariaDbTest` and
having them refused twice by constraints no mirror test had ever met.

## Remediation options

### Approved disposition — 2026-09-13

D-MD-B18-A002-001 records user approval of deferral to MD-B21 under MD-DEP-0010. No mirror
constraint is fixed or waived by that decision. Until constraint hardening is executed there,
SQLite is behavioral support only; referential-integrity and production-nullability claims need
current MariaDB proof for the exact predicate. A missing B18 constraint proof still blocks B18.
MD-B21 must read this finding at its recorded entry and add an explicit cross-stage baseline/CI
relationship before remediation. This does not open MD-B21 or add an executable resume point.

### Original options (preserved as rationale)

Not taken here; both are scope decisions.

1. **Enable foreign keys in the mirror** (`'foreign_key_constraints' => true`). Closest to
   production, and likely to fail a number of existing fixtures that seed child rows without
   parents — which is the point, but it is a broad change to a shared trait.
2. **Mirror the `NOT NULL` and default declarations** so column nullability matches the migrations.
   Narrower, and does nothing for the foreign keys.
3. **Leave the mirror as it is and rely on the MariaDB path** for constraint-bearing claims, keeping
   the mirror for behaviour. This is the current de facto position and is only sound while the
   MariaDB path actually covers the families that turn on a constraint — today that is the
   correction-and-read-path family and nothing else.

Whichever is chosen, the mirror's limits should be stated where they can be read, not left to be
rediscovered by the next fixture that meets a constraint.
