<?php

namespace Tests\Support\MarketData;

/**
 * One definition of what "the schema" is for this domain.
 *
 * The deployed schema is **not** the migrations alone. `2026_03_22_000003_create_market_data_core_schema.php`
 * executes `docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql`, which creates
 * 28 tables; the migrations then add to it. A guard that reads only `database/migrations/**` proves its
 * claim over a subset of the real surface, which is how `MD-B01-A007` and `MD-B01-A009` were written.
 * Neither claim turned out to be wrong, but both were narrower than they read. This trait exists so
 * that fact has a single home rather than three drifting copies.
 *
 * Comments are stripped from both sources. A SQL comment in the base schema states the eligibility
 * disownment — "`eligible` is not ranking, alpha, tradability approval, or watchlist policy" — and a
 * guard that reads comments as surfaces would flag the contract being honoured as the contract being
 * broken. That mistake has recurred often enough in this stage to be worth designing out here.
 */
trait ReadsMarketDataSchema
{
    private function repositoryRoot(): string
    {
        return dirname(__DIR__, 3);
    }

    private function baseSchemaPath(): string
    {
        return $this->repositoryRoot().'/docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql';
    }

    private function stripSqlComments(string $sql): string
    {
        $sql = preg_replace('!/\*.*?\*/!s', '', $sql);

        return preg_replace('/^\s*--[^\n]*$/m', '', $sql);
    }

    private function stripPhpComments(string $source): string
    {
        $out = '';
        foreach (token_get_all($source) as $token) {
            if (is_array($token)) {
                if ($token[0] === T_COMMENT || $token[0] === T_DOC_COMMENT) {
                    continue;
                }
                $out .= $token[1];
                continue;
            }
            $out .= $token;
        }

        return $out;
    }

    /**
     * The full schema surface: base SQL plus every migration, comments removed from both.
     */
    private function schemaSurface(): string
    {
        $text = $this->stripSqlComments((string) file_get_contents($this->baseSchemaPath()))."\n";

        foreach (glob($this->repositoryRoot().'/database/migrations/*.php') as $migration) {
            $text .= $this->stripPhpComments((string) file_get_contents($migration))."\n";
        }

        return $text;
    }

    /**
     * Every table and column across both sources.
     *
     * @return array<string,array<string,bool>> table => column => true
     */
    private function schemaColumnMap(): array
    {
        $out = [];

        // base SQL: CREATE TABLE blocks
        $sql = $this->stripSqlComments((string) file_get_contents($this->baseSchemaPath()));
        $table = null;
        foreach (explode("\n", $sql) as $line) {
            $trimmed = trim($line);
            if (preg_match('/^CREATE TABLE(?: IF NOT EXISTS)?\s+`?([a-z0-9_]+)`?\s*\(/i', $trimmed, $m)) {
                $table = strtolower($m[1]);
                $out[$table] = isset($out[$table]) ? $out[$table] : [];
                continue;
            }
            if ($table === null) {
                continue;
            }
            if (strpos($trimmed, ')') === 0 || preg_match('/^\)\s*ENGINE/i', $trimmed)) {
                $table = null;
                continue;
            }
            if (preg_match('/^`?([a-z_][a-z0-9_]*)`?\s+(BIGINT|INT|SMALLINT|TINYINT|VARCHAR|CHAR|TEXT|LONGTEXT|MEDIUMTEXT|DATE|DATETIME|TIMESTAMP|DECIMAL|DOUBLE|FLOAT|JSON|ENUM|BOOLEAN|BLOB|BINARY)/i', $trimmed, $m)) {
                $out[$table][strtolower($m[1])] = true;
            }
        }

        // migrations: Blueprint calls
        $table = null;
        foreach (glob($this->repositoryRoot().'/database/migrations/*.php') as $migration) {
            foreach (explode("\n", $this->stripPhpComments((string) file_get_contents($migration))) as $line) {
                if (preg_match("/Schema::(?:create|table)\(\s*'([a-z0-9_]+)'/", $line, $m)) {
                    $table = $m[1];
                    $out[$table] = isset($out[$table]) ? $out[$table] : [];
                }
                if ($table !== null && preg_match("/->\s*[a-zA-Z]+\(\s*'([a-z0-9_]+)'/", $line, $m)) {
                    $out[$table][$m[1]] = true;
                }
            }
        }

        return $out;
    }
}
