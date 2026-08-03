<?php

use PHPUnit\Framework\TestCase;

/**
 * One prohibition, applied to the whole runtime.
 *
 * Twelve static guard files carried this same check, each against its own hand-picked list of
 * paths. Between them they covered a few dozen files, so the rule held exactly where somebody had
 * remembered to apply it and nowhere else. A new repository added tomorrow would be governed by
 * none of them.
 *
 * The rule itself is global, so it is expressed globally here: nothing in app/ may resolve a
 * dataset by taking whichever trade date is newest.
 *
 * Why it matters. The current publication for a trading day is resolved through the pointer
 * table, which only points at a publication that is sealed, current, and produced by a run that
 * succeeded with coverage passing. Taking MAX(trade_date) skips every one of those conditions. On
 * a day whose publication was correctly withheld — held for coverage, blocked as an empty
 * dataset, quarantined mid-correction — a latest-date read serves the withheld data as though it
 * were published, or serves a neighbouring day's data under today's label. Both are silent: the
 * query succeeds and returns plausible rows.
 *
 * This is a prohibition, which is why it stays a source check. Execution can prove that a
 * particular call resolves through the pointer; it cannot prove that no shortcut exists anywhere.
 * The positive half — that consumer reads actually refuse non-pointer-resolved rows — is proven
 * by ReadablePublicationReadContractIntegrationTest, which drives both consumer repositories
 * through ten broken publication states.
 */
class ReadPathShortcutProhibitionTest extends TestCase
{
    /**
     * Resolving a dataset by recency rather than by the pointer.
     */
    private const FORBIDDEN_LATEST_DATE_SHORTCUTS = [
        'MAX(trade_date)',
        "max('trade_date')",
        'max("trade_date")',
        "latest('trade_date')",
        "orderByDesc('trade_date')",
        'orderByDesc("trade_date")',
        'ORDER BY trade_date DESC',
    ];

    /**
     * Naming that announces a recency-based resolver. These never existed in this codebase and
     * must not appear: a helper called latestCurrentPublication is a bypass with a friendly name.
     */
    private const FORBIDDEN_RESOLVER_NAMES = [
        'latestCurrent',
        'latestPublication',
        'latestTradeDate',
        'unsafeLatest',
    ];

    /**
     * @return array<string, string> relative path => source with comments removed
     */
    private function runtimeSources(): array
    {
        $root = dirname(__DIR__, 3).DIRECTORY_SEPARATOR.'app';
        $sources = [];

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relative = str_replace(dirname(__DIR__, 3).DIRECTORY_SEPARATOR, '', $file->getPathname());
            $sources[$relative] = $this->stripComments(file_get_contents($file->getPathname()));
        }

        ksort($sources);

        return $sources;
    }

    /**
     * Comments are removed before scanning, because the contract is documented in the code that
     * enforces it. EodPublicationRepository's gateway carries a docblock stating that no caller
     * may resolve through MAX(date) or MAX(publication_id) — scanning raw text would flag the
     * sentence describing the rule as a violation of it.
     *
     * String literals are deliberately kept: raw SQL is where 'ORDER BY trade_date DESC' would
     * actually live.
     */
    private function stripComments(string $source): string
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
     * Guards the guard. A path mistake or a comment-stripping bug that returned nothing would
     * make every assertion below pass against an empty corpus.
     */
    public function test_the_scan_actually_reads_the_runtime(): void
    {
        $sources = $this->runtimeSources();

        $this->assertGreaterThan(80, count($sources), 'the runtime scan found suspiciously few files');
        $this->assertArrayHasKey(
            'app'.DIRECTORY_SEPARATOR.'Infrastructure'.DIRECTORY_SEPARATOR.'Persistence'.DIRECTORY_SEPARATOR
            .'MarketData'.DIRECTORY_SEPARATOR.'EodPublicationRepository.php',
            $sources
        );

        $gateway = $sources['app'.DIRECTORY_SEPARATOR.'Infrastructure'.DIRECTORY_SEPARATOR.'Persistence'
            .DIRECTORY_SEPARATOR.'MarketData'.DIRECTORY_SEPARATOR.'EodPublicationRepository.php'];

        // Comment stripping must remove prose without removing code.
        $this->assertStringNotContainsString('Official read-side gateway', $gateway);
        $this->assertStringContainsString('resolveCurrentReadablePublicationForTradeDate', $gateway);
        $this->assertStringContainsString('eod_current_publication_pointer', $gateway);
    }

    public function test_nothing_in_the_runtime_resolves_a_dataset_by_latest_trade_date(): void
    {
        $violations = [];

        foreach ($this->runtimeSources() as $path => $source) {
            foreach (self::FORBIDDEN_LATEST_DATE_SHORTCUTS as $forbidden) {
                if (strpos($source, $forbidden) !== false) {
                    $violations[] = $path.' contains '.$forbidden;
                }
            }
        }

        $this->assertSame(
            [],
            $violations,
            "A dataset must be resolved through eod_current_publication_pointer, never by recency."
        );
    }

    public function test_nothing_in_the_runtime_names_a_recency_based_resolver(): void
    {
        $violations = [];

        foreach ($this->runtimeSources() as $path => $source) {
            foreach (self::FORBIDDEN_RESOLVER_NAMES as $forbidden) {
                if (strpos($source, $forbidden) !== false) {
                    $violations[] = $path.' declares or calls '.$forbidden;
                }
            }
        }

        $this->assertSame([], $violations);
    }

    /**
     * Ordering by publication_id is not banned outright, and the boundary is worth stating rather
     * than approximating.
     *
     * It is used legitimately on the write side, run-scoped and under lockForUpdate, to pick the
     * newest candidate belonging to one specific run — which is a different question from "what
     * should a consumer read". The consumer question has exactly one answer,
     * resolveCurrentReadablePublicationForTradeDate, and that method's correctness is proven by
     * execution in PublicationRepositoryIntegrationTest, CorrectionBaselineResolutionTest and
     * CurrentPointerIntegrityScanTest rather than by pattern matching here.
     *
     * What can be checked statically is that such ordering stays out of the consumer read
     * repositories entirely.
     */
    public function test_consumer_read_repositories_never_order_publications_by_recency(): void
    {
        $consumerReadPaths = [
            'app/Infrastructure/Persistence/MarketData/EligibilitySnapshotScopeRepository.php',
            'app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php',
        ];

        $violations = [];

        foreach ($consumerReadPaths as $path) {
            $full = dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
            $this->assertFileExists($full);

            $source = $this->stripComments(file_get_contents($full));

            foreach (["orderByDesc('publication_id')", "max('publication_id')", 'MAX(publication_id)'] as $forbidden) {
                if (strpos($source, $forbidden) !== false) {
                    $violations[] = $path.' contains '.$forbidden;
                }
            }
        }

        $this->assertSame([], $violations);
    }
}
