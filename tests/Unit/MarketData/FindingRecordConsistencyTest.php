<?php

use PHPUnit\Framework\TestCase;

/**
 * Every finding record must state a lifecycle a tool can read, and every open finding must reach the
 * canonical current state.
 *
 * `CURRENT_STATE.md` used to list findings from the current stage's register row alone. A finding
 * raised against any other stage therefore never appeared. Three were invisible when `MD-B00-A003`
 * checked: `F-MD-B00-A001-003`, open since `MD-B00-A001`; `F-MD-B01-A008-001`; and
 * `F-MD-B03-A003-001`, raised in the immediately preceding attempt. Section 20 of the execution
 * contract requires canonical current state to stay synchronized, and a finding nobody can see from
 * the current-state document is not synchronized — it is filed.
 *
 * Two lifecycle field names are in use. Most records write `- Status:`; `F-MD-20260821-03` writes
 * `- State:` and is registered `LIFECYCLE_UPDATE_ONLY`, so the reader accepts both rather than
 * restructuring a governed record to suit a reader.
 *
 * `UNREADABLE` is treated as open. A lifecycle nobody can parse is not evidence of closure.
 */
class FindingRecordConsistencyTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    private function findingsDir(): string
    {
        return $this->root().'/docs/market_data/development/findings';
    }

    /** @return array<int,array{id:string,status:string,file:string}> */
    private function findings(): array
    {
        $out = [];
        foreach (glob($this->findingsDir().'/F-*.md') as $path) {
            $text = (string) file_get_contents($path);
            $status = 'UNREADABLE';
            if (preg_match('/^- (?:Status|State):\s*`?([A-Z_]+)`?/m', $text, $m)) {
                $status = $m[1];
            }
            $id = basename($path);
            if (preg_match('/^(F-[A-Z0-9-]+)_/', $id, $m)) {
                $id = $m[1];
            }
            $out[] = ['id' => $id, 'status' => $status, 'file' => basename($path)];
        }

        return $out;
    }

    private function isOpen(string $status): bool
    {
        return ! in_array($status, ['CLOSED', 'RESOLVED', 'SUPERSEDED'], true);
    }

    public function test_every_finding_record_states_a_lifecycle_a_tool_can_read(): void
    {
        $findings = $this->findings();
        $this->assertGreaterThan(8, count($findings), 'the findings corpus scan must reach the records');

        $unreadable = [];
        foreach ($findings as $finding) {
            if ($finding['status'] === 'UNREADABLE') {
                $unreadable[] = $finding['file'];
            }
        }

        $this->assertSame([], $unreadable, 'a finding whose lifecycle cannot be read cannot be reported or closed');
    }

    public function test_every_open_finding_reaches_the_canonical_current_state(): void
    {
        $state = (string) file_get_contents(
            $this->root().'/docs/market_data/development/implementation/CURRENT_STATE.md'
        );
        $this->assertStringContainsString('Open findings across every stage', $state, 'current state must carry the corpus-wide finding line');

        $missing = [];
        $open = 0;
        foreach ($this->findings() as $finding) {
            if (! $this->isOpen($finding['status'])) {
                continue;
            }
            $open++;
            if (strpos($state, $finding['id']) === false) {
                $missing[] = $finding['id'].' ('.$finding['status'].')';
            }
        }

        $this->assertGreaterThan(0, $open, 'the open-finding scan must find the open records');
        $this->assertSame([], $missing, 'an open finding is absent from the generated current state — regenerate it');
    }

    /**
     * The reader must accept both field names and must not silently treat a missing lifecycle as
     * closed. Fixtures are built here rather than read from disk so the check does not depend on a
     * particular record staying malformed.
     */
    public function test_the_lifecycle_reader_handles_both_field_names_and_refuses_to_guess(): void
    {
        $pattern = '/^- (?:Status|State):\s*`?([A-Z_]+)`?/m';

        $this->assertSame(1, preg_match($pattern, "- Status: `OPEN`\n", $m));
        $this->assertSame('OPEN', $m[1]);

        $this->assertSame(1, preg_match($pattern, "- State: `CLOSED`\n", $m));
        $this->assertSame('CLOSED', $m[1]);

        $this->assertSame(0, preg_match($pattern, "- Severity: `P2`\n"), 'a record with no lifecycle field must not resolve to one');

        $this->assertTrue($this->isOpen('UNREADABLE'), 'an unreadable lifecycle counts as open');
        $this->assertTrue($this->isOpen('PARTIALLY_RESOLVED'), 'a partially resolved finding is still open');
        $this->assertFalse($this->isOpen('CLOSED'));
    }
}
