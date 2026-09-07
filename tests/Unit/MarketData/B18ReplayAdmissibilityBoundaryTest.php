<?php

use PHPUnit\Framework\TestCase;

/**
 * `MD-B18` — what a replay verdict proves, and what no amount of replay can prove.
 *
 * Owner contracts:
 *   docs/market_data/authority/strategy/book/Replay_Verification_Contract_LOCKED.md          (MD-S050)
 *   docs/market_data/authority/strategy/backtest/Backtest_Metrics_and_Acceptance_Criteria_LOCKED.md (MD-S002)
 *
 * `MD-S050` states the boundary directly: replay compares an output against itself under fixed
 * inputs, so a publication computed from a wrong observation, a missing corporate action, or an
 * absent factor reproduces exactly and returns `PASS`. The verdict is evidence of reproducibility
 * and determinism, never of data correctness, event completeness, or factor validity. `BLOCKED` is
 * not a weaker `PASS`; it says the comparison did not execute.
 *
 * These fifteen predicates are about what may be *claimed*, so the matching instrument is a corpus
 * assertion rather than a runtime one — the violation is a sentence, not a behaviour. That is the
 * one place in this stage's proof map where reading text is the right proof; everywhere else the
 * map now names guards that execute, because `F-MD-B18-A001-001` showed a string assertion cannot
 * distinguish an enforced rule from a mentioned one.
 *
 * Both halves are proven. Nothing in the active corpus makes a claim the boundary denies, and every
 * pattern is fired against the sentence it is meant to catch and against that sentence's denial —
 * a pattern too narrow to match reports a clean corpus and a broken scan identically, and a pattern
 * that reads a denial as an assertion is the mistake this repository has made repeatedly.
 */
class B18ReplayAdmissibilityBoundaryTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    /**
     * A gap that cannot swallow a negation or a modal.
     *
     * Every sentence below is discussed in the records that prove this stage, always in order to
     * deny it.
     */
    private function gap(int $length): string
    {
        return '(?:(?!\b(?:not|never|cannot|can not|no|without|tidak|bukan|denies|denied|forbids|'
            .'forbidden|refuses|must|may not)\b)[^.\n]){0,'.$length.'}';
    }

    /**
     * rule id => [regex, a sentence that makes the forbidden claim]
     *
     * @return array<string,array{0:string,1:string}>
     */
    private function forbidden(): array
    {
        return [
            // A PASS speaks to reproducibility. It says nothing about whether the values are right.
            'MD-S050-R0045' => [
                '/replay'.$this->gap(40).'(pass|verdict|result)'.$this->gap(40).'(proves|confirms|establishes|shows)'.$this->gap(30).'(values?|data)\s+(are|is|was|were)\s+correct/i',
                'A replay PASS proves the values are correct for that publication.',
            ],
            // Reproducibility freezes a provider error as faithfully as a good observation.
            'MD-S050-R0046' => [
                '/replay'.$this->gap(40).'(proves|confirms|establishes)'.$this->gap(30).'(source\s+)?observation'.$this->gap(20).'(was|is)\s+(faithful|accurate|correct)/i',
                'A successful replay proves the source observation was faithful.',
            ],
            // Determinism is not completeness.
            'MD-S050-R0050' => [
                '/replay'.$this->gap(40).'(pass|verdict)'.$this->gap(40).'(evidence|proof)\s+of'.$this->gap(20).'(data\s+correctness|event\s+completeness|factor\s+validity)/i',
                'The replay PASS is evidence of event completeness for the period.',
            ],
            // A replay verdict cannot discharge a quality obligation.
            'MD-S050-R0051' => [
                '/replay'.$this->gap(40).'(pass|verdict|result)'.$this->gap(40).'(closes|resolves|releases|dismisses|satisfies)'.$this->gap(30).'(finding|quarantine|candidate|continuity)/i',
                'The replay PASS closes the outstanding data-quality finding.',
            ],
            // BLOCKED is the absence of a comparison, not a soft success.
            'MD-S050-R0053' => [
                '/blocked'.$this->gap(30).'(is|as|counts\s+as|treated\s+as)'.$this->gap(20).'(a\s+)?(weaker|partial|soft|qualified)\s+pass/i',
                'A BLOCKED replay is a weaker pass for reporting purposes.',
            ],
            // Publication replay cannot see future-state leakage; only as-known can.
            'MD-S050-R0038' => [
                '/publication[- ]replay'.$this->gap(40).'(proves?|shows?|demonstrates?|establishes?)'.$this->gap(30).'(anti[- ]survivorship|no\s+future[- ]state|point[- ]in[- ]time)/i',
                'Publication-replay results demonstrate anti-survivorship for the dataset.',
            ],
            // Volume of publication passes never substitutes for one as-known fixture.
            'MD-S050-R0039' => [
                '/(many|volume\s+of|accumulated|repeated)'.$this->gap(30).'publication[- ]replay'.$this->gap(30).'(substitutes?|stands?\s+in|suffices?)/i',
                'Accumulated publication-replay passes substitute for the as-known fixture.',
            ],
            // Absent capability is unavailability, not coverage.
            'MD-S050-R0040' => [
                '/anti[- ]survivorship\s+fixtures?'.$this->gap(40).'(are|is)'.$this->gap(20).'(covered|satisfied|complete)/i',
                'The anti-survivorship fixtures are covered by the existing corpus.',
            ],
            // Pass rates never compensate for a semantic mismatch.
            'MD-S002-R0010' => [
                '/(pass\s+rate|row[- ]count\s+similarity)'.$this->gap(40).'(compensates?|offsets?|outweighs?|makes?\s+up)/i',
                'A high pass rate compensates for the remaining semantic mismatch.',
            ],
            // BLOCKED is missing proof, never converted.
            'MD-S002-R0009' => [
                '/blocked'.$this->gap(30).'(is|are|was|were|be|been|being|gets?|got)\s+'.'(converted|promoted|counted|rolled\s+up)'.$this->gap(20).'(to|into|as)'.$this->gap(10).'pass/i',
                'A BLOCKED outcome is counted as pass in the summary.',
            ],
            // A metric set evidences that a study ran, not that the data was good.
            'MD-S002-R0016' => [
                '/metric\s+set'.$this->gap(40).'(evidence|proof)'.$this->gap(30).'(market[- ]data\s+quality|strategy\s+merit)/i',
                'The metric set is evidence about market-data quality for the version.',
            ],
            // A clean quality replay evidences stable decisions, not sound data.
            'MD-S003-R0031' => [
                '/(clean|green)'.$this->gap(30).'(historical\s+)?quality\s+replay'.$this->gap(40).'(proves|shows|evidence\s+that)'.$this->gap(20).'(historical\s+)?data\s+(was|is)\s+sound/i',
                'A clean historical quality replay shows the historical data was sound.',
            ],
            // The as-known view holds what was recorded, not what was true.
            'MD-S004-R0011' => [
                '/as[- ]known'.$this->gap(30).'(state|view|set)'.$this->gap(30).'(was|is)\s+complete\s+at'.$this->gap(20).'cutoff/i',
                'The as-known state was complete at that cutoff for the universe.',
            ],
            // Signal features and executable prices are different facts.
            'MD-S004-R0007' => [
                '/(signal|analytical)'.$this->gap(30).'(price|feature)s?'.$this->gap(30).'(are|is|may\s+be)\s+used\s+as'.$this->gap(20).'execution\s+price/i',
                'The analytical price features are used as execution prices in simulation.',
            ],
            // Readiness admission rests on immutability and replay, not on replay alone.
            'MD-S020-R0014' => [
                '/replay'.$this->gap(30).'alone'.$this->gap(30).'(admits|qualifies|makes)'.$this->gap(20).'(data\s+)?ready/i',
                'Replay alone admits the dataset ready for downstream consumption.',
            ],
        ];
    }

    /**
     * Documents that may state the claim because they own the prohibition, plus the corpora that
     * store strategy text verbatim.
     *
     * @return array<int,string>
     */
    private function excludedPrefixes(): array
    {
        return [
            'docs/market_data/authority/strategy/',
            'docs/market_data/records/history/',
            'docs/market_data/records/decisions/legacy/',
            'docs/market_data/records/evidence/legacy/',
            'docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv',
        ];
    }

    /** @return array<int,string> repo-relative paths */
    private function scannedFiles(): array
    {
        $files = [];
        foreach (['/docs/market_data', '/app'] as $tree) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->root().$tree, FilesystemIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if (! $file->isFile()) {
                    continue;
                }
                if (! in_array(strtolower($file->getExtension()), ['md', 'json', 'php', 'csv'], true)) {
                    continue;
                }
                $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($this->root()) + 1));
                foreach ($this->excludedPrefixes() as $prefix) {
                    if (strpos($relative, $prefix) === 0) {
                        continue 2;
                    }
                }
                $files[] = $relative;
            }
        }
        sort($files);

        return $files;
    }

    /** The matched claim, or null when the sentence carrying it is a denial. */
    private function assertedMatch(string $text, string $pattern): ?string
    {
        foreach (preg_split('/(?<=[.!?])\s+|\n/', $text) as $sentence) {
            if (! preg_match($pattern, $sentence, $match, PREG_OFFSET_CAPTURE)) {
                continue;
            }
            $before = substr($sentence, 0, (int) $match[0][1]);
            if (preg_match('/\b(not|never|cannot|can not|no|nothing|without|neither|tidak|bukan|denies|denied|forbids|forbidden|refuses|may)\b/i', $before)) {
                continue;
            }

            return trim($match[0][0]);
        }

        return null;
    }

    /**
     * Nothing in the active corpus or the application cites a replay verdict for something replay
     * cannot establish.
     */
    public function test_no_active_surface_cites_a_replay_verdict_for_something_replay_cannot_establish(): void
    {
        $files = $this->scannedFiles();
        $this->assertGreaterThan(300, count($files), 'the corpus scan must reach the active document set');

        $offenders = [];
        foreach ($files as $relative) {
            $text = (string) file_get_contents($this->root().'/'.$relative);
            foreach ($this->forbidden() as $rule => $spec) {
                $hit = $this->assertedMatch($text, $spec[0]);
                if ($hit !== null) {
                    $offenders[] = $rule.' :: '.$relative.' :: '.$hit;
                }
            }
        }
        sort($offenders);

        $this->assertSame([], $offenders, 'a replay admissibility boundary is crossed somewhere in the active corpus');
    }

    /**
     * Every pattern fires on the claim it forbids and stays silent on that claim's denial.
     *
     * Without the first half a pattern too narrow to match anything would report a clean corpus.
     * Without the second half the guard would flag the very records that state the prohibition.
     */
    public function test_every_pattern_matches_the_claim_it_forbids_and_spares_the_denial(): void
    {
        $silentOnClaim = [];
        $firedOnDenial = [];

        foreach ($this->forbidden() as $rule => $spec) {
            [$pattern, $claim] = $spec;

            if ($this->assertedMatch($claim, $pattern) === null) {
                $silentOnClaim[] = $rule;
            }

            foreach (['This does not mean that ', 'Nothing here proves that ', 'It is never true that '] as $prefix) {
                $denial = $prefix.lcfirst($claim);
                if ($this->assertedMatch($denial, $pattern) !== null) {
                    $firedOnDenial[] = $rule.' :: '.$prefix;
                }
            }
        }

        $this->assertSame([], $silentOnClaim, 'these patterns cannot match the claim they exist to catch');
        $this->assertSame([], $firedOnDenial, 'these patterns read a denial as an assertion');
    }

    /**
     * Text that *names* a forbidden condition must stay green.
     *
     * A negative-test catalog lists the conditions it exists to reject, as bare participle phrases
     * with no subject and no auxiliary. An earlier form of the `MD-S002-R0009` pattern read
     * `Negative_Test_Catalog_LOCKED.md`'s own entry as the claim it forbids. The fix was to require
     * the auxiliary an assertion carries, not to exclude the document -- the corpus that states the
     * prohibitions is exactly the corpus this guard must keep reading.
     */
    public function test_naming_a_forbidden_condition_is_not_asserting_it(): void
    {
        $catalogue = [
            '- `BLOCKED`/missing evidence counted as pass or production ready.',
            '- publication-replay results demonstrating anti-survivorship;',
            '- pass rates compensating for a semantic mismatch;',
            '- replay verdict closing a data-quality finding;',
        ];

        $flagged = [];
        foreach ($catalogue as $entry) {
            foreach ($this->forbidden() as $rule => $spec) {
                if ($this->assertedMatch($entry, $spec[0]) !== null) {
                    $flagged[] = $rule.' :: '.$entry;
                }
            }
        }

        $this->assertSame([], $flagged, 'a catalogue entry naming a prohibition was read as making the claim');
    }

    /** The owner contracts still state the limits, so the caveat cannot quietly disappear. */
    public function test_each_owner_contract_still_states_the_boundary_it_owns(): void
    {
        $expected = [
            'authority/strategy/book/Replay_Verification_Contract_LOCKED.md' => [
                'Replay compares an output against itself under fixed inputs',
                '`BLOCKED` is not a weaker `PASS`',
            ],
            'authority/strategy/backtest/Backtest_Metrics_and_Acceptance_Criteria_LOCKED.md' => [
                'cannot compensate for a semantic mismatch',
            ],
        ];

        foreach ($expected as $relative => $sentences) {
            $path = $this->root().'/docs/market_data/'.$relative;
            $this->assertFileExists($path, $relative.' must exist for this boundary to have an owner');
            $text = (string) file_get_contents($path);
            foreach ($sentences as $sentence) {
                $this->assertNotFalse(
                    strpos($text, $sentence),
                    $relative.' no longer states: '.$sentence
                );
            }
        }
    }
}
