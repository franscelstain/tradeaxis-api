<?php

use PHPUnit\Framework\TestCase;

/**
 * The hybrid integrity policy, derived from the schema rather than listed.
 *
 * `DbIntegrityFkImplicitIntegrityDecisionStaticGuardTest` checks three named tables against a
 * hand-written list of DDL fragments. That covers the tables someone remembered; an artifact
 * table added later is governed by nothing.
 *
 * The policy itself, HYBRID_REQUIRED, splits on one distinction:
 *
 * Live artifact tables (eod_bars, eod_indicators, eod_eligibility) are rewritten during import,
 * promote and correction. A physical foreign key on publication_id would make those lifecycles
 * order-dependent — the rows exist before the publication is finalised, and a correction replaces
 * them while the old publication is still current. So the relation is enforced by repository
 * guards instead, and the columns stay NOT NULL with a publication-scoped index so the guard has
 * something to stand on.
 *
 * History tables are immutable proof. Nothing rewrites them, so the relation is enforced
 * physically and the database refuses an orphan outright.
 *
 * Both halves matter and they fail differently. A history table missing its foreign key can
 * accumulate rows pointing at publications that no longer exist. A live table that gains one can
 * make a correction fail mid-flight, or block a publication from being discarded.
 */
class ArtifactIntegrityPolicyTest extends TestCase
{
    /** @var string|null */
    private static $schema = null;

    private function schema(): string
    {
        if (self::$schema === null) {
            $path = dirname(__DIR__, 3).DIRECTORY_SEPARATOR
                .str_replace('/', DIRECTORY_SEPARATOR, 'docs/market_data/db/Database_Schema_MariaDB.sql');

            $this->assertFileExists($path);
            self::$schema = file_get_contents($path);
        }

        return self::$schema;
    }

    /**
     * @return array<string, string> table name => DDL block
     */
    private function tableBlocks(string $pattern): array
    {
        preg_match_all(
            '/CREATE TABLE IF NOT EXISTS ('.$pattern.') \((?:.*?)\) ENGINE=InnoDB;/ms',
            $this->schema(),
            $matches,
            PREG_SET_ORDER
        );

        $blocks = [];

        foreach ($matches as $match) {
            $blocks[$match[1]] = $match[0];
        }

        ksort($blocks);

        return $blocks;
    }

    /**
     * @return array<string, string>
     */
    private function historyTables(): array
    {
        return $this->tableBlocks('eod_[a-z_]+_history');
    }

    /**
     * @return array<string, string>
     */
    private function liveArtifactTables(): array
    {
        return $this->tableBlocks('eod_(?:bars|indicators|eligibility)');
    }

    public function test_the_schema_parses_into_both_families(): void
    {
        // Guards the guard: a DDL formatting change that stopped matching would make every
        // assertion below pass against nothing.
        $this->assertNotEmpty($this->historyTables());
        $this->assertNotEmpty($this->liveArtifactTables());
        $this->assertSame(
            array_keys($this->liveArtifactTables()),
            array_map(function ($name) {
                return str_replace('_history', '', $name);
            }, array_keys($this->historyTables())),
            'Every live artifact table must have a history counterpart and vice versa.'
        );
    }

    /**
     * Immutable proof is protected by the database itself.
     */
    public function test_every_history_table_has_a_physical_publication_foreign_key(): void
    {
        $missing = [];

        foreach ($this->historyTables() as $table => $block) {
            if (strpos($block, 'FOREIGN KEY (publication_id) REFERENCES eod_publications(publication_id)') === false) {
                $missing[] = $table;
            }
        }

        $this->assertSame(
            [],
            $missing,
            'History tables are immutable proof and must refuse orphan rows at the database level.'
        );
    }

    /**
     * Live tables are rewritten mid-lifecycle, so the same constraint would break the lifecycle
     * it is meant to protect.
     */
    public function test_no_live_artifact_table_has_a_physical_publication_foreign_key(): void
    {
        $unexpected = [];

        foreach ($this->liveArtifactTables() as $table => $block) {
            if (strpos($block, 'FOREIGN KEY (publication_id)') !== false) {
                $unexpected[] = $table;
            }
        }

        $this->assertSame(
            [],
            $unexpected,
            'A foreign key here would make import, promote and correction order-dependent. '
            .'The relation is enforced by repository guards, which SealedArtifactMutationGuardTest drives.'
        );
    }

    /**
     * The implicit guard needs the columns to be there and to be indexed. Without NOT NULL the
     * guard has nothing to check; without the index every publication-scoped read is a scan.
     *
     * @dataProvider mandatoryColumns
     */
    public function test_live_artifact_tables_carry_mandatory_lifecycle_context(string $column): void
    {
        $missing = [];

        foreach ($this->liveArtifactTables() as $table => $block) {
            if (strpos($block, $column.' BIGINT UNSIGNED NOT NULL') === false) {
                $missing[] = $table;
            }
        }

        $this->assertSame([], $missing, 'Live artifact tables must carry a mandatory '.$column.'.');
    }

    public function mandatoryColumns(): array
    {
        return [
            'publication_id' => ['publication_id'],
            'run_id' => ['run_id'],
        ];
    }

    public function test_live_artifact_tables_index_the_publication_scoped_lookup(): void
    {
        $missing = [];

        foreach ($this->liveArtifactTables() as $table => $block) {
            if (strpos($block, 'publication_id, trade_date, ticker_id') === false) {
                $missing[] = $table;
            }
        }

        $this->assertSame(
            [],
            $missing,
            'Candidate coverage reads rows by publication, date and ticker; that lookup must be indexed.'
        );
    }

    /**
     * The pointer is a single row per trading day and nothing rewrites it in place, so it takes
     * the physical constraint. This is the third case in the policy and the one that shows the
     * split is about mutability rather than about which side of the read a table sits on.
     */
    public function test_the_current_pointer_has_a_physical_publication_foreign_key(): void
    {
        $pointer = $this->tableBlocks('eod_current_publication_pointer');

        $this->assertNotEmpty($pointer);
        $this->assertStringContainsString(
            'FOREIGN KEY (publication_id) REFERENCES eod_publications(publication_id)',
            $pointer['eod_current_publication_pointer']
        );
    }

    /**
     * The policy is a decision, and a decision that is not written down gets rediscovered as a
     * bug. The schema must say which side of the split it is on and why.
     */
    public function test_the_policy_is_stated_in_the_schema_itself(): void
    {
        $schema = $this->schema();

        $this->assertStringContainsString('Final policy: HYBRID_REQUIRED', $schema);
        $this->assertStringContainsString('do not add physical FK constraints to', $schema);
        $this->assertStringContainsString('import/promote/correction/replay lifecycles', $schema);
    }
}
