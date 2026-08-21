<?php

use PHPUnit\Framework\TestCase;

/**
 * `MD-B01-A014` proof for the `eligible` compatibility-alias cluster in
 * `Domain_Boundary_Invariants_LOCKED.md`.
 *
 * `MD-S020-R0068` and `MD-S020-R0071` were classified `REFERENCE_ONLY` until this attempt, so the
 * one prohibition in the cluster that enumerates the surfaces the alias may not reach carried no
 * proof obligation while its weaker siblings did. `MD-S020-R0067` remains blocked by
 * `F-MD-B01-A003-001`; this suite also pins that finding's corrected measurement so it cannot drift
 * back to the word-sense count it was first published with.
 */
class AliasNamingAndMeaningBoundaryTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    private function read(string $relative): string
    {
        $path = $this->root().'/'.$relative;
        $body = @file_get_contents($path);
        if ($body === false) {
            $this->fail('Canonical artifact is unreadable: '.$relative);
        }

        return (string) $body;
    }

    /**
     * `MD-S020-R0068`: `data_usable` is the canonical field and `eligible` is a compatibility alias
     * retained so existing consumers do not break.
     *
     * Absence is only evidence with a positive locator, so the canonical field is asserted present
     * on the same surfaces the alias appears on. A rename that made both searches find nothing would
     * otherwise turn this green.
     */
    public function test_data_usable_is_the_canonical_field_and_eligible_is_only_its_alias(): void
    {
        $config = $this->read('config/market_data.php');
        $this->assertMatchesRegularExpression(
            "/'data_usability_field'\s*=>\s*'data_usable'/",
            $config,
            'the canonical field must be declared in configuration'
        );
        $this->assertMatchesRegularExpression(
            "/'compatibility_eligibility_field'\s*=>\s*'eligible'/",
            $config,
            'the alias must be declared as a compatibility field, not as the canonical one'
        );

        $readProduct = $this->read('app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php');
        $this->assertStringContainsString("\$result['data_usable']", $readProduct, 'the read product must expose the canonical field');
        $this->assertStringContainsString("\$result['eligible']", $readProduct, 'the alias must remain for existing consumers');
        $this->assertMatchesRegularExpression(
            '/\$result\[\'eligible\'\]\s*=\s*\$result\[\'data_usable\'\];\s*\/\/\s*compatibility alias only/',
            $readProduct,
            'the alias must be derived from the canonical field and marked as compatibility only'
        );
    }

    /**
     * `MD-S020-R0071`, positive half: a surface that carries the data-usability decision states the
     * canonical name. The read product exposes `data_usable` and an explicit `eligibility_state`
     * beside the alias, so a consumer reading the payload sees the meaning, not only the alias.
     */
    public function test_a_data_usability_surface_states_the_canonical_name_beside_the_alias(): void
    {
        $readProduct = $this->read('app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php');

        $this->assertMatchesRegularExpression(
            '/\$result\[\'eligibility_state\'\]\s*=\s*\$result\[\'data_usable\'\]\s*\?\s*\'DATA_USABLE\'\s*:\s*\'DATA_NOT_USABLE\'/',
            $readProduct,
            'the explicit state must spell the data-usability meaning out'
        );
        $this->assertMatchesRegularExpression(
            '/\'elig\.reason_code as data_usability_reason_code\'/',
            $readProduct,
            'the reason projection must be named for data usability, not for the alias'
        );
    }

    /**
     * `MD-S020-R0071`, prohibition half, for the two surfaces its enumeration names that no current
     * guard covered: configuration keys and API fields.
     *
     * `ScopeProductAndTimeBoundaryTest::test_the_eligible_alias_is_not_propagated_to_a_new_surface`
     * already pins columns, enum vocabulary, reason codes, and command signatures.
     */
    public function test_no_configuration_key_or_api_field_is_named_with_the_alias(): void
    {
        $config = $this->read('config/market_data.php');
        preg_match_all("/^\s*'([A-Za-z0-9_]+)'\s*=>/m", $config, $keys);
        $this->assertGreaterThan(100, count($keys[1]), 'the configuration key parse must reach the file body');
        foreach ($keys[1] as $key) {
            $this->assertStringNotContainsStringIgnoringCase(
                'eligible',
                $key,
                'no configuration key may be named with the alias; new surfaces use data_usable'
            );
        }

        $apiFields = [];
        foreach ($this->phpFiles($this->root().'/app/Http') as $file) {
            $body = (string) file_get_contents($file);
            if (preg_match_all("/'([A-Za-z0-9_]*eligible[A-Za-z0-9_]*)'\s*=>/i", $body, $found)) {
                foreach ($found[1] as $field) {
                    $apiFields[] = basename($file).':'.$field;
                }
            }
        }
        $this->assertSame([], $apiFields, 'no API response field may be named with the alias');
    }

    /**
     * `F-MD-B01-A003-001` corrected measurement.
     *
     * The finding first counted every active document containing the bare word `eligible` and
     * reported ten frozen strategy contracts. Eight of those ten never use the alias at all: they
     * use the English adjective in a different sense — `success-eligible` runs, `promotion-eligible`
     * candidates, artifacts `eligible` for purge — or the domain noun `eligibility`. The obligation
     * in `MD-S020-R0067` is about the compatibility field named `eligible`, so the subject of the
     * scan has to be the identifier, not the word.
     *
     * Measured on the identifier, exactly one frozen strategy contract fails, and it is the one the
     * finding correctly called the most consequential. The block is real; its scope was tenfold.
     */
    public function test_the_alias_meaning_repetition_gap_is_measured_on_the_identifier_not_the_word(): void
    {
        [$usingDocs, $withoutRepetition] = $this->aliasIdentifierScan();

        $this->assertGreaterThan(15, count($usingDocs), 'the scan must reach the documents that use the alias');
        $this->assertSame(
            [
                'authority/strategy/book/CONSUMER_READ_CONTRACT_LOCKED.md',
                'authority/strategy/registry/Volume_and_Turnover_Normalization_LOCKED.md',
                'development/implementation/CI-MD-B01-A010-001_TERM_OWNERSHIP_AND_SCHEMA_SURFACE.md',
                'records/evidence/E-MD-B00-A001-001_BASELINE_INVENTORY.md',
                'records/evidence/E-MD-B01-A002-001_DATE_DRIVEN_AND_PROVIDER_ABSTRACTION.json',
            ],
            $withoutRepetition,
            'a new document may not use the alias identifier without repeating that it means data_usable'
        );
    }

    /**
     * The word-sense scan the finding first used would flag documents that never touch the alias.
     * Pinning that difference keeps the corrected measurement from being re-broken by someone
     * reaching for the simpler pattern.
     */
    public function test_the_word_sense_scan_would_flag_documents_that_never_use_the_alias(): void
    {
        $wordSenseOnly = [
            'authority/strategy/book/Dataset_Seal_and_Freeze_Contract_LOCKED.md',
            'authority/strategy/book/EOD_Cutoff_and_Finalization_Contract_LOCKED.md',
            'authority/strategy/book/Finalize_Lock_And_Pointer_Behavior_LOCKED.md',
            'authority/strategy/book/Manual_File_Publishability_Policy_LOCKED.md',
            'authority/strategy/book/Publication_Lock_And_Replacement_Policy_LOCKED.md',
            'authority/strategy/ops/Commands_and_Runbook_LOCKED.md',
        ];

        [$usingDocs] = $this->aliasIdentifierScan();
        foreach ($wordSenseOnly as $relative) {
            $body = $this->read('docs/market_data/'.$relative);
            $this->assertMatchesRegularExpression('/eligib/i', $body, $relative.' must still contain the word');
            $this->assertNotContains($relative, $usingDocs, $relative.' does not use the alias identifier');
        }
    }

    /**
     * Identifier-sense scan over active market-data documents.
     *
     * @return array{0:array<int,string>,1:array<int,string>}
     */
    private function aliasIdentifierScan(): array
    {
        $base = $this->root().'/docs/market_data';
        $using = [];
        $missing = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }
            $path = str_replace('\\', '/', $file->getPathname());
            if (strpos($path, '/records/history/') !== false) {
                continue;
            }
            if (! preg_match('/\.(md|json|csv)$/', $path)) {
                continue;
            }
            $relative = ltrim(substr($path, strlen(str_replace('\\', '/', $base))), '/');
            if ($relative === 'authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv') {
                continue; // the matrix quotes strategy verbatim; it is source identity, not a contract
            }
            $body = (string) file_get_contents($path);
            if (! $this->usesAliasIdentifier($body)) {
                continue;
            }
            $using[] = $relative;
            if (! preg_match('/data[_ -]?usab/i', $body)) {
                $missing[] = $relative;
            }
        }
        sort($using);
        sort($missing);

        return [$using, $missing];
    }

    /**
     * True when the text uses `eligible` as a field/flag identifier rather than as the English
     * adjective. `success-eligible`, `promotion-eligible`, and "eligible for `READABLE`" are the
     * adjective and are not this rule's subject.
     */
    private function usesAliasIdentifier(string $text): bool
    {
        foreach (preg_split('/\R/', $text) as $line) {
            if (! preg_match('/eligible/i', $line)) {
                continue;
            }
            if (preg_match('/`eligible`/', $line)
                || preg_match('/\beligible\s*(=|==|:)\s*(1|0|true|false)\b/i', $line)
                || preg_match('/[\'"]eligible[\'"]/', $line)
                || preg_match('/\beligible\b\s*(column|field|flag|boolean)/i', $line)
                || preg_match('/(column|field|flag|boolean)\s+`?eligible`?/i', $line)) {
                return true;
            }
        }

        return false;
    }

    /** @return array<int,string> */
    private function phpFiles(string $directory): array
    {
        if (! is_dir($directory)) {
            return [];
        }
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isFile() && substr($file->getFilename(), -4) === '.php') {
                $files[] = $file->getPathname();
            }
        }
        sort($files);

        return $files;
    }
}
