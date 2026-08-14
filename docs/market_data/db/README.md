# Market Data Database Documentation Index

Current state: **strategy-corrected V2 foundation; rollout/enforcement proof open**.

Read in this order:

1. `Database_Schema_Contracts_MariaDB.md` — semantic persistence contract.
2. `MARKET_DATA_DICTIONARY.md` — table/field meanings and transitional roles.
3. `DB_Schema_And_Migration_Sync_Contract_LOCKED.md` — synchronization and proof rules.
4. `Database_Schema_MariaDB.sql` — legacy clean-install base executed by the core migration.
5. ordered `database/migrations/**` — forward evolution; V2 begins with `2026_08_02_000001_add_market_data_strategy_v2_foundation.php`.

The effective runtime shape is base plus every applied forward migration. Editing the SQL base does not upgrade an existing database.

Key target authorities:

- stable historical identity is `listing_id`, not current ticker text/state;
- `md_source_observations` owns immutable acquisition evidence;
- `md_config_snapshots` owns full resolved configuration identity;
- temporal symbol/mapping/calendar/status/event tables own as-of/as-known resolution;
- factor sets/factors own structural adjustment; price-break detector rows never do;
- `eod_*_history` plus publication lineage are immutable authority; current tables are projections;
- actual traded value and close-volume proxy are distinct;
- consumer authority is the active publication pointer plus versioned read gateway.

Migration V2 uses nullable columns for safe rollout. Null required lineage is not an accepted production state. Repository writers, historical backfill, constraints/references, MariaDB upgrade proof, and semantic fixtures remain required before relock.
