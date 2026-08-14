<?php

/**
 * F-036 — the backtest evidence-schema document must not drift from the deployed schema.
 *
 * `../backtest/Replay_Results_Schema_MariaDB.sql` is one of stage 18's five assigned documents, and
 * after `F-025` widened comparison_result it still declared the four-value enum and still listed
 * four meanings under LOCKED SEMANTICS. Nothing noticed: `db/Database_Schema_MariaDB.sql` was
 * updated, this document was not, and no guard compared either of them to the database. It surfaced
 * only because a fourth read-only re-audit went looking.
 *
 * Two directions are pinned, because the earlier miss was in the second one. The DDL must match the
 * deployed column, and the prose that tells a reader what the values mean must name every value —
 * a vocabulary the contract describes more narrowly than reality is its own kind of stale claim.
 */
class ReplayResultsSchemaDocumentSyncTest extends TestCase
{
    private const DOCUMENT = 'docs/market_data/backtest/Replay_Results_Schema_MariaDB.sql';

    public function test_the_document_enum_matches_the_deployed_column(): void
    {
        $documented = $this->documentedEnumMembers();
        $deployed = $this->deployedEnumMembers();

        sort($documented);
        sort($deployed);

        $this->assertSame(
            $deployed,
            $documented,
            self::DOCUMENT.' declares a different comparison_result vocabulary than the deployed column'
        );
    }

    public function test_every_enum_member_is_explained_in_the_locked_semantics(): void
    {
        $document = $this->read();
        $semantics = strstr($document, 'LOCKED SEMANTICS');

        $this->assertNotFalse($semantics, 'the document must retain its LOCKED SEMANTICS block');

        foreach ($this->documentedEnumMembers() as $member) {
            $this->assertStringContainsString(
                $member.':',
                $semantics,
                $member.' is declared in the enum but never explained; a reader would take the vocabulary to be narrower than it is'
            );
        }
    }

    private function documentedEnumMembers(): array
    {
        $document = $this->read();

        $this->assertTrue(
            (bool) preg_match("/comparison_result\s+ENUM\(([^)]*)\)/i", $document, $matches),
            'comparison_result must be declared as an ENUM in '.self::DOCUMENT
        );

        preg_match_all("/'([A-Z_]+)'/", $matches[1], $members);

        return $members[1];
    }

    private function deployedEnumMembers(): array
    {
        try {
            $pdo = new PDO(
                'mysql:host=127.0.0.1;dbname=tradeaxis',
                'root',
                '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3]
            );
            $type = $pdo->query(
                "SELECT COLUMN_TYPE FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'md_replay_daily_metrics'
                   AND COLUMN_NAME = 'comparison_result'"
            )->fetchColumn();
        } catch (\Throwable $e) {
            $this->markTestSkipped('no reachable MariaDB; document/schema sync cannot be checked on SQLite');
        }

        if ($type === false) {
            $this->markTestSkipped('md_replay_daily_metrics.comparison_result not present on the reachable database');
        }

        preg_match_all("/'([A-Z_]+)'/", (string) $type, $members);

        return $members[1];
    }

    private function read(): string
    {
        return (string) file_get_contents(__DIR__.'/../../../'.self::DOCUMENT);
    }
}
