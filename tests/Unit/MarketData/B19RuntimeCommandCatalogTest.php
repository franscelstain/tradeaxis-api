<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class B19RuntimeCommandCatalogTest extends TestCase
{
    public function test_registered_market_data_commands_and_options_match_the_operator_catalog(): void
    {
        $root = dirname(__DIR__, 3);
        $listing = new Process([PHP_BINARY, $root.'/artisan', 'list', '--raw'], $root);
        $listing->mustRun();
        preg_match_all('/^market-data:[^\s]+/m', $listing->getOutput(), $found);
        $registered = $found[0];
        sort($registered);

        $readme = file_get_contents($root.'/docs/market_data/development/implementation/ops/commands/README.md');
        $this->assertStringContainsString('## Runtime-verified command options', $readme);
        $table = explode('## Runtime-verified command options', $readme, 2)[1];
        preg_match_all('/^\| `(market-data:[^`]+)` \| (.*?) \|\r?$/m', $table, $rows, PREG_SET_ORDER);
        $documented = array_column($rows, 1);
        $this->assertCount(count(array_unique($documented)), $documented, 'Catalog has duplicate commands.');
        sort($documented);
        $this->assertSame($registered, $documented, 'Catalog command identity differs from registered runtime.');

        $common = ['help', 'quiet', 'verbose', 'version', 'ansi', 'no-ansi', 'no-interaction', 'env'];
        foreach ($rows as $row) {
            $help = new Process([PHP_BINARY, $root.'/artisan', 'help', $row[1], '--format=json'], $root);
            $help->mustRun();
            $payload = json_decode($help->getOutput(), true, 512, JSON_THROW_ON_ERROR);
            $this->assertSame($row[1], $payload['name']);
            $runtimeOptions = [];
            foreach ($payload['definition']['options'] as $key => $option) {
                if (! in_array($key, $common, true)) $runtimeOptions[] = $option['name'];
            }
            preg_match_all('/`(--[^`]+)`/', $row[2], $matches);
            $this->assertSame($runtimeOptions, $matches[1], $row[1].' option catalog differs from runtime help.');
        }
    }
}
