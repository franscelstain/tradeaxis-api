<?php

use PHPUnit\Framework\TestCase;

/**
 * `MD-S050-R0051` — a replay verdict may not close a data-quality finding, release a
 * quarantine, dismiss a corporate-action candidate, or satisfy a continuity check.
 *
 * `B18ReplayAdmissibilityBoundaryTest` proves the citation boundary as prose: nothing in the
 * active corpus *states* a forbidden claim. That guard reads sentences, so a real consumer
 * enacting the prohibition as code -- a call, not a claim -- would cross it silently. This is
 * the executable half: it enumerates every file that actually reads a replay verdict field,
 * asserts that enumeration is exhaustive over `app/`, and then asserts none of those files
 * performs any of the rule's four prohibited actions.
 *
 * F-MD-B18-A002-015 recorded, at the time this guard was written, that no such consumer exists:
 * verdict readers are confined to the replay, backfill, evidence and command surfaces. This
 * guard makes that a standing, fail-closed assertion rather than a one-time review note.
 */
class B18ReplayVerdictConsumerBoundaryTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    /**
     * Every field name that carries a replay verdict.
     */
    private const VERDICT_FIELDS = ['replay_status', 'comparison_result'];

    /**
     * The reviewed consumer surface: every file under `app/` that reads a verdict field, as of
     * this review. A file added later that reads one of `VERDICT_FIELDS` will not be in this
     * list, so `test_the_reviewed_list_is_exactly_every_file_that_reads_a_verdict_field` fails
     * until it is reviewed against the same four prohibitions and added here.
     */
    private const REVIEWED_VERDICT_CONSUMERS = [
        'app/Application/MarketData/Services/BackfillLifecycleOrchestrator.php',
        'app/Application/MarketData/Services/FullRangeCurrentEvidenceReplayService.php',
        'app/Application/MarketData/Services/MarketDataEvidenceExportService.php',
        'app/Application/MarketData/Services/ReplayBackfillService.php',
        'app/Application/MarketData/Services/ReplaySmokeSuiteService.php',
        'app/Application/MarketData/Services/ReplayVerificationService.php',
        'app/Console/Commands/MarketData/BackfillLifecycleCommand.php',
        'app/Console/Commands/MarketData/BackfillMissingTickersCommand.php',
        'app/Console/Commands/MarketData/FullRangeCurrentEvidenceReplayCommand.php',
        'app/Console/Commands/MarketData/ReplayBackfillCommand.php',
        'app/Console/Commands/MarketData/ReplaySmokeSuiteCommand.php',
        'app/Console/Commands/MarketData/VerifyReplayCommand.php',
        'app/Infrastructure/Persistence/MarketData/ReplayResultRepository.php',
    ];

    /**
     * MD-S050-R0051's four prohibited actions, each a verb/noun proximity check in either order
     * rather than a bare keyword. Either order matters because a real call site as often reads
     * `$this->quarantine->release(...)` (subject, then verb) as `releaseQuarantine(...)` (verb,
     * then subject). A bare single-word scan would also be noisy on this exact surface: the OHLC
     * `close` price field and the publication/correction `candidate` vocabulary are both used
     * throughout these same files for unrelated, legitimate reasons, so both the verb and the
     * subject must appear together, within a short span, for either ordering.
     *
     * @return array<string,string> label => pattern
     */
    private function forbiddenActions(): array
    {
        return [
            'closes a data-quality finding' =>
                '/(close[sd]?\s*\(?[^;{}]{0,30}(data.?quality.?)?finding'
                .'|finding[^;{}]{0,30}close[sd]?\s*\()/i',
            'releases a quarantine' =>
                '/(release[sd]?\s*\(?[^;{}]{0,30}quarantine'
                .'|quarantine[^;{}]{0,30}release[sd]?\s*\()/i',
            'dismisses a corporate-action candidate' =>
                '/(dismiss(es|ed)?\s*\(?[^;{}]{0,30}(corporate.?action.?)?candidate'
                .'|(corporate.?action.?)?candidate[^;{}]{0,30}dismiss(es|ed)?\s*\()/i',
            'satisfies a continuity check' =>
                '/(satisf(y|ies|ied)\s*\(?[^;{}]{0,30}continuity'
                .'|continuity[^;{}]{0,30}satisf(y|ies|ied)\s*\()/i',
        ];
    }

    /**
     * Comments are removed before scanning: the contract is documented in the code that must
     * obey it, and a docblock or `//` line explaining the boundary is not the violation it
     * describes. String literals are kept -- an actual call site is where a violation would
     * live.
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

    /** @return array<int,string> repo-relative paths, sorted */
    private function filesReadingAVerdictField(): array
    {
        $found = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->root().'/app', FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if (! $file->isFile() || strtolower($file->getExtension()) !== 'php') {
                continue;
            }
            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($this->root()) + 1));
            $source = (string) file_get_contents($file->getPathname());
            foreach (self::VERDICT_FIELDS as $field) {
                if (strpos($source, $field) !== false) {
                    $found[] = $relative;
                    break;
                }
            }
        }
        sort($found);

        return $found;
    }

    /**
     * Guards the guard. A path mistake that scanned an empty or wrong tree would make every
     * assertion below pass against nothing.
     */
    public function test_the_scan_actually_reaches_the_reviewed_consumers(): void
    {
        $found = $this->filesReadingAVerdictField();
        $this->assertGreaterThanOrEqual(10, count($found), 'the verdict-field scan found suspiciously few files');
        foreach (self::REVIEWED_VERDICT_CONSUMERS as $expected) {
            $this->assertFileExists($this->root().'/'.$expected, $expected.' must exist for this guard to mean anything');
        }
    }

    /**
     * The reviewed list is exactly the set of files that read a verdict field -- not a subset a
     * prior review happened to name. A new consumer appearing anywhere under `app/` fails this
     * closed until it is reviewed and added.
     */
    public function test_the_reviewed_list_is_exactly_every_file_that_reads_a_verdict_field(): void
    {
        $found = $this->filesReadingAVerdictField();
        $reviewed = self::REVIEWED_VERDICT_CONSUMERS;
        sort($reviewed);

        $this->assertSame(
            $reviewed,
            $found,
            'a file now reads a replay verdict field outside the reviewed consumer surface -- '
            .'review it against MD-S050-R0051 (close a finding / release a quarantine / dismiss a '
            .'candidate / satisfy a continuity check) before adding it to REVIEWED_VERDICT_CONSUMERS'
        );
    }

    /**
     * None of the reviewed consumers performs any of the four prohibited actions.
     */
    public function test_no_reviewed_consumer_closes_releases_dismisses_or_satisfies_on_a_verdict(): void
    {
        $violations = [];
        foreach (self::REVIEWED_VERDICT_CONSUMERS as $relative) {
            $full = $this->root().'/'.$relative;
            $this->assertFileExists($full, $relative.' must exist for this guard to mean anything');
            $source = $this->stripComments((string) file_get_contents($full));

            foreach ($this->forbiddenActions() as $label => $pattern) {
                if (preg_match($pattern, $source)) {
                    $violations[] = $relative.' :: '.$label;
                }
            }
        }

        $this->assertSame(
            [],
            $violations,
            'MD-S050-R0051: a replay verdict may not close a finding, release a quarantine, '
            .'dismiss a candidate, or satisfy a continuity check'
        );
    }

    /**
     * Each pattern actually fires on the exact action it forbids -- proof the scan is not
     * vacuous, and that a pattern too narrow to match anything would not report a false clean
     * surface.
     */
    public function test_each_forbidden_action_pattern_fires_on_its_own_sample_violation(): void
    {
        $samples = [
            'closes a data-quality finding' => '$this->findingRepository->closeFinding($findingId, "REPLAY_PASS");',
            'releases a quarantine' => '$this->quarantine->release($tickerCode, "verified by replay");',
            'dismisses a corporate-action candidate' => '$this->candidates->dismissCorporateActionCandidate($candidateId);',
            'satisfies a continuity check' => 'if ($replayStatus === "PASS") { $this->continuityCheck->satisfy($runId); }',
        ];

        foreach ($this->forbiddenActions() as $label => $pattern) {
            $this->assertArrayHasKey($label, $samples, 'no sample violation authored for '.$label);
            $this->assertMatchesRegularExpression(
                $pattern,
                $samples[$label],
                $label.' pattern failed to catch its own sample violation -- the scan would report a clean surface even if this exact code existed'
            );
        }
    }

    /**
     * The reviewed surface's own legitimate vocabulary -- OHLC `close`, publication/correction
     * `candidate` -- must not trip the guard. Proven directly against real file content rather
     * than assumed: `test_no_reviewed_consumer_...` above already passes against the real files,
     * this asserts why -- both words are present in that surface today, and neither is a
     * violation.
     */
    public function test_the_reviewed_surfaces_own_benign_close_and_candidate_vocabulary_is_untouched(): void
    {
        $orchestrator = $this->stripComments((string) file_get_contents(
            $this->root().'/app/Application/MarketData/Services/BackfillLifecycleOrchestrator.php'
        ));
        $this->assertStringContainsString("'close'", $orchestrator, 'the OHLC close field must still be present for this proof to mean anything');

        $exporter = $this->stripComments((string) file_get_contents(
            $this->root().'/app/Application/MarketData/Services/MarketDataEvidenceExportService.php'
        ));
        $this->assertStringContainsString('candidate_publication_id', $exporter, 'the publication-candidate vocabulary must still be present for this proof to mean anything');

        foreach ([$orchestrator, $exporter] as $source) {
            foreach ($this->forbiddenActions() as $label => $pattern) {
                $this->assertSame(0, preg_match($pattern, $source), $label.' pattern false-fired on legitimate close/candidate vocabulary');
            }
        }
    }
}
