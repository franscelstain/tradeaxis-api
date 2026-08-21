<?php

use PHPUnit\Framework\TestCase;

/**
 * MD-B01 — the terminology conflation boundary.
 *
 * Owner contracts: authority/strategy/book/Terminology_and_Scope.md, "Locked interpretation rules",
 * and authority/strategy/.../MARKET_DATA_PLATFORM_EOD_BASELINE.md.
 *
 * Two obligations are proven here and they are not the same shape.
 *
 * 1. Prohibitions — "X must never be described as Y". A prohibition is satisfied by absence, so the
 *    proof is that no active document and no executable surface states the conflation.
 *
 * 2. Documentation-state readings — "State dokumentasi harus dibaca sebagai berikut". These are
 *    proven by absence of contradiction *plus* a population check that the corpus actually raises the
 *    subject. Absence alone would be vacuous: a corpus that never mentions a concept cannot be shown
 *    to read it correctly. Two of the five readings are deliberately not claimed for exactly that
 *    reason; see the evidence record.
 *
 * A pattern here must separate an assertion from a quotation of the prohibition. A document that
 * writes "the decision horizon must never be expressed in calendar days" is obeying the rule, not
 * breaking it, and an earlier revision of this guard flagged one of its own evidence records for
 * saying so. The gap between subject and verb therefore cannot swallow a negation, and
 * test_a_quotation_of_the_prohibition_is_not_read_as_an_assertion holds the guard to that.
 *
 * Frozen strategy is excluded because it owns both the prohibition and the reading, as is the
 * traceability matrix, which stores strategy rule text verbatim under its own fingerprint control.
 */
class TerminologyConflationBoundaryTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    /**
     * A within-sentence gap of up to $n characters that cannot contain a negation. This is what makes
     * the difference between stating a conflation and forbidding it.
     */
    private function gap(int $n): string
    {
        return '(?:(?!\bnever\b|\bnot\b|\bno\b|\bbukan\b|\btidak\b|\bjangan\b|\bdilarang\b)[^.]){0,'.$n.'}';
    }

    /**
     * rule id => [regex, statement of the conflation, the same rule stated as a prohibition]
     *
     * The second element proves the pattern can fire. The third proves it does not fire on a document
     * that is quoting the prohibition.
     *
     * @return array<string,array{0:string,1:string,2:string}>
     */
    private function forbidden(): array
    {
        $g = function (int $n) { return $this->gap($n); };

        return [
            // Terminology_and_Scope.md — locked interpretation rules
            'MD-S056-R0113' => [
                '/import (completion|selesai)'.$g(40).'(is|means|berarti|adalah)\s+(a )?readable publication/i',
                'Import completion is a readable publication for this domain.',
                'Import completion must never be described as readable publication.',
            ],
            'MD-S056-R0115' => [
                '/per-ticker failure'.$g(40).'(is|means|berarti|adalah)\s+(an? )?(automatic )?full-run (fatality|failure)/i',
                'A per-ticker failure is an automatic full-run fatality.',
                'Per-ticker failure must never be described as automatic full-run fatality.',
            ],
            'MD-S056-R0116' => [
                '/coverage'.$g(40).'(is|means)\s+(the )?(count of |number of )?success(ful)? (HTTP )?request/i',
                'Coverage means the count of successful HTTP requests.',
                'Coverage must never mean successful HTTP request count.',
            ],
            'MD-S056-R0117' => [
                '/publication'.$g(40).'(is|means)\s+(the )?.?latest raw row by timestamp/i',
                'Publication means the latest raw row by timestamp.',
                'Publication must never mean the latest raw row by timestamp.',
            ],
            'MD-S056-R0119' => [
                '/provider limitation'.$g(40).'(is|means|adalah)\s+(a )?domain limitation/i',
                'A provider limitation is a domain limitation of this platform.',
                'Provider limitation abstraction must never be described as a domain limitation.',
            ],
            'MD-S056-R0120' => [
                '/.?2023-01-02.?'.$g(40).'(is|means)\s+(an )?accidental source limit/i',
                'The `2023-01-02` boundary is an accidental source limit.',
                '`2023-01-02` must never be described as an accidental source limit.',
            ],
            'MD-S056-R0128' => [
                '/(decision )?horizon'.$g(30).'(is|expressed in|dinyatakan dalam)\s+\d*\s*(calendar days|hari kalender)/i',
                'The decision horizon is 7 calendar days.',
                'The decision horizon must never be expressed in calendar days.',
            ],
            'MD-S056-R0131' => [
                '/(non-null|not null)'.$g(60).'(reason-free|no reason code)'.$g(40).'(is|means|therefore)\s+.{0,20}decision-grade/i',
                'A field that is non-null, in range, and reason-free is therefore decision-grade.',
                'A field that is non-null, in range, and reason-free must never be described as decision-grade on that basis alone.',
            ],

            // MARKET_DATA_PLATFORM_EOD_BASELINE.md — the forbidden-claims list
            'MD-S001-R0139' => [
                '/.?market-data:daily.?'.$g(40).'(menjalankan|runs|executes)\s+(jalur )?(publish|publication)/i',
                'Command `market-data:daily` menjalankan jalur publish/readability.',
                'Command `market-data:daily` tidak menjalankan jalur publish/readability.',
            ],
            'MD-S001-R0140' => [
                '/.?market-data:backfill.?'.$g(40).'(otomatis|automatically)\s+(mempublish|publishes|publish)/i',
                'Command `market-data:backfill` otomatis mempublish dataset.',
                'Command `market-data:backfill` tidak otomatis mempublish dataset.',
            ],
            'MD-S001-R0141' => [
                '/coverage\s+(dihitung|dihitung ulang)\s+dari\s+success(ful)?\s+request/i',
                'Coverage dihitung dari successful request count.',
                'Coverage bukan dihitung dari successful request count.',
            ],
        ];
    }

    /**
     * The documentation-state readings this corpus can actually be held to. Each names a subject the
     * active corpus raises, a pattern proving it raises it, and a contradiction that must be absent.
     *
     * `MD-S001-R0155` (archived proof window) and `MD-S001-R0158` (official authority, commercial SLA,
     * redistribution right) are deliberately absent: no active document raises either subject, so
     * there is no documentation state to read and a clean result would be vacuous.
     *
     * @return array<string,array{subject:string,contradiction:string,fixture:string}>
     */
    private function affirmative(): array
    {
        $g = function (int $n) { return $this->gap($n); };

        return [
            'MD-S001-R0154' => [
                'subject' => '/intentional dataset start/i',
                'contradiction' => '/.?2023-01-02.?'.$g(40).'(is|adalah)\s+(an? )?(accidental|kebetulan|tidak disengaja)/i',
                'fixture' => 'The `2023-01-02` boundary is an accidental source limit.',
            ],
            'MD-S001-R0156' => [
                'subject' => '/development data frontier/i',
                'contradiction' => '/development data frontier'.$g(40).'(is|adalah)\s+(a )?(fixed|static|tetap|permanen)/i',
                'fixture' => 'The development data frontier is a fixed boundary of the platform.',
            ],
            'MD-S001-R0157' => [
                'subject' => '/(operational activation|activation marker|marker activation)/i',
                'contradiction' => '/operational activation'.$g(40).'(has already|sudah)\s+(occurred|terjadi|dilakukan)/i',
                'fixture' => 'Operational activation sudah terjadi pada platform ini.',
            ],
        ];
    }

    /**
     * The active document set: everything current, minus the surfaces that legitimately contain the
     * forbidden sentences — frozen strategy, which owns the prohibition, and the traceability matrix,
     * which stores strategy rule text verbatim under fingerprint control.
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

    // ---------- adequacy of the guard itself ----------

    public function test_every_pattern_matches_the_statement_it_forbids(): void
    {
        $forbidden = $this->forbidden();
        $this->assertCount(11, $forbidden, 'the conflation family under test is 11 rules');

        foreach ($forbidden as $ruleId => [$pattern, $assertion, $_]) {
            $this->assertSame(
                1,
                preg_match($pattern, $assertion),
                $ruleId.': the pattern must match the very statement it forbids, otherwise a clean result proves nothing'
            );
        }
    }

    /**
     * A document that restates the prohibition is obeying the rule. A guard that cannot tell the two
     * apart turns red on correct documents — which is how it flagged one of its own evidence records
     * before this check existed.
     */
    public function test_a_quotation_of_the_prohibition_is_not_read_as_an_assertion(): void
    {
        $misread = [];
        foreach ($this->forbidden() as $ruleId => [$pattern, $_, $prohibition]) {
            if (preg_match($pattern, $prohibition, $m)) {
                $misread[] = $ruleId.' :: '.trim($m[0]);
            }
        }

        $this->assertSame([], $misread, 'the guard read a statement of the prohibition as a violation of it');
    }

    public function test_every_contradiction_pattern_matches_the_statement_it_rejects(): void
    {
        $affirmative = $this->affirmative();
        $this->assertCount(3, $affirmative, 'three documentation-state readings have a subject this corpus raises');

        foreach ($affirmative as $ruleId => $spec) {
            $this->assertSame(
                1,
                preg_match($spec['contradiction'], $spec['fixture']),
                $ruleId.': the contradiction pattern must match the statement it rejects'
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

        foreach ($forbidden as $ruleId => [$pattern, $_, $__]) {
            foreach ($forbidden as $otherId => [$___, $otherAssertion, $____]) {
                if ($ruleId === $otherId) {
                    continue;
                }
                if (preg_match($pattern, $otherAssertion)) {
                    $collisions[] = $ruleId.' matches '.$otherId;
                }
            }
        }

        $this->assertSame([], $collisions, 'each conflation rule must be proven by its own pattern');
    }

    // ---------- the corpus ----------

    /**
     * @dataProvider forbiddenProvider
     */
    public function test_no_active_document_states_the_forbidden_conflation(string $ruleId, string $pattern): void
    {
        $documents = $this->activeDocuments();
        $this->assertGreaterThan(100, count($documents), 'the document scan must reach the active corpus');

        $claims = [];
        foreach ($documents as $relative => $text) {
            if (preg_match($pattern, $text, $match)) {
                $claims[] = $relative.' :: '.trim($match[0]);
            }
        }

        $this->assertSame([], $claims, $ruleId.': an active document conflates two terms the contract separates');
    }

    public function forbiddenProvider(): array
    {
        $out = [];
        foreach ((new self('x'))->forbidden() as $ruleId => [$pattern, $_, $__]) {
            $out[$ruleId] = [$ruleId, $pattern];
        }

        return $out;
    }

    /**
     * The corpus must raise the subject and must nowhere contradict the required reading. The subject
     * check is the anti-vacuity control: without it, deleting every mention would look like success.
     *
     * @dataProvider affirmativeProvider
     */
    public function test_the_documentation_state_reading_is_never_contradicted(string $ruleId, string $subject, string $contradiction): void
    {
        $documents = $this->activeDocuments();
        $this->assertGreaterThan(100, count($documents), 'the document scan must reach the active corpus');

        $raises = [];
        $contradicts = [];
        foreach ($documents as $relative => $text) {
            if (preg_match($subject, $text)) {
                $raises[] = $relative;
            }
            if (preg_match($contradiction, $text, $match)) {
                $contradicts[] = $relative.' :: '.trim($match[0]);
            }
        }

        $this->assertNotSame([], $raises, $ruleId.': no active document raises this subject, so a clean contradiction check would be vacuous');
        $this->assertSame([], $contradicts, $ruleId.': an active document contradicts the required reading');
    }

    public function affirmativeProvider(): array
    {
        $out = [];
        foreach ((new self('x'))->affirmative() as $ruleId => $spec) {
            $out[$ruleId] = [$ruleId, $spec['subject'], $spec['contradiction']];
        }

        return $out;
    }

    /**
     * The same conflations must not be encoded in executable surfaces. A document that stays silent
     * while the code asserts the conflation would satisfy the letter and miss the point.
     */
    public function test_no_executable_surface_encodes_a_forbidden_conflation(): void
    {
        $code = '';
        $scanned = 0;

        foreach (['app', 'config', 'database'] as $dir) {
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
        foreach ($this->forbidden() as $ruleId => [$pattern, $_, $__]) {
            if (preg_match($pattern, $code, $match)) {
                $claims[] = $ruleId.' :: '.trim($match[0]);
            }
        }

        $this->assertSame([], $claims, 'no executable surface may encode a forbidden conflation');
    }
}
