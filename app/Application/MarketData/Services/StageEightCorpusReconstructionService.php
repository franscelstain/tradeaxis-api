<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use App\Infrastructure\Persistence\MarketData\CorpusAdmissionRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * One bounded, resumable lifecycle campaign for the Stage 8 current-authoritative corpus.
 */
class StageEightCorpusReconstructionService
{
    const ACQUISITION_BATCH_SIZE = 20;

    private $calendar;
    private $tickers;
    private $acquisition;
    private $pipeline;
    private $corrections;
    private $publications;
    private $hashes;
    private $artifacts;
    private $admissions;

    public function __construct(
        MarketCalendarRepository $calendar,
        TickerMasterRepository $tickers,
        ApiBackfillRangeAcquisitionService $acquisition,
        MarketDataPipelineService $pipeline,
        EodCorrectionRepository $corrections,
        EodPublicationRepository $publications,
        EodArtifactRepository $artifacts,
        DeterministicHashService $hashes,
        CorpusAdmissionRepository $admissions = null
    ) {
        $this->calendar = $calendar;
        $this->tickers = $tickers;
        $this->acquisition = $acquisition;
        $this->pipeline = $pipeline;
        $this->corrections = $corrections;
        $this->publications = $publications;
        $this->artifacts = $artifacts;
        $this->hashes = $hashes;
        $this->admissions = $admissions ?: new CorpusAdmissionRepository();
    }

    public function plan(): array
    {
        $admission = $this->admissions->activeDecision();
        $targets = $this->baselineTargets($admission);
        $scopeStart = (string) $targets[0]->trade_date;
        $scopeEnd = (string) $targets[count($targets) - 1]->trade_date;
        $dates = array_map(function ($row) {
            return (string) $row->trade_date;
        }, $targets);
        $this->assertFrozenScopeMatchesCalendar($scopeStart, $scopeEnd, $dates);
        $tickerCodes = $this->tickerCodesForDates($dates);

        $previousWindowDays = config('market_data.source.api_backfill.window_days');
        config()->set('market_data.source.api_backfill.window_days', 2000);
        try {
            $sourcePlan = $this->acquisition->plan($scopeStart, $scopeStart, $scopeEnd, $dates, $tickerCodes);
        } finally {
            config()->set('market_data.source.api_backfill.window_days', $previousWindowDays);
        }

        return [
            'status' => 'PLAN_ONLY',
            'scope_start' => $scopeStart,
            'scope_end' => $scopeEnd,
            'target_date_count' => count($targets),
            'ticker_count' => count($tickerCodes),
            'baseline_max_publication_id' => max(array_map(function ($row) {
                return (int) $row->publication_id;
            }, $targets)),
            'source_plan' => $sourcePlan,
            'acquisition_batch_size' => self::ACQUISITION_BATCH_SIZE,
            'acquisition_batch_count' => (int) ceil(count($tickerCodes) / self::ACQUISITION_BATCH_SIZE),
            'admission_decision_id' => $admission ? (int) $admission->admission_decision_id : null,
            'intentional_dataset_start' => $admission ? (string) $admission->intentional_dataset_start : null,
            'admitted_from' => $admission ? (string) $admission->admitted_from : null,
            'stage_9_replay' => 'FORBIDDEN_NOT_EXECUTED',
        ];
    }

    public function execute(array $options = []): array
    {
        $campaign = $this->resolveCampaign(! empty($options['resume']));
        if ((string) $campaign->state === 'COMPLETE') {
            return $this->completedCampaignResult($campaign, true);
        }

        $outputDir = $this->outputDirectory($campaign, $options['output_dir'] ?? null);
        $this->ensureDirectory($outputDir);
        $targets = $this->campaignTargets($campaign->campaign_id);
        $dates = array_map(function ($row) {
            return (string) $row->trade_date;
        }, $targets);
        $tickerCodes = $this->tickerCodesForDates($dates);
        $tickerCounts = $this->tickers->getTickerCountsForTradeDates($dates);
        $acquisitionManifest = $this->acquireToCache($campaign, $outputDir, $dates, $tickerCodes, $tickerCounts);

        foreach ($this->campaignTargets($campaign->campaign_id) as $target) {
            if ((string) $target->state === 'COMPLETE') {
                continue;
            }

            try {
                $this->processTarget($campaign, $target, $outputDir, $acquisitionManifest);
            } catch (\Throwable $e) {
                $this->failTargetCorrection($target, $e);
                DB::table('md_stage8_reconstruction_targets')
                    ->where('campaign_target_id', $target->campaign_target_id)
                    ->update([
                        'state' => 'FAILED',
                        'reason_code' => $this->reasonCode($e),
                        'updated_at' => $this->now(),
                    ]);
                DB::table('md_stage8_reconstruction_campaigns')
                    ->where('campaign_id', $campaign->campaign_id)
                    ->update([
                        'state' => 'BLOCKED',
                        'result_json' => json_encode([
                            'status' => 'BLOCKED',
                            'blocked_trade_date' => (string) $target->trade_date,
                            'reason_code' => $this->reasonCode($e),
                            'complete_target_count' => (int) DB::table('md_stage8_reconstruction_targets')
                                ->where('campaign_id', $campaign->campaign_id)
                                ->where('state', 'COMPLETE')
                                ->count(),
                            'stage_9_replay' => 'NOT_EXECUTED',
                        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                        'updated_at' => $this->now(),
                    ]);
                throw $e;
            }
        }

        $oracle = $this->auditCampaign((int) $campaign->campaign_id);
        if (($oracle['violation_count'] ?? 1) !== 0) {
            DB::table('md_stage8_reconstruction_campaigns')
                ->where('campaign_id', $campaign->campaign_id)
                ->update([
                    'state' => 'BLOCKED',
                    'result_json' => json_encode($oracle, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'updated_at' => $this->now(),
                ]);
            throw new \RuntimeException('STAGE8_CURRENT_CORPUS_ORACLE_FAILED: current-authoritative violations remain.');
        }

        $this->cleanupAcquisitionRows($outputDir);

        DB::table('md_stage8_reconstruction_campaigns')
            ->where('campaign_id', $campaign->campaign_id)
            ->update([
                'state' => 'COMPLETE',
                'completed_at' => $this->now(),
                'result_json' => json_encode($oracle, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'updated_at' => $this->now(),
            ]);

        return $this->completedCampaignResult(
            DB::table('md_stage8_reconstruction_campaigns')->where('campaign_id', $campaign->campaign_id)->first(),
            false
        );
    }

    public function auditCampaign($campaignId): array
    {
        $campaign = DB::table('md_stage8_reconstruction_campaigns')->where('campaign_id', $campaignId)->first();
        if (! $campaign) {
            throw new \RuntimeException('STAGE8_CAMPAIGN_NOT_FOUND.');
        }

        $targetCount = (int) DB::table('md_stage8_reconstruction_targets')->where('campaign_id', $campaignId)->count();
        $completeCount = (int) DB::table('md_stage8_reconstruction_targets')->where('campaign_id', $campaignId)->where('state', 'COMPLETE')->count();

        $current = DB::table('md_stage8_reconstruction_targets as target')
            ->leftJoin('eod_current_publication_pointer as pointer', 'pointer.trade_date', '=', 'target.trade_date')
            ->leftJoin('eod_publications as publication', 'publication.publication_id', '=', 'pointer.publication_id')
            ->leftJoin('eod_runs as run', 'run.run_id', '=', 'publication.run_id')
            ->where('target.campaign_id', $campaignId);

        $admissionBindingViolations = 0;
        if ($campaign->admission_decision_id === null) {
            $admissionBindingViolations++;
        } else {
            $admissionBindingViolations += (int) (clone $current)->where(function ($query) use ($campaign) {
                $query->whereNull('run.corpus_admission_decision_id')
                    ->orWhereNull('publication.corpus_admission_decision_id')
                    ->orWhere('run.corpus_admission_decision_id', '<>', (int) $campaign->admission_decision_id)
                    ->orWhere('publication.corpus_admission_decision_id', '<>', (int) $campaign->admission_decision_id);
            })->count();
            $admissionBindingViolations += (int) DB::table('md_stage8_reconstruction_targets as target')
                ->leftJoin('md_publication_lineage_bindings as lineage', 'lineage.publication_id', '=', 'target.replacement_publication_id')
                ->where('target.campaign_id', $campaignId)
                ->where(function ($query) use ($campaign) {
                    $query->whereNull('lineage.publication_lineage_id')
                        ->orWhereNull('lineage.corpus_admission_decision_id')
                        ->orWhere('lineage.corpus_admission_decision_id', '<>', (int) $campaign->admission_decision_id);
                })->count();
        }

        $pointerViolationCount = (int) (clone $current)->where(function ($query) {
            $query->whereNull('pointer.publication_id')
                ->orWhereColumn('pointer.publication_id', '<>', 'target.replacement_publication_id')
                ->orWhereColumn('pointer.run_id', '<>', 'target.replacement_run_id')
                ->orWhere('publication.seal_state', '<>', 'SEALED')
                ->orWhere('run.terminal_status', '<>', 'SUCCESS')
                ->orWhere('run.publishability_state', '<>', 'READABLE')
                ->orWhereNull('publication.price_product_code')
                ->orWhereNull('publication.price_product_version')
                ->orWhereNull('publication.factor_set_hash')
                ->orWhereNull('run.price_product_code')
                ->orWhereNull('run.price_product_version')
                ->orWhereNull('run.factor_set_hash')
                ->orWhereColumn('publication.run_id', '<>', 'run.run_id')
                ->orWhereColumn('publication.price_product_code', '<>', 'run.price_product_code')
                ->orWhereColumn('publication.price_product_version', '<>', 'run.price_product_version')
                ->orWhereColumn('publication.factor_set_hash', '<>', 'run.factor_set_hash');
        })->count();

        $barViolations = (int) DB::table('md_stage8_reconstruction_targets as target')
            ->join('eod_bars_history as bar', 'bar.publication_id', '=', 'target.replacement_publication_id')
            ->where('target.campaign_id', $campaignId)
            ->where(function ($query) {
                $query->whereNull('bar.listing_id')
                    ->orWhereNull('bar.source_observation_id')
                    ->orWhereNull('bar.canonicalization_version')
                    ->orWhere('bar.price_product_code', '<>', 'RAW')
                    ->orWhereNull('bar.quality_state')
                    ->orWhereNull('bar.source_scale_state');
            })->count();

        $runViolations = (int) (clone $current)->where(function ($query) {
            foreach ([
                'coverage_expected_count', 'coverage_bar_not_expected_count',
                'coverage_expectation_unknown_count', 'coverage_delivered_count',
                'coverage_delivered_valid_count',
            ] as $field) {
                $query->orWhereNull('run.'.$field);
            }
        })->count();

        $eligibilityViolations = (int) DB::table('md_stage8_reconstruction_targets as target')
            ->join('eod_eligibility_history as eligibility', 'eligibility.publication_id', '=', 'target.replacement_publication_id')
            ->where('target.campaign_id', $campaignId)
            ->where(function ($query) {
                foreach ([
                    'universe_membership_state', 'bar_expectation_state', 'delivery_state',
                    'canonical_quality_state', 'liquidity_state', 'temporal_status_state',
                    'event_risk_state', 'eligibility_reasons_json',
                    'market_structure_resolution_state',
                ] as $field) {
                    $query->orWhereNull('eligibility.'.$field);
                }
            })->count();

        $statusBindingViolations = (int) DB::table('md_stage8_reconstruction_targets as target')
            ->join('eod_eligibility_history as eligibility', 'eligibility.publication_id', '=', 'target.replacement_publication_id')
            ->where('target.campaign_id', $campaignId)
            ->where('eligibility.bar_expectation_state', 'BAR_NOT_EXPECTED')
            ->where(function ($query) {
                $query->whereNull('eligibility.trading_status_revision_id')
                    ->orWhereNull('eligibility.trading_status_source_observation_id');
            })->count();

        $lineageViolations = (int) DB::table('md_stage8_reconstruction_targets as target')
            ->leftJoin('eod_publications as publication', 'publication.publication_id', '=', 'target.replacement_publication_id')
            ->leftJoin('md_publication_lineage_bindings as lineage', 'lineage.publication_id', '=', 'publication.publication_id')
            ->where('target.campaign_id', $campaignId)
            ->where(function ($query) {
                $query->whereNull('lineage.publication_lineage_id')
                    ->orWhereNull('publication.factor_set_id')
                    ->orWhereColumn('publication.factor_set_id', '<>', 'lineage.factor_set_id')
                    ->orWhereNull('lineage.source_scale_assessment_set_hash')
                    ->orWhereNull('lineage.market_structure_revision_set_hash')
                    ->orWhereNull('lineage.factor_decision_set_hash')
                    ->orWhereColumn('publication.source_scale_assessment_set_hash', '<>', 'lineage.source_scale_assessment_set_hash')
                    ->orWhereColumn('publication.market_structure_revision_set_hash', '<>', 'lineage.market_structure_revision_set_hash')
                    ->orWhereColumn('publication.factor_decision_set_hash', '<>', 'lineage.factor_decision_set_hash');
            })->count();

        $marketStructureViolations = (int) DB::table('md_stage8_reconstruction_targets as target')
            ->join('md_publication_market_structure_bindings as binding', 'binding.publication_id', '=', 'target.replacement_publication_id')
            ->where('target.campaign_id', $campaignId)
            ->where(function ($query) {
                $query->whereNotIn('binding.resolution_state', [
                    'RESOLVED_STANDARD_BOARD',
                    'FAIL_CLOSED_BOARD_UNKNOWN',
                    'FAIL_CLOSED_BOARD_NOT_POINT_IN_TIME',
                    'FAIL_CLOSED_NON_STANDARD_BOARD',
                    'FAIL_CLOSED_BOARD_UNRECOGNIZED',
                    'FAIL_CLOSED_REVISION_MISSING',
                ])->orWhere(function ($resolved) {
                    $resolved->where('binding.resolution_state', 'RESOLVED_STANDARD_BOARD')
                        ->where(function ($missing) {
                            $missing->whereNull('binding.price_band_revision_id')
                                ->orWhereNull('binding.minimum_price_revision_id')
                                ->orWhereNull('binding.tick_size_revision_id')
                                ->orWhereNotNull('binding.reason_code');
                        });
                })->orWhere(function ($failed) {
                    $failed->where('binding.resolution_state', '<>', 'RESOLVED_STANDARD_BOARD')
                        ->where(function ($unexpected) {
                            $unexpected->whereNotNull('binding.price_band_revision_id')
                                ->orWhereNotNull('binding.minimum_price_revision_id')
                                ->orWhereNotNull('binding.tick_size_revision_id')
                                ->orWhereNull('binding.reason_code');
                        });
                });
            })->count();

        $factorDecisionViolations = (int) DB::table('md_stage8_reconstruction_targets as target')
            ->join('eod_publications as publication', 'publication.publication_id', '=', 'target.replacement_publication_id')
            ->join('md_adjustment_factor_decisions as decision', 'decision.factor_set_id', '=', 'publication.factor_set_id')
            ->leftJoin('md_source_scale_assessments as assessment', 'assessment.source_scale_assessment_id', '=', 'decision.source_scale_assessment_id')
            ->where('target.campaign_id', $campaignId)
            ->where(function ($query) {
                $query->whereNull('assessment.source_scale_assessment_id')
                    ->orWhere(function ($applied) {
                        $applied->where('decision.decision_state', 'APPLIED')
                            ->where('assessment.source_scale_state', '<>', 'AS_TRADED');
                    })
                    ->orWhere(function ($backAdjusted) {
                        $backAdjusted->where('decision.decision_state', 'HELD_PROVIDER_BACK_ADJUSTED')
                            ->where('assessment.source_scale_state', '<>', 'PROVIDER_BACK_ADJUSTED');
                    })
                    ->orWhere(function ($unknown) {
                        $unknown->where('decision.decision_state', 'HELD_SOURCE_SCALE_UNKNOWN')
                            ->where('assessment.source_scale_state', '<>', 'UNKNOWN');
                    })
                    ->orWhereNotIn('decision.decision_state', [
                        'APPLIED', 'HELD_PROVIDER_BACK_ADJUSTED', 'HELD_SOURCE_SCALE_UNKNOWN',
                    ]);
            })->count();

        $factorApplicationViolations = (int) DB::table('md_stage8_reconstruction_targets as target')
            ->join('eod_publications as publication', 'publication.publication_id', '=', 'target.replacement_publication_id')
            ->join('md_adjustment_factors as factor', 'factor.factor_set_id', '=', 'publication.factor_set_id')
            ->leftJoin('md_adjustment_factor_decisions as decision', function ($join) {
                $join->on('decision.factor_set_id', '=', 'factor.factor_set_id')
                    ->on('decision.corporate_action_revision_id', '=', 'factor.corporate_action_revision_id');
            })
            ->where('target.campaign_id', $campaignId)
            ->where(function ($query) {
                $query->whereNull('decision.factor_decision_id')
                    ->orWhere('decision.decision_state', '<>', 'APPLIED');
            })->count();

        $missingAppliedFactorViolations = (int) DB::table('md_stage8_reconstruction_targets as target')
            ->join('eod_publications as publication', 'publication.publication_id', '=', 'target.replacement_publication_id')
            ->join('md_adjustment_factor_decisions as decision', 'decision.factor_set_id', '=', 'publication.factor_set_id')
            ->leftJoin('md_adjustment_factors as factor', function ($join) {
                $join->on('factor.factor_set_id', '=', 'decision.factor_set_id')
                    ->on('factor.corporate_action_revision_id', '=', 'decision.corporate_action_revision_id');
            })
            ->where('target.campaign_id', $campaignId)
            ->where('decision.decision_state', 'APPLIED')
            ->whereNull('factor.adjustment_factor_id')
            ->count();

        $bindingCardinalityViolations = (int) DB::table('md_stage8_reconstruction_targets as target')
            ->where('target.campaign_id', $campaignId)
            ->whereRaw('(SELECT COUNT(*) FROM eod_eligibility_history e WHERE e.publication_id = target.replacement_publication_id) <> (SELECT COUNT(*) FROM md_publication_market_structure_bindings b WHERE b.publication_id = target.replacement_publication_id)')
            ->count();

        $historyViolations = (int) DB::table('md_stage8_reconstruction_targets as target')
            ->leftJoin('eod_publications as baseline', 'baseline.publication_id', '=', 'target.baseline_publication_id')
            ->where('target.campaign_id', $campaignId)
            ->where(function ($query) {
                $query->whereNull('baseline.publication_id')
                    ->orWhereColumn('baseline.bars_batch_hash', '<>', 'target.baseline_bars_batch_hash')
                    ->orWhereColumn('baseline.indicators_batch_hash', '<>', 'target.baseline_indicators_batch_hash')
                    ->orWhereColumn('baseline.eligibility_batch_hash', '<>', 'target.baseline_eligibility_batch_hash')
                    ->orWhere('baseline.seal_state', '<>', 'SEALED');
            })->count();

        [$baselineSnapshotViolations, $replacementArtifactHashViolations] = $this->artifactSnapshotViolations($campaignId);

        $violations = [
            'incomplete_target_count' => $targetCount - $completeCount,
            'corpus_admission_binding_violation_count' => $admissionBindingViolations,
            'pointer_or_identity_violation_count' => $pointerViolationCount,
            'bar_field_violation_count' => $barViolations,
            'coverage_field_violation_count' => $runViolations,
            'eligibility_field_violation_count' => $eligibilityViolations,
            'trading_status_binding_violation_count' => $statusBindingViolations,
            'publication_lineage_violation_count' => $lineageViolations,
            'market_structure_binding_violation_count' => $marketStructureViolations,
            'market_structure_cardinality_violation_count' => $bindingCardinalityViolations,
            'factor_decision_violation_count' => $factorDecisionViolations,
            'factor_application_violation_count' => $factorApplicationViolations,
            'missing_applied_factor_violation_count' => $missingAppliedFactorViolations,
            'baseline_history_metadata_violation_count' => $historyViolations,
            'baseline_history_snapshot_violation_count' => $baselineSnapshotViolations,
            'replacement_artifact_hash_violation_count' => $replacementArtifactHashViolations,
        ];

        return [
            'campaign_id' => (int) $campaignId,
            'scope_start' => (string) $campaign->scope_start,
            'scope_end' => (string) $campaign->scope_end,
            'target_date_count' => $targetCount,
            'complete_target_count' => $completeCount,
            'violations' => $violations,
            'violation_count' => array_sum($violations),
            'stage_9_replay' => 'NOT_EXECUTED',
        ];
    }

    private function processTarget($campaign, $target, $outputDir, array $acquisitionManifest): void
    {
        $tradeDate = (string) $target->trade_date;
        $current = $this->publications->findRawCurrentPublicationStateForTradeDate($tradeDate);
        if (! $current || (int) $current->publication_id !== (int) $target->baseline_publication_id) {
            if ($this->recoverCompletedTarget($campaign, $target, $current)) {
                return;
            }
            throw new \RuntimeException('STAGE8_BASELINE_POINTER_DRIFT: '.$tradeDate.' no longer points at the frozen baseline.');
        }

        $correctionId = $this->targetCorrectionId($target);

        [$rows, $telemetry] = $this->loadCachedDateAcquisition(
            $outputDir,
            $tradeDate,
            (int) ($acquisitionManifest['ticker_counts'][$tradeDate] ?? 0),
            (string) $campaign->campaign_uid,
            (string) $campaign->scope_start,
            (string) $campaign->scope_end
        );
        $run = $this->pipeline->importDailyFromAcquiredRows(
            $tradeDate,
            'api',
            $rows,
            $telemetry,
            $correctionId,
            'corpus_reconstruction'
        );
        if ($campaign->admission_decision_id === null) {
            throw new \RuntimeException('STAGE8_ADMISSION_EVIDENCE_INVALID: reconstruction campaign lacks its admission decision.');
        }
        $this->bindRunAndCandidateAdmission(
            (int) $run->run_id,
            (int) $campaign->admission_decision_id
        );
        $run = DB::table('eod_runs')->where('run_id', (int) $run->run_id)->first();
        $run = $this->pipeline->promoteDaily(
            $tradeDate,
            'api',
            (int) $run->run_id,
            $correctionId,
            'corpus_reconstruction_current'
        );

        if ((string) ($run->terminal_status ?? '') !== 'SUCCESS'
            || (string) ($run->publishability_state ?? '') !== 'READABLE') {
            throw new \RuntimeException('STAGE8_DATE_NOT_READABLE: '.$tradeDate.' ended '.(string) ($run->terminal_status ?? 'UNKNOWN').'.');
        }

        $replacement = $this->publications->resolveCurrentReadablePublicationForTradeDate($tradeDate);
        if (! $replacement || (int) $replacement->run_id !== (int) $run->run_id
            || (int) $replacement->publication_id <= (int) $campaign->baseline_max_publication_id) {
            throw new \RuntimeException('STAGE8_POINTER_SWITCH_NOT_PROVEN: '.$tradeDate.'.');
        }

        $this->checkpointCompletedTarget($target, $replacement, $run);
    }

    private function targetCorrectionId($target): int
    {
        $correction = $target->correction_id === null
            ? null
            : $this->corrections->findById((int) $target->correction_id);

        if ($correction && ! in_array((string) $correction->status, ['FAILED', 'REJECTED', 'CANCELLED'], true)) {
            return (int) $correction->correction_id;
        }

        $note = $correction
            ? 'Stage 8 retry after an immutable failed lifecycle attempt; frozen baseline retained.'
            : 'Stage 8 lifecycle reconstruction from a fresh Yahoo observation set; immutable baseline retained.';
        $correction = $this->corrections->createRequest(
            (string) $target->trade_date,
            'STAGE8_CURRENT_CORPUS_RECONSTRUCTION',
            $note,
            'system',
            (int) $target->baseline_publication_id,
            (int) $target->baseline_run_id
        );
        $correction = $this->corrections->approve((int) $correction->correction_id, 'system');

        DB::table('md_stage8_reconstruction_targets')
            ->where('campaign_target_id', $target->campaign_target_id)
            ->update([
                'correction_id' => (int) $correction->correction_id,
                'state' => 'RUNNING',
                'reason_code' => null,
                'updated_at' => $this->now(),
            ]);

        return (int) $correction->correction_id;
    }

    private function failTargetCorrection($target, \Throwable $failure): void
    {
        $persistedTarget = DB::table('md_stage8_reconstruction_targets')
            ->where('campaign_target_id', $target->campaign_target_id)
            ->first();
        if (! $persistedTarget || $persistedTarget->correction_id === null) {
            return;
        }

        $correction = $this->corrections->findById((int) $persistedTarget->correction_id);
        if (! $correction || in_array((string) $correction->status, [
            'FAILED', 'REJECTED', 'CANCELLED', 'PUBLISHED', 'CONSUMED_CURRENT', 'CLOSED',
        ], true)) {
            return;
        }

        $this->corrections->markFailed(
            (int) $correction->correction_id,
            $correction->new_run_id === null ? null : (int) $correction->new_run_id,
            (int) $target->baseline_run_id,
            $this->reasonCode($failure).': Stage 8 target failed before current-pointer replacement.',
            (int) $target->baseline_publication_id,
            null
        );
    }

    private function checkpointCompletedTarget($target, $replacement, $run): void
    {
        DB::table('md_stage8_reconstruction_targets')->where('campaign_target_id', $target->campaign_target_id)->update([
            'replacement_publication_id' => (int) $replacement->publication_id,
            'replacement_run_id' => (int) $run->run_id,
            'state' => 'COMPLETE',
            'reason_code' => null,
            'completed_at' => $this->now(),
            'updated_at' => $this->now(),
        ]);
    }

    private function recoverCompletedTarget($campaign, $target, $current): bool
    {
        if (! $current || $target->correction_id === null
            || (int) $current->publication_id <= (int) $campaign->baseline_max_publication_id) {
            return false;
        }

        $correction = $this->corrections->findById((int) $target->correction_id);
        if (! $correction
            || ! in_array((string) $correction->status, ['PUBLISHED', 'CONSUMED_CURRENT', 'CLOSED'], true)
            || (int) $correction->replacement_publication_id !== (int) $current->publication_id
            || (int) $correction->new_run_id !== (int) $current->run_id) {
            return false;
        }

        $replacement = $this->publications->resolveCurrentReadablePublicationForTradeDate((string) $target->trade_date);
        $run = DB::table('eod_runs')->where('run_id', (int) $current->run_id)->first();
        if (! $replacement || ! $run
            || (string) $run->terminal_status !== 'SUCCESS'
            || (string) $run->publishability_state !== 'READABLE') {
            return false;
        }

        $this->checkpointCompletedTarget($target, $replacement, $run);

        return true;
    }

    private function resolveCampaign($resume)
    {
        $admission = $this->admissions->activeDecision();
        $latest = DB::table('md_stage8_reconstruction_campaigns')->orderByDesc('campaign_id')->first();
        if ($latest && (string) $latest->state === 'COMPLETE') {
            $oracle = $this->auditCampaign((int) $latest->campaign_id);
            if ($oracle['violation_count'] === 0) {
                return $latest;
            }

            throw new \RuntimeException('STAGE8_COMPLETED_CAMPAIGN_ORACLE_DRIFT: a completed campaign no longer satisfies its frozen oracle.');
        }
        if ($latest && in_array((string) $latest->state, ['RUNNING', 'BLOCKED'], true)) {
            if ((string) $latest->state === 'BLOCKED'
                && $admission
                && (int) $admission->measurement_campaign_id === (int) $latest->campaign_id
                && $latest->admission_decision_id === null) {
                if (! $resume) {
                    throw new \RuntimeException('STAGE8_CAMPAIGN_RESUME_REQUIRED: --resume is required to supersede the measured blocked campaign.');
                }
                DB::table('md_stage8_reconstruction_campaigns')->where('campaign_id', $latest->campaign_id)->update([
                    'state' => 'SUPERSEDED',
                    'superseded_at' => $this->now(),
                    'updated_at' => $this->now(),
                ]);
                $latest = null;
            }
        }
        if ($latest && in_array((string) $latest->state, ['RUNNING', 'BLOCKED'], true)) {
            if (! $resume) {
                throw new \RuntimeException('STAGE8_CAMPAIGN_RESUME_REQUIRED: unfinished campaign '.(int) $latest->campaign_id.' exists.');
            }
            DB::table('md_stage8_reconstruction_campaigns')->where('campaign_id', $latest->campaign_id)->update([
                'state' => 'RUNNING',
                'updated_at' => $this->now(),
            ]);
            DB::table('md_stage8_reconstruction_targets')
                ->where('campaign_id', $latest->campaign_id)
                ->where('state', 'FAILED')
                ->update(['state' => 'PENDING', 'reason_code' => null, 'updated_at' => $this->now()]);
            return DB::table('md_stage8_reconstruction_campaigns')->where('campaign_id', $latest->campaign_id)->first();
        }

        if (! $admission) {
            throw new \RuntimeException('STAGE8_ADMISSION_EVIDENCE_INVALID: an active conformant-corpus admission decision is required.');
        }
        $targets = $this->baselineTargets($admission);
        $scopeStart = (string) $targets[0]->trade_date;
        $scopeEnd = (string) $targets[count($targets) - 1]->trade_date;
        $dates = array_map(function ($row) {
            return (string) $row->trade_date;
        }, $targets);
        $this->assertFrozenScopeMatchesCalendar($scopeStart, $scopeEnd, $dates);
        foreach ($targets as $target) {
            $target->baseline_bars_snapshot_hash = $this->artifactSnapshotHash(
                (int) $target->publication_id,
                (string) $target->trade_date,
                'bars',
                true
            );
            $target->baseline_indicators_snapshot_hash = $this->artifactSnapshotHash(
                (int) $target->publication_id,
                (string) $target->trade_date,
                'indicators',
                true
            );
            $target->baseline_eligibility_snapshot_hash = $this->artifactSnapshotHash(
                (int) $target->publication_id,
                (string) $target->trade_date,
                'eligibility',
                true
            );
        }
        $targetPayload = array_map(function ($row) {
            return [
                'trade_date' => (string) $row->trade_date,
                'publication_id' => (int) $row->publication_id,
                'run_id' => (int) $row->run_id,
                'publication_version' => (int) $row->publication_version,
                'bars_batch_hash' => (string) $row->bars_batch_hash,
                'indicators_batch_hash' => (string) $row->indicators_batch_hash,
                'eligibility_batch_hash' => (string) $row->eligibility_batch_hash,
                'bars_snapshot_hash' => (string) $row->baseline_bars_snapshot_hash,
                'indicators_snapshot_hash' => (string) $row->baseline_indicators_snapshot_hash,
                'eligibility_snapshot_hash' => (string) $row->baseline_eligibility_snapshot_hash,
            ];
        }, $targets);
        $targetHash = hash('sha256', json_encode($targetPayload, JSON_UNESCAPED_SLASHES));
        $now = $this->now();

        $campaignId = DB::transaction(function () use ($targets, $scopeStart, $scopeEnd, $targetHash, $now, $admission) {
            $campaignUid = hash('sha256', $targetHash.'|'.$now);
            $campaignId = DB::table('md_stage8_reconstruction_campaigns')->insertGetId([
                'campaign_uid' => $campaignUid,
                'scope_start' => $scopeStart,
                'scope_end' => $scopeEnd,
                'target_date_count' => count($targets),
                'baseline_max_publication_id' => max(array_map(function ($row) {
                    return (int) $row->publication_id;
                }, $targets)),
                'state' => 'RUNNING',
                'admission_decision_id' => (int) $admission->admission_decision_id,
                'supersedes_campaign_id' => (int) $admission->measurement_campaign_id,
                'superseded_at' => null,
                'baseline_target_set_hash' => $targetHash,
                'started_at' => $now,
                'completed_at' => null,
                'result_json' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($targets as $target) {
                DB::table('md_stage8_reconstruction_targets')->insert([
                    'campaign_id' => $campaignId,
                    'trade_date' => (string) $target->trade_date,
                    'baseline_publication_id' => (int) $target->publication_id,
                    'baseline_run_id' => (int) $target->run_id,
                    'baseline_publication_version' => (int) $target->publication_version,
                    'baseline_bars_batch_hash' => (string) $target->bars_batch_hash,
                    'baseline_indicators_batch_hash' => (string) $target->indicators_batch_hash,
                    'baseline_eligibility_batch_hash' => (string) $target->eligibility_batch_hash,
                    'baseline_bars_snapshot_hash' => (string) $target->baseline_bars_snapshot_hash,
                    'baseline_indicators_snapshot_hash' => (string) $target->baseline_indicators_snapshot_hash,
                    'baseline_eligibility_snapshot_hash' => (string) $target->baseline_eligibility_snapshot_hash,
                    'correction_id' => null,
                    'replacement_publication_id' => null,
                    'replacement_run_id' => null,
                    'state' => 'PENDING',
                    'reason_code' => null,
                    'completed_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            return $campaignId;
        });

        return DB::table('md_stage8_reconstruction_campaigns')->where('campaign_id', $campaignId)->first();
    }

    private function baselineTargets($admission = null): array
    {
        $query = DB::table('eod_current_publication_pointer as pointer')
            ->join('eod_publications as publication', 'publication.publication_id', '=', 'pointer.publication_id')
            ->join('eod_runs as run', 'run.run_id', '=', 'publication.run_id')
            ->orderBy('pointer.trade_date');
        if ($admission) {
            $query->where('pointer.trade_date', '>=', (string) $admission->admitted_from)
                ->where('pointer.trade_date', '<=', (string) $admission->measured_through);
        }
        $targets = $query
            ->get([
                'pointer.trade_date', 'publication.publication_id', 'publication.run_id',
                'publication.publication_version', 'publication.bars_batch_hash',
                'publication.indicators_batch_hash', 'publication.eligibility_batch_hash',
                'publication.seal_state', 'run.terminal_status',
            ])
            ->all();

        if ($targets === []) {
            throw new \RuntimeException('STAGE8_FROZEN_SCOPE_EMPTY: no current publication pointers exist.');
        }

        foreach ($targets as $target) {
            if ((string) $target->seal_state !== 'SEALED'
                || ! in_array((string) $target->terminal_status, ['SUCCESS', 'COMPLETED'], true)) {
                throw new \RuntimeException('STAGE8_BASELINE_NOT_SEALED: '.$target->trade_date.'.');
            }
            foreach (['bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash'] as $field) {
                if (! preg_match('/^[a-f0-9]{64}$/', (string) $target->{$field})) {
                    throw new \RuntimeException('STAGE8_BASELINE_HASH_MISSING: '.$target->trade_date.' '.$field.'.');
                }
            }
        }

        return $targets;
    }

    private function campaignTargets($campaignId): array
    {
        return DB::table('md_stage8_reconstruction_targets')
            ->where('campaign_id', $campaignId)
            ->orderBy('trade_date')
            ->get()
            ->all();
    }

    private function tickerCodesForDates(array $dates): array
    {
        return $this->tickers->getTickerCodesForTradeDates($dates);
    }

    private function assertFrozenScopeMatchesCalendar($startDate, $endDate, array $pointerDates): void
    {
        $calendarDates = array_values($this->calendar->tradingDatesBetween($startDate, $endDate));
        if ($calendarDates !== array_values($pointerDates)) {
            throw new \RuntimeException('STAGE8_FROZEN_SCOPE_POINTER_CALENDAR_MISMATCH: the campaign cannot omit or invent a trading date.');
        }
    }

    private function completedCampaignResult($campaign, $alreadyComplete): array
    {
        $oracle = $this->auditCampaign((int) $campaign->campaign_id);
        return [
            'status' => $alreadyComplete ? 'ALREADY_COMPLETE' : 'COMPLETE',
            'campaign_id' => (int) $campaign->campaign_id,
            'campaign_uid' => (string) $campaign->campaign_uid,
            'scope_start' => (string) $campaign->scope_start,
            'scope_end' => (string) $campaign->scope_end,
            'target_date_count' => (int) $campaign->target_date_count,
            'oracle' => $oracle,
            'stage_9_replay' => 'NOT_EXECUTED',
        ];
    }

    private function outputDirectory($campaign, $override)
    {
        if ($override !== null && trim((string) $override) !== '') {
            return rtrim((string) $override, '/\\');
        }

        return storage_path('app/market-data/stage8/'.(string) $campaign->campaign_uid);
    }

    private function ensureDirectory($directory): void
    {
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new \RuntimeException('STAGE8_OUTPUT_DIRECTORY_CREATE_FAILED.');
        }
    }

    private function acquireToCache($campaign, $directory, array $dates, array $tickerCodes, array $tickerCounts): array
    {
        $cacheDirectory = $directory.'/acquisition';
        $rowsDirectory = $cacheDirectory.'/rows';
        $this->ensureDirectory($rowsDirectory);
        $manifestPath = $cacheDirectory.'/manifest.json';
        $dateSetHash = hash('sha256', json_encode(array_values($dates), JSON_UNESCAPED_SLASHES));
        $tickerSetHash = hash('sha256', json_encode(array_values($tickerCodes), JSON_UNESCAPED_SLASHES));
        $manifest = $this->readJsonFile($manifestPath);

        if ($manifest === null) {
            $manifest = [
                'schema_version' => 'stage8-acquisition-cache/v1',
                'campaign_uid' => (string) $campaign->campaign_uid,
                'scope_start' => (string) $campaign->scope_start,
                'scope_end' => (string) $campaign->scope_end,
                'date_set_hash' => $dateSetHash,
                'ticker_set_hash' => $tickerSetHash,
                'ticker_count' => count($tickerCodes),
                'ticker_counts' => $tickerCounts,
                'ticker_counts_hash' => hash('sha256', json_encode($tickerCounts, JSON_UNESCAPED_SLASHES)),
                'batch_size' => self::ACQUISITION_BATCH_SIZE,
                'batch_count' => (int) ceil(count($tickerCodes) / self::ACQUISITION_BATCH_SIZE),
                'completed_batches' => [],
                'state' => 'ACQUIRING',
                'created_at' => $this->now(),
                'updated_at' => $this->now(),
            ];
            $this->writeJsonFile($manifestPath, $manifest);
        }

        foreach ([
            'campaign_uid' => (string) $campaign->campaign_uid,
            'scope_start' => (string) $campaign->scope_start,
            'scope_end' => (string) $campaign->scope_end,
            'date_set_hash' => $dateSetHash,
            'ticker_set_hash' => $tickerSetHash,
            'ticker_count' => count($tickerCodes),
            'ticker_counts_hash' => hash('sha256', json_encode($tickerCounts, JSON_UNESCAPED_SLASHES)),
            'batch_size' => self::ACQUISITION_BATCH_SIZE,
        ] as $field => $expected) {
            if (($manifest[$field] ?? null) !== $expected) {
                throw new \RuntimeException('STAGE8_ACQUISITION_CACHE_IDENTITY_MISMATCH: '.$field.'.');
            }
        }

        $completed = array_fill_keys(array_map('intval', $manifest['completed_batches'] ?? []), true);
        $batches = array_chunk($tickerCodes, self::ACQUISITION_BATCH_SIZE);
        foreach ($batches as $index => $tickerBatch) {
            $batchNumber = $index + 1;
            if (isset($completed[$batchNumber])) {
                continue;
            }

            $previousWindowDays = config('market_data.source.api_backfill.window_days');
            config()->set('market_data.source.api_backfill.window_days', 2000);
            try {
                $acquired = $this->acquisition->acquire(
                    (string) $campaign->scope_start,
                    (string) $campaign->scope_start,
                    (string) $campaign->scope_end,
                    $dates,
                    $tickerBatch,
                    [
                        'source_acquisition_context' => 'stage8_corpus_reconstruction',
                        'source_acquisition_batch_id' => (string) $campaign->campaign_uid.'-'.str_pad((string) $batchNumber, 3, '0', STR_PAD_LEFT),
                    ]
                );
            } finally {
                config()->set('market_data.source.api_backfill.window_days', $previousWindowDays);
            }

            if (($acquired['source_acquisition_state'] ?? null) === 'SYSTEMIC_FAILED') {
                throw new \RuntimeException('STAGE8_SOURCE_ACQUISITION_FAILED: Yahoo range acquisition is systemically unavailable.');
            }

            foreach ($dates as $date) {
                $line = json_encode([
                    'batch_number' => $batchNumber,
                    'rows' => $acquired['rows_by_trade_date'][$date] ?? [],
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                if ($line === false || file_put_contents($rowsDirectory.'/'.$date.'.jsonl', $line."\n", FILE_APPEND | LOCK_EX) === false) {
                    throw new \RuntimeException('STAGE8_ACQUISITION_CACHE_WRITE_FAILED: '.$date.'.');
                }
            }

            unset($acquired);
            $completed[$batchNumber] = true;
            $manifest['completed_batches'] = array_keys($completed);
            sort($manifest['completed_batches'], SORT_NUMERIC);
            $manifest['updated_at'] = $this->now();
            $this->writeJsonFile($manifestPath, $manifest);
        }

        $manifest['state'] = 'COMPLETE';
        $manifest['completed_at'] = $this->now();
        $manifest['updated_at'] = $this->now();
        $this->writeJsonFile($manifestPath, $manifest);

        return $manifest;
    }

    private function loadCachedDateAcquisition($directory, $tradeDate, $expectedTickerCount, $batchId, $scopeStart, $scopeEnd): array
    {
        $path = $directory.'/acquisition/rows/'.$tradeDate.'.jsonl';
        if (! is_file($path)) {
            throw new \RuntimeException('STAGE8_ACQUISITION_CACHE_DATE_MISSING: '.$tradeDate.'.');
        }

        $batchRows = [];
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new \RuntimeException('STAGE8_ACQUISITION_CACHE_DATE_UNREADABLE: '.$tradeDate.'.');
        }
        try {
            while (($line = fgets($handle)) !== false) {
                $entry = json_decode($line, true);
                if (! is_array($entry) || ! isset($entry['batch_number']) || ! is_array($entry['rows'] ?? null)) {
                    throw new \RuntimeException('STAGE8_ACQUISITION_CACHE_DATE_INVALID: '.$tradeDate.'.');
                }
                // A crash may leave a complete line before its batch checkpoint. A resumed batch
                // appends a replacement line; last complete line wins deterministically.
                $batchRows[(int) $entry['batch_number']] = $entry['rows'];
            }
        } finally {
            fclose($handle);
        }
        ksort($batchRows, SORT_NUMERIC);
        $rows = [];
        foreach ($batchRows as $items) {
            $rows = array_merge($rows, $items);
        }

        $returned = [];
        foreach ($rows as $row) {
            $code = strtoupper(trim((string) ($row['ticker_code'] ?? '')));
            if ($code !== '') {
                $returned[$code] = true;
            }
        }
        $successCount = count($returned);
        $failedCount = max(0, $expectedTickerCount - $successCount);
        $maxFailedAllowed = max(0, $expectedTickerCount - (int) ceil($expectedTickerCount * (float) config('market_data.coverage_gate.min_ratio', 0.98)));
        $telemetry = [
            'source_mode' => 'api',
            'source_acquisition_batch_id' => $batchId,
            'source_acquisition_mode' => 'range_window',
            'source_window_start' => $scopeStart,
            'source_window_end' => $scopeEnd,
            'warmup_start' => $scopeStart,
            'requested_start' => $scopeStart,
            'requested_end' => $scopeEnd,
            'source_acquisition_state' => $successCount === 0 ? 'FAILED' : ($failedCount === 0 ? 'SUCCESS' : 'PARTIAL_SUCCESS'),
            'source_final_status' => $successCount === 0 ? 'FAILED' : ($failedCount === 0 ? 'SUCCESS' : 'PARTIAL'),
            'expected_ticker_count' => $expectedTickerCount,
            'success_ticker_count' => $successCount,
            'failed_ticker_count' => $failedCount,
            'max_failed_allowed_for_coverage' => $maxFailedAllowed,
            'coverage_impossible' => $failedCount > $maxFailedAllowed,
            'returned_row_count' => count($rows),
            'accepted_row_count' => count($rows),
            'rejected_row_count' => 0,
            'invalid_row_count' => 0,
            'final_reason_code' => $successCount === 0
                ? 'RUN_SOURCE_NO_VALID_DATA'
                : ($failedCount > $maxFailedAllowed ? 'COVERAGE_BELOW_THRESHOLD' : ($failedCount > 0 ? 'RUN_SOURCE_PARTIAL_RESPONSE' : null)),
        ];

        return [$rows, $telemetry];
    }

    private function readJsonFile($path): ?array
    {
        if (! is_file($path)) {
            return null;
        }
        $value = json_decode((string) file_get_contents($path), true);
        if (! is_array($value)) {
            throw new \RuntimeException('STAGE8_ACQUISITION_CACHE_MANIFEST_INVALID.');
        }

        return $value;
    }

    private function writeJsonFile($path, array $value): void
    {
        $encoded = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $temporary = $path.'.tmp';
        if ($encoded === false
            || file_put_contents($temporary, $encoded."\n", LOCK_EX) === false
            || ! rename($temporary, $path)) {
            throw new \RuntimeException('STAGE8_ACQUISITION_CACHE_WRITE_FAILED: manifest.');
        }
    }

    private function cleanupAcquisitionRows($directory): void
    {
        $rowsDirectory = $directory.'/acquisition/rows';
        if (! is_dir($rowsDirectory)) {
            return;
        }
        foreach (new \DirectoryIterator($rowsDirectory) as $file) {
            if ($file->isDot()) {
                continue;
            }
            if (! $file->isFile() || substr($file->getFilename(), -6) !== '.jsonl') {
                throw new \RuntimeException('STAGE8_ACQUISITION_CACHE_CLEANUP_REFUSED: unexpected cache entry.');
            }
            if (! unlink($file->getPathname())) {
                throw new \RuntimeException('STAGE8_ACQUISITION_CACHE_CLEANUP_FAILED: '.$file->getFilename().'.');
            }
        }
        if (! rmdir($rowsDirectory)) {
            throw new \RuntimeException('STAGE8_ACQUISITION_CACHE_CLEANUP_FAILED: rows directory.');
        }
    }

    private function now(): string
    {
        return Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();
    }

    private function bindRunAndCandidateAdmission($runId, $admissionDecisionId): void
    {
        $decision = DB::table('md_corpus_admission_decisions')
            ->where('admission_decision_id', $admissionDecisionId)
            ->where('state', 'ACTIVE')
            ->first();
        if (! $decision) {
            throw new \RuntimeException('STAGE8_ADMISSION_EVIDENCE_INVALID: active decision cannot be resolved.');
        }

        DB::transaction(function () use ($runId, $admissionDecisionId) {
            DB::table('eod_runs')->where('run_id', $runId)->update([
                'corpus_admission_decision_id' => $admissionDecisionId,
            ]);
            DB::table('eod_publications')->where('run_id', $runId)->update([
                'corpus_admission_decision_id' => $admissionDecisionId,
            ]);
        });
    }

    private function reasonCode(\Throwable $e): string
    {
        return preg_match('/^([A-Z0-9_]+):/', (string) $e->getMessage(), $matches)
            ? $matches[1]
            : 'STAGE8_RECONSTRUCTION_FAILED';
    }

    private function artifactSnapshotViolations($campaignId): array
    {
        $baselineViolations = 0;
        $replacementViolations = 0;
        $targets = $this->campaignTargets($campaignId);

        foreach ($targets as $target) {
            foreach (['bars', 'indicators', 'eligibility'] as $artifact) {
                $baselineField = 'baseline_'.$artifact.'_snapshot_hash';
                $actualBaseline = $this->artifactSnapshotHash(
                    (int) $target->baseline_publication_id,
                    (string) $target->trade_date,
                    $artifact,
                    true
                );
                if (! hash_equals((string) $target->{$baselineField}, $actualBaseline)) {
                    $baselineViolations++;
                }

                if ($target->replacement_publication_id === null) {
                    $replacementViolations++;
                    continue;
                }

                $publication = DB::table('eod_publications')
                    ->where('publication_id', (int) $target->replacement_publication_id)
                    ->first();
                $publicationField = $artifact.'_batch_hash';
                $actualReplacement = $this->artifactSnapshotHash(
                    (int) $target->replacement_publication_id,
                    (string) $target->trade_date,
                    $artifact,
                    false
                );
                if (! $publication
                    || ! hash_equals((string) ($publication->{$publicationField} ?? ''), $actualReplacement)) {
                    $replacementViolations++;
                }
            }
        }

        return [$baselineViolations, $replacementViolations];
    }

    private function artifactSnapshotHash($publicationId, $tradeDate, string $artifact, bool $includeProvenance): string
    {
        $snapshot = $this->artifacts->loadPublicationArtifactSnapshot($publicationId, $tradeDate, $artifact);
        $columns = $this->artifactSnapshotColumns($artifact, $snapshot['columns'], $includeProvenance);
        return $this->hashes->hashRows($snapshot['rows'], $columns);
    }

    private function artifactSnapshotColumns(string $artifact, array $available, bool $includeProvenance): array
    {
        $columns = $this->artifactColumns($artifact);
        if ($includeProvenance) {
            $columns = array_values(array_unique(array_merge($columns, [
                'run_id', 'publication_id', 'created_at',
            ])));
            // Baseline snapshots deliberately add provenance fields outside the publication
            // hash contract, so their private immutable-snapshot contract uses a stable
            // alphabetical order. Replacement publications must retain the declared artifact
            // hash-column order used by MarketDataPipelineService::completeHash().
            sort($columns, SORT_STRING);
        }

        return array_values(array_intersect($columns, $available));
    }

    private function artifactColumns(string $artifact): array
    {
        if ($artifact === 'bars') {
            return MarketDataPipelineService::BARS_HASH_COLUMNS;
        }
        if ($artifact === 'indicators') {
            return MarketDataPipelineService::INDICATORS_HASH_COLUMNS;
        }
        if ($artifact === 'eligibility') {
            return MarketDataPipelineService::ELIGIBILITY_HASH_COLUMNS;
        }

        throw new \InvalidArgumentException('Unknown Stage 8 artifact: '.$artifact);
    }
}
