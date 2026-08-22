<?php

use PHPUnit\Framework\TestCase;

/**
 * W05 / MD-B05 — what temporal resolution proves, and what no amount of it can prove.
 *
 * Owner contracts:
 *   docs/market_data/authority/strategy/book/Tickers_and_Identity_Dependency_Contract_LOCKED.md
 *   docs/market_data/authority/strategy/book/Symbol_Lifecycle_and_Mapping_Contract.md
 *   docs/market_data/authority/strategy/book/Sector_Classification_Contract_LOCKED.md
 *
 * All three contracts end the same way: a capability boundary, a statement that completeness is
 * verified externally, and an acceptance criterion that names which of two guarantees it
 * establishes. The distinction they draw is between a **resolver** that is survivorship-free and a
 * **universe** that is, between a mapping set that is internally consistent and one that is
 * complete, and between a recorded classification covering a date and the exchange having
 * classified it that way.
 *
 * These are the easiest predicates in the stage to nod at and the easiest to violate, because the
 * violation is a sentence rather than a behavior. So the proof has two halves: each contract still
 * states its own limit, and nothing in the active corpus or the application makes the claim the
 * limit denies. Every pattern carries the sentence it is meant to catch, and
 * `test_every_pattern_matches_the_claim_it_forbids` proves each one can fire — a pattern too narrow
 * to match anything reports a clean corpus and a broken scan identically.
 */
class IdentityAndMembershipCapabilityBoundaryTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    /**
     * rule id => [regex, a sentence that makes the forbidden claim]
     *
     * @return array<string,array{0:string,1:string}>
     */
    private function forbidden(): array
    {
        return [
            // The master cannot be proven complete by anything downstream of it.
            'MD-S057-R0052' => [
                '/(master|universe)'.$this->gap(60).'contains\s+every\s+(security|listing|instrument)/i',
                'The identity master contains every security that existed on that date.',
            ],
            // A recorded delisting date is a record, not a verification.
            'MD-S057-R0053' => [
                '/(recorded\s+)?delisting\s+dates?'.$this->gap(40).'(is|are)'.$this->gap(20).'(verified|the real one|correct by construction)/i',
                'Each recorded delisting date is verified against the exchange.',
            ],
            // Reuse handled correctly proves the mechanism, not that reuse was noticed.
            'MD-S057-R0054' => [
                '/every\s+(symbol\s+)?reuse'.$this->gap(30).'(was|is)\s+(noticed|detected|recorded)/i',
                'Every symbol reuse was noticed by the platform.',
            ],
            // The universe is a root of expectation; no downstream gate can detect it is wrong.
            'MD-S057-R0055' => [
                '/(coverage|downstream)\s+gates?'.$this->gap(50).'detects?'.$this->gap(30).'(incomplete|missing)\s+universe/i',
                'The coverage gate detects an incomplete universe automatically.',
            ],
            // Internal consistency is not completeness.
            'MD-S055-R0035' => [
                '/mapping\s+(set|table|records?)'.$this->gap(40).'(is|are)\s+complete/i',
                'The provider mapping set is complete for the dataset range.',
            ],
            // The platform records what it requested; it cannot see what the provider served.
            'MD-S055-R0034' => [
                '/provider'.$this->gap(40).'served\s+the\s+(correct|assumed|right)\s+(security|instrument)/i',
                'The provider served the correct security for every mapped symbol.',
            ],
            // Resolution reflects what was recorded, not what the exchange published.
            'MD-S052-R0034' => [
                '/(recorded\s+)?classification'.$this->gap(40).'(is|are)\s+current\s+with\s+the\s+exchange/i',
                'The recorded classification is current with the exchange at all times.',
            ],
            // An open interval asserts that nothing was recorded, not that nothing happened.
            'MD-S052-R0035' => [
                '/(open|null)'.$this->gap(30).'effective_to'.$this->gap(40).'(means|proves|confirms)'.$this->gap(20).'no\s+change/i',
                'A null effective_to means no change occurred for that instrument.',
            ],
            // Before and after a reclassification the comparison population differs.
            'MD-S052-R0036' => [
                '/sector[- ]relative'.$this->gap(40).'comparable'.$this->gap(30).'across'.$this->gap(20).'reclassification/i',
                'Sector-relative measures remain comparable across a reclassification.',
            ],
            // An instrument absent from the membership table is unrecorded, not unclassified.
            'MD-S052-R0037' => [
                '/membership\s+coverage'.$this->gap(30).'(equals|is the same as|matches)'.$this->gap(20).'universe\s+coverage/i',
                'Membership coverage equals universe coverage for the dataset.',
            ],
            // A resolved sector is evidence about the record, never about the exchange's act.
            'MD-S052-R0038' => [
                '/resolved\s+sector'.$this->gap(40).'proves'.$this->gap(40).'(exchange|IDX)\s+classified/i',
                'A resolved sector proves the exchange classified it that way then.',
            ],
        ];
    }

    /**
     * A gap that cannot swallow a negation or a modal.
     *
     * Every one of these sentences is *discussed* in the records that prove this stage, always in
     * order to deny it. A plain `.{0,n}` would read the denial as the assertion, which is the
     * mistake this repository has made seven times.
     */
    private function gap(int $length): string
    {
        return '(?:(?!\b(?:not|never|cannot|can not|no|without|tidak|bukan|denies|denied|forbids|forbidden|refuses|must)\b)[^.\n]){0,'.$length.'}';
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

    /**
     * The matched claim, or null when the sentence carrying it is a denial.
     *
     * The negation-safe gap inside each pattern protects the span between its anchors. It cannot
     * protect what comes before the first one, and "Nothing proves the master contains every
     * security" puts the negation exactly there. So the text preceding the match, within the same
     * sentence, is inspected too — the records that prove this stage state most of these claims in
     * order to deny them, and a guard that reads a denial as an assertion is the seventh instance of
     * a guard flagging the surface obeying it.
     */
    private function assertedMatch(string $text, string $pattern): ?string
    {
        foreach (preg_split('/(?<=[.!?])\s+|\n/', $text) as $sentence) {
            if (! preg_match($pattern, $sentence, $match, PREG_OFFSET_CAPTURE)) {
                continue;
            }
            $before = substr($sentence, 0, (int) $match[0][1]);
            if (preg_match('/\b(not|never|cannot|can not|no|nothing|without|neither|tidak|bukan|denies|denied|forbids|forbidden|refuses)\b/i', $before)) {
                continue;
            }

            return trim($match[0][0]);
        }

        return null;
    }

    /** Each owner contract still states its own limit, so the caveat cannot quietly disappear. */
    public function test_each_owner_contract_still_states_the_limit_it_owns(): void
    {
        $expected = [
            'authority/strategy/book/Tickers_and_Identity_Dependency_Contract_LOCKED.md' => [
                'They cannot make the **master** complete',
                'Universe completeness is verified externally',
                'A survivorship-free **universe** additionally requires the external completeness reconciliation',
            ],
            'authority/strategy/book/Symbol_Lifecycle_and_Mapping_Contract.md' => [
                'A mapping set that is internally consistent is not thereby complete.',
                'It does not prove that every rename, relisting, or reuse in the period was recorded',
            ],
            'authority/strategy/book/Sector_Classification_Contract_LOCKED.md' => [
                'never as evidence that **the instrument was classified that way by the exchange at that time**',
                'Completeness is verified externally',
            ],
        ];

        foreach ($expected as $relative => $sentences) {
            $path = $this->root().'/docs/market_data/'.$relative;
            $this->assertFileExists($path);
            $text = (string) file_get_contents($path);
            foreach ($sentences as $sentence) {
                $this->assertNotFalse(
                    strpos($text, $sentence),
                    $relative.' no longer states: '.$sentence
                );
            }
        }
    }

    /** Nothing outside the owning contracts makes a claim the capability boundary denies. */
    public function test_no_active_document_or_application_surface_makes_a_forbidden_claim(): void
    {
        $files = $this->scannedFiles();
        $this->assertGreaterThan(300, count($files), 'the corpus scan must reach the active document set');

        $offenders = [];
        foreach ($files as $relative) {
            $text = (string) file_get_contents($this->root().'/'.$relative);
            foreach ($this->forbidden() as $rule => [$pattern]) {
                $hit = $this->assertedMatch($text, $pattern);
                if ($hit !== null) {
                    $offenders[] = $rule.' :: '.$relative.' :: '.$hit;
                }
            }
        }
        sort($offenders);

        $this->assertSame([], $offenders, 'a capability the contract denies is claimed somewhere');
    }

    /**
     * A survivorship-free claim must say which guarantee it means. An unqualified one is the exact
     * conflation both the identity contract's acceptance criterion and its qualification rule name.
     */
    public function test_a_survivorship_free_claim_names_the_resolver_or_its_reconciled_period(): void
    {
        $unqualified = [];
        $claims = 0;

        foreach ($this->scannedFiles() as $relative) {
            $text = (string) file_get_contents($this->root().'/'.$relative);
            foreach (preg_split('/(?<=[.!?])\s+|\n/', $text) as $sentence) {
                if (! preg_match('/survivorship[- ]free/i', $sentence)) {
                    continue;
                }
                $claims++;
                if (preg_match('/\b(resolver|reconcil|period|unreconciled|claim|not|cannot|never|requires)\b/i', $sentence)) {
                    continue;
                }
                $unqualified[] = $relative.' :: '.trim($sentence);
            }
        }

        $this->assertGreaterThan(0, $claims, 'the scan must find the claims it is grading');
        $this->assertSame([], $unqualified, 'a survivorship-free claim must name which of the two guarantees it makes');
    }

    /**
     * Adequacy. Without this the suite above passes on a corpus that is clean and on a scan that
     * matches nothing, and those are not the same result.
     */
    public function test_every_pattern_matches_the_claim_it_forbids(): void
    {
        foreach ($this->forbidden() as $rule => [$pattern, $fixture]) {
            $this->assertSame(1, preg_match($pattern, $fixture), $rule.': pattern cannot match its own fixture');
        }
    }

    /**
     * And the negation guard holds: the same sentences, denied, must not register as claims. Six of
     * these appear almost verbatim in the records that prove this stage.
     */
    public function test_denying_a_forbidden_claim_is_not_making_it(): void
    {
        $denials = [
            'MD-S057-R0052' => 'Nothing proves the master contains every security that existed on that date.',
            'MD-S057-R0054' => 'It cannot prove every symbol reuse was noticed.',
            'MD-S055-R0035' => 'An internally consistent mapping set is not complete.',
            'MD-S052-R0034' => 'Resolution cannot show the recorded classification is current with the exchange.',
            'MD-S052-R0037' => 'Membership coverage never equals universe coverage by construction.',
            'MD-S052-R0038' => 'A resolved sector never proves the exchange classified it that way.',
        ];

        foreach ($denials as $rule => $sentence) {
            [$pattern] = $this->forbidden()[$rule];
            $this->assertNull($this->assertedMatch($sentence, $pattern), $rule.': a denial was read as an assertion');
        }
    }
}
