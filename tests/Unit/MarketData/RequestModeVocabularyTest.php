<?php

use App\Application\MarketData\DTOs\MarketDataStageInput;
use App\Console\Commands\MarketData\AbstractMarketDataCommand;
use App\Application\MarketData\Services\MarketDataPipelineService;
use PHPUnit\Framework\TestCase;

/**
 * Behavioural cover for the request-mode vocabulary.
 *
 * `ImportPromoteSeparationStaticGuardTest` asserted that the four mode names appear in
 * MarketDataStageInput. They did — inside a private static property that nothing ever read. The
 * enforcement lived elsewhere, in an inline copy of the same list, and the guard was reading the
 * dead one as its evidence.
 *
 * The list is defined once now. These tests drive the rule instead of reading it.
 */
class RequestModeVocabularyTest extends TestCase
{
    private function assertMode(string $mode, string $stage): void
    {
        $guard = new ReflectionMethod(MarketDataPipelineService::class, 'assertAllowedRequestMode');
        $guard->setAccessible(true);

        $guard->invoke(
            (new ReflectionClass(MarketDataPipelineService::class))->newInstanceWithoutConstructor(),
            $mode,
            $stage
        );
    }

    /**
     * @dataProvider allowedModes
     */
    public function test_every_declared_request_mode_is_accepted_by_the_pipeline_guard(string $mode): void
    {
        // import_only is the one mode restricted by stage, and has its own test below.
        $this->assertMode($mode, $mode === 'import_only' ? 'INGEST_BARS' : 'FINALIZE');

        $this->assertTrue(true);
    }

    public function allowedModes(): array
    {
        $cases = [];

        foreach (MarketDataStageInput::ALLOWED_REQUEST_MODES as $mode) {
            $cases[$mode] = [$mode];
        }

        return $cases;
    }

    public function test_an_unknown_request_mode_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('REQUEST_MODE_INVALID');

        $this->assertMode('publish_everything', 'FINALIZE');
    }

    /**
     * The separation this whole contract exists for: an import may write bars and stop. It may
     * not walk into the stages that make a dataset readable.
     */
    public function test_import_only_cannot_enter_a_promote_stage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('REQUEST_MODE_IMPORT_BLOCKED_FROM_PROMOTE');

        $this->assertMode('import_only', 'FINALIZE');
    }

    public function test_import_only_is_accepted_for_the_ingest_stage(): void
    {
        $this->assertMode('import_only', 'INGEST_BARS');

        $this->assertTrue(true);
    }

    /**
     * The operator-facing vocabulary is narrower on purpose. It must stay a subset: a command
     * that accepted a mode the pipeline rejects would blow up after the run row already exists,
     * rather than being refused at the command surface with a registered reason code.
     */
    public function test_operator_request_modes_are_a_subset_of_what_the_pipeline_accepts(): void
    {
        $this->assertSame(
            [],
            array_values(array_diff(
                AbstractMarketDataCommand::OPERATOR_REQUEST_MODES,
                MarketDataStageInput::ALLOWED_REQUEST_MODES
            )),
            'A command offers a request_mode the pipeline would reject.'
        );
    }

    /**
     * The DTO fills the mode in when a caller does not state one, and the default it picks has to
     * be a mode the pipeline accepts.
     *
     * @dataProvider defaultedModes
     */
    public function test_a_defaulted_request_mode_is_always_valid(string $stage, ?int $correctionId, string $expected): void
    {
        $input = new MarketDataStageInput('2026-03-20', 'manual_file', null, $stage, $correctionId);

        $this->assertSame($expected, $input->requestMode);
        $this->assertContains($input->requestMode, MarketDataStageInput::ALLOWED_REQUEST_MODES);
    }

    public function defaultedModes(): array
    {
        return [
            'ingest defaults to import only' => ['INGEST_BARS', null, 'import_only'],
            'a correction id defaults to correction' => ['FINALIZE', 7, 'correction'],
            'anything else defaults to full publish' => ['FINALIZE', null, 'full_publish'],
        ];
    }

    /**
     * An explicit mode wins over the default, including when the stage would have implied a
     * different one. Otherwise a promote run started at the ingest stage would silently become
     * import-only and never publish.
     */
    public function test_an_explicit_request_mode_overrides_the_stage_default(): void
    {
        $input = new MarketDataStageInput('2026-03-20', 'manual_file', null, 'INGEST_BARS', null, false, null, 'promote');

        $this->assertSame('promote', $input->requestMode);
    }
}
