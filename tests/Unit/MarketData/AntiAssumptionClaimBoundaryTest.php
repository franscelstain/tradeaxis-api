<?php

use PHPUnit\Framework\TestCase;

/**
 * MD-B01 — the anti-assumption rules.
 *
 * Owner contract: authority/strategy/MARKET_DATA_PLATFORM_EOD_BASELINE.md, "Anti-assumption rules
 * (LOCKED)". The contract lists statements that documents in this domain must no longer make or
 * imply. Each list item is a separate obligation; the introducer that precedes them is not.
 *
 * These forbid a *claim*, so the surface under test is the active document set plus any executable
 * surface that could encode the same assumption. Frozen strategy is excluded because it owns the
 * prohibition, and the traceability matrix is excluded because it stores strategy rule text verbatim
 * — including the forbidden sentences themselves — and every row is already fingerprinted against
 * its source line by the documentation integrity gate.
 *
 * The patterns are the load-bearing part. A pattern too narrow to match anything would report clean
 * across every document and look identical to a genuinely clean corpus, so
 * test_every_pattern_matches_the_statement_it_forbids asserts each one can actually fire.
 */
class AntiAssumptionClaimBoundaryTest extends TestCase
{
    /**
     * rule id => [regex, a sentence that states the forbidden assumption]
     *
     * The second element is not decoration: it is the fixture the adequacy test uses to prove the
     * pattern is capable of matching.
     */
    private function forbidden(): array
    {
        return [
            'MD-S001-R0127' => [
                '/\b(cash|negotiated)\s+market\s+(is|are|dapat|boleh)\s+(mixed|dicampur|included)/i',
                'The negotiated market boleh dicampur into the canonical Regular-Market series.',
            ],
            'MD-S001-R0128' => [
                '/(sebelum|before)\s+.?2023-01-02.?[^.]{0,60}(hilang|lost|missing)\s+(akibat|due to|karena)\s+(kegagalan|failure)/i',
                'Data sebelum `2023-01-02` hilang akibat kegagalan source pada scope aktif.',
            ],
            'MD-S001-R0129' => [
                '/(archived proof window|proof window)[^.]{0,50}(adalah|is)\s+(the )?(dataset end|freshness)/i',
                'The archived proof window is the dataset end for this platform.',
            ],
            'MD-S001-R0130' => [
                '/(development (data )?frontier)[^.]{0,40}(adalah|is)\s+(a )?(capability limit|production incident)/i',
                'The development data frontier is a capability limit of the platform.',
            ],
            'MD-S001-R0131' => [
                '/historical proof[^.]{0,60}(menetapkan|sets|establishes)[^.]{0,20}OPERATIONAL_START_DATE/i',
                'A historical proof run sets OPERATIONAL_START_DATE automatically.',
            ],
            'MD-S001-R0132' => [
                '/(raw provider payload|provider payload)[^.]{0,30}(sama dengan|equals|is)\s+.?canonical .?RAW/i',
                'The raw provider payload is canonical RAW for this domain.',
            ],
            'MD-S001-R0133' => [
                '/adj_close[^.]{0,40}(adalah|is)\s+(a )?coherent adjusted/i',
                'Provider adj_close is a coherent adjusted OHLCV product.',
            ],
            'MD-S001-R0134' => [
                '/(coverage[^.]{0,60}eligibility)[^.]{0,30}(adalah|are)\s+(konsep yang sama|the same concept)/i',
                'Coverage, quality, liquidity, event risk, and eligibility are the same concept.',
            ],
            'MD-S001-R0135' => [
                '/eligibility\s+(berarti|means)\s+(buy signal|ranking alpha|persetujuan strategy)/i',
                'Eligibility means buy signal for the downstream consumer.',
            ],
            'MD-S001-R0136' => [
                '/provider default[^.]{0,30}(adalah|is)\s+(the )?source of truth/i',
                'The provider default is the source of truth for this domain.',
            ],
            'MD-S001-R0137' => [
                '/.?range=10d.?[^.]{0,40}(adalah|is)\s+(a )?capability limit/i',
                'The `range=10d` window is a capability limit of the platform.',
            ],
            'MD-S001-R0138' => [
                '/(sistem|system)[^.]{0,30}(hanya ditujukan|is only intended)[^.]{0,30}recent-only/i',
                'The system is only intended for recent-only ingestion.',
            ],

            /*
             * `MD-S001-R0142`–`R0145`, promoted at `MD-B01-A014`. They are the last four entries of
             * the same nineteen-item list; the first fifteen were required and these were not,
             * because the list was truncated rather than because they mean anything different.
             *
             * These four use the negation-safe gap. The twelve above were written before any record
             * restated them; these are restated in `E-MD-B01-A015-001` and in the Stage Register, so
             * a gap that could swallow a negation would read the record obeying the rule as the
             * record breaking it — which has happened four times in this stage already.
             */
            'MD-S001-R0142' => [
                '/import\s+success'.$this->negationSafeGap(40).'(berarti|means|implies)'.$this->negationSafeGap(40).'readable/i',
                'Import success berarti requested date readable.',
            ],
            'MD-S001-R0143' => [
                '/import'.$this->negationSafeGap(30).'(menjalankan|runs|executes|performs)'.$this->negationSafeGap(40).'(indicators|eligibility|hash|seal|finalize)/i',
                'Import menjalankan indicators, eligibility, hash, seal, dan finalize.',
            ],
            'MD-S001-R0144' => [
                '/consumer'.$this->negationSafeGap(30).'(boleh|may|can)\s+(membaca|read)'.$this->negationSafeGap(30).'raw table/i',
                'Consumer boleh membaca raw table tanpa publication context.',
            ],
            'MD-S001-R0145' => [
                '/publish\s+(cepat|fast)'.$this->negationSafeGap(40).'(boleh mengalahkan|overrides|beats|outranks)'.$this->negationSafeGap(30).'(coverage|readability)/i',
                'Publish cepat boleh mengalahkan coverage safety.',
            ],
        ];
    }

    /**
     * A within-sentence gap that cannot contain a negation, so a document quoting the prohibition is
     * not read as making the claim. Same construct as `TerminologyConflationBoundaryTest`.
     */
    private function negationSafeGap(int $n): string
    {
        return '(?:(?!\bnever\b|\bnot\b|\bno\b|\bbukan\b|\btidak\b|\bjangan\b|\bdilarang\b)[^.]){0,'.$n.'}';
    }

    /**
     * The four rules promoted at A014 are restated as prohibitions in this attempt's own records.
     * Each pattern must stay silent on that restatement.
     */
    public function test_a_quotation_of_the_prohibition_is_not_read_as_an_assertion(): void
    {
        $quotations = [
            'MD-S001-R0142' => 'Import success does not mean the requested date is readable.',
            'MD-S001-R0143' => 'Import never runs indicators, eligibility, hash, seal, or finalize.',
            'MD-S001-R0144' => 'A consumer may not read a raw table without publication context.',
            'MD-S001-R0145' => 'Publish cepat tidak boleh mengalahkan coverage safety.',
        ];

        $forbidden = $this->forbidden();
        foreach ($quotations as $ruleId => $quotation) {
            $this->assertArrayHasKey($ruleId, $forbidden, $ruleId.' must be under test before its quotation is checked');
            $this->assertSame(
                0,
                preg_match($forbidden[$ruleId][0], $quotation),
                $ruleId.': a document stating the prohibition is obeying it, not breaking it'
            );
        }
    }

    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    /**
     * The active document set: everything current, minus the two surfaces that legitimately contain
     * the forbidden sentences — frozen strategy, which owns the prohibition, and the traceability
     * matrix, which stores strategy rule text verbatim under its own fingerprint control.
     *
     * @return array<string,string>
     */
    private function activeDocuments(): array
    {
        $root = $this->root().'/docs/market_data';
        $out = [];

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if (! $file->isFile()) {
                continue;
            }
            $relative = substr(strtr($file->getPathname(), chr(92), '/'), strlen(strtr($root, chr(92), '/')) + 1);

            if (strpos($relative, 'authority/strategy/') === 0
                || strpos($relative, 'records/history/') === 0
                || strpos($relative, 'records/evidence/legacy/') === 0
                || strpos($relative, 'records/decisions/legacy/') === 0
                || $relative === 'authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv') {
                continue;
            }
            if (! preg_match('/\.(md|json|csv|sql)$/', $relative)) {
                continue;
            }
            $out[$relative] = (string) file_get_contents($file->getPathname());
        }

        return $out;
    }

    /**
     * Proves each pattern can fire. Without this a mistyped or over-narrow regex would report the
     * corpus clean and be indistinguishable from a corpus that genuinely is.
     */
    public function test_every_pattern_matches_the_statement_it_forbids(): void
    {
        $forbidden = $this->forbidden();
        $this->assertCount(16, $forbidden, 'the anti-assumption family under test is 16 rules after the A014 promotion');

        foreach ($forbidden as $ruleId => [$pattern, $fixture]) {
            $this->assertSame(
                1,
                preg_match($pattern, $fixture),
                $ruleId.': the pattern must match the very statement it forbids, otherwise a clean result proves nothing'
            );
        }
    }

    /**
     * No pattern may match a neighbouring rule's fixture. Overlapping patterns would let one rule's
     * evidence silently stand in for another's.
     */
    public function test_patterns_do_not_overlap_each_other(): void
    {
        $forbidden = $this->forbidden();
        $collisions = [];

        foreach ($forbidden as $ruleId => [$pattern, $_]) {
            foreach ($forbidden as $otherId => [$__, $otherFixture]) {
                if ($ruleId === $otherId) {
                    continue;
                }
                if (preg_match($pattern, $otherFixture)) {
                    $collisions[] = $ruleId.' matches '.$otherId;
                }
            }
        }

        $this->assertSame([], $collisions, 'each anti-assumption rule must be proven by its own pattern');
    }

    /**
     * The corpus check. Every active document is read; no forbidden assumption may be stated.
     *
     * @dataProvider forbiddenProvider
     */
    public function test_no_active_document_states_the_forbidden_assumption(string $ruleId, string $pattern): void
    {
        $documents = $this->activeDocuments();

        $this->assertGreaterThan(100, count($documents), 'the document scan must reach the active corpus');

        $claims = [];
        foreach ($documents as $relative => $text) {
            if (preg_match($pattern, $text, $match)) {
                $claims[] = $relative.' :: '.trim($match[0]);
            }
        }

        $this->assertSame([], $claims, $ruleId.': an active document states an assumption the baseline contract forbids');
    }

    public function forbiddenProvider(): array
    {
        $out = [];
        foreach ((new self('x'))->forbidden() as $ruleId => [$pattern, $_]) {
            $out[$ruleId] = [$ruleId, $pattern];
        }

        return $out;
    }

    /**
     * The same assumptions must not be encoded in executable surfaces either. A document that stays
     * silent while the code asserts the assumption would satisfy the letter and miss the point.
     */
    public function test_no_executable_surface_encodes_a_forbidden_assumption(): void
    {
        $code = '';
        $scanned = 0;

        foreach (['app', 'config'] as $dir) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->root().'/'.$dir, FilesystemIterator::SKIP_DOTS));
            foreach ($files as $file) {
                if (! $file->isFile() || substr($file->getFilename(), -4) !== '.php') {
                    continue;
                }
                $scanned++;
                $code .= (string) file_get_contents($file->getPathname())."\n";
            }
        }

        $this->assertGreaterThan(100, $scanned, 'the source scan must reach the application tree');

        $claims = [];
        foreach ($this->forbidden() as $ruleId => [$pattern, $_]) {
            if (preg_match($pattern, $code, $match)) {
                $claims[] = $ruleId.' :: '.trim($match[0]);
            }
        }

        $this->assertSame([], $claims, 'no executable surface may encode a forbidden assumption');
    }
}
