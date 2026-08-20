# DB Schema and Migration Sync Contract (STRATEGY LOCKED)

Documentation specification status: **`DOCUMENTATION_READY`**. Rollout rows marked open describe future migration/writer/backfill/enforcement work and do not weaken the target schema meaning.

## Authority order

1. current domain owner contract;
2. semantic schema contract and dictionary;
3. clean-install baseline plus ordered forward migrations;
4. supported MariaDB runtime schema;
5. SQLite test mirror;
6. repository/query code and tests.

Historical `LOCKED` documents, migrations, or green tests cannot override a corrected upstream owner contract.

## Synchronization rule

Every table/field/index/reference/status value used by the application is represented consistently in the semantic contract, dictionary, migration path, MariaDB result, SQLite mirror, repository payloads, and tests—or is recorded as an explicit rollout gap that blocks production relock.

The following must agree: name/meaning, type/unit, nullability/default, primary and temporal keys, uniqueness/overlap policy, indexes, reference/FK intent, immutable/mutable role, enum/state domain, timestamp/effective/known-time semantics, JSON serialization, and hash participation.

`Database_Schema_MariaDB.sql` is the legacy clean-install base executed by the core migration, not permission to edit deployed databases. Existing databases evolve only through forward migrations. The current target is base plus all later migrations.

## Drift detection is required (LOCKED)

The sentence above states the **target**. Nothing above requires anyone to check that a given database reaches it, and an unverified target is an assumption.

The failure this prevents is not corruption; it is a database that is simply **behind**, which produces no error anywhere. Code compiled against the intended schema passes its tests, because the test mirror is hand-written to the intended schema. Runtime paths that touch the unapplied tables fail only when exercised, and paths not yet wired never fail at all. Every surface reports health while the deployed shape and the intended shape have diverged.

Required, on its own cadence and independent of any deployment:

- **Applied-versus-available migration comparison.** Every migration file present must appear in the applied-migration record. A file present but unapplied is a **schema drift finding**, named with the migration identifier.
- **Deployed-versus-mirror table and column comparison.** The test mirror encodes the intended shape. A table or column present in one and absent in the other is a finding regardless of direction, because both directions have occurred: tables introduced in migrations but never applied, and columns present in the deployed database but absent from both migrations and mirror.
- **Explicit result.** Counts for each direction and the identifiers involved are recorded. An unverified environment is declared as such.

Rules:

- A conformance, replay, or activation claim naming a database must state its applied-migration position. **Green tests are not evidence of deployed schema state**, since the mirror is independent of the deployment.
- Drift is closed by applying forward migrations, never by editing the deployed database to match, and never by editing the mirror to match a stale deployment.
- Where runtime code depends on a table the deployed database lacks, that dependency is unrunnable in that environment. Recording the dependency as implemented is a false claim until the drift is closed.

## V2 rollout matrix

| Area | Migration state | Runtime/code state | Relock state |
|---|---|---|---|
| immutable observations | additive table introduced | writer/linkage adoption and immutability enforcement required | open |
| full config snapshots | additive table/bindings introduced | resolver/backfill/non-null seal enforcement required | open |
| issuer/instrument/listing/symbol mapping | additive temporal tables introduced | master migration and as-of repositories required | open |
| calendar/status revisions | additive temporal tables introduced | authoritative feeds and as-known resolution required | open |
| event/factor revisions | additive tables introduced | verified lifecycle/factor builder and legacy isolation required | open |
| actual EOD fields | nullable columns introduced | source population and completeness tests required | open |
| indicator/eligibility facts | nullable columns introduced | computation/read-model adoption required | open |
| publication lineage/readiness | additive bindings introduced | manifest/seal/pointer enforcement required | open |
| direct price repair | draft migration neutralized; legacy columns removed from baseline | command/service/mutation-path proof still required | open |

Nullable rollout columns deliberately avoid fabricating provenance on old rows. They are not a weaker accepted production state; a later enforcement migration is required after verified backfill.

## SQLite mirror

`tests/Support/UsesMarketDataSqlite.php` mirrors every target table/column that repositories or integration tests exercise. Allowed differences are limited to JSON→text, enum→string, unsigned→integer, timestamp syntax, disabled FKs where documented, and compatibility surrogate IDs that are never queried as domain identity.

Schema-sync tests compare required columns/indexes and reject SQLite-only business fields. A green SQLite suite without MariaDB upgrade/constraint proof is insufficient.

## Migration policy

- use forward migrations for deployed shape changes;
- preserve existing data and publication evidence;
- make backfill and enforcement distinct observable stages when nullability is needed for rollout;
- never seed synthetic actions/factors from price behavior;
- never add a direct history-repair surface;
- include idempotency/preflight for partially drifted developer databases;
- document irreversible operations and rehearse backup/restore;
- update dictionary, repositories, test mirror, fixtures, and audit in the same strategy order.

## Acceptance evidence

Production relock needs clean-install and supported-upgrade schema dumps, migration table state, MariaDB-vs-contract diff, SQLite parity test, backfill counts/rejections, enforced non-null/reference/overlap checks, repository integration tests, rollback/restore rehearsal, and negative mutation/repair tests. Missing or blocked evidence remains open.
