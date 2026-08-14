<?php

use App\Infrastructure\Persistence\MarketData\EventRiskSourceRepository;
use Tests\Support\UsesMarketDataSqlite;

class EventRiskSourceRepositoryTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    public function test_resolves_exact_date_uma_without_carry_forward(): void
    {
        $repository = new EventRiskSourceRepository();

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-05-19',
            'event_type_code' => 'UMA',
            'source_name' => 'idx_manual',
        ]);

        $context = $repository->resolveEventRiskContextForTickerIds([1], '2026-05-19');

        $this->assertSame('UMA', $context[1]['trading_status_code']);
        $this->assertSame(0, $context[1]['is_suspended']);
        $this->assertSame(1, $context[1]['is_uma']);
        $this->assertSame(1, $context[1]['event_risk_flag']);
        $this->assertStringContainsString('TRADING_STATUS:UMA', $context[1]['event_risk_reasons']);

        $this->assertSame([], $repository->resolveEventRiskContextForTickerIds([1], '2026-05-20'));
    }

    public function test_carries_suspended_until_unsuspended(): void
    {
        $repository = new EventRiskSourceRepository();

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-05-19',
            'event_type_code' => 'SUSPENDED',
            'source_name' => 'idx_manual',
        ]);

        $context = $repository->resolveEventRiskContextForTickerIds([1], '2026-05-20');

        $this->assertSame('SUSPENDED', $context[1]['trading_status_code']);
        $this->assertSame(1, $context[1]['is_suspended']);
        $this->assertSame(1, $context[1]['event_risk_flag']);
        $this->assertSame(
            [],
            $repository->suspendedTickerIdsAsOf([1], '2026-05-20'),
            'a legacy status event remains risk context but cannot shrink coverage without a verified V2 revision binding'
        );

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-05-21',
            'event_type_code' => 'UNSUSPENDED',
            'source_name' => 'idx_manual',
        ]);

        $context = $repository->resolveEventRiskContextForTickerIds([1], '2026-05-21');

        $this->assertSame('UNSUSPENDED', $context[1]['trading_status_code']);
        $this->assertSame(0, $context[1]['is_suspended']);
        $this->assertSame(0, $context[1]['event_risk_flag']);
        $this->assertSame([], $repository->suspendedTickerIdsAsOf([1], '2026-05-22'));
        $this->assertSame([], $repository->resolveEventRiskContextForTickerIds([1], '2026-05-22'));
    }

    public function test_carries_special_monitoring_until_special_monitoring_end(): void
    {
        $repository = new EventRiskSourceRepository();

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 2,
            'ticker_code' => 'BBRI',
            'trade_date' => '2026-05-19',
            'event_type_code' => 'SPECIAL_MONITORING_START',
            'source_name' => 'idx_manual',
        ]);

        $context = $repository->resolveEventRiskContextForTickerIds([2], '2026-05-20');

        $this->assertSame('SPECIAL_MONITORING_START', $context[2]['trading_status_code']);
        $this->assertSame(0, $context[2]['is_suspended']);
        $this->assertSame(0, $context[2]['is_uma']);
        $this->assertSame(1, $context[2]['event_risk_flag']);
        $this->assertStringContainsString('TRADING_STATUS:SPECIAL_MONITORING_START', $context[2]['event_risk_reasons']);

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 2,
            'ticker_code' => 'BBRI',
            'trade_date' => '2026-05-21',
            'event_type_code' => 'SPECIAL_MONITORING_END',
            'source_name' => 'idx_manual',
        ]);

        $context = $repository->resolveEventRiskContextForTickerIds([2], '2026-05-21');

        $this->assertSame('SPECIAL_MONITORING_END', $context[2]['trading_status_code']);
        $this->assertSame(0, $context[2]['is_suspended']);
        $this->assertSame(0, $context[2]['is_uma']);
        $this->assertSame(0, $context[2]['event_risk_flag']);
        $this->assertSame([], $repository->resolveEventRiskContextForTickerIds([2], '2026-05-22'));
    }

    public function test_unsuspended_only_clears_suspension_not_special_monitoring(): void
    {
        $repository = new EventRiskSourceRepository();

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-05-19',
            'event_type_code' => 'SPECIAL_MONITORING_START',
            'source_name' => 'idx_manual',
        ]);

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-05-20',
            'event_type_code' => 'SUSPENDED',
            'source_name' => 'idx_manual',
        ]);

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-05-21',
            'event_type_code' => 'UNSUSPENDED',
            'source_name' => 'idx_manual',
        ]);

        $context = $repository->resolveEventRiskContextForTickerIds([1], '2026-05-22');

        $this->assertSame('SPECIAL_MONITORING_START', $context[1]['trading_status_code']);
        $this->assertSame(0, $context[1]['is_suspended']);
        $this->assertSame(0, $context[1]['is_uma']);
        $this->assertSame(1, $context[1]['event_risk_flag']);
    }

    public function test_projection_uses_single_primary_canonical_code_and_moves_composite_context_to_reasons(): void
    {
        $repository = new EventRiskSourceRepository();

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 3,
            'ticker_code' => 'TEST',
            'trade_date' => '2026-05-19',
            'event_type_code' => 'SPECIAL_MONITORING_START',
            'source_name' => 'idx_manual',
        ]);

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 3,
            'ticker_code' => 'TEST',
            'trade_date' => '2026-05-20',
            'event_type_code' => 'UMA',
            'source_name' => 'idx_manual',
        ]);

        $context = $repository->resolveEventRiskContextForTickerIds([3], '2026-05-20');

        $this->assertSame('UMA', $context[3]['trading_status_code']);
        $this->assertSame(0, $context[3]['is_suspended']);
        $this->assertSame(1, $context[3]['is_uma']);
        $this->assertSame(1, $context[3]['event_risk_flag']);
        $this->assertStringContainsString('TRADING_STATUS:SPECIAL_MONITORING_START', $context[3]['event_risk_reasons']);
        $this->assertStringContainsString('TRADING_STATUS:UMA', $context[3]['event_risk_reasons']);
        $this->assertStringNotContainsString(',', $context[3]['trading_status_code']);
    }

    public function test_suspension_observed_projects_as_suspended_risk_without_becoming_suspended_start(): void
    {
        $repository = new EventRiskSourceRepository();

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 4,
            'ticker_code' => 'LONG',
            'trade_date' => '2026-05-19',
            'event_type_code' => 'SUSPENSION_OBSERVED',
            'source_name' => 'idx_suspension_gt_6m',
        ]);

        $context = $repository->resolveEventRiskContextForTickerIds([4], '2026-05-20');

        $this->assertSame('SUSPENSION_OBSERVED', $context[4]['trading_status_code']);
        $this->assertSame(1, $context[4]['is_suspended']);
        $this->assertSame(0, $context[4]['is_uma']);
        $this->assertSame(1, $context[4]['event_risk_flag']);
        $this->assertStringContainsString('TRADING_STATUS:SUSPENSION_OBSERVED', $context[4]['event_risk_reasons']);
    }

    public function test_no_source_data_does_not_fabricate_active_or_unsuspended_projection(): void
    {
        $repository = new EventRiskSourceRepository();

        $this->assertSame([], $repository->resolveEventRiskContextForTickerIds([5], '2026-05-20'));
    }

    public function test_repository_rejects_unknown_event_type_code(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new EventRiskSourceRepository())->upsertTradingStatusEvent([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-05-19',
            'event_type_code' => 'ACTIVE',
            'source_name' => 'idx_manual',
        ]);
    }

    /**
     * Ascending trading-day sequence ending on 2026-05-25, skipping weekends so the depth
     * assertions below exercise trading-day traversal rather than calendar subtraction.
     */
    private function tradingDates(): array
    {
        return [
            '2026-05-11', '2026-05-12', '2026-05-13', '2026-05-14', '2026-05-15',
            '2026-05-18', '2026-05-19', '2026-05-20', '2026-05-21', '2026-05-22',
            '2026-05-25',
        ];
    }

    private function insertCorporateAction($tickerId, $actionDate, $actionType): void
    {
        (new EventRiskSourceRepository())->upsertCorporateAction([
            'ticker_id' => $tickerId,
            'ticker_code' => 'TEST',
            'action_date' => $actionDate,
            'action_type' => $actionType,
            'source_name' => 'idx_manual',
        ]);
    }

    /**
     * F-040 — a re-import must not erase an attributed factor by omission.
     *
     * `updateOrInsert` writes every value it is handed, so passing `$row[...] ?? null` for the
     * quantitative payload meant re-importing an event from a CSV without the factor columns —
     * the three-column minimum the importer documents as valid — silently replaced a stored factor
     * and its provenance with NULL. The next recompute would then have restored the very artefact
     * the factor was obtained to remove.
     *
     * Both halves are pinned, because a fix that only preserved would remove the ability to correct
     * a wrong factor: absence leaves the value alone, an explicit null clears it.
     */
    public function test_an_omitted_column_preserves_a_stored_factor_while_an_explicit_null_clears_it(): void
    {
        $repository = new EventRiskSourceRepository();
        $identity = [
            'ticker_id' => 10,
            'ticker_code' => 'TEST',
            'action_date' => '2026-05-20',
            'action_type' => 'STOCK_SPLIT',
            'source_name' => 'idx_manual',
        ];

        $repository->upsertCorporateAction($identity + [
            'ex_date' => '2026-05-20',
            'price_adjustment_factor' => 0.2,
            'volume_adjustment_factor' => 5,
            'adjustment_source' => 'EXCHANGE_ANNOUNCEMENT',
            'source_ref' => 'Peng-1/BEI/05-2026',
        ]);

        $repository->upsertCorporateAction($identity);
        $preserved = DB::table('market_data_corporate_actions')->where('ticker_id', 10)->first();

        $this->assertEqualsWithDelta(0.2, (float) $preserved->price_adjustment_factor, 1e-9, 'an omitted column may not erase a factor');
        $this->assertSame('EXCHANGE_ANNOUNCEMENT', $preserved->adjustment_source, 'nor its provenance');
        $this->assertSame('Peng-1/BEI/05-2026', $preserved->source_ref, 'nor the reference to its document');
        $this->assertSame('2026-05-20', (string) $preserved->ex_date);

        $repository->upsertCorporateAction($identity + [
            'price_adjustment_factor' => null,
            'adjustment_source' => null,
        ]);
        $cleared = DB::table('market_data_corporate_actions')->where('ticker_id', 10)->first();

        $this->assertNull($cleared->price_adjustment_factor, 'an explicit null must still clear it');
        $this->assertNull($cleared->adjustment_source);
        $this->assertEqualsWithDelta(5.0, (float) $cleared->volume_adjustment_factor, 1e-9, 'and must clear only what was named');
    }

    /**
     * F-041 — the same rule on the sibling upsert, which F-040 left behind.
     *
     * Fixing `upsertCorporateAction` and not `upsertTradingStatusEvent` cost a full audit cycle to
     * discover: on a production row it erased an IDX announcement URL and its text, with all 3,700
     * rows carrying both.
     */
    public function test_a_trading_status_event_keeps_its_reference_when_a_column_is_omitted(): void
    {
        $repository = new EventRiskSourceRepository();
        $identity = [
            'ticker_id' => 10,
            'ticker_code' => 'TEST',
            'trade_date' => '2026-05-20',
            'event_type_code' => 'SUSPENDED',
            'source_name' => 'idx_manual',
        ];

        $repository->upsertTradingStatusEvent($identity + [
            'source_ref' => 'https://idx.co.id/pengumuman/1',
            'notes' => 'IDX mengumumkan suspensi',
        ]);

        $repository->upsertTradingStatusEvent($identity);
        $preserved = DB::table('market_data_trading_status_events')->where('ticker_id', 10)->first();

        $this->assertSame('https://idx.co.id/pengumuman/1', $preserved->source_ref);
        $this->assertSame('IDX mengumumkan suspensi', $preserved->notes);

        $repository->upsertTradingStatusEvent($identity + ['source_ref' => null]);
        $cleared = DB::table('market_data_trading_status_events')->where('ticker_id', 10)->first();

        $this->assertNull($cleared->source_ref, 'an explicit null must still clear');
        $this->assertSame('IDX mengumumkan suspensi', $cleared->notes, 'and must clear only what was named');
    }

    /**
     * No third upsert may reintroduce the pattern.
     *
     * `?? null` inside an `updateOrInsert` value block is the defect signature: the value is always
     * written, so a caller with nothing to say about a column erases it. A sweep on 2026-08-11 found
     * exactly two instances across nine upsert sites; this keeps the count at zero rather than
     * relying on the next author knowing the history.
     */
    public function test_no_upsert_writes_an_optional_field_as_null(): void
    {
        $offenders = [];

        foreach (glob(__DIR__.'/../../../app/Infrastructure/Persistence/MarketData/*.php') as $path) {
            /*
             * Comments are stripped first. The first version of this guard matched the prose that
             * documents the defect — the explanation of `?? null` inside the very docblock warning
             * against it — and reported three offenders where the code had none. A guard that
             * cannot tell code from commentary teaches people to ignore it.
             */
            $code = '';
            foreach (token_get_all((string) file_get_contents($path)) as $token) {
                if (is_array($token)) {
                    $code .= in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true) ? ' ' : $token[1];
                    continue;
                }
                $code .= $token;
            }

            $lines = explode("\n", $code);
            foreach ($lines as $index => $line) {
                if (strpos($line, 'updateOrInsert') === false) {
                    continue;
                }

                if (strpos(implode("\n", array_slice($lines, $index, 30)), '?? null') !== false) {
                    $offenders[] = basename($path).' near updateOrInsert';
                }
            }
        }
        $offenders = array_values(array_unique($offenders));

        $this->assertSame(
            [],
            $offenders,
            'an updateOrInsert value built with ?? null erases stored data when the caller omits the column'
        );
    }

    /**
     * F-042 — a re-import must not move the coordinates that say when a row came into being.
     *
     * `created_at` and `recorded_at` were written on every upsert, so re-importing an event moved
     * both to now. For `recorded_at` that inverts what `F-028` was built for: an event genuinely
     * known in June, re-imported in August, disappears from every cutoff before August. The
     * platform would understate what it knew, which is as wrong for replay as the leak that
     * overstated it.
     *
     * Four properties, because a fix that only preserved would break the insert path and a fix that
     * only handled one method would repeat `F-040` into `F-041`.
     */
    public function test_creation_timestamps_survive_a_reimport_on_both_upserts(): void
    {
        $repository = new EventRiskSourceRepository();
        $action = [
            'ticker_id' => 10, 'ticker_code' => 'TEST', 'action_date' => '2026-05-20',
            'action_type' => 'STOCK_SPLIT', 'source_name' => 'idx_manual',
        ];
        $status = [
            'ticker_id' => 10, 'ticker_code' => 'TEST', 'trade_date' => '2026-05-20',
            'event_type_code' => 'SUSPENDED', 'source_name' => 'idx_manual',
        ];

        $repository->upsertCorporateAction($action + ['recorded_at' => '2026-06-01 09:00:00', 'created_at' => '2026-06-01 09:00:00']);
        $repository->upsertTradingStatusEvent($status + ['recorded_at' => '2026-06-01 09:00:00', 'created_at' => '2026-06-01 09:00:00']);

        $repository->upsertCorporateAction($action);
        $repository->upsertTradingStatusEvent($status);

        $storedAction = DB::table('market_data_corporate_actions')->where('ticker_id', 10)->first();
        $storedStatus = DB::table('market_data_trading_status_events')->where('ticker_id', 10)->first();

        foreach ([$storedAction, $storedStatus] as $stored) {
            $this->assertSame('2026-06-01 09:00:00', (string) $stored->recorded_at, 'a re-import may not move the as-known coordinate');
            $this->assertSame('2026-06-01 09:00:00', (string) $stored->created_at, 'nor the creation time');
        }

        // A new row still receives both, otherwise preserving would have broken the insert path.
        // array_merge, not `+`: the union operator keeps the left-hand value and would have
        // re-upserted the same row instead of creating one.
        $repository->upsertCorporateAction(array_merge($action, ['action_date' => '2026-05-21']));
        $fresh = DB::table('market_data_corporate_actions')->where('action_date', '2026-05-21')->first();
        $this->assertNotNull($fresh->recorded_at);
        $this->assertNotNull($fresh->created_at);

        // And a caller that names a value is still obeyed, so a correction remains possible.
        $repository->upsertCorporateAction($action + ['recorded_at' => '2020-01-01 00:00:00']);
        $corrected = DB::table('market_data_corporate_actions')->where('ticker_id', 10)->where('action_date', '2026-05-20')->first();
        $this->assertSame('2020-01-01 00:00:00', (string) $corrected->recorded_at);
    }

    public function test_contamination_depth_counts_trading_days_not_calendar_days(): void
    {
        $this->insertCorporateAction(10, '2026-05-20', 'STOCK_SPLIT');

        $contamination = (new EventRiskSourceRepository())
            ->resolveCorporateActionContaminationForTickerIds([10], $this->tradingDates());

        $this->assertCount(1, $contamination[10]);
        $this->assertSame('STOCK_SPLIT', $contamination[10][0]['action_type_code']);
        // 2026-05-20 -> 21, 22, 25 = three trading days back, though five calendar days.
        $this->assertSame(3, $contamination[10][0]['depth']);
        $this->assertTrue($contamination[10][0]['breaks_price_continuity']);
        $this->assertTrue($contamination[10][0]['breaks_volume_continuity']);
        $this->assertFalse($contamination[10][0]['is_unmapped_type']);
    }

    public function test_action_on_requested_date_has_depth_zero(): void
    {
        $this->insertCorporateAction(11, '2026-05-25', 'BONUS_SHARE');

        $contamination = (new EventRiskSourceRepository())
            ->resolveCorporateActionContaminationForTickerIds([11], $this->tradingDates());

        $this->assertSame(0, $contamination[11][0]['depth']);
    }

    /**
     * A non-trading action date takes effect on the first trading day on or after it.
     */
    public function test_action_on_non_trading_day_resolves_forward_to_next_trading_day(): void
    {
        // 2026-05-16 and 2026-05-17 are absent from the sequence; the next trading day is 05-18.
        $this->insertCorporateAction(12, '2026-05-16', 'STOCK_SPLIT');

        $contamination = (new EventRiskSourceRepository())
            ->resolveCorporateActionContaminationForTickerIds([12], $this->tradingDates());

        $this->assertSame(5, $contamination[12][0]['depth'], '2026-05-18 is five trading days before 2026-05-25');
    }

    public function test_unmapped_action_type_is_treated_as_breaking_both_continuities(): void
    {
        $this->insertCorporateAction(13, '2026-05-20', 'SOMETHING_NEW_FROM_IDX');

        $contamination = (new EventRiskSourceRepository())
            ->resolveCorporateActionContaminationForTickerIds([13], $this->tradingDates());

        $this->assertTrue($contamination[13][0]['is_unmapped_type']);
        $this->assertTrue($contamination[13][0]['breaks_price_continuity']);
        $this->assertTrue($contamination[13][0]['breaks_volume_continuity']);
    }

    public function test_non_breaking_action_types_are_omitted_entirely(): void
    {
        $this->insertCorporateAction(14, '2026-05-20', 'TICKER_CODE_CHANGE');
        $this->insertCorporateAction(14, '2026-05-21', 'COMPANY_NAME_CHANGE');

        $contamination = (new EventRiskSourceRepository())
            ->resolveCorporateActionContaminationForTickerIds([14], $this->tradingDates());

        $this->assertSame([], $contamination, 'identity-only actions cannot contaminate anything');
    }

    /**
     * Dilution issues new shares denominated in the existing unit, so historical price and
     * volume stay directly comparable. Only unit redefinition breaks the arithmetic.
     */
    public function test_dilution_action_types_do_not_contaminate_anything(): void
    {
        $repository = new EventRiskSourceRepository();

        foreach ([
            'PRIVATE_PLACEMENT',
            'NON_PREEMPTIVE_RIGHTS_ISSUE',
            'WARRANT',
            'WARRANT_EXERCISE',
            'MANDATORY_CONVERTIBLE_BOND',
            'ESOP_MSOP',
        ] as $index => $actionType) {
            $tickerId = 200 + $index;
            $this->insertCorporateAction($tickerId, '2026-05-20', $actionType);

            $contamination = $repository->resolveCorporateActionContaminationForTickerIds(
                [$tickerId],
                $this->tradingDates()
            );

            $this->assertSame([], $contamination, $actionType.' is dilution, not redenomination');
        }
    }

    public function test_lifecycle_action_types_do_not_contaminate_anything(): void
    {
        $repository = new EventRiskSourceRepository();

        foreach (['IPO', 'DELISTING', 'PARTIAL_DELISTING', 'PARTIAL_RELISTING', 'CAPITAL_DEFICIENCY'] as $index => $actionType) {
            $tickerId = 300 + $index;
            $this->insertCorporateAction($tickerId, '2026-05-20', $actionType);

            $contamination = $repository->resolveCorporateActionContaminationForTickerIds(
                [$tickerId],
                $this->tradingDates()
            );

            $this->assertSame([], $contamination, $actionType.' has no continuity to break');
        }
    }

    /**
     * A rights issue rescales the price series through the ex-rights adjustment, but no
     * holding is multiplied automatically, so the volume series keeps its unit.
     */
    public function test_rights_issue_breaks_price_continuity_but_not_volume(): void
    {
        $this->insertCorporateAction(15, '2026-05-20', 'RIGHTS_ISSUE');

        $contamination = (new EventRiskSourceRepository())
            ->resolveCorporateActionContaminationForTickerIds([15], $this->tradingDates());

        $this->assertTrue($contamination[15][0]['breaks_price_continuity']);
        $this->assertFalse($contamination[15][0]['breaks_volume_continuity']);
    }

    public function test_unit_redefining_action_types_break_both_continuities(): void
    {
        $repository = new EventRiskSourceRepository();

        foreach (['STOCK_SPLIT', 'REVERSE_STOCK_SPLIT', 'BONUS_SHARE', 'STOCK_DIVIDEND', 'MERGER'] as $index => $actionType) {
            $tickerId = 400 + $index;
            $this->insertCorporateAction($tickerId, '2026-05-20', $actionType);

            $contamination = $repository->resolveCorporateActionContaminationForTickerIds(
                [$tickerId],
                $this->tradingDates()
            );

            $this->assertTrue($contamination[$tickerId][0]['breaks_price_continuity'], $actionType);
            $this->assertTrue($contamination[$tickerId][0]['breaks_volume_continuity'], $actionType);
        }
    }

    public function test_action_outside_the_window_is_not_returned(): void
    {
        $this->insertCorporateAction(16, '2026-04-30', 'STOCK_SPLIT');
        $this->insertCorporateAction(16, '2026-06-01', 'STOCK_SPLIT');

        $contamination = (new EventRiskSourceRepository())
            ->resolveCorporateActionContaminationForTickerIds([16], $this->tradingDates());

        $this->assertSame([], $contamination, 'past-window and future-dated actions must both be excluded');
    }
}
