<?php

use App\Application\MarketData\Services\ArtifactSemanticHashService;
use App\Domain\MarketData\FreshnessState;
use App\Domain\MarketData\MarketDataScope;
use Carbon\Carbon;

/**
 * MD-B10-A003 (F-MD-B18-A002-033): the freshness vocabulary and the applicability rule of operational freshness.
 *
 * Authority (not production) is the source of every expectation here: the vocabulary is read from the corrected
 * read-model contract, the applicability rule from the readiness guarantee's ordered truth table
 * (DOC-CHG-20261005-001, owner decision D-MD-B18-A002-015). A pre-activation readable publication is NOT_APPLICABLE;
 * it is not FRESH, STALE, DEGRADED or NOT_AVAILABLE, and the decision never depends on the wall clock.
 */
class PreActivationFreshnessStateTest extends TestCase
{
    private const MARKER = '2026-09-01';

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function authority(string $relative): string
    {
        return (string) file_get_contents(base_path('docs/market_data/authority/strategy/book/'.$relative));
    }

    public function test_the_vocabulary_is_exactly_the_five_states_the_corrected_read_model_contract_lists(): void
    {
        $contract = $this->authority('Downstream_Consumer_Read_Model_Contract_LOCKED.md');
        $this->assertSame(1, preg_match('/^- `freshness_state`: (.+?)(?: \(|;)/m', $contract, $m), 'the contract line must be found');
        preg_match_all('/`([A-Z_]+)`/', $m[1], $states);
        $this->assertSame($states[1], FreshnessState::VOCABULARY, 'the vocabulary must be the contract list, in order');
        $this->assertSame(
            ['FRESH', 'STALE', 'DEGRADED', 'NOT_AVAILABLE', 'NOT_APPLICABLE'],
            FreshnessState::VOCABULARY
        );

        $guarantee = $this->authority('Downstream_Data_Readiness_Guarantee_LOCKED.md');
        foreach ($states[1] as $state) {
            $this->assertMatchesRegularExpression('/^- `'.$state.'`: /m', $guarantee, $state.' must be defined as a freshness state');
        }
        $this->assertNotSame(FreshnessState::NOT_APPLICABLE, FreshnessState::NOT_AVAILABLE);
    }

    public function test_the_authority_truth_table_is_the_rule_the_label_implements(): void
    {
        $guarantee = $this->authority('Downstream_Data_Readiness_Guarantee_LOCKED.md');
        // The only row of the ordered table that produces NOT_APPLICABLE is the pre-activation readable one.
        $this->assertSame(1, preg_match('/^\| The requested publication is `READABLE`, no such degraded condition applies, and operational freshness is not in force for the date \| `NOT_APPLICABLE` \|$/m', $guarantee));
        $this->assertSame(1, preg_match('/^\| The requested publication is `READABLE`, no such degraded condition applies, operational freshness is in force for the date, and all activated operational freshness gates pass \| `FRESH` \|$/m', $guarantee));
        $this->assertStringContainsString('never from the time of the read', $guarantee);
        $this->assertStringContainsString('a `READABLE` requested publication never carries `NOT_AVAILABLE`', $guarantee);
    }

    public function applicabilityGrid(): array
    {
        return [
            'no marker, any date' => [null, '2026-03-20', false],
            'blank marker' => ['  ', '2026-03-20', false],
            'date before the marker' => [self::MARKER, '2026-08-31', false],
            'the marker date itself is in force' => [self::MARKER, '2026-09-01', true],
            'date after the marker' => [self::MARKER, '2026-09-02', true],
            'a date-time marker is cut to its date' => ['2026-09-01 00:00:00', '2026-08-31', false],
            'a date-time marker, boundary' => ['2026-09-01 00:00:00', '2026-09-01', true],
        ];
    }

    /**
     * @dataProvider applicabilityGrid
     */
    public function test_operational_freshness_is_in_force_only_from_the_marker_date_and_never_backdated($marker, string $date, bool $expected): void
    {
        $this->assertSame($expected, FreshnessState::isInForce($marker, $date));
        $this->assertSame(
            $expected ? FreshnessState::PENDING_EVALUATION_LABEL : FreshnessState::NOT_APPLICABLE,
            FreshnessState::runLabelFor($marker, $date)
        );
    }

    public function test_a_pre_activation_run_label_is_never_fresh_stale_degraded_or_not_available(): void
    {
        foreach ([null, self::MARKER] as $marker) {
            $date = $marker === null ? '2026-03-20' : '2026-08-15';
            $label = FreshnessState::runLabelFor($marker, $date);
            $this->assertSame('NOT_APPLICABLE', $label);
            foreach ([FreshnessState::FRESH, FreshnessState::STALE, FreshnessState::DEGRADED, FreshnessState::NOT_AVAILABLE] as $other) {
                $this->assertNotSame($other, $label, 'a pre-activation state must not be forced to '.$other);
            }
        }
    }

    public function test_an_activated_run_keeps_the_pending_label_which_is_never_a_vocabulary_state(): void
    {
        $this->assertSame('NOT_EVALUATED', FreshnessState::runLabelFor(self::MARKER, '2026-09-10'));
        $this->assertFalse(FreshnessState::isState(FreshnessState::PENDING_EVALUATION_LABEL));
        // And it is never hashed as fresh: the canonicaliser sends it to NOT_AVAILABLE, as it did before.
        $this->assertSame('NOT_AVAILABLE', ArtifactSemanticHashService::normalizeFreshnessState('NOT_EVALUATED'));
    }

    public function test_the_decision_does_not_depend_on_the_wall_clock(): void
    {
        $results = [];
        foreach (['2020-01-01 00:00:00', '2026-08-31 23:59:59', '2026-09-01 00:00:00', '2035-12-31 12:00:00'] as $now) {
            Carbon::setTestNow($now);
            $results[$now] = [
                'before' => FreshnessState::runLabelFor(self::MARKER, '2026-08-31'),
                'boundary' => FreshnessState::runLabelFor(self::MARKER, '2026-09-01'),
                'none' => FreshnessState::runLabelFor(null, '2026-09-01'),
            ];
        }
        $this->assertCount(1, array_unique(array_map('json_encode', $results)), 'the result must be identical whatever the clock says');
        $this->assertSame(['before' => 'NOT_APPLICABLE', 'boundary' => 'NOT_EVALUATED', 'none' => 'NOT_APPLICABLE'], reset($results));

        $source = (string) file_get_contents(base_path('app/Domain/MarketData/FreshnessState.php'));
        foreach (['now', 'time', 'microtime', 'strtotime', 'date', 'gmdate', 'hrtime'] as $clock) {
            $this->assertSame(0, preg_match('/(?<![A-Za-z_>:\\\\])'.$clock.'\s*\(/', $source), 'the applicability rule must not call a clock function: '.$clock);
        }
        foreach (['Carbon', 'new DateTime', 'DateTimeImmutable('] as $clock) {
            $this->assertStringNotContainsString($clock, $source, 'the applicability rule must not construct a clock value: '.$clock);
        }
    }

    public function test_a_date_time_value_is_cut_to_its_date_and_a_malformed_value_fails_closed(): void
    {
        $this->assertTrue(FreshnessState::isInForce(new DateTimeImmutable('2026-09-01 08:00:00'), new DateTimeImmutable('2026-09-01 23:00:00')));
        $this->assertFalse(FreshnessState::isInForce(new DateTimeImmutable('2026-09-02'), '2026-09-01'));

        foreach (['2026-13-01', '2026-02-30', 'yesterday', '20260901'] as $bad) {
            try {
                FreshnessState::isInForce($bad, '2026-09-01');
                $this->fail('a malformed marker was accepted: '.$bad);
            } catch (RuntimeException $e) {
                $this->assertStringContainsString('MARKET_DATA_OPERATIONAL_START_DATE_INVALID', $e->getMessage());
            }
            try {
                FreshnessState::isInForce(self::MARKER, $bad);
                $this->fail('a malformed requested date was accepted: '.$bad);
            } catch (RuntimeException $e) {
                $this->assertStringContainsString('MARKET_DATA_REQUESTED_DATE_INVALID', $e->getMessage());
            }
        }

        try {
            FreshnessState::isInForce(self::MARKER, null);
            $this->fail('a missing requested date was accepted next to a marker');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('MARKET_DATA_REQUESTED_DATE_INVALID', $e->getMessage());
        }
    }

    public function test_the_scope_and_the_freshness_rule_agree_on_applicability(): void
    {
        foreach ([null, '2026-09-01'] as $marker) {
            $scope = new MarketDataScope('Asia/Jakarta', '2023-01-02', $marker);
            foreach (['2025-12-31', '2026-08-31', '2026-09-01', '2026-09-02', '2027-01-04'] as $date) {
                $this->assertSame(FreshnessState::isInForce($marker, $date), $scope->isOperationallyActivatedFor($date), (string) $marker.' '.$date);
                $this->assertSame($scope->isOperationallyActivatedFor($date) ? 'OPERATIONAL' : 'DEVELOPMENT', $scope->stateFor($date));
            }
        }
    }

    public function test_the_canonicaliser_passes_the_five_states_through_and_keeps_every_other_label_out_of_fresh(): void
    {
        // The list is written out, not read from the production constant: a vocabulary that lost a state must fail here.
        foreach (['FRESH', 'STALE', 'DEGRADED', 'NOT_AVAILABLE', 'NOT_APPLICABLE'] as $state) {
            $this->assertSame($state, ArtifactSemanticHashService::normalizeFreshnessState($state));
            $this->assertSame($state, ArtifactSemanticHashService::normalizeFreshnessState(' '.strtolower($state).' '));
        }
        // Labels of runs created before DOC-CHG-20261005-001 stay outside the vocabulary on purpose: a sealed
        // publication is re-verified from its stored run and must keep its sealed identity.
        foreach (['DEVELOPMENT_NOT_OPERATIONAL', 'NOT_EVALUATED', '', null, 'fresh-ish', 'ON_TARGET'] as $raw) {
            $this->assertSame('NOT_AVAILABLE', ArtifactSemanticHashService::normalizeFreshnessState($raw), var_export($raw, true));
        }
    }

    public function test_no_application_code_writes_a_freshness_state_after_a_run_exists(): void
    {
        $offenders = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path('app'), FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $text = (string) file_get_contents($file->getPathname());
            if (preg_match('/->update\(\s*\[[^\]]*[\'"]freshness_state[\'"]/s', $text) === 1
                || preg_match('/->freshness_state\s*=[^=]/', $text) === 1) {
                $offenders[] = substr($file->getPathname(), strlen(base_path()) + 1);
            }
        }
        $this->assertSame([], $offenders, 'sealed history must not be relabelled: no code may rewrite the freshness state of an existing run');
    }
}
