<?php

use PHPUnit\Framework\TestCase;

/**
 * The two audit documents must agree about each other.
 *
 * LUMEN_IMPLEMENTATION_STATUS records what was built; LUMEN_CONTRACT_TRACKER records the rules
 * each piece of work is bound by. They point at each other: an implementation entry names its
 * [RELATED_CONTRACT], and that contract names its [RELATED_IMPLEMENTATION] back.
 *
 * That round trip was checked by hand, one assertion at a time, for about a dozen entries out of
 * fifty. Roughly a hundred string assertions covered a quarter of the documents and needed four
 * new lines every time an entry was added.
 *
 * The rule does not depend on which entries exist, so it is derived here instead: every
 * cross-reference in either direction must resolve, for every entry, including entries written
 * after this test.
 *
 * Why it matters at all: these documents are how a later session learns which rules already
 * apply. A dangling reference is a rule that looks recorded and cannot be found — which is worse
 * than no reference, because it reads as governed.
 */
class AuditCrossReferenceIntegrityTest extends TestCase
{
    private const STATUS_DOC = 'docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md';
    private const TRACKER_DOC = 'docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md';

    private function read(string $relativePath): string
    {
        $path = dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $this->assertFileExists($path);

        return file_get_contents($path);
    }

    /**
     * Splits a document into entry blocks keyed by entry name.
     *
     * Both documents are append-only, so they carry historical sections that deliberately use
     * the names in force at the time. Those are excluded: an entry renamed since 2026-05 must
     * not make its own past record a violation, because the fix would be to rewrite history.
     *
     * Historical material appears two ways, and both terminate a block:
     *   - an entry whose name begins with "Historical"
     *   - a "### Historical ... Context" sub-heading inside an otherwise current entry
     *
     * Getting this wrong is not academic. A first version of this parser attributed markers from
     * historical sub-sections to the current entry above them and reported two dangling
     * references that were nothing of the kind.
     *
     * @return array<string, string>
     */
    private function entryBlocks(string $document, string $headingPattern): array
    {
        $lines = preg_split('/\R/', $document);

        $blocks = [];
        $current = null;

        foreach ($lines as $line) {
            if (strpos($line, '### ') === 0) {
                $current = null;
                continue;
            }

            if (preg_match($headingPattern, $line, $match)) {
                $name = trim($match[1]);

                if (strpos($name, 'Historical') === 0) {
                    $current = null;
                    continue;
                }

                $current = $name;
                $blocks[$current] = $blocks[$current] ?? '';
                continue;
            }

            // A heading-shaped line that the strict pattern did not accept still ends the block.
            // "- Historical 2026-05-03 CORRECTION_LIFECYCLE_SAFETY_CONTRACT -> LOCKED" is one:
            // without this, its markers were credited to whichever entry happened to precede it.
            if (preg_match('/^- .+ (?:->|→) [A-Z_]+$/u', $line)) {
                $current = null;
                continue;
            }

            if ($current !== null) {
                $blocks[$current] .= $line."\n";
            }
        }

        return $blocks;
    }

    /**
     * @return array<string, string> implementation name => block body
     */
    private function implementationEntries(): array
    {
        return $this->entryBlocks(
            $this->read(self::STATUS_DOC),
            '/^- (.+?) (?:->|→) (?:DONE|LOCKED|ENFORCED|PARTIAL|BLOCKED|REVIEW_REQUIRED|IN_PROGRESS)$/u'
        );
    }

    /**
     * @return array<string, string> contract name => block body
     */
    private function contractEntries(): array
    {
        return $this->entryBlocks(
            $this->read(self::TRACKER_DOC),
            '/^- ([A-Z0-9_]+_CONTRACT) (?:->|→) [A-Z_]+$/u'
        );
    }

    /**
     * The two marker types cannot share a parser, and the difference is easy to get wrong.
     *
     * Contract names are tokens: [A-Z0-9_]+_CONTRACT. One line may list several, separated by
     * " / ", so they are extracted by shape.
     *
     * Implementation names are free prose and frequently contain " / " themselves — "Correction
     * Lifecycle Hardening / Correction Lifecycle Safety" is one entry, not two. Splitting them
     * the same way shredded twenty-seven valid names into fragments that resolved to nothing.
     *
     * @return string[]
     */
    private function relatedContracts(string $block): array
    {
        preg_match_all('/\[RELATED_CONTRACT\] (.+)/', $block, $lines);

        $contracts = [];

        foreach ($lines[1] as $line) {
            preg_match_all('/[A-Z0-9_]+_CONTRACT/', $line, $tokens);
            $contracts = array_merge($contracts, $tokens[0]);
        }

        return array_values(array_unique($contracts));
    }

    /**
     * @return string[]
     */
    private function relatedImplementations(string $block): array
    {
        preg_match_all('/\[RELATED_IMPLEMENTATION\] (.+)/', $block, $lines);

        return array_values(array_unique(array_map('trim', $lines[1])));
    }

    public function test_both_documents_parse_into_a_meaningful_number_of_entries(): void
    {
        // Guards the guard: a heading-pattern change that stopped matching would make every
        // cross-reference assertion below pass vacuously.
        $this->assertGreaterThan(30, count($this->implementationEntries()));
        $this->assertGreaterThan(30, count($this->contractEntries()));
    }

    /**
     * Every contract an implementation claims to be bound by must exist in the tracker.
     */
    public function test_every_related_contract_resolves_to_a_tracked_contract(): void
    {
        $contracts = array_keys($this->contractEntries());
        $dangling = [];

        foreach ($this->implementationEntries() as $implementation => $block) {
            foreach ($this->relatedContracts($block) as $contract) {
                if (! in_array($contract, $contracts, true)) {
                    $dangling[] = $implementation.' -> '.$contract;
                }
            }
        }

        $this->assertSame([], $dangling, 'Implementation entries reference contracts the tracker does not define.');
    }

    /**
     * And every implementation a contract claims to govern must exist in the status document.
     */
    public function test_every_related_implementation_resolves_to_a_recorded_entry(): void
    {
        $implementations = array_keys($this->implementationEntries());
        $dangling = [];

        foreach ($this->contractEntries() as $contract => $block) {
            foreach ($this->relatedImplementations($block) as $implementation) {
                if (! in_array($implementation, $implementations, true)) {
                    $dangling[] = $contract.' -> '.$implementation;
                }
            }
        }

        $this->assertSame([], $dangling, 'Contract entries reference implementations the status document does not record.');
    }

    /**
     * A strict round-trip rule was written here and then removed, because the documents do not
     * work that way and asserting it would have invented an invariant rather than protected one.
     *
     * The rule was: if an implementation names a contract, that contract must name it back. Five
     * entries failed, and each turned out to be legitimate. A contract can govern several pieces
     * of work over time — PUBLISHABILITY_STATE_INTEGRITY_CONTRACT names three implementations and
     * is referenced by a fourth — and there is no requirement that it list every one. Forcing the
     * documents to satisfy the rule would have meant editing an append-only audit trail to match
     * a test.
     *
     * What survives is the half that is genuinely broken when it fails: a reference that resolves
     * to nothing at all. Those are the two tests above.
     */

    /**
     * Every tracked contract must be reachable from the work that produced it. A contract with no
     * implementation behind it is a rule nobody has yet had to satisfy.
     */
    public function test_every_tracked_contract_names_at_least_one_implementation(): void
    {
        $orphans = [];

        foreach ($this->contractEntries() as $contract => $block) {
            if ($this->relatedImplementations($block) === []) {
                $orphans[] = $contract;
            }
        }

        $this->assertSame([], $orphans, 'Contracts must record which implementation they govern.');
    }

    /**
     * Contract names are canonical identifiers. A duplicate heading means two blocks claim the
     * same name and whichever a reader finds first wins.
     */
    public function test_contract_names_are_unique(): void
    {
        preg_match_all('/^- ([A-Z0-9_]+_CONTRACT) (?:->|→)/mu', $this->read(self::TRACKER_DOC), $matches);

        $counts = array_count_values($matches[1]);
        $duplicated = array_keys(array_filter($counts, function ($count) {
            return $count > 1;
        }));

        $this->assertSame([], $duplicated);
    }

    /**
     * Both documents declare which session is currently active, and they must name the same one.
     * They are updated separately, so disagreement means one was left behind.
     */
    public function test_both_documents_name_the_same_current_canonical_override(): void
    {
        $status = $this->canonicalOverrideDate($this->read(self::STATUS_DOC));
        $tracker = $this->canonicalOverrideDate($this->read(self::TRACKER_DOC));

        $this->assertNotSame('', $status, 'The implementation status must date its canonical override.');
        $this->assertSame($status, $tracker);
    }

    private function canonicalOverrideDate(string $document): string
    {
        $pattern = '/^## CURRENT CANONICAL(?: AUDIT)? OVERRIDE .* (?P<date>\d{4}-\d{2}-\d{2})$/mu';
        $this->assertMatchesRegularExpression($pattern, $document);
        preg_match($pattern, $document, $match);

        $this->assertStringContainsString('HISTORICAL SESSION RECORD', $document);
        $this->assertStringNotContainsString("\nACTIVE SESSION:\n", $document);

        return trim($match['date']);
    }
}
